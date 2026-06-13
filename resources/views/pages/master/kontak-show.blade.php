@extends('layouts.app')
@section('content')
@php
  $isCustomer = strtolower($kontak['tipe']) === 'customer';
  $isVendor   = strtolower($kontak['tipe']) === 'vendor';
  $piutang    = $kontak['piutang'] ?? 0;
  $hutang     = $kontak['hutang']  ?? 0;
  $omzet      = array_sum(array_column($transaksi, 'total'));

  $terkiniForJs = array_map(fn($t) => [
    'tanggal' => $t['tanggal'],
    'nomor'   => $t['nomor'],
    'tipe'    => $t['tipe'],
    'status'  => $t['status'],
    'total'   => fmt_rp($t['total']),
  ], $transaksi);

  $words    = array_filter(explode(' ', $kontak['nama']));
  $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice($words, 0, 2)));
  $h = 0;
  foreach (str_split($kontak['nama']) as $c) $h = (($h * 31) + ord($c)) & 0xFFFFFFFF;
  $hue = $h % 360;
  $avatarBg = "oklch(0.92 0.04 {$hue})";
  $avatarFg = "oklch(0.45 0.10 {$hue})";

  $statusLabel = [
    'draft'          => ['label' => 'Draft',          'bg' => 'var(--bg-2)',    'fg' => 'var(--ink-3)'],
    'disetujui'      => ['label' => 'Disetujui',      'bg' => 'oklch(0.92 0.06 220)', 'fg' => 'oklch(0.45 0.14 220)'],
    'selesai'        => ['label' => 'Selesai',        'bg' => 'var(--ok-bg)',   'fg' => 'var(--ok)'],
    'belum-dibayar'  => ['label' => 'Belum Dibayar',  'bg' => 'var(--bad-bg)', 'fg' => 'var(--bad)'],
    'lunas'          => ['label' => 'Lunas',          'bg' => 'var(--ok-bg)',   'fg' => 'var(--ok)'],
  ];
