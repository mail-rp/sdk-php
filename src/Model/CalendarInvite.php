<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * Member of a calendar.
 */
final readonly class CalendarInvite
{
    public function __construct(
        public UserSummary $user,
        /** Whether the member can manage the calendar */
        public bool $manager,
        /** Whether the member accepted the invitation */
        public bool $accepted,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            UserSummary::fromArray(Data::array($data, 'user')),
            Data::bool($data, 'manager'),
            Data::bool($data, 'accepted'),
        );
    }
}
