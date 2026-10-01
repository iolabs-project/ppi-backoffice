@props([
    'title' => 'Putra Pangan Indonesia',
    'description' => 'Platform ERP terintegrasi untuk penjualan, pembelian, kas & bank, dan laporan keuangan Putra Pangan Indonesia.',
])

<meta name="description" content="{{ $description }}" />
<meta name="theme-color" content="#16130E" />
<link rel="canonical" href="{{ url()->current() }}" />

{{-- Favicons --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48" />
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}" />
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
<link rel="manifest" href="{{ asset('site.webmanifest') }}" />

{{-- Open Graph --}}
<meta property="og:type" content="website" />
<meta property="og:site_name" content="Putra Pangan Indonesia" />
<meta property="og:locale" content="id_ID" />
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:image" content="{{ asset('og-image.png') }}" />
<meta property="og:image:secure_url" content="{{ secure_asset('og-image.png') }}" />
<meta property="og:image:type" content="image/png" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="PPI Backoffice ERP — Kelola bisnis lebih cerdas." />

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $title }}" />
<meta name="twitter:description" content="{{ $description }}" />
<meta name="twitter:image" content="{{ asset('og-image.png') }}" />
<meta name="twitter:image:alt" content="PPI Backoffice ERP — Kelola bisnis lebih cerdas." />
