<?php

declare(strict_types=1);

namespace Parcora\Model;

use Parcora\Util\Data;

/** The structured error recorded for a failed batch item. */
final class BatchItemError
{
    public function __construct(
        public readonly string $type,
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $carrierMessage = null,
        public readonly ?string $param = null,
    ) {}

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        $d = Data::of($data);

        return new self(
            type: $d->string('type'),
            code: $d->string('code'),
            message: $d->string('message'),
            carrierMessage: $d->stringOrNull('carrier_message'),
            param: $d->stringOrNull('param'),
        );
    }
}
