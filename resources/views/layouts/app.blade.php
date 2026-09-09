<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $pageTitle ?? 'Putra Pangan Indonesia' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet" />
    {{-- @vite(['resources/sass/app.scss', 'resources/js/app.js']) --}}
    {{-- @php
        $manifestPath = base_path('public/build/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $cssFile = $manifest['resources/sass/app.scss']['file'] ?? null;
    @endphp
    @if ($cssFile)
        <link rel="stylesheet" href="/build/{{ $cssFile }}" />
    @else
        <!-- Fallback: manifest not found or SCSS not in manifest -->
        <link rel="stylesheet" href="{{ asset('../resources/sass/app.scss') }}" />
    @endif --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="root">
        <div class="erp-shell">

            {{-- Sidebar --}}
            <x-navbar.sidebar :current-page="$currentPage ?? 'dashboard'" />

            {{-- Main area --}}
            <main class="erp-main">

                {{-- Top bar --}}
                <x-navbar.topbar :breadcrumb="$breadcrumb ?? [['label' => 'PPI']]" />

                {{-- Page content --}}
                <div class="erp-scroll">
                    @yield('content')
                </div>

            </main>
        </div>
    </div>

    @routes
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>
        (function () {
            const BASE_WIDTH = 1280;
            const root = document.getElementById('root');
            function applyScale() {
                if (window.innerWidth >= BASE_WIDTH) {
                    root.style.transform = '';
                    root.style.transformOrigin = '';
                    root.style.width = '';
                    root.style.height = '';
                    const shell = root.querySelector('.erp-shell');
                    if (shell) shell.style.height = '';
                    return;
                }
                const scale = window.innerWidth / BASE_WIDTH;
                root.style.transform = 'scale(' + scale + ')';
                root.style.transformOrigin = 'top left';
                root.style.width = BASE_WIDTH + 'px';
                root.style.height = (window.innerHeight / scale) + 'px';
                const shell = root.querySelector('.erp-shell');
                if (shell) shell.style.height = (window.innerHeight / scale) + 'px';
            }
            applyScale();
            window.addEventListener('resize', applyScale);
        })();
    </script>
    @stack('scripts')
</body>

</html>
