<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\Newsletter;

use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Model\User;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Users allowed to send mails to the subscribers of a newsletter.
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class SenderResource extends AbstractResource
{
    /**
     * `domain_newsletter.read`; search on the address, name or firstname, sort `email`.
     *
     * @param SearchQuery $query
     *
     * @return Page<User>
     */
    public function search(string $newsletterCode, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/newsletter/%s/sender/search', $newsletterCode), $query),
            User::fromArray(...)
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, User>
     */
    public function iterate(string $newsletterCode, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($newsletterCode, $q), $query);
    }

    /**
     * `domain_newsletter.update`.
     */
    public function add(string $newsletterCode, string $email): User
    {
        return User::fromArray(
            $this->sendJson('POST', self::path('/newsletter/%s/sender/add', $newsletterCode), ['email' => $email])
        );
    }

    /**
     * `domain_newsletter.update`.
     */
    public function remove(string $newsletterCode, string $email): void
    {
        $this->sendEmpty('DELETE', self::path('/newsletter/%s/sender/%s', $newsletterCode, $email));
    }
}
