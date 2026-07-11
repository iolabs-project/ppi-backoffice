<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    // fillable
    protected $fillable = [
        'company_id',
        'sales_order_id',
        'customer_id',
        'warehouse_id',
        'number',
        'reference_number',
        'delivery_date',
        'status',
        'subtotal',
        'total_amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'delivery_date' => 'date:Y-m-d',
        'subtotal' => 'double',
        'total_amount' => 'double',
    ];

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }
}
