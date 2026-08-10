<?php

namespace App\Services\Finance;
use App\Models\CashTransaction;
use App\Models\CashTransactionCost;
use App\Enums\AccountSettingEnum;
use App\Enums\CashTransactionStatusEnum;
use App\Services\JournalService;
use App\Enums\ExpenseStatus;
use App\Enums\PurchaseInvoiceStatus;
use App\Enums\PayablePaymentReferenceTypeEnum;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\Expense;
use App\Models\PayablePayment;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function generateNumber()
    {
        $prefix = 'CASH';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = CashTransaction::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function storeTransaction(Request $request, int $companyID)
    {
        $costCollection = collect($request->input('costs', []));
        DB::transaction(function () use ($request, $costCollection, $companyID) {
            $costTotalAmount = $costCollection->sum('amount');
            $taxAmount = $request->input('amount',0) * ($request->input('tax_percentage',0) / 100);
            $transaction = CashTransaction::create([
                'company_id' => $companyID,
                // 'account_id' => $request->input('account_id'),
                'from_account_id' => $request->input('from_account_id'),
                'to_account_id' => $request->input('to_account_id'),
                'contact_id' => $request->input('contact_id'),
                'reference_type' => $request->input('reference_type'),
                'reference_id' => $request->input('reference_id'),
                'number' => $this->generateNumber(),
                'reference_number' => $request->input('reference_number'),
                'transaction_date' => $request->input('transaction_date'),
                'type' => $request->input('type'),
                'status' => $request->input('status', 'draft'),
                'amount' => $request->input('amount',0),
                'tax_percentage' => $request->input('tax_percentage',0),
                'tax_amount' => $taxAmount,
                'total_amount' => $request->input('amount',0) + $taxAmount + $costTotalAmount,
                'description' => $request->input('description'),
                'created_by' => Auth::id(),
            ]);

            CashTransactionCost::where('cash_transaction_id', $transaction->id)->delete();
            foreach ($costCollection as $cost) {
                CashTransactionCost::create([
                    'cash_transaction_id' => $transaction->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'amount' => $cost['amount'] ?? 0,
                ]);
            }

            // Skip if have rererence type and reference id, because journal entry will be created in the reference model
            if ($transaction->status === CashTransactionStatusEnum::POSTED->value && !$transaction->reference_type && !$transaction->reference_id) {
                // TODO: Implement journal entry creation logic here using $this->journalService
            }
        });
    }

    // private function postCashJournal
}