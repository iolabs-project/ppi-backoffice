<div class="card" style="overflow:hidden;" x-data="batchTableModule()">
    <div class="master-toolbar">
        <div class="master-search">
            <span class="master-search__icon"><x-misc.icon name="search" :size="14" stroke="var(--ink-4)" /></span>
            <input class="input master-search__input" placeholder="Cari batch di gudang ini..." x-model="filter.search"
                @input.debounce.300ms="handleSearch()" />
        </div>
        <div class="master-toolbar__filters">
            <label
                style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--ink-3); cursor:pointer; user-select:none;">
                <input type="checkbox" x-model="filter.only_available" @change="page = 1; fetchData()" />
                Tampilkan batch tersedia saja
            </label>
        </div>
    </div>

    <table class="tbl">
        <thead>
            <tr>
                <th>Batch</th>
                <th style="text-align:right;">Qty</th>
                <th>Satuan</th>
                <th style="text-align:right;">HPP (FIFO)</th>
                <th style="text-align:right;">Nilai</th>
            </tr>
        </thead>
        <template x-if="loading">
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
                        Memuat data...
                    </td>
                </tr>
            </tbody>
        </template>
        <template x-if="!loading && groupedBatches.length === 0">
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
                        Belum ada batch di gudang ini
                    </td>
                </tr>
            </tbody>
        </template>
        <template x-if="!loading && groupedBatches.length > 0">
            <template x-for="group in groupedBatches" :key="group.product.id">
                <tbody>
                    <tr class="coa-group-row">
                        <td colspan="5">
                            <span x-text="group.product.name"></span>
                            <span class="chip mono" style="font-size:11px; margin-left:6px;"
                                x-text="group.product.code"></span>
                        </td>
                    </tr>
                    <template x-for="b in group.batches" :key="b.id">
                        <tr>
                            {{-- <td x-text="b.batch_number"></td> --}}
                            <td>
                                    <a :href="route('master.products.batches.show', { id: b.product_id, batch_id: b.id })" class="btn btn-ghost btn-sm"  x-text="b.batch_number"></a>
                                </td>
                            <td style="text-align:right;">
                                <span x-text="m(b.quantity)"></span>
                                <div
                                    style="margin-top:4px; height:4px; border-radius:99px; background:var(--line-2); overflow:hidden; min-width:80px;">
                                    <div style="height:100%; border-radius:99px; background:var(--accent);"
                                        :style="{
                                            width: (b.initial_quantity > 0 ? Math.min(b.quantity / b.initial_quantity *
                                                100,
                                                100) : 0) + '%'
                                        }">
                                    </div>
                                </div>
                                <div style="font-size:10px; color:var(--ink-4); margin-top:2px;"
                                    x-text="m(b.initial_quantity) + ' awal'"></div>
                            </td>
                            <td x-text="group.product.unit.symbol"></td>
                            <td style="text-align:right;" x-text="m(b.unit_cost)"></td>
                            <td style="text-align:right;" x-text="m(b.quantity * b.unit_cost)"></td>
                        </tr>
                    </template>
                </tbody>
            </template>
        </template>
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

@push('batch-table-scripts')
    <script>
        function batchTableModule() {
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
                        const r = await axios.get(route('master.warehouses.batch_datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.filter.search,
                                warehouse_id: {{ $warehouse->id }},
                                only_available: this.filter.only_available,
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

                get groupedBatches() {
                    let src = this.tableData.data;
                    const map = {};
                    for (const b of src) {
                        if (!map[b.product_id]) map[b.product_id] = {
                            product: b.product,
                            batches: []
                        };
                        map[b.product_id].batches.push(b);
                    }
                    return Object.values(map);
                },
            }
        }
    </script>
@endpush
