<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * The API answered with a success status but a body the SDK cannot read (invalid JSON, missing field...).
 */
class UnexpectedResponseException extends \UnexpectedValueException implements MailRpExceptionInterface
{
}
