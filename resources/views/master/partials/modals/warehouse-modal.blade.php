<x-misc.modal title="Form Gudang" show="modal === 'add_warehouse' || modal === 'edit_warehouse'" close-handler="modal = null">
            <div class="form-body">
                <x-misc.field label="Nama Gudang" :required="true">
                    <input class="input" x-model="form.name" placeholder="Gudang Bekasi, dll." />
                </x-misc.field>
                <x-misc.field label="Kode Gudang" :required="true">
                    <input class="input mono" x-model="form.code" placeholder="GDG-xxx" />
                </x-misc.field>
                <x-misc.field label="Alamat">
                    <textarea class="input" rows="2" x-model="form.address" placeholder="Alamat gudang..."></textarea>
                </x-misc.field>
                <x-misc.field label="Catatan">
                    <textarea class="input" rows="2" x-model="form.note" placeholder="Keterangan gudang..."></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="modal === 'add_warehouse' ? handleCreate() : handleUpdate()"><x-misc.icon name="check"
                        :size="14" />Simpan Gudang</button>
            </x-slot:footer>
        </x-misc.modal>