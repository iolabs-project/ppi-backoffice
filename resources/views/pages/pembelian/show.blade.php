@extends('layouts.app')
@section('content')
@php
  $subtotal = array_sum(array_map(fn($i) => $i['qty'] * $i['harga'], $poDetailItems));
  $diskon   = 4_800_000;
  $ongkir   = 2_400_000;
  $total    = $subtotal - $diskon + $ongkir;
@endphp
<div class="order-page">
  <div class="order-hd order-hd--start">
    <div>
      <a href="{{ route('pembelian.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-misc.icon name="chev-left" :size="13" />Kembali ke Daftar PO
      </a>
      <div class="order-title-row">
        <h1 class="order-title display">{{ $po['id'] }}</h1>
        <x-misc.status-badge :status="$po['status']" />
      </div>
      <div class="order-sub">
        Dibuat {{ $po['tanggal'] }} · Estimasi HPP akan dihitung dari kuantitas riil + biaya kirim aktual
      </div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost"><x-misc.icon name="x" :size="14" />Batal Pemesanan</button>
      <a href="{{ route('pembelian.penerimaan', $po['id']) }}" class="btn btn-dark">
        <x-misc.icon name="truck" :size="14" />Buat Penerimaan
      </a>
      <a href="{{ route('pembelian.tagihan', $po['id']) }}" class="btn btn-primary">
        <x-misc.icon name="receipt" :size="14" />Buat Tagihan
      </a>
    </div>
  </div>

  <div class="card order-meta">
    @foreach([['Vendor',$po['vendor'],true],['Tanggal PO',$po['tanggal'],false],['Jatuh Tempo',$po['jatuhTempo'],false],['Gudang Tujuan',$po['gudang'],false],['Pembeli','Nadia Rahmawati',false]] as [$lbl,$val,$av])
      <div>
        <div class="label order-meta__label">{{ $lbl }}</div>
        <div class="order-meta__value">
          @if($av)<x-misc.avatar :name="$val" />@endif
          <span>{{ $val }}</span>
        </div>
      </div>
    @endforeach
  </div>

  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Daftar Produk</div>
      <div style="font-size:12px; color:var(--ink-4);">{{ count($poDetailItems) }} item · {{ array_sum(array_column($poDetailItems,'qty')) }} unit dipesan</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th><th>Produk</th>
        <th style="text-align:right;">Qty Pesan</th>
        <th style="text-align:right;">Qty Diterima</th>
        <th style="text-align:right;">Susut</th>
        <th>Satuan</th>
        <th style="text-align:right;">Harga Beli</th>
        <th style="text-align:right;">Subtotal</th>
      </tr></thead>
      <tbody>
        @foreach($poDetailItems as $i => $it)
          <tr>
            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</td>
            <td>
              <div style="font-weight:600;">{{ $it['nama'] }}</div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it['kode'] }}</div>
            </td>
            <td class="num" style="text-align:right;">{{ $it['qty'] }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ $it['qtyDiterima'] }}</td>
            <td class="num" style="text-align:right; color:{{ $it['susut'] ? 'var(--bad)' : 'var(--ink-5)' }};">{{ $it['susut'] ?: '—' }}</td>
            <td style="color:var(--ink-3);">{{ $it['satuan'] }}</td>
            <td class="num" style="text-align:right;">{{ fmt_rp($it['harga']) }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($it['qty'] * $it['harga']) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="order-items-split" style="grid-template-columns:1fr 320px;">
      <div class="order-notes">
        <div class="label">Catatan Pembelian</div>
        <div class="order-notes__text">Kontrak harga tertahan 14 hari. Jika harga gandum naik di Sep, renegotiate sebelum lot berikutnya.</div>
      </div>
      <div class="order-detail-summary">
        @foreach([['Subtotal',$subtotal,false],['Diskon',-$diskon,false],['Est. Ongkos Kirim',$ongkir,false],['Total Pesanan',$total,true]] as [$lbl,$val,$bold])
          <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $bold ? 'border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;' : '' }}">
            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
            <span class="num" style="color:{{ $bold ? 'var(--accent)' : 'var(--ink)' }};">{{ $val < 0 ? '–' : '' }}{{ fmt_rp(abs($val)) }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
