<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $superAdminRole = Role::firstOrCreate([
            'name' => RoleEnum::SUPER_ADMIN->value,
        ]);
        $adminRole = Role::firstOrCreate([
            'name' => RoleEnum::ADMIN->value,
        ]);
        $accountingRole = Role::firstOrCreate([
            'name' => RoleEnum::ACCOUNTING->value,
        ]);

        $superAdminRole->syncPermissions(Permission::all());

        // Admin: full access except everything under master.*
        $adminRole->syncPermissions(
            Permission::where('name', 'not like', 'master.%')->get()
        );

        // Accounting: view everything except master.*
        $accountingRole->syncPermissions(
            Permission::where('name', 'like', '%.view')
                ->where('name', 'not like', 'master.%')
                ->get()
        );
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
