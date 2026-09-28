@extends('layouts.print', ['pageTitle' => 'Surat Jalan ' . $deliveryOrder->number])
@section('content')
    @php
        // Batches the goods are taken from; several are listed with their quantity, e.g. "JAG-...-0001 (40)"
        $rows = $deliveryOrder->items->map(fn ($it) => [
            $it->product?->name,
            $it->product?->code,
            $it->batches->count() > 1
                ? $it->batches->map(fn ($b) => $b->productBatch?->batch_number . ' (' . fmt_doc_qty($b->quantity) . ')')->implode(', ')
                : $it->batches->first()?->productBatch?->batch_number,
            fmt_doc_qty($it->quantity),
            $it->product?->unit?->symbol,
        ])->all();
    @endphp
    <x-print.document
        title="Surat Jalan"
        :meta="[
            'Nomor' => $deliveryOrder->number,
            'Tanggal' => $deliveryOrder->delivery_date?->format('d/m/Y'),
            'Ref. SO' => $deliveryOrder->salesOrder?->number,
            'Gudang Asal' => $deliveryOrder->warehouse?->name,
        ]"
        :parties="[
            \App\Support\PrintDocument::company(),
            \App\Support\PrintDocument::party('Kirim Kepada', $deliveryOrder->customer),
        ]"
        :columns="[
            ['label' => 'Produk'],
            ['label' => 'Kode'],
            ['label' => 'Batch'],
            ['label' => 'Kuantitas', 'align' => 'right'],
            ['label' => 'Satuan', 'align' => 'center'],
        ]"
        :rows="$rows"
        :totals="[['label' => 'Total Dikirim', 'value' => fmt_doc_qty($deliveryOrder->items->sum('quantity'))]]"
        :note="$deliveryOrder->note"
        :signatures="[
            ['caption' => 'Pengirim,', 'name' => null, 'role' => config('company.letterhead.name')],
            ['caption' => 'Penerima,', 'name' => null, 'role' => $deliveryOrder->customer?->name],
        ]" />
@endsection
