 <script>
     window.productTable = {
         addProduct() {
             this.formData.details.push({
                 product_id: null,
                 name: null,
                 code: null,
                 quantity: null,
                 unit: null,
                 unit_price: null,
                 subtotal: null,
                 discount_percentage: null,
                 discount_amount: null,
                 total_amount: null,
                 unit_cost: null,
             });
         },
         deleteProduct(index) {
             this.formData.details.splice(index, 1);
             this.recalculate();
         },
         selectProduct(item, product) {
             if (this.formData.details.some(d => d.product_id === product.id)) {
                 Toast.fire({
                     icon: 'error',
                     title: 'Produk sudah terpilih sebelumnya'
                 });

                 return;
             }
             item.name = product.name;
             item.product_id = product.id;
             item.code = product.code;
             item.unit = product.unit.symbol;
         },
         calculateDetailTotal(index) {
             const d = this.formData.details[index];
             d.subtotal = this.n(d.quantity) * this.n(d.unit_price);
             d.total_amount = d.subtotal - this.n(d.discount_amount);
             this.recalculate();
         },
         handleDetailDiscountPercentageInput(index) {
             const d = this.formData.details[index];
             d.discount_percentage = Math.min(100, Math.max(0, this.n(d.discount_percentage)));
             d.discount_amount = Math.round((d.discount_percentage / 100) * (this.n(d.quantity) * this.n(d
                 .unit_price)));
             this.calculateDetailTotal(index);
         },
     }
 </script>
 <table class="tbl">
     <thead>
         <tr>
             <th style="width:48px;">#</th>
             <th>Pilih Produk</th>
             <th style="width:120px; text-align:right;">Qty</th>
             <th style="width:140px;">Satuan</th>
             <th style="width:160px; text-align:right;">Harga</th>
             <th style="width:100px; text-align:right;">Diskon (%)</th>
             <th style="width:160px; text-align:right;">Subtotal</th>
             <th style="width:160px; text-align:right;">Est. HPP</th>
             <th style="width:40px;"></th>
         </tr>
     </thead>
     <tbody>
         <template x-for="(it, i) in formData.details" :key="i">
             <tr x-data="{ open: false }">
                 <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
                 <td>
                     <div style="display:flex; align-items:center; gap:10px;">
                         <div class="product-icon">
                             <x-misc.icon name="box" :size="16" stroke="var(--ink-3)" />
                         </div>
                         <div style="flex:1;" class="dropdown-wrap" @click.outside="open=false">
                             <div class="input dropdown-trigger" style="height:32px; padding:0 10px;"
                                 @click="open=!open">
                                 <span style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                                     :style="it.product_id ? '' : 'color:var(--ink-4);'"
                                     x-text="it.product_id ? it.name : 'Pilih Produk'"></span>
                                 <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                     stroke="var(--ink-4)" stroke-width="1.6" stroke-linecap="round"
                                     stroke-linejoin="round" style="flex-shrink:0;">
                                     <path d="m6 9 6 6 6-6" />
                                 </svg>
                             </div>
                             <div class="mono" style="font-size:11px; color:var(--ink-4); margin-top:3px;"
                                 x-text="it.code || '— belum dipilih'"></div>
                             <div class="dropdown-menu" x-show="open" x-cloak style="min-width:320px;">
                                 <template x-for="p in availableProducts()" :key="p.id">
                                     <div class="dropdown-item" @click="selectProduct(it, p);open=false">
                                         <div style="flex:1; min-width:0;">
                                             <div style="font-size:13px;" x-text="p.name"></div>
                                             <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                                 x-text="p.code"></div>
                                         </div>
                                         <span class="dropdown-item__sub" x-text="p.unit.symbol"></span>
                                     </div>
                                 </template>
                             </div>
                         </div>
                     </div>
                 </td>
                 <td>
                     <input class="input num" style="height:32px; text-align:right;" x-model="it.quantity"
                         @input="calculateDetailTotal(i)" x-mask:dynamic="$money($input, ',')" />
                 </td>
                 <td>
                     <div class="input input--readonly"
                         style="height:32px; display:flex; align-items:center; padding:0 10px; color:var(--ink-3);">
                         <span x-text="it.unit || '—'"></span>
                     </div>
                 </td>
                 <td>
                     <input class="input num" style="height:32px; text-align:right;" x-model="it.unit_price"
                         @input="calculateDetailTotal(i)" x-mask:dynamic="$money($input, ',')" />

                     <template x-if="it.subtotal !== null && it.subtotal !== undefined">
                         <div class="order-items__sub mono"
                             style="font-size:11px; color:var(--ink-4); margin-top:2px; text-align: right;"
                             x-text="NumberUtils.formatNumericIntoMask(it.subtotal)">
                         </div>
                     </template>
                 </td>
                 <td>
                     <input class="input num" style="height:32px; text-align:right;" x-model="it.discount_percentage"
                         @input="handleDetailDiscountPercentageInput(i)" x-mask:dynamic="$money($input, ',')" />

                     <template x-if="it.discount_amount !== null && it.discount_amount !== undefined">
                         <div class="order-items__sub mono"
                             style="font-size:11px; color:var(--ink-4); margin-top:2px; text-align: right;"
                             x-text="NumberUtils.formatNumericIntoMask(it.discount_amount)">
                         </div>
                     </template>
                 </td>
                 <td>
                     <input class="input num" style="height:32px; text-align:right;" x-model.number="it.total_amount"
                         x-mask:dynamic="$money($input, ',')" disabled />
                 </td>
                 <td>
                     <input class="input num" style="height:32px; text-align:right;" x-model.number="it.unit_cost"
                         x-mask:dynamic="$money($input, ',')" disabled />
                 </td>
                 <td>
                     <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                         :disabled="formData.details.length <= 1"
                         :style="formData.details.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''"
                         @click="deleteProduct(i)">
                         <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                     </button>
                 </td>
         </template>
     </tbody>
 </table>
