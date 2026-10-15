<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\User;

use Xelyos\MailRp\Model\Data;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Redirections of a user: addresses that receive a copy of every mail the user receives.
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class RedirectionResource extends AbstractResource
{
    /**
     * `user_redirection.search`; search on the address, sort `email`.
     *
     * @param SearchQuery $query
     *
     * @return Page<string> redirection addresses
     */
    public function search(string $email, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/user/%s/redirection/search', $email), $query),
            static fn (array $row): string => Data::string($row, 'email')
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, string>
     */
    public function iterate(string $email, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($email, $q), $query);
    }

    /**
     * `user_redirection.create` (private domain, enabled user).
     *
     * @return string the redirection address
     */
    public function add(string $email, string $redirectionEmail): string
    {
        return Data::string(
            $this->sendJson('POST', self::path('/user/%s/redirection/add', $email), ['email' => $redirectionEmail]),
            'email'
        );
    }

    /**
     * `user_redirection.delete`.
     */
    public function remove(string $email, string $redirectionEmail): void
    {
        $this->sendEmpty('DELETE', self::path('/user/%s/redirection/%s', $email, $redirectionEmail));
    }

    /**
     * Removes every redirection of the user (`user_redirection.delete`).
     */
    public function removeAll(string $email): void
    {
        $this->sendEmpty('DELETE', self::path('/user/%s/redirection/all', $email));
    }
}
