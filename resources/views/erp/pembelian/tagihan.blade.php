@extends('layouts.erp')
@section('content')
@php
  $subtotal   = array_sum(array_map(fn($i) => $i['qtyDiterima'] * $i['harga'], $poDetailItems));
  $biayaKirim = 2_650_000;
  $ppn        = $subtotal * 0.11;
  $total      = $subtotal + $biayaKirim + $ppn;
@endphp
<div class="order-page">
  <div>
    <a href="{{ route('erp.pembelian.show', $po['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-erp.icon name="chev-left" :size="13" />Kembali ke {{ $po['id'] }}
    </a>
    <div class="order-title-row">
      <h1 class="order-title display">Buat Tagihan dari Vendor</h1>
      <x-erp.status-badge status="draft" />
    </div>
    <div class="order-sub">
      Tagihan akan otomatis membentuk jurnal umum. <strong style="color:var(--ink);">HPP final akan menyesuaikan biaya pengiriman aktual</strong> dari vendor.
    </div>
  </div>

  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Tagihan</div>
    <div class="order-form-grid-3">
      <x-erp.field label="Vendor" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
          <x-erp.avatar :name="$po['vendor']" />
          <span style="flex:1; font-weight:500;">{{ $po['vendor'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="No. Invoice"><input class="input mono" placeholder="cth. INV-BFM-23104" /></x-erp.field>
      <x-erp.field label="Tanggal Transaksi" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">12 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Nomor Pengiriman" :required="true">
        <div class="input mono input--readonly" style="display:flex; align-items:center;">
          <span style="flex:1; font-weight:600;">GRN-2026-0072</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Gudang" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $po['gudang'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Tanggal Jatuh Tempo">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">26 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Biaya Pengiriman (Fixed)">
        <input class="input num" style="text-align:right;" value="{{ $biayaKirim }}" />
      </x-erp.field>
      <x-erp.field label="No. Faktur Pajak"><input class="input mono" placeholder="010.xxx-xx.xxxxxxxx" /></x-erp.field>
    </div>
  </div>

  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Daftar Produk</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th><th>Produk</th>
        <th style="text-align:right;">Qty Diterima</th><th>Satuan</th>
        <th style="text-align:right;">Harga</th><th style="text-align:right;">Subtotal</th>
      </tr></thead>
      <tbody>
        @foreach($poDetailItems as $i => $it)
          <tr>
            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</td>
            <td>
              <div style="font-weight:600;">{{ $it['nama'] }}</div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it['kode'] }}</div>
            </td>
            <td class="num" style="text-align:right; font-weight:600;">{{ $it['qtyDiterima'] }}</td>
            <td style="color:var(--ink-3);">{{ $it['satuan'] }}</td>
            <td class="num" style="text-align:right;">{{ fmt_rp($it['harga']) }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($it['qtyDiterima'] * $it['harga']) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="order-items-split">
      <div style="padding:18px 22px; font-size:12.5px; color:var(--ink-3); max-width:480px;">
        HPP final akan disesuaikan otomatis berdasarkan biaya pengiriman fix di atas. Variance dengan estimasi awal akan dicatat ke jurnal.
      </div>
      <div class="order-summary">
        @foreach([['Subtotal Produk',$subtotal],['Biaya Pengiriman',$biayaKirim],['PPN 11%',$ppn]] as [$lbl,$val])
          <div class="order-summary__row">
            <span class="order-summary__label">{{ $lbl }}</span>
            <span class="num" style="font-weight:500;">{{ fmt_rp($val) }}</span>
          </div>
        @endforeach
        <div class="order-summary__divider"></div>
        <div class="order-summary__total">
          <span class="order-summary__total-label">Total Tagihan</span>
          <span class="order-summary__total-value display num">{{ fmt_rp($total) }}</span>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('erp.pembelian.show', $po['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Tagihan</button>
  </div>
</div>
@endsection
