@extends('layouts.app')
@section('content')
    @php
        $pendapatan = 4287650000;
        $hpp = 3412900000;
        $dataPct = (int) round(array_sum(array_column($dataCoverage, 'pct')) / count($dataCoverage));

        $monthly = $monthly ?? [];
        $maxVal = 0;
        foreach ($monthly as $m) {
            $maxVal = max($maxVal, $m[1], $m[2]);
        }
        $maxVal = $maxVal * 1.15;
        $barW = 14;
        $gap = 4;
        $groupGap = 22;
        $groupW = $barW * 2 + $gap;
        $chartH = 220;
        $chartBase = $chartH - 32;

        $pipelineMax = max(array_column($pipeline, 'value'));
    @endphp
    <div class="dash">

        {{-- Greeting --}}
        <div class="dash-greeting">
            <div>
                <div class="dash-greeting__date">
                    <x-misc.icon name="sun" :size="14" /> <span data-dashboard-date></span>
                </div>
                <h1 class="dash-greeting__title display">Selamat pagi, Albert.</h1>
                <div class="dash-greeting__sub">
                    Ada <strong style="color:var(--ink);">3 SO</strong> menunggu pengiriman dan <strong
                        style="color:var(--ink);">2
                        tagihan</strong> jatuh tempo dalam 48 jam.
                </div>
            </div>
        </div>

        {{-- KPI row --}}
        <div class="dash-kpi-grid">
            <x-misc.kpi-card label="Total Penjualan" :value="fmt_rp($kpis['penjualan']['value'])" :delta="$kpis['penjualan']['delta']" :sparkline="$kpis['penjualan']['sparkline']"
                :accent="true" />
            <x-misc.kpi-card label="Total Pembelian" :value="fmt_rp($kpis['pembelian']['value'])" :delta="$kpis['pembelian']['delta']" :sparkline="$kpis['pembelian']['sparkline']" />
            {{-- <x-misc.kpi-card label="Blended Margin" :value="number_format($kpis['margin']['value'], 1)" suffix="%" :delta="$kpis['margin']['delta']" :sparkline="$kpis['margin']['sparkline']" /> --}}
            {{-- <x-misc.kpi-card label="Stok Aktif" :value="fmt_rp_short($kpis['stok']['value'])" :delta="$kpis['stok']['delta']" :sparkline="$kpis['stok']['sparkline']" /> --}}
        </div>

        {{-- Chart + Pipeline --}}
        <div class="dash-charts">
            {{-- Bar Chart --}}
            <div class="card" style="padding:22px 24px;">
                <div class="chart-hd">
                    <div>
                        <div class="chart-title display">Penjualan vs Pembelian</div>
                        <div class="chart-sub">12 bulan terakhir · dalam Juta Rupiah</div>
                    </div>
                    <div class="chart-legend">
                        <span class="chart-legend-item">
                            <span class="chart-legend-dot" style="background:var(--accent);"></span>Penjualan
                        </span>
                        <span class="chart-legend-item">
                            <span class="chart-legend-dot" style="background:var(--ink-2);"></span>Pembelian
                        </span>
                    </div>
                </div>
                {{-- SVG bar chart --}}
                <div class="barchart-wrap"
                    style="position:relative; height:{{ $chartH }}px; padding-left:48px; padding-top:8px;">
                    @foreach ([0, 0.5, 1] as $t)
                        <div
                            style="position:absolute; left:0; right:0; top:{{ 8 + ($chartH - 40) * (1 - $t) }}px; border-top:1px dashed var(--line); display:flex; align-items:center;">
                            <span
                                style="position:absolute; left:0; transform:translateY(-50%); font-size:10.5px; color:var(--ink-4);"
                                class="mono">{{ fmt_rp_short($maxVal * $t * 1_000_000) }}</span>
                        </div>
                    @endforeach
                    @php
                        $totalW = count($monthly) * ($groupW + $groupGap) - $groupGap;
                    @endphp
                    <svg width="100%" height="{{ $chartBase }}" viewBox="0 0 {{ $totalW }} {{ $chartBase }}"
                        preserveAspectRatio="none" style="overflow:visible;">
                        @foreach ($monthly as $i => $d)
                            @php
                                $x0 = $i * ($groupW + $groupGap);
                                $h1 = round(($d[1] / $maxVal) * ($chartH - 40));
                                $h2 = round(($d[2] / $maxVal) * ($chartH - 40));
                            @endphp
                            <rect class="barchart-bar" data-series="Penjualan"
                                data-tooltip="{{ fmt_rp_short($d[1] * 1_000_000) }}" x="{{ $x0 }}"
                                y="{{ $chartBase - $h1 }}" width="{{ $barW }}" height="{{ $h1 }}"
                                rx="3" fill="var(--accent)" />
                            <rect class="barchart-bar" data-series="Pembelian"
                                data-tooltip="{{ fmt_rp_short($d[2] * 1_000_000) }}" x="{{ $x0 + $barW + $gap }}"
                                y="{{ $chartBase - $h2 }}" width="{{ $barW }}" height="{{ $h2 }}"
                                rx="3" fill="var(--ink-2)" opacity="0.85" />
                        @endforeach
                    </svg>
                    <div class="barchart-tooltip" aria-hidden="true">
                        <div class="barchart-tooltip__box">
                            <div class="barchart-tooltip__label"></div>
                            <div class="barchart-tooltip__value"></div>
                        </div>
                        <div class="barchart-tooltip__pin"></div>
                    </div>
                    <div style="position:absolute; left:48px; right:0; bottom:0; display:flex; gap:{{ $groupGap }}px;">
                        @foreach ($monthly as $d)
                            <div style="width:{{ $groupW }}px; font-size:11px; color:var(--ink-4); text-align:center;"
                                class="mono">{{ $d[0] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Pipeline --}}
            <div class="card" style="padding:22px 24px;">
                <x-erp.section-title title="Pipeline Penjualan" subtitle="Bulan berjalan · 8 hari ke depan" />
                <div style="display:grid; gap:10px;">
                    @foreach ($pipeline as $s)
                        @php $w = round(($s['value'] / $pipelineMax) * 100); @endphp
                        <div style="display:grid; grid-template-columns:140px 1fr 110px; align-items:center; gap:14px;">
                            <div>
                                <div style="font-size:13px; font-weight:600;">{{ $s['stage'] }}</div>
                                <div style="font-size:11px; color:var(--ink-4);" class="mono">{{ $s['count'] }} dokumen
                                </div>
                            </div>
                            <div style="height:32px; background:var(--bg-2); border-radius:8px; overflow:hidden;">
                                <div style="width:{{ $w }}%; height:100%; background:{{ $s['color'] }};">
                                </div>
                            </div>
                            <div class="num" style="font-size:13px; font-weight:600; text-align:right;">
                                {{ fmt_rp_short($s['value']) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="pipeline-insight">
                    <div class="pipeline-insight__icon">
                        <x-misc.icon name="trend" :size="16" />
                    </div>
                    <div class="pipeline-insight__text">
                        Konversi SO → Lunas <strong>78.5%</strong>. Tagihan ≥ 30 hari turun <strong>–Rp 42 Jt</strong>
                        minggu ini.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function() {
                    const dateEl = document.querySelector('[data-dashboard-date]');
                    if (!dateEl) return;

                    const formatter = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    });

                    const updateDate = () => {
                        dateEl.textContent = formatter.format(new Date());
                    };

                    updateDate();
                    setInterval(updateDate, 60 * 1000);
                })
                ();
            </script>
        @endpush
    @endonce

    @once
        @push('scripts')
            <script>
                (function() {
                    // Tooltip behavior for bar chart
                    const wrap = document.querySelector('.barchart-wrap');
                    if (!wrap) return;
                    const tooltip = wrap.querySelector('.barchart-tooltip');
                    const labelEl = tooltip.querySelector('.barchart-tooltip__label');
                    const valueEl = tooltip.querySelector('.barchart-tooltip__value');

                    const show = (series, value, x, y) => {
                        labelEl.textContent = series;
                        valueEl.textContent = value;
                        tooltip.style.display = 'block';
                        const rect = wrap.getBoundingClientRect();
                        const left = Math.max(8, x - rect.left - 40);
                        const top = Math.max(8, y - rect.top - 60);
                        tooltip.style.left = left + 'px';
                        tooltip.style.top = top + 'px';
                    };

                    const hide = () => {
                        tooltip.style.display = 'none';
                    };

                    wrap.querySelectorAll('.barchart-bar').forEach((bar) => {
                        bar.addEventListener('mouseenter', (e) => {
                            const series = bar.dataset.series || '';
                            const value = bar.dataset.tooltip || '';
                            show(series, value, e.clientX, e.clientY);
                        });
                        bar.addEventListener('mousemove', (e) => {
                            const series = bar.dataset.series || '';
                            const value = bar.dataset.tooltip || '';
                            show(series, value, e.clientX, e.clientY);
                        });
                        bar.addEventListener('mouseleave', hide);
                    });
                })
                ();
            </script>
            <style>
                .barchart-wrap {
                    position: relative;
                }

                .barchart-tooltip {
                    position: absolute;
                    display: none;
                    pointer-events: none;
                    transform: translateY(0);
                    z-index: 20;
                }

                .barchart-tooltip__box {
                    background: #000;
                    color: #fff;
                    padding: 8px 10px;
                    border-radius: 8px;
                    font-size: 12px;
                    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
                    white-space: nowrap;
                }

                .barchart-tooltip__label {
                    font-weight: 600;
                    font-size: 11px;
                    opacity: .9;
                }

                .barchart-tooltip__value {
                    font-weight: 700;
                    margin-top: 2px;
                }

                .barchart-tooltip__pin {
                    width: 10px;
                    height: 10px;
                    background: #000;
                    transform: rotate(45deg);
                    margin-top: -6px;
                    margin-left: 45%;
                    border-radius: 1px;
                }
            </style>
        @endpush
    @endonce
@endsection
