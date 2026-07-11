<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceCost extends Model
{
    protected $fillable = [
        'purchase_invoice_id',
        'account_id',
        'description',
        'amount',   
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
