{{-- =================== LOG AKTIVITAS =================== --}}
<div class="card" style="overflow:hidden;" x-data="activityLogModule()">
    <div class="card-hd">
        <div class="display card-hd-title">Log Aktivitas</div>
        <div class="order-actions" style="display:flex; align-items:center; gap:8px;">
            <label style="font-size:12px; color:var(--ink-3);">Dari</label>
            <input type="date" class="filter-panel__input" x-model="filter.start_date"
                x-on:change="page = 1; fetchData()" style="height:28px; font-size:12px;">
            <label style="font-size:12px; color:var(--ink-3);">Sampai</label>
            <input type="date" class="filter-panel__input" x-model="filter.end_date"
                x-on:change="page = 1; fetchData()" style="height:28px; font-size:12px;">
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="print" :size="13" />Cetak</button>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="download" :size="13" />Ekspor</button>
        </div>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:160px;">Tanggal</th>
                <th style="width:180px;">No. Ref</th>
                <th style="width:180px;">Pengguna</th>
                <th style="width:180px;">Aksi</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <template x-if="loading">
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--ink-3); padding:32px;">
                        Memuat data...
                    </td>
                </tr>
            </tbody>
        </template>
        <template x-if="!loading && tableData.data.length === 0">
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--ink-3); padding:32px;">
                        Tidak ada data
                    </td>
                </tr>
            </tbody>
        </template>
        <template x-if="!loading && tableData.data.length > 0">
            <tbody>
                <template x-for="log in tableData.data" :key="log.id">
                    <tr>
                        <td style="white-space:nowrap; font-size:12.5px; color:var(--ink-3); font-weight:500;"
                            x-text="formatDateTime(log.created_at)"></td>
                            <td>
                                <span class="mono"
                                    style="display:inline-flex; align-items:center; min-height:24px; padding:0 8px; border:1px solid var(--line); border-radius:var(--r-chip); font-size:11.5px; font-weight:600; color:var(--accent); background:var(--accent-3);"
                                    :style="log.subject && log.subject.number ? {} : { color: 'var(--ink-4)', background: 'var(--bg-2)' }"
                                    x-text="log.subject && log.subject.number ? log.subject.number : '—'"></span>
                            </td>
                        <td style="font-size:13px; color:var(--ink-2); font-weight:600;"
                            x-text="log.causer ? log.causer.username : '-'"></td>
                            <td>
                                <span class="chip" :class="{
                                    'chip-ok': log.event === 'created',
                                    'chip-info': log.event === 'updated',
                                    'chip-bad': log.event === 'deleted'
                                }">
                                    <span class="chip-dot" :class="{
                                        'dot-ok': log.event === 'created',
                                        'dot-info': log.event === 'updated',
                                        'dot-bad': log.event === 'deleted'
                                    }"></span>
                                    <span x-text="eventLabel(log.event)"></span>
                                </span>
                            </td>
                        <td style="font-size:13px; color:var(--ink-2);"
                            x-text="log.description"></td>
                    </tr>
                </template>
            </tbody>
        </template>
    </table>

    <div class="table-pagination">
        <div class="pagination-actions">
            <div class="pagination-label">Per</div>
            <select x-model.number="perPage" x-on:change="page = 1; fetchData()" class="btn btn-ghost btn-sm pagination-select">
                <template x-for="n in perPageOptions" :key="n">
                    <option :value="n" x-text="n"></option>
                </template>
            </select>
        </div>
        <div class="pagination-info">
            <template x-if="tableData.total === 0">
                <span x-text="'0 dari 0'"></span>
            </template>
            <template x-if="tableData.total > 0">
                <span
                    x-text="( (page-1)*perPage + 1 ) + '-' + Math.min(page*perPage, tableData.total) + ' dari ' + tableData.total"></span>
            </template>
        </div>
        <div class="pagination-controls">
            <div class="pagination-page-info">Halaman <strong x-text="page"></strong> / <strong
                    x-text="Math.ceil(tableData.total/tableData.per_page)"></strong></div>
            <button class="btn btn-ghost btn-sm" x-on:click="prev()" :disabled="page <= 1"><x-misc.icon
                    name="chev-left" :size="13" /> Prev</button>
            <button class="btn btn-ghost btn-sm" x-on:click="next()"
                :disabled="page >= Math.ceil(tableData.total / tableData.per_page)">Next
                <x-misc.icon name="chev-right" :size="13" /></button>
        </div>
    </div>
</div>
@push('activity-log-scripts')
    <script>
        function activityLogModule() {
            return {
                tableData: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    prev_page_url: null,
                    next_page_url: null,
                    data: [],
                },
                loading: false,
                perPageOptions: [10, 25, 50],
                page: 1,
                perPage: 10,
                filter: {
                    start_date: '{{ now()->startOfMonth()->format('Y-m-d') }}',
                    end_date: '{{ now()->endOfMonth()->format('Y-m-d') }}',
                },

                formatDateTime(dt) {
                    if (!dt) return '-';
                    const d = new Date(dt);
                    const day = String(d.getDate()).padStart(2, '0');
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const year = d.getFullYear();
                    const hours = String(d.getHours()).padStart(2, '0');
                    const minutes = String(d.getMinutes()).padStart(2, '0');
                    return `${day}/${month}/${year} ${hours}:${minutes}`;
                },

                eventLabel(event) {
                    return {
                        created: 'Dibuat',
                        updated: 'Diperbarui',
                        deleted: 'Dihapus',
                    }[event] || '-';
                },

                async init() {
                    this.loading = true;
                    await this.fetchData();
                    this.loading = false;
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const r = await axios.get(route('reports.activity_log.datatable'), {
                            params: {
                                page: this.page,
                                per_page: this.perPage,
                                start_date: this.filter.start_date,
                                end_date: this.filter.end_date,
                            }
                        });
                        this.tableData = r.data;
                    } catch {
                        Toast.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan saat memuat data.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },

                async next() {
                    if (this.tableData && this.page < this.tableData.last_page) {
                        this.page++;
                        await this.fetchData();
                    }
                },
                async prev() {
                    if (this.tableData && this.page > 1) {
                        this.page--;
                        await this.fetchData();
                    }
                }
            }
        }
    </script>
@endpush
