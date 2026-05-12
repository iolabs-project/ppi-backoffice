@extends('layouts.erp')
@section('content')
@php
  $totalDiterima = array_sum(array_column($poDetailItems, 'qtyDiterima'));
  $totalSusut    = array_sum(array_column($poDetailItems, 'susut'));
  $estHpp        = array_sum(array_map(fn($i) => $i['qtyDiterima'] * $i['harga'], $poDetailItems)) + 2_400_000;
@endphp
<div style="padding:24px 28px 60px; display:grid; gap:16px;">
  <div>
    <a href="{{ route('erp.pembelian.show', $po['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-erp.icon name="chev-left" :size="13" />Kembali ke {{ $po['id'] }}
    </a>
    <div style="display:flex; align-items:center; gap:12px;">
      <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Buat Pengiriman Masuk</h1>
      <x-erp.status-badge status="draft" />
    </div>
    <div style="font-size:13px; color:var(--ink-4); margin-top:6px;">
      Pengiriman yang berhasil disimpan akan otomatis membentuk jurnal umum dan <strong style="color:var(--ink);">menambah stok</strong> di gudang tujuan.
    </div>
  </div>

  <div class="card" style="padding:22px 24px; display:grid; gap:18px;">
    <div style="display:flex; justify-content:space-between; align-items:baseline;">
      <div class="display" style="font-weight:700; font-size:15px;">Informasi Pengiriman</div>
      <div style="font-size:11.5px; color:var(--ink-4);"><span style="color:var(--accent);">*</span> Field terisi otomatis dari PO</div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
      <x-erp.field label="Vendor" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:10px; background:var(--bg-2);">
          <x-erp.avatar :name="$po['vendor']" />
          <span style="flex:1; font-weight:500;">{{ $po['vendor'] }}</span>
          <span style="font-size:10.5px; color:var(--ink-4); text-transform:uppercase; letter-spacing:.06em;">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="No. Pemesanan" :required="true">
        <div class="input mono" style="display:flex; align-items:center; background:var(--bg-2);">
          <span style="flex:1; font-weight:600;">{{ $po['id'] }}</span>
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
      <x-erp.field label="Nomor Pengiriman"><input class="input mono" value="GRN-2026-0072" /></x-erp.field>
      <x-erp.field label="Tanggal Pengiriman">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">12 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Surat Jalan Vendor"><input class="input mono" placeholder="cth. SJ-BFM-9842" /></x-erp.field>
    </div>
    <x-erp.field label="Notes">
      <textarea class="input" rows="2">2 sak Cakra Kembar robek; foto sudah dikirim ke claim Bogasari. Disepakati menjadi susut.</textarea>
    </x-erp.field>
  </div>

  <div class="card" style="overflow:hidden;">
    <div style="padding:14px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
      <div class="display" style="font-weight:700; font-size:15px;">Produk dari PO</div>
      <div style="font-size:11.5px; color:var(--ink-4);">Total diterima <strong style="color:var(--ink);">{{ $totalDiterima }}</strong> · Susut <strong style="color:var(--bad);">{{ $totalSusut }}</strong></div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th><th>Produk</th>
        <th style="text-align:right; width:120px;">Qty Pesan</th>
        <th style="text-align:right; width:140px;">Qty Diterima</th>
        <th style="text-align:right; width:120px;">Susut</th>
        <th style="width:140px;">Satuan</th>
      </tr></thead>
      <tbody>
        @foreach($poDetailItems as $i => $it)
          <tr>
            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</td>
            <td>
              <div style="font-weight:600;">{{ $it['nama'] }}</div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it['kode'] }}</div>
            </td>
            <td class="num" style="text-align:right; color:var(--ink-4);">{{ $it['qty'] }}</td>
            <td><input class="input num" style="height:32px; text-align:right;" value="{{ $it['qtyDiterima'] }}" /></td>
            <td><input class="input num" style="height:32px; text-align:right;" value="{{ $it['susut'] }}" /></td>
            <td style="color:var(--ink-3);">{{ $it['satuan'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div style="display:grid; grid-template-columns:1fr 360px; border-top:1px solid var(--line);">
      <div style="padding:18px 22px;">
        <x-erp.field label="Estimasi Biaya Pengiriman">
          <input class="input num" style="text-align:right;" value="2400000" />
        </x-erp.field>
        <div style="font-size:11.5px; color:var(--ink-4); margin-top:8px; line-height:1.5; max-width:380px;">
          Estimasi ini akan dipakai untuk menghitung HPP awal. Nilai final akan diperbarui saat tagihan dari vendor diterbitkan.
        </div>
      </div>
      <div style="padding:22px; background:var(--bg-2); border-left:1px solid var(--line);">
        <div class="label" style="margin-bottom:6px;">Estimasi HPP Total</div>
        <div class="display num" style="font-size:22px; font-weight:700; color:var(--accent);">{{ fmt_rp($estHpp) }}</div>
        <div style="font-size:11.5px; color:var(--ink-4); margin-top:8px;">
          {{ $totalDiterima }} unit diterima · HPP/unit ≈ <span class="mono">{{ fmt_rp(round($estHpp / max($totalDiterima,1))) }}</span>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('erp.pembelian.show', $po['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Pengiriman</button>
  </div>
</div>
@endsection
