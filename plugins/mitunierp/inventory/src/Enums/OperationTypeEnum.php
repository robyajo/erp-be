<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Enums;

enum OperationTypeEnum: string
{
    case Receipt = 'receipt';
    case Delivery = 'delivery';
    case InternalTransfer = 'internal_transfer';
}
