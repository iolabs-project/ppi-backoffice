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
            }
        }
    </script>
    <div x-data="cashModule()" x-init="fetchData()" class="kasbank-page">
        <div class="order-hd">
            <div>
                <h1 class="order-title display">{{ $account->name }} ({{ $account->code }})</h1>
            </div>
            <div class="order-actions">
                <a href="#" class="btn btn-ghost">
                    <x-misc.icon name="swap" :size="14" />Transfer Dana
                </a>
                <a href="#" class="btn btn-ghost">
                    <x-misc.icon name="send" :size="14" />Kirim Dana
                </a>
                <a href="#" class="btn btn-ghost">
                    <x-misc.icon name="inbox" :size="14" />Terima Dana
                </a>
            </div>
        </div>

        {{-- Total saldo card --}}
        <div class="card saldo-hero">
            <div>
                <div class="saldo-hero__label">Total Saldo Tersedia</div>
                <div class="saldo-hero__value display num">{{ number_format($account->balance, 2, '.', ',') }}</div>
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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && tableData.data.length === 0">
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && tableData.data.length > 0" x-for="tx in tableData.data"
                        :key="tx.id">
                        <tr>
                            <td style="color:var(--ink-3); white-space:nowrap;" x-text="tx.transaction_date"></td>
                            <td style="font-weight:500;" x-text="tx.note"></td>
                            <td style="font-size:12px; color:var(--ink-4);"></td>
                            <td class="mono" style="font-size:11.5px; color:var(--ink-4);"></td>
                            <td class="num" style="text-align:right; color:var(--ink-5); font-weight:400;"
                                x-text="m(tx.total_amount)"></td>
                            <td></td>
                        </tr>
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
