@extends('layouts.app')
@section('content')
    <div x-data="{
        filter: 'semua',
        page: 1,
        perPage: 10,
        perPageOptions: [10, 25, 50],
        total: {{ count($salesOrders) }},
        setPerPage(n) {
            this.perPage = n;
            this.page = 1
        },
        prev() { if (this.page > 1) this.page-- },
        next() { if (this.page < Math.ceil(this.total / this.perPage)) this.page++ }
    }" class="order-page">

        <div class="order-hd">
            <div>
                <h1 class="order-title display">Sales Order</h1>
                <div class="order-sub">{{ count($salesOrders) }} dokumen · Periode Mei 2026</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
                <a href="{{ route('penjualan.create') }}" class="btn btn-primary"><x-misc.icon name="plus"
                        :size="15" />Tambah SO</a>
            </div>
        </div>

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
                @php $cnt = $st['id'] === 'semua' ? count($salesOrders) : count(array_filter($salesOrders, fn($s) => $s['status'] === $st['id'])); @endphp
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
                        <th>Nomor SO</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Gudang</th>
                        <th>Jatuh Tempo</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="table-action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrders as $s)
                        <tr class="row-tap" x-data="{ idx: {{ $loop->index }}, status: '{{ $s['status'] }}' }"
                            x-show="(filter === 'semua' || filter === status) && idx >= (page-1)*perPage && idx < page*perPage"
                            x-on:click="window.location='{{ route('penjualan.show', $s['id']) }}'">
                            <td class="mono table-id">{{ $s['id'] }}</td>
                            <td class="table-secondary">{{ $s['tanggal'] }}</td>
                            <td>
                                <div class="table-customer-row">
                                    <x-misc.avatar :name="$s['customer']" />
                                    <span class="table-customer-name">{{ $s['customer'] }}</span>
                                </div>
                            </td>
                            <td class="table-secondary">{{ $s['gudang'] }}</td>
                            <td class="table-secondary">{{ $s['jatuhTempo'] }}</td>
                            <td class="num table-numeric">{{ fmt_rp($s['total']) }}</td>
                            <td><x-misc.status-badge :status="$s['status']" /></td>
                            <td class="table-action-col" x-on:click.stop>
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
                                        @if ($s['status'] === 'draft')
                                            <a href="{{ route('penjualan.edit', $s['id']) }}" class="action-menu__item">
                                                <x-misc.icon name="edit" :size="14" stroke="var(--ink-3)" />Edit
                                                Draft
                                            </a>
                                            <button class="action-menu__item">
                                                <x-misc.icon name="check" :size="14"
                                                    stroke="var(--ink-3)" />Konfirmasi SO
                                            </button>
                                            <button class="action-menu__item">
                                                <x-misc.icon name="print" :size="14"
                                                    stroke="var(--ink-3)" />Pratinjau
                                            </button>
                                            <div class="action-menu__divider"></div>
                                            <button class="action-menu__item action-menu__item--danger">
                                                <x-misc.icon name="trash" :size="14" stroke="currentColor" />Hapus
                                                Draft
                                            </button>
                                        @else
                                            <a href="{{ route('penjualan.show', $s['id']) }}" class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                Detail
                                            </a>
                                            <a href="{{ route('penjualan.pengiriman', $s['id']) }}"
                                                class="action-menu__item">
                                                <x-misc.icon name="truck" :size="14" stroke="var(--ink-3)" />Buat
                                                Pengiriman
                                            </a>
                                            <button class="action-menu__item">
                                                <x-misc.icon name="receipt" :size="14" stroke="var(--ink-3)" />Buat
                                                Tagihan
                                            </button>
                                            <button class="action-menu__item">
                                                <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                                SO
                                            </button>
                                            <div class="action-menu__divider"></div>
                                            <button class="action-menu__item action-menu__item--danger">
                                                <x-misc.icon name="x" :size="14"
                                                    stroke="currentColor" />Batalkan SO
                                            </button>
                                        @endif
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
