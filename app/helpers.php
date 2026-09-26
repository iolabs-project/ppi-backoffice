<?php

if (!function_exists('fmt_rp')) {
    function fmt_rp(int|float $n): string
    {
        return 'Rp ' . number_format(round($n));
    }
}

if (!function_exists('fmt_rp_short')) {
    function fmt_rp_short(int|float $n): string
    {
        $abs = abs($n);
        if ($abs >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 1) . ' M';
        if ($abs >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 1) . ' Jt';
        if ($abs >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0) . 'rb';
        return 'Rp ' . number_format($n);
    }
}

if (!function_exists('fmt_num')) {
    function fmt_num(int|float $n): string
    {
        return number_format($n);
    }
}

// Printed documents follow the invoice the customer receives (Indonesian notation: 1.405.710)

if (!function_exists('fmt_doc')) {
    /** Amount on a printed document, e.g. 189981000 → "189.981.000". */
    function fmt_doc(int|float|string|null $n): string
    {
        return number_format(round((float) $n), 0, ',', '.');
    }
}

if (!function_exists('fmt_doc_qty')) {
    /** Quantity on a printed document: whole numbers without decimals, otherwise up to 2 ("12,5"). */
    function fmt_doc_qty(int|float|string|null $n): string
    {
        $n = (float) $n;
        if (fmod($n, 1.0) === 0.0) {
            return number_format($n, 0, ',', '.');
        }

        // Trim only the decimal part: "12,50" → "12,5"
        return rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
    }
}

if (!function_exists('fmt_doc_percent')) {
    /** Percentage on a printed document, e.g. 1.75 → "1.75%"; empty when zero. */
    function fmt_doc_percent(int|float|string|null $n): string
    {
        $n = (float) $n;

        return $n == 0.0 ? '' : rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.') . '%';
    }
}

if (!function_exists('terbilang')) {
    /** Whole rupiah amount in Indonesian words, e.g. 1250 → "Seribu Dua Ratus Lima Puluh". */
    function terbilang(int|float|string|null $n): string
    {
        $n = (int) round(abs((float) $n));
        if ($n === 0) {
            return 'Nol';
        }

        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $spell = function (int $n) use (&$spell, $words): string {
            return match (true) {
                $n < 12 => $words[$n],
                $n < 20 => $words[$n - 10] . ' Belas',
                $n < 100 => $words[intdiv($n, 10)] . ' Puluh ' . $words[$n % 10],
                $n < 200 => 'Seratus ' . $spell($n - 100),
                $n < 1000 => $words[intdiv($n, 100)] . ' Ratus ' . $spell($n % 100),
                $n < 2000 => 'Seribu ' . $spell($n - 1000),
                $n < 1_000_000 => $spell(intdiv($n, 1000)) . ' Ribu ' . $spell($n % 1000),
                $n < 1_000_000_000 => $spell(intdiv($n, 1_000_000)) . ' Juta ' . $spell($n % 1_000_000),
                $n < 1_000_000_000_000 => $spell(intdiv($n, 1_000_000_000)) . ' Miliar ' . $spell($n % 1_000_000_000),
                default => $spell(intdiv($n, 1_000_000_000_000)) . ' Triliun ' . $spell($n % 1_000_000_000_000),
            };
        };

        return trim(preg_replace('/\s+/', ' ', $spell($n)));
    }
}

if (!function_exists('abort_unless_draft')) {
    /**
     * Hanya izinkan edit/update saat record masih berstatus draft.
     */
    function abort_unless_draft(?string $status, string $label): void
    {
        if ($status !== 'draft') {
            abort(403, "{$label} hanya dapat diubah saat berstatus draft.");
        }
    }
}

if (!function_exists('avatar_meta')) {
    function avatar_meta(string $name): array
    {
        $words    = array_filter(explode(' ', $name));
        $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice($words, 0, 2)));
        $h = 0;
        foreach (str_split($name) as $c) {
            $h = (($h * 31) + ord($c)) & 0xFFFFFFFF;
        }
        $hue = $h % 360;
        return [
            'initials' => $initials,
            'bg'       => "oklch(0.92 0.04 {$hue})",
            'fg'       => "oklch(0.45 0.10 {$hue})",
        ];
    }
}
