<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Xelyos\MailRp\Exception\TransportException;

/**
 * Sends the requests through any PSR-18 client (Guzzle, Symfony HttpClient, a mock client...).
 *
 * The timeout given to the SDK is not applied: configure it on your PSR-18 client.
 */
final readonly class Psr18Transport implements Transport
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): RawResponse
    {
        $request = $this->requestFactory->createRequest($method, $url);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        if (null !== $body) {
            $request = $request->withBody($this->streamFactory->createStream($body));
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new TransportException(sprintf('%s %s failed: %s', $method, $url, $exception->getMessage()), 0, false, $exception);
        }

        $responseHeaders = [];
        foreach ($response->getHeaders() as $name => $values) {
            $responseHeaders[(string) $name] = implode(', ', $values);
        }

        return new RawResponse($response->getStatusCode(), $responseHeaders, (string) $response->getBody());
    }
}
