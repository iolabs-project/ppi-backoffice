{{-- =================== EKSEKUTIF =================== --}}
<div class="card" style="overflow:hidden;" x-data="executiveModule()">
    <template x-if="loading">
        <div style="text-align:center; color:var(--ink-3); padding:32px;">Memuat data...</div>
    </template>
    <template x-if="!loading">
        <div>
            <template x-for="section in sections" :key="section.title">
                <div>
                    <div style="background:var(--bg-2); padding:10px 20px; font-weight:700; font-size:13px;"
                        x-text="section.title"></div>
                    <template x-for="row in section.rows" :key="row.label">
                        <div
                            style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid var(--line);">
                            <span style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--ink-2);">
                                <span x-text="row.label"></span>
                                <template x-if="row.tooltip">
                                    <span :title="row.tooltip" style="display:inline-flex; color:var(--ink-4);">
                                        <x-misc.icon name="help" :size="12" />
                                    </span>
                                </template>
                            </span>
                            <span class="num" style="font-size:13px; font-weight:600;" :style="`color:${row.color}`"
                                x-text="row.value"></span>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </template>
</div>

@push('executive-scripts')
    <script>
        function executiveModule() {
            return {
                tableData: {
                    cash: {
                        cash_in: 0,
                        cash_out: 0,
                        cash_change: 0,
                        closing_balance: 0,
                    },
                    profitability: {
                        revenue: 0,
                        cost_of_goods_sold: 0,
                        gross_profit: 0,
                        expenses: 0,
                        net_profit: 0,
                    },
                    balance_sheet: {
                        asset: 0,
                        liability: 0,
                        equity: 0,
                    },
                    revenue_stats: {
                        invoice_count: 0,
                        average_invoice: 0,
                    },
                    performance: {
                        gross_profit_margin: 0,
                        net_profit_margin: 0,
                        roi_annualized: 0,
                    },
                    position: {
                        receivable_days: 0,
                        payable_days: 0,
                        debt_to_equity_ratio: 0,
                        asset_to_liability_ratio: 0,
                    },
                },
                filter: {
                    start_date: '{{ now()->startOfMonth()->format('Y-m-d') }}',
                    end_date: '{{ now()->endOfMonth()->format('Y-m-d') }}',
                },
                loading: false,

                get sections() {
                    const d = this.tableData;
                    const amountColor = 'var(--info)';

                    return [{
                            title: 'Kas',
                            rows: [{
                                    label: 'Kas masuk',
                                    value: this.formatAmount(d.cash.cash_in),
                                    color: amountColor,
                                },
                                {
                                    label: 'Kas keluar',
                                    value: this.formatAmount(d.cash.cash_out),
                                    color: amountColor,
                                },
                                {
                                    label: 'Perubahan kas',
                                    value: this.formatAmount(d.cash.cash_change),
                                    color: amountColor,
                                },
                                {
                                    label: 'Saldo penutupan',
                                    value: this.formatAmount(d.cash.closing_balance),
                                    color: amountColor,
                                },
                            ],
                        },
                        {
                            title: 'Profitabilitas',
                            rows: [{
                                    label: 'Pendapatan',
                                    value: this.formatAmount(d.profitability.revenue),
                                    color: amountColor,
                                },
                                {
                                    label: 'Biaya penjualan',
                                    value: this.formatAmount(d.profitability.cost_of_goods_sold),
                                    color: amountColor,
                                },
                                {
                                    label: 'Laba kotor',
                                    value: this.formatAmount(d.profitability.gross_profit),
                                    color: amountColor,
                                },
                                {
                                    label: 'Biaya',
                                    value: this.formatAmount(d.profitability.expenses),
                                    color: amountColor,
                                },
                                {
                                    label: 'Laba bersih',
                                    value: this.formatAmount(d.profitability.net_profit),
                                    color: amountColor,
                                },
                            ],
                        },
                        {
                            title: 'Neraca',
                            rows: [{
                                    label: 'Aset',
                                    value: this.formatAmount(d.balance_sheet.asset),
                                    color: amountColor,
                                },
                                {
                                    label: 'Liabilitas',
                                    value: this.formatAmount(d.balance_sheet.liability),
                                    color: amountColor,
                                },
                                {
                                    label: 'Modal pemilik',
                                    value: this.formatAmount(d.balance_sheet.equity),
                                    color: amountColor,
                                },
                            ],
                        },
                        {
                            title: 'Pendapatan',
                            rows: [{
                                    label: 'Jumlah tagihan diterbitkan',
                                    value: this.formatNumber(d.revenue_stats.invoice_count),
                                    color: amountColor,
                                    tooltip: 'Jumlah tagihan penjualan yang diterbitkan pada periode ini.',
                                },
                                {
                                    label: 'Rata-rata nilai tagihan',
                                    value: this.formatAmount(d.revenue_stats.average_invoice),
                                    color: amountColor,
                                    tooltip: 'Rata-rata nilai tagihan penjualan yang diterbitkan pada periode ini.',
                                },
                            ],
                        },
                        {
                            title: 'Performa',
                            rows: [{
                                    label: 'Margin laba kotor',
                                    value: this.formatPercent(d.performance.gross_profit_margin),
                                    color: 'var(--ink)',
                                    tooltip: 'Laba kotor dibagi pendapatan.',
                                },
                                {
                                    label: 'Margin laba bersih',
                                    value: this.formatPercent(d.performance.net_profit_margin),
                                    color: 'var(--ink)',
                                    tooltip: 'Laba bersih dibagi pendapatan.',
                                },
                                {
                                    label: 'Pengembalian investasi / ROI (p.a.)',
                                    value: this.formatPercent(d.performance.roi_annualized),
                                    color: 'var(--ink)',
                                    tooltip: 'Laba bersih dibagi total aset, disetahunkan menjadi basis per tahun.',
                                },
                            ],
                        },
                        {
                            title: 'Posisi',
                            rows: [{
                                    label: 'Rata-rata lama konversi piutang',
                                    value: this.formatDays(d.position.receivable_days),
                                    color: 'var(--ink)',
                                    tooltip: 'Rata-rata jumlah hari untuk menagih piutang (Days Sales Outstanding).',
                                },
                                {
                                    label: 'Rata-rata lama konversi hutang',
                                    value: this.formatDays(d.position.payable_days),
                                    color: 'var(--ink)',
                                    tooltip: 'Rata-rata jumlah hari untuk membayar hutang (Days Payable Outstanding).',
                                },
                                {
                                    label: 'Rasio hutang terhadap ekuitas',
                                    value: this.formatRatio(d.position.debt_to_equity_ratio),
                                    color: 'var(--ink)',
                                    tooltip: 'Total liabilitas dibagi total ekuitas.',
                                },
                                {
                                    label: 'Rasio aset terhadap liabilitas',
                                    value: this.formatRatio(d.position.asset_to_liability_ratio),
                                    color: 'var(--ink)',
                                    tooltip: 'Total aset dibagi total liabilitas.',
                                },
                            ],
                        },
                    ];
                },

                formatAmount(value) {
                    return Math.round(Number(value ?? 0)).toLocaleString('id-ID');
                },

                formatNumber(value) {
                    return Number(value ?? 0).toLocaleString('id-ID');
                },

                formatPercent(value) {
                    return `${Number(value ?? 0).toFixed(1)} %`;
                },

                formatRatio(value) {
                    return Number(value ?? 0).toFixed(2);
                },

                formatDays(value) {
                    return Number(value ?? 0).toFixed(2);
                },

                async init() {
                    this.loading = true;
                    await this.fetchData();
                    this.loading = false;
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('reports.executive.datatable'), {
                            params: {
                                start_date: this.filter.start_date,
                                end_date: this.filter.end_date,
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
            }
        }
    </script>
@endpush
