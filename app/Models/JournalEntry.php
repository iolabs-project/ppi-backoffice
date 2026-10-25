<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Jurnal';
    protected $fillable = [
        'company_id',
        'number',
        'journal_date',
        'reference_type',
        'reference_id',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'journal_date' => 'date:Y-m-d',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(JournalEntryItem::class);
    }
}
