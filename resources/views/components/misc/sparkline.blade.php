@props(['data', 'color' => 'var(--accent)', 'w' => 96, 'h' => 28, 'area' => true])
@php
  $min  = min($data);
  $max  = max($data);
  $span = ($max - $min) ?: 1;
  $pts  = [];
  $cnt  = count($data);
  foreach ($data as $i => $v) {
      $pts[] = [
          round(($i / ($cnt - 1)) * $w, 1),
          round($h - (($v - $min) / $span) * $h * 0.85 - $h * 0.075, 1),
      ];
  }
  $d     = implode(' ', array_map(fn($p, $i) => ($i ? 'L' : 'M') . $p[0] . ' ' . $p[1], $pts, array_keys($pts)));
  $dArea = $d . " L {$w} {$h} L 0 {$h} Z";
  $last  = end($pts);
@endphp
<svg width="{{ $w }}" height="{{ $h }}" viewBox="0 0 {{ $w }} {{ $h }}">
  @if($area)
    <path d="{{ $dArea }}" fill="{{ $color }}" opacity="0.10" />
  @endif
  <path d="{{ $d }}" stroke="{{ $color }}" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round" />
  <circle cx="{{ $last[0] }}" cy="{{ $last[1] }}" r="2.5" fill="{{ $color }}" />
</svg>
