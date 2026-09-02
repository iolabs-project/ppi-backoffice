@extends('layouts.app')
@section('content')
    @php
        use App\Enums\SalesInvoiceStatus;
        $draft = SalesInvoiceStatus::DRAFT->value;
        $open = SalesInvoiceStatus::OPEN->value;
    @endphp
    <div x-data="datatable()" x-init="fetchData()" class="order-page">
        <div class="order-hd">
            <div>
                <h1 class="order-title display">Tagihan Penjualan</h1>
                <div class="order-sub"><span x-text="tableData ? tableData.total : 0"></span> dokumen</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
                <button class="btn btn-primary" x-on:click="openSoPicker()"><x-misc.icon name="plus"
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
                <input class="table-search__input" placeholder="Cari nomor tagihan / customer..."
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
                        <th>No. Tagihan</th>
                        <th>Tanggal</th>
                        <th>Ref. SO</th>
                        <th>Pelanggan</th>
                        <th>Jatuh Tempo</th>
                        <th style="text-align:right;">Total</th>
                        <th>Status</th>
                        <th class="table-action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && tableData.data.length === 0">
                        <tr>
                            <td colspan="8" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading">
                        <template x-for="row in tableData.data" :key="row.id">
                            <tr class="row-tap" x-show="filter === 'all' || filter === row.status"
                                @click="row.status === '{{ $draft }}' ? window.location = route('sales.sales_invoices.edit', row.id) : null">
                                <td class="mono" style="font-weight:600;" x-text="row.number"></td>
                                <td style="color:var(--ink-3);" x-text="row.invoice_date ?? '-'"></td>
                                <td class="mono" style="font-weight:600;" x-text="row.sales_order.number"></td>
                                <td style="font-weight:500;" x-text="row.customer.name ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.due_date ?? '-'"></td>
                                <td class="num" style="text-align:right;"
                                    x-text="NumberUtils.formatNumericIntoMask(row.total_amount)"></td>
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
                                             <a :href="route('sales.sales_invoices.show', row.id)" @click.stop
                                                class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                Detail
                                            </a>
                                            <a :href="route('sales.sales_orders.show', row.sales_order_id)" @click.stop
                                                class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                SO
                                            </a>
                                            <button class="action-menu__item" @click.stop>
                                                <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                                Tagihan
                                            </button>
                                            <template x-if="row.status === '{{ $draft }}'">
                                                <div>
                                                    <a :href="route('sales.sales_invoices.edit', row.id)" @click.stop
                                                        class="action-menu__item">
                                                        <x-misc.icon name="edit" :size="14"
                                                            stroke="var(--ink-3)" />Edit
                                                        Tagihan
                                                    </a>
                                                </div>
                                            </template>
                                            <template
                                                x-if="row.status === '{{ $draft }}' || row.status === '{{ $open }}'">
                                                <div>
                                                    <div class="action-menu__divider"></div>
                                                    <button class="action-menu__item action-menu__item--danger"
                                                        @click.stop="handleCancel(row.id)">
                                                        <x-misc.icon name="x" :size="14"
                                                            stroke="currentColor" />Hapus
                                                        Tagihan
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

        <x-misc.modal title="Pilih Sales Order" show="soPickerOpen" close-handler="closeSoPicker()" :width="640">
            <div style="margin-bottom:12px;">
                <input class="input" style="height:32px; width:100%;" placeholder="Cari nomor SO atau customer..."
                    x-model="soPickerSearch" x-on:input.debounce.400ms="fetchSoPickerData()" />
            </div>
            <table class="tbl tbl-tight">
                <thead>
                    <tr>
                        <th>Nomor SO</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th style="text-align:right;">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="soPickerLoading">
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--ink-3); padding:16px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>
                    <template x-if="!soPickerLoading && soPickerData.length === 0">
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--ink-3); padding:16px;">
                                Tidak ada SO yang tersedia untuk ditagihkan.
                            </td>
                        </tr>
                    </template>
                    <template x-if="!soPickerLoading">
                        <template x-for="so in soPickerData" :key="so.id">
                            <tr class="row-tap" @click="handleCreateSalesInvoice(so.id)">
                                <td class="mono" style="font-weight:600;" x-text="so.number"></td>
                                <td style="color:var(--ink-3);" x-text="so.order_date ?? '-'"></td>
                                <td style="font-weight:500;" x-text="so.customer?.name ?? '-'"></td>
                                <td class="num" style="text-align:right;" x-text="m(so.total_amount)"></td>
                                <td>
                                    <span :class="soStatusChip(so.status).chip">
                                        <span :class="soStatusChip(so.status).dot"></span>
                                        <span x-text="soStatusChip(so.status).label"></span>
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

                soPickerOpen: false,
                soPickerLoading: false,
                soPickerSearch: '',
                soPickerData: [],

                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

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
                        partial: {
                            chip: 'chip chip-warn',
                            dot: 'chip-dot dot-warn',
                            label: 'Partial'
                        },
                        paid: {
                            chip: 'chip',
                            dot: 'chip-dot dot-ok',
                            label: 'Paid'
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
                        const response = await axios.get(route('sales.sales_invoices.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                status: this.filter,
                                search: this.search,
                                start_date: this.dateFrom,
                                end_date: this.dateTo,
                            },
                        });
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

                async handleCancel(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membatalkan Tagihan Penjualan ini?',
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
                                    'sales.sales_invoices.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let message =
                                    'Terjadi kesalahan saat membatalkan Tagihan Penjualan. Silakan coba lagi.';
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

                soStatusChip(status) {
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

                openSoPicker() {
                    this.soPickerOpen = true;
                    this.soPickerSearch = '';
                    this.fetchSoPickerData();
                },

                closeSoPicker() {
                    this.soPickerOpen = false;
                },

                async fetchSoPickerData() {
                    this.soPickerLoading = true;
                    try {
                        const response = await axios.get(route('sales.sales_orders.datatable'), {
                            params: {
                                per_page: 100,
                                is_invoicable: true,
                                search: this.soPickerSearch,
                            },
                        });
                        this.soPickerData = (response.data.data ?? []).filter(so => so.is_invoicable);
                    } catch (error) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data SO. Silakan coba lagi.'
                        });
                    } finally {
                        this.soPickerLoading = false;
                    }
                },

                async handleCreateSalesInvoice(salesOrderId) {
                    this.closeSoPicker();
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuat Tagihan untuk SO ini?',
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
                                    'sales.sales_invoices.store', {
                                        sales_order_id: salesOrderId
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
                },
            };
        }
    </script>
@endpush
