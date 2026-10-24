<?php

use App\Http\Requests\Finance\AccountPayableFormRequest;
use App\Http\Requests\Finance\AccountReceivableFormRequest;
use App\Http\Requests\Purchasing\PurchaseInvoiceFormRequest;
use App\Http\Requests\Sales\SalesInvoiceFormRequest;
use Illuminate\Support\Facades\Route;

// Builds the form request as if it came in on the named route
function rulesForRoute(string $requestClass, string $routeName, array $input = []): array
{
    $request = $requestClass::create('/', 'POST', $input);
    $request->setRouteResolver(fn() => Route::getRoutes()->getByName($routeName));

    return $request->rules();
}

test('invoice store requests validate the source order on their real route', function (string $requestClass, string $routeName, string $field) {
    expect(Route::has($routeName))->toBeTrue()
        ->and(rulesForRoute($requestClass, $routeName))->toHaveKey($field);
})->with([
    'sales invoice' => [SalesInvoiceFormRequest::class, 'sales.sales_invoices.store', 'sales_order_id'],
    'purchase invoice' => [PurchaseInvoiceFormRequest::class, 'purchasings.purchase_invoices.store', 'purchase_order_id'],
]);

test('invoice updates without a known status are rejected', function (string $requestClass, string $routeName) {
    $rules = rulesForRoute($requestClass, $routeName, ['status' => 'paid']);

    expect($rules)->toHaveKey('status')
        ->and(validator(['status' => 'paid'], $rules)->fails())->toBeTrue();
})->with([
    'sales invoice' => [SalesInvoiceFormRequest::class, 'sales.sales_invoices.update'],
    'purchase invoice' => [PurchaseInvoiceFormRequest::class, 'purchasings.purchase_invoices.update'],
]);

test('payments must be more than zero', function (string $requestClass) {
    $rules = (new $requestClass())->rules();

    expect(validator(['amount' => 0], ['amount' => $rules['amount']])->fails())->toBeTrue()
        ->and(validator(['amount' => 1000], ['amount' => $rules['amount']])->fails())->toBeFalse();
})->with([
    'receivable' => [AccountReceivableFormRequest::class],
    'payable' => [AccountPayableFormRequest::class],
]);
