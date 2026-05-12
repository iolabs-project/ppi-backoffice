@extends('layouts.erp')
@section('content')
<div style="padding:24px 28px 60px; display:grid; gap:16px;">
  <div>
    <a href="{{ route('erp.penjualan.show', $so['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-erp.icon name="chev-left" :size="13" />Kembali ke {{ $so['id'] }}
    </a>
    <div style="display:flex; align-items:center; gap:12px;">
      <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Buat Pengiriman</h1>
      <x-erp.status-badge status="draft" />
    </div>
    <div style="font-size:13px; color:var(--ink-4); margin-top:6px;">
      Pengiriman yang berhasil dibuat akan otomatis membentuk jurnal umum dan mengurangi stok di gudang asal.
    </div>
  </div>

  {{-- Info --}}
  <div class="card" style="padding:22px 24px; display:grid; gap:18px;">
    <div style="display:flex; justify-content:space-between; align-items:baseline;">
      <div class="display" style="font-weight:700; font-size:15px;">Informasi Pengiriman</div>
      <div style="font-size:11.5px; color:var(--ink-4);">
        <span style="color:var(--accent);">*</span> Field yang terisi otomatis dari SO
      </div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
      <x-erp.field label="Customer" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:10px; background:var(--bg-2);">
          <x-erp.avatar :name="$so['customer']" />
          <span style="flex:1; font-weight:500;">{{ $so['customer'] }}</span>
          <span style="font-size:10.5px; color:var(--ink-4); text-transform:uppercase; letter-spacing:.06em;">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="No. Pemesanan" :required="true">
        <div class="input mono" style="display:flex; align-items:center; background:var(--bg-2);">
          <span style="flex:1; font-weight:600;">{{ $so['id'] }}</span>
          <span style="font-size:10.5px; color:var(--ink-4); text-transform:uppercase; letter-spacing:.06em;">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Gudang" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px; background:var(--bg-2);">
          <x-erp.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $so['gudang'] }}</span>
          <span style="font-size:10.5px; color:var(--ink-4); text-transform:uppercase; letter-spacing:.06em;">Auto</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Nomor Pengiriman">
        <input class="input mono" value="DO-2026-0089" />
      </x-erp.field>
      <x-erp.field label="Tanggal Pengiriman">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="calendar" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">09 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Ekspedisi">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="truck" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">Internal – Truk Box L300</span>
          <x-erp.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-erp.field>
    </div>
    <x-erp.field label="Notes">
      <textarea class="input" rows="2">Muat dari rak A-3 dan B-1. Konfirmasi ke Pak Tarno sebelum berangkat. Surat jalan rangkap 3.</textarea>
    </x-erp.field>
  </div>

  {{-- Products --}}
  <div class="card" style="overflow:hidden;">
    <div style="padding:14px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
      <div class="display" style="font-weight:700; font-size:15px;">Produk dari Sales Order</div>
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
    <div style="display:grid; grid-template-columns:1fr 360px; border-top:1px solid var(--line);">
      <div style="padding:18px 22px;">
        <div class="label">Driver</div>
        <div style="display:flex; align-items:center; gap:10px; margin-top:4px;">
          <x-erp.avatar name="Sutrisno Hadi" />
          <div>
            <div style="font-weight:600; font-size:13px;">Sutrisno Hadi</div>
            <div class="mono" style="font-size:11px; color:var(--ink-4);">B 9821 KAB · ETA 14:30</div>
          </div>
        </div>
      </div>
      <div style="padding:18px 22px; background:var(--bg-2); border-left:1px solid var(--line);">
        <x-erp.field label="Biaya Pengiriman">
          <input class="input num" style="text-align:right;" value="1800000" />
        </x-erp.field>
        <div style="font-size:11.5px; color:var(--ink-4); margin-top:8px; line-height:1.5;">
          Akan dicatat sebagai <strong style="color:var(--ink);">HPP – Biaya Pengiriman</strong> dan masuk ke jurnal umum saat pengiriman disimpan.
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('erp.penjualan.show', $so['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan Pengiriman</button>
  </div>
</div>
@endsection
