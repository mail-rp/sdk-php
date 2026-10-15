<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class Domain
{
    public function __construct(
        public string $name,
        public ?string $title,
        public bool $public,
        public bool $activated,
        public bool $addressBook,
        /** Logo URL */
        public ?string $logo,
        public int $usersCount,
        public int $newslettersCount,
        /** Manager of the domain (private domains only) */
        public ?User $manager,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $manager = Data::nullableArray($data, 'manager');

        return new self(
            Data::string($data, 'name'),
            Data::nullableString($data, 'title'),
            Data::bool($data, 'public'),
            Data::bool($data, 'activated'),
            Data::bool($data, 'addressBook'),
            Data::nullableString($data, 'logo'),
            Data::int($data, 'usersCount'),
            Data::int($data, 'newslettersCount'),
            null === $manager ? null : User::fromArray($manager),
            Data::date($data, 'createdAt'),
            Data::nullableDate($data, 'updatedAt'),
        );
    }
}
