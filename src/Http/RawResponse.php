<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Http;

/**
 * HTTP response as returned by a transport, before any interpretation by the SDK.
 */
final readonly class RawResponse
{
    /** @var array<string, string> */
    public array $headers;

    /**
     * @param array<string, string> $headers header values indexed by name (any case)
     */
    public function __construct(
        public int $statusCode,
        array $headers,
        public string $body,
    ) {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}
