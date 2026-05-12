@extends('layouts.erp')
@section('content')
@php
  $subtotal   = array_sum(array_map(fn($i) => $i['qtyDiterima'] * $i['harga'], $poDetailItems));
  $biayaKirim = 2_650_000;
  $ppn        = $subtotal * 0.11;
  $total      = $subtotal + $biayaKirim + $ppn;
@endphp
<div style="padding:24px 28px 60px; display:grid; gap:16px;">
  <div>
    <a href="{{ route('erp.pembelian.show', $po['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-erp.icon name="chev-left" :size="13" />Kembali ke {{ $po['id'] }}
    </a>
    <div style="display:flex; align-items:center; gap:12px;">
      <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Buat Tagihan dari Vendor</h1>
      <x-erp.status-badge status="draft" />
    </div>
    <div style="font-size:13px; color:var(--ink-4); margin-top:6px;">
      Tagihan akan otomatis membentuk jurnal umum. <strong style="color:var(--ink);">HPP final akan menyesuaikan biaya pengiriman aktual</strong> dari vendor.
    </div>
  </div>

  <div class="card" style="padding:22px 24px; display:grid; gap:18px;">
    <div class="display" style="font-weight:700; font-size:15px;">Informasi Tagihan</div>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
      <x-erp.field label="Vendor" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:10px; background:var(--bg-2);">
          <x-erp.avatar :name="$po['vendor']" />
          <span style="flex:1; font-weight:500;">{{ $po['vendor'] }}</span>
          <span style="font-size:10.5px; color:var(--ink-4); text-transform:uppercase; letter-spacing:.06em;">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="No. Invoice"><input class="input mono" placeholder="cth. INV-BFM-23104" /></x-erp.field>
      <x-erp.field label="Tanggal Transaksi" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">12 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Nomor Pengiriman" :required="true">
        <div class="input mono" style="display:flex; align-items:center; background:var(--bg-2);">
          <span style="flex:1; font-weight:600;">GRN-2026-0072</span>
          <span style="font-size:10.5px; color:var(--ink-4); text-transform:uppercase; letter-spacing:.06em;">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Gudang" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px; background:var(--bg-2);">
          <x-erp.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $po['gudang'] }}</span>
          <span style="font-size:10.5px; color:var(--ink-4); text-transform:uppercase; letter-spacing:.06em;">Auto</span>
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
    <div style="padding:14px 22px; border-bottom:1px solid var(--line);">
      <div class="display" style="font-weight:700; font-size:15px;">Daftar Produk</div>
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
    <div style="display:grid; grid-template-columns:1fr 360px; border-top:1px solid var(--line);">
      <div style="padding:18px 22px; font-size:12.5px; color:var(--ink-3); max-width:480px;">
        HPP final akan disesuaikan otomatis berdasarkan biaya pengiriman fix di atas. Variance dengan estimasi awal akan dicatat ke jurnal.
      </div>
      <div style="padding:22px; background:var(--bg-2); border-left:1px solid var(--line); display:grid; gap:8px;">
        @foreach([['Subtotal Produk',$subtotal],['Biaya Pengiriman',$biayaKirim],['PPN 11%',$ppn]] as [$lbl,$val])
          <div style="display:flex; justify-content:space-between; font-size:13px;">
            <span style="color:var(--ink-3);">{{ $lbl }}</span>
            <span class="num" style="font-weight:500;">{{ fmt_rp($val) }}</span>
          </div>
        @endforeach
        <div style="height:1px; background:var(--line-2); margin:4px 0;"></div>
        <div style="display:flex; justify-content:space-between; align-items:baseline;">
          <span style="font-size:13px; font-weight:600;">Total Tagihan</span>
          <span class="display num" style="font-size:22px; font-weight:700; color:var(--accent);">{{ fmt_rp($total) }}</span>
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
