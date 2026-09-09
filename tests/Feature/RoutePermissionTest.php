<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('report routes require permission for the requested report', function () {
    $user = User::query()->create([
        'username' => 'balance-sheet-user',
        'password' => 'password',
    ]);
    $user->givePermissionTo(Permission::create([
        'name' => 'reports.balance-sheet.view',
        'guard_name' => 'web',
    ]));

    Auth::login($user);

    get(route('reports.show', 'profit-loss'))
        ->assertForbidden();

    get(route('reports.show', 'balance-sheet'))
        ->assertOk();
});

test('resource routes reject users without the required permission', function () {
    $user = User::query()->create([
        'username' => 'restricted-user',
        'password' => 'password',
    ]);

    Auth::login($user);

    get(route('sales.sales_orders.index'))
        ->assertForbidden();
});

test('master page only renders tabs allowed by the user permissions', function () {
    $user = User::query()->create([
        'username' => 'contacts-user',
        'password' => 'password',
    ]);
    $user->givePermissionTo(Permission::create([
        'name' => 'master.contacts.view',
        'guard_name' => 'web',
    ]));

    Auth::login($user);

    get(route('master.index'))
        ->assertOk()
        ->assertSee('const permittedTabs = ["kontak"]', false)
        ->assertSee('>Kontak</button>', false)
        ->assertDontSee('>Produk</button>', false)
        ->assertDontSee('>Gudang</button>', false)
        ->assertDontSee('>User</button>', false)
        ->assertDontSee('>Hak Akses</button>', false)
        ->assertDontSee('>Akun</button>', false)
        ->assertDontSee('>Pengaturan Akun</button>', false);
});
