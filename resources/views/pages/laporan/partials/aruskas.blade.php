{{-- =================== ARUS KAS =================== --}}
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
