<?php

namespace App\Http\Requests\Purchasing;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\PurchaseInvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseInvoiceFormRequest extends FormRequest
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
        if ($this->routeIs('purchasing.purchase_invoices.store')) {
            return [
                'purchase_order_id' => 'required|exists:purchase_orders,id',
            ];
        }

        if ($this->input('status') === PurchaseInvoiceStatus::DRAFT->value) {
            return $this->draftRules();
        } else if ($this->input('status') === PurchaseInvoiceStatus::OPEN->value) {
            return $this->openRules();
        }

        return [];
    }

    private function draftRules()
    {
        return [];
    }

    private function openRules()
    {
        return [
            'reference_number' => 'nullable|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'status' => 'required|in:open',
            'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
            'discount_amount' => 'nullable|numeric|min:0',
            'down_payment_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.goods_receipt_item_id' => 'required|exists:goods_receipt_items,id',
            'details.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:0',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'costs' => 'nullable|array',
            'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
            'costs.*.description' => 'nullable|string|max:1000',
            'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_order_id.required' => 'Purchase order harus dipilih.',
            'purchase_order_id.exists' => 'Purchase order tidak ditemukan.',
            'invoice_date.required' => 'Tanggal invoice harus diisi.',
            'due_date.required' => 'Tanggal jatuh tempo harus diisi.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice.',
            'status.required' => 'Status invoice harus diisi.',
            'details.required' => 'Daftar item invoice harus diisi.',
            'details.*.goods_receipt_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item GR.',
            'details.*.purchase_order_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item PO.',
            'details.*.product_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item PO.',
            'details.*.quantity.required' => 'Terdapat produk yang belum diisi jumlah. Silakan isi jumlah yang diharapkan untuk setiap produk.',
            'details.*.unit_price.required' => 'Terdapat produk yang belum diisi harga satuannya. Silakan isi harga satuan untuk setiap produk.',
            'details.*.discount_percentage.required' => 'Terdapat produk yang belum diisi persentase diskon. Silakan isi persentase diskon untuk setiap produk.',
            'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
            'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
        ];
    }
}
