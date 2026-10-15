<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Enum;

/**
 * Icon of a calendar event (Bootstrap Icons classes).
 */
enum EventIcon: string
{
    case CALENDAR = 'bi-calendar-event';
    case PEOPLE = 'bi-people';
    case CALL = 'bi-telephone';
    case VIDEO = 'bi-camera-video';
    case APPOINTMENT = 'bi-calendar-check';
    case GIFT = 'bi-gift';
    case TASK = 'bi-check2-square';
    case REMINDER = 'bi-bell';
    case MEAL = 'bi-cup-hot';
    case TRAVEL = 'bi-airplane';
    case SPORT = 'bi-trophy';
    case TRAINING = 'bi-mortarboard';
    case IMPORTANT = 'bi-star';
    case WORK = 'bi-briefcase';
    case HOME = 'bi-house-door';
    case HEALTH = 'bi-heart-pulse';
}
