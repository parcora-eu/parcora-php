<?php

declare(strict_types=1);

namespace Parcora\Exception;

/**
 * An error the API returned as the standard envelope:
 *
 *   { "error": { "type", "code", "message", "carrier_message"?, "param"?, "request_id" } }
 *
 * The concrete subclass is chosen from `error.type` so callers can catch the
 * category they care about (e.g. {@see RateLimitException}) or this base class
 * for any API error.
 */
class ApiErrorException extends ParcoraException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus,
        public readonly string $errorType,
        public readonly string $errorCode,
        public readonly ?string $param = null,
        public readonly ?string $carrierMessage = null,
        public readonly ?string $requestId = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message);
    }

    /**
     * Build the right exception from a decoded error envelope.
     *
     * @param  array<array-key, mixed>  $body  the decoded response body
     */
    public static function fromEnvelope(int $status, array $body, ?int $retryAfter = null): self
    {
        $error = \is_array($body['error'] ?? null) ? $body['error'] : [];

        $type = \is_string($error['type'] ?? null) ? $error['type'] : 'api_error';
        $code = \is_string($error['code'] ?? null) ? $error['code'] : 'error';
        $message = \is_string($error['message'] ?? null) ? $error['message'] : 'The Parcora API returned an error.';
        $param = \is_string($error['param'] ?? null) ? $error['param'] : null;
        $carrierMessage = \is_string($error['carrier_message'] ?? null) ? $error['carrier_message'] : null;
        $requestId = \is_string($error['request_id'] ?? null) ? $error['request_id'] : null;

        $class = match ($type) {
            'validation_error' => InvalidRequestException::class,
            'authentication_error' => AuthenticationException::class,
            'permission_error' => PermissionException::class,
            'not_found' => NotFoundException::class,
            'rate_limit_error' => RateLimitException::class,
            'carrier_error' => CarrierException::class,
            'idempotency_error' => IdempotencyException::class,
            'billing_error' => BillingException::class,
            default => ApiException::class,
        };

        return new $class($message, $status, $type, $code, $param, $carrierMessage, $requestId, $retryAfter);
    }
}
