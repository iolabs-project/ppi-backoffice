<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\LimitsAmounts;

class ContactFormRequest extends FormRequest
{
    use LimitsAmounts;

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
                'nullable',
                'string',
                'max:50',
                Rule::unique('contacts', 'code')
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
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'is_customer' => [
                'required',
                'boolean',
                // A contact without any type never shows up as a customer, vendor or employee option
                function ($attribute, $value, $fail) {
                    if (!$this->boolean('is_customer') && !$this->boolean('is_supplier') && !$this->boolean('is_employee')) {
                        $fail('Pilih minimal satu tipe kontak: Customer, Vendor, atau Karyawan.');
                    }
                },
            ],
            'is_supplier' => [
                'required',
                'boolean',
            ],
            'is_employee' => [
                'required',
                'boolean',
            ],
            'transportation_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode kontak wajib diisi.',
            'code.string' => 'Kode kontak harus berupa string.',
            'code.max' => 'Kode kontak tidak boleh lebih dari 50 karakter.',
            'code.unique' => 'Kode kontak sudah digunakan.',

            'name.required' => 'Nama kontak wajib diisi.',
            'name.string' => 'Nama kontak harus berupa string.',
            'name.max' => 'Nama kontak tidak boleh lebih dari 255 karakter.',

            'address.string' => 'Alamat kontak harus berupa string.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'address.max' => 'Alamat kontak tidak boleh lebih dari 255 karakter.',

            'phone.string' => 'Nomor telepon harus berupa string.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',

            'email.email' => 'Email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',

            'is_customer.required' => 'Status customer harus diisi.',
            'is_customer.boolean' => 'Status customer harus berupa boolean.',

            'is_supplier.required' => 'Status supplier harus diisi.',
            'is_supplier.boolean' => 'Status supplier harus berupa boolean.',

            'is_employee.required' => 'Status karyawan harus diisi.',
            'is_employee.boolean' => 'Status karyawan harus berupa boolean.',

            'transportation_cost.numeric' => 'Biaya transportasi harus berupa angka.',
            'transportation_cost.min' => 'Biaya transportasi tidak boleh kurang dari 0.',
        ];
    }
}
