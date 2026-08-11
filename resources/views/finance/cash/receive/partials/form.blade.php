{{-- Header Form --}}
<div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Transaksi</div>
    <div class="order-form-grid-3">

        {{-- Dari --}}
        <x-misc.field label="Dari" :required="true">
            <x-misc.select display="contactSelected ? contactSelected.name : 'Pilih kontak'" hasValue="contactSelected"
                placeholder="Cari kontak...">
                <template x-for="k in contacts.filter(k => !q || k.name.toLowerCase().includes(q.toLowerCase()))"
                    :key="k.id">
                    <div class="dropdown-item" @click="contactSelected = k; formData.contact_id = k.id; open = false; q = ''">
                        <div class="avatar" style="width:28px;height:28px;background:var(--bg-3);color:var(--ink-2);"
                            x-text="initials(k.name)"></div>
                        <div>
                            <div style="font-weight:500;" x-text="k.name"></div>
                            <div style="font-size:11px; color:var(--ink-4);" x-text="k.type"></div>
                        </div>
                    </div>
                </template>
                <template x-if="!contacts.some(k => !q || k.name.toLowerCase().includes(q.toLowerCase()))">
                    <div class="dropdown-empty">Tidak ditemukan</div>
                </template>
            </x-misc.select>
        </x-misc.field>

        {{-- Nomor --}}
        <x-misc.field label="Nomor" :required="true">
            <div class="input mono input--readonly" style="display:flex; align-items:center;">
                <span style="flex:1; font-weight:600;" x-text="formData.number"></span>
                <span class="auto-tag">Auto</span>
            </div>
        </x-misc.field>

        {{-- Tanggal Transaksi --}}
        <x-misc.field label="Tanggal Transaksi" :required="true">
            <input type="date" class="input" x-model="formData.transaction_date" />
        </x-misc.field>

    </div>
</div>

{{-- Items --}}
<div class="card" style="overflow:visible;">
    <div class="card-hd">
        <div class="display card-hd-title">Rincian</div>
        <button class="btn btn-ghost btn-sm" @click="addRow()">
            <x-misc.icon name="plus" :size="13" />Tambah Baris
        </button>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>Akun</th>
                <th>Deskripsi</th>
                <th style="text-align:right; width:180px;">Total</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, i) in formData.items" :key="i">
                <tr>
                    <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
                    <td>
                        <x-misc.select
                            display="row.account_id ? (accounts.find(a => a.id === row.account_id)?.code + ' - ' + accounts.find(a => a.id === row.account_id)?.name) : 'Pilih akun'"
                            hasValue="row.account_id" placeholder="Cari akun..." min-width="260px" height="32px">
                            <template
                                x-for="a in accounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || (a.code || '').toLowerCase().includes(q.toLowerCase()))"
                                :key="a.id">
                                <div class="dropdown-item" @click="row.account_id=a.id; calculate(); open=false; q=''">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:13px;" x-text="a.name"></div>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="a.code">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template
                                x-if="!accounts.some(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || (a.code || '').toLowerCase().includes(q.toLowerCase()))">
                                <div class="dropdown-empty">Tidak ditemukan</div>
                            </template>
                        </x-misc.select>
                    </td>
                    <td style="padding:6px 10px;">
                        <input class="input" style="height:32px; width:100%;" x-model="row.description" />
                    </td>
                    <td style="padding:6px 10px;">
                        <input class="input num" style="height:32px; text-align:right;" x-model="row.amount"
                            x-mask:dynamic="$money($input, '.',',')" @input="calculate()" />
                    </td>
                    <td style="padding:15px; text-align:center;">
                        <button @click="removeRow(i)" class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                            :disabled="formData.items.length <= 1"
                            :style="formData.items.length <= 1 ? 'opacity:0.25; cursor:not-allowed;' : ''">
                            <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

<div class="card" style="overflow:visible;">
    <div class="order-items-split">
        <div class="order-extras">
            {{-- <x-misc.field label="Catatan Internal">
                        <textarea class="input" rows="2" placeholder="Tulis catatan untuk tim gudang/pengiriman…"
                            x-model="formData.note"></textarea>
                    </x-misc.field> --}}
        </div>
        <div class="order-summary">
            <div class="display order-summary__title">Ringkasan</div>

            <div class="order-summary__grid">

                <div class="order-summary__group">
                    <div class="order-summary__row">
                        <span class="order-summary__label"></span>
                        <span class="num order-summary__val"
                            x-text="(formData.subtotal ? m(formData.subtotal) : '0')"></span>
                    </div>
                    <div class="order-summary__row">
                        <span class="order-summary__label">Pajak</span>
                        <div class="order-summary__pct-group">
                            <input class="input num order-summary__pct-input" x-model="formData.tax_percentage"
                                x-mask:dynamic="$money($input, '.',',')" @input="calculate()" />
                            <span class="order-summary__pct-sym">%</span>
                            <input class="input num input--readonly order-summary__amount-display"
                                x-model="formData.tax_amount" x-mask:dynamic="$money($input, '.',',')" disabled />
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-summary__total">
                <span class="order-summary__total-label">Total</span>
                <span class="order-summary__total-value display num"
                    x-text="'Rp ' + (formData.total_amount ? m(formData.total_amount) : '0')"></span>
            </div>
        </div>
    </div>
</div>
