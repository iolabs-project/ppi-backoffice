@props(['breadcrumb' => []])
@php $user = ['name' => 'Albert Irgi', 'role' => 'Direktur Operasional']; @endphp
<header class="topbar">
    <div class="topbar__inner">

        {{-- Breadcrumb --}}
        <div class="topbar__breadcrumb">
            @foreach ($breadcrumb as $i => $crumb)
                @if ($i > 0)
                    <x-erp.icon name="chev-right" :size="14" stroke="var(--ink-5)" />
                @endif
                @if (isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}"
                        class="topbar__crumb {{ $i === count($breadcrumb) - 1 ? 'topbar__crumb--last' : 'topbar__crumb--prev' }}">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span
                        class="topbar__crumb {{ $i === count($breadcrumb) - 1 ? 'topbar__crumb--last' : 'topbar__crumb--prev' }}">
                        {{ $crumb['label'] }}
                    </span>
                @endif
            @endforeach
        </div>

        {{-- Right side --}}
        <div class="topbar__right">
            <div class="topbar__search">
                <input placeholder="Cari transaksi, kontak, produk…" class="topbar__search-input"
                    onfocus="this.style.borderColor='var(--ink-4)'" onblur="this.style.borderColor='var(--line)'" />
                <div class="topbar__search-icon">
                    <x-erp.icon name="search" :size="15" stroke="var(--ink-4)" />
                </div>
                <kbd class="topbar__search-kbd">⌘K</kbd>
            </div>

            <button class="btn btn-ghost btn-icon" style="height:36px; width:36px; border:none;">
                <x-erp.icon name="bell" :size="16" />
            </button>

            <div class="topbar__user">
                <x-erp.avatar :name="$user['name']" />
                <div style="line-height:1.1;">
                    <div class="topbar__user-name">{{ $user['name'] }}</div>
                    <div class="topbar__user-role">{{ $user['role'] }}</div>
                </div>
            </div>
        </div>
    </div>
</header>
