{{-- Create Role Modal --}}
<x-misc.modal title="Tambah Role Baru" show="modal === 'add_role'" close-handler="modal = null">
    <div class="form-body">
        <x-misc.field label="Nama Role" :required="true">
            <input class="input" x-model="form.name" placeholder="Contoh: Manajer Gudang" x-on:keydown.enter="handleCreate()" />
        </x-misc.field>
    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
        <button class="btn btn-primary" x-on:click="handleCreate()">
            <x-misc.icon name="plus" :size="14" /> Buat Role
        </button>
    </x-slot:footer>
</x-misc.modal>

{{-- Edit Role Modal --}}
<x-misc.modal title="Edit Nama Role" show="modal === 'edit_role'" close-handler="modal = null">
    <div class="form-body">
        <x-misc.field label="Nama Role" :required="true">
            <input class="input" x-model="form.name" x-on:keydown.enter="handleUpdate()" />
        </x-misc.field>
    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
        <button class="btn btn-primary" x-on:click="handleUpdate()">
            <x-misc.icon name="check" :size="14" /> Simpan Perubahan
        </button>
    </x-slot:footer>
</x-misc.modal>
