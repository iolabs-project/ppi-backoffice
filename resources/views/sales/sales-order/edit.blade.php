@extends('layouts.app')
@section('content')
    <script>
        const salesOrder = @json($salesOrder);
        const inventories = @json($inventories);

        function salesOrderForm() {
            return {
                formData: {
                    id: salesOrder.id || null,
                    customer_id: salesOrder.customer_id || null,
                    warehouse_id: salesOrder.warehouse_id || null,
                    sales_person_id: salesOrder.sales_person_id || null,
                    number: salesOrder.number || null,
                    reference_number: salesOrder.reference_number || null,
                    order_date: salesOrder.order_date || "{{ now()->format('Y-m-d') }}",
                    due_date: salesOrder.due_date || null,
                    discount_percentage: salesOrder.discount_percentage || null,
                    discount_amount: salesOrder.discount_amount || null,
                    tax_percentage: salesOrder.tax_percentage || null,
                    tax_amount: salesOrder.tax_amount || null,
                    down_payment_account_id: salesOrder.down_payment_account_id || null,
                    down_payment_amount: salesOrder.down_payment_amount || null,
                    payment_terms: salesOrder.payment_terms || null,
                    subtotal: salesOrder.subtotal || null,
                    total_amount: salesOrder.total_amount || null,
                    note: salesOrder.note || null,
                    charges: (salesOrder.charges || []).map(charge => ({
                        account_id: charge.account_id,
                        description: charge.description,
                        amount: charge.amount,
                    })),
                    costs: (salesOrder.costs || []).map(cost => ({
                        account_id: cost.account_id,
                        description: cost.description,
                        amount: cost.amount,
                    })),
                    details: (salesOrder.items || []).map(item => ({
                        id: item.id,
                        product_id: item.product_id,
                        code: item.product.code,
                        name: item.product.name,
                        unit: item.product.unit.symbol,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        discount_percentage: item.discount_percentage,
                        discount_amount: item.discount_amount,
                        total_amount: item.total_amount,
                        average_unit_cost: inventories.find(inv => inv.product_id === item.product_id && inv
                            .warehouse_id === salesOrder.warehouse_id)?.average_unit_cost || null,
                    })),
                },
                // Customer Options
                customers: @json($customers),
                customerSelected: null,
                // Sales Person Options
                salesPersons: @json($salesPersons),
                salesPersonSelected: null,
                // Warehouse Options
                warehouses: @json($warehouses),
                warehouseSelected: null,
                // Payment Terms
                paymentTerms: @json($paymentTerms),
                paymentTermSelected: null,
                // Cash Bank Options
                cashBanks: @json($cashBankAccounts),
                cashBankSelected: null,

                // Shorthand: parse masked string to number
                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                addInventory() {
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
                        average_unit_cost: null,
                    });
                },
                deleteInventory(index) {
                    this.formData.details.splice(index, 1);
                    this.recalculate();
                },
                selectInventory(item, inventory) {
                    if (this.formData.details.some(d => d.product_id === inventory.product_id)) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Produk sudah terpilih sebelumnya'
                        });

                        return;
                    }
                    console.log('Selected inventory:', inventory);
                    item.name = inventory.product.name;
                    item.product_id = inventory.product_id;
                    item.code = inventory.product.code;
                    item.unit = inventory.product.unit.symbol;
                    item.available_stock = inventory.available_quantity;
                    item.average_unit_cost = inventory.average_unit_cost;
                },
                selectCustomer(customer) {
                    if (this.customerSelected && this.customerSelected.id === customer.id) {
                        return;
                    }
                    this.customerSelected = customer;
                    this.formData.customer_id = customer.id;

                    if (customer.transportation_cost > 0 && this.formData.charges.length === 0) {
                        this.formData.charges.push({
                            account_id: null,
                            description: 'Biaya Transportasi',
                            amount: customer.transportation_cost,
                        });
                    } else if (this.formData.charges.length > 0) {
                        Swal.fire({
                            title: 'Perubahan Customer',
                            text: 'Customer diganti. Apakah Anda ingin menghapus biaya-biaya sebelumnya?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, hapus biaya',
                            cancelButtonText: 'Batal',
                            reverseButtons: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.formData.charges = [];

                                if (customer.transportation_cost > 0) {
                                    this.formData.charges.push({
                                        account_id: null,
                                        description: 'Biaya Transportasi',
                                        amount: customer.transportation_cost,
                                    });
                                }
                            }
                        });
                    }
                },
                addCharge() {
                    this.formData.charges.push({
                        account_id: null,
                        description: null,
                        amount: null,
                    });
                    this.recalculate();
                },
                removeCharge(index) {
                    this.formData.charges.splice(index, 1);
                    this.recalculate();
                },
                handleChargeInput() {
                    this.recalculate();
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
                chargesTotal() {
                    return this.formData.charges.reduce((sum, c) => sum + this.n(c.amount), 0);
                },
                estimatedCOGS() {
                    return this.formData.details.reduce((sum, d) => sum + (this.n(d.quantity) * (Number(d
                        .average_unit_cost) || 0)), 0);
                },
                estimatedRevenue() {
                    return this.n(this.formData.subtotal) - this.n(this.formData.discount_amount) + this.chargesTotal();
                },
                estimatedCost() {
                    return this.formData.costs.reduce((sum, c) => sum + this.n(c.amount), 0);
                },
                estimatedProfit() {
                    return this.estimatedRevenue() - this.estimatedCOGS() - this.estimatedCost();
                },
                estimatedMargin() {
                    const revenue = this.estimatedRevenue();
                    return revenue > 0 ? (this.estimatedProfit() / revenue) * 100 : 0;
                },
                handleWarehouseChange() {
                    if (this.warehouseSelected) {
                        if (this.warehouseSelected.id !== this.formData.warehouse_id) {
                            this.formData.details = [{
                                product_id: null,
                                name: null,
                                code: null,
                                available_stock: null,
                                quantity: null,
                                unit: null,
                                unit_price: null,
                                subtotal: null,
                                discount_percentage: null,
                                discount_amount: null,
                                total_amount: null,
                                average_unit_cost: null,
                            }];
                        }
                        this.formData.warehouse_id = this.warehouseSelected.id;

                        this.recalculate();
                    } else {
                        this.formData.warehouse_id = null;
                    }
                },
                handleQuantityInput(index) {
                    const d = this.formData.details[index];
                    d.quantity = Math.max(0, this.n(d.quantity));
                    d.quantity = Math.min(d.quantity, d.available_stock);
                    this.calculateDetailTotal(index);
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
                        this.chargesTotal() +
                        this.n(this.formData.tax_amount) -
                        this.n(this.formData.down_payment_amount);
                },
                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
                },

                availableInventories(q) {
                    let list = inventories.filter(p =>
                        !this.formData.details.some(d => d.product_id === p.id) &&
                        this.formData.warehouse_id && p.warehouse_id === this.formData.warehouse_id
                    );

                    if (q) {
                        const s = q.toLowerCase();
                        list = list.filter(p =>
                            (p.product.name || '').toLowerCase().includes(s) ||
                            (p.product.code || '').toLowerCase().includes(s)
                        );
                    }

                    return list;
                },
                rowsSubtotal() {
                    let subtotal = 0;
                    this.formData.details.forEach(d => {
                        subtotal += this.n(d.quantity) * this.n(d.unit_price);
                    });
                    return subtotal;
                },
                rowsDiscountAmount() {
                    let discountAmount = 0;
                    this.formData.details.forEach(d => {
                        discountAmount += this.n(d.quantity) * this.n(d.unit_price) * (this.n(d
                            .discount_percentage) / 100);
                    });
                    return discountAmount;
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
                    this.warehouseSelected = this.warehouses.find(g => g.id === this.formData.warehouse_id) ||
                        null;
                    this.salesPersonSelected = this.salesPersons.find(s => s.id === this.formData
                        .sales_person_id) || null;
                    this.customerSelected = this.customers.find(c => c.id === this.formData.customer_id) ||
                        null;

                    this.formData.details.forEach((d, i) => {
                        const inventory = inventories.find(inv => inv.product_id === d.product_id && inv
                            .warehouse_id === this.formData.warehouse_id);
                        if (inventory) {
                            d.available_stock = inventory.available_quantity;
                            d.average_unit_cost = inventory.average_unit_cost;
                        } else {
                            d.available_stock = 0;
                        }
                    });
                    Swal.close();
                },

                buildBody(status) {
                    const body = {
                        ...this.formData,
                        status
                    };
                    body.discount_percentage = this.n(body.discount_percentage);
                    body.tax_percentage = this.n(body.tax_percentage);
                    body.down_payment_amount = this.n(body.down_payment_amount);
                    body.details = body.details
                    .filter(d => d.product_id !== null)
                    .map(d => ({
                        ...d,
                        quantity: this.n(d.quantity),
                        unit_price: this.n(d.unit_price),
                        discount_percentage: this.n(d.discount_percentage),
                        total_amount: this.n(d.total_amount),
                    }));
                    body.charges = body.charges
                        .map(c => ({
                            ...c,
                            amount: this.n(c.amount)
                        }));
                    body.costs = body.costs
                        .map(c => ({
                            ...c,
                            amount: this.n(c.amount)
                        }));
                    return body;
                },

                async submit(status) {
                    const titles = {
                        draft: 'Memproses penyimpanan draft SO...',
                        open: 'Memproses penyimpanan SO...',
                    };
                    Swal.fire({
                        title: titles[status] ?? 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                    try {
                        const response = await axios.put(
                            route('sales.sales_orders.update', this.formData.id), this.buildBody(status)
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
                    } finally {
                        this.isSubmitting = false;
                    }
                }

            };
        }
    </script>

    <div x-data="salesOrderForm()" x-init="init();
    recalculate();" class="order-page">

        <div>
            <a href="{{ route('sales.sales_orders.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Edit Sales Order</h1>
            <div class="order-sub">Ubah dokumen SO yang ada</div>
        </div>

        {{-- Info Order --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Order</div>
            <div class="order-form-grid-4">

                {{-- Customer Dropdown --}}
                <x-misc.field label="Customer" :required="true">
                    <x-misc.select display="customerSelected ? customerSelected.name : 'Pilih Customer'"
                        hasValue="customerSelected" placeholder="Cari customer...">
                        <template x-for="c in customers.filter(c => !q || c.name.toLowerCase().includes(q.toLowerCase()))"
                            :key="c.id">
                            <div class="dropdown-item" @click="selectCustomer(c); open=false; q=''">
                                <div class="avatar"
                                    style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                    x-text="initials(c.name)"></div>
                                <span x-text="c.name"></span>
                            </div>
                        </template>
                        <template x-if="!customers.some(c => !q || c.name.toLowerCase().includes(q.toLowerCase()))">
                            <div class="dropdown-empty">Tidak ditemukan</div>
                        </template>
                    </x-misc.select>
                </x-misc.field>

                {{-- Nomor SO --}}
                <x-misc.field label="Nomor SO" :required="true">
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
                    <x-misc.select display="warehouseSelected ? warehouseSelected.name : 'Pilih Gudang'"
                        hasValue="warehouseSelected" placeholder="Cari gudang...">
                        <template x-for="g in warehouses.filter(g => !q || g.name.toLowerCase().includes(q.toLowerCase()))"
                            :key="g.id">
                            <div class="dropdown-item"
                                @click="warehouseSelected=g; handleWarehouseChange(); open=false; q=''">
                                <div class="avatar"
                                    style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                    x-text="initials(g.name)"></div>
                                <span x-text="g.name"></span>
                            </div>
                        </template>
                        <template x-if="!warehouses.some(g => !q || g.name.toLowerCase().includes(q.toLowerCase()))">
                            <div class="dropdown-empty">Tidak ditemukan</div>
                        </template>
                    </x-misc.select>
                </x-misc.field>

                <x-misc.field label="Sales">
                    <x-misc.select display="salesPersonSelected ? salesPersonSelected.name : 'Pilih Sales'"
                        hasValue="salesPersonSelected" placeholder="Cari sales...">
                        <template
                            x-for="s in salesPersons.filter(s => !q || s.name.toLowerCase().includes(q.toLowerCase()))"
                            :key="s.id">
                            <div class="dropdown-item"
                                @click="salesPersonSelected=s; formData.sales_person_id=s.id; open=false; q=''">
                                <div class="avatar"
                                    style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                    x-text="initials(s.name)"></div>
                                <span x-text="s.name"></span>
                            </div>
                        </template>
                        <template x-if="!salesPersons.some(s => !q || s.name.toLowerCase().includes(q.toLowerCase()))">
                            <div class="dropdown-empty">Tidak ditemukan</div>
                        </template>
                    </x-misc.select>
                </x-misc.field>

                {{-- Termin Pembayaran Dropdown --}}
                <x-misc.field label="Termin Pembayaran" :required="true">
                    <x-misc.select display="paymentTermSelected ? paymentTermSelected.name : 'Pilih Termin Pembayaran'"
                        hasValue="paymentTermSelected" placeholder="Cari termin...">
                        <template
                            x-for="t in paymentTerms.filter(t => !q || t.name.toLowerCase().includes(q.toLowerCase()))"
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
                <button class="btn btn-ghost btn-sm" @click="addInventory()">
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
                                        <x-misc.select display="it.product_id ? it.name : 'Pilih Produk'"
                                            hasValue="it.product_id" placeholder="Cari produk..." min-width="320px"
                                            height="32px">
                                            <template x-for="p in availableInventories(q)" :key="p.id">
                                                <div class="dropdown-item"
                                                    @click="selectInventory(it, p);open=false;q=''">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="p.product.name"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="p.product.code"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub"
                                                        x-text="NumberUtils.formatNumericIntoMask(p.available_quantity) + ' ' + p.product.unit.symbol"></span>
                                                </div>
                                            </template>
                                            <template x-if="availableInventories(q).length === 0">
                                                <div class="dropdown-empty">Tidak ditemukan</div>
                                            </template>
                                        </x-misc.select>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                            x-text="it.product_id ? it.code + ' (' + it.available_stock + ' ' + it.unit + ')' : '— belum dipilih'">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" x-model="it.quantity"
                                    @input="handleQuantityInput(i)" x-mask:dynamic="$money($input, '.',',')" />
                            </td>
                            <td>
                                <div class="input input--readonly"
                                    style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                                    <span x-text="it.product_id ? it.unit : '—'"></span>
                                </div>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;" x-model="it.unit_price"
                                    @input="calculateDetailTotal(i)" x-mask:dynamic="$money($input, '.',',')" />

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
                                    x-mask:dynamic="$money($input, '.',',')" />

                                <template x-if="it.discount_amount !== null && it.discount_amount !== undefined">
                                    <div class="order-items__sub mono"
                                        style="font-size:11px; color:var(--ink-4); margin-top:2px; text-align: right;"
                                        x-text="NumberUtils.formatNumericIntoMask(it.discount_amount)">
                                    </div>
                                </template>
                            </td>
                            <td>
                                <input class="input num" style="height:32px; text-align:right;"
                                    x-model.number="it.total_amount" x-mask:dynamic="$money($input, '.',',')" disabled />
                            </td>
                            <td>
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    :disabled="formData.details.length <= 1"
                                    :style="formData.details.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                                    @click="deleteInventory(i)">
                                    <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                                </button>
                            </td>
                    </template>
                </tbody>
            </table>
        </div>

        @include('sales.partials.additional-charge-table', ['accounts' => $accounts])
        @include('sales.partials.additional-cost-table', ['accounts' => $accounts])

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
                                    x-text="(formData.details ? NumberUtils.formatNumericIntoMask(rowsSubtotal()) : '0')"></span>
                            </div>

                            <div class="order-summary__row">
                                <span class="order-summary__label">Diskon Per Item</span>
                                <span class="num order-summary__val order-summary__val--negative"
                                    x-text="(formData.details ? NumberUtils.formatNumericIntoMask(rowsDiscountAmount()) : '0')"></span>
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
                                        x-model="formData.discount_percentage" x-mask:dynamic="$money($input, '.',',')"
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
                                        x-mask:dynamic="$money($input, '.',',')" @input="handleTaxPercentageInput()" />
                                    <span class="order-summary__pct-sym">%</span>
                                    <input class="input num input--readonly order-summary__amount-display"
                                        :value="formData.tax_amount ? NumberUtils.formatNumericIntoMask(formData.tax_amount) :
                                            '0'"
                                        disabled />
                                </div>
                            </div>

                            <div class="order-summary__row">
                                <span class="order-summary__label">Biaya Tambahan (Customer)</span>
                                <span class="num order-summary__val"
                                    x-text="NumberUtils.formatNumericIntoMask(chargesTotal())"></span>
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
                                    <x-misc.select display="cashBankSelected ? cashBankSelected.name : 'Sumber Kas'"
                                        hasValue="cashBankSelected" placeholder="Cari sumber kas..." align="right"
                                        min-width="180px" trigger-class="order-summary__dp-trigger">
                                        <template
                                            x-for="cb in cashBanks.filter(cb => !q || cb.name.toLowerCase().includes(q.toLowerCase()))"
                                            :key="cb.id">
                                            <div class="dropdown-item"
                                                @click="cashBankSelected=cb; formData.down_payment_account_id=cb.id; open=false; q=''">
                                                <span x-text="cb.name"></span>
                                            </div>
                                        </template>
                                        <template
                                            x-if="!cashBanks.some(cb => !q || cb.name.toLowerCase().includes(q.toLowerCase()))">
                                            <div class="dropdown-empty">Tidak ditemukan</div>
                                        </template>
                                    </x-misc.select>
                                    <div class="input-with-prefix">
                                        {{-- <span class="input-with-prefix__label">- Rp</span> --}}
                                        <input
                                            class="input num order-summary__cost-input order-summary__amount-display--negative"
                                            x-model="formData.down_payment_amount"
                                            x-mask:dynamic="$money($input, '.',',')" @input="recalculate()"
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

                    <div
                        style="margin-top:12px; padding:12px; border-radius:8px; background:var(--bg-3); border:1px solid var(--line-2);">
                        <div class="display" style="font-size:12px; font-weight:600; margin-bottom:8px;">Profit Preview
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px; padding:3px 0;">
                            <span style="color:var(--ink-3);">Estimasi Revenue</span>
                            <span class="num"
                                x-text="NumberUtils.formatNumericIntoMask(Math.round(estimatedRevenue()))"></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px; padding:3px 0;">
                            <span style="color:var(--ink-3);">Estimasi HPP (Customer)</span>
                            <span class="num"
                                x-text="NumberUtils.formatNumericIntoMask(Math.round(estimatedCOGS()))"></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px; padding:3px 0;">
                            <span style="color:var(--ink-3);">Estimasi Cost (Internal)</span>
                            <span class="num"
                                x-text="NumberUtils.formatNumericIntoMask(Math.round(estimatedCost()))"></span>
                        </div>
                        <div
                            style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; padding-top:6px; margin-top:4px; border-top:1px solid var(--line-2);">
                            <span>Estimasi Profit</span>
                            <span class="num" style="color:var(--accent);"
                                x-text="NumberUtils.formatNumericIntoMask(Math.round(estimatedProfit()))"></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12px; padding-top:2px;">
                            <span style="color:var(--ink-3);">Margin</span>
                            <span class="num" x-text="estimatedMargin().toFixed(2) + '%'"></span>
                        </div>
                        <div style="font-size:10px; color:var(--ink-4); margin-top:6px;">
                            Profit preview adalah estimasi. Nilai akan berubah sesuai item, biaya tambahan, atau biaya
                            internal.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submit('draft')">Simpan Draft</button>
            <button class="btn btn-primary" @click="submit('open')"><x-misc.icon name="check"
                    :size="14" />Simpan
                SO</button>
        </div>

    </div>
@endsection
