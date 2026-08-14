{{-- =================== NERACA =================== --}}
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
