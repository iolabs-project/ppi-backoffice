<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ $pageTitle ?? 'Cetak Dokumen' }}</title>
    <style>
        /* Printed documents (invoice, orders, delivery notes...), modelled on the company's invoice */
        :root {
            --doc-blue: #3a6ec6;
            --doc-navy: #333f58;
            --doc-ink: #1d2230;
            --doc-muted: #5c6270;
            --doc-cell: #e8eaef;
        }

        @page {
            size: A4 portrait;
            margin: 14mm 12mm;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            background: #eceef2;
            color: var(--doc-ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .doc-toolbar {
            position: sticky;
            top: 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #1d2230;
        }

        .doc-toolbar button {
            font: 600 13px Arial, Helvetica, sans-serif;
            padding: 8px 18px;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            background: #fff;
            color: var(--doc-ink);
        }

        .doc-toolbar button.primary { background: var(--doc-blue); color: #fff; }

        .doc-page {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 14mm 12mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .12);
        }

        @media screen and (max-width: 820px) {
            .doc-page { width: auto; min-height: 0; margin: 0; padding: 20px 16px; box-shadow: none; }
        }

        @media print {
            html, body { background: #fff; }
            .doc-toolbar { display: none; }
            .doc-page { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="doc-toolbar">
        <button type="button" class="primary" onclick="window.print()">Cetak / Simpan PDF</button>
        <button type="button" onclick="window.close()">Tutup</button>
    </div>

    <div class="doc-page">
        @yield('content')
    </div>

    <script>
        // Opened from a "Cetak" button: go straight to the print dialog (?noprint shows the preview only)
        if (!new URLSearchParams(window.location.search).has('noprint')) {
            window.addEventListener('load', () => setTimeout(() => window.print(), 250));
        }
    </script>
</body>

</html>
