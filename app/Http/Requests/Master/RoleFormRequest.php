<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RoleFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role harus diisi.',
            'name.string' => 'Nama role harus berupa string.',
            'name.max' => 'Nama role tidak boleh lebih dari 255 karakter.',
            'permissions.required' => 'Hak akses harus diisi.',
            'permissions.array' => 'Hak akses harus berupa array.',
            'permissions.*.integer' => 'Hak akses harus berupa angka.',
            'permissions.*.exists' => 'Hak akses tidak valid.',
        ];
    }
}
