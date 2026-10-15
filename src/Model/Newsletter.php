<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

final readonly class Newsletter
{
    /**
     * @param list<string>|null $emails addresses allowed to send to the newsletter (only after a creation or an
     *                                  update; use the senders search otherwise)
     */
    public function __construct(
        /** Code of the newsletter: mails sent to `<code>@newsletter` reach every subscriber */
        public string $code,
        public string $name,
        /** Domain name */
        public string $domain,
        public ?array $emails = null,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'code'),
            Data::string($data, 'name'),
            Data::string($data, 'domain'),
            isset($data['emails']) ? Data::stringList($data, 'emails') : null,
        );
    }
}
