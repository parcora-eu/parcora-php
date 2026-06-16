<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Http\ApiRequestor;

abstract class AbstractService
{
    public function __construct(protected readonly ApiRequestor $requestor) {}

    /**
     * The `data` rows of a list envelope, as arrays.
     *
     * @param  array<array-key, mixed>  $body
     * @return list<array<array-key, mixed>>
     */
    protected function rows(array $body): array
    {
        $rows = $body['data'] ?? null;
        $out = [];

        if (\is_array($rows)) {
            foreach ($rows as $row) {
                if (\is_array($row)) {
                    $out[] = $row;
                }
            }
        }

        return $out;
    }

    /** @param array<array-key, mixed> $body */
    protected function hasMore(array $body): bool
    {
        return (bool) ($body['has_more'] ?? false);
    }
}
