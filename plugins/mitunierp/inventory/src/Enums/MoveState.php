<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Enums;

enum MoveState: string
{
    case Draft = 'draft';
    case Waiting = 'waiting';
    case Confirmed = 'confirmed';
    case Assigned = 'assigned';
    case PartiallyAssigned = 'partially_assigned';
    case Done = 'done';
    case Canceled = 'canceled';
}
