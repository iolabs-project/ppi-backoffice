<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProductFormRequest extends FormRequest
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
                Rule::unique('products', 'code')
                    ->where('company_id', config('context.selected_company_id'))
                    ->ignore($this->route('id')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'batch_prefix' => [
                'required',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'unit_id' => [
                'required',
                'exists:units,id',
            ],
            'category_id' => [
                'required',
                'exists:product_categories,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode produk wajib diisi.',
            'code.string' => 'Kode produk harus berupa string.',
            'code.max' => 'Kode produk tidak boleh lebih dari 50 karakter.',
            'code.unique' => 'Kode produk sudah digunakan.',

            'name.required' => 'Nama produk wajib diisi.',
            'name.string' => 'Nama produk harus berupa string.',
            'name.max' => 'Nama produk tidak boleh lebih dari 255 karakter.',

            'batch_prefix.required' => 'Prefix batch wajib diisi.',
            'batch_prefix.string' => 'Prefix batch harus berupa string.',
            'batch_prefix.max' => 'Prefix batch tidak boleh lebih dari 50 karakter.',

            'description.string' => 'Deskripsi produk harus berupa string.',
            'description.max' => 'Deskripsi produk tidak boleh lebih dari 255 karakter.',

            'unit_id.required' => 'Satuan wajib dipilih.',
            'unit_id.exists' => 'Satuan yang dipilih tidak valid.',

            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
        ];
    }
}
