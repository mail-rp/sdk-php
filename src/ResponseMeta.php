<?php

declare(strict_types=1);

namespace Xelyos\MailRp;

use Xelyos\MailRp\Http\RawResponse;

/**
 * Metadata of the last response received by the client (request id, rate limit state).
 */
final readonly class ResponseMeta
{
    public function __construct(
        public int $statusCode,
        /** Identifier of the request, as shown in the history of the key in the panel */
        public ?string $requestId,
        /** Requests allowed per minute by the most restrictive limiter (key or server) */
        public ?int $rateLimitLimit,
        /** Requests left in the current window of the most restrictive limiter */
        public ?int $rateLimitRemaining,
    ) {
    }

    public static function fromResponse(RawResponse $response): self
    {
        return new self(
            $response->statusCode,
            $response->header('X-Request-Id'),
            self::toInt($response->header('X-RateLimit-Limit')),
            self::toInt($response->header('X-RateLimit-Remaining')),
        );
    }

    private static function toInt(?string $value): ?int
    {
        return null !== $value && ctype_digit($value) ? (int) $value : null;
    }
}
