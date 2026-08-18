{{-- =================== UTANG =================== --}}
@php
  $utang = [
    ['nama'=>'PT Bogasari Flour Mills','ref'=>'INV-BFM-23104','jatuhTempo'=>'26 Mei 2026','jumlah'=>98_400_000,'umur'=>-14],
    ['nama'=>'CV Agro Sejahtera','ref'=>'INV-AGR-00891','jatuhTempo'=>'18 Mei 2026','jumlah'=>24_800_000,'umur'=>-6],
  ];
@endphp
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
