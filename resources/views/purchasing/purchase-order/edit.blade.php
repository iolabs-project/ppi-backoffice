@extends('layouts.app')
@section('content')
    <script>
        const purchaseOrder = @json($purchaseOrder);
        const products = @json($products);

        function purchaseOrderForm() {
            return {
                formData: {
                    id: purchaseOrder.id || null,
                    supplier_id: purchaseOrder.supplier_id || null,
                    warehouse_id: purchaseOrder.warehouse_id || null,
                    number: purchaseOrder.number || null,
                    reference_number: purchaseOrder.reference_number || null,
                    order_date: purchaseOrder.order_date || "{{ now()->format('Y-m-d') }}",
                    due_date: purchaseOrder.due_date || "{{ now()->addDays(14)->format('Y-m-d') }}",
                    discount_percentage: purchaseOrder.discount_percentage || null,
                    discount_amount: purchaseOrder.discount_amount || null,
                    tax_percentage: purchaseOrder.tax_percentage || null,
                    tax_amount: purchaseOrder.tax_amount || null,
                    transport_cost: purchaseOrder.transport_cost || null,
                    other_cost: purchaseOrder.other_cost || null,
                    down_payment_account_id: purchaseOrder.down_payment_account_id || null,
                    down_payment_amount: purchaseOrder.down_payment_amount || null,
                    payment_terms: purchaseOrder.payment_terms || null,
                    subtotal: purchaseOrder.subtotal || null,
                    total_amount: purchaseOrder.total_amount || null,
                    note: purchaseOrder.note || null,
                    details: (purchaseOrder.items || []).map(item => ({
                        id: item.id,
                        product_id: item.product_id,
                        code: item.product.code,
                        name: item.product.name,
                        unit: item.product.unit.symbol,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        subtotal: item.subtotal,
                        discount_percentage: item.discount_percentage,
                        discount_amount: item.discount_amount,
                        total_amount: item.total_amount,
                        unit_cost: null,
                    })),
                },
                // Supplier Options
                // Supplier Options
                suppliers: @json($suppliers),
                supplierLoading: false,
                supplierSearch: '',
                supplierSelected: null,
                supplierOpen: false,
                // Warehouse Options
                warehouses: @json($warehouses),
                warehouseLoading: false,
                warehouseSearch: '',
                warehouseSelected: null,
                warehouseOpen: false,
                // Payment Terms
                paymentTerms: @json($paymentTerms),
                paymentTermSelected: null,
                paymentTermOpen: false,
                // Product Options
                productSelected: [],
                productOpen: false,
                // Cash Bank Options
                cashBanks: @json($cashBankAccounts),
                cashBankLoading: false,
                cashBankSelected: null,
                cashBankOpen: false,
                // Submit
                isSubmitting: false,

                // Shorthand: parse masked string to number
                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                addProduct() {
                    this.formData.details.push({
                        product_id: null,
                        name: null,
                        code: null,
                        quantity: null,
                        unit: null,
                        unit_price: null,
                        subtotal: null,
                        discount_percentage: null,
                        discount_amount: null,
                        total_amount: null,
                        unit_cost: null,
                    });
                },
                deleteProduct(index) {
                    this.formData.details.splice(index, 1);
                    this.recalculate();
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
                    const d = this.formData.details[index];
                    d.discount_percentage = Math.min(100, Math.max(0, this.n(d.discount_percentage)));
                    d.discount_amount = Math.round((d.discount_percentage / 100) * (this.n(d.quantity) * this.n(d
                        .unit_price)));
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
                    d.total_amount = d.subtotal - this.n(d.discount_amount);
                    this.recalculate();
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
                        this.n(this.formData.transport_cost) +
                        this.n(this.formData.other_cost) +
                        this.n(this.formData.tax_amount) -
                        this.n(this.formData.down_payment_amount);
                    this.calculateEstimatedHPP();
                },
                calculateEstimatedHPP() {
                    const headerDiscount = this.n(this.formData.discount_amount);
                    const additionalCost =
                        this.n(this.formData.transport_cost) +
                        this.n(this.formData.other_cost);

                    const totalQty = this.formData.details.reduce(
                        (sum, d) => sum + this.n(d.quantity),
                        0
                    );

                    const subtotal = this.formData.subtotal;

                    this.formData.details.forEach(d => {
                        const qty = this.n(d.quantity);

                        const qtyRatio =
                            totalQty > 0 ? qty / totalQty : 0;

                        const valueRatio =
                            subtotal > 0 ?
                            this.n(d.total_amount) / subtotal :
                            0;

                        const additionalCostPerUnit =
                            qty > 0 ?
                            (additionalCost * qtyRatio) / qty :
                            0;

                        const discountPerUnit =
                            qty > 0 ?
                            (headerDiscount * valueRatio) / qty :
                            0;

                        d.unit_cost =
                            this.n(d.unit_price) -
                            (qty > 0 ? this.n(d.discount_amount) / qty : 0) -
                            discountPerUnit +
                            additionalCostPerUnit;

                        d.unit_cost = Math.round(d.unit_cost);
                    });
                },
                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
                },

                availableProducts() {
                    return products.filter(p => !this.formData.details.some(d => d.product_id === p.id));
                },

                async init() {
                    Swal.fire({
                        title: 'Memuat data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    this.paymentTermSelected = this.paymentTerms.find(t => t.id === this.formData.payment_terms) ||
                        null;
                    this.cashBankSelected = this.cashBanks.find(cb => cb.id === this.formData
                        .down_payment_account_id) || null;
                    Swal.close();
                },

                buildBody(status) {
                    const body = {
                        ...this.formData,
                        status
                    };
                    body.discount_percentage = this.n(body.discount_percentage);
                    body.tax_percentage = this.n(body.tax_percentage);
                    body.transport_cost = this.n(body.transport_cost);
                    body.other_cost = this.n(body.other_cost);
                    body.down_payment_amount = this.n(body.down_payment_amount);
                    body.details = body.details.map(d => ({
                        ...d,
                        quantity: this.n(d.quantity),
                        unit_price: this.n(d.unit_price),
                        discount_percentage: this.n(d.discount_percentage),
                        total_amount: this.n(d.total_amount),
                    }));
                    return body;
                },

                async submit(status) {
                    this.isSubmitting = true;
                    const titles = {
                        draft: 'Memproses penyimpanan draft PO...',
                        open: 'Memproses penyimpanan PO...',
                    };
                    Swal.fire({
                        title: titles[status] ?? 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                    try {
                        const response = await axios.put(
                            route('purchasings.purchase_orders.update', this.formData.id), this.buildBody(status)
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
                            message = Object.values(error.response.data.errors).flat().join(', ');
                        } else if (error.response?.data?.message) {
                            message = error.response.data.message;
                        }
                        Toast.fire({
                            icon: 'error',
                            title: message
                        });
                    } finally {
                        this.isSubmitting = false;
                    }
                }

            };
        }
    </script>

    <div x-data="purchaseOrderForm()" x-init="init();
    recalculate();" class="order-page">

        <div>
            <a href="{{ route('purchasings.purchase_orders.index') }}" class="btn btn-ghost btn-sm"
                style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Edit Purchase Order</h1>
            <div class="order-sub">Ubah dokumen PO yang ada</div>
        </div>

        {{-- Info Order --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Order</div>
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
                                <span class="order-summary__label">Transport (Est)</span>
                                <input class="input num order-summary__cost-input" x-model="formData.transport_cost"
                                    x-mask:dynamic="$money($input, ',')" @input="recalculate()" />
                            </div>

                            <div class="order-summary__row">
                                <span class="order-summary__label">Biaya Lain-lain</span>
                                <input class="input num order-summary__cost-input" x-model="formData.other_cost"
                                    x-mask:dynamic="$money($input, ',')" @input="recalculate()" />
                            </div>


                        </div>

                        {{-- Group 3: Uang Muka --}}
                        <div class="order-summary__group">
                            <div class="order-summary__row">
                                <span class="order-summary__label"></span>
                                <span class="num order-summary__val"
                                    x-text="NumberUtils.formatNumericIntoMask(n(formData.total_amount) + n(formData.down_payment_amount))"></span>
                            </div>
                            <div class="order-summary__row">
                                <span class="order-summary__label">Uang Muka</span>
                                <div class="order-summary__dp-group">
                                    <div class="dropdown-wrap" @click.outside="cashBankOpen=false">
                                        <div class="input dropdown-trigger order-summary__dp-trigger"
                                            @click="cashBankOpen=!cashBankOpen">
                                            <span
                                                style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                                :style="cashBankSelected ? '' : 'color:var(--ink-4);'"
                                                x-text="cashBankSelected ? cashBankSelected.name : 'Sumber Kas'"></span>
                                            <x-misc.icon name="chev-down" :size="11" stroke="var(--ink-4)" />
                                        </div>
                                        <div class="dropdown-menu" x-show="cashBankOpen" x-cloak
                                            style="right:0; left:auto; min-width:180px;">
                                            <template x-for="cb in cashBanks" :key="cb.id">
                                                <div class="dropdown-item"
                                                    @click="cashBankSelected=cb; formData.down_payment_account_id=cb.id; cashBankOpen=false">
                                                    <span x-text="cb.name"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="input-with-prefix">
                                        {{-- <span class="input-with-prefix__label">- Rp</span> --}}
                                        <input class="input num order-summary__cost-input order-summary__amount-display--negative" x-model="formData.down_payment_amount"
                                            x-mask:dynamic="$money($input, ',')" @input="recalculate()"
                                            placeholder="0" />
                                    </div>
                                </div>
                            </div>
                        </div>

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
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submit('draft')">Simpan Draft</button>
            <button class="btn btn-primary" @click="submit('open')"><x-misc.icon name="check"
                    :size="14" />Simpan
                SO</button>
        </div>

    </div>
@endsection
