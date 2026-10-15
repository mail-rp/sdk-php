<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class Health
{
    public function __construct(
        public string $status,
        public string $version,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(Data::string($data, 'status'), Data::string($data, 'version'));
    }
}
