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
    <div style="padding:24px 28px 60px; display:grid; gap:20px;">

        {{-- Greeting --}}
        <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div>
                <div
                    style="font-size:13px; color:var(--ink-4); margin-bottom:6px; display:flex; align-items:center; gap:8px;">
                    <x-erp.icon name="sun" :size="14" /> Kamis, 8 Mei 2026
                </div>
                <h1 class="display"
                    style="margin:0; font-size:34px; font-weight:700; letter-spacing:-0.025em; line-height:1.1;">Selamat
                    pagi, Albert.</h1>
                <div style="font-size:14px; color:var(--ink-3); margin-top:6px;">
                    Margin tertimbang minggu ini naik <strong style="color:var(--ink);">1.6 poin</strong>. Ada <strong
                        style="color:var(--ink);">3 SO</strong> menunggu pengiriman dan <strong style="color:var(--ink);">2
                        tagihan</strong> jatuh tempo dalam 48 jam.
                </div>
            </div>
            <div style="display:flex; gap:8px;">
                <button class="btn btn-ghost"><x-erp.icon name="download" :size="14" />Ekspor</button>
                <a href="{{ route('erp.penjualan.create') }}" class="btn btn-primary"><x-erp.icon name="plus"
                        :size="15" />Buat SO Baru</a>
            </div>
        </div>

        {{-- KPI row --}}
        <div style="display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:14px;">
            <x-erp.kpi-card label="Total Penjualan" :value="fmt_rp_short($kpis['penjualan']['value'])" :delta="$kpis['penjualan']['delta']" :sparkline="$kpis['penjualan']['sparkline']"
                :accent="true" />
            <x-erp.kpi-card label="Total Pembelian" :value="fmt_rp_short($kpis['pembelian']['value'])" :delta="$kpis['pembelian']['delta']" :sparkline="$kpis['pembelian']['sparkline']" />
            <x-erp.kpi-card label="Blended Margin" :value="number_format($kpis['margin']['value'], 1)" suffix="%" :delta="$kpis['margin']['delta']"
                :sparkline="$kpis['margin']['sparkline']" />
            <x-erp.kpi-card label="Stok Aktif" :value="fmt_rp_short($kpis['stok']['value'])" :delta="$kpis['stok']['delta']" :sparkline="$kpis['stok']['sparkline']" />
        </div>

        {{-- Chart + Pipeline --}}
        <div style="display:grid; grid-template-columns:1.6fr 1fr; gap:14px;">
            {{-- Bar Chart --}}
            <div class="card" style="padding:22px 24px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px;">
                    <div>
                        <div class="display" style="font-size:18px; font-weight:700;">Penjualan vs Pembelian</div>
                        <div style="font-size:12px; color:var(--ink-4); margin-top:2px;">12 bulan terakhir · dalam Juta
                            Rupiah</div>
                    </div>
                    <div style="display:flex; gap:14px; align-items:center; font-size:12px;">
                        <span style="display:inline-flex; align-items:center; gap:6px;"><span
                                style="width:10px; height:10px; border-radius:3px; background:var(--accent); display:inline-block;"></span>Penjualan</span>
                        <span style="display:inline-flex; align-items:center; gap:6px;"><span
                                style="width:10px; height:10px; border-radius:3px; background:var(--ink-2); display:inline-block;"></span>Pembelian</span>
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
                <div
                    style="margin-top:18px; padding:14px; background:var(--bg-2); border-radius:12px; display:flex; gap:12px; align-items:center;">
                    <div
                        style="width:34px; height:34px; border-radius:10px; background:var(--accent); color:var(--accent-ink); display:grid; place-items:center; flex:0 0 34px;">
                        <x-erp.icon name="trend" :size="16" />
                    </div>
                    <div style="flex:1; font-size:12.5px; color:var(--ink-2); line-height:1.45;">
                        Konversi SO → Lunas <strong>78.5%</strong>. Tagihan ≥ 30 hari turun <strong>–Rp 42 Jt</strong>
                        minggu ini.
                    </div>
                </div>
            </div>
        </div>

        {{-- Coverage + Klien --}}
        <div style="display:grid; grid-template-columns:1fr 1.6fr; gap:14px;">
            {{-- Coverage ring --}}
            <div class="card" style="padding:22px 24px;">
                <x-erp.section-title title="Kelengkapan Data"
                    subtitle="Rata-rata {{ $dataPct }}% di seluruh master" />
                <div style="display:flex; align-items:center; gap:18px; padding:8px 0 14px;">
                    @php
                        $r = (84 - 9) / 2;
                        $c = 2 * M_PI * $r;
                        $dash = ($dataPct / 100) * $c;
                    @endphp
                    <div style="position:relative; flex-shrink:0;">
                        <svg width="84" height="84">
                            <circle cx="42" cy="42" r="{{ $r }}" fill="none"
                                stroke="var(--bg-3)" stroke-width="9" />
                            <circle cx="42" cy="42" r="{{ $r }}" fill="none"
                                stroke="var(--accent)" stroke-width="9" stroke-linecap="round"
                                stroke-dasharray="{{ round($dash, 1) }} {{ round($c, 1) }}"
                                transform="rotate(-90 42 42)" />
                        </svg>
                        <div style="position:absolute; inset:0; display:grid; place-items:center;">
                            <div class="display num" style="font-size:22px; font-weight:700;">{{ $dataPct }}%</div>
                        </div>
                    </div>
                    <div style="flex:1; display:grid; gap:10px;">
                        @foreach ($dataCoverage as $d)
                            @php $barColor = $d['pct'] >= 80 ? 'var(--ok)' : ($d['pct'] >= 60 ? 'var(--warn)' : 'var(--bad)'); @endphp
                            <div>
                                <div
                                    style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                    <span style="color:var(--ink-2);">{{ $d['label'] }}</span>
                                    <span class="mono" style="color:var(--ink-4);">{{ $d['items'] }}</span>
                                </div>
                                <div style="height:5px; background:var(--bg-3); border-radius:999px;">
                                    <div
                                        style="width:{{ $d['pct'] }}%; height:100%; background:{{ $barColor }}; border-radius:999px;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div style="border-top:1px solid var(--line); padding-top:14px; font-size:12px; color:var(--ink-3);">
                    <strong style="color:var(--ink);">22 produk</strong> belum punya foto.
                    <a href="{{ route('erp.master.index') }}"
                        style="color:var(--accent); font-weight:600; text-decoration:none;">Lengkapi sekarang →</a>
                </div>
            </div>

            {{-- Klien table --}}
            <div class="card" style="padding:22px 24px;">
                <x-erp.section-title title="Daftar Klien & Vendor Aktif"
                    subtitle="Diurutkan berdasarkan omzet 30 hari terakhir">
                    <x-slot:action>
                        <div style="display:flex; gap:6px;">
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
                                    <td><span class="chip"
                                            style="background:var(--bg-2); color:var(--ink-3);">{{ $k['jenis'] }}</span>
                                    </td>
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
