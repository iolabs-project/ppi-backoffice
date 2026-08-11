<?php

namespace App\Http\Controllers\Finance;

use App\Enums\AccountCategoryEnum;
use App\Http\Controllers\Controller;
use App\Services\Finance\CashService;
use App\Services\Master\AccountService;
use App\Services\Master\ContactService;
use Illuminate\Http\Request;
use App\Enums\CashTransactionTypeEnum;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Finance\CashFormRequest;
use App\Http\Requests\Finance\CashReceiveFormRequest;
use App\Http\Requests\Finance\CashTransferFormRequest;
use Illuminate\Support\Facades\Log;

class CashController extends Controller
{
    private CashService $cashService;
    private AccountService $accountService;
    private ContactService $contactService;
    public function __construct(CashService $cashService, AccountService $accountService, ContactService $contactService)
    {
        $this->cashService = $cashService;
        $this->accountService = $accountService;
        $this->contactService = $contactService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'finance.cash',
            'breadcrumb'     => [['label' => 'Kas & Bank']],
            'activeAccounts' => $this->cashService->fetchActiveAccountData(config('context.selected_company_id')),
        ];
        return view('finance.cash.index', $data);
    }

    public function datatable(Request $request)
    {
        try {
            $data = $this->cashService->fetchTransactionTableData($request);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error CashController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba mengambil data transaksi kas dan bank. Silakan coba lagi.'], 500);
        }
    }

    public function show(int $id)
    {
        $account = $this->cashService->fetchActiveAccountDataByID(config('context.selected_company_id'), $id);
        $data = [
            'currentPage'    => 'finance.cash',
            'breadcrumb'     => [['label' => 'Kas & Bank', 'url' => route('finances.cash.index')], ['label' => 'Detail']],
            'account' => $account,
        ];
        return view('finance.cash.show', $data);
    }

    public function createTransfer(int $id)
    {
        $account = $this->cashService->fetchActiveAccountDataByID(config('context.selected_company_id'), $id);
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'    => 'finance.cash',
            'breadcrumb'     => [['label' => 'Kas & Bank', 'url' => route('finances.cash.index')], ['label' => 'Transfer Dana']],
            'account' => $account,
            'accounts' => $this->cashService->fetchActiveAccountData(companyID: $companyID, includeBalance: false, excludeAccountIDs: [$id]),
            'number' => $this->cashService->generateNumber(companyID: $companyID),
        ];

        return view('finance.cash.transfer.create', $data);
    }

    public function storeTransfer(CashTransferFormRequest $request)
    {
        try {
            $this->cashService->storeTransaction(request: $request, companyID: config('context.selected_company_id'));
            return response()->json(['redirect' => route('finances.cash.show', $request->input('from_account_id')), 'message' => 'Transaksi kas berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@storeTransfer: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menambahkan transaksi kas. Silakan coba lagi.'], 500);
        }
    }

    public function editTransfer(int $id, int $transfer)
    {
        $account = $this->cashService->fetchActiveAccountDataByID(config('context.selected_company_id'), $id);
        $transaction = $this->cashService->fetchTransactionByID($transfer);
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'    => 'finance.cash',
            'breadcrumb'     => [['label' => 'Kas & Bank', 'url' => route('finances.cash.index')], ['label' => 'Transfer Dana']],
            'account' => $account,
            'accounts' => $this->cashService->fetchActiveAccountData(companyID: $companyID, includeBalance: false, excludeAccountIDs: [$id]),
            'transaction' => $transaction,
        ];

        return view('finance.cash.transfer.edit', $data);
    }

    public function updateTransfer(CashTransferFormRequest $request, int $id, int $transfer)
    {
        try {
            $this->cashService->updateTransaction(request: $request, accountID: $id, transactionID: $transfer);
            return response()->json(['redirect' => route('finances.cash.show', $request->input('from_account_id')), 'message' => 'Transaksi kas berhasil diperbarui.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@updateTransfer: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui transaksi kas. Silakan coba lagi.'], 500);
        }
    }

    public function cancelTransfer(int $id, int $transfer)
    {
        try {
            $this->cashService->cancelTransaction(id: $transfer, companyID: config('context.selected_company_id'));
            return response()->json(['redirect' => route('finances.cash.show', $id), 'message' => 'Transaksi kas berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@cancelTransfer: ' . $e->getMessage(), [
                'exception' => $e,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan transaksi kas. Silakan coba lagi.'], 500);
        }
    }

    public function createReceive(int $id)
    {
        $account = $this->cashService->fetchActiveAccountDataByID(config('context.selected_company_id'), $id);
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'    => 'finance.cash',
            'breadcrumb'     => [['label' => 'Kas & Bank', 'url' => route('finances.cash.index')], ['label' => 'Terima Dana']],
            'account' => $account,
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID, excludeCategoryID: AccountCategoryEnum::CASH_BANK->value),
            'contacts' => $this->contactService->fetchContactData(),
            'number' => $this->cashService->generateNumber(companyID: $companyID),
        ];

        return view('finance.cash.receive.create', $data);
    }

    public function storeReceive(CashReceiveFormRequest $request)
    {
        try {
            $this->cashService->storeTransaction(request: $request, companyID: config('context.selected_company_id'));
            return response()->json(['redirect' => route('finances.cash.show', $request->input('to_account_id')), 'message' => 'Transaksi kas berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@storeReceive: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menambahkan transaksi kas. Silakan coba lagi.'], 500);
        }
    }

    public function editReceive(int $id, int $receive)
    {
        $account = $this->cashService->fetchActiveAccountDataByID(config('context.selected_company_id'), $id);
        $transaction = $this->cashService->fetchTransactionByID($receive);
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'    => 'finance.cash',
            'breadcrumb'     => [['label' => 'Kas & Bank', 'url' => route('finances.cash.index')], ['label' => 'Terima Dana']],
            'account' => $account,
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID, excludeCategoryID: AccountCategoryEnum::CASH_BANK->value),
            'contacts' => $this->contactService->fetchContactData(),
            'transaction' => $transaction,
        ];

        return view('finance.cash.receive.edit', $data);
    }

    public function updateReceive(CashReceiveFormRequest $request, int $id, int $receive)
    {
        try {
            $this->cashService->updateTransaction(request: $request, accountID: $id, transactionID: $receive);
            return response()->json(['redirect' => route('finances.cash.show', $request->input('to_account_id')), 'message' => 'Transaksi kas berhasil diperbarui.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@updateReceive: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui transaksi kas. Silakan coba lagi.'], 500);
        }
    }

    public function cancelReceive(int $id, int $receive)
    {
        try {
            $this->cashService->cancelTransaction(id: $receive, companyID: config('context.selected_company_id'));
            return response()->json(['redirect' => route('finances.cash.show', $id), 'message' => 'Transaksi kas berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@cancelReceive: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => ['id' => $id, 'receive' => $receive],
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan transaksi kas. Silakan coba lagi.'], 500);
        }
    }
}
