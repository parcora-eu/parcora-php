<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** Too many requests (HTTP 429). See $retryAfter for the cool-off in seconds. */
final class RateLimitException extends ApiErrorException {}
