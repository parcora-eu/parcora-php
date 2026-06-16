<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Model\Collection;
use Parcora\Model\ShipmentBatch;
use Parcora\Model\ShipmentBatchItem;

/** `/v1/shipment_batches` — submit a bulk batch and read back per-item results. */
final class ShipmentBatchService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $params  e.g. ['shipments' => [...], 'metadata' => [...]]
     * @param  array<string, mixed>  $opts
     */
    public function create(array $params, array $opts = []): ShipmentBatch
    {
        return ShipmentBatch::fromArray($this->requestor->request('POST', 'shipment_batches', null, $params, $opts)->data);
    }

    /** @param array<string, mixed> $opts */
    public function retrieve(string $id, array $opts = []): ShipmentBatch
    {
        return ShipmentBatch::fromArray($this->requestor->request('GET', 'shipment_batches/'.$id, null, null, $opts)->data);
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     * @return Collection<ShipmentBatchItem>
     */
    public function items(string $id, array $params = [], array $opts = []): Collection
    {
        $body = $this->requestor->request('GET', 'shipment_batches/'.$id.'/items', $params, null, $opts)->data;

        return new Collection(
            array_map(ShipmentBatchItem::fromArray(...), $this->rows($body)),
            $this->hasMore($body),
            fn (ShipmentBatchItem $last): Collection => $this->items($id, [...$params, 'starting_after' => $last->id], $opts),
        );
    }

    /**
     * Raw merged label-sheet bytes for the whole batch.
     *
     * @param  array<string, mixed>  $params  e.g. ['format' => 'pdf_a4']
     * @param  array<string, mixed>  $opts
     */
    public function labels(string $id, array $params = [], array $opts = []): string
    {
        return $this->requestor->request('GET', 'shipment_batches/'.$id.'/labels', $params, null, $opts)->body;
    }
}
