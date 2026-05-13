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
                    ['id' => 'pending', 'label' => 'Pending'],
                    ['id' => 'dikirim', 'label' => 'Dikirim'],
                    ['id' => 'tagihan', 'label' => 'Tagihan'],
                    ['id' => 'lunas', 'label' => 'Lunas'],
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
                                <button class="btn btn-ghost btn-icon btn-sm btn--borderless"><x-misc.icon name="more"
                                        :size="15" /></button>
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
                <span x-text="( (page-1)*perPage + 1 ) + '–' + Math.min(page*perPage, total) + ' dari ' + total"></span>
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
