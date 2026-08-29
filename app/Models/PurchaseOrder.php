<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'company_id',
        'supplier_id',
        'warehouse_id',
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
        'total_received_quantity' => 'double',
        'total_invoiced_quantity' => 'double',
    ];

    protected $appends = [
        'is_cancellable',
        'is_receivable',
        'is_invoicable',
    ];

    public function getIsCancellableAttribute()
    {
        return $this->status === PurchaseOrderStatus::DRAFT->value || ($this->status === PurchaseOrderStatus::OPEN->value && $this->goodsReceipts->isEmpty());
    }

    public function getIsReceivableAttribute()
    {
        return $this->status === PurchaseOrderStatus::OPEN->value && $this->total_received_quantity < $this->total_quantity;
    }

    public function getIsInvoicableAttribute()
    {
        return (!in_array($this->status, [PurchaseOrderStatus::DRAFT->value, PurchaseOrderStatus::CANCELLED->value])) && $this->total_invoiced_quantity < $this->total_received_quantity;
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function costs()
    {
        return $this->hasMany(PurchaseOrderCost::class);
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

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class)->where('status', '!=', 'cancelled');
    }

    public function downPaymentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'down_payment_account_id');
    }

    #[Scope]
    protected function invoicable(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereHas('items', function ($q) {
                $q->whereColumn('invoiced_quantity', '<', 'received_quantity');
            })
                ->whereNotIn('status', [PurchaseOrderStatus::DRAFT->value, PurchaseOrderStatus::CANCELLED->value]);
        });
    }

    #[Scope]
    protected function receivable(Builder $query): void
    {
        $query->where(function ($q) {
            $q->where('status', PurchaseOrderStatus::OPEN->value)
                ->whereHas('items', function ($q) {
                    $q->whereColumn('received_quantity', '<', 'quantity');
                });
        });
    }
}
