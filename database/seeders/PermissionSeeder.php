<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'purchasing' => [
                'purchase-orders' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'goods-receipts' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'invoices' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
            ],

            'sales' => [
                'sales-orders' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'delivery-orders' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'invoices' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
            ],
            'master' => [
                'products' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'contacts' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'warehouses' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'accounts' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'users' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ],
                'roles' => [
                    'view',
                    'create',
                    'edit',
                    'delete',
                ]
            ],
        ];

        foreach ($permissions as $module => $resources) {
            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate([
                        'name' => "{$module}.{$resource}.{$action}"
                    ]);
                }
            }
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
