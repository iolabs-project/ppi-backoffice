<div x-data="permitModule()" x-show="tab === 'permit'" x-cloak>
    <div class="permit-grid">

        {{-- ── Left panel: Roles list ──────────────────────────────── --}}
        <div class="card permit-roles">
            <div class="master-toolbar" style="justify-content:space-between; flex-shrink:0;">
                <div>
                    <div style="font-weight:700; font-size:14px;">Daftar Role</div>
                    <div style="font-size:11.5px; color:var(--ink-4); margin-top:2px;"
                        x-text="roles.length + ' role terdaftar'"></div>
                </div>
                <button class="btn btn-primary btn-sm" x-on:click="openCreateModal()">
                    <x-misc.icon name="plus" :size="14" /> Tambah
                </button>
            </div>
            <div class="permit-roles__list">
                <template x-if="roles.length === 0">
                    <div class="permit-roles__empty">Belum ada role</div>
                </template>
                <template x-for="r in roles" :key="r.id">
                    <div class="role-item" x-on:click="selectRole(r)"
                        :class="selectedRole && selectedRole.id === r.id ? 'role-item--active' : ''">
                        <div class="role-item__avatar" x-text="r.name.charAt(0).toUpperCase()"></div>
                        <div class="role-item__body">
                            <div class="role-item__name" x-text="r.name"></div>
                            <div class="role-item__count" x-text="r.permissions.length + ' hak akses'"></div>
                        </div>
                        <div x-on:click.stop>
                            <div class="action-menu" x-data="{ open: false }">
                                <button class="btn btn-ghost btn-icon btn-sm" style="border:none;"
                                    x-on:click="open = !open" x-on:click.outside="open = false">
                                    <x-misc.icon name="more" :size="15" />
                                </button>
                                <div class="action-menu__panel role-item__menu" x-show="open" x-cloak
                                    x-on:click="open = false">
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
        <div class="card permit-detail">
            <template x-if="!selectedRole">
                <div class="permit-empty">
                    <div class="permit-empty__icon">
                        <x-misc.icon name="users" :size="26" stroke="var(--ink-4)" />
                    </div>
                    <div class="permit-empty__text">Pilih role untuk mengatur hak akses</div>
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
                    <div class="permit-tree">
                        <template x-for="[mKey, module] in Object.entries(permissionTree)" :key="mKey">
                            <div class="perm-module">
                                {{-- Module header --}}
                                <div class="perm-module__header" x-on:click="toggleModule(mKey)">
                                    <div class="perm-module__title-group">
                                        <input type="checkbox"
                                            style="width:15px; height:15px; cursor:pointer; accent-color:var(--accent); flex-shrink:0;"
                                            :checked="isModuleChecked(mKey)"
                                            x-effect="$el.indeterminate = isModuleIndeterminate(mKey)"
                                            x-on:click.stop
                                            x-on:change="toggleModuleAll(mKey, $event.target.checked)" />
                                        <div class="perm-module__icon">
                                            <template x-if="moduleIcon(mKey) === 'wallet'"><x-misc.icon name="wallet" :size="15" /></template>
                                            <template x-if="moduleIcon(mKey) === 'database'"><x-misc.icon name="database" :size="15" /></template>
                                            <template x-if="moduleIcon(mKey) === 'cart'"><x-misc.icon name="cart" :size="15" /></template>
                                            <template x-if="moduleIcon(mKey) === 'trend'"><x-misc.icon name="trend" :size="15" /></template>
                                            <template x-if="moduleIcon(mKey) === 'layers'"><x-misc.icon name="layers" :size="15" /></template>
                                        </div>
                                        <span class="perm-module__title" x-text="moduleLabel(mKey)"></span>
                                    </div>
                                    <div class="perm-module__meta">
                                        <span class="perm-module__count"
                                            x-text="moduleActiveCount(mKey) + '/' + moduleTotalCount(mKey) + ' aktif'"></span>
                                        <span class="perm-module__chevron" :class="expandedModules[mKey] ? 'perm-module__chevron--open' : ''">
                                            <x-misc.icon name="chev-down" :size="14" stroke="var(--ink-3)" />
                                        </span>
                                    </div>
                                </div>
                                {{-- Resources --}}
                                <div x-show="expandedModules[mKey]">
                                    <template x-for="[rKey, actions] in Object.entries(module)" :key="rKey">
                                        <div class="perm-resource-row">
                                            <div class="perm-resource__label" x-text="resourceLabel(rKey)"></div>
                                            <div class="perm-actions">
                                                <template x-for="action in actions" :key="action">
                                                    <label class="perm-action-chip"
                                                        :class="hasPermission(mKey, rKey, action) ? (action === 'delete' ? 'perm-action-chip--danger' : 'perm-action-chip--active') : ''">
                                                        <input type="checkbox"
                                                            :checked="hasPermission(mKey, rKey, action)"
                                                            x-on:change="togglePermission(mKey, rKey, action)" />
                                                        <template x-if="action === 'create'"><x-misc.icon name="plus" :size="12" :sw="2" /></template>
                                                        <template x-if="action === 'delete'"><x-misc.icon name="trash" :size="12" :sw="2" /></template>
                                                        <template x-if="action === 'edit'"><x-misc.icon name="edit" :size="12" :sw="2" /></template>
                                                        <template x-if="action === 'view'"><x-misc.icon name="eye" :size="12" :sw="2" /></template>
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
