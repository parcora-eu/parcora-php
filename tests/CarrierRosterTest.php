<?php

declare(strict_types=1);

use Parcora\Enum\CarrierCode;
use Parcora\Tests\Support\MockHttpClient;

/**
 * Carriers the gate added after 0.1.0. Until they were cases here, a quote or
 * shipment on them decoded to `Unknown` — safe, but it cost every consumer the
 * carrier code on exactly the routes they had just started selling.
 */
it('knows La Poste and Posti', function () {
    $http = new MockHttpClient(listResponse([
        ['object' => 'rate', 'carrier' => 'laposte', 'service' => 'laposte.home', 'amount_minor' => 595, 'currency' => 'EUR'],
        ['object' => 'rate', 'carrier' => 'posti', 'service' => 'posti.parcel', 'amount_minor' => 790, 'currency' => 'EUR'],
    ]));

    $rates = parcora($http)->rates->calculate([
        'sender' => ['country' => 'FR'],
        'receiver' => ['country' => 'FR'],
        'parcels' => [['weight_grams' => 700]],
    ]);

    expect($rates[0]->carrier)->toBe(CarrierCode::LaPoste)
        ->and($rates[1]->carrier)->toBe(CarrierCode::Posti);
});
