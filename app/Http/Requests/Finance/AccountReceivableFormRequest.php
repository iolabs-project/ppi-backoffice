<?php

namespace App\Http\Requests\Finance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ReceivablePaymentReferenceTypeEnum;
class AccountReceivableFormRequest extends FormRequest
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
            'account_id' => ['required', 'exists:chart_of_accounts,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,credit_card'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'reference_id' => ['required', 'integer'],
            'reference_type' => ['required', new Enum(ReceivablePaymentReferenceTypeEnum::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => 'Akun kas & bank harus diisi.',
            'account_id.exists' => 'Akun kas & bank tidak ditemukan.',
            'payment_date.required' => 'Tanggal pembayaran harus diisi.',
            'payment_date.date' => 'Tanggal pembayaran tidak valid.',
            'payment_method.required' => 'Metode pembayaran harus diisi.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'reference_number.string' => 'Nomor referensi harus berupa string.',
            'reference_number.max' => 'Nomor referensi tidak boleh lebih dari 50 karakter.',
            'amount.required' => 'Jumlah pembayaran harus diisi.',
            'amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'amount.min' => 'Jumlah pembayaran tidak boleh kurang dari 0.',
            'note.string' => 'Catatan harus berupa string.',
        ];
    }
}
