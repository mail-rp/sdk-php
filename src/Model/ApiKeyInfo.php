<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

/**
 * Key used by the client, as returned by `GET /`.
 */
final readonly class ApiKeyInfo
{
    /**
     * @param list<string>|null $permissions only when requested (e.g. `domain.read`, `user_mail.create`)
     */
    public function __construct(
        public string $message,
        public string $name,
        public ?string $description,
        /** Language of the messages returned by the API for this key */
        public string $locale,
        public ?array $permissions,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $key = Data::array($data, 'key');

        return new self(
            Data::string($data, 'message'),
            Data::string($key, 'name'),
            Data::nullableString($key, 'description'),
            Data::string($key, 'locale'),
            isset($key['permissions']) ? Data::stringList($key, 'permissions') : null,
        );
    }

    public function hasPermission(string $permission): ?bool
    {
        return null === $this->permissions ? null : in_array($permission, $this->permissions, true);
    }
}
