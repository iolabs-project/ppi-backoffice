@extends('layouts.app')
@section('content')

<script>
  function tagihanPembelianCreateData() {
    return {
      vendorOpen: false, gudangOpen: false, terminOpen: false,
      vendor: null, selectedGudang: null,
      termin: 'Net 30',
      pesanOpen: false,
      vendors: @json(collect($kontak)->where('tipe', 'Vendor')->values()),
      gudangList: @json($gudang),
      produkList: @json($produk),
      terminList: ['Net 7', 'Net 14', 'Net 30', 'Net 45', 'COD'],
      pajakList: ['—', 'PPN 11%', 'PPN 12%', 'PPh 21', 'PPh 23', 'PPh 25'],
      items: [{ produkOpen: false, satuanOpen: false, pajakOpen: false, kode: '', nama: '', deskripsi: '', qty: 1, satuan: '', discount: 0, harga: 0, pajak: '—' }],
      diskon: 0, ongkir: 0, biayaTransaksi: 0, pemotongan: 0, uangMuka: 0,
      get subtotal() {
        return this.items.reduce((s, i) => {
          let line = i.qty * i.harga;
          return s + line - (line * (i.discount / 100));
        }, 0);
      },
      get total() { return this.subtotal - this.diskon + this.ongkir + this.biayaTransaksi; },
      get sisaTagihan() { return this.total - this.pemotongan - this.uangMuka; },
      lineTotal(i) { let l = i.qty * i.harga; return l - (l * i.discount / 100); },
      addItem() { this.items.push({ produkOpen: false, satuanOpen: false, pajakOpen: false, kode: '', nama: '', deskripsi: '', qty: 1, satuan: '', discount: 0, harga: 0, pajak: '—' }); },
      removeItem(idx) { if (this.items.length > 1) this.items.splice(idx, 1); },
      fmt(n) { return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
      fmtNum(n) { return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
      parseNum(str) { return Number(String(str).replace(/[^0-9]/g, '')) || 0; },
      fmtInput(e) {
        let el = e.target;
        let pos = el.value.slice(0, el.selectionStart).replace(/[^0-9]/g, '').length;
        let raw = el.value.replace(/[^0-9]/g, '');
        el.value = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        let i = 0, c = 0;
        while (i < el.value.length && c < pos) { if (/\d/.test(el.value[i])) c++; i++; }
        el.setSelectionRange(i, i);
      },
      initials(name) { return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?'; },
    };
  }
</script>

<div x-data="tagihanPembelianCreateData()" class="order-page">

  <div>
    <a href="{{ route('pembelian.tagihan_list') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali
    </a>
    <h1 class="order-title display">Buat Tagihan Pembelian</h1>
    <div class="order-sub">Buat tagihan untuk pembayaran ke vendor.</div>
  </div>

  {{-- Info Tagihan --}}
  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Tagihan</div>
    <div class="order-form-grid-3">

      {{-- Vendor --}}
      <div style="grid-column: span 2;">
      <x-misc.field label="Vendor" :required="true">
        <div class="dropdown-wrap" @click.outside="vendorOpen=false">
          <div class="input dropdown-trigger" @click="vendorOpen=!vendorOpen">
            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
              x-text="initials(vendor ? vendor.nama : '')"></div>
            <span style="flex:1; font-weight:500;" x-text="vendor ? vendor.nama : 'Pilih kontak'"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="vendorOpen" x-cloak>
            <template x-for="v in vendors" :key="v.id">
              <div class="dropdown-item" @click="vendor=v; vendorOpen=false">
                <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                  x-text="initials(v.nama)"></div>
                <span x-text="v.nama"></span>
              </div>
            </template>
          </div>
        </div>
      </x-misc.field>
      </div>

      {{-- Nomor Tagihan --}}
      <x-misc.field label="Nomor" :required="true">
        <input class="input mono" value="BILL/{{ date('Y') }}/{{ str_pad(rand(1,999), 5, '0', STR_PAD_LEFT) }}" />
      </x-misc.field>

      {{-- Tanggal --}}
      <x-misc.field label="Tgl. Transaksi" :required="true">
        <input type="date" class="input" value="{{ date('Y-m-d') }}" />
      </x-misc.field>

      {{-- Jatuh Tempo --}}
      <x-misc.field label="Tgl. Jatuh Tempo" :required="true">
        <input type="date" class="input" value="{{ date('Y-m-d', strtotime('+30 days')) }}" />
      </x-misc.field>

      {{-- Termin --}}
      <x-misc.field label="Termin">
        <div class="dropdown-wrap" @click.outside="terminOpen=false">
          <div class="input dropdown-trigger" @click="terminOpen=!terminOpen">
            <span style="flex:1;" x-text="termin"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="terminOpen" x-cloak>
            <template x-for="t in terminList" :key="t">
              <div class="dropdown-item"
                :class="termin === t ? 'dropdown-item--active' : ''"
                @click="termin=t; terminOpen=false"
                x-text="t"></div>
            </template>
          </div>
        </div>
      </x-misc.field>

      {{-- Gudang --}}
      <x-misc.field label="Gudang" :required="true">
        <div class="dropdown-wrap" @click.outside="gudangOpen=false">
          <div class="input dropdown-trigger" @click="gudangOpen=!gudangOpen">
            <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
            <span style="flex:1;" x-text="selectedGudang ? selectedGudang.nama : 'Pilih Gudang'"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="gudangOpen" x-cloak>
            <template x-for="g in gudangList" :key="g.kode">
              <div class="dropdown-item" @click="selectedGudang=g; gudangOpen=false">
                <span style="flex:1;" x-text="g.nama"></span>
                <span class="dropdown-item__sub" x-text="g.kota"></span>
              </div>
            </template>
          </div>
        </div>
      </x-misc.field>

      {{-- Scan Barcode --}}
      <x-misc.field label="Scan Barcode / SKU">
        <input class="input" placeholder="Scan atau ketik SKU…" />
      </x-misc.field>

    </div>
  </div>

  {{-- Items --}}
  <div class="card" style="overflow:visible;">
    <div class="card-hd">
      <div class="display card-hd-title">Daftar Produk</div>
    </div>

    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th>
        <th>Produk</th>
        <th style="width:160px;">Deskripsi</th>
        <th style="width:100px; text-align:right;">Kuantitas</th>
        <th style="width:110px;">Satuan</th>
        <th style="width:90px; text-align:right;">Discount</th>
        <th style="width:150px; text-align:right;">Harga</th>
        <th style="width:120px;">Pajak</th>
        <th style="width:150px; text-align:right;">Jumlah</th>
        <th style="width:40px;"></th>
      </tr></thead>
      <tbody>
        <template x-for="(it, i) in items" :key="i">
          <tr>
            <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>

            {{-- Produk --}}
            <td>
              <div style="display:flex; align-items:center; gap:8px;">
                <div class="product-icon">
                  <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                </div>
                <div style="flex:1;" class="dropdown-wrap" @click.outside="it.produkOpen=false">
                  <div class="input dropdown-trigger" style="height:32px; padding:0 10px;" @click="it.produkOpen=!it.produkOpen">
                    <span style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                      :style="it.nama ? '' : 'color:var(--ink-4);'"
                      x-text="it.nama || 'Pilih Produk'"></span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="m6 9 6 6 6-6"/></svg>
                  </div>
                  <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                    x-text="it.kode || '— belum dipilih'"></div>
                  <div class="dropdown-menu" x-show="it.produkOpen" x-cloak style="min-width:300px;">
                    <template x-for="p in produkList" :key="p.kode">
                      <div class="dropdown-item"
                        @click="it.nama=p.nama; it.kode=p.kode; it.satuan=p.satuan; it.harga=p.hargaBeli; it.produkOpen=false">
                        <div style="flex:1; min-width:0;">
                          <div style="font-size:13px;" x-text="p.nama"></div>
                          <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="p.kode"></div>
                        </div>
                        <span class="dropdown-item__sub" x-text="p.satuan"></span>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </td>

            {{-- Deskripsi --}}
            <td>
              <input class="input" style="height:32px;"
                x-model="it.deskripsi" placeholder="—" />
            </td>

            {{-- Qty --}}
            <td>
              <input class="input num" style="height:32px; text-align:right;"
                :value="fmtNum(it.qty)"
                @focus="$event.target.select()"
                @input="fmtInput($event); it.qty = parseNum($event.target.value)" />
            </td>

            {{-- Satuan --}}
            <td>
              <div class="input input--readonly" style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                <span x-text="it.satuan || '—'"></span>
              </div>
            </td>

            {{-- Discount --}}
            <td>
              <input class="input num" style="height:32px; text-align:right; padding-right:6px;"
                :value="it.discount + '%'"
                @focus="$event.target.value = it.discount; $event.target.select()"
                @blur="it.discount = Math.min(100, Math.max(0, parseFloat($event.target.value) || 0)); $event.target.value = it.discount + '%'" />
            </td>

            {{-- Harga --}}
            <td>
              <input class="input num" style="height:32px; text-align:right;"
                :value="fmtNum(it.harga)"
                @focus="$event.target.select()"
                @input="fmtInput($event); it.harga = parseNum($event.target.value)" />
            </td>

            {{-- Pajak --}}
            <td>
              <div class="dropdown-wrap" @click.outside="it.pajakOpen=false">
                <div class="input dropdown-trigger" style="height:32px; padding:0 8px;" @click="it.pajakOpen=!it.pajakOpen">
                  <span style="flex:1; font-size:12px;" x-text="it.pajak"></span>
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="m6 9 6 6 6-6"/></svg>
                </div>
                <div class="dropdown-menu" x-show="it.pajakOpen" x-cloak style="min-width:140px;">
                  <template x-for="pj in pajakList" :key="pj">
                    <div class="dropdown-item"
                      :class="it.pajak === pj ? 'dropdown-item--active' : ''"
                      @click="it.pajak=pj; it.pajakOpen=false"
                      x-text="pj"></div>
                  </template>
                </div>
              </div>
            </td>

            {{-- Jumlah --}}
            <td class="num" style="text-align:right; font-weight:600;"
              x-text="fmt(lineTotal(it))"></td>

            {{-- Hapus --}}
            <td>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                :disabled="items.length <= 1"
                :style="items.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                @click="removeItem(i)">
                <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
              </button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div style="padding:12px 20px; border-top:1px solid var(--border);">
      <button class="btn btn-ghost btn-sm" @click="addItem()">
        <x-misc.icon name="plus" :size="13" />Tambah baris
      </button>
    </div>

    <div class="order-items-split">
      {{-- Pesan --}}
      <div class="order-extras" style="align-self:flex-start;">
        <button class="btn btn-ghost btn-sm" style="width:fit-content;" @click="pesanOpen=!pesanOpen">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
            :style="pesanOpen ? 'transform:rotate(90deg);' : ''" style="transition:transform .15s;"><path d="m9 18 6-6-6-6"/></svg>
          Pesan
        </button>
        <div x-show="pesanOpen" x-cloak>
          <textarea class="input" rows="3" placeholder="Catatan untuk vendor…" style="width:100%; margin-top:8px;"></textarea>
        </div>
      </div>

      {{-- Summary --}}
      <div class="order-summary">
        <div class="display order-summary__title">Ringkasan</div>

        <div class="order-summary__row">
          <span class="order-summary__label">Sub Total</span>
          <span class="num" style="font-weight:500;" x-text="fmt(subtotal)"></span>
        </div>

        <div class="order-summary__row" style="align-items:center;">
          <span class="order-summary__label">Diskon</span>
          <input class="input num" style="height:28px; width:130px; text-align:right; font-size:13px;"
            :value="fmtNum(diskon)"
            @focus="$event.target.select()"
            @input="fmtInput($event); diskon = parseNum($event.target.value)" />
        </div>

        <div class="order-summary__row" style="align-items:center;">
          <span class="order-summary__label">Biaya Pengiriman</span>
          <input class="input num" style="height:28px; width:130px; text-align:right; font-size:13px;"
            :value="fmtNum(ongkir)"
            @focus="$event.target.select()"
            @input="fmtInput($event); ongkir = parseNum($event.target.value)" />
        </div>

        <div class="order-summary__row" style="align-items:center;">
          <span class="order-summary__label">Biaya Transaksi</span>
          <input class="input num" style="height:28px; width:130px; text-align:right; font-size:13px;"
            :value="fmtNum(biayaTransaksi)"
            @focus="$event.target.select()"
            @input="fmtInput($event); biayaTransaksi = parseNum($event.target.value)" />
        </div>

        <div class="order-summary__divider"></div>

        <div class="order-summary__row">
          <span class="order-summary__label" style="font-weight:600;">Total</span>
          <span class="num" style="font-weight:600;" x-text="fmt(total)"></span>
        </div>

        <div class="order-summary__row" style="align-items:center;">
          <span class="order-summary__label">Pemotongan</span>
          <input class="input num" style="height:28px; width:130px; text-align:right; font-size:13px;"
            :value="fmtNum(pemotongan)"
            @focus="$event.target.select()"
            @input="fmtInput($event); pemotongan = parseNum($event.target.value)" />
        </div>

        <div class="order-summary__row" style="align-items:center;">
          <span class="order-summary__label">Uang Muka</span>
          <input class="input num" style="height:28px; width:130px; text-align:right; font-size:13px;"
            :value="fmtNum(uangMuka)"
            @focus="$event.target.select()"
            @input="fmtInput($event); uangMuka = parseNum($event.target.value)" />
        </div>

        <div class="order-summary__divider"></div>

        <div class="order-summary__total">
          <span class="order-summary__total-label">Sisa Tagihan</span>
          <span class="order-summary__total-value display num" x-text="fmt(sisaTagihan)"></span>
        </div>
      </div>
    </div>
  </div>

  <div class="order-form-footer">
    <a href="{{ route('pembelian.tagihan_list') }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-ghost" style="border-style:dashed;">Simpan Draft</button>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Tagihan</button>
  </div>

</div>
@endsection
