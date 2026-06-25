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

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
