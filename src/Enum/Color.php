<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Enum;

/**
 * Colour of a calendar (events inherit the colour of their calendar).
 */
enum Color: string
{
    case PRIMARY = 'primary';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGER = 'danger';
    case INFO = 'info';
}
