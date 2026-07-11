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
    ];

    protected $casts = [
        'amount' => 'double',
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
