<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class WarehouseFormRequest extends FormRequest
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
                Rule::unique('warehouses', 'code')
                    ->where('company_id', config('context.selected_company_id'))
                    ->ignore($this->route('id')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
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
            'code.required' => 'Kode gudang harus diisi.',
            'code.string' => 'Kode gudang harus berupa string.',
            'code.max' => 'Kode gudang tidak boleh lebih dari 50 karakter.',
            'code.unique' => 'Kode gudang sudah digunakan.',
            'name.required' => 'Nama gudang harus diisi.',
            'name.string' => 'Nama gudang harus berupa string.',
            'name.max' => 'Nama gudang tidak boleh lebih dari 255 karakter.',
            'address.string' => 'Alamat gudang harus berupa string.',
            'address.max' => 'Alamat gudang tidak boleh lebih dari 255 karakter.',
            'note.string' => 'Catatan harus berupa string.',
            'note.max' => 'Catatan tidak boleh lebih dari 255 karakter.',
        ];
    }
}
