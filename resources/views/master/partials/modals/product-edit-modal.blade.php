<x-misc.modal title="Edit Produk" show="modal === 'edit_produk'" close-handler="modal = null">
    <div class="form-body">
        <div class="form-grid-2">
            <x-misc.field label="Kode Produk" :required="true">
                <input class="input mono" x-model="form.code" placeholder="cth. TPG-003" />
            </x-misc.field>
            <x-misc.field label="Kategori" :required="true">
                <select class="input" x-model="form.category_id">
                    <option value="">— Pilih Kategori —</option>
                    <template x-for="c in categoriesAll" :key="c.id">
                        <option :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
            </x-misc.field>
        </div>
        <x-misc.field label="Nama Produk" :required="true">
            <input class="input" x-model="form.name" placeholder="Nama lengkap produk" />
        </x-misc.field>
        <x-misc.field label="Deskripsi">
            <textarea class="input" rows="2" x-model="form.description" placeholder="Deskripsi singkat..."></textarea>
        </x-misc.field>
        <div class="form-grid-2">
            <x-misc.field label="Satuan" :required="true">
                <select class="input" x-model="form.unit_id">
                    <option value="">— Pilih Satuan —</option>
                    <template x-for="u in unitsAll" :key="u.id">
                        <option :value="u.id" x-text="u.name + ' (' + u.symbol + ')'"></option>
                    </template>
                </select>
            </x-misc.field>
        </div>
    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
        <button class="btn btn-primary" x-on:click="handleUpdate()"><x-misc.icon name="check"
                :size="14" />Simpan Produk</button>
    </x-slot:footer>
</x-misc.modal>
