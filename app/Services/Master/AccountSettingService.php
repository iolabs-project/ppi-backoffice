<?php

namespace App\Services\Master;

use App\Enums\AccountSettingEnum;
use App\Models\AccountSetting;
use Illuminate\Http\Request;

class AccountSettingService
{
    public function fetchAccountSettingGroups(): array
    {
        return AccountSettingEnum::grouped();
    }

    public function fetchAccountSettingValues(): array
    {
        $existing = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->pluck('account_id', 'setting_key');

        $values = [];
        foreach (AccountSettingEnum::cases() as $case) {
            $values[$case->value] = $existing[$case->value] ?? null;
        }

        return $values;
    }

    public function updateAccountSettings(Request $request): void
    {
        $companyID = config('context.selected_company_id');

        foreach ($request->input('settings', []) as $setting) {
            $accountID = $setting['account_id'] ?? null;

            if (!$accountID) {
                AccountSetting::where('company_id', $companyID)
                    ->where('setting_key', $setting['setting_key'])
                    ->delete();
                continue;
            }

            AccountSetting::updateOrCreate(
                [
                    'company_id' => $companyID,
                    'setting_key' => $setting['setting_key'],
                ],
                [
                    'account_id' => $accountID,
                ]
            );
        }
    }
}
