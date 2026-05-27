@extends('layouts.app')
@section('content')

<script>
  function tagihanShowData() {
    return {
      payments: [],
      metodeOpen: false,
      newPayment: {
        tanggal: '{{ date("Y-m-d") }}',
        jumlah: 0,
        metode: 'Transfer Bank',
        referensi: '',
        catatan: '',
      },
      metodeList: ['Transfer Bank', 'Tunai', 'Cek / Giro', 'QRIS', 'Kartu Debit'],
      totalTagihan: {{ $inv['total'] }},
      get totalDibayar() { return this.payments.reduce((s, p) => s + p.jumlah, 0); },
      get sisaTagihan()  { return this.totalTagihan - this.totalDibayar; },
      tambahPembayaran() {
        if (this.newPayment.jumlah <= 0) return;
        this.payments.push({ ...this.newPayment, id: Date.now() });
        this.newPayment = { tanggal: '{{ date("Y-m-d") }}', jumlah: 0, metode: 'Transfer Bank', referensi: '', catatan: '' };
      },
      hapusPembayaran(id) { this.payments = this.payments.filter(p => p.id !== id); },
      fmtRp(n)      { return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
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
    };
  }
</script>

<div x-data="tagihanShowData()" class="order-page">

  <div>
    <a href="{{ route('penjualan.tagihan_list') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali ke Tagihan
    </a>
    <div class="order-title-row">
      <h1 class="order-title display">{{ $inv['id'] }}</h1>
      <x-misc.status-badge :status="$inv['status']" />
    </div>
    <div class="order-sub">
      Ref. SO: <strong>{{ $inv['soRef'] }}</strong> &nbsp;·&nbsp;
      Diterbitkan {{ $inv['tanggal'] }} &nbsp;·&nbsp;
      Jatuh Tempo {{ $inv['jatuhTempo'] }}
    </div>
  </div>

  {{-- Info Tagihan --}}
  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Tagihan</div>
    <div class="order-form-grid-3">
      <x-misc.field label="Customer">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
          <x-misc.avatar :name="$inv['customer']" />
          <span style="flex:1; font-weight:500;">{{ $inv['customer'] }}</span>
        </div>
      </x-misc.field>
      <x-misc.field label="No. Invoice">
        <div class="input mono input--readonly" style="display:flex; align-items:center;">
          <span style="flex:1; font-weight:600;">{{ $inv['id'] }}</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Ref. Sales Order">
        <div class="input mono input--readonly" style="display:flex; align-items:center;">
          <a href="{{ route('penjualan.show', $inv['soRef']) }}" class="link orange-link" style="flex:1; font-weight:600;">{{ $inv['soRef'] }}</a>
        </div>
      </x-misc.field>
      <x-misc.field label="Tanggal Invoice">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="calendar" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $inv['tanggal'] }}</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Jatuh Tempo">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="calendar" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $inv['jatuhTempo'] }}</span>
        </div>
      </x-misc.field>
    </div>
  </div>

  {{-- Produk --}}
  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Daftar Produk</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th>
        <th>Produk</th>
        <th style="text-align:right; width:120px;">Qty</th>
        <th style="width:120px;">Satuan</th>
        <th style="text-align:right; width:160px;">Harga</th>
        <th style="text-align:right; width:160px;">Subtotal</th>
      </tr></thead>
      <tbody>
        @foreach ($soDetailItems as $i => $it)
          <tr>
            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</td>
            <td>
              <div style="font-weight:600;">{{ $it['nama'] }}</div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it['kode'] }}</div>
            </td>
            <td class="num" style="text-align:right;">{{ number_format($it['qty'], 0, ',', '.') }}</td>
            <td style="color:var(--ink-3);">{{ $it['satuan'] }}</td>
            <td class="num" style="text-align:right;">{{ fmt_rp($it['harga']) }}</td>
            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($it['qty'] * $it['harga']) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="order-summary" style="margin:0; border-top:1px solid var(--border-line); padding:20px 24px;">
      <div class="order-summary__row">
        <span class="order-summary__label">Subtotal</span>
        <span class="num" style="font-weight:500;">{{ fmt_rp(collect($soDetailItems)->sum(fn($i) => $i['qty'] * $i['harga'])) }}</span>
      </div>
      <div class="order-summary__divider"></div>
      <div class="order-summary__total">
        <span class="order-summary__total-label">Total Tagihan</span>
        <span class="order-summary__total-value display num">{{ fmt_rp($inv['total']) }}</span>
      </div>
    </div>
  </div>

  {{-- Riwayat & Form Pembayaran --}}
  <div class="card" style="overflow:visible;">
    <div class="card-hd">
      <div class="display card-hd-title">Pembayaran</div>
      <div style="display:flex; align-items:center; gap:16px; font-size:13px;">
        <span style="color:var(--ink-4);">Sisa: <strong class="num" style="color:var(--ink);" x-text="fmtRp(sisaTagihan)"></strong></span>
        <span style="color:var(--ink-4);">Dibayar: <strong class="num" style="color:var(--accent);" x-text="fmtRp(totalDibayar)"></strong></span>
      </div>
    </div>

    {{-- Riwayat pembayaran --}}
    <template x-if="payments.length > 0">
      <table class="tbl" style="border-bottom:1px solid var(--border-line);">
        <thead><tr>
          <th>Tanggal</th>
          <th>Metode</th>
          <th>Referensi</th>
          <th style="text-align:right;">Jumlah</th>
          <th style="width:40px;"></th>
        </tr></thead>
        <tbody>
          <template x-for="p in payments" :key="p.id">
            <tr>
              <td class="table-secondary" x-text="p.tanggal"></td>
              <td x-text="p.metode"></td>
              <td class="mono" style="color:var(--ink-4);" x-text="p.referensi || '—'"></td>
              <td class="num" style="text-align:right; font-weight:600;" x-text="fmtRp(p.jumlah)"></td>
              <td>
                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;" @click="hapusPembayaran(p.id)">
                  <x-misc.icon name="trash" :size="13" stroke="var(--ink-4)" />
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </template>

    {{-- Form tambah pembayaran --}}
    <div style="padding:20px 24px;">
      <div class="display" style="font-size:13px; margin-bottom:16px; color:var(--ink-3);">Tambah Pembayaran</div>
      <div class="order-form-grid-3" style="margin-bottom:12px;">

        {{-- Jumlah --}}
        <x-misc.field label="Jumlah Dibayar" :required="true">
          <input class="input num" style="text-align:right;"
            :value="fmtNum(newPayment.jumlah)"
            @focus="$event.target.select()"
            @input="fmtInput($event)"
            @blur="newPayment.jumlah = parseNum($event.target.value)" />
        </x-misc.field>

        {{-- Tanggal --}}
        <x-misc.field label="Tanggal" :required="true">
          <input type="date" class="input" x-model="newPayment.tanggal" />
        </x-misc.field>

        {{-- Metode --}}
        <x-misc.field label="Metode Pembayaran">
          <div class="dropdown-wrap" @click.outside="metodeOpen=false">
            <div class="input dropdown-trigger" @click="metodeOpen=!metodeOpen">
              <span style="flex:1;" x-text="newPayment.metode"></span>
              <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
            </div>
            <div class="dropdown-menu" x-show="metodeOpen" x-cloak>
              <template x-for="m in metodeList" :key="m">
                <div class="dropdown-item"
                  :class="newPayment.metode === m ? 'dropdown-item--active' : ''"
                  @click="newPayment.metode=m; metodeOpen=false"
                  x-text="m"></div>
              </template>
            </div>
          </div>
        </x-misc.field>

        {{-- Referensi --}}
        <x-misc.field label="Referensi">
          <input class="input mono" placeholder="No. transfer / cek…" x-model="newPayment.referensi" />
        </x-misc.field>

        {{-- Catatan --}}
        <x-misc.field label="Catatan" style="grid-column: span 2;">
          <input class="input" placeholder="(opsional)" x-model="newPayment.catatan" />
        </x-misc.field>

      </div>

      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div class="num" style="font-size:13px; color:var(--ink-3);">
          Sisa setelah pembayaran ini:
          <strong style="color:var(--ink);" x-text="fmtRp(Math.max(0, sisaTagihan - newPayment.jumlah))"></strong>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('penjualan.tagihan_list') }}" class="btn btn-ghost">Kembali</a>
    <button class="btn btn-ghost"><x-misc.icon name="print" :size="14" />Cetak Invoice</button>
    <button class="btn btn-primary" @click="tambahPembayaran()">
      <x-misc.icon name="check" :size="14" />Simpan Pembayaran
    </button>
  </div>

</div>
@endsection
