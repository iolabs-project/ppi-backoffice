@extends('layouts.app')
@section('content')
@php
  $masukTotal  = array_sum(array_column(array_filter($transaksiKas, fn($t) => $t['masuk'] > 0), 'masuk'));
  $keluarTotal = array_sum(array_column(array_filter($transaksiKas, fn($t) => $t['keluar'] > 0), 'keluar'));
@endphp

<script>
function kasBankShowData() {
  return {
    modal: null,
    akunKas: @json($akunKas),
    totalTransfer: 0,
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
  };
}
</script>

<div x-data="kasBankShowData()" class="kasbank-page">

  <div class="order-hd order-hd--start">
    <div>
      <a href="{{ route('kasbank.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-misc.icon name="chev-left" :size="13" />Kembali ke Kas &amp; Bank
      </a>
      <h1 class="order-title display">{{ $akun['nama'] }}</h1>
      <div class="order-sub">
        {{ $akun['bank'] }}@if($akun['norek']) · <span class="mono">{{ $akun['norek'] }}</span>@endif
      </div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost" x-on:click="modal = 'transfer'">
        <x-misc.icon name="swap" :size="14" />Transfer Dana
      </button>
      <a href="{{ route('kasbank.kirim', $akun['id']) }}" class="btn btn-ghost">
        <x-misc.icon name="send" :size="14" />Kirim Dana
      </a>
      <a href="{{ route('kasbank.terima', $akun['id']) }}" class="btn btn-ghost">
        <x-misc.icon name="inbox" :size="14" />Terima Dana
      </a>
      <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
    </div>
  </div>

  {{-- Saldo + stat cards --}}
  <div class="stat-cards">
    <div class="card stat-card stat-card--dark">
      <div class="stat-card__label">Saldo Saat Ini</div>
      <div class="stat-card__value display num">{{ fmt_rp($akun['saldo']) }}</div>
    </div>
    <div class="card stat-card stat-card--good">
      <div class="label stat-card__label">↑ Total Masuk</div>
      <div class="stat-card__value display num">{{ fmt_rp($masukTotal) }}</div>
      <div class="stat-card__sub">Bulan ini</div>
    </div>
    <div class="card stat-card stat-card--bad">
      <div class="label stat-card__label">↓ Total Keluar</div>
      <div class="stat-card__value display num">{{ fmt_rp($keluarTotal) }}</div>
      <div class="stat-card__sub">Bulan ini</div>
    </div>
  </div>

  {{-- Transaction history --}}
  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Riwayat Transaksi</div>
      <div style="font-size:12px; color:var(--ink-4);">{{ count($transaksiKas) }} transaksi</div>
    </div>
    <table class="tbl">
      <thead><tr>
        <th style="width:130px;">Tanggal</th>
        <th>Keterangan</th>
        <th>Ref</th>
        <th style="text-align:right; width:160px;">Masuk</th>
        <th style="text-align:right; width:160px;">Keluar</th>
        <th style="text-align:right; width:180px;">Saldo</th>
      </tr></thead>
      <tbody>
        @foreach($transaksiKas as $tx)
        <tr>
          <td style="color:var(--ink-3); white-space:nowrap;">{{ $tx['tanggal'] }}</td>
          <td>
            <div style="font-weight:500;">{{ $tx['keterangan'] }}</div>
            @if(!empty($tx['akun']))<div style="font-size:11.5px; color:var(--ink-4);">{{ $tx['akun'] }}</div>@endif
          </td>
          <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $tx['ref'] ?? '—' }}</td>
          <td class="num" style="text-align:right; color:var(--good); font-weight:{{ $tx['masuk'] ? 600 : 400 }};">
            {{ $tx['masuk'] ? fmt_rp($tx['masuk']) : '' }}
          </td>
          <td class="num" style="text-align:right; color:var(--bad); font-weight:{{ $tx['keluar'] ? 600 : 400 }};">
            {{ $tx['keluar'] ? fmt_rp($tx['keluar']) : '' }}
          </td>
          <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($tx['saldo']) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Modal: Transfer Dana --}}
  <x-misc.modal title="Transfer Dana" show="modal === 'transfer'" close-handler="modal = null" :width="520">
    <div class="form-body">
      <x-misc.field label="Dari" :required="true">
        <select class="input">
          @foreach($akunKas as $ak)
            <option value="{{ $ak['id'] }}" {{ $ak['id'] === $akun['id'] ? 'selected' : '' }}>
              {{ $ak['id'] }} — {{ $ak['nama'] }}
            </option>
          @endforeach
        </select>
      </x-misc.field>
      <x-misc.field label="Ke" :required="true">
        <select class="input">
          @foreach($akunKas as $ak)
            <option value="{{ $ak['id'] }}">{{ $ak['id'] }} — {{ $ak['nama'] }}</option>
          @endforeach
        </select>
      </x-misc.field>
      <div class="form-grid-3">
        <x-misc.field label="Tanggal Transaksi" :required="true">
          <input type="date" class="input" value="{{ date('Y-m-d') }}" />
        </x-misc.field>
        <x-misc.field label="Total" :required="true">
          <input class="input num" style="text-align:right;"
            :value="fmtNum(totalTransfer)"
            @focus="$event.target.select()"
            @input="fmtInput($event); totalTransfer = parseNum($event.target.value)" />
        </x-misc.field>
        <x-misc.field label="Nomor">
          <input class="input mono" value="TR/00001" />
        </x-misc.field>
      </div>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">
        <x-misc.icon name="x" :size="14" />Batal
      </button>
      <button class="btn btn-primary">
        <x-misc.icon name="send" :size="14" />Transfer
      </button>
    </x-slot:footer>
  </x-misc.modal>

</div>
@endsection
