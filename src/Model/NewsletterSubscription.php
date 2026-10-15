<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class NewsletterSubscription
{
    public function __construct(
        public string $email,
        public ?\DateTimeImmutable $createdAt,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(Data::string($data, 'email'), Data::nullableDate($data, 'createdAt'));
    }
}
