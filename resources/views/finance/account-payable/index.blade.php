@extends('layouts.app')
@section('content')
    <div x-data="apDatatable()" x-init="fetchData()" class="order-page">
        <div class="order-hd">
            <div>
                <h1 class="order-title display">Account Payable</h1>
                <div class="order-sub"><span x-text="tableData ? tableData.total : 0"></span> invoice dengan outstanding</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-primary" @click="openPicker()">
                    <x-misc.icon name="plus" :size="15" />New Payment
                </button>
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
            <input class="input" style="height:32px; width:200px;" placeholder="Cari nomor invoice / supplier..."
                x-model="search" x-on:input.debounce.400ms="page = 1; fetchData()" />
            <button class="btn btn-ghost btn-sm" @click="showFilters = !showFilters">
                <x-misc.icon name="filter" :size="13" />Filters
            </button>
        </div>

        <div class="card" x-show="showFilters" x-cloak
            style="padding:12px 16px; margin-bottom:12px; display:flex; gap:12px; align-items:flex-end;">
            <x-misc.field label="Dari Tanggal">
                <input type="date" class="input" style="height:32px;" x-model="dateFrom"
                    x-on:change="page = 1; fetchData()" />
            </x-misc.field>
            <x-misc.field label="Sampai Tanggal">
                <input type="date" class="input" style="height:32px;" x-model="dateTo"
                    x-on:change="page = 1; fetchData()" />
            </x-misc.field>
            <button class="btn btn-ghost btn-sm" @click="dateFrom = ''; dateTo = ''; page = 1; fetchData()">
                <x-misc.icon name="x" :size="13" />Reset
            </button>
        </div>

        <div class="card table-card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">No</th>
                        <th>Invoice No.</th>
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
                        <template x-for="(row, i) in tableData.data" :key="row.id">
                            <tr class="row-tap" @click="window.location = route('finances.account_payables.show', row.id)">
                                <td class="mono" style="color:var(--ink-4);"
                                    x-text="(tableData.current_page - 1) * tableData.per_page + i + 1"></td>
                                <td class="mono" style="font-weight:600;" x-text="row.number"></td>
                                <td style="font-weight:500;" x-text="row.supplier?.name ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.invoice_date ?? '-'"></td>
                                <td style="color:var(--ink-3);" x-text="row.due_date ?? '-'"></td>
                                <td class="num" style="text-align:right;"
                                    x-text="NumberUtils.formatNumericIntoMask(row.total_amount)"></td>
                                <td class="num" style="text-align:right; font-weight:600;"
                                    x-text="NumberUtils.formatNumericIntoMask(row.remaining_amount)"></td>
                                <td>
                                    <span :class="statusChip(row.display_status).chip">
                                        <span :class="statusChip(row.display_status).dot"></span>
                                        <span x-text="statusChip(row.display_status).label"></span>
                                    </span>
                                </td>
                                <td x-on:click.stop>
                                    <a :href="route('finances.account_payables.show', row.id)"
                                        class="btn btn-ghost btn-icon btn-sm" style="border:none;">
                                        <x-misc.icon name="eye" :size="15" stroke="var(--ink-3)" />
                                    </a>
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

        {{-- New Payment: pick invoice --}}
        <x-misc.modal title="Pilih Invoice" show="pickerOpen" close-handler="pickerOpen = false" :width="480">
            <div class="form-body">
                <x-misc.field label="Invoice">
                    <x-misc.select display="pickerSelectedLabel" hasValue="!!pickerSelected"
                        placeholder="Cari nomor invoice / supplier..." min-width="420px" height="40px">
                        <template
                            x-for="inv in pickerInvoices.filter(i => !q || i.number.toLowerCase().includes(q.toLowerCase()) || (i.supplier?.name || '').toLowerCase().includes(q.toLowerCase()))"
                            :key="inv.id">
                            <div class="dropdown-item" @click="pickerSelected = inv; open = false; q = ''">
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:13px; font-weight:600;" x-text="inv.number"></div>
                                    <div style="font-size:11.5px; color:var(--ink-4);"
                                        x-text="(inv.supplier?.name ?? '-') + ' · Outstanding ' + NumberUtils.formatNumericIntoMask(inv.remaining_amount)">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="pickerInvoices.length === 0">
                            <div class="dropdown-empty">Memuat...</div>
                        </template>
                    </x-misc.select>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" @click="pickerOpen = false">
                    <x-misc.icon name="x" :size="14" />Batal
                </button>
                <button class="btn btn-primary" :disabled="!pickerSelected" @click="goToPicked()">
                    Lanjutkan<x-misc.icon name="chev-right" :size="14" />
                </button>
            </x-slot:footer>
        </x-misc.modal>
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
                pickerOpen: false,
                pickerInvoices: [],
                pickerSelected: null,

                get pickerSelectedLabel() {
                    return this.pickerSelected ? (this.pickerSelected.number + ' - ' + (this.pickerSelected.supplier
                        ?.name ?? '-')) : 'Pilih invoice';
                },

                statusChip(status) {
                    const map = {
                        'not-yet-due': {
                            chip: 'chip chip-ok',
                            dot: 'chip-dot dot-ok',
                            label: 'Not Yet Due'
                        },
                        'unpaid': {
                            chip: 'chip chip-bad',
                            dot: 'chip-dot dot-bad',
                            label: 'Unpaid'
                        },
                        'partial': {
                            chip: 'chip chip-warn',
                            dot: 'chip-dot dot-warn',
                            label: 'Partial'
                        },
                        'paid': {
                            chip: 'chip',
                            dot: 'chip-dot dot-neutral',
                            label: 'Paid'
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

                async openPicker() {
                    this.pickerOpen = true;
                    this.pickerSelected = null;
                    if (this.pickerInvoices.length === 0) {
                        try {
                            const res = await axios.get(route('finances.account_payables.datatable'), {
                                params: {
                                    per_page: 100,
                                    status: 'all'
                                },
                            });
                            this.pickerInvoices = res.data.data;
                        } catch (error) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Gagal memuat daftar invoice.'
                            });
                        }
                    }
                },

                goToPicked() {
                    if (!this.pickerSelected) return;
                    window.location = route('finances.account_payables.show', this.pickerSelected.id) + '?pay=1';
                },
            };
        }
    </script>
@endpush
