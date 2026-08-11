<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentTerm;
use App\Http\Requests\ExpenseFormRequest;
use App\Services\ExpenseService;
use App\Services\Master\AccountService;
use App\Services\Master\ContactService;

class ExpenseController extends Controller
{
    private AccountService $accountService;
    private ContactService $contactService;
    private ExpenseService $expenseService;

    public function __construct(AccountService $accountService, ContactService $contactService, ExpenseService $expenseService)
    {
        $this->accountService = $accountService;
        $this->contactService = $contactService;
        $this->expenseService = $expenseService;
    }
    public function index()
    {
        $data = [
            'currentPage'    => 'biaya',
            'breadcrumb'     => [['label' => 'Biaya']],
            'status' => ExpenseStatus::dropdownOptions(),
        ];
        return view('expense.index', $data);
    }

    public function datatable(Request $request)
    {
        $data = $this->expenseService->fetchTableData($request);
        return response()->json($data);
    }

    public function create()
    {
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'    => 'biaya',
            'breadcrumb'     => [['label' => 'Biaya', 'url' => route('expenses.index')], ['label' => 'Buat']],
            'number' => $this->expenseService->generateNumber(),
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'contacts' => $this->contactService->fetchContactData(),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];
        return view('expense.create', $data);
    }

    public function store(ExpenseFormRequest $request)
    {
        try {
            $this->expenseService->storeExpense($request);
            return response()->json(['redirect' => route('expenses.index'), 'message' => 'Biaya berhasil dibuat.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data.'], 500);
        }
    }

    public function show(int $id)
    {
        $data = [
            'currentPage'    => 'biaya',
            'breadcrumb'     => [['label' => 'Biaya', 'url' => route('expenses.index')], ['label' => 'Detail']],
            'expense' => $this->expenseService->fetchExpenseByID($id),
        ];
        return view('expense.show', $data);
    }

    public function edit(int $id)
    {
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'    => 'biaya',
            'breadcrumb'     => [['label' => 'Biaya', 'url' => route('expenses.index')], ['label' => 'Edit']],
            'expense' => $this->expenseService->fetchExpenseByID($id),
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'contacts' => $this->contactService->fetchContactData(),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];
        return view('expense.edit', $data);
    }

    public function update(ExpenseFormRequest $request, int $id)
    {
        try {
            $this->expenseService->updateExpense($request, $id);
            return response()->json(['redirect' => route('expenses.index'), 'message' => 'Biaya berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat memperbarui data.'], 500);
        }
    }

    public function cancel(int $id)
    {
        try {
            $this->expenseService->cancelExpense($id);
            return response()->json(['message' => 'Biaya berhasil dibatalkan.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat membatalkan biaya.'], 500);
        }
    }
}
