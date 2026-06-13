@extends('layouts.app')
@section('content')
@php
  $stok      = $produk['stok'] ?? 0;
  $totalJual = array_sum(array_map(fn($t) => abs($t['qty']), array_filter($transaksiTerkini, fn($t) => $t['qty'] < 0)));
  $totalBeli = array_sum(array_map(fn($t) => $t['qty'], array_filter($transaksiTerkini, fn($t) => $t['qty'] > 0)));
  $stokTotal = array_sum(array_column($stokPerGudang, 'stok'));
  $gudangColors = ['oklch(0.72 0.14 155)', 'oklch(0.72 0.14 220)', 'oklch(0.72 0.14 45)'];

  $runningStok = $stok;
  $pergerakanStokData = [];
  foreach (array_reverse($transaksiTerkini) as $t) {
    $pergerakanStokData[] = [
      'tanggal'  => $t['tanggal'],
      'deskripsi' => $t['deskripsi'],
      'harga'    => $t['harga'],
      'hargaFmt' => fmt_rp($t['harga']),
      'hppFmt'   => fmt_rp($produk['hargaBeli']),
      'qty'      => $t['qty'],
      'qtyFmt'   => ($t['qty'] > 0 ? '+' : '') . fmt_num($t['qty']),
      'stokFmt'  => fmt_num($runningStok) . ' ' . $produk['satuan'],
    ];
    $runningStok -= $t['qty'];
  }

  $terkiniForJs = array_map(fn($t) => [
    'tanggal'  => $t['tanggal'],
    'deskripsi' => $t['deskripsi'],
    'ref'      => $t['ref'],
    'qty'      => $t['qty'],
    'qtyFmt'   => ($t['qty'] > 0 ? '+' : '') . fmt_num($t['qty']),
    'hargaFmt' => fmt_rp($t['harga']),
    'totalFmt' => fmt_rp($t['total']),
  ], $transaksiTerkini);

  $transferForJs = array_map(fn($t) => [
    'tanggal' => $t['tanggal'],
    'nomor'   => $t['nomor'],
    'dari'    => $t['dari'],
    'ke'      => $t['ke'],
    'qtyFmt'  => fmt_num($t['qty']),
  ], $transferGudang);
