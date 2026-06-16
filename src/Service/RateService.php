<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Model\Rate;

/** `/v1/rates` — price quotes across carriers for a sender/receiver/parcel. */
final class RateService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     * @return list<Rate>
     */
    public function calculate(array $params, array $opts = []): array
    {
        $body = $this->requestor->request('POST', 'rates', null, $params, $opts)->data;

        return array_map(Rate::fromArray(...), $this->rows($body));
    }
}
