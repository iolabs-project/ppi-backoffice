@extends('layouts.app')
@section('content')
    <script>
        const goodsReceipt = @json($goodsReceipt);
        const remainingPOItems = @json($remainingPOItems);
        console.log('goodsReceipt', goodsReceipt);
        console.log('remainingPOItems', remainingPOItems);

        function goodsReceiptForm() {
            return {
                formData: {
                    id: goodsReceipt.id || null,
                    number: goodsReceipt.number || null,
                    reference_number: goodsReceipt.reference_number || null,
                    receipt_date: goodsReceipt.receipt_date || null,
                    subtotal: goodsReceipt.subtotal || null,
                    discount_amount: goodsReceipt.discount_amount || null,
                    tax_amount: goodsReceipt.tax_amount || null,
                    transport_cost: goodsReceipt.transport_cost || null,
                    other_cost: goodsReceipt.other_cost || null,
                    total_amount: goodsReceipt.total_amount || null,
                    note: goodsReceipt.note || null,
                    details: (goodsReceipt.items || []).map(item => ({
                        id: item.id,
                        purchase_order_item_id: item.purchase_order_item_id,
                        product_id: item.product_id,
                        code: item.product.code,
                        name: item.product.name,
                        unit: item.product.unit.symbol,
                        batch_number: item.batch_number,
                        remaining_quantity: item.purchase_order_item.quantity - item.purchase_order_item
                            .received_quantity,
                        expected_quantity: item.expected_quantity,
                        received_quantity: item.received_quantity,
                        shrinkage_quantity: item.shrinkage_quantity,
                        unit_price: item.unit_price,
                        allocated_cost: item.allocated_cost,
                        unit_cost: item.unit_cost,
                        total_cost: item.total_cost,
                    })),
                },

                addProduct() {
                    this.formData.details.push({
                        product_id: null,
                        name: null,
                        code: null,
                        unit: null,
                        batch_number: null,
                        expected_quantity: null,
                        received_quantity: null,
                        shrinkage_quantity: null,
                        unit_price: null,
                        allocated_cost: null,
                        unit_cost: null,
                        total_cost: null,
                    });
                },
                deleteProduct(index) {
                    this.formData.details.splice(index, 1);
                    this.calculateHPP();
                },
                selectProduct(item, poItem) {
                    if (this.formData.details.some(d => d.product_id === poItem.product_id)) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Produk sudah terpilih sebelumnya'
                        });

                        return;
                    }
                    item.purchase_order_item_id = poItem.id;

                    item.product_id = poItem.product_id;
                    item.code = poItem.product_code;
                    item.name = poItem.product_name;
                    item.unit = poItem.unit;

                    item.remaining_quantity = poItem.remaining_quantity;
                    item.expected_quantity = poItem.remaining_quantity;
                    item.received_quantity = poItem.remaining_quantity;
                    item.shrinkage_quantity = 0;

                    item.unit_price = poItem.unit_price;

                    this.calculateHPP();
                },

                availablePOItems() {
                    const selectedIds = this.formData.details
                        .filter(d => d.purchase_order_item_id)
                        .map(d => d.purchase_order_item_id);

                    return remainingPOItems.filter(item =>
                        item.remaining_quantity > 0 &&
                        !selectedIds.includes(item.id)
                    );
                },

                calculateHPP() {
                    const additionalCost =
                        NumberUtils.parseMaskIntoNumeric(this.formData.transport_cost) +
                        NumberUtils.parseMaskIntoNumeric(this.formData.other_cost) -
                        NumberUtils.parseMaskIntoNumeric(this.formData.discount_amount);


                    const totalWeight = this.formData.details.reduce(
                        (sum, item) => sum + NumberUtils.parseMaskIntoNumeric(item.received_quantity),
                        0
                    );

                    const costPerKg = totalWeight > 0 ?
                        Math.round(additionalCost / totalWeight) :
                        0;

                    this.formData.details.forEach(item => {
                        const unitPrice = NumberUtils.parseMaskIntoNumeric(item.unit_price);
                        const receivedQuantity = NumberUtils.parseMaskIntoNumeric(item.received_quantity);
                        console.log('HPP Calculation:', {
                            unitPrice,
                            costPerKg,
                            additionalCost,
                            totalWeight
                        });
                        item.allocated_cost = costPerKg * receivedQuantity;
                        item.unit_cost = unitPrice + costPerKg;
                        item.total_cost = item.unit_cost * receivedQuantity;
                    });
                },

                handleExpectedQuantityInput(item) {
                    const expectedQuantity = NumberUtils.parseMaskIntoNumeric(item.expected_quantity);
                    item.received_quantity = null;
                    if (expectedQuantity > item.remaining_quantity) {
                        item.expected_quantity = item.remaining_quantity;
                        Toast.fire({
                            icon: 'error',
                            title: 'Quantity ekspektasi tidak boleh melebihi sisa quantity PO'
                        });
                    } else if (expectedQuantity < 0) {
                        item.expected_quantity = null;
                        Toast.fire({
                            icon: 'error',
                            title: 'Quantity ekspektasi tidak boleh kurang dari 0'
                        });
                    }

                    this.calculateShrinkage(item);
                },

                handleReceivedQuantityInput(item) {
                    const receivedQuantity = NumberUtils.parseMaskIntoNumeric(item.received_quantity);
                    const expectedQuantity = NumberUtils.parseMaskIntoNumeric(item.expected_quantity);

                    if (receivedQuantity > expectedQuantity) {
                        item.received_quantity = expectedQuantity;
                        Toast.fire({
                            icon: 'error',
                            title: 'Quantity diterima tidak boleh melebihi quantity ekspektasi'
                        });
                    } else if (receivedQuantity < 0) {
                        item.received_quantity = null;
                        Toast.fire({
                            icon: 'error',
                            title: 'Quantity diterima tidak boleh kurang dari 0'
                        });
                    }

                    this.calculateShrinkage(item);
                },

                calculateShrinkage(item) {
                    const expectedQuantity = NumberUtils.parseMaskIntoNumeric(item.expected_quantity);
                    const receivedQuantity = NumberUtils.parseMaskIntoNumeric(item.received_quantity);
                    item.shrinkage_quantity = Math.max(0, expectedQuantity - receivedQuantity);
                },

                init() {
                    Swal.fire({
                        title: 'Memuat data penerimaan barang...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    this.calculateHPP();
                    Swal.close();
                },

                async submitDraft() {
                    let body = {
                        ...this.formData,
                        status: 'draft',
                    };
                    body.discount_amount = NumberUtils.parseMaskIntoNumeric(body.discount_amount);
                    body.transport_cost = NumberUtils.parseMaskIntoNumeric(body.transport_cost);
                    body.other_cost = NumberUtils.parseMaskIntoNumeric(body.other_cost);
                    body.details = body.details.map(d => ({
                        ...d,
                        expected_quantity: NumberUtils.parseMaskIntoNumeric(d.expected_quantity),
                        received_quantity: NumberUtils.parseMaskIntoNumeric(d.received_quantity),
                        shrinkage_quantity: NumberUtils.parseMaskIntoNumeric(d.shrinkage_quantity),
                        unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                        allocated_cost: NumberUtils.parseMaskIntoNumeric(d.allocated_cost),
                        unit_cost: NumberUtils.parseMaskIntoNumeric(d.unit_cost),
                    }));

                    Swal.fire({
                        title: 'Memproses penyimpanan draft Penerimaan Barang...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('purchasings.goods_receipts.update', this.formData.id), body
                        );
                        console.log('response', response.data.message);

                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: response.data.message
                        });

                        window.location.href = response.data.redirect;
                    } catch (error) {
                        Swal.close();
                        let message = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';

                        if (error.response?.status === 422) {
                            message = Object.values(error.response.data.errors)
                                .flat()
                                .join(', ');
                        } else if (error.response?.data?.message) {
                            message = error.response.data.message;
                        }

                        Toast.fire({
                            icon: 'error',
                            title: message
                        });

                    }
                },

                async submitFinish() {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin menyelesaikan penerimaan barang ini?',
                        text: 'Setelah diselesaikan, penerimaan barang akan membentuk jurnal umum dan menambah stok di gudang tujuan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, selesaikan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            let body = {
                                ...this.formData,
                                status: 'finished',
                            };
                            body.discount_amount = NumberUtils.parseMaskIntoNumeric(body.discount_amount);
                            body.transport_cost = NumberUtils.parseMaskIntoNumeric(body.transport_cost);
                            body.other_cost = NumberUtils.parseMaskIntoNumeric(body.other_cost);
                            body.details = body.details.map(d => ({
                                ...d,
                                expected_quantity: NumberUtils.parseMaskIntoNumeric(d
                                    .expected_quantity),
                                received_quantity: NumberUtils.parseMaskIntoNumeric(d
                                    .received_quantity),
                                shrinkage_quantity: NumberUtils.parseMaskIntoNumeric(d
                                    .shrinkage_quantity),
                                unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                                allocated_cost: NumberUtils.parseMaskIntoNumeric(d
                                    .allocated_cost),
                                unit_cost: NumberUtils.parseMaskIntoNumeric(d.unit_cost),
                            }));

                            Swal.fire({
                                title: 'Memproses penyimpanan Penerimaan Barang...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            try {
                                const response = await axios.put(
                                    route('purchasings.goods_receipts.update', this.formData.id), body
                                );

                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });

                                window.location.href = response.data.redirect;
                            } catch (error) {
                                Swal.close();
                                let message = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';

                                if (error.response?.status === 422) {
                                    message = Object.values(error.response.data.errors)
                                        .flat()
                                        .join(', ');
                                } else if (error.response?.data?.message) {
                                    message = error.response.data.message;
                                }

                                Toast.fire({
                                    icon: 'error',
                                    title: message
                                });

                            }
                        }
                    });
                }

            };
        }
    </script>

    <div x-data="goodsReceiptForm()" x-init="init()" class="order-page">
        <div>
            <a href="#" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali ke PLACEHOLDER
            </a>
            <div class="order-title-row">
                <h1 class="order-title display">Buat Penerimaan Barang</h1>
                <x-misc.status-badge status="draft" />
            </div>
            <div class="order-sub">
                Penerimaan yang berhasil disimpan akan otomatis membentuk jurnal umum dan <strong
                    style="color:var(--ink);">menambah stok</strong> di gudang tujuan.
            </div>
        </div>

        <div class="card card-bd--form">
            <div class="shipping-form-info">
                <div class="display card-hd-title">Informasi Penerimaan</div>
                <div class="shipping-form-info__sub"><span style="color:var(--accent);">*</span> Field terisi otomatis dari
                    PO</div>
            </div>
            <div class="order-form-grid-3">
                <x-misc.field label="Supplier" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
                        <x-misc.avatar :name="$goodsReceipt->supplier->name" />
                        <span style="flex:1; font-weight:500;">{{ $goodsReceipt->supplier->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="No. Pemesanan" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $goodsReceipt->purchaseOrder->number }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="Gudang Tujuan" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
                        <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
                        <span style="flex:1;">{{ $goodsReceipt->warehouse->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="No. Penerimaan">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $goodsReceipt->number }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="Tanggal Penerimaan" :required="true">
                    <input type="date" class="input" x-model="formData.receipt_date" />
                </x-misc.field>
                <x-misc.field label="Nomor Referensi"><input class="input mono" placeholder="(opsional)"
                        x-model="formData.reference_number" /></x-misc.field>
            </div>
            <x-misc.field label="Catatan Penerimaan">
                <textarea class="input" rows="2"
                    placeholder="Catat kondisi barang, kekurangan, atau informasi penting lainnya..." x-model="formData.note"></textarea>
            </x-misc.field>
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Daftar Produk</div>
                <button class="btn btn-ghost btn-sm" @click="addProduct()">
                    <x-misc.icon name="plus" :size="13" />Tambah Produk
                </button>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Produk</th>
                        <th style="text-align:right; width:140px;">Batch</th>
                        <th style="text-align:right; width:140px;">Sisa (PO)</th>
                        <th style="text-align:right; width:140px;">Qty (Ekspektasi)</th>
                        <th style="text-align:right; width:140px;">Qty (Diterima)</th>
                        <th style="text-align:right; width:80px;">Susut</th>
                        <th style="width:60px;">Satuan</th>
                        <th style="width:140px; text-align:right;">Harga</th>
                        <th style="width:140px; text-align:right;">HPP</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template class="" x-for="(item, index) in formData.details" :key="index">
                        <tr x-data="{ open: false }">
                            <td class="mono" style="color:var(--ink-4);" x-text="String(index + 1).padStart(2, '0')"></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="product-icon">
                                        <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                                    </div>
                                    <div style="flex:1;" class="dropdown-wrap" @click.outside="open=false">
                                        <div class="input dropdown-trigger" style="height:32px; padding:0 10px;"
                                            @click="open=!open">
                                            <span
                                                style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                                :style="item.product_id ? '' : 'color:var(--ink-4);'"
                                                x-text="item.product_id ? item.name : 'Pilih Produk'"></span>
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round"
                                                stroke-linejoin="round" style="flex-shrink:0;">
                                                <path d="m6 9 6 6 6-6" />
                                            </svg>
                                        </div>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="item.code || '— belum dipilih'"></div>
                                        <div class="dropdown-menu" x-show="open" x-cloak style="min-width:320px;">
                                            <template x-for="p in availablePOItems()" :key="p.id">
                                                <div class="dropdown-item" @click="selectProduct(item, p);open=false">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="p.product_name"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="p.product_code"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub" x-text="p.unit"></span>
                                                </div>
                                            </template>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><input class="input" style="height:32px;" x-model="item.batch_number" /></td>
                            {{-- <td><input class="input num" style="height:32px; text-align:right;"
                                    x-model.number="item.remaining_quantity" x-mask:dynamic="$money($input, ',')"
                                    disabled /></td> --}}
                            <td style="text-align: right"><span class="mono" style="font-weight:600"
                                    x-text="item.remaining_quantity ? NumberUtils.formatNumericIntoMask(item.remaining_quantity) : '0'"></span>
                            </td>
                            <td><input class="input num" style="height:32px; text-align:right;"
                                    x-model="item.expected_quantity" x-mask:dynamic="$money($input, ',')"
                                    @input="handleExpectedQuantityInput(item)" /></td>
                            <td><input class="input num" style="height:32px; text-align:right;"
                                    x-model="item.received_quantity" x-mask:dynamic="$money($input, ',')"
                                    @input="handleReceivedQuantityInput(item); calculateHPP()" /></td>
                            <td style="text-align: right"><span class="mono" style="font-weight:600"
                                    x-text="item.shrinkage_quantity ? NumberUtils.formatNumericIntoMask(item.shrinkage_quantity) : '0'"></span>
                            </td>
                            <td style="color:var(--ink-3);" x-text="item.unit"></td>
                            <td style="text-align: right">
                                <span class="mono" style="font-weight:600;"
                                    x-text="item.unit_price ? NumberUtils.formatNumericIntoMask(item.unit_price) : '0'"></span>
                            </td>
                            <td style="text-align: right">
                                <span class="mono" style="font-weight:600"
                                    x-text="item.unit_cost ? NumberUtils.formatNumericIntoMask(item.unit_cost) : '0'"></span>
                            </td>
                            <td>
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    :disabled="formData.details.length <= 1"
                                    :style="formData.details.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                                    @click="deleteProduct(index)">
                                    <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                                </button>
                            </td>
                        </tr>

                    </template>
                </tbody>
            </table>
            <div class="order-items-split">
                <div class="order-extras">
                    <div class="display order-extras__title">Biaya Tambahan</div>
                    <div class="order-extras__grid-3">
                        <x-misc.field label="Diskon (PO)">
                            {{-- <input class="input num" style="text-align:right;" x-model="formData.discount_amount"
                                readonly /> --}}
                            <div class="input mono input--readonly" style="display:flex; align-items:center;">
                                <span class="auto-tag" style="flex:1">Auto</span>
                                <span style="font-weight:600;"
                                    x-text="formData.discount_amount ? NumberUtils.formatNumericIntoMask(formData.discount_amount) : '0'"></span>
                            </div>
                        </x-misc.field>
                        <x-misc.field label="Biaya Transportasi">
                            <input class="input num" style="text-align:right;" x-model="formData.transport_cost"
                                x-mask:dynamic="$money($input, ',')" @input="calculateHPP()" />
                        </x-misc.field>
                        <x-misc.field label="Biaya Lain-lain">
                            <input class="input num" style="text-align:right;" x-model="formData.other_cost"
                                x-mask:dynamic="$money($input, ',')" @input="calculateHPP()" />
                        </x-misc.field>
                    </div>
                </div>
                <div class="hpp-summary">
                    <div class="label" style="margin-bottom:6px;">Estimasi HPP Total</div>
                    <div class="hpp-summary__value display num">{{ fmt_rp(0) }}</div>
                    <div class="hpp-summary__sub">
                        0 unit diterima · HPP/unit ≈ <span class="mono">{{ fmt_rp(round(0 / max(0, 1))) }}</span>
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
