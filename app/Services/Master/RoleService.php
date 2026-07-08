<?php

namespace App\Services;

use Spatie\Permission\Models\Role;

class RoleService
{
    public function fetchRoleData()
    {
        $data = Role::select('id', 'name')
            ->get();

        return $data;
    }
}
