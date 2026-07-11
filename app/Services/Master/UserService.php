<?php

namespace App\Services\Master;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function fetchUserTableData(Request $request)
    {
        $data = User::with(['contact:id,name', 'roles:id,name'])
            ->select('id', 'username', 'contact_id', 'deleted_at');

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('username', 'like', '%' . $request->search . '%')
                    ->orWhereHas('contact', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        return $data->orderBy('username', 'asc')->paginate($request->input('per_page', 10));
    }

    public function fetchUserOptionData(Request $request)
    {
        $data = User::with('contact:id,name')->select('id', 'username', 'contact_id');

        if ($request->filled('search')) {
            $data->where('username', 'like', '%' . $request->search . '%');
        }

        return $data->get();
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
                'contact_id' => $request->contact_id ?: null,
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
                $user->syncRoles([(int) $request->role_id]);
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
            $updateData = [
                'username' => $request->username,
                'contact_id' => $request->contact_id ?: null,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            if ($request->filled('company_ids')) {
                UserCompany::where('user_id', $user->id)->delete();

                foreach ($request->company_ids as $companyId) {
                    UserCompany::create([
                        'user_id' => $user->id,
                        'company_id' => $companyId,
                    ]);
                }
            }

            if ($request->filled('role_id')) {
                $user->syncRoles([(int) $request->role_id]);
            }
        });
    }

    public function toggleUserStatus(int $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'deleted_at' => $user->deleted_at ? null : now(),
        ]);
    }
}
