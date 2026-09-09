<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('sidebar only shows modules allowed by the user permissions', function () {
    $user = User::query()->create([
        'username' => 'sales-user',
        'password' => 'password',
    ]);
    $user->givePermissionTo(Permission::create([
        'name' => 'sales.sales-orders.view',
        'guard_name' => 'web',
    ]));

    Auth::login($user);

    $html = Blade::render('<x-navbar.sidebar />');

    expect($html)
        ->toContain('title="Penjualan"')
        ->toContain('Sales Order')
        ->not->toContain('title="Pembelian"')
        ->not->toContain('title="Finance"')
        ->not->toContain('title="Master Data"')
        ->not->toContain('title="Laporan"');
});
