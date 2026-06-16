<?php

declare(strict_types=1);

namespace Parcora\Service;

use Parcora\Model\Collection;
use Parcora\Model\WebhookEndpoint;

/** `/v1/webhook_endpoints` — register and manage webhook endpoints. */
final class WebhookEndpointService extends AbstractService
{
    /**
     * @param  array<string, mixed>  $params  e.g. ['url' => '…', 'events' => ['shipment.delivered']]
     * @param  array<string, mixed>  $opts
     */
    public function create(array $params, array $opts = []): WebhookEndpoint
    {
        return WebhookEndpoint::fromArray($this->requestor->request('POST', 'webhook_endpoints', null, $params, $opts)->data);
    }

    /** @param array<string, mixed> $opts */
    public function retrieve(string $id, array $opts = []): WebhookEndpoint
    {
        return WebhookEndpoint::fromArray($this->requestor->request('GET', 'webhook_endpoints/'.$id, null, null, $opts)->data);
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $opts
     * @return Collection<WebhookEndpoint>
     */
    public function all(array $params = [], array $opts = []): Collection
    {
        $body = $this->requestor->request('GET', 'webhook_endpoints', $params, null, $opts)->data;

        return new Collection(
            array_map(WebhookEndpoint::fromArray(...), $this->rows($body)),
            $this->hasMore($body),
            fn (WebhookEndpoint $last): Collection => $this->all([...$params, 'starting_after' => $last->id], $opts),
        );
    }

    /**
     * @param  array<string, mixed>  $opts
     * @return array<array-key, mixed> the `{ id, object, deleted: true }` confirmation
     */
    public function delete(string $id, array $opts = []): array
    {
        return $this->requestor->request('DELETE', 'webhook_endpoints/'.$id, null, null, $opts)->data;
    }
}
