<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\AccountFormRequest;
use App\Services\Master\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function datatable(Request $request, AccountService $accountService)
    {
        try {
            $data = $accountService->fetchAccountTableData($request);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/AccountController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data akun',
            ], 500);
        }
    }

    public function store(AccountFormRequest $request, AccountService $accountService)
    {
        try {
            $accountService->storeAccount($request);

            return response()->json([
                'message' => 'Akun berhasil dibuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Error AccountController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat akun',
            ], 500);
        }
    }

    public function update(AccountFormRequest $request, AccountService $accountService, int $id)
    {
        try {
            $accountService->updateAccount($request, $id);

            return response()->json([
                'message' => 'Akun berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error AccountController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui akun',
            ], 500);
        }
    }

    public function status(Request $request, AccountService $accountService, int $id)
    {
        try {
            $accountService->toggleAccountStatus($id);

            return response()->json([
                'message' => 'Status akun berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error AccountController@status: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui status akun',
            ], 500);
        }
    }
}
