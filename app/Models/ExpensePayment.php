<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpensePayment extends Model
{
    protected $fillable = [
        'company_id',
        'expense_id',
        'account_id',
        'number',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'note',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'double',
        'payment_date' => 'date:Y-m-d',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
