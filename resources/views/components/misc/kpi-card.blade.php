@props(['label', 'value', 'suffix' => '', 'delta', 'sparkline', 'accent' => false])
@php
    $positive = $delta >= 0;
    $dBg = $accent ? 'rgba(252,250,245,.12)' : ($positive ? 'var(--ok-bg)' : 'var(--bad-bg)');
    $dFg = $accent
        ? ($positive ? 'oklch(0.85 0.12 155)' : 'oklch(0.85 0.10 30)')
        : ($positive ? 'var(--ok)' : 'var(--bad)');
    $spColor = $accent ? 'oklch(0.78 0.16 60)' : 'var(--accent)';
@endphp
<div class="card kpi-card {{ $accent ? 'kpi-card--accent' : '' }}">
    <div class="kpi-card__hd">
        <div class="kpi-card__body">
            <div class="kpi-card__label">{{ $label }}</div>
            <div class="kpi-card__value display num">
                {{ $value }}@if ($suffix)
                    <span class="kpi-card__suffix">{{ $suffix }}</span>
                @endif
            </div>
            <div class="kpi-card__delta">
                <span class="chip" style="background:{{ $dBg }}; color:{{ $dFg }}; padding:0 8px; height:20px;">
                    {{ $positive ? '↑' : '↓' }} {{ abs($delta) }}%
                </span>
                <span class="kpi-card__period">vs bulan lalu</span>
            </div>
        </div>
        <x-misc.sparkline :data="$sparkline" :color="$spColor" :w="159" :h="95" />
    </div>
</div>
