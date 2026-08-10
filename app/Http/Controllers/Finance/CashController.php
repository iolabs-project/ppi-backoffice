<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\CashService;
use Illuminate\Http\Request;
use App\Enums\CashTransactionTypeEnum;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Finance\CashFormRequest;
use Illuminate\Support\Facades\Log;

class CashController extends Controller
{
    private CashService $cashService;
    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
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

    public function create(Request $request)
    {
        switch ($request->input('type')) {
            case CashTransactionTypeEnum::SEND:
                return view('finance.cash.send.create');
            case CashTransactionTypeEnum::RECEIVE:
                return view('finance.cash.receive.create');
            case CashTransactionTypeEnum::TRANSFER:
                return view('finance.cash.transfer.create');
            default:
                abort(404);
        }
    }

    public function store(CashFormRequest $request)
    {
        try {
            $this->cashService->storeTransaction(request: $request, companyID: config('context.selected_company_id'));
            return response()->json(['redirect' => route('finances.cash.index'), 'message' => 'Transaksi kas berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menambahkan transaksi kas. Silakan coba lagi.'], 500);
        }
    }

    public function show($id)
    {
        // Implement the logic to show a specific cash transaction
    }

    public function edit(int $id)
    {
        $transaction = $this->cashService->fetchTransactionByID($id);
        $data = [
            'currentPage'    => 'finance.cash',
            'breadcrumb'     => [['label' => 'Kas', 'url' => route('finances.cash.index')], ['label' => $transaction->number ?? 'Detail']],
            'transaction' => $transaction,
        ];
        switch ($transaction->type) {
            case CashTransactionTypeEnum::SEND:
                return view('finance.cash.send.edit', $data);
            case CashTransactionTypeEnum::RECEIVE:
                return view('finance.cash.receive.edit', $data);
            case CashTransactionTypeEnum::TRANSFER:
                return view('finance.cash.transfer.edit', $data);
            default:
                abort(404);
        }
    }

    public function update(CashFormRequest $request, int $id)
    {
        try {
            $this->cashService->updateTransaction(request: $request, id: $id);
            return response()->json(['redirect' => route('finances.cash.index'), 'message' => 'Transaksi kas berhasil diperbarui.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui transaksi kas. Silakan coba lagi.'], 500);
        }       
    }

    public function cancel(int $id)
    {
        try {
            $this->cashService->cancelTransaction(id: $id);
            return response()->json(['redirect' => route('finances.cash.index'), 'message' => 'Transaksi kas berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error Finance/CashController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan transaksi kas. Silakan coba lagi.'], 500);
        }
    }
}
