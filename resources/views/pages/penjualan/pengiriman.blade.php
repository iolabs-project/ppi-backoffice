@extends('layouts.app')
@section('content')
<div class="order-page">
  <div>
    <a href="{{ route('penjualan.show', $so['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali ke {{ $so['id'] }}
    </a>
    <div class="order-title-row">
      <h1 class="order-title display">Buat Pengiriman</h1>
      <x-misc.status-badge status="draft" />
    </div>
    <div class="order-sub">
      Pengiriman yang berhasil dibuat akan otomatis membentuk jurnal umum dan mengurangi stok di gudang asal.
    </div>
  </div>

  {{-- Info --}}
  <div class="card card-bd--form">
    <div class="shipping-form-info">
      <div class="display card-hd-title">Informasi Pengiriman</div>
      <div class="shipping-form-info__sub">
        <span style="color:var(--accent);">*</span> Field yang terisi otomatis dari SO
      </div>
    </div>
    <div class="order-form-grid-3">
      <x-misc.field label="Customer" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
          <x-misc.avatar :name="$so['customer']" />
          <span style="flex:1; font-weight:500;">{{ $so['customer'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-misc.field>
      <x-misc.field label="No. Pemesanan" :required="true">
        <div class="input mono input--readonly" style="display:flex; align-items:center;">
          <span style="flex:1; font-weight:600;">{{ $so['id'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Gudang" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $so['gudang'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Nomor Pengiriman">
        <input class="input mono" value="DO-2026-0089" />
      </x-misc.field>
      <x-misc.field label="Tanggal Pengiriman">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="calendar" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">09 Mei 2026</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Ekspedisi">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="truck" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">Internal – Truk Box L300</span>
          <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-misc.field>
    </div>
    <x-misc.field label="Notes">
      <textarea class="input" rows="2">Muat dari rak A-3 dan B-1. Konfirmasi ke Pak Tarno sebelum berangkat. Surat jalan rangkap 3.</textarea>
    </x-misc.field>
  </div>

  {{-- Products --}}
  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Produk dari Sales Order</div>
      <div style="font-size:11.5px; color:var(--ink-4);">Kuantitas terkunci dari {{ $so['id'] }}</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th><th>Produk</th>
        <th style="text-align:right; width:140px;">Qty Pesanan</th>
        <th style="text-align:right; width:140px;">Qty Dikirim</th>
        <th style="width:160px;">Satuan</th>
        <th style="width:160px;">Lokasi Rak</th>
      </tr></thead>
      <tbody>
        @foreach($soDetailItems as $i => $it)
          <tr>
            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</td>
            <td>
              <div style="font-weight:600;">{{ $it['nama'] }}</div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it['kode'] }}</div>
            </td>
            <td class="num" style="text-align:right; color:var(--ink-4);">{{ $it['qty'] }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ $it['qty'] }}</td>
            <td style="color:var(--ink-3);">{{ $it['satuan'] }}</td>
            <td class="mono" style="color:var(--ink-3);">{{ ['A-3','B-1','C-2'][$i % 3] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="order-items-split">
      <div class="shipping-driver">
        <div class="label">Driver</div>
        <div class="shipping-driver__row">
          <x-misc.avatar name="Sutrisno Hadi" />
          <div>
            <div class="shipping-driver__name">Sutrisno Hadi</div>
            <div class="mono" style="font-size:11px; color:var(--ink-4);">B 9821 KAB · ETA 14:30</div>
          </div>
        </div>
      </div>
      <div class="hpp-summary">
        <x-misc.field label="Biaya Pengiriman">
          <input class="input num" style="text-align:right;" value="1800000" />
        </x-misc.field>
        <div style="font-size:11.5px; color:var(--ink-4); margin-top:8px; line-height:1.5;">
          Akan dicatat sebagai <strong style="color:var(--ink);">HPP – Biaya Pengiriman</strong> dan masuk ke jurnal umum saat pengiriman disimpan.
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('penjualan.show', $so['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Pengiriman</button>
  </div>
</div>
@endsection
