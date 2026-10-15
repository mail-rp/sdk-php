<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * HTTP 401: API key missing, malformed, unknown or deleted.
 */
class AuthenticationException extends ApiException
{
}
