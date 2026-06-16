<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Model\Collection;
use Parcora\Model\PickupPoint;

/** `/v1/pickup_points` — carrier pickup points, filterable by carrier/country/type. */
final class PickupPointService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     * @return Collection<PickupPoint>
     */
    public function all(array $params = [], array $opts = []): Collection
    {
        $body = $this->requestor->request('GET', 'pickup_points', $params, null, $opts)->data;

        return new Collection(
            array_map(PickupPoint::fromArray(...), $this->rows($body)),
            $this->hasMore($body),
            fn (PickupPoint $last): Collection => $this->all([...$params, 'starting_after' => $last->id], $opts),
        );
    }
}
