@extends('layouts.print', ['pageTitle' => 'Tagihan Pembelian ' . $purchaseInvoice->number])
@section('content')
    <x-print.document
        title="Tagihan Pembelian"
        :meta="[
            'Nomor' => $purchaseInvoice->number,
            'Tanggal' => $purchaseInvoice->invoice_date?->format('d/m/Y'),
            'Tgl. Jatuh Tempo' => $purchaseInvoice->due_date?->format('d/m/Y'),
            'Ref. PO' => $purchaseInvoice->purchaseOrder?->number,
        ]"
        :parties="[
            \App\Support\PrintDocument::company(),
            \App\Support\PrintDocument::party('Tagihan Dari', $purchaseInvoice->supplier),
        ]"
        :columns="\App\Support\PrintDocument::tradeColumns()"
        :rows="\App\Support\PrintDocument::tradeRows($purchaseInvoice->items, $purchaseInvoice->tax_percentage)"
        :totals="\App\Support\PrintDocument::tradeTotals($purchaseInvoice, $purchaseInvoice->items, (float) $purchaseInvoice->remaining_amount)"
        :amount-in-words="$purchaseInvoice->total_amount"
        :note="$purchaseInvoice->note"
        :signatures="[
            ['caption' => 'Dibuat oleh,', 'name' => $purchaseInvoice->creator?->username, 'role' => null],
            ['caption' => 'Disetujui oleh,', 'name' => null, 'role' => null],
        ]" />
@endsection
