<?php

namespace App\Http\Controllers\Master;

use App\Enums\AccountSettingEnum;
use App\Http\Controllers\Controller;
use App\Services\Master\AccountSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AccountSettingController extends Controller
{
    public function update(Request $request, AccountSettingService $accountSettingService)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.setting_key' => [
                'required',
                'string',
                Rule::in(array_column(AccountSettingEnum::cases(), 'value')),
            ],
            'settings.*.account_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ]);

        try {
            $accountSettingService->updateAccountSettings($request);

            return response()->json([
                'message' => 'Pengaturan akun berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            Log::error('Error AccountSettingController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pengaturan akun',
            ], 500);
        }
    }
}
