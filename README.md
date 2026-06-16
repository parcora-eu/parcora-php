# Parcora PHP

[![CI](https://github.com/parcora-eu/parcora-php/actions/workflows/ci.yml/badge.svg)](https://github.com/parcora-eu/parcora-php/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/parcora/parcora-php.svg)](https://packagist.org/packages/parcora/parcora-php)
[![PHP Version](https://img.shields.io/packagist/php-v/parcora/parcora-php.svg)](https://packagist.org/packages/parcora/parcora-php)
[![License](https://img.shields.io/packagist/l/parcora/parcora-php.svg)](LICENSE)

The official PHP SDK for the [Parcora](https://parcora.eu) shipping API — one REST
API for parcel delivery across Europe: shipments, bulk batches, labels, lockers,
rates, tracking and webhooks.

- Typed, read-only models and enums — full IDE autocomplete, no array guessing.
- Works with **any** [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client.
- Automatic idempotency keys on writes, cursor auto-pagination, and a typed
  exception per error category.

## Requirements

- PHP 8.2+
- A PSR-18 HTTP client and PSR-17 factories (e.g. `guzzlehttp/guzzle` or
  `symfony/http-client` + `nyholm/psr7`).

## Installation

```bash
composer require parcora/parcora-php guzzlehttp/guzzle
```

Any PSR-18 client works; Guzzle is just the easiest to start with. The SDK
auto-discovers an installed client, or you can inject your own (see
[Configuration](#configuration)).

## Quick start

```php
$client = new \Parcora\Client('dp_test_…'); // your secret API key

$shipment = $client->shipments->create([
    'carrier' => 'omniva',
    'service' => 'omniva.courier',
    'sender'   => ['name' => 'Shop', 'line1' => 'Gedimino 1', 'city' => 'Vilnius', 'postcode' => '01103', 'country' => 'LT'],
    'receiver' => ['name' => 'Anna', 'line1' => 'Brivibas 1', 'city' => 'Riga', 'postcode' => 'LV-1010', 'country' => 'LV'],
    'parcels'  => [['weight_grams' => 1000]],
]);

echo $shipment->id;                 // shp_…
echo $shipment->status->value;      // "registered"  (ShipmentStatus enum)
echo $shipment->trackingCode;       // dp_trk_…

file_put_contents('label.pdf', $client->shipments->label($shipment->id, ['format' => 'pdf_a4']));
```

Use a `dp_test_…` key for test mode and `dp_live_…` for production — same code,
different key.

## Resources

### Shipments

```php
$client->shipments->create([...]);
$client->shipments->retrieve('shp_123');
$client->shipments->cancel('shp_123');
$client->shipments->addLeg('shp_123', ['type' => 'return', 'carrier' => 'omniva', 'service' => 'omniva.courier']);
$client->shipments->label('shp_123', ['format' => 'pdf_a4']); // raw bytes
$client->shipments->slip('shp_123');                          // raw PDF bytes
$client->shipments->trackingEvents('shp_123');                // list<TrackingEvent>

foreach ($client->shipments->all(['status' => 'in_transit'])->autoPaging() as $shipment) {
    // …every shipment across every page
}
```

### Bulk batches

```php
$batch = $client->shipmentBatches->create([
    'shipments' => [ [...], [...], [...] ], // same shape as shipments->create
]);

$batch = $client->shipmentBatches->retrieve($batch->id);
echo $batch->status->value;          // processing → completed / completed_with_errors
echo $batch->counts->succeeded;

foreach ($client->shipmentBatches->items($batch->id)->autoPaging() as $item) {
    echo $item->status->value;       // succeeded / failed
    echo $item->shipment ?? $item->error?->message;
}

file_put_contents('labels.pdf', $client->shipmentBatches->labels($batch->id)); // merged sheet
```

### Carriers, pickup points, rates, trackers

```php
foreach ($client->carriers->all() as $carrier) {
    echo $carrier->carrier->value, $carrier->connected ? ' ✓' : '';
}

$client->pickupPoints->all(['carrier' => 'omniva', 'country' => 'EE']);
$client->rates->calculate(['sender' => [...], 'receiver' => [...], 'parcels' => [...]]); // list<Rate>
$client->trackers->retrieve('dp_trk_…'); // Tracker with its event timeline
```

### Webhook endpoints

```php
$endpoint = $client->webhookEndpoints->create([
    'url'    => 'https://example.com/webhooks/parcora',
    'events' => ['shipment.delivered', 'shipment_batch.completed'],
]);
echo $endpoint->secret; // shown once — store it to verify deliveries

$client->webhookEndpoints->all();
$client->webhookEndpoints->retrieve($endpoint->id);
$client->webhookEndpoints->delete($endpoint->id);
```

## Pagination

List methods return a `Collection`. Use it directly for one page, or
`->autoPaging()` to stream every page lazily (the SDK passes `starting_after`
for you):

```php
$page = $client->shipments->all(['limit' => 100]);
count($page);          // items on this page
$page->hasMore;        // is there another page?
$page->first();

foreach ($page->autoPaging() as $shipment) { /* all pages */ }
```

## Errors

Every API error throws a typed exception extending
`Parcora\Exception\ApiErrorException`, chosen from the error `type`:

| Exception | When |
|---|---|
| `InvalidRequestException` | `validation_error` (422) — see `->param` |
| `AuthenticationException` | `authentication_error` (401) |
| `PermissionException` | `permission_error` (403) |
| `NotFoundException` | `not_found` (404) |
| `RateLimitException` | `rate_limit_error` (429) — see `->retryAfter` |
| `BillingException` | `billing_error` (402) |
| `CarrierException` | `carrier_error` — see `->carrierMessage` |
| `IdempotencyException` | `idempotency_error` |
| `ApiException` | server / unexpected errors |
| `ApiConnectionException` | the request never reached the API |

```php
use Parcora\Exception\InvalidRequestException;
use Parcora\Exception\ApiErrorException;

try {
    $client->shipments->create([...]);
} catch (InvalidRequestException $e) {
    echo $e->param;        // "receiver.postcode"
    echo $e->getMessage();
} catch (ApiErrorException $e) {
    echo $e->errorType, $e->errorCode, $e->requestId;
}
```

## Idempotency

Writes (`POST`) automatically carry an `Idempotency-Key` so transient network
retries can't double-create. Pass your own to make a specific call replay-safe:

```php
$client->shipments->create([...], ['idempotency_key' => 'order-12345']);
```

## Verifying webhooks

```php
use Parcora\Webhook;
use Parcora\Exception\SignatureVerificationException;

try {
    $event = Webhook::constructEvent(
        $payload,                              // raw request body
        $request->header('Webhook-Signature'), // the signature header
        $endpointSecret,                       // from webhookEndpoints->create()->secret
    );
} catch (SignatureVerificationException $e) {
    http_response_code(400);
    exit;
}

echo $event->type;          // "shipment.delivered"
echo $event->data['id'];    // the event's data.object
```

## Configuration

```php
$client = new \Parcora\Client('dp_live_…', [
    'base_url'            => 'https://api.parcora.eu', // override for self-hosted
    'http_client'         => $myPsr18Client,           // inject any PSR-18 client
    'request_factory'     => $myPsr17RequestFactory,
    'stream_factory'      => $myPsr17StreamFactory,
    'max_network_retries' => 2,                        // retry transport / 5xx / 429
]);
```

## Development

```bash
composer install
composer test    # pest
composer stan    # phpstan (max)
composer lint    # pint --test
```

## License

MIT — see [LICENSE](LICENSE).
