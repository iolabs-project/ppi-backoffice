@extends('layouts.app')
@section('content')
    @php
        use App\Enums\DeliveryOrderStatus;
        $draft = DeliveryOrderStatus::DRAFT->value;
        $finished = DeliveryOrderStatus::FINISHED->value;
    @endphp
    <div x-data="detailPage()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('sales.delivery_orders.index') }}" class="btn btn-ghost btn-sm"
                    style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $deliveryOrder->number }}</h1>
                    <x-misc.status-badge :status="$deliveryOrder->status" />
                </div>
                <div class="order-sub">
                    Pengiriman untuk SO <span style="font-weight:600;">{{ $deliveryOrder->salesOrder->number }}</span>
                </div>
            </div>
            <div class="order-actions">
                @if ($deliveryOrder->status == $draft)
                    <a href="{{ route('sales.delivery_orders.edit', $deliveryOrder->id) }}" class="btn btn-primary">
                        <x-misc.icon name="edit" :size="14" />Edit Pengiriman
                    </a>
                    <button class="btn btn-ghost" @click="handleCancel({{ $deliveryOrder->id }})"><x-misc.icon name="x"
                            :size="14" />Batal Pengiriman</button>
                @endif

                @if ($deliveryOrder->status == $finished)
                    <a href="{{ route('sales.sales_orders.show', $deliveryOrder->sales_order_id) }}" class="btn btn-primary">
                        <x-misc.icon name="receipt" :size="14" />Buat Tagihan
                    </a>
                @endif
            </div>
        </div>

        <div class="card order-meta">
            @foreach ([['Customer', $deliveryOrder->customer->name, true], ['Nomor SO', $deliveryOrder->salesOrder->number, false], ['Tanggal Pengiriman', $deliveryOrder->delivery_date->format('d/m/Y'), false], ['Gudang Asal', $deliveryOrder->warehouse->name, false]] as [$lbl, $val, $av])
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
                <div style="font-size:12px; color:var(--ink-4);">{{ count($deliveryOrder->items) }} item ·
                    {{ $deliveryOrder->items->sum('quantity') }} unit dikirim</div>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Produk</th>
                        <th style="text-align:right;">Quantity Dikirim</th>
                        <th>Satuan</th>
                        <th>Batch</th>
                        <th style="text-align:right;">HPP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveryOrder->items as $i => $it)
                        <tr>
                            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $it->product->name }}</div>
                                <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it->product->code }}
                                </div>
                            </td>
                            <td class="num" style="text-align:right;">{{ $it->quantity }}</td>
                            <td style="color:var(--ink-3);">{{ $it->product->unit->symbol ?? '-' }}</td>
                            <td>
                                @foreach ($it->batches as $b)
                                    <div class="mono" style="font-size:12px;">{{ $b->productBatch->batch_number }} ·
                                        {{ $b->quantity }}</div>
                                @endforeach
                            </td>
                            <td class="num" style="text-align:right; font-weight:600;">
                                {{ fmt_rp($it->batches->sum(fn($b) => $b->quantity * $b->unit_cost)) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="order-notes">
                <div class="label">Catatan Penerimaan</div>
                <div class="order-notes__text">{{ $deliveryOrder->note ?? '-' }}</div>
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
                        title: 'Apakah Anda yakin ingin membatalkan Pengiriman Barang ini?',
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
                                    'sales.delivery_orders.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });

                                window.location.href = route('sales.delivery_orders.index');
                            } catch (error) {
                                Swal.close();
                                let message =
                                    'Terjadi kesalahan saat membatalkan Pengiriman Barang. Silakan coba lagi.';
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
            };
        }
    </script>
@endpush
