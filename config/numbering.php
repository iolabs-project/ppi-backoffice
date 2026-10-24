<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Transaction document numbers
    |--------------------------------------------------------------------------
    |
    | Format: {PREFIX}-{COMPANY CODE}-{YEAR}-{COUNTER}, e.g. "SO-PPI-2026-0001".
    | The counter restarts every year and is counted per company.
    |
    */

    'documents' => [
        'padding' => 4,
        'prefixes' => [
            'sales_order' => 'SO',
            'delivery_order' => 'DO',
            'sales_invoice' => 'SI',
            'purchase_order' => 'PO',
            'goods_receipt' => 'GR',
            'purchase_invoice' => 'PI',
            'account_receivable' => 'AR',
            'account_payable' => 'AP',
            'cash' => 'CASH',
            'expense' => 'EXP',
            'journal' => 'JNL',
            'stock_adjustment' => 'SA',
            'warehouse_transfer' => 'WT',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Master data codes
    |--------------------------------------------------------------------------
    |
    | Format: {PREFIX}-{COUNTER}, e.g. "PRD-001". Used when the code field is
    | left empty. The counter continues from the highest existing code with
    | the same prefix in the selected company.
    |
    */

    'master' => [
        'padding' => 3,
        'prefixes' => [
            'product' => 'PRD',
            'warehouse' => 'WH',
            'contact' => 'C',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product batch prefix and batch numbers
    |--------------------------------------------------------------------------
    |
    | Format: {PRODUCT CODE}-{DATE}-{COUNTER}, e.g. "PRD-001-261025-01".
    | The date is the goods receipt date (PHP date format) and the counter
    | restarts per product per day.
    |
    | prefix_length / fallback_prefix only fill the legacy products.batch_prefix
    | column (derived from the name, e.g. "Katul" → "KTL"); batch numbers no
    | longer use it.
    |
    */

    'batch' => [
        'date_format' => 'ymd',
        'padding' => 2,
        'prefix_length' => 3,
        'fallback_prefix' => 'PRD',
    ],

];
