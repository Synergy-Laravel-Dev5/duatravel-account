<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingPerson;
use App\Models\BookingTransport;
use App\Models\BookingVisa;
use App\Models\Client;
use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /**
     * Display a listing of all Client and Supplier Refunds.
     */
    public function index(Request $request)
    {
        $refundType = $request->input('refund_type', 'all');
        $search     = $request->input('search');
        $fromDate   = $request->input('from_date');
        $toDate     = $request->input('to_date');

        $query = ExpenseTransaction::whereNotNull('refund_type')
            ->with(['client', 'supplier', 'bank', 'account', 'booking']);

        if ($refundType && in_array($refundType, ['client', 'supplier'])) {
            $query->where('refund_type', $refundType);
        }

        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $allRefunds = $query->latest('date')->latest('id')->get();

        // Calculate KPIs
        $totalClientRefundsPkr   = 0.0;
        $totalSupplierRefundsPkr = 0.0;
        $totalClientRefundsSar   = 0.0;
        $totalSupplierRefundsSar = 0.0;

        foreach ($allRefunds as $ref) {
            $amtPkr = (float) $ref->amount_pkr ?: (float) $ref->amount;
            $roe = (float) ($ref->exchange_rate > 0 ? $ref->exchange_rate : 75);
            $amtSar = ($ref->currency === 'SAR') ? (float)$ref->amount : (($roe > 1) ? round($amtPkr / $roe, 2) : 0);

            if ($ref->refund_type === 'client') {
                $totalClientRefundsPkr += $amtPkr;
                $totalClientRefundsSar += $amtSar;
            } elseif ($ref->refund_type === 'supplier') {
                $totalSupplierRefundsPkr += $amtPkr;
                $totalSupplierRefundsSar += $amtSar;
            }
        }

        // Paginate manually from collection or paginate directly
        $refunds = $query->paginate(20)->withQueryString();

        return view('refund.index', compact(
            'refunds',
            'refundType',
            'search',
            'fromDate',
            'toDate',
            'totalClientRefundsPkr',
            'totalSupplierRefundsPkr',
            'totalClientRefundsSar',
            'totalSupplierRefundsSar'
        ));
    }

    /**
     * Show form for creating a new refund.
     */
    public function create()
    {
        $package = session('dashboard_package', 'hajj');
        $banks = Bank::orderBy('name')->get();
        $cashAccounts = Account::where('status', 'active')->orderBy('account_name')->get();
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

        // Calculate dynamic balances for clients
        $bkgTotals = Booking::selectRaw('client_id, SUM(total_amount) as debits, SUM(total_received) as credits')
            ->groupBy('client_id')->get()->keyBy('client_id');
        $txTotals = Transaction::where('status', 'confirmed')
            ->selectRaw('client_id, SUM(amount) as credits')
            ->groupBy('client_id')->get()->keyBy('client_id');
        $etTotals = ExpenseTransaction::whereNotNull('client_id')
            ->selectRaw('client_id, 
                SUM(CASE WHEN transaction_type = "credit" THEN (CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) ELSE 0 END) as credits,
                SUM(CASE WHEN transaction_type = "debit" THEN (CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) ELSE 0 END) as debits')
            ->groupBy('client_id')->get()->keyBy('client_id');

        foreach ($clients as $c) {
            $opPkr = ($c->opening_balance_pkr_type === 'credit' ? -1 : 1) * ((float)$c->opening_balance_pkr ?: 0);
            $cRoe = (float)($c->opening_balance_roe > 1 ? $c->opening_balance_roe : 75.0);
            if ((float)$c->opening_balance_sar != 0) {
                $opPkr += ($c->opening_balance_sar_type === 'credit' ? -1 : 1) * ((float)$c->opening_balance_sar * $cRoe);
            }
            $bDeb = (float) ($bkgTotals[$c->id]->debits ?? 0);
            $bCred = (float) ($bkgTotals[$c->id]->credits ?? 0);
            $tCred = (float) ($txTotals[$c->id]->credits ?? 0);
            $eCred = (float) ($etTotals[$c->id]->credits ?? 0);
            $eDeb  = (float) ($etTotals[$c->id]->debits ?? 0);

            $c->balance = round($opPkr + $bDeb - $bCred - $tCred - $eCred + $eDeb, 2);
        }

        // Calculate dynamic balances for suppliers
        $supHotel = BookingHotel::selectRaw('supplier_id, SUM(COALESCE(cost_amount, cost_per_night * no_of_rooms * no_of_nights * COALESCE(cost_roe, roe, 75))) as total')
            ->groupBy('supplier_id')->get()->keyBy('supplier_id');
        $supVisa = BookingVisa::selectRaw('supplier_id, SUM(COALESCE(cost_amount, cost_rate * COALESCE(cost_roe, roe, 75))) as total')
            ->groupBy('supplier_id')->get()->keyBy('supplier_id');
        $supTrans = BookingTransport::selectRaw('supplier_id, SUM(COALESCE(cost_amount, cost_rate * quantity * COALESCE(cost_roe, roe, 75))) as total')
            ->groupBy('supplier_id')->get()->keyBy('supplier_id');
        $supFlight = Booking::selectRaw('flight_supplier_id as supplier_id, SUM(CASE WHEN flight_currency = "SAR" THEN flight_cost * COALESCE(flight_cost_roe, 75) ELSE flight_cost END) as total')
            ->whereNotNull('flight_supplier_id')->groupBy('flight_supplier_id')->get()->keyBy('supplier_id');
        $supRetFlight = Booking::selectRaw('return_flight_supplier_id as supplier_id, SUM(CASE WHEN flight_currency = "SAR" THEN flight_cost * COALESCE(flight_cost_roe, 75) ELSE flight_cost END) as total')
            ->whereNotNull('return_flight_supplier_id')->groupBy('return_flight_supplier_id')->get()->keyBy('supplier_id');
        $supPaxFlt = BookingPerson::whereNotNull('flight_supplier_id')
            ->selectRaw('flight_supplier_id as supplier_id, SUM(COALESCE(departure_cost, flight_cost, 0)) as total')
            ->groupBy('flight_supplier_id')->get()->keyBy('supplier_id');
        $supPaxRetFlt = BookingPerson::whereNotNull('return_flight_supplier_id')
            ->selectRaw('return_flight_supplier_id as supplier_id, SUM(COALESCE(return_cost, 0)) as total')
            ->groupBy('return_flight_supplier_id')->get()->keyBy('supplier_id');

        $supPaid = ExpenseTransaction::whereNotNull('supplier_id')
            ->selectRaw('supplier_id, 
                SUM(CASE WHEN transaction_type = "debit" THEN (CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END)
                         WHEN transaction_type = "credit" THEN -1 * (CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END)
                         ELSE (CASE WHEN currency = "SAR" THEN amount * exchange_rate ELSE amount END) END) as total_paid')
            ->groupBy('supplier_id')->get()->keyBy('supplier_id');

        foreach ($suppliers as $s) {
            $sOpPkr = ($s->opening_balance_pkr_type === 'credit' ? 1 : -1) * ((float)$s->opening_balance_pkr ?: 0);
            $sRoe = (float)($s->opening_balance_roe > 1 ? $s->opening_balance_roe : 75.0);
            if ((float)$s->opening_balance_sar != 0) {
                $sOpPkr += ($s->opening_balance_sar_type === 'credit' ? 1 : -1) * ((float)$s->opening_balance_sar * $sRoe);
            }
            $sBilled = (float)($supHotel[$s->id]->total ?? 0)
                     + (float)($supVisa[$s->id]->total ?? 0)
                     + (float)($supTrans[$s->id]->total ?? 0)
                     + (float)($supFlight[$s->id]->total ?? 0)
                     + ((float)($supPaxFlt[$s->id]->total ?? 0) * 75)
                     + ((float)($supPaxRetFlt[$s->id]->total ?? 0) * 75)
                     + $sOpPkr;
            $sPaidAmt = (float)($supPaid[$s->id]->total_paid ?? 0);
            $s->balance = round($sBilled - $sPaidAmt, 2);
        }

        $allBookings = Booking::with('client')->latest('id')->take(100)->get();

        // Generate default reference number
        $nextId = (ExpenseTransaction::whereNotNull('refund_type')->max('id') ?? 0) + 1;
        $defaultRef = 'REF-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('refund.create', compact('clients', 'suppliers', 'banks', 'cashAccounts', 'allBookings', 'defaultRef'));
    }

    /**
     * Store a new Client or Supplier Refund.
     */
    public function store(Request $request)
    {
        $request->validate([
            'refund_type'   => 'required|in:client,supplier',
            'client_id'     => 'required_if:refund_type,client|nullable|exists:clients,id',
            'supplier_id'   => 'required_if:refund_type,supplier|nullable|exists:suppliers,id',
            'amount'        => 'required|numeric|min:0.01',
            'currency'      => 'required|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0.0001',
            'payment_type'  => 'required|in:cash,bank',
            'bank_id'       => 'required_if:payment_type,bank|nullable|exists:banks,id',
            'account_id'    => 'nullable|exists:accounts,id',
            'booking_id'    => 'nullable|exists:bookings,id',
            'service_type'  => 'nullable|string',
            'date'          => 'required|date',
            'reference_no'  => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:500',
        ]);

        $currency = strtoupper($request->currency ?: 'PKR');
        $rawAmount = (float) $request->amount;
        $roe = (float) ($request->exchange_rate ?: ($currency === 'PKR' ? 1.0 : 75.0));

        $amountPkr = ($currency === 'SAR') ? round($rawAmount * $roe, 2) : $rawAmount;

        $package = session('dashboard_package', 'hajj');
        $year    = (int) session('dashboard_year', Carbon::now()->year);

        $refNo = $request->reference_no;
        if (empty($refNo)) {
            $prefix = ($request->refund_type === 'client') ? 'REF-CL' : 'REF-SUP';
            $nextId = (ExpenseTransaction::max('id') ?? 0) + 1;
            $refNo = "{$prefix}-" . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        $desc = $request->description;
        if (empty($desc)) {
            if ($request->refund_type === 'client') {
                $client = Client::find($request->client_id);
                $desc = "Client Refund paid to " . ($client ? $client->name : "Client #{$request->client_id}");
            } else {
                $supplier = Supplier::find($request->supplier_id);
                $desc = "Supplier Refund received from " . ($supplier ? $supplier->name : "Supplier #{$request->supplier_id}");
            }
        }

        // Transaction Type:
        // Client Refund -> 'debit' (Dr. Client Receivable / Ledger Outflow, Cr. Cash/Bank)
        // Supplier Refund -> 'credit' (Cr. Supplier Payable / Ledger Inflow, Dr. Cash/Bank)
        $txType = ($request->refund_type === 'client') ? 'debit' : 'credit';

        $tx = new ExpenseTransaction();
        $tx->refund_type       = $request->refund_type;
        $tx->payee_type        = $request->refund_type === 'client' ? 'client' : 'supplier';
        $tx->client_id         = $request->refund_type === 'client' ? $request->client_id : null;
        $tx->supplier_id       = $request->refund_type === 'supplier' ? $request->supplier_id : null;
        $tx->transaction_type  = $txType;
        $tx->currency          = $currency;
        $tx->exchange_rate     = $roe;
        $tx->amount            = $rawAmount;
        $tx->amount_pkr        = $amountPkr;
        $tx->payment_type      = $request->payment_type;
        $tx->bank_id           = ($request->payment_type === 'bank') ? $request->bank_id : null;
        $tx->account_id        = $request->account_id;
        $tx->booking_id        = $request->booking_id;
        $tx->service_type      = $request->service_type;
        $tx->date              = $request->date;
        $tx->reference_no      = $refNo;
        $tx->description       = $desc;
        $tx->hajj_umrah        = $package;
        $tx->year              = $year;
        $tx->save();

        $partyName = ($request->refund_type === 'client') 
            ? ($tx->client ? $tx->client->name : 'Client') 
            : ($tx->supplier ? $tx->supplier->name : 'Supplier');

        return redirect()->route('refunds.index')
            ->with('success', "Refund transaction #{$refNo} for {$partyName} recorded successfully and posted to ledger!");
    }

    /**
     * Remove the specified refund transaction.
     */
    public function destroy($id)
    {
        $tx = ExpenseTransaction::whereNotNull('refund_type')->findOrFail($id);
        $refNo = $tx->reference_no ?: "REF-{$tx->id}";
        $tx->delete();

        return redirect()->route('refunds.index')
            ->with('success', "Refund transaction #{$refNo} has been deleted successfully.");
    }
}
