<div x-data="productModule()" x-init="fetchData()" x-show="tab === 'produk'" x-cloak>
    <div class="card" style="overflow:hidden;">
        <div class="master-toolbar" style="justify-content: space-between">
            <div class="master-search">
                <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                        stroke="var(--ink-4)" /></span>
                <input class="input master-search__input" placeholder="Cari produk..." x-model="search"
                    x-on:input.debounce.400ms="handleSearch(search)" />
            </div>
            <button class="btn btn-primary btn-sm" x-on:click="modal = 'add_produk'">
                <x-misc.icon name="plus" :size="14" /> Tambah Produk
            </button>
        </div>
        <table class="tbl">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th>Status</th>
                    <th style="width:40px;"></th>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...
                        </td>
                    </tr>
                </template>

                <template x-if="!loading && tableData.data.length === 0">
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">Tidak ada data
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && tableData.data.length > 0">
                    <template x-for="(row, i) in tableData.data" :key="row.id">
                        <tr class="row-tap" style="cursor:pointer;">
                            <td class="mono" style="font-weight:600; font-size:12px; color:var(--ink-4);"
                                x-text="row.code">
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px;" x-text="row.name"></div>
                                <div x-show="row.description" style="font-size:11px; color:var(--ink-4);"
                                    x-text="row.description">
                                </div>
                            </td>
                            <td><span class="chip" x-text="row.category.name"></span></td>
                            <td style="color:var(--ink-3); font-size:13px;" x-text="row.unit.symbol"></td>
                            {{-- <td class="num" style="text-align:right; font-size:13px;" x-text="p.stok ?? 0"></td> --}}
                            <template x-if="row.deleted_at">
                                <td><x-misc.status-badge status="inactive" /></td>
                            </template>
                            <template x-if="!row.deleted_at">
                                <td><x-misc.status-badge status="active" /></td>
                            </template>
                            <td x-on:click.stop>
                                <div class="action-menu" x-data="{ open: false }">
                                    <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                        x-on:click="open = !open" x-on:click.outside="open = false">
                                        <x-misc.icon name="more" :size="15" />
                                    </button>
                                    <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                        style="position:absolute; right:0; top:100%; margin-top:4px;">
                                        <button class="action-menu__item" x-on:click="openEditModal(row)">
                                            <x-misc.icon name="edit" :size="14" /> Edit Produk
                                        </button>
                                        <template x-if="!row.deleted_at">
                                            <button class="action-menu__item action-menu__item--danger"
                                                x-on:click="handleStatus(row.id)">
                                                <x-misc.icon name="trash" :size="14" /> Nonaktifkan
                                            </button>
                                        </template>
                                        <template x-if="row.deleted_at">
                                            <button class="action-menu__item" x-on:click="handleStatus(row.id)">
                                                <x-misc.icon name="refresh" :size="14" /> Aktifkan
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </td>
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

    @include('master.partials.modals.product-create-modal')
    @include('master.partials.modals.product-edit-modal')
</div>
