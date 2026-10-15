<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource\User;

use Xelyos\MailRp\Enum\EventIcon;
use Xelyos\MailRp\Model\Data;
use Xelyos\MailRp\Resource\AbstractResource;

/**
 * Mails sent on behalf of a user.
 *
 * @phpstan-type MailEvent array{title: string, start: \DateTimeInterface|string, end: \DateTimeInterface|string, description?: string|null, location?: string|null, icon?: EventIcon|string|null, allDay?: bool, requiredParticipants?: bool, calendarUuid?: string|null}
 * @phpstan-type MailInput array{to: string|list<string>, subject: string, from?: string|null, cc?: string|list<string>, content?: string|null, openConfirmation?: bool, priority?: bool, attachments?: list<string|array{url: string, name?: string|null}>, event?: MailEvent|null}
 */
final class MailResource extends AbstractResource
{
    /**
     * Sends a mail from a user (`user_mail.create`). The mail is queued: unknown recipients are reported to the sender
     * by a bounce mail, not by this call.
     *
     * - `from`: address of the user (default) or one of their active aliases
     * - `to` / `cc`: one address or a list; `code@newsletter` targets the subscribers of a newsletter
     * - `content`: HTML
     * - `attachments`: URLs (`https://...`) or `{url, name}`
     * - `event`: event attached to the mail, created in the personal calendar of the sender (or `calendarUuid`);
     *   `requiredParticipants: true` invites `to` as required and `cc` as optional participants
     *
     * @param MailInput $mail
     *
     * @return string confirmation message of the API
     */
    public function send(string $email, array $mail): string
    {
        return Data::string($this->sendJson('POST', self::path('/user/%s/mail/send', $email), $mail), 'message');
    }
}
