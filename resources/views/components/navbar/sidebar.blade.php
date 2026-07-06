@props(['currentPage' => 'dashboard'])
@php
    $penjualanActive = str_starts_with($currentPage, 'penjualan');
    $pembelianActive = str_starts_with($currentPage, 'pembelian');
    $penjualanSubmenus = [
        ['id' => 'penjualan',            'label' => 'Sales Order', 'desc' => 'Kelola pesanan penjualan',    'url' => route('sales.sales_orders.index'),           'icon' => 'receipt',  'bg' => '#EEF2FF', 'fg' => '#6366F1'],
        ['id' => 'penjualan.pengiriman', 'label' => 'Pengiriman',  'desc' => 'Kelola pengiriman penjualan', 'url' => route('penjualan.pengiriman_list'), 'icon' => 'truck',  'bg' => '#F0FDF4', 'fg' => '#16A34A'],
        ['id' => 'penjualan.tagihan',    'label' => 'Tagihan',     'desc' => 'Kelola tagihan penjualan',    'url' => route('penjualan.tagihan_list'),    'icon' => 'wallet', 'bg' => '#FFF7ED', 'fg' => '#EA580C'],
    ];
    $pembelianSubmenus = [
        ['id' => 'pembelian',              'label' => 'Purchase Order', 'desc' => 'Kelola pesanan pembelian',  'url' => route('purchasings.purchase_orders.index'),            'icon' => 'receipt',   'bg' => '#EFF6FF', 'fg' => '#2563EB'],
        ['id' => 'pembelian.penerimaan',   'label' => 'Penerimaan',     'desc' => 'Catatan penerimaan barang', 'url' => route('purchasings.goods_receipts.index'),  'icon' => 'box',    'bg' => '#FFF7ED', 'fg' => '#EA580C'],
        ['id' => 'pembelian.tagihan_list', 'label' => 'Tagihan',        'desc' => 'Kelola tagihan pembelian',  'url' => route('purchasings.purchase_invoices.index'),     'icon' => 'wallet', 'bg' => '#F0FDF4', 'fg' => '#16A34A'],
    ];
    $navItems = [
        ['id' => 'kasbank', 'icon' => 'piggy-bank', 'label' => 'Kas & Bank',  'url' => route('kasbank.index')],
        ['id' => 'biaya',   'icon' => 'receipt', 'label' => 'Biaya',       'url' => route('biaya.index')],
        ['id' => 'master',  'icon' => 'database', 'label' => 'Master Data', 'url' => route('master.index')],
        ['id' => 'laporan', 'icon' => 'clipboard', 'label' => 'Laporan',     'url' => route('laporan.index')],
    ];
    $bottom = [];
@endphp

