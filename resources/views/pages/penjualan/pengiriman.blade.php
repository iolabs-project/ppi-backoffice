@extends('layouts.app')
@section('content')

<script>
  function pengirimanData() {
    return {
      statusOpen: false,
      status: 'open',
      statusList: [
        { id: 'open',     label: 'Open' },
        { id: 'terkirim', label: 'Terkirim' },
      ],
      driverOpen: false,
      driver: { id: 'DRV-001', nama: 'Sutrisno Hadi', plat: 'B 9821 KAB', eta: '14:30' },
      driverList: [
        { id: 'DRV-001', nama: 'Sutrisno Hadi',  plat: 'B 9821 KAB', eta: '14:30' },
        { id: 'DRV-002', nama: 'Ahmad Fauzi',    plat: 'B 4432 TGA', eta: '15:00' },
        { id: 'DRV-003', nama: 'Dedi Kurniawan', plat: 'D 7810 MNP', eta: '16:00' },
      ],
      items: @json($soDetailItems).map(it => ({ ...it, qtyDikirim: it.qty })),
      biayaPengiriman: 1800000,
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

<div x-data="pengirimanData()" class="order-page">

  <div>
    <a href="{{ route('penjualan.show', $so['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali ke {{ $so['id'] }}
    </a>
    <div class="order-title-row">
      <h1 class="order-title display">Buat Pengiriman</h1>
      {{-- Status dropdown --}}
      <div class="dropdown-wrap" @click.outside="statusOpen=false">
        <div @click="statusOpen=!statusOpen" style="cursor:pointer;">
          <span :class="{
            'chip chip-info': status === 'open',
            'chip chip-ok':   status === 'terkirim'
          }">
            <span :class="{
              'chip-dot dot-info': status === 'open',
              'chip-dot dot-ok':   status === 'terkirim'
            }"></span>
            <span x-text="statusList.find(s => s.id === status)?.label"></span>
            <x-misc.icon name="chev-down" :size="12" stroke="currentColor" />
          </span>
        </div>
        <div class="dropdown-menu" x-show="statusOpen" x-cloak style="min-width:140px;">
          <template x-for="s in statusList" :key="s.id">
            <div class="dropdown-item" :class="status === s.id ? 'dropdown-item--active' : ''"
              @click="status=s.id; statusOpen=false" x-text="s.label"></div>
          </template>
        </div>
      </div>
    </div>
    <div class="order-sub">
      Pengiriman yang berhasil dibuat akan otomatis membentuk jurnal umum dan mengurangi stok di gudang asal.
    </div>
  </div>

  {{-- Info --}}
  <div class="card card-bd--form">
    <div class="shipping-form-info">
      <div class="display card-hd-title">Informasi Pengiriman</div>
      <div class="shipping-form-info__sub">
        Field dengan <strong>AUTO</strong> terisi otomatis dari SO &nbsp;·&nbsp;
        <span style="color:var(--accent);">*</span> wajib diisi
      </div>
    </div>
    <div class="order-form-grid-3">
      <x-misc.field label="Customer" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
          <x-misc.avatar :name="$so['customer']" />
          <span style="flex:1; font-weight:500;">{{ $so['customer'] }}</span>
          <span class="auto-tag">AUTO</span>
        </div>
      </x-misc.field>
      <x-misc.field label="No. Pemesanan" :required="true">
        <div class="input mono input--readonly" style="display:flex; align-items:center;">
          <span style="flex:1; font-weight:600;">{{ $so['id'] }}</span>
          <span class="auto-tag">AUTO</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Gudang" :required="true">
        <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">{{ $so['gudang'] }}</span>
          <span class="auto-tag">AUTO</span>
        </div>
      </x-misc.field>
      <x-misc.field label="Nomor Pengiriman">
        <input class="input mono" value="DO-2026-0089" />
      </x-misc.field>
      <x-misc.field label="Tanggal Pengiriman">
        <input type="date" class="input" value="2026-05-09" />
      </x-misc.field>
      <x-misc.field label="Ekspedisi">
        <div class="input" style="display:flex; align-items:center; gap:8px;">
          <x-misc.icon name="truck" :size="14" stroke="var(--ink-4)" />
          <span style="flex:1;">Internal – Truk Box L300</span>
          <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
        </div>
      </x-misc.field>
    </div>
    <x-misc.field label="Notes">
      <textarea class="input" rows="2">Muat dari rak A-3 dan B-1. Konfirmasi ke Pak Tarno sebelum berangkat. Surat jalan rangkap 3.</textarea>
    </x-misc.field>
  </div>

  {{-- Products --}}
  <div class="card" style="overflow:visible;">
    <div class="card-hd">
      <div class="display card-hd-title">Produk dari Sales Order</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:48px;">#</th>
        <th>Produk</th>
        <th style="text-align:right; width:140px;">Qty Pesanan</th>
        <th style="text-align:right; width:160px;">Qty Dikirim</th>
        <th style="width:160px;">Satuan</th>
      </tr></thead>
      <tbody>
        <template x-for="(it, i) in items" :key="i">
          <tr>
            <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
            <td>
              <div style="font-weight:600;" x-text="it.nama"></div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="it.kode"></div>
            </td>
            <td class="num" style="text-align:right; color:var(--ink-4);" x-text="it.qty"></td>
            <td>
              <input class="input num" style="height:32px; text-align:right;"
                :value="fmtNum(it.qtyDikirim)"
                @focus="$event.target.select()"
                @input="fmtInput($event)"
                @blur="it.qtyDikirim = parseNum($event.target.value)" />
            </td>
            <td style="color:var(--ink-3);" x-text="it.satuan"></td>
          </tr>
        </template>
      </tbody>
    </table>

    <div class="order-items-split">
      {{-- Driver Dropdown --}}
      <div class="shipping-driver">
        <div class="label">Driver</div>
        <div class="dropdown-wrap" @click.outside="driverOpen=false">
          <div class="input dropdown-trigger" @click="driverOpen=!driverOpen" style="height:auto; padding:10px 12px;">
            <div class="avatar" style="width:32px;height:32px;flex-shrink:0;background:var(--bg-3);color:var(--ink-2);"
              x-text="initials(driver.nama)"></div>
            <div style="flex:1;">
              <div class="shipping-driver__name" x-text="driver.nama"></div>
              <div class="mono" style="font-size:11px; color:var(--ink-4);"
                x-text="driver.plat + ' · ETA ' + driver.eta"></div>
            </div>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="driverOpen" x-cloak>
            <template x-for="d in driverList" :key="d.id">
              <div class="dropdown-item" @click="driver=d; driverOpen=false">
                <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                  x-text="initials(d.nama)"></div>
                <div style="flex:1;">
                  <div style="font-size:13px;" x-text="d.nama"></div>
                  <div class="mono" style="font-size:11px; color:var(--ink-4);"
                    x-text="d.plat + ' · ETA ' + d.eta"></div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      {{-- Biaya Pengiriman --}}
      <div class="hpp-summary">
        <x-misc.field label="Biaya Pengiriman">
          <input class="input num" style="text-align:right;"
            :value="fmtNum(biayaPengiriman)"
            @focus="$event.target.select()"
            @input="fmtInput($event)"
            @blur="biayaPengiriman = parseNum($event.target.value)" />
        </x-misc.field>
      </div>
    </div>
  </div>

  <div style="display:flex; justify-content:flex-end; gap:10px;">
    <a href="{{ route('penjualan.show', $so['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-ghost"><x-misc.icon name="receipt" :size="14" />Buat Tagihan</button>
    <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Pengiriman</button>
  </div>

</div>
@endsection
