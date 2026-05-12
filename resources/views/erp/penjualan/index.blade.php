@extends('layouts.erp')
@section('content')
<div x-data="{ filter: 'semua' }" class="order-page">

  <div class="order-hd">
    <div>
      <h1 class="order-title display">Sales Order</h1>
      <div class="order-sub">{{ count($salesOrders) }} dokumen · Periode Mei 2026</div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost"><x-erp.icon name="download" :size="14" />Ekspor</button>
      <a href="{{ route('erp.penjualan.create') }}" class="btn btn-primary"><x-erp.icon name="plus" :size="15" />Tambah SO</a>
    </div>
  </div>

  {{-- Filter pills --}}
  <div class="filter-pills">
    @php
      $statuses = [
        ['id'=>'semua',   'label'=>'Semua'],
        ['id'=>'pending', 'label'=>'Pending'],
        ['id'=>'dikirim', 'label'=>'Dikirim'],
        ['id'=>'tagihan', 'label'=>'Tagihan'],
        ['id'=>'lunas',   'label'=>'Lunas'],
      ];
    @endphp
    @foreach($statuses as $st)
      @php $cnt = $st['id'] === 'semua' ? count($salesOrders) : count(array_filter($salesOrders, fn($s) => $s['status'] === $st['id'])); @endphp
      <button x-on:click="filter = '{{ $st['id'] }}'"
              :class="filter === '{{ $st['id'] }}' ? 'filter-pill filter-pill--active' : 'filter-pill'">
        {{ $st['label'] }}<span class="filter-pill__count mono">{{ $cnt }}</span>
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
        <th>Nomor SO</th><th>Tanggal</th><th>Customer</th><th>Gudang</th><th>Jatuh Tempo</th>
        <th style="text-align:right;">Total</th><th>Status</th><th style="width:40px;"></th>
      </tr></thead>
      <tbody>
        @foreach($salesOrders as $s)
          <tr class="row-tap"
              x-show="filter === 'semua' || filter === '{{ $s['status'] }}'"
              x-on:click="window.location='{{ route('erp.penjualan.show', $s['id']) }}'">
            <td x-on:click.stop><input type="checkbox" /></td>
            <td class="mono" style="font-weight:600;">{{ $s['id'] }}</td>
            <td style="color:var(--ink-3);">{{ $s['tanggal'] }}</td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <x-erp.avatar :name="$s['customer']" />
                <span style="font-weight:500;">{{ $s['customer'] }}</span>
              </div>
            </td>
            <td style="color:var(--ink-3);">{{ $s['gudang'] }}</td>
            <td style="color:var(--ink-3);">{{ $s['jatuhTempo'] }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($s['total']) }}</td>
            <td><x-erp.status-badge :status="$s['status']" /></td>
            <td x-on:click.stop>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"><x-erp.icon name="more" :size="15" /></button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
