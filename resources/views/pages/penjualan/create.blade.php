@extends('layouts.app')
@section('content')
<div x-data="{
  items: [
    { kode:'TPG-001', nama:'Tepung Terigu Cakra Kembar', qty:120, satuan:'Sak (25 kg)',    harga:215000 },
    { kode:'GLP-002', nama:'Gula Pasir Kemasan Premium', qty:40,  satuan:'Sak (50 kg)',    harga:678000 },
  ],
  diskon: 2500000, ongkir: 1800000, biayaLain: 0,
  get subtotal() { return this.items.reduce((s,i) => s + i.qty * i.harga, 0); },
  get total()    { return this.subtotal - this.diskon + this.ongkir + this.biayaLain; },
  addItem()   { this.items.push({ kode:'', nama:'', qty:1, satuan:'', harga:0 }); },
  removeItem(idx) { this.items.splice(idx,1); },
  fmt(n)  { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); },
}" class="order-page">

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
      <x-erp.field label="Pilih Customer" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
          <x-erp.avatar name="PT Roti Sumber Rejeki" />
          <span style="flex:1; font-weight:500;">PT Roti Sumber Rejeki</span>
          <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-erp.field>
      <x-erp.field label="Nomor SO" :required="true">
        <input class="input mono" value="SO-2026-0143" />
      </x-erp.field>
      <x-erp.field label="Tanggal" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">08 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Jatuh Tempo" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">22 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Gudang" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">Gudang Bekasi</span>
          <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-erp.field>
      <x-erp.field label="Sales Person">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.avatar name="Reza Pratama" />
          <span style="flex:1; font-weight:500;">Reza Pratama</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Termin Pembayaran">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <span style="flex:1;">Net 14 hari</span>
          <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-erp.field>
      <x-erp.field label="Nomor Referensi">
        <input class="input mono" placeholder="(opsional)" />
      </x-erp.field>
    </div>
  </div>

  {{-- Items --}}
  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Daftar Produk</div>
      <button class="btn btn-ghost btn-sm" x-on:click="addItem()">
        <x-misc.icon name="plus" :size="13" />Tambah Baris
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
                  <input class="input" style="height:32px; padding:0 10px;" x-model="it.nama" />
                  <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;" x-text="it.kode || '— belum dipilih'"></div>
                </div>
              </div>
            </td>
            <td><input class="input num" style="height:32px; text-align:right;" x-model.number="it.qty" /></td>
            <td><input class="input" style="height:32px;" x-model="it.satuan" /></td>
            <td><input class="input num" style="height:32px; text-align:right;" x-model.number="it.harga" /></td>
            <td class="num" style="text-align:right; font-weight:600;" x-text="fmt(it.qty * it.harga)"></td>
            <td>
              <button class="btn btn-ghost btn-icon btn-sm" style="border:none;" x-on:click="removeItem(i)">
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
          <x-erp.field label="Diskon">
            <input class="input num" style="text-align:right;" x-model.number="diskon" />
          </x-erp.field>
          <x-erp.field label="Ongkos Kirim">
            <input class="input num" style="text-align:right;" x-model.number="ongkir" />
          </x-erp.field>
          <x-erp.field label="Biaya Lain-lain">
            <input class="input num" style="text-align:right;" x-model.number="biayaLain" />
          </x-erp.field>
        </div>
        <x-erp.field label="Catatan Internal">
          <textarea class="input" rows="2" placeholder="Tulis catatan untuk tim gudang/pengiriman…"></textarea>
        </x-erp.field>
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
