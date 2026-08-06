<?php

namespace App\Services\Master;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public function fetchRoleData()
    {
        return Role::with('permissions')->get();
    }

    public function fetchPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Permission::orderBy('name')->get(['id', 'name']);
    }

    public function storeRole(Request $request): void
    {
        Role::create([
            'name' => $request->input('name'),
        ]);
    }

    public function updateRole(Request $request, int $roleID): void
    {
        $role = Role::findOrFail($roleID);
        $role->name = $request->input('name');
        $role->save();
    }

    public function updatePermit(Request $request, int $roleID): void
    {
        $role = Role::findOrFail($roleID);
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);
    }
}
