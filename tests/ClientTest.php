<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Xelyos\MailRp\Enum\SortOrder;
use Xelyos\MailRp\Exception\ApiException;
use Xelyos\MailRp\Exception\AuthenticationException;
use Xelyos\MailRp\Exception\ConflictException;
use Xelyos\MailRp\Exception\InvalidArgumentException;
use Xelyos\MailRp\Exception\NotFoundException;
use Xelyos\MailRp\Exception\PermissionDeniedException;
use Xelyos\MailRp\Exception\RateLimitException;
use Xelyos\MailRp\Exception\ServerException;
use Xelyos\MailRp\Exception\ServiceUnavailableException;
use Xelyos\MailRp\Exception\UnexpectedResponseException;
use Xelyos\MailRp\Exception\ValidationException;
use Xelyos\MailRp\Http\Psr18Transport;
use Xelyos\MailRp\MailRpClient;

final class ClientTest extends TestCase
{
    private FakeTransport $transport;

    /** @var list<int> */
    private array $sleeps = [];

    protected function setUp(): void
    {
        $this->transport = new FakeTransport();
        $this->sleeps = [];
    }

    private function client(int $maxRetries = 2, int $maxRetryDelay = 60): MailRpClient
    {
        return new MailRpClient(
            ' xls-mrp-test ',
            'https://api.example.test/',
            timeout: 12.5,
            maxRetries: $maxRetries,
            maxRetryDelay: $maxRetryDelay,
            transport: $this->transport,
            sleep: function (int $seconds): void {
                $this->sleeps[] = $seconds;
            },
        );
    }

    public function testSendsAuthenticatedJsonRequests(): void
    {
        $this->transport->queue(200, Fixtures::user(), ['X-Request-Id' => 'req-1', 'X-RateLimit-Limit' => '60', 'X-RateLimit-Remaining' => '59']);

        $client = $this->client();
        $user = $client->users->update('commander+rp@lspd.gov', ['name' => 'Foster', 'firstname' => 'Dàn']);

        $request = $this->transport->last();
        self::assertSame('PUT', $request['method']);
        self::assertSame('https://api.example.test/user/commander%2Brp%40lspd.gov', $request['url']);
        self::assertSame('Bearer xls-mrp-test', $request['headers']['Authorization']);
        self::assertSame('application/json', $request['headers']['Accept']);
        self::assertSame('application/json', $request['headers']['Content-Type']);
        self::assertSame('mail-rp-php/' . MailRpClient::VERSION, $request['headers']['User-Agent']);
        self::assertSame('{"name":"Foster","firstname":"Dàn"}', $request['body']);
        self::assertSame(12.5, $request['timeout']);

        self::assertSame('Dan', $user->firstname);
        $meta = $client->getLastResponse();
        self::assertNotNull($meta);
        self::assertSame('req-1', $meta->requestId);
        self::assertSame(60, $meta->rateLimitLimit);
        self::assertSame(59, $meta->rateLimitRemaining);
    }

    public function testBuildsQueryStrings(): void
    {
        $this->transport->queue(200, Fixtures::page([]));
        $this->client()->users->events->search(
            'commander@lspd.gov',
            new \DateTimeImmutable('2026-10-14 08:30'),
            '2026-10-20',
            ['search' => 'brief ing', 'order' => SortOrder::DESC, 'sort' => null, 'limit' => 5, 'calendar' => 'abc']
        );

        self::assertSame(
            'https://api.example.test/user/commander%40lspd.gov/event/search?start=2026-10-14%2008%3A30&end=2026-10-20&search=brief%20ing&order=desc&limit=5&calendar=abc',
            $this->transport->last()['url']
        );
        self::assertNull($this->transport->last()['body']);
        self::assertArrayNotHasKey('Content-Type', $this->transport->last()['headers']);
    }

    public function testCheckKeyOnlySendsThePermissionsFlagWhenAsked(): void
    {
        $key = ['message' => 'OK', 'key' => ['name' => 'Bot', 'description' => null, 'locale' => 'fr']];
        $this->transport
            ->queue(200, $key)
            ->queue(200, ['key' => $key['key'] + ['permissions' => ['user.read']]] + $key);

        $info = $this->client()->system->checkKey();
        self::assertSame('https://api.example.test/', $this->transport->last()['url']);
        self::assertNull($info->permissions);
        self::assertNull($info->hasPermission('user.read'));

        $info = $this->client()->system->checkKey(true);
        self::assertSame('https://api.example.test/?permissions=1', $this->transport->last()['url']);
        self::assertSame(['user.read'], $info->permissions);
        self::assertTrue($info->hasPermission('user.read'));
        self::assertFalse($info->hasPermission('user.delete'));
    }

    public function testNoContentResponses(): void
    {
        $this->transport->queue(204);
        $this->client()->users->delete('commander@lspd.gov');

        self::assertSame('DELETE', $this->transport->last()['method']);
        self::assertNull($this->transport->last()['body']);
    }

