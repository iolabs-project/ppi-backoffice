<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'company_id',
        'supplier_id',
        'warehouse_id',
        'sales_person_id',
        'number',
        'reference_number',
        'order_date',
        'due_date',
        'discount_amount',
        'transport_cost',
        'other_cost',
        'subtotal',
        'total_amount',
        'note',
        'payment_terms',
        'status',
        'created_by',
    ];

    // cast attributes to specific types
    protected $casts = [
        'order_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'discount_amount' => 'double',
        'transport_cost' => 'double',
        'other_cost' => 'double',
        'subtotal' => 'double',
        'total_amount' => 'double',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Contact::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(Contact::class, 'sales_person_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
