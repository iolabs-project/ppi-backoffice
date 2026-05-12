@extends('layouts.erp')
@section('content')
@php
  $masukTotal  = array_sum(array_column(array_filter($transaksiKas, fn($t) => $t['masuk'] > 0), 'masuk'));
  $keluarTotal = array_sum(array_column(array_filter($transaksiKas, fn($t) => $t['keluar'] > 0), 'keluar'));
@endphp
<div style="padding:24px 28px 60px; display:grid; gap:16px;">

  <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
    <div>
      <a href="{{ route('erp.kasbank.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-erp.icon name="chev-left" :size="13" />Kembali ke Kas &amp; Bank
      </a>
      <div style="display:flex; align-items:center; gap:12px;">
        <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">{{ $akun['nama'] }}</h1>
      </div>
      <div style="font-size:13px; color:var(--ink-4); margin-top:6px;">
        {{ $akun['bank'] }}@if($akun['norek']) · <span class="mono">{{ $akun['norek'] }}</span>@endif
      </div>
    </div>
    <div style="display:flex; gap:8px;">
      <button class="btn btn-ghost"><x-erp.icon name="download" :size="14" />Ekspor</button>
    </div>
  </div>

  {{-- Saldo + stat cards --}}
  <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
    <div class="card" style="padding:20px 22px; background:var(--ink); color:var(--paper);">
      <div style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; opacity:.55; margin-bottom:8px;">Saldo Saat Ini</div>
      <div class="display num" style="font-size:26px; font-weight:700;">{{ fmt_rp($akun['saldo']) }}</div>
    </div>
    <div class="card" style="padding:20px 22px;">
      <div class="label" style="margin-bottom:8px; color:var(--good);">↑ Total Masuk</div>
      <div class="display num" style="font-size:22px; font-weight:700; color:var(--good);">{{ fmt_rp($masukTotal) }}</div>
      <div style="font-size:11.5px; color:var(--ink-4); margin-top:4px;">Bulan ini</div>
    </div>
    <div class="card" style="padding:20px 22px;">
      <div class="label" style="margin-bottom:8px; color:var(--bad);">↓ Total Keluar</div>
      <div class="display num" style="font-size:22px; font-weight:700; color:var(--bad);">{{ fmt_rp($keluarTotal) }}</div>
      <div style="font-size:11.5px; color:var(--ink-4); margin-top:4px;">Bulan ini</div>
    </div>
  </div>

  {{-- Transaction history --}}
  <div class="card" style="overflow:hidden;">
    <div style="padding:14px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
      <div class="display" style="font-weight:700; font-size:15px;">Riwayat Transaksi</div>
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
