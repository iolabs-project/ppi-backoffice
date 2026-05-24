@extends('layouts.app')
@section('content')

@php
    $bulanMap = [
        'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
        'Mei' => '05', 'Jun' => '06', 'Jul' => '07', 'Agu' => '08',
        'Sep' => '09', 'Okt' => '10', 'Nov' => '11', 'Des' => '12',
    ];
    $toDateInput = function(string $tgl) use ($bulanMap): string {
        [$d, $m, $y] = explode(' ', $tgl);
        return $y . '-' . ($bulanMap[$m] ?? '01') . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
    };
    $editCustomer   = collect($kontak)->where('tipe', 'Customer')->firstWhere('nama', $so['customer']);
    $editGudang     = collect($gudang)->firstWhere('nama', $so['gudang']);
    $editCustomers  = collect($kontak)->where('tipe', 'Customer')->values();
    $editItems      = collect($soDetailItems)->map(function ($i) {
        return ['kode' => $i['kode'], 'nama' => $i['nama'], 'qty' => $i['qty'], 'satuan' => $i['satuan'], 'harga' => $i['harga']];
    })->values();
@endphp

<script>
  function soEditData() {
    return {
      customerOpen: false, gudangOpen: false, terminOpen: false,
      customer: @json($editCustomer),
      selectedGudang: @json($editGudang),
      termin: 'Net 14 hari',
      customers: @json($editCustomers),
      gudangList: @json($gudang),
      produkList: @json($produk),
      terminList: ['Net 7 hari', 'Net 14 hari', 'Net 30 hari', 'Net 45 hari', 'COD'],
      items: @json($editItems),
      diskon: 2500000, ongkir: 1800000, biayaLain: 0,
      get subtotal() { return this.items.reduce((s, i) => s + i.qty * i.harga, 0); },
      get total()    { return this.subtotal - this.diskon + this.ongkir + this.biayaLain; },
      addItem()      { this.items.push({ kode: '', nama: '', qty: 1, satuan: '', harga: 0 }); },
      removeItem(idx){ this.items.splice(idx, 1); },
      fmt(n)         { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); },
      parseNum(str)  { return Number(String(str).replace(/\./g, '')) || 0; },
      fmtNum(n)      { return Math.round(n).toLocaleString('id-ID'); },
      fmtInput(e)    { let r = e.target.value.replace(/[^0-9]/g,''); e.target.value = r ? Number(r).toLocaleString('id-ID') : ''; },
      initials(name) { return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?'; },
    };
  }
</script>

