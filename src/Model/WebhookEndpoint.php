<?php

declare(strict_types=1);

namespace Parcora\Model;

use DateTimeImmutable;
use Parcora\Util\Data;

/** A registered webhook endpoint. The signing `secret` is only present on create. */
final class WebhookEndpoint
{
    /** @param list<string> $events */
    public function __construct(
        public readonly string $id,
        public readonly bool $livemode,
        public readonly string $url,
        public readonly array $events,
        public readonly bool $active,
        public readonly ?DateTimeImmutable $created,
        public readonly ?string $secret = null,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            id: $d->string('id'),
            livemode: $d->bool('livemode'),
            url: $d->string('url'),
            events: $d->listOfStrings('events'),
            active: $d->bool('active'),
            created: $d->dateTimeOrNull('created'),
            secret: $d->stringOrNull('secret'),
        );
    }
}
