<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * HTTP 429: too many requests (60 per minute per key, 300 per minute per server). Thrown once the automatic retries
 * configured on the client are exhausted.
 */
class RateLimitException extends ApiException
{
    /**
     * Seconds to wait before retrying, as announced by the API.
     */
    public function getRetryAfter(): ?int
    {
        $value = $this->getHeaders()['retry-after'] ?? null;

        return null !== $value && ctype_digit($value) ? (int) $value : null;
    }
}
