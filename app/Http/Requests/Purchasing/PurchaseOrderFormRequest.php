<?php

namespace App\Http\Requests\Purchasing;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderFormRequest extends FormRequest
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
        if ($this->input('status') === PurchaseOrderStatus::DRAFT->value) {
            return $this->draftRules();
        } else if ($this->input('status') === PurchaseOrderStatus::OPEN->value) {
            return $this->openRules();
        }

        return [];
    }

    private function draftRules(): array
    {
        return [
            'supplier_id' => 'nullable|exists:contacts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('purchase_orders', 'number')->ignore($this->route('id')),
            ],
            'reference_number' => 'nullable|string|max:50',
            'order_date' => 'required|date',
            'due_date' => 'nullable|date',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'tax_percentage' => 'nullable|numeric|between:0,100',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:' . PurchaseOrderStatus::DRAFT->value . ',' . PurchaseOrderStatus::OPEN->value,
        ];
    }

    private function openRules(): array
    {
        return [
            'supplier_id' => 'required|exists:contacts,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('purchase_orders', 'number')->ignore($this->route('id')),
            ],
            'reference_number' => 'nullable|string|max:50',
            'order_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
            'discount_percentage' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'down_payment_amount' => 'nullable|numeric|min:0',
            'down_payment_account_id' => 'nullable|exists:chart_of_accounts,id',
            'total_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,open',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:1',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.discount_percentage' => 'nullable|numeric|min:0',
            'details.*.discount_amount' => 'nullable|numeric|min:0',
            'costs' => 'nullable|array',
            'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
            'costs.*.description' => 'nullable|string|max:1000',
            'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
            'costs.*.billed_by' => 'required_with:costs.*.amount|in:supplier,third_party,internal',
            'costs.*.is_inventory_cost' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier harus dipilih.',
            'warehouse_id.required' => 'Gudang harus dipilih.',
            'number.required' => 'Nomor PO harus diisi.',
            'number.unique' => 'Nomor PO sudah digunakan. Silakan gunakan nomor lain.',
            'order_date.required' => 'Tanggal PO harus diisi.',
            'payment_terms.required' => 'Syarat pembayaran harus dipilih.',
            'details.required' => 'Daftar item tidak boleh kosong.',
            'details.*.product_id.required' => 'Produk harus dipilih untuk setiap item.',
            'details.*.quantity.required' => 'Kuantitas harus diisi untuk setiap item.',
            'details.*.quantity.min' => 'Kuantitas harus lebih besar dari 0 untuk setiap item.',
            'details.*.unit_price.required' => 'Harga satuan harus diisi untuk setiap item.',
            'details.*.unit_price.min' => 'Harga satuan harus lebih besar dari 0 untuk setiap item.',
            'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
            'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
            'costs.*.billed_by.required_with' => 'Pihak yang menagih harus dipilih jika jumlah biaya diisi.'
        ];
    }
}
