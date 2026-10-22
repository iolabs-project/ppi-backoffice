<?php

use App\Enums\PaymentTerm;
use App\Services\Master\ProductService;

// Batch prefix = first letter + following consonants, padded from the remaining letters
test('batch prefix is derived from the product name', function (string $name, string $prefix) {
    expect(app(ProductService::class)->generateBatchPrefix($name))->toBe($prefix);
})->with([
    ['Katul', 'KTL'],
    ['Tepung Terigu', 'TPN'],
    ['Gula', 'GLU'],
    ['Jagung', 'JGN'],
    ['123', 'PRD'],
]);

test('due date follows the payment term, manual keeps the picked date', function () {
    expect(PaymentTerm::dueDate('cash', '2026-10-22')->toDateString())->toBe('2026-10-22')
        ->and(PaymentTerm::dueDate('cod', '2026-10-22')->toDateString())->toBe('2026-10-22')
        ->and(PaymentTerm::dueDate('net_14', '2026-10-22')->toDateString())->toBe('2026-11-05')
        ->and(PaymentTerm::dueDate('custom', '2026-10-22', '2026-12-01'))->toBe('2026-12-01')
        ->and(PaymentTerm::dueDate(null, '2026-10-22'))->toBeNull();
});
