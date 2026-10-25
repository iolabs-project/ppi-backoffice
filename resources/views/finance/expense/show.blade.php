@extends('layouts.app')
@section('content')
    @php
        use App\Enums\ExpenseStatus;
        use App\Enums\PaymentTerm;
        $draft = ExpenseStatus::DRAFT->value;
        $open = ExpenseStatus::OPEN->value;
        $partial = ExpenseStatus::PARTIAL->value;
        $paymentMethodLabels = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'credit_card' => 'Credit Card'];
        $costTotal = $expense->costs->sum('amount');
        $paidTotal = $expense->payments->sum('amount');
    @endphp
    <div x-data="detailPage()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $expense->number }}</h1>
                    <x-misc.status-badge :status="$expense->status" />
                </div>
                <div class="order-sub">
                    Dibuat {{ $expense->created_at?->translatedFormat('d M Y') }} oleh <span
                        style="font-weight:600;">{{ $expense->creator?->username ?? '—' }}</span>
                </div>
            </div>
            <div class="order-actions">
                @if (auth()->user()->can('finances.expenses.edit') && $expense->status == $draft)
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-ghost">
                        <x-misc.icon name="edit" :size="14" />Edit
                    </a>
                @endif
                @if (auth()->user()->can('finances.expenses.delete') && in_array($expense->status, [$draft, $open], true))
                    <button class="btn btn-ghost" @click="handleCancel({{ $expense->id }})"><x-misc.icon name="x"
                            :size="14" />Batalkan Biaya</button>
                @endif
                @if (auth()->user()->can('finances.payables.view') && in_array($expense->status, [$open, $partial], true))
                    <a href="{{ route('finances.account_payables.show', ['id' => $expense->id, 'reference_type' => 'expense']) }}"
                        class="btn btn-ghost">
                        <x-misc.icon name="wallet" :size="14" />Bayar
                    </a>
                @endif
                <a href="{{ route('expenses.print', $expense->id) }}" target="_blank" rel="noopener" class="btn btn-ghost">
                    <x-misc.icon name="print" :size="14" />Cetak
                </a>
            </div>
        </div>

        <div class="card order-meta">
            @foreach ([
                ['Kontak', $expense->contact?->name ?? '—', (bool) $expense->contact],
                ['Tanggal Biaya', $expense->expense_date?->format('d/m/Y') ?? '—', false],
                ['Jatuh Tempo', $expense->due_date?->format('d/m/Y') ?? '—', false],
                ['Syarat Pembayaran', PaymentTerm::tryFrom((string) $expense->payment_terms)?->label() ?? '—', false],
                ['No. Referensi', $expense->reference_number ?: '—', false],
            ] as [$lbl, $val, $av])
                <div>
                    <div class="label order-meta__label">{{ $lbl }}</div>
                    <div class="order-meta__value">
                        @if ($av)
                            <x-misc.avatar :name="$val" />
                        @endif
                        <span>{{ $val }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Daftar Biaya</div>
            </div>
            <div class="tbl-scroll">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Akun</th>
                            <th>Deskripsi</th>
                            <th style="text-align:right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expense->items as $i => $it)
                            <tr>
                                <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $it->account ? $it->account->code . ' - ' . $it->account->name : '—' }}</td>
                                <td>{{ $it->description ?: '—' }}</td>
                                <td class="num" style="text-align:right; font-weight:600;">{{ number_format($it->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; color:var(--ink-3); padding:20px;">Belum ada item biaya</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:center; font-weight:600;">Total</td>
                            <td class="num" style="text-align:right; font-weight:600;">
                                {{ number_format($expense->items->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if ($expense->costs->isNotEmpty())
            <div class="card" style="overflow:hidden;">
                <div class="card-hd">
                    <div class="display card-hd-title">Biaya Tambahan</div>
                </div>
                <div class="tbl-scroll">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th style="width:48px;">#</th>
                                <th>Deskripsi</th>
                                <th>Akun</th>
                                <th style="text-align:right;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expense->costs as $i => $cost)
                                <tr>
                                    <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $cost->description ?: '—' }}</td>
                                    <td>{{ $cost->account ? $cost->account->code . ' - ' . $cost->account->name : '—' }}</td>
                                    <td class="num" style="text-align:right; font-weight:600;">{{ number_format($cost->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split order-items-split--aside-wide">
                <div class="order-notes">
                    <div class="label">Catatan</div>
                    <div class="order-notes__text">{{ $expense->note }}</div>
                </div>
                <div class="order-detail-summary">
                    @foreach ([
                        ['Subtotal',       $expense->subtotal,         false, false],
                        ['Diskon',         -$expense->discount_amount, false, false],
                        ['Pajak',          $expense->tax_amount,       false, false],
                        ['Biaya Tambahan', $costTotal,                 false, false],
                        ['Total Biaya',    $expense->total_amount,     true,  true],
                        ['Dibayar',        -$paidTotal,                false, false],
                        ['Sisa Tagihan',   $expense->remaining_amount, true,  true],
                    ] as [$lbl, $val, $bold, $divider])
                        <div
                            style="display:flex; justify-content:space-between; padding:6px 0; font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $divider ? 'border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;' : '' }}">
                            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
                            <span class="num"
                                style="color:{{ $bold ? 'var(--accent)' : 'var(--ink)' }};">{{ $val < 0 ? '–' : '' }}{{ number_format(abs($val), 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Riwayat Pembayaran</div>
            </div>
            <div class="tbl-scroll">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No. Pembayaran</th>
                            <th>Tanggal</th>
                            <th>Akun</th>
                            <th>Metode</th>
                            <th>No. Referensi</th>
                            <th style="text-align:right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expense->payments as $i => $payment)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="mono" style="font-weight:600;">{{ $payment->number }}</td>
                                <td style="color:var(--ink-3);">{{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                                <td>{{ $payment->account ? $payment->account->code . ' - ' . $payment->account->name : '—' }}</td>
                                <td>{{ $paymentMethodLabels[$payment->payment_method] ?? $payment->payment_method }}</td>
                                <td class="mono" style="font-size:11.5px; color:var(--ink-4);">{{ $payment->reference_number ?: '—' }}</td>
                                <td class="num" style="text-align:right; font-weight:600;">{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">Belum ada pembayaran</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($expense->payments->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align:right; font-weight:700;">Total Dibayar</td>
                                <td class="num" style="text-align:right; font-weight:700;">{{ number_format($paidTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function detailPage() {
            return {
                async handleCancel(id) {
                    const result = await Swal.fire({
                        title: 'Apakah Anda yakin ingin membatalkan Biaya ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, batalkan',
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
                        const response = await axios.post(route('expenses.cancel', id));
                        Swal.close();
                        Toast.fire({
                            icon: 'success',
                            title: response.data.message
                        });
                        window.location.reload();
                    } catch (error) {
                        Swal.close();
                        Toast.fire({
                            icon: 'error',
                            title: error.response?.data?.message ?? 'Terjadi kesalahan saat membatalkan Biaya. Silakan coba lagi.'
                        });
                    }
                },
            };
        }
    </script>
@endpush
