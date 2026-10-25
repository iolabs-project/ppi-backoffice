<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class AccountSetting extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'visible';

    protected string $activityLogSubject = 'Pengaturan akun';

    protected $fillable = [
        'company_id',
        'account_id',
        'setting_key',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
