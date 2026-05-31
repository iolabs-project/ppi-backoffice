@extends('layouts.app')
@section('content')
<script>
function gudangShowData() {
  return {
    search: '',
    produkAll: @json($produkForJs),
    page: 1,
    perPage: 10,
    get filtered() {
      if (!this.search) return this.produkAll;
      const q = this.search.toLowerCase();
      return this.produkAll.filter(p => p.nama.toLowerCase().includes(q) || p.kode.toLowerCase().includes(q));
    },
    get paged() {
      return this.filtered.slice((this.page - 1) * this.perPage, this.page * this.perPage);
    },
    get totalPages() {
      return Math.ceil(this.filtered.length / this.perPage);
    },
    resetPage() { this.page = 1; },
  };
}
</script>
<div class="order-page" x-data="gudangShowData()">

  {{-- Header --}}
  <div class="order-hd order-hd--start">
    <div>
      <a href="{{ route('master.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-misc.icon name="chev-left" :size="13" /> Kembali ke Master Data
      </a>
      <div class="order-title-row">
        <h1 class="order-title display">{{ $gudang['nama'] }}</h1>
        <span class="chip mono" style="font-size:11px;">{{ $gudang['kode'] }}</span>
      </div>
      <div class="order-sub">
        {{ $gudang['kota'] }}
        @if(!empty($gudang['PIC'])) · PIC: {{ $gudang['PIC'] }} @endif
        @if(!empty($gudang['kapasitas'])) · Kapasitas {{ fmt_num($gudang['kapasitas']) }} unit @endif
      </div>
    </div>
    <div class="order-actions">
      <a href="{{ route('master.gudang.transfer', $gudang['kode']) }}" class="btn btn-primary">
        <x-misc.icon name="swap" :size="14" /> Transfer Gudang
      </a>
    </div>
  </div>

  {{-- Stat cards --}}
  <div class="produk-stat-grid" style="grid-template-columns: repeat(3, 1fr);">

    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">
        {{ fmt_num($totalStok) }}
      </div>
      <div class="produk-stat__label">Total Stok</div>
      <div class="produk-stat__unit">{{ count($produkForJs) }} SKU</div>
    </div>

    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 45); color:oklch(0.45 0.14 45);">
        {{ fmt_rp_short($totalNilai) }}
      </div>
      <div class="produk-stat__label">Total Nilai Produk</div>
      <div class="produk-stat__unit">berdasarkan harga pokok</div>
    </div>

    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 0); color:oklch(0.45 0.14 0);">
        {{ fmt_rp_short($rataHpp) }}
      </div>
      <div class="produk-stat__label">Rata-Rata HPP</div>
      <div class="produk-stat__unit">per produk</div>
    </div>

  </div>

  {{-- Products table --}}
  <div class="card" style="overflow:hidden;">
    <div class="master-toolbar">
      <div class="master-search">
        <span class="master-search__icon"><x-misc.icon name="search" :size="14" stroke="var(--ink-4)" /></span>
        <input class="input master-search__input" placeholder="Cari produk di gudang ini..."
               x-model="search" x-on:input="resetPage()" />
      </div>
      <div style="font-size:12.5px; color:var(--ink-4);" x-text="filtered.length + ' produk ditemukan'"></div>
    </div>

    <table class="tbl">
      <thead><tr>
        <th>Nama Produk</th>
        <th>Kode</th>
        <th style="text-align:right;">Qty</th>
        <th>Satuan</th>
        <th style="text-align:right;">Nilai</th>
      </tr></thead>
      <tbody>
        <template x-for="p in paged" :key="p.kode">
          <tr class="row-tap" style="cursor:pointer;"
              x-on:click="window.location.href='/master/produk/'+p.kode">
            <td>
              <div style="font-weight:600; font-size:13px;" x-text="p.nama"></div>
            </td>
            <td class="mono" style="font-size:12px; font-weight:600; color:var(--ink-4);" x-text="p.kode"></td>
            <td class="num" style="text-align:right; font-size:13px; font-weight:600;" x-text="p.qtyFmt"></td>
            <td style="font-size:13px; color:var(--ink-3);" x-text="p.satuan"></td>
            <td class="num" style="text-align:right; font-size:13px;" x-text="p.nilaiFmt"></td>
          </tr>
        </template>
        <template x-if="filtered.length === 0">
          <tr>
            <td colspan="5" style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
              Tidak ada produk ditemukan
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div class="table-pagination" x-show="totalPages > 1">
      <span class="pagination-info"
            x-text="'Menampilkan ' + ((page-1)*perPage+1) + '–' + Math.min(page*perPage, filtered.length) + ' dari ' + filtered.length + ' produk'"></span>
      <div class="pagination-controls">
        <span class="pagination-page-info">Hal. <strong x-text="page"></strong> / <strong x-text="totalPages"></strong></span>
        <button class="btn btn-ghost btn-sm" :disabled="page <= 1" x-on:click="page--">
          <x-misc.icon name="chev-left" :size="14" /> Prev
        </button>
        <button class="btn btn-ghost btn-sm" :disabled="page >= totalPages" x-on:click="page++">
          Next <x-misc.icon name="chev-right" :size="14" />
        </button>
      </div>
    </div>
  </div>

</div>
@endsection
