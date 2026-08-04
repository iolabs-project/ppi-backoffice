<?php

namespace App\Http\Requests\Sales;

use App\Enums\DeliveryOrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeliveryOrderFormRequest extends FormRequest
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
        if ($this->routeIs('sales.delivery_orders.store')) {
            return [
                'sales_order_id' => 'required|exists:sales_orders,id',
            ];
        }

        if ($this->input('status') === DeliveryOrderStatus::DRAFT->value) {
            return $this->draftRules();
        } else if ($this->input('status') === DeliveryOrderStatus::FINISHED->value) {
            return $this->finishedRules();
        }

        return [];
    }

    private function draftRules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:50',
            'delivery_date' => 'required|date',
            'status' => 'required|in:' . DeliveryOrderStatus::DRAFT->value . ',' . DeliveryOrderStatus::FINISHED->value,
            'note' => 'nullable|string|max:1000',
        ];
    }

    private function finishedRules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:50',
            'delivery_date' => 'required|date',
            'status' => 'required|in:' . DeliveryOrderStatus::DRAFT->value . ',' . DeliveryOrderStatus::FINISHED->value,
            'note' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:0.0001',
            'details.*.batches' => 'required|array|min:1',
            'details.*.batches.*.product_batch_id' => 'required|exists:product_batches,id',
            'details.*.batches.*.quantity' => 'required|numeric|min:0.0001',
            'costs' => 'nullable|array',
            'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
            'costs.*.description' => 'nullable|string|max:1000',
            'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'sales_order_id.required' => 'Sales order harus dipilih.',
            'sales_order_id.exists' => 'Sales order tidak ditemukan.',
            'delivery_date.required' => 'Tanggal pengiriman harus diisi.',
            'status.required' => 'Status pengiriman harus diisi.',
            'status.in' => 'Status pengiriman tidak valid.',
            'details.required' => 'Daftar produk pengiriman harus diisi.',
            'details.*.sales_order_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item SO.',
            'details.*.product_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item SO.',
            'details.*.quantity.required' => 'Terdapat produk yang belum diisi jumlah yang akan dikirim.',
            'details.*.batches.required' => 'Terdapat produk yang belum memilih batch. Silakan pilih batch untuk setiap produk.',
            'details.*.batches.*.product_batch_id.required' => 'Terdapat produk yang belum memilih batch. Silakan pilih batch untuk setiap produk.',
            'details.*.batches.*.quantity.required' => 'Terdapat batch yang belum diisi jumlahnya.',
            'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
            'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
        ];
    }
}
