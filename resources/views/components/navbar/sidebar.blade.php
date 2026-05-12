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
<aside
    style="width:72px; flex:0 0 72px; background:var(--bg-2); border-right:1px solid var(--line); display:flex; flex-direction:column; align-items:center; padding:18px 0; gap:18px;">
    <div class="ppi-logo">PPI</div>
    <div style="width:32px; height:1px; background:var(--line);"></div>

    <nav style="display:flex; flex-direction:column; gap:6px; flex:1;">
        @foreach ($items as $item)
            @php $active = str_starts_with($currentPage, $item['id']); @endphp
            <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                style="position:relative; width:44px; height:44px; border-radius:12px; display:grid; place-items:center; text-decoration:none; transition:all .15s ease;
                background:{{ $active ? 'var(--ink)' : 'transparent' }};
                color:{{ $active ? 'var(--paper)' : 'var(--ink-3)' }};"
                onmouseover="if(!this.dataset.active){this.style.background='var(--bg-3)';this.style.color='var(--ink)';}"
                onmouseout="if(!this.dataset.active){this.style.background='transparent';this.style.color='var(--ink-3)';}"
                {{ $active ? 'data-active=true' : '' }}>
                <x-erp.icon :name="$item['icon']" :size="18" sw="1.7" />
                @if ($active)
                    <span
                        style="position:absolute; left:-12px; top:10px; bottom:10px; width:3px; border-radius:0 3px 3px 0; background:var(--accent);"></span>
                @endif
            </a>
        @endforeach
    </nav>

    <nav style="display:flex; flex-direction:column; gap:6px;">
        @foreach ($bottom as $item)
            @php $active = str_starts_with($currentPage, $item['id']); @endphp
            <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                style="position:relative; width:44px; height:44px; border-radius:12px; display:grid; place-items:center; text-decoration:none; transition:all .15s ease;
                background:{{ $active ? 'var(--ink)' : 'transparent' }};
                color:{{ $active ? 'var(--paper)' : 'var(--ink-3)' }};"
                onmouseover="if(!this.dataset.active){this.style.background='var(--bg-3)';this.style.color='var(--ink)';}"
                onmouseout="if(!this.dataset.active){this.style.background='transparent';this.style.color='var(--ink-3)';}">
                <x-erp.icon :name="$item['icon']" :size="18" sw="1.7" />
            </a>
        @endforeach
    </nav>

    {{-- <div
        style="width:36px; height:36px; border-radius:50%; background:oklch(0.78 0.05 60); color:var(--ink); display:grid; place-items:center; font-weight:700; font-size:12px;">
        AI</div> --}}
</aside>
