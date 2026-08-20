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
                filter: 'all',

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
                        const response = await axios.get(route('expenses.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                status: this.filter,
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

                async handleCancel(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membatalkan Biaya ini?',
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
                                    'expenses.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let title = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                                let html = null;
                                if (error.response?.status === 422) {
                                    title = 'Validasi gagal. Silakan periksa kembali input Anda.';
                                    html = '<ul style="text-align:left; margin:0; padding-left:20px;">' +
                                        Object.values(error.response.data.errors)
                                        .flat()
                                        .map(msg => `<li>${msg}</li>`)
                                        .join('') +
                                        '</ul>';
                                } else if (error.response?.data?.message) {
                                    title = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: title,
                                    html: html
                                });
                            }

                        }
                    })
                },
            };
        }
    </script>

    @php
        use App\Enums\ExpenseStatus;
        $draft = ExpenseStatus::DRAFT->value;
        $open = ExpenseStatus::OPEN->value;
    @endphp
    <div x-data="datatable()" x-init="fetchData()" class="biaya-page">

        <div class="order-hd">
            <div>
                <h1 class="order-title display">Biaya</h1>
                <div class="order-sub">Pencatatan dan persetujuan biaya operasional</div>
            </div>
            <div class="order-actions">
                <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                    <x-misc.icon name="plus" :size="14" />Tambah Biaya
                </a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="biaya-summary">
            <div class="card stat-card stat-card--dark">
                <div class="stat-card__label">Total Biaya Bulan Ini</div>
                <div class="stat-card__value display num">{{ number_format($summary['total_expenses'], 2) }}</div>
                {{-- <div class="stat-card__sub"><span x-text="tableData.data.length"></span> entri tercatat</div> --}}
            </div>
            <div class="card stat-card stat-card--good">
                <div class="stat-card__label">Sudah Dibayarkan</div>
                <div class="stat-card__value display num">{{ number_format($summary['total_paid_expenses'], 2) }}</div>
                {{-- <div class="stat-card__sub">{{ collect($data)->where('status', 'disetujui')->count() }} entri</div> --}}
            </div>
            <div class="card stat-card stat-card--warn">
                <div class="stat-card__label">Belum Dibayarkan</div>
                <div class="stat-card__value display num">{{ number_format($summary['total_open_expenses'], 2) }}</div>
                {{-- <div class="stat-card__sub">{{ collect($data)->where('status', 'menunggu')->count() }} entri</div> --}}
            </div>
            <div class="card stat-card stat-card--bad">
                <div class="stat-card__label">Dibatalkan</div>
                <div class="stat-card__value display num">{{ number_format($summary['total_cancelled_expenses'], 2) }}</div>
                {{-- <div class="stat-card__sub">{{ collect($data)->where('status', 'ditolak')->count() }} entri</div> --}}
            </div>
        </div>

        {{-- Table card --}}
        <div class="card" style="overflow:hidden;">
            <div class="master-toolbar" style="justify-content:space-between;">
                <div class="filter-pills">
                    @foreach ($status as $s)
                        <button x-on:click="setStatus('{{ $s['id'] }}')"
                            :class="filter === '{{ $s['id'] }}' ? 'filter-pill filter-pill--active' : 'filter-pill'">
                            <span x-text="'{{ $s['name'] }}'"></span>
                        </button>
                    @endforeach
                </div>
                <button class="btn btn-ghost btn-sm">
                    <x-misc.icon name="download" :size="13" />Ekspor
                </button>
            </div>

            <table class="tbl">
                <thead>
                    <tr>
                        <th>No. Biaya</th>
                        <th>Tanggal</th>
                        <th>No. Ref</th>
                        <th>Penerima</th>
                        <th style="text-align:right;">Sisa</th>
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
                                @click="window.location = route('expenses.show', row.id)">
                                <td class="mono" style="font-weight:600;" x-text="row.number"></td>
                                <td style="color:var(--ink-3);" x-text="row.expense_date ?? '-'"></td>
                                <td class="mono" style="font-weight:600;" x-text="row.reference_number"></td>
                                <td style="font-weight:500;" x-text="row.contact?.name ?? '-'"></td>
                                <td class="num" style="text-align:right;"
                                    x-text="NumberUtils.formatNumericIntoMask(row.remaining_amount)"></td>
                                <td class="num" style="text-align:right;"
                                    x-text="NumberUtils.formatNumericIntoMask(row.total_amount)"></td>
                                {{-- <td><span class="mono" x-text="row.status"></span></td> --}}
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
                                            <button class="action-menu__item" @click.stop>
                                                <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                                Biaya
                                            </button>
                                            <template x-if="row.status === '{{ $draft }}'">
                                                <div>
                                                    <a :href="route('expenses.edit', row.id)" @click.stop
                                                        class="action-menu__item">
                                                        <x-misc.icon name="edit" :size="14"
                                                            stroke="var(--ink-3)" />Edit
                                                        Biaya
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
                                                        Biaya
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

    </div>
@endsection
