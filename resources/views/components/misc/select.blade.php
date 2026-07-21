@props([
    'display',
    'hasValue' => 'false',
    'placeholder' => 'Cari...',
    'minWidth' => null,
    'align' => 'left',
    'height' => '40px',
    'triggerClass' => '',
    'triggerStyle' => '',
])
<div class="dropdown-wrap" x-data="{ open: false, q: '' }"
    x-on:close-dropdowns.window="open = false; q = ''"
    x-on:click.outside="open = false; q = ''"
    x-effect="if (open) { $nextTick(() => $refs.searchInput && $refs.searchInput.focus()) }">
    <div class="input dropdown-trigger {{ $triggerClass }}" style="height:{{ $height }}; {{ $triggerStyle }}" x-on:click="
        let wasOpen = open;
        $dispatch('close-dropdowns');
        if (!wasOpen) {
            let r = $el.getBoundingClientRect();
            $refs.menu.style.top = (r.bottom + 4) + 'px';
            @if ($align === 'right')
                $refs.menu.style.right = (window.innerWidth - r.right) + 'px';
                $refs.menu.style.left = 'auto';
            @else
                $refs.menu.style.left = r.left + 'px';
                $refs.menu.style.right = 'auto';
            @endif
            $refs.menu.style.width = r.width + 'px';
            open = true;
        } else {
            open = false;
        }
    ">
        @isset($trigger)
            {{ $trigger }}
        @else
            <span style="flex:1; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"
                :style="({{ $hasValue }}) ? '' : 'color:var(--ink-4);'" x-text="{{ $display }}"></span>
        @endisset
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--ink-4)" stroke-width="1.6"
            stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </div>
    <div x-ref="menu" class="dropdown-menu" x-show="open" x-cloak
        @if ($minWidth) style="min-width:{{ $minWidth }};" @endif>
        <div class="dropdown-search">
            <input type="text" class="dropdown-search__input" x-ref="searchInput" x-model="q"
                x-on:click.stop placeholder="{{ $placeholder }}" />
        </div>
        <div class="dropdown-list">
            {{ $slot }}
        </div>
    </div>
</div>
