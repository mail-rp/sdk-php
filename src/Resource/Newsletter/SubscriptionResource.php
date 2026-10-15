<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\Newsletter;

use Xelyos\MailRp\Model\NewsletterSubscription;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Subscribers of a newsletter.
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class SubscriptionResource extends AbstractResource
{
    /**
     * `domain_newsletter_subscription.read`; search on the address, sort `email`.
     *
     * @param SearchQuery $query
     *
     * @return Page<NewsletterSubscription>
     */
    public function search(string $newsletterCode, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/newsletter/%s/subscription/search', $newsletterCode), $query),
            NewsletterSubscription::fromArray(...)
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, NewsletterSubscription>
     */
    public function iterate(string $newsletterCode, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($newsletterCode, $q), $query);
    }

    /**
     * `domain_newsletter_subscription.create`.
     */
    public function add(string $newsletterCode, string $email): NewsletterSubscription
    {
        return NewsletterSubscription::fromArray(
            $this->sendJson('POST', self::path('/newsletter/%s/subscription/add', $newsletterCode), ['email' => $email])
        );
    }

    /**
     * `domain_newsletter_subscription.delete`.
     */
    public function remove(string $newsletterCode, string $email): void
    {
        $this->sendEmpty('DELETE', self::path('/newsletter/%s/subscription/%s', $newsletterCode, $email));
    }
}
