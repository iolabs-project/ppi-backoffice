@extends('layouts.app')
@section('content')
    <script>
        function batchDetailModule() {
            return {
                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },
                batch: @json($batch),
                product: @json($product),
            }
        }

        function batchTransactionModule() {
            return {
                search: '',
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    data: []
                },
                loading: false,
                page: 1,
                perPage: 10,
                perPageOptions: [10, 25, 50],
                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.products.transaction_datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search,
                                product_id: '{{ $batch->product_id }}',
                                product_batch_id: '{{ $batch->id }}',
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },
                next() {
                    if (this.page < this.tableData.last_page) {
                        this.page++;
                        this.fetchData();
                    }
                },
                prev() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchData();
                    }
                },
                handleSearch(q) {
                    this.search = q;
                    this.page = 1;
                    this.fetchData();
                },
            }
        }
    </script>
    <div class="order-page" x-data="batchDetailModule()">

        {{-- Header --}}
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('master.products.show', $product->id) }}" class="btn btn-ghost btn-sm"
                    style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" /> Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $batch->batch_number }}</h1>
                    <span class="chip mono" style="font-size:11px;">{{ $product->code }}</span>
                    @if ($batch->warehouse)
                        <span class="chip">{{ $batch->warehouse->name }}</span>
                    @endif
                </div>
                <div class="order-sub">
                    {{ $product->name }}
                </div>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="produk-stat-grid">
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">
                    {{ number_format($batch->available_quantity, 2) }}</div>
                <div class="produk-stat__label">Tersedia</div>
                <div class="produk-stat__unit">{{ $product->unit->symbol }}</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 220); color:oklch(0.45 0.14 220);">
                    {{ number_format($batch->quantity, 2) }}</div>
                <div class="produk-stat__label">Sisa Kuantitas</div>
                <div class="produk-stat__unit">{{ $product->unit->symbol }}</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 45); color:oklch(0.45 0.14 45);">
                    {{ number_format($batch->reserved_quantity, 2) }}</div>
                <div class="produk-stat__label">Dipesan (Reserved)</div>
                <div class="produk-stat__unit">{{ $product->unit->symbol }}</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:var(--bg-2); color:var(--ink-2);">
                    {{ number_format($batch->quantity * $batch->unit_cost, 2, '.', ',') }}
                </div>
                <div class="produk-stat__label">Nilai</div>
                <div class="produk-stat__unit">Berdasarkan HPP Batch</div>
            </div>
        </div>

        {{-- Body: transaksi + sidebar --}}
        <div class="produk-body">

            {{-- Left: transaksi --}}
            <div class="card" style="overflow:hidden;" x-data="batchTransactionModule()" x-init="fetchData()">
                <div class="master-toolbar">
                    <div class="master-search">
                        <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                                stroke="var(--ink-4)" /></span>
                        <input class="input master-search__input" placeholder="Cari transaksi untuk batch ini..."
                            x-model="search" x-on:input.debounce.400ms="handleSearch(search)" />
                    </div>
                </div>

                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Description</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Harga</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="tableData.data.length === 0">
                            <tr>
                                <td colspan="5"
                                    style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
                                    Belum ada transaksi untuk batch ini
                                </td>
                            </tr>
                        </template>
                        <template x-for="(t,i) in tableData.data" :key="i">
                            <tr>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="t.transaction_date"></td>
                                <td style="font-size:13px;">
                                    <template x-if="t.reference_redirect">
                                        <a :href="t.reference_redirect" class="btn btn-ghost btn-sm" x-text="t.note"></a>
                                    </template>
                                    <template x-if="!t.reference_redirect">
                                        <span x-text="t.note"></span>
                                    </template>
                                </td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.quantity)"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.unit_cost)"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.total_cost)"></td>
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
                                x-text="tableData.last_page"></strong></div>
                        <button class="btn btn-ghost btn-sm" @click="prev()" :disabled="page <= 1">
                            <x-misc.icon name="chev-left" :size="13" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" @click="next()" :disabled="page >= tableData.last_page">
                            Next<x-misc.icon name="chev-right" :size="13" />
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right: info sidebar --}}
            <div class="card produk-sidebar">

                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Informasi Batch</div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Produk</span>
                        <a href="{{ route('master.products.show', $product->id) }}" class="produk-sidebar__val">
                            {{ $product->name }}
                        </a>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Satuan</span>
                        <span class="produk-sidebar__val">{{ $product->unit->name }} ({{ $product->unit->symbol }})</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">No. Batch</span>
                        <span class="produk-sidebar__val mono">{{ $batch->batch_number }}</span>
                    </div>
                    @if ($batch->supplier_batch_number)
                        <div class="produk-sidebar__row">
                            <span class="produk-sidebar__key">No. Batch Supplier</span>
                            <span class="produk-sidebar__val mono">{{ $batch->supplier_batch_number }}</span>
                        </div>
                    @endif
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Gudang</span>
                        <span class="produk-sidebar__val">{{ $batch->warehouse->name ?? '-' }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">HPP Batch</span>
                        <span class="produk-sidebar__val num">{{ number_format($batch->unit_cost, 2, '.', ',') }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Kuantitas Awal</span>
                        <span class="produk-sidebar__val num">{{ number_format($batch->initial_quantity, 2) }}
                            {{ $product->unit->symbol }}</span>
                    </div>
                </div>

                @if ($batch->goodsReceiptItem && $batch->goodsReceiptItem->goodsReceipt)
                    <div class="produk-sidebar__section">
                        <div class="produk-sidebar__heading">Sumber</div>
                        <div class="produk-sidebar__row">
                            <span class="produk-sidebar__key">Penerimaan Barang</span>
                            <a href="{{ route('purchasings.goods_receipts.show', $batch->goodsReceiptItem->goodsReceipt->id) }}"
                                class="produk-sidebar__val mono">
                                {{ $batch->goodsReceiptItem->goodsReceipt->number }}
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
