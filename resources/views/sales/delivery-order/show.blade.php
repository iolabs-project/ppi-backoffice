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
                <a href="{{ route('sales.delivery_orders.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $deliveryOrder->number }}</h1>
                    <x-misc.status-badge :status="$deliveryOrder->status" />
                </div>
                <div class="order-sub">
                    Dibuat {{ $deliveryOrder->created_at->format('d M Y') }} oleh <span
                        style="font-weight:600;">{{ $deliveryOrder->creator->username }}</span>
                </div>
            </div>
            <div class="order-actions">
                @if ($deliveryOrder->status === $draft)
                    <button class="btn btn-ghost" @click="handleCancel({{ $deliveryOrder->id }})"><x-misc.icon
                            name="x" :size="14" />Batal Pengiriman</button>
                    <a href="{{ route('sales.delivery_orders.edit', $deliveryOrder->id) }}" class="btn btn-primary">
                        <x-misc.icon name="edit" :size="14" />Edit Pengiriman
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
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Produk</th>
                        <th style="text-align:right;">Quantity Dikirim</th>
                        <th>Satuan</th>
                        <th>Batch</th>
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
                            <td class="num" style="text-align:right;">{{ number_format($it->quantity, 2) }}</td>
                            <td style="color:var(--ink-3);">{{ $it->product->unit->symbol ?? '-' }}</td>
                            <td>
                                @foreach ($it->batches as $b)
                                    <div class="mono" style="font-size:12px;">{{ $b->productBatch->batch_number }} ·
                                        {{ number_format($b->quantity, 2) }}</div>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:right; font-weight:600;">Total</td>
                        <td class="num" style="text-align:right; font-weight:600;">{{ number_format($deliveryOrder->items->sum('quantity'), 2) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($deliveryOrder->costs->isNotEmpty())
            <div class="card" style="overflow:hidden;">
                <div class="card-hd">
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
                        @foreach ($deliveryOrder->costs as $i => $cost)
                            <tr>
                                <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $cost->description ?: '—' }}</td>
                                <td>{{ $cost->account->code }} - {{ $cost->account->name }}</td>
                                <td class="num" style="text-align:right; font-weight:600;">{{ number_format($cost->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:center; font-weight:600;">Total</td>
                            <td class="num" style="text-align:right; font-weight:600;">{{ number_format($deliveryOrder->costs->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split" style="grid-template-columns:1fr 500px;">
                <div class="order-notes">
                    <div class="label">Catatan Pengiriman</div>
                    <div class="order-notes__text">{{ $deliveryOrder->note ?? '-' }}</div>
                </div>
                <div class="order-detail-summary">
                    @foreach ([['Total Biaya Tambahan', $deliveryOrder->costs->sum('amount'), false]] as [$lbl, $val, $bold])
                        <div
                            style="display:flex; justify-content:space-between; padding:6px 0; font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $bold ? 'border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;' : '' }}">
                            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
                            <span class="num" style="color:{{ $bold ? 'var(--accent)' : 'var(--ink)' }};">{{ number_format($val, 2) }}</span>
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
