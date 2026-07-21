@extends('layouts.app')
@section('content')
    <script>
        const purchaseInvoice = @json($purchaseInvoice);
        const remainingGRItems = @json($remainingGRItems);
        console.log('purchaseInvoice', purchaseInvoice);

        function purchaseInvoiceForm() {
            return {
                formData: {
                    id: purchaseInvoice.id || null,
                    supplier_id: purchaseInvoice.supplier_id || null,
                    warehouse_id: purchaseInvoice.warehouse_id || null,
                    number: purchaseInvoice.number || null,
                    reference_number: purchaseInvoice.reference_number || null,
                    invoice_date: purchaseInvoice.invoice_date || "{{ now()->format('Y-m-d') }}",
                    due_date: purchaseInvoice.due_date || "{{ now()->addDays(14)->format('Y-m-d') }}",
                    discount_percentage: purchaseInvoice.discount_percentage || null,
                    discount_amount: purchaseInvoice.discount_amount || null,
                    tax_percentage: purchaseInvoice.tax_percentage || null,
                    tax_amount: purchaseInvoice.tax_amount || null,
                    down_payment_amount: purchaseInvoice.down_payment_amount || 0,
                    payment_terms: purchaseInvoice.payment_terms || null,
                    subtotal: purchaseInvoice.subtotal || null,
                    total_amount: purchaseInvoice.total_amount || null,
                    note: purchaseInvoice.note || null,
                    costs: (purchaseInvoice.costs || []).map(cost => ({
                        account_id: cost.account_id,
                        description: cost.description,
                        amount: cost.amount,
                    })),
                    details: (purchaseInvoice.items || []).map(item => ({
                        id: item.id,
                        goods_receipt_item_id: item.goods_receipt_item_id,
                        purchase_order_item_id: item.purchase_order_item_id,
                        product_id: item.product_id,
                        code: item.product.code,
                        name: item.product.name,
                        batch_number: item.batch_number,
                        unit: item.product.unit.symbol,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        subtotal: item.subtotal,
                        discount_percentage: item.discount_percentage,
                        discount_amount: item.discount_amount,
                        total_amount: item.total_amount,
                    })),
                },



                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },
                // Payment Terms
                paymentTerms: @json($paymentTerms),
                paymentTermSelected: null,

                addProduct() {
                    this.formData.details.push({
                        id: null,
                        purchase_order_item_id: null,
                        goods_receipt_item_id: null,
                        product_id: null,
                        code: null,
                        name: null,
                        unit: null,
                        quantity: 0,
                        unit_price: 0,
                        subtotal: 0,
                        discount_percentage: 0,
                        discount_amount: 0,
                        total_amount: 0,
                    });
                },
                deleteProduct(index) {
                    this.formData.details.splice(index, 1);
                    this.recalculate();
                },
                selectProduct(item, product) {
                    if (this.formData.details.some(d => d.batch_number === product.batch_number)) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Produk dengan batch number ' + product.batch_number +
                                ' sudah dipilih sebelumnya.'
                        });

                        return;
                    }

                    item.purchase_order_item_id = product.purchase_order_item_id;
                    item.goods_receipt_item_id = product.id;
                    item.product_id = product.product_id;
                    item.code = product.product_code;
                    item.name = product.product_name;
                    item.batch_number = product.batch_number;
                    item.quantity = product.quantity;
                    item.unit = product.unit;

                    item.unit_price = product.unit_price;
                    item.subtotal = product.quantity * product.unit_price;
                    item.discount_percentage = product.discount_percentage;
                    item.discount_amount = item.subtotal * (product.discount_percentage / 100);
                    item.total_amount = item.subtotal - item.discount_amount;

                    this.recalculate();
                },


                availableGRItems(q) {
                    const selectedIds = this.formData.details
                        .filter(d => d.goods_receipt_item_id)
                        .map(d => d.goods_receipt_item_id);

                    let list = remainingGRItems.filter(item => !selectedIds.includes(item.id));

                    if (q) {
                        const s = q.toLowerCase();
                        list = list.filter(item =>
                            (item.product_name || '').toLowerCase().includes(s) ||
                            (item.product_code || '').toLowerCase().includes(s)
                        );
                    }

                    return list;
                },
                handleDetailDiscountPercentageInput(index) {
                    const percentage = this.n(this.formData.details[index].discount_percentage);
                    const quantity = this.n(this.formData.details[index].quantity);
                    const unitPrice = this.n(this.formData.details[index].unit_price);

                    if (percentage < 0) {
                        this.formData.details[index].discount_percentage = 0;
                    } else if (percentage > 100) {
                        this.formData.details[index].discount_percentage = 100;
                    }

                    if (quantity && unitPrice) {
                        this.formData.details[index].discount_amount = Math.round((percentage / 100) * (quantity *
                            unitPrice));
                    } else {
                        this.formData.details[index].discount_amount = 0;
                    }

                    this.calculateDetailTotal(index);
                },
                handleDiscountPercentageInput() {
                    this.recalculate();
                },
                handleTaxPercentageInput() {
                    this.recalculate();
                },
                calculateDetailTotal(index) {
                    const d = this.formData.details[index];
                    d.subtotal = this.n(d.quantity) * this.n(d.unit_price);

                    d.discount_amount = this.n(d.subtotal) * (this.n(d.discount_percentage) / 100);
                    d.total_amount = d.subtotal - this.n(d.discount_amount);
                    // this.recalculate();
                },
                addCost() {
                    this.formData.costs.push({
                        account_id: null,
                        description: null,
                        amount: null,
                    });
                    this.recalculate();
                },
                removeCost(index) {
                    this.formData.costs.splice(index, 1);
                    this.recalculate();
                },
                handleCostInput() {
                    this.recalculate();
                },
                costsTotal() {
                    return this.formData.costs.reduce((sum, c) => sum + this.n(c.amount), 0);
                },
                recalculate() {
                    const sub = this.formData.details.reduce((sum, d) => sum + this.n(d.total_amount), 0);
                    this.formData.subtotal = sub;
                    this.formData.discount_amount = Math.round((this.n(this.formData.discount_percentage) / 100) * sub);
                    this.formData.tax_amount = Math.round((this.n(this.formData.tax_percentage) / 100) * (sub - this.n(this
                        .formData.discount_amount)));
                    this.formData.total_amount =
                        sub -
                        this.n(this.formData.discount_amount) +
                        this.costsTotal() +
                        this.n(this.formData.tax_amount) -
                        this.n(this.formData.down_payment_amount);
                },
                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
                },
                handlePaymentTermChange() {
                    if (this.paymentTermSelected) {
                        this.formData.payment_terms = this.paymentTermSelected.id;
                        const days = this.n(this.paymentTermSelected.days);
                        const invoiceDate = new Date(this.formData.invoice_date);
                        const dueDate = new Date(invoiceDate.getTime() + days * 24 * 60 * 60 * 1000);
                        this.formData.due_date = dueDate.toISOString().split('T')[0];
                    } else {
                        this.formData.payment_terms = null;
                        this.formData.due_date = null;
                    }
                },
                handleInvoiceDateChange() {
                    if (this.paymentTermSelected) {
                        const days = this.n(this.paymentTermSelected.days);
                        const invoiceDate = new Date(this.formData.invoice_date);
                        const dueDate = new Date(invoiceDate.getTime() + days * 24 * 60 * 60 * 1000);
                        this.formData.due_date = dueDate.toISOString().split('T')[0];
                    }
                },
                handleDownPaymentAmountInput() {
                    if (this.n(purchaseInvoice.purchase_order.down_payment_remaining_amount) > 0) {
                        if (this.n(this.formData.down_payment_amount) < 0) {
                            this.formData.down_payment_amount = 0;
                        } else if (this.n(this.formData.down_payment_amount) > this.n(purchaseInvoice.purchase_order.down_payment_remaining_amount)) {
                            this.formData.down_payment_amount = this.n(purchaseInvoice.purchase_order.down_payment_remaining_amount);
                        }
                    } else {
                        this.formData.down_payment_amount = 0;
                    }
                    this.recalculate();
                },
                async loadSuppliers() {
                    this.supplierLoading = true;

                    try {
                        const response = await axios.get(
                            route('master.contacts.options'), {
                                params: {
                                    search: this.supplierSearch,
                                    type: 'supplier'
                                }
                            }
                        );

                        this.suppliers = response.data.data;


                    } finally {
                        this.supplierLoading = false;
                    }
                },

                async loadWarehouses() {
                    this.warehouseLoading = true;

                    try {
                        const response = await axios.get(
                            route('master.warehouses.options'), {
                                params: {
                                    search: this.warehouseSearch,
                                }
                            }
                        );

                        this.warehouses = response.data.data;


                    } finally {
                        this.warehouseLoading = false;
                    }
                },

                async loadProducts() {
                    this.productLoading = true;

                    try {
                        const response = await axios.get(
                            route('master.products.options'), {
                                params: {
                                    search: this.productSearch,
                                }
                            }
                        );

                        this.products = response.data.data;
                    } finally {
                        this.productLoading = false;
                    }
                },

                async init() {
                    this.paymentTermSelected = this.paymentTerms.find(t => t.id === this.formData.payment_terms) ||
                        null;

                    Swal.fire({
                        title: 'Memuat data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    await Promise.all([
                        this.loadSuppliers(),
                        this.loadWarehouses(),
                        this.loadProducts(),
                    ]);
                    Swal.close();
                },

                async submitDraft() {
                    this.isSubmitting = true;

                    let body = {
                        ...this.formData,
                        status: 'draft',
                    };
                    body.discount_percentage = this.n(body.discount_percentage);
                    body.tax_percentage = this.n(body.tax_percentage);
                    body.down_payment_amount = this.n(body.down_payment_amount);
                    body.details = body.details.map(d => ({
                        ...d,
                        quantity: this.n(d.quantity),
                        unit_price: this.n(d.unit_price),
                        discount_percentage: this.n(d.discount_percentage),
                        total_amount: this.n(d.total_amount),
                    }));
                    body.costs = body.costs
                        .filter(c => c.account_id && this.n(c.amount) > 0)
                        .map(c => ({
                            ...c,
                            amount: this.n(c.amount),
                        }));

                    Swal.fire({
                        title: 'Memproses penyimpanan draft tagihan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('purchasings.purchase_invoices.update', this.formData.id), body
                        );
                        console.log('response', response.data.message);

                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: response.data.message
                        });

                        window.location.href = response.data.redirect;
                    } catch (error) {
                        console.error('Error submitting draft PI:', error);
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

                async submitOpen() {
                    this.isSubmitting = true;

                    let body = {
                        ...this.formData,
                        status: 'open',
                    };
                    body.discount_percentage = this.n(body.discount_percentage);
                    body.tax_percentage = this.n(body.tax_percentage);
                    body.down_payment_amount = this.n(body.down_payment_amount);
                    body.details = body.details.map(d => ({
                        ...d,
                        quantity: this.n(d.quantity),
                        unit_price: this.n(d.unit_price),
                        discount_percentage: this.n(d.discount_percentage),
                        total_amount: this.n(d.total_amount),
                    }));
                    body.costs = body.costs
                        .filter(c => c.account_id && this.n(c.amount) > 0)
                        .map(c => ({
                            ...c,
                            amount: this.n(c.amount),
                        }));

                    Swal.fire({
                        title: 'Memproses penyimpanan tagihan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('purchasings.purchase_invoices.update', this.formData.id), body
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

            };
        }
    </script>

    <div x-data="purchaseInvoiceForm()" x-init="init();
    recalculate();" class="order-page">

        <div>
            <a href="{{ route('purchasings.purchase_invoices.index') }}" class="btn btn-ghost btn-sm"
                style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Edit Tagihan Pembelian</h1>
            <div class="order-sub">Ubah dokumen Tagihan yang ada</div>
        </div>

        {{-- Info Tagihan --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Tagihan</div>
            <div class="order-form-grid-4">

                {{-- Supplier Dropdown --}}
                <x-misc.field label="Supplier" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
                        <x-misc.avatar :name="$purchaseInvoice->supplier->name" />
                        <span style="flex:1; font-weight:500;">{{ $purchaseInvoice->supplier->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Nomor PO --}}
                <x-misc.field label="No. Pemesanan" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $purchaseInvoice->purchaseOrder->number }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Tanggal --}}
                <x-misc.field label="Tanggal" :required="true">
                    <input type="date" class="input" x-model="formData.invoice_date"
                        @change="handleInvoiceDateChange" />
                </x-misc.field>

                {{-- Jatuh Tempo --}}
                <x-misc.field label="Jatuh Tempo" :required="true">
                    <input type="date" class="input" x-model="formData.due_date" />
                </x-misc.field>

                {{-- Gudang Dropdown --}}
                <x-misc.field label="Gudang Tujuan" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
                        <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
                        <span style="flex:1;">{{ $purchaseInvoice->warehouse->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Termin Pembayaran Dropdown --}}
                <x-misc.field label="Termin Pembayaran" :required="true">
                    <x-misc.select display="paymentTermSelected ? paymentTermSelected.name : 'Pilih Termin Pembayaran'"
                        hasValue="paymentTermSelected" placeholder="Cari termin...">
                        <template x-for="t in paymentTerms.filter(t => !q || t.name.toLowerCase().includes(q.toLowerCase()))"
                            :key="t.id">
                            <div class="dropdown-item"
                                @click="paymentTermSelected=t; handlePaymentTermChange(); open=false; q=''">
                                <span x-text="t.name"></span>
                            </div>
                        </template>
                        <template x-if="!paymentTerms.some(t => !q || t.name.toLowerCase().includes(q.toLowerCase()))">
                            <div class="dropdown-empty">Tidak ditemukan</div>
                        </template>
                    </x-misc.select>
                </x-misc.field>

                {{-- Nomor Referensi --}}
                <x-misc.field label="Nomor Referensi">
                    <input class="input mono" placeholder="(opsional)" x-model="formData.reference_number" />
                </x-misc.field>

            </div>
        </div>

        {{-- Items --}}
        <div class="card" style="overflow:visible;">
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
                        <th>Pilih Produk</th>
                        <th style="width:120px; text-align:right;">Qty</th>
                        <th style="width:140px;">Satuan</th>
                        <th style="width:160px; text-align:right;">Harga</th>
                        <th style="width:100px; text-align:right;">Diskon (%)</th>
                        <th style="width:160px; text-align:right;">Total</th>
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
                                            display="it.product_id ? it.name + ' (' + it.batch_number + ')' : 'Pilih Produk'"
                                            hasValue="it.product_id" placeholder="Cari produk..." min-width="320px"
                                            height="32px">
                                            <template x-for="p in availableGRItems(q)" :key="p.id">
                                                <div class="dropdown-item" @click="selectProduct(it, p);open=false;q=''">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;"
                                                            x-text="p.product_name + ' (' + p.batch_number + ')'"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="p.product_code"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub" x-text="p.unit.symbol"></span>
                                                </div>
                                            </template>
                                            <template x-if="availableGRItems(q).length === 0">
                                                <div class="dropdown-empty">Tidak ditemukan</div>
                                            </template>
                                        </x-misc.select>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="it.code || '— belum dipilih'"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" x-model="it.quantity"
                                    @input="calculateDetailTotal(i)" x-mask:dynamic="$money($input, ',')" />
                            </td>
                            <td>
                                <div class="input input--readonly"
                                    style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                                    <span x-text="it.unit || '—'"></span>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" x-model="it.unit_price"
                                    @input="calculateDetailTotal(i)" x-mask:dynamic="$money($input, ',')" />

                                <template x-if="it.subtotal !== null && it.subtotal !== undefined">
                                    <div class="order-items__sub mono"
                                        style="font-size:11px; color:var(--ink-4); margin-top:2px; text-align: right;"
                                        x-text="NumberUtils.formatNumericIntoMask(it.subtotal)">
                                    </div>
                                </template>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;"
                                    x-model="it.discount_percentage" @input="handleDetailDiscountPercentageInput(i)"
                                    x-mask:dynamic="$money($input, ',')" />

                                <template x-if="it.discount_amount !== null && it.discount_amount !== undefined">
                                    <div class="order-items__sub mono"
                                        style="font-size:11px; color:var(--ink-4); margin-top:2px; text-align: right;"
                                        x-text="NumberUtils.formatNumericIntoMask(it.discount_amount)">
                                    </div>
                                </template>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;"
                                    x-model.number="it.total_amount" x-mask:dynamic="$money($input, ',')" disabled />
                            </td>
                            <td>
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    :disabled="formData.details.length <= 1"
                                    :style="formData.details.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                                    @click="deleteProduct(i)">
                                    <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                                </button>
                            </td>
                    </template>
                </tbody>
            </table>
        </div>

        @include('purchasing.partials.additional-cost-table', ['accounts' => $accounts])

        <div class="card" style="overflow:visible;">
            <div class="order-items-split">
                <div class="order-extras">
                    <x-misc.field label="Catatan Internal">
                        <textarea class="input" rows="2" placeholder="Tulis catatan untuk tim gudang/pengiriman…"
                            x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>
                <div class="order-summary">
                    <div class="display order-summary__title">Ringkasan</div>
                    <div class="order-summary__grid">

                        {{-- Group 1: Nilai Bruto -> Subtotal --}}
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

                        {{-- Group 2: Diskon, Pajak, Transport, Biaya Lain-lain -> Subtotal --}}
                        <div class="order-summary__group">
                            <div class="order-summary__row">
                                <span class="order-summary__label"></span>
                                <span class="num order-summary__val"
                                    x-text="(formData.subtotal ? NumberUtils.formatNumericIntoMask(formData.subtotal) : '0')"></span>
                            </div>
                            <div class="order-summary__row">
                                <span class="order-summary__label">Diskon</span>
                                <div class="order-summary__pct-group">
                                    <input class="input num order-summary__pct-input"
                                        x-model="formData.discount_percentage" x-mask:dynamic="$money($input, ',')"
                                        @input="handleDiscountPercentageInput()" />
                                    <span class="order-summary__pct-sym">%</span>
                                    <input
                                        class="input num input--readonly order-summary__amount-display order-summary__amount-display--negative"
                                        :value="'- ' + (formData.discount_amount ? NumberUtils.formatNumericIntoMask(formData
                                            .discount_amount) : '0')"
                                        disabled />
                                </div>
                            </div>

                            <div class="order-summary__row">
                                <span class="order-summary__label">Pajak</span>
                                <div class="order-summary__pct-group">
                                    <input class="input num order-summary__pct-input" x-model="formData.tax_percentage"
                                        x-mask:dynamic="$money($input, ',')" @input="handleTaxPercentageInput()" />
                                    <span class="order-summary__pct-sym">%</span>
                                    <input class="input num input--readonly order-summary__amount-display"
                                        :value="formData.tax_amount ? NumberUtils.formatNumericIntoMask(formData.tax_amount) :
                                            '0'"
                                        disabled />
                                </div>
                            </div>

                            <div class="order-summary__row">
                                <span class="order-summary__label">Biaya Tambahan</span>
                                <span class="num order-summary__val" x-text="NumberUtils.formatNumericIntoMask(costsTotal())"></span>
                            </div>


                        </div>

                        <template x-if="purchaseInvoice.purchase_order && purchaseInvoice.purchase_order.down_payment_remaining_amount > 0">
                            <div class="order-summary__group">
                                <div class="order-summary__row">
                                    <span class="order-summary__label"></span>
                                    <span class="num order-summary__val"
                                        x-text="NumberUtils.formatNumericIntoMask(n(formData.total_amount) + n(formData.down_payment_amount))"></span>
                                </div>
                                <div class="order-summary__row">
                                    <span class="order-summary__label">Uang Muka</span>
                                    <div class="order-summary__dp-group">
                                        <div class="input-with-prefix">
                                            <input
                                                class="input num input--readonly order-summary__cost-input"
                                                :value="purchaseInvoice.purchase_order.down_payment_remaining_amount ? NumberUtils.formatNumericIntoMask(purchaseInvoice.purchase_order.down_payment_remaining_amount) : '0'"
                                                disabled />
                                        </div>
                                        <div class="input-with-prefix">
                                            {{-- <span class="input-with-prefix__label">- Rp</span> --}}
                                            <input
                                                class="input num order-summary__cost-input order-summary__amount-display--negative"
                                                x-model="formData.down_payment_amount"
                                                x-mask:dynamic="$money($input, ',')" @input="handleDownPaymentAmountInput()"
                                                placeholder="0" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="order-summary__total">
                        <span class="order-summary__total-label">Total Harga</span>
                        <span class="order-summary__total-value display num"
                            x-text="'Rp ' + (formData.total_amount ? NumberUtils.formatNumericIntoMask(formData.total_amount) : '0')"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <a href="{{ route('purchasings.purchase_orders.index') }}" class="btn btn-ghost">Batal</a>
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submitDraft()">Simpan Draft</button>
            <button class="btn btn-primary" @click="submitOpen()"><x-misc.icon name="check" :size="14" />Simpan
                SO</button>
        </div>

    </div>
@endsection
