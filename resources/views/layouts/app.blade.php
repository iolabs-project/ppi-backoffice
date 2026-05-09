<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Favicon -->
    {{-- <link rel="icon" type="image/x-icon" href="{{ asset('assets/components/icon.png') }}"> --}}

    {{-- Main CSS File - Production build --}}
    @php
        $manifestPath = base_path('public/build/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $cssFile = $manifest['resources/sass/app.scss']['file'] ?? null;
    @endphp
    @if ($cssFile)
        <link rel="stylesheet" href="/build/{{ $cssFile }}" />
    @else
        <!-- Fallback: manifest not found or SCSS not in manifest -->
        <link rel="stylesheet" href="{{ asset('../resources/sass/app.scss') }}" />
    @endif
    @vite(['resources/sass/app.scss'])
</head>

<body>
    @yield('content')

    {{-- Main JS File - Production build --}}
    @php
        $manifestPath = base_path('public/build/manifest.json');
        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
    @endphp
    @if ($jsFile)
        <script type="module" src="/build/{{ $jsFile }}"></script>
    @else
        <!-- Fallback: manifest not found or JS not in manifest -->
        <script type="module" src="/build/assets/app-BGoPVvf_.js"></script>
    @endif
</body>

</html>
