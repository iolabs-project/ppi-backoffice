@extends('layouts.app')
@section('content')

<script>
  function tagihanPembelianCreateData() {
    return {
      vendorOpen: false, poOpen: false, metodeOpen: false,
      vendor: null, selectedPO: null,
      metode: 'Transfer Bank',
      vendors: @json(collect($kontak)->where('tipe', 'Vendor')->values()),
      purchaseOrders: @json(collect($purchaseOrders)->where('status', 'disetujui')->values()),
      metodeList: ['Transfer Bank', 'Tunai', 'Cek / Giro', 'QRIS', 'Kartu Debit'],
      jumlah: 0,
      fmtNum(n)     { return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
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

  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Tagihan</div>
    <div class="order-form-grid-3">

      {{-- Vendor --}}
      <x-misc.field label="Vendor" :required="true">
        <div class="dropdown-wrap" @click.outside="vendorOpen=false">
          <div class="input dropdown-trigger" @click="vendorOpen=!vendorOpen">
            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
              x-text="initials(vendor ? vendor.nama : '')"></div>
            <span style="flex:1; font-weight:500;" x-text="vendor ? vendor.nama : 'Pilih Vendor'"></span>
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

      {{-- Ref PO --}}
      <x-misc.field label="Ref. Purchase Order">
        <div class="dropdown-wrap" @click.outside="poOpen=false">
          <div class="input dropdown-trigger mono" @click="poOpen=!poOpen">
            <span style="flex:1;" x-text="selectedPO ? selectedPO.id : 'Pilih PO (opsional)'"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="poOpen" x-cloak>
            <template x-for="s in purchaseOrders" :key="s.id">
              <div class="dropdown-item" @click="selectedPO=s; poOpen=false">
                <div style="flex:1;">
                  <div class="mono" style="font-size:13px;" x-text="s.id"></div>
                  <div style="font-size:11px; color:var(--ink-4);" x-text="s.vendor"></div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </x-misc.field>

      {{-- No. Tagihan --}}
      <x-misc.field label="Nomor Tagihan" :required="true">
        <input class="input mono" value="BILL-2026-0025" />
      </x-misc.field>

      {{-- Tanggal --}}
      <x-misc.field label="Tanggal" :required="true">
        <input type="date" class="input" value="{{ date('Y-m-d') }}" />
      </x-misc.field>

      {{-- Jatuh Tempo --}}
      <x-misc.field label="Jatuh Tempo" :required="true">
        <input type="date" class="input" value="{{ date('Y-m-d', strtotime('+14 days')) }}" />
      </x-misc.field>

      {{-- Metode --}}
      <x-misc.field label="Metode Pembayaran">
        <div class="dropdown-wrap" @click.outside="metodeOpen=false">
          <div class="input dropdown-trigger" @click="metodeOpen=!metodeOpen">
            <span style="flex:1;" x-text="metode"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="metodeOpen" x-cloak>
            <template x-for="m in metodeList" :key="m">
              <div class="dropdown-item"
                :class="metode === m ? 'dropdown-item--active' : ''"
                @click="metode=m; metodeOpen=false"
                x-text="m"></div>
            </template>
          </div>
        </div>
      </x-misc.field>

    </div>

    <x-misc.field label="Jumlah Tagihan" :required="true">
      <input class="input num" style="text-align:right; max-width:280px;"
        :value="fmtNum(jumlah)"
        @focus="$event.target.select()"
        @input="fmtInput($event)"
        @blur="jumlah = parseNum($event.target.value)" />
    </x-misc.field>

    <x-misc.field label="Catatan">
      <textarea class="input" rows="2" placeholder="(opsional)"></textarea>
    </x-misc.field>
  </div>

  <div class="order-form-footer">
    <a href="{{ route('pembelian.tagihan_list') }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Tagihan</button>
  </div>

</div>
@endsection
