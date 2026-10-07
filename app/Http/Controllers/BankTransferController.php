<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankTransfer;
use App\Models\Bank;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BankTransferController extends Controller
{
    public function index()
    {
        $transfers = BankTransfer::with(['fromBank', 'toBank'])->latest('transfer_date')->latest('id')->get();
        return view('bank_transfer.index', compact('transfers'));
    }

    public function create()
    {
        $banks = Bank::where('status', 'active')->orderBy('name')->get();
        return view('bank_transfer.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_bank_id' => 'required|exists:banks,id|different:to_bank_id',
            'to_bank_id'   => 'required|exists:banks,id',
            'amount'       => 'required|numeric|min:1',
            'transfer_date'=> 'required|date',
        ]);
        
        $transfer = BankTransfer::create($request->all());

        if (function_exists('logUserActivity')) {
            $fromName = Bank::find($request->from_bank_id)?->name ?? 'Bank';
            $toName = Bank::find($request->to_bank_id)?->name ?? 'Bank';
            logUserActivity('Bank Transfer Created', "Transferred PKR " . number_format($request->amount, 2) . " from {$fromName} to {$toName}", $transfer->id, 'BankTransfer');
        }

        return redirect()->route('bank-transfer.index')->with('success', 'Transfer recorded successfully.');
    }

    public function destroy(BankTransfer $bankTransfer)
    {
        $bankTransfer->delete();
        return redirect()->route('bank-transfer.index')->with('success', 'Transfer deleted successfully.');
    }

    public function reportFilter()
    {
        $banks = Bank::where('status', 'active')->orderBy('name')->get();
        return view('bank_transfer.report_filter', compact('banks'));
    }

    public function report(Request $request)
    {
        $fromBankId = $request->get('from_bank_id');
        $toBankId   = $request->get('to_bank_id');
        $bankId     = $request->get('bank_id');
        $fromDate   = $request->get('from_date');
        $toDate     = $request->get('to_date');
        $minAmount  = $request->get('min_amount');
        $maxAmount  = $request->get('max_amount');
        $search     = $request->get('search');

        $query = BankTransfer::with(['fromBank', 'toBank']);

        if ($fromBankId) {
            $query->where('from_bank_id', $fromBankId);
        }

        if ($toBankId) {
            $query->where('to_bank_id', $toBankId);
        }

        if ($bankId) {
            $query->where(function ($q) use ($bankId) {
                $q->where('from_bank_id', $bankId)
                  ->orWhere('to_bank_id', $bankId);
            });
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('transfer_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('transfer_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('transfer_date', '<=', $toDate);
        }

        if ($minAmount) {
            $query->where('amount', '>=', $minAmount);
        }

        if ($maxAmount) {
            $query->where('amount', '<=', $maxAmount);
        }

        if ($search) {
            $query->where('description', 'LIKE', "%{$search}%");
        }

        $transfers = $query->orderBy('transfer_date', 'desc')->orderBy('id', 'desc')->get();

        $totalAmount = $transfers->sum('amount');
        $totalCount  = $transfers->count();

        // Bank-wise summary matrix
        $allBanks = Bank::where('status', 'active')->orderBy('name')->get();
        $bankBreakdown = [];
        foreach ($allBanks as $b) {
            $outAmt = $transfers->where('from_bank_id', $b->id)->sum('amount');
            $inAmt  = $transfers->where('to_bank_id', $b->id)->sum('amount');
            $outCount = $transfers->where('from_bank_id', $b->id)->count();
            $inCount  = $transfers->where('to_bank_id', $b->id)->count();

            if ($outAmt > 0 || $inAmt > 0) {
                $bankBreakdown[] = [
                    'bank'      => $b,
                    'outgoing'  => $outAmt,
                    'out_count' => $outCount,
                    'incoming'  => $inAmt,
                    'in_count'  => $inCount,
                    'net'       => $inAmt - $outAmt,
                ];
            }
        }

        $banks = $allBanks;

        return view('bank_transfer.report', compact(
            'transfers',
            'banks',
            'totalAmount',
            'totalCount',
            'bankBreakdown',
            'fromBankId',
            'toBankId',
            'bankId',
            'fromDate',
            'toDate',
            'minAmount',
            'maxAmount',
            'search'
        ));
    }

    public function reportPdf(Request $request)
    {
        $fromBankId = $request->get('from_bank_id');
        $toBankId   = $request->get('to_bank_id');
        $bankId     = $request->get('bank_id');
        $fromDate   = $request->get('from_date');
        $toDate     = $request->get('to_date');
        $minAmount  = $request->get('min_amount');
        $maxAmount  = $request->get('max_amount');
        $search     = $request->get('search');

        $query = BankTransfer::with(['fromBank', 'toBank']);

        if ($fromBankId) {
            $query->where('from_bank_id', $fromBankId);
        }

        if ($toBankId) {
            $query->where('to_bank_id', $toBankId);
        }

        if ($bankId) {
            $query->where(function ($q) use ($bankId) {
                $q->where('from_bank_id', $bankId)
                  ->orWhere('to_bank_id', $bankId);
            });
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('transfer_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('transfer_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('transfer_date', '<=', $toDate);
        }

        if ($minAmount) {
            $query->where('amount', '>=', $minAmount);
        }

        if ($maxAmount) {
            $query->where('amount', '<=', $maxAmount);
        }

        if ($search) {
            $query->where('description', 'LIKE', "%{$search}%");
        }

        $transfers = $query->orderBy('transfer_date', 'desc')->orderBy('id', 'desc')->get();
        $totalAmount = $transfers->sum('amount');
        $totalCount  = $transfers->count();

        $selectedFromBank = $fromBankId ? Bank::find($fromBankId) : null;
        $selectedToBank   = $toBankId ? Bank::find($toBankId) : null;
        $selectedBank     = $bankId ? Bank::find($bankId) : null;

        $pdf = Pdf::loadView('bank_transfer.report_pdf', compact(
            'transfers',
            'totalAmount',
            'totalCount',
            'selectedFromBank',
            'selectedToBank',
            'selectedBank',
            'fromDate',
            'toDate'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('bank_transfers_report_' . date('Ymd_His') . '.pdf');
    }
}
