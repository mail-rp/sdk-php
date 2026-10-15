<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * HTTP 400 / 422: missing, malformed or invalid data. {@see getPath()} names the field when known.
 */
class ValidationException extends ApiException
{
}
