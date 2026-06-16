<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Parcora\Enum\ShipmentStatus;
use Parcora\Model\Shipment;
use Parcora\Tests\Support\MockHttpClient;

it('creates a shipment with bearer auth, a json body and an idempotency key', function () {
    $http = new MockHttpClient(jsonResponse(201, [...shipmentData('shp_1'), 'metadata' => ['order_id' => 'A1']]));

    $shipment = parcora($http)->shipments->create([
        'carrier' => 'omniva',
        'service' => 'omniva.courier',
        'parcels' => [['weight_grams' => 1000]],
    ]);

    expect($shipment)->toBeInstanceOf(Shipment::class)
        ->and($shipment->id)->toBe('shp_1')
        ->and($shipment->status)->toBe(ShipmentStatus::Registered)
        ->and($shipment->carrier?->value)->toBe('omniva')
        ->and($shipment->metadata['order_id'])->toBe('A1')
        ->and($shipment->created?->format('Y'))->toBe('2026');

    $request = $http->lastRequest();
    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.test/v1/shipments')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer dp_test_123')
        ->and($request->getHeaderLine('Content-Type'))->toContain('application/json')
        ->and($request->getHeaderLine('Idempotency-Key'))->not->toBe('')
        ->and((string) $request->getBody())->toContain('"carrier":"omniva"');
});

it('honours a custom idempotency key', function () {
    $http = new MockHttpClient(jsonResponse(201, shipmentData('shp_1')));

    parcora($http)->shipments->create(['carrier' => 'omniva'], ['idempotency_key' => 'my-key-1']);

    expect($http->lastRequest()->getHeaderLine('Idempotency-Key'))->toBe('my-key-1');
});

it('does not add an idempotency key to GET requests', function () {
    $http = new MockHttpClient(jsonResponse(200, shipmentData('shp_42', 'delivered')));

    $shipment = parcora($http)->shipments->retrieve('shp_42');

    expect($shipment->status)->toBe(ShipmentStatus::Delivered)
        ->and($http->lastRequest()->getMethod())->toBe('GET')
        ->and((string) $http->lastRequest()->getUri())->toBe('https://api.test/v1/shipments/shp_42')
        ->and($http->lastRequest()->getHeaderLine('Idempotency-Key'))->toBe('');
});

it('returns raw label bytes for binary endpoints', function () {
    $http = new MockHttpClient(new Response(200, ['Content-Type' => 'application/pdf'], '%PDF-1.7 fake'));

    $bytes = parcora($http)->shipments->label('shp_1', ['format' => 'pdf_a4']);

    expect($bytes)->toStartWith('%PDF')
        ->and((string) $http->lastRequest()->getUri())->toContain('format=pdf_a4');
});
