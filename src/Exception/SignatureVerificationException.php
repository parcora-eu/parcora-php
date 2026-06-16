<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** A webhook payload could not be verified against the signing secret. */
final class SignatureVerificationException extends ParcoraException {}
