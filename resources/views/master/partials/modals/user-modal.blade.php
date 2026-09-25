<x-misc.modal title="Form User" show="modal === 'add_user' || modal === 'edit_user'" close-handler="modal = null">
    <div class="form-body">
        <x-misc.field label="Username" name="username" :required="true">
            <input class="input mono" x-model="form.username" placeholder="budi.santoso" />
        </x-misc.field>
        <template x-if="modal === 'add_user'">
            <div class="form-grid-2">
                <x-misc.field label="Password" name="password" :required="true">
                    <input class="input" type="password" x-model="form.password" placeholder="••••••••" />
                </x-misc.field>
                <x-misc.field label="Konfirmasi Password" name="password_confirmation" :required="true">
                    <input class="input" type="password" x-model="form.password_confirmation" placeholder="••••••••" />
                </x-misc.field>
            </div>
        </template>
        <x-misc.field label="Kontak / Karyawan" name="contact_id">
            <select class="input" x-model="form.contact_id">
                <option value="">— Tidak dihubungkan —</option>
                <template x-for="k in contactOptions" :key="k.id">
                    <option :value="k.id" x-text="k.name"></option>
                </template>
            </select>
        </x-misc.field>
        <x-misc.field label="Role" name="role_id" :required="true">
            <select class="input" x-model="form.role_id">
                <option value="">— Pilih Role —</option>
                <template x-for="r in userRoles" :key="r.id">
                    <option :value="r.id" x-text="r.name"></option>
                </template>
            </select>
        </x-misc.field>
    </div>
    <x-slot:footer>
        <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
        <button class="btn btn-primary" x-on:click="modal === 'add_user' ? handleCreate() : handleUpdate()"><x-misc.icon name="check"
                :size="14" />Simpan User</button>
    </x-slot:footer>
</x-misc.modal>

@if (Route::has('master.users.password'))
    <x-misc.modal title="Ganti Password" show="modal === 'change_password'" close-handler="modal = null">
        <div class="form-body">
            <x-misc.field label="Username" name="username">
                <input class="input mono" x-model="form.username" disabled />
            </x-misc.field>
            <div class="form-grid-2">
                <x-misc.field label="Password Baru" name="new_password" :required="true">
                    <input class="input" type="password" x-model="changePasswordForm.new_password"
                        placeholder="••••••••" autocomplete="new-password" />
                </x-misc.field>
                <x-misc.field label="Konfirmasi Password Baru" name="new_password_confirmation" :required="true">
                    <input class="input" type="password" x-model="changePasswordForm.new_password_confirmation"
                        placeholder="••••••••" autocomplete="new-password" />
                </x-misc.field>
            </div>
        </div>
        <x-slot:footer>
            <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
            <button class="btn btn-primary" x-on:click="handlePasswordChange()"><x-misc.icon name="check"
                    :size="14" />Simpan Password</button>
        </x-slot:footer>
    </x-misc.modal>
@endif
