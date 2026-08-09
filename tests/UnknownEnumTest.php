<?php

declare(strict_types=1);

use Parcora\Enum\CarrierCode;
use Parcora\Enum\LabelFormat;
use Parcora\Enum\PickupPointType;
use Parcora\Enum\ShipmentStatus;
use Parcora\Enum\TrackingCode;
use Parcora\Tests\Support\MockHttpClient;

/**
 * The API's vocabularies are open — carriers get added, a tracking milestone is
 * refined — and an SDK release always lags. A value this version has not heard
 * of has to degrade to `Unknown`, not throw: decoding is all-or-nothing, so a
 * strict read costs the caller the entire response over one unfamiliar string.
 */
it('reads an unfamiliar tracking code as Unknown and keeps the carrier code', function () {
    // This is the case that broke a live integration: the gate started sending
    // `unknown` for carrier codes it cannot classify, and no release of this SDK
    // had the case, so fetching any timeline containing one threw.
    $http = new MockHttpClient(listResponse([[
        'object' => 'tracking_event',
        'code' => 'unknown',
        'carrier_code' => 'TRT_SHIPMENT_REGISTERED',
        'description' => 'Registered with the carrier',
        'occurred_at' => '2026-06-16T09:00:00+00:00',
    ]]));

    $events = parcora($http)->shipments->trackingEvents('shp_1');

    expect($events[0]->code)->toBe(TrackingCode::Unknown)
        // The raw code is what support actually needs, and it survives.
        ->and($events[0]->carrierCode)->toBe('TRT_SHIPMENT_REGISTERED');
});

it('reads a milestone invented after this release as Unknown', function () {
    $http = new MockHttpClient(listResponse([[
        'object' => 'tracking_event',
        'code' => 'held_at_customs',
        'carrier_code' => 'CUSTOMS_HOLD',
        'occurred_at' => '2026-06-16T09:00:00+00:00',
    ]]));

    expect(parcora($http)->shipments->trackingEvents('shp_1')[0]->code)->toBe(TrackingCode::Unknown);
});

it('reads a carrier and status this release does not know as Unknown', function () {
    $http = new MockHttpClient(jsonResponse(200, [
        ...shipmentData('shp_1'),
        'carrier' => 'a-carrier-added-next-quarter',
        'status' => 'awaiting_customs',
        'legs' => [[
            'id' => 'leg_1',
            'object' => 'shipment_leg',
            'type' => 'outbound',
            'carrier' => 'a-carrier-added-next-quarter',
            'service' => 'x.y',
            'status' => 'awaiting_customs',
        ]],
    ]));

    $shipment = parcora($http)->shipments->retrieve('shp_1');

    expect($shipment->carrier)->toBe(CarrierCode::Unknown)
        ->and($shipment->status)->toBe(ShipmentStatus::Unknown)
        ->and($shipment->legs[0]->carrier)->toBe(CarrierCode::Unknown)
        ->and($shipment->legs[0]->status)->toBe(ShipmentStatus::Unknown);
});

it('keeps the formats and point types it knows when one in the list is new', function () {
    $http = new MockHttpClient(listResponse([[
        'object' => 'carrier',
        'carrier' => 'omniva',
        'name' => 'Omniva',
        'label_formats' => ['pdf_a4', 'pdf_a6_thermal'],
        'pickup_point_types' => ['locker', 'post_office_counter'],
    ]]));

    $carrier = parcora($http)->carriers->all()->data[0];

    expect($carrier->labelFormats)->toBe([LabelFormat::PdfA4, LabelFormat::Unknown])
        ->and($carrier->pickupPointTypes)->toBe([PickupPointType::Locker, PickupPointType::Unknown]);
});
