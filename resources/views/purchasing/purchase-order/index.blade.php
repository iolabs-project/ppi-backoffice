@extends('layouts.app')
@section('content')
    @php
        use App\Enums\PurchaseOrderStatus;
        $draft = PurchaseOrderStatus::DRAFT->value;
        $open = PurchaseOrderStatus::OPEN->value;
        $cancelled = PurchaseOrderStatus::CANCELLED->value;
    @endphp
    <div x-data="datatable()" x-init="fetchData()" class="order-page">
        <div class="order-hd">
            <div>
                <h1 class="order-title display">Purchase Order</h1>
                <div class="order-sub"><span x-text="tableData ? tableData.total : 0"></span> dokumen</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
                <a href="{{ route('purchasings.purchase_orders.create') }}" class="btn btn-primary"><x-misc.icon
                        name="plus" :size="15" />Tambah PO</a>
            </div>
        </div>

        <div class="filter-pills">
            @foreach ($status as $s)
                <button x-on:click="setStatus('{{ $s['id'] }}')"
                    :class="filter === '{{ $s['id'] }}' ? 'filter-pill filter-pill--active' : 'filter-pill'">
                    <span x-text="'{{ $s['name'] }}'"></span>
                </button>
            @endforeach
            <div style="flex:1;"></div>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="sort" :size="13" />Tanggal</button>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="filter" :size="13" />Filter</button>
        </div>

        <div class="card table-card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th>Gudang</th>
                        <th>Jatuh Tempo</th>
                        <th>Penerimaan</th>
                        <th>Tagihan</th>
                        <th style="text-align:right;">Total</th>
                        <th>Status</th>
                        <th style="width:40px;"></th>
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

                    <template x-if="!loading">
                        <template x-for="row in tableData.data" :key="row.id">
                            <tr class="row-tap" x-show="filter === 'all' || filter === row.status"
                                @click="window.location = route('purchasings.purchase_orders.show', row.id)">
                                <td class="mono" style="font-weight:600;" x-text="row.number"></td>
                                <td style="color:var(--ink-3);" x-text="row.order_date ?? '-'"></td>
                                <td style="font-weight:500;" x-text="row.supplier.name ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.warehouse.name ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.due_date ?? '-'"></td>
                                <td style="min-width:100px;">
                                    <template
                                        x-if="row.status === '{{ $draft }}' || row.status === '{{ $cancelled }}'">
                                        <span style="color:var(--ink-3);">-</span>
                                    </template>
                                    <template
                                        x-if="row.status !== '{{ $draft }}' && row.status !== '{{ $cancelled }}'">
                                        <div class="progress-cell">
                                            <span class="progress-label"
                                                x-text="m(row.total_received_quantity) + ' / ' + m(row.total_quantity)"></span>
                                            <div class="progress-track">
                                                <div class="progress-bar"
                                                    x-bind:style="'width:' + Math.min((row.total_received_quantity / row
                                                        .total_quantity) * 100, 100) + '%'">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </td>
                                <td style="min-width:100px;">
                                    <template
                                        x-if="row.status === '{{ $draft }}' || row.status === '{{ $cancelled }}'">
                                        <span style="color:var(--ink-3);">-</span>
                                    </template>
                                    <template
                                        x-if="row.status !== '{{ $draft }}' && row.status !== '{{ $cancelled }}'">
                                        <div class="progress-cell">
                                            <span class="progress-label"
                                                x-text="m(row.total_invoiced_quantity) + ' / ' + m(row.total_quantity)"></span>
                                            <div class="progress-track">
                                                <div class="progress-bar progress-bar--invoice"
                                                    x-bind:style="'width:' + Math.min((row.total_invoiced_quantity / row
                                                        .total_quantity) * 100, 100) + '%'">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </td>
                                <td class="num" style="text-align:right; font-weight:600;"
                                    x-text="m(row.total_amount)"></td>
                                <td>
                                    <span :class="statusChip(row.status).chip">
                                        <span :class="statusChip(row.status).dot"></span>
                                        <span x-text="statusChip(row.status).label"></span>
                                    </span>
                                </td>
                                <td x-on:click.stop>
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
                                            <template x-if="row.status === '{{ $draft }}'">
                                                <div>
                                                    <a :href="route('purchasings.purchase_orders.edit', row.id)" @click.stop
                                                        class="action-menu__item">
                                                        <x-misc.icon name="edit" :size="14"
                                                            stroke="var(--ink-3)" />
                                                        Edit Draft
                                                    </a>
                                                </div>
                                            </template>
                                            <template x-if="row.status !== '{{ $draft }}'">
                                                <div>
                                                    <a :href="route('purchasings.purchase_orders.show', row.id)" @click.stop
                                                        class="action-menu__item">
                                                        <x-misc.icon name="eye" :size="14"
                                                            stroke="var(--ink-3)" />Lihat
                                                        Detail
                                                    </a>
                                                    <button class="action-menu__item" @click.stop>
                                                        {{-- TODO: Implement print functionality --}}
                                                        <x-misc.icon name="print" :size="14"
                                                            stroke="var(--ink-3)" />Cetak
                                                        PO
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="row.is_receivable">
                                                <button class="action-menu__item"
                                                    @click.stop="handleCreateGoodsReceipt(row.id)">
                                                    <x-misc.icon name="box" :size="14"
                                                        stroke="var(--ink-3)" />Buat
                                                    Penerimaan
                                                </button>
                                            </template>
                                            <template
                                                x-if="row.is_invoicable">
                                                <button class="action-menu__item"
                                                    @click.stop="handleCreatePurchaseInvoice(row.id)">
                                                    <x-misc.icon name="wallet" :size="14"
                                                        stroke="var(--ink-3)" />Buat
                                                    Tagihan
                                                </button>
                                            </template>

                                            <template
                                                x-if="row.is_cancellable">
                                                <div>
                                                    <div class="action-menu__divider"></div>
                                                    <button class="action-menu__item action-menu__item--danger"
                                                        @click.stop="handleCancel(row.id)">
                                                        <x-misc.icon name="trash" :size="14"
                                                            stroke="currentColor" />Batalkan
                                                        PO
                                                    </button>
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
                <button class="btn btn-ghost btn-sm" @click="prev()"
                    :disabled="!tableData || !tableData.prev_page_url"><x-misc.icon name="chev-left" :size="13" />
                    Prev</button>
                <button class="btn btn-ghost btn-sm" @click="next()"
                    :disabled="!tableData || !tableData.next_page_url">Next
                    <x-misc.icon name="chev-right" :size="13" /></button>
            </div>
        </div>
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

                statusChip(status) {
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
                        const response = await axios.get(route('purchasings.purchase_orders.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                status: this.filter,
                            },
                        });
                        console.log('Response data:', response.data);
                        this.tableData = response.data;
                    } catch (error) {
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

                m(v){
                    return NumberUtils.formatNumericIntoMask(v);
                },

                async handleOpen(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuka PO ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buka',
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
                                    'purchasings.purchase_orders.open', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let message = 'Terjadi kesalahan saat membuka PO. Silakan coba lagi.';
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

                async handleCancel(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membatalkan PO ini?',
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
                                    'purchasings.purchase_orders.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let message = 'Terjadi kesalahan saat membatalkan PO. Silakan coba lagi.';
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

                async handleCreateGoodsReceipt(id) {
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
                                        purchase_order_id: id
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

                async handleCreatePurchaseInvoice(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuat Tagihan untuk PO ini?',
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
                                    'purchasings.purchase_invoices.store', {
                                        purchase_order_id: id
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
                                    'Terjadi kesalahan saat membuat Tagihan. Silakan coba lagi.';
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
                }
            };
        }
    </script>
@endpush
