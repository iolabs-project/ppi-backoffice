{{-- =================== LABA RUGI =================== --}}
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
