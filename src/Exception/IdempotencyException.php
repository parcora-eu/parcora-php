<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** An Idempotency-Key was reused with a different request. */
final class IdempotencyException extends ApiErrorException {}
