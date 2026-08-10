<?php

namespace App\Http\Requests\Finance;

use App\Enums\CashTransactionTypeEnum;
use App\Enums\CashTransactionStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CashFormRequest extends FormRequest
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
        if ($this->input('type') === CashTransactionTypeEnum::TRANSFER->value) {
            if ($this->input('status') === CashTransactionStatusEnum::DRAFT->value) {
                return $this->transferDraftRules();
            } else if ($this->input('status') === CashTransactionStatusEnum::POSTED->value) {
                return $this->transferPostedRules();
            }
        } else if ($this->input('type') === CashTransactionTypeEnum::SEND->value) {
            if ($this->input('status') === CashTransactionStatusEnum::DRAFT->value) {
                return $this->sendDraftRules();
            } else if ($this->input('status') === CashTransactionStatusEnum::POSTED->value) {
                return $this->sendPostedRules();
            }
        } else if ($this->input('type') === CashTransactionTypeEnum::RECEIVE->value) {
            if ($this->input('status') === CashTransactionStatusEnum::DRAFT->value) {
                return $this->receiveDraftRules();
            } else if ($this->input('status') === CashTransactionStatusEnum::POSTED->value) {
                return $this->receivePostedRules();
            }
        }
        return [];
    }

    private function transferDraftRules(): array
    {
        return [
            'to_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'from_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:transfer'],
        ];
    }

    private function transferPostedRules(): array
    {
        return [
            'to_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'from_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:transfer'],
            'subtotal' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    private function sendDraftRules(): array
    {
        return [
            'from_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:send'],
            'tax_percentage' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    private function sendPostedRules(): array
    {
        return [
            'from_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:send'],
            'subtotal' => ['required', 'numeric', 'min:0.01'],
            'tax_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'costs' => ['nullable', 'array'],
            'costs.*.account_id' => ['required_with:costs.*.amount', 'exists:chart_of_accounts,id'],
            'costs.*.amount' => ['required_with:costs.*.account_id', 'numeric', 'min:0.01'],
        ];
    }

    private function receiveDraftRules(): array
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

    private function receivePostedRules(): array
    {
        return [
            'to_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:receive'],
            'subtotal' => ['required', 'numeric', 'min:0.01'],
            'tax_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'costs' => ['nullable', 'array'],
            'costs.*.account_id' => ['required_with:costs.*.amount', 'exists:chart_of_accounts,id'],
            'costs.*.amount' => ['required_with:costs.*.account_id', 'numeric', 'min:0.01'],
        ];
    }
}
