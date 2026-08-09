<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** The role of a carrier leg within a shipment. */
enum LegType: string
{
    case Outbound = 'outbound';
    case Return = 'return';
    case Redelivery = 'redelivery';

    /** A leg type this SDK version does not know. */
    case Unknown = 'unknown';
}
