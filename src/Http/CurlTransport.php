<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Http;

use Xelyos\MailRp\Exception\TransportException;

/**
 * Default transport, based on the cURL extension.
 */
final readonly class CurlTransport implements Transport
{
    /**
     * @param array<int, mixed> $curlOptions extra CURLOPT_* options (proxy, CA bundle...), applied last
     */
    public function __construct(
        private array $curlOptions = [],
        private float $connectTimeout = 10.0,
    ) {
    }

    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): RawResponse
    {
        $handle = curl_init();
        if (false === $handle) {
            throw new TransportException('Unable to initialize cURL');
        }

        $responseHeaders = [];
        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_TIMEOUT_MS => (int) round($timeout * 1000),
            CURLOPT_CONNECTTIMEOUT_MS => (int) round(min($timeout, $this->connectTimeout) * 1000),
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$responseHeaders): int {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, 'HTTP/')) {
                    // New response (after a 100 Continue for instance): forget the previous headers
                    $responseHeaders = [];
                } elseif (str_contains($trimmed, ':')) {
                    [$name, $value] = explode(':', $trimmed, 2);
                    $name = strtolower(trim($name));
                    $value = trim($value);
                    $responseHeaders[$name] = isset($responseHeaders[$name])
                        ? $responseHeaders[$name] . ', ' . $value
                        : $value;
                }

                return strlen($line);
            },
        ];
        if (null !== $body) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($handle, $this->curlOptions + $options);

        $result = curl_exec($handle);
        if (!is_string($result)) {
            $errno = curl_errno($handle);

            throw new TransportException(sprintf('%s %s failed: %s', $method, $url, curl_error($handle) ?: 'unknown cURL error'), $errno, CURLE_OPERATION_TIMEDOUT === $errno);
        }

        return new RawResponse((int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE), $responseHeaders, $result);
    }
}
