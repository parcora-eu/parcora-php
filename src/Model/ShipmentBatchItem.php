<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Enum\BatchItemStatus;
use Parcora\Util\Data;

/** One submitted shipment within a batch and its outcome. */
final class ShipmentBatchItem
{
    public function __construct(
        public readonly string $id,
        public readonly int $position,
        public readonly BatchItemStatus $status,
        public readonly ?string $shipment,
        public readonly ?BatchItemError $error,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);
        $error = $d->objectOrNull('error');

        return new self(
            id: $d->string('id'),
            position: $d->int('position'),
            status: BatchItemStatus::from($d->string('status')),
            shipment: $d->stringOrNull('shipment'),
            error: $error !== null ? BatchItemError::fromArray($error) : null,
        );
    }
}
