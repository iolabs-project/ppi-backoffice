@extends('layouts.erp')
@section('content')
@php
  $tabs = [
    ['id'=>'neraca',     'label'=>'Neraca'],
    ['id'=>'aruskas',    'label'=>'Arus Kas'],
    ['id'=>'labarugi',   'label'=>'Laba Rugi'],
    ['id'=>'eksekutif',  'label'=>'Eksekutif'],
    ['id'=>'utang',      'label'=>'Utang &amp; Piutang'],
    ['id'=>'jurnal',     'label'=>'Jurnal Umum'],
  ];

  $aset       = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '1'));
  $liabilitas = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '2'));
  $ekuitas    = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '3'));
  $pendapatan = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '4'));
  $beban      = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '5') || str_starts_with($a['kode'], '6'));

  $totalAset       = array_sum(array_column(array_values($aset), 'saldo'));
  $totalLiab       = array_sum(array_column(array_values($liabilitas), 'saldo'));
  $totalEkuitas    = array_sum(array_column(array_values($ekuitas), 'saldo'));
  $totalPendapatan = array_sum(array_column(array_values($pendapatan), 'saldo'));
  $totalBeban      = array_sum(array_column(array_values($beban), 'saldo'));
  $labaRugi        = $totalPendapatan - $totalBeban;

  $months     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  $monthlyIn  = [820,950,1100,880,1240,1050,960,1300,1150,1080,1420,1600];
  $monthlyOut = [720,830,980,800,1100,920,840,1180,1020,960,1280,1450];
