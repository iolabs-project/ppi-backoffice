{{-- =================== NERACA =================== --}}
<div x-data="balanceSheetModule()">
    <div class="card-hd-2" style="margin-bottom:12px;">
        <div class="display card-hd-title">Neraca</div>
        <div class="order-actions" style="display:flex; align-items:center; gap:8px;">
            <label style="font-size:12px; color:var(--ink-3);">Per Tanggal</label>
            <input type="date" class="filter-panel__input" x-model="filter.as_of_date"
                x-on:change="fetchData()" style="height:28px; font-size:12px;">
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="print" :size="13" />Cetak</button>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
        </div>
    </div>
<div class="neraca-grid">
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
