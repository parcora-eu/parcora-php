<?php

declare(strict_types=1);

namespace Parcora\Model;

use DateTimeImmutable;
use Parcora\Enum\TrackingCode;
use Parcora\Util\Data;

/** A single tracking milestone on a shipment. */
final class TrackingEvent
{
    public function __construct(
        public readonly TrackingCode $code,
        public readonly ?string $carrierCode,
        public readonly string $source,
        public readonly ?string $description,
        public readonly ?string $location,
        public readonly ?DateTimeImmutable $occurredAt,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            code: TrackingCode::from($d->string('code')),
            carrierCode: $d->stringOrNull('carrier_code'),
            source: $d->string('source'),
            description: $d->stringOrNull('description'),
            location: $d->stringOrNull('location'),
            occurredAt: $d->dateTimeOrNull('occurred_at'),
        );
    }
}
