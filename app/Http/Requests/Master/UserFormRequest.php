<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class UserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'store' => $this->storeRules(),
            'password' => $this->changePasswordRules(),
            default => $this->updateRules(),
        };
    }

    private function storeRules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username'),
            ],
            'contact_id' => 'nullable|exists:contacts,id',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    private function updateRules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->route('id')),
            ],
            'contact_id' => 'nullable|exists:contacts,id',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    private function changePasswordRules(): array
    {
        return [
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Username wajib diisi',
            'username.string' => 'Username harus berupa string',
            'username.max' => 'Username maksimal 255 karakter',
            'username.unique' => 'Username sudah digunakan',
            'contact_id.exists' => 'Contact tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.string' => 'Password harus berupa string',
            'password.min' => 'Password minimal 8 karakter',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi',
            'password_confirmation.string' => 'Konfirmasi password harus berupa string',
            'password_confirmation.min' => 'Konfirmasi password minimal 8 karakter',
            'password_confirmation.same' => 'Konfirmasi password harus sama dengan password',
            'role_id.required' => 'Role wajib diisi',
            'role_id.exists' => 'Role tidak valid',
        ];
    }
}
