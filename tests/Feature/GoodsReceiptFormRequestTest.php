<?php

use App\Http\Requests\Purchasing\GoodsReceiptFormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

function goodsReceiptErrors(array $input): array
{
    $request = GoodsReceiptFormRequest::create('/', 'PUT', $input);

    return Validator::make($input, $request->rules(), $request->messages())->errors()->toArray();
}

test('the store rule matches the real route name', function () {
    expect(Route::has('purchasings.goods_receipts.store'))->toBeTrue();
});

test('finishing a goods receipt requires at least one item', function () {
    $errors = goodsReceiptErrors(['status' => 'finished', 'receipt_date' => '2026-10-25', 'details' => []]);

    expect($errors)->toHaveKey('details');
});

test('finishing does not require a batch number, the server generates it', function () {
    $errors = goodsReceiptErrors([
        'status' => 'finished',
        'receipt_date' => '2026-10-25',
        'details' => [['received_quantity' => 5, 'unit_price' => 1000]],
    ]);

    expect($errors)->not->toHaveKey('details.0.batch_number')
        ->and($errors)->not->toHaveKey('details');
});

test('a draft can still be saved without items', function () {
    expect(goodsReceiptErrors(['status' => 'draft', 'receipt_date' => '2026-10-25']))->toBe([]);
});

test('an unknown status is rejected', function () {
    expect(goodsReceiptErrors([]))->toHaveKey('status');
});
