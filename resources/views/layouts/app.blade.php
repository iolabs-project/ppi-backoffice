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
    <script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @stack('scripts')
</body>

</html>