@endphp
<script>
function produkShowData() {
  return {
    modal: null,
    editProduk: {
      kode:      '{{ $produk['kode'] }}',
      nama:      '{{ addslashes($produk['nama']) }}',
      kategori:  '{{ addslashes($produk['kategori'] ?? '') }}',
      satuan:    '{{ addslashes($produk['satuan']) }}',
      hargaBeli: {{ $produk['hargaBeli'] }},
      hargaJual: {{ $produk['hargaJual'] }},
    },
    transaksiTab: 'terkini',

    penyesuaian: {
      tipe: 'perhitungan',
      gudang: '', tanggal: '', akun: '8-80100 Penyesuaian Persediaan', nomor: 'SA/00007',
      qtyAktual: 0, selisih: 0, hargaRataRata: 0,
    },

    terkiniAll: @json($terkiniForJs),
    terkiniPage: 1, terkiniPerPage: 10,
    get terkiniPaged() { return this.terkiniAll.slice((this.terkiniPage-1)*this.terkiniPerPage, this.terkiniPage*this.terkiniPerPage); },
    get terkiniTotal() { return Math.ceil(this.terkiniAll.length / this.terkiniPerPage); },

    stokAll: @json($pergerakanStokData),
    stokPage: 1, stokPerPage: 10,
    get stokPaged() { return this.stokAll.slice((this.stokPage-1)*this.stokPerPage, this.stokPage*this.stokPerPage); },
    get stokTotal() { return Math.ceil(this.stokAll.length / this.stokPerPage); },

    transferAll: @json($transferForJs),
    transferPage: 1, transferPerPage: 10,
    get transferPaged() { return this.transferAll.slice((this.transferPage-1)*this.transferPerPage, this.transferPage*this.transferPerPage); },
    get transferTotal() { return Math.ceil(this.transferAll.length / this.transferPerPage); },
  };
}
</script>
<div class="order-page" x-data="produkShowData()">

  {{-- Header --}}
  <div class="order-hd order-hd--start">
    <div>
      <a href="{{ route('master.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-misc.icon name="chev-left" :size="13" /> Kembali ke Master Data
      </a>
      <div class="order-title-row">
        <h1 class="order-title display">{{ $produk['nama'] }}</h1>
        <span class="chip">{{ $produk['kategori'] }}</span>
        <span class="chip mono" style="font-size:11px;">{{ $produk['kode'] }}</span>
      </div>
      <div class="order-sub">{{ $produk['satuan'] }} · Diperbarui hari ini</div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost" x-on:click="modal = 'edit_produk'"><x-misc.icon name="edit" :size="14" /> Edit Produk</button>
      <button class="btn btn-primary" x-on:click="modal = 'penyesuaian'"><x-misc.icon name="plus" :size="14" /> Penyesuaian Stok</button>
    </div>
  </div>

  {{-- Stat cards --}}
  <div class="produk-stat-grid">
    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">{{ fmt_num($stok) }}</div>
      <div class="produk-stat__label">Stok di tangan</div>
      <div class="produk-stat__unit">{{ $produk['satuan'] }}</div>
    </div>
    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 220); color:oklch(0.45 0.14 220);">{{ fmt_num($totalJual) }}</div>
      <div class="produk-stat__label">Terjual</div>
      <div class="produk-stat__unit">unit · bulan ini</div>
    </div>
    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 45); color:oklch(0.45 0.14 45);">{{ fmt_num($totalBeli) }}</div>
      <div class="produk-stat__label">Diterima</div>
      <div class="produk-stat__unit">unit · bulan ini</div>
    </div>
    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:var(--bg-2); color:var(--ink-2);">{{ fmt_rp($produk['hargaBeli']) }}</div>
      <div class="produk-stat__label">Harga Pokok</div>
      <div class="produk-stat__unit">per {{ $produk['satuan'] }}</div>
    </div>
  </div>

  {{-- Body: transaksi + sidebar --}}
  <div class="produk-body">

    {{-- Left: transaksi --}}
    <div class="card" style="overflow:hidden;">
      {{-- <div class="card-hd" style="padding-bottom:0;">
        <div class="display card-hd-title">Transaksi</div>
      </div> --}}
      <div class="utab" style="padding:0 16px; border-bottom:1px solid var(--line);">
        <button class="utab-item" x-on:click="transaksiTab='terkini'; terkiniPage=1" :class="transaksiTab==='terkini' ? 'utab-active' : ''">Transaksi Terkini</button>
        <button class="utab-item" x-on:click="transaksiTab='stok'; stokPage=1"       :class="transaksiTab==='stok'    ? 'utab-active' : ''">Pergerakan Stok</button>
        <button class="utab-item" x-on:click="transaksiTab='transfer'; transferPage=1" :class="transaksiTab==='transfer' ? 'utab-active' : ''">Transfer Gudang</button>
      </div>

      {{-- Transaksi Terkini --}}
      <div x-show="transaksiTab === 'terkini'" x-cloak>
        <table class="tbl">
          <thead><tr>
            <th>Tanggal</th><th>Deskripsi</th><th>Referensi</th>
            <th style="text-align:right;">Kuantitas</th>
            <th style="text-align:right;">Harga</th>
            <th style="text-align:right;">Total</th>
          </tr></thead>
          <tbody>
            <template x-for="t in terkiniPaged" :key="t.tanggal + t.deskripsi">
              <tr class="row-tap">
                <td style="font-size:13px; color:var(--ink-3);" x-text="t.tanggal"></td>
                <td style="font-size:13px;" x-text="t.deskripsi"></td>
                <td>
                  <span x-show="t.ref" class="chip mono" style="font-size:11px;" x-text="t.ref"></span>
                  <span x-show="!t.ref" style="color:var(--ink-4);">—</span>
                </td>
                <td class="num" style="text-align:right; font-size:13px; font-weight:600;"
                    :style="t.qty < 0 ? 'color:var(--bad)' : 'color:var(--ok)'"
                    x-text="t.qtyFmt"></td>
                <td class="num" style="text-align:right; font-size:13px;" x-text="t.hargaFmt"></td>
                <td class="num" style="text-align:right; font-size:13px; font-weight:600;" x-text="t.totalFmt"></td>
              </tr>
            </template>
          </tbody>
        </table>
        <div class="table-pagination">
          <span class="pagination-info" x-text="'Menampilkan ' + ((terkiniPage-1)*terkiniPerPage+1) + '–' + Math.min(terkiniPage*terkiniPerPage, terkiniAll.length) + ' dari ' + terkiniAll.length + ' transaksi'"></span>
          <div class="pagination-controls">
            <span class="pagination-page-info">Hal. <strong x-text="terkiniPage"></strong> / <strong x-text="terkiniTotal"></strong></span>
            <button class="btn btn-ghost btn-sm" :disabled="terkiniPage <= 1" x-on:click="terkiniPage--">
              <x-misc.icon name="chev-left" :size="14" /> Prev
            </button>
            <button class="btn btn-ghost btn-sm" :disabled="terkiniPage >= terkiniTotal" x-on:click="terkiniPage++">
              Next <x-misc.icon name="chev-right" :size="14" />
            </button>
          </div>
        </div>
      </div>

      {{-- Pergerakan Stok --}}
      <div x-show="transaksiTab === 'stok'" x-cloak>
        <table class="tbl">
          <thead><tr>
            <th>Tanggal</th><th>Deskripsi</th>
            <th style="text-align:right;">Harga</th>
            <th style="text-align:right;">HPP</th>
            <th style="text-align:right;">Pergerakan</th>
            <th style="text-align:right;">Stok</th>
          </tr></thead>
          <tbody>
            <template x-for="t in stokPaged" :key="t.tanggal + t.deskripsi">
              <tr>
                <td style="font-size:13px; color:var(--ink-3);" x-text="t.tanggal"></td>
                <td style="font-size:13px;" x-text="t.deskripsi"></td>
                <td class="num" style="text-align:right; font-size:13px;" x-text="t.hargaFmt"></td>
                <td class="num" style="text-align:right; font-size:13px;" x-text="t.hppFmt"></td>
                <td class="num" style="text-align:right; font-size:13px; font-weight:600;"
                    :style="t.qty < 0 ? 'color:var(--bad)' : 'color:var(--ok)'"
                    x-text="t.qtyFmt"></td>
                <td class="num" style="text-align:right; font-size:13px;" x-text="t.stokFmt"></td>
              </tr>
            </template>
          </tbody>
        </table>
        <div class="table-pagination">
          <span class="pagination-info" x-text="'Menampilkan ' + ((stokPage-1)*stokPerPage+1) + '–' + Math.min(stokPage*stokPerPage, stokAll.length) + ' dari ' + stokAll.length + ' baris'"></span>
          <div class="pagination-controls">
            <span class="pagination-page-info">Hal. <strong x-text="stokPage"></strong> / <strong x-text="stokTotal"></strong></span>
            <button class="btn btn-ghost btn-sm" :disabled="stokPage <= 1" x-on:click="stokPage--">
              <x-misc.icon name="chev-left" :size="14" /> Prev
            </button>
            <button class="btn btn-ghost btn-sm" :disabled="stokPage >= stokTotal" x-on:click="stokPage++">
              Next <x-misc.icon name="chev-right" :size="14" />
            </button>
          </div>
        </div>
      </div>

      {{-- Transfer Gudang --}}
      <div x-show="transaksiTab === 'transfer'" x-cloak>
        <table class="tbl">
          <thead><tr>
            <th>Tanggal</th><th>Nomor</th><th>Dari</th><th>Ke</th><th>Referensi</th>
            <th style="text-align:right;">Kuantitas</th>
          </tr></thead>
          <tbody>
            <template x-for="t in transferPaged" :key="t.nomor">
              <tr class="row-tap">
                <td style="font-size:13px; color:var(--ink-3);" x-text="t.tanggal"></td>
                <td><span class="chip mono" style="font-size:11px; color:var(--accent);" x-text="t.nomor"></span></td>
                <td style="font-size:13px;" x-text="t.dari"></td>
                <td style="font-size:13px;" x-text="t.ke"></td>
                <td style="color:var(--ink-4);">—</td>
                <td class="num" style="text-align:right; font-size:13px; font-weight:600;" x-text="t.qtyFmt"></td>
              </tr>
            </template>
          </tbody>
        </table>
        <div class="table-pagination">
          <span class="pagination-info" x-text="'Menampilkan ' + ((transferPage-1)*transferPerPage+1) + '–' + Math.min(transferPage*transferPerPage, transferAll.length) + ' dari ' + transferAll.length + ' transfer'"></span>
          <div class="pagination-controls">
            <span class="pagination-page-info">Hal. <strong x-text="transferPage"></strong> / <strong x-text="transferTotal"></strong></span>
            <button class="btn btn-ghost btn-sm" :disabled="transferPage <= 1" x-on:click="transferPage--">
              <x-misc.icon name="chev-left" :size="14" /> Prev
            </button>
            <button class="btn btn-ghost btn-sm" :disabled="transferPage >= transferTotal" x-on:click="transferPage++">
              Next <x-misc.icon name="chev-right" :size="14" />
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Right: info sidebar --}}
    <div class="card produk-sidebar">

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Informasi</div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Kategori</span>
          <span class="chip">{{ $produk['kategori'] }}</span>
        </div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Satuan</span>
          <span class="produk-sidebar__val">{{ $produk['satuan'] }}</span>
        </div>
      </div>

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Lokasi Gudang</div>
        @foreach($stokPerGudang as $i => $g)
        <div style="margin-bottom:10px;">
          <div style="display:flex; justify-content:space-between; font-size:12.5px; margin-bottom:4px;">
            <span style="color:{{ $gudangColors[$i] ?? 'var(--ink-3)' }}; font-weight:600;">{{ $g['nama'] }}</span>
            <span class="num" style="font-size:12px; color:var(--ink-3);">{{ fmt_num($g['stok']) }} {{ $produk['satuan'] }}</span>
          </div>
          <div style="height:4px; border-radius:999px; background:var(--line);">
            <div style="height:4px; border-radius:999px; background:{{ $gudangColors[$i] ?? 'var(--accent)' }}; width:{{ $stokTotal > 0 ? round($g['stok']/$stokTotal*100) : 0 }}%;"></div>
          </div>
        </div>
        @endforeach
      </div>

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Pembelian</div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Harga</span>
          <span class="num produk-sidebar__val">{{ fmt_rp($produk['hargaBeli']) }}</span>
        </div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Akun</span>
          <span class="produk-sidebar__val" style="color:var(--accent); font-size:12px;">{{ $akunPembelian }}</span>
        </div>
      </div>

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Penjualan</div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Harga</span>
          <span class="num produk-sidebar__val">{{ fmt_rp($produk['hargaJual']) }}</span>
        </div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Akun</span>
          <span class="produk-sidebar__val" style="color:var(--accent); font-size:12px;">{{ $akunPenjualan }}</span>
        </div>
      </div>

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Inventori</div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Akun Aset</span>
          <span class="produk-sidebar__val" style="color:var(--accent); font-size:12px;">{{ $akunInventori }}</span>
        </div>
      </div>

      <div class="produk-sidebar__section" style="border-bottom:none;">
        <div class="produk-sidebar__heading">Satuan</div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Satuan Dasar</span>
          <span class="produk-sidebar__val">{{ $produk['satuan'] }}</span>
        </div>
      </div>

    </div>
  </div>

  {{-- Modal: Edit Produk --}}
  <x-misc.modal title="Edit Produk" show="modal === 'edit_produk'" close-handler="modal = null">
    <div class="form-body">
      <div class="form-grid-2">
        <x-misc.field label="Kode Produk" :required="true">
          <input class="input mono" x-model="editProduk.kode" />
        </x-misc.field>
        <x-misc.field label="Kategori">
          <input class="input" x-model="editProduk.kategori" placeholder="Tepung, Gula, Minyak..." />
        </x-misc.field>
      </div>
      <x-misc.field label="Nama Produk" :required="true">
        <input class="input" x-model="editProduk.nama" />
      </x-misc.field>
      <div class="form-grid-3">
        <x-misc.field label="Satuan">
          <input class="input" x-model="editProduk.satuan" placeholder="Sak, Kg, Liter..." />
        </x-misc.field>
        <x-misc.field label="Harga Beli">
          <input class="input num" type="number" style="text-align:right;" x-model="editProduk.hargaBeli" />
        </x-misc.field>
        <x-misc.field label="Harga Jual">
          <input class="input num" type="number" style="text-align:right;" x-model="editProduk.hargaJual" />
        </x-misc.field>
      </div>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">
        <x-misc.icon name="x" :size="14" /> Batal
      </button>
      <button class="btn btn-primary">
        <x-misc.icon name="check" :size="14" /> Simpan Perubahan
      </button>
    </x-slot:footer>
  </x-misc.modal>

  {{-- Modal: Penyesuaian Stok --}}
  <x-misc.modal title="Penyesuaian Stok" show="modal === 'penyesuaian'" close-handler="modal = null" :width="620">
    <div class="form-body">

      {{-- Tipe --}}
      <x-misc.field label="Tipe penyesuaian stok" :required="true">
        <div class="ps-tipe-group">
          <label class="ps-tipe-btn" :class="penyesuaian.tipe === 'perhitungan' ? 'ps-tipe-btn--active' : ''">
            <input type="radio" x-model="penyesuaian.tipe" value="perhitungan" style="display:none;" />
            <x-misc.icon name="box" :size="14" />
            Perhitungan Stok
          </label>
          <label class="ps-tipe-btn" :class="penyesuaian.tipe === 'masuk_keluar' ? 'ps-tipe-btn--active' : ''">
            <input type="radio" x-model="penyesuaian.tipe" value="masuk_keluar" style="display:none;" />
            <x-misc.icon name="swap" :size="14" />
            Stok Masuk / Keluar
          </label>
        </div>
      </x-misc.field>

      {{-- Gudang & Tanggal --}}
      <div class="form-grid-2">
        <x-misc.field label="Gudang" :required="true">
          <select class="input" x-model="penyesuaian.gudang">
            <option value="" disabled>Pilih gudang</option>
            @foreach($stokPerGudang as $g)
              <option value="{{ $g['nama'] }}">{{ $g['nama'] }}</option>
            @endforeach
          </select>
        </x-misc.field>
        <x-misc.field label="Tanggal" :required="true">
          <input class="input" type="date" x-model="penyesuaian.tanggal" value="{{ date('Y-m-d') }}" />
        </x-misc.field>
      </div>

      {{-- Akun & Nomor --}}
      <div class="form-grid-2">
        <x-misc.field label="Akun" :required="true">
          <select class="input" x-model="penyesuaian.akun">
            <option value="8-80100 Penyesuaian Persediaan">8-80100 Penyesuaian Persediaan</option>
            <option value="1-1300 Persediaan Barang">1-1300 Persediaan Barang</option>
          </select>
        </x-misc.field>
        <x-misc.field label="Nomor">
          <input class="input mono" x-model="penyesuaian.nomor" placeholder="SA/00001" />
        </x-misc.field>
      </div>

      {{-- Qty adjustment --}}
      <div class="ps-qty-section">
        <div class="ps-qty-header">
          <div>Qty Tercatat</div>
          <div>Satuan</div>
          <div>Qty Aktual</div>
          <div>Selisih</div>
          <div>Harga Rata-rata</div>
        </div>
        <div class="ps-qty-row">
          {{-- Qty Tercatat: always read-only --}}
          <div class="ps-qty-cell ps-qty-cell--readonly">{{ fmt_num($stok) }}</div>

          {{-- Satuan --}}
          <div class="ps-qty-cell">
            <select class="input" style="padding:6px 10px; height:36px;">
              <option>{{ $produk['satuan'] }}</option>
            </select>
          </div>

          {{-- Qty Aktual: editable (perhitungan) / read-only computed (masuk_keluar) --}}
          <div class="ps-qty-cell">
            <input class="input num" type="number" style="text-align:right;" x-model="penyesuaian.qtyAktual"
                   x-show="penyesuaian.tipe === 'perhitungan'" placeholder="0" />
            <div class="ps-qty-cell--readonly" x-show="penyesuaian.tipe === 'masuk_keluar'" x-cloak
                 x-text="{{ $stok }} + Number(penyesuaian.selisih)"></div>
          </div>

          {{-- Selisih: read-only computed (perhitungan) / editable (masuk_keluar) --}}
          <div class="ps-qty-cell">
            <div class="ps-qty-cell--readonly" x-show="penyesuaian.tipe === 'perhitungan'"
                 x-text="Number(penyesuaian.qtyAktual) - {{ $stok }}"></div>
            <input class="input num" type="number" style="text-align:right;" x-model="penyesuaian.selisih"
                   x-show="penyesuaian.tipe === 'masuk_keluar'" x-cloak placeholder="0" />
          </div>

          {{-- Harga Rata-rata: always editable --}}
          <div class="ps-qty-cell">
            <input class="input num" type="number" style="text-align:right;" x-model="penyesuaian.hargaRataRata" placeholder="0" />
          </div>
        </div>
      </div>

    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">
        <x-misc.icon name="x" :size="14" /> Batal
      </button>
      <button class="btn btn-primary">
        <x-misc.icon name="check" :size="14" /> Simpan
      </button>
    </x-slot:footer>
  </x-misc.modal>

</div>
@endsection
