<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Enum\CarrierCode;
use Parcora\Enum\ShipmentStatus;
use Parcora\Util\Data;

/** The public tracking view of a shipment: its status plus the event timeline. */
final class Tracker
{
    /**
     * @param  array<string, string>  $metadata
     * @param  list<TrackingEvent>  $events
     */
    public function __construct(
        public readonly string $id,
        public readonly bool $livemode,
        public readonly string $trackingCode,
        public readonly ShipmentStatus $status,
        public readonly ?CarrierCode $carrier,
        public readonly ?string $trackingNumber,
        public readonly array $metadata,
        public readonly string $shipment,
        public readonly string $queried,
        public readonly array $events,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);
        $carrier = $d->stringOrNull('carrier');

        return new self(
            id: $d->string('id'),
            livemode: $d->bool('livemode'),
            trackingCode: $d->string('tracking_code'),
            status: ShipmentStatus::from($d->string('status')),
            carrier: $carrier !== null ? CarrierCode::from($carrier) : null,
            trackingNumber: $d->stringOrNull('tracking_number'),
            metadata: $d->stringMap('metadata'),
            shipment: $d->string('shipment'),
            queried: $d->string('queried'),
            events: array_map(TrackingEvent::fromArray(...), $d->listOfObjects('events')),
        );
    }
}
