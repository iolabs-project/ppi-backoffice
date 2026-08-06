<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(RoleService $roleService)
    {
        try {
            $roles = $roleService->fetchRoleData()->map(fn($r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'permissions' => $r->permissions->pluck('name')->values(),
            ]);

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error('RoleController@index: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data role'], 500);
        }
    }

    public function store(Request $request, RoleService $roleService)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
        ]);

        try {
            $roleService->storeRole($request);

            return response()->json(['message' => 'Role berhasil dibuat']);
        } catch (\Exception $e) {
            Log::error('RoleController@store: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat membuat role'], 500);
        }
    }

    public function update(Request $request, RoleService $roleService, int $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($id)],
        ]);

        try {
            $roleService->updateRole($request, $id);

            return response()->json(['message' => 'Role berhasil diperbarui']);
        } catch (\Exception $e) {
            Log::error('RoleController@update: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui role'], 500);
        }
    }

    public function updatePermissions(Request $request, RoleService $roleService, int $id)
    {
        $request->validate([
            'permissions'   => 'present|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        try {
            $roleService->updatePermit($request, $id);

            return response()->json(['message' => 'Hak akses berhasil diperbarui']);
        } catch (\Exception $e) {
            Log::error('RoleController@updatePermissions: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui hak akses'], 500);
        }
    }
}
