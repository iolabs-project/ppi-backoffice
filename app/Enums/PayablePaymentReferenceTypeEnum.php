<?php
namespace App\Enums;

use App\Models\Expense;
use App\Models\PurchaseInvoice;

enum PayablePaymentReferenceTypeEnum: string
{
    CASE PURCHASE_INVOICE = 'purchase_invoice';
    CASE EXPENSE = 'expense';

    public function model(): string
    {
        return match ($this) {
            self::PURCHASE_INVOICE => PurchaseInvoice::class,
            self::EXPENSE => Expense::class, 
        };
    }
}