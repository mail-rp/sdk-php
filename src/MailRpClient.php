<?php

declare(strict_types=1);

namespace Xelyos\MailRp;

use Xelyos\MailRp\Exception\InvalidArgumentException;
use Xelyos\MailRp\Http\ApiRequester;
use Xelyos\MailRp\Http\CurlTransport;
use Xelyos\MailRp\Http\Transport;
use Xelyos\MailRp\Resource\CalendarResource;
use Xelyos\MailRp\Resource\DomainResource;
use Xelyos\MailRp\Resource\NewsletterResource;
use Xelyos\MailRp\Resource\ServerResource;
use Xelyos\MailRp\Resource\SystemResource;
use Xelyos\MailRp\Resource\UserResource;

/**
 * Entry point of the SDK.
 *
 * ```php
 * $mailRp = new MailRpClient('xls-mrp-...');
 * $user = $mailRp->users->get('aros@lspd.gov');
 * ```
 */
final class MailRpClient
{
    public const VERSION = '1.0.2-dev';
    public const DEFAULT_BASE_URL = 'https://api.mail-rp.com';

    public readonly SystemResource $system;
    public readonly ServerResource $server;
    public readonly DomainResource $domains;
    public readonly UserResource $users;
    public readonly CalendarResource $calendars;
    public readonly NewsletterResource $newsletters;

    private readonly ApiRequester $requester;

    /**
     * @param string|null                $apiKey        key created in the panel (`xls-mrp-...`); without it, only the
     *                                                  public routes (`system->health()`) can be called
     * @param string                     $baseUrl       URL of the API
     * @param float                      $timeout       seconds before a request is abandoned (ignored by {@see Http\Psr18Transport})
     * @param int                        $maxRetries    automatic retries of a request refused with 429 (0 to disable)
     * @param int                        $maxRetryDelay longest `Retry-After` (seconds) worth waiting for; above it the
     *                                                  {@see Exception\RateLimitException} is thrown at once
     * @param Transport|null             $transport     HTTP layer, {@see CurlTransport} by default
     * @param (\Closure(int): void)|null $sleep         waits the given seconds between two retries (`sleep()` by default)
     */
    public function __construct(
        ?string $apiKey = null,
        string $baseUrl = self::DEFAULT_BASE_URL,
        float $timeout = 30.0,
        int $maxRetries = 2,
        int $maxRetryDelay = 60,
        ?Transport $transport = null,
        ?\Closure $sleep = null,
    ) {
        if ($timeout <= 0 || $maxRetries < 0 || $maxRetryDelay < 0) {
            throw new InvalidArgumentException('timeout must be positive, maxRetries and maxRetryDelay cannot be negative');
        }

        $this->requester = new ApiRequester(
            null === $apiKey || '' === trim($apiKey) ? null : trim($apiKey),
            $baseUrl,
            $transport ?? new CurlTransport(),
            $timeout,
            $maxRetries,
            $maxRetryDelay,
            $sleep,
        );

        $this->system = new SystemResource($this->requester);
        $this->server = new ServerResource($this->requester);
        $this->domains = new DomainResource($this->requester);
        $this->users = new UserResource($this->requester);
        $this->calendars = new CalendarResource($this->requester);
        $this->newsletters = new NewsletterResource($this->requester);
    }

    /**
     * Whether an API key is set on this client (a blank key counts as missing). Without a key, the requests are sent
     * without authentication and the API answers `401` ({@see Exception\AuthenticationException}) except on the public routes.
     */
    public function hasApiKey(): bool
    {
        return $this->requester->hasApiKey();
    }

    /**
     * Request id and rate limit state of the last response (success or error).
     */
    public function getLastResponse(): ?ResponseMeta
    {
        return $this->requester->getLastResponse();
    }
}
