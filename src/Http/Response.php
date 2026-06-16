<?php

declare(strict_types=1);

namespace Parcora\Http;

/** A successful, decoded API response. Binary endpoints expose bytes via {@see $body}. */
final class Response
{
    /**
     * @param  array<array-key, mixed>  $data  the decoded JSON body (empty for binary responses)
     * @param  array<string, array<int, string>>  $headers
     */
    public function __construct(
        public readonly int $status,
        public readonly array $data,
        public readonly array $headers,
        public readonly string $body,
        public readonly ?string $requestId = null,
        public readonly bool $idempotentReplayed = false,
    ) {}

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $values) {
            if (strcasecmp($key, $name) === 0) {
                return $values[0] ?? null;
            }
        }

        return null;
    }
}
