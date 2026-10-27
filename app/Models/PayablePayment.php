<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class PayablePayment extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'visible';

    protected string $activityLogSubject = 'Pembayaran hutang';
    protected $fillable = [
        'company_id',
        'account_id',
        'reference_type',
        'reference_id',
        'number',
        'payment_date',
        'payment_method',
        'reference_number',
        'amount',
        'note',
        'voided_at',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'amount' => 'double',
        'voided_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
