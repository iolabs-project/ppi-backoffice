{{-- =================== LABA RUGI =================== --}}
<div class="labarugi-grid" x-data="profitLossModule()">
    <div class="labarugi-tables">
        <div class="card" style="overflow:hidden;">
            <div class="neraca-card-hd">
                <div class="display" style="font-weight:700; font-size:14px;">Laba & Rugi</div>
            </div>
            <table class="tbl">
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="3" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading">
                        <template x-for="row in reportRows()" :key="row.key">
                            <tr :style="row.rowStyle">
                                <template x-for="cell in row.cells" :key="cell.key">
                                    <td :colspan="cell.colspan" :class="cell.class" :style="cell.style"
                                        x-text="cell.text"></td>
                                </template>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="labarugi-summary">
        <div class="labarugi-summary-card card">
            <div class="label" style="margin-bottom:16px;">Ringkasan</div>
            <template x-for="item in summaryItems" :key="item.label">
                <div class="labarugi-summary-row"
                    :style="`font-size:${item.bold ? 15 : 13}px; font-weight:${item.bold ? 700 : 500}; ${item.bold ? 'border-top:1px solid var(--line); margin-top:6px; padding-top:14px;' : ''}`">
                    <span :style="`color:${item.bold ? 'var(--ink)' : 'var(--ink-3)'}`" x-text="item.label"></span>
                    <span class="num" :style="`color:${profitColor(item.value)}`"
                        x-text="formatCurrency(item.value)"></span>
                </div>
            </template>
        </div>
        <div class="card labarugi-summary-card" :style="`background:${netProfitBackground()};`">
            <div class="label" style="margin-bottom:6px;">Margin Bersih</div>
            <div class="display num"
                :style="`font-size:32px; font-weight:700; color:${profitColor(tableData.net_profit)};`"
                x-text="`${netProfitMargin()}%`"></div>
        </div>
    </div>
</div>

