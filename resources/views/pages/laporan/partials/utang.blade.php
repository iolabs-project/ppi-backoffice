{{-- =================== UTANG & PIUTANG =================== --}}
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
