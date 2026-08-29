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
                {{-- TODO: Add edit button --}}
                
                @if ($purchaseOrder->is_cancellable)
                    <button class="btn btn-ghost" @click="handleCancel({{ $purchaseOrder->id }})"><x-misc.icon
                            name="x" :size="14" />Batal Pemesanan</button>
                @endif

                @if ($purchaseOrder->is_receivable)
                    <button @click="handleCreateGoodsReceipt({{ $purchaseOrder->id }})" class="btn btn-dark">
                        <x-misc.icon name="truck" :size="14" />Buat Penerimaan
                    </button>
                @endif

                @if ($purchaseOrder->is_invoicable)
                    <button @click="handleCreatePurchaseInvoice({{ $purchaseOrder->id }})" class="btn btn-primary">
                        <x-misc.icon name="wallet" :size="14" />Buat Tagihan
                    </button>
                @endif

                {{-- TODO: Add print button --}}
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
                            <td class="num" style="text-align:right;">{{ number_format($it->quantity, 2) }}
                            </td>
                            <td style="color:var(--ink-3);">{{ $it->product->unit->name }}</td>
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
                            {{ number_format($purchaseOrder->items->sum('quantity'), 2) }}</td>
                        <td>Unit</td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($purchaseOrder->items->sum(function($item) {
                                return $item->unit_price * $item->quantity;
                            }), 2) }}</td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($purchaseOrder->items->sum('discount_amount'), 2) }}</td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($purchaseOrder->items->sum('total_amount'), 2) }}</td>
                    </tr>
            </table>
        </div>

        @php
            $inventoryCostTotal = $purchaseOrder->costs->where('is_inventory_cost', true)->sum('amount');
            $nonInventoryCostTotal = $purchaseOrder->costs->where('is_inventory_cost', false)->sum('amount');
        @endphp
        @if ($purchaseOrder->costs->isNotEmpty())
            <div class="card" style="overflow:hidden;">
                <div class="card-hd">
                    <div class="display card-hd-title">Biaya Tambahan (Landed Cost)</div>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Deskripsi</th>
                            <th>Akun</th>
                            <th>Ditagih Oleh</th>
                            <th style="text-align:center;">Biaya Inventory</th>
                            <th style="text-align:right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->costs as $i => $cost)
                            <tr>
                                <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>{{ $cost->description ?: '—' }}</td>
                                <td>{{ $cost->account->code }} - {{ $cost->account->name }}</td>
                                <td>{{ \App\Enums\BilledBy::from($cost->billed_by)->label() }}</td>
                                <td style="text-align:center;">{{ $cost->is_inventory_cost ? 'Ya' : 'Tidak' }}</td>
                                <td class="num" style="text-align:right; font-weight:600;">
                                    {{ number_format($cost->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align:center; font-weight:600;">Total</td>
                            <td class="num" style="text-align:right; font-weight:600;">
                                {{ number_format($purchaseOrder->costs->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split" style="grid-template-columns:1fr 320px;">
                <div class="order-notes">
                    <div class="label">Catatan Pembelian</div>
                    <div class="order-notes__text">{{ $purchaseOrder->note }}</div>
                </div>
                <div class="order-detail-summary">
                    @foreach ([
                        ['Nilai Bruto',                    $purchaseOrder->items->sum('subtotal'),         false, false],
                        ['Diskon Item',                    -$purchaseOrder->items->sum('discount_amount'),  false, false],
                        ['Subtotal',                       $purchaseOrder->subtotal,                        false, true],
                        ['Diskon',                         -$purchaseOrder->discount_amount,                false, false],
                        ['Pajak',                          $purchaseOrder->tax_amount,                      false, false],
                        ['Biaya Tambahan (Inventory)',     $inventoryCostTotal,                             false, false],
                        ['Biaya Tambahan (Non-Inventory)', $nonInventoryCostTotal,                          false, false],
                        ['Total Pesanan',                  $purchaseOrder->total_amount,                    true,  true],
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
    </div>
@endsection

@push('scripts')
    <script>
        function detailPage() {
            return {
                m(v) {
                    return NumberUtils.formatNumericIntoMask(v);
                },

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
                },

                async handleCreatePurchaseInvoice(id) {
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin membuat Tagihan untuk PO ini?',
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
                                    'purchasings.purchase_invoices.store', {
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
                },


            };
        }
    </script>
@endpush
