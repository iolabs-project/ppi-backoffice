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

        $contact = \App\Models\Contact::create([
            'name' => 'Randy',
            'code' => 'EMP-001',
            'company_id' => $companny->id,
            'is_customer' => false,
            'is_supplier' => false,
            'is_employee' => true,
        ]);

        $user = \App\Models\User::create([
            'username' => 'admin',
            'contact_id' => $contact->id,
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
