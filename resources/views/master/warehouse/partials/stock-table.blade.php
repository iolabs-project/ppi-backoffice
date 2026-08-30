<div class="card" style="overflow:hidden;" x-data="stockTableModule()">
    <div class="master-toolbar">
        <div class="master-search">
            <span class="master-search__icon"><x-misc.icon name="search" :size="14" stroke="var(--ink-4)" /></span>
            <input class="input master-search__input" placeholder="Cari produk di gudang ini..."
                x-model="filter.search" @input.debounce.300ms="handleSearch()" />
        </div>
    </div>

    <table class="tbl">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Kode</th>
                <th style="text-align:right;">Qty</th>
                <th>Satuan</th>
                <th style="text-align:right;">HPP (Average)</th>
                <th style="text-align:right;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
                        Memuat data...
                    </td>
                </tr>
            </template>
            <template x-if="!loading && tableData.data.length === 0">
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
                        Belum ada produk di gudang ini
                    </td>
                </tr>
            </template>
            <template x-if="!loading && tableData.data.length > 0">
                <template x-for="stock in tableData.data" :key="stock.id">
                    <tr>
                        <td x-text="stock.product.name"></td>
                        <td class="mono" x-text="stock.product.code"></td>
                        <td style="text-align:right;" x-text="m(stock.quantity)"></td>
                        <td x-text="stock.product.unit.name"></td>
                        <td style="text-align:right;" x-text="m(stock.average_unit_cost)"></td>
                        <td style="text-align:right;" x-text="m(stock.quantity * stock.average_unit_cost)"></td>
                    </tr>
                </template>
            </template>
        </tbody>
    </table>
    <div class="table-pagination">
        <div class="pagination-actions">
            <div class="pagination-label">Per</div>
            <select x-model.number="perPage" x-on:change="page = 1; fetchData()"
                class="btn btn-ghost btn-sm pagination-select">
                <template x-for="n in perPageOptions" :key="n">
                    <option :value="n" x-text="n" x-bind:selected="n === perPage"></option>
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
            <div class="pagination-page-info">Halaman <strong x-text="tableData.current_page"></strong> / <strong
                    x-text="tableData.last_page"></strong></div>
            <button class="btn btn-ghost btn-sm" @click="prev()" :disabled="!tableData || !tableData.prev_page_url">
                <x-misc.icon name="chev-left" :size="13" />Prev
            </button>
            <button class="btn btn-ghost btn-sm" @click="next()" :disabled="!tableData || !tableData.next_page_url">
                Next<x-misc.icon name="chev-right" :size="13" />
            </button>
        </div>
    </div>
</div>
@push('stock-table-scripts')
    <script>
        function stockTableModule() {
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
                    only_available: true,
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
                        const r = await axios.get(route('master.warehouses.stock_datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.filter.search,
                                warehouse_id: {{ $warehouse->id }}
                            }
                        });
                        this.tableData = r.data;
                    } catch (error) {
                        console.error(error);
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },

                async handleSearch() {
                    this.page = 1;
                    await this.fetchData();
                },

                async next() {
                    if (this.tableData && this.page < this.tableData.last_page) {
                        this.page++;
                        await this.fetchData();
                    }
                },
                async prev() {
                    if (this.tableData && this.page > 1) {
                        this.page--;
                        await this.fetchData();
                    }
                },
            }
        }
    </script>
@endpush
