@extends('layouts.erp')
@section('content')
@php
  $masukTotal  = array_sum(array_column(array_filter($transaksiKas, fn($t) => $t['masuk'] > 0), 'masuk'));
  $keluarTotal = array_sum(array_column(array_filter($transaksiKas, fn($t) => $t['keluar'] > 0), 'keluar'));
@endphp
<div class="kasbank-page">

  <div class="order-hd order-hd--start">
    <div>
      <a href="{{ route('erp.kasbank.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-erp.icon name="chev-left" :size="13" />Kembali ke Kas &amp; Bank
      </a>
      <h1 class="order-title display">{{ $akun['nama'] }}</h1>
      <div class="order-sub">
        {{ $akun['bank'] }}@if($akun['norek']) · <span class="mono">{{ $akun['norek'] }}</span>@endif
      </div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost"><x-erp.icon name="download" :size="14" />Ekspor</button>
    </div>
  </div>

  {{-- Saldo + stat cards --}}
  <div class="stat-cards">
    <div class="card stat-card stat-card--dark">
      <div class="stat-card__label">Saldo Saat Ini</div>
      <div class="stat-card__value display num">{{ fmt_rp($akun['saldo']) }}</div>
    </div>
    <div class="card stat-card stat-card--good">
      <div class="label stat-card__label">↑ Total Masuk</div>
      <div class="stat-card__value display num">{{ fmt_rp($masukTotal) }}</div>
      <div class="stat-card__sub">Bulan ini</div>
    </div>
    <div class="card stat-card stat-card--bad">
      <div class="label stat-card__label">↓ Total Keluar</div>
      <div class="stat-card__value display num">{{ fmt_rp($keluarTotal) }}</div>
      <div class="stat-card__sub">Bulan ini</div>
    </div>
  </div>

  {{-- Transaction history --}}
  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Riwayat Transaksi</div>
      <div style="font-size:12px; color:var(--ink-4);">{{ count($transaksiKas) }} transaksi</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:130px;">Tanggal</th>
        <th>Keterangan</th>
        <th>Ref</th>
        <th style="text-align:right; width:160px;">Masuk</th>
        <th style="text-align:right; width:160px;">Keluar</th>
        <th style="text-align:right; width:180px;">Saldo</th>
      </tr></thead>
      <tbody>
        @foreach($transaksiKas as $tx)
        <tr>
          <td style="color:var(--ink-3); white-space:nowrap;">{{ $tx['tanggal'] }}</td>
          <td>
            <div style="font-weight:500;">{{ $tx['keterangan'] }}</div>
            @if(!empty($tx['akun']))<div style="font-size:11.5px; color:var(--ink-4);">{{ $tx['akun'] }}</div>@endif
          </td>
          <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $tx['ref'] ?? '—' }}</td>
          <td class="num" style="text-align:right; color:var(--good); font-weight:{{ $tx['masuk'] ? 600 : 400 }};">
            {{ $tx['masuk'] ? fmt_rp($tx['masuk']) : '' }}
          </td>
          <td class="num" style="text-align:right; color:var(--bad); font-weight:{{ $tx['keluar'] ? 600 : 400 }};">
            {{ $tx['keluar'] ? fmt_rp($tx['keluar']) : '' }}
          </td>
          <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($tx['saldo']) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
