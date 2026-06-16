<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** The outcome of a single item within a batch. */
enum BatchItemStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