@endphp
<div x-data="{ tab: 'neraca' }" class="laporan-page">

  <div class="laporan-hd">
    <div>
      <h1 class="order-title display">Laporan Keuangan</h1>
      <div class="order-sub">Periode Januari – Mei 2026</div>
    </div>
    <div class="order-actions">
      <button class="btn btn-ghost"><x-erp.icon name="print" :size="14" />Cetak</button>
      <button class="btn btn-ghost"><x-erp.icon name="download" :size="14" />Ekspor</button>
    </div>
  </div>

  {{-- Tab bar --}}
  <div class="utab">
    @foreach($tabs as $t)
    <button class="utab-item" x-on:click="tab = '{{ $t['id'] }}'" :class="tab === '{{ $t['id'] }}' ? 'utab-active' : ''">{!! $t['label'] !!}</button>
    @endforeach
  </div>

  {{-- =================== NERACA =================== --}}
  <div x-show="tab === 'neraca'" x-cloak>
    <div class="neraca-grid">
      {{-- Aset --}}
      <div class="card" style="overflow:hidden;">
        <div class="neraca-card-hd">
          <div class="display" style="font-weight:700; font-size:14px;">Aset</div>
          <div class="num" style="font-weight:700; color:var(--accent);">{{ fmt_rp($totalAset) }}</div>
        </div>
        <table class="tbl">
          <tbody>
            @foreach($aset as $a)
            <tr>
              <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;">{{ $a['kode'] }}</td>
              <td style="font-size:13px;">{{ $a['nama'] }}</td>
              <td class="num" style="text-align:right; font-weight:600; font-size:13px;">{{ fmt_rp($a['saldo']) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Liabilitas + Ekuitas --}}
      <div class="neraca-side">
        <div class="card" style="overflow:hidden;">
          <div class="neraca-card-hd">
            <div class="display" style="font-weight:700; font-size:14px;">Liabilitas</div>
            <div class="num" style="font-weight:700; color:var(--bad);">{{ fmt_rp($totalLiab) }}</div>
          </div>
          <table class="tbl">
            <tbody>
              @foreach($liabilitas as $a)
              <tr>
                <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;">{{ $a['kode'] }}</td>
                <td style="font-size:13px;">{{ $a['nama'] }}</td>
                <td class="num" style="text-align:right; font-weight:600; font-size:13px;">{{ fmt_rp($a['saldo']) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="card" style="overflow:hidden;">
          <div class="neraca-card-hd">
            <div class="display" style="font-weight:700; font-size:14px;">Ekuitas</div>
            <div class="num" style="font-weight:700; color:var(--good);">{{ fmt_rp($totalEkuitas) }}</div>
          </div>
          <table class="tbl">
            <tbody>
              @foreach($ekuitas as $a)
              <tr>
                <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;">{{ $a['kode'] }}</td>
                <td style="font-size:13px;">{{ $a['nama'] }}</td>
                <td class="num" style="text-align:right; font-weight:600; font-size:13px;">{{ fmt_rp($a['saldo']) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="card neraca-total-row">
          <span style="font-size:13px; font-weight:600;">Total Liabilitas + Ekuitas</span>
          <span class="num" style="font-size:16px; font-weight:700; color:var(--accent);">{{ fmt_rp($totalLiab + $totalEkuitas) }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- =================== ARUS KAS =================== --}}
  <div x-show="tab === 'aruskas'" x-cloak>
    <div class="form-body">
      {{-- Bar chart --}}
      <div class="aruskas-chart card">
        <div class="display report-chart-title">Arus Kas Bulanan 2026</div>
        @php
          $maxVal = max(array_merge($monthlyIn, $monthlyOut));
          $chartH = 120;
        @endphp
        <div class="aruskas-bars" style="height:{{ $chartH }}px;">
          @foreach($months as $mi => $m)
          @php
            $inH  = round($monthlyIn[$mi]  / $maxVal * $chartH);
            $outH = round($monthlyOut[$mi] / $maxVal * $chartH);
          @endphp
          <div class="aruskas-bar-group" style="height:{{ $chartH }}px;">
            <div class="aruskas-bar-pair">
              <div class="aruskas-bar-in" style="height:{{ $inH }}px;"></div>
              <div class="aruskas-bar-out" style="height:{{ $outH }}px;"></div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="aruskas-labels">
          @foreach($months as $m)
          <div class="aruskas-month">{{ $m }}</div>
          @endforeach
        </div>
        <div class="aruskas-legend">
          <div class="aruskas-legend-item">
            <div class="aruskas-legend-dot" style="background:var(--good);"></div>Kas Masuk
          </div>
          <div class="aruskas-legend-item">
            <div class="aruskas-legend-dot" style="background:var(--bad);"></div>Kas Keluar
          </div>
        </div>
      </div>

      {{-- Summary --}}
      <div class="aruskas-summary">
        @php
          $totalIn  = array_sum($monthlyIn)  * 1_000_000;
          $totalOut = array_sum($monthlyOut) * 1_000_000;
          $netCash  = $totalIn - $totalOut;
        @endphp
        @foreach([
          ['Kas Masuk YTD', $totalIn,  'var(--good)'],
          ['Kas Keluar YTD', $totalOut, 'var(--bad)'],
          ['Net Kas', $netCash, $netCash >= 0 ? 'var(--good)' : 'var(--bad)'],
        ] as [$lbl, $val, $clr])
        <div class="card stat-card">
          <div class="label" style="margin-bottom:8px; color:{{ $clr }};">{{ $lbl }}</div>
          <div class="display num" style="font-size:22px; font-weight:700; color:{{ $clr }};">{{ fmt_rp(abs($val)) }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- =================== LABA RUGI =================== --}}
  <div x-show="tab === 'labarugi'" x-cloak>
    <div class="labarugi-grid">
      <div class="labarugi-tables">
        <div class="card" style="overflow:hidden;">
          <div class="neraca-card-hd">
            <div class="display" style="font-weight:700; font-size:14px;">Pendapatan</div>
          </div>
          <table class="tbl">
            <tbody>
              @foreach($pendapatan as $a)
              <tr>
                <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;">{{ $a['kode'] }}</td>
                <td style="font-size:13px;">{{ $a['nama'] }}</td>
                <td class="num" style="text-align:right; font-weight:600; font-size:13px; color:var(--good);">{{ fmt_rp($a['saldo']) }}</td>
              </tr>
              @endforeach
              <tr style="background:var(--bg-2); font-weight:700;">
                <td colspan="2" style="font-size:13px; padding-left:16px;">Total Pendapatan</td>
                <td class="num" style="text-align:right; color:var(--good);">{{ fmt_rp($totalPendapatan) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card" style="overflow:hidden;">
          <div class="neraca-card-hd">
            <div class="display" style="font-weight:700; font-size:14px;">Beban</div>
          </div>
          <table class="tbl">
            <tbody>
              @foreach($beban as $a)
              <tr>
                <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;">{{ $a['kode'] }}</td>
                <td style="font-size:13px;">{{ $a['nama'] }}</td>
                <td class="num" style="text-align:right; font-weight:600; font-size:13px; color:var(--bad);">{{ fmt_rp($a['saldo']) }}</td>
              </tr>
              @endforeach
              <tr style="background:var(--bg-2); font-weight:700;">
                <td colspan="2" style="font-size:13px; padding-left:16px;">Total Beban</td>
                <td class="num" style="text-align:right; color:var(--bad);">{{ fmt_rp($totalBeban) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="labarugi-summary">
        <div class="labarugi-summary-card card">
          <div class="label" style="margin-bottom:16px;">Ringkasan</div>
          @foreach([
            ['Pendapatan', $totalPendapatan, false],
            ['Beban', -$totalBeban, false],
            ['Laba Bersih', $labaRugi, true],
          ] as [$lbl, $val, $bold])
          <div class="labarugi-summary-row" style="font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $bold ? 'border-top:1px solid var(--line); margin-top:6px; padding-top:14px;' : '' }}">
            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
            <span class="num" style="color:{{ $val >= 0 ? 'var(--good)' : 'var(--bad)' }};">
              {{ $val < 0 ? '–' : '' }}{{ fmt_rp(abs($val)) }}
            </span>
          </div>
          @endforeach
        </div>
        <div class="card labarugi-summary-card" style="background:{{ $labaRugi >= 0 ? 'var(--good-bg, #f0faf0)' : 'var(--bad-bg, #fff0f0)' }};">
          <div class="label" style="margin-bottom:6px;">Margin Bersih</div>
          @php $margin = $totalPendapatan > 0 ? round($labaRugi / $totalPendapatan * 100, 1) : 0; @endphp
          <div class="display num" style="font-size:32px; font-weight:700; color:{{ $labaRugi >= 0 ? 'var(--good)' : 'var(--bad)' }};">{{ $margin }}%</div>
        </div>
      </div>
    </div>
  </div>

  {{-- =================== EKSEKUTIF =================== --}}
  <div x-show="tab === 'eksekutif'" x-cloak>
    <div class="eksekutif-kpi">
      @foreach([
        ['Total Aset',    $totalAset,  'var(--accent)'],
        ['Laba Bersih',   $labaRugi,   $labaRugi >= 0 ? 'var(--good)' : 'var(--bad)'],
        ['Total Liabilitas', $totalLiab, 'var(--bad)'],
      ] as [$lbl, $val, $clr])
      <div class="card" style="padding:22px;">
        <div class="label" style="margin-bottom:8px;">{{ $lbl }}</div>
        <div class="display num" style="font-size:24px; font-weight:700; color:{{ $clr }};">{{ fmt_rp($val) }}</div>
      </div>
      @endforeach
    </div>
    <div class="eksekutif-chart card">
      <div class="display report-chart-title">Kinerja Bulanan (Pendapatan vs Beban)</div>
      @php
        $months5 = ['Jan','Feb','Mar','Apr','Mei'];
        $rev5 = [8_200_000_000, 9_500_000_000, 11_000_000_000, 8_800_000_000, 12_400_000_000];
        $exp5 = [7_200_000_000, 8_300_000_000, 9_800_000_000, 8_000_000_000, 11_000_000_000];
        $maxE = max(array_merge($rev5, $exp5));
        $hE   = 100;
      @endphp
      <div class="eksekutif-bars" style="height:{{ $hE }}px;">
        @foreach($months5 as $mi => $m)
        @php
          $rH = round($rev5[$mi] / $maxE * $hE);
          $eH = round($exp5[$mi] / $maxE * $hE);
        @endphp
        <div class="eksekutif-bar-group">
          <div class="eksekutif-bar-pair">
            <div class="eksekutif-bar-rev" style="height:{{ $rH }}px;"></div>
            <div class="eksekutif-bar-exp" style="height:{{ $eH }}px;"></div>
          </div>
        </div>
        @endforeach
      </div>
      <div class="eksekutif-labels">
        @foreach($months5 as $m)
        <div class="eksekutif-month">{{ $m }}</div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- =================== UTANG & PIUTANG =================== --}}
  <div x-show="tab === 'utang'" x-cloak>
    @php
      $piutang = [
        ['nama'=>'PT Maju Bersama','ref'=>'SO-2026-0087','jatuhTempo'=>'20 Mei 2026','jumlah'=>48_500_000,'umur'=>8],
        ['nama'=>'CV Sumber Rezeki','ref'=>'SO-2026-0081','jatuhTempo'=>'15 Mei 2026','jumlah'=>29_200_000,'umur'=>13],
        ['nama'=>'UD Karya Agung','ref'=>'SO-2026-0075','jatuhTempo'=>'10 Mei 2026','jumlah'=>17_800_000,'umur'=>18],
        ['nama'=>'PT Berkah Niaga','ref'=>'SO-2026-0068','jatuhTempo'=>'30 Apr 2026','jumlah'=>62_300_000,'umur'=>28],
      ];
      $utang = [
        ['nama'=>'PT Bogasari Flour Mills','ref'=>'INV-BFM-23104','jatuhTempo'=>'26 Mei 2026','jumlah'=>98_400_000,'umur'=>-14],
        ['nama'=>'CV Agro Sejahtera','ref'=>'INV-AGR-00891','jatuhTempo'=>'18 Mei 2026','jumlah'=>24_800_000,'umur'=>-6],
      ];
    @endphp
    <div class="utang-grid">
      <div class="card" style="overflow:hidden;">
        <div class="utang-card-hd">
          <div class="display" style="font-weight:700; font-size:14px;">Piutang Dagang</div>
          <span style="font-size:12px; color:var(--ink-4);">{{ count($piutang) }} tagihan</span>
        </div>
        <table class="tbl">
          <thead><tr>
            <th>Klien</th><th>Ref</th><th>Jatuh Tempo</th><th style="text-align:right;">Jumlah</th>
          </tr></thead>
          <tbody>
            @foreach($piutang as $p)
            <tr>
              <td style="font-weight:500; font-size:13px;">{{ $p['nama'] }}</td>
              <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $p['ref'] }}</td>
              <td style="font-size:12.5px; color:{{ $p['umur'] > 14 ? 'var(--bad)' : 'var(--ink-3)' }};">{{ $p['jatuhTempo'] }}</td>
              <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($p['jumlah']) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="utang-card-ft">
          <span style="font-size:13px; font-weight:600;">Total Piutang</span>
          <span class="num" style="font-weight:700; color:var(--good);">{{ fmt_rp(array_sum(array_column($piutang, 'jumlah'))) }}</span>
        </div>
      </div>

      <div class="card" style="overflow:hidden;">
        <div class="utang-card-hd">
          <div class="display" style="font-weight:700; font-size:14px;">Utang Dagang</div>
          <span style="font-size:12px; color:var(--ink-4);">{{ count($utang) }} tagihan</span>
        </div>
        <table class="tbl">
          <thead><tr>
            <th>Vendor</th><th>Ref</th><th>Jatuh Tempo</th><th style="text-align:right;">Jumlah</th>
          </tr></thead>
          <tbody>
            @foreach($utang as $u)
            <tr>
              <td style="font-weight:500; font-size:13px;">{{ $u['nama'] }}</td>
              <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $u['ref'] }}</td>
              <td style="font-size:12.5px; color:var(--ink-3);">{{ $u['jatuhTempo'] }}</td>
              <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($u['jumlah']) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="utang-card-ft">
          <span style="font-size:13px; font-weight:600;">Total Utang</span>
          <span class="num" style="font-weight:700; color:var(--bad);">{{ fmt_rp(array_sum(array_column($utang, 'jumlah'))) }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- =================== JURNAL UMUM =================== --}}
  <div x-show="tab === 'jurnal'" x-cloak>
    <div class="card" style="overflow:hidden;">
      <div class="card-hd">
        <div class="display card-hd-title">Jurnal Umum</div>
        <button class="btn btn-ghost btn-sm"><x-erp.icon name="download" :size="13" />Ekspor</button>
      </div>
      <table class="tbl">
        <thead><tr>
          <th>Tanggal</th><th>No. Jurnal</th><th>Keterangan</th><th>Akun</th>
          <th style="text-align:right;">Debit</th><th style="text-align:right;">Kredit</th>
        </tr></thead>
        <tbody>
          @foreach($jurnal as $j)
          @php $first = true; @endphp
          @foreach($j['entri'] as $e)
          <tr style="{{ $first ? 'border-top:2px solid var(--line-2);' : '' }}">
            <td style="color:var(--ink-3); white-space:nowrap; font-size:12.5px;">{{ $first ? $j['tanggal'] : '' }}</td>
            <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $first ? $j['noJurnal'] : '' }}</td>
            <td style="font-size:12.5px; color:var(--ink-3);">{{ $first ? $j['keterangan'] : '' }}</td>
            <td style="font-size:13px; {{ $e['posisi'] === 'kredit' ? 'padding-left:28px; color:var(--ink-3);' : 'font-weight:500;' }}">{{ $e['akun'] }}</td>
            <td class="num" style="text-align:right; font-size:13px;">{{ $e['posisi'] === 'debit' ? fmt_rp($e['jumlah']) : '' }}</td>
            <td class="num" style="text-align:right; font-size:13px;">{{ $e['posisi'] === 'kredit' ? fmt_rp($e['jumlah']) : '' }}</td>
          </tr>
          @php $first = false; @endphp
          @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
