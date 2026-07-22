@extends('layouts.app')
@section('content')
    @php
        use App\Enums\SalesOrderStatus;
        use App\Enums\DeliveryOrderStatus;
        $draft = SalesOrderStatus::DRAFT->value;
        $open = SalesOrderStatus::OPEN->value;
        $hasActiveDeliveryOrder = $salesOrder->deliveryOrders->where('status', '<>', DeliveryOrderStatus::CANCELLED->value)->isNotEmpty();
    @endphp
    <div x-data="detailPage()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('sales.sales_orders.index') }}" class="btn btn-ghost btn-sm"
                    style="margin-bottom:10px;">
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

                @if ($salesOrder->status == $draft)
                    <a href="{{ route('sales.sales_orders.edit', $salesOrder->id) }}" class="btn btn-primary">
                        <x-misc.icon name="edit" :size="14" />Edit Penjualan
                    </a>
                @endif
                @if ($salesOrder->status == $draft || ($salesOrder->status == $open && !$hasActiveDeliveryOrder))
                    <button class="btn btn-ghost" @click="handleCancel({{ $salesOrder->id }})"><x-misc.icon
                            name="x" :size="14" />Batal Penjualan</button>
                @endif

                @if ($salesOrder->status === $open)
                    <button @click="handleCreateDeliveryOrder({{ $salesOrder->id }})" class="btn btn-dark">
                        <x-misc.icon name="truck" :size="14" />Buat Pengiriman
                    </button>
                @endif
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
                <div style="font-size:12px; color:var(--ink-4);">{{ count($salesOrder->items) }} item ·
                    {{ $salesOrder->items->sum('quantity') }} unit dipesan</div>
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
                            <td class="num" style="text-align:right;">{{ $it->quantity }}</td>
                            <td style="color:var(--ink-3);">{{ $it->product->unit->name }}</td>
                            <td class="num" style="text-align:right;">{{ fmt_rp($it->unit_price * $it->quantity) }} ({{ fmt_rp($it->unit_price) }})</td>
                            <td class="num" style="text-align:right;">{{ fmt_rp($it->discount_amount) }}
                                ({{ $it->discount_percentage }}%)</td>
                            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($it->total_amount) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
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
                                    <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $charge->description ?: '—' }}</td>
                                    <td>{{ $charge->account->code }} - {{ $charge->account->name }}</td>
                                    <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($charge->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
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
                                    <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $cost->description ?: '—' }}</td>
                                    <td>{{ $cost->account->code }} - {{ $cost->account->name }}</td>
                                    <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($cost->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
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
                    @foreach (
                        [
                            ['Nilai Bruto', $salesOrder->items->sum('subtotal'), false],
                            ['Diskon Item', -$salesOrder->items->sum('discount_amount'), false],
                            ['Subtotal', $salesOrder->subtotal, false],
                            ['Diskon', -$salesOrder->discount_amount, false],
                            ['Pajak', $salesOrder->tax_amount, false],
                            ['Biaya Tambahan (Customer)', $salesOrder->charges->sum('amount'), false],
                            ['Uang Muka', -$salesOrder->down_payment_amount, false],
                            ['Total Penjualan', $salesOrder->total_amount, true]
                        ] as [$lbl, $val, $bold])
                        <div
                            style="display:flex; justify-content:space-between; padding:6px 0; font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $bold ? 'border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;' : '' }}">
                            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
                            <span class="num"
                                style="color:{{ $bold ? 'var(--accent)' : 'var(--ink)' }};">{{ $val < 0 ? '–' : '' }}{{ fmt_rp(abs($val)) }}</span>
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

                                window.location.href = route('sales.sales_orders.index');
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
                        title: 'Apakah Anda yakin ingin membuat Pengiriman untuk SO ini?',
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
                                let message =
                                    'Terjadi kesalahan saat membuat Pengiriman. Silakan coba lagi.';
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
