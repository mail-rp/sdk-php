<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Tests;

/**
 * Response bodies shaped like the ones of the API (OpenAPI examples).
 */
final class Fixtures
{
    /** @return array<string, mixed> */
    public static function user(string $email = 'commander@lspd.gov'): array
    {
        return [
            'email' => $email,
            'name' => 'Foster',
            'firstname' => 'Dan',
            'activated' => true,
            'createdAt' => '2026-01-01T12:00:00+00:00',
            'updatedAt' => null,
            'avatar' => 'https://media.mail-rp.com/files/0199c8a1.webp',
            'publicInformation' => ['title' => 'Aros', 'description' => 'Mail RP Manager'],
        ];
    }

    /** @return array<string, mixed> */
    public static function domain(string $name = 'lspd.gov'): array
    {
        return [
            'name' => $name,
            'public' => false,
            'createdAt' => '2026-01-01T12:00:00+00:00',
            'updatedAt' => '2026-01-02T12:00:00+00:00',
            'activated' => true,
            'title' => 'LSPD',
            'logo' => null,
            'usersCount' => 153,
            'newslettersCount' => 7,
            'manager' => ['email' => 'chief@lspd.gov', 'name' => 'Doe', 'firstname' => 'John', 'activated' => true, 'avatar' => null],
            'addressBook' => true,
        ];
    }

    /** @return array<string, mixed> */
    public static function calendar(): array
    {
        return [
            'uuid' => '0199c8a1-6f3e-7a52-9d4b-2f8e1c0a7b31',
            'name' => 'Patrouilles',
            'color' => 'primary',
            'createdAt' => '2026-01-01T12:00:00+00:00',
            'updatedAt' => null,
            'user' => ['email' => 'commander@lspd.gov', 'name' => 'Foster', 'firstname' => 'Dan'],
        ];
    }

    /** @return array<string, mixed> */
    public static function event(): array
    {
        return [
            'uuid' => '0199c8a1-0000-7a52-9d4b-2f8e1c0a7b31',
            'title' => 'Briefing',
            'description' => 'Point du matin',
            'location' => 'Salle A',
            'allDay' => false,
            'calendar' => ['uuid' => '0199c8a1-6f3e-7a52-9d4b-2f8e1c0a7b31', 'name' => 'Patrouilles'],
            'reference' => null,
            'color' => 'primary',
            'start' => '2026-10-14 09:00',
            'end' => '2026-10-14 10:00',
        ];
    }

    /** @return array<string, mixed> */
    public static function newsletter(bool $withEmails = false): array
    {
        return ['code' => 'lspd-news', 'name' => 'LSPD News', 'domain' => 'lspd.gov'] + ($withEmails ? ['emails' => ['chief@lspd.gov']] : []);
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return array{total: int, rows: list<array<string, mixed>>}
     */
    public static function page(array $rows, ?int $total = null): array
    {
        return ['total' => $total ?? count($rows), 'rows' => $rows];
    }
}
