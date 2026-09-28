<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Letterhead for printed documents
    |--------------------------------------------------------------------------
    |
    | Shown on invoices, orders, delivery notes and the other printable
    | documents. The companies table has no contact details yet, so they
    | live here and can be overridden per environment.
    |
    */

    'letterhead' => [
        'name' => env('COMPANY_NAME', 'CV Putra Pangan Indonesia'),
        'address' => env('COMPANY_ADDRESS', 'Plaza Segi 8 Blok C850'),
        'phone' => env('COMPANY_PHONE', '081252304070'),
        'email' => env('COMPANY_EMAIL', 'putrapanganindonesia@gmail.com'),
    ],

];
