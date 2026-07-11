@extends('layouts.app')
@section('content')
    <script>
        function masterPageData() {
            return {
                tab: sessionStorage.getItem('master_tab') || 'produk',
                modal: null,

                init() {
                    this.fetchProduk();
                    this.fetchKontak();
                    this.fetchAkun();
                    this.fetchGudang();
                    this.fetchUser();
                },

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
                    const words = name.trim().split(/\s+/).filter(Boolean);
                    const initials = words.slice(0, 2).map(w => w[0].toUpperCase()).join('');
                    let h = 0;
                    for (const c of name) h = ((h * 31) + c.charCodeAt(0)) & 0xFFFFFFFF;
                    const hue = ((h % 360) + 360) % 360;
                    return {
                        initials,
                        bg: 'oklch(0.92 0.04 ' + hue + ')',
                        fg: 'oklch(0.45 0.10 ' + hue + ')'
                    };
                },

                // =============== PRODUK ===============
                unitsAll: @json($units),
                categoriesAll: @json($productCategories),
                inventoryAccounts: @json($inventoryAccounts),
                salesAccounts: @json($salesAccounts),
                cogsAccounts: @json($cogsAccounts),

                produkTable: { current_page: 1, last_page: 1, per_page: 10, total: 0, data: [] },
                produkLoading: false,
                produkPage: 1,
                produkPerPage: 10,
                produkSearch: '',

                async fetchProduk() {
                    this.produkLoading = true;
                    try {
                        const response = await axios.get(route('master.products.datatable'), {
                            params: { page: this.produkPage, per_page: this.produkPerPage, search: this.produkSearch },
                        });
                        this.produkTable = response.data;
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan saat memuat data produk.' });
                    } finally {
                        this.produkLoading = false;
                    }
                },
                produkNext() {
                    if (this.produkTable && this.produkPage < this.produkTable.last_page) {
                        this.produkPage++;
                        this.fetchProduk();
                    }
                },
                produkPrev() {
                    if (this.produkPage > 1) {
                        this.produkPage--;
                        this.fetchProduk();
                    }
                },

                produkForm: {
                    code: '', name: '', description: '', category_id: '', unit_id: '',
                    minimum_stock: 0, inventory_account_id: '', sales_account_id: '', cogs_account_id: '',
                },
                editProdukData: {
                    id: '', code: '', name: '', description: '', category_id: '', unit_id: '',
                    minimum_stock: 0, inventory_account_id: '', sales_account_id: '', cogs_account_id: '',
                },
                openEditProduk(p) {
                    this.editProdukData = {
                        id: p.id, code: p.code, name: p.name, description: p.description || '',
                        category_id: p.category_id || '', unit_id: p.unit_id || '',
                        minimum_stock: p.minimum_stock || 0,
                        inventory_account_id: p.inventory_account_id || '',
                        sales_account_id: p.sales_account_id || '',
                        cogs_account_id: p.cogs_account_id || '',
                    };
                    this.modal = 'edit_produk';
                },
                async submitAddProduk() {
                    try {
                        const response = await axios.post(route('master.products.store'), this.produkForm);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        this.produkForm = {
                            code: '', name: '', description: '', category_id: '', unit_id: '',
                            minimum_stock: 0, inventory_account_id: '', sales_account_id: '', cogs_account_id: '',
                        };
                        await this.fetchProduk();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal menyimpan produk.') });
                    }
                },
                async submitEditProduk() {
                    try {
                        const response = await axios.put(route('master.products.update', this.editProdukData.id), this.editProdukData);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        await this.fetchProduk();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui produk.') });
                    }
                },
                toggleProdukStatus(p) {
                    Swal.fire({
                        title: p.deleted_at ? 'Aktifkan kembali produk ini?' : 'Nonaktifkan produk ini?',
                        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal', reverseButtons: true,
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;
                        try {
                            const response = await axios.post(route('master.products.status', p.id));
                            Toast.fire({ icon: 'success', title: response.data.message });
                            await this.fetchProduk();
                        } catch (error) {
                            Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui status produk.') });
                        }
                    });
                },

                // =============== KONTAK ===============
                receivableAccounts: @json($receivableAccounts),
                payableAccounts: @json($payableAccounts),

                kontakTable: { current_page: 1, last_page: 1, per_page: 10, total: 0, data: [] },
                kontakLoading: false,
                kontakPage: 1,
                kontakPerPage: 10,
                kontakSearch: '',

                async fetchKontak() {
                    this.kontakLoading = true;
                    try {
                        const response = await axios.get(route('master.contacts.datatable'), {
                            params: { page: this.kontakPage, per_page: this.kontakPerPage, search: this.kontakSearch },
                        });
                        this.kontakTable = response.data;
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan saat memuat data kontak.' });
                    } finally {
                        this.kontakLoading = false;
                    }
                },
                kontakNext() {
                    if (this.kontakTable && this.kontakPage < this.kontakTable.last_page) {
                        this.kontakPage++;
                        this.fetchKontak();
                    }
                },
                kontakPrev() {
                    if (this.kontakPage > 1) {
                        this.kontakPage--;
                        this.fetchKontak();
                    }
                },

                kontakForm: {
                    code: '', name: '', email: '', phone: '', address: '', city: '', state: '', postal_code: '', note: '',
                    is_customer: true, is_supplier: false, is_employee: false,
                    receivable_account_id: '', payable_account_id: '',
                },
                editKontakData: {
                    id: '', code: '', name: '', email: '', phone: '', address: '', city: '', state: '', postal_code: '', note: '',
                    is_customer: true, is_supplier: false, is_employee: false,
                    receivable_account_id: '', payable_account_id: '',
                },
                openEditKontak(k) {
                    this.editKontakData = {
                        id: k.id, code: k.code, name: k.name, email: k.email || '', phone: k.phone || '',
                        address: k.address || '', city: k.city || '', state: k.state || '', postal_code: k.postal_code || '', note: k.note || '',
                        is_customer: !!k.is_customer, is_supplier: !!k.is_supplier, is_employee: !!k.is_employee,
                        receivable_account_id: k.receivable_account_id || '', payable_account_id: k.payable_account_id || '',
                    };
                    this.modal = 'edit_kontak';
                },
                async submitAddKontak() {
                    try {
                        const response = await axios.post(route('master.contacts.store'), this.kontakForm);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        this.kontakForm = {
                            code: '', name: '', email: '', phone: '', address: '', city: '', state: '', postal_code: '', note: '',
                            is_customer: true, is_supplier: false, is_employee: false,
                            receivable_account_id: '', payable_account_id: '',
                        };
                        await this.fetchKontak();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal menyimpan kontak.') });
                    }
                },
                async submitEditKontak() {
                    try {
                        const response = await axios.put(route('master.contacts.update', this.editKontakData.id), this.editKontakData);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        await this.fetchKontak();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui kontak.') });
                    }
                },
                toggleKontakStatus(k) {
                    Swal.fire({
                        title: k.deleted_at ? 'Aktifkan kembali kontak ini?' : 'Nonaktifkan kontak ini?',
                        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal', reverseButtons: true,
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;
                        try {
                            const response = await axios.post(route('master.contacts.status', k.id));
                            Toast.fire({ icon: 'success', title: response.data.message });
                            await this.fetchKontak();
                        } catch (error) {
                            Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui status kontak.') });
                        }
                    });
                },

                // =============== AKUN / COA ===============
                accountCategoriesAll: @json($accountCategories),

                akunTable: { current_page: 1, last_page: 1, per_page: 10, total: 0, data: [] },
                akunLoading: false,
                akunPage: 1,
                akunPerPage: 10,
                akunSearch: '',

                async fetchAkun() {
                    this.akunLoading = true;
                    try {
                        const response = await axios.get(route('master.accounts.datatable'), {
                            params: { page: this.akunPage, per_page: this.akunPerPage, search: this.akunSearch },
                        });
                        this.akunTable = response.data;
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan saat memuat data akun.' });
                    } finally {
                        this.akunLoading = false;
                    }
                },
                akunNext() {
                    if (this.akunTable && this.akunPage < this.akunTable.last_page) {
                        this.akunPage++;
                        this.fetchAkun();
                    }
                },
                akunPrev() {
                    if (this.akunPage > 1) {
                        this.akunPage--;
                        this.fetchAkun();
                    }
                },
                akunGroupedRows() {
                    const rows = [];
                    let lastCategoryId;
                    for (const a of this.akunTable.data) {
                        const categoryId = a.category_id ?? null;
                        if (categoryId !== lastCategoryId) {
                            rows.push({ type: 'group', key: 'g-' + a.id, label: a.category ? a.category.name : 'Lainnya' });
                            lastCategoryId = categoryId;
                        }
                        rows.push({ type: 'row', key: 'r-' + a.id, data: a });
                    }
                    return rows;
                },

                akunForm: { code: '', name: '', category_id: '', note: '' },
                editAkunData: { id: '', code: '', name: '', category_id: '', note: '' },
                openEditAkun(a) {
                    this.editAkunData = {
                        id: a.id, code: a.code, name: a.name,
                        category_id: a.category_id || '', note: a.note || '',
                    };
                    this.modal = 'edit_akun';
                },
                async submitAddAkun() {
                    try {
                        const response = await axios.post(route('master.accounts.store'), this.akunForm);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        this.akunForm = { code: '', name: '', category_id: '', note: '' };
                        await this.fetchAkun();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal menyimpan akun.') });
                    }
                },
                async submitEditAkun() {
                    try {
                        const response = await axios.put(route('master.accounts.update', this.editAkunData.id), this.editAkunData);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        await this.fetchAkun();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui akun.') });
                    }
                },
                toggleAkunStatus(a) {
                    Swal.fire({
                        title: a.deleted_at ? 'Aktifkan kembali akun ini?' : 'Nonaktifkan akun ini?',
                        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal', reverseButtons: true,
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;
                        try {
                            const response = await axios.post(route('master.accounts.status', a.id));
                            Toast.fire({ icon: 'success', title: response.data.message });
                            await this.fetchAkun();
                        } catch (error) {
                            Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui status akun.') });
                        }
                    });
                },

                // =============== GUDANG ===============
                gudangTable: { current_page: 1, last_page: 1, per_page: 20, total: 0, data: [] },
                gudangLoading: false,
                gudangPage: 1,
                gudangPerPage: 20,
                gudangSearch: '',

                async fetchGudang() {
                    this.gudangLoading = true;
                    try {
                        const response = await axios.get(route('master.warehouses.datatable'), {
                            params: { page: this.gudangPage, per_page: this.gudangPerPage, search: this.gudangSearch },
                        });
                        this.gudangTable = response.data;
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan saat memuat data gudang.' });
                    } finally {
                        this.gudangLoading = false;
                    }
                },
                gudangNext() {
                    if (this.gudangTable && this.gudangPage < this.gudangTable.last_page) {
                        this.gudangPage++;
                        this.fetchGudang();
                    }
                },
                gudangPrev() {
                    if (this.gudangPage > 1) {
                        this.gudangPage--;
                        this.fetchGudang();
                    }
                },

                gudangForm: { code: '', name: '', address: '', note: '' },
                editGudangData: { id: '', code: '', name: '', address: '', note: '' },
                openEditGudang(g) {
                    this.editGudangData = { id: g.id, code: g.code, name: g.name, address: g.address || '', note: g.note || '' };
                    this.modal = 'edit_gudang';
                },
                async submitAddGudang() {
                    try {
                        const response = await axios.post(route('master.warehouses.store'), this.gudangForm);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        this.gudangForm = { code: '', name: '', address: '', note: '' };
                        await this.fetchGudang();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal menyimpan gudang.') });
                    }
                },
                async submitEditGudang() {
                    try {
                        const response = await axios.put(route('master.warehouses.update', this.editGudangData.id), this.editGudangData);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        await this.fetchGudang();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui gudang.') });
                    }
                },
                toggleGudangStatus(g) {
                    Swal.fire({
                        title: g.deleted_at ? 'Aktifkan kembali gudang ini?' : 'Nonaktifkan gudang ini?',
                        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal', reverseButtons: true,
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;
                        try {
                            const response = await axios.post(route('master.warehouses.status', g.id));
                            Toast.fire({ icon: 'success', title: response.data.message });
                            await this.fetchGudang();
                        } catch (error) {
                            Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui status gudang.') });
                        }
                    });
                },

                contactOptions: @json($contactOptions),
                userRoles: @json($userRoles),

                userTable: { current_page: 1, last_page: 1, per_page: 10, total: 0, data: [] },
                userLoading: false,
                userPage: 1,
                userPerPage: 10,
                userSearch: '',

                userName(u) {
                    return u?.contact ? u.contact.name : (u?.username || '');
                },

                async fetchUser() {
                    this.userLoading = true;
                    try {
                        const response = await axios.get(route('master.users.datatable'), {
                            params: { page: this.userPage, per_page: this.userPerPage, search: this.userSearch },
                        });
                        this.userTable = response.data;
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan saat memuat data user.' });
                    } finally {
                        this.userLoading = false;
                    }
                },
                userNext() {
                    if (this.userTable && this.userPage < this.userTable.last_page) {
                        this.userPage++;
                        this.fetchUser();
                    }
                },
                userPrev() {
                    if (this.userPage > 1) {
                        this.userPage--;
                        this.fetchUser();
                    }
                },

                addUserForm: { username: '', password: '', confirm_password: '', contact_id: '', role_id: '' },
                editUserData: { id: '', username: '', password: '', confirm_password: '', contact_id: '', role_id: '', deleted_at: null, nama: '' },
                openEditUser(u) {
                    this.editUserData = {
                        id: u.id,
                        username: u.username,
                        password: '',
                        confirm_password: '',
                        contact_id: u.contact_id || '',
                        role_id: u.roles && u.roles.length ? u.roles[0].id : '',
                        deleted_at: u.deleted_at,
                        nama: this.userName(u),
                    };
                    this.modal = 'edit_user';
                },
                async submitAddUser() {
                    try {
                        const response = await axios.post(route('master.users.store'), this.addUserForm);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        this.addUserForm = { username: '', password: '', confirm_password: '', contact_id: '', role_id: '' };
                        await this.fetchUser();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal menyimpan user.') });
                    }
                },
                async submitEditUser() {
                    try {
                        const response = await axios.put(route('master.users.update', this.editUserData.id), this.editUserData);
                        Toast.fire({ icon: 'success', title: response.data.message });
                        this.modal = null;
                        await this.fetchUser();
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui user.') });
                    }
                },
                toggleUserStatus(u) {
                    Swal.fire({
                        title: u.deleted_at ? 'Aktifkan kembali user ini?' : 'Nonaktifkan user ini?',
                        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal', reverseButtons: true,
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;
                        try {
                            const response = await axios.post(route('master.users.status', u.id));
                            Toast.fire({ icon: 'success', title: response.data.message });
                            await this.fetchUser();
                        } catch (error) {
                            Toast.fire({ icon: 'error', title: this.extractError(error, 'Gagal memperbarui status user.') });
                        }
                    });
                },

                rolesAll: @json($roles),
                permitModules: [
                    { key: 'dashboard', label: 'Dashboard' },
                    { key: 'penjualan', label: 'Penjualan' },
                    { key: 'pembelian', label: 'Pembelian' },
                    { key: 'kas',       label: 'Kas & Bank' },
                    { key: 'biaya',     label: 'Biaya' },
                    { key: 'master',    label: 'Master Data' },
                    { key: 'laporan',   label: 'Laporan' },
                ],
                hasPermit(roleId, modKey) {
                    const role = this.rolesAll.find(r => r.id === roleId);
                    return role ? role.akses.includes(modKey) : false;
                },
                togglePermit(roleId, modKey) {
                    const role = this.rolesAll.find(r => r.id === roleId);
                    if (!role) return;
                    const idx = role.akses.indexOf(modKey);
                    if (idx === -1) role.akses.push(modKey);
                    else role.akses.splice(idx, 1);
                },
                addRoleForm: { nama: '', deskripsi: '' },
                editRoleData: { id: '', nama: '', deskripsi: '' },
                openEditRole(r) {
                    this.editRoleData = { id: r.id, nama: r.nama, deskripsi: r.deskripsi };
                    this.modal = 'edit_role';
                },
            };
        }
    </script>
    <div x-data="masterPageData()" x-init="init()" class="master-page">

        <div class="master-hd">
            <div>
                <h1 class="order-title display">Master Data</h1>
                <div class="order-sub">Kelola data referensi untuk seluruh modul ERP</div>
            </div>
            <div>
                <button class="btn btn-primary" x-on:click="modal = 'add_' + tab">
                    <x-misc.icon name="plus" :size="14" />
                    <span
                        x-text="{ produk:'Tambah Produk', kontak:'Tambah Kontak', akun:'Tambah Akun', gudang:'Tambah Gudang', user:'Tambah User', permit:'Tambah Role' }[tab]"></span>
                </button>
            </div>
        </div>

        {{-- Tab bar --}}
        <div class="utab">
            @foreach ([['produk', 'Produk'], ['kontak', 'Kontak'], ['akun', 'Chart of Accounts'], ['gudang', 'Gudang'], ['user', 'User'], ['permit', 'Hak Akses']] as [$id, $lbl])
                <button class="utab-item" x-on:click="tab = '{{ $id }}'; sessionStorage.setItem('master_tab', '{{ $id }}')"
                    :class="tab === '{{ $id }}' ? 'utab-active' : ''">{{ $lbl }}</button>
            @endforeach
        </div>

        {{-- =================== PRODUK =================== --}}
        <div x-show="tab === 'produk'" x-cloak>
            <div class="card" style="overflow:hidden;">
                <div class="master-toolbar">
                    <div class="master-search">
                        <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                                stroke="var(--ink-4)" /></span>
                        <input class="input master-search__input" placeholder="Cari produk..."
                            x-model="produkSearch" x-on:input.debounce.400ms="produkPage = 1; fetchProduk()" />
                    </div>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th style="text-align:right;">Stok Minimum</th>
                            <th>Status</th>
                            <th style="width:40px;"></th>
                    </thead>
                    <tbody>
                        <template x-if="produkLoading">
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...</td>
                            </tr>
                        </template>
                        <template x-if="!produkLoading && produkTable.data.length === 0">
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">Tidak ada data</td>
                            </tr>
                        </template>
                        <template x-if="!produkLoading">
                            <template x-for="p in produkTable.data" :key="p.id">
                                <tr>
                                    <td class="mono" style="font-weight:600; font-size:12px; color:var(--ink-4);"
                                        x-text="p.code"></td>
                                    <td>
                                        <div style="font-weight:600; font-size:13px;" x-text="p.name"></div>
                                        <div x-show="p.description" style="font-size:11px; color:var(--ink-4);"
                                            x-text="p.description"></div>
                                    </td>
                                    <td><span class="chip" x-text="p.category ? p.category.name : '—'"></span></td>
                                    <td style="color:var(--ink-3); font-size:13px;" x-text="p.unit ? p.unit.symbol : '—'"></td>
                                    <td class="num" style="text-align:right; font-size:13px;" x-text="p.minimum_stock ?? 0"></td>
                                    <td>
                                        <span class="chip" :style="!p.deleted_at ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
                                            x-text="!p.deleted_at ? 'Aktif' : 'Nonaktif'"></span>
                                    </td>
                                    <td x-on:click.stop>
                                        <div class="action-menu" x-data="{ open: false }">
                                            <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                                    x-on:click="open = !open" x-on:click.outside="open = false">
                                                <x-misc.icon name="more" :size="15" />
                                            </button>
                                            <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                                 style="position:absolute; right:0; top:100%; margin-top:4px;">
                                                <button class="action-menu__item" x-on:click="openEditProduk(p)">
                                                    <x-misc.icon name="edit" :size="14" /> Edit Produk
                                                </button>
                                                <button class="action-menu__item" x-on:click="toggleProdukStatus(p)">
                                                    <x-misc.icon name="x" :size="14" />
                                                    <span x-text="p.deleted_at ? 'Aktifkan' : 'Nonaktifkan'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
                <div class="table-pagination">
                    <span class="pagination-info"
                        x-text="(produkTable.total === 0 ? 0 : ((produkTable.current_page - 1) * produkTable.per_page + 1)) + '–' + (produkTable.total === 0 ? 0 : Math.min(produkTable.current_page * produkTable.per_page, produkTable.total)) + ' dari ' + produkTable.total + ' produk'"></span>
                    <div class="pagination-controls">
                        <span class="pagination-page-info">Hal. <strong x-text="produkTable.current_page"></strong> / <strong
                                x-text="produkTable.last_page"></strong></span>
                        <button class="btn btn-ghost btn-sm" :disabled="produkTable.current_page <= 1" x-on:click="produkPrev()">
                            <x-misc.icon name="chev-left" :size="14" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" :disabled="produkTable.current_page >= produkTable.last_page"
                            x-on:click="produkNext()">
                            Next <x-misc.icon name="chev-right" :size="14" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== KONTAK =================== --}}
        <div x-show="tab === 'kontak'" x-cloak>
            <div class="card" style="overflow:hidden;">
                <div class="master-toolbar">
                    <div class="master-search">
                        <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                                stroke="var(--ink-4)" /></span>
                        <input class="input master-search__input" placeholder="Cari kontak..."
                            x-model="kontakSearch" x-on:input.debounce.400ms="kontakPage = 1; fetchKontak()" />
                    </div>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Kota</th>
                            <th>Status</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="kontakLoading">
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...</td>
                            </tr>
                        </template>
                        <template x-if="!kontakLoading && kontakTable.data.length === 0">
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--ink-3); padding:20px;">Tidak ada data</td>
                            </tr>
                        </template>
                        <template x-if="!kontakLoading">
                            <template x-for="k in kontakTable.data" :key="k.id">
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div class="avatar"
                                                :style="'background:' + avatarMeta(k.name).bg + '; color:' + avatarMeta(k.name).fg"
                                                x-text="avatarMeta(k.name).initials"></div>
                                            <span style="font-weight:600; font-size:13px;" x-text="k.name"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="chip" x-text="[k.is_customer ? 'Customer' : null, k.is_supplier ? 'Vendor' : null, k.is_employee ? 'Karyawan' : null].filter(Boolean).join(', ') || '—'"></span>
                                    </td>
                                    <td style="font-size:13px; color:var(--ink-3);" x-text="k.email || '—'"></td>
                                    <td style="font-size:13px; color:var(--ink-3);" x-text="k.phone || '—'"></td>
                                    <td style="font-size:13px; color:var(--ink-3);" x-text="k.city || '—'"></td>
                                    <td>
                                        <span class="chip" :style="!k.deleted_at ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
                                            x-text="!k.deleted_at ? 'Aktif' : 'Nonaktif'"></span>
                                    </td>
                                    <td x-on:click.stop>
                                        <div class="action-menu" x-data="{ open: false }">
                                            <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                                    x-on:click="open = !open" x-on:click.outside="open = false">
                                                <x-misc.icon name="more" :size="15" />
                                            </button>
                                            <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                                 style="position:absolute; right:0; top:100%; margin-top:4px;">
                                                <button class="action-menu__item" x-on:click="openEditKontak(k)">
                                                    <x-misc.icon name="edit" :size="14" /> Edit Kontak
                                                </button>
                                                <button class="action-menu__item" x-on:click="toggleKontakStatus(k)">
                                                    <x-misc.icon name="x" :size="14" />
                                                    <span x-text="k.deleted_at ? 'Aktifkan' : 'Nonaktifkan'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
                <div class="table-pagination">
                    <span class="pagination-info"
                        x-text="(kontakTable.total === 0 ? 0 : ((kontakTable.current_page - 1) * kontakTable.per_page + 1)) + '–' + (kontakTable.total === 0 ? 0 : Math.min(kontakTable.current_page * kontakTable.per_page, kontakTable.total)) + ' dari ' + kontakTable.total + ' kontak'"></span>
                    <div class="pagination-controls">
                        <span class="pagination-page-info">Hal. <strong x-text="kontakTable.current_page"></strong> / <strong
                                x-text="kontakTable.last_page"></strong></span>
                        <button class="btn btn-ghost btn-sm" :disabled="kontakTable.current_page <= 1" x-on:click="kontakPrev()">
                            <x-misc.icon name="chev-left" :size="14" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" :disabled="kontakTable.current_page >= kontakTable.last_page"
                            x-on:click="kontakNext()">
                            Next <x-misc.icon name="chev-right" :size="14" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== AKUN / COA =================== --}}
        <div x-show="tab === 'akun'" x-cloak>
            <div class="card" style="overflow:hidden;">
                <div class="master-toolbar">
                    <div class="master-search">
                        <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                                stroke="var(--ink-4)" /></span>
                        <input class="input master-search__input" placeholder="Cari akun..."
                            x-model="akunSearch" x-on:input.debounce.400ms="akunPage = 1; fetchAkun()" />
                    </div>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Akun</th>
                            <th>Kategori</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="akunLoading">
                            <tr>
                                <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...</td>
                            </tr>
                        </template>
                        <template x-if="!akunLoading && akunTable.data.length === 0">
                            <tr>
                                <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">Tidak ada data</td>
                            </tr>
                        </template>
                        <template x-if="!akunLoading">
                            <template x-for="row in akunGroupedRows()" :key="row.key">
                                <tr :class="row.type === 'group' ? 'coa-group-row' : ''">
                                    <td colspan="6" x-show="row.type === 'group'" x-text="row.label"></td>
                                    <td class="mono" x-show="row.type === 'row'" style="font-weight:600; font-size:12px; color:var(--ink-4);"
                                        x-text="row.data?.code"></td>
                                    <td x-show="row.type === 'row'" style="font-weight:600; font-size:13px;" x-text="row.data?.name"></td>
                                    <td x-show="row.type === 'row'"><span class="chip" x-text="row.data?.category ? row.data.category.name : '—'"></span></td>
                                    <td x-show="row.type === 'row'" style="color:var(--ink-3); font-size:13px;" x-text="row.data?.note || '—'"></td>
                                    <td x-show="row.type === 'row'">
                                        <span class="chip" :style="!row.data?.deleted_at ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
                                            x-text="!row.data?.deleted_at ? 'Aktif' : 'Nonaktif'"></span>
                                    </td>
                                    <td x-show="row.type === 'row'" x-on:click.stop>
                                        <div class="action-menu" x-data="{ open: false }">
                                            <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                                    x-on:click="open = !open" x-on:click.outside="open = false">
                                                <x-misc.icon name="more" :size="15" />
                                            </button>
                                            <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                                 style="position:absolute; right:0; top:100%; margin-top:4px;">
                                                <button class="action-menu__item" x-on:click="openEditAkun(row.data)">
                                                    <x-misc.icon name="edit" :size="14" /> Edit Akun
                                                </button>
                                                <button class="action-menu__item" x-on:click="toggleAkunStatus(row.data)">
                                                    <x-misc.icon name="x" :size="14" />
                                                    <span x-text="row.data?.deleted_at ? 'Aktifkan' : 'Nonaktifkan'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
                <div class="table-pagination">
                    <span class="pagination-info"
                        x-text="(akunTable.total === 0 ? 0 : ((akunTable.current_page - 1) * akunTable.per_page + 1)) + '–' + (akunTable.total === 0 ? 0 : Math.min(akunTable.current_page * akunTable.per_page, akunTable.total)) + ' dari ' + akunTable.total + ' akun'"></span>
                    <div class="pagination-controls">
                        <span class="pagination-page-info">Hal. <strong x-text="akunTable.current_page"></strong> / <strong
                                x-text="akunTable.last_page"></strong></span>
                        <button class="btn btn-ghost btn-sm" :disabled="akunTable.current_page <= 1" x-on:click="akunPrev()">
                            <x-misc.icon name="chev-left" :size="14" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" :disabled="akunTable.current_page >= akunTable.last_page"
                            x-on:click="akunNext()">
                            Next <x-misc.icon name="chev-right" :size="14" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== GUDANG =================== --}}
        <div x-show="tab === 'gudang'" x-cloak>
            <div class="master-toolbar" style="padding-left:0; padding-right:0;">
                <div class="master-search">
                    <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                            stroke="var(--ink-4)" /></span>
                    <input class="input master-search__input" placeholder="Cari gudang..."
                        x-model="gudangSearch" x-on:input.debounce.400ms="gudangPage = 1; fetchGudang()" />
                </div>
            </div>
            <template x-if="gudangLoading">
                <div style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...</div>
            </template>
            <div class="gudang-grid" x-show="!gudangLoading">
                <template x-for="g in gudangTable.data" :key="g.id">
                    <div class="card gudang-card">
                        <div class="gudang-card__hd">
                            <div class="gudang-card__info">
                                <div class="gudang-card__icon">
                                    <x-misc.icon name="building" :size="18" stroke="var(--accent)" />
                                </div>
                                <div>
                                    <div class="gudang-card__name" x-text="g.name"></div>
                                    <div class="gudang-card__code mono" x-text="g.code"></div>
                                </div>
                            </div>
                            <div class="action-menu" x-data="{ open: false }" x-on:click.stop>
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                        x-on:click="open = !open" x-on:click.outside="open = false">
                                    <x-misc.icon name="more" :size="15" />
                                </button>
                                <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                     style="position:absolute; right:0; top:100%; margin-top:4px;">
                                    <button class="action-menu__item" x-on:click="openEditGudang(g)">
                                        <x-misc.icon name="edit" :size="14" /> Edit Gudang
                                    </button>
                                    <button class="action-menu__item" x-on:click="toggleGudangStatus(g)">
                                        <x-misc.icon name="x" :size="14" />
                                        <span x-text="g.deleted_at ? 'Aktifkan' : 'Nonaktifkan'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="gudang-card__meta">
                            <div class="gudang-card__meta-row">
                                <x-misc.icon name="building" :size="12"
                                    stroke="var(--ink-4)" /><span x-text="g.address || '—'"></span>
                            </div>
                            <div class="gudang-card__meta-row" x-show="g.note">
                                <x-misc.icon name="layers" :size="12" stroke="var(--ink-4)" /><span x-text="g.note"></span>
                            </div>
                            <div class="gudang-card__meta-row">
                                <span class="chip" :style="!g.deleted_at ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
                                    x-text="!g.deleted_at ? 'Aktif' : 'Nonaktif'"></span>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Add new gudang card --}}
                <div class="card gudang-add-card" x-on:click="modal = 'add_gudang'">
                    <div>
                        <x-misc.icon name="plus" :size="24" stroke="var(--ink-3)" />
                        <div class="gudang-add-label">Tambah Gudang</div>
                    </div>
                </div>
            </div>
            <div class="table-pagination" x-show="!gudangLoading">
                <span class="pagination-info"
                    x-text="(gudangTable.total === 0 ? 0 : ((gudangTable.current_page - 1) * gudangTable.per_page + 1)) + '–' + (gudangTable.total === 0 ? 0 : Math.min(gudangTable.current_page * gudangTable.per_page, gudangTable.total)) + ' dari ' + gudangTable.total + ' gudang'"></span>
                <div class="pagination-controls">
                    <span class="pagination-page-info">Hal. <strong x-text="gudangTable.current_page"></strong> / <strong
                            x-text="gudangTable.last_page"></strong></span>
                    <button class="btn btn-ghost btn-sm" :disabled="gudangTable.current_page <= 1" x-on:click="gudangPrev()">
                        <x-misc.icon name="chev-left" :size="14" /> Prev
                    </button>
                    <button class="btn btn-ghost btn-sm" :disabled="gudangTable.current_page >= gudangTable.last_page"
                        x-on:click="gudangNext()">
                        Next <x-misc.icon name="chev-right" :size="14" />
                    </button>
                </div>
            </div>
        </div>

        {{-- =================== USER =================== --}}
        <div x-show="tab === 'user'" x-cloak>
            <div class="card" style="overflow:hidden;">
                <div class="master-toolbar">
                    <div class="master-search">
                        <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                                stroke="var(--ink-4)" /></span>
                        <input class="input master-search__input" placeholder="Cari user..."
                            x-model="userSearch" x-on:input.debounce.400ms="userPage = 1; fetchUser()" />
                    </div>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Nama / Username</th>
                            <th>Role</th>
                            <th>Kontak / Karyawan</th>
                            <th>Status</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="u in userTable.data" :key="u.id">
                            <tr class="row-tap" style="cursor:pointer;">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="avatar"
                                            :style="'background:' + avatarMeta(userName(u)).bg + '; color:' + avatarMeta(userName(u)).fg"
                                            x-text="avatarMeta(userName(u)).initials"></div>
                                        <div>
                                            <div style="font-weight:600; font-size:13px;" x-text="userName(u)"></div>
                                            <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="u.username"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="chip" x-text="u.role_name || '—'"></span></td>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="u.contact ? u.contact.name : '—'"></td>
                                <td>
                                    <span class="chip" :style="!u.deleted_at ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
                                        x-text="!u.deleted_at ? 'Aktif' : 'Nonaktif'"></span>
                                </td>
                                <td x-on:click.stop>
                                    <div class="action-menu" x-data="{ open: false }">
                                        <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                                x-on:click="open = !open" x-on:click.outside="open = false">
                                            <x-misc.icon name="more" :size="15" />
                                        </button>
                                        <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                             style="position:absolute; right:0; top:100%; margin-top:4px;">
                                            <button class="action-menu__item" x-on:click="openEditUser(u)">
                                                <x-misc.icon name="edit" :size="14" /> Edit User
                                            </button>
                                            <button class="action-menu__item" x-on:click="toggleUserStatus(u)">
                                                <x-misc.icon name="x" :size="14" />
                                                <span x-text="u.deleted_at ? 'Aktifkan' : 'Nonaktifkan'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="table-pagination">
                    <span class="pagination-info"
                        x-text="(userTable.total === 0 ? 0 : ((userTable.current_page - 1) * userTable.per_page + 1)) + '–' + (userTable.total === 0 ? 0 : Math.min(userTable.current_page * userTable.per_page, userTable.total)) + ' dari ' + userTable.total + ' user'"></span>
                    <div class="pagination-controls">
                        <span class="pagination-page-info">Hal. <strong x-text="userTable.current_page"></strong> / <strong
                                x-text="userTable.last_page"></strong></span>
                        <button class="btn btn-ghost btn-sm" :disabled="userTable.current_page <= 1" x-on:click="userPrev()">
                            <x-misc.icon name="chev-left" :size="14" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" :disabled="userTable.current_page >= userTable.last_page"
                            x-on:click="userNext()">
                            Next <x-misc.icon name="chev-right" :size="14" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- =================== PERMIT / HAK AKSES =================== --}}
        <div x-show="tab === 'permit'" x-cloak>
            <div class="card" style="overflow:hidden;">
                <div class="master-toolbar" style="border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-weight:600; font-size:13px;">Matriks Hak Akses per Role</div>
                        <div style="font-size:12px; color:var(--ink-4); margin-top:2px;">Centang modul yang dapat diakses oleh setiap role</div>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="tbl" style="min-width:700px;">
                        <thead>
                            <tr>
                                <th style="min-width:190px;">Role</th>
                                <template x-for="m in permitModules" :key="m.key">
                                    <th style="text-align:center; width:88px; font-size:12px;" x-text="m.label"></th>
                                </template>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="r in rolesAll" :key="r.id">
                                <tr>
                                    <td>
                                        <div style="font-weight:600; font-size:13px;" x-text="r.nama"></div>
                                        <div style="font-size:11px; color:var(--ink-4); margin-top:1px;" x-text="r.deskripsi"></div>
                                    </td>
                                    <template x-for="m in permitModules" :key="m.key">
                                        <td style="text-align:center;">
                                            <input type="checkbox"
                                                style="width:16px; height:16px; cursor:pointer; accent-color:var(--accent);"
                                                :checked="hasPermit(r.id, m.key)"
                                                x-on:change="togglePermit(r.id, m.key)" />
                                        </td>
                                    </template>
                                    <td x-on:click.stop>
                                        <div class="action-menu" x-data="{ open: false }">
                                            <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                                    x-on:click="open = !open" x-on:click.outside="open = false">
                                                <x-misc.icon name="more" :size="15" />
                                            </button>
                                            <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                                 style="position:absolute; right:0; top:100%; margin-top:4px;">
                                                <button class="action-menu__item" x-on:click="openEditRole(r)">
                                                    <x-misc.icon name="edit" :size="14" /> Edit Role
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal: Tambah Produk --}}
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
                        <input class="input num" type="number" style="text-align:right;" x-model="produkForm.minimum_stock" placeholder="0" />
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
                <button class="btn btn-primary" x-on:click="submitAddProduk()"><x-misc.icon name="check" :size="14" />Simpan Produk</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Produk --}}
        <x-misc.modal title="Edit Produk" show="modal === 'edit_produk'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
                        <x-misc.icon name="box" :size="20" stroke="var(--ink-3)" />
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editProdukData.name"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editProdukData.code"></div>
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
                        <input class="input num" type="number" style="text-align:right;" x-model="editProdukData.minimum_stock" />
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
                <button class="btn btn-primary" x-on:click="submitEditProduk()"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
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
                        <input type="checkbox" x-model="kontakForm.is_customer" style="accent-color:var(--accent);" /> Customer
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="kontakForm.is_supplier" style="accent-color:var(--accent);" /> Vendor
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="kontakForm.is_employee" style="accent-color:var(--accent);" /> Karyawan
                    </label>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Email">
                        <input class="input" type="email" x-model="kontakForm.email" placeholder="kontak@perusahaan.com" />
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
                <button class="btn btn-primary" x-on:click="submitAddKontak()"><x-misc.icon name="check" :size="14" />Simpan Kontak</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Kontak --}}
        <x-misc.modal title="Edit Kontak" show="modal === 'edit_kontak'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div class="avatar" style="width:44px; height:44px; font-size:14px;"
                         :style="'background:' + avatarMeta(editKontakData.name).bg + '; color:' + avatarMeta(editKontakData.name).fg"
                         x-text="avatarMeta(editKontakData.name).initials"></div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editKontakData.name"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editKontakData.code"></div>
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
                        <input type="checkbox" x-model="editKontakData.is_customer" style="accent-color:var(--accent);" /> Customer
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="editKontakData.is_supplier" style="accent-color:var(--accent);" /> Vendor
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" x-model="editKontakData.is_employee" style="accent-color:var(--accent);" /> Karyawan
                    </label>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Email">
                        <input class="input" type="email" x-model="editKontakData.email" placeholder="kontak@perusahaan.com" />
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
                <button class="btn btn-primary" x-on:click="submitEditKontak()"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
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
                <button class="btn btn-primary" x-on:click="submitAddAkun()"><x-misc.icon name="check" :size="14" />Simpan Akun</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Akun --}}
        <x-misc.modal title="Edit Akun" show="modal === 'edit_akun'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
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
                <button class="btn btn-primary" x-on:click="submitEditAkun()"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
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
                <button class="btn btn-primary" x-on:click="submitAddGudang()"><x-misc.icon name="check" :size="14" />Simpan Gudang</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Gudang --}}
        <x-misc.modal title="Edit Gudang" show="modal === 'edit_gudang'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
                        <x-misc.icon name="building" :size="20" stroke="var(--ink-3)" />
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editGudangData.name"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editGudangData.code"></div>
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
                <button class="btn btn-primary" x-on:click="submitEditGudang()"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
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
                    <input class="input" type="password" x-model="addUserForm.confirm_password" placeholder="••••••••" />
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
                <button class="btn btn-primary" x-on:click="submitAddUser()"><x-misc.icon name="check" :size="14" />Simpan User</button>
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
                        <input class="input" type="password" x-model="editUserData.password" placeholder="Kosongkan jika tidak diubah" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Konfirmasi Password Baru">
                    <input class="input" type="password" x-model="editUserData.confirm_password" placeholder="Kosongkan jika tidak diubah" />
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
                <button class="btn btn-primary" x-on:click="submitEditUser()"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Role --}}
        <x-misc.modal title="Tambah Role Baru" show="modal === 'add_permit'" close-handler="modal = null">
            <div class="form-body">
                <x-misc.field label="Nama Role" :required="true">
                    <input class="input" x-model="addRoleForm.nama" placeholder="Kasir, Operator, dll." />
                </x-misc.field>
                <x-misc.field label="Deskripsi">
                    <input class="input" x-model="addRoleForm.deskripsi" placeholder="Deskripsi singkat akses role ini" />
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
                    <div style="width:44px; height:44px; border-radius:10px; background:var(--bg-2); display:grid; place-items:center;">
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
