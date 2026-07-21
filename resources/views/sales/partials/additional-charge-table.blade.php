{{-- Shared Additional Charges (Bill Customer) table.
     Expects the enclosing Alpine scope to expose:
       - formData.charges: [{ account_id, description, amount }]
       - addCharge() / removeCharge(index)
       - handleChargeInput() -- called on every change to trigger recalculation
     Blade params:
       - $accounts: collection of {id, code, name}
--}}
<script>
    var accounts = @json($accounts);
</script>
<div class="card" style="overflow:visible;">
    <div class="card-hd">
        <div class="display card-hd-title">Biaya Tambahan (Tagih ke Customer)</div>
        <button type="button" class="btn btn-ghost btn-sm" @click="addCharge()">
            <x-misc.icon name="plus" :size="13" />Tambah Biaya
        </button>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>Deskripsi</th>
                <th style="min-width:200px;">Akun (Pendapatan)</th>
                <th style="width:160px; text-align:right;">Jumlah</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(charge, i) in formData.charges" :key="i">
                <tr>
                    <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
                    <td>
                        <input class="input" style="height:32px;" placeholder="Deskripsi singkat..."
                            x-model="charge.description" @input="handleChargeInput()" />
                    </td>
                    <td>
                        <x-misc.select
                            display="charge.account_id ? (accounts.find(a => a.id === charge.account_id)?.code + ' - ' + accounts.find(a => a.id === charge.account_id)?.name) : 'Pilih akun'"
                            hasValue="charge.account_id" placeholder="Cari akun..." min-width="260px" height="32px">
                            <template
                                x-for="a in accounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || (a.code || '').toLowerCase().includes(q.toLowerCase()))"
                                :key="a.id">
                                <div class="dropdown-item" @click="charge.account_id=a.id; handleChargeInput(); open=false; q=''">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:13px;" x-text="a.name"></div>
                                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="a.code"></div>
                                    </div>
                                </div>
                            </template>
                            <template
                                x-if="!accounts.some(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || (a.code || '').toLowerCase().includes(q.toLowerCase()))">
                                <div class="dropdown-empty">Tidak ditemukan</div>
                            </template>
                        </x-misc.select>
                    </td>
                    <td>
                        <input class="input num" style="height:32px; text-align:right;" x-model="charge.amount"
                            x-mask:dynamic="$money($input, ',')" @input="handleChargeInput()" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                            @click="removeCharge(i)">
                            <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                        </button>
                    </td>
                </tr>
            </template>
            <template x-if="formData.charges.length === 0">
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--ink-4); padding:16px 0;">
                        Belum ada biaya tambahan ke customer.
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
    <div style="padding:12px 16px; font-size:12px; color:var(--ink-3); line-height:1.7;">
        Biaya di tabel ini akan ikut muncul pada Sales Invoice dan ditagihkan ke customer.
    </div>
</div>
