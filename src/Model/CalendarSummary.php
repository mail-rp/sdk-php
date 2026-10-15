<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * Calendar an event belongs to.
 */
final readonly class CalendarSummary
{
    public function __construct(
        public string $uuid,
        public string $name,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(Data::string($data, 'uuid'), Data::string($data, 'name'));
    }
}
