@props(['label', 'value', 'suffix' => '', 'delta', 'sparkline', 'accent' => false])
@php
    $positive = $delta >= 0;
    $bg = $accent ? 'var(--ink)' : 'var(--paper)';
    $color = $accent ? 'var(--paper)' : 'var(--ink)';
    $lcolor = $accent ? 'rgba(252,250,245,.55)' : 'var(--ink-4)';
    $dBg = $accent ? 'rgba(252,250,245,.12)' : ($positive ? 'var(--ok-bg)' : 'var(--bad-bg)');
    $dFg = $accent
        ? ($positive
            ? 'oklch(0.85 0.12 155)'
            : 'oklch(0.85 0.10 30)')
        : ($positive
            ? 'var(--ok)'
            : 'var(--bad)');
    $spColor = $accent ? 'oklch(0.78 0.16 60)' : 'var(--accent)';
@endphp
<div class="card"
    style="padding:18px 20px; overflow:hidden; background:{{ $bg }}; color:{{ $color }};">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
        <div style="min-width:0;">
            <div
                style="font-size:11.5px; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; color:{{ $lcolor }};">
                {{ $label }}</div>
            <div class="display num" style="font-size:28px; font-weight:700; margin-top:6px; color:{{ $color }};">
                {{ $value }}@if ($suffix)
                    <span
                        style="font-size:18px; font-weight:600; margin-left:3px; color:{{ $lcolor }};">{{ $suffix }}</span>
                @endif
            </div>
            <div style="display:flex; align-items:center; gap:8px; margin-top:8px;">
                <span class="chip"
                    style="background:{{ $dBg }}; color:{{ $dFg }}; padding:0 8px; height:20px;">
                    {{ $positive ? '↑' : '↓' }} {{ abs($delta) }}%
                </span>
                <span style="font-size:11.5px; color:{{ $lcolor }};">vs bulan lalu</span>
            </div>
        </div>
        <x-erp.sparkline :data="$sparkline" :color="$spColor" :w="88" :h="36" />
    </div>
</div>
