<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * Short form of a user (owner of a calendar, member of a calendar).
 */
final readonly class UserSummary
{
    public function __construct(
        public string $email,
        public string $name,
        public string $firstname,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(Data::string($data, 'email'), Data::string($data, 'name'), Data::string($data, 'firstname'));
    }
}
