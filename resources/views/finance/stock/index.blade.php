@extends('layouts.app')
@section('content')
    <script>
        function datatable() {
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
                search: '',
                expanded: [],

                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

                toggle(id) {
                    this.expanded = this.expanded.includes(id)
                        ? this.expanded.filter(e => e !== id)
                        : [...this.expanded, id];
                },

                // Same search as the table, every page, one row per product per warehouse
                exportXlsx() {
                    return ExportUtils.runExport(async () => {
                        const products = await ExportUtils.fetchAllRows(route('finances.stocks.datatable'), {
                            search: this.search,
                        });
                        const rows = products.flatMap(p => p.warehouses.map(w => ({ ...w, product: p.product })));
                        await ExportUtils.exportXlsx({
                            filename: 'Stok',
                            title: 'Stok Seluruh Gudang',
                            subtitle: ExportUtils.describeFilters({ search: this.search }),
                            columns: [
                                { header: 'Kode', value: r => r.product.code },
                                { header: 'Nama Produk', value: r => r.product.name },
                                { header: 'Gudang', value: r => r.warehouse?.name },
                                { header: 'Qty', value: r => r.quantity, type: 'number' },
                                { header: 'Dipesan', value: r => r.reserved_quantity, type: 'number' },
                                { header: 'Tersedia', value: r => r.available_quantity, type: 'number' },
                                { header: 'Satuan', value: r => r.product.unit?.name },
                                { header: 'HPP / Unit', value: r => r.average_unit_cost, type: 'number' },
                                { header: 'Nilai', value: r => r.total_value, type: 'number' },
                            ],
                            rows,
                        });
                    });
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const response = await axios.get(route('finances.stocks.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search,
                            },
                        });
                        this.tableData = response.data;
                        this.expanded = [];
                    } catch (error) {
                        console.error('Error fetching data:', error);
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data. Silakan coba lagi.'
                        });
                    } finally {
                        this.loading = false;
                    }
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
            };
        }
    </script>

    <div x-data="datatable()" x-init="fetchData()" class="biaya-page">

        <div class="order-hd">
            <div>
                <h1 class="order-title display">Stok</h1>
                <div class="order-sub">Total stok seluruh gudang beserta HPP rata-rata per produk</div>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="stok-summary">
            <div class="card stat-card stat-card--dark">
                <div class="stat-card__label">Total Nilai Persediaan</div>
                <div class="stat-card__value display num">{{ number_format($summary['total_value'], 2) }}</div>
            </div>
            <div class="card stat-card">
                <div class="stat-card__label">Produk Tersedia</div>
                <div class="stat-card__value display num">{{ number_format($summary['product_count']) }}</div>
            </div>
            <div class="card stat-card">
                <div class="stat-card__label">Gudang Berisi Stok</div>
                <div class="stat-card__value display num">{{ number_format($summary['warehouse_count']) }}</div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="card" style="overflow:hidden;">
            <div class="master-toolbar" style="justify-content:flex-end;">
                <div class="table-toolbar" style="gap:8px;">
                    <div class="table-search">
                        <span class="table-search__icon"><x-misc.icon name="search" :size="14" /></span>
                        <input class="table-search__input" placeholder="Cari nama / kode produk..."
                            x-model="search" x-on:input.debounce.400ms="page = 1; fetchData()" />
                    </div>
                    <button class="btn btn-ghost btn-sm" type="button" @click="exportXlsx()">
                        <x-misc.icon name="download" :size="13" />Ekspor
                    </button>
                </div>
            </div>

            <div class="tbl-scroll">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:32px;"></th>
                            <th>Nama Produk</th>
                            <th>Kode</th>
                            <th style="text-align:right;">Gudang</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Dipesan</th>
                            <th style="text-align:right;">Tersedia</th>
                            <th>Satuan</th>
                            <th style="text-align:right;">HPP / Unit</th>
                            <th style="text-align:right;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="loading">
                            <tr>
                                <td colspan="10" style="text-align:center; color:var(--ink-3); padding:20px;">
                                    Memuat data...
                                </td>
                            </tr>
                        </template>

                        <template x-if="!loading && tableData.data.length === 0">
                            <tr>
                                <td colspan="10" style="text-align:center; color:var(--ink-3); padding:20px;">
                                    Tidak ada data
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    {{-- One tbody per product so the warehouse breakdown sits right under its row --}}
                    <template x-if="!loading">
                        <template x-for="row in tableData.data" :key="row.id">
                            <tbody>
                                <tr class="row-tap" @click="toggle(row.id)">
                                    <td>
                                        <span class="stok-expand-btn"
                                            :class="expanded.includes(row.id) && 'stok-expand-btn--open'">
                                            <x-misc.icon name="chev-right" :size="13" stroke="var(--ink-3)" />
                                        </span>
                                    </td>
                                    <td style="font-weight:500;" x-text="row.product.name"></td>
                                    <td class="mono" x-text="row.product.code"></td>
                                    <td class="num" style="text-align:right;" x-text="row.warehouse_count"></td>
                                    <td class="num" style="text-align:right; font-weight:600;" x-text="m(row.quantity)"></td>
                                    <td class="num" style="text-align:right; color:var(--ink-3);" x-text="m(row.reserved_quantity)"></td>
                                    <td class="num" style="text-align:right;" x-text="m(row.available_quantity)"></td>
                                    <td x-text="row.product.unit?.name ?? '-'"></td>
                                    <td class="num" style="text-align:right;" x-text="m(row.average_unit_cost)"></td>
                                    <td class="num" style="text-align:right; font-weight:600;" x-text="m(row.total_value)"></td>
                                </tr>
                                <tr class="stok-detail-row" x-show="expanded.includes(row.id)" x-cloak>
                                    <td colspan="10">
                                        <table class="tbl stok-detail-tbl">
                                            <thead>
                                                <tr>
                                                    <th>Gudang</th>
                                                    <th style="text-align:right;">Qty</th>
                                                    <th style="text-align:right;">Dipesan</th>
                                                    <th style="text-align:right;">Tersedia</th>
                                                    <th style="text-align:right;">HPP / Unit</th>
                                                    <th style="text-align:right;">Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="w in row.warehouses" :key="w.id">
                                                    <tr>
                                                        <td>
                                                            <span x-text="w.warehouse?.name"></span>
                                                            <span class="mono" style="color:var(--ink-4);"
                                                                x-text="w.warehouse?.code"></span>
                                                        </td>
                                                        <td class="num" style="text-align:right;" x-text="m(w.quantity)"></td>
                                                        <td class="num" style="text-align:right; color:var(--ink-3);" x-text="m(w.reserved_quantity)"></td>
                                                        <td class="num" style="text-align:right;" x-text="m(w.available_quantity)"></td>
                                                        <td class="num" style="text-align:right;" x-text="m(w.average_unit_cost)"></td>
                                                        <td class="num" style="text-align:right;" x-text="m(w.total_value)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                    </template>
                </table>
            </div>

            <div class="table-pagination">
                <div class="pagination-actions">
                    <div class="pagination-label">Per</div>
                    <select x-model.number="perPage" x-on:change="page = 1; fetchData()" class="btn btn-ghost btn-sm pagination-select">
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

    </div>
@endsection