@push('profit-loss-scripts')
    <script>
        function profitLossModule() {
            return {
                tableData: {
                    revenue: {
                        accounts: [],
                        total: 0,
                    },
                    cogs: {
                        accounts: [],
                        total: 0,
                    },
                    gross_profit: 0,
                    operating_expense: {
                        accounts: [],
                        total: 0,
                    },
                    operating_profit: 0,
                    other_income: {
                        accounts: [],
                        total: 0,
                    },
                    other_expense: {
                        accounts: [],
                        total: 0,
                    },
                    net_profit: 0,
                },
                filter: {
                    search: '',
                    start_date: '{{ now()->startOfMonth()->format('Y-m-d') }}',
                    end_date: '{{ now()->endOfMonth()->format('Y-m-d') }}',
                },
                sectionGroups: [{
                        key: 'profit',
                        label: 'Profit',
                        totalLabel: 'Total Profit',
                        color: 'var(--good)',
                        sections: [{
                                key: 'revenue',
                                label: 'Pendapatan',
                                subtotalLabel: 'Subtotal Pendapatan',
                                color: 'var(--good)',
                            },
                            {
                                key: 'other_income',
                                label: 'Pendapatan Lain-lain',
                                subtotalLabel: 'Subtotal Pendapatan Lain-lain',
                                color: 'var(--good)',
                            },
                        ],
                    },
                    {
                        key: 'loss',
                        label: 'Loss',
                        totalLabel: 'Total Loss',
                        color: 'var(--bad)',
                        sections: [{
                                key: 'cogs',
                                label: 'Harga Pokok Penjualan',
                                subtotalLabel: 'Subtotal Harga Pokok Penjualan',
                                color: 'var(--bad)',
                            },
                            {
                                key: 'operating_expense',
                                label: 'Beban Operasional',
                                subtotalLabel: 'Subtotal Beban Operasional',
                                color: 'var(--bad)',
                            },
                            {
                                key: 'other_expense',
                                label: 'Beban Lain-lain',
                                subtotalLabel: 'Subtotal Beban Lain-lain',
                                color: 'var(--bad)',
                            },
                        ],
                    },
                ],
                loading: false,

                get summaryItems() {
                    return [{
                            label: 'Pendapatan',
                            value: this.sectionTotal('revenue'),
                            bold: false,
                        },
                        {
                            label: 'Beban',
                            value: this.totalExpenses(),
                            bold: false,
                        },
                        {
                            label: 'Laba Bersih',
                            value: this.tableData.net_profit ?? 0,
                            bold: true,
                        },
                    ];
                },

                sectionData(key) {
                    return this.tableData[key] ?? {
                        accounts: [],
                        total: 0,
                    };
                },

                sectionAccounts(key) {
                    return this.sectionData(key).accounts ?? [];
                },

                sectionTotal(key) {
                    return Number(this.sectionData(key).total ?? 0);
                },

                sectionHeaderCells(label) {
                    return [{
                        key: 'label',
                        text: label,
                        colspan: 3,
                        style: 'font-size:13px; padding-left:16px;',
                    }];
                },

                accountCells(account, color) {
                    return [{
                            key: 'code',
                            text: account.account_code,
                            class: 'mono',
                            style: 'font-size:11.5px; color:var(--ink-4); width:80px;',
                        },
                        {
                            key: 'name',
                            text: account.account_name,
                            style: 'font-size:13px;',
                        },
                        {
                            key: 'value',
                            text: this.formatCurrency(account.balance),
                            class: 'num',
                            style: `text-align:right; font-weight:600; font-size:13px; color:${color}`,
                        },
                    ];
                },

                totalCells(label, value, color) {
                    return [{
                            key: 'label',
                            text: label,
                            colspan: 2,
                            style: 'font-size:13px; padding-left:16px;',
                        },
                        {
                            key: 'value',
                            text: this.formatCurrency(value),
                            class: 'num',
                            style: `text-align:right; color:${color}`,
                        },
                    ];
                },

                resultRow(key, label, value) {
                    return {
                        key,
                        rowStyle: 'background:var(--bg-1); font-weight:700;',
                        cells: this.totalCells(label, value, this.profitColor(value)),
                    };
                },

                reportRows() {
                    const rows = this.sectionGroups.flatMap((group) => {
                        const groupRows = [{
                            key: `${group.key}-group`,
                            rowStyle: 'background:var(--bg-1); font-weight:700;',
                            cells: this.sectionHeaderCells(group.label),
                        }, ];

                        group.sections.forEach((section) => {
                            groupRows.push({
                                key: `${group.key}-${section.key}-section`,
                                rowStyle: 'background:var(--bg-2); font-weight:700;',
                                cells: this.sectionHeaderCells(section.label),
                            });

                            this.sectionAccounts(section.key).forEach((account, accountIndex) => {
                                groupRows.push({
                                    key: account.account_id ??
                                        `${section.key}-account-${accountIndex}`,
                                    rowStyle: '',
                                    cells: this.accountCells(account, section.color),
                                });
                            });

                            groupRows.push({
                                key: `${group.key}-${section.key}-subtotal`,
                                rowStyle: 'background:var(--bg-2); font-weight:700;',
                                cells: this.totalCells(section.subtotalLabel, this.sectionTotal(section.key),
                                    section.color),
                            });
                        });

                        groupRows.push({
                            key: `${group.key}-total`,
                            rowStyle: 'background:var(--bg-1); font-weight:700;',
                            cells: this.totalCells(group.totalLabel, this.groupTotal(group.key), group.color),
                        });

                        return groupRows;
                    });

                    rows.push(this.resultRow('gross-profit-result', 'Laba Kotor', this.tableData.gross_profit));
                    rows.push(this.resultRow('operating-profit-result', 'Laba Operasional', this.tableData
                        .operating_profit));
                    rows.push(this.resultRow('net-profit-result', 'Laba Bersih', this.tableData.net_profit));

                    return rows;
                },

                groupTotal(groupKey) {
                    const group = this.sectionGroups.find((item) => item.key === groupKey);

                    if (!group) {
                        return 0;
                    }

                    return group.sections.reduce((total, section) => total + this.sectionTotal(section.key), 0);
                },

                totalExpenses() {
                    return this.groupTotal('loss');
                },

                formatCurrency(value) {
                    const amount = Number(value ?? 0);

                    return amount >= 0 ?
                        amount.toLocaleString('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }) :
                        '-' + Math.abs(amount).toLocaleString('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        });
                },

                profitColor(value) {
                    return Number(value ?? 0) >= 0 ? 'var(--good)' : 'var(--bad)';
                },

                netProfitBackground() {
                    return Number(this.tableData.net_profit ?? 0) >= 0 ?
                        'var(--good-bg, #f0faf0)' :
                        'var(--bad-bg, #fff0f0)';
                },

                netProfitMargin() {
                    const revenue = this.sectionTotal('revenue');

                    if (revenue <= 0) {
                        return 0;
                    }

                    return ((Number(this.tableData.net_profit ?? 0) / revenue) * 100).toFixed(1);
                },

                async init() {
                    // Any initialization logic can go here
                    this.loading = true;
                    await this.fetchData();
                    this.loading = false;
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('reports.profit_loss.datatable'), {
                            params: {
                                search: this.filter.search,
                                start_date: this.filter.start_date,
                                end_date: this.filter.end_date
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
