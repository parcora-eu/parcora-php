<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Util\Data;

/** One parcel within a shipment leg. */
final class Parcel
{
    public function __construct(
        public readonly ?int $weightGrams = null,
        public readonly ?int $lengthMm = null,
        public readonly ?int $widthMm = null,
        public readonly ?int $heightMm = null,
        public readonly ?string $reference = null,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            weightGrams: $d->intOrNull('weight_grams'),
            lengthMm: $d->intOrNull('length_mm'),
            widthMm: $d->intOrNull('width_mm'),
            heightMm: $d->intOrNull('height_mm'),
            reference: $d->stringOrNull('reference'),
        );
    }
}
