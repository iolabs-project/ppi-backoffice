<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'warehouse_id',
        'sales_person_id',
        'number',
        'reference_number',
        'order_date',
        'due_date',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'shipping_charge',
        'other_charge',
        'subtotal',
        'down_payment_amount',
        'down_payment_remaining_amount',
        'down_payment_account_id',
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
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'tax_percentage' => 'double',
        'tax_amount' => 'double',
        'shipping_charge' => 'double',
        'other_charge' => 'double',
        'subtotal' => 'double',
        'down_payment_amount' => 'double',
        'down_payment_remaining_amount' => 'double',
        'total_amount' => 'double',
        'total_quantity' => 'double',
        'total_shipped_quantity' => 'double',
        'total_invoiced_quantity' => 'double',
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
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

    public function downPaymentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'down_payment_account_id');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class);
    }
}
