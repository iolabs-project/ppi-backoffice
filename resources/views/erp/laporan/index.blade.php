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

  // Simplified CoA groups for Neraca
  $aset     = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '1'));
  $liabilitas = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '2'));
  $ekuitas  = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '3'));
  $pendapatan = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '4'));
  $beban    = array_filter($chartOfAccounts, fn($a) => str_starts_with($a['kode'], '5') || str_starts_with($a['kode'], '6'));

  $totalAset   = array_sum(array_column(array_values($aset), 'saldo'));
  $totalLiab   = array_sum(array_column(array_values($liabilitas), 'saldo'));
  $totalEkuitas = array_sum(array_column(array_values($ekuitas), 'saldo'));
  $totalPendapatan = array_sum(array_column(array_values($pendapatan), 'saldo'));
  $totalBeban  = array_sum(array_column(array_values($beban), 'saldo'));
  $labaRugi    = $totalPendapatan - $totalBeban;

  // Monthly data for Arus Kas chart
  $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  $monthlyIn  = [820,950,1100,880,1240,1050,960,1300,1150,1080,1420,1600];
  $monthlyOut = [720,830,980,800,1100,920,840,1180,1020,960,1280,1450];
@endphp
<div x-data="{ tab: 'neraca' }" style="padding:24px 28px 60px; display:grid; gap:16px;">

  <div style="display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h1 class="display" style="margin:0; font-size:26px; font-weight:700; letter-spacing:-0.02em;">Laporan Keuangan</h1>
      <div style="font-size:13px; color:var(--ink-4); margin-top:4px;">Periode Januari – Mei 2026</div>
    </div>
    <div style="display:flex; gap:8px;">
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
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
      {{-- Aset --}}
      <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
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
      <div style="display:grid; gap:14px; align-content:start;">
        <div class="card" style="overflow:hidden;">
          <div style="padding:14px 20px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
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
          <div style="padding:14px 20px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
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
        <div class="card" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
          <span style="font-size:13px; font-weight:600;">Total Liabilitas + Ekuitas</span>
          <span class="num" style="font-size:16px; font-weight:700; color:var(--accent);">{{ fmt_rp($totalLiab + $totalEkuitas) }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- =================== ARUS KAS =================== --}}
  <div x-show="tab === 'aruskas'" x-cloak>
    <div style="display:grid; gap:14px;">
      {{-- Bar chart --}}
      <div class="card" style="padding:22px 24px;">
        <div class="display" style="font-weight:700; font-size:14px; margin-bottom:16px;">Arus Kas Bulanan 2026</div>
        @php
          $maxVal = max(array_merge($monthlyIn, $monthlyOut));
          $chartH = 120;
        @endphp
        <div style="display:flex; align-items:flex-end; gap:6px; height:{{ $chartH }}px; padding-bottom:0;">
          @foreach($months as $mi => $m)
          @php
            $inH  = round($monthlyIn[$mi]  / $maxVal * $chartH);
            $outH = round($monthlyOut[$mi] / $maxVal * $chartH);
          @endphp
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:2px; height:{{ $chartH }}px; justify-content:flex-end;">
            <div style="display:flex; align-items:flex-end; gap:2px; width:100%;">
              <div style="flex:1; height:{{ $inH }}px; background:var(--good); border-radius:3px 3px 0 0; opacity:.75;"></div>
              <div style="flex:1; height:{{ $outH }}px; background:var(--bad); border-radius:3px 3px 0 0; opacity:.75;"></div>
            </div>
          </div>
          @endforeach
        </div>
        <div style="display:flex; gap:6px; margin-top:6px;">
          @foreach($months as $m)
          <div style="flex:1; text-align:center; font-size:10px; color:var(--ink-4);">{{ $m }}</div>
          @endforeach
        </div>
        <div style="display:flex; gap:16px; margin-top:14px;">
          <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:var(--ink-3);">
            <div style="width:10px; height:10px; border-radius:2px; background:var(--good); opacity:.75;"></div>Kas Masuk
          </div>
          <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:var(--ink-3);">
            <div style="width:10px; height:10px; border-radius:2px; background:var(--bad); opacity:.75;"></div>Kas Keluar
          </div>
        </div>
      </div>

      {{-- Summary --}}
      <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
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
        <div class="card" style="padding:20px 22px;">
          <div class="label" style="margin-bottom:8px; color:{{ $clr }};">{{ $lbl }}</div>
          <div class="display num" style="font-size:22px; font-weight:700; color:{{ $clr }};">{{ fmt_rp(abs($val)) }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- =================== LABA RUGI =================== --}}
  <div x-show="tab === 'labarugi'" x-cloak>
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:14px; align-items:start;">
      <div style="display:grid; gap:14px;">
        <div class="card" style="overflow:hidden;">
          <div style="padding:14px 20px; border-bottom:1px solid var(--line);">
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
          <div style="padding:14px 20px; border-bottom:1px solid var(--line);">
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

      <div style="display:grid; gap:14px; align-content:start;">
        <div class="card" style="padding:22px;">
          <div class="label" style="margin-bottom:16px;">Ringkasan</div>
          @foreach([
            ['Pendapatan', $totalPendapatan, false],
            ['Beban', -$totalBeban, false],
            ['Laba Bersih', $labaRugi, true],
          ] as [$lbl, $val, $bold])
          <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $bold ? 'border-top:1px solid var(--line); margin-top:6px; padding-top:14px;' : '' }}">
            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
            <span class="num" style="color:{{ $val >= 0 ? 'var(--good)' : 'var(--bad)' }};">
              {{ $val < 0 ? '–' : '' }}{{ fmt_rp(abs($val)) }}
            </span>
          </div>
          @endforeach
        </div>
        <div class="card" style="padding:22px; background:{{ $labaRugi >= 0 ? 'var(--good-bg, #f0faf0)' : 'var(--bad-bg, #fff0f0)' }};">
          <div class="label" style="margin-bottom:6px;">Margin Bersih</div>
          @php $margin = $totalPendapatan > 0 ? round($labaRugi / $totalPendapatan * 100, 1) : 0; @endphp
          <div class="display num" style="font-size:32px; font-weight:700; color:{{ $labaRugi >= 0 ? 'var(--good)' : 'var(--bad)' }};">{{ $margin }}%</div>
        </div>
      </div>
    </div>
  </div>

  {{-- =================== EKSEKUTIF =================== --}}
  <div x-show="tab === 'eksekutif'" x-cloak>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:14px;">
      @foreach([
        ['Total Aset',    $totalAset,        'var(--accent)'],
        ['Laba Bersih',   $labaRugi,         $labaRugi >= 0 ? 'var(--good)' : 'var(--bad)'],
        ['Total Liabitas',$totalLiab,        'var(--bad)'],
      ] as [$lbl, $val, $clr])
      <div class="card" style="padding:22px;">
        <div class="label" style="margin-bottom:8px;">{{ $lbl }}</div>
        <div class="display num" style="font-size:24px; font-weight:700; color:{{ $clr }};">{{ fmt_rp($val) }}</div>
      </div>
      @endforeach
    </div>
    <div class="card" style="padding:22px 24px;">
      <div class="display" style="font-weight:700; font-size:14px; margin-bottom:16px;">Kinerja Bulanan (Pendapatan vs Beban)</div>
      @php
        $months5 = ['Jan','Feb','Mar','Apr','Mei'];
        $rev5 = [8_200_000_000, 9_500_000_000, 11_000_000_000, 8_800_000_000, 12_400_000_000];
        $exp5 = [7_200_000_000, 8_300_000_000, 9_800_000_000, 8_000_000_000, 11_000_000_000];
        $maxE = max(array_merge($rev5, $exp5));
        $hE   = 100;
      @endphp
      <div style="display:flex; align-items:flex-end; gap:18px; height:{{ $hE }}px; padding-bottom:0;">
        @foreach($months5 as $mi => $m)
        @php
          $rH = round($rev5[$mi] / $maxE * $hE);
          $eH = round($exp5[$mi] / $maxE * $hE);
        @endphp
        <div style="flex:1; display:flex; flex-direction:column; align-items:center;">
          <div style="display:flex; align-items:flex-end; gap:3px; width:100%; justify-content:center;">
            <div style="flex:1; height:{{ $rH }}px; background:var(--good); border-radius:3px 3px 0 0; opacity:.8;"></div>
            <div style="flex:1; height:{{ $eH }}px; background:var(--bad); border-radius:3px 3px 0 0; opacity:.8;"></div>
          </div>
        </div>
        @endforeach
      </div>
      <div style="display:flex; gap:18px; margin-top:6px;">
        @foreach($months5 as $m)
        <div style="flex:1; text-align:center; font-size:11px; color:var(--ink-4);">{{ $m }}</div>
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
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
      <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
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
        <div style="padding:12px 20px; background:var(--bg-2); border-top:1px solid var(--line); display:flex; justify-content:space-between;">
          <span style="font-size:13px; font-weight:600;">Total Piutang</span>
          <span class="num" style="font-weight:700; color:var(--good);">{{ fmt_rp(array_sum(array_column($piutang, 'jumlah'))) }}</span>
        </div>
      </div>

      <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
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
        <div style="padding:12px 20px; background:var(--bg-2); border-top:1px solid var(--line); display:flex; justify-content:space-between;">
          <span style="font-size:13px; font-weight:600;">Total Utang</span>
          <span class="num" style="font-weight:700; color:var(--bad);">{{ fmt_rp(array_sum(array_column($utang, 'jumlah'))) }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- =================== JURNAL UMUM =================== --}}
  <div x-show="tab === 'jurnal'" x-cloak>
    <div class="card" style="overflow:hidden;">
      <div style="padding:14px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
        <div class="display" style="font-weight:700; font-size:15px;">Jurnal Umum</div>
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
