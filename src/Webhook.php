<?php

declare(strict_types=1);

namespace Parcora;

use Parcora\Exception\SignatureVerificationException;
use Parcora\Model\Event;

/**
 * Verifies inbound webhook signatures and parses the payload into an {@see Event}.
 *
 * The `Webhook-Signature` header is `t=<unix>,v1=<hmac>`, where
 * v1 = HMAC-SHA256(secret, "{t}.{raw body}").
 *
 *   $event = \Parcora\Webhook::constructEvent(
 *       $request->getContent(),
 *       $request->header('Webhook-Signature'),
 *       $endpointSecret,
 *   );
 */
final class Webhook
{
    public const DEFAULT_TOLERANCE = 300;

    /**
     * Verify the signature and return the parsed event.
     *
     * @throws SignatureVerificationException
     */
    public static function constructEvent(string $payload, string $signatureHeader, string $secret, int $tolerance = self::DEFAULT_TOLERANCE): Event
    {
        self::verifySignature($payload, $signatureHeader, $secret, $tolerance);

        $decoded = json_decode($payload, true);

        if (! \is_array($decoded)) {
            throw new SignatureVerificationException('Webhook payload is not valid JSON.');
        }

        return Event::fromArray($decoded);
    }

    /**
     * Verify a payload against the signature header, throwing on any mismatch.
     *
     * @throws SignatureVerificationException
     */
    public static function verifySignature(string $payload, string $signatureHeader, string $secret, int $tolerance = self::DEFAULT_TOLERANCE): void
    {
        if (preg_match('/^t=(\d+),v1=([0-9a-f]{64})$/', $signatureHeader, $matches) !== 1) {
            throw new SignatureVerificationException('Unexpected signature header format.');
        }

        $timestamp = (int) $matches[1];
        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        if (! hash_equals($expected, $matches[2])) {
            throw new SignatureVerificationException('Signature does not match the expected value.');
        }

        if ($tolerance > 0 && abs(time() - $timestamp) > $tolerance) {
            throw new SignatureVerificationException('Timestamp outside the tolerance window.');
        }
    }
}
