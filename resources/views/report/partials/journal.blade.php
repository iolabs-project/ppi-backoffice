{{-- =================== JURNAL UMUM =================== --}}
<div class="card" style="overflow:hidden;" x-data="journalModule()" x-init="init()">
    <div class="card-hd">
        <div class="display card-hd-title">Jurnal Umum</div>
        <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
    </div>
    <table class="tbl tbl-journal">
        <thead>
            <tr>
                <th style="width:100px;">Tanggal</th>
                <th style="width:130px;">No. Jurnal</th>
                <th>Keterangan</th>
                <th>Akun</th>
                <th style="text-align:right;">Debit</th>
                <th style="text-align:right;">Kredit</th>
            </tr>
        </thead>
        <template x-if="loading">
            <tbody>
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--ink-3); padding:32px;">
                        Memuat data...
                    </td>
                </tr>
            </tbody>
        </template>
        <template x-if="!loading && tableData.data.length === 0">
            <tbody>
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--ink-3); padding:32px;">
                        Tidak ada data
                    </td>
                </tr>
            </tbody>
        </template>
        <template x-if="!loading && tableData.data.length > 0">
            <template x-for="journal in tableData.data" :key="journal.id">
                <tbody>
                    <tr class="tbl-journal__group">
                        <td style="white-space:nowrap; font-size:12.5px; color:var(--ink-3); font-weight:600;"
                            x-text="journal.journal_date"></td>
                        <td class="mono" style="font-size:11.5px; color:var(--ink-4); font-weight:600;"
                            x-text="journal.number"></td>
                        <td style="font-size:13px; color:var(--ink-2); font-weight:600;" x-text="journal.description">
                        </td>
                        <td colspan="3"></td>
                    </tr>
                    <template x-for="entry in journal.items" :key="entry.id">
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td x-text="entry.account.name"
                                :style="entry.credit > 0 ? 'padding-left:30px; font-size:13px; color:var(--ink-3);' :
                                    'font-size:13px; font-weight:500; color:var(--ink-2);'">
                            </td>
                            <td class="num" style="text-align:right; font-size:13px; color:var(--ink-2);"
                                x-text="entry.debit > 0 ? m(entry.debit) : '—'"></td>
                            <td class="num" style="text-align:right; font-size:13px; color:var(--ink-2);"
                                x-text="entry.credit > 0 ? m(entry.credit) : '—'"></td>
                        </tr>
                    </template>
                    {{-- Subtotal --}}
                    <tr class="tbl-journal__subtotal">
                        <td colspan="3"></td>
                        <td
                            style="font-size:11.5px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; color:var(--ink-4); text-align:right;">
                            Subtotal</td>
                        <td class="num" style="text-align:right; font-weight:700; font-size:13px;"
                            x-text="m(journal.items.reduce((sum, entry) => sum + entry.debit, 0))"></td>
                        <td class="num" style="text-align:right; font-weight:700; font-size:13px;"
                            x-text="m(journal.items.reduce((sum, entry) => sum + entry.credit, 0))"></td>
                    </tr>
                </tbody>
            </template>
        </template>
    </table>

    <div class="table-pagination">
            <div class="pagination-actions">
                <div class="pagination-label">Per</div>
                <select x-model.number="perPage" x-on:change="page = 1" class="btn btn-ghost btn-sm pagination-select">
                    <template x-for="n in perPageOptions" :key="n">
                        <option :value="n" x-text="n"></option>
                    </template>
                </select>
            </div>
            <div class="pagination-info">
                <template x-if="tableData.total === 0">
                    <span x-text="'0 dari 0'"></span>
                </template>
                <template x-if="tableData.total > 0">
                    <span
                        x-text="( (page-1)*perPage + 1 ) + '-' + Math.min(page*perPage, tableData.total) + ' dari ' + tableData.total"></span>
                </template>
            </div>
            <div class="pagination-controls">
                <div class="pagination-page-info">Halaman <strong x-text="page"></strong> / <strong
                        x-text="Math.ceil(tableData.total/tableData.per_page)"></strong></div>
                <button class="btn btn-ghost btn-sm" x-on:click="prev()" :disabled="page <= 1"><x-misc.icon
                        name="chev-left" :size="13" /> Prev</button>
                <button class="btn btn-ghost btn-sm" x-on:click="next()"
                    :disabled="page >= Math.ceil(tableData.total / tableData.per_page)">Next
                    <x-misc.icon name="chev-right" :size="13" /></button>
            </div>
        </div>
</div>
@push('journal-scripts')
    <script>
        function journalModule() {
            return {
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    prev_page_url: null,
                    next_page_url: null,
                    data: [],
                },
                loading: false,
                perPageOptions: [10, 25, 50],
                page: 1,
                perPage: 10,
                filter: {
                    search: '',
                    start_date: '{{ now()->startOfMonth()->format('Y-m-d') }}',
                    end_date: '{{ now()->endOfMonth()->format('Y-m-d') }}',
                },

                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
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
                        const r = await axios.get(route('reports.journal.datatable'), {
                            params: {
                                search: this.filter.search,
                                start_date: this.filter.start_date,
                                end_date: this.filter.end_date
                            }
                        });
                        console.log(r.data);
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
