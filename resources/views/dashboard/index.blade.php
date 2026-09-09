@extends('layouts.app')
@section('content')
    @php
        $authUser = auth()->user();
        $userName = $authUser?->name ?? 'User';

        $monthly = $monthly ?? [];
        $maxVal = 0;
        foreach ($monthly as $m) {
            $maxVal = max($maxVal, $m[1], $m[2]);
        }
        $maxVal = $maxVal * 1.15 ?: 1;

        $pipelineMax = max(array_column($pipeline, 'value')) ?: 1;
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
                    @if ($greetingStats['pending_deliveries'] > 0)
                        Ada <strong class="dash-greeting__strong">{{ $greetingStats['pending_deliveries'] }} SO</strong> menunggu pengiriman
                    @endif
                    @if ($greetingStats['pending_deliveries'] > 0 && $greetingStats['overdue_invoices'] > 0)
                        dan
                    @endif
                    @if ($greetingStats['overdue_invoices'] > 0)
                        <strong class="dash-greeting__strong">{{ $greetingStats['overdue_invoices'] }} tagihan</strong> jatuh tempo
                    @endif
                    @if ($greetingStats['pending_deliveries'] == 0 && $greetingStats['overdue_invoices'] == 0)
                        Semua berjalan lancar. Tidak ada tugas yang mendesak.
                    @endif
                </div>
            </div>
        </div>

        {{-- KPI row --}}
        <div class="dash-kpi-grid">
            <x-misc.kpi-card label="Pendapatan" :value="fmt_rp($kpis['pendapatan']['value'])" :raw-value="$kpis['pendapatan']['value']" :delta="$kpis['pendapatan']['delta']" :sparkline="$kpis['pendapatan']['sparkline']"
                :accent="true" />
            <x-misc.kpi-card label="Pengeluaran" :value="fmt_rp($kpis['pengeluaran']['value'])" :raw-value="$kpis['pengeluaran']['value']" :delta="$kpis['pengeluaran']['delta']" :sparkline="$kpis['pengeluaran']['sparkline']" />
            <x-misc.kpi-card label="Laba Bersih" :value="fmt_rp($kpis['laba']['value'])" :raw-value="$kpis['laba']['value']" :delta="$kpis['laba']['delta']" :sparkline="$kpis['laba']['sparkline']" />
            <x-misc.kpi-card label="Saldo Kas" :value="fmt_rp($kpis['kas']['value'])" :raw-value="$kpis['kas']['value']" :delta="$kpis['kas']['delta']" :sparkline="$kpis['kas']['sparkline']" />
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
                {{-- CSS bar chart with tooltips --}}
                <div class="bc" data-chart>
                    {{-- Y-axis grid --}}
                    <div class="bc__yaxis">
                        @foreach ([1, 0.75, 0.5, 0.25, 0] as $t)
                            <div class="bc__yaxis-row" style="bottom:{{ $t * 100 }}%">
                                <span class="bc__yaxis-label mono">{{ fmt_rp_short($maxVal * $t * 1_000_000) }}</span>
                                <span class="bc__yaxis-line"></span>
                            </div>
                        @endforeach
                    </div>
                    {{-- Bars --}}
                    <div class="bc__bars">
                        @foreach ($monthly as $i => $d)
                            @php
                                $pct1 = $maxVal > 0 ? round(($d[1] / $maxVal) * 100, 2) : 0;
                                $pct2 = $maxVal > 0 ? round(($d[2] / $maxVal) * 100, 2) : 0;
                            @endphp
                            <div class="bc__group" data-idx="{{ $i }}"
                                data-month="{{ $d[0] }}"
                                data-sales="{{ $d[1] }}"
                                data-purchase="{{ $d[2] }}">
                                <div class="bc__hover-label">
                                    <span class="bc__hover-label-val mono" style="color:var(--accent)">{{ fmt_rp_short($d[1] * 1_000_000) }}</span>
                                    <span class="bc__hover-label-val mono" style="color:var(--ink-3)">{{ fmt_rp_short($d[2] * 1_000_000) }}</span>
                                </div>
                                <div class="bc__bars-inner">
                                    <div class="bc__bar bc__bar--sales" style="--h:{{ $pct1 }}%; --delay:{{ $i * 40 }}ms"></div>
                                    <div class="bc__bar bc__bar--purchase" style="--h:{{ $pct2 }}%; --delay:{{ $i * 40 + 20 }}ms"></div>
                                </div>
                                <div class="bc__group-label mono">{{ $d[0] }}</div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Tooltip --}}
                    <div class="bc__tooltip" data-tooltip>
                        <div class="bc__tooltip-head" data-tooltip-head></div>
                        <div class="bc__tooltip-row bc__tooltip-row--sales">
                            <span class="bc__tooltip-dot" style="background:var(--accent)"></span>
                            <span class="bc__tooltip-name">Penjualan</span>
                            <span class="bc__tooltip-val mono" data-tooltip-sales></span>
                        </div>
                        <div class="bc__tooltip-row bc__tooltip-row--purchase">
                            <span class="bc__tooltip-dot" style="background:var(--ink-2)"></span>
                            <span class="bc__tooltip-name">Pembelian</span>
                            <span class="bc__tooltip-val mono" data-tooltip-purchase></span>
                        </div>
                        <div class="bc__tooltip-row bc__tooltip-row--diff">
                            <span class="bc__tooltip-dot" style="background:transparent"></span>
                            <span class="bc__tooltip-name">Selisih</span>
                            <span class="bc__tooltip-val mono" data-tooltip-diff></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pipeline --}}
            <div class="card card-chart">
                <x-misc.section-title title="Pipeline Penjualan" subtitle="Bulan berjalan" />
                <div class="pipeline-grid">
                    @foreach ($pipeline as $i => $s)
                        @php $w = $pipelineMax > 0 ? round(($s['value'] / $pipelineMax) * 100) : 0; @endphp
                        <div class="pipeline-row">
                            <div>
                                <div class="pipeline-stage-header">{{ $s['stage'] }}</div>
                                <div class="pipeline-stage-label mono">{{ $s['count'] }} dokumen
                                </div>
                            </div>
                            <div class="pipeline-bar-track">
                                <div class="pipeline-bar-fill" style="width:{{ $w }}%; background:{{ $s['color'] }}; --delay:{{ $i * 100 }}ms;">
                                </div>
                            </div>
                            <div class="num pipeline-value" style="--delay:{{ $i * 100 }}ms;">
                                {{ fmt_rp_short($s['value']) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Bottom section: Recent Activities + Overdue + Top Contacts --}}
        <div class="dash-bottom">

            {{-- Recent Activities --}}
            <div class="card card-chart dash-activity">
                <x-misc.section-title title="Aktivitas Terbaru" subtitle="10 transaksi terakhir" />
                <div class="dash-activity__list">
                    @forelse ($recentActivities as $act)
                        <a href="{{ $act['url'] }}" class="dash-activity__item">
                            <div class="dash-activity__icon" style="background:{{ $act['color'] }}20; color:{{ $act['color'] }};">
                                <x-misc.icon :name="$act['icon']" :size="14" />
                            </div>
                            <div class="dash-activity__info">
                                <div class="dash-activity__label">{{ $act['label'] }}</div>
                                <div class="dash-activity__number mono">{{ $act['number'] }}</div>
                            </div>
                            <div class="dash-activity__meta">
                                <div class="dash-activity__amount num">{{ fmt_rp($act['amount']) }}</div>
                                <div class="dash-activity__date">{{ $act['date']->format('d M Y') }}</div>
                            </div>
                            <x-misc.status-badge :status="$act['status']" />
                        </a>
                    @empty
                        <div class="dash-empty">
                            <x-misc.icon name="clipboard" :size="20" stroke="var(--ink-4)" />
                            <span>Belum ada aktivitas</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right column: Overdue + Top Contacts --}}
            <div class="dash-bottom-right">

                {{-- Overdue Invoices --}}
                <div class="card card-chart dash-overdue" x-data="{
                    page: 1,
                    perPage: 5,
                    get total() { return {{ count($overdueInvoices) }} },
                    get totalPages() { return Math.ceil(this.total / this.perPage) },
                    get items() {
                        const el = this.$refs.overdueList;
                        if (!el) return [];
                        return Array.from(el.children);
                    },
                    update() {
                        this.items.forEach((item, i) => {
                            const start = (this.page - 1) * this.perPage;
                            item.style.display = (i >= start && i < start + this.perPage) ? '' : 'none';
                        });
                    },
                    init() { this.$nextTick(() => this.update()) },
                    next() { if (this.page < this.totalPages) { this.page++; this.update() } },
                    prev() { if (this.page > 1) { this.page--; this.update() } }
                }">
                    <x-misc.section-title title="Tagihan Jatuh Tempo" :subtitle="count($overdueInvoices) . ' tagihan terlambat'" />
                    @if (count($overdueInvoices) > 0)
                        <div class="dash-overdue__list" x-ref="overdueList">
                            @foreach ($overdueInvoices as $inv)
                                <a href="{{ $inv['url'] }}" class="dash-overdue__item">
                                    <div class="dash-overdue__info">
                                        <div class="dash-overdue__contact">{{ $inv['contact'] }}</div>
                                        <div class="dash-overdue__number mono">{{ $inv['number'] }}</div>
                                    </div>
                                    <div class="dash-overdue__meta">
                                        <div class="dash-overdue__amount num">{{ fmt_rp($inv['amount']) }}</div>
                                        <div class="dash-overdue__days" style="color:var(--bad);">
                                            {{ $inv['days'] }} hari
                                        </div>
                                    </div>
                                    <span class="chip chip-bad" style="font-size:10px;">{{ $inv['type'] }}</span>
                                </a>
                            @endforeach
                        </div>
                        @if (count($overdueInvoices) > 5)
                            <div class="dash-pagination">
                                <button class="dash-pagination__btn" :disabled="page <= 1" @click="prev()">
                                    <x-misc.icon name="chev-left" :size="14" />
                                </button>
                                <span class="dash-pagination__info mono" x-text="page + ' / ' + totalPages"></span>
                                <button class="dash-pagination__btn" :disabled="page >= totalPages" @click="next()">
                                    <x-misc.icon name="chev-right" :size="14" />
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="dash-empty">
                            <x-misc.icon name="check" :size="20" stroke="var(--good)" />
                            <span style="color:var(--good);">Semua tagihan tepat waktu</span>
                        </div>
                    @endif
                </div>

                {{-- Top Contacts --}}
                <div class="card card-chart dash-top-contacts">
                    <x-misc.section-title title="Kontak Teratas" subtitle="Berdasarkan total transaksi" />
                    <div class="dash-top-contacts__tabs" x-data="{
                        tab: 'customers',
                        custPage: 1,
                        vendPage: 1,
                        perPage: 5,
                        custTotal: {{ count($topContacts['customers']) }},
                        vendTotal: {{ count($topContacts['vendors']) }},
                        get custTotalPages() { return Math.ceil(this.custTotal / this.perPage) },
                        get vendTotalPages() { return Math.ceil(this.vendTotal / this.perPage) },
                        get custItems() {
                            const el = this.$refs.custList;
                            if (!el) return [];
                            return Array.from(el.children);
                        },
                        get vendItems() {
                            const el = this.$refs.vendList;
                            if (!el) return [];
                            return Array.from(el.children);
                        },
                        updateCust() {
                            this.custItems.forEach((item, i) => {
                                const start = (this.custPage - 1) * this.perPage;
                                item.style.display = (i >= start && i < start + this.perPage) ? '' : 'none';
                            });
                        },
                        updateVend() {
                            this.vendItems.forEach((item, i) => {
                                const start = (this.vendPage - 1) * this.perPage;
                                item.style.display = (i >= start && i < start + this.perPage) ? '' : 'none';
                            });
                        },
                        init() { this.$nextTick(() => { this.updateCust(); this.updateVend() }) },
                        switchTab(t) { this.tab = t; this.$nextTick(() => { this.updateCust(); this.updateVend() }) },
                        custNext() { if (this.custPage < this.custTotalPages) { this.custPage++; this.updateCust() } },
                        custPrev() { if (this.custPage > 1) { this.custPage--; this.updateCust() } },
                        vendNext() { if (this.vendPage < this.vendTotalPages) { this.vendPage++; this.updateVend() } },
                        vendPrev() { if (this.vendPage > 1) { this.vendPage--; this.updateVend() } }
                    }">
                        <div class="dash-top-contacts__tab-bar">
                            <button class="dash-top-contacts__tab" :class="tab === 'customers' ? 'dash-top-contacts__tab--active' : ''"
                                @click="switchTab('customers')">Customer</button>
                            <button class="dash-top-contacts__tab" :class="tab === 'vendors' ? 'dash-top-contacts__tab--active' : ''"
                                @click="switchTab('vendors')">Vendor</button>
                        </div>
                        <div x-show="tab === 'customers'">
                            <div class="dash-top-contacts__list" x-ref="custList">
                                @forelse ($topContacts['customers'] as $i => $c)
                                    <div class="dash-top-contacts__item">
                                        <div class="dash-top-contacts__rank">{{ $i + 1 }}</div>
                                        <div class="dash-top-contacts__info">
                                            <div class="dash-top-contacts__name">{{ $c['name'] }}</div>
                                            <div class="dash-top-contacts__count">{{ $c['count'] }} transaksi</div>
                                        </div>
                                        <div class="dash-top-contacts__total num">{{ fmt_rp_short($c['total']) }}</div>
                                    </div>
                                @empty
                                    <div class="dash-empty">
                                        <span>Belum ada data</span>
                                    </div>
                                @endforelse
                            </div>
                            @if (count($topContacts['customers']) > 5)
                                <div class="dash-pagination">
                                    <button class="dash-pagination__btn" :disabled="custPage <= 1" @click="custPrev()">
                                        <x-misc.icon name="chev-left" :size="14" />
                                    </button>
                                    <span class="dash-pagination__info mono" x-text="custPage + ' / ' + custTotalPages"></span>
                                    <button class="dash-pagination__btn" :disabled="custPage >= custTotalPages" @click="custNext()">
                                        <x-misc.icon name="chev-right" :size="14" />
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div x-show="tab === 'vendors'">
                            <div class="dash-top-contacts__list" x-ref="vendList">
                                @forelse ($topContacts['vendors'] as $i => $c)
                                    <div class="dash-top-contacts__item">
                                        <div class="dash-top-contacts__rank">{{ $i + 1 }}</div>
                                        <div class="dash-top-contacts__info">
                                            <div class="dash-top-contacts__name">{{ $c['name'] }}</div>
                                            <div class="dash-top-contacts__count">{{ $c['count'] }} transaksi</div>
                                        </div>
                                        <div class="dash-top-contacts__total num">{{ fmt_rp_short($c['total']) }}</div>
                                    </div>
                                @empty
                                    <div class="dash-empty">
                                        <span>Belum ada data</span>
                                    </div>
                                @endforelse
                            </div>
                            @if (count($topContacts['vendors']) > 5)
                                <div class="dash-pagination">
                                    <button class="dash-pagination__btn" :disabled="vendPage <= 1" @click="vendPrev()">
                                        <x-misc.icon name="chev-left" :size="14" />
                                    </button>
                                    <span class="dash-pagination__info mono" x-text="vendPage + ' / ' + vendTotalPages"></span>
                                    <button class="dash-pagination__btn" :disabled="vendPage >= vendTotalPages" @click="vendNext()">
                                        <x-misc.icon name="chev-right" :size="14" />
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function() {
                    // ── Greeting & Date ──
                    const dateEl = document.querySelector('[data-dashboard-date]');
                    const greetingEl = document.querySelector('[data-dashboard-greeting]');

                    const updateGreeting = () => {
                        if (!greetingEl) return;
                        const hour = new Date().getHours();
                        let greeting = 'Selamat Malam';
                        if (hour < 11) greeting = 'Selamat Pagi';
                        else if (hour < 15) greeting = 'Selamat Siang';
                        else if (hour < 18) greeting = 'Selamat Sore';
                        greetingEl.textContent = greeting;
                    };

                    if (dateEl) {
                        const formatter = new Intl.DateTimeFormat('id-ID', {
                            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                        });
                        const updateDate = () => { dateEl.textContent = formatter.format(new Date()); };
                        updateGreeting();
                        updateDate();
                        setInterval(updateDate, 60 * 1000);
                        setInterval(updateGreeting, 60 * 1000);
                    }

                    // ── KPI Counting Animation ──
                    function animateCount(el, target, duration) {
                        const start = performance.now();
                        const valueEl = el.querySelector('[data-count-value]');
                        if (!valueEl) return;

                        function formatRp(n) {
                            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
                        }

                        function step(now) {
                            const elapsed = now - start;
                            const progress = Math.min(elapsed / duration, 1);
                            // easeOutExpo for a satisfying deceleration
                            const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                            const current = target * eased;
                            valueEl.textContent = formatRp(current);

                            if (progress < 1) {
                                requestAnimationFrame(step);
                            }
                        }

                        requestAnimationFrame(step);
                    }

                    document.querySelectorAll('[data-count-target]').forEach((el, i) => {
                        const target = parseFloat(el.dataset.countTarget);
                        if (!isNaN(target) && target !== 0) {
                            setTimeout(() => animateCount(el, target, 1200), i * 150);
                        }
                    });

                    // ── Bar Chart Tooltip ──
                    const chart = document.querySelector('[data-chart]');
                    if (!chart) return;

                    const tooltip = chart.querySelector('[data-tooltip]');
                    const groups = chart.querySelectorAll('.bc__group');
                    let activeGroup = null;
                    let hideTimer = null;

                    function fmtRp(n) {
                        if (n >= 1e9) return 'Rp ' + (n / 1e9).toFixed(1).replace(/\.0$/, '') + ' M';
                        if (n >= 1e6) return 'Rp ' + (n / 1e6).toFixed(1).replace(/\.0$/, '') + ' jt';
                        if (n >= 1e3) return 'Rp ' + (n / 1e3).toFixed(0) + ' rb';
                        return 'Rp ' + n.toLocaleString('id-ID');
                    }

                    function showTooltip(group) {
                        clearTimeout(hideTimer);
                        if (activeGroup === group) return;
                        activeGroup = group;

                        const month = group.dataset.month;
                        const sales = parseInt(group.dataset.sales) * 1e6;
                        const purchase = parseInt(group.dataset.purchase) * 1e6;
                        const diff = sales - purchase;

                        tooltip.querySelector('[data-tooltip-head]').textContent = month;
                        tooltip.querySelector('[data-tooltip-sales]').textContent = fmtRp(sales);
                        tooltip.querySelector('[data-tooltip-purchase]').textContent = fmtRp(purchase);

                        const diffEl = tooltip.querySelector('[data-tooltip-diff]');
                        diffEl.textContent = (diff >= 0 ? '+' : '') + fmtRp(Math.abs(diff));
                        diffEl.style.color = diff >= 0 ? 'var(--good)' : 'var(--bad)';

                        tooltip.classList.add('bc__tooltip--visible');

                        // Position
                        const groupRect = group.getBoundingClientRect();
                        const chartRect = chart.getBoundingClientRect();
                        const tipW = tooltip.offsetWidth;
                        const tipH = tooltip.offsetHeight;

                        let left = groupRect.left - chartRect.left + groupRect.width / 2 - tipW / 2;
                        let top = groupRect.top - chartRect.top - tipH - 12;

                        // Clamp horizontal
                        if (left < 4) left = 4;
                        if (left + tipW > chartRect.width - 4) left = chartRect.width - tipW - 4;

                        // If above viewport, show below
                        if (top < 0) {
                            top = groupRect.bottom - chartRect.top + 12;
                            tooltip.classList.add('bc__tooltip--below');
                        } else {
                            tooltip.classList.remove('bc__tooltip--below');
                        }

                        tooltip.style.left = left + 'px';
                        tooltip.style.top = top + 'px';

                        // Highlight group
                        groups.forEach(g => g.classList.remove('bc__group--active'));
                        group.classList.add('bc__group--active');
                    }

                    function hideTooltip() {
                        hideTimer = setTimeout(() => {
                            tooltip.classList.remove('bc__tooltip--visible');
                            activeGroup = null;
                            groups.forEach(g => g.classList.remove('bc__group--active'));
                        }, 120);
                    }

                    groups.forEach(group => {
                        group.addEventListener('mouseenter', () => showTooltip(group));
                        group.addEventListener('mouseleave', hideTooltip);
                        group.addEventListener('touchstart', (e) => {
                            e.preventDefault();
                            showTooltip(group);
                        }, { passive: false });
                    });

                    // Hide on outside click
                    document.addEventListener('touchstart', (e) => {
                        if (!chart.contains(e.target)) hideTooltip();
                    });
                })();
            </script>
        @endpush
    @endonce
@endsection
