<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xelyos\MailRp\Enum\Color;
use Xelyos\MailRp\Enum\EventIcon;
use Xelyos\MailRp\Enum\RepeatFrequency;
use Xelyos\MailRp\MailRpClient;
use Xelyos\MailRp\Model\Calendar;
use Xelyos\MailRp\Model\CalendarEvent;
use Xelyos\MailRp\Model\CalendarInvite;
use Xelyos\MailRp\Model\Contact;
use Xelyos\MailRp\Model\Domain;
use Xelyos\MailRp\Model\Health;
use Xelyos\MailRp\Model\InviteResult;
use Xelyos\MailRp\Model\Newsletter;
use Xelyos\MailRp\Model\NewsletterSubscription;
use Xelyos\MailRp\Model\Page;
use Xelyos\MailRp\Model\Server;
use Xelyos\MailRp\Model\User;

/**
 * Every endpoint: method, path, query and body sent, type returned.
 */
final class ResourcesTest extends TestCase
{
    private const CAL = '0199c8a1-6f3e-7a52-9d4b-2f8e1c0a7b31';
    private const EVT = '0199c8a1-0000-7a52-9d4b-2f8e1c0a7b31';

    /**
     * @return iterable<string, array{\Closure(MailRpClient): mixed, int, array<mixed>|null, string, string, array<mixed>|null, class-string|string|null}>
     */
    public static function endpoints(): iterable
    {
        $user = Fixtures::user();
        $domain = Fixtures::domain();
        $calendar = Fixtures::calendar();
        $event = Fixtures::event();
        $newsletter = Fixtures::newsletter();
        $member = ['user' => ['email' => 'a@lspd.gov', 'name' => 'A', 'firstname' => 'B'], 'manager' => true, 'accepted' => false];

        // System / server
        yield 'health' => [static fn (MailRpClient $c) => $c->system->health(), 200, ['status' => 'online', 'version' => '2.0.0'], 'GET', '/health', null, Health::class];
        yield 'server' => [static fn (MailRpClient $c) => $c->server->get(), 200, [
            'name' => 'Xelyos', 'status' => 'online', 'discordId' => null, 'url' => 'https://xelyos.mail-rp.com', 'logo' => null,
            'createdAt' => '2026-01-01T12:00:00+00:00', 'updatedAt' => null,
            'domains' => ['total' => 3], 'users' => ['total' => 40], 'mails' => ['total' => 1200],
        ], 'GET', '/server', null, Server::class];

        // Domains
        yield 'domain search' => [static fn (MailRpClient $c) => $c->domains->search(['search' => 'lspd', 'sort' => 'name']), 200, Fixtures::page([$domain]), 'GET', '/domain/search?search=lspd&sort=name', null, Page::class];
        yield 'domain get' => [static fn (MailRpClient $c) => $c->domains->get('lspd.gov'), 200, $domain, 'GET', '/domain/lspd.gov', null, Domain::class];
        yield 'domain create' => [static fn (MailRpClient $c) => $c->domains->create(['name' => 'lspd.gov', 'public' => false, 'manager' => ['email' => 'chief@lspd.gov', 'name' => 'Doe', 'firstname' => 'John', 'password' => 'secret123']]), 201, $domain, 'POST', '/domain/add', ['name' => 'lspd.gov', 'public' => false, 'manager' => ['email' => 'chief@lspd.gov', 'name' => 'Doe', 'firstname' => 'John', 'password' => 'secret123']], Domain::class];
        yield 'domain update' => [static fn (MailRpClient $c) => $c->domains->update('lspd.gov', ['title' => 'LSPD', 'manager' => 'chief@lspd.gov']), 200, $domain, 'PATCH', '/domain/lspd.gov', ['title' => 'LSPD', 'manager' => 'chief@lspd.gov'], Domain::class];
        yield 'domain delete' => [static fn (MailRpClient $c) => $c->domains->delete('lspd.gov'), 204, null, 'DELETE', '/domain/lspd.gov', null, null];
        yield 'domain disable' => [static fn (MailRpClient $c) => $c->domains->disable('lspd.gov'), 200, $domain, 'POST', '/domain/lspd.gov/disable', null, Domain::class];
        yield 'domain enable' => [static fn (MailRpClient $c) => $c->domains->enable('lspd.gov'), 200, $domain, 'POST', '/domain/lspd.gov/enable', null, Domain::class];
        yield 'domain users' => [static fn (MailRpClient $c) => $c->domains->searchUsers('lspd.gov', ['limit' => 20]), 200, Fixtures::page([$user]), 'GET', '/domain/lspd.gov/user/search?limit=20', null, Page::class];
        yield 'domain newsletters' => [static fn (MailRpClient $c) => $c->domains->searchNewsletters('lspd.gov'), 200, Fixtures::page([$newsletter]), 'GET', '/domain/lspd.gov/newsletter/search', null, Page::class];
        yield 'domain newsletter create' => [static fn (MailRpClient $c) => $c->domains->createNewsletter('lspd.gov', 'News'), 201, Fixtures::newsletter(true), 'POST', '/domain/lspd.gov/newsletter/add', ['name' => 'News'], Newsletter::class];

        // Users
        yield 'user search' => [static fn (MailRpClient $c) => $c->users->search(['search' => 'dan']), 200, Fixtures::page([$user]), 'GET', '/user/search?search=dan', null, Page::class];
        yield 'user get' => [static fn (MailRpClient $c) => $c->users->get('commander@lspd.gov'), 200, $user, 'GET', '/user/commander%40lspd.gov', null, User::class];
        yield 'user create' => [static fn (MailRpClient $c) => $c->users->create(['email' => 'commander@lspd.gov', 'name' => 'Foster', 'firstname' => 'Dan', 'invite' => true]), 201, $user + ['inviteLink' => 'https://x/invite'], 'POST', '/user/add', ['email' => 'commander@lspd.gov', 'name' => 'Foster', 'firstname' => 'Dan', 'invite' => true], User::class];
        yield 'user update' => [static fn (MailRpClient $c) => $c->users->update('commander@lspd.gov', ['name' => 'F', 'firstname' => 'D', 'publicInformation' => ['title' => 'Chef']]), 200, $user, 'PUT', '/user/commander%40lspd.gov', ['name' => 'F', 'firstname' => 'D', 'publicInformation' => ['title' => 'Chef']], User::class];
        yield 'user delete' => [static fn (MailRpClient $c) => $c->users->delete('commander@lspd.gov'), 204, null, 'DELETE', '/user/commander%40lspd.gov', null, null];
        yield 'user disable' => [static fn (MailRpClient $c) => $c->users->disable('commander@lspd.gov'), 200, $user, 'POST', '/user/commander%40lspd.gov/disable', null, User::class];
        yield 'user enable' => [static fn (MailRpClient $c) => $c->users->enable('commander@lspd.gov'), 200, $user, 'POST', '/user/commander%40lspd.gov/enable', null, User::class];

        yield 'alias search' => [static fn (MailRpClient $c) => $c->users->aliases->search('commander@lspd.gov'), 200, Fixtures::page([['email' => 'd@lspd.gov']]), 'GET', '/user/commander%40lspd.gov/alias/search', null, Page::class];
        yield 'alias add' => [static fn (MailRpClient $c) => $c->users->aliases->add('commander@lspd.gov', 'd'), 201, ['email' => 'd@lspd.gov'], 'POST', '/user/commander%40lspd.gov/alias/add', ['email' => 'd'], 'string'];
        yield 'alias remove' => [static fn (MailRpClient $c) => $c->users->aliases->remove('commander@lspd.gov', 'd@lspd.gov'), 204, null, 'DELETE', '/user/commander%40lspd.gov/alias/d%40lspd.gov', null, null];

        yield 'user calendars' => [static fn (MailRpClient $c) => $c->users->calendars->search('commander@lspd.gov'), 200, Fixtures::page([['uuid' => self::CAL, 'name' => 'Perso', 'color' => 'info']]), 'GET', '/user/commander%40lspd.gov/calendar/search', null, Page::class];
        yield 'user calendar create' => [static fn (MailRpClient $c) => $c->users->calendars->create('commander@lspd.gov', ['name' => 'Perso', 'color' => Color::INFO]), 201, $calendar, 'POST', '/user/commander%40lspd.gov/calendar/add', ['name' => 'Perso', 'color' => 'info'], Calendar::class];

        yield 'contact search' => [static fn (MailRpClient $c) => $c->users->contacts->search('commander@lspd.gov'), 200, Fixtures::page([['name' => 'Bob', 'email' => 'bob@lspd.gov']]), 'GET', '/user/commander%40lspd.gov/contact/search', null, Page::class];
        yield 'contact create' => [static fn (MailRpClient $c) => $c->users->contacts->create('commander@lspd.gov', ['name' => 'Bob', 'email' => 'bob@lspd.gov']), 201, ['name' => 'Bob', 'email' => 'bob@lspd.gov'], 'POST', '/user/commander%40lspd.gov/contact/add', ['name' => 'Bob', 'email' => 'bob@lspd.gov'], Contact::class];
        yield 'contact update' => [static fn (MailRpClient $c) => $c->users->contacts->update('commander@lspd.gov', 'bob@lspd.gov', ['name' => 'Bobby', 'email' => 'bob@lspd.gov']), 200, ['name' => 'Bobby', 'email' => 'bob@lspd.gov'], 'PUT', '/user/commander%40lspd.gov/contact/bob%40lspd.gov', ['name' => 'Bobby', 'email' => 'bob@lspd.gov'], Contact::class];
        yield 'contact delete' => [static fn (MailRpClient $c) => $c->users->contacts->delete('commander@lspd.gov', 'bob@lspd.gov'), 204, null, 'DELETE', '/user/commander%40lspd.gov/contact/bob%40lspd.gov', null, null];

        yield 'user events' => [static fn (MailRpClient $c) => $c->users->events->search('commander@lspd.gov', '2026-10-01', '2026-10-31'), 200, Fixtures::page([$event]), 'GET', '/user/commander%40lspd.gov/event/search?start=2026-10-01&end=2026-10-31', null, Page::class];

        yield 'mail send' => [static fn (MailRpClient $c) => $c->users->mails->send('commander@lspd.gov', [
            'to' => ['bob@lspd.gov'], 'subject' => 'Rapport', 'content' => '<p>Hello</p>', 'attachments' => ['https://x/a.pdf'],
            'event' => ['title' => 'Meeting', 'start' => new \DateTimeImmutable('2026-10-20 14:00'), 'end' => '2026-10-20 15:00', 'icon' => EventIcon::PEOPLE, 'requiredParticipants' => true],
        ]), 202, ['message' => 'Mail en cours d\'envoi'], 'POST', '/user/commander%40lspd.gov/mail/send', [
            'to' => ['bob@lspd.gov'], 'subject' => 'Rapport', 'content' => '<p>Hello</p>', 'attachments' => ['https://x/a.pdf'],
            'event' => ['title' => 'Meeting', 'start' => '2026-10-20 14:00', 'end' => '2026-10-20 15:00', 'icon' => 'bi-people', 'requiredParticipants' => true],
        ], 'string'];

        yield 'redirection search' => [static fn (MailRpClient $c) => $c->users->redirections->search('commander@lspd.gov'), 200, Fixtures::page([['email' => 'r@lspd.gov']]), 'GET', '/user/commander%40lspd.gov/redirection/search', null, Page::class];
        yield 'redirection add' => [static fn (MailRpClient $c) => $c->users->redirections->add('commander@lspd.gov', 'r@lspd.gov'), 201, ['email' => 'r@lspd.gov'], 'POST', '/user/commander%40lspd.gov/redirection/add', ['email' => 'r@lspd.gov'], 'string'];
        yield 'redirection remove' => [static fn (MailRpClient $c) => $c->users->redirections->remove('commander@lspd.gov', 'r@lspd.gov'), 204, null, 'DELETE', '/user/commander%40lspd.gov/redirection/r%40lspd.gov', null, null];
        yield 'redirection remove all' => [static fn (MailRpClient $c) => $c->users->redirections->removeAll('commander@lspd.gov'), 204, null, 'DELETE', '/user/commander%40lspd.gov/redirection/all', null, null];

        // Calendars
        yield 'calendar get' => [static fn (MailRpClient $c) => $c->calendars->get(self::CAL), 200, $calendar, 'GET', '/calendar/' . self::CAL, null, Calendar::class];
        yield 'calendar update' => [static fn (MailRpClient $c) => $c->calendars->update(self::CAL, ['name' => 'X', 'color' => 'danger']), 200, $calendar, 'PUT', '/calendar/' . self::CAL, ['name' => 'X', 'color' => 'danger'], Calendar::class];
        yield 'calendar delete' => [static fn (MailRpClient $c) => $c->calendars->delete(self::CAL), 204, null, 'DELETE', '/calendar/' . self::CAL, null, null];
        yield 'calendar transfer' => [static fn (MailRpClient $c) => $c->calendars->transfer(self::CAL, 'bob@lspd.gov'), 200, $calendar, 'POST', '/calendar/' . self::CAL . '/transfer', ['email' => 'bob@lspd.gov'], Calendar::class];

        yield 'event search' => [static fn (MailRpClient $c) => $c->calendars->events->search(self::CAL, '2026-10-01 00:00', '2026-10-31 23:59', ['sort' => 'title']), 200, Fixtures::page([$event]), 'GET', '/calendar/' . self::CAL . '/event/search?start=2026-10-01%2000%3A00&end=2026-10-31%2023%3A59&sort=title', null, Page::class];
        yield 'event get' => [static fn (MailRpClient $c) => $c->calendars->events->get(self::CAL, self::EVT), 200, $event, 'GET', '/calendar/' . self::CAL . '/event/' . self::EVT, null, CalendarEvent::class];
        yield 'event create' => [static fn (MailRpClient $c) => $c->calendars->events->create(self::CAL, [
            'title' => 'Ronde', 'start' => '2026-10-01 08:00', 'end' => '2026-10-01 09:00',
            'repeat' => ['start' => '2026-10-01', 'end' => '2026-12-31', 'frequency' => RepeatFrequency::WEEKLY, 'monday' => true],
        ]), 201, $event, 'POST', '/calendar/' . self::CAL . '/event/add', [
            'title' => 'Ronde', 'start' => '2026-10-01 08:00', 'end' => '2026-10-01 09:00',
            'repeat' => ['start' => '2026-10-01', 'end' => '2026-12-31', 'frequency' => 'weekly', 'monday' => true],
        ], CalendarEvent::class];
        yield 'event update' => [static fn (MailRpClient $c) => $c->calendars->events->update(self::CAL, self::EVT, ['title' => 'Ronde', 'start' => '2026-10-01 08:00', 'end' => '2026-10-01 09:00', 'applyAll' => true]), 200, $event, 'PUT', '/calendar/' . self::CAL . '/event/' . self::EVT, ['title' => 'Ronde', 'start' => '2026-10-01 08:00', 'end' => '2026-10-01 09:00', 'applyAll' => true], CalendarEvent::class];
        yield 'event delete' => [static fn (MailRpClient $c) => $c->calendars->events->delete(self::CAL, self::EVT), 204, null, 'DELETE', '/calendar/' . self::CAL . '/event/' . self::EVT, null, null];
        yield 'event delete series' => [static fn (MailRpClient $c) => $c->calendars->events->delete(self::CAL, self::EVT, applyAllNext: true), 204, null, 'DELETE', '/calendar/' . self::CAL . '/event/' . self::EVT . '?applyAllNext=1', null, null];

        yield 'invite search' => [static fn (MailRpClient $c) => $c->calendars->invites->search(self::CAL), 200, Fixtures::page([$member]), 'GET', '/calendar/' . self::CAL . '/invite/search', null, Page::class];
        yield 'invite add' => [static fn (MailRpClient $c) => $c->calendars->invites->add(self::CAL, ['a@lspd.gov', 'x@y.z'], true), 200, ['invited' => ['a@lspd.gov'], 'ignored' => ['x@y.z']], 'POST', '/calendar/' . self::CAL . '/invite/add', ['users' => ['a@lspd.gov', 'x@y.z'], 'manager' => true], InviteResult::class];
        yield 'invite update' => [static fn (MailRpClient $c) => $c->calendars->invites->update(self::CAL, 'a@lspd.gov', false), 200, $member, 'PUT', '/calendar/' . self::CAL . '/invite/a%40lspd.gov', ['manager' => false], CalendarInvite::class];
        yield 'invite remove' => [static fn (MailRpClient $c) => $c->calendars->invites->remove(self::CAL, 'a@lspd.gov'), 204, null, 'DELETE', '/calendar/' . self::CAL . '/invite/a%40lspd.gov', null, null];

        // Newsletters
        yield 'newsletter search' => [static fn (MailRpClient $c) => $c->newsletters->search(), 200, Fixtures::page([$newsletter]), 'GET', '/newsletter/search', null, Page::class];
        yield 'newsletter get' => [static fn (MailRpClient $c) => $c->newsletters->get('lspd-news'), 200, $newsletter, 'GET', '/newsletter/lspd-news', null, Newsletter::class];
        yield 'newsletter create' => [static fn (MailRpClient $c) => $c->newsletters->create('lspd.gov', 'News'), 201, Fixtures::newsletter(true), 'POST', '/newsletter/add', ['domain' => 'lspd.gov', 'name' => 'News'], Newsletter::class];
        yield 'newsletter update' => [static fn (MailRpClient $c) => $c->newsletters->update('lspd-news', 'Infos'), 200, Fixtures::newsletter(true), 'PUT', '/newsletter/lspd-news', ['name' => 'Infos'], Newsletter::class];
        yield 'newsletter delete' => [static fn (MailRpClient $c) => $c->newsletters->delete('lspd-news'), 204, null, 'DELETE', '/newsletter/lspd-news', null, null];
        yield 'sender search' => [static fn (MailRpClient $c) => $c->newsletters->senders->search('lspd-news'), 200, Fixtures::page([$user]), 'GET', '/newsletter/lspd-news/sender/search', null, Page::class];
        yield 'sender add' => [static fn (MailRpClient $c) => $c->newsletters->senders->add('lspd-news', 'commander@lspd.gov'), 201, $user, 'POST', '/newsletter/lspd-news/sender/add', ['email' => 'commander@lspd.gov'], User::class];
        yield 'sender remove' => [static fn (MailRpClient $c) => $c->newsletters->senders->remove('lspd-news', 'commander@lspd.gov'), 204, null, 'DELETE', '/newsletter/lspd-news/sender/commander%40lspd.gov', null, null];
        yield 'subscription search' => [static fn (MailRpClient $c) => $c->newsletters->subscriptions->search('lspd-news'), 200, Fixtures::page([['email' => 's@x.y', 'createdAt' => '2026-01-01T12:00:00+00:00']]), 'GET', '/newsletter/lspd-news/subscription/search', null, Page::class];
        yield 'subscription add' => [static fn (MailRpClient $c) => $c->newsletters->subscriptions->add('lspd-news', 's@x.y'), 201, ['email' => 's@x.y', 'createdAt' => '2026-01-01T12:00:00+00:00'], 'POST', '/newsletter/lspd-news/subscription/add', ['email' => 's@x.y'], NewsletterSubscription::class];
        yield 'subscription remove' => [static fn (MailRpClient $c) => $c->newsletters->subscriptions->remove('lspd-news', 's@x.y'), 204, null, 'DELETE', '/newsletter/lspd-news/subscription/s%40x.y', null, null];
    }

