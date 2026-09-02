@extends('layouts.app')
@section('content')
    <div x-data="{
        filter: 'semua',
        page: 1,
        perPage: 10,
        perPageOptions: [10, 25, 50],
        total: {{ count($purchaseOrders) }},
        setPerPage(n) { this.perPage = n;
            this.page = 1 },
        prev() { if (this.page > 1) this.page-- },
        next() { if (this.page < Math.ceil(this.total / this.perPage)) this.page++ }
    }" class="order-page">
        <div class="order-hd">
            <div>
                <h1 class="order-title display">Purchase Order</h1>
                <div class="order-sub">{{ count($purchaseOrders) }} dokumen · Periode Mei 2026</div>
            </div>
            <div class="order-actions">
                <button class="btn btn-ghost"><x-misc.icon name="download" :size="14" />Ekspor</button>
                <a href="{{ route('pembelian.create') }}" class="btn btn-primary"><x-misc.icon name="plus"
                        :size="15" />Tambah PO</a>
            </div>
        </div>

        <div class="filter-pills">
            @php $statuses = [['semua','Semua'],['draft','Draft'],['disetujui','Disetujui'],['selesai','Selesai']]; @endphp
            @foreach ($statuses as [$id, $lbl])
                @php $cnt = $id === 'semua' ? count($purchaseOrders) : count(array_filter($purchaseOrders, fn($s) => $s['status'] === $id)); @endphp
                <button x-on:click="filter = '{{ $id }}'; page = 1"
                    :class="filter === '{{ $id }}' ? 'filter-pill filter-pill--active' : 'filter-pill'">
                    {{ $lbl }}<span class="filter-pill__count mono">{{ $cnt }}</span>
                </button>
            @endforeach
            <div style="flex:1;"></div>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="sort" :size="13" />Tanggal</button>
            <button class="btn btn-ghost btn-sm"><x-misc.icon name="filter" :size="13" />Filter</button>
        </div>

        <div class="card table-card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th>Gudang</th>
                        <th>Jatuh Tempo</th>
                        <th style="text-align:right;">Total</th>
                        <th>Status</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrders as $s)
                        <tr class="row-tap" x-data="{ idx: {{ $loop->index }}, status: '{{ $s['status'] }}' }"
                            x-show="(filter === 'semua' || filter === status) && idx >= (page-1)*perPage && idx < page*perPage"
                            x-on:click="window.location='{{ route('pembelian.show', $s['id']) }}'">
                            <td class="mono" style="font-weight:600;">{{ $s['id'] }}</td>
                            <td style="color:var(--ink-3);">{{ $s['tanggal'] }}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <x-misc.avatar :name="$s['vendor']" />
                                    <span style="font-weight:500;">{{ $s['vendor'] }}</span>
                                </div>
                            </td>
                            <td style="color:var(--ink-3);">{{ $s['gudang'] }}</td>
                            <td style="color:var(--ink-3);">{{ $s['jatuhTempo'] }}</td>
                            <td class="num" style="text-align:right; font-weight:600;">{{ fmt_rp($s['total']) }}</td>
                            <td><x-misc.status-badge :status="$s['status']" /></td>
                            <td x-on:click.stop>
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
                                            <a href="{{ route('pembelian.edit', $s['id']) }}" class="action-menu__item">
                                                <x-misc.icon name="edit" :size="14" stroke="var(--ink-3)" />Edit
                                                Draft
                                            </a>
                                            <button class="action-menu__item">
                                                <x-misc.icon name="check" :size="14"
                                                    stroke="var(--ink-3)" />Konfirmasi PO
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
                                        @elseif ($s['status'] === 'disetujui')
                                            <a href="{{ route('pembelian.show', $s['id']) }}" class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                Detail
                                            </a>
                                            <a href="{{ route('pembelian.penerimaan', $s['id']) }}"
                                                class="action-menu__item">
                                                <x-misc.icon name="box" :size="14" stroke="var(--ink-3)" />Buat
                                                Penerimaan
                                            </a>
                                            <a href="{{ route('pembelian.tagihan', $s['id']) }}" class="action-menu__item">
                                                <x-misc.icon name="receipt" :size="14" stroke="var(--ink-3)" />Buat
                                                Tagihan
                                            </a>
                                            <button class="action-menu__item">
                                                <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                                PO
                                            </button>
                                            <div class="action-menu__divider"></div>
                                            <button class="action-menu__item action-menu__item--danger">
                                                <x-misc.icon name="x" :size="14"
                                                    stroke="currentColor" />Batalkan PO
                                            </button>
                                        @else
                                            <a href="{{ route('pembelian.show', $s['id']) }}" class="action-menu__item">
                                                <x-misc.icon name="eye" :size="14" stroke="var(--ink-3)" />Lihat
                                                Detail
                                            </a>
                                            <button class="action-menu__item">
                                                <x-misc.icon name="print" :size="14" stroke="var(--ink-3)" />Cetak
                                                PO
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
                <template x-if="total === 0">
                    <span x-text="'0 dari 0'"></span>
                </template>
                <template x-if="total > 0">
                    <span
                        x-text="( (page-1)*perPage + 1 ) + '-' + Math.min(page*perPage, total) + ' dari ' + total"></span>
                </template>
            </div>
            <div class="pagination-controls">
                <div class="pagination-page-info">Halaman <strong x-text="page"></strong> / <strong
                        x-text="Math.ceil(total/perPage)"></strong></div>
                <button class="btn btn-ghost btn-sm" x-on:click="prev()" :disabled="page <= 1"><x-misc.icon
                        name="chev-left" :size="13" /> Prev</button>
                <button class="btn btn-ghost btn-sm" x-on:click="next()"
                    :disabled="page >= Math.ceil(total / perPage)">Next <x-misc.icon name="chev-right"
                        :size="13" /></button>
            </div>
        </div>
    </div>
@endsection
