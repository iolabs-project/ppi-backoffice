@extends('layouts.app')
@section('content')
    @php
        use App\Enums\GoodsReceiptStatus;
        $draft = GoodsReceiptStatus::DRAFT->value;
        $finished = GoodsReceiptStatus::FINISHED->value;
    @endphp
    <div x-data="detailPage()" class="order-page">
        <div class="order-hd order-hd--start">
            <div>
                <a href="{{ route('purchasings.goods_receipts.index') }}" class="btn btn-ghost btn-sm"
                    style="margin-bottom:10px;">
                    <x-misc.icon name="chev-left" :size="13" />Kembali
                </a>
                <div class="order-title-row">
                    <h1 class="order-title display">{{ $goodsReceipt->number }}</h1>
                    <x-misc.status-badge :status="$goodsReceipt->status" />
                </div>
                <div class="order-sub">
                    Dibuat {{ $goodsReceipt->created_at->format('d M Y') }} oleh <span
                        style="font-weight:600;">{{ $goodsReceipt->creator->username }}</span>
                </div>
            </div>
            <div class="order-actions">

                @if ($goodsReceipt->status == $draft)
                    <a href="{{ route('purchasings.goods_receipts.edit', $goodsReceipt->id) }}" class="btn btn-primary">
                        <x-misc.icon name="edit" :size="14" />Edit Penerimaan
                    </a>
                    <button class="btn btn-ghost" @click="handleCancel({{ $goodsReceipt->id }})"><x-misc.icon name="x"
                            :size="14" />Batal Penerimaan</button>
                @endif

                @if ($goodsReceipt->status == $finished)
                    <a href="#" class="btn btn-primary">
                        <x-misc.icon name="receipt" :size="14" />Buat Tagihan
                    </a>
                @endif
            </div>
        </div>

        <div class="card order-meta">
            @foreach ([['Vendor', $goodsReceipt->supplier->name, true], ['Nomor PO', $goodsReceipt->purchaseOrder->number, false], ['Tanggal Penerimaan', $goodsReceipt->receipt_date->format('d/m/Y'), false], ['Gudang Tujuan', $goodsReceipt->warehouse->name, false]] as [$lbl, $val, $av])
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
                <div style="font-size:12px; color:var(--ink-4);">{{ count($goodsReceipt->items) }} item ·
                    {{ $goodsReceipt->items->sum('received_quantity') }} unit diterima</div>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Produk</th>
                        <th style="text-align:right;">Quantity (Diharapkan)</th>
                        <th style="text-align:right;">Quantity (Diterima)</th>
                        <th style="text-align:right;">Quantity (Susut)</th>
                        <th>Satuan</th>
                        <th style="text-align:right;">Harga Beli</th>
                        <th style="text-align:right;">Diskon (%)</th>
                        <th style="text-align:right;">HPP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($goodsReceipt->items as $i => $it)
                        <tr>
                            <td class="mono" style="color:var(--ink-4);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $it->product->name }}</div>
                                <div class="mono" style="font-size:11px; color:var(--ink-4);">{{ $it->product->code }}
                                </div>
                            </td>
                            <td class="num" style="text-align:right;">{{ $it->expected_quantity }}</td>
                            <td class="num" style="text-align:right;">{{ $it->received_quantity }}</td>
                            <td class="num" style="text-align:right;">{{ $it->shrinkage_quantity }}</td>
                            <td style="color:var(--ink-3);">{{ $it->product->unit->symbol }}</td>
                            <td class="num" style="text-align:right;">{{ fmt_rp($it->unit_price) }}</td>
                            <td class="num" style="text-align:right;">{{ $it->discount_percentage }}</td>
                            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($it->unit_cost) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="order-items-split" style="grid-template-columns:1fr 320px;">
                <div class="order-notes">
                    <div class="label">Catatan Penerimaan</div>
                    <div class="order-notes__text">{{ $goodsReceipt->note }}</div>
                </div>
                <div class="order-detail-summary">
                    @foreach ([['Diskon', -$goodsReceipt->discount_amount, false], ['Ongkos Kirim', $goodsReceipt->transport_cost, false], ['Biaya Lain-Lain', $goodsReceipt->other_cost, false]] as [$lbl, $val, $bold])
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
                        title: 'Apakah Anda yakin ingin membatalkan Penerimaan Barang ini?',
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
                                    'purchasings.goods_receipts.cancel', id));
                                Swal.close();
                                Toast.fire({
                                    icon: 'success',
                                    title: response.data.message
                                });

                                window.location.href = route('purchasings.goods_receipts.index');
                            } catch (error) {
                                Swal.close();
                                let message =
                                    'Terjadi kesalahan saat membatalkan Penerimaan Barang. Silakan coba lagi.';
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
