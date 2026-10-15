<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

use Xelyos\MailRp\Http\RawResponse;

/**
 * The API answered with an error status (4xx / 5xx). The message is the one returned by the API, in the language
 * configured on the key.
 */
class ApiException extends \RuntimeException implements MailRpExceptionInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly ?string $path = null,
        private readonly ?string $requestId = null,
        private readonly mixed $body = null,
        private readonly array $headers = [],
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * Builds the exception matching the status code of the response.
     */
    public static function fromResponse(RawResponse $response): self
    {
        $body = json_decode($response->body, true);
        $message = is_array($body) && is_string($body['message'] ?? null) && '' !== $body['message']
            ? $body['message']
            : sprintf('The Mail RP API returned HTTP %d', $response->statusCode);
        $path = is_array($body) && is_string($body['path'] ?? null) ? $body['path'] : null;

        $class = match (true) {
            400 === $response->statusCode, 422 === $response->statusCode => ValidationException::class,
            401 === $response->statusCode => AuthenticationException::class,
            403 === $response->statusCode => PermissionDeniedException::class,
            404 === $response->statusCode => NotFoundException::class,
            409 === $response->statusCode => ConflictException::class,
            429 === $response->statusCode => RateLimitException::class,
            503 === $response->statusCode => ServiceUnavailableException::class,
            $response->statusCode >= 500 => ServerException::class,
            default => self::class,
        };

        return new $class(
            $message,
            $response->statusCode,
            $path,
            $response->header('X-Request-Id'),
            $body ?? $response->body,
            $response->headers,
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Field or parameter the error is about (validation errors, conflicts, missing sub-resources), when given.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Identifier of the request, as shown in the history of the key in the panel. Null when the key was not
     * recognised (401) or the API is in maintenance.
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * Decoded JSON body, or the raw body when it is not JSON.
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * @return array<string, string> lower-cased header names
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
