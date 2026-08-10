<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'to_account_id',
        'from_account_id',
        'contact_id',
        'reference_type',
        'reference_id',
        'number',
        'reference_number',
        'transaction_date',
        'type',
        'status',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date:Y-m-d',
        'subtotal' => 'double',
        'tax_percentage' => 'double',
        'tax_amount' => 'double',
        'total_amount' => 'double',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function toAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'to_account_id');
    }

    public function fromAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
