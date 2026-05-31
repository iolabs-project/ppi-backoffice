@extends('layouts.app')
@section('content')
<script>
function transferData() {
  return {
    tipe: 'keluar',

    gudangAsal: @json($gudang['nama']),
    gudangTujuan: '',
    tanggal: '{{ date('Y-m-d') }}',
    nomor: @json($nomorDefault),
    catatan: '',

    get dari() { return this.tipe === 'keluar' ? this.gudangAsal : this.gudangTujuan; },
    get ke()   { return this.tipe === 'keluar' ? this.gudangTujuan : this.gudangAsal; },

    produkOptions: @json($produkGudang),
    lines: [],
    scan: '',

    addLine(kode) {
      if (!kode) return;
      const p = this.produkOptions.find(x => x.kode === kode || x.nama.toLowerCase().includes(kode.toLowerCase()));
      if (!p) return;
      if (this.lines.find(l => l.kode === p.kode)) { this.scan = ''; return; }
      this.lines.push({ kode: p.kode, nama: p.nama, satuan: p.satuan, stokAwal: p.stok, qty: 0 });
      this.scan = '';
    },
    removeLine(kode) {
      this.lines = this.lines.filter(l => l.kode !== kode);
    },
    qtySetelahnya(line) {
      return this.tipe === 'keluar'
        ? line.stokAwal - Number(line.qty)
        : line.stokAwal + Number(line.qty);
    },
  };
}
</script>
<div class="order-page" x-data="transferData()">

  {{-- Header --}}
  <div class="order-hd order-hd--start">
    <div>
      <a href="{{ route('master.gudang.show', $gudang['kode']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-misc.icon name="chev-left" :size="13" /> Kembali ke {{ $gudang['nama'] }}
      </a>
      <div class="order-title-row">
        <h1 class="order-title display">Transfer Gudang</h1>
        <span class="chip mono" style="font-size:11px;" x-text="nomor"></span>
      </div>
      <div class="order-sub">{{ $gudang['nama'] }} · {{ date('d M Y') }}</div>
    </div>
  </div>

  {{-- Tipe toggle --}}
  <div class="card" style="padding:18px 20px;">
    <div class="label" style="margin-bottom:10px;">Tipe Transfer</div>
    <div class="ps-tipe-group">
      <label class="ps-tipe-btn" :class="tipe === 'keluar' ? 'ps-tipe-btn--active' : ''">
        <input type="radio" x-model="tipe" value="keluar" style="display:none;" />
        <x-misc.icon name="send" :size="14" />
        Keluar dari Gudang
      </label>
      <label class="ps-tipe-btn" :class="tipe === 'masuk' ? 'ps-tipe-btn--active' : ''">
        <input type="radio" x-model="tipe" value="masuk" style="display:none;" />
        <x-misc.icon name="inbox" :size="14" />
        Masuk ke Gudang
      </label>
    </div>
  </div>

  {{-- Form fields --}}
  <div class="card" style="padding:20px;">
    <div class="form-body" style="margin:0;">

      {{-- Dari & Ke --}}
      <div class="form-grid-2">
        <x-misc.field label="Dari" :required="true">
          <div x-show="tipe === 'keluar'">
            <input class="input" :value="gudangAsal" readonly style="background:var(--bg-2); color:var(--ink-3);" />
          </div>
          <div x-show="tipe === 'masuk'" x-cloak>
            <select class="input" x-model="gudangTujuan">
              <option value="" disabled>Pilih gudang asal</option>
              @foreach($otherGudang as $g)
                <option value="{{ $g['nama'] }}">{{ $g['nama'] }}</option>
              @endforeach
            </select>
          </div>
        </x-misc.field>

        <x-misc.field label="Ke" :required="true">
          <div x-show="tipe === 'masuk'">
            <input class="input" :value="gudangAsal" readonly style="background:var(--bg-2); color:var(--ink-3);" />
          </div>
          <div x-show="tipe === 'keluar'" x-cloak>
            <select class="input" x-model="gudangTujuan">
              <option value="" disabled>Pilih gudang tujuan</option>
              @foreach($otherGudang as $g)
                <option value="{{ $g['nama'] }}">{{ $g['nama'] }}</option>
              @endforeach
            </select>
          </div>
        </x-misc.field>
      </div>

      {{-- Tanggal & Nomor --}}
      <div class="form-grid-2">
        <x-misc.field label="Tanggal" :required="true">
          <input class="input" type="date" x-model="tanggal" />
        </x-misc.field>
        <x-misc.field label="Nomor">
          <input class="input mono" x-model="nomor" placeholder="WT/00001" />
        </x-misc.field>
      </div>

    </div>
  </div>

  {{-- Products --}}
  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Produk</div>
      <div style="display:flex; align-items:center; gap:8px;">
        <input class="input" style="width:260px;" placeholder="Scan Barcode / cari SKU..."
               x-model="scan"
               x-on:keydown.enter.prevent="addLine(scan)"
               x-on:keydown.tab.prevent="addLine(scan)" />
        <button class="btn btn-ghost btn-sm" x-on:click="addLine(scan)">
          <x-misc.icon name="plus" :size="13" /> Tambah
        </button>
      </div>
    </div>

    <table class="tbl">
      <thead><tr>
        <th>Produk</th>
        <th>Gudang</th>
        <th style="text-align:right;">Qty Sebelumnya</th>
        <th style="text-align:right;">Qty Setelahnya</th>
        <th style="text-align:right; width:160px;">Transfer Qty</th>
        <th style="width:40px;"></th>
      </tr></thead>
      <tbody>
        <template x-for="line in lines" :key="line.kode">
          <tr>
            <td>
              <div style="font-weight:600; font-size:13px;" x-text="line.nama"></div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="line.kode"></div>
            </td>
            <td style="font-size:13px; color:var(--ink-3);" x-text="dari || '—'"></td>
            <td class="num" style="text-align:right; font-size:13px;" x-text="line.stokAwal + ' ' + line.satuan"></td>
            <td class="num" style="text-align:right; font-size:13px; font-weight:600;"
                :style="qtySetelahnya(line) < 0 ? 'color:var(--bad)' : ''"
                x-text="qtySetelahnya(line) + ' ' + line.satuan"></td>
            <td style="text-align:right;">
              <input class="input num" type="number" min="0" style="text-align:right; width:120px;"
                     x-model="line.qty" placeholder="0" />
            </td>
            <td>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none; color:var(--bad);"
                      x-on:click="removeLine(line.kode)">
                <x-misc.icon name="x" :size="14" />
              </button>
            </td>
          </tr>
        </template>

        {{-- Empty state --}}
        <template x-if="lines.length === 0">
          <tr>
            <td colspan="6">
              <div style="display:flex; flex-direction:column; align-items:center; padding:40px; gap:8px;">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="8" y="16" width="32" height="24" rx="3" fill="var(--line)" />
                  <path d="M16 16V12a8 8 0 0 1 16 0v4" stroke="var(--ink-4)" stroke-width="2" stroke-linecap="round"/>
                  <circle cx="24" cy="28" r="3" fill="var(--ink-4)" opacity=".5"/>
                </svg>
                <div style="font-size:13px; color:var(--ink-4);">Belum ada produk ditambahkan</div>
                <div style="font-size:12px; color:var(--ink-4); opacity:.7;">Scan barcode atau ketik SKU di atas untuk menambah produk</div>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

  {{-- Footer: catatan + simpan --}}
  <div style="display:grid; grid-template-columns:1fr 320px; gap:16px; align-items:start;">

    <div class="card" style="padding:0; overflow:hidden;">
      <div class="card-hd" style="padding:14px 18px; cursor:pointer; user-select:none;">
        <div style="font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="doc" :size="14" stroke="var(--ink-3)" /> Catatan
        </div>
      </div>
      <div style="padding:18px 16px;">
        <textarea class="input" rows="4" x-model="catatan"
                  placeholder="Tambahkan catatan untuk transfer ini..."></textarea>
      </div>
    </div>

    <div class="card" style="padding:20px; display:grid; gap:12px;">
      <div style="font-size:13px; color:var(--ink-3);">
        <div style="display:flex; justify-content:space-between; padding:4px 0;">
          <span>Tipe</span>
          <span style="font-weight:600; color:var(--ink);" x-text="tipe === 'keluar' ? 'Keluar dari Gudang' : 'Masuk ke Gudang'"></span>
        </div>
        <div style="display:flex; justify-content:space-between; padding:4px 0;">
          <span>Dari</span>
          <span style="font-weight:600; color:var(--ink);" x-text="dari || '—'"></span>
        </div>
        <div style="display:flex; justify-content:space-between; padding:4px 0;">
          <span>Ke</span>
          <span style="font-weight:600; color:var(--ink);" x-text="ke || '—'"></span>
        </div>
        <div style="display:flex; justify-content:space-between; padding:4px 0; border-top:1px solid var(--line); margin-top:4px; padding-top:10px;">
          <span>Total Item</span>
          <span style="font-weight:700; color:var(--ink);" x-text="lines.length + ' produk'"></span>
        </div>
      </div>
      <button class="btn btn-primary" style="width:100%; justify-content:center;">
        <x-misc.icon name="check" :size="14" /> Simpan Transfer
      </button>
    </div>

  </div>

</div>
@endsection
