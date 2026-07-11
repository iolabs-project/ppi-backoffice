<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'sales_order_id',
        'customer_id',
        'sales_person_id',
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
        'tax_amount',,
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

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(Contact::class, 'sales_person_id');
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
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function charges()
    {
        return $this->hasMany(SalesInvoiceCharge::class);
    }
}
