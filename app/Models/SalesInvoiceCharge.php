<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceCharge extends Model
{
    protected $fillable = [
        'sales_invoice_id',
        'account_id',
        'description',
        'amount',
        'is_taxable',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_taxable' => 'boolean',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
