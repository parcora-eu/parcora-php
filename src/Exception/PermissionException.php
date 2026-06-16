<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** The API key lacks permission for this action (HTTP 403). */
final class PermissionException extends ApiErrorException {}
