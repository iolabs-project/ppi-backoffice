<div x-data="gudangModule()" x-init="fetchData()" x-show="tab === 'gudang'" x-cloak>
    <div class="master-toolbar" style="padding-left:0; padding-right:0;">
        <div class="master-search">
            <span class="master-search__icon"><x-misc.icon name="search" :size="14"
                    stroke="var(--ink-4)" /></span>
            <input class="input master-search__input" placeholder="Cari gudang..." x-model="search"
                x-on:input.debounce.400ms="page = 1; fetchData()" />
        </div>
    </div>
    <template x-if="loading">
        <div style="text-align:center; color:var(--ink-3); padding:20px;">Memuat data...</div>
    </template>
    <div class="gudang-grid" x-show="!loading">
        <template x-for="g in tableData.data" :key="g.id">
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
                            <button class="action-menu__item" x-on:click="$dispatch('open-edit-gudang', g)">
                                <x-misc.icon name="edit" :size="14" /> Edit Gudang
                            </button>
                            <button class="action-menu__item" x-on:click="handleStatus(g)">
                                <x-misc.icon name="x" :size="14" />
                                <span x-text="g.deleted_at ? 'Aktifkan' : 'Nonaktifkan'"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="gudang-card__meta">
                    <div class="gudang-card__meta-row">
                        <x-misc.icon name="building" :size="12" stroke="var(--ink-4)" />
                        <span x-text="g.address || '—'"></span>
                    </div>
                    <div class="gudang-card__meta-row" x-show="g.note">
                        <x-misc.icon name="layers" :size="12" stroke="var(--ink-4)" />
                        <span x-text="g.note"></span>
                    </div>
                    <div class="gudang-card__meta-row">
                        <span class="chip"
                            :style="!g.deleted_at ? 'background:oklch(0.92 0.06 145);color:oklch(0.40 0.12 145)' : 'background:oklch(0.92 0.04 15);color:oklch(0.45 0.14 15)'"
                            x-text="!g.deleted_at ? 'Aktif' : 'Nonaktif'"></span>
                    </div>
                </div>
            </div>
        </template>

        <div class="card gudang-add-card" x-on:click="modal = 'add_gudang'">
            <div>
                <x-misc.icon name="plus" :size="24" stroke="var(--ink-3)" />
                <div class="gudang-add-label">Tambah Gudang</div>
            </div>
        </div>
    </div>
    <div class="table-pagination" x-show="!loading">
        <span class="pagination-info"
            x-text="(tableData.total === 0 ? 0 : ((tableData.current_page - 1) * tableData.per_page + 1)) + '–' + (tableData.total === 0 ? 0 : Math.min(tableData.current_page * tableData.per_page, tableData.total)) + ' dari ' + tableData.total + ' gudang'"></span>
        <div class="pagination-controls">
            <span class="pagination-page-info">Hal. <strong x-text="tableData.current_page"></strong> / <strong
                    x-text="tableData.last_page"></strong></span>
            <button class="btn btn-ghost btn-sm" :disabled="tableData.current_page <= 1" x-on:click="prev()">
                <x-misc.icon name="chev-left" :size="14" /> Prev
            </button>
            <button class="btn btn-ghost btn-sm" :disabled="tableData.current_page >= tableData.last_page"
                x-on:click="next()">
                Next <x-misc.icon name="chev-right" :size="14" />
            </button>
        </div>
    </div>
</div>
