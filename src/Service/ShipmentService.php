<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Model\Collection;
use Parcora\Model\Shipment;
use Parcora\Model\ShipmentLeg;
use Parcora\Model\TrackingEvent;

/** `/v1/shipments` — create, retrieve, list, cancel, add legs and fetch labels. */
final class ShipmentService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     */
    public function create(array $params, array $opts = []): Shipment
    {
        return Shipment::fromArray($this->requestor->request('POST', 'shipments', null, $params, $opts)->data);
    }

    /** @param array<string, mixed> $opts */
    public function retrieve(string $id, array $opts = []): Shipment
    {
        return Shipment::fromArray($this->requestor->request('GET', 'shipments/'.$id, null, null, $opts)->data);
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     * @return Collection<Shipment>
     */
    public function all(array $params = [], array $opts = []): Collection
    {
        $body = $this->requestor->request('GET', 'shipments', $params, null, $opts)->data;

        return new Collection(
            array_map(Shipment::fromArray(...), $this->rows($body)),
            $this->hasMore($body),
            fn (Shipment $last): Collection => $this->all([...$params, 'starting_after' => $last->id], $opts),
        );
    }

    /** @param array<string, mixed> $opts */
    public function cancel(string $id, array $opts = []): Shipment
    {
        return Shipment::fromArray($this->requestor->request('POST', 'shipments/'.$id.'/cancel', null, null, $opts)->data);
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     */
    public function addLeg(string $id, array $params, array $opts = []): ShipmentLeg
    {
        return ShipmentLeg::fromArray($this->requestor->request('POST', 'shipments/'.$id.'/legs', null, $params, $opts)->data);
    }

    /**
     * Raw carrier label bytes (PDF or ZPL).
     *
     * @param  array<string, mixed>  $params  e.g. ['format' => 'pdf_a4', 'leg' => 'leg_…']
     * @param  array<string, mixed>  $opts
     */
    public function label(string $id, array $params = [], array $opts = []): string
    {
        return $this->requestor->request('GET', 'shipments/'.$id.'/label', $params, null, $opts)->body;
    }

    /**
     * Raw branded shipping-slip bytes (PDF).
     *
     * @param  array<string, mixed>  $opts
     */
    public function slip(string $id, array $opts = []): string
    {
        return $this->requestor->request('GET', 'shipments/'.$id.'/slip', null, null, $opts)->body;
    }

    /**
     * @param  array<string, mixed>  $opts
     * @return list<TrackingEvent>
     */
    public function trackingEvents(string $id, array $opts = []): array
    {
        $body = $this->requestor->request('GET', 'shipments/'.$id.'/tracking_events', null, null, $opts)->data;

        return array_map(TrackingEvent::fromArray(...), $this->rows($body));
    }
}
