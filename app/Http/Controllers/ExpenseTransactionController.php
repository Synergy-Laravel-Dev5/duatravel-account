<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Supplier;
use App\Models\Client;
use App\Models\Company;
use App\Models\Staff;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingVisa;
use App\Models\BookingTransport;
use App\Models\ExpenseTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseTransactionController extends Controller
{
    public function index()
    {
        $package = session('dashboard_package', 'hajj');
        $year    = (int) session('dashboard_year', Carbon::now()->year);

        $transactions = ExpenseTransaction::with(['expense', 'bank', 'supplier', 'client', 'staff'])
            ->where('hajj_umrah', $package)
            ->where('year', $year)
            ->latest('date')
            ->latest('id')
            ->get();

        return view('expense_transaction.index', compact('transactions', 'package', 'year'));
    }

    public function create()
    {
        $package = session('dashboard_package', 'hajj');
        $expenses = Expense::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        $clientsQ = Client::orderBy('name');
        if ($package === 'hajj') {
            $clientsQ->where(function ($q) {
                $q->where('package_type', 'hajj')->orWhereNull('package_type');
            })->where('package_type', '!=', 'umrah');
        } elseif ($package === 'umrah') {
            $clientsQ->where('package_type', 'umrah');
        }
        $clients = $clientsQ->get();

        $staffMembers = Staff::with(['department', 'designation'])->where('status', 'active')->orderBy('first_name')->get();
        $cashAccounts = Account::where('status', 'active')->orderBy('account_name')->get();

        $lastId = ExpenseTransaction::withTrashed()->max('id') ?? 0;
        $autoRef = 'DT-EXP-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
        $autoJvRef = 'DT-JV-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

        return view('expense_transaction.create', compact('expenses', 'banks', 'suppliers', 'clients', 'staffMembers', 'cashAccounts', 'autoRef', 'autoJvRef'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_id'          => 'nullable|exists:banks,id',
            'account_id'       => 'nullable|exists:accounts,id',
            'expense_id'       => 'nullable|exists:expenses,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'client_id'        => 'nullable|exists:clients,id',
            'staff_id'         => 'nullable|exists:staff,id',
            'hajj_umrah'       => 'required|in:hajj,umrah',
            'year'             => 'required|integer|digits:4|min:2000|max:2100',
            'amount'           => 'required|numeric|min:0.01',
            'payment_type'     => 'required|string|max:50',
            'transaction_type' => 'nullable|string',
            'payee_type'       => 'nullable|in:supplier,client,staff,expense,general,self,jv,loan',
            'currency'         => 'required|string|max:10',
            'exchange_rate'    => 'nullable|numeric|min:0.0001',
            'from_roe'         => 'nullable|numeric|min:0.0001',
            'to_roe'           => 'nullable|numeric|min:0.0001',
            'jv_from_roe'      => 'nullable|numeric|min:0.0001',
            'jv_to_roe'        => 'nullable|numeric|min:0.0001',
            'jv_contra_client_roe'   => 'nullable|numeric|min:0.0001',
            'jv_contra_supplier_roe' => 'nullable|numeric|min:0.0001',
            'date'             => 'required|date',
            'reference_no'     => 'nullable|string|max:100',
            'proof'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'description'      => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $file = $request->file('proof');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/images/expense_proofs'), $filename);
                $proofPath = $filename;
            }

            $currency = $request->currency ?: 'PKR';
            $exchangeRate = floatval($request->exchange_rate ?: ($currency === 'PKR' ? 1 : 1));
            if ($request->filled('exchange_rate') && floatval($request->exchange_rate) > 0) {
                $exchangeRate = floatval($request->exchange_rate);
            }
            $payeeType = $request->payee_type ?: 'supplier';

            $supplierId = ($payeeType === 'supplier') ? ($request->supplier_id ?: null) : null;
            $clientId   = ($payeeType === 'client') ? ($request->client_id ?: null) : null;
            $staffId    = ($payeeType === 'staff') ? ($request->staff_id ?: null) : null;
            $companyId  = ($payeeType === 'company') ? ($request->company_id ?: null) : null;
            $expenseId  = ($payeeType === 'expense' || $request->filled('expense_id')) ? ($request->expense_id ?: null) : null;

            $dateFormatted = Carbon::parse($request->date)->format('Y-m-d');
            $refNo = $request->reference_no ?: ('DT-EXP-' . str_pad((ExpenseTransaction::withTrashed()->max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT));

            if ($payeeType === 'jv') {
                $refNo = $request->reference_no ?: ('DT-JV-' . str_pad((ExpenseTransaction::withTrashed()->max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT));
                $jvMode = $request->input('jv_mode');

                $fromType = $request->input('jv_from_type');
                $fromId   = $request->input('jv_from_id');
                $toType   = $request->input('jv_to_type');
                $toId     = $request->input('jv_to_id');

                if ($request->filled('jv_from_account')) {
                    $parts = explode(':', $request->input('jv_from_account'), 2);
                    if (count($parts) === 2) {
                        $fromType = $parts[0];
                        $fromId   = $parts[1];
                    }
                }
                if ($request->filled('jv_to_account')) {
                    $parts = explode(':', $request->input('jv_to_account'), 2);
                    if (count($parts) === 2) {
                        $toType = $parts[0];
                        $toId   = $parts[1];
                    }
                }

                // Case 0: Universal JV (From Account ➔ To Account)
                if (($jvMode === 'universal' || empty($jvMode) || $jvMode === 'transfer') && $fromType && $fromId && $toType && $toId) {

                    $mapEntity = function($type, $id, &$fields, &$name) {
                        switch($type) {
                            case 'client':
                                $fields['client_id'] = $id;
                                $obj = Client::find($id);
                                $name = $obj ? "Client: {$obj->name}" : "Client #{$id}";
                                break;
                            case 'supplier':
                                $fields['supplier_id'] = $id;
                                $obj = Supplier::find($id);
                                $name = $obj ? "Supplier: {$obj->name}" : "Supplier #{$id}";
                                break;
                            case 'staff':
                                $fields['staff_id'] = $id;
                                $obj = Staff::find($id);
                                $name = $obj ? "Staff: {$obj->first_name} {$obj->last_name}" : "Staff #{$id}";
                                break;
                            case 'bank':
                                $fields['bank_id'] = $id;
                                $obj = Bank::find($id);
                                $name = $obj ? "Bank: {$obj->name}" : "Bank #{$id}";
                                break;
                            case 'cash':
                            case 'loan':
                            case 'account':
                                $fields['account_id'] = $id;
                                $obj = Account::find($id);
                                $name = $obj ? "Account: {$obj->account_name}" : "Account #{$id}";
                                break;
                            case 'expense':
                                $fields['expense_id'] = $id;
                                $obj = Expense::find($id);
                                $name = $obj ? "Expense: {$obj->name}" : "Expense #{$id}";
                                break;
                            default:
                                $name = "Party #{$id}";
                        }
                    };

                    $fromFields = [
                        'bank_id' => null, 'expense_id' => null, 'supplier_id' => null,
                        'client_id' => null, 'company_id' => null, 'staff_id' => null, 'account_id' => null
                    ];
                    $toFields = [
                        'bank_id' => null, 'expense_id' => null, 'supplier_id' => null,
                        'client_id' => null, 'company_id' => null, 'staff_id' => null, 'account_id' => null
                    ];
                    $fromName = '';
                    $toName = '';

                    $mapEntity($fromType, $fromId, $fromFields, $fromName);
                    $mapEntity($toType, $toId, $toFields, $toName);

                    $fromRoe = (float) ($request->input('jv_from_roe') ?: ($request->input('from_roe') ?: $exchangeRate));
                    $toRoe   = (float) ($request->input('jv_to_roe') ?: ($request->input('to_roe') ?: $exchangeRate));
                    if ($fromRoe <= 0) $fromRoe = ($exchangeRate > 0 ? $exchangeRate : 76.00);
                    if ($toRoe <= 0) $toRoe = ($exchangeRate > 0 ? $exchangeRate : 75.00);

                    $userNotes = $request->description ? (' — ' . $request->description) : '';
                    $fromDesc = "JV Transfer TO [{$toName}] @ ROE {$fromRoe}{$userNotes}";
                    $toDesc   = "JV Transfer FROM [{$fromName}] @ ROE {$toRoe}{$userNotes}";

                    // Leg 1: Outgoing / Credit from From-Account (Leg 1 ROE)
                    $t1 = ExpenseTransaction::create(array_merge($fromFields, [
                        'hajj_umrah'       => $request->hajj_umrah,
                        'year'             => $request->year,
                        'amount'           => $request->amount,
                        'payment_type'     => 'jv',
                        'transaction_type' => 'credit',
                        'payee_type'       => 'jv',
                        'currency'         => $currency,
                        'exchange_rate'    => $fromRoe,
                        'reference_no'     => $refNo,
                        'proof'            => $proofPath,
                        'date'             => $dateFormatted,
                        'description'      => $fromDesc,
                    ]));

                    // Leg 2: Incoming / Debit to To-Account (Leg 2 ROE)
                    $t2 = ExpenseTransaction::create(array_merge($toFields, [
                        'hajj_umrah'       => $request->hajj_umrah,
                        'year'             => $request->year,
                        'amount'           => $request->amount,
                        'payment_type'     => 'jv',
                        'transaction_type' => 'debit',
                        'payee_type'       => 'jv',
                        'currency'         => $currency,
                        'exchange_rate'    => $toRoe,
                        'reference_no'     => $refNo,
                        'proof'            => $proofPath,
                        'date'             => $dateFormatted,
                        'description'      => $toDesc,
                    ]));

                    logUserActivity('Transaction Created', 'Type: jv (universal) | From: ' . $fromName . ' | To: ' . $toName . ' | Ref: ' . $refNo, $t1->id, 'ExpenseTransaction');
                    DB::commit();
                    return redirect()->route('expense.transaction.index')
                        ->with('success', "Universal Journal Voucher recorded successfully ({$fromName} ➔ {$toName}).");
                }

                // Case 1: Multi-Client Journal Entries
                if ($jvMode === 'multi_client' && $request->has('jv_clients')) {
                    $jvClients = $request->input('jv_clients', []);
                    $created = [];
                    foreach ($jvClients as $jc) {
                        $cId = $jc['client_id'] ?? null;
                        $cAmt = (float)($jc['amount'] ?? 0);
                        if (!$cId || $cAmt <= 0) continue;

                        $cType = $jc['type'] ?? 'debit';
                        $cNotes = !empty($jc['notes']) ? $jc['notes'] : ($request->description ?: 'JV Multi-Client Adjustment');

                        $t = ExpenseTransaction::create([
                            'bank_id'          => $request->bank_id ?: null,
                            'expense_id'       => null,
                            'supplier_id'      => null,
                            'client_id'        => $cId,
                            'company_id'       => null,
                            'staff_id'         => null,
                            'hajj_umrah'       => $request->hajj_umrah,
                            'year'             => $request->year,
                            'amount'           => $cAmt,
                            'payment_type'     => 'jv',
                            'transaction_type' => $cType,
                            'payee_type'       => 'jv',
                            'currency'         => $currency,
                            'exchange_rate'    => $exchangeRate,
                            'reference_no'     => $refNo,
                            'proof'            => $proofPath,
                            'date'             => $dateFormatted,
                            'description'      => $cNotes,
                        ]);
                        $created[] = $t;
                    }

                    if (!empty($created)) {
                        logUserActivity('Transaction Created', 'Type: jv (multi_client) | Entries: ' . count($created) . ' | Ref: ' . $refNo, $created[0]->id, 'ExpenseTransaction');
                        DB::commit();
                        return redirect()->route('expense.transaction.index')
                            ->with('success', 'Journal Voucher (Multi-Client) recorded successfully.');
                    }
                }

                // Case 2: Client & Supplier Contra / Adjustment (Both in One JV)
                $jvToggle = $request->input('jv_party_toggle');
                $hasBoth = ($jvToggle === 'both') || ($request->filled('jv_client_id') && $request->filled('jv_supplier_id'));
                if ($jvMode === 'client_supplier' || $hasBoth) {
                    $clientId = $request->jv_client_id ?: ($request->client_id ?: null);
                    $supplierId = $request->jv_supplier_id ?: ($request->supplier_id ?: null);

                    if ($clientId && $supplierId) {
                        $direction = $request->input('jv_cs_direction', 'client_to_supplier');
                        $clientObj = Client::find($clientId);
                        $supplierObj = Supplier::find($supplierId);
                        $clientName = $clientObj ? $clientObj->name : "Client #$clientId";
                        $supplierName = $supplierObj ? $supplierObj->name : "Supplier #$supplierId";

                        $clientType = ($direction === 'supplier_to_client') ? 'debit' : 'credit';
                        $supplierType = ($direction === 'supplier_to_client') ? 'credit' : 'debit';

                        $clientRoe   = (float) ($request->input('jv_contra_client_roe') ?: ($request->input('jv_from_roe') ?: $exchangeRate));
                        $supplierRoe = (float) ($request->input('jv_contra_supplier_roe') ?: ($request->input('jv_to_roe') ?: $exchangeRate));
                        if ($clientRoe <= 0) $clientRoe = ($exchangeRate > 0 ? $exchangeRate : 76.00);
                        if ($supplierRoe <= 0) $supplierRoe = ($exchangeRate > 0 ? $exchangeRate : 75.00);

                        $dirLabel = ($direction === 'supplier_to_client') ? 'Supplier Refund to Client' : 'Client Payment to Supplier';
                        $userDesc = $request->description ? (' — ' . $request->description) : '';

                        $clientDesc = "JV Contra [Supplier: {$supplierName}] ({$dirLabel}) @ ROE {$clientRoe}{$userDesc}";
                        $supplierDesc = "JV Contra [Client: {$clientName}] ({$dirLabel}) @ ROE {$supplierRoe}{$userDesc}";

                        // Leg 1: Client side (client_id set, supplier_id null so supplier ledger doesn't double-count)
                        $t1 = ExpenseTransaction::create([
                            'bank_id'          => $request->bank_id ?: null,
                            'account_id'       => $request->account_id ?: null,
                            'expense_id'       => null,
                            'supplier_id'      => null,
                            'client_id'        => $clientId,
                            'company_id'       => null,
                            'staff_id'         => null,
                            'hajj_umrah'       => $request->hajj_umrah,
                            'year'             => $request->year,
                            'amount'           => $request->amount,
                            'payment_type'     => 'jv',
                            'transaction_type' => $clientType,
                            'payee_type'       => 'jv',
                            'currency'         => $currency,
                            'exchange_rate'    => $clientRoe,
                            'reference_no'     => $refNo,
                            'proof'            => $proofPath,
                            'date'             => $dateFormatted,
                            'description'      => $clientDesc,
                        ]);

                        // Leg 2: Supplier side (supplier_id set, client_id null so client ledger doesn't double-count)
                        $t2 = ExpenseTransaction::create([
                            'bank_id'          => $request->bank_id ?: null,
                            'account_id'       => $request->account_id ?: null,
                            'expense_id'       => null,
                            'supplier_id'      => $supplierId,
                            'client_id'        => null,
                            'company_id'       => null,
                            'staff_id'         => null,
                            'hajj_umrah'       => $request->hajj_umrah,
                            'year'             => $request->year,
                            'amount'           => $request->amount,
                            'payment_type'     => 'jv',
                            'transaction_type' => $supplierType,
                            'payee_type'       => 'jv',
                            'currency'         => $currency,
                            'exchange_rate'    => $supplierRoe,
                            'reference_no'     => $refNo,
                            'proof'            => $proofPath,
                            'date'             => $dateFormatted,
                            'description'      => $supplierDesc,
                        ]);

                        logUserActivity('Transaction Created', 'Type: jv (client_supplier) | Ref: ' . $refNo, $t1->id, 'ExpenseTransaction');
                        DB::commit();
                        return redirect()->route('expense.transaction.index')
                            ->with('success', 'Journal Voucher (Client & Supplier Contra) recorded successfully.');
                    }
                }

                // Case 3: Single Party or General JV
                if ($request->filled('jv_single_party')) {
                    $parts = explode(':', $request->input('jv_single_party'), 2);
                    if (count($parts) === 2) {
                        $pType = $parts[0];
                        $pId   = $parts[1];
                        if ($pType === 'client') $clientId = $pId;
                        elseif ($pType === 'supplier') $supplierId = $pId;
                        elseif ($pType === 'staff') $staffId = $pId;
                        elseif ($pType === 'expense') $expenseId = $pId;
                        elseif ($pType === 'account') $request->merge(['account_id' => $pId]);
                        elseif ($pType === 'bank') $request->merge(['bank_id' => $pId]);
                    }
                } elseif ($request->filled('jv_client_id') || $request->input('jv_party_toggle') === 'client') {
                    $clientId = $request->jv_client_id ?: ($request->client_id ?: null);
                    $supplierId = null;
                } elseif ($request->filled('jv_supplier_id') || $request->input('jv_party_toggle') === 'supplier') {
                    $supplierId = $request->jv_supplier_id ?: ($request->supplier_id ?: null);
                    $clientId = null;
                } elseif ($jvMode === 'single_party') {
                    $singleType = $request->input('jv_single_type', 'client');
                    if ($singleType === 'client') {
                        $clientId = $request->jv_single_client_id ?: ($request->jv_client_id ?: null);
                        $supplierId = null;
                    } else {
                        $supplierId = $request->jv_single_supplier_id ?: ($request->jv_supplier_id ?: null);
                        $clientId = null;
                    }
                } else {
                    $clientId = null;
                    $supplierId = null;
                }
            }

            $accountId = null;
            if ($payeeType === 'loan') {
                $accountId = $request->loan_account_id ?: ($request->account_id ?: null);
            } elseif ($request->payment_type === 'cash' || $request->filled('account_id')) {
                $accountId = $request->account_id ?: null;
            }

            $transaction = ExpenseTransaction::create([
                'bank_id'          => $request->bank_id ?: null,
                'account_id'       => $accountId,
                'expense_id'       => $expenseId,
                'supplier_id'      => $supplierId,
                'client_id'        => $clientId,
                'company_id'       => $companyId,
                'staff_id'         => $staffId,
                'hajj_umrah'       => $request->hajj_umrah,
                'year'             => $request->year,
                'amount'           => $request->amount,
                'payment_type'     => ($payeeType === 'jv') ? 'jv' : $request->payment_type,
                'transaction_type' => $request->transaction_type ?: 'debit',
                'payee_type'       => $payeeType,
                'currency'         => $currency,
                'exchange_rate'    => $exchangeRate,
                'reference_no'     => $refNo,
                'proof'            => $proofPath,
                'date'             => $dateFormatted,
                'description'      => $request->description,
            ]);

            logUserActivity('Transaction Created', 'Type: ' . $payeeType . ' | Amount: ' . $transaction->amount . ' | Currency: ' . $transaction->currency . ' | Date: ' . $transaction->date, $transaction->id, 'ExpenseTransaction');

            DB::commit();

            return redirect()->route('expense.transaction.index')
                ->with('success', 'Transaction recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to save transaction: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $transaction = ExpenseTransaction::with(['bank', 'supplier', 'client', 'staff', 'expense'])->findOrFail($id);
        $package = $transaction->hajj_umrah ?: session('dashboard_package', 'hajj');
        $expenses = Expense::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        $clientsQ = Client::orderBy('name');
        if ($package === 'hajj') {
            $clientsQ->where(function ($q) {
                $q->where('package_type', 'hajj')->orWhereNull('package_type');
            })->where('package_type', '!=', 'umrah');
        } elseif ($package === 'umrah') {
            $clientsQ->where('package_type', 'umrah');
        }
        $clients = $clientsQ->get();

        $staffMembers = Staff::with(['department', 'designation'])->where('status', 'active')->orderBy('first_name')->get();
        $cashAccounts = Account::where('status', 'active')->orderBy('account_name')->get();

        $relatedJvEntries = collect();
        if ($transaction->payee_type === 'jv' && !empty($transaction->reference_no)) {
            $relatedJvEntries = ExpenseTransaction::with(['client', 'supplier', 'bank', 'staff', 'expense', 'account'])
                ->where('payee_type', 'jv')
                ->where('reference_no', $transaction->reference_no)
                ->where('id', '!=', $transaction->id)
                ->get();
        }

        return view('expense_transaction.edit', compact('transaction', 'expenses', 'banks', 'suppliers', 'clients', 'staffMembers', 'cashAccounts', 'relatedJvEntries'));
    }

    public function update(Request $request, $id)
    {
        $transaction = ExpenseTransaction::findOrFail($id);

        $request->validate([
            'bank_id'          => 'nullable|exists:banks,id',
            'account_id'       => 'nullable|exists:accounts,id',
            'expense_id'       => 'nullable|exists:expenses,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'client_id'        => 'nullable|exists:clients,id',
            'staff_id'         => 'nullable|exists:staff,id',
            'hajj_umrah'       => 'required|in:hajj,umrah',
            'year'             => 'required|integer|digits:4|min:2000|max:2100',
            'amount'           => 'required|numeric|min:0.01',
            'payment_type'     => 'required|string|max:50',
            'transaction_type' => 'nullable|string',
            'payee_type'       => 'nullable|in:supplier,client,staff,expense,general,self,jv,loan',
            'currency'         => 'required|string|max:10',
            'exchange_rate'    => 'nullable|numeric|min:0.0001',
            'from_roe'         => 'nullable|numeric|min:0.0001',
            'to_roe'           => 'nullable|numeric|min:0.0001',
            'jv_from_roe'      => 'nullable|numeric|min:0.0001',
            'jv_to_roe'        => 'nullable|numeric|min:0.0001',
            'jv_contra_client_roe'   => 'nullable|numeric|min:0.0001',
            'jv_contra_supplier_roe' => 'nullable|numeric|min:0.0001',
            'date'             => 'required|date',
            'reference_no'     => 'nullable|string|max:100',
            'proof'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'description'      => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('proof')) {
                if ($transaction->proof) {
                    $oldPath = public_path('assets/images/expense_proofs/' . $transaction->proof);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $file = $request->file('proof');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/images/expense_proofs'), $filename);
                $transaction->proof = $filename;
            }

            $currency = $request->currency ?: 'PKR';
            $exchangeRate = floatval($request->exchange_rate ?: ($currency === 'PKR' ? 1 : 1));
            if ($request->filled('exchange_rate') && floatval($request->exchange_rate) > 0) {
                $exchangeRate = floatval($request->exchange_rate);
            }
            $payeeType = $request->payee_type ?: 'supplier';

            $supplierId = ($payeeType === 'supplier') ? ($request->supplier_id ?: null) : null;
            $clientId   = ($payeeType === 'client') ? ($request->client_id ?: null) : null;
            $staffId    = ($payeeType === 'staff') ? ($request->staff_id ?: null) : null;
            $companyId  = null;
            $expenseId  = ($payeeType === 'expense' || $request->filled('expense_id')) ? ($request->expense_id ?: null) : null;

            if ($payeeType === 'jv') {
                if ($request->filled('jv_client_id')) {
                    $clientId = $request->jv_client_id;
                } elseif ($request->filled('client_id')) {
                    $clientId = $request->client_id;
                }
                if ($request->filled('jv_supplier_id')) {
                    $supplierId = $request->jv_supplier_id;
                } elseif ($request->filled('supplier_id')) {
                    $supplierId = $request->supplier_id;
                }
            }

            $dateFormatted = Carbon::parse($request->date)->format('Y-m-d');

            $accountId = null;
            if ($payeeType === 'loan') {
                $accountId = $request->loan_account_id ?: ($request->account_id ?: null);
            } elseif ($request->payment_type === 'cash' || $request->filled('account_id')) {
                $accountId = $request->account_id ?: null;
            }

            $transaction->update([
                'bank_id'          => $request->bank_id ?: null,
                'account_id'       => $accountId,
                'expense_id'       => $expenseId,
                'supplier_id'      => $supplierId,
                'client_id'        => $clientId,
                'company_id'       => $companyId,
                'staff_id'         => $staffId,
                'hajj_umrah'       => $request->hajj_umrah,
                'year'             => $request->year,
                'amount'           => $request->amount,
                'payment_type'     => ($payeeType === 'jv') ? 'jv' : $request->payment_type,
                'transaction_type' => $request->transaction_type ?: 'debit',
                'payee_type'       => $payeeType,
                'currency'         => $currency,
                'exchange_rate'    => $exchangeRate,
                'reference_no'     => $request->reference_no,
                'date'             => $dateFormatted,
                'description'      => $request->description,
            ]);

            logUserActivity('Transaction Updated', 'Type: ' . $payeeType . ' | Amount: ' . $transaction->amount . ' | Currency: ' . $transaction->currency . ' | Date: ' . $transaction->date, $transaction->id, 'ExpenseTransaction');

            DB::commit();

            return redirect()->route('expense.transaction.index')
                ->with('success', 'Transaction updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update transaction: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $transaction = ExpenseTransaction::findOrFail($id);

        if ($transaction->proof && file_exists(public_path('assets/images/expense_proofs/' . $transaction->proof))) {
            unlink(public_path('assets/images/expense_proofs/' . $transaction->proof));
        }

        logUserActivity('Transaction Deleted', 'Amount: ' . $transaction->amount . ' | Date: ' . $transaction->date, $transaction->id, 'ExpenseTransaction');

        if ($transaction->payee_type === 'jv' && !empty($transaction->reference_no)) {
            ExpenseTransaction::where('payee_type', 'jv')->where('reference_no', $transaction->reference_no)->delete();
        } else {
            $transaction->delete();
        }

        return back()->with('success', 'Transaction deleted successfully.');
    }

    public function reportFilter()
    {
        $expenses = Expense::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        $staffMembers = Staff::where('status', 'active')->orderBy('first_name')->get();

        return view('expense_transaction.report_filter', compact('expenses', 'banks', 'suppliers', 'clients', 'staffMembers'));
    }

    public function report(Request $request)
    {
        $request->validate([
            'hajj_umrah'   => 'nullable|string',
            'year'         => 'nullable|integer|digits:4|min:2000|max:2100',
            'payee_type'   => 'nullable|string',
            'bank_id'      => 'nullable|exists:banks,id',
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'client_id'    => 'nullable|exists:clients,id',
            'expense_id'   => 'nullable|exists:expenses,id',
            'staff_id'     => 'nullable|exists:staff,id',
            'payment_type' => 'nullable|string',
            'currency'     => 'nullable|in:PKR,SAR,USD',
            'from_date'    => 'nullable|date',
            'to_date'      => 'nullable|date|after_or_equal:from_date',
        ]);

        $query = ExpenseTransaction::with(['expense', 'bank', 'supplier', 'client', 'staff']);

        if ($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all') {
            $query->where('hajj_umrah', $request->hajj_umrah);
        }

        if ($request->filled('year') && $request->year !== 'all') {
            $query->where('year', $request->year);
        }

        if ($request->filled('payee_type')) {
            $query->where('payee_type', $request->payee_type);
        }

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('expense_id')) {
            $query->where('expense_id', $request->expense_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', Carbon::parse($request->from_date)->format('Y-m-d'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', Carbon::parse($request->to_date)->format('Y-m-d'));
        }

        $transactions = $query->orderBy('date', 'asc')->get();

        $selectedSupplier = null;
        $supplierHotels = collect();
        $supplierFlights = collect();
        $supplierVisas = collect();
        $supplierTransports = collect();
        $supplierSummary = null;

        if ($request->filled('supplier_id')) {
            $selectedSupplier = Supplier::find($request->supplier_id);
            if ($selectedSupplier) {
                $supplierHotels = BookingHotel::where('supplier_id', $selectedSupplier->id)
                    ->whereHas('booking', function ($q) use ($request) {
                        if ($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all') {
                            $q->where('package_type', $request->hajj_umrah);
                        }
                        if ($request->filled('year') && $request->year !== 'all') {
                            $q->where('package_year', $request->year);
                        }
                    })
                    ->with(['booking.client'])
                    ->get();

                $supplierFlights = Booking::with('persons')
                    ->where(function ($q) use ($selectedSupplier) {
                        $q->whereHas('persons', function ($pq) use ($selectedSupplier) {
                            $pq->where('flight_supplier_id', $selectedSupplier->id);
                        })->orWhere(function ($mq) use ($selectedSupplier) {
                            $mq->where('flight_supplier_id', $selectedSupplier->id)
                               ->where(function ($sub) {
                                   $sub->doesntHave('persons')
                                       ->orWhereHas('persons', function ($pq) {
                                           $pq->whereNull('flight_supplier_id')->orWhere('flight_supplier_id', '');
                                       });
                               });
                        });
                    })
                    ->when($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all', fn($q) => $q->where('package_type', $request->hajj_umrah))
                    ->when($request->filled('year') && $request->year !== 'all', fn($q) => $q->where('package_year', $request->year))
                    ->with('client')
                    ->get();

                $totalFlightCost = 0.00;
                foreach ($supplierFlights as $sf) {
                    $sfPersons = $sf->persons ? $sf->persons->filter(function ($p) use ($selectedSupplier, $sf) {
                        if (!empty($p->flight_supplier_id)) {
                            return (int) $p->flight_supplier_id === (int) $selectedSupplier->id;
                        }
                        return (int) $sf->flight_supplier_id === (int) $selectedSupplier->id;
                    }) : collect();

                    $pCost = $sfPersons->sum(function ($p) {
                        return (float) $p->flight_cost;
                    });

                    if ($pCost > 0) {
                        $totalFlightCost += $pCost;
                    } else {
                        $totPax = (int) ($sf->no_of_pax ?: 1);
                        $sPax = $sfPersons->count() ?: $totPax;
                        $totalFlightCost += round(((float) $sf->flight_cost) * ($sPax / $totPax), 2);
                    }
                }

                $supplierVisas = BookingVisa::where('supplier_id', $selectedSupplier->id)
                    ->whereHas('booking', function ($q) use ($request) {
                        if ($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all') {
                            $q->where('package_type', $request->hajj_umrah);
                        }
                        if ($request->filled('year') && $request->year !== 'all') {
                            $q->where('package_year', $request->year);
                        }
                    })
                    ->with(['booking.client'])
                    ->get();

                $supplierTransports = BookingTransport::where('supplier_id', $selectedSupplier->id)
                    ->whereHas('booking', function ($q) use ($request) {
                        if ($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all') {
                            $q->where('package_type', $request->hajj_umrah);
                        }
                        if ($request->filled('year') && $request->year !== 'all') {
                            $q->where('package_year', $request->year);
                        }
                    })
                    ->with(['booking.client'])
                    ->get();

                $totalHotelCost     = (float) $supplierHotels->sum('cost_amount');
                // $totalFlightCost is calculated per-passenger above
                $totalVisaCost      = (float) $supplierVisas->sum('cost_amount');
                $totalTransportCost = (float) $supplierTransports->sum('cost_amount');
                $totalPayable       = $totalHotelCost + $totalFlightCost + $totalVisaCost + $totalTransportCost;

                $totalPaid = (float) ExpenseTransaction::where('supplier_id', $selectedSupplier->id)
                    ->when($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all', fn($q) => $q->where('hajj_umrah', $request->hajj_umrah))
                    ->when($request->filled('year') && $request->year !== 'all', fn($q) => $q->where('year', $request->year))
                    ->sum('amount');

                $supplierSummary = [
                    'total_hotel_cost'     => $totalHotelCost,
                    'total_flight_cost'    => $totalFlightCost,
                    'total_visa_cost'      => $totalVisaCost,
                    'total_transport_cost' => $totalTransportCost,
                    'total_payable'        => $totalPayable,
                    'total_paid'           => $totalPaid,
                    'balance_due'          => $totalPayable - $totalPaid,
                ];
            }
        }

        $selectedClient = null;
        $clientBookings = collect();
        $clientSummary  = null;

        if ($request->filled('client_id')) {
            $selectedClient = Client::find($request->client_id);
            if ($selectedClient) {
                $clientBookings = Booking::where('client_id', $selectedClient->id)
                    ->when($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all', fn($q) => $q->where('package_type', $request->hajj_umrah))
                    ->when($request->filled('year') && $request->year !== 'all', fn($q) => $q->where('package_year', $request->year))
                    ->with(['hotels', 'visas', 'transports'])
                    ->latest()
                    ->get();

                $totalBookingAmount = (float) $clientBookings->sum('total_amount');
                $bookingReceived    = (float) $clientBookings->sum('total_received');

                $clientExpensePaid = (float) ExpenseTransaction::where('client_id', $selectedClient->id)
                    ->when($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all', fn($q) => $q->where('hajj_umrah', $request->hajj_umrah))
                    ->when($request->filled('year') && $request->year !== 'all', fn($q) => $q->where('year', $request->year))
                    ->sum('amount');

                $totalReceived = $bookingReceived + $clientExpensePaid;

                $clientSummary = [
                    'total_bookings_amount' => $totalBookingAmount,
                    'booking_received'      => $bookingReceived,
                    'transaction_paid'      => $clientExpensePaid,
                    'total_received'        => $totalReceived,
                    'balance_due'           => $totalBookingAmount - $totalReceived,
                ];
            }
        }

        $filters  = $request->all();
        $expenses = Expense::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        $staffMembers = Staff::where('status', 'active')->orderBy('first_name')->get();

        return view('expense_transaction.report', compact(
            'transactions',
            'filters',
            'expenses',
            'banks',
            'suppliers',
            'clients',
            'staffMembers',
            'selectedSupplier',
            'supplierHotels',
            'supplierFlights',
            'supplierVisas',
            'supplierTransports',
            'supplierSummary',
            'selectedClient',
            'clientBookings',
            'clientSummary'
        ));
    }
}
