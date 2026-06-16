<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Enum\CarrierCode;
use Parcora\Util\Data;

/** A price quote for one carrier service. */
final class Rate
{
    public function __construct(
        public readonly CarrierCode $carrier,
        public readonly string $service,
        public readonly int $amountMinor,
        public readonly string $currency,
        public readonly ?string $source,
        public readonly ?int $estimatedDays,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            carrier: CarrierCode::from($d->string('carrier')),
            service: $d->string('service'),
            amountMinor: $d->int('amount_minor'),
            currency: $d->string('currency'),
            source: $d->stringOrNull('source'),
            estimatedDays: $d->intOrNull('estimated_days'),
        );
    }
}
