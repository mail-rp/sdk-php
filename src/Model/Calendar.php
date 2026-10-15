<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * A calendar. In search results only `uuid`, `name` and `color` are filled.
 */
final readonly class Calendar
{
    public function __construct(
        public string $uuid,
        public string $name,
        /** `primary`, `success`, `warning`, `danger` or `info` */
        public string $color,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        /** Owner of the calendar */
        public ?UserSummary $user = null,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $user = Data::nullableArray($data, 'user');

        return new self(
            Data::string($data, 'uuid'),
            Data::string($data, 'name'),
            Data::string($data, 'color'),
            Data::nullableDate($data, 'createdAt'),
            Data::nullableDate($data, 'updatedAt'),
            null === $user ? null : UserSummary::fromArray($user),
        );
    }
}