<div x-data="{
    openPanel: null,
    penjualanActive: {{ $penjualanActive ? 'true' : 'false' }},
    pembelianActive: {{ $pembelianActive ? 'true' : 'false' }},
    toggle(p) { this.openPanel = this.openPanel === p ? null : p; }
}" style="display: contents;">

    <aside class="sidebar">
        <div class="ppi-logo">PPI</div>
        <div class="sidebar__divider"></div>

        <nav class="sidebar__nav">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" title="Dashboard"
                class="sidebar-item"
                :data-active="openPanel === null && {{ str_starts_with($currentPage, 'dashboard') ? 'true' : 'false' }} ? '' : null">
                <x-misc.icon name="grid" :size="18" sw="1.7" />
            </a>

            {{-- Penjualan --}}
            <button type="button" title="Penjualan"
                class="sidebar-item"
                :data-active="openPanel === 'penjualan' || (openPanel === null && penjualanActive) ? '' : null"
                @click="toggle('penjualan')">
                <x-misc.icon name="dollar" :size="18" sw="1.7" />
            </button>

            {{-- Pembelian --}}
            <button type="button" title="Pembelian"
                class="sidebar-item"
                :data-active="openPanel === 'pembelian' || (openPanel === null && pembelianActive) ? '' : null"
                @click="toggle('pembelian')">
                <x-misc.icon name="cart" :size="18" sw="1.7" />
            </button>

            {{-- Other nav items --}}
            @foreach ($navItems as $item)
                @php $active = str_starts_with($currentPage, $item['id']); @endphp
                <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                    class="sidebar-item"
                    :data-active="openPanel === null && {{ $active ? 'true' : 'false' }} ? '' : null">
                    <x-misc.icon :name="$item['icon']" :size="18" sw="1.7" />
                </a>
            @endforeach
        </nav>

        <nav class="sidebar__nav sidebar__nav--bottom">
            @foreach ($bottom as $item)
                @php $active = str_starts_with($currentPage, $item['id']); @endphp
                <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                    class="sidebar-item"
                    :data-active="openPanel === null && {{ $active ? 'true' : 'false' }} ? '' : null">
                    <x-misc.icon :name="$item['icon']" :size="18" sw="1.7" />
                </a>
            @endforeach

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="sidebar-item sidebar-item--danger">
                    <x-misc.icon name="logout" :size="18" sw="1.7" />
                </button>
            </form>
        </nav>
    </aside>

    {{-- Backdrop --}}
    <div class="submenu-backdrop"
         x-show="openPanel !== null"
         x-transition:enter="submenu-bd-anim"
         x-transition:enter-start="submenu-bd-start"
         x-transition:enter-end="submenu-bd-end"
         x-transition:leave="submenu-bd-anim"
         x-transition:leave-start="submenu-bd-end"
         x-transition:leave-end="submenu-bd-start"
         @click="openPanel = null"
         x-cloak></div>

    {{-- Penjualan panel --}}
    <div class="submenu-panel"
         x-show="openPanel === 'penjualan'"
         x-transition:enter="submenu-panel-anim"
         x-transition:enter-start="submenu-panel-start"
         x-transition:enter-end="submenu-panel-end"
         x-transition:leave="submenu-panel-anim"
         x-transition:leave-start="submenu-panel-end"
         x-transition:leave-end="submenu-panel-start"
         x-cloak>
        <p class="submenu-panel__section">Penjualan</p>
        @foreach ($penjualanSubmenus as $sub)
            <a href="{{ $sub['url'] }}"
               class="submenu-panel__item {{ $currentPage === $sub['id'] ? 'active' : '' }}">
                <div class="submenu-panel__icon"
                     style="background: {{ $sub['bg'] }}; color: {{ $sub['fg'] }};">
                    <x-misc.icon :name="$sub['icon']" :size="18" sw="1.7" />
                </div>
                <div>
                    <div class="submenu-panel__title">{{ $sub['label'] }}</div>
                    <div class="submenu-panel__desc">{{ $sub['desc'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Pembelian panel --}}
    <div class="submenu-panel"
         x-show="openPanel === 'pembelian'"
         x-transition:enter="submenu-panel-anim"
         x-transition:enter-start="submenu-panel-start"
         x-transition:enter-end="submenu-panel-end"
         x-transition:leave="submenu-panel-anim"
         x-transition:leave-start="submenu-panel-end"
         x-transition:leave-end="submenu-panel-start"
         x-cloak>
        <p class="submenu-panel__section">Pembelian</p>
        @foreach ($pembelianSubmenus as $sub)
            <a href="{{ $sub['url'] }}"
               class="submenu-panel__item {{ $currentPage === $sub['id'] ? 'active' : '' }}">
                <div class="submenu-panel__icon"
                     style="background: {{ $sub['bg'] }}; color: {{ $sub['fg'] }};">
                    <x-misc.icon :name="$sub['icon']" :size="18" sw="1.7" />
                </div>
                <div>
                    <div class="submenu-panel__title">{{ $sub['label'] }}</div>
                    <div class="submenu-panel__desc">{{ $sub['desc'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

</div>
