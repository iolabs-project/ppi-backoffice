@extends('layouts.app')
@section('content')
@php
  $totalDiterima = array_sum(array_column($poDetailItems, 'qtyDiterima'));
  $totalSusut    = array_sum(array_column($poDetailItems, 'susut'));
  $estHpp        = array_sum(array_map(fn($i) => $i['qtyDiterima'] * $i['harga'], $poDetailItems)) + 2_400_000;
@endphp

<script>
  function penerimaanData() {
    return {
      fmtInput(e) {
        let el = e.target;
        let pos = el.value.slice(0, el.selectionStart).replace(/[^0-9]/g, '').length;
        let raw = el.value.replace(/[^0-9]/g, '');
        el.value = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        let i = 0, c = 0;
        while (i < el.value.length && c < pos) { if (/\d/.test(el.value[i])) c++; i++; }
        el.setSelectionRange(i, i);
      },
    };
  }
</script>

<div x-data="penerimaanData()" class="order-page">
  <div>
    <a href="{{ route('pembelian.show', $po['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali ke {{ $po['id'] }}
    </a>
    <div class="order-title-row">
      <h1 class="order-title display">Buat Penerimaan Barang</h1>
      <x-misc.status-badge status="draft" />
    </div>
    <div class="order-sub">
      Penerimaan yang berhasil disimpan akan otomatis membentuk jurnal umum dan <strong style="color:var(--ink);">menambah stok</strong> di gudang tujuan.
    </div>
  </div>

  <div class="card card-bd--form">
    <div class="shipping-form-info">
      <div class="display card-hd-title">Informasi Penerimaan</div>
      <div class="shipping-form-info__sub"><span style="color:var(--accent);">*</span> Field terisi otomatis dari PO</div>
    </div>
    <div class="order-form-grid-3">
      <x-misc.field label="Vendor" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
          <x-misc.avatar :name="$po['vendor']" />
          <span style="flex:1; font-weight:500;">{{ $po['vendor'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-misc.field>
      <x-misc.field label="No. Pemesanan" :required="true">
        <div class="input mono input--readonly" style="display:flex; align-items:center;">
          <span style="flex:1; font-weight:600;">{{ $po['id'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Gudang Tujuan" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $po['gudang'] }}</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-misc.field>
      <x-misc.field label="No. Penerimaan">
        <div class="input mono input--readonly" style="display:flex; align-items:center;">
          <span style="flex:1; font-weight:600;">GRN-2026-0073</span>
          <span class="auto-tag">Auto</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Tanggal Penerimaan" :required="true">
        <input type="date" class="input" value="{{ date('Y-m-d') }}" />
      </x-misc.field>
      <x-misc.field label="No. Surat Jalan Vendor"><input class="input mono" placeholder="cth. SJ-BFM-9842" /></x-misc.field>
    </div>
    <x-misc.field label="Catatan Penerimaan">
      <textarea class="input" rows="2" placeholder="Catat kondisi barang, kekurangan, atau informasi penting lainnya..."></textarea>
    </x-misc.field>
  </div>

  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Produk dari PO</div>
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
            <td><input class="input num" style="height:32px; text-align:right;" value="{{ number_format($it['qtyDiterima'], 0, ',', '.') }}" @input="fmtInput($event)" /></td>
            <td><input class="input num" style="height:32px; text-align:right;" value="{{ number_format($it['susut'], 0, ',', '.') }}" @input="fmtInput($event)" /></td>
            <td style="color:var(--ink-3);">{{ $it['satuan'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="order-items-split">
      <div style="padding:18px 22px;">
        <x-misc.field label="Estimasi Biaya Pengiriman">
          <input class="input num" style="text-align:right;" value="2.400.000" @input="fmtInput($event)" />
        </x-misc.field>
        <div style="font-size:11.5px; color:var(--ink-4); margin-top:8px; line-height:1.5; max-width:380px;">
          Estimasi ini akan dipakai untuk menghitung HPP awal. Nilai final akan diperbarui saat tagihan dari vendor diterbitkan.
        </div>
      </div>
      <div class="hpp-summary">
        <div class="label" style="margin-bottom:6px;">Estimasi HPP Total</div>
        <div class="hpp-summary__value display num">{{ fmt_rp($estHpp) }}</div>
        <div class="hpp-summary__sub">
          {{ $totalDiterima }} unit diterima · HPP/unit ≈ <span class="mono">{{ fmt_rp(round($estHpp / max($totalDiterima,1))) }}</span>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('pembelian.show', $po['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Penerimaan</button>
  </div>
</div>
@endsection
