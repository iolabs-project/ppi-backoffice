<?php

namespace App\Http\Controllers\Master;

use App\Enums\AccountSettingEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\AccountSettingFormRequest;
use App\Services\Master\AccountSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AccountSettingController extends Controller
{
    public function update(AccountSettingFormRequest $request, AccountSettingService $accountSettingService)
    {
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
