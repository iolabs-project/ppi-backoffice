<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ExpenseStatus;
use App\Services\ExpenseService;
use App\Services\Master\AccountService;

class ExpenseController extends Controller
{
    private AccountService $accountService;
    private ExpenseService $expenseService;

    public function __construct(AccountService $accountService, ExpenseService $expenseService)
    {
        $this->accountService = $accountService;
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
        $data = [
            'currentPage'    => 'biaya',
            'breadcrumb'     => [['label' => 'Biaya', 'url' => route('expenses.index')], ['label' => 'Buat']],
            'accounts' => $this->accountService->fetchAccountData(null),

        ];
        return view('expense.create', $data);
    }

    public function store(Request $request)
    {
        // $data = $this->expenseService->storeExpense($request);
        // return response()->json(['redirect' => route('expenses.edit', $data->id), 'message' => 'Biaya berhasil dibuat.']);
        if ($request->input('status') === ExpenseStatus::DRAFT->value) {
            $request->validate(
                [
                    'contact_id' => 'nullable|exists:contacts,id',
                    'account_id' => 'nullable|exists:chart_of_accounts,id',
                    'expense_date' => 'required|date',
                    'due_date' => 'nullable|date|after_or_equal:expense_date',
                    'payment_terms' => 'nullable|in:net_7,net_14,net_30,net_45',
                    'items' => 'nullable|array',
                    'items.*.account_id' => 'required_with:items|exists:chart_of_accounts,id',
                    'items.*.amount' => 'required_with:items|numeric|min:0.00',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs|exists:chart_of_accounts,id',
                    'costs.*.amount' => 'required_with:costs|numeric|min:0.00',
                ],
                [
                    'contact_id.exists' => 'Kontak tidak ditemukan.',
                    'account_id.exists' => 'Akun tidak ditemukan.',
                    'expense_date.required' => 'Tanggal biaya harus diisi.',
                    'expense_date.date' => 'Tanggal biaya tidak valid.',
                    'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
                    'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal biaya.',
                    'payment_terms.in' => 'Syarat pembayaran tidak valid.',
                    'items.array' => 'Item biaya harus berupa array.',
                    'items.*.account_id.required_with' => 'Akun untuk item biaya harus diisi.',
                    'items.*.account_id.exists' => 'Akun untuk item biaya tidak ditemukan.',
                    'items.*.amount.required_with' => 'Jumlah untuk item biaya harus diisi.',
                    'items.*.amount.numeric' => 'Jumlah untuk item biaya harus berupa angka.',
                    'items.*.amount.min' => 'Jumlah untuk item biaya harus lebih besar dari atau sama dengan 0.',
                    'costs.array' => 'Biaya tambahan harus berupa array.',
                    'costs.*.account_id.required_with' => 'Akun untuk biaya tambahan harus diisi.',
                    'costs.*.account_id.exists' => 'Akun untuk biaya tambahan tidak ditemukan.',
                    'costs.*.amount.required_with' => 'Jumlah untuk biaya tambahan harus diisi.',
                    'costs.*.amount.numeric' => 'Jumlah untuk biaya tambahan harus berupa angka.',
                    'costs.*.amount.min' => 'Jumlah untuk biaya tambahan harus lebih besar dari atau sama dengan 0.',
                ]
            );
        } else if ($request->input('status') === ExpenseStatus::OPEN->value) {
            $request->validate(
                [
                    'contact_id' => 'required|exists:contacts,id',
                    'account_id' => 'required|exists:chart_of_accounts,id',
                    'expense_date' => 'required|date',
                    'due_date' => 'nullable|date|after_or_equal:expense_date',
                    'payment_terms' => 'nullable|in:net_7,net_14,net_30,net_45',
                    'items' => 'required|array|min:1',
                    'items.*.account_id' => 'required_with:items|exists:chart_of_accounts,id',
                    'items.*.amount' => 'required_with:items|numeric|min:0.00',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs|exists:chart_of_accounts,id',
                    'costs.*.amount' => 'required_with:costs|numeric|min:0.00',
                ],
                [
                    'contact_id.required' => 'Kontak harus diisi.',
                    'contact_id.exists' => 'Kontak tidak ditemukan.',
                    'account_id.required' => 'Akun harus diisi.',
                    'account_id.exists' => 'Akun tidak ditemukan.',
                    'expense_date.required' => 'Tanggal biaya harus diisi.',
                    'expense_date.date' => 'Tanggal biaya tidak valid.',
                    'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
                    'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal biaya.',
                    'payment_terms.in' => 'Syarat pembayaran tidak valid.',
                    'items.required' => 'Item biaya harus diisi.',
                    'items.array' => 'Item biaya harus berupa array.',
                    'items.*.account_id.required_with' => 'Akun untuk item biaya harus diisi.',
                    'items.*.account_id.exists' => 'Akun untuk item biaya tidak ditemukan.',
                    'items.*.amount.required_with' => 'Jumlah untuk item biaya harus diisi.',
                    'items.*.amount.numeric' => 'Jumlah untuk item biaya harus berupa angka.',
                    'items.*.amount.min' => 'Jumlah untuk item biaya harus lebih besar dari atau sama dengan 0.',
                    'costs.array' => 'Biaya tambahan harus berupa array.',
                    'costs.*.account_id.required_with' => 'Akun untuk biaya tambahan harus diisi.',
                    'costs.*.account_id.exists' => 'Akun untuk biaya tambahan tidak ditemukan.',
                    'costs.*.amount.required_with' => 'Jumlah untuk biaya tambahan harus diisi.',
                    'costs.*.amount.numeric' => 'Jumlah untuk biaya tambahan harus berupa angka.',
                    'costs.*.amount.min' => 'Jumlah untuk biaya tambahan harus lebih besar dari atau sama dengan 0.',
                ]
            );
        } else {
            return response()->json(['message' => "Status tidak valid. Harus berupa draft atau open."], 400);
        }
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
        $data = [
            'currentPage'    => 'biaya',
            'breadcrumb'     => [['label' => 'Biaya', 'url' => route('expenses.index')], ['label' => 'Edit']],
            'expense' => $this->expenseService->fetchExpenseByID($id),
            'accounts' => $this->accountService->fetchAccountData(null),
        ];
        return view('expense.edit', $data);
    }

