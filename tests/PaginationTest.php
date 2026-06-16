<?php

declare(strict_types=1);

use Parcora\Tests\Support\MockHttpClient;

it('walks across pages with auto-paging, carrying starting_after', function () {
    $http = new MockHttpClient(
        listResponse([shipmentData('shp_1'), shipmentData('shp_2')], hasMore: true),
        listResponse([shipmentData('shp_3')], hasMore: false),
    );

    $ids = [];
    foreach (parcora($http)->shipments->all(['limit' => 2])->autoPaging() as $shipment) {
        $ids[] = $shipment->id;
    }

    expect($ids)->toBe(['shp_1', 'shp_2', 'shp_3'])
        ->and((string) $http->requests[0]->getUri())->toContain('limit=2')
        ->and((string) $http->requests[1]->getUri())->toContain('starting_after=shp_2');
});

it('exposes a single page as countable and iterable', function () {
    $http = new MockHttpClient(listResponse([shipmentData('shp_1')], hasMore: false));

    $page = parcora($http)->shipments->all();

    expect($page)->toHaveCount(1)
        ->and($page->first()?->id)->toBe('shp_1')
        ->and($page->hasMore)->toBeFalse()
        ->and(iterator_to_array($page))->toHaveCount(1);
});
