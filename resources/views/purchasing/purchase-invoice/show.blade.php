@extends('layouts.app')
@section('content')
    @php
        use App\Enums\PurchaseInvoiceStatus;
        $draft = PurchaseInvoiceStatus::DRAFT->value;
        $open = PurchaseInvoiceStatus::OPEN->value;
    @endphp
    <div x-data="detailPage()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('purchasings.purchase_invoices.index') }}" class="btn btn-ghost btn-sm"
                    style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $purchaseInvoice->number }}</h1>
                    <x-misc.status-badge :status="$purchaseInvoice->status" />
                </div>
                <div class="order-sub">
                    Dibuat {{ $purchaseInvoice->created_at->format('d M Y') }} oleh <span
                        style="font-weight:600;">{{ $purchaseInvoice->creator->username }}</span>
                </div>
            </div>
            <div class="order-actions">
                {{-- TODO: Add edit button --}}
                
                @if ($purchaseInvoice->status == $draft)
                    <button class="btn btn-ghost" @click="handleCancel({{ $purchaseInvoice->id }})"><x-misc.icon
                            name="x" :size="14" />Batal Tagihan</button>
                @endif
                {{-- TODO: Add print button --}}
            </div>
        </div>

        <div class="card order-meta">
            @foreach ([['Vendor', $purchaseInvoice->supplier->name, true], ['Tanggal PO', $purchaseInvoice->invoice_date->format('d/m/Y'), false], ['Jatuh Tempo', $purchaseInvoice->due_date->format('d/m/Y'), false], ['Gudang Tujuan', $purchaseInvoice->warehouse->name, false]] as [$lbl, $val, $av])
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
                    @foreach ($purchaseInvoice->items as $i => $it)
                        <tr>
                            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $it->product->name }}</div>
                                <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it->product->code }}
                                </div>
                            </td>
                            <td class="num" style="text-align:right;">{{ number_format($it->quantity, 2, '.', ',') }}
                            </td>
                            <td style="color:var(--ink-3);">{{ $it->product->unit->symbol }}</td>
                            <td class="num" style="text-align:right;">
                                {{ number_format($it->unit_price * $it->quantity, 2, '.', ',') }}
                                ({{ fmt_rp($it->unit_price) }})
                            </td>
                            <td class="num" style="text-align:right;">
                                {{ number_format($it->discount_amount, 2, '.', ',') }}
                                ({{ number_format($it->discount_percentage, 2, '.', ',') }}%)
                            </td>
                            <td class="num" style="text-align:right; font-weight:600;">
                                {{ number_format($it->total_amount, 2, '.', ',') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align:center; font-weight:600;">Total</td>

                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($purchaseInvoice->items->sum('quantity'), 2, '.', ',') }}</td>
                        <td>Unit</td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format(
                                $purchaseInvoice->items->sum(function ($item) {
                                    return $item->unit_price * $item->quantity;
                                }),
                                2,
                                '.',
                                ',',
                            ) }}
                        </td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($purchaseInvoice->items->sum('discount_amount'), 2, '.', ',') }}</td>
                        <td class="num" style="text-align:right; font-weight:600;">
                            {{ number_format($purchaseInvoice->items->sum('total_amount'), 2, '.', ',') }}</td>
                    </tr>
            </table>
        </div>

        @php
            $inventoryCostTotal = $purchaseInvoice->costs->where('is_inventory_cost', true)->sum('amount');
            $nonInventoryCostTotal = $purchaseInvoice->costs->where('is_inventory_cost', false)->sum('amount');
        @endphp
        @if ($purchaseInvoice->costs->isNotEmpty())
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
                            <th style="text-align:center;">Biaya Inventory</th>
                            <th style="text-align:right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseInvoice->costs as $i => $cost)
                            <tr>
                                <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>{{ $cost->description ?: '—' }}</td>
                                <td>{{ $cost->account->code }} - {{ $cost->account->name }}</td>
                                <td style="text-align:center;">{{ $cost->is_inventory_cost ? 'Ya' : 'Tidak' }}</td>
                                <td class="num" style="text-align:right; font-weight:600;">
                                    {{ number_format($cost->amount, 2, '.', ',') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="card" style="overflow:hidden;">
            <div class="order-items-split" style="grid-template-columns:1fr 500px;">
                <div class="order-notes">
                    <div class="label">Catatan Pembelian</div>
                    <div class="order-notes__text">{{ $purchaseInvoice->note }}</div>
                </div>
                <div class="order-detail-summary">
                    @foreach ([['Nilai Bruto', $purchaseInvoice->items->sum('subtotal'), false], ['Diskon Item', -$purchaseInvoice->items->sum('discount_amount'), false], ['Subtotal', $purchaseInvoice->subtotal, false], ['Diskon', -$purchaseInvoice->discount_amount, false], ['Pajak', $purchaseInvoice->tax_amount, false], ['Biaya Tambahan (Inventory)', $inventoryCostTotal, false], ['Biaya Tambahan (Non-Inventory)', $nonInventoryCostTotal, false], ['Total Tagihan', $purchaseInvoice->total_amount, true]] as [$lbl, $val, $bold])
                        <div
                            style="display:flex; justify-content:space-between; padding:6px 0; font-size:{{ $bold ? 15 : 13 }}px; font-weight:{{ $bold ? 700 : 500 }}; {{ $bold ? 'border-top:1px solid var(--line-2); margin-top:8px; padding-top:12px;' : '' }}">
                            <span style="color:{{ $bold ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $lbl }}</span>
                            <span class="num"
                                style="color:{{ $bold ? 'var(--accent)' : 'var(--ink)' }};">{{ $val < 0 ? '–' : '' }}{{ number_format(abs($val), 2, '.', ',') }}</span>
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
                        title: 'Apakah Anda yakin ingin membatalkan Tagihan Pembelian ini?',
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
                                    'purchasings.purchase_invoices.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });
                                await this.fetchData();
                            } catch (error) {
                                Swal.close();
                                let message =
                                    'Terjadi kesalahan saat membatalkan Tagihan Pembelian. Silakan coba lagi.';
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
