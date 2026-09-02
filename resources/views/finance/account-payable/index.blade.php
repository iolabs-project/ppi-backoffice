@extends('layouts.app')
@section('content')
    @php
        use App\Enums\AccountPayableStatusEnum;
        $draft = AccountPayableStatusEnum::DRAFT->value;
        $open = AccountPayableStatusEnum::OPEN->value;
        $partial = AccountPayableStatusEnum::PARTIAL->value;
        $paid = AccountPayableStatusEnum::PAID->value;
        $cancelled = AccountPayableStatusEnum::CANCELLED->value;
    @endphp
    <div x-data="apDatatable()" x-init="fetchData()" class="order-page">
        <div class="order-hd">
            <div>
                <h1 class="order-title display">Hutang</h1>
                {{-- <div class="order-sub"><span x-text="tableData ? tableData.total : 0"></span> invoice dengan outstanding</div> --}}
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
                <input class="table-search__input" placeholder="Cari nomor invoice / supplier..."
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
                        <th style="width:48px;">No</th>
                        <th>No. Invoice</th>
                        <th>Supplier</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th style="text-align:right;">Invoice Total</th>
                        <th style="text-align:right;">Outstanding</th>
                        <th>Status</th>
                        <th style="width:48px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="9" style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && tableData.data.length === 0">
                        <tr>
                            <td colspan="9" style="text-align:center; color:var(--ink-3); padding:20px;">Tidak ada data
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading">
                        <template x-for="(row, i) in tableData.data" :key="row.id + '-' + row.type">
                            <tr class="row-tap"
                                @click="window.location = route('finances.account_payables.show', {
                                id: row.id,
                                reference_type: row.type
                            })">
                                <td class="mono" style="color:var(--ink-4);"
                                    x-text="(tableData.current_page - 1) * tableData.per_page + i + 1"></td>
                                <td class="mono" style="font-weight:600;" x-text="row.number"></td>
                                <td style="font-weight:500;" x-text="row.contact_name ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.invoice_date ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.due_date ?? '-'"></td>
                                <td class="num" style="text-align:right;"
                                    x-text="NumberUtils.formatNumericIntoMask(row.total_amount)"></td>
                                <td class="num" style="text-align:right; font-weight:600;"
                                    x-text="NumberUtils.formatNumericIntoMask(row.remaining_amount)"></td>
                                <td>
                                    <span :class="statusChip(row.status).chip">
                                        <span :class="statusChip(row.status).dot"></span>
                                        <span x-text="statusChip(row.status).label"></span>
                                    </span>
                                </td>
                                {{-- <td x-on:click.stop>
                                    <a :href="route('finances.account_payables.show', row.id)"
                                        class="btn btn-ghost btn-icon btn-sm" style="border:none;">
                                        <x-misc.icon name="eye" :size="15" stroke="var(--ink-3)" />
                                    </a>
                                </td> --}}
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
                                            <a :href="route('finances.account_payables.show', { id: row.id, reference_type: row.type })" @click.stop
                                                class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                Detail
                                            </a>
                                            {{-- <a :href="route('purchasings.purchase_orders.show', row.purchase_order_id)"
                                                @click.stop class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                PO
                                            </a> --}}
                                            {{-- <button class="action-menu__item" @click.stop>
                                                <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                                Tagihan
                                            </button> --}}
                                            {{-- <template x-if="row.status !== '{{ $paid }}' && row.status !== '{{ $cancelled }}'">
                                                <div>
                                                    <a :href="route('finances.account_payables.edit', row.id)"
                                                        @click.stop class="action-menu__item">
                                                        <x-misc.icon name="edit" :size="14"
                                                            stroke="var(--ink-3)" />Edit
                                                        Hutang
                                                    </a>
                                                </div>
                                            </template> --}}
                                            {{-- <template
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
                                            </template> --}}
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
                <button class="btn btn-ghost btn-sm" @click="prev()" :disabled="!tableData || !tableData.prev_page_url">
                    <x-misc.icon name="chev-left" :size="13" />Prev
                </button>
                <button class="btn btn-ghost btn-sm" @click="next()" :disabled="!tableData || !tableData.next_page_url">
                    Next<x-misc.icon name="chev-right" :size="13" />
                </button>
            </div>
        </div>


    </div>
@endsection

@push('scripts')
    <script>
        function apDatatable() {
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
                            chip: 'chip chip-ok',
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

                async fetchData() {
                    this.loading = true;
                    try {
                        const response = await axios.get(route('finances.account_payables.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                status: this.filter,
                                search: this.search,
                                date_from: this.dateFrom,
                                date_to: this.dateTo,
                            },
                        });
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
@endpush
