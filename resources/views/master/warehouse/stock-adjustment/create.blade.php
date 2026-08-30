@extends('layouts.app')
@section('content')
    <script>
        function stockAdjustmentForm() {
            return {
                warehouse: @json($warehouse),
                formData: {
                    number: '{{ $number }}',
                    adjustment_date: "{{ now()->format('Y-m-d') }}",
                    note: null,
                    details: [{
                        product_batch_id: null,
                        product_id: null,
                        product_name: null,
                        product_code: null,
                        batch_number: null,
                        unit: null,
                        system_quantity: null,
                        unit_cost: null,
                        counted_quantity: null,
                    }],
                },

                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },
                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

                addDetail() {
                    this.formData.details.push({
                        product_batch_id: null,
                        product_id: null,
                        product_name: null,
                        product_code: null,
                        batch_number: null,
                        unit: null,
                        system_quantity: null,
                        unit_cost: null,
                        counted_quantity: null,
                    });
                },
                deleteDetail(index) {
                    this.formData.details.splice(index, 1);
                },
                excludedBatchIds() {
                    return this.formData.details
                        .map(d => d.product_batch_id)
                        .filter(id => id !== null);
                },
                selectBatch(item, batch) {
                    item.product_batch_id = batch.id;
                    item.product_id = batch.product_id;
                    item.product_name = batch.product.name;
                    item.product_code = batch.product.code;
                    item.batch_number = batch.batch_number;
                    item.unit = batch.product.unit.symbol;
                    item.system_quantity = batch.quantity;
                    item.unit_cost = batch.unit_cost;
                    item.counted_quantity = null;
                },
                difference(item) {
                    if (item.product_batch_id === null || item.counted_quantity === null || item.counted_quantity === '') return null;
                    return this.n(item.counted_quantity) - item.system_quantity;
                },
                hasValidDetails() {
                    return this.formData.details.length > 0 &&
                        this.formData.details.every(d => d.product_batch_id &&
                            d.counted_quantity !== null && d.counted_quantity !== '' &&
                            this.n(d.counted_quantity) >= 0);
                },

                buildBody() {
                    return {
                        adjustment_date: this.formData.adjustment_date,
                        note: this.formData.note,
                        details: this.formData.details.map(d => ({
                            product_id: d.product_id,
                            product_batch_id: d.product_batch_id,
                            counted_quantity: this.n(d.counted_quantity),
                        })),
                    };
                },

                async submit() {
                    if (!this.hasValidDetails()) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Periksa kembali rincian produk yang disesuaikan.'
                        });
                        return;
                    }

                    const result = await Swal.fire({
                        title: 'Konfirmasi Penyesuaian Stok',
                        text: 'Apakah anda yakin ingin membuat penyesuaian stok dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat penyesuaian',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    });

                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Memproses penyesuaian stok...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    try {
                        const response = await axios.post(
                            route('master.warehouses.stock_adjustments.store', this.warehouse.id), this.buildBody()
                        );
                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: response.data.message
                        });
                        window.location.href = response.data.redirect;
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
                },
            };
        }
    </script>

    <div x-data="stockAdjustmentForm()" class="order-page">

        <div>
            <a href="{{ route('master.warehouses.show', $warehouse->id) }}" class="btn btn-ghost btn-sm"
                style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Penyesuaian Stok</h1>
            <div class="order-sub">Sesuaikan stok fisik untuk {{ $warehouse->name }}</div>
        </div>

        {{-- Info Penyesuaian --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Penyesuaian</div>
            <div class="order-form-grid-4">

                {{-- Nomor Penyesuaian --}}
                <x-misc.field label="Nomor Penyesuaian" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;" x-text="formData.number"></span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Gudang --}}
                <x-misc.field label="Gudang" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span x-text="warehouse.name"></span>
                    </div>
                </x-misc.field>

                {{-- Tanggal --}}
                <x-misc.field label="Tanggal Penyesuaian" :required="true">
                    <input type="date" class="input" x-model="formData.adjustment_date" />
                </x-misc.field>

            </div>
        </div>

        {{-- Items --}}
        <div class="card" style="overflow:visible;">
            <div class="card-hd">
                <div class="display card-hd-title">Daftar Produk</div>
                <button class="btn btn-ghost btn-sm" @click="addDetail()">
                    <x-misc.icon name="plus" :size="13" />Tambah Produk
                </button>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Pilih Produk / Batch</th>
                        <th style="width:120px; text-align:right;">Qty Sistem</th>
                        <th style="width:140px; text-align:right;">Qty Hasil Hitung</th>
                        <th style="width:120px; text-align:right;">Selisih</th>
                        <th style="width:100px;">Satuan</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(it, i) in formData.details" :key="i">
                        <tr>
                            <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="product-icon">
                                        <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                                    </div>
                                    <div style="flex:1;">
                                        <x-misc.async-select
                                            url="{{ route('master.products.options.batches') }}"
                                            display="it.product_batch_id ? it.product_name : 'Pilih Produk / Batch'"
                                            hasValue="it.product_batch_id"
                                            default="it.product_batch_id ? [{ id: it.product_batch_id, product_id: it.product_id, product: { name: it.product_name, code: it.product_code, unit: { symbol: it.unit } }, batch_number: it.batch_number, quantity: it.system_quantity, unit_cost: it.unit_cost }] : []"
                                            params="{ warehouse_id: warehouse.id, exclude_batch_ids: excludedBatchIds() }"
                                            placeholder="Cari produk atau batch..." min-width="320px" height="32px">
                                            <template x-for="b in items" :key="b.id">
                                                <div class="dropdown-item" @click="selectBatch(it, b);open=false;q=''">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="b.product.name"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="b.product.code + ' · ' + b.batch_number"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub"
                                                        x-text="m(b.quantity) + ' ' + b.product.unit.symbol"></span>
                                                </div>
                                            </template>
                                        </x-misc.async-select>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="it.product_code ? (it.product_code + ' · ' + it.batch_number) : '— belum dipilih'">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input input--readonly"
                                    style="height:32px; display:flex; align-items:center; justify-content:flex-end; padding:0 10px; color:var(--ink-3);">
                                    <span x-text="it.system_quantity !== null ? m(it.system_quantity) : '—'"></span>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;"
                                    x-model="it.counted_quantity" :disabled="!it.product_batch_id"
                                    x-mask:dynamic="$money($input, '.',',')" />
                            </td>
                            <td class="mono" style="text-align:right;"
                                :style="difference(it) > 0 ? 'color:var(--ok);' : (difference(it) < 0 ? 'color:var(--bad);' : 'color:var(--ink-3);')">
                                <span x-text="difference(it) !== null ? (difference(it) > 0 ? '+' : '') + m(difference(it)) : '—'"></span>
                            </td>
                            <td>
                                <div class="input input--readonly"
                                    style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                                    <span x-text="it.unit || '—'"></span>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    :disabled="formData.details.length <= 1"
                                    :style="formData.details.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                                    @click="deleteDetail(i)">
                                    <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Catatan --}}
        <div class="card" style="overflow:visible;">
            <div class="order-items-split2">
                <div class="order-extras">
                    <x-misc.field label="Catatan">
                        <textarea class="input" rows="2" placeholder="Tulis catatan untuk penyesuaian ini…"
                            x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <button class="btn btn-primary" @click="submit()">
                <x-misc.icon name="check" :size="14" />Buat Penyesuaian
            </button>
        </div>

    </div>
@endsection
