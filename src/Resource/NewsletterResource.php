<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource;

use Xelyos\MailRp\Http\ApiRequester;
use Xelyos\MailRp\Model\Newsletter;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\Newsletter\SenderResource;
use Xelyos\MailRp\Resource\Newsletter\SubscriptionResource;

/**
 * Newsletters of the server, identified by their code (limited to the domains of the key).
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class NewsletterResource extends AbstractResource
{
    public readonly SenderResource $senders;
    public readonly SubscriptionResource $subscriptions;

    public function __construct(ApiRequester $requester)
    {
        parent::__construct($requester);
        $this->senders = new SenderResource($requester);
        $this->subscriptions = new SubscriptionResource($requester);
    }

    /**
     * `domain_newsletter.search`; search on the name, code or domain, sort `name`, `code`.
     *
     * @param SearchQuery $query
     *
     * @return Page<Newsletter>
     *
     * @throws \JsonException
     */
    public function search(array $query = []): Page
    {
        return Page::fromArray($this->getJson('/newsletter/search', $query), Newsletter::fromArray(...));
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, Newsletter>
     *
     * @throws \JsonException
     */
    public function iterate(array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($q), $query);
    }

    /**
     * `domain_newsletter.read`.
     */
    public function get(string $newsletterCode): Newsletter
    {
        return Newsletter::fromArray($this->getJson(self::path('/newsletter/%s', $newsletterCode)));
    }

    /**
     * Creates a newsletter on a private domain (`domain_newsletter.create`).
     */
    public function create(string $domainName, string $name): Newsletter
    {
        return Newsletter::fromArray($this->sendJson('POST', '/newsletter/add', ['domain' => $domainName, 'name' => $name]));
    }

    /**
     * Renames a newsletter (`domain_newsletter.update`).
     */
    public function update(string $newsletterCode, string $name): Newsletter
    {
        return Newsletter::fromArray($this->sendJson('PUT', self::path('/newsletter/%s', $newsletterCode), ['name' => $name]));
    }

    /**
     * `domain_newsletter.delete`.
     */
    public function delete(string $newsletterCode): void
    {
        $this->sendEmpty('DELETE', self::path('/newsletter/%s', $newsletterCode));
    }
}
