<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    //

    // fillable attributes
    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'number',
        'reference_number',
        'invoice_date',
        'due_date',
        'payment_terms',
        'status',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'down_payment_amount',
        'total_amount',
        'remaining_amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'tax_percentage' => 'double',
        'tax_amount' => 'double',
        'down_payment_amount' => 'double',
        'subtotal' => 'double',
        'total_amount' => 'double',
        'remaining_amount' => 'double',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Contact::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function costs()
    {
        return $this->hasMany(PurchaseInvoiceCost::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }
}
