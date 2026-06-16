<?php

declare(strict_types=1);

namespace Parcora\Model;

use DateTimeImmutable;
use Parcora\Util\Data;

/** A webhook event. {@see $data} is the `data.object` payload of the event. */
final class Event
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly bool $livemode,
        public readonly ?DateTimeImmutable $created,
        public readonly array $data,
    ) {}

    /** @param array<array-key, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $d = Data::of($payload);
        $envelope = $d->objectOrNull('data') ?? [];
        $object = Data::of($envelope)->objectOrNull('object') ?? [];

        return new self(
            id: $d->string('id'),
            type: $d->string('type'),
            livemode: $d->bool('livemode'),
            created: $d->timestampOrNull('created'),
            data: $object,
        );
    }
}
