# Mail RP — PHP SDK

PHP client for the [Mail RP API](https://api.mail-rp.com/docs/): domains, users, calendars, newsletters, mail sending.

> **Development version (`1.0.3-dev`)**: the API surface may still change before the first stable release.

- PHP ≥ 8.2, `curl` extension (no other dependency)
- Typed responses (read-only objects), typed exceptions, automatic pagination
- Automatic retry on `429 Too Many Requests`
- Replaceable HTTP transport (any PSR-18 client: Guzzle, Symfony HttpClient…)

## Installation

```bash
composer require xelyos/mail-rp:@dev
```

## Quick start

```php
use Xelyos\MailRp\MailRpClient;

$mailRp = new MailRpClient('xls-mrp-...'); // key created in the panel (Server > API)

$user = $mailRp->users->get('commander@lspd.gov');
echo $user->getFullName();

$mailRp->users->mails->send('commander@lspd.gov', [
    'to' => ['chief@lspd.gov'],
    'subject' => 'Patrol report',
    'content' => '<p>Nothing to report tonight.</p>',
]);
```

## Configuration

```php
$mailRp = new MailRpClient(
    apiKey: 'xls-mrp-...',
    baseUrl: 'https://api.mail-rp.com', // default
    timeout: 30.0,                      // seconds
    maxRetries: 2,                      // retries on 429 (0 to disable)
    maxRetryDelay: 60,                  // longest accepted Retry-After (s); above it, the exception is thrown at once
    transport: null,                    // CurlTransport by default
);
```

A key only has access to the permissions ticked in the panel and, when it is restricted, to its domains.
`$mailRp->system->checkKey(true)` returns the name, the language and the permissions of the key.

## Resources

| Access | Methods |
| --- | --- |
| `$mailRp->system` | `health()`, `checkKey(bool $withPermissions = false)` |
| `$mailRp->server` | `get()` |
| `$mailRp->domains` | `search()`, `iterate()`, `get()`, `create()`, `update()`, `delete()`, `disable()`, `enable()`, `searchUsers()`, `iterateUsers()`, `searchNewsletters()`, `iterateNewsletters()`, `createNewsletter()` |
| `$mailRp->users` | `search()`, `iterate()`, `get()`, `create()`, `update()`, `delete()`, `disable()`, `enable()` |
| `$mailRp->users->aliases` | `search()`, `iterate()`, `add()`, `remove()` |
| `$mailRp->users->calendars` | `search()`, `iterate()`, `create()` |
| `$mailRp->users->contacts` | `search()`, `iterate()`, `create()`, `update()`, `delete()` |
| `$mailRp->users->events` | `search()`, `iterate()` |
| `$mailRp->users->mails` | `send()` |
| `$mailRp->users->redirections` | `search()`, `iterate()`, `add()`, `remove()`, `removeAll()` |
| `$mailRp->calendars` | `get()`, `update()`, `delete()`, `transfer()` |
| `$mailRp->calendars->events` | `search()`, `iterate()`, `get()`, `create()`, `update()`, `delete()` |
| `$mailRp->calendars->invites` | `search()`, `iterate()`, `add()`, `update()`, `remove()` |
| `$mailRp->newsletters` | `search()`, `iterate()`, `get()`, `create()`, `update()`, `delete()` |
| `$mailRp->newsletters->senders` | `search()`, `iterate()`, `add()`, `remove()` |
| `$mailRp->newsletters->subscriptions` | `search()`, `iterate()`, `add()`, `remove()` |

Resources are identified as in the API: email address for a user, name for a domain, uuid for a calendar or an event,
code for a newsletter. The docblock of each method states the required permission.

### Search and pagination

`search()` methods return a `Page` (`$page->total`, `$page->rows`, iterable and countable). Parameters: `search`,
`limit` (1 to 100, API default 10), `offset`, `sort`, `order` (`asc` / `desc` or `SortOrder`).

```php
$page = $mailRp->domains->search(['search' => 'lspd', 'sort' => 'name', 'limit' => 20]);
echo $page->total;

// iterate() chains the pages (100 rows per call by default): a generator, consumed on demand
foreach ($mailRp->users->iterate(['search' => 'lspd.gov']) as $user) {
    echo $user->email, PHP_EOL;
}
```

Aliases and redirections are returned as addresses (`Page<string>`).

### Calendars and events

Period dates are read in the time zone of the server, in the `Y-m-d H:i` format (or `Y-m-d`). A `DateTimeInterface`
is accepted everywhere and formatted that way, in its own time zone. In responses, the `start` / `end` of events stay
`Y-m-d H:i` strings; `createdAt` / `updatedAt` are `DateTimeImmutable` objects.

```php
use Xelyos\MailRp\Enum\{Color, EventIcon, RepeatFrequency};

$calendar = $mailRp->users->calendars->create('commander@lspd.gov', ['name' => 'Patrols', 'color' => Color::DANGER]);

$event = $mailRp->calendars->events->create($calendar->uuid, [
    'title' => 'Briefing',
    'start' => new DateTimeImmutable('2026-10-20 09:00'),
    'end' => '2026-10-20 10:00',
    'icon' => EventIcon::PEOPLE,
    'requiredParticipants' => ['chief@lspd.gov'],
    'repeat' => ['start' => '2026-10-20', 'end' => '2026-12-31', 'frequency' => RepeatFrequency::WEEKLY, 'monday' => true],
]);

$week = $mailRp->users->events->search('commander@lspd.gov', '2026-10-19', '2026-10-25', ['calendar' => $calendar->uuid]);

// Repeated event: delete the whole series
$mailRp->calendars->events->delete($calendar->uuid, $event->uuid, applyAll: true);
```

### Sending a mail

```php
$mailRp->users->mails->send('commander@lspd.gov', [
    'from' => 'patrol@lspd.gov',                    // address of the user (default) or one of their active aliases
    'to' => ['chief@lspd.gov', 'infos@newsletter'], // `code@newsletter` targets the subscribers of a newsletter
    'cc' => 'archives@lspd.gov',
    'subject' => 'Meeting',
    'content' => '<p>Agenda attached.</p>',
    'attachments' => ['https://example.com/agenda.pdf', ['url' => 'https://example.com/map.png', 'name' => 'Map']],
    'priority' => true,
    'openConfirmation' => true,
    'event' => [
        'title' => 'Meeting', 'start' => '2026-10-20 14:00', 'end' => '2026-10-20 15:00',
        'requiredParticipants' => true,             // `to` = required participants, `cc` = optional participants
    ],
]);
```

Sending is asynchronous (`202`): an unknown recipient is reported to the sender by a bounce mail, not by an exception.

## Errors

All exceptions implement `Xelyos\MailRp\Exception\MailRpExceptionInterface`.

| Exception | Case |
| --- | --- |
| `ValidationException` | 400 / 422 — missing or invalid data (`getPath()` gives the field) |
| `AuthenticationException` | 401 — missing, unknown or deleted key |
| `PermissionDeniedException` | 403 — missing permission, domain outside the key, refused IP, pack without API, server offline |
| `NotFoundException` | 404 — unknown resource |
| `ConflictException` | 409 — already existing, already disabled… |
| `RateLimitException` | 429 — once the retries are exhausted (`getRetryAfter()`) |
| `ServiceUnavailableException` | 503 — API under maintenance |
| `ServerException` | other 5xx |
| `ApiException` | parent of the previous ones (`getStatusCode()`, `getPath()`, `getRequestId()`, `getBody()`) |
| `TransportException` | no HTTP response (DNS, connection, timeout: `isTimeout()`) |
| `UnexpectedResponseException` | unreadable response |

The message is the one of the API, in the language set on the key. `getRequestId()` matches the identifier shown in
the history of the key (panel).

```php
use Xelyos\MailRp\Exception\{NotFoundException, ValidationException};

try {
    $mailRp->users->create(['email' => 'commander@lspd.gov', 'name' => 'Foster', 'firstname' => 'Dan', 'invite' => true]);
} catch (ValidationException $e) {
    echo "{$e->getPath()}: {$e->getMessage()}";
} catch (NotFoundException $e) {
    echo 'Unknown domain';
}
```

## Rate limit

60 requests per minute per key and 300 per server. On a `429`, the client waits for the announced `Retry-After`, then
replays the request (`maxRetries` times). The current state is available after each call:

```php
$meta = $mailRp->getLastResponse();
echo $meta?->rateLimitRemaining, ' / ', $meta?->rateLimitLimit, ' — ', $meta?->requestId;
```

## Custom HTTP client (PSR-18)

```php
use Xelyos\MailRp\Http\Psr18Transport;

$factory = new Nyholm\Psr7\Factory\Psr17Factory();
$mailRp = new MailRpClient('xls-mrp-...', transport: new Psr18Transport($psr18Client, $factory, $factory));
```

The `timeout` of the SDK does not apply to a PSR-18 client: configure it on that client. For more specific needs,
implement `Xelyos\MailRp\Http\Transport`.

## Development

```bash
composer install
composer test      # PHPUnit
composer phpstan   # max level
```

## Repository

This GitHub repository is a read-only mirror of the development version: the code is maintained in the Mail RP
repository (`sdk/php`) and published here with each release. Please open issues here; pull requests cannot be merged
directly into this mirror.

## License

[MIT](LICENSE) © Xelyos
