<?php

namespace App\Enums;

enum InventoryTransactionTypeEnum: String
{
    case OPENING = 'opening';
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case ADJUSTMENT_PLUS = 'adjustment_plus';
    case ADJUSTMENT_MINUS = 'adjustment_minus';
    case COST_ADJUSTMENT = 'cost_adjustment';
}
