{{-- Shared Additional Charges (Bill Customer) table.
     Expects the enclosing Alpine scope to expose:
       - formData.charges: [{ account_id, description, amount, is_taxable }]
       - addCharge() / removeCharge(index)
       - handleChargeInput() -- called on every change to trigger recalculation
       - formData.tax_percentage -- shown next to Taxable rows as the effective rate
     Blade params:
       - $accounts: collection of {id, code, name}
--}}
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
                <th style="width:90px; text-align:center;">Kena Pajak</th>
                <th style="width:80px; text-align:right;">Pajak (%)</th>
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
                        <select class="input" style="height:32px;" x-model.number="charge.account_id"
                            @change="handleChargeInput()">
                            <option value="">Pilih akun</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" x-model="charge.is_taxable" @change="handleChargeInput()" />
                    </td>
                    <td class="num" style="text-align:right;"
                        x-text="charge.is_taxable ? NumberUtils.formatNumericIntoMask(n(formData.tax_percentage)) + '%' : '0%'"></td>
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
                    <td colspan="7" style="text-align:center; color:var(--ink-4); padding:16px 0;">
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
