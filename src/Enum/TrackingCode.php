<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** A normalized tracking milestone. */
enum TrackingCode: string
{
    case Created = 'created';
    case HandedIn = 'handed_in';
    case InTransit = 'in_transit';
    case OutForDelivery = 'out_for_delivery';
    case DeliveredToPickupPoint = 'delivered_to_pickup_point';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Exception = 'exception';
    case Cancelled = 'cancelled';
}
