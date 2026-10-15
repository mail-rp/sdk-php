<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\User;

use Xelyos\MailRp\Enum\Color;
use Xelyos\MailRp\Model\Calendar;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Calendars of a user (own calendars and calendars shared with them).
 *
 * @phpstan-import-type SearchQuery from AbstractResource
 */
final class CalendarResource extends AbstractResource
{
    /**
     * `calendar.search`; search on the name, sort `name`, `createdAt`. Rows only carry `uuid`, `name` and `color`.
     *
     * @param SearchQuery $query
     *
     * @return Page<Calendar>
     */
    public function search(string $email, array $query = []): Page
    {
        return Page::fromArray(
            $this->getJson(self::path('/user/%s/calendar/search', $email), $query),
            Calendar::fromArray(...)
        );
    }

    /**
     * @param SearchQuery $query
     *
     * @return \Generator<int, Calendar>
     */
    public function iterate(string $email, array $query = []): \Generator
    {
        return self::paginate(fn (array $q): Page => $this->search($email, $q), $query);
    }

    /**
     * Creates a calendar owned by the user (`calendar.create`).
     *
     * @param array{name: string, color?: Color|string|null} $data
     */
    public function create(string $email, array $data): Calendar
    {
        return Calendar::fromArray($this->sendJson('POST', self::path('/user/%s/calendar/add', $email), $data));
    }
}
