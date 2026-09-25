<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UserFormRequest;
use App\Services\Master\UserService;
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

    public function store(UserFormRequest $request, UserService $userService)
    {
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

    public function update(UserFormRequest $request, UserService $userService, int $id)
    {
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

    public function password(UserFormRequest $request, UserService $userService, int $id)
    {
        try {
            $userService->updateUserPassword($request, $id);

            return response()->json([
                'message' => 'Password user berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error UserController@password: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui password user',
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

    public function options(Request $request, UserService $userService)
    {
        try {
            $data = $userService->fetchUserOptionData($request);

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error UserController@options: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data user',
            ], 500);
        }
    }
}
