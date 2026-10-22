<x-misc.modal title="Form Produk" show="modal === 'add_product' || modal === 'edit_product'" close-handler="modal = null">
    <div class="form-body">
        <div class="form-grid-2">
            <x-misc.field label="Kode Produk" name="code">
                <input class="input mono" x-model="form.code"
                    :placeholder="(form.batch_prefix || 'PREFIX') + '-001 (otomatis)'" />
                <span class="field__hint">Kosongkan untuk dibuat otomatis.</span>
            </x-misc.field>
            <x-misc.field label="Kategori" name="category_id" :required="true">
                <select class="input" x-model="form.category_id">
                    <option value="">— Pilih Kategori —</option>
                    <template x-for="c in productCategories" :key="c.id">
                        <option :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
            </x-misc.field>
        </div>
        <x-misc.field label="Nama Produk" name="name" :required="true">
            {{-- Same rule as ProductService::generateBatchPrefix: first letter + following consonants --}}
            <input class="input" x-model="form.name" placeholder="Nama lengkap produk"
                x-on:input="if (modal === 'add_product') { const l = (form.name || '').toUpperCase().replace(/[^A-Z]/g, ''), r = l.slice(1); form.batch_prefix = (l.slice(0, 1) + r.replace(/[AEIOU]/g, '') + r).slice(0, 3); }" />
        </x-misc.field>
        <x-misc.field label="Deskripsi" name="description">
            <textarea class="input" rows="2" x-model="form.description" placeholder="Deskripsi singkat..."></textarea>
        </x-misc.field>
        <div class="form-grid-2">
            <x-misc.field label="Satuan" name="unit_id" :required="true">
                <select class="input" x-model="form.unit_id">
                    <option value="">— Pilih Satuan —</option>
                    <template x-for="u in productUnits" :key="u.id">
                        <option :value="u.id" x-text="u.name + ' (' + u.symbol + ')'"></option>
                    </template>
                </select>
            </x-misc.field>
            <x-misc.field label="Prefix Batch" name="batch_prefix">
                <input class="input mono" x-model="form.batch_prefix" placeholder="Otomatis dari nama" />
                <span class="field__hint">Format nomor batch: <span x-text="(form.batch_prefix || 'PREFIX') + '-0001'"></span></span>
            </x-misc.field>
        </div>
    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
        <button class="btn btn-primary" x-on:click="modal === 'add_product' ? handleCreate() : handleUpdate()"><x-misc.icon name="check"
                :size="14" />Simpan Produk</button>
    </x-slot:footer>
</x-misc.modal>
