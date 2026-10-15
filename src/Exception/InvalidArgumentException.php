<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * Invalid value given to the SDK before any request is sent.
 */
class InvalidArgumentException extends \InvalidArgumentException implements MailRpExceptionInterface
{
}
