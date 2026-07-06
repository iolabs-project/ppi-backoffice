@push('item-table-scripts')
    <script>
        window.deliveryOrderTable = {

            availableSOItems() {
                return []
            },

            addProduct() {
                this.formData.details.push({
                    product_id: null,
                    name: null,
                    code: null,
                    unit: null,
                    quantity_ordered: 0,
                    quantity_delivered: 0,
                    quantity_to_deliver: 0
                });
            },

            selectProduct(item, product) {

                item.product_id = product.id;

                item.name = product.name;

                this.recalculate();
            },

            deleteProduct(index) {

                this.formData.details.splice(index, 1);

                this.recalculate();
            }

        }
    </script>
@endpush

<table class="tbl">
    <thead>
        <tr>
            <th style="width:48px;">#</th>
            <th>Produk</th>
            <th style="width:50px;">Satuan</th>
            <th style="width:140px; text-align:right;">Quantity (Dipesan)</th>
            <th style="width:140px; text-align:right;">Quantity (Terkirim)</th>
            <th style="width:140px; text-align:right;">Quantity (Perlu Dikirim)</th>
            <th style="width:40px;"></th>
        </tr>
    </thead>
    <tbody>
        <template class="" x-for="(item, index) in formData.details" :key="index">
            <tr x-data="{ open: false }">
                <td class="mono" style="color:var(--ink-4);" x-text="String(index + 1).padStart(2, '0')"></td>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div class="product-icon">
                            <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                        </div>
                        <div style="flex:1;" class="dropdown-wrap" @click.outside="open=false">
                            <div class="input dropdown-trigger" style="height:32px; padding:0 10px;"
                                @click="open=!open">
                                <span style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                    :style="item.product_id ? '' : 'color:var(--ink-4);'"
                                    x-text="item.product_id ? item.name : 'Pilih Produk'"></span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                    stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round"
                                    stroke-linejoin="round" style="flex-shrink:0;">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </div>
                            <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                x-text="item.code || '— belum dipilih'"></div>
                            <div class="dropdown-menu" x-show="open" x-cloak style="min-width:320px;">
                                <template x-for="p in availableSOItems()" :key="p.id">
                                    <div class="dropdown-item" @click="selectProduct(item, p);open=false">
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-size:13px;" x-text="p.product_name"></div>
                                            <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                x-text="p.product_code"></div>
                                        </div>
                                        <span class="dropdown-item__sub" x-text="p.unit"></span>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--ink-3);" x-text="item.unit"></td>
                <td style="text-align: right"><span class="mono" style="font-weight:600"
                        x-text="0"></span>
                </td>
                <td style="text-align: right"><span class="mono" style="font-weight:600"
                        x-text="0"></span>
                </td>
                <td style="text-align: right"><span class="mono" style="font-weight:600"
                        x-text="0"></span>
                </td>
                <td>
                    <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                        :disabled="formData.details.length <= 1"
                        :style="formData.details.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                        @click="deleteProduct(index)">
                        <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                    </button>
                </td>
            </tr>

        </template>
    </tbody>
</table>