    public function update(Request $request, int $id)
    {
        if ($request->input('status') === ExpenseStatus::DRAFT->value) {
            $request->validate(
                [
                    'contact_id' => 'nullable|exists:contacts,id',
                    'account_id' => 'nullable|exists:chart_of_accounts,id',
                    'expense_date' => 'required|date',
                    'due_date' => 'nullable|date|after_or_equal:expense_date',
                    'payment_terms' => 'nullable|in:net_7,net_14,net_30,net_45',
                    'items' => 'nullable|array',
                    'items.*.account_id' => 'required_with:items|exists:chart_of_accounts,id',
                    'items.*.amount' => 'required_with:items|numeric|min:0.00',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs|exists:chart_of_accounts,id',
                    'costs.*.amount' => 'required_with:costs|numeric|min:0.00',
                ],
                [
                    'contact_id.exists' => 'Kontak tidak ditemukan.',
                    'account_id.exists' => 'Akun tidak ditemukan.',
                    'expense_date.required' => 'Tanggal biaya harus diisi.',
                    'expense_date.date' => 'Tanggal biaya tidak valid.',
                    'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
                    'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal biaya.',
                    'payment_terms.in' => 'Syarat pembayaran tidak valid.',
                    'items.array' => 'Item biaya harus berupa array.',
                    'items.*.account_id.required_with' => 'Akun untuk item biaya harus diisi.',
                    'items.*.account_id.exists' => 'Akun untuk item biaya tidak ditemukan.',
                    'items.*.amount.required_with' => 'Jumlah untuk item biaya harus diisi.',
                    'items.*.amount.numeric' => 'Jumlah untuk item biaya harus berupa angka.',
                    'items.*.amount.min' => 'Jumlah untuk item biaya harus lebih besar dari atau sama dengan 0.',
                    'costs.array' => 'Biaya tambahan harus berupa array.',
                    'costs.*.account_id.required_with' => 'Akun untuk biaya tambahan harus diisi.',
                    'costs.*.account_id.exists' => 'Akun untuk biaya tambahan tidak ditemukan.',
                    'costs.*.amount.required_with' => 'Jumlah untuk biaya tambahan harus diisi.',
                    'costs.*.amount.numeric' => 'Jumlah untuk biaya tambahan harus berupa angka.',
                    'costs.*.amount.min' => 'Jumlah untuk biaya tambahan harus lebih besar dari atau sama dengan 0.',
                ]
            );
        } else if ($request->input('status') === ExpenseStatus::OPEN->value) {
            $request->validate(
                [
                    'contact_id' => 'required|exists:contacts,id',
                    'account_id' => 'required|exists:chart_of_accounts,id',
                    'expense_date' => 'required|date',
                    'due_date' => 'nullable|date|after_or_equal:expense_date',
                    'payment_terms' => 'nullable|in:net_7,net_14,net_30,net_45',
                    'items' => 'required|array|min:1',
                    'items.*.account_id' => 'required_with:items|exists:chart_of_accounts,id',
                    'items.*.amount' => 'required_with:items|numeric|min:0.00',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs|exists:chart_of_accounts,id',
                    'costs.*.amount' => 'required_with:costs|numeric|min:0.00',
                ],
                [
                    'contact_id.required' => 'Kontak harus diisi.',
                    'contact_id.exists' => 'Kontak tidak ditemukan.',
                    'account_id.required' => 'Akun harus diisi.',
                    'account_id.exists' => 'Akun tidak ditemukan.',
                    'expense_date.required' => 'Tanggal biaya harus diisi.',
                    'expense_date.date' => 'Tanggal biaya tidak valid.',
                    'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
                    'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal biaya.',
                    'payment_terms.in' => 'Syarat pembayaran tidak valid.',
                    'items.required' => 'Item biaya harus diisi.',
                    'items.array' => 'Item biaya harus berupa array.',
                    'items.*.account_id.required_with' => 'Akun untuk item biaya harus diisi.',
                    'items.*.account_id.exists' => 'Akun untuk item biaya tidak ditemukan   .',
                    'items.*.amount.required_with' => 'Jumlah untuk item biaya harus diisi.',
                    'items.*.amount.numeric' => 'Jumlah untuk item biaya harus berupa angka.',
                    'items.*.amount.min' => 'Jumlah untuk item biaya harus lebih besar dari atau sama dengan 0.',
                    'costs.array' => 'Biaya tambahan harus berupa array.',
                    'costs.*.account_id.required_with' => 'Akun untuk biaya tambahan harus diisi.',
                    'costs.*.account_id.exists' => 'Akun untuk biaya tambahan tidak ditemukan.',
                    'costs.*.amount.required_with' => 'Jumlah untuk biaya tambahan harus diisi.',
                    'costs.*.amount.numeric' => 'Jumlah untuk biaya tambahan harus berupa angka.',
                    'costs.*.amount.min' => 'Jumlah untuk biaya tambahan harus lebih besar dari atau sama dengan 0.',
                ]   
            );

        } else {
            return response()->json(['message' => "Status tidak valid. Harus berupa draft atau open."], 400);
        }
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
