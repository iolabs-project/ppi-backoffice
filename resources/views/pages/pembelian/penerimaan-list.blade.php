@extends('layouts.app')
@section('content')
    <div x-data="{
        filter: 'semua',
        page: 1,
        perPage: 10,
        perPageOptions: [10, 25, 50],
        total: {{ count($penerimaan) }},
        setPerPage(n) { this.perPage = n;
            this.page = 1 },
        prev() { if (this.page > 1) this.page-- },
        next() { if (this.page < Math.ceil(this.total / this.perPage)) this.page++ }
    }" class="order-page">

        <div class="order-hd">
            <div>
                <h1 class="order-title display">Penerimaan</h1>
                <div class="order-sub">{{ count($penerimaan) }} catatan · Periode Mei 2026</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
            </div>
        </div>

        {{-- Summary cards --}}
        @php
            $totalNilai = array_sum(array_column($penerimaan, 'nilaiSusut'));
            $totalPending = count(array_filter($penerimaan, fn($s) => $s['status'] === 'pending'));
        @endphp
        {{-- <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px;">
            <div class="card" style="padding: 16px 20px;">
                <div style="font-size: 11px; color: var(--ink-3); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Total Catatan</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--ink-1);">{{ count($penerimaan) }}</div>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;">dokumen penerimaan</div>
            </div>
            <div class="card" style="padding: 16px 20px;">
                <div style="font-size: 11px; color: var(--ink-3); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Nilai Penerimaan</div>
                <div style="font-size: 24px; font-weight: 700; color: #EA580C;">{{ fmt_rp($totalNilai) }}</div>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;">total kerugian</div>
            </div>
            <div class="card" style="padding: 16px 20px;">
                <div style="font-size: 11px; color: var(--ink-3); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Perlu Diproses</div>
                <div style="font-size: 24px; font-weight: 700; color: #CA8A04;">{{ $totalPending }}</div>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;">menunggu tindak lanjut</div>
            </div>
        </div> --}}

        {{-- Filter pills --}}
        <div class="filter-pills">
            @php
                $statuses = [
                    ['id' => 'semua', 'label' => 'Semua'],
                    ['id' => 'draft', 'label' => 'Draft'],
                    ['id' => 'selesai', 'label' => 'Selesai'],
                ];
            @endphp
            @foreach ($statuses as $st)
                @php $cnt = $st['id'] === 'semua' ? count($penerimaan) : count(array_filter($penerimaan, fn($s) => $s['status'] === $st['id'])); @endphp
                <button x-on:click="filter = '{{ $st['id'] }}'; page = 1"
                    :class="filter === '{{ $st['id'] }}' ? 'filter-pill filter-pill--active' : 'filter-pill'">
                    {{ $st['label'] }}<span class="filter-pill__count mono">{{ $cnt }}</span>
                </button>
            @endforeach
            <div class="flex-spacer"></div>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="sort" :size="13" />Tanggal</button>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="filter" :size="13" />Filter</button>
        </div>

        <div class="card table-card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>No. Susut</th>
                        <th>Tanggal</th>
                        <th>Ref. PO</th>
                        <th>Vendor</th>
                        <th>Produk</th>
                        <th style="text-align:right;">Qty Susut</th>
                        <th style="text-align:right;">Nilai Susut</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th class="table-action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penerimaan as $d)
                        <tr x-data="{ idx: {{ $loop->index }}, status: '{{ $d['status'] }}' }"
                            x-show="(filter === 'semua' || filter === status) && idx >= (page-1)*perPage && idx < page*perPage">
                            <td class="mono table-id">{{ $d['id'] }}</td>
                            <td class="table-secondary">{{ $d['tanggal'] }}</td>
                            <td class="mono">
                                <a href="{{ route('pembelian.show', $d['poRef']) }}"
                                    class="link orange-link">{{ $d['poRef'] }}</a>
                            </td>
                            <td>
                                <div class="table-customer-row">
                                    <x-misc.avatar :name="$d['vendor']" />
                                    <span class="table-customer-name">{{ $d['vendor'] }}</span>
                                </div>
                            </td>
                            <td>{{ $d['produk'] }}</td>
                            <td class="num table-numeric" style="color: #EA580C;">
                                {{ $d['qtySusut'] }} {{ $d['satuan'] }}
                            </td>
                            <td class="num table-numeric" style="color: #EA580C;">{{ fmt_rp($d['nilaiSusut']) }}</td>
                            <td class="table-secondary"
                                style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $d['keterangan'] }}</td>
                            <td><x-misc.status-badge :status="$d['status']" /></td>
                            <td class="table-action-col">
                                <div x-data="{ open: false }" class="action-menu">
                                    <button class="btn btn-ghost btn-icon btn-sm btn--borderless"
                                        x-on:click.stop="
                                            let wasOpen = open;
                                            $dispatch('close-menus');
                                            if (!wasOpen) {
                                                let r = $el.getBoundingClientRect();
                                                $refs.panel.style.top = (r.bottom + 6) + 'px';
                                                $refs.panel.style.right = (window.innerWidth - r.right) + 'px';
                                                open = true;
                                            }
                                        ">
                                        <x-misc.icon name="more" :size="15" />
                                    </button>
                                    <div x-ref="panel" x-show="open" x-cloak x-on:close-menus.window="open = false"
                                        x-on:click.away="open = false" class="action-menu__panel">
                                        <a href="{{ route('pembelian.show', $d['poRef']) }}" class="action-menu__item">
                                            <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                            Detail PO
                                        </a>
                                        <button class="action-menu__item">
                                            <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                            Berita Acara
                                        </button>
                                        <button class="action-menu__item">
                                            <x-misc.icon name="edit" :size="14" stroke="var(--ink-3)" />Edit
                                            Catatan
                                        </button>
                                        <div class="action-menu__divider"></div>
                                        <button class="action-menu__item">
                                            <x-misc.icon name="check" :size="14" stroke="var(--ink-3)" />Tandai
                                            Selesai
                                        </button>
                                        <div class="action-menu__divider"></div>
                                        <button class="action-menu__item action-menu__item--danger">
                                            <x-misc.icon name="x" :size="14" stroke="currentColor" />Hapus
                                            Catatan
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            <div class="pagination-actions">
                <div class="pagination-label">Per</div>
                <select x-model.number="perPage" x-on:change="page = 1" class="btn btn-ghost btn-sm pagination-select">
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
                        x-text="Math.ceil(total/perPage)"></strong></div>
                <button class="btn btn-ghost btn-sm" x-on:click="prev()" :disabled="page <= 1"><x-misc.icon
                        name="chev-left" :size="13" /> Prev</button>
                <button class="btn btn-ghost btn-sm" x-on:click="next()" :disabled="page >= Math.ceil(total / perPage)">Next
                    <x-misc.icon name="chev-right" :size="13" /></button>
            </div>
        </div>
    </div>
@endsection
