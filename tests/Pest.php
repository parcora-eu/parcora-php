<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Parcora\Client;
use Parcora\Tests\Support\MockHttpClient;

/** Build a Client wired to a mock HTTP client. */
function parcora(MockHttpClient $http): Client
{
    $factory = new HttpFactory;

    return new Client('dp_test_123', [
        'http_client' => $http,
        'request_factory' => $factory,
        'stream_factory' => $factory,
        'base_url' => 'https://api.test',
    ]);
}

/**
 * @param  array<string, mixed>  $body
 * @param  array<string, string>  $headers
 */
function jsonResponse(int $status, array $body, array $headers = []): Response
{
    return new Response(
        $status,
        ['Content-Type' => 'application/json', 'X-Request-Id' => 'req_test', ...$headers],
        (string) json_encode($body),
    );
}

/**
 * A minimal shipment object for list/create fixtures.
 *
 * @return array<string, mixed>
 */
function shipmentData(string $id, string $status = 'registered'): array
{
    return [
        'id' => $id,
        'object' => 'shipment',
        'livemode' => false,
        'tracking_code' => 'dp_trk_'.$id,
        'status' => $status,
        'carrier' => 'omniva',
        'tracking_number' => 'TEST'.$id,
        'metadata' => [],
        'legs' => [],
        'created' => '2026-06-16T09:00:00+00:00',
    ];
}

/** @param list<array<string, mixed>> $body */
function listResponse(array $body, bool $hasMore = false): Response
{
    return jsonResponse(200, ['object' => 'list', 'has_more' => $hasMore, 'data' => $body]);
}
