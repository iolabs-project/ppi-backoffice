@props(['name', 'size' => 32])
@php $m = avatar_meta($name); @endphp
<div class="avatar" style="width:{{ $size }}px; height:{{ $size }}px; background:{{ $m['bg'] }}; color:{{ $m['fg'] }};">
  {{ $m['initials'] }}
</div>
