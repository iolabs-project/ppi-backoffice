<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WarehouseTransferFormRequest extends FormRequest
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
            'to_warehouse_id' => 'required|integer|exists:warehouses,id',
            'transfer_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|integer|exists:products,id',
            'details.*.product_batch_id' => 'required|integer|exists:product_batches,id',
            'details.*.quantity' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'to_warehouse_id.required' => 'Tujuan gudang harus diisi.',
            'to_warehouse_id.integer' => 'Tujuan gudang harus berupa angka.',
            'to_warehouse_id.exists' => 'Tujuan gudang tidak ditemukan.',
            'transfer_date.required' => 'Tanggal transfer harus diisi.',
            'transfer_date.date' => 'Tanggal transfer harus berupa tanggal yang valid.',
            'note.string' => 'Catatan harus berupa teks.',
            'details.required' => 'Rincian transfer harus diisi.',
            'details.array' => 'Rincian transfer harus berupa array.',
            'details.*.product_id.required' => 'Produk harus diisi.',
            'details.*.product_id.integer' => 'Produk harus berupa angka.',
            'details.*.product_id.exists' => 'Produk tidak ditemukan.',
            'details.*.product_batch_id.required' => 'Batch produk harus diisi.',
            'details.*.product_batch_id.integer' => 'Batch produk harus berupa angka.',
            'details.*.product_batch_id.exists' => 'Batch produk tidak ditemukan.',
            'details.*.quantity.required' => 'Kuantitas harus diisi.',
            'details.*.quantity.numeric' => 'Kuantitas harus berupa angka.',
            'details.*.quantity.min' => 'Kuantitas harus lebih besar dari 0.',
        ];
    }
}
