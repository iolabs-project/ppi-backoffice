<script>
    window.deliveryOrderTable = {

        availableSOItems() {
            return remainingSOItems.filter(s => s.remaining_quantity > 0);
        },

        addProduct() {
            this.formData.details.push({
                sales_order_item_id: null,
                product_id: null,
                code: null,
                name: null,
                unit: null,
                quantity_ordered: 0,
                quantity_previously_delivered: 0,
                remaining_quantity: 0,
                batches: [],
            });
        },

        selectProduct(item, soItem) {
            if (this.formData.details.some(d => d.sales_order_item_id === soItem.id)) {
                Toast.fire({
                    icon: 'error',
                    title: 'Produk tersebut sudah dipilih sebelumnya'
                });
                return;
            }

            item.sales_order_item_id = soItem.id;
            item.product_id = soItem.product_id;
            item.code = soItem.product_code;
            item.name = soItem.product_name;
            item.unit = soItem.unit;
            item.quantity_ordered = soItem.quantity;
            item.quantity_previously_delivered = soItem.shipped_quantity;
            item.remaining_quantity = soItem.remaining_quantity;
            item.batches = [];
        },

        deleteProduct(index) {
            this.formData.details.splice(index, 1);
        },

        batchTotal(item) {
            return item.batches.reduce((sum, b) => sum + this.n(b.quantity), 0);
        },

        allocatedElsewhere(productBatchId, excludeItem) {
            return this.formData.details
                .filter(d => d !== excludeItem)
                .reduce((sum, d) => sum + d.batches
                    .filter(b => b.product_batch_id === productBatchId)
                    .reduce((s, b) => s + this.n(b.quantity), 0), 0);
        },

        availableBatchesForItem(item) {
            if (!item || !item.product_id) {
                return [];
            }

            return availableBatches
                .filter(b => b.product_id === item.product_id)
                .map(b => {
                    const usedElsewhere = this.allocatedElsewhere(b.id, item);
                    const alreadyInItem = item.batches
                        .filter(ib => ib.product_batch_id === b.id)
                        .reduce((s, ib) => s + this.n(ib.quantity), 0);
                    return {
                        ...b,
                        remaining_available: Math.max(0, b.available_quantity - usedElsewhere - alreadyInItem),
                    };
                });
        },

        activeItem() {
            return this.activeItemIndex !== null ? this.formData.details[this.activeItemIndex] : null;
        },

        remainingToShip(item) {
            return Math.max(0, this.n(item.remaining_quantity) - this.batchTotal(item));
        },

        addBatchToActiveItem() {
            const item = this.activeItem();
            const batch = this.availableBatchesForItem(item).find(b => b.id === this.batchForm.product_batch_id);
            const qty = this.n(this.batchForm.quantity);

            if (!batch) {
                Toast.fire({ icon: 'error', title: 'Silakan pilih batch terlebih dahulu.' });
                return;
            }
            if (qty <= 0) {
                Toast.fire({ icon: 'error', title: 'Quantity harus lebih dari 0.' });
                return;
            }
            if (qty > batch.remaining_available) {
                Toast.fire({ icon: 'error', title: `Quantity melebihi stok tersedia pada batch tersebut (${batch.remaining_available}).` });
                return;
            }
            if (qty > this.remainingToShip(item)) {
                Toast.fire({ icon: 'error', title: `Quantity melebihi sisa yang perlu dikirim untuk produk ini (${this.remainingToShip(item)}).` });
                return;
            }

            const existing = item.batches.find(b => b.product_batch_id === batch.id);
            if (existing) {
                existing.quantity = this.n(existing.quantity) + qty;
            } else {
                item.batches.push({
                    product_batch_id: batch.id,
                    batch_number: batch.batch_number,
                    quantity: qty,
                    unit_cost: batch.unit_cost,
                });
            }

            this.closeBatchModal();
        },

        removeBatch(item, index) {
            item.batches.splice(index, 1);
        },
    }
</script>

