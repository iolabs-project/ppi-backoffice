<?php

namespace App\Http\Requests\Finance;

use App\Enums\CashTransactionStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CashReceiveFormRequest extends FormRequest
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
         if ($this->input('status') === CashTransactionStatusEnum::DRAFT->value) {
                return $this->draftRules();
            } else if ($this->input('status') === CashTransactionStatusEnum::POSTED->value) {
                return $this->postedRules();
            }
        return [
            
        ];
    }

    private function draftRules(): array
    {
        return [
            'to_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:receive'],
            'tax_percentage' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    private function postedRules(): array
    {
        return [
            'to_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'contact_id' => ['required', 'exists:contacts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:receive'],
            'tax_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required_with:items.*.amount', 'exists:chart_of_accounts,id'],
            'items.*.amount' => ['required_with:items.*.account_id', 'numeric', 'min:0.01'],
            
        ];
    }

    public function messages(): array
    {
        return [
            'to_account_id.required' => 'Akun tujuan harus diisi.',
            'contact_id.required' => 'Kontak harus diisi.',
            'items.required' => 'Daftar akun harus diisi.',
            'items.min' => 'Daftar akun harus memiliki minimal 1 akun.',
            'items.*.account_id.required_with' => 'Akun harus dipilih jika jumlah diisi.',
            'items.*.amount.required_with' => 'Jumlah harus diisi jika akun dipilih.',
            'items.*.amount.numeric' => 'Jumlah harus berupa angka.',
            'items.*.amount.min' => 'Jumlah harus lebih besar dari 0.',
        ];
    }
}
