<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionService
{
    public function fetchPermissionData()
    {
        $data = Permission::all()->pluck('name');
        return $data;
    }
}
