@extends('layouts.app')
@section('content')
    <script>
        function masterPageData() {
            return {
                tab: sessionStorage.getItem('master_tab') || 'produk',
                modal: null,

                init() {},

                extractError(error, fallback) {
                    const errors = error.response?.data?.errors;
                    if (errors) {
                        const first = Object.values(errors)[0];
                        if (Array.isArray(first) && first.length) return first[0];
                    }
                    return error.response?.data?.message || fallback;
                },

                fmtRp(n) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
                },

                avatarMeta(name) {
                    const words = (name || '').trim().split(/\s+/).filter(Boolean);
                    const initials = words.slice(0, 2).map(w => w[0].toUpperCase()).join('');
                    let h = 0;
                    for (const c of (name || '')) h = ((h * 31) + c.charCodeAt(0)) & 0xFFFFFFFF;
                    const hue = ((h % 360) + 360) % 360;
                    return { initials, bg: 'oklch(0.92 0.04 ' + hue + ')', fg: 'oklch(0.45 0.10 ' + hue + ')' };
                },

                // shared data for modals
                unitsAll: @json($units),
                categoriesAll: @json($productCategories),
                inventoryAccounts: @json($inventoryAccounts),
                salesAccounts: @json($salesAccounts),
                cogsAccounts: @json($cogsAccounts),
                receivableAccounts: @json($receivableAccounts),
                payableAccounts: @json($payableAccounts),
                accountCategoriesAll: @json($accountCategories),
                contactOptions: @json($contactOptions),
                userRoles: @json($userRoles),
                rolesAll: @json($roles),
                permitModules: [
                    { key:'dashboard', label:'Dashboard' }, { key:'penjualan', label:'Penjualan' },
                    { key:'pembelian', label:'Pembelian' }, { key:'kas', label:'Kas & Bank' },
                    { key:'biaya', label:'Biaya' }, { key:'master', label:'Master Data' },
                    { key:'laporan', label:'Laporan' },
                ],

                // produk
                produkForm: { code:'', name:'', description:'', category_id:'', unit_id:'', minimum_stock:0, inventory_account_id:'', sales_account_id:'', cogs_account_id:'' },
                editProdukData: { id:'', code:'', name:'', description:'', category_id:'', unit_id:'', minimum_stock:0, inventory_account_id:'', sales_account_id:'', cogs_account_id:'' },
                openEditProduk(p) {
                    this.editProdukData = { id:p.id, code:p.code, name:p.name, description:p.description||'', category_id:p.category_id||'', unit_id:p.unit_id||'', minimum_stock:p.minimum_stock||0, inventory_account_id:p.inventory_account_id||'', sales_account_id:p.sales_account_id||'', cogs_account_id:p.cogs_account_id||'' };
                    this.modal = 'edit_produk';
                },
                async submitAddProduk() {
                    try {
                        const r = await axios.post(route('master.products.store'), this.produkForm);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.produkForm = { code:'', name:'', description:'', category_id:'', unit_id:'', minimum_stock:0, inventory_account_id:'', sales_account_id:'', cogs_account_id:'' };
                        this.$dispatch('produk-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal menyimpan produk.') }); }
                },
                async submitEditProduk() {
                    try {
                        const r = await axios.put(route('master.products.update', this.editProdukData.id), this.editProdukData);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.$dispatch('produk-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal memperbarui produk.') }); }
                },

                // kontak
                kontakForm: { code:'', name:'', email:'', phone:'', address:'', city:'', state:'', postal_code:'', note:'', is_customer:true, is_supplier:false, is_employee:false, receivable_account_id:'', payable_account_id:'' },
                editKontakData: { id:'', code:'', name:'', email:'', phone:'', address:'', city:'', state:'', postal_code:'', note:'', is_customer:true, is_supplier:false, is_employee:false, receivable_account_id:'', payable_account_id:'' },
                openEditKontak(k) {
                    this.editKontakData = { id:k.id, code:k.code, name:k.name, email:k.email||'', phone:k.phone||'', address:k.address||'', city:k.city||'', state:k.state||'', postal_code:k.postal_code||'', note:k.note||'', is_customer:!!k.is_customer, is_supplier:!!k.is_supplier, is_employee:!!k.is_employee, receivable_account_id:k.receivable_account_id||'', payable_account_id:k.payable_account_id||'' };
                    this.modal = 'edit_kontak';
                },
                async submitAddKontak() {
                    try {
                        const r = await axios.post(route('master.contacts.store'), this.kontakForm);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.kontakForm = { code:'', name:'', email:'', phone:'', address:'', city:'', state:'', postal_code:'', note:'', is_customer:true, is_supplier:false, is_employee:false, receivable_account_id:'', payable_account_id:'' };
                        this.$dispatch('kontak-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal menyimpan kontak.') }); }
                },
                async submitEditKontak() {
                    try {
                        const r = await axios.put(route('master.contacts.update', this.editKontakData.id), this.editKontakData);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.$dispatch('kontak-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal memperbarui kontak.') }); }
                },

                // akun
                akunForm: { code:'', name:'', category_id:'', note:'' },
                editAkunData: { id:'', code:'', name:'', category_id:'', note:'' },
                openEditAkun(a) {
                    this.editAkunData = { id:a.id, code:a.code, name:a.name, category_id:a.category_id||'', note:a.note||'' };
                    this.modal = 'edit_akun';
                },
                async submitAddAkun() {
                    try {
                        const r = await axios.post(route('master.accounts.store'), this.akunForm);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.akunForm = { code:'', name:'', category_id:'', note:'' };
                        this.$dispatch('akun-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal menyimpan akun.') }); }
                },
                async submitEditAkun() {
                    try {
                        const r = await axios.put(route('master.accounts.update', this.editAkunData.id), this.editAkunData);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.$dispatch('akun-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal memperbarui akun.') }); }
                },

                // gudang
                gudangForm: { code:'', name:'', address:'', note:'' },
                editGudangData: { id:'', code:'', name:'', address:'', note:'' },
                openEditGudang(g) {
                    this.editGudangData = { id:g.id, code:g.code, name:g.name, address:g.address||'', note:g.note||'' };
                    this.modal = 'edit_gudang';
                },
                async submitAddGudang() {
                    try {
                        const r = await axios.post(route('master.warehouses.store'), this.gudangForm);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.gudangForm = { code:'', name:'', address:'', note:'' };
                        this.$dispatch('gudang-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal menyimpan gudang.') }); }
                },
                async submitEditGudang() {
                    try {
                        const r = await axios.put(route('master.warehouses.update', this.editGudangData.id), this.editGudangData);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.$dispatch('gudang-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal memperbarui gudang.') }); }
                },

                // user
                addUserForm: { username:'', password:'', confirm_password:'', contact_id:'', role_id:'' },
                editUserData: { id:'', username:'', password:'', confirm_password:'', contact_id:'', role_id:'', deleted_at:null, nama:'' },
                userName(u) { return u?.contact ? u.contact.name : (u?.username || ''); },
                openEditUser(u) {
                    this.editUserData = { id:u.id, username:u.username, password:'', confirm_password:'', contact_id:u.contact_id||'', role_id:u.roles?.length ? u.roles[0].id : '', deleted_at:u.deleted_at, nama:this.userName(u) };
                    this.modal = 'edit_user';
                },
                async submitAddUser() {
                    try {
                        const r = await axios.post(route('master.users.store'), this.addUserForm);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.addUserForm = { username:'', password:'', confirm_password:'', contact_id:'', role_id:'' };
                        this.$dispatch('user-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal menyimpan user.') }); }
                },
                async submitEditUser() {
                    try {
                        const r = await axios.put(route('master.users.update', this.editUserData.id), this.editUserData);
                        Toast.fire({ icon:'success', title:r.data.message });
                        this.modal = null;
                        this.$dispatch('user-refresh');
                    } catch (e) { Toast.fire({ icon:'error', title:this.extractError(e,'Gagal memperbarui user.') }); }
                },

                // role
                addRoleForm: { nama:'', deskripsi:'' },
                editRoleData: { id:'', nama:'', deskripsi:'' },
                openEditRole(r) {
                    this.editRoleData = { id:r.id, nama:r.nama, deskripsi:r.deskripsi };
                    this.modal = 'edit_role';
                },
            };
        }

        function productModule() {
            return {
                search: '',
                tableData: { current_page:1, last_page:1, per_page:10, total:0, data:[] },
                loading: false,
                page: 1,
                perPage: 10,

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.products.datatable'), { params:{ page:this.page, per_page:this.perPage, search:this.search } });
                        this.tableData = r.data;
                    } catch { Toast.fire({ icon:'error', title:'Terjadi kesalahan saat memuat data.' }); }
                    finally { this.loading = false; }
                },
                next() { if (this.page < this.tableData.last_page) { this.page++; this.fetchData(); } },
                prev() { if (this.page > 1) { this.page--; this.fetchData(); } },
                handleSearch(q) { this.search = q; this.page = 1; this.fetchData(); },
                async handleStatus(id) {
                    Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
                    try {
                        const r = await axios.post(route('master.products.status', id));
                        Swal.close(); Toast.fire({ icon:'success', title:r.data.message });
                        await this.fetchData();
                    } catch (e) { Swal.close(); Toast.fire({ icon:'error', title:e.response?.data?.message||'Gagal mengubah status.' }); }
                },
            };
        }

        function contactModule() {
            return {
                search: '',
                tableData: { current_page:1, last_page:1, per_page:10, total:0, data:[] },
                loading: false,
                page: 1,
                perPage: 10,

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.contacts.datatable'), { params:{ page:this.page, per_page:this.perPage, search:this.search } });
                        this.tableData = r.data;
                    } catch { Toast.fire({ icon:'error', title:'Terjadi kesalahan saat memuat data.' }); }
                    finally { this.loading = false; }
                },
                next() { if (this.page < this.tableData.last_page) { this.page++; this.fetchData(); } },
                prev() { if (this.page > 1) { this.page--; this.fetchData(); } },
                handleSearch(q) { this.search = q; this.page = 1; this.fetchData(); },
                async handleStatus(id) {
                    Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
                    try {
                        const r = await axios.post(route('master.contacts.status', id));
                        Swal.close(); Toast.fire({ icon:'success', title:r.data.message });
                        await this.fetchData();
                    } catch (e) { Swal.close(); Toast.fire({ icon:'error', title:e.response?.data?.message||'Gagal mengubah status.' }); }
                },
            };
        }

        function accountModule() {
            return {
                search: '',
                tableData: { data:{} },
                loading: false,

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.accounts.datatable'), { params:{ search:this.search } });
                        this.tableData.data = r.data;
                    } catch { Toast.fire({ icon:'error', title:'Terjadi kesalahan saat memuat data.' }); }
                    finally { this.loading = false; }
                },
                handleSearch(q) { this.search = q; this.fetchData(); },
                async handleStatus(id) {
                    Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
                    try {
                        const r = await axios.post(route('master.accounts.status', id));
                        Swal.close(); Toast.fire({ icon:'success', title:r.data.message });
                        await this.fetchData();
                    } catch (e) { Swal.close(); Toast.fire({ icon:'error', title:e.response?.data?.message||'Gagal mengubah status.' }); }
                },
            };
        }

        function gudangModule() {
            return {
                search: '',
                tableData: { current_page:1, last_page:1, per_page:20, total:0, data:[] },
                loading: false,
                page: 1,
                perPage: 20,

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.warehouses.datatable'), { params:{ page:this.page, per_page:this.perPage, search:this.search } });
                        this.tableData = r.data;
                    } catch { Toast.fire({ icon:'error', title:'Terjadi kesalahan saat memuat data gudang.' }); }
                    finally { this.loading = false; }
                },
                next() { if (this.page < this.tableData.last_page) { this.page++; this.fetchData(); } },
                prev() { if (this.page > 1) { this.page--; this.fetchData(); } },
                async handleStatus(g) {
                    Swal.fire({ title: g.deleted_at ? 'Aktifkan kembali gudang ini?' : 'Nonaktifkan gudang ini?', icon:'warning', showCancelButton:true, confirmButtonText:'Ya', cancelButtonText:'Batal', reverseButtons:true })
                        .then(async result => {
                            if (!result.isConfirmed) return;
                            try {
                                const r = await axios.post(route('master.warehouses.status', g.id));
                                Toast.fire({ icon:'success', title:r.data.message });
                                await this.fetchData();
                            } catch (e) { Toast.fire({ icon:'error', title:e.response?.data?.message||'Gagal mengubah status.' }); }
                        });
                },
            };
        }

        function userModule() {
            return {
                search: '',
                tableData: { current_page:1, last_page:1, per_page:10, total:0, data:[] },
                loading: false,
                page: 1,
                perPage: 10,

                userName(u) { return u?.contact ? u.contact.name : (u?.username || ''); },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('master.users.datatable'), { params:{ page:this.page, per_page:this.perPage, search:this.search } });
                        this.tableData = r.data;
                    } catch { Toast.fire({ icon:'error', title:'Terjadi kesalahan saat memuat data user.' }); }
                    finally { this.loading = false; }
                },
                next() { if (this.page < this.tableData.last_page) { this.page++; this.fetchData(); } },
                prev() { if (this.page > 1) { this.page--; this.fetchData(); } },
                async handleStatus(u) {
                    Swal.fire({ title: u.deleted_at ? 'Aktifkan kembali user ini?' : 'Nonaktifkan user ini?', icon:'warning', showCancelButton:true, confirmButtonText:'Ya', cancelButtonText:'Batal', reverseButtons:true })
                        .then(async result => {
                            if (!result.isConfirmed) return;
                            try {
                                const r = await axios.post(route('master.users.status', u.id));
                                Toast.fire({ icon:'success', title:r.data.message });
                                await this.fetchData();
                            } catch (e) { Toast.fire({ icon:'error', title:e.response?.data?.message||'Gagal mengubah status.' }); }
                        });
                },
            };
        }

        function permitModule() {
            return {
                roles: @json($roles),
                modules: [
                    { key:'dashboard', label:'Dashboard' }, { key:'penjualan', label:'Penjualan' },
                    { key:'pembelian', label:'Pembelian' }, { key:'kas', label:'Kas & Bank' },
                    { key:'biaya', label:'Biaya' }, { key:'master', label:'Master Data' },
                    { key:'laporan', label:'Laporan' },
                ],
                hasPermit(roleId, modKey) {
                    const role = this.roles.find(r => r.id === roleId);
                    return role ? role.akses.includes(modKey) : false;
                },
                togglePermit(roleId, modKey) {
                    const role = this.roles.find(r => r.id === roleId);
                    if (!role) return;
                    const idx = role.akses.indexOf(modKey);
                    if (idx === -1) role.akses.push(modKey);
                    else role.akses.splice(idx, 1);
                },
            };
        }
    </script>
    <div x-data="masterPageData()" x-init="init()"
        x-on:open-edit-gudang.window="openEditGudang($event.detail)"
        x-on:open-edit-user.window="openEditUser($event.detail)"
        x-on:open-edit-role.window="openEditRole($event.detail)"
        class="master-page">

        <div class="master-hd">
            <div>
                <h1 class="order-title display">Master Data</h1>
                <div class="order-sub">Kelola data referensi untuk seluruh modul ERP</div>
            </div>
            <div>
                <button class="btn btn-primary" x-on:click="modal = 'add_' + tab">
                    <x-misc.icon name="plus" :size="14" />
                    <span x-text="{ produk:'Tambah Produk', kontak:'Tambah Kontak', akun:'Tambah Akun', gudang:'Tambah Gudang', user:'Tambah User', permit:'Tambah Role' }[tab]"></span>
                </button>
            </div>
        </div>

        {{-- Tab bar --}}
        <div class="utab">
            @foreach ([['produk', 'Produk'], ['kontak', 'Kontak'], ['akun', 'Chart of Accounts'], ['gudang', 'Gudang'], ['user', 'User'], ['permit', 'Hak Akses']] as [$id, $lbl])
                <button class="utab-item"
                    x-on:click="tab = '{{ $id }}'; sessionStorage.setItem('master_tab', '{{ $id }}')"
                    :class="tab === '{{ $id }}' ? 'utab-active' : ''">{{ $lbl }}</button>
            @endforeach
        </div>

        @include('master.partials.tabs.product')
        @include('master.partials.tabs.contact')
        @include('master.partials.tabs.account')
        @include('master.partials.tabs.warehouse')
        @include('master.partials.tabs.user')
        @include('master.partials.tabs.permit')
        <x-misc.modal title="Tambah Produk Baru" show="modal === 'add_produk'" close-handler="modal = null">
            <div class="form-body">
                <div class="form-grid-2">
                    <x-misc.field label="Kode Produk" :required="true">
                        <input class="input mono" x-model="produkForm.code" placeholder="cth. TPG-003" />
                    </x-misc.field>
                    <x-misc.field label="Kategori" :required="true">
                        <select class="input" x-model="produkForm.category_id">
                            <option value="">— Pilih Kategori —</option>
                            <template x-for="c in categoriesAll" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
                <x-misc.field label="Nama Produk" :required="true">
                    <input class="input" x-model="produkForm.name" placeholder="Nama lengkap produk" />
                </x-misc.field>
                <x-misc.field label="Deskripsi">
                    <textarea class="input" rows="2" x-model="produkForm.description" placeholder="Deskripsi singkat..."></textarea>
                </x-misc.field>
                <div class="form-grid-2">
                    <x-misc.field label="Satuan" :required="true">
                        <select class="input" x-model="produkForm.unit_id">
                            <option value="">— Pilih Satuan —</option>
                            <template x-for="u in unitsAll" :key="u.id">
                                <option :value="u.id" x-text="u.name + ' (' + u.symbol + ')'"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Stok Minimum">
                        <input class="input num" type="number" style="text-align:right;"
                            x-model="produkForm.minimum_stock" placeholder="0" />
                    </x-misc.field>
                </div>
                <div class="form-grid-3">
                    <x-misc.field label="Akun Persediaan" :required="true">
                        <select class="input" x-model="produkForm.inventory_account_id">
                            <option value="">— Pilih Akun —</option>
                            <template x-for="a in inventoryAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Akun Penjualan" :required="true">
                        <select class="input" x-model="produkForm.sales_account_id">
                            <option value="">— Pilih Akun —</option>
                            <template x-for="a in salesAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Akun HPP" :required="true">
                        <select class="input" x-model="produkForm.cogs_account_id">
                            <option value="">— Pilih Akun —</option>
                            <template x-for="a in cogsAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitAddProduk()"><x-misc.icon name="check"
                        :size="14" />Simpan Produk</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Produk --}}
        <x-misc.modal title="Edit Produk" show="modal === 'edit_produk'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div
                        style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
                        <x-misc.icon name="box" :size="20" stroke="var(--ink-3)" />
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editProdukData.name"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editProdukData.code">
                        </div>
                    </div>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Kode Produk" :required="true">
                        <input class="input mono" x-model="editProdukData.code" />
                    </x-misc.field>
                    <x-misc.field label="Kategori" :required="true">
                        <select class="input" x-model="editProdukData.category_id">
                            <option value="">— Pilih Kategori —</option>
                            <template x-for="c in categoriesAll" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
                <x-misc.field label="Nama Produk" :required="true">
                    <input class="input" x-model="editProdukData.name" />
                </x-misc.field>
                <x-misc.field label="Deskripsi">
                    <textarea class="input" rows="2" x-model="editProdukData.description"></textarea>
                </x-misc.field>
                <div class="form-grid-2">
                    <x-misc.field label="Satuan" :required="true">
                        <select class="input" x-model="editProdukData.unit_id">
                            <option value="">— Pilih Satuan —</option>
                            <template x-for="u in unitsAll" :key="u.id">
                                <option :value="u.id" x-text="u.name + ' (' + u.symbol + ')'"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Stok Minimum">
                        <input class="input num" type="number" style="text-align:right;"
                            x-model="editProdukData.minimum_stock" />
                    </x-misc.field>
                </div>
                <div class="form-grid-3">
                    <x-misc.field label="Akun Persediaan" :required="true">
                        <select class="input" x-model="editProdukData.inventory_account_id">
                            <option value="">— Pilih Akun —</option>
                            <template x-for="a in inventoryAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Akun Penjualan" :required="true">
                        <select class="input" x-model="editProdukData.sales_account_id">
                            <option value="">— Pilih Akun —</option>
                            <template x-for="a in salesAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Akun HPP" :required="true">
                        <select class="input" x-model="editProdukData.cogs_account_id">
                            <option value="">— Pilih Akun —</option>
                            <template x-for="a in cogsAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitEditProduk()"><x-misc.icon name="check"
                        :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Kontak --}}
        <x-misc.modal title="Tambah Kontak Baru" show="modal === 'add_kontak'" close-handler="modal = null">
            <div class="form-body">
                <div class="form-grid-2">
                    <x-misc.field label="Kode Kontak" :required="true">
                        <input class="input mono" x-model="kontakForm.code" placeholder="cth. C-001" />
                    </x-misc.field>
                    <x-misc.field label="Nama" :required="true">
                        <input class="input" x-model="kontakForm.name" placeholder="Nama perusahaan / individu" />
                    </x-misc.field>
                </div>
                <div style="display:flex; gap:16px;">
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="kontakForm.is_customer" style="accent-color:var(--accent);" />
                        Customer
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="kontakForm.is_supplier" style="accent-color:var(--accent);" />
                        Vendor
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="kontakForm.is_employee" style="accent-color:var(--accent);" />
                        Karyawan
                    </label>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Email">
                        <input class="input" type="email" x-model="kontakForm.email"
                            placeholder="kontak@perusahaan.com" />
                    </x-misc.field>
                    <x-misc.field label="Telepon">
                        <input class="input" x-model="kontakForm.phone" placeholder="08xx-xxxx-xxxx" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Alamat">
                    <textarea class="input" rows="2" x-model="kontakForm.address" placeholder="Alamat lengkap..."></textarea>
                </x-misc.field>
                <div class="form-grid-3">
                    <x-misc.field label="Kota">
                        <input class="input" x-model="kontakForm.city" placeholder="Jakarta, Surabaya..." />
                    </x-misc.field>
                    <x-misc.field label="Provinsi">
                        <input class="input" x-model="kontakForm.state" />
                    </x-misc.field>
                    <x-misc.field label="Kode Pos">
                        <input class="input" x-model="kontakForm.postal_code" />
                    </x-misc.field>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Akun Piutang">
                        <select class="input" x-model="kontakForm.receivable_account_id">
                            <option value="">— Tidak ada —</option>
                            <template x-for="a in receivableAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Akun Hutang">
                        <select class="input" x-model="kontakForm.payable_account_id">
                            <option value="">— Tidak ada —</option>
                            <template x-for="a in payableAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitAddKontak()"><x-misc.icon name="check"
                        :size="14" />Simpan Kontak</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Kontak --}}
        <x-misc.modal title="Edit Kontak" show="modal === 'edit_kontak'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div class="avatar" style="width:44px; height:44px; font-size:14px;"
                        :style="'background:' + avatarMeta(editKontakData.name).bg + '; color:' + avatarMeta(editKontakData
                            .name).fg"
                        x-text="avatarMeta(editKontakData.name).initials"></div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editKontakData.name"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editKontakData.code">
                        </div>
                    </div>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Kode Kontak" :required="true">
                        <input class="input mono" x-model="editKontakData.code" />
                    </x-misc.field>
                    <x-misc.field label="Nama" :required="true">
                        <input class="input" x-model="editKontakData.name" placeholder="Nama perusahaan / individu" />
                    </x-misc.field>
                </div>
                <div style="display:flex; gap:16px;">
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="editKontakData.is_customer"
                            style="accent-color:var(--accent);" /> Customer
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="editKontakData.is_supplier"
                            style="accent-color:var(--accent);" /> Vendor
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="editKontakData.is_employee"
                            style="accent-color:var(--accent);" /> Karyawan
                    </label>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Email">
                        <input class="input" type="email" x-model="editKontakData.email"
                            placeholder="kontak@perusahaan.com" />
                    </x-misc.field>
                    <x-misc.field label="Telepon">
                        <input class="input" x-model="editKontakData.phone" placeholder="08xx-xxxx-xxxx" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Alamat">
                    <textarea class="input" rows="2" x-model="editKontakData.address" placeholder="Alamat lengkap..."></textarea>
                </x-misc.field>
                <div class="form-grid-3">
                    <x-misc.field label="Kota">
                        <input class="input" x-model="editKontakData.city" placeholder="Jakarta, Surabaya..." />
                    </x-misc.field>
                    <x-misc.field label="Provinsi">
                        <input class="input" x-model="editKontakData.state" />
                    </x-misc.field>
                    <x-misc.field label="Kode Pos">
                        <input class="input" x-model="editKontakData.postal_code" />
                    </x-misc.field>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Akun Piutang">
                        <select class="input" x-model="editKontakData.receivable_account_id">
                            <option value="">— Tidak ada —</option>
                            <template x-for="a in receivableAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Akun Hutang">
                        <select class="input" x-model="editKontakData.payable_account_id">
                            <option value="">— Tidak ada —</option>
                            <template x-for="a in payableAccounts" :key="a.id">
                                <option :value="a.id" x-text="a.code + ' – ' + a.name"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitEditKontak()"><x-misc.icon name="check"
                        :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Akun --}}
        <x-misc.modal title="Tambah Akun Baru" show="modal === 'add_akun'" close-handler="modal = null">
            <div class="form-body">
                <div class="form-grid-1-2">
                    <x-misc.field label="Kode Akun" :required="true">
                        <input class="input mono" x-model="akunForm.code" placeholder="1-xxx" />
                    </x-misc.field>
                    <x-misc.field label="Nama Akun" :required="true">
                        <input class="input" x-model="akunForm.name" placeholder="Nama akun" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Kategori" :required="true">
                    <select class="input" x-model="akunForm.category_id">
                        <option value="">— Pilih Kategori —</option>
                        <template x-for="c in accountCategoriesAll" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </x-misc.field>
                <x-misc.field label="Catatan">
                    <textarea class="input" rows="2" x-model="akunForm.note" placeholder="Catatan (opsional)"></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitAddAkun()"><x-misc.icon name="check"
                        :size="14" />Simpan Akun</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Akun --}}
        <x-misc.modal title="Edit Akun" show="modal === 'edit_akun'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div
                        style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
                        <x-misc.icon name="book" :size="20" stroke="var(--ink-3)" />
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editAkunData.name"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editAkunData.code"></div>
                    </div>
                </div>
                <div class="form-grid-1-2">
                    <x-misc.field label="Kode Akun" :required="true">
                        <input class="input mono" x-model="editAkunData.code" />
                    </x-misc.field>
                    <x-misc.field label="Nama Akun" :required="true">
                        <input class="input" x-model="editAkunData.name" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Kategori" :required="true">
                    <select class="input" x-model="editAkunData.category_id">
                        <option value="">— Pilih Kategori —</option>
                        <template x-for="c in accountCategoriesAll" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </x-misc.field>
                <x-misc.field label="Catatan">
                    <textarea class="input" rows="2" x-model="editAkunData.note" placeholder="Catatan (opsional)"></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitEditAkun()"><x-misc.icon name="check"
                        :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Gudang --}}
        <x-misc.modal title="Tambah Gudang Baru" show="modal === 'add_gudang'" close-handler="modal = null">
            <div class="form-body">
                <x-misc.field label="Nama Gudang" :required="true">
                    <input class="input" x-model="gudangForm.name" placeholder="Gudang Bekasi, dll." />
                </x-misc.field>
                <x-misc.field label="Kode Gudang" :required="true">
                    <input class="input mono" x-model="gudangForm.code" placeholder="GDG-xxx" />
                </x-misc.field>
                <x-misc.field label="Alamat">
                    <textarea class="input" rows="2" x-model="gudangForm.address" placeholder="Alamat gudang..."></textarea>
                </x-misc.field>
                <x-misc.field label="Catatan">
                    <textarea class="input" rows="2" x-model="gudangForm.note" placeholder="Keterangan gudang..."></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitAddGudang()"><x-misc.icon name="check"
                        :size="14" />Simpan Gudang</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Gudang --}}
        <x-misc.modal title="Edit Gudang" show="modal === 'edit_gudang'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div
                        style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
                        <x-misc.icon name="building" :size="20" stroke="var(--ink-3)" />
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editGudangData.name"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editGudangData.code">
                        </div>
                    </div>
                </div>
                <x-misc.field label="Nama Gudang" :required="true">
                    <input class="input" x-model="editGudangData.name" />
                </x-misc.field>
                <x-misc.field label="Kode Gudang" :required="true">
                    <input class="input mono" x-model="editGudangData.code" />
                </x-misc.field>
                <x-misc.field label="Alamat">
                    <textarea class="input" rows="2" x-model="editGudangData.address"></textarea>
                </x-misc.field>
                <x-misc.field label="Catatan">
                    <textarea class="input" rows="2" x-model="editGudangData.note"></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitEditGudang()"><x-misc.icon name="check"
                        :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah User --}}
        <x-misc.modal title="Tambah User Baru" show="modal === 'add_user'" close-handler="modal = null">
            <div class="form-body">
                <div class="form-grid-2">
                    <x-misc.field label="Username" :required="true">
                        <input class="input mono" x-model="addUserForm.username" placeholder="budi.santoso" />
                    </x-misc.field>
                    <x-misc.field label="Password" :required="true">
                        <input class="input" type="password" x-model="addUserForm.password" placeholder="••••••••" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Konfirmasi Password" :required="true">
                    <input class="input" type="password" x-model="addUserForm.confirm_password"
                        placeholder="••••••••" />
                </x-misc.field>
                <x-misc.field label="Kontak / Karyawan">
                    <select class="input" x-model="addUserForm.contact_id">
                        <option value="">— Tidak dihubungkan —</option>
                        <template x-for="k in contactOptions" :key="k.id">
                            <option :value="k.id" x-text="k.name"></option>
                        </template>
                    </select>
                </x-misc.field>
                <x-misc.field label="Role" :required="true">
                    <select class="input" x-model="addUserForm.role_id">
                        <option value="">— Pilih Role —</option>
                        <template x-for="r in userRoles" :key="r.id">
                            <option :value="r.id" x-text="r.name"></option>
                        </template>
                    </select>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitAddUser()"><x-misc.icon name="check"
                        :size="14" />Simpan User</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit User --}}
        <x-misc.modal title="Edit User" show="modal === 'edit_user'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div class="avatar" style="width:44px; height:44px; font-size:14px;"
                        :style="'background:' + avatarMeta(editUserData.nama).bg + '; color:' + avatarMeta(editUserData.nama).fg"
                        x-text="avatarMeta(editUserData.nama).initials"></div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editUserData.nama"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editUserData.id"></div>
                    </div>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Username" :required="true">
                        <input class="input mono" x-model="editUserData.username" />
                    </x-misc.field>
                    <x-misc.field label="Password Baru">
                        <input class="input" type="password" x-model="editUserData.password"
                            placeholder="Kosongkan jika tidak diubah" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Konfirmasi Password Baru">
                    <input class="input" type="password" x-model="editUserData.confirm_password"
                        placeholder="Kosongkan jika tidak diubah" />
                </x-misc.field>
                <x-misc.field label="Kontak / Karyawan">
                    <select class="input" x-model="editUserData.contact_id">
                        <option value="">— Tidak dihubungkan —</option>
                        <template x-for="k in contactOptions" :key="k.id">
                            <option :value="k.id" x-text="k.name"></option>
                        </template>
                    </select>
                </x-misc.field>
                <x-misc.field label="Role" :required="true">
                    <select class="input" x-model="editUserData.role_id">
                        <template x-for="r in userRoles" :key="r.id">
                            <option :value="r.id" x-text="r.name"></option>
                        </template>
                    </select>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary" x-on:click="submitEditUser()"><x-misc.icon name="check"
                        :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Role --}}
        <x-misc.modal title="Tambah Role Baru" show="modal === 'add_permit'" close-handler="modal = null">
            <div class="form-body">
                <x-misc.field label="Nama Role" :required="true">
                    <input class="input" x-model="addRoleForm.nama" placeholder="Kasir, Operator, dll." />
                </x-misc.field>
                <x-misc.field label="Deskripsi">
                    <input class="input" x-model="addRoleForm.deskripsi"
                        placeholder="Deskripsi singkat akses role ini" />
                </x-misc.field>
                <div>
                    <div style="font-size:12px; font-weight:600; color:var(--ink-3); margin-bottom:8px;">Akses Modul</div>
                    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:8px;">
                        <template x-for="m in permitModules" :key="m.key">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                                <input type="checkbox" style="accent-color:var(--accent);" />
                                <span x-text="m.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Role</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Role --}}
        <x-misc.modal title="Edit Role" show="modal === 'edit_role'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div
                        style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
                        <x-misc.icon name="users" :size="20" stroke="var(--ink-3)" />
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editRoleData.nama"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editRoleData.id"></div>
                    </div>
                </div>
                <x-misc.field label="Nama Role" :required="true">
                    <input class="input" x-model="editRoleData.nama" />
                </x-misc.field>
                <x-misc.field label="Deskripsi">
                    <input class="input" x-model="editRoleData.deskripsi" />
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

    </div>
@endsection
