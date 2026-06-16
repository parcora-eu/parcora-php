<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Enum\CarrierCode;
use Parcora\Enum\PickupPointType;
use Parcora\Util\Data;

/** A carrier pickup point (locker, post office or counter). */
final class PickupPoint
{
    public function __construct(
        public readonly string $id,
        public readonly CarrierCode $carrier,
        public readonly ?string $externalId,
        public readonly PickupPointType $type,
        public readonly string $name,
        public readonly Address $address,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly bool $codSupported,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);
        $location = $d->objectOrNull('location');

        return new self(
            id: $d->string('id'),
            carrier: CarrierCode::from($d->string('carrier')),
            externalId: $d->stringOrNull('external_id'),
            type: PickupPointType::from($d->string('type')),
            name: $d->string('name'),
            address: Address::fromArray($d->objectOrNull('address') ?? []),
            latitude: $location !== null ? Data::of($location)->floatOrNull('latitude') : null,
            longitude: $location !== null ? Data::of($location)->floatOrNull('longitude') : null,
            codSupported: $d->bool('cod_supported'),
        );
    }
}
