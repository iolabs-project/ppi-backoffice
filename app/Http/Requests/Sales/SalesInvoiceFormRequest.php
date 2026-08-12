<?php

namespace App\Http\Requests\Sales;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\SalesInvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;

class SalesInvoiceFormRequest extends FormRequest
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
        if ($this->routeIs('sales.delivery_orders.store')) {
            return [
                'sales_order_id' => 'required|exists:sales_orders,id',
            ];
        }

        if ($this->input('status') === SalesInvoiceStatus::DRAFT->value) {
            return $this->draftRules();
        } else if ($this->input('status') === SalesInvoiceStatus::OPEN->value) {
            return $this->openRules();
        }

        return [];
    }

    private function draftRules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'tax_percentage' => 'nullable|numeric|between:0,100',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:' . SalesInvoiceStatus::DRAFT->value . ',' . SalesInvoiceStatus::OPEN->value,
        ];
    }

    private function openRules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'tax_percentage' => 'nullable|numeric|between:0,100',
            'down_payment_amount' => 'nullable|numeric|min:0',
            'down_payment_account_id' => 'nullable|exists:chart_of_accounts,id',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:' . SalesInvoiceStatus::DRAFT->value . ',' . SalesInvoiceStatus::OPEN->value,
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:1',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.discount_percentage' => 'nullable|numeric|min:0',
            'details.*.discount_amount' => 'nullable|numeric|min:0',
            'charges' => 'nullable|array',
            'charges.*.account_id' => 'required_with:charges.*.amount|exists:chart_of_accounts,id',
            'charges.*.description' => 'nullable|string|max:1000',
            'charges.*.amount' => 'required_with:charges.*.account_id|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'sales_order_id.required' => 'Sales order harus dipilih.',
            'sales_order_id.exists' => 'Sales order tidak ditemukan.',
            'invoice_date.required' => 'Tanggal invoice harus diisi.',
            'payment_terms.required' => 'Syarat pembayaran harus dipilih.',
            'discount_percentage.between' => 'Persentase diskon harus antara 0 dan 100.',
            'tax_percentage.between' => 'Persentase pajak harus antara 0 dan 100.',
            'details.required' => 'Daftar item tidak boleh kosong.',
            'details.*.product_id.required' => 'Produk harus dipilih untuk setiap item.',
            'details.*.quantity.required' => 'Kuantitas harus diisi untuk setiap item.',
            'details.*.unit_price.required' => 'Harga satuan harus diisi untuk setiap item.',
            'details.*.quantity.min' => 'Kuantitas harus lebih besar dari 0 untuk setiap item.',
            'details.*.unit_price.min' => 'Harga satuan harus lebih besar dari 0 untuk setiap item.',
            'charges.*.account_id.required_with' => 'Akun biaya belum dipilih.',
            'charges.*.amount.required_with' => 'Jumlah biaya belum diisi.',
        ];
    }
}
