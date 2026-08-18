@extends('layouts.app')
@section('content')
@php
  $tabs = [
    ['id'=>'balance-sheet',     'label'=>'Neraca'],
    ['id'=>'cash-flow',    'label'=>'Arus Kas'],
    ['id'=>'profit-loss',   'label'=>'Laba Rugi'],
    ['id'=>'executive',  'label'=>'Eksekutif'],
    ['id'=>'payable-receivable',      'label'=>'Utang &amp; Piutang'],
    ['id'=>'journal',     'label'=>'Jurnal Umum'],
  ];
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
