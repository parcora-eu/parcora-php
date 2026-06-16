<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Enum\CarrierCode;
use Parcora\Enum\LegType;
use Parcora\Enum\ShipmentStatus;
use Parcora\Util\Data;

/** A carrier leg of a shipment (outbound, return or redelivery). */
final class ShipmentLeg
{
    /**
     * @param  list<Parcel>  $parcels
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public readonly string $id,
        public readonly LegType $type,
        public readonly CarrierCode $carrier,
        public readonly string $service,
        public readonly ShipmentStatus $status,
        public readonly ?string $trackingNumber,
        public readonly array $parcels,
        public readonly array $options,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            id: $d->string('id'),
            type: LegType::from($d->string('type')),
            carrier: CarrierCode::from($d->string('carrier')),
            service: $d->string('service'),
            status: ShipmentStatus::from($d->string('status')),
            trackingNumber: $d->stringOrNull('tracking_number'),
            parcels: array_map(Parcel::fromArray(...), $d->listOfObjects('parcels')),
            options: $d->objectOrNull('options') ?? [],
        );
    }
}
