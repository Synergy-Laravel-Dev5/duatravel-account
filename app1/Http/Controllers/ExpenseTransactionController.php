<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use App\Models\ExpenseTransaction;
use Illuminate\Http\Request;

class ExpenseTransactionController extends Controller
{
    public function index()
    {
        $package = session('dashboard_package', 'hajj');
        $year    = (int) session('dashboard_year', Carbon::now()->year);

        $transactions = ExpenseTransaction::with('expense')
            ->where('hajj_umrah', $package)
            ->where('year', $year)
            ->latest()
            ->get();

        return view('expense_transaction.index', compact('transactions', 'package', 'year'));
    }

    public function create()
    {
        $expenses = Expense::all();
        return view('expense_transaction.create', compact('expenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_id'    => 'required|exists:expenses,id',
            'hajj_umrah'    => 'required|in:hajj,umrah',
            'year'          => 'required|integer|digits:4|min:2000|max:2100',
            'amount'        => 'required|numeric|min:1',
            'payment_type'  => 'required|in:cash,bank,online,ewallet',
            'currency'      => 'required|in:PKR,SAR',
            'exchange_rate' => 'required_if:currency,SAR|nullable|numeric|min:0.01',
            'date'          => 'required|date',
            'reference_no'  => 'nullable|string|max:100',
            'proof'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'description'   => 'nullable|string',
        ]);

        $proofPath = null;

        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/expense_proofs'), $filename);
            $proofPath = $filename;
        }


        $exchangeRate = $request->currency === 'PKR' ? 1 : $request->exchange_rate;

        $transaction = ExpenseTransaction::create([
            'expense_id'    => $request->expense_id,
            'hajj_umrah'    => $request->hajj_umrah,
            'year'          => $request->year,
            'amount'        => $request->amount,
            'payment_type'  => $request->payment_type,
            'currency'      => $request->currency,
            'exchange_rate' => $exchangeRate,
            'reference_no'  => $request->reference_no,
            'proof'         => $proofPath,
            'date'          => $request->date,
            'description'   => $request->description,
        ]);

        logUserActivity('Expense Transaction Created', 'Amount: ' . $transaction->amount . ' | Currency: ' . $transaction->currency . ' | Type: ' . $transaction->payment_type . ' | Date: ' . $transaction->date, $transaction->id, 'ExpenseTransaction');

        return redirect()->route('expense.transaction.index')
            ->with('success', 'Expense Transaction created successfully.');
    }

    public function edit($id)
    {
        $transaction = ExpenseTransaction::findOrFail($id);
        $expenses    = Expense::all();

        return view('expense_transaction.edit', compact('transaction', 'expenses'));
    }

    public function update(Request $request, $id)
    {
        $transaction = ExpenseTransaction::findOrFail($id);

        $request->validate([
            'expense_id'    => 'required|exists:expenses,id',
            'hajj_umrah'    => 'required|in:hajj,umrah',
            'year'          => 'required|integer|digits:4|min:2000|max:2100',
            'amount'        => 'required|numeric|min:1',
            'payment_type'  => 'required|in:cash,bank,online,ewallet',
            'currency'      => 'required|in:PKR,SAR',
            'exchange_rate' => 'required_if:currency,SAR|nullable|numeric|min:0.01',
            'date'          => 'required|date',
            'reference_no'  => 'nullable|string|max:100',
            'proof'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'description'   => 'nullable|string',
        ]);

        if ($request->hasFile('proof')) {
            if ($transaction->proof) {
                $oldPath = public_path('assets/images/expense_proofs/' . $transaction->proof);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('proof');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/expense_proofs'), $filename);
            $transaction->proof = $filename;
        }

        $exchangeRate = $request->currency === 'PKR' ? 1 : $request->exchange_rate;

        $transaction->update([
            'expense_id'    => $request->expense_id,
            'hajj_umrah'    => $request->hajj_umrah,
            'year'          => $request->year,
            'amount'        => $request->amount,
            'payment_type'  => $request->payment_type,
            'currency'      => $request->currency,
            'exchange_rate' => $exchangeRate,
            'reference_no'  => $request->reference_no,
            'date'          => $request->date,
            'description'   => $request->description,
        ]);

        logUserActivity('Expense Transaction Updated', 'Amount: ' . $transaction->amount . ' | Currency: ' . $transaction->currency . ' | Type: ' . $transaction->payment_type . ' | Date: ' . $transaction->date, $transaction->id, 'ExpenseTransaction');

        return redirect()->route('expense.transaction.index')
            ->with('success', 'Expense Transaction updated successfully.');
    }

    public function destroy($id)
    {
        $transaction = ExpenseTransaction::findOrFail($id);

        if ($transaction->proof && file_exists(public_path('assets/images/expense_proofs/' . $transaction->proof))) {
            unlink(public_path('assets/images/expense_proofs/' . $transaction->proof));
        }

        logUserActivity('Expense Transaction Deleted', 'Amount: ' . $transaction->amount . ' | Type: ' . $transaction->payment_type . ' | Date: ' . $transaction->date, $transaction->id, 'ExpenseTransaction');

        $transaction->delete();

        return back()->with('success', 'Transaction deleted successfully.');
    }

    public function reportFilter()
    {
        $expenses = Expense::all();
        return view('expense_transaction.report_filter', compact('expenses'));
    }

    public function report(Request $request)
    {
        $request->validate([
            'hajj_umrah'   => 'required|in:hajj,umrah',
            'year'         => 'required|integer|digits:4|min:2000|max:2100',
            'expense_id'   => 'nullable|exists:expenses,id',
            'payment_type' => 'nullable|in:cash,bank,online,ewallet',
            'currency'     => 'nullable|in:PKR,SAR',
            'from_date'    => 'nullable|date',
            'to_date'      => 'nullable|date|after_or_equal:from_date',
        ]);

        $query = ExpenseTransaction::with('expense')
            ->where('hajj_umrah', $request->hajj_umrah)
            ->where('year', $request->year);

        if ($request->filled('expense_id')) {
            $query->where('expense_id', $request->expense_id);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('date', 'asc')->get();

        $filters  = $request->only(['hajj_umrah', 'year', 'expense_id', 'payment_type', 'currency', 'from_date', 'to_date']);
        $expenses = Expense::all();

        return view('expense_transaction.report', compact('transactions', 'filters', 'expenses'));
    }
}
