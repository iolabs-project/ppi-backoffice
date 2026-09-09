@extends('layouts.app')
@section('content')
@php
  $tabs = collect([
    ['id'=>'balance-sheet', 'label'=>'Neraca', 'permission'=>'reports.balance-sheet.view'],
    ['id'=>'cash-flow', 'label'=>'Arus Kas', 'permission'=>'reports.cash-flow.view'],
    ['id'=>'profit-loss', 'label'=>'Laba Rugi', 'permission'=>'reports.profit-loss.view'],
    ['id'=>'executive', 'label'=>'Eksekutif', 'permission'=>'reports.executive.view'],
    ['id'=>'receivable', 'label'=>'Piutang', 'permission'=>'reports.receivable.view'],
    ['id'=>'payable', 'label'=>'Utang', 'permission'=>'reports.payable.view'],
    ['id'=>'journal', 'label'=>'Jurnal Umum', 'permission'=>'reports.journal.view'],
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
    @foreach($tabs as $t)
    <a href="{{ route('reports.show', $t['id']) }}" class="utab-item">{!! $t['label'] !!}</a>
    @endforeach
  </div>

  <div class="card" style="padding:56px 24px; text-align:center;">
    <x-misc.icon name="clipboard" :size="28" stroke="var(--ink-4)" />
    <div class="display" style="font-size:15px; font-weight:700; margin-top:14px;">Pilih Laporan</div>
    <div style="font-size:13px; color:var(--ink-3); margin-top:4px;">
      Pilih salah satu jenis laporan di atas untuk menampilkan datanya.
    </div>
  </div>

</div>
@endsection
