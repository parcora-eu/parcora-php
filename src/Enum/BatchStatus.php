<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** The rolled-up status of a shipment batch. */
enum BatchStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case CompletedWithErrors = 'completed_with_errors';
    case Failed = 'failed';
}