    /**
     * @param \Closure(MailRpClient): mixed $call
     * @param array<mixed>|null             $response
     * @param array<mixed>|null             $expectedBody
     * @param class-string|string|null      $expectedType
     *
     * @throws \JsonException
     */
    #[DataProvider('endpoints')]
    public function testEndpoint(\Closure $call, int $status, ?array $response, string $method, string $path, ?array $expectedBody, ?string $expectedType): void
    {
        $transport = new FakeTransport()->queue($status, $response);
        $result = $call(new MailRpClient('xls-mrp-test', 'https://api.test', transport: $transport));

        self::assertSame($method, $transport->last()['method']);
        self::assertSame('https://api.test' . $path, $transport->last()['url']);
        self::assertSame($expectedBody, $transport->lastJsonBody());

        self::assertSame($expectedType ?? 'null', get_debug_type($result));
    }

    public function testHydratesModels(): void
    {
        $transport = new FakeTransport()
            ->queue(200, Fixtures::domain())
            ->queue(200, Fixtures::page([Fixtures::event()], 31))
            ->queue(201, Fixtures::user() + ['inviteLink' => 'https://xelyos.mail-rp.com/invite/abc']);
        $client = new MailRpClient('xls-mrp-test', transport: $transport);

        $domain = $client->domains->get('lspd.gov');
        self::assertSame('lspd.gov', $domain->name);
        self::assertFalse($domain->public);
        self::assertSame(153, $domain->usersCount);
        self::assertSame('chief@lspd.gov', $domain->manager?->email);
        self::assertNull($domain->manager->createdAt);
        self::assertSame('2026-01-02T12:00:00+00:00', $domain->updatedAt?->format(DATE_ATOM));

        $events = $client->calendars->events->search(self::CAL, '2026-10-01', '2026-10-31');
        self::assertSame(31, $events->total);
        self::assertCount(1, $events);
        foreach ($events as $event) {
            self::assertSame('Patrouilles', $event->calendar?->name);
            self::assertSame('2026-10-14 09:00', $event->start);
        }

        $user = $client->users->create(['email' => 'commander@lspd.gov', 'name' => 'Foster', 'firstname' => 'Dan', 'invite' => true]);
        self::assertSame('https://xelyos.mail-rp.com/invite/abc', $user->inviteLink);
        self::assertSame('Mail RP Manager', $user->publicInformation?->description);
        self::assertSame('Dan Foster', $user->getFullName());
    }

