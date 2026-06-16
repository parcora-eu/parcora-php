<?php

declare(strict_types=1);

namespace Parcora;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use Parcora\Http\ApiRequestor;
use Parcora\Service\CarrierService;
use Parcora\Service\PickupPointService;
use Parcora\Service\RateService;
use Parcora\Service\ShipmentBatchService;
use Parcora\Service\ShipmentService;
use Parcora\Service\TrackerService;
use Parcora\Service\WebhookEndpointService;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * The Parcora API client.
 *
 *   $client = new \Parcora\Client('dp_live_…');
 *   $shipment = $client->shipments->create([...]);
 *
 * Any PSR-18 HTTP client is used; one is auto-discovered unless you pass your
 * own in `$config['http_client']`.
 */
final class Client
{
    public const VERSION = '0.1.0';

    public const DEFAULT_BASE_URL = 'https://api.parcora.eu';

    public readonly ShipmentService $shipments;

    public readonly ShipmentBatchService $shipmentBatches;

    public readonly CarrierService $carriers;

    public readonly PickupPointService $pickupPoints;

    public readonly RateService $rates;

    public readonly TrackerService $trackers;

    public readonly WebhookEndpointService $webhookEndpoints;

    /**
     * @param  string  $apiKey  a `dp_live_…` or `dp_test_…` secret key
     * @param  array{
     *     base_url?: string,
     *     http_client?: ClientInterface,
     *     request_factory?: RequestFactoryInterface,
     *     stream_factory?: StreamFactoryInterface,
     *     max_network_retries?: int,
     * }  $config
     */
    public function __construct(string $apiKey, array $config = [])
    {
        if (trim($apiKey) === '') {
            throw new InvalidArgumentException('A Parcora API key is required.');
        }

        $requestor = new ApiRequestor(
            apiKey: $apiKey,
            baseUrl: $config['base_url'] ?? self::DEFAULT_BASE_URL,
            http: $config['http_client'] ?? Psr18ClientDiscovery::find(),
            requestFactory: $config['request_factory'] ?? Psr17FactoryDiscovery::findRequestFactory(),
            streamFactory: $config['stream_factory'] ?? Psr17FactoryDiscovery::findStreamFactory(),
            maxNetworkRetries: $config['max_network_retries'] ?? 0,
            userAgent: 'parcora-php/'.self::VERSION.' php/'.PHP_VERSION,
        );

        $this->shipments = new ShipmentService($requestor);
        $this->shipmentBatches = new ShipmentBatchService($requestor);
        $this->carriers = new CarrierService($requestor);
        $this->pickupPoints = new PickupPointService($requestor);
        $this->rates = new RateService($requestor);
        $this->trackers = new TrackerService($requestor);
        $this->webhookEndpoints = new WebhookEndpointService($requestor);
    }
}
