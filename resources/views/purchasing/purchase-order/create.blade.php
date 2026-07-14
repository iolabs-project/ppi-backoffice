@extends('layouts.app')
@section('content')
    <script>
        const products = @json($products);

        function purchaseOrderForm() {
            return {
                formData: {
                    supplier_id: null,
                    warehouse_id: null,
                    number: '{{ $number }}',
                    reference_number: null,
                    order_date: "{{ now()->format('Y-m-d') }}",
                    due_date: null,
                    discount_percentage: null,
                    discount_amount: null,
                    tax_percentage: null,
                    tax_amount: null,
                    down_payment_account_id: null,
                    down_payment_amount: null,
                    payment_terms: null,
                    subtotal: null,
                    total_amount: null,
                    note: null,
                    costs: [],
                    details: [{
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
                    }],
                },
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

                // Shorthand: parse masked string to number
                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },
                handleDiscountPercentageInput() {
                    this.recalculate();
                },
                handleTaxPercentageInput() {
                    this.recalculate();
                },
                handlePaymentTermChange() {
                    if (this.paymentTermSelected) {
                        this.formData.payment_terms = this.paymentTermSelected.id;
                        const days = NumberUtils.parseMaskIntoNumeric(this.paymentTermSelected.days);
                        const orderDate = new Date(this.formData.order_date);
                        const dueDate = new Date(orderDate.getTime() + days * 24 * 60 * 60 * 1000);
                        this.formData.due_date = dueDate.toISOString().split('T')[0];
                    } else {
                        this.formData.payment_terms = null;
                        this.formData.due_date = null;
                    }
                },
                handleOrderDateChange() {
                    if (this.paymentTermSelected) {
                        const days = NumberUtils.parseMaskIntoNumeric(this.paymentTermSelected.days);
                        const orderDate = new Date(this.formData.order_date);
                        const dueDate = new Date(orderDate.getTime() + days * 24 * 60 * 60 * 1000);
                        this.formData.due_date = dueDate.toISOString().split('T')[0];
                    }
                },

                addCost() {
                    this.formData.costs.push({
                        account_id: null,
                        description: null,
                        billed_by: 'supplier',
                        is_inventory_cost: false,
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

                recalculate() {
                    const sub = this.formData.details.reduce((sum, d) => sum + this.n(d.total_amount), 0);
                    this.formData.subtotal = sub;
                    this.formData.discount_amount = Math.round((this.n(this.formData.discount_percentage) / 100) * sub);
                    this.formData.tax_amount = Math.round((this.n(this.formData.tax_percentage) / 100) * (sub - this.n(this
                        .formData.discount_amount)));
                    this.formData.total_amount =
                        sub -
                        this.n(this.formData.discount_amount) +
                        this.costsInventoryTotal() +
                        this.costsNonInventoryTotal() +
                        this.n(this.formData.tax_amount) -
                        this.n(this.formData.down_payment_amount);
                    this.calculateEstimatedHPP();
                },
                calculateEstimatedHPP() {
                    const headerDiscount = this.n(this.formData.discount_amount);
                    const additionalCost = this.costsInventoryTotal();

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

                buildBody(status) {
                    const body = {
                        ...this.formData,
                        status
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
                    return body;
                },

                init() {
                    Swal.fire({
                        title: 'Memuat data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    // this.recalc();
                    Object.assign(this, window.productTable);
                    Swal.close();
                },

                async submit(status) {
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
                        const response = await axios.post(
                            route('purchasings.purchase_orders.store'), this.buildBody(status)
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
                    }
                }

            };
        }
    </script>
    <div x-data="purchaseOrderForm()" class="order-page">

        <div>
            <a href="{{ route('purchasings.purchase_orders.index') }}" class="btn btn-ghost btn-sm"
                style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Tambah Purchase Order</h1>
            <div class="order-sub">Buat dokumen PO baru</div>
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
                    <input type="date" class="input" x-model="formData.order_date" @change="handleOrderDateChange" />
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
                                    @click="paymentTermSelected=t; handlePaymentTermChange(); paymentTermOpen=false">
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
            @include('purchasing.purchase-order.partials.create.item-table')
        </div>

        @include('purchasing.partials.additional-cost-table', ['accounts' => $accounts, 'billedByOptions' => $billedByOptions])

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
                                <span class="order-summary__label">Biaya Tambahan (Inventory)</span>
                                <span class="num order-summary__val" x-text="NumberUtils.formatNumericIntoMask(costsInventoryTotal())"></span>
                            </div>

                            <div class="order-summary__row">
                                <span class="order-summary__label">Biaya Tambahan (Non-Inventory)</span>
                                <span class="num order-summary__val" x-text="NumberUtils.formatNumericIntoMask(costsNonInventoryTotal())"></span>
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
                                        <input
                                            class="input num order-summary__cost-input order-summary__amount-display--negative"
                                            x-model="formData.down_payment_amount" x-mask:dynamic="$money($input, ',')"
                                            @input="recalculate()" placeholder="0" />
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

                    <template x-if="formData.details.reduce((sum, d) => sum + n(d.quantity), 0) > 0">
                        <div style="margin-top:12px; padding:10px 12px; border-radius:8px; background:var(--bg-3); border:1px solid var(--line-2);">
                            <div style="font-size:11px; color:var(--ink-4);">Estimasi Landed Cost per Unit</div>
                            <div class="num" style="font-weight:600; font-size:14px;"
                                x-text="'Rp ' + NumberUtils.formatNumericIntoMask(Math.round(costsInventoryTotal() / formData.details.reduce((sum, d) => sum + n(d.quantity), 0)))"></div>
                            <div style="font-size:10px; color:var(--ink-4);">(Total Biaya Inventory / Total Qty)</div>
                        </div>
                    </template>
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
