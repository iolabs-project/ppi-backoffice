<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCost extends Model
{
    protected $fillable = [
        'expense_id',
        'account_id',
        'description',
        'amount',
        'is_taxable',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_taxable' => 'boolean',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
