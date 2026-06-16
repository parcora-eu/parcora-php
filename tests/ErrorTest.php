<?php

declare(strict_types=1);

use Parcora\Exception\ApiErrorException;
use Parcora\Exception\BillingException;
use Parcora\Exception\CarrierException;
use Parcora\Exception\InvalidRequestException;
use Parcora\Exception\NotFoundException;
use Parcora\Exception\RateLimitException;
use Parcora\Tests\Support\MockHttpClient;

/** @return array<string, mixed> */
function errorBody(string $type, string $code, string $message, ?string $param = null, ?string $carrierMessage = null): array
{
    return ['error' => array_filter([
        'type' => $type, 'code' => $code, 'message' => $message,
        'param' => $param, 'carrier_message' => $carrierMessage, 'request_id' => 'req_9',
    ], fn ($v) => $v !== null)];
}

it('maps a validation error to InvalidRequestException with the offending param', function () {
    $http = new MockHttpClient(jsonResponse(422, errorBody('validation_error', 'parameter_invalid', 'The receiver postcode is invalid.', 'receiver.postcode')));

    $caught = null;
    try {
        parcora($http)->shipments->create([]);
    } catch (InvalidRequestException $e) {
        $caught = $e;
    }

    expect($caught)->toBeInstanceOf(InvalidRequestException::class)
        ->and($caught?->errorType)->toBe('validation_error')
        ->and($caught?->param)->toBe('receiver.postcode')
        ->and($caught?->httpStatus)->toBe(422)
        ->and($caught?->requestId)->toBe('req_9')
        ->and($caught?->getMessage())->toBe('The receiver postcode is invalid.');
});

it('maps a 404 to NotFoundException', function () {
    $http = new MockHttpClient(jsonResponse(404, errorBody('not_found', 'resource_missing', 'No such shipment.')));

    expect(fn () => parcora($http)->shipments->retrieve('shp_x'))->toThrow(NotFoundException::class);
});

it('maps a 429 to RateLimitException and reads Retry-After', function () {
    $http = new MockHttpClient(jsonResponse(429, errorBody('rate_limit_error', 'rate_limit_exceeded', 'Slow down.'), ['Retry-After' => '30']));

    $caught = null;
    try {
        parcora($http)->shipments->all();
    } catch (RateLimitException $e) {
        $caught = $e;
    }

    expect($caught)->toBeInstanceOf(RateLimitException::class)
        ->and($caught?->retryAfter)->toBe(30);
});

it('maps a 402 to BillingException', function () {
    $http = new MockHttpClient(jsonResponse(402, errorBody('billing_error', 'quota_exceeded', 'Quota used up.')));

    expect(fn () => parcora($http)->shipments->create([]))->toThrow(BillingException::class);
});

it('maps a carrier error and exposes carrier_message', function () {
    $http = new MockHttpClient(jsonResponse(422, errorBody('carrier_error', 'omniva_rejected', 'The carrier rejected the shipment.', 'service', 'unknown product code')));

    $caught = null;
    try {
        parcora($http)->shipments->create([]);
    } catch (CarrierException $e) {
        $caught = $e;
    }

    expect($caught?->carrierMessage)->toBe('unknown product code')
        ->and($caught)->toBeInstanceOf(ApiErrorException::class);
});
