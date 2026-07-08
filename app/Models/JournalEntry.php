<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
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
        'journal_date' => 'datetime',
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
