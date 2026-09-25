<x-misc.modal title="Tambah Pembayaran" show="modalOpen" close-handler="modalOpen = false" :width="1000">
    <div class="form-body">
        <div class="card" style="padding:12px 14px; margin-bottom:14px; background:var(--bg-3);">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 20px; font-size:12.5px;">
                <div><span style="color:var(--ink-3);">No.
                        Invoice</span><br><strong>{{ $invoice->number }}</strong></div>
                <div><span style="color:var(--ink-3);">Supplier</span><br><strong>{{ $invoice->supplier->name }}</strong>
                </div>
                <div><span style="color:var(--ink-3);">Jumlah
                        Invoice</span><br><strong class="num">{{ number_format($invoice->total_amount, 2) }}</strong></div>
                <div><span style="color:var(--ink-3);">Sisa Tagihan</span><br><strong class="num"
                        style="color:var(--bad);" x-text="m(outstandingAmount())"></strong></div>
            </div>
        </div>
        <div class="form-grid-2">
            <x-misc.field label="No. Pembayaran">
                <input class="input mono" disabled value="Otomatis saat disimpan" style="color:var(--ink-4);" />
            </x-misc.field>
            <x-misc.field label="Tanggal Pembayaran" name="payment_date" :required="true">
                <input type="date" class="input" x-model="form.payment_date" />
            </x-misc.field>
            <x-misc.field label="Akun Kas / Bank" name="account_id" :required="true">
                <x-misc.select
                    display="form.account_id ? (cashBankAccounts.find(a => a.id === form.account_id)?.code + ' - ' + cashBankAccounts.find(a => a.id === form.account_id)?.name) : 'Pilih akun'"
                    hasValue="form.account_id" placeholder="Cari akun..." min-width="240px">
                    <template
                        x-for="a in cashBankAccounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || (a.code || '').toLowerCase().includes(q.toLowerCase()))"
                        :key="a.id">
                        <div class="dropdown-item" @click="form.account_id = a.id; open = false; q = ''">
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:13px;" x-text="a.name"></div>
                                <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="a.code">
                                </div>
                            </div>
                        </div>
                    </template>
                </x-misc.select>
            </x-misc.field>
            <x-misc.field label="Metode Pembayaran" name="payment_method" :required="true">
                <select class="input" x-model="form.payment_method">
                    <option value="cash">Tunai</option>
                    <option value="bank_transfer">Transfer Bank</option>
                    <option value="credit_card">Kartu Kredit</option>
                </select>
            </x-misc.field>
            <x-misc.field label="No. Referensi" name="reference_number">
                <input class="input" x-model="form.reference_number" placeholder="Nomor referensi" />
            </x-misc.field>
            <x-misc.field label="Jumlah" name="amount" :required="true">
                <input class="input num" style="text-align:right;" x-model="form.amount"
                    x-mask:dynamic="$money($input, '.',',')" />
                <div style="font-size:11px; color:var(--ink-4); margin-top:4px;">
                    Maksimal: <span class="num" x-text="m(outstandingAmount())"></span>
                </div>
                <div x-show="insufficientBalance()" x-cloak style="font-size:11px; color:var(--bad); margin-top:4px;">
                    Saldo akun ini <span class="num" x-text="m(selectedAccountBalance())"></span>, kurang dari jumlah
                    pembayaran.
                </div>
            </x-misc.field>
        </div>
        <div style="margin-top:14px;">
            <x-misc.field label="Catatan" name="note">
                <textarea class="input" rows="3" x-model="form.note" placeholder="Catatan pembayaran (opsional)"></textarea>
            </x-misc.field>
        </div>
    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" type="button" @click="modalOpen = false">
            <x-misc.icon name="x" :size="14" />Batal
        </button>
        <button class="btn btn-primary" type="button" :disabled="saving" @click="submit()">
            <x-misc.icon name="check" :size="14" />Simpan
        </button>
    </x-slot:footer>
</x-misc.modal>