<div x-data="soEditData()" class="order-page">

  <div>
    <a href="{{ route('penjualan.show', $so['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali
    </a>
    <div style="display:flex; align-items:center; gap:10px;">
      <h1 class="order-title display">Edit Draft SO</h1>
      <x-misc.status-badge status="draft" />
    </div>
    <div class="order-sub">{{ $so['id'] }} · Perubahan hanya tersimpan sebagai draft hingga dikonfirmasi.</div>
  </div>

  {{-- Info Order --}}
  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Order</div>
    <div class="order-form-grid-4">

      {{-- Customer Dropdown --}}
      <x-misc.field label="Pilih Customer" :required="true">
        <div class="dropdown-wrap" @click.outside="customerOpen=false">
          <div class="input dropdown-trigger" @click="customerOpen=!customerOpen">
            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
              x-text="initials(customer ? customer.nama : '')"></div>
            <span style="flex:1; font-weight:500;"
              x-text="customer ? customer.nama : 'Pilih Customer'"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="customerOpen" x-cloak>
            <template x-for="c in customers" :key="c.id">
              <div class="dropdown-item" @click="customer=c; customerOpen=false">
                <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                  x-text="initials(c.nama)"></div>
                <span x-text="c.nama"></span>
              </div>
            </template>
          </div>
        </div>
      </x-misc.field>

      {{-- Nomor SO --}}
      <x-misc.field label="Nomor SO" :required="true">
        <input class="input mono" value="{{ $so['id'] }}" />
      </x-misc.field>

      {{-- Tanggal --}}
      <x-misc.field label="Tanggal" :required="true">
        <input type="date" class="input" value="{{ $toDateInput($so['tanggal']) }}" />
      </x-misc.field>

      {{-- Jatuh Tempo --}}
      <x-misc.field label="Jatuh Tempo" :required="true">
        <input type="date" class="input" value="{{ $toDateInput($so['jatuhTempo']) }}" />
      </x-misc.field>

      {{-- Gudang Dropdown --}}
      <x-misc.field label="Gudang" :required="true">
        <div class="dropdown-wrap" @click.outside="gudangOpen=false">
          <div class="input dropdown-trigger" @click="gudangOpen=!gudangOpen">
            <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
            <span style="flex:1;"
              x-text="selectedGudang ? selectedGudang.nama : 'Pilih Gudang'"></span>
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

      {{-- Sales Person (static) --}}
      <x-misc.field label="Sales Person">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.avatar name="Reza Pratama" />
          <span style="flex:1; font-weight:500;">Reza Pratama</span>
        </div>
      </x-misc.field>

      {{-- Termin Pembayaran Dropdown --}}
      <x-misc.field label="Termin Pembayaran">
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
          <tr x-data="{ open: false }">
            <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div class="product-icon">
                  <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                </div>
                <div style="flex:1;" class="dropdown-wrap" @click.outside="open=false">
                  <div class="input dropdown-trigger" style="height:32px; padding:0 10px;" @click="open=!open">
                    <span style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                      :style="it.nama ? '' : 'color:var(--ink-4);'"
                      x-text="it.nama || 'Pilih Produk'"></span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="m6 9 6 6 6-6"/></svg>
                  </div>
                  <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                    x-text="it.kode || '— belum dipilih'"></div>
                  <div class="dropdown-menu" x-show="open" x-cloak style="min-width:320px;">
                    <template x-for="p in produkList" :key="p.kode">
                      <div class="dropdown-item"
                        @click="it.nama=p.nama; it.kode=p.kode; it.satuan=p.satuan; it.harga=p.hargaJual; open=false">
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
            <td>
              <input class="input num" style="height:32px; text-align:right;"
                :value="fmtNum(it.qty)"
                @focus="$event.target.value = it.qty; $event.target.select()"
                @input="fmtInput($event)"
                @blur="it.qty = parseNum($event.target.value)" />
            </td>
            <td>
              <div class="input input--readonly" style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                <span x-text="it.satuan || '—'"></span>
              </div>
            </td>
            <td>
              <input class="input num" style="height:32px; text-align:right;"
                :value="fmtNum(it.harga)"
                @focus="$event.target.value = it.harga; $event.target.select()"
                @input="fmtInput($event)"
                @blur="it.harga = parseNum($event.target.value)" />
            </td>
            <td class="num" style="text-align:right; font-weight:600;"
              x-text="fmt(it.qty * it.harga)"></td>
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
          <x-misc.field label="Diskon">
            <input class="input num" style="text-align:right;"
              :value="fmtNum(diskon)"
              @focus="$event.target.value = diskon; $event.target.select()"
              @input="fmtInput($event)"
              @blur="diskon = parseNum($event.target.value)" />
          </x-misc.field>
          <x-misc.field label="Ongkos Kirim">
            <input class="input num" style="text-align:right;"
              :value="fmtNum(ongkir)"
              @focus="$event.target.value = ongkir; $event.target.select()"
              @input="fmtInput($event)"
              @blur="ongkir = parseNum($event.target.value)" />
          </x-misc.field>
          <x-misc.field label="Biaya Lain-lain">
            <input class="input num" style="text-align:right;"
              :value="fmtNum(biayaLain)"
              @focus="$event.target.value = biayaLain; $event.target.select()"
              @input="fmtInput($event)"
              @blur="biayaLain = parseNum($event.target.value)" />
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
    <a href="{{ route('penjualan.show', $so['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-ghost" style="border-style:dashed;">Simpan Draft</button>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Konfirmasi SO</button>
  </div>

</div>
@endsection
