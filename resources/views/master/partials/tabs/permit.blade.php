<div x-data="permitModule()" x-show="tab === 'permit'" x-cloak>
    <div style="display:grid; grid-template-columns:280px 1fr; gap:0; min-height:400px; align-items:start;">

        {{-- ── Left panel: Roles list ──────────────────────────────── --}}
        <div class="card" style="display:flex; flex-direction:column; border-right:1px solid var(--border); border-radius:var(--radius) 0 0 var(--radius);">
            <div class="master-toolbar" style="justify-content:space-between; flex-shrink:0;">
                <div style="font-weight:600; font-size:13px;">Daftar Role</div>
                <button class="btn btn-primary btn-sm" x-on:click="openCreateModal()">
                    <x-misc.icon name="plus" :size="14" /> Tambah
                </button>
            </div>
            <div>
                <template x-if="roles.length === 0">
                    <div style="padding:24px; text-align:center; color:var(--ink-4); font-size:13px;">Belum ada role</div>
                </template>
                <template x-for="r in roles" :key="r.id">
                    <div x-on:click="selectRole(r)"
                        style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; cursor:pointer; border-bottom:1px solid var(--border); transition:background .1s;"
                        :style="selectedRole && selectedRole.id === r.id ? 'background:var(--bg-2);' : ''">
                        <div>
                            <div style="font-weight:600; font-size:13px;" x-text="r.name"></div>
                            <div style="font-size:11px; color:var(--ink-4); margin-top:1px;"
                                x-text="r.permissions.length + ' hak akses'"></div>
                        </div>
                        <div x-on:click.stop>
                            <div class="action-menu" x-data="{ open: false }">
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    x-on:click="open = !open" x-on:click.outside="open = false">
                                    <x-misc.icon name="more" :size="15" />
                                </button>
                                <div class="action-menu__panel" x-show="open" x-cloak x-on:click="open = false"
                                    style="position:absolute; right:0; top:100%; margin-top:4px; z-index:50;">
                                    <button class="action-menu__item" x-on:click="openEditModal(r)">
                                        <x-misc.icon name="edit" :size="14" /> Edit Nama Role
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── Right panel: Permission tree ────────────────────────── --}}
        <div class="card" style="display:flex; flex-direction:column; border-radius:0 var(--radius) var(--radius) 0;">
            <template x-if="!selectedRole">
                <div style="padding:48px; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:8px; color:var(--ink-4);">
                    <x-misc.icon name="users" :size="32" stroke="var(--ink-4)" />
                    <div style="font-size:13px;">Pilih role untuk mengatur hak akses</div>
                </div>
            </template>
            <template x-if="selectedRole">
                <div style="display:flex; flex-direction:column;">
                    <div class="master-toolbar" style="justify-content:space-between; flex-shrink:0;">
                        <div>
                            <div style="font-weight:700; font-size:14px;" x-text="selectedRole.name"></div>
                            <div style="font-size:12px; color:var(--ink-4); margin-top:2px;">Atur hak akses untuk role ini</div>
                        </div>
                        <button class="btn btn-primary btn-sm" :disabled="saving"
                            x-on:click="savePermissions()">
                            <template x-if="!saving"><x-misc.icon name="check" :size="14" /></template>
                            <span x-text="saving ? 'Menyimpan...' : 'Simpan Hak Akses'"></span>
                        </button>
                    </div>

                    {{-- Permission tree --}}
                    <div style="padding:16px; display:flex; flex-direction:column; gap:10px;">
                        <template x-for="[mKey, module] in Object.entries(permissionTree)" :key="mKey">
                            <div style="border:1px solid var(--border); border-radius:8px; overflow:hidden;">
                                {{-- Module header --}}
                                <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--bg-1); cursor:pointer; user-select:none;"
                                    x-on:click="toggleModule(mKey)">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <input type="checkbox"
                                            style="width:15px; height:15px; cursor:pointer; accent-color:var(--accent); flex-shrink:0;"
                                            :checked="isModuleChecked(mKey)"
                                            x-effect="$el.indeterminate = isModuleIndeterminate(mKey)"
                                            x-on:click.stop
                                            x-on:change="toggleModuleAll(mKey, $event.target.checked)" />
                                        <span style="font-weight:700; font-size:13px; text-transform:capitalize;" x-text="moduleLabel(mKey)"></span>
                                    </div>
                                    <x-misc.icon name="chevron-down" :size="14" stroke="var(--ink-3)"
                                        x-bind:style="expandedModules[mKey] ? 'transform:rotate(180deg); transition:.2s' : 'transition:.2s'" />
                                </div>
                                {{-- Resources --}}
                                <div x-show="expandedModules[mKey]">
                                    <template x-for="[rKey, actions] in Object.entries(module)" :key="rKey">
                                        <div style="padding:8px 14px 8px 38px; display:flex; align-items:center; justify-content:space-between; border-top:1px solid var(--border); background:var(--bg-0);">
                                            <div style="font-size:13px; font-weight:500; color:var(--ink-2); min-width:160px; text-transform:capitalize;"
                                                x-text="resourceLabel(rKey)"></div>
                                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                                <template x-for="action in actions" :key="action">
                                                    <label style="display:flex; align-items:center; gap:5px; padding:3px 10px; border-radius:99px; border:1px solid var(--border); cursor:pointer; font-size:12px; user-select:none;"
                                                        :style="hasPermission(mKey, rKey, action) ? 'background:oklch(0.92 0.06 145); border-color:oklch(0.75 0.12 145); color:oklch(0.38 0.12 145);' : 'background:var(--bg-1); color:var(--ink-4);'">
                                                        <input type="checkbox"
                                                            style="width:13px; height:13px; accent-color:var(--accent);"
                                                            :checked="hasPermission(mKey, rKey, action)"
                                                            x-on:change="togglePermission(mKey, rKey, action)" />
                                                        <span x-text="actionLabel(action)"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @include('master.partials.modals.permit-modal')
</div>
