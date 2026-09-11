<x-misc.modal title="Tambah Rekening" show="modal === 'add_account'" close-handler="modal = null">
    <div class="form-body">
        <div class="form-grid-1-2">
            <x-misc.field label="Kode Akun" :required="true">
                <input class="input mono" x-model="form.code" placeholder="1-xxx" />
            </x-misc.field>
            <x-misc.field label="Nama Rekening" :required="true">
                <input class="input" x-model="form.name" placeholder="Nama rekening" />
            </x-misc.field>
        </div>
        <x-misc.field label="Kategori">
            <div class="input mono input--readonly"  style="display:flex; align-items:center;">Kas &amp; Bank</div>
        </x-misc.field>
        <x-misc.field label="Catatan">
            <textarea class="input" rows="2" x-model="form.note" placeholder="Catatan (opsional)"></textarea>
        </x-misc.field>
    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
        <button class="btn btn-primary" x-on:click="createAccount()">
            <x-misc.icon name="check" :size="14" />Simpan Rekening
        </button>
    </x-slot:footer>
</x-misc.modal>