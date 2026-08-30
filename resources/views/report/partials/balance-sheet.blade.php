{{-- =================== NERACA =================== --}}
<div class="neraca-grid" x-data="balanceSheetModule()">
    {{-- Aset --}}
    <div class="card" style="overflow:hidden;">
        <div class="neraca-card-hd">
            <div class="display" style="font-weight:700; font-size:14px;">Aset</div>
            <div class="num" style="font-weight:700; color:var(--accent);" x-text="formatCurrency(tableData.asset.total)"></div>
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
                    <template x-for="account in tableData.asset.accounts" :key="account.account_id">
                        <tr>
                            <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;"
                                x-text="account.account_code"></td>
                            <td style="font-size:13px;" x-text="account.account_name"></td>
                            <td class="num" style="text-align:right; font-weight:600; font-size:13px;"
                                x-text="formatCurrency(account.balance)"></td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Liabilitas + Ekuitas --}}
    <div class="neraca-side">
        <div class="card" style="overflow:hidden;">
            <div class="neraca-card-hd">
                <div class="display" style="font-weight:700; font-size:14px;">Liabilitas</div>
                <div class="num" style="font-weight:700; color:var(--bad);" x-text="formatCurrency(tableData.liability.total)"></div>
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
                        <template x-for="account in tableData.liability.accounts" :key="account.account_id">
                            <tr>
                                <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;"
                                    x-text="account.account_code"></td>
                                <td style="font-size:13px;" x-text="account.account_name"></td>
                                <td class="num" style="text-align:right; font-weight:600; font-size:13px;"
                                    x-text="formatCurrency(account.balance)"></td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="card" style="overflow:hidden;">
            <div class="neraca-card-hd">
                <div class="display" style="font-weight:700; font-size:14px;">Ekuitas</div>
                <div class="num" style="font-weight:700; color:var(--good);" x-text="formatCurrency(tableData.equity.total)"></div>
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
                        <template x-for="account in tableData.equity.accounts"
                            :key="account.account_id ?? account.account_name">
                            <tr>
                                <td class="mono" style="font-size:11.5px; color:var(--ink-4); width:80px;"
                                    x-text="account.account_code"></td>
                                <td style="font-size:13px;" x-text="account.account_name"></td>
                                <td class="num" style="text-align:right; font-weight:600; font-size:13px;"
                                    x-text="formatCurrency(account.balance)"></td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="card neraca-total-row">
            <span style="font-size:13px; font-weight:600;">Total Liabilitas + Ekuitas</span>
            <span class="num" style="font-size:16px; font-weight:700; color:var(--accent);"
                x-text="formatCurrency(tableData.total_liabilities_and_equity)"></span>
        </div>
    </div>
</div>

@push('balance-sheet-scripts')
    <script>
        function balanceSheetModule() {
            return {
                tableData: {
                    as_of_date: null,
                    asset: {
                        accounts: [],
                        total: 0,
                    },
                    liability: {
                        accounts: [],
                        total: 0,
                    },
                    equity: {
                        accounts: [],
                        total: 0,
                    },
                    total_liabilities_and_equity: 0,
                },
                filter: {
                    search: '',
                    as_of_date: '{{ now()->format('Y-m-d') }}',
                },
                loading: false,

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

                async init() {
                    this.loading = true;
                    await this.fetchData();
                    this.loading = false;
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('reports.balance_sheet.datatable'), {
                            params: {
                                search: this.filter.search,
                                as_of_date: this.filter.as_of_date,
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
