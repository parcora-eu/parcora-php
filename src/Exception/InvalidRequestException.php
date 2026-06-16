<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** Invalid parameters were supplied (HTTP 422). See $param for the offending field. */
final class InvalidRequestException extends ApiErrorException {}
