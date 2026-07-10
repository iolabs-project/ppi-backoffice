<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
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

    public function store(Request $request, AccountService $accountService)
    {
        $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts', 'code')->where(function ($query) {
                    return $query->where('company_id', config('context.selected_company_id'));
                }),
            ],
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:account_categories,id',
            'note' => 'nullable|string|max:255',
        ]);
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

    public function update(Request $request, AccountService $accountService, int $id)
    {
        $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts', 'code')->ignore($id)->where(function ($query) {
                    return $query->where('company_id', config('context.selected_company_id'));
                }),
            ],
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:account_categories,id',
            'note' => 'nullable|string|max:255',
        ]);
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
