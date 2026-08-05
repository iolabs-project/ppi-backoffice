<?php
namespace App\Enums;

use App\Models\SalesInvoice;

enum ReceivablePaymentReferenceTypeEnum: string
{
    CASE SALES_INVOICE = 'sales_invoice';

    public function model(): string
    {
        return match ($this) {
            self::SALES_INVOICE => SalesInvoice::class,
        };
    }
}