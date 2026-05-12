@extends('layouts.erp')
@section('content')
@php
  $subtotal = array_sum(array_map(fn($i) => $i['qty'] * $i['harga'], $soDetailItems));
  $diskon   = 2_500_000;
  $ongkir   = 1_800_000;
  $total    = $subtotal - $diskon + $ongkir;
@endphp
<div style="padding:24px 28px 60px; display:grid; gap:16px;">

  <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
    <div>
      <a href="{{ route('erp.penjualan.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-erp.icon name="chev-left" :size="13" />Kembali ke Daftar SO
      </a>
      <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">{{ $so['id'] }}</h1>
        <x-erp.status-badge :status="$so['status']" />
      </div>
      <div style="font-size:13px; color:var(--ink-4); margin-top:6px;">
        Dibuat {{ $so['tanggal'] }} oleh Albert Irgi · Terakhir diperbarui 8 jam lalu
      </div>
    </div>
    <div style="display:flex; gap:8px;">
      <button class="btn btn-ghost"><x-erp.icon name="x" :size="14" />Batal Pemesanan</button>
      <a href="{{ route('erp.penjualan.pengiriman', $so['id']) }}" class="btn btn-dark">
        <x-erp.icon name="truck" :size="14" />Buat Pengiriman
      </a>
      <button class="btn btn-primary"><x-erp.icon name="receipt" :size="14" />Buat Tagihan</button>
    </div>
  </div>

  {{-- Meta --}}
  <div class="card" style="padding:18px 22px; display:grid; grid-template-columns:repeat(5,1fr); gap:24px;">
    @foreach([['Customer',$so['customer'],true],['Tanggal SO',$so['tanggal'],false],['Jatuh Tempo',$so['jatuhTempo'],false],['Gudang',$so['gudang'],false],['Sales Person','Reza Pratama',false]] as [$lbl,$val,$av])
      <div>
        <div class="label" style="margin-bottom:6px;">{{ $lbl }}</div>
        <div style="display:flex; align-items:center; gap:8px;">
          @if($av)<x-erp.avatar :name="$val" />@endif
          <span style="font-weight:600; font-size:14px;">{{ $val }}</span>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Products --}}
  <div class="card" style="overflow:hidden;">
    <div style="padding:14px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
      <div class="display" style="font-weight:700; font-size:15px;">Daftar Produk</div>
      <div style="font-size:12px; color:var(--ink-4);">{{ count($soDetailItems) }} item · {{ array_sum(array_column($soDetailItems,'qty')) }} unit</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th><th>Produk</th>
        <th style="text-align:right;">Qty</th><th>Satuan</th>
        <th style="text-align:right;">Harga Satuan</th><th style="text-align:right;">Subtotal</th>
      </tr></thead>
      <tbody>
        @foreach($soDetailItems as $i => $it)
          <tr>
            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</td>
            <td>
              <div style="font-weight:600;">{{ $it['nama'] }}</div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it['kode'] }}</div>
            </td>
            <td class="num" style="text-align:right;">{{ $it['qty'] }}</td>
            <td style="color:var(--ink-3);">{{ $it['satuan'] }}</td>
            <td class="num" style="text-align:right;">{{ fmt_rp($it['harga']) }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($it['qty'] * $it['harga']) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div style="display:grid; grid-template-columns:1fr 320px; border-top:1px solid var(--line);">
      <div style="padding:18px 22px;">
        <div class="label">Catatan Internal</div>
        <div style="font-size:13px; color:var(--ink-3); max-width:480px;">Pengiriman split 2 truk; truk pertama Rabu pagi, sisa Kamis. Konfirmasi muat dengan Pak Tarno (gudang).</div>
      </div>
      <div style="padding:18px 22px; background:var(--bg-2); border-left:1px solid var(--line);">
        @foreach([['Subtotal',$subtotal,false],['Diskon',-$diskon,false],['Ongkos Kirim',$ongkir,false],['Total',$total,true]] as [$lbl,$val,$bold])
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
