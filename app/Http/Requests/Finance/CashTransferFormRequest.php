<?php

namespace App\Http\Requests\Finance;

use App\Enums\CashTransactionStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CashTransferFormRequest extends FormRequest
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
        // Any other status would skip validation and be saved as-is
        return [
            'status' => 'required|in:' . CashTransactionStatusEnum::DRAFT->value . ',' . CashTransactionStatusEnum::POSTED->value,
        ];
    }

    private function draftRules(): array
    {
        return [
            'to_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'from_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:transfer'],
        ];
    }

    private function postedRules(): array
    {
        return [
            'to_account_id' => ['required', 'exists:chart_of_accounts,id', 'different:from_account_id'],
            'from_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:transfer'],
            'subtotal' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_account_id.required' => 'Akun tujuan harus diisi.',
            'to_account_id.different' => 'Akun tujuan harus berbeda dengan akun asal.',
            'from_account_id.required' => 'Akun asal harus diisi.',
            'transaction_date.required' => 'Tanggal transaksi harus diisi.',
            'subtotal.required' => 'Total harus diisi.',
            'subtotal.numeric' => 'Total harus berupa angka.',
            'subtotal.min' => 'Total harus lebih besar dari 0.',
        ];
    }
}
