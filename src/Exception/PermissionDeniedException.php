<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * HTTP 403: permission missing on the key, resource outside its domains, IP not allowed, pack without API access, server offline or action not allowed on this resource.
 */
class PermissionDeniedException extends ApiException
{
}
