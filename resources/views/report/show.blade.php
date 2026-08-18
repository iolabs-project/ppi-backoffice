@extends('layouts.app')
@section('content')
    @php
        $tabs = [
            ['id' => 'balance-sheet', 'label' => 'Neraca'],
            ['id' => 'cash-flow', 'label' => 'Arus Kas'],
            ['id' => 'profit-loss', 'label' => 'Laba Rugi'],
            ['id' => 'executive', 'label' => 'Eksekutif'],
            ['id' => 'receivable', 'label' => 'Piutang'],
            ['id' => 'payable', 'label' => 'Utang'],
            ['id' => 'journal', 'label' => 'Jurnal Umum'],
        ];
    @endphp
    <div class="laporan-page">

        <div class="laporan-hd">
            <div>
                <h1 class="order-title display">Laporan Keuangan</h1>
                {{-- <div class="order-sub">Periode Januari - Mei 2026</div> --}}
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-misc.icon name="print" :size="14" />Cetak</button>
                <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
            </div>
        </div>

        {{-- Tab bar --}}
        <div class="utab">
            @foreach ($tabs as $t)
                <a href="{{ route('reports.show', $t['id']) }}"
                    class="utab-item {{ $activeTab === $t['id'] ? 'utab-active' : '' }}">{!! $t['label'] !!}</a>
            @endforeach
        </div>

        @include('report.partials.' . $activeTab)

    </div>
    @stack('profit-loss-scripts')
    @stack('journal-scripts')
@endsection
