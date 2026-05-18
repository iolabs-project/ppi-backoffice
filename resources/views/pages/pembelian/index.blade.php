@extends('layouts.app')
@section('content')
<div x-data="{ filter: 'semua' }" class="order-page">
  <div class="order-hd">
    <div>
      <h1 class="order-title display">Purchase Order</h1>
      <div class="order-sub">{{ count($purchaseOrders) }} dokumen · Periode Mei 2026</div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
      <a href="{{ route('pembelian.create') }}" class="btn btn-primary"><x-misc.icon name="plus" :size="15" />Tambah PO</a>
    </div>
  </div>

  <div class="filter-pills">
    @php $statuses = [['semua','Semua'],['pending','Pending'],['dikirim','Dikirim'],['tagihan','Tagihan'],['lunas','Lunas']]; @endphp
    @foreach($statuses as [$id,$lbl])
      @php $cnt = $id === 'semua' ? count($purchaseOrders) : count(array_filter($purchaseOrders, fn($s) => $s['status'] === $id)); @endphp
      <button x-on:click="filter = '{{ $id }}'"
              :class="filter === '{{ $id }}' ? 'filter-pill filter-pill--active' : 'filter-pill'">
        {{ $lbl }}<span class="filter-pill__count mono">{{ $cnt }}</span>
      </button>
    @endforeach
    <div style="flex:1;"></div>
    <button class="btn btn-ghost btn-sm"><x-misc.icon name="sort" :size="13" />Tanggal</button>
    <button class="btn btn-ghost btn-sm"><x-misc.icon name="filter" :size="13" />Filter</button>
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
              x-on:click="window.location='{{ route('pembelian.show', $s['id']) }}'">
            <td x-on:click.stop><input type="checkbox" /></td>
            <td class="mono" style="font-weight:600;">{{ $s['id'] }}</td>
            <td style="color:var(--ink-3);">{{ $s['tanggal'] }}</td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <x-misc.avatar :name="$s['vendor']" />
                <span style="font-weight:500;">{{ $s['vendor'] }}</span>
              </div>
            </td>
            <td style="color:var(--ink-3);">{{ $s['gudang'] }}</td>
            <td style="color:var(--ink-3);">{{ $s['jatuhTempo'] }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($s['total']) }}</td>
            <td><x-misc.status-badge :status="$s['status']" /></td>
            <td x-on:click.stop>
              <div x-data="{ open: false }" class="action-menu">
                <button
                  class="btn btn-ghost btn-icon btn-sm btn--borderless"
                  x-on:click.stop="
                    let r = $el.getBoundingClientRect();
                    $refs.panel.style.top = (r.bottom + 6) + 'px';
                    $refs.panel.style.right = (window.innerWidth - r.right) + 'px';
                    open = !open;
                  ">
                  <x-misc.icon name="more" :size="15" />
                </button>
                <div x-ref="panel" x-show="open" x-cloak x-on:click.away="open = false" class="action-menu__panel">
                  <a href="{{ route('pembelian.show', $s['id']) }}" class="action-menu__item">
                    <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat Detail
                  </a>
                  <a href="{{ route('pembelian.pengiriman', $s['id']) }}" class="action-menu__item">
                    <x-misc.icon name="box" :size="14" stroke="var(--ink-3)" />Buat Penerimaan
                  </a>
                  <a href="{{ route('pembelian.tagihan', $s['id']) }}" class="action-menu__item">
                    <x-misc.icon name="receipt" :size="14" stroke="var(--ink-3)" />Buat Tagihan
                  </a>
                  <button class="action-menu__item">
                    <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak PO
                  </button>
                  <div class="action-menu__divider"></div>
                  <button class="action-menu__item action-menu__item--danger">
                    <x-misc.icon name="x" :size="14" stroke="currentColor" />Batalkan PO
                  </button>
                </div>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
