<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AccountSettingEnum;

class AccountSettingFormRequest extends FormRequest
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
        return [
            'settings' => 'required|array',
            'settings.*.setting_key' => [
                'required',
                'string',
                Rule::in(array_column(AccountSettingEnum::cases(), 'value')),
            ],
            'settings.*.account_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Pengaturan akun harus diisi.',
            'settings.array' => 'Pengaturan akun harus berupa array.',
            'settings.*.setting_key.required' => 'Kata kunci pengaturan akun harus diisi.',
            'settings.*.setting_key.string' => 'Kata kunci pengaturan akun harus berupa string.',
            'settings.*.setting_key.in' => 'Kata kunci pengaturan akun tidak valid.',
            'settings.*.account_id.integer' => 'ID akun harus berupa angka.',
            'settings.*.account_id.exists' => 'Akun tidak valid.',
        ];
    }
}
