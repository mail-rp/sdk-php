<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Http;

use Xelyos\MailRp\Exception\ApiException;
use Xelyos\MailRp\Exception\RateLimitException;
use Xelyos\MailRp\Exception\UnexpectedResponseException;
use Xelyos\MailRp\MailRpClient;
use Xelyos\MailRp\ResponseMeta;

/**
 * Builds, sends and decodes the API requests: authentication, JSON, retries on 429, error mapping.
 *
 * @internal
 */
final class ApiRequester
{
    /** Format of the dates sent in query strings and bodies (period of the events) */
    public const DATE_FORMAT = 'Y-m-d H:i';

    private ?ResponseMeta $lastResponse = null;

    /** @var \Closure(int): void */
    private readonly \Closure $sleep;

    /**
     * @param (\Closure(int): void)|null $sleep waits the given number of seconds (replaced in tests)
     */
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
        private readonly Transport $transport,
        private readonly float $timeout,
        private readonly int $maxRetries,
        private readonly int $maxRetryDelay,
        ?\Closure $sleep = null,
    ) {
        $this->sleep = $sleep ?? static function (int $seconds): void {
            sleep($seconds);
        };
    }

    public function hasApiKey(): bool
    {
        return null !== $this->apiKey;
    }

    public function getLastResponse(): ?ResponseMeta
    {
        return $this->lastResponse;
    }

    /**
     * @param array<string, mixed>      $query null values are skipped
     * @param array<string, mixed>|null $body  sent as JSON
     *
     * @return array<mixed>|null decoded JSON body, null when the response has no body (204)
     */
    public function request(string $method, string $path, array $query = [], ?array $body = null): ?array
    {
        $url = rtrim($this->baseUrl, '/') . $path;
        $queryString = self::buildQuery($query);
        if ('' !== $queryString) {
            $url .= '?' . $queryString;
        }

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'mail-rp-php/' . MailRpClient::VERSION,
        ];
        if (null !== $this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        $payload = null;
        if (null !== $body) {
            $headers['Content-Type'] = 'application/json';
            $payload = json_encode(
                self::normalize($body),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
            );
        }

        $attempt = 0;
        while (true) {
            $response = $this->transport->send($method, $url, $headers, $payload, $this->timeout);
            $this->lastResponse = ResponseMeta::fromResponse($response);

            if ($response->statusCode < 400) {
                return $this->decode($response);
            }

            $exception = ApiException::fromResponse($response);
            // 429: the request was not processed, so it can safely be replayed after the announced delay
            if ($exception instanceof RateLimitException && $attempt < $this->maxRetries) {
                $delay = max(1, $exception->getRetryAfter() ?? 1);
                if ($delay <= $this->maxRetryDelay) {
                    ++$attempt;
                    ($this->sleep)($delay);

                    continue;
                }
            }

            throw $exception;
        }
    }

    /**
     * Encodes a path segment (email, domain name, uuid...).
     */
    public static function segment(string $value): string
    {
        return rawurlencode($value);
    }

    /**
     * @param array<string, mixed> $query
     */
    public static function buildQuery(array $query): string
    {
        $parts = [];
        foreach ($query as $name => $value) {
            $value = self::normalize($value);
            if (null === $value) {
                continue;
            }
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            if (!is_scalar($value)) {
                continue;
            }
            $parts[] = rawurlencode($name) . '=' . rawurlencode((string) $value);
        }

        return implode('&', $parts);
    }

    /**
     * Turns the SDK-friendly values (enums, dates) into JSON / query values, recursively.
     */
    private static function normalize(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format(self::DATE_FORMAT);
        }
        if (is_array($value)) {
            return array_map(self::normalize(...), $value);
        }

        return $value;
    }

    /** @return array<mixed>|null */
    private function decode(RawResponse $response): ?array
    {
        if (204 === $response->statusCode || '' === trim($response->body)) {
            return null;
        }

        try {
            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new UnexpectedResponseException(sprintf('The Mail RP API returned an invalid JSON body (HTTP %d)', $response->statusCode), 0, $exception);
        }
        if (!is_array($data)) {
            throw new UnexpectedResponseException('The Mail RP API returned a JSON scalar instead of an object');
        }

        return $data;
    }
}
