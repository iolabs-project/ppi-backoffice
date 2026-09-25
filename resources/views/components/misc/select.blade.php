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
    :data-has-value="({{ $hasValue }}) ? 'true' : 'false'"
    x-on:close-dropdowns.window="open = false; q = ''"
    x-on:click.outside="open = false; q = ''"
    x-effect="if (open) { $nextTick(() => $refs.searchInput && $refs.searchInput.focus()) }">
    <div class="input dropdown-trigger {{ $triggerClass }}" style="height:{{ $height }}; {{ $triggerStyle }}" x-on:click="
        let wasOpen = open;
        $dispatch('close-dropdowns');
        if (!wasOpen) {
            let r = $el.getBoundingClientRect();
            let m = $refs.menu;
            if (m.dataset.minWidth === undefined) m.dataset.minWidth = m.style.minWidth;
            m.style.minWidth = m.dataset.minWidth;
            m.style.top = (r.bottom + 4) + 'px';
            @if ($align === 'right')
                m.style.right = (window.innerWidth - r.right) + 'px';
                m.style.left = 'auto';
            @else
                m.style.left = r.left + 'px';
                m.style.right = 'auto';
            @endif
            m.style.width = r.width + 'px';
            open = true;
            // Keep the menu on screen: shift it sideways, and open it upwards when there's no room below.
            // Measured once the menu is actually visible (a hidden menu reports a 0×0 box).
            const place = () => {
                if (!open) return;
                if (!m.offsetWidth) return requestAnimationFrame(place);
                const pad = 8, vw = window.innerWidth, vh = window.innerHeight;
                if (m.offsetWidth > vw - pad * 2) {
                    m.style.minWidth = '0';
                    m.style.width = (vw - pad * 2) + 'px';
                }
                const mr = m.getBoundingClientRect();
                if (mr.right > vw - pad || mr.left < pad) {
                    m.style.left = Math.min(Math.max(pad, mr.left), vw - pad - mr.width) + 'px';
                    m.style.right = 'auto';
                }
                if (mr.bottom > vh - pad && r.top > vh - r.bottom) {
                    m.style.top = Math.max(pad, r.top - 4 - mr.height) + 'px';
                }
            };
            $nextTick(place);
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
