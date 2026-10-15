<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource;

use Xelyos\MailRp\Enum\Color;
use Xelyos\MailRp\Http\ApiRequester;
use Xelyos\MailRp\Model\Calendar;
use Xelyos\MailRp\Resource\Calendar\EventResource;
use Xelyos\MailRp\Resource\Calendar\InviteResource;

/**
 * Calendars, identified by their uuid (see `$client->users->calendars` to list or create the calendars of a user).
 */
final class CalendarResource extends AbstractResource
{
    public readonly EventResource $events;
    public readonly InviteResource $invites;

    public function __construct(ApiRequester $requester)
    {
        parent::__construct($requester);
        $this->events = new EventResource($requester);
        $this->invites = new InviteResource($requester);
    }

    /**
     * `calendar.read`.
     */
    public function get(string $calendarUuid): Calendar
    {
        return Calendar::fromArray($this->getJson(self::path('/calendar/%s', $calendarUuid)));
    }

    /**
     * `calendar.update`.
     *
     * @param array{name: string, color?: Color|string|null} $data
     *
     * @throws \JsonException
     */
    public function update(string $calendarUuid, array $data): Calendar
    {
        return Calendar::fromArray($this->sendJson('PUT', self::path('/calendar/%s', $calendarUuid), $data));
    }

    /**
     * Deletes a calendar and its events (`calendar.delete`).
     *
     * @throws \JsonException
     */
    public function delete(string $calendarUuid): void
    {
        $this->sendEmpty('DELETE', self::path('/calendar/%s', $calendarUuid));
    }

    /**
     * Gives the calendar to another user of the server (`calendar.update`).
     *
     * @throws \JsonException
     */
    public function transfer(string $calendarUuid, string $newOwnerEmail): Calendar
    {
        return Calendar::fromArray(
            $this->sendJson('POST', self::path('/calendar/%s/transfer', $calendarUuid), ['email' => $newOwnerEmail])
        );
    }
}
