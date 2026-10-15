<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Tests;

use Xelyos\MailRp\Http\RawResponse;
use Xelyos\MailRp\Http\Transport;

/**
 * Replays queued responses and records the requests.
 */
final class FakeTransport implements Transport
{
    /** @var list<array{method: string, url: string, headers: array<string, string>, body: string|null, timeout: float}> */
    public array $requests = [];

    /** @var list<RawResponse> */
    private array $responses = [];

    /**
     * @param array<string, string> $headers
     * @param array<mixed>          $body
     */
    public function queue(int $status, array|string|null $body = null, array $headers = []): self
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
            $headers += ['Content-Type' => 'application/json'];
        }
        $this->responses[] = new RawResponse($status, $headers, $body ?? '');

        return $this;
    }

    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): RawResponse
    {
        $this->requests[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body, 'timeout' => $timeout];

        return array_shift($this->responses) ?? throw new \LogicException(sprintf('No response queued for %s %s', $method, $url));
    }

    /**
     * @return array{method: string, url: string, headers: array<string, string>, body: string|null, timeout: float}
     */
    public function last(): array
    {
        return $this->requests[array_key_last($this->requests)] ?? throw new \LogicException('No request sent');
    }

    /** @return array<mixed> */
    public function lastJsonBody(): ?array
    {
        $body = $this->last()['body'];
        if (null === $body) {
            return null;
        }
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : throw new \LogicException('Body is not a JSON object');
    }
}
