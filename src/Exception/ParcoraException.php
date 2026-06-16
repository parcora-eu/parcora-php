<?php

declare(strict_types=1);

namespace Parcora\Exception;

use Exception;

/** Base class for every SDK exception. */
class ParcoraException extends Exception implements ExceptionInterface {}
