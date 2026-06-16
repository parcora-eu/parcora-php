<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** The request never reached the API or no response came back (network/transport). */
final class ApiConnectionException extends ParcoraException {}
