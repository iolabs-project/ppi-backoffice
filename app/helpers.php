<?php

if (!function_exists('fmt_rp')) {
    function fmt_rp(int|float $n): string
    {
        return 'Rp ' . number_format(round($n), 0, ',', '.');
    }
}

if (!function_exists('fmt_rp_short')) {
    function fmt_rp_short(int|float $n): string
    {
        $abs = abs($n);
        if ($abs >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 1) . ' M';
        if ($abs >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 1) . ' Jt';
        if ($abs >= 1_000)         return 'Rp ' . number_format($n / 1_000, 0) . 'rb';
        return 'Rp ' . number_format($n, 0, ',', '.');
    }
}

if (!function_exists('fmt_num')) {
    function fmt_num(int|float $n): string
    {
        return number_format($n, 0, ',', '.');
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
