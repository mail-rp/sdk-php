<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Enum;

/**
 * Frequency of a repeated event.
 */
enum RepeatFrequency: string
{
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
