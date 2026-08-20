@extends('layouts.app')
@section('content')
    @php
        use App\Enums\SalesOrderStatus;
        use App\Enums\DeliveryOrderStatus;
        $draft = SalesOrderStatus::DRAFT->value;
        $open = SalesOrderStatus::OPEN->value;
        $hasActiveDeliveryOrder = $salesOrder->deliveryOrders
            ->where('status', '<>', DeliveryOrderStatus::CANCELLED->value)
            ->isNotEmpty();
    @endphp
    <div x-data="detailPage()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('sales.sales_orders.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $salesOrder->number }}</h1>
                    <x-misc.status-badge :status="$salesOrder->status" />
                </div>
                <div class="order-sub">
                    Dibuat {{ $salesOrder->created_at->format('d M Y') }} oleh <span
                        style="font-weight:600;">{{ $salesOrder->creator->username }}</span>
                </div>
            </div>
            <div class="order-actions">

                {{-- TODO: Add edit button --}}

                @if ($salesOrder->is_cancellable)
                    <button class="btn btn-ghost" @click="handleCancel({{ $salesOrder->id }})"><x-misc.icon name="x"
                            :size="14" />Batal Pemesanan</button>
                @endif

                @if ($salesOrder->is_deliverable)
                    <button @click="handleCreateDeliveryOrder({{ $salesOrder->id }})" class="btn btn-dark">
                        <x-misc.icon name="truck" :size="14" />Buat Pengiriman
                    </button>
                @endif

                @if ($salesOrder->is_invoicable)
                    <button @click="handleCreateSalesInvoice({{ $salesOrder->id }})" class="btn btn-primary">
                        <x-misc.icon name="wallet" :size="14" />Buat Tagihan
                    </button>
                @endif

                {{-- TODO: Add close button --}}

                {{-- TODO: Add print button --}}
            </div>
        </div>

        <div class="card order-meta">
            @foreach ([['Customer', $salesOrder->customer->name, true], ['Tanggal SO', $salesOrder->order_date->format('d/m/Y'), false], ['Jatuh Tempo', $salesOrder->due_date->format('d/m/Y'), false], ['Gudang', $salesOrder->warehouse->name, false]] as [$lbl, $val, $av])
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
                <div class="display card-hd-title">Daftar Produk</div>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Produk</th>
                        <th style="text-align:right;">Quantity</th>
                        <th>Satuan</th>
                        <th style="text-align:right;">Harga Jual</th>
                        <th style="text-align:right;">Diskon</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrder->items as $i => $it)
                        <tr>
                            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $it->product->name }}</div>
                                <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it->product->code }}
                                </div>
                            </td>
                            <td class="num" style="text-align:right;">{{ number_format($it->quantity, 2) }}
                            </td>
                            <td style="color:var(--ink-3);">{{ $it->product->unit->symbol }}</td>
                            <td class="num" style="text-align:right;">
                                {{ number_format($it->unit_price * $it->quantity, 2) }}
                                ({{ number_format($it->unit_price, 2) }})
                            </td>
                            <td class="num" style="text-align:right;">
                                {{ number_format($it->discount_amount, 2) }}
                                ({{ number_format($it->discount_percentage, 2) }}%)
                            </td>
                            <td class="num" style="text-align:right; font-weight:600;">
                                {{ number_format($it->total_amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:center; font-weight:600;">Total</td>

                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($salesOrder->items->sum('quantity'), 2) }}</td>
                        <td>Unit</td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format(
                                $salesOrder->items->sum(function ($item) {
                                    return $item->unit_price * $item->quantity;
                                }),
                                2,
                                '.',
                                ',',
                            ) }}
                        </td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($salesOrder->items->sum('discount_amount'), 2) }}</td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($salesOrder->items->sum('total_amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($salesOrder->charges->isNotEmpty() || $salesOrder->costs->isNotEmpty())
            <div class="card" style="overflow:hidden;">
                @if ($salesOrder->charges->isNotEmpty())
                    <div class="card-hd">
                        <div class="display card-hd-title">Biaya Tambahan (Tagih ke Customer)</div>
                    </div>
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
                            @foreach ($salesOrder->charges as $i => $charge)
                                <tr>
                                    <td class="mono" style="color:var(--ink-4);">
                                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $charge->description ?: '—' }}</td>
                                    <td>{{ $charge->account->code }} - {{ $charge->account->name }}</td>
                                    <td class="num" style="text-align:right; font-weight:600;">
                                        {{ number_format($charge->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align:center; font-weight:600;">Total</td>
                                <td class="num" style="text-align:right; font-weight:600;">
                                    {{ number_format($salesOrder->charges->sum('amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
                @if ($salesOrder->costs->isNotEmpty())
                    <div class="card-hd" style="border-top:1px solid var(--line-2);">
                        <div class="display card-hd-title">Biaya Tambahan (Internal Only)</div>
                    </div>
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
                            @foreach ($salesOrder->costs as $i => $cost)
                                <tr>
                                    <td class="mono" style="color:var(--ink-4);">
                                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $cost->description ?: '—' }}</td>
                                    <td>{{ $cost->account->code }} - {{ $cost->account->name }}</td>
                                    <td class="num" style="text-align:right; font-weight:600;">
                                        {{ number_format($cost->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align:center; font-weight:600;">Total</td>
                                <td class="num" style="text-align:right; font-weight:600;">
                                    {{ number_format($salesOrder->costs->sum('amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
        @endif

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split" style="grid-template-columns:1fr 320px;">
                <div class="order-notes">
                    <div class="label">Catatan Internal</div>
                    <div class="order-notes__text">{{ $salesOrder->note }}</div>
                </div>
                <div class="order-detail-summary">
                    @foreach ([
                        ['Nilai Bruto',               $salesOrder->items->sum('subtotal'),          false, false],
                        ['Diskon Item',               -$salesOrder->items->sum('discount_amount'),  false, false],
                        ['Subtotal',                  $salesOrder->subtotal,                        false, true],
                        ['Diskon',                    -$salesOrder->discount_amount,                false, false],
                        ['Pajak',                     $salesOrder->tax_amount,                      false, false],
                        ['Biaya Tambahan (Customer)', $salesOrder->charges->sum('amount'),          false, false],
                        ['Uang Muka',                 -$salesOrder->down_payment_amount,            false, false],
                        ['Total',                     $salesOrder->total_amount,                    true,  true],
                    ] as [$lbl, $val, $bold, $divider])
                        <div
                            style="display:flex; justify-content:space-between; padding:6px 0; font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $divider ? 'border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;' : '' }}">
                            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
                            <span class="num"
                                style="color:{{ $bold ? 'var(--accent)' : 'var(--ink)' }};">{{ $val < 0 ? '-' : '' }}{{ number_format(abs($val), 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function detailPage() {
            return {
                async handleCancel(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membatalkan SO ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, batalkan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            try {
                                const response = await axios.post(route(
                                    'sales.sales_orders.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let message = 'Terjadi kesalahan saat membatalkan SO. Silakan coba lagi.';
                                if (error.response?.data?.message) {
                                    message = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: message
                                });
                            }

                        }
                    })
                },

                async handleCreateDeliveryOrder(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuat Pengiriman Barang untuk SO ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            try {
                                const response = await axios.post(route(
                                    'sales.delivery_orders.store', {
                                        sales_order_id: id
                                    }));
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
                            }

                        }
                    })
                },

                async handleCreateSalesInvoice(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuat Tagihan untuk SO ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            try {
                                const response = await axios.post(route(
                                    'sales.sales_invoices.store', {
                                        sales_order_id: id
                                    }));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });

                                window.location.href = response.data.redirect;
                            } catch (error) {
                                Swal.close();
                                let message =
                                    'Terjadi kesalahan saat membuat Tagihan. Silakan coba lagi.';
                                if (error.response?.data?.message) {
                                    message = error.response.data.message;
                                }
                                Toast.fire({
                                    icon: 'error',
                                    title: message
                                });
                            }

                        }
                    })
                }
            };
        }
    </script>
@endpush
