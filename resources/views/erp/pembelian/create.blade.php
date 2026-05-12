@extends('layouts.erp')
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
}" style="padding:24px 28px 60px; display:grid; gap:16px;">

  <div>
    <a href="{{ route('erp.pembelian.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-erp.icon name="chev-left" :size="13" />Kembali
    </a>
    <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Tambah Purchase Order</h1>
    <div style="font-size:13px; color:var(--ink-4); margin-top:4px;">Buat dokumen PO baru. Stok akan ditambah otomatis saat pengiriman diterima.</div>
  </div>

  <div class="card" style="padding:22px 24px; display:grid; gap:18px;">
    <div class="display" style="font-weight:700; font-size:15px;">Informasi Order</div>
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px;">
      <x-erp.field label="Pilih Vendor" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
          <x-erp.avatar name="PT Bogasari Flour Mills" />
          <span style="flex:1; font-weight:500;">PT Bogasari Flour Mills</span>
          <x-erp.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-erp.field>
      <x-erp.field label="Nomor PO" :required="true">
        <input class="input mono" value="PO-2026-0095" />
      </x-erp.field>
      <x-erp.field label="Tanggal" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">12 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Jatuh Tempo" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="calendar" :size="14" stroke="var(--ink-4)" /><span style="flex:1;">26 Mei 2026</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Gudang Tujuan" :required="true">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">Gudang Bekasi</span>
          <x-erp.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-erp.field>
      <x-erp.field label="Pembeli">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-erp.avatar name="Nadia Rahmawati" /><span style="flex:1; font-weight:500;">Nadia Rahmawati</span>
        </div>
      </x-erp.field>
      <x-erp.field label="Termin Pembayaran">
        <div class="input" style="display:flex; align-items:center; gap:8px;"><span style="flex:1;">Net 14 hari</span><x-erp.icon name="chev-down" :size="14" stroke="var(--ink-4)" /></div>
      </x-erp.field>
      <x-erp.field label="No. Referensi Vendor"><input class="input mono" placeholder="(opsional)" /></x-erp.field>
    </div>
  </div>

  <div class="card" style="overflow:hidden;">
    <div style="padding:14px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
      <div class="display" style="font-weight:700; font-size:15px;">Daftar Produk</div>
      <button class="btn btn-ghost btn-sm" x-on:click="addItem()"><x-erp.icon name="plus" :size="13" />Tambah Baris</button>
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
                <div style="width:36px; height:36px; border-radius:8px; background:var(--bg-2); display:grid; place-items:center; flex-shrink:0;">
                  <x-erp.icon name="box" :size="16" stroke="var(--ink-3)" />
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
            <td><button class="btn btn-ghost btn-icon btn-sm" style="border:none;" x-on:click="removeItem(i)"><x-erp.icon name="trash" :size="14" stroke="var(--ink-4)" /></button></td>
          </tr>
        </template>
      </tbody>
    </table>
    <div style="display:grid; grid-template-columns:1fr 360px; border-top:1px solid var(--line);">
      <div style="padding:18px 22px; display:grid; gap:14px;">
        <div class="display" style="font-weight:700; font-size:14px;">Estimasi Biaya Tambahan</div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
          <x-erp.field label="Diskon"><input class="input num" style="text-align:right;" x-model.number="diskon" /></x-erp.field>
          <x-erp.field label="Est. Ongkos Kirim"><input class="input num" style="text-align:right;" x-model.number="ongkir" /></x-erp.field>
          <x-erp.field label="Biaya Lain-lain"><input class="input num" style="text-align:right;" x-model.number="biayaLain" /></x-erp.field>
        </div>
        <div style="font-size:11.5px; color:var(--ink-4); padding:8px 12px; background:var(--accent-3); border-radius:8px; line-height:1.5;">
          Estimasi HPP akan dihitung dari kuantitas riil + biaya kirim aktual saat pengiriman diterima.
        </div>
      </div>
      <div style="padding:22px; background:var(--bg-2); border-left:1px solid var(--line); display:grid; gap:8px; align-content:start;">
        <div class="display" style="font-weight:700; font-size:14px; margin-bottom:6px;">Ringkasan</div>
        <div style="display:flex; justify-content:space-between; font-size:13px;"><span style="color:var(--ink-3);">Subtotal</span><span class="num" x-text="fmt(subtotal)"></span></div>
        <div style="display:flex; justify-content:space-between; font-size:13px;"><span style="color:var(--ink-3);">Diskon</span><span class="num" x-text="'–' + fmt(diskon)"></span></div>
        <div style="display:flex; justify-content:space-between; font-size:13px;"><span style="color:var(--ink-3);">Est. Ongkir</span><span class="num" x-text="fmt(ongkir)"></span></div>
        <div style="height:1px; background:var(--line-2); margin:8px 0;"></div>
        <div style="display:flex; justify-content:space-between; align-items:baseline;">
          <span style="font-size:13px; font-weight:600;">Total Pesanan</span>
          <span class="display num" style="font-size:22px; font-weight:700; color:var(--accent);" x-text="fmt(total)"></span>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('erp.pembelian.index') }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-ghost" style="border-style:dashed;">Simpan Draft</button>
    <button class="btn btn-primary"><x-erp.icon name="check" :size="14" />Simpan PO</button>
  </div>
</div>
@endsection
