<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * HTTP 404: the resource (domain, user, calendar, newsletter...) does not exist or is not visible with this key.
 */
class NotFoundException extends ApiException
{
}
