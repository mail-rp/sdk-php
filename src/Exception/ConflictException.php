<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * HTTP 409: the request conflicts with the current state (already exists, already disabled...).
 */
class ConflictException extends ApiException
{
}
