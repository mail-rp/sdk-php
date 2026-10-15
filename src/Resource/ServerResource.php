<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Resource;

use Xelyos\MailRp\Model\Server;

final class ServerResource extends AbstractResource
{
    /**
     * Information and counters of the server the key belongs to (`GET /server`).
     */
    public function get(): Server
    {
        return Server::fromArray($this->getJson('/server'));
    }
}
