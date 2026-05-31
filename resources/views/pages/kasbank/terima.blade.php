@extends('layouts.app')
@section('content')

<script>
function terimaDanaData() {
  return {
    kontakList: @json($kontak),
    coaList: @json($coa),
    selectedKontak: null,
    kontakOpen: false,
    rows: [{ akun: '', deskripsi: '', pajak: '', total: 0 }],
    pemotongan: [],
    addRow() {
      this.rows.push({ akun: '', deskripsi: '', pajak: '', total: 0 });
    },
    removeRow(i) {
      if (this.rows.length > 1) this.rows.splice(i, 1);
    },
    addPemotongan() {
      this.pemotongan.push({ akun: '', nilai: 0 });
    },
    removePemotongan(i) {
      this.pemotongan.splice(i, 1);
    },
    get subTotal() {
      return this.rows.reduce((s, r) => s + (Number(r.total) || 0), 0);
    },
    get totalPemotongan() {
      return this.pemotongan.reduce((s, p) => s + (Number(p.nilai) || 0), 0);
    },
    get sisaTagihan() { return this.subTotal - this.totalPemotongan; },
    fmtRp(n) { return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
    fmtNum(n) { return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
    fmtInput(e) {
      let el = e.target;
      let pos = el.value.slice(0, el.selectionStart).replace(/[^0-9]/g, '').length;
      let raw = el.value.replace(/[^0-9]/g, '');
      el.value = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
      let i = 0, c = 0;
      while (i < el.value.length && c < pos) { if (/\d/.test(el.value[i])) c++; i++; }
      el.setSelectionRange(i, i);
    },
    parseNum(str) { return Number(String(str).replace(/[^0-9]/g, '')) || 0; },
    initials(name) { return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?'; },
  };
}
</script>

<div x-data="terimaDanaData()" class="order-page">

  <div>
    <a href="{{ route('kasbank.show', $akun['id']) }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
      <x-misc.icon name="chev-left" :size="13" />Kembali
    </a>
    <h1 class="order-title display">Terima Dana</h1>
    <div class="order-sub">{{ $akun['nama'] }} <span class="mono" style="color:var(--ink-4);">({{ $akun['id'] }})</span></div>
  </div>

  {{-- Header Form --}}
  <div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Transaksi</div>
    <div class="order-form-grid-3">

      {{-- Dari --}}
      <x-misc.field label="Dari" :required="true">
        <div class="dropdown-wrap" @click.outside="kontakOpen = false">
          <div class="input dropdown-trigger" @click="kontakOpen = !kontakOpen">
            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
              x-text="initials(selectedKontak ? selectedKontak.nama : '')"></div>
            <span style="flex:1; font-weight:500;" x-text="selectedKontak ? selectedKontak.nama : 'Pilih kontak'"></span>
            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
          </div>
          <div class="dropdown-menu" x-show="kontakOpen" x-cloak>
            <template x-for="k in kontakList" :key="k.id">
              <div class="dropdown-item" @click="selectedKontak = k; kontakOpen = false">
                <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                  x-text="initials(k.nama)"></div>
                <div>
                  <div style="font-weight:500;" x-text="k.nama"></div>
                  <div style="font-size:11px; color:var(--ink-4);" x-text="k.tipe"></div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </x-misc.field>

      {{-- Nomor --}}
      <x-misc.field label="Nomor" :required="true">
        <input class="input mono" value="TERIMA/00001" />
      </x-misc.field>

      {{-- Tanggal Transaksi --}}
      <x-misc.field label="Tanggal Transaksi" :required="true">
        <input type="date" class="input" value="{{ date('Y-m-d') }}" />
      </x-misc.field>

    </div>
  </div>

  {{-- Items --}}
  <div class="card" style="overflow:visible;">
    <div class="card-hd">
      <div class="display card-hd-title">Rincian</div>
      <button class="btn btn-ghost btn-sm" @click="addRow()">
        <x-misc.icon name="plus" :size="13" />Tambah Baris
      </button>
    </div>
    <table class="tbl">
      <thead><tr>
        <th>Akun</th>
        <th>Deskripsi</th>
        <th style="width:180px;">Pajak</th>
        <th style="text-align:right; width:180px;">Total</th>
        <th style="width:40px;"></th>
      </tr></thead>
      <tbody>
        <template x-for="(row, i) in rows" :key="i">
          <tr>
            <td style="padding:15px;">
              <select class="input" style="height:32px;" x-model="row.akun">
                <option value="">Pilih akun</option>
                @foreach($coa as $c)
                  <option value="{{ $c['kode'] }}">{{ $c['kode'] }} - {{ $c['nama'] }}</option>
                @endforeach
              </select>
            </td>
            <td style="padding:6px 10px;">
              <input class="input" style="height:32px; width:100%;" x-model="row.deskripsi" />
            </td>
            <td style="padding:6px 10px;">
              <select class="input" style="height:32px;" x-model="row.pajak">
                <option value="">—</option>
                <optgroup label="PPn">
                  <option value="ppn11">PPn 11%</option>
                  <option value="ppn12">PPn 12%</option>
                </optgroup>
                <optgroup label="PPh">
                  <option value="pph2">PPh 2%</option>
                  <option value="pph15">PPh 15%</option>
                  <option value="pph23">PPh 23%</option>
                </optgroup>
              </select>
            </td>
            <td style="padding:6px 10px;">
              <input class="input num" style="height:32px; text-align:right; width:100%;"
                :value="fmtNum(row.total)"
                @focus="$event.target.select()"
                @input="fmtInput($event); row.total = parseNum($event.target.value)" />
            </td>
            <td style="padding:15px; text-align:center;">
              <button @click="removeRow(i)"
                class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                :disabled="rows.length <= 1"
                :style="rows.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''">
                <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
              </button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div class="order-items-split">
      <div></div>
      <div class="order-summary">
        <div class="display order-summary__title">Ringkasan</div>
        <div class="order-summary__row">
          <span class="order-summary__label">Sub Total</span>
          <span class="num" style="font-weight:500;" x-text="fmtRp(subTotal)">Rp 0</span>
        </div>
        <div class="order-summary__divider"></div>
        <div class="order-summary__total">
          <span class="order-summary__total-label">Total</span>
          <span class="order-summary__total-value display num" x-text="fmtRp(subTotal)">Rp 0</span>
        </div>

        {{-- Pemotongan rows --}}
        <template x-for="(p, i) in pemotongan" :key="i">
          <div style="margin-top:8px; padding:8px 10px; background:var(--bg-2); border-radius:var(--r); border:1px solid var(--line);">
            <div style="display:flex; align-items:center; gap:6px; margin-bottom:6px;">
              <select class="input" style="height:30px; flex:1; font-size:12px;" x-model="p.akun">
                <option value="">Pilih akun</option>
                @foreach($coa as $c)
                  <option value="{{ $c['kode'] }}">{{ $c['kode'] }} - {{ $c['nama'] }}</option>
                @endforeach
              </select>
              <button @click="removePemotongan(i)"
                class="btn btn-ghost btn-icon btn-sm" style="color:var(--bad); border-color:var(--bad-bg); flex-shrink:0; height:30px; width:30px;">
                <x-misc.icon name="trash" :size="13" />
              </button>
            </div>
            <div style="display:flex; align-items:center; gap:6px;">
              <span style="font-size:12px; color:var(--ink-3); flex-shrink:0;">Rp</span>
              <input class="input num" style="height:28px; text-align:right; flex:1; font-size:12px;"
                :value="fmtNum(p.nilai)"
                @focus="$event.target.select()"
                @input="fmtInput($event); p.nilai = parseNum($event.target.value)" />
            </div>
          </div>
        </template>

        <div style="text-align:right; margin-top:6px;">
          <button @click="addPemotongan()" class="btn btn-ghost btn-sm" style="color:var(--accent); font-size:12px;">
            <x-misc.icon name="plus" :size="12" />Pemotongan
          </button>
        </div>

        <div class="order-summary__divider"></div>
        <div class="order-summary__row">
          <span class="order-summary__label">Sisa Tagihan</span>
          <span class="num" style="font-weight:600;" x-text="fmtRp(sisaTagihan)">Rp 0</span>
        </div>
      </div>
    </div>
  </div>

  <div class="order-form-footer">
    <a href="{{ route('kasbank.show', $akun['id']) }}" class="btn btn-ghost">Batal</a>
    <button class="btn btn-primary"><x-misc.icon name="doc" :size="14" />Simpan</button>
  </div>

</div>
@endsection
