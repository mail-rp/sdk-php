<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\Calendar;

use Xelyos\MailRp\Model\CalendarInvite;
use Xelyos\MailRp\Model\InviteResult;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Members of a calendar (users the calendar is shared with).
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class InviteResource extends AbstractResource
{
    /**
     * `calendar.read`; search on the address, name or firstname, sort `email`.
     *
     * @param SearchQuery $query
     *
     * @return Page<CalendarInvite>
     */
    public function search(string $calendarUuid, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/calendar/%s/invite/search', $calendarUuid), $query),
            CalendarInvite::fromArray(...)
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, CalendarInvite>
     */
    public function iterate(string $calendarUuid, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($calendarUuid, $q), $query);
    }

    /**
     * Shares the calendar (`calendar.update`). Unknown or disabled users, the owner and users outside the key domains
     * are ignored (see {@see InviteResult::$ignored}).
     *
     * @param list<string> $emails
     * @param bool         $manager whether the new members can manage the calendar
     */
    public function add(string $calendarUuid, array $emails, bool $manager = false): InviteResult
    {
        return InviteResult::fromArray($this->sendJson(
            'POST',
            self::path('/calendar/%s/invite/add', $calendarUuid),
            ['users' => $emails, 'manager' => $manager]
        ));
    }

    /**
     * Changes the role of a member (`calendar.update`).
     */
    public function update(string $calendarUuid, string $userEmail, bool $manager): CalendarInvite
    {
        return CalendarInvite::fromArray($this->sendJson(
            'PUT',
            self::path('/calendar/%s/invite/%s', $calendarUuid, $userEmail),
            ['manager' => $manager]
        ));
    }

    /**
     * `calendar.update`.
     */
    public function remove(string $calendarUuid, string $userEmail): void
    {
        $this->sendEmpty('DELETE', self::path('/calendar/%s/invite/%s', $calendarUuid, $userEmail));
    }
}
