<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Bank;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::latest()->get();
        return view('account.index', compact('accounts'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $staffMembers = Staff::where('status', 'active')->orderBy('first_name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();

        return view('account.create', compact('clients', 'staffMembers', 'suppliers', 'banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_name'        => 'required|string|max:255',
            'account_type'        => 'required|in:cash,bank,general,loan_asset,loan_liability',
            'entity_type'         => 'nullable|string|max:50',
            'entity_id'           => 'nullable|integer',
            'opening_balance_pkr' => 'nullable|numeric',
            'opening_balance_sar' => 'nullable|numeric',
            'opening_balance_roe' => 'nullable|numeric',
            'status'              => 'required|in:active,inactive',
            'notes'               => 'nullable|string',
        ]);
        
        $obPkr = (float)($request->opening_balance_pkr ?? 0);
        $obSar = (float)($request->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : 75.0);
        if ($obRoe <= 1) {
            $obSar = 0.00;
        } else {
            if ($obSar == 0 && $obPkr != 0) {
                $obSar = round($obPkr / $obRoe, 2);
            } elseif ($obPkr == 0 && $obSar != 0) {
                $obPkr = round($obSar * $obRoe, 2);
            }
        }

        $entityType = $request->entity_type ?: null;
        $entityId = $request->entity_id ?: null;
        if ($entityType === 'staff' && $request->filled('staff_id')) {
            $entityId = $request->staff_id;
        } elseif ($entityType === 'client' && $request->filled('client_id')) {
            $entityId = $request->client_id;
        } elseif ($entityType === 'supplier' && $request->filled('supplier_id')) {
            $entityId = $request->supplier_id;
        } elseif ($entityType === 'bank' && $request->filled('bank_id')) {
            $entityId = $request->bank_id;
        }

        Account::create([
            'account_name'        => $request->account_name,
            'account_type'        => $request->account_type ?? 'cash',
            'entity_type'         => $entityType,
            'entity_id'           => $entityId,
            'opening_balance_pkr' => $obPkr,
            'opening_balance_sar' => $obSar,
            'opening_balance_roe' => $obRoe,
            'status'              => $request->status,
            'notes'               => $request->notes,
        ]);
        return redirect()->route('account.index')->with('success', 'Account created successfully.');
    }

    public function edit(Account $account)
    {
        $clients = Client::orderBy('name')->get();
        $staffMembers = Staff::where('status', 'active')->orderBy('first_name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();

        return view('account.edit', compact('account', 'clients', 'staffMembers', 'suppliers', 'banks'));
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'account_name'        => 'required|string|max:255',
            'account_type'        => 'required|in:cash,bank,general,loan_asset,loan_liability',
            'entity_type'         => 'nullable|string|max:50',
            'entity_id'           => 'nullable|integer',
            'opening_balance_pkr' => 'nullable|numeric',
            'opening_balance_sar' => 'nullable|numeric',
            'opening_balance_roe' => 'nullable|numeric',
            'status'              => 'required|in:active,inactive',
            'notes'               => 'nullable|string',
        ]);
        
        $obPkr = (float)($request->opening_balance_pkr ?? 0);
        $obSar = (float)($request->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : 75.0);
        if ($obRoe <= 1) {
            $obSar = 0.00;
        } else {
            if ($obSar == 0 && $obPkr != 0) {
                $obSar = round($obPkr / $obRoe, 2);
            } elseif ($obPkr == 0 && $obSar != 0) {
                $obPkr = round($obSar * $obRoe, 2);
            }
        }

        $entityType = $request->entity_type ?: null;
        $entityId = $request->entity_id ?: null;
        if ($entityType === 'staff' && $request->filled('staff_id')) {
            $entityId = $request->staff_id;
        } elseif ($entityType === 'client' && $request->filled('client_id')) {
            $entityId = $request->client_id;
        } elseif ($entityType === 'supplier' && $request->filled('supplier_id')) {
            $entityId = $request->supplier_id;
        } elseif ($entityType === 'bank' && $request->filled('bank_id')) {
            $entityId = $request->bank_id;
        }

        $account->update([
            'account_name'        => $request->account_name,
            'account_type'        => $request->account_type ?? 'cash',
            'entity_type'         => $entityType,
            'entity_id'           => $entityId,
            'opening_balance_pkr' => $obPkr,
            'opening_balance_sar' => $obSar,
            'opening_balance_roe' => $obRoe,
            'status'              => $request->status,
            'notes'               => $request->notes,
        ]);
        return redirect()->route('account.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return redirect()->route('account.index')->with('success', 'Account deleted successfully.');
    }
}
