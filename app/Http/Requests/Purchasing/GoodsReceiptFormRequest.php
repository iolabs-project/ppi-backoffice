<?php

namespace App\Http\Requests\Purchasing;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\GoodsReceiptStatus;
use Illuminate\Foundation\Http\FormRequest;

class GoodsReceiptFormRequest extends FormRequest
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
        // if store route
        if ($this->routeIs('purchasing.goods_receipts.store')) {
            return [
                'purchase_order_id' => 'required|exists:purchase_orders,id',
            ];
        }

        if ($this->input('status') === GoodsReceiptStatus::DRAFT->value) {
            return $this->draftRules();
        } else if ($this->input('status') === GoodsReceiptStatus::FINISHED->value) {
            return $this->finishedRules();
        }

        return [];
    }

    private function draftRules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:50',
            'receipt_date' => 'required|date',
            'status' => 'required|in:draft,finished',
            'discount_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ];
    }

    private function finishedRules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:50',
            'receipt_date' => 'required|date',
            'status' => 'required|in:draft,finished',
            'discount_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.batch_number' => 'required|string|max:50',
            'details.*.received_quantity' => 'required|numeric|min:0',
            'details.*.expected_quantity' => 'nullable|numeric|min:0',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'costs' => 'nullable|array',
            'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
            'costs.*.description' => 'nullable|string|max:1000',
            'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
            'costs.*.is_inventory_cost' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_order_id.required' => 'Purchase order harus dipilih.',
            'purchase_order_id.exists' => 'Purchase order tidak ditemukan.',
            'receipt_date.required' => 'Tanggal penerimaan harus diisi.',
            'details.required' => 'Detail penerimaan harus diisi.',
            'details.*.purchase_order_item_id.required' => 'Item purchase order harus dipilih.',
            'details.*.purchase_order_item_id.exists' => 'Item purchase order tidak ditemukan.',
            'details.*.product_id.required' => 'Produk harus dipilih.',
            'details.*.product_id.exists' => 'Produk tidak ditemukan.',
            'details.*.batch_number.required' => 'Nomor batch harus diisi.',
            'details.*.received_quantity.required' => 'Kuantitas diterima harus diisi.',
            'details.*.received_quantity.numeric' => 'Kuantitas diterima harus berupa angka.',
            'details.*.received_quantity.min' => 'Kuantitas diterima tidak boleh kurang dari 0.',
            'details.*.expected_quantity.numeric' => 'Kuantitas yang diharapkan harus berupa angka.',
            'details.*.expected_quantity.min' => 'Kuantitas yang diharapkan tidak boleh kurang dari 0.',
            'details.*.unit_price.required' => 'Harga satuan harus diisi.',
            'details.*.unit_price.numeric' => 'Harga satuan harus berupa angka.',
            'details.*.unit_price.min' => 'Harga satuan tidak boleh kurang dari 0.',
            'details.*.discount_percentage.numeric' => 'Persentase diskon harus berupa angka.',
            'details.*.discount_percentage.min' => 'Persentase diskon tidak boleh kurang dari 0.',
            'details.*.discount_percentage.max' => 'Persentase diskon tidak boleh lebih dari 100.',
            'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
            'costs.*.account_id.exists' => 'Akun tidak ditemukan.',
            'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
            'costs.*.amount.numeric' => 'Jumlah biaya harus berupa angka.',
            'costs.*.amount.min' => 'Jumlah biaya tidak boleh kurang dari 0.',
        ];
    }
}
