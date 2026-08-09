<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** The kind of a pickup point. */
enum PickupPointType: string
{
    case Locker = 'locker';
    case PostOffice = 'post_office';
    case PickupPoint = 'pickup_point';

    /** A point type this SDK version does not know. */
    case Unknown = 'unknown';
}
