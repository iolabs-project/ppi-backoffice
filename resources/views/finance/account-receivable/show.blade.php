@extends('layouts.app')
@section('content')
    @php
        $paidAmount = $salesInvoice->total_amount - $salesInvoice->remaining_amount;
        $canPay = $salesInvoice->remaining_amount > 0 && $salesInvoice->status !== 'cancelled';
        $paymentMethodLabels = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'credit_card' => 'Credit Card'];
    @endphp
    <div x-data="arShowPage()" x-init="init()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('finances.account_receivables.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $salesInvoice->number }}</h1>
                    <x-misc.status-badge :status="$displayStatus" />
                </div>
                <div class="order-sub">{{ $salesInvoice->customer->name }}</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost" type="button">
                    {{-- TODO: Implement print functionality --}}
                    <x-misc.icon name="print" :size="14" />Print Invoice
                </button>
                @if ($canPay)
                    <button class="btn btn-primary" type="button" @click="openModal()">
                        <x-misc.icon name="plus" :size="14" />Add Payment
                    </button>
                @endif
            </div>
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split" style="grid-template-columns:1fr 320px;">
                <div style="padding:20px;">
                    <div class="label" style="margin-bottom:14px;">Invoice Information</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px 24px;">
                        <div>
                            <div class="label" style="font-size:11px;">Invoice No.</div>
                            <div style="font-weight:600;">{{ $salesInvoice->number }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Customer</div>
                            <div style="font-weight:600;">{{ $salesInvoice->customer->name }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Invoice Date</div>
                            <div>{{ $salesInvoice->invoice_date->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Due Date</div>
                            <div>{{ $salesInvoice->due_date->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Currency</div>
                            <div>IDR</div>
                        </div>
                        <div>
                            <div class="label" style="font-size:11px;">Description</div>
                            <div>{{ $salesInvoice->note ?: '—' }}</div>
                        </div>
                    </div>
                </div>
                <div class="order-detail-summary" style="padding:20px; border-left:1px solid var(--line-2);">
                    <div style="display:flex; justify-content:space-between; padding:6px 0;">
                        <span style="color:var(--ink-3);">Invoice Total</span>
                        <span class="num" style="font-weight:600;">{{ fmt_rp($salesInvoice->total_amount) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:6px 0;">
                        <span style="color:var(--ink-3);">Paid Amount</span>
                        <span class="num" style="font-weight:600; color:var(--good);">{{ fmt_rp($paidAmount) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;">
                        <span style="color:var(--ink);font-weight:700;">Outstanding</span>
                        <span class="num" style="font-weight:700; color:var(--bad);">{{ fmt_rp($salesInvoice->remaining_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Payment History</div>
                @if ($canPay)
                    <button class="btn btn-ghost btn-sm" type="button" @click="openModal()">
                        <x-misc.icon name="plus" :size="13" />Add Payment
                    </button>
                @endif
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Payment No.</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                        <th>Reference No.</th>
                        <th style="text-align:right;">Amount</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesInvoice->payments as $payment)
                        <tr>
                            <td class="mono" style="font-weight:600;">{{ $payment->number }}</td>
                            <td style="color:var(--ink-3);">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>{{ $paymentMethodLabels[$payment->payment_method] ?? $payment->payment_method }}</td>
                            <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $payment->reference_number ?: '—' }}</td>
                            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($payment->amount) }}</td>
                            <td style="color:var(--ink-3);">{{ $payment->note ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--ink-4); padding:16px 0;">Belum ada pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($salesInvoice->payments->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align:right; font-weight:700;">Total Paid</td>
                            <td class="num" style="text-align:right; font-weight:700;">{{ fmt_rp($salesInvoice->payments->sum('amount')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Add Payment modal --}}
        <x-misc.modal title="Add Payment" show="modalOpen" close-handler="modalOpen = false" :width="560">
            <div class="form-body">
                <div class="card" style="padding:12px 14px; margin-bottom:14px; background:var(--bg-3);">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 20px; font-size:12.5px;">
                        <div><span style="color:var(--ink-3);">Invoice No.</span><br><strong>{{ $salesInvoice->number }}</strong></div>
                        <div><span style="color:var(--ink-3);">Customer</span><br><strong>{{ $salesInvoice->customer->name }}</strong></div>
                        <div><span style="color:var(--ink-3);">Invoice Total</span><br><strong>{{ fmt_rp($salesInvoice->total_amount) }}</strong></div>
                        <div><span style="color:var(--ink-3);">Outstanding</span><br><strong style="color:var(--bad);">{{ fmt_rp($salesInvoice->remaining_amount) }}</strong></div>
                    </div>
                </div>
                <div class="form-grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <x-misc.field label="Payment No.">
                        <input class="input mono" disabled value="Otomatis saat disimpan" style="color:var(--ink-4);" />
                    </x-misc.field>
                    <x-misc.field label="Payment Date" :required="true">
                        <input type="date" class="input" x-model="form.payment_date" />
                    </x-misc.field>
                    <x-misc.field label="Cash / Bank Account" :required="true">
                        <x-misc.select
                            display="form.account_id ? (cashBankAccounts.find(a => a.id === form.account_id)?.code + ' - ' + cashBankAccounts.find(a => a.id === form.account_id)?.name) : 'Pilih akun'"
                            hasValue="form.account_id" placeholder="Cari akun..." min-width="240px">
                            <template x-for="a in cashBankAccounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || (a.code || '').toLowerCase().includes(q.toLowerCase()))" :key="a.id">
                                <div class="dropdown-item" @click="form.account_id = a.id; open = false; q = ''">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:13px;" x-text="a.name"></div>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="a.code"></div>
                                    </div>
                                </div>
                            </template>
                        </x-misc.select>
                    </x-misc.field>
                    <x-misc.field label="Payment Method" :required="true">
                        <select class="input" x-model="form.payment_method">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Reference Number">
                        <input class="input" x-model="form.reference_number" placeholder="Nomor referensi" />
                    </x-misc.field>
                    <x-misc.field label="Amount" :required="true">
                        <input class="input num" style="text-align:right;" x-model="form.amount"
                            x-mask:dynamic="$money($input, '.',',')" />
                        <div style="font-size:11px; color:var(--ink-4); margin-top:4px;">
                            Maximum amount: {{ fmt_rp($salesInvoice->remaining_amount) }}
                        </div>
                    </x-misc.field>
                </div>
                <div style="margin-top:14px;">
                    <x-misc.field label="Note">
                        <textarea class="input" rows="3" x-model="form.note" placeholder="Catatan pembayaran (opsional)"></textarea>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" type="button" @click="modalOpen = false">
                    <x-misc.icon name="x" :size="14" />Cancel
                </button>
                <button class="btn btn-primary" type="button" :disabled="saving" @click="submit()">
                    <x-misc.icon name="check" :size="14" />Save
                </button>
            </x-slot:footer>
        </x-misc.modal>
    </div>
@endsection

@push('scripts')
    <script>
        function arShowPage() {
            return {
                invoiceId: {{ $salesInvoice->id }},
                outstanding: {{ $salesInvoice->remaining_amount }},
                cashBankAccounts: @json($cashBankAccounts),
                modalOpen: false,
                saving: false,
                form: {
                    payment_date: new Date().toISOString().substring(0, 10),
                    account_id: null,
                    payment_method: 'cash',
                    reference_number: '',
                    amount: '',
                    note: '',
                },

                init() {
                    if (new URLSearchParams(window.location.search).get('pay') === '1') {
                        this.openModal();
                    }
                },

                openModal() {
                    this.form = {
                        payment_date: new Date().toISOString().substring(0, 10),
                        account_id: null,
                        payment_method: 'cash',
                        reference_number: '',
                        amount: '',
                        note: '',
                    };
                    this.modalOpen = true;
                },

                async submit() {
                    const amount = NumberUtils.parseMaskIntoNumeric(this.form.amount);

                    if (!this.form.account_id) {
                        Toast.fire({ icon: 'error', title: 'Pilih akun kas/bank terlebih dahulu.' });
                        return;
                    }
                    if (!amount || amount <= 0) {
                        Toast.fire({ icon: 'error', title: 'Jumlah pembayaran harus lebih dari 0.' });
                        return;
                    }
                    if (amount > this.outstanding) {
                        Toast.fire({ icon: 'error', title: 'Jumlah pembayaran melebihi outstanding invoice.' });
                        return;
                    }

                    this.saving = true;
                    try {
                        const response = await axios.post(route('finances.account_receivables.store', this.invoiceId), {
                            account_id: this.form.account_id,
                            payment_date: this.form.payment_date,
                            payment_method: this.form.payment_method,
                            reference_number: this.form.reference_number,
                            amount: amount,
                            note: this.form.note,
                        });
                        Toast.fire({ icon: 'success', title: response.data.message });
                        window.location.href = response.data.redirect;
                    } catch (error) {
                        let message = 'Terjadi kesalahan saat menambahkan pembayaran. Silakan coba lagi.';
                        if (error.response?.data?.message) {
                            message = error.response.data.message;
                        }
                        Toast.fire({ icon: 'error', title: message });
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
@endpush