    /**
     * @return iterable<string, array{int, class-string<ApiException>}>
     */
    public static function errorStatuses(): iterable
    {
        yield '400' => [400, ValidationException::class];
        yield '401' => [401, AuthenticationException::class];
        yield '403' => [403, PermissionDeniedException::class];
        yield '404' => [404, NotFoundException::class];
        yield '405' => [405, ApiException::class];
        yield '409' => [409, ConflictException::class];
        yield '422' => [422, ValidationException::class];
        yield '429' => [429, RateLimitException::class];
        yield '500' => [500, ServerException::class];
        yield '503' => [503, ServiceUnavailableException::class];
    }

    /**
     * @param class-string<ApiException> $class
     */
    #[DataProvider('errorStatuses')]
    public function testMapsErrorStatuses(int $status, string $class): void
    {
        $this->transport->queue($status, ['message' => 'Adresse invalide', 'path' => 'email'], ['X-Request-Id' => 'req-9']);

        try {
            $this->client(maxRetries: 0)->users->get('commander@lspd.gov');
            self::fail('No exception thrown');
        } catch (ApiException $exception) {
            self::assertSame($class, $exception::class);
            self::assertSame('Adresse invalide', $exception->getMessage());
            self::assertSame($status, $exception->getStatusCode());
            self::assertSame('email', $exception->getPath());
            self::assertSame('req-9', $exception->getRequestId());
            self::assertSame(['message' => 'Adresse invalide', 'path' => 'email'], $exception->getBody());
        }
    }

    public function testErrorWithoutJsonBody(): void
    {
        $this->transport->queue(502, '<html>Bad gateway</html>');

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('The Mail RP API returned HTTP 502');
        $this->client()->server->get();
    }

    public function testRetriesRateLimitedRequests(): void
    {
        $this->transport
            ->queue(429, ['message' => 'Too many requests'], ['Retry-After' => '3'])
            ->queue(429, ['message' => 'Too many requests'], ['Retry-After' => '0'])
            ->queue(200, Fixtures::user());

        $user = $this->client()->users->get('commander@lspd.gov');

        self::assertSame('commander@lspd.gov', $user->email);
        self::assertSame([3, 1], $this->sleeps);
        self::assertCount(3, $this->transport->requests);
    }

    public function testGivesUpAfterMaxRetries(): void
    {
        $this->transport
            ->queue(429, ['message' => 'Too many requests'], ['Retry-After' => '2'])
            ->queue(429, ['message' => 'Too many requests'], ['Retry-After' => '7']);

        try {
            $this->client(maxRetries: 1)->users->get('commander@lspd.gov');
            self::fail('No exception thrown');
        } catch (RateLimitException $exception) {
            self::assertSame(7, $exception->getRetryAfter());
        }
        self::assertSame([2], $this->sleeps);
    }

    public function testDoesNotWaitLongerThanMaxRetryDelay(): void
    {
        $this->transport->queue(429, ['message' => 'Too many requests'], ['Retry-After' => '45']);

        $this->expectException(RateLimitException::class);
        try {
            $this->client(maxRetryDelay: 10)->users->get('commander@lspd.gov');
        } finally {
            self::assertSame([], $this->sleeps);
        }
    }

    public function testInvalidJsonBody(): void
    {
        $this->transport->queue(200, '{not json');

        $this->expectException(UnexpectedResponseException::class);
        $this->client()->server->get();
    }

    public function testMissingFieldInResponse(): void
    {
        $this->transport->queue(200, ['email' => 'commander@lspd.gov']);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('Field "name" is missing');
        $this->client()->users->get('commander@lspd.gov');
    }

    public function testRejectsEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MailRpClient(' ');
    }

    public function testPsr18Transport(): void
    {
        $factory = new Psr17Factory();
        $psrClient = new class implements ClientInterface {
            public ?RequestInterface $request = null;

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->request = $request;

                return new Response(201, ['Content-Type' => 'application/json', 'X-Request-Id' => 'req-psr'], '{"email":"alias@lspd.gov"}');
            }
        };
        $client = new MailRpClient('xls-mrp-test', transport: new Psr18Transport($psrClient, $factory, $factory));

        self::assertSame('alias@lspd.gov', $client->users->aliases->add('commander@lspd.gov', 'alias'));
        self::assertNotNull($psrClient->request);
        self::assertSame('POST', $psrClient->request->getMethod());
        self::assertSame('https://api.mail-rp.com/user/commander%40lspd.gov/alias/add', (string) $psrClient->request->getUri());
        self::assertSame('Bearer xls-mrp-test', $psrClient->request->getHeaderLine('Authorization'));
        self::assertSame('{"email":"alias"}', (string) $psrClient->request->getBody());
        self::assertSame('req-psr', $client->getLastResponse()?->requestId);
    }
}
