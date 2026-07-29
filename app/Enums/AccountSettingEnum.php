<?php

namespace App\Enums;

enum AccountSettingEnum: String
{
    case CASH = 'cash';
    case BANK = 'bank';
    case ACCOUNT_RECEIVABLE = 'account_receivable';
    case INVENTORY = 'inventory';
    case INPUT_TAX = 'input_tax';
    case PURCHASE_DOWN_PAYMENT = 'purchase_down_payment';
    case ACCOUNT_PAYABLE = 'account_payable';
    case GRNI = 'grni';
    case OUTPUT_TAX = 'output_tax';
    case SALES_DOWN_PAYMENT = 'sales_down_payment';
    case RETAINED_EARNINGS = 'retained_earnings';
    case OPENING_BALANCE_EQUITY = 'opening_balance_equity';
    case SALES_REVENUE = 'sales_revenue';
    case SALES_DISCOUNT = 'sales_discount';
    case SALES_RETURN = 'sales_return';
    case COGS = 'cogs';
    case PURCHASE_DISCOUNT = 'purchase_discount';
    case PURCHASE_RETURN = 'purchase_return';
    case INVENTORY_ADJUSTMENT_LOSS = 'inventory_adjustment_loss';
    case ROUNDING_GAIN = 'rounding_gain';
    case ROUNDING_LOSS = 'rounding_loss';
}
