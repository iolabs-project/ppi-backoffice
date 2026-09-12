<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Expense extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('visible')
            ->setDescriptionForEvent(function (string $eventName) {
                $event = match ($eventName) {
                    'created' => 'dibuat',
                    'updated' => 'diperbarui',
                    'deleted' => 'dihapus',
                    default => $eventName,
                };
                return "Biaya {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'company_id',
        'contact_id',
        'account_id',
        'number',
        'reference_number',
        'expense_date',
        'due_date',
        'payment_terms',
        'status',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'remaining_amount',
        'note',
        'created_by'
    ];

    protected $casts = [
        'expense_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'tax_percentage' => 'double',
        'tax_amount' => 'double',
        'subtotal' => 'double',
        'total_amount' => 'double',
        'remaining_amount' => 'double',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function costs()
    {
        return $this->hasMany(ExpenseCost::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
