@extends('layouts.app')
@section('content')
    @php
        use App\Enums\GoodsReceiptStatus;
        $draft = GoodsReceiptStatus::DRAFT->value;
        $finished = GoodsReceiptStatus::FINISHED->value;
    @endphp
    <div x-data="datatable()" x-init="fetchData()" class="order-page">
        <div class="order-hd">
            <div>
                <h1 class="order-title display">Penerimaan Barang</h1>
                <div class="order-sub"><span x-text="tableData ? tableData.total : 0"></span> catatan</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
                <button class="btn btn-primary" x-on:click="openPoPicker()"><x-misc.icon name="plus"
                        :size="15" />Tambah</button>
            </div>
        </div>
        <div class="filter-pills">
            @foreach ($status as $s)
                <button x-on:click="setStatus('{{ $s['id'] }}')"
                    :class="filter === '{{ $s['id'] }}' ? 'filter-pill filter-pill--active' : 'filter-pill'">
                    <span x-text="'{{ $s['name'] }}'"></span>
                </button>
            @endforeach
        </div>

        <div class="table-toolbar">
            <div class="table-search">
                <span class="table-search__icon"><x-misc.icon name="search" :size="14" /></span>
                <input class="table-search__input" placeholder="Cari nomor penerimaan / supplier..."
                    x-model="search" x-on:input.debounce.400ms="page = 1; fetchData()" />
            </div>
            <div class="table-toolbar__spacer"></div>
            <button class="table-filter-btn" :class="showFilters && 'table-filter-btn--active'"
                @click="showFilters = !showFilters">
                <x-misc.icon name="filter" :size="13" />Filter
            </button>
        </div>

        <div class="filter-panel" x-show="showFilters" x-cloak>
            <div class="filter-panel__group">
                <label class="filter-panel__label">Dari Tanggal</label>
                <input type="date" class="filter-panel__input" x-model="dateFrom"
                    x-on:change="page = 1; fetchData()" />
            </div>
            <div class="filter-panel__group">
                <label class="filter-panel__label">Sampai Tanggal</label>
                <input type="date" class="filter-panel__input" x-model="dateTo"
                    x-on:change="page = 1; fetchData()" />
            </div>
            <button class="filter-panel__reset" @click="dateFrom = ''; dateTo = ''; page = 1; fetchData()">
                <x-misc.icon name="x" :size="12" />Reset
            </button>
        </div>

        <div class="card table-card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>No. Penerimaan</th>
                        <th>Tanggal</th>
                        <th>Ref. PO</th>
                        <th>Supplier</th>
                        <th>Gudang</th>
                        <th style="text-align:right;">Berat Diterima</th>
                        <th style="text-align:right;">Berat Susut</th>
                        <th>Status</th>
                        <th class="table-action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="9" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && tableData.data.length === 0">
                        <tr>
                            <td colspan="9" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading">
                        <template x-for="row in tableData.data" :key="row.id">
                            <tr class="row-tap" x-show="filter === 'all' || filter === row.status"
                                @click="window.location = route('purchasings.goods_receipts.show', row.id)">
                                <td class="mono" style="font-weight:600;" x-text="row.number"></td>
                                <td style="color:var(--ink-3);" x-text="row.receipt_date ?? '-'"></td>
                                <td class="mono" style="font-weight:600;" x-text="row.purchase_order.number"></td>
                                <td style="font-weight:500;" x-text="row.supplier.name ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.warehouse.name ?? '-'"></td>
                                <td class="num" style="text-align:right;" x-text="m(row.total_received_quantity)"></td>
                                <td class="num" style="text-align:right;" x-text="m(row.total_shrinkage_quantity)"></td>
                                <td>
                                    <span :class="statusChip(row.status).chip">
                                        <span :class="statusChip(row.status).dot"></span>
                                        <span x-text="statusChip(row.status).label"></span>
                                    </span>
                                </td>
                                <td class="table-action-col">
                                    <div x-data="{ open: false }" class="action-menu">
                                        <button class="btn btn-ghost btn-icon btn-sm btn--borderless"
                                            x-on:click.stop="
                                            let wasOpen = open;
                                            $dispatch('close-menus');
                                            if (!wasOpen) {
                                                let r = $el.getBoundingClientRect();
                                                $refs.panel.style.top = (r.bottom + 6) + 'px';
                                                $refs.panel.style.right = (window.innerWidth - r.right) + 'px';
                                                open = true;
                                            }
                                        ">
                                            <x-misc.icon name="more" :size="15" />
                                        </button>
                                        <div x-ref="panel" x-show="open" x-cloak x-on:close-menus.window="open = false"
                                            x-on:click.away="open = false" class="action-menu__panel">
                                            <a :href="route('purchasings.goods_receipts.show', row.id)" @click.stop
                                                class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                Detail
                                            </a>
                                            <a :href="route('purchasings.purchase_orders.show', row.purchase_order_id)"
                                                @click.stop class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                PO
                                            </a>
                                            <button class="action-menu__item" @click.stop>
                                                <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                                Penerimaan
                                            </button>
                                            <template x-if="row.status === '{{ $draft }}'">
                                                <div>
                                                    <a :href="route('purchasings.goods_receipts.edit', row.id)" @click.stop
                                                        class="action-menu__item">
                                                        <x-misc.icon name="edit" :size="14"
                                                            stroke="var(--ink-3)" />Edit
                                                        Penerimaan
                                                    </a>
                                                    <div class="action-menu__divider"></div>
                                                    <button class="action-menu__item action-menu__item--danger"
                                                        @click.stop="handleCancel(row.id)">
                                                        <x-misc.icon name="x" :size="14"
                                                            stroke="currentColor" />Batalkan
                                                        Penerimaan
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="row.status === '{{ $finished }}'">
                                                <div>
                                                    <a :href="route('pembelian.tagihan', row.purchase_order_id)" @click.stop
                                                        class="action-menu__item">
                                                        <x-misc.icon name="receipt" :size="14"
                                                            stroke="var(--ink-3)" />Buat Tagihan
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                        </template>
                    </template>
                </tbody>
            </table>
        </div>

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

        <x-misc.modal title="Pilih Purchase Order" show="poPickerOpen" close-handler="closePoPicker()" :width="640">
            <div style="margin-bottom:12px;">
                <input class="input" style="height:32px; width:100%;" placeholder="Cari nomor PO atau supplier..."
                    x-model="poPickerSearch" x-on:input.debounce.400ms="fetchPoPickerData()" />
            </div>
            <table class="tbl tbl-tight">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th style="text-align:right;">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="poPickerLoading">
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--ink-3); padding:16px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>
                    <template x-if="!poPickerLoading && poPickerData.length === 0">
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--ink-3); padding:16px;">
                                Tidak ada PO yang tersedia untuk diterima.
                            </td>
                        </tr>
                    </template>
                    <template x-if="!poPickerLoading">
                        <template x-for="po in poPickerData" :key="po.id">
                            <tr class="row-tap" @click="handleCreateGoodsReceipt(po.id)">
                                <td class="mono" style="font-weight:600;" x-text="po.number"></td>
                                <td style="color:var(--ink-3);" x-text="po.order_date ?? '-'"></td>
                                <td style="font-weight:500;" x-text="po.supplier?.name ?? '-'"></td>
                                <td class="num" style="text-align:right;" x-text="m(po.total_amount)"></td>
                                <td>
                                    <span :class="poStatusChip(po.status).chip">
                                        <span :class="poStatusChip(po.status).dot"></span>
                                        <span x-text="poStatusChip(po.status).label"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </x-misc.modal>
    </div>
