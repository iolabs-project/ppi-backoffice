<?php

namespace App\Http\Requests;

use App\Enums\ExpenseStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseFormRequest extends FormRequest
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
        if ($this->input('status') === ExpenseStatus::DRAFT->value) {
            return $this->draftRules();
        } else if ($this->input('status') === ExpenseStatus::OPEN->value) {
            return $this->openRules();
        }

        return [];
    }

    private function draftRules(): array
    {
        return [
            'contact_id' => 'nullable|exists:contacts,id',
            'expense_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:expense_date',
            'payment_terms' => 'nullable|in:net_7,net_14,net_30,net_45',
            'items' => 'nullable|array',
            'items.*.account_id' => 'required_with:items|exists:chart_of_accounts,id',
            'items.*.amount' => 'required_with:items|numeric|min:0.00',
            'costs' => 'nullable|array',
            'costs.*.account_id' => 'required_with:costs|exists:chart_of_accounts,id',
            'costs.*.amount' => 'required_with:costs|numeric|min:0.00',
        ];
    }

    private function openRules(): array
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
                    'expense_date' => 'required|date',
                    'due_date' => 'nullable|date|after_or_equal:expense_date',
                    'payment_terms' => 'nullable|in:net_7,net_14,net_30,net_45',
                    'items' => 'required|array|min:1',
                    'items.*.account_id' => 'required_with:items|exists:chart_of_accounts,id',
                    'items.*.amount' => 'required_with:items|numeric|min:0.00',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs|exists:chart_of_accounts,id',
                    'costs.*.amount' => 'required_with:costs|numeric|min:0.00',
        ];
    }

    public function messages(): array
    {
        return [
            'contact_id.required' => 'Kontak wajib diisi.',
            'contact_id.exists' => 'Kontak tidak valid.',
            'expense_date.required' => 'Tanggal biaya wajib diisi.',
            'expense_date.date' => 'Tanggal biaya tidak valid.',
            'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal biaya.',
            'payment_terms.in' => 'Syarat pembayaran tidak valid.',
            'items.required' => 'Item biaya wajib diisi.',
            'items.array' => 'Item biaya harus berupa array.',
            'items.*.account_id.required_with' => 'Akun untuk item biaya wajib diisi.',
            'items.*.account_id.exists' => 'Akun untuk item biaya tidak valid.',
            'items.*.amount.required_with' => 'Jumlah untuk item biaya wajib diisi.',
            'items.*.amount.numeric' => 'Jumlah untuk item biaya harus berupa angka.',
            'items.*.amount.min' => 'Jumlah untuk item biaya harus lebih besar dari 0.',
            'costs.array' => 'Biaya tambahan harus berupa array.',
            'costs.*.account_id.required_with' => 'Akun untuk biaya tambahan wajib diisi.',
            'costs.*.account_id.exists' => 'Akun untuk biaya tambahan tidak valid.',
            'costs.*.amount.required_with' => 'Jumlah untuk biaya tambahan wajib diisi.',
            'costs.*.amount.numeric' => 'Jumlah untuk biaya tambahan harus berupa angka.',
            'costs.*.amount.min' => 'Jumlah untuk biaya tambahan harus lebih besar dari 0.',
        ];
    }
}
