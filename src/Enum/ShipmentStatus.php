<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** The unified status of a shipment or leg. */
enum ShipmentStatus: string
{
    case Pending = 'pending';
    case Registered = 'registered';
    case LabelReady = 'label_ready';
    case InTransit = 'in_transit';
    case AtPickupPoint = 'at_pickup_point';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