    public function testIteratesOverEveryPage(): void
    {
        $transport = new FakeTransport()
            ->queue(200, Fixtures::page([Fixtures::user('a@lspd.gov'), Fixtures::user('b@lspd.gov')], 5))
            ->queue(200, Fixtures::page([Fixtures::user('c@lspd.gov'), Fixtures::user('d@lspd.gov')], 5))
            ->queue(200, Fixtures::page([Fixtures::user('e@lspd.gov')], 5));
        $client = new MailRpClient('xls-mrp-test', 'https://api.test', transport: $transport);

        $emails = [];
        foreach ($client->users->iterate(['search' => 'lspd', 'limit' => 2]) as $user) {
            $emails[] = $user->email;
        }

        self::assertSame(['a@lspd.gov', 'b@lspd.gov', 'c@lspd.gov', 'd@lspd.gov', 'e@lspd.gov'], $emails);
        self::assertSame(
            [
                'https://api.test/user/search?search=lspd&limit=2&offset=0',
                'https://api.test/user/search?search=lspd&limit=2&offset=2',
                'https://api.test/user/search?search=lspd&limit=2&offset=4',
            ],
            array_column($transport->requests, 'url')
        );
    }

    public function testIterationStopsOnEmptyPage(): void
    {
        // Announced total higher than the rows actually available (deletion during the iteration)
        $transport = new FakeTransport()
            ->queue(200, Fixtures::page([['email' => 'a@x.y']], 10))
            ->queue(200, Fixtures::page([], 10));
        $client = new MailRpClient('xls-mrp-test', 'https://api.test', transport: $transport);

        self::assertSame(['a@x.y'], iterator_to_array($client->users->aliases->iterate('commander@lspd.gov'), false));
        self::assertSame('https://api.test/user/commander%40lspd.gov/alias/search?limit=100&offset=0', $transport->requests[0]['url']);
        self::assertCount(2, $transport->requests);
    }
}
