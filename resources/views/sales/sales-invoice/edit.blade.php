@extends('layouts.app')
@section('content')
    <script>
        const salesInvoice = @json($salesInvoice);
        const remainingDOItems = @json($remainingDOItems);

        function salesInvoiceForm() {
            return {
                formData: {
                    id: salesInvoice.id || null,
                    customer_id: salesInvoice.customer_id || null,
                    sales_person_id: salesInvoice.sales_person_id || null,
                    warehouse_id: salesInvoice.warehouse_id || null,
                    number: salesInvoice.number || null,
                    reference_number: salesInvoice.reference_number || null,
                    invoice_date: salesInvoice.invoice_date || "{{ now()->format('Y-m-d') }}",
                    due_date: salesInvoice.due_date || "{{ now()->addDays(14)->format('Y-m-d') }}",
                    discount_percentage: salesInvoice.discount_percentage || null,
                    discount_amount: salesInvoice.discount_amount || null,
                    tax_percentage: salesInvoice.tax_percentage || null,
                    tax_amount: salesInvoice.tax_amount || null,
                    down_payment_amount: salesInvoice.down_payment_amount || 0,
                    payment_terms: salesInvoice.payment_terms || null,
                    subtotal: salesInvoice.subtotal || null,
                    total_amount: salesInvoice.total_amount || null,
                    note: salesInvoice.note || null,
                    charges: (salesInvoice.charges || []).map(charge => ({
                        account_id: charge.account_id,
                        description: charge.description,
                        is_taxable: !!charge.is_taxable,
                        amount: charge.amount,
                    })),
                    details: (salesInvoice.items || []).map(item => ({
                        id: item.id,
                        delivery_order_item_id: item.delivery_order_item_id,
                        sales_order_item_id: item.sales_order_item_id,
                        product_id: item.product_id,
                        code: item.product.code,
                        name: item.product.name,
                        unit: item.product.unit.symbol,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        subtotal: item.quantity * item.unit_price,
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
                        delivery_order_item_id: null,
                        sales_order_item_id: null,
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
                    if (this.formData.details.some(d => d.delivery_order_item_id === product.id)) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Produk ' + product.product_name + ' sudah dipilih sebelumnya.'
                        });

                        return;
                    }

                    item.sales_order_item_id = product.sales_order_item_id;
                    item.delivery_order_item_id = product.id;
                    item.product_id = product.product_id;
                    item.code = product.product_code;
                    item.name = product.product_name;
                    item.quantity = product.quantity;
                    item.unit = product.unit_symbol;

                    item.unit_price = product.unit_price;
                    item.subtotal = product.quantity * product.unit_price;
                    item.discount_percentage = product.discount_percentage;
                    item.discount_amount = item.subtotal * (product.discount_percentage / 100);
                    item.total_amount = item.subtotal - item.discount_amount;

                    this.recalculate();
                },

                availableDOItems(q) {
                    const selectedIds = this.formData.details
                        .filter(d => d.delivery_order_item_id)
                        .map(d => d.delivery_order_item_id);

                    let list = remainingDOItems.filter(item => !selectedIds.includes(item.id));

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
                },
                addCharge() {
                    this.formData.charges.push({
                        account_id: null,
                        description: null,
                        is_taxable: false,
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
                chargesTotal() {
                    return this.formData.charges.reduce((sum, c) => sum + this.n(c.amount), 0);
                },
                taxableChargesTotal() {
                    return this.formData.charges.filter(c => c.is_taxable).reduce((sum, c) => sum + this.n(c.amount), 0);
                },
                recalculate() {
                    const sub = this.formData.details.reduce((sum, d) => sum + this.n(d.total_amount), 0);
                    this.formData.subtotal = sub;
                    this.formData.discount_amount = Math.round((this.n(this.formData.discount_percentage) / 100) * sub);
                    this.formData.tax_amount = Math.round((this.n(this.formData.tax_percentage) / 100) * (sub - this.n(this
                        .formData.discount_amount) + this.taxableChargesTotal()));
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
                    if (this.n(salesInvoice.sales_order.down_payment_remaining_amount) > 0) {
                        if (this.n(this.formData.down_payment_amount) < 0) {
                            this.formData.down_payment_amount = 0;
                        } else if (this.n(this.formData.down_payment_amount) > this.n(salesInvoice.sales_order.down_payment_remaining_amount)) {
                            this.formData.down_payment_amount = this.n(salesInvoice.sales_order.down_payment_remaining_amount);
                        }
                    } else {
                        this.formData.down_payment_amount = 0;
                    }
                    this.recalculate();
                },

                init() {
                    this.paymentTermSelected = this.paymentTerms.find(t => t.id === this.formData.payment_terms) ||
                        null;
                    this.recalculate();
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
                    body.charges = body.charges
                        .filter(c => c.account_id && this.n(c.amount) > 0)
                        .map(c => ({ ...c, amount: this.n(c.amount) }));

                    Swal.fire({
                        title: 'Memproses penyimpanan draft tagihan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('sales.sales_invoices.update', this.formData.id), body
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
                    body.charges = body.charges
                        .filter(c => c.account_id && this.n(c.amount) > 0)
                        .map(c => ({ ...c, amount: this.n(c.amount) }));

                    Swal.fire({
                        title: 'Memproses penyimpanan tagihan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await axios.put(
                            route('sales.sales_invoices.update', this.formData.id), body
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

    <div x-data="salesInvoiceForm()" x-init="init()" class="order-page">

        <div>
            <a href="{{ route('sales.sales_invoices.index') }}" class="btn btn-ghost btn-sm"
                style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Edit Tagihan Penjualan</h1>
            <div class="order-sub">Ubah dokumen Tagihan yang ada</div>
        </div>

        {{-- Info Tagihan --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Tagihan</div>
            <div class="order-form-grid-4">

                {{-- Pelanggan --}}
                <x-misc.field label="Pelanggan" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
                        <x-misc.avatar :name="$salesInvoice->customer->name" />
                        <span style="flex:1; font-weight:500;">{{ $salesInvoice->customer->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Nomor SO --}}
                <x-misc.field label="No. SO" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $salesInvoice->salesOrder->number }}</span>
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

                {{-- Gudang --}}
                <x-misc.field label="Gudang" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
                        <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
                        <span style="flex:1;">{{ $salesInvoice->warehouse->name }}</span>
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
                                        <x-misc.select display="it.product_id ? it.name : 'Pilih Produk'"
                                            hasValue="it.product_id" placeholder="Cari produk..." min-width="320px"
                                            height="32px">
                                            <template x-for="p in availableDOItems(q)" :key="p.id">
                                                <div class="dropdown-item" @click="selectProduct(it, p);open=false;q=''">
                                                    <div style="flex:1; min-width:0;">
                                                        <div style="font-size:13px;" x-text="p.product_name"></div>
                                                        <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                            x-text="p.product_code"></div>
                                                    </div>
                                                    <span class="dropdown-item__sub" x-text="p.unit_symbol"></span>
                                                </div>
                                            </template>
                                            <template x-if="availableDOItems(q).length === 0">
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

        @include('sales.partials.additional-charge-table', ['accounts' => $accounts])

        <div class="card" style="overflow:visible;">
            <div class="order-items-split">
                <div class="order-extras">
                    <x-misc.field label="Catatan Internal">
                        <textarea class="input" rows="2" placeholder="Tulis catatan untuk tim finance…"
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

                        {{-- Group 2: Diskon, Pajak, Ongkos Kirim, Biaya Lain-lain -> Subtotal --}}
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
                                <span class="num order-summary__val" x-text="NumberUtils.formatNumericIntoMask(chargesTotal())"></span>
                            </div>

                        </div>

                        <template x-if="salesInvoice.sales_order && salesInvoice.sales_order.down_payment_remaining_amount > 0">
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
                                                :value="salesInvoice.sales_order.down_payment_remaining_amount ? NumberUtils.formatNumericIntoMask(salesInvoice.sales_order.down_payment_remaining_amount) : '0'"
                                                disabled />
                                        </div>
                                        <div class="input-with-prefix">
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
            <a href="{{ route('sales.sales_invoices.index') }}" class="btn btn-ghost">Batal</a>
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submitDraft()">Simpan Draft</button>
            <button class="btn btn-primary" @click="submitOpen()"><x-misc.icon name="check" :size="14" />Simpan
                Tagihan</button>
        </div>

    </div>
@endsection
