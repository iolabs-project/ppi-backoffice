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
                    note: goodsReceipt.note || null,
                    costs: (goodsReceipt.costs || []).map(cost => ({
                        account_id: cost.account_id,
                        description: cost.description,
                        billed_by: cost.billed_by,
                        is_inventory_cost: !!cost.is_inventory_cost,
                        amount: cost.amount,
                    })),
                    details: (goodsReceipt.items || []).map(item => ({
                        id: item.id,
                        purchase_order_item_id: item.purchase_order_item_id,
                        product_id: item.product_id,
                        code: item.product.code,
                        name: item.product.name,
                        batch_prefix: item.product.batch_prefix,
                        unit: item.product.unit.symbol,
                        remaining_quantity: item.purchase_order_item.quantity - item.purchase_order_item
                            .received_quantity,
                        expected_quantity: item.expected_quantity,
                        received_quantity: item.received_quantity,
                        shrinkage_quantity: item.shrinkage_quantity,
                        unit_price: item.unit_price,
                        subtotal: item.subtotal,
                        discount_percentage: item.discount_percentage,
                        discount_amount: item.discount_amount,
                        unit_cost: item.unit_cost,
                        total_cost: item.total_cost,
                    })),
                },

                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                addProduct() {
                    this.formData.details.push({
                        product_id: null,
                        name: null,
                        code: null,
                        unit: null,
                        batch_prefix: null,
                        expected_quantity: null,
                        received_quantity: null,
                        shrinkage_quantity: null,
                        unit_price: null,
                        subtotal: null,
                        discount_percentage: null,
                        discount_amount: null,
                        unit_cost: null,
                        total_cost: null,
                    });
                    this.recalc();
                },
                deleteProduct(index) {
                    this.formData.details.splice(index, 1);
                    this.recalc();
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
                    item.discount_percentage = poItem.discount_percentage;
                    item.discount_amount = poItem.discount_amount;

                    item.batch_prefix = poItem.product_batch_prefix;

                    this.recalc();
                },

                availablePOItems(q) {
                    const selectedIds = this.formData.details
                        .filter(d => d.purchase_order_item_id)
                        .map(d => d.purchase_order_item_id);

                    let list = remainingPOItems.filter(item =>
                        item.remaining_quantity > 0 &&
                        !selectedIds.includes(item.id)
                    );

                    if (q) {
                        const s = q.toLowerCase();
                        list = list.filter(item =>
                            (item.product_name || '').toLowerCase().includes(s) ||
                            (item.product_code || '').toLowerCase().includes(s)
                        );
                    }

                    return list;
                },

                batchNumberPlaceholder(prefix) {
                    if (!prefix) {
                        return '-';
                    }
                    const datePart = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                    return `${prefix}-${datePart}-XXXX`;
                },

                recalc() {
                    this.calculateSubtotal();
                    this.calculateHPP();
                },

                addCost() {
                    this.formData.costs.push({
                        account_id: null,
                        description: null,
                        billed_by: 'supplier',
                        is_inventory_cost: false,
                        amount: null,
                    });
                    this.recalc();
                },
                removeCost(index) {
                    this.formData.costs.splice(index, 1);
                    this.recalc();
                },
                handleCostInput() {
                    this.recalc();
                },
                costsInventoryTotal() {
                    return this.formData.costs
                        .filter(c => c.is_inventory_cost)
                        .reduce((sum, c) => sum + this.n(c.amount), 0);
                },
                costsNonInventoryTotal() {
                    return this.formData.costs
                        .filter(c => !c.is_inventory_cost)
                        .reduce((sum, c) => sum + this.n(c.amount), 0);
                },

                calculateHPP() {
                    const additionalCost = this.costsInventoryTotal();

                    const totalQty = this.formData.details.reduce(
                        (sum, d) => sum + this.n(d.received_quantity),
                        0
                    );

                    this.formData.details.forEach(d => {
                        const qty = this.n(d.received_quantity);
                        if (qty <= 0) {
                            d.unit_cost = 0;
                            return;
                        }

                        const qtyRatio =
                            totalQty > 0 ? qty / totalQty : 0;

                        const additionalCostPerUnit =
                            qty > 0 ?
                            (additionalCost * qtyRatio) / qty :
                            0;

                        d.unit_cost =
                            this.n(d.unit_price) -
                            (qty > 0 ? this.n(d.discount_amount) / qty : 0) +
                            additionalCostPerUnit;

                        d.unit_cost = Math.round(d.unit_cost);
                    });
                },

                calculateSubtotal() {
                    let subtotal = this.formData.details.reduce((sum, item) => {
                        const qty = this.n(item.received_quantity);
                        const price = this.n(item.unit_price);
                        const discountPercentage = this.n(item.discount_percentage);
                        item.discount_amount = Math.round((discountPercentage / 100) * (qty * price));
                        item.subtotal = qty * price;
                        item.total_amount = item.subtotal - item.discount_amount;
                        return sum + item.total_amount;
                    }, 0);
                    this.formData.subtotal = subtotal;
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
                    this.recalc();
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

                calculateDetailTotal(index) {
                    const d = this.formData.details[index];
                    d.subtotal = this.n(d.quantity) * this.n(d.unit_price);
                    d.total_amount = d.subtotal - this.n(d.discount_amount);
                    this.recalculate();
                },

                init() {
                    Swal.fire({
                        title: 'Memuat data penerimaan barang...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    this.recalc();
                    Swal.close();
                },

                async submitDraft() {
                    let body = {
                        ...this.formData,
                        status: 'draft',
                    };
                    body.details = body.details.map(d => ({
                        ...d,
                        expected_quantity: NumberUtils.parseMaskIntoNumeric(d.expected_quantity),
                        received_quantity: NumberUtils.parseMaskIntoNumeric(d.received_quantity),
                        shrinkage_quantity: NumberUtils.parseMaskIntoNumeric(d.shrinkage_quantity),
                        unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                        unit_cost: NumberUtils.parseMaskIntoNumeric(d.unit_cost),
                    }));
                    body.costs = body.costs
                        .filter(c => c.account_id && NumberUtils.parseMaskIntoNumeric(c.amount) > 0)
                        .map(c => ({
                            ...c,
                            amount: NumberUtils.parseMaskIntoNumeric(c.amount),
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
                            body.details = body.details.map(d => ({
                                ...d,
                                expected_quantity: NumberUtils.parseMaskIntoNumeric(d
                                    .expected_quantity),
                                received_quantity: NumberUtils.parseMaskIntoNumeric(d
                                    .received_quantity),
                                shrinkage_quantity: NumberUtils.parseMaskIntoNumeric(d
                                    .shrinkage_quantity),
                                unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                                unit_cost: NumberUtils.parseMaskIntoNumeric(d.unit_cost),
                                discount_percentage: NumberUtils.parseMaskIntoNumeric(d
                                    .discount_percentage),
                            }));
                            body.costs = body.costs
                                .filter(c => c.account_id && NumberUtils.parseMaskIntoNumeric(c.amount) > 0)
                                .map(c => ({
                                    ...c,
                                    amount: NumberUtils.parseMaskIntoNumeric(c.amount),
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

    <div x-data="goodsReceiptForm()" x-init="init()" class="order-page">
        <div>
            <a href="{{ route('purchasings.goods_receipts.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
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
                        <th style="width:240px;">Batch</th>
                        <th style="text-align:right; width:140px;">Sisa (PO)</th>
                        <th style="text-align:right; width:140px;">Qty (Ekspektasi)</th>
                        <th style="text-align:right; width:140px;">Qty (Diterima)</th>
                        <th style="text-align:right; width:80px;">Susut</th>
                        <th style="width:50px;">Satuan</th>
                        <th style="width:120px; text-align:right;">Harga</th>
                        <th style="width:80px; text-align:right;">Diskon(%)</th>
                        <th style="width:120px; text-align:right;">HPP</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template class="" x-for="(item, index) in formData.details" :key="index">
                        <tr>
                            <td class="mono" style="color:var(--ink-4);" x-text="String(index + 1).padStart(2, '0')"></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="product-icon">
                                        <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                                    </div>
                                    <div style="flex:1;">
                                        <x-misc.select display="item.product_id ? item.name : 'Pilih Produk'"
                                            hasValue="item.product_id" placeholder="Cari produk..." min-width="320px"
                                            height="32px">
                                            <template x-for="p in availablePOItems(q)" :key="p.id">
                                                <div class="dropdown-item" @click="selectProduct(item, p);open=false;q=''">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="p.product_name"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="p.product_code"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub" x-text="p.unit"></span>
                                                </div>
                                            </template>
                                            <template x-if="availablePOItems(q).length === 0">
                                                <div class="dropdown-empty">Tidak ditemukan</div>
                                            </template>
                                        </x-misc.select>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="item.code || '— belum dipilih'"></div>
                                    </div>
                                </div>
                            </td>
                            {{-- <td><input class="input" style="height:32px;" x-model="item.batch_number" /></td> --}}
                            {{-- <td><input class="input num" style="height:32px; text-align:right;"
                                    x-model.number="item.remaining_quantity" x-mask:dynamic="$money($input, '.',',')"
                                    disabled /></td> --}}
                            <td>
                                <div class="input input--readonly"
                                    style="display:flex; align-items:center; gap:10px; height: 32px;">
                                    <span style="flex:1; font-weight:500;"
                                        x-text="batchNumberPlaceholder(item.batch_prefix)"></span>
                                    <span class="auto-tag">Auto</span>
                                </div>
                            </td>
                            <td style="text-align: right"><span class="mono" style="font-weight:600"
                                    x-text="item.remaining_quantity ? NumberUtils.formatNumericIntoMask(item.remaining_quantity) : '0'"></span>
                            </td>
                            <td><input class="input num" style="height:32px; text-align:right;"
                                    x-model="item.expected_quantity" x-mask:dynamic="$money($input, '.',',')"
                                    @input="handleExpectedQuantityInput(item)" /></td>
                            <td><input class="input num" style="height:32px; text-align:right;"
                                    x-model="item.received_quantity" x-mask:dynamic="$money($input, '.',',')"
                                    @input="handleReceivedQuantityInput(item); recalc()" /></td>
                            <td style="text-align: right"><span class="mono" style="font-weight:600"
                                    x-text="item.shrinkage_quantity ? NumberUtils.formatNumericIntoMask(item.shrinkage_quantity) : '0'"></span>
                            </td>
                            <td style="color:var(--ink-3);" x-text="item.unit"></td>
                            <td style="text-align: right">
                                <span class="mono" style="font-weight:600;"
                                    x-text="item.unit_price ? NumberUtils.formatNumericIntoMask(item.unit_price) : '0'"></span>
                                <template x-if="item.subtotal !== null && item.subtotal !== undefined">
                                    <div class="order-items__sub mono"
                                        style="font-size:11px; color:var(--ink-4); margin-top:2px; text-align: right;"
                                        x-text="NumberUtils.formatNumericIntoMask(item.subtotal)">
                                    </div>
                                </template>
                            </td>
                            <td style="text-align: right">
                                <span class="mono" style="font-weight:600;"
                                    x-text="item.discount_percentage ? NumberUtils.formatNumericIntoMask(item.discount_percentage) : '0'"></span>
                                <template x-if="item.discount_amount !== null && item.discount_amount !== undefined">
                                    <div class="order-items__sub mono"
                                        style="font-size:11px; color:var(--ink-4); margin-top:2px; text-align: right;"
                                        x-text="NumberUtils.formatNumericIntoMask(item.discount_amount)">
                                    </div>
                                </template>
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
                    <template x-if="formData.details.length === 0">
                        <tr>
                            <td colspan="12" style="text-align:center; color:var(--ink-4); padding:16px 0;">
                                Belum ada produk yang ditambahkan. Klik tombol <strong>Tambah Produk</strong> untuk
                                menambahkan produk dari PO.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div style="padding:12px 16px; font-size:12px; color:var(--ink-3); line-height:1.7;">
                <div>• <strong>Nomor Batch</strong> akan otomatis terbuat ketika penerimaan barang <strong>SELESAI</strong>.
                </div>
            </div>
        </div>

        @include('purchasing.partials.additional-cost-table', [
            'accounts' => $accounts,
            'showInventoryCost' => true,
        ])

        <div class="card" style="overflow:visible;">
            <div class="order-items-split">
                <div class="order-extras">
                    <x-misc.field label="Catatan Penerimaan">
                        <textarea class="input" rows="2"
                            placeholder="Catat kondisi barang, kekurangan, atau informasi penting lainnya..." x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>
                <div class="order-summary">
                    <div class="display order-summary__title">Ringkasan</div>
                    <div class="order-summary__grid">
                        <div class="order-summary__group">
                            <div class="order-summary__row">
                                <span class="order-summary__label">Nilai Bruto</span>
                                <span class="num order-summary__val"
                                    x-text="(formData.details ? NumberUtils.formatNumericIntoMask(formData.details.reduce((acc, item) => acc + item.subtotal, 0)) : '0')"></span>
                            </div>

                            <div class="order-summary__row">
                                <span class="order-summary__label">Diskon Per Item</span>
                                <span class="num order-summary__val order-summary__val--negative"
                                    x-text="(formData.details ? NumberUtils.formatNumericIntoMask(formData.details.reduce((acc, item) => acc + item.discount_amount, 0)) : '0')"></span>
                            </div>
                        </div>
                        <div class="order-summary__group">
                            <div class="order-summary__row">
                                <span class="order-summary__label"></span>
                                <span class="num order-summary__val"
                                    x-text="(formData.subtotal ? NumberUtils.formatNumericIntoMask(formData.subtotal) : '0')"></span>
                            </div>
                            <div class="order-summary__row">
                                <span class="order-summary__label">Biaya Tambahan (Inventory)</span>
                                <span class="num order-summary__val"
                                    x-text="NumberUtils.formatNumericIntoMask(costsInventoryTotal())"></span>
                            </div>
                            <div class="order-summary__row">
                                <span class="order-summary__label">Biaya Tambahan (Non-Inventory)</span>
                                <span class="num order-summary__val"
                                    x-text="NumberUtils.formatNumericIntoMask(costsNonInventoryTotal())"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="order-form-footer">
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submitDraft()">Simpan Draft</button>
            <button class="btn btn-primary" @click="submitFinish()"><x-misc.icon name="check"
                    :size="14" />Selesai</button>
        </div>
    </div>
@endsection
