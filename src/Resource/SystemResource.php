<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource;

use Xelyos\MailRp\Model\ApiKeyInfo;
use Xelyos\MailRp\Model\Health;

final class SystemResource extends AbstractResource
{
    /**
     * State of the API (`GET /health`, no permission needed).
     */
    public function health(): Health
    {
        return Health::fromArray($this->getJson('/health'));
    }

    /**
     * Checks the key and returns its information (`GET /`).
     *
     * @param bool $withPermissions also list the permissions granted to the key
     *
     * @throws \JsonException
     */
    public function checkKey(bool $withPermissions = false): ApiKeyInfo
    {
        // The parameter is read from its mere presence: it is not sent at all when not requested
        return ApiKeyInfo::fromArray($this->getJson('/', $withPermissions ? ['permissions' => true] : []));
    }
}
