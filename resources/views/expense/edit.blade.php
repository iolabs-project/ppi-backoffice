@extends('layouts.app')
@section('content')
    <script>
        const expense = @json($expense);

        function expenseForm() {
            return {
                formData: {
                    id: expense.id || null,
                    contact_id: expense.contact_id || null,
                    number: expense.number || null,
                    reference_number: expense.reference_number || null,
                    expense_date: expense.expense_date || "{{ now()->format('Y-m-d') }}",
                    due_date: expense.due_date || null,
                    payment_terms: expense.payment_terms || null,
                    discount_percentage: expense.discount_percentage || null,
                    discount_amount: expense.discount_amount || null,
                    tax_percentage: expense.tax_percentage || null,
                    tax_amount: expense.tax_amount || null,
                    subtotal: expense.subtotal || null,
                    total_amount: expense.total_amount || null,
                    note: expense.note || null,
                    items: (expense.items || []).map(it => ({
                        account_id: it.account_id,
                        description: it.description,
                        amount: it.amount,
                    })),
                    costs: (expense.costs || []).map(c => ({
                        account_id: c.account_id,
                        description: c.description,
                        amount: c.amount,
                    })),
                },
                // Contact Options
                contacts: @json($contacts),
                contactSelected: null,
                // Payment Terms
                paymentTerms: @json($paymentTerms),
                paymentTermSelected: null,
                // Submit
                isSubmitting: false,

                // Shorthand: parse masked string to number
                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },

                addItem() {
                    this.formData.items.push({
                        account_id: null,
                        description: null,
                        amount: null,
                    });
                },
                removeItem(index) {
                    this.formData.items.splice(index, 1);
                    this.recalculate();
                },
                handleItemInput() {
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
                costsTotal() {
                    return this.formData.costs.reduce((sum, c) => sum + this.n(c.amount), 0);
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
                        const expenseDate = new Date(this.formData.expense_date);
                        const dueDate = new Date(expenseDate.getTime() + days * 24 * 60 * 60 * 1000);
                        this.formData.due_date = dueDate.toISOString().split('T')[0];
                    } else {
                        this.formData.payment_terms = null;
                        this.formData.due_date = null;
                    }
                },
                handleExpenseDateChange() {
                    if (this.paymentTermSelected) {
                        const days = NumberUtils.parseMaskIntoNumeric(this.paymentTermSelected.days);
                        const expenseDate = new Date(this.formData.expense_date);
                        const dueDate = new Date(expenseDate.getTime() + days * 24 * 60 * 60 * 1000);
                        this.formData.due_date = dueDate.toISOString().split('T')[0];
                    }
                },

                recalculate() {
                    const sub = this.formData.items.reduce((sum, it) => sum + this.n(it.amount), 0);
                    this.formData.subtotal = sub;
                    this.formData.discount_amount = Math.round((this.n(this.formData.discount_percentage) / 100) * sub);
                    this.formData.tax_amount = Math.round((this.n(this.formData.tax_percentage) / 100) * (sub - this.n(
                        this.formData.discount_amount)));
                    this.formData.total_amount =
                        sub -
                        this.n(this.formData.discount_amount) +
                        this.n(this.formData.tax_amount) +
                        this.costsTotal();
                },

                initials(name) {
                    return name ? name.split(' ').slice(0, 2).map(w => w[0]).join('') : '?';
                },

                buildBody(status) {
                    const body = {
                        ...this.formData,
                        status
                    };
                    body.discount_percentage = this.n(body.discount_percentage);
                    body.tax_percentage = this.n(body.tax_percentage);
                    body.items = body.items
                        .filter(it => it.account_id)
                        .map(it => ({
                            ...it,
                            amount: this.n(it.amount),
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
                    this.contactSelected = this.contacts.find(c => c.id === this.formData.contact_id) || null;
                    this.paymentTermSelected = this.paymentTerms.find(t => t.id === this.formData.payment_terms) ||
                        null;
                    this.recalculate();
                },

                async submit(status) {
                    this.isSubmitting = true;
                    const titles = {
                        draft: 'Memproses penyimpanan draft biaya...',
                        open: 'Memproses penyimpanan biaya...',
                    };
                    Swal.fire({
                        title: titles[status] ?? 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                    try {
                        const response = await axios.put(
                            route('expenses.update', this.formData.id), this.buildBody(status)
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

    <div x-data="expenseForm()" x-init="init()" class="order-page">

        <div>
            <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <h1 class="order-title display">Edit Biaya</h1>
            <div class="order-sub">Ubah dokumen biaya yang ada</div>
        </div>

        {{-- Info Biaya --}}
        <div class="card card-bd--form">
            <div class="display card-hd-title">Informasi Biaya</div>
            <div class="order-form-grid-4">

                {{-- Supplier/Contact Dropdown --}}
                <x-misc.field label="Supplier / Kontak">
                    <x-misc.select display="contactSelected ? contactSelected.name : 'Pilih supplier / kontak (opsional)'"
                        hasValue="contactSelected" placeholder="Cari kontak...">
                        <template x-for="c in contacts.filter(c => !q || c.name.toLowerCase().includes(q.toLowerCase()))"
                            :key="c.id">
                            <div class="dropdown-item"
                                @click="contactSelected=c; formData.contact_id=c.id; open=false; q=''">
                                <div class="avatar"
                                    style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                                    x-text="initials(c.name)"></div>
                                <span x-text="c.name"></span>
                            </div>
                        </template>
                        <template x-if="!contacts.some(c => !q || c.name.toLowerCase().includes(q.toLowerCase()))">
                            <div class="dropdown-empty">Tidak ditemukan</div>
                        </template>
                    </x-misc.select>
                </x-misc.field>

                {{-- Nomor Biaya --}}
                <x-misc.field label="Nomor Biaya" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;" x-text="formData.number"></span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>

                {{-- Tanggal --}}
                <x-misc.field label="Tanggal Biaya" :required="true">
                    <input type="date" class="input" x-model="formData.expense_date"
                        @change="handleExpenseDateChange" />
                </x-misc.field>

                {{-- Jatuh Tempo --}}
                <x-misc.field label="Jatuh Tempo">
                    <input type="date" class="input" x-model="formData.due_date" />
                </x-misc.field>

                {{-- Termin Pembayaran Dropdown --}}
                <x-misc.field label="Termin Pembayaran">
                    <x-misc.select display="paymentTermSelected ? paymentTermSelected.name : 'Pilih termin pembayaran'"
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
                    <input class="input mono" placeholder="e.g. INV/PLN/0726" x-model="formData.reference_number" />
                </x-misc.field>

            </div>
        </div>

        {{-- Items --}}
        <div class="card" style="overflow:visible;">
            <div class="card-hd">
                <div class="display card-hd-title">Item Biaya</div>
                <button type="button" class="btn btn-ghost btn-sm" @click="addItem()">
                    <x-misc.icon name="plus" :size="13" />Tambah Item
                </button>
            </div>
            @include('expense.partials.item-table', ['accounts' => $accounts])
        </div>

        @include('expense.partials.cost-table', ['accounts' => $accounts])

        <div class="card" style="overflow:visible;">
            <div class="order-items-split">
                <div class="order-extras">
                    <x-misc.field label="Catatan">
                        <textarea class="input" rows="2" placeholder="Tulis catatan internal…"
                            x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>
                <div class="order-summary">
                    <div class="display order-summary__title">Ringkasan</div>

                    <div class="order-summary__grid">

                        <div class="order-summary__group">
                            <div class="order-summary__row">
                                <span class="order-summary__label">Subtotal</span>
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
                                <span class="order-summary__label">Biaya Tambahan</span>
                                <span class="num order-summary__val"
                                    x-text="NumberUtils.formatNumericIntoMask(costsTotal())"></span>
                            </div>
                        </div>

                    </div>

                    <div class="order-summary__total">
                        <span class="order-summary__total-label">Total Biaya</span>
                        <span class="order-summary__total-value display num"
                            x-text="'Rp ' + (formData.total_amount ? NumberUtils.formatNumericIntoMask(formData.total_amount) : '0')"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-form-footer">
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submit('draft')"
                :disabled="isSubmitting">Simpan Draft</button>
            <button class="btn btn-primary" @click="submit('open')" :disabled="isSubmitting"><x-misc.icon
                    name="check" :size="14" />Simpan
                Biaya</button>
        </div>

    </div>
@endsection
