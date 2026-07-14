{{-- Shared Additional Cost (Landed Cost) table.
     Expects the enclosing Alpine scope to expose:
       - formData.costs: [{ account_id, description, amount, billed_by, is_inventory_cost }]
       - addCost() / removeCost(index)
       - handleCostInput() -- called on every change to trigger recalculation
     Blade params:
       - $accounts: collection of {id, code, name}
       - $billedByOptions: array of {id, name}, or null/omitted to hide the
         Billed By + Inventory Cost columns (used for Purchase Invoice, where
         every cost is inherently supplier-billed).
--}}
@php($showBilledBy = !empty($billedByOptions))
<div class="card" style="overflow:visible;">
    <div class="card-hd">
        <div class="display card-hd-title">Biaya Tambahan (Landed Cost)</div>
        <button type="button" class="btn btn-ghost btn-sm" @click="addCost()">
            <x-misc.icon name="plus" :size="13" />Tambah Biaya
        </button>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:48px;">#</th>
                <th>Deskripsi</th>
                <th style="min-width:200px;">Akun</th>
                @if ($showBilledBy)
                    <th style="width:150px;">Ditagih Oleh</th>
                    <th style="width:110px; text-align:center;">Biaya Inventory</th>
                @endif
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
                    @if ($showBilledBy)
                        <td>
                            <select class="input" style="height:32px;" x-model="cost.billed_by"
                                @change="handleCostInput()">
                                @foreach ($billedByOptions as $option)
                                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <input type="checkbox" x-model="cost.is_inventory_cost" @change="handleCostInput()" />
                        </td>
                    @endif
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
                    <td colspan="{{ $showBilledBy ? 7 : 5 }}" style="text-align:center; color:var(--ink-4); padding:16px 0;">
                        Belum ada biaya tambahan.
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
    @if ($showBilledBy)
        <div style="padding:12px 16px; font-size:12px; color:var(--ink-3); line-height:1.7;">
            <div>• Biaya <strong>Ditagih Oleh "Supplier"</strong> akan ikut masuk ke dalam tagihan pembelian dari supplier.</div>
            <div>• Biaya <strong>Ditagih Oleh "Pihak Ketiga"</strong> akan ditagihkan terpisah oleh vendor terkait.</div>
            <div>• Hanya biaya dengan <strong>Biaya Inventory</strong> dicentang yang akan dialokasikan ke dalam nilai persediaan (HPP).</div>
        </div>
    @endif
</div>
