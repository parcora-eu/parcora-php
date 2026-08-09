<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Enum\CarrierCode;
use Parcora\Enum\LabelFormat;
use Parcora\Enum\PickupPointType;
use Parcora\Util\Data;

/** A carrier and the capabilities available to the current organization. */
final class Carrier
{
    /**
     * @param  list<string>  $services
     * @param  list<LabelFormat>  $labelFormats
     * @param  list<PickupPointType>  $pickupPointTypes
     * @param  list<string>  $capabilities
     */
    public function __construct(
        public readonly CarrierCode $carrier,
        public readonly string $name,
        public readonly bool $connected,
        public readonly ?string $credentialSource,
        public readonly array $services,
        public readonly array $labelFormats,
        public readonly array $pickupPointTypes,
        public readonly array $capabilities,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            carrier: $d->enum('carrier', CarrierCode::class),
            name: $d->string('name'),
            connected: $d->bool('connected'),
            credentialSource: $d->stringOrNull('credential_source'),
            services: $d->listOfStrings('services'),
            labelFormats: array_map(fn (string $v): LabelFormat => LabelFormat::tryFrom($v) ?? LabelFormat::Unknown, $d->listOfStrings('label_formats')),
            pickupPointTypes: array_map(fn (string $v): PickupPointType => PickupPointType::tryFrom($v) ?? PickupPointType::Unknown, $d->listOfStrings('pickup_point_types')),
            capabilities: $d->listOfStrings('capabilities'),
        );
    }
}
