@extends('layouts.print', ['pageTitle' => 'Invoice ' . $salesInvoice->number])
@section('content')
    <x-print.document
        title="Invoice"
        :meta="[
            'Nomor' => $salesInvoice->number,
            'Tanggal' => $salesInvoice->invoice_date?->format('d/m/Y'),
            'Tgl. Jatuh Tempo' => $salesInvoice->due_date?->format('d/m/Y'),
        ]"
        :parties="[
            \App\Support\PrintDocument::company(),
            \App\Support\PrintDocument::party('Tagihan Kepada', $salesInvoice->customer),
        ]"
        :columns="\App\Support\PrintDocument::tradeColumns()"
        :rows="\App\Support\PrintDocument::tradeRows($salesInvoice->items, $salesInvoice->tax_percentage)"
        :totals="\App\Support\PrintDocument::tradeTotals($salesInvoice, $salesInvoice->items, (float) $salesInvoice->remaining_amount)"
        :amount-in-words="$salesInvoice->total_amount"
        :note="$salesInvoice->note"
        :signatures="[['caption' => 'Dengan Hormat,', 'name' => config('company.letterhead.name'), 'role' => 'Jabatan']]" />
@endsection
