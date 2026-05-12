<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<title>{{ $pageTitle ?? 'Putra Pangan Indonesia' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
<style>
  :root {
    --bg:        #F5F2EB;
    --bg-2:      #EFEBE2;
    --bg-3:      #E8E2D4;
    --paper:     #FCFAF5;
    --ink:       #16130E;
    --ink-2:     #2A2620;
    --ink-3:     #5C5447;
    --ink-4:     #8A8170;
    --ink-5:     #B6AC97;
    --line:      #E2DCCC;
    --line-2:    #D4CCB7;
    --accent:    oklch(0.63 0.19 38);
    --accent-2:  oklch(0.55 0.20 35);
    --accent-3:  oklch(0.95 0.04 60);
    --accent-ink:#FCFAF5;
    --ok:        oklch(0.62 0.12 155);
    --ok-bg:     oklch(0.94 0.04 155);
    --warn:      oklch(0.72 0.13 80);
    --warn-bg:   oklch(0.95 0.06 85);
    --bad:       oklch(0.55 0.13 30);
    --bad-bg:    oklch(0.93 0.04 35);
    --info:      oklch(0.55 0.10 240);
    --info-bg:   oklch(0.95 0.03 240);
    --r-card:    16px;
    --r-btn:     10px;
    --r-chip:    999px;
    --shadow-1:  0 1px 0 rgba(22,19,14,.04), 0 1px 2px rgba(22,19,14,.04);
    --shadow-2:  0 1px 0 rgba(22,19,14,.04), 0 6px 18px -6px rgba(60,40,15,.10), 0 14px 40px -20px rgba(60,40,15,.12);
    --shadow-pop:0 1px 0 rgba(22,19,14,.05), 0 18px 50px -10px rgba(60,40,15,.20), 0 40px 90px -30px rgba(60,40,15,.25);
    --font-sans:  'Plus Jakarta Sans', system-ui, sans-serif;
    --font-display:'Sora', 'Plus Jakarta Sans', system-ui, sans-serif;
    --font-mono:  'JetBrains Mono', ui-monospace, monospace;
  }
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  body {
    font-family: var(--font-sans);
    color: var(--ink);
    background: var(--bg);
    font-size: 14px;
    line-height: 1.45;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
    overflow: hidden;
  }
  body::before {
    content:'';
    position: fixed; inset: 0;
    pointer-events: none;
    z-index: 1;
    opacity: .35;
    mix-blend-mode: multiply;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='180' height='180'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/><feColorMatrix values='0 0 0 0 0.10  0 0 0 0 0.08  0 0 0 0 0.05  0 0 0 0.045 0'/></filter><rect width='100%25' height='100%25' filter='url(%23n)'/></svg>");
  }
  .display { font-family: var(--font-display); letter-spacing: -0.02em; }
  .mono    { font-family: var(--font-mono); font-variant-numeric: tabular-nums; }
  .num     { font-family: var(--font-mono); font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }
  ::selection { background: var(--accent); color: var(--accent-ink); }
  ::-webkit-scrollbar { width: 10px; height: 10px; }
  ::-webkit-scrollbar-thumb { background: var(--line-2); border-radius: 999px; border: 2px solid var(--bg); }
  ::-webkit-scrollbar-thumb:hover { background: var(--ink-5); }
  #root { position: relative; z-index: 2; height: 100vh; }
  .card {
    background: var(--paper);
    border: 1px solid var(--line);
    border-radius: var(--r-card);
    box-shadow: var(--shadow-1);
  }
  .btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    height: 38px; padding: 0 16px;
    border-radius: var(--r-btn);
    font-family: var(--font-sans);
    font-weight: 600; font-size: 13px;
    letter-spacing: 0.02em;
    border: 1px solid transparent;
    cursor: pointer; user-select: none;
    transition: all .15s ease;
    text-transform: uppercase;
    text-decoration: none;
  }
  .btn-primary { background: var(--accent); color: var(--accent-ink); }
  .btn-primary:hover { background: var(--accent-2); }
  .btn-ghost   { background: transparent; color: var(--ink); border-color: var(--line-2); }
  .btn-ghost:hover { background: var(--bg-2); border-color: var(--ink-4); }
  .btn-dark    { background: var(--ink); color: var(--paper); }
  .btn-dark:hover { background: var(--ink-2); }
  .btn-sm { height: 30px; padding: 0 12px; font-size: 11.5px; }
  .btn-icon { width: 38px; padding: 0; }
  .chip {
    display: inline-flex; align-items: center; gap: 6px;
    height: 22px; padding: 0 10px;
    border-radius: var(--r-chip);
    font-size: 11px; font-weight: 600;
    letter-spacing: 0.02em;
  }
  .chip-dot { width: 6px; height: 6px; border-radius: 999px; flex-shrink: 0; }
  .input, .select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid var(--line);
    background: var(--paper);
    color: var(--ink);
    font: inherit;
    font-size: 13.5px;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
  }
  .input:focus, .select:focus {
    border-color: var(--ink-3);
    box-shadow: 0 0 0 3px rgba(22,19,14,.06);
  }
  .label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--ink-3);
    margin-bottom: 6px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  textarea.input { height: auto; padding: 12px; min-height: 80px; resize: vertical; }
  .nav-tab {
    display: inline-flex; align-items: center; gap: 8px;
    height: 38px; padding: 0 14px;
    border-radius: 10px;
    color: var(--ink-3);
    font-weight: 500; font-size: 13.5px;
    cursor: pointer; transition: all .12s ease;
    border: 1px solid transparent;
  }
  .nav-tab:hover { color: var(--ink); background: var(--bg-2); }
  .nav-tab.active { color: var(--ink); background: var(--paper); border-color: var(--line); box-shadow: var(--shadow-1); }
  .tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
  .tbl th {
    text-align: left; font-weight: 600;
    font-size: 10.5px; letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--ink-4);
    padding: 10px 14px;
    background: var(--bg-2);
    border-bottom: 1px solid var(--line);
  }
  .tbl th:first-child { border-top-left-radius: 12px; }
  .tbl th:last-child  { border-top-right-radius: 12px; }
  .tbl td {
    padding: 14px;
    font-size: 13.5px;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
  }
  .tbl tr:last-child td { border-bottom: none; }
  .tbl tr:hover td { background: var(--bg); }
  .tbl-tight td, .tbl-tight th { padding: 10px 12px; }
  .utab {
    display: inline-flex; align-items: center; gap: 6px;
    height: 44px; padding: 0 4px;
    color: var(--ink-4); font-weight: 500; font-size: 13.5px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color .12s ease, border-color .12s ease;
    text-decoration: none;
  }
  .utab + .utab { margin-left: 22px; }
  .utab:hover { color: var(--ink-2); }
  .utab.active { color: var(--ink); border-bottom-color: var(--accent); }
  .hr { height: 1px; background: var(--line); border: 0; margin: 0; }
  .ppi-logo {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--accent);
    color: var(--accent-ink);
    display: grid; place-items: center;
    font-family: var(--font-display);
    font-weight: 800; font-size: 13px; letter-spacing: -0.04em;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 4px 10px -4px oklch(0.55 0.20 35 / .5);
  }
  .avatar { width: 32px; height: 32px; border-radius: 999px; display: grid; place-items: center; font-weight: 600; font-size: 11.5px; letter-spacing: 0.02em; flex-shrink: 0; }
  .row-tap { cursor: pointer; }
  .row-tap:hover { background: var(--bg) !important; }
  .sep-v { width: 1px; align-self: stretch; background: var(--line); }
  kbd {
    font-family: var(--font-mono);
    font-size: 10.5px;
    padding: 1px 6px;
    border-radius: 5px;
    background: var(--bg-2);
    border: 1px solid var(--line-2);
    color: var(--ink-3);
  }
  [x-cloak] { display: none !important; }
  @media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
  }
</style>
</head>
<body>
<div id="root">
  <div style="display:flex; height:100vh; overflow:hidden;">

    {{-- Sidebar --}}
    <x-navbar.sidebar :current-page="$currentPage ?? 'dashboard'" />

    {{-- Main area --}}
    <main style="flex:1; display:flex; flex-direction:column; overflow:hidden; background:var(--bg);">

      {{-- Top bar --}}
      <x-navbar.topbar :breadcrumb="$breadcrumb ?? [['label'=>'PPI']]" />

      {{-- Page content --}}
      <div style="flex:1; overflow-y:auto;">
        @yield('content')
      </div>

    </main>
  </div>
</div>

{{-- Alpine.js --}}
<script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
