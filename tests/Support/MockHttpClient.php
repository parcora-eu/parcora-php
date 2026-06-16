<?php

declare(strict_types=1);

namespace Parcora\Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/** A PSR-18 client that replays queued responses and records the requests it saw. */
final class MockHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @var list<ResponseInterface> */
    private array $responses;

    /** @var \Closure|null */
    private $onRequest;

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = array_values($responses);
    }

    /** Throw a transport exception the first N times (to exercise retries). */
    public function onRequest(callable $callback): void
    {
        $this->onRequest = $callback(...);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if ($this->onRequest !== null) {
            ($this->onRequest)(\count($this->requests));
        }

        return array_shift($this->responses) ?? new Response(200, ['Content-Type' => 'application/json'], '{}');
    }

    public function lastRequest(): RequestInterface
    {
        $last = end($this->requests);

        if ($last === false) {
            throw new \RuntimeException('No request has been recorded yet.');
        }

        return $last;
    }
}
