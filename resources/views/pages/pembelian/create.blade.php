@extends('layouts.app')
@section('content')
<div x-data="{
  items: [
    { kode:'TPG-001', nama:'Tepung Terigu Cakra Kembar', qty:200, satuan:'Sak (25 kg)', harga:188000 },
    { kode:'TPG-002', nama:'Tepung Terigu Segitiga Biru', qty:120, satuan:'Sak (25 kg)', harga:172000 },
  ],
  diskon: 4800000, ongkir: 2400000, biayaLain: 0,
  get subtotal() { return this.items.reduce((s,i) => s + i.qty * i.harga, 0); },
  get total()    { return this.subtotal - this.diskon + this.ongkir + this.biayaLain; },
  addItem()   { this.items.push({ kode:'', nama:'', qty:1, satuan:'', harga:0 }); },
  removeItem(idx) { this.items.splice(idx,1); },
  fmt(n)  { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); },
}" class="order-page">

  <div>
    <a href="{{ route('pembelian.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali
    </a>
    <h1 class="order-title display">Tambah Purchase Order</h1>
    <div class="order-sub">Buat dokumen PO baru. Stok akan ditambah otomatis saat pengiriman diterima.</div>
  </div>

  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Order</div>
    <div class="order-form-grid-4">
      <x-misc.field label="Pilih Vendor" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
          <x-misc.avatar name="PT Bogasari Flour Mills" />
          <span style="flex:1; font-weight:500;">PT Bogasari Flour Mills</span>
          <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-misc.field>
      <x-misc.field label="Nomor PO" :required="true">
        <input class="input mono" value="PO-2026-0095" />
      </x-misc.field>
      <x-misc.field label="Tanggal" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">12 Mei 2026</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Jatuh Tempo" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">26 Mei 2026</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Gudang Tujuan" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">Gudang Bekasi</span>
          <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-misc.field>
      <x-misc.field label="Pembeli">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.avatar name="Nadia Rahmawati" /><span style="flex:1; font-weight:500;">Nadia Rahmawati</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Termin Pembayaran">
        <div class="input" style="display:flex; align-items:center; gap:8px;"><span style="flex:1;">Net 14 hari</span><x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" /></div>
      </x-misc.field>
      <x-misc.field label="No. Referensi Vendor"><input class="input mono" placeholder="(opsional)" /></x-misc.field>
    </div>
  </div>

  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Daftar Produk</div>
      <button class="btn btn-ghost btn-sm" x-on:click="addItem()"><x-misc.icon name="plus" :size="13" />Tambah Baris</button>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th><th>Pilih Produk</th>
        <th style="width:120px; text-align:right;">Qty</th><th style="width:140px;">Satuan</th>
        <th style="width:160px; text-align:right;">Harga Beli</th>
        <th style="width:160px; text-align:right;">Subtotal</th><th style="width:40px;"></th>
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
            <td><button class="btn btn-ghost btn-icon btn-sm" style="border:none;" x-on:click="removeItem(i)"><x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" /></button></td>
          </tr>
        </template>
      </tbody>
    </table>
    <div class="order-items-split">
      <div class="order-extras">
        <div class="display order-extras__title">Estimasi Biaya Tambahan</div>
        <div class="order-extras__grid-3">
          <x-misc.field label="Diskon"><input class="input num" style="text-align:right;" x-model.number="diskon" /></x-misc.field>
          <x-misc.field label="Est. Ongkos Kirim"><input class="input num" style="text-align:right;" x-model.number="ongkir" /></x-misc.field>
          <x-misc.field label="Biaya Lain-lain"><input class="input num" style="text-align:right;" x-model.number="biayaLain" /></x-misc.field>
        </div>
        <div class="order-info-note">
          Estimasi HPP akan dihitung dari kuantitas riil + biaya kirim aktual saat pengiriman diterima.
        </div>
      </div>
      <div class="order-summary">
        <div class="display order-summary__title">Ringkasan</div>
        <div class="order-summary__row"><span class="order-summary__label">Subtotal</span><span class="num" x-text="fmt(subtotal)"></span></div>
        <div class="order-summary__row"><span class="order-summary__label">Diskon</span><span class="num" x-text="'–' + fmt(diskon)"></span></div>
        <div class="order-summary__row"><span class="order-summary__label">Est. Ongkir</span><span class="num" x-text="fmt(ongkir)"></span></div>
        <div class="order-summary__divider"></div>
        <div class="order-summary__total">
          <span class="order-summary__total-label">Total Pesanan</span>
          <span class="order-summary__total-value display num" x-text="fmt(total)"></span>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('pembelian.index') }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-ghost" style="border-style:dashed;">Simpan Draft</button>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan PO</button>
  </div>
</div>
@endsection
