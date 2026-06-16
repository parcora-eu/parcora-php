<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Model\Carrier;
use Parcora\Model\Collection;

/** `/v1/carriers` — the capability matrix for the organization's carriers. */
final class CarrierService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     * @return Collection<Carrier>
     */
    public function all(array $params = [], array $opts = []): Collection
    {
        $body = $this->requestor->request('GET', 'carriers', $params, null, $opts)->data;

        return new Collection(array_map(Carrier::fromArray(...), $this->rows($body)), $this->hasMore($body));
    }
}
