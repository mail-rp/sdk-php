<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\User;

use Xelyos\MailRp\Model\CalendarEvent;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Events of a user (every calendar they can see, and the events they take part in).
 *
 * @phpstan-type EventSearchQuery array{search?: string|null, limit?: int|null, offset?: int|null, sort?: string|null, order?: \Xelyos\MailRp\Enum\SortOrder|string|null, calendar?: string|null}
 */
final class EventResource extends AbstractResource
{
    /**
     * Events in a period (`calendar_event.search`). Search on the uuid, reference or title; sort `start`, `end`,
     * `title` (`start` ascending by default).
     *
     * @param \DateTimeInterface|string $start date (`Y-m-d`) or date and time (`Y-m-d H:i`)
     * @param \DateTimeInterface|string $end   date (`Y-m-d`) or date and time (`Y-m-d H:i`)
     * @param EventSearchQuery          $query `calendar`: only the events of this calendar (uuid)
     *
     * @return Page<CalendarEvent>
     */
    public function search(string $email, \DateTimeInterface|string $start, \DateTimeInterface|string $end, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/user/%s/event/search', $email), ['start' => $start, 'end' => $end] + $query),
            CalendarEvent::fromArray(...)
        );
    }

    /**
     * @param EventSearchQuery $query
     *
     * @return \Generator<int, CalendarEvent>
     */
    public function iterate(string $email, \DateTimeInterface|string $start, \DateTimeInterface|string $end, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($email, $start, $end, $q), $query);
    }
}