<table class="tbl">
    <thead>
        <tr>
            <th style="width:48px;">#</th>
            <th>Produk</th>
            <th style="width:70px;">Satuan</th>
            <th style="width:120px; text-align:right;">Qty Dipesan</th>
            <th style="width:120px; text-align:right;">Qty Terkirim</th>
            <th style="width:130px; text-align:right;">Qty Dikirim</th>
            <th style="width:40px;"></th>
        </tr>
    </thead>
    <template x-for="(item, index) in formData.details" :key="index">
        <tbody>
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
                <td style="color:var(--ink-3);" x-text="item.unit || '—'"></td>
                <td style="text-align: right"><span class="mono" style="font-weight:600"
                        x-text="item.quantity_ordered"></span>
                </td>
                <td style="text-align: right"><span class="mono" style="font-weight:600"
                        x-text="item.quantity_previously_delivered"></span>
                </td>
                <td style="text-align:right;">
                    <span class="mono" style="font-weight:600;" x-text="batchTotal(item)"></span>
                    <div style="font-size:11px; color:var(--ink-4);" x-show="item.product_id"
                        x-text="'maks ' + item.remaining_quantity"></div>
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
            <tr x-show="item.product_id">
                <td></td>
                <td colspan="6">
                    <div class="batch-panel">
                        <div class="batch-panel__hd">
                            <span>Batch Terpilih</span>
                            <span class="mono" :class="batchTotal(item) > n(item.remaining_quantity) ? 'batch-panel__total--warn' : 'batch-panel__total--ok'"
                                x-text="'Qty Dikirim: ' + batchTotal(item) + ' / maks ' + item.remaining_quantity + ' ' + (item.unit || '')"></span>
                        </div>
                        <template x-if="item.batches.length === 0">
                            <div class="batch-panel__empty">Belum ada batch dipilih.</div>
                        </template>
                        <table class="tbl tbl-tight" style="table-layout:fixed;" x-show="item.batches.length > 0">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th style="width:200px;">Batch No.</th>
                                    <th style="width:110px; text-align:right;">Qty Dikirim</th>
                                    <th style="width:120px; text-align:right;">Unit Cost</th>
                                    <th style="width:130px; text-align:right;">Amount</th>
                                    <th style="width:36px;"></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(b, bIndex) in item.batches" :key="b.product_batch_id">
                                    <tr>
                                        <td class="mono" style="color:var(--ink-4);" x-text="String(bIndex + 1).padStart(2, '0')"></td>
                                        <td class="mono" x-text="b.batch_number"></td>
                                        <td class="num" style="text-align:right;" x-text="n(b.quantity)"></td>
                                        <td class="num" style="text-align:right;" x-text="NumberUtils.formatNumericIntoMask(n(b.unit_cost))"></td>
                                        <td class="num" style="text-align:right;" x-text="NumberUtils.formatNumericIntoMask(n(b.quantity) * n(b.unit_cost))"></td>
                                        <td>
                                            <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                                @click="removeBatch(item, bIndex)">
                                                <x-misc.icon name="trash" :size="13" stroke="var(--ink-4)" />
                                            </button>
                                        </td>
                                        <td></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <button class="btn btn-ghost btn-sm" style="margin-top:8px;" @click="openBatchModal(index)">
                            <x-misc.icon name="plus" :size="13" />Tambah Batch
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </template>
</table>

<x-misc.modal title="Pilih Batch" show="modal === 'add_batch'" close-handler="closeBatchModal()" :width="560">
    <template x-if="activeItem()">
        <div>
            <div style="font-size:13px; color:var(--ink-3); margin-bottom:12px;">
                Produk: <strong style="color:var(--ink);" x-text="activeItem().name"></strong> ·
                Sudah Dipilih: <span class="mono" x-text="batchTotal(activeItem()) + ' ' + (activeItem().unit || '')"></span> ·
                Sisa yang Perlu Dikirim: <span class="mono" x-text="remainingToShip(activeItem()) + ' ' + (activeItem().unit || '')"></span>
            </div>

            <table class="tbl tbl-tight" style="table-layout:fixed;">
                <thead>
                    <tr>
                        <th style="width:32px;"></th>
                        <th style="width:auto;">Batch No.</th>
                        <th style="width:110px; text-align:right;">Tersedia</th>
                        <th style="width:130px; text-align:right;">Unit Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="b in availableBatchesForItem(activeItem())" :key="b.id">
                        <tr class="row-tap" @click="batchForm.product_batch_id = b.id; batchForm.quantity = Math.min(b.remaining_available, remainingToShip(activeItem()))">
                            <td><input type="radio" :checked="batchForm.product_batch_id === b.id" /></td>
                            <td class="mono" x-text="b.batch_number"></td>
                            <td class="num" style="text-align:right;" x-text="b.remaining_available"></td>
                            <td class="num" style="text-align:right;" x-text="NumberUtils.formatNumericIntoMask(b.unit_cost)"></td>
                        </tr>
                    </template>
                    <template x-if="availableBatchesForItem(activeItem()).length === 0">
                        <tr>
                            <td colspan="4" style="text-align:center; color:var(--ink-4); padding:16px;">
                                Tidak ada batch tersedia untuk produk ini di gudang tersebut.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div style="margin-top:14px;">
                <x-misc.field label="Quantity yang Dikirim">
                    <input class="input num" style="text-align:right;" x-model="batchForm.quantity"
                        x-mask:dynamic="$money($input, ',')" />
                </x-misc.field>
            </div>
        </div>
    </template>
    <x-slot:footer>
        <button class="btn btn-ghost" @click="closeBatchModal()">
            <x-misc.icon name="x" :size="14" /> Batal
        </button>
        <button class="btn btn-primary" @click="addBatchToActiveItem()">
            <x-misc.icon name="check" :size="14" /> Tambahkan
        </button>
    </x-slot:footer>
</x-misc.modal>
