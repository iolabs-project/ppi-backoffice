<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentFormRequest extends FormRequest
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
            'adjustment_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|integer|exists:products,id',
            'details.*.product_batch_id' => 'required|integer|exists:product_batches,id',
            'details.*.counted_quantity' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'adjustment_date.required' => 'Tanggal penyesuaian harus diisi.',
            'adjustment_date.date' => 'Tanggal penyesuaian harus berupa tanggal yang valid.',
            'note.string' => 'Catatan harus berupa teks.',
            'details.required' => 'Rincian penyesuaian harus diisi.',
            'details.array' => 'Rincian penyesuaian harus berupa array.',
            'details.*.product_id.required' => 'Produk harus diisi.',
            'details.*.product_id.integer' => 'Produk harus berupa angka.',
            'details.*.product_id.exists' => 'Produk tidak ditemukan.',
            'details.*.product_batch_id.required' => 'Batch produk harus diisi.',
            'details.*.product_batch_id.integer' => 'Batch produk harus berupa angka.',
            'details.*.product_batch_id.exists' => 'Batch produk tidak ditemukan.',
            'details.*.counted_quantity.required' => 'Kuantitas hasil hitung harus diisi.',
            'details.*.counted_quantity.numeric' => 'Kuantitas hasil hitung harus berupa angka.',
            'details.*.counted_quantity.min' => 'Kuantitas hasil hitung tidak boleh kurang dari 0.',
        ];
    }
}
