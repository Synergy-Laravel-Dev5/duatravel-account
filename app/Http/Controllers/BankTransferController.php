<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankTransfer;
use App\Models\Bank;

class BankTransferController extends Controller
{
    public function index()
    {
        $transfers = BankTransfer::with(['fromBank', 'toBank'])->latest()->get();
        return view('bank_transfer.index', compact('transfers'));
    }

    public function create()
    {
        $banks = Bank::where('status', 'active')->get();
        return view('bank_transfer.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_bank_id' => 'required|exists:banks,id|different:to_bank_id',
            'to_bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
        ]);
        
        BankTransfer::create($request->all());
        return redirect()->route('bank-transfer.index')->with('success', 'Transfer recorded successfully.');
    }

    public function destroy(BankTransfer $bankTransfer)
    {
        $bankTransfer->delete();
        return redirect()->route('bank-transfer.index')->with('success', 'Transfer deleted successfully.');
    }
}
