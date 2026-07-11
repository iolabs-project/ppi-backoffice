<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'number',
        'reference_number',
        'receipt_date',
        'status',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'note',
        'created_by',
    ];

    // cast attributes to specific types
    protected $casts = [
        'receipt_date' => 'date:Y-m-d',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'subtotal' => 'double',
        'total_amount' => 'double',
        'total_received_quantity' => 'double',
        'total_shrinkage_quantity' => 'double',
    ];

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function costs()
    {
        return $this->hasMany(GoodsReceiptCost::class);
    }

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
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
