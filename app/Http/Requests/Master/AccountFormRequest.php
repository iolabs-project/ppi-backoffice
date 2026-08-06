<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountFormRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('chart_of_accounts', 'code')
                    ->where('company_id', config('context.selected_company_id'))
                    ->ignore($this->route('id')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'category_id' => [
                'required',
                'exists:account_categories,id',
            ],
            'note' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode akun harus diisi.',
            'code.string' => 'Kode akun harus berupa string.',
            'code.max' => 'Kode akun tidak boleh lebih dari 50 karakter.',
            'code.unique' => 'Kode akun sudah digunakan.',
            'name.required' => 'Nama akun harus diisi.',
            'name.string' => 'Nama akun harus berupa string.',
            'name.max' => 'Nama akun tidak boleh lebih dari 255 karakter.',
            'category_id.required' => 'Kategori akun harus diisi.',
            'category_id.exists' => 'Kategori akun tidak valid.',
            'note.string' => 'Catatan harus berupa string.',
            'note.max' => 'Catatan tidak boleh lebih dari 255 karakter.',
        ];
    }
}
