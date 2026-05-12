@extends('layouts.erp')
@section('content')
<div x-data="{ filter: 'semua' }" style="padding:24px 28px 60px; display:grid; gap:16px;">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
    <div>
      <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Purchase Order</h1>
      <div style="font-size:13px; color:var(--ink-4); margin-top:4px;">{{ count($purchaseOrders) }} dokumen · Periode Mei 2026</div>
    </div>
    <div style="display:flex; gap:8px;">
      <button class="btn btn-ghost"><x-erp.icon name="download" :size="14" />Ekspor</button>
      <a href="{{ route('erp.pembelian.create') }}" class="btn btn-primary"><x-erp.icon name="plus" :size="15" />Tambah PO</a>
    </div>
  </div>

  <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
    @php $statuses = [['semua','Semua'],['pending','Pending'],['dikirim','Dikirim'],['tagihan','Tagihan'],['lunas','Lunas']]; @endphp
    @foreach($statuses as [$id,$lbl])
      @php $cnt = $id === 'semua' ? count($purchaseOrders) : count(array_filter($purchaseOrders, fn($s) => $s['status'] === $id)); @endphp
      <button x-on:click="filter = '{{ $id }}'"
              :style="filter === '{{ $id }}' ? 'background:var(--ink); color:var(--paper); border-color:var(--ink);' : 'background:var(--paper); color:var(--ink-3); border-color:var(--line);'"
              style="height:32px; padding:0 14px; border-radius:999px; font-size:12.5px; font-weight:600; border:1px solid; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
        {{ $lbl }}<span class="mono" style="font-size:11px; opacity:.7;">{{ $cnt }}</span>
      </button>
    @endforeach
    <div style="flex:1;"></div>
    <button class="btn btn-ghost btn-sm"><x-erp.icon name="sort" :size="13" />Tanggal</button>
    <button class="btn btn-ghost btn-sm"><x-erp.icon name="filter" :size="13" />Filter</button>
  </div>

  <div class="card" style="overflow:hidden;">
    <table class="tbl">
      <thead><tr>
        <th style="width:36px;"><input type="checkbox" /></th>
        <th>Nomor PO</th><th>Tanggal</th><th>Vendor</th><th>Gudang</th><th>Jatuh Tempo</th>
        <th style="text-align:right;">Total</th><th>Status</th><th style="width:40px;"></th>
      </tr></thead>
      <tbody>
        @foreach($purchaseOrders as $s)
          <tr class="row-tap"
              x-show="filter === 'semua' || filter === '{{ $s['status'] }}'"
              x-on:click="window.location='{{ route('erp.pembelian.show', $s['id']) }}'">
            <td x-on:click.stop><input type="checkbox" /></td>
            <td class="mono" style="font-weight:600;">{{ $s['id'] }}</td>
            <td style="color:var(--ink-3);">{{ $s['tanggal'] }}</td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <x-erp.avatar :name="$s['vendor']" />
                <span style="font-weight:500;">{{ $s['vendor'] }}</span>
              </div>
            </td>
            <td style="color:var(--ink-3);">{{ $s['gudang'] }}</td>
            <td style="color:var(--ink-3);">{{ $s['jatuhTempo'] }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($s['total']) }}</td>
            <td><x-erp.status-badge :status="$s['status']" /></td>
            <td x-on:click.stop><button class="btn btn-ghost btn-icon btn-sm" style="border:none;"><x-erp.icon name="more" :size="15" /></button></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
