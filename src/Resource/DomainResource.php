<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource;

use Xelyos\MailRp\Model\Domain;
use Xelyos\MailRp\Model\Newsletter;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Model\User;

/**
 * Domains of the server (limited to the domains of the key when it is not allowed on every domain).
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 *
 * @phpstan-type NewUser array{email: string, name: string, firstname: string, password: string, publicInformation?: array{title?: string|null, description?: string|null}|null}
 */
final class DomainResource extends AbstractResource
{
    /**
     * Searches the domains (`domain.search`). Search on the name or title; sort `name`, `title`, `public`, `activated`.
     *
     * @param SearchQuery $query
     *
     * @return Page<Domain>
     *
     * @throws \JsonException
     */
    public function search(array $query = []): Page
    {
        return Page::fromArray($this->getJson('/domain/search', $query), Domain::fromArray(...));
    }

    /**
     * Iterates over every domain matching the search, page after page.
     *
     * @param SearchQuery $query
     *
     * @return \Generator<int, Domain>
     *
     * @throws \JsonException
     */
    public function iterate(array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($q), $query);
    }

    /**
     * `domain.read`.
     */
    public function get(string $domainName): Domain
    {
        return Domain::fromArray($this->getJson(self::path('/domain/%s', $domainName)));
    }

    /**
     * Creates a domain (`domain.create`, needs a key allowed on every domain). A private domain needs a manager,
     * created at the same time.
     *
     * @param array{name: string, title?: string|null, public?: bool|null, addressBook?: bool|null, manager?: NewUser|null} $data
     *
     * @throws \JsonException
     */
    public function create(array $data): Domain
    {
        return Domain::fromArray($this->sendJson('POST', '/domain/add', $data));
    }

    /**
     * Partial update (`domain.update`): only the given fields change. `manager` is the address of an existing user
     * of the domain or a new user; making the domain public removes its manager and its address book.
     *
     * @param array{title?: string|null, public?: bool|null, addressBook?: bool|null, manager?: string|NewUser|null} $data
     *
     * @throws \JsonException
     */
    public function update(string $domainName, array $data): Domain
    {
        return Domain::fromArray($this->sendJson('PATCH', self::path('/domain/%s', $domainName), $data));
    }

    /**
     * Deletes a disabled domain with its users, mails, calendars and newsletters (`domain.delete`).
     */
    public function delete(string $domainName): void
    {
        $this->sendEmpty('DELETE', self::path('/domain/%s', $domainName));
    }

    /**
     * `domain.update`.
     */
    public function disable(string $domainName): Domain
    {
        return Domain::fromArray($this->sendJson('POST', self::path('/domain/%s/disable', $domainName)));
    }

    /**
     * `domain.update`.
     */
    public function enable(string $domainName): Domain
    {
        return Domain::fromArray($this->sendJson('POST', self::path('/domain/%s/enable', $domainName)));
    }

    /**
     * Users of a domain (`user.search`). Search on the email, name or firstname; sort `email`, `name`, `firstname`,
     * `fullName`, `activated`, `createdAt`.
     *
     * @param SearchQuery $query
     *
     * @return Page<User>
     *
     * @throws \JsonException
     */
    public function searchUsers(string $domainName, array $query = []): Page
    {
        return Page::fromArray($this->getJson(self::path('/domain/%s/user/search', $domainName), $query), User::fromArray(...));
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, User>
     *
     * @throws \JsonException
     */
    public function iterateUsers(string $domainName, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->searchUsers($domainName, $q), $query);
    }

    /**
     * Newsletters of a domain (`domain_newsletter.search`). Search on the name or code; sort `name`, `code`.
     *
     * @param SearchQuery $query
     *
     * @return Page<Newsletter>
     *
     * @throws \JsonException
     */
    public function searchNewsletters(string $domainName, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/domain/%s/newsletter/search', $domainName), $query),
            Newsletter::fromArray(...)
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, Newsletter>
     *
     * @throws \JsonException
     */
    public function iterateNewsletters(string $domainName, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->searchNewsletters($domainName, $q), $query);
    }

    /**
     * Creates a newsletter on a private domain (`domain_newsletter.create`).
     */
    public function createNewsletter(string $domainName, string $name): Newsletter
    {
        return Newsletter::fromArray(
            $this->sendJson('POST', self::path('/domain/%s/newsletter/add', $domainName), ['name' => $name])
        );
    }
}
