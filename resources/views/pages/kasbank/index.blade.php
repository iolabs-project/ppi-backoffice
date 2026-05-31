@extends('layouts.app')
@section('content')
<div x-data="{ modal: null }" class="kasbank-page">

  <div class="order-hd">
    <div>
      <h1 class="order-title display">Kas &amp; Bank</h1>
      <div class="order-sub">{{ count($akunKas) }} rekening aktif · Total saldo</div>
    </div>
    <div class="order-actions">
      <button class="btn btn-primary" x-on:click="modal = 'tambah'">
        <x-misc.icon name="plus" :size="14" />Tambah Rekening
      </button>
    </div>
  </div>

  {{-- Total saldo card --}}
  <div class="card saldo-hero">
    <div>
      <div class="saldo-hero__label">Total Saldo Tersedia</div>
      <div class="saldo-hero__value display num">{{ fmt_rp($totalSaldo) }}</div>
      <div class="saldo-hero__sub">{{ count($akunKas) }} rekening · diperbarui hari ini</div>
    </div>
    <div class="saldo-hero__icon">
      <x-misc.icon name="bank" :size="56" />
    </div>
  </div>

  {{-- Account cards --}}
  <div class="account-grid">
    @foreach($akunKas as $ak)
    @php
      $pct = $totalSaldo > 0 ? round($ak['saldo'] / $totalSaldo * 100) : 0;
      $isKas = str_contains(strtolower($ak['nama']), 'kas');
    @endphp
    <a href="{{ route('kasbank.show', $ak['id']) }}" class="card account-card">
      <div class="account-card__hd">
        <div class="account-card__info">
          <div class="account-card__icon">
            <x-misc.icon :name="$isKas ? 'wallet' : 'bank'" :size="18" stroke="var(--accent)" />
          </div>
          <div>
            <div class="account-card__name">{{ $ak['nama'] }}</div>
            <div class="account-card__bank">{{ $ak['bank'] }}
              @if($ak['norek'])<span class="mono"> · {{ $ak['norek'] }}</span>@endif
            </div>
          </div>
        </div>
        <span class="account-card__pct">{{ $pct }}%</span>
      </div>
      <div>
        <div class="account-card__value display num">{{ fmt_rp($ak['saldo']) }}</div>
        <div class="account-card__bar">
          <div class="account-card__bar-fill" style="width:{{ $pct }}%;"></div>
        </div>
      </div>
      <div class="account-card__ft">
        <span>{{ $ak['transaksi'] ?? 0 }} transaksi bulan ini</span>
        <span class="account-card__link">Lihat detail →</span>
      </div>
    </a>
    @endforeach
  </div>

  {{-- Recent transactions --}}
  <div class="card" style="overflow:hidden;">
    <div class="card-hd">
      <div class="display card-hd-title">Transaksi Terbaru</div>
      <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
    </div>
    <table class="tbl">
      <thead><tr>
        <th>Tanggal</th><th>Keterangan</th><th>Akun</th><th>Ref</th>
        <th style="text-align:right;">Masuk</th><th style="text-align:right;">Keluar</th>
        <th style="text-align:right;">Saldo</th>
      </tr></thead>
      <tbody>
        @foreach($transaksiKas as $tx)
        <tr>
          <td style="color:var(--ink-3); white-space:nowrap;">{{ $tx['tanggal'] }}</td>
          <td style="font-weight:500;">{{ $tx['keterangan'] }}</td>
          <td style="font-size:12px; color:var(--ink-4);">{{ $tx['akun'] }}</td>
          <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $tx['ref'] ?? '—' }}</td>
          <td class="num" style="text-align:right; color:{{ $tx['masuk'] ? 'var(--good)' : 'var(--ink-5)' }}; font-weight:{{ $tx['masuk'] ? 600 : 400 }};">
            {{ $tx['masuk'] ? fmt_rp($tx['masuk']) : '—' }}
          </td>
          <td class="num" style="text-align:right; color:{{ $tx['keluar'] ? 'var(--bad)' : 'var(--ink-5)' }}; font-weight:{{ $tx['keluar'] ? 600 : 400 }};">
            {{ $tx['keluar'] ? fmt_rp($tx['keluar']) : '—' }}
          </td>
          <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($tx['saldo']) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Modal: Tambah Rekening --}}
  <x-misc.modal title="Tambah Akun" show="modal === 'tambah'" close-handler="modal = null">
    <div class="form-body">
      <x-misc.field label="Nama" :required="true">
        <input class="input" placeholder="Nama" />
      </x-misc.field>
      <x-misc.field label="Kode" :required="true">
        <input class="input" placeholder="Kode" />
      </x-misc.field>
      <x-misc.field label="Kategori" :required="true">
        <select class="input">
          <option value="">Pilih kategori</option>
          <option value="kas_bank">Kas &amp; Bank</option>
          <option value="kartu_kredit">Kartu Kredit</option>
        </select>
      </x-misc.field>
      <x-misc.field label="Sub Akun dari">
        <select class="input">
          <option value="">Pilih akun</option>
          @foreach($akunKas as $ak)
            <option value="{{ $ak['id'] }}">{{ $ak['nama'] }}</option>
          @endforeach
        </select>
      </x-misc.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Rekening</button>
    </x-slot:footer>
  </x-misc.modal>

</div>
@endsection
