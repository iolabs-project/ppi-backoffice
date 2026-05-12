@props(['currentPage' => 'dashboard'])
@php
    $items = [
        ['id' => 'dashboard', 'icon' => 'grid', 'label' => 'Dashboard', 'url' => route('erp.dashboard')],
        ['id' => 'penjualan', 'icon' => 'sales', 'label' => 'Penjualan', 'url' => route('erp.penjualan.index')],
        ['id' => 'pembelian', 'icon' => 'cart', 'label' => 'Pembelian', 'url' => route('erp.pembelian.index')],
        ['id' => 'kasbank', 'icon' => 'wallet', 'label' => 'Kas & Bank', 'url' => route('erp.kasbank.index')],
        ['id' => 'master', 'icon' => 'box', 'label' => 'Master Data', 'url' => route('erp.master.index')],
        ['id' => 'laporan', 'icon' => 'book', 'label' => 'Laporan', 'url' => route('erp.laporan.index')],
    ];
    $bottom = [
        // ['id'=>'pengaturan', 'icon'=>'settings','label'=>'Pengaturan', 'url'=>route('erp.pengaturan.index')],
    ];
@endphp
<aside class="sidebar">
    <div class="ppi-logo">PPI</div>
    <div class="sidebar__divider"></div>

    <nav class="sidebar__nav">
        @foreach ($items as $item)
            @php $active = str_starts_with($currentPage, $item['id']); @endphp
            <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                class="sidebar-item"
                @if($active) data-active @endif>
                <x-erp.icon :name="$item['icon']" :size="18" sw="1.7" />
            </a>
        @endforeach
    </nav>

    <nav class="sidebar__nav sidebar__nav--bottom">
        @foreach ($bottom as $item)
            @php $active = str_starts_with($currentPage, $item['id']); @endphp
            <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                class="sidebar-item"
                @if($active) data-active @endif>
                <x-erp.icon :name="$item['icon']" :size="18" sw="1.7" />
            </a>
        @endforeach
    </nav>
</aside>
