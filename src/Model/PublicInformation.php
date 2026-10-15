<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * Public information of a user (title and description shown in the directory).
 */
final readonly class PublicInformation
{
    public function __construct(
        public ?string $title,
        public ?string $description,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(Data::nullableString($data, 'title'), Data::nullableString($data, 'description'));
    }
}
