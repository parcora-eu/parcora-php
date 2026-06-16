<?php

declare(strict_types=1);

namespace Parcora\Exception;

/** An upstream carrier rejected or failed the operation. See $carrierMessage. */
final class CarrierException extends ApiErrorException {}
