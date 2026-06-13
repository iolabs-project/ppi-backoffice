<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $pageTitle ?? 'Login — Putra Pangan Indonesia' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet" />
    @php
        $manifestPath = base_path('public/build/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $cssFile = $manifest['resources/sass/app.scss']['file'] ?? null;
    @endphp
    @if ($cssFile)
        <link rel="stylesheet" href="/build/{{ $cssFile }}" />
    @endif
    @vite(['resources/sass/app.scss'])
</head>

<body class="auth-body">
    <div id="root">
        @yield('content')
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @stack('scripts')
</body>

</html>
