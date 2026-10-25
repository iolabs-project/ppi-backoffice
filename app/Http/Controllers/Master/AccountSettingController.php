<?php

namespace App\Http\Controllers\Master;

use App\Enums\AccountSettingEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\AccountSettingFormRequest;
use App\Services\Master\AccountSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AccountSettingController extends Controller
{
    public function update(AccountSettingFormRequest $request, AccountSettingService $accountSettingService)
    {
        try {
            $accountSettingService->updateAccountSettings($request);

            return response()->json([
                'message' => 'Pengaturan akun berhasil disimpan',
            ]);
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error AccountSettingController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $this->loggableRequest($request),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pengaturan akun',
            ], 500);
        }
    }
}
