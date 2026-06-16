<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Model\Tracker;

/** `/v1/trackers` — the public tracking view for a tracking number. */
final class TrackerService extends AbstractService
{
    /** @param array<string, mixed> $opts */
    public function retrieve(string $trackingNumber, array $opts = []): Tracker
    {
        return Tracker::fromArray(
            $this->requestor->request('GET', 'trackers/'.rawurlencode($trackingNumber), null, null, $opts)->data,
        );
    }
}
