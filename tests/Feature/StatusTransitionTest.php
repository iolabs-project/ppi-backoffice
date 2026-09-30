<?php

use Symfony\Component\HttpKernel\Exception\HttpException;

// Status only moves forward: cancelled, closed and finished documents are final.
test('status changes only move forward', function (string $from, string $to, bool $allowed) {
    $change = fn () => abort_unless_can_become($from, $to, 'Dokumen');

    $allowed
        ? expect($change)->not->toThrow(HttpException::class)
        : expect($change)->toThrow(HttpException::class);
})->with([
    'draft can open' => ['draft', 'open', true],
    'open can close' => ['open', 'closed', true],
    'draft can cancel' => ['draft', 'cancelled', true],
    'open can cancel' => ['open', 'cancelled', true],
    'cancelled cannot reopen' => ['cancelled', 'open', false],
    'closed cannot reopen' => ['closed', 'open', false],
    'cancelled cannot cancel again' => ['cancelled', 'cancelled', false],
    'finished cannot cancel' => ['finished', 'cancelled', false],
    'open cannot open again' => ['open', 'open', false],
]);
