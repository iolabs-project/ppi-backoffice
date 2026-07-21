@extends('layouts.app')
@section('content')

<script>
  function soCreateData() {
    return {
      customer: null, selectedGudang: null, salesPerson: null,
      termin: 'Net 14 hari',
      salesPersonList: [
        { id: 'SP-001', nama: 'Reza Pratama' },
        { id: 'SP-002', nama: 'Sari Dewi' },
        { id: 'SP-003', nama: 'Budi Santoso' },
        { id: 'SP-004', nama: 'Andi Wijaya' },
      ],
      customers: @json(collect($kontak)->where('tipe', 'Customer')->values()),
      gudangList: @json($gudang),
      produkList: @json($produk),
      terminList: ['Net 7 hari', 'Net 14 hari', 'Net 30 hari', 'Net 45 hari', 'COD'],
      items: [
        { kode: 'TPG-001', nama: 'Tepung Terigu Cakra Kembar', qty: 120, satuan: 'Kg',    harga: 215000 },
        { kode: 'GLP-002', nama: 'Gula Pasir Kemasan Premium', qty: 40,  satuan: 'Kg',    harga: 678000 },
      ],
      diskon: 2500000, ongkir: 1800000, biayaLain: 0,
      get subtotal() { return this.items.reduce((s, i) => s + i.qty * i.harga, 0); },
      get total()    { return this.subtotal - this.diskon + this.ongkir + this.biayaLain; },
      addItem()      { this.items.push({ kode: '', nama: '', qty: 1, satuan: '', harga: 0 }); },
      removeItem(idx){ if (this.items.length > 1) this.items.splice(idx, 1); },
      fmt(n)         { return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
      parseNum(str)  { return Number(String(str).replace(/[^0-9]/g, '')) || 0; },
      fmtNum(n)      { return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
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

<div x-data="soCreateData()" class="order-page">

  <div>
    <a href="{{ route('penjualan.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali
    </a>
    <h1 class="order-title display">Tambah Sales Order</h1>
    <div class="order-sub">Buat dokumen SO baru. Stok akan dipotong otomatis saat pengiriman dibuat.</div>
  </div>

  {{-- Info Order --}}
  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Order</div>
    <div class="order-form-grid-4">

      {{-- Customer Dropdown --}}
      <x-misc.field label="Pilih Customer" :required="true">
        <x-misc.select display="customer ? customer.nama : 'Pilih Customer'" hasValue="customer"
          placeholder="Cari customer...">
          <template x-for="c in customers.filter(c => !q || c.nama.toLowerCase().includes(q.toLowerCase()))" :key="c.id">
            <div class="dropdown-item" @click="customer=c; open=false; q=''">
              <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                x-text="initials(c.nama)"></div>
              <span x-text="c.nama"></span>
            </div>
          </template>
          <template x-if="!customers.some(c => !q || c.nama.toLowerCase().includes(q.toLowerCase()))">
            <div class="dropdown-empty">Tidak ditemukan</div>
          </template>
        </x-misc.select>
      </x-misc.field>

      {{-- Nomor SO --}}
      <x-misc.field label="Nomor SO" :required="true">
        <input class="input mono" value="SO-2026-0143" />
      </x-misc.field>

      {{-- Tanggal --}}
      <x-misc.field label="Tanggal" :required="true">
        <input type="date" class="input" value="2026-05-08" />
      </x-misc.field>

      {{-- Jatuh Tempo --}}
      <x-misc.field label="Jatuh Tempo" :required="true">
        <input type="date" class="input" value="2026-05-22" />
      </x-misc.field>

      {{-- Gudang Dropdown --}}
      <x-misc.field label="Gudang" :required="true">
        <x-misc.select display="selectedGudang ? selectedGudang.nama : 'Pilih Gudang'" hasValue="selectedGudang"
          placeholder="Cari gudang...">
          <template x-for="g in gudangList.filter(g => !q || g.nama.toLowerCase().includes(q.toLowerCase()))" :key="g.kode">
            <div class="dropdown-item" @click="selectedGudang=g; open=false; q=''">
              <span style="flex:1;" x-text="g.nama"></span>
              <span class="dropdown-item__sub" x-text="g.kota"></span>
            </div>
          </template>
          <template x-if="!gudangList.some(g => !q || g.nama.toLowerCase().includes(q.toLowerCase()))">
            <div class="dropdown-empty">Tidak ditemukan</div>
          </template>
        </x-misc.select>
      </x-misc.field>

      {{-- Sales Person Dropdown --}}
      <x-misc.field label="Sales Person">
        <x-misc.select display="salesPerson ? salesPerson.nama : 'Pilih Sales Person'" hasValue="salesPerson"
          placeholder="Cari sales person...">
          <template x-for="sp in salesPersonList.filter(sp => !q || sp.nama.toLowerCase().includes(q.toLowerCase()))"
            :key="sp.id">
            <div class="dropdown-item" @click="salesPerson=sp; open=false; q=''">
              <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                x-text="initials(sp.nama)"></div>
              <span x-text="sp.nama"></span>
            </div>
          </template>
          <template x-if="!salesPersonList.some(sp => !q || sp.nama.toLowerCase().includes(q.toLowerCase()))">
            <div class="dropdown-empty">Tidak ditemukan</div>
          </template>
        </x-misc.select>
      </x-misc.field>

      {{-- Termin Pembayaran Dropdown --}}
      <x-misc.field label="Termin Pembayaran">
        <x-misc.select display="termin" hasValue="termin" placeholder="Cari termin...">
          <template x-for="t in terminList.filter(t => !q || t.toLowerCase().includes(q.toLowerCase()))" :key="t">
            <div class="dropdown-item" :class="termin === t ? 'dropdown-item--active' : ''"
              @click="termin=t; open=false; q=''" x-text="t"></div>
          </template>
          <template x-if="!terminList.some(t => !q || t.toLowerCase().includes(q.toLowerCase()))">
            <div class="dropdown-empty">Tidak ditemukan</div>
          </template>
        </x-misc.select>
      </x-misc.field>

      {{-- Nomor Referensi --}}
      <x-misc.field label="Nomor Referensi">
        <input class="input mono" placeholder="(opsional)" />
      </x-misc.field>

    </div>
  </div>

  {{-- Items --}}
  <div class="card" style="overflow:visible;">
    <div class="card-hd">
      <div class="display card-hd-title">Daftar Produk</div>
      <button class="btn btn-ghost btn-sm" x-on:click="addItem()">
        <x-misc.icon name="plus" :size="13" />Tambah Produk
      </button>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th><th>Pilih Produk</th>
        <th style="width:120px; text-align:right;">Qty</th>
        <th style="width:140px;">Satuan</th>
        <th style="width:160px; text-align:right;">Harga</th>
        <th style="width:160px; text-align:right;">Subtotal</th>
        <th style="width:40px;"></th>
      </tr></thead>
      <tbody>
        <template x-for="(it, i) in items" :key="i">
          <tr>
            <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div class="product-icon">
                  <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                </div>
                <div style="flex:1;">
                  <x-misc.select display="it.nama || 'Pilih Produk'" hasValue="it.nama" placeholder="Cari produk..."
                    min-width="320px" height="32px">
                    <template x-for="p in produkList.filter(p => !q || p.nama.toLowerCase().includes(q.toLowerCase()) || (p.kode || '').toLowerCase().includes(q.toLowerCase()))"
                      :key="p.kode">
                      <div class="dropdown-item"
                        @click="it.nama=p.nama; it.kode=p.kode; it.satuan=p.satuan; it.harga=p.hargaJual; open=false; q=''">
                        <div style="flex:1; min-width:0;">
                          <div style="font-size:13px;" x-text="p.nama"></div>
                          <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="p.kode"></div>
                        </div>
                        <span class="dropdown-item__sub" x-text="p.satuan"></span>
                      </div>
                    </template>
                    <template x-if="!produkList.some(p => !q || p.nama.toLowerCase().includes(q.toLowerCase()) || (p.kode || '').toLowerCase().includes(q.toLowerCase()))">
                      <div class="dropdown-empty">Tidak ditemukan</div>
                    </template>
                  </x-misc.select>
                  <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                    x-text="it.kode || '— belum dipilih'"></div>
                </div>
              </div>
            </td>
            <td>
              <input class="input num" style="height:32px; text-align:right;"
                :value="fmtNum(it.qty)"
                @focus="$event.target.select()"
                @input="fmtInput($event); it.qty = parseNum($event.target.value)" />
            </td>
            <td>
              <div class="input input--readonly" style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                <span x-text="it.satuan || '—'"></span>
              </div>
            </td>
            <td>
              <input class="input num" style="height:32px; text-align:right;"
                :value="fmtNum(it.harga)"
                @focus="$event.target.select()"
                @input="fmtInput($event); it.harga = parseNum($event.target.value)" />
            </td>
            <td class="num" style="text-align:right; font-weight:600;"
              x-text="fmt(it.qty * it.harga)"></td>
            <td>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                :disabled="items.length <= 1"
                :style="items.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                x-on:click="removeItem(i)">
                <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
              </button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div class="order-items-split">
      <div class="order-extras">
        <div class="display order-extras__title">Biaya Tambahan</div>
        <div class="order-extras__grid-3">
          <x-misc.field label="Diskon">
            <input class="input num" style="text-align:right;"
              :value="fmtNum(diskon)"
              @focus="$event.target.select()"
              @input="fmtInput($event); diskon = parseNum($event.target.value)" />
          </x-misc.field>
          <x-misc.field label="Ongkos Kirim">
            <input class="input num" style="text-align:right;"
              :value="fmtNum(ongkir)"
              @focus="$event.target.select()"
              @input="fmtInput($event); ongkir = parseNum($event.target.value)" />
          </x-misc.field>
          <x-misc.field label="Biaya Lain-lain">
            <input class="input num" style="text-align:right;"
              :value="fmtNum(biayaLain)"
              @focus="$event.target.select()"
              @input="fmtInput($event); biayaLain = parseNum($event.target.value)" />
          </x-misc.field>
        </div>
        <x-misc.field label="Catatan Internal">
          <textarea class="input" rows="2" placeholder="Tulis catatan untuk tim gudang/pengiriman…"></textarea>
        </x-misc.field>
      </div>
      <div class="order-summary">
        <div class="display order-summary__title">Ringkasan</div>
        <div class="order-summary__row">
          <span class="order-summary__label">Subtotal</span>
          <span class="num" style="font-weight:500;" x-text="fmt(subtotal)"></span>
        </div>
        <div class="order-summary__row">
          <span class="order-summary__label">Diskon</span>
          <span class="num" style="font-weight:500;" x-text="'–' + fmt(diskon)"></span>
        </div>
        <div class="order-summary__row">
          <span class="order-summary__label">Ongkos Kirim</span>
          <span class="num" style="font-weight:500;" x-text="fmt(ongkir)"></span>
        </div>
        <div class="order-summary__row">
          <span class="order-summary__label">Biaya Lain-lain</span>
          <span class="num" style="font-weight:500;" x-text="fmt(biayaLain)"></span>
        </div>
        <div class="order-summary__divider"></div>
        <div class="order-summary__total">
          <span class="order-summary__total-label">Total Harga</span>
          <span class="order-summary__total-value display num" x-text="fmt(total)"></span>
        </div>
      </div>
    </div>
  </div>

  <div class="order-form-footer">
    <a href="{{ route('penjualan.index') }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-ghost" style="border-style:dashed;">Simpan Draft</button>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan SO</button>
  </div>

</div>
@endsection
