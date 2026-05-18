@extends('layouts.app')
@section('content')
    @php
        $authUser = auth()->user();
        $userName = $authUser?->name ?? 'User';

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
                <h1 class="dash-greeting__title display">
                    <span data-dashboard-greeting>Selamat pagi</span>, {{ $userName }}.
                </h1>
                <div class="dash-greeting__sub">
                    Ada <strong class="dash-greeting__strong">3 SO</strong> menunggu pengiriman dan <strong
                        class="dash-greeting__strong">2
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
            <div class="card card-chart">
                <div class="chart-hd">
                    <div>
                        <div class="chart-title display">Penjualan vs Pembelian</div>
                        <div class="chart-sub">12 bulan terakhir · dalam Juta Rupiah</div>
                    </div>
                    <div class="chart-legend">
                        <span class="chart-legend-item">
                            <span class="chart-legend-dot chart-legend-dot--accent"></span>Penjualan
                        </span>
                        <span class="chart-legend-item">
                            <span class="chart-legend-dot chart-legend-dot--ink"></span>Pembelian
                        </span>
                    </div>
                </div>
                {{-- SVG bar chart --}}
                <div class="barchart-wrap"
                    style="position:relative; height:{{ $chartH }}px; padding-left:48px; padding-top:8px;">
                    @foreach ([0, 0.5, 1] as $t)
                        <div
                            class="barchart-grid-line"
                            style="top:{{ 8 + ($chartH - 40) * (1 - $t) }}px;">
                            <span
                                class="barchart-grid-label mono">{{ fmt_rp_short($maxVal * $t * 1_000_000) }}</span>
                        </div>
                    @endforeach
                    @php
                        $totalW = count($monthly) * ($groupW + $groupGap) - $groupGap;
                    @endphp
                    <svg width="100%" height="{{ $chartBase }}" viewBox="0 0 {{ $totalW }} {{ $chartBase }}"
                        preserveAspectRatio="none" class="svg-barchart">
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
                    <div class="barchart-months">
                        @foreach ($monthly as $i => $d)
                            @php
                                $cx = $i * ($groupW + $groupGap) + $groupW / 2;
                                $pct = round($cx / $totalW * 100, 2);
                            @endphp
                            <div class="barchart-month-label mono"
                                style="position:absolute; left:{{ $pct }}%; transform:translateX(-50%);">{{ $d[0] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Pipeline --}}
            <div class="card card-chart">
                <x-misc.section-title title="Pipeline Penjualan" subtitle="Bulan berjalan · 8 hari ke depan" />
                <div class="pipeline-grid">
                    @foreach ($pipeline as $s)
                        @php $w = round(($s['value'] / $pipelineMax) * 100); @endphp
                        <div class="pipeline-row">
                            <div>
                                <div class="pipeline-stage-header">{{ $s['stage'] }}</div>
                                <div class="pipeline-stage-label mono">{{ $s['count'] }} dokumen
                                </div>
                            </div>
                            <div class="pipeline-bar-track">
                                <div class="pipeline-bar-fill" style="width:{{ $w }}%; background:{{ $s['color'] }};">
                                </div>
                            </div>
                            <div class="num pipeline-value">
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
                    const greetingEl = document.querySelector('[data-dashboard-greeting]');

                    const updateGreeting = () => {
                        if (!greetingEl) return;

                        const hour = new Date().getHours();
                        let greeting = 'Selamat Malam';

                        if (hour < 11) {
                            greeting = 'Selamat Pagi';
                        } else if (hour < 15) {
                            greeting = 'Selamat Siang';
                        } else if (hour < 18) {
                            greeting = 'Selamat Sore';
                        }

                        greetingEl.textContent = greeting;
                    };

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

                    updateGreeting();
                    updateDate();
                    setInterval(updateDate, 60 * 1000);
                    setInterval(updateGreeting, 60 * 1000);
                })
                ();
            </script>
        @endpush
    @endonce
@endsection
