<div class="card card-bd--form">
    <div class="display card-hd-title">Informasi Transaksi</div>
    <div class="order-form-grid-3">

        {{-- Ke --}}
        <x-misc.field label="Akun Tujuan" :required="true">
            <x-misc.select display="selectedAccount ? selectedAccount.name : 'Pilih akun'" hasValue="selectedAccount"
                placeholder="Cari akun...">
                <template x-for="a in accounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()))"
                    :key="a.id">
                    <div class="dropdown-item"
                        @click="selectedAccount = a; formData.to_account_id = a.id; open = false; q = ''">
                        <div style="font-weight:500;" x-text="a.name"></div>
                        <div style="font-size:11px; color:var(--ink-4);" x-text="a.code"></div>
                    </div>
                </template>
                <template x-if="!accounts.some(a => !q || a.name.toLowerCase().includes(q.toLowerCase()))">
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

        {{-- Total --}}
        <x-misc.field label="Total" :required="true">
            <input class="input num" x-model="formData.subtotal" x-mask:dynamic="$money($input, '.',',')" />
        </x-misc.field>
    </div>

</div>
