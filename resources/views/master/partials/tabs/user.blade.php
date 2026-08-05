<div x-data="userModule()" x-init="fetchData()" x-show="tab === 'user'" x-cloak>
    <div class="card" style="overflow:hidden;">
        <div class="master-toolbar">
            <div class="master-search">
                <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                        stroke="var(--ink-4)" /></span>
                <input class="input master-search__input" placeholder="Cari user..." x-model="search"
                    x-on:input.debounce.400ms="page = 1; fetchData()" />
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
                <template x-for="u in tableData.data" :key="u.id">
                    <tr class="row-tap" style="cursor:pointer;">
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="avatar"
                                    :style="'background:' + $root.__x.$data.avatarMeta(userName(u)).bg + '; color:' + $root.__x.$data.avatarMeta(userName(u)).fg"
                                    x-text="$root.__x.$data.avatarMeta(userName(u)).initials"></div>
                                <div>
                                    <div style="font-weight:600; font-size:13px;" x-text="userName(u)"></div>
                                    <div class="mono" style="font-size:11px; color:var(--ink-4);"
                                        x-text="u.username"></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="chip" x-text="u.role_name || '—'"></span></td>
                        <td style="font-size:13px; color:var(--ink-3);"
                            x-text="u.contact ? u.contact.name : '—'"></td>
                        <td>
                            <span class="chip"
                                :style="!u.deleted_at ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
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
                                    <button class="action-menu__item" x-on:click="$dispatch('open-edit-user', u)">
                                        <x-misc.icon name="edit" :size="14" /> Edit User
                                    </button>
                                    <button class="action-menu__item" x-on:click="handleStatus(u)">
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
                x-text="(tableData.total === 0 ? 0 : ((tableData.current_page - 1) * tableData.per_page + 1)) + '–' + (tableData.total === 0 ? 0 : Math.min(tableData.current_page * tableData.per_page, tableData.total)) + ' dari ' + tableData.total + ' user'"></span>
            <div class="pagination-controls">
                <span class="pagination-page-info">Hal. <strong x-text="tableData.current_page"></strong> /
                    <strong x-text="tableData.last_page"></strong></span>
                <button class="btn btn-ghost btn-sm" :disabled="tableData.current_page <= 1" x-on:click="prev()">
                    <x-misc.icon name="chev-left" :size="14" /> Prev
                </button>
                <button class="btn btn-ghost btn-sm"
                    :disabled="tableData.current_page >= tableData.last_page" x-on:click="next()">
                    Next <x-misc.icon name="chev-right" :size="14" />
                </button>
            </div>
        </div>
    </div>
</div>
