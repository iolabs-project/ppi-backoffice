<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceCharge extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Biaya tambahan invoice penjualan';

    protected $fillable = [
        'sales_invoice_id',
        'account_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
