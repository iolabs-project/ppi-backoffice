@extends('layouts.app')
@section('content')
    <script>
        const batches = @json($batches);

        function warehouseTransferForm() {
            return {
                warehouse: @json($warehouse),
                warehouses: @json($warehouses),
                formData: {
                    to_warehouse_id: null,
                    number: '{{ $number }}',
                    transfer_date: "{{ now()->format('Y-m-d') }}",
                    note: null,
                    details: [{
                        product_batch_id: null,
                        product_id: null,
                        product_name: null,
                        product_code: null,
                        batch_number: null,
                        unit: null,
                        available_quantity: null,
                        unit_cost: null,
                        quantity: null,
                    }],
                },
                toWarehouseSelected: null,

                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },
                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },
                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
                },

                addDetail() {
                    this.formData.details.push({
                        product_batch_id: null,
                        product_id: null,
                        product_name: null,
                        product_code: null,
                        batch_number: null,
                        unit: null,
                        available_quantity: null,
                        unit_cost: null,
                        quantity: null,
                    });
                },
                deleteDetail(index) {
                    this.formData.details.splice(index, 1);
                },
                availableBatches(q) {
                    let list = batches.filter(b => !this.formData.details.some(d => d.product_batch_id === b.id));

                    if (q) {
                        const s = q.toLowerCase();
                        list = list.filter(b =>
                            (b.product.name || '').toLowerCase().includes(s) ||
                            (b.product.code || '').toLowerCase().includes(s) ||
                            (b.batch_number || '').toLowerCase().includes(s)
                        );
                    }

                    return list;
                },
                selectBatch(item, batch) {
                    item.product_batch_id = batch.id;
                    item.product_id = batch.product_id;
                    item.product_name = batch.product.name;
                    item.product_code = batch.product.code;
                    item.batch_number = batch.batch_number;
                    item.unit = batch.product.unit.symbol;
                    item.available_quantity = batch.available_quantity;
                    item.unit_cost = batch.unit_cost;
                    item.quantity = null;
                },
                isOverQuantity(item) {
                    return item.available_quantity !== null && this.n(item.quantity) > item.available_quantity;
                },
                hasValidDetails() {
                    return this.formData.details.length > 0 &&
                        this.formData.details.every(d => d.product_batch_id && this.n(d.quantity) > 0 && !this
                            .isOverQuantity(d));
                },

                buildBody() {
                    return {
                        ...this.formData,
                        details: this.formData.details.map(d => ({
                            product_id: d.product_id,
                            product_batch_id: d.product_batch_id,
                            quantity: this.n(d.quantity),
                        })),
                    };
                },

                async submit() {
                    if (!this.formData.to_warehouse_id) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Gudang tujuan harus diisi.'
                        });
                        return;
                    }
                    if (!this.hasValidDetails()) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Periksa kembali rincian produk yang ditransfer.'
                        });
                        return;
                    }

                    const result = await Swal.fire({
                        title: 'Konfirmasi Transfer Gudang',
                        text: 'Apakah anda yakin ingin membuat transfer gudang dengan data yang telah diisi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat transfer',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    });

                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Memproses transfer gudang...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    try {
                        const response = await axios.post(
                            route('master.warehouses.warehouse_transfers.store', this.warehouse.id), this.buildBody()
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

    <div x-data="warehouseTransferForm()" class="order-page">

        <div>
            <a href="{{ route('master.warehouses.show', $warehouse->id) }}" class="btn btn-ghost btn-sm"
                style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Transfer Gudang</h1>
            <div class="order-sub">Pindahkan stok dari {{ $warehouse->name }} ke gudang lain</div>
        </div>

        {{-- Info Transfer --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Transfer</div>
            <div class="order-form-grid-4">

                {{-- Nomor Transfer --}}
                <x-misc.field label="Nomor Transfer" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;" x-text="formData.number"></span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Gudang Asal --}}
                <x-misc.field label="Gudang Asal" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span x-text="warehouse.name"></span>
                    </div>
                </x-misc.field>

                {{-- Gudang Tujuan --}}
                <x-misc.field label="Gudang Tujuan" :required="true">
                    <x-misc.select display="toWarehouseSelected ? toWarehouseSelected.name : 'Pilih Gudang Tujuan'"
                        hasValue="toWarehouseSelected" placeholder="Cari gudang...">
                        <template
                            x-for="g in warehouses.filter(g => g.id !== warehouse.id && (!q || g.name.toLowerCase().includes(q.toLowerCase())))"
                            :key="g.id">
                            <div class="dropdown-item"
                                @click="toWarehouseSelected=g; formData.to_warehouse_id=g.id; open=false; q=''">
                                <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                    x-text="initials(g.name)"></div>
                                <span x-text="g.name"></span>
                            </div>
                        </template>
                        <template
                            x-if="!warehouses.some(g => g.id !== warehouse.id && (!q || g.name.toLowerCase().includes(q.toLowerCase())))">
                            <div class="dropdown-empty">Tidak ditemukan</div>
                        </template>
                    </x-misc.select>
                </x-misc.field>

                {{-- Tanggal --}}
                <x-misc.field label="Tanggal Transfer" :required="true">
                    <input type="date" class="input" x-model="formData.transfer_date" />
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
                        <th style="width:140px; text-align:right;">Stok Tersedia</th>
                        <th style="width:140px; text-align:right;">Qty Transfer</th>
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
                                        <x-misc.select
                                            display="it.product_batch_id ? it.product_name : 'Pilih Produk / Batch'"
                                            hasValue="it.product_batch_id" placeholder="Cari produk atau batch..."
                                            min-width="320px" height="32px">
                                            <template x-for="b in availableBatches(q)" :key="b.id">
                                                <div class="dropdown-item" @click="selectBatch(it, b);open=false;q=''">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="b.product.name"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="b.product.code + ' · ' + b.batch_number"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub"
                                                        x-text="m(b.available_quantity) + ' ' + b.product.unit.symbol"></span>
                                                </div>
                                            </template>
                                            <template x-if="availableBatches(q).length === 0">
                                                <div class="dropdown-empty">Tidak ditemukan</div>
                                            </template>
                                        </x-misc.select>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="it.product_code ? (it.product_code + ' · ' + it.batch_number) : '— belum dipilih'">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input input--readonly"
                                    style="height:32px; display:flex; align-items:center; justify-content:flex-end; padding:0 10px; color:var(--ink-3);">
                                    <span x-text="it.available_quantity !== null ? m(it.available_quantity) : '—'"></span>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" x-model="it.quantity"
                                    :style="isOverQuantity(it) ? 'border-color:var(--danger);' : ''"
                                    x-mask:dynamic="$money($input, '.',',')" />
                                <template x-if="isOverQuantity(it)">
                                    <div style="font-size:11px; color:var(--danger); margin-top:2px; text-align:right;">
                                        Melebihi stok tersedia
                                    </div>
                                </template>
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
                        <textarea class="input" rows="2" placeholder="Tulis catatan untuk transfer ini…"
                            x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <button class="btn btn-primary" @click="submit()">
                <x-misc.icon name="check" :size="14" />Buat Transfer
            </button>
        </div>

    </div>
@endsection
