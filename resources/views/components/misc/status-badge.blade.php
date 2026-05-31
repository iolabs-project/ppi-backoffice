@props(['status' => 'pending'])
@php
  $map = [
    'sehat'   => ['chip chip-ok',   'chip-dot dot-ok',   'Sehat'],
    'review'  => ['chip chip-warn', 'chip-dot dot-warn', 'Perlu Review'],
    'kritis'  => ['chip chip-bad',  'chip-dot dot-bad',  'Kritis'],
    'pending' => ['chip',           'chip-dot dot-neutral','Pending'],
    'dikirim' => ['chip chip-info', 'chip-dot dot-info', 'Dikirim'],
    'tagihan' => ['chip chip-warn', 'chip-dot dot-warn', 'Tagihan'],
    'lunas'   => ['chip chip-ok',   'chip-dot dot-ok',   'Lunas'],
    'draft'           => ['chip',           'chip-dot dot-muted', 'Draft'],
    'disetujui'       => ['chip chip-info', 'chip-dot dot-info',  'Disetujui'],
    'selesai'         => ['chip chip-ok',   'chip-dot dot-ok',    'Selesai'],
    'open'            => ['chip chip-info', 'chip-dot dot-info',  'Open'],
    'terkirim'        => ['chip chip-ok',   'chip-dot dot-ok',    'Terkirim'],
    'belum-dibayar'   => ['chip chip-bad',  'chip-dot dot-bad',   'Belum Dibayar'],
    'dibayar-sebagian'=> ['chip chip-warn', 'chip-dot dot-warn',  'Dibayar Sebagian'],
    'menunggu'        => ['chip chip-warn', 'chip-dot dot-warn',  'Menunggu'],
    'ditolak'         => ['chip chip-bad',  'chip-dot dot-bad',   'Ditolak'],
  ];
  [$chipClass, $dotClass, $label] = $map[$status] ?? $map['pending'];
@endphp
<span class="{{ $chipClass }}">
  <span class="{{ $dotClass }}"></span>
  {{ $label }}
</span>
