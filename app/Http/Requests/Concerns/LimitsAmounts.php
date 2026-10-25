<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Validator;

/**
 * Money and quantity columns are decimal(18, 4), so they hold at most 14 integer digits.
 * Without this check a too-large number reaches the database and fails as a 500.
 */
trait LimitsAmounts
{
    private const MAX_AMOUNT = 99_999_999_999_999;

    public function after(): array
    {
        return [function (Validator $validator) {
            $tooLarge = false;
            $input = $this->all();
            array_walk_recursive($input, function ($value, $key) use (&$tooLarge) {
                if (is_string($key) && preg_match('/(quantity|price|amount|cost|subtotal)$/', $key)
                    && is_numeric($value) && abs((float) $value) > self::MAX_AMOUNT) {
                    $tooLarge = true;
                }
            });

            $documentTotal = collect($this->input('details', []))
                ->sum(fn ($line) => (float) ($line['quantity'] ?? 0) * (float) ($line['unit_price'] ?? 0));
            foreach (['items', 'costs', 'charges'] as $group) {
                $documentTotal += collect($this->input($group, []))->sum(fn ($line) => (float) ($line['amount'] ?? 0));
            }

            if ($tooLarge || $documentTotal > self::MAX_AMOUNT) {
                $validator->errors()->add('amount', 'Nilai terlalu besar. Setiap angka dan total dokumen maksimal 99.999.999.999.999.');
            }
        }];
    }
}
