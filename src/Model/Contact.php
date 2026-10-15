<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class Contact
{
    public function __construct(
        public string $name,
        public string $email,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(Data::string($data, 'name'), Data::string($data, 'email'));
    }
}
