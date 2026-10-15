<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * Result of a calendar invitation.
 */
final readonly class InviteResult
{
    /**
     * @param list<string> $invited addresses invited
     * @param list<string> $ignored addresses ignored (unknown, disabled, owner of the calendar, outside the key domains)
     */
    public function __construct(
        public array $invited,
        public array $ignored,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(Data::stringList($data, 'invited'), Data::stringList($data, 'ignored'));
    }
}
