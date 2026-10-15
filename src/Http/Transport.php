<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Http;

use Xelyos\MailRp\Exception\TransportException;

/**
 * Sends one HTTP request. Implement it to plug any HTTP stack in the SDK; {@see CurlTransport} is used by default
 * and {@see Psr18Transport} wraps any PSR-18 client.
 */
interface Transport
{
    /**
     * @param array<string, string> $headers
     * @param float                 $timeout seconds
     *
     * @throws TransportException when no HTTP response could be obtained (DNS, connection, timeout...)
     */
    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): RawResponse;
}
