<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::latest()->get();
        return view('bank.index', compact('banks'));
    }

    public function create()
    {
        return view('bank.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'account_name'        => 'nullable|string|max:255',
            'account_number'      => 'nullable|string|max:100',
            'branch'              => 'nullable|string|max:255',
            'account_type'        => 'nullable|in:bank,cash',
            'opening_balance_pkr' => 'nullable|numeric',
            'opening_balance_sar' => 'nullable|numeric',
            'opening_balance_roe' => 'nullable|numeric',
            'status'              => 'required|in:active,inactive',
        ]);
        
        $obPkr = (float)($request->opening_balance_pkr ?? 0);
        $obSar = (float)($request->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : 75.0);
        if ($obSar == 0 && $obPkr != 0) {
            $obSar = round($obPkr / $obRoe, 2);
        } elseif ($obPkr == 0 && $obSar != 0) {
            $obPkr = round($obSar * $obRoe, 2);
        }

        Bank::create([
            'name'                => $request->name,
            'account_name'        => $request->account_name,
            'account_number'      => $request->account_number,
            'branch'              => $request->branch,
            'account_type'        => $request->account_type ?? 'bank',
            'opening_balance_pkr' => $obPkr,
            'opening_balance_sar' => $obSar,
            'opening_balance_roe' => $obRoe,
            'status'              => $request->status,
        ]);
        return redirect()->route('bank.index')->with('success', 'Account created successfully.');
    }

    public function edit(Bank $bank)
    {
        return view('bank.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'account_name'        => 'nullable|string|max:255',
            'account_number'      => 'nullable|string|max:100',
            'branch'              => 'nullable|string|max:255',
            'account_type'        => 'nullable|in:bank,cash',
            'opening_balance_pkr' => 'nullable|numeric',
            'opening_balance_sar' => 'nullable|numeric',
            'opening_balance_roe' => 'nullable|numeric',
            'status'              => 'required|in:active,inactive',
        ]);

        $obPkr = (float)($request->opening_balance_pkr ?? 0);
        $obSar = (float)($request->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : 75.0);
        if ($obSar == 0 && $obPkr != 0) {
            $obSar = round($obPkr / $obRoe, 2);
        } elseif ($obPkr == 0 && $obSar != 0) {
            $obPkr = round($obSar * $obRoe, 2);
        }
        
        $bank->update([
            'name'                => $request->name,
            'account_name'        => $request->account_name,
            'account_number'      => $request->account_number,
            'branch'              => $request->branch,
            'account_type'        => $request->account_type ?? 'bank',
            'opening_balance_pkr' => $obPkr,
            'opening_balance_sar' => $obSar,
            'opening_balance_roe' => $obRoe,
            'status'              => $request->status,
        ]);
        return redirect()->route('bank.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('bank.index')->with('success', 'Bank deleted successfully.');
    }
}
