<?php

declare(strict_types=1);

namespace Parcora\Model;

use DateTimeImmutable;
use Parcora\Enum\BatchStatus;
use Parcora\Util\Data;

/** A bulk create request: N shipments created asynchronously, with rolled-up counts. */
final class ShipmentBatch
{
    /** @param array<string, string> $metadata */
    public function __construct(
        public readonly string $id,
        public readonly bool $livemode,
        public readonly BatchStatus $status,
        public readonly Counts $counts,
        public readonly array $metadata,
        public readonly ?DateTimeImmutable $created,
        public readonly ?DateTimeImmutable $completed,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            id: $d->string('id'),
            livemode: $d->bool('livemode'),
            status: BatchStatus::from($d->string('status')),
            counts: Counts::fromArray($d->array('counts')),
            metadata: $d->stringMap('metadata'),
            created: $d->dateTimeOrNull('created'),
            completed: $d->dateTimeOrNull('completed'),
        );
    }
}
