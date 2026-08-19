@extends('layouts.app')
@section('content')
    @php
        use App\Enums\AccountPayableStatusEnum;
        $draft = AccountPayableStatusEnum::DRAFT->value;
        $open = AccountPayableStatusEnum::OPEN->value;
        $partial = AccountPayableStatusEnum::PARTIAL->value;
        $paid = AccountPayableStatusEnum::PAID->value;
        $cancelled = AccountPayableStatusEnum::CANCELLED->value;

        $canPay = $invoice->remaining_amount > 0 && in_array($invoice->status, [$open, $partial]);
        $paidAmount = $invoice->total_amount - $invoice->remaining_amount;
        $paymentMethodLabels = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'credit_card' => 'Credit Card'];
    @endphp
    <div x-data="paymentModule()" x-init="init()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('finances.account_payables.index') }}" class="btn btn-ghost btn-sm"
                    style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $invoice->number }}</h1>
                    <x-misc.status-badge :status="$invoice->status" />
                </div>
                <div class="order-sub">{{ $invoice->supplier->name }}</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost" type="button">
                    {{-- TODO: Implement print functionality --}}
                    <x-misc.icon name="print" :size="14" />Print Invoice
                </button>
            </div>

        </div>

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split" style="grid-template-columns:1fr 320px;">
                <div style="padding:20px;">
                    <div class="label" style="margin-bottom:14px; font-weight:700;">Informasi Invoice</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px 24px;">
                        <div>
                            <div class="label" style="font-size:11px;">No. Invoice</div>
                            <div style="font-weight:600;">{{ $invoice->number }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Supplier</div>
                            <div style="font-weight:600;">{{ $invoice->supplier->name }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Tanggal Invoice</div>
                            <div>{{ $invoice->invoice_date->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Jatuh Tempo</div>
                            <div>{{ $invoice->due_date->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Catatan</div>
                            <div>{{ $invoice->note ?: '—' }}</div>
                        </div>
                    </div>
                </div>
                <div class="order-detail-summary" style="padding:20px; border-left:1px solid var(--line-2);">
                    <div style="display:flex; justify-content:space-between; padding:6px 0;">
                        <span style="color:var(--ink-3);">Jumlah Invoice</span>
                        <span class="num" style="font-weight:600;" x-text="m(invoiceData.total_amount)"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:6px 0;">
                        <span style="color:var(--ink-3);">Jumlah Dibayar</span>
                        <span class="num" style="font-weight:600; color:var(--good);" x-text="m(tableData.data.reduce((sum, p) => sum + p.amount, 0))"></span>
                    </div>
                    <div
                        style="display:flex; justify-content:space-between; padding:6px 0; border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;">
                        <span style="color:var(--ink);font-weight:700;">Sisa Tagihan</span>
                        <span class="num"
                            style="font-weight:700; color:var(--bad);" x-text="m(outstandingAmount())"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Riwayat Pembayaran</div>
                @if ($canPay)
                    <button class="btn btn-ghost btn-sm" type="button" @click="openModal()">
                        <x-misc.icon name="plus" :size="13" />Tambah Pembayaran
                    </button>
                @endif
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Pembayaran</th>
                        <th>Tanggal Pembayaran</th>
                        <th>Metode Pembayaran</th>
                        <th>No. Referensi</th>
                        <th style="text-align:right;">Jumlah</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="tableLoading">
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Memuat data...
                            </td>
                        </tr>
                    </template>
                    <template x-if="!tableLoading && tableData.data.length === 0">
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">
                                Tidak ada data
                            </td>
                        </tr>
                    </template>
                    <template x-if="!tableLoading && tableData.data.length > 0">
                        <template x-for="(payment, index) in tableData.data" :key="payment.id">
                            <tr>
                                <td x-text="index + 1"></td>
                                <td class="mono" style="font-weight:600;" x-text="payment.number"></td>
                                <td style="color:var(--ink-3);"
                                    x-text="new Date(payment.payment_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })">
                                </td>
                                <td x-text="paymentMethod(payment.payment_method)"></td>
                                <td class="mono" style="font-size:11.5px; color:var(--ink-4);"
                                    x-text="payment.reference_number || '—'"></td>
                                <td class="num" style="text-align:right; font-weight:600;" x-text="m(payment.amount)">
                                </td>
                                <td style="color:var(--ink-3);" x-text="payment.note || '—'"></td>
                            </tr>
                        </template>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right; font-weight:700;">Total Dibayar</td>
                        <td class="num" style="text-align:right; font-weight:700;"
                            x-text="m(tableData.data.reduce((sum, p) => sum + p.amount, 0))"></td>
                        <td></td>
                    </tr>
                </tfoot>
                {{-- @if ($invoice->payments->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align:right; font-weight:700;">Total Dibayar</td>
                            <td class="num" style="text-align:right; font-weight:700;">
                                {{ fmt_rp($invoice->payments->sum('amount')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif --}}
            </table>
        </div>

        {{-- Add Payment modal --}}
        @include('finance.account-payable.partials.payment-modal')
    </div>
@endsection

@push('scripts')
    <script>
        function paymentModule() {
            return {
                invoiceData: @js($invoice),
                invoiceId: {{ $invoice->id }},
                cashBankAccounts: @json($cashBankAccounts),
                modalOpen: false,
                saving: false,
                form: {
                    payment_date: new Date().toISOString().substring(0, 10),
                    account_id: null,
                    payment_method: 'cash',
                    reference_number: '',
                    reference_id: {{ $invoice->id }},
                    reference_type: '{{ $type }}',
                    amount: '',
                    note: '',
                },
                tableData: {
                    data: [],
                },
                tableLoading: false,

                async fetchData() {
                    this.tableLoading = true;
                    try {
                        const response = await axios.get(route('finances.account_payables.payment_datatable'), {
                            params: {
                                reference_id: this.invoiceId,
                                reference_type: this.form.reference_type,
                            },
                        });
                        this.tableData = response.data;
                    } catch (error) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data. Silakan coba lagi.'
                        });
                    } finally {
                        this.tableLoading = false;
                    }
                },

                async init() {
                    
                    Swal.fire({
                        title: 'Memuat data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    if (new URLSearchParams(window.location.search).get('pay') === '1') {
                        this.openModal();
                    }

                    await this.fetchData();

                    Swal.close();
                },

                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

                outstandingAmount() {
                    return this.invoiceData.total_amount - this.tableData.data.reduce((sum, p) => sum + p.amount, 0);
                },

                openModal() {
                    this.form.payment_date = new Date().toISOString().substring(0, 10);
                    this.form.account_id = null;
                    this.form.payment_method = 'cash';
                    this.form.reference_number = '';
                    this.form.amount = '';
                    this.form.note = '';
                    this.modalOpen = true;
                },

                paymentMethod(method) {
                    const map = {
                        cash: 'Cash',
                        bank_transfer: 'Transfer Bank',
                        credit_card: 'Kartu Kredit',
                    };
                    return map[method] ?? method;
                },

                async submit() {
                    const amount = NumberUtils.parseMaskIntoNumeric(this.form.amount);

                    if (!this.form.account_id) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Pilih akun kas/bank terlebih dahulu.'
                        });
                        return;
                    }
                    if (!amount || amount <= 0) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Jumlah pembayaran harus lebih dari 0.'
                        });
                        return;
                    }
                    if (amount > this.outstandingAmount()) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Jumlah pembayaran melebihi outstanding invoice.'
                        });
                        return;
                    }

                    let body = {
                        account_id: this.form.account_id,
                        payment_date: this.form.payment_date,
                        payment_method: this.form.payment_method,
                        reference_number: this.form.reference_number,
                        reference_id: this.form.reference_id,
                        reference_type: this.form.reference_type,
                        amount: amount,
                        note: this.form.note,
                    };

                    Swal.fire({
                        title: 'Memproses penyimpanan pembayaran...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    try {

                        const response = await axios.post(route('finances.account_payables.store', this.invoiceId),
                            body);
                        Swal.close();
                        this.modalOpen = false;
                        Toast.fire({
                            icon: 'success',
                            title: response.data.message
                        });
                        this.fetchData();
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
@endpush