@endphp
<script>
function kontakShowData() {
  return {
    transaksiAll: @json($terkiniForJs),
    transaksiPage: 1,
    transaksiPerPage: 8,
    get transaksiPaged() { return this.transaksiAll.slice((this.transaksiPage-1)*this.transaksiPerPage, this.transaksiPage*this.transaksiPerPage); },
    get transaksiTotal() { return Math.ceil(this.transaksiAll.length / this.transaksiPerPage); },
    modal: null,
    editData: {
      nama: @json($kontak['nama']),
      tipe: @json($kontak['tipe']),
      email: @json($kontak['email'] ?? ''),
      telepon: @json($kontak['telepon'] ?? ''),
      kota: @json($kontak['kota'] ?? ''),
      alamat: '',
    },
  };
}
</script>
<div class="order-page" x-data="kontakShowData()">

  {{-- Header --}}
  <div class="order-hd order-hd--start">
    <div>
      <a href="{{ route('master.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
        <x-misc.icon name="chev-left" :size="13" /> Kembali ke Master Data
      </a>
      <div style="display:flex; align-items:center; gap:14px;">
        <div class="avatar" style="width:52px; height:52px; font-size:16px; border-radius:14px;
             background:{{ $avatarBg }}; color:{{ $avatarFg }};">{{ $initials }}</div>
        <div>
          <div class="order-title-row">
            <h1 class="order-title display">{{ $kontak['nama'] }}</h1>
            <span class="chip {{ $isVendor ? 'chip-accent' : '' }}">{{ $kontak['tipe'] }}</span>
            <span class="chip mono" style="font-size:11px;">{{ $kontak['id'] }}</span>
          </div>
          <div class="order-sub">{{ $kontak['kota'] ?? '—' }} · Diperbarui hari ini</div>
        </div>
      </div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost" x-on:click="modal = 'edit'">
        <x-misc.icon name="edit" :size="14" /> Edit Kontak
      </button>
    </div>
  </div>

  {{-- Stat cards --}}
  @php $statCols = strtolower($kontak['tipe']) === 'keduanya' ? 4 : 3; @endphp
  <div class="produk-stat-grid" style="grid-template-columns: repeat({{ $statCols }}, 1fr);">
    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 45); color:oklch(0.45 0.14 45);">
        {{ fmt_rp_short($omzet) }}
      </div>
      <div class="produk-stat__label">Total Omzet</div>
      <div class="produk-stat__unit">semua waktu</div>
    </div>

    @if($isCustomer || strtolower($kontak['tipe']) === 'keduanya')
    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:{{ $piutang > 0 ? 'var(--bad-bg)' : 'var(--bg-2)' }}; color:{{ $piutang > 0 ? 'var(--bad)' : 'var(--ink-3)' }};">
        {{ fmt_rp_short($piutang) }}
      </div>
      <div class="produk-stat__label">Piutang</div>
      <div class="produk-stat__unit">outstanding</div>
    </div>
    @endif

    @if($isVendor || strtolower($kontak['tipe']) === 'keduanya')
    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:{{ $hutang > 0 ? 'oklch(0.92 0.06 220)' : 'var(--bg-2)' }}; color:{{ $hutang > 0 ? 'oklch(0.45 0.14 220)' : 'var(--ink-3)' }};">
        {{ fmt_rp_short($hutang) }}
      </div>
      <div class="produk-stat__label">Hutang</div>
      <div class="produk-stat__unit">outstanding</div>
    </div>
    @endif

    <div class="card produk-stat">
      <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">
        {{ count($transaksi) }}
      </div>
      <div class="produk-stat__label">Transaksi</div>
      <div class="produk-stat__unit">tercatat</div>
    </div>
  </div>

  {{-- Body --}}
  <div class="produk-body">

    {{-- Left: transaksi + chart --}}
    <div style="display:grid; gap:16px;">

      {{-- Transaksi table --}}
      <div class="card" style="overflow:hidden;">
        <div class="card-hd">
          <div class="display card-hd-title">Riwayat Transaksi</div>
          <div style="font-size:12px; color:var(--ink-4);" x-text="transaksiAll.length + ' dokumen'"></div>
        </div>
        <table class="tbl">
          <thead><tr>
            <th>Tanggal</th><th>Nomor</th><th>Tipe</th><th>Status</th>
            <th style="text-align:right;">Total</th>
          </tr></thead>
          <tbody>
            <template x-for="t in transaksiPaged" :key="t.nomor">
              <tr class="row-tap">
                <td style="font-size:13px; color:var(--ink-3);" x-text="t.tanggal"></td>
                <td><span class="mono" style="font-size:12px; font-weight:600; color:var(--accent);" x-text="t.nomor"></span></td>
                <td style="font-size:13px;" x-text="t.tipe"></td>
                <td>
                  @foreach($statusLabel as $key => $s)
                    <span x-show="t.status === '{{ $key }}'" class="chip"
                          style="background:{{ $s['bg'] }}; color:{{ $s['fg'] }};">{{ $s['label'] }}</span>
                  @endforeach
                </td>
                <td class="num" style="text-align:right; font-size:13px; font-weight:600;" x-text="t.total"></td>
              </tr>
            </template>
          </tbody>
        </table>
        <div class="table-pagination">
          <span class="pagination-info" x-text="'Menampilkan ' + ((transaksiPage-1)*transaksiPerPage+1) + '–' + Math.min(transaksiPage*transaksiPerPage, transaksiAll.length) + ' dari ' + transaksiAll.length + ' dokumen'"></span>
          <div class="pagination-controls">
            <span class="pagination-page-info">Hal. <strong x-text="transaksiPage"></strong> / <strong x-text="transaksiTotal"></strong></span>
            <button class="btn btn-ghost btn-sm" :disabled="transaksiPage <= 1" x-on:click="transaksiPage--">
              <x-misc.icon name="chev-left" :size="14" /> Prev
            </button>
            <button class="btn btn-ghost btn-sm" :disabled="transaksiPage >= transaksiTotal" x-on:click="transaksiPage++">
              Next <x-misc.icon name="chev-right" :size="14" />
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Right: info sidebar --}}
    <div class="card produk-sidebar">

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Informasi Kontak</div>
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Tipe</span>
          <span class="chip {{ $isVendor ? 'chip-accent' : '' }}">{{ $kontak['tipe'] }}</span>
        </div>
        @if(!empty($kontak['kota']))
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Kota</span>
          <span class="produk-sidebar__val">{{ $kontak['kota'] }}</span>
        </div>
        @endif
        @if(!empty($kontak['email']))
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Email</span>
          <span class="produk-sidebar__val" style="font-size:12px; color:var(--accent);">{{ $kontak['email'] }}</span>
        </div>
        @endif
        @if(!empty($kontak['telepon']))
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Telepon</span>
          <span class="produk-sidebar__val" style="font-size:12.5px;">{{ $kontak['telepon'] }}</span>
        </div>
        @endif
      </div>

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Akun</div>
        @if($isCustomer || strtolower($kontak['tipe']) === 'keduanya')
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Piutang</span>
          <span class="produk-sidebar__val" style="color:var(--accent); font-size:12px;">1-1200 Piutang Usaha</span>
        </div>
        @endif
        @if($isVendor || strtolower($kontak['tipe']) === 'keduanya')
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Hutang</span>
          <span class="produk-sidebar__val" style="color:var(--accent); font-size:12px;">2-1100 Hutang Usaha</span>
        </div>
        @endif
      </div>

      <div class="produk-sidebar__section">
        <div class="produk-sidebar__heading">Saldo</div>
        @if($piutang > 0)
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Piutang</span>
          <span class="num produk-sidebar__val" style="color:var(--bad); font-weight:700;">{{ fmt_rp($piutang) }}</span>
        </div>
        @endif
        @if($hutang > 0)
        <div class="produk-sidebar__row">
          <span class="produk-sidebar__key">Hutang</span>
          <span class="num produk-sidebar__val" style="color:oklch(0.45 0.14 220); font-weight:700;">{{ fmt_rp($hutang) }}</span>
        </div>
        @endif
        @if($piutang === 0 && $hutang === 0)
        <div style="font-size:12.5px; color:var(--ink-4); padding:4px 0;">Tidak ada saldo outstanding</div>
        @endif
      </div>


    </div>
  </div>

  {{-- Modal: Edit Kontak --}}
  <x-misc.modal title="Edit Kontak" show="modal === 'edit'" close-handler="modal = null">
    <div class="form-body">
      <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
        <div class="avatar" style="width:44px; height:44px; font-size:14px; border-radius:12px;
             background:{{ $avatarBg }}; color:{{ $avatarFg }};">{{ $initials }}</div>
        <div>
          <div style="font-weight:700; font-size:14px;" x-text="editData.nama"></div>
          <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $kontak['id'] }}</div>
        </div>
      </div>
      <x-misc.field label="Nama" :required="true">
        <input class="input" x-model="editData.nama" />
      </x-misc.field>
      <div class="form-grid-2">
        <x-misc.field label="Tipe" :required="true">
          <select class="input" x-model="editData.tipe">
            <option value="Customer">Customer</option>
            <option value="Vendor">Vendor</option>
            <option value="Keduanya">Keduanya</option>
          </select>
        </x-misc.field>
        <x-misc.field label="Kota">
          <input class="input" x-model="editData.kota" placeholder="Jakarta, Surabaya..." />
        </x-misc.field>
      </div>
      <div class="form-grid-2">
        <x-misc.field label="Email">
          <input class="input" type="email" x-model="editData.email" />
        </x-misc.field>
        <x-misc.field label="Telepon">
          <input class="input" x-model="editData.telepon" />
        </x-misc.field>
      </div>
      <x-misc.field label="Alamat">
        <textarea class="input" rows="2" x-model="editData.alamat" placeholder="Alamat lengkap..."></textarea>
      </x-misc.field>
    </div>
    <x-slot:footer>
      <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
      <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
    </x-slot:footer>
  </x-misc.modal>

</div>
@endsection
