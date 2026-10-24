<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Builds document numbers and master data codes from config/numbering.php
class NumberingService
{
    // Next "{PREFIX}-{COMPANY}-{YEAR}-0001" style number, counted per company per year
    public function document(string $type, string $model, ?int $companyID = null): string
    {
        $companyID ??= config('context.selected_company_id');
        $prefix = config("numbering.documents.prefixes.{$type}");
        $companyCode = Company::select('code')->where('id', $companyID)->first()->code ?? 'XXX';
        $datePart = date('Y');

        /** @var class-string<Model> $model */
        $counter = $model::whereYear('created_at', date('Y'))
            ->where('company_id', $companyID)
            ->count() + 1;
        $counter = str_pad($counter, config('numbering.documents.padding'), '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    // Next "PRD-001" style master data code for the selected company
    public function masterCode(string $type, string $model): string
    {
        $prefix = config("numbering.master.prefixes.{$type}") . '-';

        // Only codes that are exactly prefix + digits count, so manual codes like "C-JKT" are skipped
        /** @var class-string<Model> $model */
        $last = $model::where('company_id', config('context.selected_company_id'))
            ->where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->filter(fn($code) => preg_match('/^' . preg_quote($prefix, '/') . '\d+$/', $code))
            ->map(fn($code) => (int) substr($code, strlen($prefix)))
            ->max() ?? 0;

        return $prefix . str_pad($last + 1, config('numbering.master.padding'), '0', STR_PAD_LEFT);
    }

    // Next "PRD-001-261025-01" style batch number, the counter restarts per product per day
    public function batchNumber(string $productCode, int $productID, int $companyID, $date = null): string
    {
        $prefix = $productCode . '-' . Carbon::parse($date ?? now())->format(config('numbering.batch.date_format')) . '-';

        $last = DB::table('product_batches')
            ->where('company_id', $companyID)
            ->where('product_id', $productID)
            ->where('batch_number', 'like', $prefix . '%')
            ->pluck('batch_number')
            ->filter(fn($number) => preg_match('/^' . preg_quote($prefix, '/') . '\d+$/', $number))
            ->map(fn($number) => (int) substr($number, strlen($prefix)))
            ->max() ?? 0;

        return $prefix . str_pad($last + 1, config('numbering.batch.padding'), '0', STR_PAD_LEFT);
    }

    // First letter + following consonants of the name, e.g. "Katul" → "KTL", "Gula" → "GLU" (mirrored in product-modal.blade.php)
    public function batchPrefix(string $name): string
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($name));
        $rest = substr($letters, 1);

        return substr(substr($letters, 0, 1) . preg_replace('/[AEIOU]/', '', $rest) . $rest, 0, config('numbering.batch.prefix_length'))
            ?: config('numbering.batch.fallback_prefix');
    }
}
