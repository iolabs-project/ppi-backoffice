{{-- =================== JURNAL UMUM =================== --}}
<div class="card" style="overflow:hidden;">
  <div class="card-hd">
    <div class="display card-hd-title">Jurnal Umum</div>
    <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
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
