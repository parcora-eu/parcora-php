<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** No valid API key was provided (HTTP 401). */
final class AuthenticationException extends ApiErrorException {}
