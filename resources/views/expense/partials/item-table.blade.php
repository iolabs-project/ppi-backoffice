{{-- Expense item table.
     Expects the enclosing Alpine scope to expose:
       - formData.items: [{ account_id, description, amount }]
       - addItem() / removeItem(index)
       - handleItemInput() -- called on every change to trigger recalculation
     Blade params:
       - $accounts: collection of {id, code, name}
--}}
<script>
    var accounts = @json($accounts);
</script>
<table class="tbl">
    <thead>
        <tr>
            <th style="width:48px;">#</th>
            <th style="min-width:220px;">Akun</th>
            <th>Deskripsi</th>
            <th style="width:160px; text-align:right;">Jumlah</th>
            <th style="width:40px;"></th>
        </tr>
    </thead>
    <tbody>
        <template x-for="(item, i) in formData.items" :key="i">
            <tr>
                <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
                <td>
                    <x-misc.select
                        display="item.account_id ? (accounts.find(a => a.id === item.account_id)?.code + ' - ' + accounts.find(a => a.id === item.account_id)?.name) : 'Pilih akun'"
                        hasValue="item.account_id" placeholder="Cari akun..." min-width="260px" height="32px">
                        <template
                            x-for="a in accounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || (a.code || '').toLowerCase().includes(q.toLowerCase()))"
                            :key="a.id">
                            <div class="dropdown-item" @click="item.account_id=a.id; handleItemInput(); open=false; q=''">
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
                    <input class="input" style="height:32px;" placeholder="Deskripsi singkat..."
                        x-model="item.description" @input="handleItemInput()" />
                </td>
                <td>
                    <input class="input num" style="height:32px; text-align:right;" x-model="item.amount"
                        x-mask:dynamic="$money($input, '.',',')" @input="handleItemInput()" />
                </td>
                <td>
                    <button type="button" class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                        @click="removeItem(i)">
                        <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                    </button>
                </td>
            </tr>
        </template>
        <template x-if="formData.items.length === 0">
            <tr>
                <td colspan="5" style="text-align:center; color:var(--ink-4); padding:16px 0;">
                    Belum ada item biaya.
                </td>
            </tr>
        </template>
    </tbody>
</table>
