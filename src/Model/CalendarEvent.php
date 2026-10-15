<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class CalendarEvent
{
    public function __construct(
        public string $uuid,
        public string $title,
        public string $description,
        public string $location,
        public bool $allDay,
        /** `primary`, `success`, `warning`, `danger` or `info` (inherited from the calendar) */
        public string $color,
        /** Start, in the time zone of the server (format `Y-m-d H:i`) */
        public string $start,
        /** End, in the time zone of the server (format `Y-m-d H:i`) */
        public string $end,
        public ?CalendarSummary $calendar,
        /** Shared by every occurrence of a repeated event */
        public ?string $reference,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $calendar = Data::nullableArray($data, 'calendar');

        return new self(
            Data::string($data, 'uuid'),
            Data::string($data, 'title'),
            Data::nullableString($data, 'description') ?? '',
            Data::nullableString($data, 'location') ?? '',
            Data::bool($data, 'allDay'),
            Data::string($data, 'color'),
            Data::string($data, 'start'),
            Data::string($data, 'end'),
            null === $calendar ? null : CalendarSummary::fromArray($calendar),
            Data::nullableString($data, 'reference'),
        );
    }
}
