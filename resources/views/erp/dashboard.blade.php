@extends('layouts.erp')
@section('content')
    @php
        $pendapatan = 4_287_650_000;
        $hpp = 3_412_900_000;
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
                    <x-erp.icon name="sun" :size="14" /> Kamis, 8 Mei 2026
                </div>
                <h1 class="dash-greeting__title display">Selamat pagi, Albert.</h1>
                <div class="dash-greeting__sub">
                    Margin tertimbang minggu ini naik <strong style="color:var(--ink);">1.6 poin</strong>. Ada <strong
                        style="color:var(--ink);">3 SO</strong> menunggu pengiriman dan <strong style="color:var(--ink);">2
                        tagihan</strong> jatuh tempo dalam 48 jam.
                </div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-erp.icon name="download" :size="14" />Ekspor</button>
                <a href="{{ route('erp.penjualan.create') }}" class="btn btn-primary"><x-erp.icon name="plus"
                        :size="15" />Buat SO Baru</a>
            </div>
        </div>

        {{-- KPI row --}}
        <div class="dash-kpi-grid">
            <x-erp.kpi-card label="Total Penjualan" :value="fmt_rp_short($kpis['penjualan']['value'])" :delta="$kpis['penjualan']['delta']" :sparkline="$kpis['penjualan']['sparkline']"
                :accent="true" />
            <x-erp.kpi-card label="Total Pembelian" :value="fmt_rp_short($kpis['pembelian']['value'])" :delta="$kpis['pembelian']['delta']" :sparkline="$kpis['pembelian']['sparkline']" />
            <x-erp.kpi-card label="Blended Margin" :value="number_format($kpis['margin']['value'], 1)" suffix="%" :delta="$kpis['margin']['delta']"
                :sparkline="$kpis['margin']['sparkline']" />
            <x-erp.kpi-card label="Stok Aktif" :value="fmt_rp_short($kpis['stok']['value'])" :delta="$kpis['stok']['delta']" :sparkline="$kpis['stok']['sparkline']" />
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
                <div style="position:relative; height:{{ $chartH }}px; padding-left:48px; padding-top:8px;">
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
                            <rect x="{{ $x0 }}" y="{{ $chartBase - $h1 }}" width="{{ $barW }}"
                                height="{{ $h1 }}" rx="3" fill="var(--accent)" />
                            <rect x="{{ $x0 + $barW + $gap }}" y="{{ $chartBase - $h2 }}" width="{{ $barW }}"
                                height="{{ $h2 }}" rx="3" fill="var(--ink-2)" opacity="0.85" />
                        @endforeach
                    </svg>
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
                                <div style="font-size:11px; color:var(--ink-4);" class="mono">{{ $s['count'] }} dokumen</div>
                            </div>
                            <div style="height:32px; background:var(--bg-2); border-radius:8px; overflow:hidden;">
                                <div style="width:{{ $w }}%; height:100%; background:{{ $s['color'] }};"></div>
                            </div>
                            <div class="num" style="font-size:13px; font-weight:600; text-align:right;">
                                {{ fmt_rp_short($s['value']) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="pipeline-insight">
                    <div class="pipeline-insight__icon">
                        <x-erp.icon name="trend" :size="16" />
                    </div>
                    <div class="pipeline-insight__text">
                        Konversi SO → Lunas <strong>78.5%</strong>. Tagihan ≥ 30 hari turun <strong>–Rp 42 Jt</strong>
                        minggu ini.
                    </div>
                </div>
            </div>
        </div>

        {{-- Coverage + Klien --}}
        <div class="dash-bottom">
            {{-- Coverage ring --}}
            <div class="card" style="padding:22px 24px;">
                <x-erp.section-title title="Kelengkapan Data"
                    subtitle="Rata-rata {{ $dataPct }}% di seluruh master" />
                <div class="coverage-ring">
                    @php
                        $r = (84 - 9) / 2;
                        $c = 2 * M_PI * $r;
                        $dash = ($dataPct / 100) * $c;
                    @endphp
                    <div class="coverage-ring__svg">
                        <svg width="84" height="84">
                            <circle cx="42" cy="42" r="{{ $r }}" fill="none"
                                stroke="var(--bg-3)" stroke-width="9" />
                            <circle cx="42" cy="42" r="{{ $r }}" fill="none"
                                stroke="var(--accent)" stroke-width="9" stroke-linecap="round"
                                stroke-dasharray="{{ round($dash, 1) }} {{ round($c, 1) }}"
                                transform="rotate(-90 42 42)" />
                        </svg>
                        <div class="coverage-ring__pct">
                            <div class="coverage-ring__label display num">{{ $dataPct }}%</div>
                        </div>
                    </div>
                    <div class="coverage-bars">
                        @foreach ($dataCoverage as $d)
                            @php $barColor = $d['pct'] >= 80 ? 'var(--ok)' : ($d['pct'] >= 60 ? 'var(--warn)' : 'var(--bad)'); @endphp
                            <div>
                                <div class="coverage-bar__row">
                                    <span style="color:var(--ink-2);">{{ $d['label'] }}</span>
                                    <span class="mono" style="color:var(--ink-4);">{{ $d['items'] }}</span>
                                </div>
                                <div class="coverage-bar__track">
                                    <div style="width:{{ $d['pct'] }}%; height:100%; background:{{ $barColor }}; border-radius:999px;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="coverage-footer">
                    <strong style="color:var(--ink);">22 produk</strong> belum punya foto.
                    <a href="{{ route('erp.master.index') }}" class="coverage-footer__link">Lengkapi sekarang →</a>
                </div>
            </div>

            {{-- Klien table --}}
            <div class="card" style="padding:22px 24px;">
                <x-erp.section-title title="Daftar Klien & Vendor Aktif"
                    subtitle="Diurutkan berdasarkan omzet 30 hari terakhir">
                    <x-slot:action>
                        <div class="order-actions">
                            <button class="btn btn-ghost btn-sm"><x-erp.icon name="filter"
                                    :size="13" />Filter</button>
                            <a href="{{ route('erp.master.index') }}" class="btn btn-ghost btn-sm">Lihat Semua<x-erp.icon
                                    name="chev-right" :size="13" /></a>
                        </div>
                    </x-slot:action>
                </x-erp.section-title>
                <div style="overflow:hidden; border-radius:12px; border:1px solid var(--line);">
                    <table class="tbl tbl-tight">
                        <thead>
                            <tr>
                                <th>Kontak</th>
                                <th>Jenis</th>
                                <th style="text-align:right;">Omzet 30D</th>
                                <th style="text-align:right;">Saldo Net</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_slice($klien, 0, 6) as $k)
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <x-erp.avatar :name="$k['nama']" />
                                            <div>
                                                <div style="font-weight:600; font-size:13px;">{{ $k['nama'] }}</div>
                                                <div style="font-size:11.5px; color:var(--ink-4);" class="mono">
                                                    {{ $k['id'] }} · {{ $k['kota'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="chip">{{ $k['jenis'] }}</span></td>
                                    <td class="num" style="text-align:right; font-weight:600;">
                                        {{ fmt_rp_short($k['omzet']) }}</td>
                                    <td class="num"
                                        style="text-align:right; color:{{ $k['piutang'] >= 0 ? 'var(--ink)' : 'var(--ink-4)' }};">
                                        {{ $k['piutang'] >= 0 ? fmt_rp_short($k['piutang']) : '–' . fmt_rp_short(abs($k['piutang'])) }}
                                    </td>
                                    <td><x-erp.status-badge :status="$k['status']" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
