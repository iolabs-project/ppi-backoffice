<?php

use App\Http\Requests\Purchasing\PurchaseOrderFormRequest;
use App\Models\User;
use App\Services\Finance\CashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// Company, signed-in user and two cash accounts without any balance
function cashBasics(): array
{
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);
    Auth::login(User::query()->create(['username' => 'tester', 'password' => 'password']));
    $categoryID = DB::table('account_categories')->insertGetId(['name' => 'Kas & Bank']);
    $accountIDs = collect(['1-1001', '1-1002'])->map(fn($code) => DB::table('chart_of_accounts')->insertGetId([
        'company_id' => $companyID, 'category_id' => $categoryID, 'code' => $code, 'name' => $code,
    ]));

    return [$companyID, $accountIDs[0], $accountIDs[1]];
}

test('a posted transfer from an account without enough balance is refused', function () {
    [$companyID, $from, $to] = cashBasics();

    expect(fn() => app(CashService::class)->storeTransaction(new Request([
        'type' => 'transfer', 'status' => 'posted', 'from_account_id' => $from, 'to_account_id' => $to,
        'transaction_date' => '2026-10-26', 'subtotal' => 1,
    ]), $companyID))->toThrow(ValidationException::class, 'Saldo tidak mencukupi.');

    expect(DB::table('cash_transactions')->count())->toBe(0);
});

test('run_once rejects a repeated key but frees it after a failed run', function () {
    run_once('test-key', fn() => 1);
    expect(fn() => run_once('test-key', fn() => 1))->toThrow(ValidationException::class);

    expect(fn() => run_once('failing-key', fn() => throw new RuntimeException('boom')))->toThrow(RuntimeException::class);
    expect(run_once('failing-key', fn() => 'ok'))->toBe('ok');
});

test('a draft order whose total does not fit the decimal columns is refused', function () {
    $request = PurchaseOrderFormRequest::create('/', 'POST', [
        'status' => 'draft', 'number' => 'PO-1', 'order_date' => '2026-10-26',
        'details' => [['quantity' => 999_999_999_999, 'unit_price' => 999_999_999_999]],
    ]);
    $request->setContainer(app())->setRedirector(app('redirect'));

    expect(fn() => $request->validateResolved())->toThrow(ValidationException::class, 'Nilai terlalu besar');
});

test('a deactivated user cannot log in', function () {
    User::query()->create(['username' => 'inactive', 'password' => 'password'])
        ->forceFill(['deleted_at' => now()])->save();

    $this->postJson(route('login.post'), ['username' => 'inactive', 'password' => 'password'])
        ->assertStatus(422);
    expect(Auth::check())->toBeFalse();
});
