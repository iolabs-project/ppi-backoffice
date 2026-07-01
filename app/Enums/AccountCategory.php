<?php

namespace App\Enums;

enum AccountCategory: int
{
    case CASH_BANK = 1;
    case ACCOUNT_RECEIVABLE = 2;
    case INVENTORY = 3;
    case OTHER_CURRENT_ASSET = 4;
    case FIXED_ASSET = 5;
    case ACCUMULATED_DEPRECIATION_AMORTIZATION = 6;
    case OTHER_ASSET = 7;
    case ACCOUNT_PAYABLE = 8;
    case OTHER_CURRENT_LIABILITY = 9;
    case LONG_TERM_LIABILITY = 10;
    case EQUITY = 11;
    case REVENUE = 12;
    case COST_OF_GOODS_SOLD = 13;
    case OPERATING_EXPENSE = 14;
    case OTHER_INCOME = 15;
    case OTHER_EXPENSE = 16;
    case CREDIT_CARD = 17;
}
