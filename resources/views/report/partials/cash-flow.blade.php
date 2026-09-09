{{-- =================== ARUS KAS =================== --}}
<div x-data="cashFlowModule()">
    <div class="card-hd-2" style="margin-bottom:12px;">
        <div class="display card-hd-title">Arus Kas</div>
        <div class="order-actions" style="display:flex; align-items:center; gap:8px;">
            <label style="font-size:12px; color:var(--ink-3);">Dari</label>
            <input type="date" class="filter-panel__input" x-model="filter.start_date"
                x-on:change="fetchData()" style="height:28px; font-size:12px;">
            <label style="font-size:12px; color:var(--ink-3);">Sampai</label>
            <input type="date" class="filter-panel__input" x-model="filter.end_date"
                x-on:change="fetchData()" style="height:28px; font-size:12px;">
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="print" :size="13" />Cetak</button>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
        </div>
    </div>
<div class="card" style="overflow:hidden;">
    <template x-if="loading">
        <div style="text-align:center; color:var(--ink-3); padding:32px;">Memuat data...</div>
    </template>
    <template x-if="!loading">
        <div>
            <div style="background:var(--bg-2); padding:10px 20px; font-weight:700; font-size:13px;">Aktivitas
                Operasional</div>
            <template x-for="row in operatingRows" :key="row.label">
                <div :style="rowStyle(row)">
                    <span style="font-size:13px;" :style="`color:${row.bold ? 'var(--ink)' : 'var(--ink-2)'}`"
                        x-text="row.label"></span>
                    <span class="num" style="font-size:13px;"
                        :style="`color:${row.color}; font-weight:${row.bold ? 700 : 600};`"
                        x-text="formatAmount(row.value)"></span>
                </div>
            </template>

            <div style="background:var(--bg-2); padding:10px 20px; font-weight:700; font-size:13px;">Aktivitas
                Investasi</div>
            <template x-for="row in investingRows" :key="row.label">
                <div :style="rowStyle(row)">
                    <span style="font-size:13px;" :style="`color:${row.bold ? 'var(--ink)' : 'var(--ink-2)'}`"
                        x-text="row.label"></span>
                    <span class="num" style="font-size:13px;"
                        :style="`color:${row.color}; font-weight:${row.bold ? 700 : 600};`"
                        x-text="formatAmount(row.value)"></span>
                </div>
            </template>

            <div style="background:var(--bg-2); padding:10px 20px; font-weight:700; font-size:13px;">Aktivitas
                Pendanaan</div>
            <template x-for="row in financingRows" :key="row.label">
                <div :style="rowStyle(row)">
                    <span style="font-size:13px;" :style="`color:${row.bold ? 'var(--ink)' : 'var(--ink-2)'}`"
                        x-text="row.label"></span>
                    <span class="num" style="font-size:13px;"
                        :style="`color:${row.color}; font-weight:${row.bold ? 700 : 600};`"
                        x-text="formatAmount(row.value)"></span>
                </div>
            </template>

            <div
                style="background:var(--bg-2); padding:10px 20px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:13px;">Arus kas bersih</span>
                <span class="num" style="font-weight:700; font-size:13px;"
                    x-text="formatAmount(tableData.net_cash_flow)"></span>
            </div>

            <div style="background:var(--bg-2); padding:10px 20px; font-weight:700; font-size:13px;">Kas dan Setara
                Kas</div>
            <template x-for="row in cashRows" :key="row.label">
                <div :style="rowStyle(row)">
                    <span style="font-size:13px;" :style="`color:${row.bold ? 'var(--ink)' : 'var(--ink-2)'}`"
                        x-text="row.label"></span>
                    <span class="num" style="font-size:13px;"
                        :style="`color:${row.color}; font-weight:${row.bold ? 700 : 600};`"
                        x-text="formatAmount(row.value)"></span>
                </div>
            </template>
        </div>
    </template>
</div>
</div>

@push('cash-flow-scripts')
    <script>
        function cashFlowModule() {
            return {
                tableData: {
                    operating: {
                        lines: {},
                        total: 0,
                    },
                    investing: {
                        lines: {},
                        total: 0,
                    },
                    financing: {
                        lines: {},
                        total: 0,
                    },
                    net_cash_flow: 0,
                    opening_cash_balance: 0,
                    closing_cash_balance: 0,
                },
                filter: {
                    start_date: '{{ now()->startOfMonth()->format('Y-m-d') }}',
                    end_date: '{{ now()->endOfMonth()->format('Y-m-d') }}',
                },
                loading: false,

                get operatingRows() {
                    const l = this.tableData.operating.lines;

                    return [{
                            label: 'Penerimaan dari pelanggan',
                            value: l.receipts_from_customers,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Aset lancar lainnya',
                            value: l.other_current_assets,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Pembayaran ke pemasok',
                            value: l.payments_to_suppliers,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Kartu kredit dan liabilitas jangka pendek lainnya',
                            value: l.credit_card_and_other_current_liabilities,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Pendapatan lain-lain',
                            value: l.other_income,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Pembayaran biaya operasional',
                            value: l.operating_expense_payments,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Pembayaran kartu kredit',
                            value: l.credit_card_payments,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Arus kas bersih dari aktivitas operasional',
                            value: this.tableData.operating.total,
                            color: 'var(--ink)',
                            bold: true,
                        },
                    ];
                },

                get investingRows() {
                    const l = this.tableData.investing.lines;

                    return [{
                            label: 'Perolehan/pembelian aset',
                            value: l.asset_acquisition,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Aktivitas investasi lainnya',
                            value: l.other_investing,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Arus kas bersih dari aktivitas investasi',
                            value: this.tableData.investing.total,
                            color: 'var(--ink)',
                            bold: true,
                        },
                    ];
                },

                get financingRows() {
                    const l = this.tableData.financing.lines;

                    return [{
                            label: 'Liabilitas jangka panjang',
                            value: l.long_term_liabilities,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Modal pemilik',
                            value: l.owner_equity,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Arus kas bersih dari aktivitas pendanaan',
                            value: this.tableData.financing.total,
                            color: 'var(--ink)',
                            bold: true,
                        },
                    ];
                },

                get cashRows() {
                    return [{
                            label: 'Kas dan setara kas diawal periode',
                            value: this.tableData.opening_cash_balance,
                            color: 'var(--ink)',
                        },
                        {
                            label: 'Kas dan setara kas diakhir periode',
                            value: this.tableData.closing_cash_balance,
                            color: 'var(--info)',
                        },
                        {
                            label: 'Perubahan kas untuk periode',
                            value: this.tableData.net_cash_flow,
                            color: 'var(--ink)',
                        },
                    ];
                },

                rowStyle(row) {
                    const base = 'display:flex; justify-content:space-between; padding:10px 20px; border-bottom:1px solid var(--line);';

                    return row.bold ? `${base} border-top:1px solid var(--line);` : base;
                },

                formatAmount(value) {
                    return Math.round(Number(value ?? 0)).toLocaleString('id-ID');
                },

                async init() {
                    this.loading = true;
                    await this.fetchData();
                    this.loading = false;
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('reports.cash_flow.datatable'), {
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
