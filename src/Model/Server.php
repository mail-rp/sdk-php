<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class Server
{
    public function __construct(
        public string $name,
        /** `online` or `coming_soon` */
        public string $status,
        public ?string $discordId,
        public string $url,
        public ?string $logo,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        /** Number of activated domains */
        public int $domainCount,
        public int $userCount,
        public int $mailCount,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'name'),
            Data::string($data, 'status'),
            Data::nullableString($data, 'discordId'),
            Data::string($data, 'url'),
            Data::nullableString($data, 'logo'),
            Data::date($data, 'createdAt'),
            Data::nullableDate($data, 'updatedAt'),
            Data::int(Data::array($data, 'domains'), 'total'),
            Data::int(Data::array($data, 'users'), 'total'),
            Data::int(Data::array($data, 'mails'), 'total'),
        );
    }
}