@endsection

@push('scripts')
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
                filter: 'all',
                search: '',
                showFilters: false,
                dateFrom: '',
                dateTo: '',

                poPickerOpen: false,
                poPickerLoading: false,
                poPickerSearch: '',
                poPickerData: [],

                statusChip(status) {
                    const map = {
                        draft: {
                            chip: 'chip',
                            dot: 'chip-dot dot-muted',
                            label: 'Draft'
                        },
                        finished: {
                            chip: 'chip chip-ok',
                            dot: 'chip-dot dot-ok',
                            label: 'Finished'
                        },
                        cancelled: {
                            chip: 'chip chip-bad',
                            dot: 'chip-dot dot-bad',
                            label: 'Cancelled'
                        },
                    };
                    return map[status] ?? {
                        chip: 'chip',
                        dot: 'chip-dot dot-neutral',
                        label: status
                    };
                },

                async setStatus(statusId) {
                    this.filter = statusId;
                    this.page = 1;
                    await this.fetchData();
                },

                async tableLoad() {
                    await this.fetchData();
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const response = await axios.get(route('purchasings.goods_receipts.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                status: this.filter,
                                search: this.search,
                                start_date: this.dateFrom,
                                end_date: this.dateTo,
                            },
                        });
                        console.log('Response data:', response.data);
                        this.tableData = response.data;
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

                // async next
                async next() {
                    console.log('Current page:', this.page, 'Last page:', this.tableData.last_page);
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

                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

                async handleCancel(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membatalkan Penerimaan Barang ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, batalkan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            try {
                                const response = await axios.post(route(
                                    'purchasings.goods_receipts.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let message =
                                    'Terjadi kesalahan saat membatalkan Penerimaan Barang. Silakan coba lagi.';
                                if (error.response?.data?.message) {
                                    message = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: message
                                });
                            }

                        }
                    })
                },

                poStatusChip(status) {
                    const map = {
                        draft: {
                            chip: 'chip',
                            dot: 'chip-dot dot-muted',
                            label: 'Draft'
                        },
                        open: {
                            chip: 'chip chip-info',
                            dot: 'chip-dot dot-info',
                            label: 'Open'
                        },
                        closed: {
                            chip: 'chip chip-ok',
                            dot: 'chip-dot dot-ok',
                            label: 'Closed'
                        },
                        cancelled: {
                            chip: 'chip chip-bad',
                            dot: 'chip-dot dot-bad',
                            label: 'Cancelled'
                        },
                    };
                    return map[status] ?? {
                        chip: 'chip',
                        dot: 'chip-dot dot-neutral',
                        label: status
                    };
                },

                openPoPicker() {
                    this.poPickerOpen = true;
                    this.poPickerSearch = '';
                    this.fetchPoPickerData();
                },

                closePoPicker() {
                    this.poPickerOpen = false;
                },

                async fetchPoPickerData() {
                    this.poPickerLoading = true;
                    try {
                        const response = await axios.get(route('purchasings.purchase_orders.datatable'), {
                            params: {
                                per_page: 100,
                                is_receivable: true,
                                search: this.poPickerSearch,
                            },
                        });
                        this.poPickerData = (response.data.data ?? []).filter(po => po.is_receivable);
                    } catch (error) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data PO. Silakan coba lagi.'
                        });
                    } finally {
                        this.poPickerLoading = false;
                    }
                },

                async handleCreateGoodsReceipt(purchaseOrderId) {
                    this.closePoPicker();
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuat Penerimaan Barang untuk PO ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            try {
                                const response = await axios.post(route(
                                    'purchasings.goods_receipts.store', {
                                        purchase_order_id: purchaseOrderId
                                    }));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });

                                window.location.href = response.data.redirect;
                            } catch (error) {
                                Swal.close();
                                let message =
                                    'Terjadi kesalahan saat membuat Penerimaan Barang. Silakan coba lagi.';
                                if (error.response?.data?.message) {
                                    message = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: message
                                });
                            }

                        }
                    })
                },
            };
        }
    </script>
@endpush
