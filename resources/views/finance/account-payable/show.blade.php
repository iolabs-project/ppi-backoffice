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
        $canVoid = auth()->user()->can('finances.payables.delete');
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
                    @if ($canPay)
                        @foreach ([$open, $partial, $paid] as $badgeStatus)
                            <x-misc.status-badge :status="$badgeStatus" x-show="invoiceStatus === '{{ $badgeStatus }}'"
                                :style="$invoice->status === $badgeStatus ? null : 'display:none'" />
                        @endforeach
                    @else
                        <x-misc.status-badge :status="$invoice->status" />
                    @endif
                </div>
                <div class="order-sub">{{ $invoice->supplier->name }}</div>
            </div>
            <div class="order-actions">
                @php
                    // Hutang comes from a purchase invoice or an expense: print that source document
                    [$printRoute, $printPermission] = $type === \App\Enums\PayablePaymentReferenceTypeEnum::EXPENSE->value
                        ? ['expenses.print', 'finances.expenses.view']
                        : ['purchasings.purchase_invoices.print', 'purchasing.invoices.view'];
                @endphp
                @can($printPermission)
                    <a href="{{ route($printRoute, $invoice->id) }}" target="_blank" rel="noopener" class="btn btn-ghost">
                        <x-misc.icon name="print" :size="14" />Cetak Tagihan
                    </a>
                @endcan
            </div>

        </div>

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split order-items-split--aside">
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
                            <div>{{ $invoice->invoice_date->translatedFormat('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Jatuh Tempo</div>
                            <div>{{ $invoice->due_date->translatedFormat('d M Y') }}</div>
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
                        <span class="num" style="font-weight:600; color:var(--good);" x-text="m(paidTotal())"></span>
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
                @if ($canPay && auth()->user()->can('finances.payables.edit'))
                    <button class="btn btn-ghost btn-sm" type="button" x-show="canPay()" @click="openModal()">
                        <x-misc.icon name="plus" :size="13" />Tambah Pembayaran
                    </button>
                @endif
            </div>
            <div class="tbl-scroll">
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
                            @if ($canVoid)
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="tableLoading">
                            <tr>
                                <td colspan="{{ $canVoid ? 8 : 7 }}" style="text-align:center; color:var(--ink-3); padding:20px;">
                                    Memuat data...
                                </td>
                            </tr>
                        </template>
                        <template x-if="!tableLoading && tableData.data.length === 0">
                            <tr>
                                <td colspan="{{ $canVoid ? 8 : 7 }}" style="text-align:center; color:var(--ink-3); padding:20px;">
                                    Tidak ada data
                                </td>
                            </tr>
                        </template>
                        <template x-if="!tableLoading && tableData.data.length > 0">
                            <template x-for="(payment, index) in tableData.data" :key="payment.id">
                                <tr>
                                    <td x-text="index + 1"></td>
                                    <td class="mono" style="font-weight:600; white-space:nowrap;">
                                        <span x-text="payment.number"></span>
                                        <span class="chip chip-bad" x-show="payment.voided_at"><span class="chip-dot dot-bad"></span>Void</span>
                                    </td>
                                    <td style="color:var(--ink-3);"
                                        x-text="new Date(payment.payment_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })">
                                    </td>
                                    <td x-text="paymentMethod(payment.payment_method)"></td>
                                    <td class="mono" style="font-size:11.5px; color:var(--ink-4);"
                                        x-text="payment.reference_number || '—'"></td>
                                    <td class="num" style="text-align:right; font-weight:600;" x-text="m(payment.amount)"
                                        :style="payment.voided_at ? { textDecoration: 'line-through', color: 'var(--ink-4)' } : {}">
                                    </td>
                                    <td style="color:var(--ink-3);" x-text="payment.note || '—'"></td>
                                    @if ($canVoid)
                                        <td style="text-align:right;">
                                            <button type="button" class="btn btn-ghost btn-sm" x-show="!payment.voided_at"
                                                @click="handleVoid(payment)">Void</button>
                                        </td>
                                    @endif
                                </tr>
                            </template>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align:right; font-weight:700;">Total Dibayar</td>
                            <td class="num" style="text-align:right; font-weight:700;"
                                x-text="m(paidTotal())"></td>
                            <td></td>
                            @if ($canVoid)
                                <td></td>
                            @endif
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
                invoiceStatus: @js($invoice->status),
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
                    return this.invoiceData.total_amount - this.paidTotal();
                },

                // Rounded to cents so float leftovers don't block paying off the full balance
                roundedOutstanding() {
                    return Math.round(this.outstandingAmount() * 100) / 100;
                },

                canPay() {
                    return this.outstandingAmount() > 0 && ['{{ $open }}', '{{ $partial }}'].includes(this.invoiceStatus);
                },

                // Null when the account list carries no balance, so the warning simply stays hidden
                selectedAccountBalance() {
                    const account = this.cashBankAccounts.find(a => a.id === this.form.account_id);
                    return account && account.balance !== undefined && account.balance !== null ? Number(account.balance) : null;
                },

                insufficientBalance() {
                    const balance = this.selectedAccountBalance();
                    return balance !== null && NumberUtils.parseMaskIntoNumeric(this.form.amount) > balance;
                },

                // Mirror the status the backend sets after a payment, so the badge updates without a reload
                syncStatus() {
                    const outstanding = this.roundedOutstanding();
                    if (outstanding <= 0) {
                        this.invoiceStatus = '{{ $paid }}';
                    } else if (outstanding < Number(this.invoiceData.total_amount)) {
                        this.invoiceStatus = '{{ $partial }}';
                    }
                },

                openModal() {
                    this.form.payment_date = new Date().toISOString().substring(0, 10);
                    this.form.account_id = null;
                    this.form.payment_method = 'cash';
                    this.form.reference_number = '';
                    this.form.amount = NumberUtils.formatNumericIntoMask(this.roundedOutstanding());
                    this.form.note = '';
                    this.form.idempotency_key = Date.now().toString(36) + Math.random().toString(36).slice(2);
                    this.modalOpen = true;
                },

                // Voided payments stay in the history but no longer count as paid
                paidTotal() {
                    return this.tableData.data.filter(p => !p.voided_at).reduce((sum, p) => sum + p.amount, 0);
                },

                async handleVoid(payment) {
                    const result = await Swal.fire({
                        title: `Void pembayaran ${payment.number}?`,
                        text: 'Jurnal pembayaran dibalik, transaksi kasnya dibatalkan, dan sisa hutang bertambah kembali.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, void',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    });
                    if (!result.isConfirmed) {
                        return;
                    }

                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    try {
                        await axios.post(route('finances.account_payables.void', payment.id));
                        window.location.reload();
                    } catch (error) {
                        Swal.close();
                        Toast.fire({
                            icon: 'error',
                            title: error.response?.data?.message ?? 'Terjadi kesalahan saat void pembayaran. Silakan coba lagi.'
                        });
                    }
                },

                paymentMethod(method) {
                    const map = {
                        cash: 'Tunai',
                        bank_transfer: 'Transfer Bank',
                        credit_card: 'Kartu Kredit',
                    };
                    return map[method] ?? method;
                },

                async submit() {
                    if (this.saving) return;
                    if (!FormValidation.validateRequired(this.$root)) {
                        return;
                    }
                    
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
                    if (amount > this.roundedOutstanding()) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Jumlah pembayaran melebihi sisa tagihan.'
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
                        idempotency_key: this.form.idempotency_key,
                    };

                    this.saving = true;
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
                        await this.fetchData();
                        this.syncStatus();
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
                        this.saving = false;
                    }
                },
            };
        }
    </script>
@endpush
