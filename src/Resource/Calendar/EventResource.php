<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\Calendar;

use Xelyos\MailRp\Enum\EventIcon;
use Xelyos\MailRp\Enum\RepeatFrequency;
use Xelyos\MailRp\Model\CalendarEvent;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Events of a calendar. Dates are read in the time zone of the server, as `Y-m-d H:i` (a `DateTimeInterface` is
 * formatted that way, in its own time zone).
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 *
 * @phpstan-type EventInput array{title: string, start: \DateTimeInterface|string, end: \DateTimeInterface|string, description?: string|null, location?: string|null, icon?: EventIcon|string|null, allDay?: bool, requiredParticipants?: list<string>, optionalParticipants?: list<string>}
 * @phpstan-type Repeat array{start: \DateTimeInterface|string, end: \DateTimeInterface|string, frequency?: RepeatFrequency|string, monday?: bool, tuesday?: bool, wednesday?: bool, thursday?: bool, friday?: bool, saturday?: bool, sunday?: bool}
 */
final class EventResource extends AbstractResource
{
    /**
     * Events of the calendar in a period (`calendar_event.search`). Search on the uuid, reference or title; sort
     * `start`, `end`, `title`.
     *
     * @param \DateTimeInterface|string $start date (`Y-m-d`) or date and time (`Y-m-d H:i`)
     * @param \DateTimeInterface|string $end   date (`Y-m-d`) or date and time (`Y-m-d H:i`)
     * @param SearchQuery               $query
     *
     * @return Page<CalendarEvent>
     */
    public function search(string $calendarUuid, \DateTimeInterface|string $start, \DateTimeInterface|string $end, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/calendar/%s/event/search', $calendarUuid), ['start' => $start, 'end' => $end] + $query),
            CalendarEvent::fromArray(...)
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, CalendarEvent>
     */
    public function iterate(string $calendarUuid, \DateTimeInterface|string $start, \DateTimeInterface|string $end, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($calendarUuid, $start, $end, $q), $query);
    }

    /**
     * `calendar_event.read`.
     */
    public function get(string $calendarUuid, string $eventUuid): CalendarEvent
    {
        return CalendarEvent::fromArray($this->getJson(self::path('/calendar/%s/event/%s', $calendarUuid, $eventUuid)));
    }

    /**
     * Creates an event (`calendar_event.create`). Participants are invited by mail; addresses that are not users of
     * the server (or outside the key domains) are ignored. With `repeat` (weekly: at least one day; at most 366
     * occurrences) the first occurrence is returned.
     *
     * @param EventInput&array{repeat?: Repeat|null} $event
     */
    public function create(string $calendarUuid, array $event): CalendarEvent
    {
        return CalendarEvent::fromArray(
            $this->sendJson('POST', self::path('/calendar/%s/event/add', $calendarUuid), $event)
        );
    }

    /**
     * Replaces an event (`calendar_event.update`). For a repeated event, `applyAll` changes every occurrence and
     * `applyAllNext` this one and the following ones (not both).
     *
     * @param EventInput&array{applyAll?: bool, applyAllNext?: bool} $event
     */
    public function update(string $calendarUuid, string $eventUuid, array $event): CalendarEvent
    {
        return CalendarEvent::fromArray(
            $this->sendJson('PUT', self::path('/calendar/%s/event/%s', $calendarUuid, $eventUuid), $event)
        );
    }

    /**
     * `calendar_event.delete`. For a repeated event, `$applyAll` deletes every occurrence and `$applyAllNext` this
     * one and the following ones (not both).
     */
    public function delete(string $calendarUuid, string $eventUuid, bool $applyAll = false, bool $applyAllNext = false): void
    {
        $this->sendEmpty(
            'DELETE',
            self::path('/calendar/%s/event/%s', $calendarUuid, $eventUuid),
            ['applyAll' => $applyAll ?: null, 'applyAllNext' => $applyAllNext ?: null]
        );
    }
}
