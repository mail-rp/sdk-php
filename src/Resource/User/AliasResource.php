<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\User;

use Xelyos\MailRp\Model\Data;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Aliases of a user: other addresses of their domain they receive and send mails with.
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class AliasResource extends AbstractResource
{
    /**
     * `user_alias.search`; search on the address, sort `email`.
     *
     * @param SearchQuery $query
     *
     * @return Page<string> alias addresses
     */
    public function search(string $email, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/user/%s/alias/search', $email), $query),
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
     * Adds an alias (`user_alias.create`, private domain, within the alias quota of the pack).
     *
     * @param string $alias full address, or local part completed with the domain of the user
     *
     * @return string the full alias address
     */
    public function add(string $email, string $alias): string
    {
        return Data::string($this->sendJson('POST', self::path('/user/%s/alias/add', $email), ['email' => $alias]), 'email');
    }

    /**
     * `user_alias.delete`.
     */
    public function remove(string $email, string $alias): void
    {
        $this->sendEmpty('DELETE', self::path('/user/%s/alias/%s', $email, $alias));
    }
}
