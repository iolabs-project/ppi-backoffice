@extends('layouts.print', ['pageTitle' => 'Bukti Biaya ' . $expense->number])
@section('content')
    @php
        $subtotal = (float) $expense->items->sum('amount');
        $discount = (float) $expense->discount_amount;
        $tax = (float) $expense->tax_amount;
        // Whatever is left between the lines above and the stored total is the additional costs
        $other = round((float) $expense->total_amount - ($subtotal - $discount + $tax), 2);
        $totals = array_values(array_filter([
            ['label' => 'Subtotal', 'value' => 'Rp ' . fmt_doc($subtotal)],
            $discount > 0 ? ['label' => 'Diskon', 'value' => '(Rp ' . fmt_doc($discount) . ')'] : null,
            $tax > 0 ? ['label' => 'Pajak ' . fmt_doc_percent($expense->tax_percentage), 'value' => 'Rp ' . fmt_doc($tax)] : null,
            abs($other) >= 0.5 ? ['label' => 'Biaya Tambahan', 'value' => 'Rp ' . fmt_doc($other)] : null,
            ['label' => 'Total', 'value' => 'Rp ' . fmt_doc($expense->total_amount), 'emphasis' => true],
            ['label' => 'Sisa Tagihan', 'value' => 'Rp ' . fmt_doc($expense->remaining_amount)],
        ]));
    @endphp
    <x-print.document
        title="Bukti Biaya"
        :meta="[
            'Nomor' => $expense->number,
            'Tanggal' => $expense->expense_date?->format('d/m/Y'),
            'Tgl. Jatuh Tempo' => $expense->due_date?->format('d/m/Y'),
            'No. Referensi' => $expense->reference_number,
        ]"
        :parties="[
            \App\Support\PrintDocument::company(),
            \App\Support\PrintDocument::party('Dibayarkan Kepada', $expense->contact),
        ]"
        :columns="[
            ['label' => 'Akun'],
            ['label' => 'Deskripsi'],
            ['label' => 'Jumlah', 'align' => 'right'],
        ]"
        :rows="$expense->items->map(fn ($it) => [
            trim(($it->account?->code ?? '') . ' - ' . ($it->account?->name ?? ''), ' -'),
            $it->description,
            fmt_doc($it->amount),
        ])->all()"
        :totals="$totals"
        :amount-in-words="$expense->total_amount"
        :note="$expense->note"
        :signatures="[
            ['caption' => 'Dibuat oleh,', 'name' => $expense->creator?->username, 'role' => null],
            ['caption' => 'Disetujui oleh,', 'name' => null, 'role' => null],
        ]" />
@endsection
