@extends('layouts.app')
@section('content')
    <script>
        const deliveryOrder = @json($deliveryOrder);
        console.log(deliveryOrder);
        const remainingSOItems = @json($remainingSOItems);
        const availableBatches = @json($availableBatches);

        function deliveryOrderForm() {
            return {
                modal: null,
                activeItemIndex: null,
                batchForm: {
                    product_batch_id: null,
                    quantity: null,
                },

                formData: {
                    id: deliveryOrder.id || null,
                    reference_number: deliveryOrder.reference_number || null,
                    delivery_date: deliveryOrder.delivery_date ? deliveryOrder.delivery_date.substring(0, 10) : null,
                    note: deliveryOrder.note || null,
                    costs: (deliveryOrder.costs || []).map(cost => ({
                        account_id: cost.account_id,
                        description: cost.description,
                        amount: cost.amount,
                    })),
                    details: (deliveryOrder.items || []).map(item => {
                        const soItem = remainingSOItems.find(s => s.id === item.sales_order_item_id);
                        return {
                            sales_order_item_id: item.sales_order_item_id,
                            product_id: item.product_id,
                            code: item.product?.code,
                            name: item.product?.name,
                            unit: item.product?.unit?.symbol,
                            quantity_ordered: soItem ? soItem.quantity : 0,
                            quantity_previously_delivered: soItem ? soItem.shipped_quantity : 0,
                            remaining_quantity: soItem ? soItem.remaining_quantity : 0,
                            batches: (item.batches || []).map(b => ({
                                product_batch_id: b.product_batch_id,
                                batch_number: b.product_batch?.batch_number,
                                quantity: b.quantity,
                                unit_cost: b.unit_cost,
                            })),
                        };
                    }),
                },

                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                addCost() {
                    this.formData.costs.push({
                        account_id: null,
                        description: null,
                        amount: null,
                    });
                },
                removeCost(index) {
                    this.formData.costs.splice(index, 1);
                },
                handleCostInput() {},
                totalAdditionalCosts() {
                    return this.formData.costs.reduce((sum, c) => sum + this.n(c.amount), 0);
                },

                init() {
                    Object.assign(this, window.deliveryOrderTable);
                },

                openBatchModal(index) {
                    this.activeItemIndex = index;
                    this.batchForm = {
                        product_batch_id: null,
                        quantity: null
                    };
                    this.modal = 'add_batch';
                },

                closeBatchModal() {
                    this.modal = null;
                    this.activeItemIndex = null;
                },

                async submitDraft() {
                    await this.submit('draft');
                },

                async submitFinish() {
                    if (!this.validateBatches()) {
                        return;
                    }

                    Swal.fire({
                        title: 'Apakah Anda yakin ingin menyelesaikan pengiriman barang ini?',
                        text: 'Setelah diselesaikan, pengiriman barang akan membentuk jurnal umum dan mengurangi stok di gudang asal.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, selesaikan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            await this.submit('finished');
                        }
                    });
                },

                validateBatches() {
                    for (const item of this.formData.details) {
                        if (!item.product_id) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Terdapat baris produk yang belum dipilih.'
                            });
                            return false;
                        }
                        const batchTotal = item.batches.reduce((sum, b) => sum + this.n(b.quantity), 0);
                        if (batchTotal <= 0) {
                            Toast.fire({
                                icon: 'error',
                                title: `Belum ada batch dipilih untuk ${item.name}.`
                            });
                            return false;
                        }
                    }
                    return true;
                },

                async submit(status) {
                    let body = {
                        ...this.formData,
                        status,
                    };
                    body.details = body.details.map(d => {
                        const batches = d.batches.map(b => ({
                            ...b,
                            quantity: this.n(b.quantity),
                        }));
                        return {
                            ...d,
                            quantity: batches.reduce((sum, b) => sum + b.quantity, 0),
                            batches,
                        };
                    });
                    body.costs = body.costs
                        .filter(c => c.account_id && this.n(c.amount) > 0)
                        .map(c => ({
                            ...c,
                            amount: this.n(c.amount)
                        }));

                    Swal.fire({
                        title: 'Memproses penyimpanan Pengiriman Barang...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('sales.delivery_orders.update', this.formData.id), body
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

    <div x-data="deliveryOrderForm()" x-init="init()" class="order-page">
        <div>
            <a href="{{ route('sales.delivery_orders.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <div class="order-title-row">
                <h1 class="order-title display">Edit Pengiriman Barang</h1>
                <x-misc.status-badge status="draft" />
            </div>
            <div class="order-sub">
                Pengiriman yang berhasil disimpan akan otomatis membentuk jurnal umum dan <strong
                    style="color:var(--ink);">mengurangi stok</strong> di gudang asal.
            </div>
        </div>

        <div class="card card-bd--form">
            <div class="shipping-form-info">
                <div class="display card-hd-title">Informasi Pengiriman</div>
                <div class="shipping-form-info__sub"><span style="color:var(--accent);">*</span> Field terisi otomatis dari
                    SO</div>
            </div>
            <div class="order-form-grid-3">
                <x-misc.field label="Customer" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
                        <x-misc.avatar :name="$deliveryOrder->customer->name" />
                        <span style="flex:1; font-weight:500;">{{ $deliveryOrder->customer->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="No. Pemesanan" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $deliveryOrder->salesOrder->number }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="Gudang Asal" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
                        <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
                        <span style="flex:1;">{{ $deliveryOrder->warehouse->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="No. Pengiriman">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $deliveryOrder->number }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="Tanggal Pengiriman" :required="true">
                    <input type="date" class="input" x-model="formData.delivery_date" />
                </x-misc.field>
                <x-misc.field label="Nomor Referensi"><input class="input mono" placeholder="(opsional)"
                        x-model="formData.reference_number" /></x-misc.field>
            </div>
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Daftar Produk</div>
                <button class="btn btn-ghost btn-sm" @click="addProduct()">
                    <x-misc.icon name="plus" :size="13" />Tambah Produk
                </button>
            </div>
            @include('sales.delivery-order.partials.edit.item-table')
        </div>

        @include('sales.partials.additional-cost-table', ['accounts' => $accounts])

        <div class="card" style="overflow:visible;">
            <div class="order-items-split">
                <div class="order-extras">
                    <x-misc.field label="Catatan Penerimaan">
                        <textarea class="input" rows="2"
                            placeholder="Catat kondisi barang, kekurangan, atau informasi penting lainnya..." x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>
                <div class="order-summary">
                    <div class="display order-summary__title">Ringkasan Biaya</div>
                    <div class="order-summary__grid">
                        <div class="order-summary__group">
                            <div class="order-summary__row">
                                <span class="order-summary__label">Biaya Tambahan (Internal)</span>
                                <span class="num order-summary__val"
                                    x-text="NumberUtils.formatNumericIntoMask(totalAdditionalCosts())"></span>
                            </div>
                        </div>
                    </div>
                    <div class="order-summary__total">
                        <span class="order-summary__total-label">Total Biaya</span>
                        <span class="num order-summary__total-value"
                            x-text="NumberUtils.formatNumericIntoMask(totalAdditionalCosts())"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submitDraft()">Simpan Draft</button>
            <button class="btn btn-primary" @click="submitFinish()"><x-misc.icon name="check"
                    :size="14" />Selesaikan</button>
        </div>
    </div>
@endsection
