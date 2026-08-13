<x-misc.modal title="Form Kontak" show="modal === 'add_contact' || modal === 'edit_contact'" close-handler="modal = null">
    <div class="form-body">

        <div class="form-section">
            <div class="form-section-title">Profil Kontak</div>
            <div class="form-grid-2">
                <x-misc.field label="Kode Kontak" :required="true">
                    <input class="input mono" x-model="form.code" placeholder="cth. C-001" />
                </x-misc.field>
                <x-misc.field label="Nama" :required="true">
                    <input class="input" x-model="form.name" placeholder="Nama perusahaan / individu" />
                </x-misc.field>
            </div>
            <div style="display:flex; gap:16px;">
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                    <input type="checkbox" x-model="form.is_customer" style="accent-color:var(--accent);" />
                    Customer
                </label>
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                    <input type="checkbox" x-model="form.is_supplier" style="accent-color:var(--accent);" />
                    Vendor
                </label>
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                    <input type="checkbox" x-model="form.is_employee" style="accent-color:var(--accent);" />
                    Karyawan
                </label>
            </div>
            <div class="form-grid-2">
                <x-misc.field label="Email">
                    <input class="input" type="email" x-model="form.email" placeholder="kontak@perusahaan.com" />
                </x-misc.field>
                <x-misc.field label="Telepon">
                    <input class="input" x-model="form.phone" placeholder="08xx-xxxx-xxxx" />
                </x-misc.field>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Alamat</div>
            <x-misc.field label="Alamat">
                <textarea class="input" rows="2" x-model="form.address" placeholder="Alamat lengkap..."></textarea>
            </x-misc.field>
            <div class="form-grid-3">
                <x-misc.field label="Provinsi">
                    <input class="input" x-model="form.state" />
                </x-misc.field>
                <x-misc.field label="Kota">
                    <input class="input" x-model="form.city" placeholder="Jakarta, Surabaya..." />
                </x-misc.field>
                <x-misc.field label="Kode Pos">
                    <input class="input" x-model="form.postal_code" />
                </x-misc.field>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Biaya (Penjualan)</div>
            <div class="form-grid-2">
                <x-misc.field label="Biaya Transportasi" :required="false">
                    <input class="input num" x-model="form.transportation_cost" x-mask:dynamic="$money($input, '.',',')" />
                </x-misc.field>
            </div>
        </div>

    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
        <button class="btn btn-primary"
            x-on:click="modal === 'add_contact' ? handleCreate() : handleUpdate()"><x-misc.icon name="check"
                :size="14" />Simpan Kontak</button>
    </x-slot:footer>
</x-misc.modal>
