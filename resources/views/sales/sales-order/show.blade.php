@extends('layouts.app')
@section('content')
    @php
        use App\Enums\PurchaseOrderStatus;
        $draft = PurchaseOrderStatus::DRAFT->value;
        $open = PurchaseOrderStatus::OPEN->value;
    @endphp
    <div x-data="detailPage()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('purchasings.purchase_orders.index') }}" class="btn btn-ghost btn-sm"
                    style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $purchaseOrder->number }}</h1>
                    <x-misc.status-badge :status="$purchaseOrder->status" />
                </div>
                <div class="order-sub">
                    Dibuat {{ $purchaseOrder->created_at->format('d M Y') }} oleh <span
                        style="font-weight:600;">{{ $purchaseOrder->creator->username }}</span>
                </div>
            </div>
            <div class="order-actions">

                @if ($purchaseOrder->status == $draft)
                    <a href="{{ route('purchasings.purchase_orders.edit', $purchaseOrder->id) }}" class="btn btn-primary">
                        <x-misc.icon name="edit" :size="14" />Edit Pemesanan
                    </a>
                @endif
                @if ($purchaseOrder->status == $draft || ($purchaseOrder->status == $open && !$purchaseOrder->goods_receipts))
                    <button class="btn btn-ghost" @click="handleCancel({{ $purchaseOrder->id }})"><x-misc.icon
                            name="x" :size="14" />Batal Pemesanan</button>
                @endif

                @if ($purchaseOrder->status === $open)
                    <button @click="handleCreateGoodsReceipt({{ $purchaseOrder->id }})" class="btn btn-dark">
                        <x-misc.icon name="truck" :size="14" />Buat Penerimaan
                    </button>
                @endif
            </div>
        </div>

        <div class="card order-meta">
            @foreach ([['Vendor', $purchaseOrder->supplier->name, true], ['Tanggal PO', $purchaseOrder->order_date->format('d/m/Y'), false], ['Jatuh Tempo', $purchaseOrder->due_date->format('d/m/Y'), false], ['Gudang Tujuan', $purchaseOrder->warehouse->name, false]] as [$lbl, $val, $av])
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
                <div style="font-size:12px; color:var(--ink-4);">{{ count($purchaseOrder->items) }} item ·
                    {{ $purchaseOrder->items->sum('quantity') }} unit dipesan</div>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Produk</th>
                        <th style="text-align:right;">Quantity</th>
                        <th>Satuan</th>
                        <th style="text-align:right;">Harga Beli</th>
                        <th style="text-align:right;">Diskon</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $i => $it)
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
            <div class="order-items-split" style="grid-template-columns:1fr 320px;">
                <div class="order-notes">
                    <div class="label">Catatan Pembelian</div>
                    <div class="order-notes__text">{{ $purchaseOrder->note }}</div>
                </div>
                <div class="order-detail-summary">
                    @foreach (
                        [
                            ['Nilai Bruto', $purchaseOrder->items->sum('subtotal'), false], 
                            ['Diskon Item', -$purchaseOrder->items->sum('discount_amount'), false], 
                            ['Subtotal', $purchaseOrder->subtotal, false], 
                            ['Diskon', -$purchaseOrder->discount_amount, false], 
                            ['Pajak', $purchaseOrder->tax_amount, false], 
                            ['Ongkos Kirim', $purchaseOrder->transport_cost, false], 
                            ['Biaya Lain-Lain', $purchaseOrder->other_cost, false], 
                            ['Total Pesanan', $purchaseOrder->total_amount, true]
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
                        title: 'Apakah Anda yakin ingin membatalkan PO ini?',
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
                                    'purchasings.purchase_orders.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });

                                window.location.href = route('purchasings.purchase_orders.index');
                            } catch (error) {
                                Swal.close();
                                let message = 'Terjadi kesalahan saat membatalkan PO. Silakan coba lagi.';
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

                async handleCreateGoodsReceipt(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuat Penerimaan Barang untuk PO ini?',
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
                                    'purchasings.goods_receipts.store', {
                                        purchase_order_id: id
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
                                    'Terjadi kesalahan saat membuat Penerimaan Barang. Silakan coba lagi.';
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
