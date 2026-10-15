<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\User;

use Xelyos\MailRp\Model\Contact;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Contacts of a user.
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class ContactResource extends AbstractResource
{
    /**
     * `user_contact.read`; search on the name or address, sort `name`, `email`.
     *
     * @param SearchQuery $query
     *
     * @return Page<Contact>
     */
    public function search(string $email, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/user/%s/contact/search', $email), $query),
            Contact::fromArray(...)
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, Contact>
     */
    public function iterate(string $email, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($email, $q), $query);
    }

    /**
     * Adds a contact (`user_contact.create`); refused if the address is already a contact of the user.
     *
     * @param array{name: string, email: string} $contact
     */
    public function create(string $email, array $contact): Contact
    {
        return Contact::fromArray($this->sendJson('POST', self::path('/user/%s/contact/add', $email), $contact));
    }

    /**
     * Renames a contact and/or changes its address (`user_contact.update`).
     *
     * @param string                             $contactEmail current address of the contact
     * @param array{name: string, email: string} $contact
     */
    public function update(string $email, string $contactEmail, array $contact): Contact
    {
        return Contact::fromArray(
            $this->sendJson('PUT', self::path('/user/%s/contact/%s', $email, $contactEmail), $contact)
        );
    }

    /**
     * `user_contact.delete`.
     */
    public function delete(string $email, string $contactEmail): void
    {
        $this->sendEmpty('DELETE', self::path('/user/%s/contact/%s', $email, $contactEmail));
    }
}
