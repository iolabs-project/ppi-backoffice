@extends('layouts.app')
@section('content')
    <script>
        function cashModule() {
            return {
                account: @json($account),
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
                modal: null,

                statusChip(status) {
                    const map = {
                        draft: {
                            chip: 'chip',
                            dot: 'chip-dot dot-muted',
                            label: 'Draft'
                        },
                        posted: {
                            chip: 'chip chip-ok',
                            dot: 'chip-dot dot-ok',
                            label: 'Posted'
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

                async fetchData() {
                    this.loading = true;
                    try {
                        const response = await axios.get(route('finances.cash.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                status: this.filter,
                                account_id: this.account.id,
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

                async cancel(accountID, transactionID, type) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membatalkan transaksi ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, batalkan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let url = '';
                            if (type === 'transfer') {
                                url = route('finances.cash.transfer.cancel', {
                                    id: accountID,
                                    transfer: transactionID
                                });
                            } else if (type === 'send') {
                                url = route('finances.cash.send.cancel', {
                                    id: accountID,
                                    send: transactionID
                                });
                            } else if (type === 'receive') {
                                url = route('finances.cash.receive.cancel', {
                                    id: accountID,
                                    receive: transactionID
                                });
                            } else {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Tipe transaksi tidak valid. Tidak dapat membatalkan.'
                                });
                                return;
                            }
                            Swal.fire({
                                title: 'Memproses...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            try {
                                const response = await axios.post(url);
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                console.error('Error during cancel transaction:', error);
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
            }
        }
    </script>
    @php
        use App\Enums\CashTransactionTypeEnum;
        $transfer = CashTransactionTypeEnum::TRANSFER->value;
        $send = CashTransactionTypeEnum::SEND->value;
        $receive = CashTransactionTypeEnum::RECEIVE->value;

        use App\Enums\CashTransactionStatusEnum;
        $draft = CashTransactionStatusEnum::DRAFT->value;
        $posted = CashTransactionStatusEnum::POSTED->value;
        $cancelled = CashTransactionStatusEnum::CANCELLED->value;
    @endphp
    <div x-data="cashModule()" x-init="fetchData()" class="kasbank-page">
        <div class="order-hd">
            <div>
                <a href="{{ route('finances.cash.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <h1 class="order-title display">{{ $account->name }} ({{ $account->code }})</h1>
            </div>
            <div class="order-actions">
                <a href="{{ route('finances.cash.transfer.create', $account->id) }}" class="btn btn-ghost">
                    <x-misc.icon name="swap" :size="14" />Transfer Dana
                </a>
                <a href="{{ route('finances.cash.send.create', $account->id) }}" class="btn btn-ghost">
                    <x-misc.icon name="send" :size="14" />Kirim Dana
                </a>
                <a href="{{ route('finances.cash.receive.create', $account->id) }}" class="btn btn-ghost">
                    <x-misc.icon name="inbox" :size="14" />Terima Dana
                </a>
            </div>
        </div>

        {{-- Total saldo card --}}
        <div class="card saldo-hero">
            <div>
                <div class="saldo-hero__label">Total Saldo Tersedia</div>
                <div class="saldo-hero__value display num">{{ number_format($account->balance, 2) }}</div>
                {{-- <div class="saldo-hero__sub">{{ count($activeAccounts) }} rekening · diperbarui hari ini</div> --}}
            </div>
            <div class="saldo-hero__icon">
                <x-misc.icon name="wallet" :size="56" />
            </div>
        </div>

        {{-- Recent transactions --}}
        <div class="card table-card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Transaksi</div>
                <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Akun</th>
                        <th>Ref</th>
                        <th style="text-align:right;">Jumlah</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && tableData.data.length === 0">
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && tableData.data.length > 0">
                        <template x-for="tx in tableData.data" :key="tx.id">
                            <tr>
                                <td style="color:var(--ink-3); white-space:nowrap;" x-text="tx.transaction_date"></td>
                                <td style="font-weight:500;" x-text="tx.description"></td>
                                <td>
                                    <template x-if="tx.type === '{{ $transfer }}'">
                                        <div style="display:flex; align-items:center; gap:4px;">
                                            <span class="chip chip-info">
                                                <span x-text="tx.from_account.name"></span>
                                            </span>
                                            <template x-if="tx.to_account">
                                                <div style="display:flex; align-items:center; gap:4px;">
                                                    <x-misc.icon name="arrow" :size="12" />
                                                    <span class="chip chip-info">
                                                        <span x-text="tx.to_account.name"></span>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="tx.type === '{{ $send }}'">
                                        <span class="chip chip-info">
                                            <span x-text="tx.from_account.name"></span>
                                        </span>
                                    </template>
                                    <template x-if="tx.type === '{{ $receive }}'">
                                        <span class="chip chip-info">
                                            <span x-text="tx.to_account.name"></span>
                                        </span>
                                    </template>
                                </td>
                                <td class="mono" style="font-size:11.5px; color:var(--ink-4);"></td>
                                <td class="num" style="text-align:right; color:var(--ink-5); font-weight:400;"
                                    x-text="m(tx.total_amount)"></td>
                                <td>
                                    <span :class="statusChip(tx.status).chip">
                                        <span :class="statusChip(tx.status).dot"></span>
                                        <span x-text="statusChip(tx.status).label"></span>
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
                                            <template x-if="tx.status === '{{ $draft }}'">
                                                <div>
                                                    <template x-if="tx.type === '{{ $transfer }}'">
                                                        <div>
                                                            <a :href="route('finances.cash.transfer.edit', {
                                                                id: '{{ $account->id }}',
                                                                transfer: tx.id
                                                            })"
                                                                @click.stop class="action-menu__item">
                                                                <x-misc.icon name="edit" :size="14"
                                                                    stroke="var(--ink-3)" />
                                                                Edit Transaksi
                                                            </a>

                                                        </div>
                                                    </template>
                                                    <template x-if="tx.type === '{{ $receive }}'">
                                                        <div>
                                                            <a :href="route('finances.cash.receive.edit', {
                                                                id: '{{ $account->id }}',
                                                                receive: tx.id
                                                            })"
                                                                @click.stop class="action-menu__item">
                                                                <x-misc.icon name="edit" :size="14"
                                                                    stroke="var(--ink-3)" />
                                                                Edit Transaksi
                                                            </a>

                                                        </div>
                                                    </template>
                                                    <div class="action-menu__divider"></div>
                                                    <button class="action-menu__item action-menu__item--danger"
                                                        @click.stop="cancel(tx.from_account.id, tx.id, tx.type)">
                                                        <x-misc.icon name="trash" :size="14"
                                                            stroke="currentColor" />Batalkan
                                                        Transaksi
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="tx.status === '{{ $posted }}'">
                                                <div>
                                                    <a href="#" @click.stop class="action-menu__item">
                                                        <x-misc.icon name="eye" :size="14"
                                                            stroke="var(--ink-3)" />
                                                        Lihat Detail
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
                        :disabled="!tableData || !tableData.prev_page_url"><x-misc.icon name="chev-left"
                            :size="13" />
                        Prev</button>
                    <button class="btn btn-ghost btn-sm" @click="next()"
                        :disabled="!tableData || !tableData.next_page_url">Next
                        <x-misc.icon name="chev-right" :size="13" /></button>
                </div>
            </div>
        </div>


    </div>
@endsection
