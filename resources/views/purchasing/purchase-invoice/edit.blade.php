@extends('layouts.app')
@section('content')
    <script>
        const purchaseInvoice = @json($purchaseInvoice);
        console.log('purchaseInvoice', purchaseInvoice);

        function purchaseInvoiceForm() {
            return {
                formData: {
                    id: purchaseOrder.id || null,
                    supplier_id: purchaseOrder.supplier_id || null,
                    warehouse_id: purchaseOrder.warehouse_id || null,
                    number: purchaseInvoice.number || null,
                    reference_number: purchaseInvoice.reference_number || null,
                    invoice_date: purchaseInvoice.invoice_date || "{{ now()->format('Y-m-d') }}",
                    due_date: purchaseInvoice.due_date || "{{ now()->addDays(14)->format('Y-m-d') }}",
                    discount_percentage: purchaseInvoice.discount_percentage || null,
                    discount_amount: purchaseInvoice.discount_amount || null,
                    tax_percentage: purchaseInvoice.tax_percentage || null,
                    tax_amount: purchaseInvoice.tax_amount || null,
                    payment_terms: purchaseInvoice.payment_terms || null,
                    subtotal: purchaseInvoice.subtotal || null,
                    total_amount: purchaseInvoice.total_amount || null,
                    note: purchaseInvoice.note || null,
                    details: (purchaseInvoice.items || []).map(item => ({
                        id: item.id,
                        goods_receipt_item_id: item.goods_receipt_item_id,
                        product_id: item.product_id,
                        code: item.product.code,
                        name: item.product.name,
                        unit: item.product.unit.symbol,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        discount_percentage: item.discount_percentage,
                        discount_amount: item.discount_amount,
                        total_amount: item.total_amount,
                    })),
                },
                // Supplier Options
                suppliers: [],
                supplierLoading: false,
                supplierSearch: '',
                supplierSelected: purchaseOrder.supplier || null,
                supplierOpen: false,
                // Warehouse Options
                warehouses: [],
                warehouseLoading: false,
                warehouseSearch: '',
                warehouseSelected: purchaseOrder.warehouse || null,
                warehouseOpen: false,
                // Payment Terms
                paymentTerms: @json($paymentTerms),
                paymentTermSelected: null,
                paymentTermOpen: false,
                // Product Options
                products: [],
                productLoading: false,
                productSearch: '',
                productOpen: false,
                // Submit
                isSubmitting: false,

                addProduct() {
                    this.formData.details.push({
                        product_id: null,
                        name: null,
                        code: null,
                        quantity: null,
                        unit: null,
                        unit_price: null,
                        total_amount: null,
                        unit_cost: null,
                    });
                },
                deleteProduct(index) {
                    this.formData.details.splice(index, 1);
                    this.calculateSubtotal();
                },
                selectProduct(item, product) {
                    if (this.formData.details.some(d => d.product_id === product.id)) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Produk sudah terpilih sebelumnya'
                        });

                        return;
                    }
                    item.name = product.name;
                    item.product_id = product.id;
                    item.code = product.code;
                    item.unit = product.unit.symbol;
                },
                handleDetailDiscountPercentageInput(index) {
                    const percentage = NumberUtils.parseMaskIntoNumeric(this.formData.details[index].discount_percentage);
                    const quantity = NumberUtils.parseMaskIntoNumeric(this.formData.details[index].quantity);
                    const unitPrice = NumberUtils.parseMaskIntoNumeric(this.formData.details[index].unit_price);

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
                    const percentage = NumberUtils.parseMaskIntoNumeric(this.formData.discount_percentage);
                    const subtotal = NumberUtils.parseMaskIntoNumeric(this.formData.subtotal);
                    this.formData.discount_amount = Math.round((percentage / 100) * subtotal);
                    this.calculateTotal();
                },
                handleTaxPercentageInput() {
                    const percentage = NumberUtils.parseMaskIntoNumeric(this.formData.tax_percentage);
                    const subtotal = NumberUtils.parseMaskIntoNumeric(this.formData.subtotal);
                    const discount = NumberUtils.parseMaskIntoNumeric(this.formData.discount_amount);
                    this.formData.tax_amount = Math.round((percentage / 100) * (subtotal - discount));
                    this.calculateTotal();
                },
                calculateEstimatedHPP() {
                    const estimatedAdditionalCost =
                        NumberUtils.parseMaskIntoNumeric(this.formData.transport_cost) +
                        NumberUtils.parseMaskIntoNumeric(this.formData.other_cost) -
                        NumberUtils.parseMaskIntoNumeric(this.formData.discount_amount);

                    const totalWeight = this.formData.details.reduce(
                        (sum, item) => sum + NumberUtils.parseMaskIntoNumeric(item.quantity),
                        0
                    );

                    const costPerUnit = totalWeight > 0 ?
                        Math.round(estimatedAdditionalCost / totalWeight) :
                        0;

                    this.formData.details.forEach(item => {
                        const unitPrice = NumberUtils.parseMaskIntoNumeric(item.unit_price);
                        const quantity = NumberUtils.parseMaskIntoNumeric(item.quantity);
                        const discountPerUnit = quantity > 0 ? Math.round(NumberUtils.parseMaskIntoNumeric(item
                            .discount_amount) / quantity) : 0;

                        item.unit_cost = unitPrice + costPerUnit - discountPerUnit;
                    });
                },
                calculateDetailTotal(index) {
                    let item = this.formData.details[index];

                    item.total_amount = NumberUtils.parseMaskIntoNumeric(item.quantity) * NumberUtils.parseMaskIntoNumeric(
                        item.unit_price) - NumberUtils.parseMaskIntoNumeric(item.discount_amount);
                    this.calculateSubtotal();
                },
                calculateSubtotal() {
                    this.formData.subtotal = this.formData.details.reduce((sum, item) => {
                        return sum + NumberUtils.parseMaskIntoNumeric(item.total_amount);
                    }, 0);
                    this.calculateTotal();
                },
                calculateTotal() {
                    this.formData.total_amount = NumberUtils.parseMaskIntoNumeric(this.formData.subtotal) - NumberUtils
                        .parseMaskIntoNumeric(this.formData.discount_amount) + NumberUtils.parseMaskIntoNumeric(this
                            .formData.transport_cost) + NumberUtils.parseMaskIntoNumeric(this.formData.other_cost) +
                        NumberUtils.parseMaskIntoNumeric(this.formData.tax_amount);
                },
                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
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
                    body.discount_percentage = NumberUtils.parseMaskIntoNumeric(body.discount_percentage);
                    body.tax_percentage = NumberUtils.parseMaskIntoNumeric(body.tax_percentage);
                    body.transport_cost = NumberUtils.parseMaskIntoNumeric(body.transport_cost);
                    body.other_cost = NumberUtils.parseMaskIntoNumeric(body.other_cost);
                    body.details = body.details.map(d => ({
                        ...d,
                        quantity: NumberUtils.parseMaskIntoNumeric(d.quantity),
                        unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                        discount_percentage: NumberUtils.parseMaskIntoNumeric(d.discount_percentage),
                        total_amount: NumberUtils.parseMaskIntoNumeric(d.total_amount),
                    }));

                    Swal.fire({
                        title: 'Memproses penyimpanan draft PO...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('purchasings.purchasing_orders.update', this.formData.id), body
                        );
                        console.log('response', response.data.message);

                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: response.data.message
                        });

                        window.location.href = response.data.redirect;
                    } catch (error) {
                        console.error('Error submitting draft PO:', error);
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
                    body.discount_percentage = NumberUtils.parseMaskIntoNumeric(body.discount_percentage);
                    body.tax_percentage = NumberUtils.parseMaskIntoNumeric(body.tax_percentage);
                    body.transport_cost = NumberUtils.parseMaskIntoNumeric(body.transport_cost);
                    body.other_cost = NumberUtils.parseMaskIntoNumeric(body.other_cost);
                    body.details = body.details.map(d => ({
                        ...d,
                        quantity: NumberUtils.parseMaskIntoNumeric(d.quantity),
                        unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                        discount_percentage: NumberUtils.parseMaskIntoNumeric(d.discount_percentage),
                        total_amount: NumberUtils.parseMaskIntoNumeric(d.total_amount),
                    }));

                    Swal.fire({
                        title: 'Memproses penyimpanan PO...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('purchasings.purchasing_orders.update', this.formData.id), body
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
    calculateEstimatedHPP();" class="order-page">

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
                    <div class="dropdown-wrap" @click.outside="supplierOpen=false">
                        <div class="input dropdown-trigger" @click="supplierOpen=!supplierOpen">
                            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                x-text="initials(supplierSelected ? supplierSelected.name : '')"></div>
                            <span style="flex:1; font-weight:500;"
                                x-text="supplierSelected ? supplierSelected.name : 'Pilih Supplier'"></span>
                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                        </div>
                        <div class="dropdown-menu" x-show="supplierOpen" x-cloak>
                            <template x-for="s in suppliers" :key="s.id">
                                <div class="dropdown-item"
                                    @click="supplierSelected=s; formData.supplier_id=s.id; supplierOpen=false">
                                    <div class="avatar"
                                        style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                        x-text="initials(s.name)"></div>
                                    <span x-text="s.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-misc.field>

                {{-- Nomor PO --}}
                <x-misc.field label="Nomor PO" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;" x-text="formData.number"></span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Tanggal --}}
                <x-misc.field label="Tanggal" :required="true">
                    <input type="date" class="input" x-model="formData.order_date" />
                </x-misc.field>

                {{-- Jatuh Tempo --}}
                <x-misc.field label="Jatuh Tempo" :required="true">
                    <input type="date" class="input" x-model="formData.due_date" />
                </x-misc.field>

                {{-- Gudang Dropdown --}}
                <x-misc.field label="Gudang" :required="true">
                    <div class="dropdown-wrap" @click.outside="warehouseOpen=false">
                        <div class="input dropdown-trigger" @click="warehouseOpen=!warehouseOpen">
                            <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                x-text="initials(warehouseSelected ? warehouseSelected.name : '')"></div>
                            <span style="flex:1; font-weight:500;"
                                x-text="warehouseSelected ? warehouseSelected.name : 'Pilih Gudang'"></span>
                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                        </div>
                        <div class="dropdown-menu" x-show="warehouseOpen" x-cloak>
                            <template x-for="g in warehouses" :key="g.id">
                                <div class="dropdown-item"
                                    @click="warehouseSelected=g; formData.warehouse_id=g.id; warehouseOpen=false">
                                    <div class="avatar"
                                        style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                        x-text="initials(g.name)"></div>
                                    <span x-text="g.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-misc.field>

                {{-- Termin Pembayaran Dropdown --}}
                <x-misc.field label="Termin Pembayaran" :required="true">
                    <div class="dropdown-wrap" @click.outside="paymentTermOpen=false">
                        <div class="input dropdown-trigger" @click="paymentTermOpen=!paymentTermOpen">
                            <span style="flex:1; font-weight:500;"
                                x-text="paymentTermSelected ? paymentTermSelected.name : 'Pilih Termin Pembayaran'"></span>
                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-4)" />
                        </div>
                        <div class="dropdown-menu" x-show="paymentTermOpen" x-cloak>
                            <template x-for="t in paymentTerms" :key="t.id">
                                <div class="dropdown-item"
                                    @click="paymentTermSelected=t; formData.payment_terms=t.id; paymentTermOpen=false">
                                    <span x-text="t.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
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
                        <th style="width:160px; text-align:right;">Subtotal</th>
                        <th style="width:160px; text-align:right;">Est. HPP</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(it, i) in formData.details" :key="i">
                        <tr x-data="{ open: false }">
                            <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
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
                                                :style="it.product_id ? '' : 'color:var(--ink-4);'"
                                                x-text="it.product_id ? it.name : 'Pilih Produk'"></span>
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round"
                                                stroke-linejoin="round" style="flex-shrink:0;">
                                                <path d="m6 9 6 6 6-6" />
                                            </svg>
                                        </div>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="it.code || '— belum dipilih'"></div>
                                        <div class="dropdown-menu" x-show="open" x-cloak style="min-width:320px;">
                                            <template x-for="p in availableProducts()" :key="p.id">
                                                <div class="dropdown-item" @click="selectProduct(it, p);open=false">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="p.name"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="p.code"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub" x-text="p.unit.symbol"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" x-model="it.quantity"
                                    @input="calculateDetailTotal(i); calculateEstimatedHPP();"
                                    x-mask:dynamic="$money($input, ',')" />
                            </td>
                            <td>
                                <div class="input input--readonly"
                                    style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                                    <span x-text="it.unit || '—'"></span>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" x-model="it.unit_price"
                                    @input="calculateDetailTotal(i); calculateEstimatedHPP();"
                                    x-mask:dynamic="$money($input, ',')" />
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;"
                                    x-model="it.discount_percentage"
                                    @input="handleDetailDiscountPercentageInput(i); calculateEstimatedHPP();"
                                    x-mask:dynamic="$money($input, ',')" />
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;"
                                    x-model.number="it.total_amount" x-mask:dynamic="$money($input, ',')" disabled />
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;"
                                    x-model.number="it.unit_cost" x-mask:dynamic="$money($input, ',')" disabled />
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

            <div class="order-items-split">
                <div class="order-extras">
                    <x-misc.field label="Catatan Internal">
                        <textarea class="input" rows="2" placeholder="Tulis catatan untuk tim gudang/pengiriman…"
                            x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>
                <div class="order-summary">
                    <div class="display order-summary__title">Ringkasan</div>
                    <div class="order-summary__row">
                        <span class="order-summary__label">Subtotal</span>
                        <span class="num" style="font-weight:500;"
                            x-text="'Rp ' + (formData.subtotal ? NumberUtils.formatNumericIntoMask(formData.subtotal) : '0')"></span>
                    </div>
                    <div class="order-summary__row" style="align-items:center;">
                        <span class="order-summary__label">Diskon (%)</span>
                        <div>
                            <input class="input num" style="height:28px; width:60px; text-align:right; font-size:13px;"
                                x-model="formData.discount_percentage" x-mask:dynamic="$money($input, ',')"
                                @input="handleDiscountPercentageInput(); calculateTotal(); calculateEstimatedHPP();" />
                            <input class="input num input--readonly"
                                style="height:28px; width:130px; text-align:right; font-size:13px;"
                                x-model="formData.discount_amount" x-mask:dynamic="$money($input, ',')" disabled />
                        </div>
                    </div>
                    <div class="order-summary__row" style="align-items:center;">
                        <span class="order-summary__label">Pajak (%)</span>
                        <div>
                            <input class="input num" style="height:28px; width:60px; text-align:right; font-size:13px;"
                                x-model="formData.tax_percentage" x-mask:dynamic="$money($input, ',')"
                                @input="handleTaxPercentageInput(); calculateTotal();" />
                            <input class="input num input--readonly"
                                style="height:28px; width:130px; text-align:right; font-size:13px;"
                                x-model="formData.tax_amount" x-mask:dynamic="$money($input, ',')" disabled />
                        </div>
                    </div>
                    <div class="order-summary__divider"></div>
                    <div class="order-summary__total">
                        <span class="order-summary__total-label">Total Harga</span>
                        <span class="order-summary__total-value display num"
                            x-text="'Rp ' + (formData.total_amount ? NumberUtils.formatNumericIntoMask(formData.total_amount) : '0')"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <a href="{{ route('purchasings.purchasing_orders.index') }}" class="btn btn-ghost">Batal</a>
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submitDraft()">Simpan Draft</button>
            <button class="btn btn-primary" @click="submitOpen()"><x-misc.icon name="check" :size="14" />Simpan
                SO</button>
        </div>

    </div>
@endsection
