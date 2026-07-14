{{-- Shared Additional Costs (Internal Only) table.
     Expects the enclosing Alpine scope to expose:
       - formData.costs: [{ account_id, description, amount, is_inventory_related }]
       - addCost() / removeCost(index)
       - handleCostInput() -- called on every change to trigger recalculation
     Blade params:
       - $accounts: collection of {id, code, name}
--}}
<div class="card" style="overflow:visible;">
    <div class="card-hd">
        <div class="display card-hd-title">Biaya Tambahan (Internal Only)</div>
        <button type="button" class="btn btn-ghost btn-sm" @click="addCost()">
            <x-misc.icon name="plus" :size="13" />Tambah Biaya
        </button>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>Deskripsi</th>
                <th style="min-width:200px;">Akun (Beban)</th>
                <th style="width:120px; text-align:center;">Terkait Inventory</th>
                <th style="width:160px; text-align:right;">Jumlah</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(cost, i) in formData.costs" :key="i">
                <tr>
                    <td class="mono" style="color:var(--ink-4);" x-text="String(i+1).padStart(2,'0')"></td>
                    <td>
                        <input class="input" style="height:32px;" placeholder="Deskripsi singkat..."
                            x-model="cost.description" @input="handleCostInput()" />
                    </td>
                    <td>
                        <select class="input" style="height:32px;" x-model.number="cost.account_id"
                            @change="handleCostInput()">
                            <option value="">Pilih akun</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" x-model="cost.is_inventory_related" @change="handleCostInput()" />
                    </td>
                    <td>
                        <input class="input num" style="height:32px; text-align:right;" x-model="cost.amount"
                            x-mask:dynamic="$money($input, ',')" @input="handleCostInput()" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                            @click="removeCost(i)">
                            <x-misc.icon name="trash" :size="14" stroke="var(--ink-4)" />
                        </button>
                    </td>
                </tr>
            </template>
            <template x-if="formData.costs.length === 0">
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--ink-4); padding:16px 0;">
                        Belum ada biaya internal.
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
    <div style="padding:12px 16px; font-size:12px; color:var(--ink-3); line-height:1.7;">
        Biaya di tabel ini tidak ditagihkan ke customer — hanya digunakan untuk perhitungan estimasi profit.
    </div>
</div>
