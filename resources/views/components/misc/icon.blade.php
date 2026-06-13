@props(['name', 'size' => 18, 'stroke' => 'currentColor', 'sw' => 1.6])
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}"
    stroke-width="{{ $sw }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" />
            <rect x="14" y="14" width="7" height="7" rx="1.5" />
        @break

        @case('sales')
            <path d="M3 3v18h18" />
            <path d="M7 14l4-4 3 3 5-6" />
        @break

        @case('cart')
            <path d="M3 4h2l2.4 11.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6" />
            <circle cx="9" cy="20" r="1.2" />
            <circle cx="17" cy="20" r="1.2" />
        @break

        @case('truck')
            <rect x="2" y="6" width="13" height="10" rx="1.5" />
            <path d="M15 9h4l3 3v4h-7" />
            <circle cx="6.5" cy="17.5" r="1.6" />
            <circle cx="17.5" cy="17.5" r="1.6" />
        @break

        @case('wallet')
            <rect x="3" y="6" width="18" height="13" rx="2" />
            <path d="M3 10h18" />
            <circle cx="17" cy="14.5" r="1.2" />
        @break

        @case('receipt')
            <path d="M5 3h14v18l-3-2-3 2-3-2-3 2-2-2V3z" />
            <path d="M9 8h6M9 12h6M9 16h4" />
        @break

        @case('box')
            <path d="M3 7l9-4 9 4-9 4-9-4z" />
            <path d="M3 7v10l9 4 9-4V7" />
            <path d="M12 11v10" />
        @break

        @case('users')
            <circle cx="9" cy="8" r="3.5" />
            <path d="M2.5 19a6.5 6.5 0 0 1 13 0" />
            <path d="M16 11a3 3 0 1 0 0-6" />
            <path d="M22 19a5.5 5.5 0 0 0-5-5.5" />
        @break

        @case('book')
            <path d="M4 4h11a4 4 0 0 1 4 4v12H8a4 4 0 0 1-4-4z" />
            <path d="M4 16a4 4 0 0 1 4-4h11" />
        @break

        @case('settings')
            <circle cx="12" cy="12" r="3" />
            <path
                d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1A2 2 0 1 1 4.4 17l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8L4.2 7A2 2 0 1 1 7 4.2l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1A2 2 0 1 1 19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z" />
        @break

        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
        @break

        @case('bell')
            <path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6" />
            <path d="M10 19a2 2 0 0 0 4 0" />
        @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
        @break

        @case('chev-down')
            <path d="m6 9 6 6 6-6" />
        @break

        @case('chev-right')
            <path d="m9 6 6 6-6 6" />
        @break

        @case('chev-left')
            <path d="m15 6-6 6 6 6" />
        @break

        @case('arrow')
            <path d="M5 12h14M13 5l7 7-7 7" />
        @break

        @case('filter')
            <path d="M3 5h18M6 12h12M10 19h4" />
        @break

        @case('sort')
            <path d="M7 4v16M4 17l3 3 3-3M17 20V4M14 7l3-3 3 3" />
        @break

        @case('download')
            <path d="M12 3v12M7 10l5 5 5-5" />
            <path d="M5 21h14" />
        @break

        @case('print')
            <path d="M6 9V3h12v6" />
            <rect x="3" y="9" width="18" height="9" rx="1.5" />
            <path d="M6 14h12v7H6z" />
        @break

        @case('more')
            <circle cx="5" cy="12" r="1.4" />
            <circle cx="12" cy="12" r="1.4" />
            <circle cx="19" cy="12" r="1.4" />
        @break

        @case('check')
            <path d="m5 12 5 5L20 7" />
        @break

        @case('x')
            <path d="M6 6l12 12M18 6 6 18" />
        @break

        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M3 10h18M8 3v4M16 3v4" />
        @break

        @case('edit')
            <path d="M4 20h4l11-11-4-4L4 16z" />
            <path d="M14 6l4 4" />
        @break

        @case('trash')
            <path d="M4 7h16M9 7V4h6v3M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13" />
        @break

        @case('bank')
            <path d="M3 10 12 4l9 6" />
            <path d="M5 10v8M9 10v8M15 10v8M19 10v8" />
            <path d="M3 20h18" />
        @break

        @case('send')
            <path d="M22 2 11 13" />
            <path d="M22 2 15 22l-4-9-9-4z" />
        @break

        @case('inbox')
            <path d="M3 13h5l1 3h6l1-3h5" />
            <path d="M5 5h14l3 8v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z" />
        @break

        @case('swap')
            <path d="M7 7h13l-3-3M17 17H4l3 3" />
        @break

        @case('building')
            <rect x="4" y="3" width="16" height="18" rx="1" />
            <path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2" />
            <path d="M10 21v-3h4v3" />
        @break

        @case('tag')
            <path d="M3 12V4h8l10 10-8 8z" />
            <circle cx="8" cy="9" r="1.4" />
        @break

        @case('eye')
            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" />
            <circle cx="12" cy="12" r="3" />
        @break

        @case('help')
            <circle cx="12" cy="12" r="9" />
            <path d="M9.5 9.5a2.5 2.5 0 1 1 4 2c-1 .5-1.5 1-1.5 2.5" />
            <circle cx="12" cy="17.5" r=".6" fill="currentColor" />
        @break

        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
        @break

        @case('trend')
            <path d="M3 17l6-6 4 4 8-8" />
            <path d="M14 7h7v7" />
        @break

        @case('layers')
            <path d="m12 3 9 5-9 5-9-5z" />
            <path d="m3 13 9 5 9-5" />
            <path d="m3 17 9 5 9-5" />
        @break

        @case('doc')
            <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
            <path d="M14 3v6h6" />
            <path d="M9 13h6M9 17h4" />
        @break

        @case('wave')
            <path d="M3 12c2 0 2-3 4-3s2 6 4 6 2-9 4-9 2 6 4 6 2-3 2-3" />
        @break

        @case('dollar')
            <path d="M12 2v20" />
            <path d="M17 6H9.5a3.5 3.5 0 0 0 0 7H15a3.5 3.5 0 0 1 0 7H8" />
        @break

        @case('coins')
            <ellipse cx="12" cy="16" rx="7" ry="2" />
            <path d="M5 16v-4M19 16v-4" />
            <ellipse cx="12" cy="12" rx="7" ry="2" />
            <path d="M5 12V9M19 12V9" />
            <ellipse cx="12" cy="9" rx="7" ry="2" />
        @break

        @case('database')
            <ellipse cx="12" cy="5" rx="9" ry="3" />
            <path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5" />
            <path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3" />
        @break

        @case('clipboard')
            <rect x="5" y="5" width="14" height="16" rx="2" />
            <rect x="9" y="2" width="6" height="5" rx="1.5" />
            <path d="M8 12h8M8 16h5M8 20h3" />
        @break

        @case('piggy-bank')
            <path d="M19 5c-1.5 0-2.8.4-3.9 1-1.2-.6-2.6-1-4.1-1C6.2 5 2 8.6 2 13c0 2.1.9 4.1 2.5 5.5V21h4v-2h3v2h4v-2.5c1.6-1.4 2.5-3.4 2.5-5.5 0-.4 0-.8-.1-1.2" />
            <circle cx="17" cy="7" r="2" />
            <circle cx="9" cy="13" r="1" fill="currentColor" />
            <path d="M2 13h2" />
            <path d="m20 8-1.5 1.5" />
        @break

        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="M16 17l5-5-5-5" />
            <path d="M21 12H9" />
        @break

    @endswitch
</svg>
