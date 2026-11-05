<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseTransaction;
use App\Models\Account;
use App\Models\Bank;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseLedgerController extends Controller
{
    /**
     * Standalone filter page for Expense Ledger (Dr / Cr)
     */
    public function ledgerFilter(Request $request)
    {
        $expenses = Expense::orderBy('name')->get();
        $cashAccounts = Account::where('status', 'active')->orderBy('account_name')->get();
        $banks = Bank::where('status', 'active')->orderBy('name')->get();

        return view('expense.ledger_filter', compact('expenses', 'cashAccounts', 'banks'));
    }

    /**
     * Complete double-entry Statement of Account (Ledger) for an Expense Head or Consolidated
     */
    public function ledgerView(Request $request)
    {
        $expenseId = $request->input('expense_id', 'all');
        $fromDate  = $request->from_date;
        $toDate    = $request->to_date;
        $package   = $request->input('package_type', $request->input('hajj_umrah', 'all'));
        $search    = $request->search;
        $accountId = $request->account_id;
        $bankId    = $request->bank_id;

        $allExpenses = Expense::orderBy('name')->get();

        if ($expenseId === 'all' || empty($expenseId)) {
            $expenseId = 'all';
            $expense = (object)[
                'id'          => 'all',
                'name'        => 'All Expenses (Consolidated Statement)',
                'description' => 'Comprehensive Statement of All Operational, Administrative & Financial Expenses',
            ];
        } else {
            $expense = Expense::find($expenseId);
            if (!$expense) {
                return redirect()->route('expense.ledger.filter')
                    ->with('error', 'Selected expense category was not found. Please select from the list.');
            }
        }

        // 1. Calculate Opening Balance prior to from_date
        $openingBalancePkr = 0.0;
        $openingBalanceSar = 0.0;

        if ($fromDate) {
            $priorQuery = ExpenseTransaction::whereDate('date', '<', $fromDate);

            if ($expenseId !== 'all') {
                $priorQuery->where('expense_id', $expenseId);
            } else {
                $priorQuery->where(function ($sub) {
                    $sub->whereNotNull('expense_id')
                        ->orWhere('payee_type', 'expense');
                });
            }

            if ($package && $package !== 'all') {
                $priorQuery->where('hajj_umrah', $package);
            }

            if ($bankId) {
                $priorQuery->where('bank_id', $bankId);
            }
            if ($accountId) {
                $priorQuery->where('account_id', $accountId);
            }

            $priorList = $priorQuery->get();

            $priorDebitsPkr  = 0.0;
            $priorCreditsPkr = 0.0;
            $priorDebitsSar  = 0.0;
            $priorCreditsSar = 0.0;

            foreach ($priorList as $pet) {
                $cur  = strtoupper($pet->currency ?: 'PKR');
                $rate = (float) ($pet->exchange_rate > 0 ? $pet->exchange_rate : 75);
                $raw  = (float) $pet->amount;

                if ($cur === 'SAR') {
                    $pkrVal = round($raw * $rate, 2);
                    $sarVal = $raw;
                } else {
                    $pkrVal = $raw;
                    $sarVal = ($rate > 1) ? round($raw / $rate, 2) : 0.00;
                }

                // In Expense Accounts: payment is DEBIT (expense increase), refund/credit note is CREDIT
                if ($pet->transaction_type === 'credit') {
                    $priorCreditsPkr += $pkrVal;
                    $priorCreditsSar += $sarVal;
                } else {
                    $priorDebitsPkr  += $pkrVal;
                    $priorDebitsSar  += $sarVal;
                }
            }

            $openingBalancePkr = $priorDebitsPkr - $priorCreditsPkr;
            $openingBalanceSar = $priorDebitsSar - $priorCreditsSar;
        }

        // 2. Query Transactions in the Selected Date Range
        $query = ExpenseTransaction::with(['expense', 'bank', 'account', 'staff', 'supplier', 'client']);

        if ($expenseId !== 'all') {
            $query->where('expense_id', $expenseId);
        } else {
            $query->where(function ($sub) {
                $sub->whereNotNull('expense_id')
                    ->orWhere('payee_type', 'expense');
            });
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        if ($package && $package !== 'all') {
            $query->where('hajj_umrah', $package);
        }

        if ($bankId) {
            $query->where('bank_id', $bankId);
        }
        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('expense', fn($e) => $e->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('bank', fn($b) => $b->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('account', fn($a) => $a->where('account_name', 'like', "%{$search}%"));
            });
        }

        $transactions = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        // 3. Process Statement Rows with Running Balance
        $runningBalancePkr = $openingBalancePkr;
        $runningBalanceSar = $openingBalanceSar;

        $totalDebitsPkr  = 0.0;
        $totalCreditsPkr = 0.0;
        $totalDebitsSar  = 0.0;
        $totalCreditsSar = 0.0;

        $statementLines = [];

        foreach ($transactions as $t) {
            $cur  = strtoupper($t->currency ?: 'PKR');
            $rate = (float) ($t->exchange_rate > 0 ? $t->exchange_rate : 75);
            $raw  = (float) $t->amount;

            if ($cur === 'SAR') {
                $pkrVal = round($raw * $rate, 2);
                $sarVal = $raw;
            } else {
                $pkrVal = $raw;
                $sarVal = ($rate > 1) ? round($raw / $rate, 2) : 0.00;
            }

            $isCredit = ($t->transaction_type === 'credit');

            if ($isCredit) {
                $debPkr = 0.00;
                $crdPkr = $pkrVal;
                $debSar = 0.00;
                $crdSar = $sarVal;
            } else {
                $debPkr = $pkrVal;
                $crdPkr = 0.00;
                $debSar = $sarVal;
                $crdSar = 0.00;
            }

            $runningBalancePkr += ($debPkr - $crdPkr);
            $runningBalanceSar += ($debSar - $crdSar);

            $totalDebitsPkr  += $debPkr;
            $totalCreditsPkr += $crdPkr;
            $totalDebitsSar  += $debSar;
            $totalCreditsSar += $crdSar;

            // Contra Account info
            $contraAccount = 'Cash Account';
            $contraType = 'Cash';
            if ($t->bank) {
                $contraAccount = $t->bank->name;
                $contraType = 'Bank';
            } elseif ($t->account) {
                $contraAccount = $t->account->account_name;
                $contraType = 'Cash A/C';
            } elseif ($t->payee_type === 'jv') {
                $contraAccount = 'Journal Voucher (JV)';
                $contraType = 'JV';
            } elseif ($t->supplier) {
                $contraAccount = $t->supplier->name;
                $contraType = 'Supplier';
            } elseif ($t->staff) {
                $contraAccount = $t->staff->first_name . ' ' . $t->staff->last_name;
                $contraType = 'Staff';
            }

            $headName = $t->expense ? $t->expense->name : 'General Expense';

            $statementLines[] = [
                'id'                  => $t->id,
                'date'                => $t->date ? Carbon::parse($t->date)->format('Y-m-d') : Carbon::parse($t->created_at)->format('Y-m-d'),
                'voucher_no'          => $t->reference_no ?: ('DT-EXP-' . str_pad($t->id, 6, '0', STR_PAD_LEFT)),
                'expense_category'    => $headName,
                'contra_account'      => $contraAccount,
                'contra_type'         => $contraType,
                'description'         => $t->description ?: "Payment for {$headName}",
                'payment_method'      => ucfirst($t->payment_type ?? 'cash'),
                'hajj_umrah'          => ucfirst($t->hajj_umrah ?? 'umrah'),
                'year'                => $t->year,
                'currency'            => $cur,
                'roe'                 => $rate,
                'debit_pkr'           => $debPkr,
                'credit_pkr'          => $crdPkr,
                'balance_pkr'         => $runningBalancePkr,
                'debit_sar'           => $debSar,
                'credit_sar'          => $crdSar,
                'balance_sar'         => $runningBalanceSar,
                'proof'               => $t->proof ? asset('assets/images/expense_proofs/' . $t->proof) : null,
                'proof_filename'      => $t->proof,
                'is_credit'           => $isCredit,
            ];
        }

        $closingBalancePkr = $runningBalancePkr;
        $closingBalanceSar = $runningBalanceSar;
        $absClosingPkr     = abs($closingBalancePkr);
        $absClosingSar     = abs($closingBalanceSar);
        $entriesCount      = count($statementLines);

        // Status badge
        if ($closingBalancePkr > 0) {
            $statusBadgeClass = 'bg-danger text-white';
            $statusLabel = 'Dr (Expense Incurred)';
        } elseif ($closingBalancePkr < 0) {
            $statusBadgeClass = 'bg-success text-white';
            $statusLabel = 'Cr (Net Refund / Credit)';
        } else {
            $statusBadgeClass = 'bg-secondary text-white';
            $statusLabel = 'Settled (0.00)';
        }

        return view('expense.ledger', compact(
            'expense',
            'expenseId',
            'allExpenses',
            'fromDate',
            'toDate',
            'package',
            'search',
            'openingBalancePkr',
            'openingBalanceSar',
            'statementLines',
            'totalDebitsPkr',
            'totalCreditsPkr',
            'totalDebitsSar',
            'totalCreditsSar',
            'closingBalancePkr',
            'closingBalanceSar',
            'absClosingPkr',
            'absClosingSar',
            'entriesCount',
            'statusBadgeClass',
            'statusLabel'
        ));
    }
}
