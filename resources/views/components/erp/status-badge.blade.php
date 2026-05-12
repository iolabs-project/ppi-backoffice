@props(['status' => 'pending'])
@php
  $map = [
    'sehat'   => ['bg'=>'var(--ok-bg)',   'fg'=>'var(--ok)',   'dot'=>'var(--ok)',   'label'=>'Sehat'],
    'review'  => ['bg'=>'var(--warn-bg)', 'fg'=>'var(--warn)', 'dot'=>'var(--warn)', 'label'=>'Perlu Review'],
    'kritis'  => ['bg'=>'var(--bad-bg)',  'fg'=>'var(--bad)',  'dot'=>'var(--bad)',  'label'=>'Kritis'],
    'pending' => ['bg'=>'var(--bg-2)',    'fg'=>'var(--ink-3)','dot'=>'var(--ink-4)','label'=>'Pending'],
    'dikirim' => ['bg'=>'var(--info-bg)', 'fg'=>'var(--info)', 'dot'=>'var(--info)', 'label'=>'Dikirim'],
    'tagihan' => ['bg'=>'var(--warn-bg)', 'fg'=>'var(--warn)', 'dot'=>'var(--warn)', 'label'=>'Tagihan'],
    'lunas'   => ['bg'=>'var(--ok-bg)',   'fg'=>'var(--ok)',   'dot'=>'var(--ok)',   'label'=>'Lunas'],
    'draft'   => ['bg'=>'var(--bg-2)',    'fg'=>'var(--ink-4)','dot'=>'var(--ink-5)','label'=>'Draft'],
  ];
  $s = $map[$status] ?? $map['pending'];
@endphp
<span class="chip" style="background:{{ $s['bg'] }}; color:{{ $s['fg'] }};">
  <span class="chip-dot" style="background:{{ $s['dot'] }};"></span>
  {{ $s['label'] }}
</span>
