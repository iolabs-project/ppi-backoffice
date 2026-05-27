@extends('layouts.app')
@section('content')

<script>
  function tagihanCreateData() {
    return {
      customerOpen: false, soOpen: false, metodeOpen: false,
      customer: null, selectedSO: null,
      metode: 'Transfer Bank',
      customers: @json(collect($kontak)->where('tipe', 'Customer')->values()),
      salesOrders: @json(collect($salesOrders)->where('status', 'selesai')->values()),
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

<div x-data="tagihanCreateData()" class="order-page">

  <div>
    <a href="{{ route('penjualan.tagihan_list') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali
    </a>
    <h1 class="order-title display">Buat Tagihan</h1>
    <div class="order-sub">Buat invoice untuk dikirimkan ke customer.</div>
  </div>

  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Tagihan</div>
    <div class="order-form-grid-3">

      {{-- Customer --}}
      <x-misc.field label="Customer" :required="true">
        <div class="dropdown-wrap" @click.outside="customerOpen=false">
          <div class="input dropdown-trigger" @click="customerOpen=!customerOpen">
            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
              x-text="initials(customer ? customer.nama : '')"></div>
            <span style="flex:1; font-weight:500;" x-text="customer ? customer.nama : 'Pilih Customer'"></span>
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

      {{-- Ref SO --}}
      <x-misc.field label="Ref. Sales Order">
        <div class="dropdown-wrap" @click.outside="soOpen=false">
          <div class="input dropdown-trigger mono" @click="soOpen=!soOpen">
            <span style="flex:1;" x-text="selectedSO ? selectedSO.id : 'Pilih SO (opsional)'"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="soOpen" x-cloak>
            <template x-for="s in salesOrders" :key="s.id">
              <div class="dropdown-item" @click="selectedSO=s; soOpen=false">
                <div style="flex:1;">
                  <div class="mono" style="font-size:13px;" x-text="s.id"></div>
                  <div style="font-size:11px; color:var(--ink-4);" x-text="s.customer"></div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </x-misc.field>

      {{-- No. Invoice --}}
      <x-misc.field label="Nomor Invoice" :required="true">
        <input class="input mono" value="INV-2026-0043" />
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
    <a href="{{ route('penjualan.tagihan_list') }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Tagihan</button>
  </div>

</div>
@endsection
