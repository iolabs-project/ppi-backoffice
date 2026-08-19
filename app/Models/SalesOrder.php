<?php

namespace App\Models;

use App\Enums\SalesOrderStatus;
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
        'subtotal' => 'double',
        'down_payment_amount' => 'double',
        'down_payment_remaining_amount' => 'double',
        'total_amount' => 'double',
        'total_quantity' => 'double',
        'total_shipped_quantity' => 'double',
        'total_invoiced_quantity' => 'double',
    ];

    protected $appends = [
        'is_cancellable',
        'is_deliverable',
        'is_invoicable',
    ];

    public function getIsCancellableAttribute()
    {
        return $this->status === SalesOrderStatus::DRAFT->value || ($this->status === SalesOrderStatus::OPEN->value && $this->deliveryOrders->isEmpty());
    }

    public function getIsDeliverableAttribute()
    {
        return $this->status === SalesOrderStatus::OPEN->value;
    }

    public function getIsInvoicableAttribute()
    {
        return ($this->status === SalesOrderStatus::OPEN->value || $this->status === SalesOrderStatus::CLOSED->value) && $this->total_invoiced_quantity < $this->total_shipped_quantity;
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function charges()
    {
        return $this->hasMany(SalesOrderCharge::class);
    }

    public function costs()
    {
        return $this->hasMany(SalesOrderCost::class);
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
