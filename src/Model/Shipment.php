<?php

declare(strict_types=1);

namespace Parcora\Model;

use DateTimeImmutable;
use Parcora\Enum\CarrierCode;
use Parcora\Enum\LegType;
use Parcora\Enum\ShipmentStatus;
use Parcora\Util\Data;

/** A logical shipment with its stable tracking code and one or more carrier legs. */
final class Shipment
{
    /**
     * @param  array<string, string>  $metadata
     * @param  list<ShipmentLeg>  $legs
     */
    public function __construct(
        public readonly string $id,
        public readonly bool $livemode,
        public readonly string $trackingCode,
        public readonly ShipmentStatus $status,
        public readonly ?CarrierCode $carrier,
        public readonly ?string $trackingNumber,
        public readonly array $metadata,
        public readonly ?Address $sender,
        public readonly ?Address $receiver,
        public readonly array $legs,
        public readonly ?DateTimeImmutable $created,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);
        $carrier = $d->stringOrNull('carrier');
        $sender = $d->objectOrNull('sender');
        $receiver = $d->objectOrNull('receiver');

        return new self(
            id: $d->string('id'),
            livemode: $d->bool('livemode'),
            trackingCode: $d->string('tracking_code'),
            status: ShipmentStatus::from($d->string('status')),
            carrier: $carrier !== null ? CarrierCode::from($carrier) : null,
            trackingNumber: $d->stringOrNull('tracking_number'),
            metadata: $d->stringMap('metadata'),
            sender: $sender !== null ? Address::fromArray($sender) : null,
            receiver: $receiver !== null ? Address::fromArray($receiver) : null,
            legs: array_map(ShipmentLeg::fromArray(...), $d->listOfObjects('legs')),
            created: $d->dateTimeOrNull('created'),
        );
    }

    public function outboundLeg(): ?ShipmentLeg
    {
        foreach ($this->legs as $leg) {
            if ($leg->type === LegType::Outbound) {
                return $leg;
            }
        }

        return null;
    }
}
