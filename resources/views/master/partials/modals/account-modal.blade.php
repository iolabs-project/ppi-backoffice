<x-misc.modal title="Form Akun" show="modal === 'add_account' || modal === 'edit_account'" close-handler="modal = null">
            <div class="form-body">
                <div class="form-grid-1-2">
                    <x-misc.field label="Kode Akun" :required="true">
                        <input class="input mono" x-model="form.code" placeholder="1-xxx" />
                    </x-misc.field>
                    <x-misc.field label="Nama Akun" :required="true">
                        <input class="input" x-model="form.name" placeholder="Nama akun" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Kategori" :required="true">
                    <select class="input" x-model="form.category_id">
                        <option value="">— Pilih Kategori —</option>
                        <template x-for="c in accountCategoriesAll" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </x-misc.field>
                <x-misc.field label="Catatan">
                    <textarea class="input" rows="2" x-model="form.note" placeholder="Catatan (opsional)"></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="modal === 'add_account' ? handleCreate() : handleUpdate()"><x-misc.icon name="check"
                        :size="14" />Simpan Akun</button>
            </x-slot:footer>
        </x-misc.modal>