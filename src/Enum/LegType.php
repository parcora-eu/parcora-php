<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** The role of a carrier leg within a shipment. */
enum LegType: string
{
    case Outbound = 'outbound';
    case Return = 'return';
    case Redelivery = 'redelivery';
}
