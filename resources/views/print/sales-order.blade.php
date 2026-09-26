@extends('layouts.print', ['pageTitle' => 'Sales Order ' . $salesOrder->number])
@section('content')
    <x-print.document
        title="Sales Order"
        :meta="[
            'Nomor' => $salesOrder->number,
            'Tanggal' => $salesOrder->order_date?->format('d/m/Y'),
            'Tgl. Jatuh Tempo' => $salesOrder->due_date?->format('d/m/Y'),
            'Gudang' => $salesOrder->warehouse?->name,
        ]"
        :parties="[
            \App\Support\PrintDocument::company(),
            \App\Support\PrintDocument::party('Pemesan', $salesOrder->customer),
        ]"
        :columns="\App\Support\PrintDocument::tradeColumns()"
        :rows="\App\Support\PrintDocument::tradeRows($salesOrder->items, $salesOrder->tax_percentage)"
        :totals="\App\Support\PrintDocument::tradeTotals($salesOrder, $salesOrder->items)"
        :amount-in-words="$salesOrder->total_amount"
        :note="$salesOrder->note"
        :signatures="[['caption' => 'Dengan Hormat,', 'name' => config('company.letterhead.name'), 'role' => 'Jabatan']]" />
@endsection
