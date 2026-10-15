<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource;

use Xelyos\MailRp\Http\ApiRequester;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Model\User;
use Xelyos\MailRp\Resource\User\AliasResource;
use Xelyos\MailRp\Resource\User\CalendarResource;
use Xelyos\MailRp\Resource\User\ContactResource;
use Xelyos\MailRp\Resource\User\EventResource;
use Xelyos\MailRp\Resource\User\MailResource;
use Xelyos\MailRp\Resource\User\RedirectionResource;

/**
 * Users of the server, identified by their email address.
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 *
 * @phpstan-type PublicInformationInput array{title?: string|null, description?: string|null}
 */
final class UserResource extends AbstractResource
{
    public readonly AliasResource $aliases;
    public readonly CalendarResource $calendars;
    public readonly ContactResource $contacts;
    public readonly EventResource $events;
    public readonly MailResource $mails;
    public readonly RedirectionResource $redirections;

    public function __construct(ApiRequester $requester)
    {
        parent::__construct($requester);
        $this->aliases = new AliasResource($requester);
        $this->calendars = new CalendarResource($requester);
        $this->contacts = new ContactResource($requester);
        $this->events = new EventResource($requester);
        $this->mails = new MailResource($requester);
        $this->redirections = new RedirectionResource($requester);
    }

    /**
     * Searches the users of the server, limited to the domains of the key (`user.search`). Search on the email,
     * name, firstname or Discord account; sort `email`, `name`, `firstname`, `fullName`, `activated`, `createdAt`.
     *
     * @param SearchQuery $query
     *
     * @return Page<User>
     *
     * @throws \JsonException
     */
    public function search(array $query = []): Page
    {
        return Page::fromArray($this->getJson('/user/search', $query), User::fromArray(...));
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, User>
     *
     * @throws \JsonException
     */
    public function iterate(array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($q), $query);
    }

    /**
     * `user.read`.
     */
    public function get(string $email): User
    {
        return User::fromArray($this->getJson(self::path('/user/%s', $email)));
    }

    /**
     * Creates a user; the domain is taken from the address (`user.create`). With `invite: true` the password is
     * generated and {@see User::$inviteLink} lets the user choose their own.
     *
     * @param array{email: string, name: string, firstname: string, password?: string, invite?: bool, publicInformation?: PublicInformationInput|null} $data
     *
     * @throws \JsonException
     */
    public function create(array $data): User
    {
        return User::fromArray($this->sendJson('POST', '/user/add', $data));
    }

    /**
     * `user.update`.
     *
     * @param array{name: string, firstname: string, publicInformation?: PublicInformationInput|null} $data
     *
     * @throws \JsonException
     */
    public function update(string $email, array $data): User
    {
        return User::fromArray($this->sendJson('PUT', self::path('/user/%s', $email), $data));
    }

    /**
     * Deletes a disabled user with their mails, calendars and contacts (`user.delete`).
     *
     * @throws \JsonException
     */
    public function delete(string $email): void
    {
        $this->sendEmpty('DELETE', self::path('/user/%s', $email));
    }

    /**
     * `user.update`.
     *
     * @throws \JsonException
     */
    public function disable(string $email): User
    {
        return User::fromArray($this->sendJson('POST', self::path('/user/%s/disable', $email)));
    }

    /**
     * `user.update` (refused when the maximum number of users of the pack is reached).
     *
     * @throws \JsonException
     */
    public function enable(string $email): User
    {
        return User::fromArray($this->sendJson('POST', self::path('/user/%s/enable', $email)));
    }
}
