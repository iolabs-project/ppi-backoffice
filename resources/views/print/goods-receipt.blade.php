@extends('layouts.print', ['pageTitle' => 'Penerimaan Barang ' . $goodsReceipt->number])
@section('content')
    <x-print.document
        title="Penerimaan Barang"
        :meta="[
            'Nomor' => $goodsReceipt->number,
            'Tanggal' => $goodsReceipt->receipt_date?->format('d/m/Y'),
            'Ref. PO' => $goodsReceipt->purchaseOrder?->number,
            'Gudang' => $goodsReceipt->warehouse?->name,
        ]"
        :parties="[
            \App\Support\PrintDocument::company(),
            \App\Support\PrintDocument::party('Diterima Dari', $goodsReceipt->supplier),
        ]"
        :columns="[
            ['label' => 'Produk'],
            ['label' => 'Kode'],
            ['label' => 'Batch'],
            ['label' => 'Dipesan', 'align' => 'right'],
            ['label' => 'Diterima', 'align' => 'right'],
            ['label' => 'Susut', 'align' => 'right'],
            ['label' => 'Satuan', 'align' => 'center'],
        ]"
        :rows="$goodsReceipt->items->map(fn ($it) => [
            $it->product?->name,
            $it->product?->code,
            $it->batch_number,
            fmt_doc_qty($it->expected_quantity),
            fmt_doc_qty($it->received_quantity),
            fmt_doc_qty($it->shrinkage_quantity),
            $it->product?->unit?->symbol,
        ])->all()"
        :totals="[
            ['label' => 'Total Diterima', 'value' => fmt_doc_qty($goodsReceipt->items->sum('received_quantity'))],
            ['label' => 'Total Susut', 'value' => fmt_doc_qty($goodsReceipt->items->sum('shrinkage_quantity'))],
        ]"
        :note="$goodsReceipt->note"
        :signatures="[
            ['caption' => 'Diserahkan oleh,', 'name' => null, 'role' => $goodsReceipt->supplier?->name],
            ['caption' => 'Diterima oleh,', 'name' => null, 'role' => config('company.letterhead.name')],
        ]" />
@endsection
