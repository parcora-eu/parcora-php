<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Util\Data;

/** A sender, receiver or pickup-point address block. */
final class Address
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $line1 = null,
        public readonly ?string $line2 = null,
        public readonly ?string $city = null,
        public readonly ?string $region = null,
        public readonly ?string $postcode = null,
        public readonly ?string $country = null,
        public readonly ?string $pickupPointId = null,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            name: $d->stringOrNull('name'),
            phone: $d->stringOrNull('phone'),
            email: $d->stringOrNull('email'),
            line1: $d->stringOrNull('line1'),
            line2: $d->stringOrNull('line2'),
            city: $d->stringOrNull('city'),
            region: $d->stringOrNull('region'),
            postcode: $d->stringOrNull('postcode'),
            country: $d->stringOrNull('country'),
            pickupPointId: $d->stringOrNull('pickup_point_id'),
        );
    }
}
