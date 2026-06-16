<?php

declare(strict_types=1);

use Parcora\Exception\SignatureVerificationException;
use Parcora\Webhook;

function signedHeader(string $payload, string $secret, ?int $timestamp = null): string
{
    $timestamp ??= time();

    return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
}

it('constructs an event from a valid signature', function () {
    $payload = (string) json_encode([
        'id' => 'evt_1', 'object' => 'event', 'type' => 'shipment.delivered',
        'created' => 1700000000, 'livemode' => false,
        'data' => ['object' => ['id' => 'shp_1', 'object' => 'shipment', 'status' => 'delivered']],
    ]);

    $event = Webhook::constructEvent($payload, signedHeader($payload, 'whsec_x'), 'whsec_x');

    expect($event->id)->toBe('evt_1')
        ->and($event->type)->toBe('shipment.delivered')
        ->and($event->livemode)->toBeFalse()
        ->and($event->created?->getTimestamp())->toBe(1700000000)
        ->and($event->data['id'])->toBe('shp_1');
});

it('rejects a tampered signature', function () {
    expect(fn () => Webhook::constructEvent('{"id":"evt_1"}', 't='.time().',v1='.str_repeat('0', 64), 'whsec_x'))
        ->toThrow(SignatureVerificationException::class);
});

it('rejects a timestamp outside the tolerance window', function () {
    $payload = '{}';
    $old = time() - 10_000;

    expect(fn () => Webhook::constructEvent($payload, signedHeader($payload, 'whsec_x', $old), 'whsec_x'))
        ->toThrow(SignatureVerificationException::class);
});

it('rejects a malformed signature header', function () {
    expect(fn () => Webhook::constructEvent('{}', 'not-a-signature', 'whsec_x'))
        ->toThrow(SignatureVerificationException::class);
});
