@extends('layouts.app')
@section('content')
    <script>
        function warehouseDetailModule() {
            return {
                search: {
                    product: '',
                    batch: ''
                },
                modal: null,
                warehouse: @json($warehouse),
                products: @json($products),
                batches: @json($batches),
                form: {
                    id: null,
                    code: null,
                    name: null,
                    address: null,
                    note: null
                },

                get filteredProducts() {
                    if (!this.search.product) return this.products;
                    const q = this.search.product.toLowerCase();
                    return this.products.filter(p =>
                        p.product.name.toLowerCase().includes(q) ||
                        p.product.code.toLowerCase().includes(q)
                    );
                },

                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

                openEditModal() {
                    this.form = {
                        id: this.warehouse.id,
                        code: this.warehouse.code,
                        name: this.warehouse.name,
                        address: this.warehouse.address,
                        note: this.warehouse.note
                    };
                    this.modal = 'edit_warehouse';
                },

                async handleUpdate() {
                    Swal.fire({
                        title: 'Konfirmasi Perubahan Gudang',
                        text: 'Apakah anda yakin ingin memperbarui gudang dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, perbarui gudang',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.form,
                            };

                            Swal.fire({
                                title: 'Memproses perubahan Gudang...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('master.warehouses.update', this.form.id), body
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
            };
        }
    </script>
    <div class="order-page" x-data="warehouseDetailModule()">

        {{-- Header --}}
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('master.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" /> Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $warehouse->name }}</h1>
                    <span class="chip mono" style="font-size:11px;">{{ $warehouse->code }}</span>
                </div>
                <div class="order-sub">
                    {{ $warehouse->address }}
                </div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost" x-on:click="openEditModal()">
                    <x-misc.icon name="edit" :size="14" /> Edit Gudang
                </button>
                <a href="{{ route('master.warehouses.warehouse_transfers.create', $warehouse->id) }}" class="btn btn-ghost">
                    <x-misc.icon name="swap" :size="14" /> Transfer Gudang
                </a>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="produk-stat-grid" style="grid-template-columns: repeat(3, 1fr);">

            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 155); color:oklch(0.45 0.14 155);">
                    {{ number_format($products->sum('quantity'), 2) }}
                </div>
                <div class="produk-stat__label">Total Stok</div>
                <div class="produk-stat__unit">{{ $products->count() }} Produk / {{ $batches->count() }} Batch</div>
            </div>

            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 45); color:oklch(0.45 0.14 45);">
                    {{ number_format($products->sum(fn($p) => $p->quantity * $p->average_unit_cost), 2) }}
                </div>
                <div class="produk-stat__label">Total Nilai Produk</div>
                <div class="produk-stat__unit">berdasarkan harga pokok</div>
            </div>

            <div class="card produk-stat">
                <div class="produk-stat__badge" style="background:oklch(0.92 0.06 0); color:oklch(0.45 0.14 0);">
                    {{ number_format($products->avg('average_unit_cost'), 2) }}
                </div>
                <div class="produk-stat__label">Rata-Rata HPP</div>
                <div class="produk-stat__unit">per produk</div>
            </div>

        </div>

        {{-- Products table --}}
        @include('master.warehouse.partials.stock-table', ['warehouse' => $warehouse])

        {{-- Batches table --}}
        @include('master.warehouse.partials.batch-table', ['warehouse' => $warehouse])

        {{-- Modals --}}
        @include('master.partials.modals.warehouse-modal')
    </div>
    @stack('stock-table-scripts')
    @stack('batch-table-scripts')
@endsection
