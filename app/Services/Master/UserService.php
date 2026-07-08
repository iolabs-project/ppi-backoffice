<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function fetchUserData(Request $request)
    {
        $data = User::select(
            'id',
            'code',
            'name',
        )
            ->where('company_id', config('context.selected_company_id'))
            ->whereNull('deleted_at')
            ->where('is_user', true);

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        return $data->get();
    }

    public function fetchUserTableData(Request $request)
    {
        $data = User::with(['contact:id,name'])->select(
            'id',
            'username',
            'name',
            'contact_id',
        )
            ->where('company_id', config('context.selected_company_id'))
            ->whereNull('deleted_at')
            ->where('is_user', true);

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('username', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $data = $data->orderBy('username', 'asc')->paginate($request->input('per_page', 10));
        return $data;
    }

    public function storeUser(Request $request)
    {
        $existingUsername = User::where('username', $request->username)->first();
        if ($existingUsername) {
            throw ValidationException::withMessages([
                'username' => 'Username sudah digunakan. Silakan gunakan username lain.',
            ]);
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('company_ids')) {
                foreach ($request->company_ids as $companyId) {
                    UserCompany::create([
                        'user_id' => $user->id,
                        'company_id' => $companyId,
                    ]);
                }
            }

            if ($request->filled('role_id')) {
                $user->syncRoles([$request->role_id]);
            }
        });
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $existingUsername = User::where('username', $request->username)
            ->where('id', '!=', $user->id)
            ->first();
        if ($existingUsername) {
            throw ValidationException::withMessages([
                'username' => 'Username sudah digunakan. Silakan gunakan username lain.',
            ]);
        }

        DB::transaction(function () use ($request, $user) {
            $user->update([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('company_ids')) {
                // Delete existing user-company relationships
                UserCompany::where('user_id', $user->id)->delete();

                // Create new user-company relationships
                foreach ($request->company_ids as $companyId) {
                    UserCompany::create([
                        'user_id' => $user->id,
                        'company_id' => $companyId,
                    ]);
                }
            }

            if ($request->filled('role_id')) {
                $user->syncRoles([$request->role_id]);
            }
        });
    }

    public function toggleUserStatus(int $id)
    {
        User::where('id', $id)->update([
            'deleted_at' => DB::raw('IF(deleted_at IS NULL, NOW(), NULL)'),
        ]);
    }
}
