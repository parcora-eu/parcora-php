<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Util\Data;

/** Rolled-up item counts of a shipment batch. */
final class Counts
{
    public function __construct(
        public readonly int $total,
        public readonly int $succeeded,
        public readonly int $failed,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            total: $d->int('total'),
            succeeded: $d->int('succeeded'),
            failed: $d->int('failed'),
        );
    }
}
