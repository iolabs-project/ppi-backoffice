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
        'is_inventory_cost',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_inventory_cost' => 'boolean',
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
