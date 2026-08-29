@extends('layouts.app')
@section('content')
    <script>
        function productDetailModule() {
            return {
                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },
                modal: null,
                product: @json($product),
                productCategories: @json($categories),
                productUnits: @json($units),
                form: {
                    id: null,
                    code: null,
                    name: null,
                    description: null,
                    category_id: null,
                    unit_id: null,
                },

                openEditModal() {
                    this.form = {
                        id: this.product.id,
                        code: this.product.code,
                        name: this.product.name,
                        description: this.product.description,
                        category_id: this.product.category_id,
                        unit_id: this.product.unit_id,
                    };
                    this.modal = 'edit_product';
                },

                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Produk',
                        text: 'Apakah anda yakin ingin memperbarui produk dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui produk',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses perubahan Produk...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('master.products.update', this.form.id), body
                                );
                                this.modal = null;
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                window.location.reload();
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
                    });
                }
            }
        }

        function productTransactionModule() {
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

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.products.transaction_datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                search: this.search,
                                product_id: '{{ $product->id }}',
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
    <div class="order-page" x-data="productDetailModule()">

        {{-- Header --}}
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('master.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" /> Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $product->name }}</h1>
                    <span class="chip">{{ $product->category->name }}</span>
                    <span class="chip mono" style="font-size:11px;">{{ $product->code }}</span>
                </div>
                <div class="order-sub">
                    {{ $product->description }}
                </div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost" x-on:click="openEditModal()"><x-misc.icon name="edit"
                        :size="14" /> Edit Produk</button>
                {{-- <button class="btn btn-primary" x-on:click="modal = 'penyesuaian'"><x-misc.icon name="plus"
                        :size="14" /> Penyesuaian Stok</button> --}}
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="produk-stat-grid">
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">
                    {{ number_format($stocks->sum('quantity'), 2) }}</div>
                <div class="produk-stat__label">Stok di tangan</div>
                <div class="produk-stat__unit">{{ $product->unit->symbol }}</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 220); color:oklch(0.45 0.14 220);">
                    {{ number_format($soldQty, 2) }}</div>
                <div class="produk-stat__label">Terjual</div>
                <div class="produk-stat__unit">Unit · Bulan Ini</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 45); color:oklch(0.45 0.14 45);">
                    {{ number_format($receivedQty, 2) }}</div>
                <div class="produk-stat__label">Diterima</div>
                <div class="produk-stat__unit">Unit · Bulan Ini</div>
            </div>
            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:var(--bg-2); color:var(--ink-2);">
                    {{ number_format($stocks->sum(function ($stock) {return $stock->quantity * $stock->average_unit_cost;}),2,'.',',') }}
                </div>
                <div class="produk-stat__label">Nilai</div>
                <div class="produk-stat__unit">Berdasarkan HPP Rata Rata</div>
            </div>
        </div>

        {{-- Body: transaksi + sidebar --}}
        <div class="produk-body">

            {{-- Left: transaksi --}}
            <div class="card" style="overflow:hidden;" x-data="productTransactionModule()" x-init="fetchData()">
                <div class="master-toolbar">
                    <div class="master-search">
                        <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                                stroke="var(--ink-4)" /></span>
                        <input class="input master-search__input" placeholder="Cari transaksi untuk produk ini..."
                            x-model="search.transaction" />
                    </div>
                </div>

                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Description</th>
                            <th>Batch</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Harga</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="tableData.data.length === 0">
                            <tr>
                                <td colspan="6"
                                    style="text-align:center; color:var(--ink-4); padding:32px; font-size:13px;">
                                    Belum ada transaksi untuk produk ini
                                </td>
                            </tr>
                        </template>
                        <template x-for="(t,i) in tableData.data" :key="i">
                            <tr>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="t.transaction_date"></td>
                                <td>
                                    <template x-if="t.reference_redirect">
                                        <a :href="t.reference_redirect" class="btn btn-ghost btn-sm" x-text="t.note">
                                    </template>
                                    <template x-else>
                                        <span x-text="t.note"></span>
                                    </template>
                                </td>
                                <td>
                                    <a :href="route('master.products.batches.show', { id: t.product_id, batch_id: t.product_batch_id })" class="btn btn-ghost btn-sm"  x-text="t.product_batch.batch_number"></a>
                                </td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.quantity)"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.unit_cost)"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="m(t.total_cost)"></td>
                                {{-- <td>
                                    <template x-if="t.reference_redirect">
                                        <a :href="t.reference_redirect" class="btn btn-ghost btn-sm">
                                            <x-misc.icon name="eye" :size="13" />Referensi
                                        </a>
                                    </template>
                                </td> --}}
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            

            {{-- Right: info sidebar --}}
            <div class="card produk-sidebar">

                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Informasi</div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Kategori</span>
                        <span class="chip">{{ $product->category->name }}</span>
                    </div>
                    <div class="produk-sidebar__row">
                        <span class="produk-sidebar__key">Satuan</span>
                        <span class="produk-sidebar__val">{{ $product->unit->name }} ({{ $product->unit->symbol }})</span>
                    </div>
                </div>

                <div class="produk-sidebar__section">
                    <div class="produk-sidebar__heading">Lokasi Gudang</div>
                    @foreach ($stocks as $stock)
                        <div style="margin-bottom:10px;">
                            <div style="display:flex; justify-content:space-between; font-size:12.5px; margin-bottom:4px;">
                                <span
                                    style="color:{{ $gudangColors[$loop->index] ?? 'var(--ink-3)' }}; font-weight:600;">{{ $stock->warehouse->name }}</span>
                                <span class="num"
                                    style="font-size:12px; color:var(--ink-3);">{{ number_format($stock->quantity, 2) }}
                                    {{ $product->unit->symbol }}</span>
                            </div>
                            <div style="height:4px; border-radius:999px; background:var(--line);">
                                <div
                                    style="height:4px; border-radius:999px; background:{{ $gudangColors[$loop->index] ?? 'var(--accent)' }}; width:{{ $stocks->sum('quantity') > 0 ? round(($stock->quantity / $stocks->sum('quantity')) * 100) : 0 }}%;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        @include('master.partials.modals.product-modal')
    </div>
@endsection
