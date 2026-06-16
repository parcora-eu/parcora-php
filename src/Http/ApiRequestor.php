<?php

declare(strict_types=1);

namespace Parcora\Http;

use Parcora\Exception\ApiConnectionException;
use Parcora\Exception\ApiErrorException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Random\RandomException;

/**
 * Turns method/path/params into a signed HTTP request, sends it through any
 * PSR-18 client, and maps the result into a {@see Response} or the right
 * {@see ApiErrorException}. Adds a per-call Idempotency-Key to POSTs so network
 * retries are safe, and retries idempotent transport failures.
 */
final class ApiRequestor
{
    private const VERSION_PREFIX = '/v1';

    /** @var list<int> */
    private const RETRY_STATUSES = [429, 500, 502, 503, 504];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly ClientInterface $http,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly int $maxNetworkRetries,
        private readonly string $userAgent,
    ) {}

    /**
     * @param  array<string, mixed>|null  $query
     * @param  array<string, mixed>|null  $body
     * @param  array<string, mixed>  $opts  supports `idempotency_key` and `headers`
     */
    public function request(string $method, string $path, ?array $query = null, ?array $body = null, array $opts = []): Response
    {
        $response = $this->send($this->build($method, $path, $query, $body, $opts));

        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();
        $requestId = $response->getHeaderLine('X-Request-Id') ?: null;

        $data = [];
        if ($raw !== '' && str_contains($response->getHeaderLine('Content-Type'), 'application/json')) {
            $decoded = json_decode($raw, true);
            $data = \is_array($decoded) ? $decoded : [];
        }

        if ($status >= 400) {
            $retryAfter = $response->getHeaderLine('Retry-After');

            throw ApiErrorException::fromEnvelope($status, $data, $retryAfter !== '' ? (int) $retryAfter : null);
        }

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[(string) $name] = array_values($values);
        }

        return new Response(
            status: $status,
            data: $data,
            headers: $headers,
            body: $raw,
            requestId: $requestId,
            idempotentReplayed: strtolower($response->getHeaderLine('Idempotency-Replayed')) === 'true',
        );
    }

    /**
     * @param  array<string, mixed>|null  $query
     * @param  array<string, mixed>|null  $body
     * @param  array<string, mixed>  $opts
     */
    private function build(string $method, string $path, ?array $query, ?array $body, array $opts): RequestInterface
    {
        $url = rtrim($this->baseUrl, '/').self::VERSION_PREFIX.'/'.ltrim($path, '/');

        if ($query !== null && $query !== []) {
            $url .= '?'.http_build_query($query);
        }

        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Authorization', 'Bearer '.$this->apiKey)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', $this->userAgent);

        if ($body !== null) {
            $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }

        $idempotencyKey = $opts['idempotency_key'] ?? null;
        if (! \is_string($idempotencyKey) && strtoupper($method) === 'POST') {
            $idempotencyKey = self::generateIdempotencyKey();
        }
        if (\is_string($idempotencyKey)) {
            $request = $request->withHeader('Idempotency-Key', $idempotencyKey);
        }

        $headers = $opts['headers'] ?? null;
        if (\is_array($headers)) {
            foreach ($headers as $name => $value) {
                if (\is_string($name) && (\is_scalar($value) || $value === null)) {
                    $request = $request->withHeader($name, (string) $value);
                }
            }
        }

        return $request;
    }

    private function send(RequestInterface $request): ResponseInterface
    {
        for ($attempt = 0; ; $attempt++) {
            try {
                $response = $this->http->sendRequest($request);
            } catch (ClientExceptionInterface $e) {
                if ($attempt < $this->maxNetworkRetries) {
                    $this->backoff($attempt + 1);

                    continue;
                }

                throw new ApiConnectionException('Could not reach the Parcora API: '.$e->getMessage(), previous: $e);
            }

            if ($attempt < $this->maxNetworkRetries && \in_array($response->getStatusCode(), self::RETRY_STATUSES, true)) {
                $this->backoff($attempt + 1);

                continue;
            }

            return $response;
        }
    }

    private function backoff(int $attempt): void
    {
        usleep((int) (min(0.5 * 2 ** ($attempt - 1), 5.0) * 1_000_000));
    }

    private static function generateIdempotencyKey(): string
    {
        try {
            return 'idem_'.bin2hex(random_bytes(16));
        } catch (RandomException) {
            return 'idem_'.bin2hex(pack('N*', ...array_map('crc32', str_split(uniqid('', true)))));
        }
    }
}
