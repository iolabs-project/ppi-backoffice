@extends('layouts.app')
@section('content')
    @php
        $tabs = collect([
            ['id' => 'balance-sheet', 'label' => 'Neraca', 'permission' => 'reports.balance-sheet.view'],
            ['id' => 'cash-flow', 'label' => 'Arus Kas', 'permission' => 'reports.cash-flow.view'],
            ['id' => 'profit-loss', 'label' => 'Laba Rugi', 'permission' => 'reports.profit-loss.view'],
            ['id' => 'executive', 'label' => 'Eksekutif', 'permission' => 'reports.executive.view'],
            ['id' => 'receivable', 'label' => 'Piutang', 'permission' => 'reports.receivable.view'],
            ['id' => 'payable', 'label' => 'Utang', 'permission' => 'reports.payable.view'],
            ['id' => 'journal', 'label' => 'Jurnal Umum', 'permission' => 'reports.journal.view'],
        ])->filter(fn (array $tab): bool => auth()->user()->can($tab['permission']))->values();
    @endphp
    <div class="laporan-page">

        <div class="laporan-hd">
            <div>
                <h1 class="order-title display">Laporan Keuangan</h1>
                {{-- <div class="order-sub">Periode Januari - Mei 2026</div> --}}
            </div>
        </div>

        {{-- Tab bar --}}
        <div class="utab">
            @foreach ($tabs as $t)
                <a href="{{ route('reports.show', $t['id']) }}"
                    class="utab-item {{ $activeTab === $t['id'] ? 'utab-active' : '' }}">{!! $t['label'] !!}</a>
            @endforeach
        </div>

        @includeWhen(
            $tabs->contains('id', $activeTab),
            'report.partials.' . $activeTab
        )

    </div>
    @stack('balance-sheet-scripts')
    @stack('cash-flow-scripts')
    @stack('executive-scripts')
    @stack('receivable-scripts')
    @stack('payable-scripts')
    @stack('profit-loss-scripts')
    @stack('journal-scripts')
@endsection
