<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class User
{
    public function __construct(
        public string $email,
        public string $name,
        public string $firstname,
        public bool $activated,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        /** Avatar URL */
        public ?string $avatar,
        public ?PublicInformation $publicInformation,
        /** Link to choose a password, only right after a creation with `invite: true` */
        public ?string $inviteLink = null,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $publicInformation = Data::nullableArray($data, 'publicInformation');

        return new self(
            Data::string($data, 'email'),
            Data::string($data, 'name'),
            Data::string($data, 'firstname'),
            Data::bool($data, 'activated'),
            Data::nullableDate($data, 'createdAt'),
            Data::nullableDate($data, 'updatedAt'),
            Data::nullableString($data, 'avatar'),
            null === $publicInformation ? null : PublicInformation::fromArray($publicInformation),
            Data::nullableString($data, 'inviteLink'),
        );
    }

    public function getFullName(): string
    {
        return trim($this->firstname . ' ' . $this->name);
    }
}
