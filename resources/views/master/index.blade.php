@extends('layouts.app')
@section('content')
    <script>
        function masterPageData() {
            return {
                tab: sessionStorage.getItem('master_tab') || 'produk',
                modal: null,

                produkAll: @json($produk),
                produkPage: 1,
                produkPerPage: 5,
                get produkPaged() {
                    return this.produkAll.slice((this.produkPage - 1) * this.produkPerPage, this.produkPage * this
                        .produkPerPage);
                },
                get produkTotalPages() {
                    return Math.ceil(this.produkAll.length / this.produkPerPage);
                },

                kontakAll: @json($kontak),
                kontakPage: 1,
                kontakPerPage: 5,
                get kontakPaged() {
                    return this.kontakAll.slice((this.kontakPage - 1) * this.kontakPerPage, this.kontakPage * this
                        .kontakPerPage);
                },
                get kontakTotalPages() {
                    return Math.ceil(this.kontakAll.length / this.kontakPerPage);
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

                produkForm: {
                    kode: '',
                    nama: '',
                    satuan: '',
                    kategori: '',
                    hargaBeli: 0,
                    hargaJual: 0
                },
                editProdukData: { kode: '', nama: '', kategori: '', satuan: '', hargaBeli: 0, hargaJual: 0 },
                openEditProduk(p) {
                    this.editProdukData = { kode: p.kode, nama: p.nama, kategori: p.kategori || '', satuan: p.satuan || '', hargaBeli: p.hargaBeli || 0, hargaJual: p.hargaJual || 0 };
                    this.modal = 'edit_produk';
                },
                kontakForm: {
                    nama: '',
                    tipe: 'klien',
                    email: '',
                    telepon: '',
                    alamat: '',
                    kota: ''
                },
                editKontakData: { id: '', nama: '', tipe: 'Customer', email: '', telepon: '', kota: '', alamat: '' },
                openEditKontak(k) {
                    this.editKontakData = { id: k.id, nama: k.nama, tipe: k.tipe, email: k.email || '', telepon: k.telepon || '', kota: k.kota || '', alamat: k.alamat || '' };
                    this.modal = 'edit_kontak';
                },
                coaAll: @json($chartOfAccounts),
                akunForm: {
                    kode: '',
                    nama: '',
                    tipe: '',
                    subKategori: ''
                },
                editAkunData: { kode: '', nama: '', tipe: '', subKategori: '' },
                openEditAkun(a) {
                    this.editAkunData = { kode: a.kode, nama: a.nama, tipe: a.tipe, subKategori: a.parent || '' };
                    this.modal = 'edit_akun';
                },
                gudangForm: {
                    kode: '',
                    nama: '',
                    kota: '',
                    kapasitas: 0,
                    PIC: ''
                },

                userAll: @json($users),
                userPage: 1,
                userPerPage: 10,
                get userPaged() {
                    return this.userAll.slice((this.userPage - 1) * this.userPerPage, this.userPage * this.userPerPage);
                },
                get userTotalPages() {
                    return Math.ceil(this.userAll.length / this.userPerPage);
                },
                addUserForm: { username: '', password: '', kontak: '', role: '' },
                editUserData: { id: '', username: '', nama: '', kontak: '', role: '', aktif: true },
                openEditUser(u) {
                    this.editUserData = { id: u.id, username: u.username, nama: u.nama, kontak: u.kontak || '', role: u.role, aktif: u.aktif };
                    this.modal = 'edit_user';
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
    <div x-data="masterPageData()" class="master-page">

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
                        <input class="input master-search__input" placeholder="Cari produk..." />
                    </div>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th style="text-align:right;">Harga Beli</th>
                            <th style="text-align:right;">Harga Jual</th>
                            <th style="text-align:right;">Stok</th>
                            <th style="width:40px;"></th>
                    </thead>
                    <tbody>
                        <template x-for="p in produkPaged" :key="p.kode">
                            <tr class="row-tap" style="cursor:pointer;" x-on:click="window.location.href='/master/produk/'+p.kode">
                                <td class="mono" style="font-weight:600; font-size:12px; color:var(--ink-4);"
                                    x-text="p.kode"></td>
                                <td>
                                    <div style="font-weight:600; font-size:13px;" x-text="p.nama"></div>
                                    <div x-show="p.deskripsi" style="font-size:11px; color:var(--ink-4);"
                                        x-text="p.deskripsi"></div>
                                </td>
                                <td><span class="chip" x-text="p.kategori"></span></td>
                                <td style="color:var(--ink-3); font-size:13px;" x-text="p.satuan"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="fmtRp(p.hargaBeli)">
                                </td>
                                <td class="num" style="text-align:right; font-size:13px; font-weight:600;"
                                    x-text="fmtRp(p.hargaJual)"></td>
                                <td class="num" style="text-align:right; font-size:13px;" x-text="p.stok ?? 0"></td>
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
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="table-pagination">
                    <span class="pagination-info"
                        x-text="'Menampilkan ' + ((produkPage-1)*produkPerPage+1) + '–' + Math.min(produkPage*produkPerPage, produkAll.length) + ' dari ' + produkAll.length + ' produk'"></span>
                    <div class="pagination-controls">
                        <span class="pagination-page-info">Hal. <strong x-text="produkPage"></strong> / <strong
                                x-text="produkTotalPages"></strong></span>
                        <button class="btn btn-ghost btn-sm" :disabled="produkPage <= 1" x-on:click="produkPage--">
                            <x-misc.icon name="chev-left" :size="14" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" :disabled="produkPage >= produkTotalPages"
                            x-on:click="produkPage++">
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
                        <input class="input master-search__input" placeholder="Cari kontak..." />
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
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="k in kontakPaged" :key="k.id">
                            <tr class="row-tap" style="cursor:pointer;" x-on:click="window.location.href='/master/kontak/'+k.id">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="avatar"
                                            :style="'background:' + avatarMeta(k.nama).bg + '; color:' + avatarMeta(k.nama).fg"
                                            x-text="avatarMeta(k.nama).initials"></div>
                                        <span style="font-weight:600; font-size:13px;" x-text="k.nama"></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="chip" :class="k.tipe.toLowerCase() === 'vendor' ? 'chip-accent' : ''"
                                        x-text="k.tipe"></span>
                                </td>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="k.email || '—'"></td>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="k.telepon || '—'"></td>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="k.kota || '—'"></td>
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
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="table-pagination">
                    <span class="pagination-info"
                        x-text="'Menampilkan ' + ((kontakPage-1)*kontakPerPage+1) + '–' + Math.min(kontakPage*kontakPerPage, kontakAll.length) + ' dari ' + kontakAll.length + ' kontak'"></span>
                    <div class="pagination-controls">
                        <span class="pagination-page-info">Hal. <strong x-text="kontakPage"></strong> / <strong
                                x-text="kontakTotalPages"></strong></span>
                        <button class="btn btn-ghost btn-sm" :disabled="kontakPage <= 1" x-on:click="kontakPage--">
                            <x-misc.icon name="chev-left" :size="14" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" :disabled="kontakPage >= kontakTotalPages"
                            x-on:click="kontakPage++">
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
                        <input class="input master-search__input" placeholder="Cari akun..." />
                    </div>
                </div>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Akun</th>
                            <th>Tipe</th>
                            <th style="text-align:right;">Saldo Normal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $akunGroups = [];
                            foreach ($chartOfAccounts as $a) {
                                $prefix = substr($a['kode'], 0, 1);
                                $akunGroups[$prefix][] = $a;
                            }
                            $groupNames = [
                                '1' => 'Aset',
                                '2' => 'Liabilitas',
                                '3' => 'Ekuitas',
                                '4' => 'Pendapatan',
                                '5' => 'Beban Pokok',
                                '6' => 'Beban Operasional',
                            ];
                        @endphp
                        @foreach ($akunGroups as $prefix => $items)
                            <tr class="coa-group-row">
                                <td colspan="5">{{ $groupNames[$prefix] ?? 'Lainnya' }}</td>
                            </tr>
                            @foreach ($items as $a)
                                @php $tipeLbl = $a['tipe'] ?? ucfirst($groupNames[$prefix] ?? '—'); @endphp
                                <tr class="row-tap" style="cursor:pointer;"
                                    x-on:click="openEditAkun({ kode: {{ json_encode($a['kode']) }}, nama: {{ json_encode($a['nama']) }}, tipe: {{ json_encode($tipeLbl) }}, parent: {{ json_encode($a['parent']) }} })">
                                    <td class="mono" style="font-weight:600; font-size:12px; color:var(--ink-4);">
                                        {{ $a['kode'] }}</td>
                                    <td style="font-weight:500; font-size:13px;">{{ $a['nama'] }}</td>
                                    <td><span class="chip">{{ $tipeLbl }}</span></td>
                                    <td class="num" style="text-align:right; font-weight:600; font-size:13px;">
                                        {{ fmt_rp($a['saldo']) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- =================== GUDANG =================== --}}
        <div x-show="tab === 'gudang'" x-cloak>
            <div class="gudang-grid">
                @foreach ($gudang as $g)
                    <div class="card gudang-card" style="cursor:pointer;"
                         onclick="window.location.href='{{ route('master.gudang.show', $g['kode']) }}'">
                        <div class="gudang-card__hd">
                            <div class="gudang-card__info">
                                <div class="gudang-card__icon">
                                    <x-misc.icon name="building" :size="18" stroke="var(--accent)" />
                                </div>
                                <div>
                                    <div class="gudang-card__name">{{ $g['nama'] }}</div>
                                    <div class="gudang-card__code mono">{{ $g['kode'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="gudang-card__meta">
                            <div class="gudang-card__meta-row">
                                <x-misc.icon name="building" :size="12"
                                    stroke="var(--ink-4)" />{{ $g['kota'] ?? '—' }}
                            </div>
                            @if (!empty($g['PIC']))
                                <div class="gudang-card__meta-row">
                                    <x-misc.icon name="users" :size="12" stroke="var(--ink-4)" />PIC:
                                    {{ $g['PIC'] }}
                                </div>
                            @endif
                            @if (!empty($g['kapasitas']))
                                <div class="gudang-card__meta-row">
                                    <x-misc.icon name="layers" :size="12" stroke="var(--ink-4)" />Kapasitas:
                                    {{ fmt_num($g['kapasitas']) }} unit
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Add new gudang card --}}
                <div class="card gudang-add-card" x-on:click="modal = 'add_gudang'">
                    <div>
                        <x-misc.icon name="plus" :size="24" stroke="var(--ink-3)" />
                        <div class="gudang-add-label">Tambah Gudang</div>
                    </div>
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
                        <input class="input master-search__input" placeholder="Cari user..." />
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
                        <template x-for="u in userPaged" :key="u.id">
                            <tr class="row-tap" style="cursor:pointer;">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="avatar"
                                            :style="'background:' + avatarMeta(u.nama).bg + '; color:' + avatarMeta(u.nama).fg"
                                            x-text="avatarMeta(u.nama).initials"></div>
                                        <div>
                                            <div style="font-weight:600; font-size:13px;" x-text="u.nama"></div>
                                            <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="u.username"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="chip" x-text="u.role"></span></td>
                                <td style="font-size:13px; color:var(--ink-3);" x-text="u.kontak || '—'"></td>
                                <td>
                                    <span class="chip" :style="u.aktif ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
                                        x-text="u.aktif ? 'Aktif' : 'Nonaktif'"></span>
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
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="table-pagination">
                    <span class="pagination-info"
                        x-text="'Menampilkan ' + ((userPage-1)*userPerPage+1) + '–' + Math.min(userPage*userPerPage, userAll.length) + ' dari ' + userAll.length + ' user'"></span>
                    <div class="pagination-controls">
                        <span class="pagination-page-info">Hal. <strong x-text="userPage"></strong> / <strong
                                x-text="userTotalPages"></strong></span>
                        <button class="btn btn-ghost btn-sm" :disabled="userPage <= 1" x-on:click="userPage--">
                            <x-misc.icon name="chev-left" :size="14" /> Prev
                        </button>
                        <button class="btn btn-ghost btn-sm" :disabled="userPage >= userTotalPages"
                            x-on:click="userPage++">
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
                        <input class="input mono" placeholder="cth. TPG-003" />
                    </x-misc.field>
                    <x-misc.field label="Kategori">
                        <input class="input" placeholder="Tepung, Gula, Minyak..." />
                    </x-misc.field>
                </div>
                <x-misc.field label="Nama Produk" :required="true">
                    <input class="input" placeholder="Nama lengkap produk" />
                </x-misc.field>
                <div class="form-grid-3">
                    <x-misc.field label="Satuan">
                        <input class="input" placeholder="Sak, Kg, Liter..." />
                    </x-misc.field>
                    <x-misc.field label="Harga Beli">
                        <input class="input num" style="text-align:right;" placeholder="0" />
                    </x-misc.field>
                    <x-misc.field label="Harga Jual">
                        <input class="input num" style="text-align:right;" placeholder="0" />
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Produk</button>
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
                        <div style="font-weight:700; font-size:14px;" x-text="editProdukData.nama"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editProdukData.kode"></div>
                    </div>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Kode Produk" :required="true">
                        <input class="input mono" x-model="editProdukData.kode" />
                    </x-misc.field>
                    <x-misc.field label="Kategori">
                        <input class="input" x-model="editProdukData.kategori" placeholder="Tepung, Gula, Minyak..." />
                    </x-misc.field>
                </div>
                <x-misc.field label="Nama Produk" :required="true">
                    <input class="input" x-model="editProdukData.nama" />
                </x-misc.field>
                <div class="form-grid-3">
                    <x-misc.field label="Satuan">
                        <input class="input" x-model="editProdukData.satuan" placeholder="Sak, Kg, Liter..." />
                    </x-misc.field>
                    <x-misc.field label="Harga Beli">
                        <input class="input num" type="number" style="text-align:right;" x-model="editProdukData.hargaBeli" />
                    </x-misc.field>
                    <x-misc.field label="Harga Jual">
                        <input class="input num" type="number" style="text-align:right;" x-model="editProdukData.hargaJual" />
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Kontak --}}
        <x-misc.modal title="Tambah Kontak Baru" show="modal === 'add_kontak'" close-handler="modal = null">
            <div class="form-body">
                <x-misc.field label="Nama" :required="true">
                    <input class="input" placeholder="Nama perusahaan / individu" />
                </x-misc.field>
                <div class="form-grid-2">
                    <x-misc.field label="Tipe" :required="true">
                        <select class="input">
                            <option>Customer</option>
                            <option>Vendor</option>
                            <option>Keduanya</option>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Kota">
                        <input class="input" placeholder="Jakarta, Surabaya..." />
                    </x-misc.field>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Email">
                        <input class="input" type="email" placeholder="kontak@perusahaan.com" />
                    </x-misc.field>
                    <x-misc.field label="Telepon">
                        <input class="input" placeholder="08xx-xxxx-xxxx" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Alamat">
                    <textarea class="input" rows="2" placeholder="Alamat lengkap..."></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Kontak</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Edit Kontak --}}
        <x-misc.modal title="Edit Kontak" show="modal === 'edit_kontak'" close-handler="modal = null">
            <div class="form-body">
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:4px;">
                    <div class="avatar" style="width:44px; height:44px; font-size:14px;"
                         :style="'background:' + avatarMeta(editKontakData.nama).bg + '; color:' + avatarMeta(editKontakData.nama).fg"
                         x-text="avatarMeta(editKontakData.nama).initials"></div>
                    <div>
                        <div style="font-weight:700; font-size:14px;" x-text="editKontakData.nama"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editKontakData.id"></div>
                    </div>
                </div>
                <x-misc.field label="Nama" :required="true">
                    <input class="input" x-model="editKontakData.nama" placeholder="Nama perusahaan / individu" />
                </x-misc.field>
                <div class="form-grid-2">
                    <x-misc.field label="Tipe" :required="true">
                        <select class="input" x-model="editKontakData.tipe">
                            <option value="Customer">Customer</option>
                            <option value="Vendor">Vendor</option>
                            <option value="Keduanya">Keduanya</option>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Kota">
                        <input class="input" x-model="editKontakData.kota" placeholder="Jakarta, Surabaya..." />
                    </x-misc.field>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Email">
                        <input class="input" type="email" x-model="editKontakData.email" placeholder="kontak@perusahaan.com" />
                    </x-misc.field>
                    <x-misc.field label="Telepon">
                        <input class="input" x-model="editKontakData.telepon" placeholder="08xx-xxxx-xxxx" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Alamat">
                    <textarea class="input" rows="2" x-model="editKontakData.alamat" placeholder="Alamat lengkap..."></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Akun --}}
        <x-misc.modal title="Tambah Akun Baru" show="modal === 'add_akun'" close-handler="modal = null">
            <div class="form-body">
                <div class="form-grid-1-2">
                    <x-misc.field label="Kode Akun" :required="true">
                        <input class="input mono" placeholder="1-xxx" />
                    </x-misc.field>
                    <x-misc.field label="Nama Akun" :required="true">
                        <input class="input" placeholder="Nama akun" />
                    </x-misc.field>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Tipe Akun">
                        <select class="input" x-model="akunForm.tipe">
                            <option value="">— Pilih Tipe —</option>
                            <option>Aset</option>
                            <option>Liabilitas</option>
                            <option>Ekuitas</option>
                            <option>Pendapatan</option>
                            <option>Beban</option>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Sub Kategori">
                        <select class="input" x-model="akunForm.subKategori">
                            <option value="">— Tidak ada (akun utama) —</option>
                            <template x-for="c in coaAll.filter(c => c.parent === null)" :key="c.kode">
                                <option :value="c.kode" x-text="c.kode + ' – ' + c.nama"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Akun</button>
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
                        <div style="font-weight:700; font-size:14px;" x-text="editAkunData.nama"></div>
                        <div class="mono" style="font-size:11px; color:var(--ink-4);" x-text="editAkunData.kode"></div>
                    </div>
                </div>
                <div class="form-grid-1-2">
                    <x-misc.field label="Kode Akun" :required="true">
                        <input class="input mono" x-model="editAkunData.kode" />
                    </x-misc.field>
                    <x-misc.field label="Nama Akun" :required="true">
                        <input class="input" x-model="editAkunData.nama" />
                    </x-misc.field>
                </div>
                <div class="form-grid-2">
                    <x-misc.field label="Tipe Akun">
                        <select class="input" x-model="editAkunData.tipe">
                            <option value="Aset">Aset</option>
                            <option value="Liabilitas">Liabilitas</option>
                            <option value="Ekuitas">Ekuitas</option>
                            <option value="Pendapatan">Pendapatan</option>
                            <option value="Beban Pokok">Beban Pokok</option>
                            <option value="Beban Operasional">Beban Operasional</option>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Sub Kategori">
                        <select class="input" x-model="editAkunData.subKategori">
                            <option value="">— Tidak ada (akun utama) —</option>
                            <template x-for="c in coaAll.filter(c => c.parent === null)" :key="c.kode">
                                <option :value="c.kode" x-text="c.kode + ' – ' + c.nama"></option>
                            </template>
                        </select>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
            </x-slot:footer>
        </x-misc.modal>

        {{-- Modal: Tambah Gudang --}}
        <x-misc.modal title="Tambah Gudang Baru" show="modal === 'add_gudang'" close-handler="modal = null">
            <div class="form-body">
                <x-misc.field label="Nama Gudang" :required="true">
                    <input class="input" placeholder="Gudang Bekasi, dll." />
                </x-misc.field>
                <x-misc.field label="Kode Gudang" :required="true">
                    <input class="input mono" placeholder="GDG-xxx" />
                </x-misc.field>
                <x-misc.field label="Deskripsi">
                    <textarea class="input" rows="3" placeholder="Keterangan gudang..."></textarea>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Gudang</button>
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
                <x-misc.field label="Kontak / Karyawan">
                    <select class="input" x-model="addUserForm.kontak">
                        <option value="">— Tidak dihubungkan —</option>
                        <template x-for="k in kontakAll" :key="k.id">
                            <option :value="k.nama" x-text="k.nama"></option>
                        </template>
                    </select>
                </x-misc.field>
                <x-misc.field label="Role" :required="true">
                    <select class="input" x-model="addUserForm.role">
                        <option value="">— Pilih Role —</option>
                        <template x-for="r in rolesAll" :key="r.id">
                            <option :value="r.nama" x-text="r.nama"></option>
                        </template>
                    </select>
                </x-misc.field>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan User</button>
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
                        <input class="input" type="password" placeholder="Kosongkan jika tidak diubah" />
                    </x-misc.field>
                </div>
                <x-misc.field label="Kontak / Karyawan">
                    <select class="input" x-model="editUserData.kontak">
                        <option value="">— Tidak dihubungkan —</option>
                        <template x-for="k in kontakAll" :key="k.id">
                            <option :value="k.nama" x-text="k.nama"></option>
                        </template>
                    </select>
                </x-misc.field>
                <div class="form-grid-2">
                    <x-misc.field label="Role" :required="true">
                        <select class="input" x-model="editUserData.role">
                            <template x-for="r in rolesAll" :key="r.id">
                                <option :value="r.nama" x-text="r.nama"></option>
                            </template>
                        </select>
                    </x-misc.field>
                    <x-misc.field label="Status">
                        <select class="input" x-model="editUserData.aktif">
                            <option :value="true">Aktif</option>
                            <option :value="false">Nonaktif</option>
                        </select>
                    </x-misc.field>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" x-on:click="modal = null">Batal</button>
                <button class="btn btn-primary"><x-misc.icon name="check" :size="14" />Simpan Perubahan</button>
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
