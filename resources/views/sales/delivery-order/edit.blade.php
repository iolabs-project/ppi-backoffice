@extends('layouts.app')
@section('content')
    <script>
        const deliveryOrder = @json($deliveryOrder);
        const remainingSOItems = @json($remainingSOItems);

        function deliveryOrderForm() {
            return {
                formData: {
                    id: deliveryOrder.id || null,
                    reference_number: deliveryOrder.reference_number || null,
                    delivery_date: deliveryOrder.delivery_date || null,
                    note: deliveryOrder.note || null,
                    // details: (deliveryOrder.items || []).map(item => ({
                    //     id: item.id,
                    //     purchase_order_item_id: item.purchase_order_item_id,
                    //     product_id: item.product_id,
                    //     code: item.product.code,
                    //     name: item.product.name,
                    //     unit: item.product.unit.symbol,
                    //     batch_number: item.batch_number,
                    //     remaining_quantity: item.purchase_order_item.quantity - item.purchase_order_item
                    //         .received_quantity,
                    //     expected_quantity: item.expected_quantity,
                    //     received_quantity: item.received_quantity,
                    //     shrinkage_quantity: item.shrinkage_quantity,
                    //     unit_price: item.unit_price,
                    //     subtotal: item.subtotal,
                    //     discount_percentage: item.discount_percentage,
                    //     discount_amount: item.discount_amount,
                    //     unit_cost: item.unit_cost,
                    //     total_cost: item.total_cost,
                    // })),
                    details: []
                },

                n(v) {
                    return NumberUtils.parseMaskIntoNumeric(v);
                },


                // availablePOItems() {
                //     const selectedIds = this.formData.details
                //         .filter(d => d.purchase_order_item_id)
                //         .map(d => d.purchase_order_item_id);

                //     return remainingPOItems.filter(item =>
                //         item.remaining_quantity > 0 &&
                //         !selectedIds.includes(item.id)
                //     );
                // },
                availableSOItems() {
                    return []
                },
                init() {
                    Swal.fire({
                        title: 'Memuat data penerimaan barang...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    // this.recalc();
                    Object.assign(this, window.deliveryOrderTable);
                    Swal.close();
                },

                async submitDraft() {
                    // let body = {
                    //     ...this.formData,
                    //     status: 'draft',
                    // };
                    // body.discount_amount = NumberUtils.parseMaskIntoNumeric(body.discount_amount);
                    // body.transport_cost = NumberUtils.parseMaskIntoNumeric(body.transport_cost);
                    // body.other_cost = NumberUtils.parseMaskIntoNumeric(body.other_cost);
                    // body.details = body.details.map(d => ({
                    //     ...d,
                    //     expected_quantity: NumberUtils.parseMaskIntoNumeric(d.expected_quantity),
                    //     received_quantity: NumberUtils.parseMaskIntoNumeric(d.received_quantity),
                    //     shrinkage_quantity: NumberUtils.parseMaskIntoNumeric(d.shrinkage_quantity),
                    //     unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                    //     unit_cost: NumberUtils.parseMaskIntoNumeric(d.unit_cost),
                    // }));

                    // Swal.fire({
                    //     title: 'Memproses penyimpanan draft Penerimaan Barang...',
                    //     allowOutsideClick: false,
                    //     didOpen: () => {
                    //         Swal.showLoading();
                    //     }
                    // });

                    // try {
                    //     const response = await axios.put(
                    //         route('purchasings.goods_receipts.update', this.formData.id), body
                    //     );
                    //     console.log('response', response.data.message);

                    //     Swal.close();
                    //     Toast.fire({
                    //         icon: 'success',
                    //         title: response.data.message
                    //     });

                    //     window.location.href = response.data.redirect;
                    // } catch (error) {
                    //     Swal.close();
                    //     let message = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';

                    //     if (error.response?.status === 422) {
                    //         message = Object.values(error.response.data.errors)
                    //             .flat()
                    //             .join(', ');
                    //     } else if (error.response?.data?.message) {
                    //         message = error.response.data.message;
                    //     }

                    //     Toast.fire({
                    //         icon: 'error',
                    //         title: message
                    //     });

                    // }
                },

                async submitFinish() {
                    // Swal.fire({
                    //     title: 'Apakah Anda yakin ingin menyelesaikan penerimaan barang ini?',
                    //     text: 'Setelah diselesaikan, penerimaan barang akan membentuk jurnal umum dan menambah stok di gudang tujuan.',
                    //     icon: 'warning',
                    //     showCancelButton: true,
                    //     confirmButtonText: 'Ya, selesaikan',
                    //     cancelButtonText: 'Batal',
                    //     reverseButtons: true,
                    // }).then(async (result) => {
                    //     if (result.isConfirmed) {
                    //         let body = {
                    //             ...this.formData,
                    //             status: 'finished',
                    //         };
                    //         body.discount_amount = NumberUtils.parseMaskIntoNumeric(body.discount_amount);
                    //         body.transport_cost = NumberUtils.parseMaskIntoNumeric(body.transport_cost);
                    //         body.other_cost = NumberUtils.parseMaskIntoNumeric(body.other_cost);
                    //         body.details = body.details.map(d => ({
                    //             ...d,
                    //             expected_quantity: NumberUtils.parseMaskIntoNumeric(d
                    //                 .expected_quantity),
                    //             received_quantity: NumberUtils.parseMaskIntoNumeric(d
                    //                 .received_quantity),
                    //             shrinkage_quantity: NumberUtils.parseMaskIntoNumeric(d
                    //                 .shrinkage_quantity),
                    //             unit_price: NumberUtils.parseMaskIntoNumeric(d.unit_price),
                    //             unit_cost: NumberUtils.parseMaskIntoNumeric(d.unit_cost),
                    //             discount_percentage: NumberUtils.parseMaskIntoNumeric(d
                    //                 .discount_percentage),
                    //         }));

                    //         Swal.fire({
                    //             title: 'Memproses penyimpanan Penerimaan Barang...',
                    //             allowOutsideClick: false,
                    //             didOpen: () => {
                    //                 Swal.showLoading();
                    //             }
                    //         });

                    //         try {
                    //             const response = await axios.put(
                    //                 route('purchasings.goods_receipts.update', this.formData.id), body
                    //             );

                    //             Swal.close();
                    //             Toast.fire({
                    //                 icon: 'success',
                    //                 title: response.data.message
                    //             });

                    //             window.location.href = response.data.redirect;
                    //         } catch (error) {
                    //             Swal.close();
                    //             let message = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';

                    //             if (error.response?.status === 422) {
                    //                 message = Object.values(error.response.data.errors)
                    //                     .flat()
                    //                     .join(', ');
                    //             } else if (error.response?.data?.message) {
                    //                 message = error.response.data.message;
                    //             }

                    //             Toast.fire({
                    //                 icon: 'error',
                    //                 title: message
                    //             });

                    //         }
                    //     }
                    // });
                }

            };
        }
    </script>

    <div x-data="deliveryOrderForm()" x-init="init()" class="order-page">
        <div>
            <a href="{{ route('sales.delivery_orders.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:10px;">
                <x-misc.icon name="chev-left" :size="13" />Kembali
            </a>
            <div class="order-title-row">
                <h1 class="order-title display">Edit Pengiriman Barang</h1>
                <x-misc.status-badge status="draft" />
            </div>
            <div class="order-sub">
                Pengiriman yang berhasil disimpan akan otomatis membentuk jurnal umum dan <strong
                    style="color:var(--ink);">mengurangi stok</strong> di gudang asal.
            </div>
        </div>

        <div class="card card-bd--form">
            <div class="shipping-form-info">
                <div class="display card-hd-title">Informasi Pengiriman</div>
                <div class="shipping-form-info__sub"><span style="color:var(--accent);">*</span> Field terisi otomatis dari
                    PO</div>
            </div>
            <div class="order-form-grid-3">
                <x-misc.field label="Customer" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:10px;">
                        <x-misc.avatar :name="$deliveryOrder->customer->name" />
                        <span style="flex:1; font-weight:500;">{{ $deliveryOrder->customer->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="No. Pemesanan" :required="true">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $deliveryOrder->salesOrder->number }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="Gudang Tujuan" :required="true">
                    <div class="input input--readonly" style="display:flex; align-items:center; gap:8px;">
                        <x-misc.icon name="building" :size="14" stroke="var(--ink-4)" />
                        <span style="flex:1;">{{ $deliveryOrder->warehouse->name }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="No. Pengiriman">
                    <div class="input mono input--readonly" style="display:flex; align-items:center;">
                        <span style="flex:1; font-weight:600;">{{ $deliveryOrder->number }}</span>
                        <span class="auto-tag">Auto</span>
                    </div>
                </x-misc.field>
                <x-misc.field label="Tanggal Pengiriman" :required="true">
                    <input type="date" class="input" x-model="formData.delivery_date" />
                </x-misc.field>
                <x-misc.field label="Nomor Referensi"><input class="input mono" placeholder="(opsional)"
                        x-model="formData.reference_number" /></x-misc.field>
            </div>
        </div>

        <div class="card" style="overflow:hidden;">
            <div class="card-hd">
                <div class="display card-hd-title">Daftar Produk</div>
                <button class="btn btn-ghost btn-sm" @click="addProduct()">
                    <x-misc.icon name="plus" :size="13" />Tambah Produk
                </button>
            </div>
            {{-- <x-sales.delivery-order.item-table/> --}}
            @include('sales.delivery-order.partials.edit.item-table')
            <div class="order-items-split">
                <div class="order-extras">
                    <x-misc.field label="Catatan Penerimaan">
                        <textarea class="input" rows="2"
                            placeholder="Catat kondisi barang, kekurangan, atau informasi penting lainnya..." x-model="formData.note"></textarea>
                    </x-misc.field>
                </div>  
            </div>
        </div>
        <div class="order-form-footer">
            <button class="btn btn-ghost" style="border-style:dashed;" @click="submitDraft()">Simpan Draft</button>
            <button class="btn btn-primary" @click="submitFinish()"><x-misc.icon name="check"
                    :size="14" />Selesaikan</button>
        </div>
    </div>
@endsection
