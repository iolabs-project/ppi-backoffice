@extends('layouts.print', ['pageTitle' => 'Purchase Order ' . $purchaseOrder->number])
@section('content')
    <x-print.document
        title="Purchase Order"
        :meta="[
            'Nomor' => $purchaseOrder->number,
            'Tanggal' => $purchaseOrder->order_date?->format('d/m/Y'),
            'Tgl. Jatuh Tempo' => $purchaseOrder->due_date?->format('d/m/Y'),
            'Gudang Tujuan' => $purchaseOrder->warehouse?->name,
        ]"
        :parties="[
            \App\Support\PrintDocument::company(),
            \App\Support\PrintDocument::party('Kepada', $purchaseOrder->supplier),
        ]"
        :columns="\App\Support\PrintDocument::tradeColumns()"
        :rows="\App\Support\PrintDocument::tradeRows($purchaseOrder->items, $purchaseOrder->tax_percentage)"
        :totals="\App\Support\PrintDocument::tradeTotals($purchaseOrder, $purchaseOrder->items)"
        :amount-in-words="$purchaseOrder->total_amount"
        :note="$purchaseOrder->note"
        :signatures="[['caption' => 'Dengan Hormat,', 'name' => config('company.letterhead.name'), 'role' => 'Jabatan']]" />
@endsection
