<div x-data="accountModule()" x-init="fetchData()" x-show="tab === 'akun'" x-cloak>
    <div class="card" style="overflow:hidden;">
        <div class="master-toolbar" style="justify-content: space-between">
            <div class="master-search">
                <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                        stroke="var(--ink-4)" /></span>
                <input class="input master-search__input" placeholder="Cari kontak..." x-model="search"
                    x-on:input.debounce.400ms="handleSearch(search)" />
            </div>
            <button class="btn btn-primary btn-sm" x-on:click="modal = 'add_account'">
                <x-misc.icon name="plus" :size="14" /> Tambah Akun
            </button>
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
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...</td>
                    </tr>
                </template>
            </tbody>

                <template x-if="!loading && Object.keys(tableData.data).length === 0">
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--ink-3); padding:20px;">Tidak ada data
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && Object.keys(tableData.data).length > 0">
                    <template x-for="[groupName, items] in Object.entries(tableData.data)" :key="groupName">
                        <tbody>
                            <tr class="coa-group-row">
                                <td colspan="6" x-text="groupName"></td>
                            </tr>
                            <template x-for="item in items" :key="item.id">
                                <tr>
                                    <td style="font-weight:600; font-size:12px; color:var(--ink-4);" x-text="item.code">
                                    </td>
                                    <td style="font-weight:600; font-size:13px;" x-text="item.name"></td>
                                    <td><span class="chip" x-text="item.category ? item.category.name : '—'"></span></td>
                                    <td style="color:var(--ink-3); font-size:13px;" x-text="item.note || '—'"></td>
                                    <td>
                                        <template x-if="item.deleted_at">
                                            <x-misc.status-badge status="inactive" />
                                        </template>
                                        <template x-if="!item.deleted_at">
                                            <x-misc.status-badge status="active" />
                                        </template>
                                    </td>
                                    <td x-on:click.stop>
                                        <div class="action-menu" x-data="{ open: false }">
                                            <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                                x-on:click="open = !open" x-on:click.outside="open = false">
                                                <x-misc.icon name="more" :size="15" />
                                            </button>
                                            <div class="action-menu__panel" x-show="open" x-cloak
                                                x-on:click="open = false"
                                                style="position:absolute; right:0; top:100%; margin-top:4px;">
                                                <button class="action-menu__item" x-on:click="openEditModal(item)">
                                                    <x-misc.icon name="edit" :size="14" /> Edit Akun
                                                </button>
                                                <template x-if="!item.deleted_at">
                                                    <button class="action-menu__item action-menu__item--danger"
                                                        x-on:click="handleStatus(item.id)">
                                                        <x-misc.icon name="trash" :size="14" /> Nonaktifkan
                                                    </button>
                                                </template>
                                                <template x-if="item.deleted_at">
                                                    <button class="action-menu__item"
                                                        x-on:click="handleStatus(item.id)">
                                                        <x-misc.icon name="refresh" :size="14" /> Aktifkan
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </template>
                </template>
            </tbody>
        </table>
    </div>

    @include('master.partials.modals.account-modal')
</div>
