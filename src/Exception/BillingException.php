<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** The action is blocked by billing state or quota (HTTP 402). */
final class BillingException extends ApiErrorException {}
