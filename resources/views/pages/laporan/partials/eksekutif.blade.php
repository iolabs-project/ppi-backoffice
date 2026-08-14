{{-- =================== EKSEKUTIF =================== --}}
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
