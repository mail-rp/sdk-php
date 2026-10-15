<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Exception;

/**
 * No HTTP response could be obtained (DNS failure, connection refused, timeout...).
 */
class TransportException extends \RuntimeException implements MailRpExceptionInterface
{
    public function __construct(
        string $message,
        int $code = 0,
        private readonly bool $timeout = false,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function isTimeout(): bool
    {
        return $this->timeout;
    }
}
