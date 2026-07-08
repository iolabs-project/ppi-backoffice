<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companny = \App\Models\Company::create([
            'code' => 'PPI',
            'name' => 'Putra Pangan Indonesia',
        ]);

        $user = \App\Models\User::create([
            'username' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        ]);

        // $user->companies()->attach($companny);
        \App\Models\UserCompany::create([
            'user_id' => $user->id,
            'company_id' => $companny->id,
        ]);

        $user->assignRole(RoleEnum::SUPER_ADMIN->value);
    }
}
