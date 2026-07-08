<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function datatable(Request $request, UserService $userService)
    {
        try {
            $data = $userService->fetchUserTableData($request);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/UserController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data user',
            ], 500);
        }
    }

    public function store(Request $request, UserService $userService)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username'),
            ],
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
            'role_id' => 'required|exists:roles,id',
        ]);
        try {
            $userService->storeUser($request);

            return response()->json([
                'message' => 'User berhasil dibuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Error UserController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat user',
            ], 500);
        }
    }

    public function update(Request $request, UserService $userService, int $id)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($id),
            ],
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'confirm_password' => 'nullable|string|min:8|same:password',
            'role_id' => 'required|exists:roles,id',
        ]);
        try {
            $userService->updateUser($request, $id);

            return response()->json([
                'message' => 'User berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error UserController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui user',
            ], 500);
        }
    }

    public function status(Request $request, UserService $userService, int $id)
    {
        try {
            $userService->toggleUserStatus($id);

            return response()->json([
                'message' => 'Status user berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error UserController@status: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui status user',
            ], 500);
        }
    }
}
