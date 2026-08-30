{{-- =================== PIUTANG =================== --}}
<div class="card" style="overflow:hidden;" x-data="receivableModule()">
    <div class="utang-card-hd">
        <div class="display" style="font-weight:700; font-size:14px;">Piutang Dagang</div>
        <span style="font-size:12px; color:var(--ink-4);" x-text="`${tableData.invoices.length} tagihan`"></span>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Klien</th>
                <th>Ref</th>
                <th>Jatuh Tempo</th>
                <th style="text-align:right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr>
                    <td colspan="4" style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...</td>
                </tr>
            </template>
            <template x-if="!loading && tableData.invoices.length === 0">
                <tr>
                    <td colspan="4" style="text-align:center; color:var(--ink-3); padding:20px;">Tidak ada piutang
                        tertunggak</td>
                </tr>
            </template>
            <template x-if="!loading">
                <template x-for="invoice in tableData.invoices" :key="invoice.id">
                    <tr>
                        <td style="font-weight:500; font-size:13px;" x-text="invoice.contact_name"></td>
                        <td class="mono" style="font-size:11.5px; color:var(--ink-4);" x-text="invoice.number"></td>
                        <td style="font-size:12.5px;"
                            :style="`color:${invoice.days_overdue > 14 ? 'var(--bad)' : 'var(--ink-3)'}`"
                            x-text="formatDueDate(invoice.due_date)"></td>
                        <td class="num" style="text-align:right; font-weight:600;"
                            x-text="formatCurrency(invoice.amount)"></td>
                    </tr>
                </template>
            </template>
        </tbody>
    </table>
    <div class="utang-card-ft">
        <span style="font-size:13px; font-weight:600;">Total Piutang</span>
        <span class="num" style="font-weight:700; color:var(--good);" x-text="formatCurrency(tableData.total)"></span>
    </div>
</div>

@push('receivable-scripts')
    <script>
        function receivableModule() {
            return {
                tableData: {
                    invoices: [],
                    total: 0,
                },
                filter: {
                    search: '',
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

                formatDueDate(value) {
                    if (!value) {
                        return '-';
                    }

                    return new Date(value).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
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
                        const r = await axios.get(route('reports.receivable.datatable'), {
                            params: {
                                search: this.filter.search,
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
