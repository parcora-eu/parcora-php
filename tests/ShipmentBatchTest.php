<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Parcora\Enum\BatchItemStatus;
use Parcora\Enum\BatchStatus;
use Parcora\Tests\Support\MockHttpClient;

it('creates a batch and exposes counts and a status enum', function () {
    $http = new MockHttpClient(jsonResponse(202, [
        'id' => 'bat_1', 'object' => 'shipment_batch', 'livemode' => false, 'status' => 'processing',
        'counts' => ['total' => 2, 'succeeded' => 0, 'failed' => 0],
        'metadata' => ['source' => 'wms'], 'created' => '2026-06-16T09:00:00+00:00', 'completed' => null,
    ]));

    $batch = parcora($http)->shipmentBatches->create(['shipments' => [['carrier' => 'omniva']]]);

    expect($batch->id)->toBe('bat_1')
        ->and($batch->status)->toBe(BatchStatus::Processing)
        ->and($batch->counts->total)->toBe(2)
        ->and($batch->metadata['source'])->toBe('wms')
        ->and((string) $http->lastRequest()->getUri())->toBe('https://api.test/v1/shipment_batches');
});

it('lists batch items with per-item status and error detail', function () {
    $http = new MockHttpClient(listResponse([
        ['id' => 'bati_1', 'object' => 'shipment_batch_item', 'position' => 0, 'status' => 'succeeded', 'shipment' => 'shp_1', 'error' => null],
        ['id' => 'bati_2', 'object' => 'shipment_batch_item', 'position' => 1, 'status' => 'failed', 'shipment' => null, 'error' => ['type' => 'carrier_error', 'code' => 'x', 'message' => 'boom']],
    ]));

    $items = iterator_to_array(parcora($http)->shipmentBatches->items('bat_1')->autoPaging());

    expect($items)->toHaveCount(2)
        ->and($items[0]->status)->toBe(BatchItemStatus::Succeeded)
        ->and($items[0]->shipment)->toBe('shp_1')
        ->and($items[1]->status)->toBe(BatchItemStatus::Failed)
        ->and($items[1]->error?->message)->toBe('boom');
});

it('downloads merged label bytes for a batch', function () {
    $http = new MockHttpClient(new Response(200, ['Content-Type' => 'application/pdf'], '%PDF-merged'));

    $bytes = parcora($http)->shipmentBatches->labels('bat_1');

    expect($bytes)->toStartWith('%PDF')
        ->and((string) $http->lastRequest()->getUri())->toBe('https://api.test/v1/shipment_batches/bat_1/labels');
});
