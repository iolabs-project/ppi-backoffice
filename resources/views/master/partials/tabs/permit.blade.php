<div x-data="permitModule()" x-show="tab === 'permit'" x-cloak>
    <div class="card" style="overflow:hidden;">
        <div class="master-toolbar" style="border-bottom:1px solid var(--border);">
            <div>
                <div style="font-weight:600; font-size:13px;">Matriks Hak Akses per Role</div>
                <div style="font-size:12px; color:var(--ink-4); margin-top:2px;">Centang modul yang dapat diakses
                    oleh setiap role</div>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="tbl" style="min-width:700px;">
                <thead>
                    <tr>
                        <th style="min-width:190px;">Role</th>
                        <template x-for="m in modules" :key="m.key">
                            <th style="text-align:center; width:88px; font-size:12px;" x-text="m.label"></th>
                        </template>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="r in roles" :key="r.id">
                        <tr>
                            <td>
                                <div style="font-weight:600; font-size:13px;" x-text="r.nama"></div>
                                <div style="font-size:11px; color:var(--ink-4); margin-top:1px;"
                                    x-text="r.deskripsi"></div>
                            </td>
                            <template x-for="m in modules" :key="m.key">
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
                                        <button class="action-menu__item"
                                            x-on:click="$dispatch('open-edit-role', r)">
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
