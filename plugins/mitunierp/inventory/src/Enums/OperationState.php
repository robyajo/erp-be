<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Enums;

enum OperationState: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Assigned = 'assigned';
    case Done = 'done';
    case Canceled = 'canceled';
}
