<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\ExpenseTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $package = session('dashboard_package', 'hajj');
        $year    = (int) session('dashboard_year', Carbon::now()->year);

        $query = Transaction::with(['client', 'company', 'booking'])
            ->whereHas('booking', function ($q) use ($package, $year) {
                $q->where('package_type', $package)
                    ->where('package_year', $year);
            })
            ->latest();

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        $transactions = $query->get();

        $trashCount = Transaction::onlyTrashed()
            ->whereHas('booking', function ($q) use ($package, $year) {
                $q->where('package_type', $package)
                    ->where('package_year', $year);
            })
            ->count();

        $clients   = Client::where('status', 'active')->get();
        $companies = Company::all();

        $clientSummary = Transaction::with('client')
            ->confirmed()
            ->whereHas('booking', function ($q) use ($package, $year) {
                $q->where('package_type', $package)
                    ->where('package_year', $year);
            })
            ->selectRaw('client_id, SUM(amount) as total_paid')
            ->groupBy('client_id')
            ->get();

        return view('transaction.index', compact(
            'transactions',
            'trashCount',
            'clients',
            'companies',
            'clientSummary',
            'package',
            'year'
        ));
    }

    public function create()
    {
        $clients   = Client::where('status', 'active')->get();
        $companies = Company::all();
        $bookings  = Booking::with(['client', 'company'])->latest()->get();
        return view('transaction.create', compact('clients', 'companies', 'bookings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_for'      => 'required|in:client,company',
            'client_id'        => 'required_if:booking_for,client|nullable|exists:clients,id',
            'company_id'       => 'required_if:booking_for,company|nullable|exists:companies,id',
            'booking_id'       => 'required|exists:bookings,id',
            'payment_type'     => 'required|in:cash,bank_transfer,cheque,online',
            'amount'           => 'required|numeric|min:1',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes'            => 'nullable|string',
            'status'           => 'required|in:pending,confirmed,rejected',
        ]);

        $clientId  = $request->booking_for === 'client'  ? $request->client_id  : null;
        $companyId = $request->booking_for === 'company' ? $request->company_id : null;

        $proofPath = null;

        if ($request->hasFile('proof_of_payment')) {
            $file      = $request->file('proof_of_payment');
            $filename  = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/transaction_proofs'), $filename);
            $proofPath = $filename;
        }

        // booking_id is validated as required above, so it always comes
        // straight from the user's selection now — no more auto-pick fallback.
        $bookingId = $request->booking_id;

        $roe = (float) ($request->exchange_rate ?: 75.0);
        $amountPkr = (float) $request->amount;
        $sarAmount = ($roe > 0) ? round($amountPkr / $roe, 2) : 0.00;

        $transaction = Transaction::create([
            'client_id'        => $clientId,
            'company_id'       => $companyId,
            'booking_id'       => $bookingId,
            'payment_type'     => $request->payment_type,
            'amount'           => $amountPkr,
            'currency'         => 'PKR',
            'exchange_rate'    => $roe,
            'sar_amount'       => $sarAmount,
            'payment_date'     => $request->payment_date,
            'reference_number' => $request->reference_number,
            'proof_of_payment' => $proofPath,
            'notes'            => $request->notes,
            'status'           => $request->status,
        ]);

        if ($transaction->booking_id && $transaction->status === 'confirmed') {
            $this->updateBookingBalance($transaction->booking_id);
        }

        logUserActivity('Transaction Created', 'Amount: ' . $transaction->amount . ' | Type: ' . $transaction->payment_type . ' | Status: ' . $transaction->status, $transaction->id, 'Transaction');

        return redirect()->route('transaction.index')
            ->with('success', 'Transaction added successfully!');
    }

    public function show($id)
    {
        $transaction = Transaction::with(['client', 'company', 'booking'])->findOrFail($id);
        return view('transaction.show', compact('transaction'));
    }

    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);
        $clients     = Client::where('status', 'active')->get();
        $companies   = Company::all();
        $bookings    = Booking::with(['client', 'company'])->latest()->get();
        return view('transaction.edit', compact('transaction', 'clients', 'companies', 'bookings'));
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $request->validate([
            'booking_for'      => 'required|in:client,company',
            'client_id'        => 'required_if:booking_for,client|nullable|exists:clients,id',
            'company_id'       => 'required_if:booking_for,company|nullable|exists:companies,id',
            // booking_id is now required: the edit form always shows a visible
            // booking selector, so the user must explicitly pick which booking
            // this payment belongs to. This prevents payments being silently
            // applied to the wrong booking when a client/company has multiple.
            'booking_id'       => 'required|exists:bookings,id',
            'payment_type'     => 'required|in:cash,bank_transfer,cheque,online',
            'amount'           => 'required|numeric|min:1',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes'            => 'nullable|string',
            'status'           => 'required|in:pending,confirmed,rejected',
        ]);

        $clientId  = $request->booking_for === 'client'  ? $request->client_id  : null;
        $companyId = $request->booking_for === 'company' ? $request->company_id : null;

        if ($request->hasFile('proof_of_payment')) {
            if ($transaction->proof_of_payment) {
                $oldPath = public_path('assets/images/transaction_proofs/' . $transaction->proof_of_payment);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file      = $request->file('proof_of_payment');
            $filename  = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/transaction_proofs'), $filename);
            $transaction->proof_of_payment = $filename;
        }

        // booking_id is validated as required above, so it always comes
        // straight from the user's selection now — no more auto-pick fallback.
        $bookingId = $request->booking_id;

        $oldBookingId = $transaction->booking_id;

        $roe = (float) ($request->exchange_rate ?: 75.0);
        $amountPkr = (float) $request->amount;
        $sarAmount = ($roe > 0) ? round($amountPkr / $roe, 2) : 0.00;

        $transaction->update([
            'client_id'        => $clientId,
            'company_id'       => $companyId,
            'booking_id'       => $bookingId,
            'payment_type'     => $request->payment_type,
            'amount'           => $amountPkr,
            'currency'         => 'PKR',
            'exchange_rate'    => $roe,
            'sar_amount'       => $sarAmount,
            'payment_date'     => $request->payment_date,
            'reference_number' => $request->reference_number,
            'notes'            => $request->notes,
            'status'           => $request->status,
        ]);

        if ($oldBookingId) {
            $this->updateBookingBalance($oldBookingId);
        }
        if ($bookingId && $bookingId != $oldBookingId) {
            $this->updateBookingBalance($bookingId);
        }

        logUserActivity('Transaction Updated', 'Amount: ' . $transaction->amount . ' | Type: ' . $transaction->payment_type . ' | Status: ' . $transaction->status, $transaction->id, 'Transaction');

        return redirect()->route('transaction.index')
            ->with('success', 'Transaction updated successfully!');
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $bookingId   = $transaction->booking_id;

        logUserActivity('Transaction Deleted', 'Amount: ' . $transaction->amount . ' | Type: ' . $transaction->payment_type . ' | Status: ' . $transaction->status, $transaction->id, 'Transaction');

        $transaction->delete();

        if ($bookingId) {
            $this->updateBookingBalance($bookingId);
        }

        return redirect()->route('transaction.index')
            ->with('success', 'Transaction moved to trash.');
    }

    public function trash()
    {
        $transactions = Transaction::onlyTrashed()->with(['client', 'company', 'booking'])->latest()->get();
        return view('transaction.trash', compact('transactions'));
    }

    public function restore($id)
    {
        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $transaction->restore();

        if ($transaction->booking_id) {
            $this->updateBookingBalance($transaction->booking_id);
        }

        logUserActivity('Transaction Restored', 'Amount: ' . $transaction->amount . ' | Type: ' . $transaction->payment_type . ' | Status: ' . $transaction->status, $transaction->id, 'Transaction');

        return redirect()->route('transaction.index')
            ->with('success', 'Transaction restored successfully.');
    }

    public function ledgerFilter()
    {
        $clients = Client::all();
        return view('transaction.ledger-filter', compact('clients'));
    }

    public function ledgerView(Request $request)
    {
        $clientId = $request->input('client_id', 'all');
        $fromDate = $request->from_date;
        $toDate   = $request->to_date;

        if ($clientId === 'all' || empty($clientId)) {
            $clientId = 'all';
            $client = (object)[
                'id'              => 'all',
                'name'            => 'All Clients (Consolidated)',
                'company_name'    => 'All Associated Companies / Walk-ins',
                'phone'           => null,
                'email'           => null,
                'status'          => 'active',
                'passport_number' => null,
                'cnic'            => null,
            ];
        } else {
            $client = Client::find($clientId);
            if (!$client) {
                return redirect()->route('transaction.ledger.filter')
                    ->with('error', 'Selected client was not found. Showing filter page.');
            }
        }

        // Define reusable calculation closure for Booking SAR
        $calcBookingSar = function ($b) {
            $cur = strtoupper($b->currency ?: 'PKR');
            $roe = (float) ($b->flight_sale_roe > 1 ? $b->flight_sale_roe : ($b->flight_roe > 1 ? $b->flight_roe : 75));
            $totAmt = (float) $b->total_amount;
            $recAmt = (float) $b->total_received;

            if ($cur === 'SAR') {
                return [
                    'debit_sar'  => $totAmt,
                    'credit_sar' => $recAmt,
                    'roe'        => $roe,
                ];
            }

            // Attempt component-based SAR calculation
            $compSar = 0;

            // Hotels
            if ($b->relationLoaded('hotels')) {
                foreach ($b->hotels as $h) {
                    $hSaleRoe = (float) ($h->sale_roe > 0 ? $h->sale_roe : ($h->roe > 0 ? $h->roe : $roe));
                    $nights = max(1, (int) ($h->no_of_nights ?: 1));
                    $rooms = max(1, (int) ($h->no_of_rooms ?: 1));
                    $hSpn = (float) $h->selling_per_night;
                    if ($hSpn > 0) {
                        $compSar += ($hSpn * $nights * $rooms);
                    } elseif ((float) $h->selling_amount > 0 && $hSaleRoe > 0) {
                        $compSar += round((float) $h->selling_amount / $hSaleRoe, 2);
                    }
                }
            }

            // Flights
            $fRoe = (float) ($b->flight_sale_roe > 0 ? $b->flight_sale_roe : ($b->flight_roe > 0 ? $b->flight_roe : $roe));
            $fCharges = (float) $b->flight_charges;
            $fSellRate = (float) $b->flight_selling_rate;
            if ($fCharges > 0 && $fRoe > 0) {
                $compSar += round($fCharges / $fRoe, 2);
            } elseif ($fSellRate > 0) {
                $compSar += $fSellRate;
            }

            // Visas
            if ($b->relationLoaded('visas')) {
                foreach ($b->visas as $v) {
                    $vRoe = (float) ($v->sale_roe > 0 ? $v->sale_roe : ($v->roe > 0 ? $v->roe : $roe));
                    if ((float) $v->selling_rate > 0) {
                        $compSar += (float) $v->selling_rate;
                    } elseif ((float) $v->selling_amount > 0 && $vRoe > 0) {
                        $compSar += round((float) $v->selling_amount / $vRoe, 2);
                    }
                }
            }

            // Transports
            if ($b->relationLoaded('transports')) {
                foreach ($b->transports as $tr) {
                    $trRoe = (float) ($tr->sale_roe > 0 ? $tr->sale_roe : ($tr->roe > 0 ? $tr->roe : $roe));
                    if ((float) $tr->selling_rate > 0) {
                        $compSar += (float) $tr->selling_rate;
                    } elseif ((float) $tr->selling_amount > 0 && $trRoe > 0) {
                        $compSar += round((float) $tr->selling_amount / $trRoe, 2);
                    }
                }
            }

            if ($compSar > 0) {
                $debSar = round($compSar, 2);
                $effRoe = ($debSar > 0 && $totAmt > 0) ? round($totAmt / $debSar, 2) : $roe;
            } else {
                $debSar = ($roe > 1) ? round($totAmt / $roe, 2) : 0.00;
                $effRoe = $roe;
            }

            $crdSar = 0.00;
            if ($recAmt > 0) {
                if ($totAmt > 0 && $debSar > 0) {
                    $crdSar = round($recAmt * ($debSar / $totAmt), 2);
                } elseif ($effRoe > 1) {
                    $crdSar = round($recAmt / $effRoe, 2);
                }
            }

            return [
                'debit_sar'  => $debSar,
                'credit_sar' => $crdSar,
                'roe'        => $effRoe,
            ];
        };

        // 1. Calculate Initial Client Opening Balance (from client profile) + prior to from_date
        $clientInitialOpPkr = 0;
        $clientInitialOpSar = 0;
        $defaultRoe = 75.0;
        if ($client && $clientId !== 'all') {
            $pkrSign = ($client->opening_balance_pkr_type === 'credit') ? -1 : 1;
            $sarSign = ($client->opening_balance_sar_type === 'credit') ? -1 : 1;
            $clientInitialOpPkr = $pkrSign * (float) ($client->opening_balance_pkr ?? 0);
            $clientInitialOpSar = $sarSign * (float) ($client->opening_balance_sar ?? 0);

            if ((float)($client->opening_balance_roe ?? 0) > 0) {
                $defaultRoe = (float) $client->opening_balance_roe;
            } elseif ($client->bookings()->exists()) {
                $latestB = $client->bookings()->latest('id')->first();
                if ($latestB) {
                    $defaultRoe = (float) ($latestB->flight_sale_roe > 1 ? $latestB->flight_sale_roe : ($latestB->flight_roe > 1 ? $latestB->flight_roe : 75.0));
                }
            }

            // If opening balance was provided in PKR but not in SAR (or vice-versa), auto-sync so both currencies match ONLY if ROE > 1
            $clientRoe = (float)($client->opening_balance_roe ?? 0);
            if ($clientRoe > 1) {
                if ($clientInitialOpSar == 0 && $clientInitialOpPkr != 0) {
                    $clientInitialOpSar = round($clientInitialOpPkr / $defaultRoe, 2);
                } elseif ($clientInitialOpPkr == 0 && $clientInitialOpSar != 0) {
                    $clientInitialOpPkr = round($clientInitialOpSar * $defaultRoe, 2);
                }
            } else {
                $clientInitialOpSar = 0.00;
            }
        }

        $openingBalancePkr = $clientInitialOpPkr;
        $openingBalanceSar = $clientInitialOpSar;
        if ($fromDate) {
            $priorBookings = Booking::with(['hotels', 'visas', 'transports'])->where(function ($q) use ($clientId) {
                if ($clientId !== 'all') $q->where('client_id', $clientId);
            })->whereDate('booking_date', '<', $fromDate)->get();

            $priorBookingDebitsPkr = 0;
            $priorBookingCreditsPkr = 0;
            $priorBookingDebitsSar = 0;
            $priorBookingCreditsSar = 0;

            foreach ($priorBookings as $pb) {
                $cur = strtoupper($pb->currency ?: 'PKR');
                $totAmt = (float) $pb->total_amount;
                $recAmt = (float) $pb->total_received;

                $sarInfo = $calcBookingSar($pb);
                $priorBookingDebitsSar += $sarInfo['debit_sar'];
                $priorBookingCreditsSar += $sarInfo['credit_sar'];

                if ($cur === 'SAR') {
                    $priorBookingDebitsPkr += round($totAmt * $sarInfo['roe'], 2);
                    $priorBookingCreditsPkr += round($recAmt * $sarInfo['roe'], 2);
                } else {
                    $priorBookingDebitsPkr += $totAmt;
                    $priorBookingCreditsPkr += $recAmt;
                }
            }

            $priorTxnCreditsPkr = (float) Transaction::where('status', 'confirmed')
                ->where(function ($q) use ($clientId) {
                    if ($clientId !== 'all') $q->where('client_id', $clientId);
                })->whereDate('payment_date', '<', $fromDate)->sum('amount');
            $priorTxnCreditsSar = round($priorTxnCreditsPkr / 75, 2);

            $priorEtList = ExpenseTransaction::where(function ($q) {
                    $q->where('payee_type', 'client')
                      ->orWhere('payee_type', 'jv');
                })
                ->whereNotNull('client_id')
                ->where(function ($q) use ($clientId) {
                    if ($clientId !== 'all') $q->where('client_id', $clientId);
                })->whereDate('date', '<', $fromDate)->get();

            $priorEtDebitsPkr = 0;
            $priorEtCreditsPkr = 0;
            $priorEtDebitsSar = 0;
            $priorEtCreditsSar = 0;

            foreach ($priorEtList as $pet) {
                $cur = strtoupper($pet->currency ?: 'PKR');
                $rate = (float) ($pet->exchange_rate > 0 ? $pet->exchange_rate : 75);
                $raw = (float) $pet->amount;
                if ($cur === 'SAR') {
                    $pkrVal = round($raw * $rate, 2);
                    $sarVal = $raw;
                } else {
                    $pkrVal = $raw;
                    $sarVal = ($rate > 1) ? round($raw / $rate, 2) : 0.00;
                }

                if ($pet->transaction_type === 'debit') {
                    $priorEtDebitsPkr += $pkrVal;
                    $priorEtDebitsSar += $sarVal;
                } else {
                    $priorEtCreditsPkr += $pkrVal;
                    $priorEtCreditsSar += $sarVal;
                }
            }

            $openingBalancePkr = $clientInitialOpPkr + (($priorBookingDebitsPkr + $priorEtDebitsPkr) - ($priorBookingCreditsPkr + $priorTxnCreditsPkr + $priorEtCreditsPkr));
            $openingBalanceSar = $clientInitialOpSar + (($priorBookingDebitsSar + $priorEtDebitsSar) - ($priorBookingCreditsSar + $priorTxnCreditsSar + $priorEtCreditsSar));
        }
        $openingBalance = $openingBalancePkr;

        // 2. Retrieve Bookings in Date Range (Debit = Charges, Credit = Initial Advance)
        $bookingsQuery = Booking::with(['package', 'client', 'company', 'hotels', 'visas', 'transports']);
        if ($clientId !== 'all') {
            $bookingsQuery->where('client_id', $clientId);
        }
        if ($fromDate) {
            $bookingsQuery->whereDate('booking_date', '>=', $fromDate);
        }
        if ($toDate) {
            $bookingsQuery->whereDate('booking_date', '<=', $toDate);
        }
        $bookings = $bookingsQuery->latest('booking_date')->get();

        $rawEntries = [];
        $bookingReceived = 0;
        $totalBooking = 0;

        foreach ($bookings as $b) {
            $bDate = $b->booking_date ? Carbon::parse($b->booking_date)->format('Y-m-d') : Carbon::parse($b->created_at)->format('Y-m-d');
            $bTimestamp = $b->booking_date ? Carbon::parse($b->booking_date)->startOfDay()->timestamp : Carbon::parse($b->created_at)->timestamp;

            $bCurrency = strtoupper($b->currency ?: 'PKR');
            $totalAmount = (float) $b->total_amount;
            $advanceAmount = (float) $b->total_received;

            $sarData = $calcBookingSar($b);
            $bDebSar = $sarData['debit_sar'];
            $bCrdSar = $sarData['credit_sar'];
            $bEffectiveRoe = $sarData['roe'];

            if ($bCurrency === 'SAR') {
                $bDebPkr = round($totalAmount * $bEffectiveRoe, 2);
                $bCrdPkr = round($advanceAmount * $bEffectiveRoe, 2);
            } else {
                $bDebPkr = $totalAmount;
                $bCrdPkr = $advanceAmount;
            }

            $totalBooking += $bDebPkr;
            $bookingReceived += $bCrdPkr;

            // Debit Entry: Booking Invoice / Charges
            $rawEntries[] = [
                'date'             => $bDate,
                'timestamp'        => $bTimestamp,
                'entry_type'       => 'invoice',
                'badge_class'      => 'bg-soft-primary text-primary',
                'type_label'       => 'Booking Charges',
                'voucher_no'       => 'BK-' . $b->id,
                'ref_no'           => $b->voucher_number ?: ('#BK-' . $b->id),
                'description'      => 'Booking #' . $b->id . ' — ' . ($b->package_name ?: ($b->package->name ?? 'Package')) . ' (' . ($b->no_of_pax ?: 1) . ' Pax)' . ($b->voucher_number ? ' [Voucher: ' . $b->voucher_number . ']' : ''),
                'contra_account'   => 'Package Sales / Revenue',
                'client_id'        => $b->client_id,
                'client_name'      => $b->client->name ?? '—',
                'booking_id'       => $b->id,
                'currency'         => $bCurrency,
                'exchange_rate'    => $bEffectiveRoe,
                'original_amount'  => $totalAmount,
                'debit'            => $bDebPkr,
                'credit'           => 0.00,
                'debit_pkr'        => $bDebPkr,
                'credit_pkr'       => 0.00,
                'debit_sar'        => $bDebSar,
                'credit_sar'       => 0.00,
                'payment_method'   => 'Invoice Bill',
                'status'           => 'confirmed',
                'proof'            => null,
            ];

            // Credit Entry: Initial Advance Received at Booking
            if ($advanceAmount > 0) {
                $rawEntries[] = [
                    'date'             => $bDate,
                    'timestamp'        => $bTimestamp + 1,
                    'entry_type'       => 'receipt',
                    'badge_class'      => 'bg-soft-success text-success',
                    'type_label'       => 'Advance at Booking',
                    'voucher_no'       => 'ADV-' . $b->id,
                    'ref_no'           => '#ADV-' . $b->id,
                    'description'      => 'Initial Advance Payment received at Booking #' . $b->id,
                    'contra_account'   => 'Cash / Bank (Advance)',
                    'client_id'        => $b->client_id,
                    'client_name'      => $b->client->name ?? '—',
                    'booking_id'       => $b->id,
                    'currency'         => $bCurrency,
                    'exchange_rate'    => $bEffectiveRoe,
                    'original_amount'  => $advanceAmount,
                    'debit'            => 0.00,
                    'credit'           => $bCrdPkr,
                    'debit_pkr'        => 0.00,
                    'credit_pkr'       => $bCrdPkr,
                    'debit_sar'        => 0.00,
                    'credit_sar'       => $bCrdSar,
                    'payment_method'   => 'Advance / Cash',
                    'status'           => 'confirmed',
                    'proof'            => null,
                ];
            }
        }

        // 3. Retrieve Confirmed Transactions (Credit = Payments Received)
        $txQuery = Transaction::with(['booking', 'client', 'company']);
        if ($clientId !== 'all') {
            $txQuery->where('client_id', $clientId);
        }
        if ($fromDate) {
            $txQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $txQuery->whereDate('payment_date', '<=', $toDate);
        }
        $transactions = $txQuery->latest('payment_date')->get();

        $transactionsPaid = 0;

        foreach ($transactions as $t) {
            $tDate = $t->payment_date ? Carbon::parse($t->payment_date)->format('Y-m-d') : Carbon::parse($t->created_at)->format('Y-m-d');
            $tTimestamp = $t->payment_date ? Carbon::parse($t->payment_date)->startOfDay()->timestamp + 2 : Carbon::parse($t->created_at)->timestamp;
            $amt = (float) $t->amount;

            if ($t->status === 'confirmed') {
                $transactionsPaid += $amt;
            }

            $txRoe = 75.0;
            if ($t->exchange_rate > 1) {
                $txRoe = (float) $t->exchange_rate;
            } elseif ($t->booking) {
                $txRoe = (float) ($t->booking->flight_sale_roe > 1 ? $t->booking->flight_sale_roe : ($t->booking->flight_roe > 1 ? $t->booking->flight_roe : 75));
            } elseif ($client && $client->id !== 'all' && $client->bookings()->exists()) {
                $latestB = $client->bookings()->latest('id')->first();
                if ($latestB) {
                    $txRoe = (float) ($latestB->flight_sale_roe > 1 ? $latestB->flight_sale_roe : ($latestB->flight_roe > 1 ? $latestB->flight_roe : 75));
                }
            }
            $txSar = ($t->sar_amount > 0) ? (float) $t->sar_amount : (($txRoe > 1) ? round($amt / $txRoe, 2) : 0.00);

            $contra = match(strtolower($t->payment_type)) {
                'cash' => 'Cash in Hand',
                'bank', 'bank_transfer' => 'Bank Account',
                'cheque' => 'Cheque Clearance',
                'online' => 'Online Banking',
                default => ucwords(str_replace('_', ' ', $t->payment_type ?? 'Cash in Hand'))
            };

            $rawEntries[] = [
                'date'             => $tDate,
                'timestamp'        => $tTimestamp,
                'entry_type'       => 'receipt',
                'badge_class'      => $t->status === 'confirmed' ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning',
                'type_label'       => 'Payment Received',
                'voucher_no'       => 'TXN-' . $t->id,
                'ref_no'           => $t->reference_number ?: ('#TXN-' . $t->id),
                'description'      => 'Payment Received via ' . ucwords(str_replace('_', ' ', $t->payment_type)) . ($t->notes ? ' — ' . $t->notes : '') . ($t->booking_id ? ' [For Booking #' . $t->booking_id . ']' : ''),
                'contra_account'   => $contra,
                'client_id'        => $t->client_id,
                'client_name'      => $t->client->name ?? '—',
                'booking_id'       => $t->booking_id,
                'currency'         => 'PKR',
                'exchange_rate'    => $txRoe,
                'original_amount'  => $amt,
                'debit'            => 0.00,
                'credit'           => $t->status === 'confirmed' ? $amt : 0.00,
                'debit_pkr'        => 0.00,
                'credit_pkr'       => $t->status === 'confirmed' ? $amt : 0.00,
                'debit_sar'        => 0.00,
                'credit_sar'       => $t->status === 'confirmed' ? $txSar : 0.00,
                'payment_method'   => ucwords(str_replace('_', ' ', $t->payment_type)),
                'status'           => $t->status,
                'proof'            => $t->proof_of_payment ? asset('assets/images/transaction_proofs/' . $t->proof_of_payment) : null,
            ];
        }

        // 4. Retrieve Client ExpenseTransactions (Receipts & Debits from expense_transactions)
        $etQuery = ExpenseTransaction::with(['client', 'supplier', 'bank', 'account'])
            ->where(function ($q) {
                $q->where('payee_type', 'client')
                  ->orWhere('payee_type', 'jv');
            })
            ->whereNotNull('client_id');
        if ($clientId !== 'all') {
            $etQuery->where('client_id', $clientId);
        }
        if ($fromDate) {
            $etQuery->whereDate('date', '>=', $fromDate);
        }
        if ($toDate) {
            $etQuery->whereDate('date', '<=', $toDate);
        }
        $etList = $etQuery->latest('date')->get();

        foreach ($etList as $et) {
            $etDate = Carbon::parse($et->date)->format('Y-m-d');
            $etTimestamp = Carbon::parse($et->date)->startOfDay()->timestamp + 3;

            $currency = strtoupper($et->currency ?: 'PKR');
            $exchangeRate = (float) ($et->exchange_rate > 0 ? $et->exchange_rate : 75);
            $rawAmount = (float) $et->amount;

            // Transaction amount is ALWAYS entered and stored in PKR.
            // When an exchange rate > 1 is present (e.g. currency is SAR with ROE),
            // the ledger records the PKR amount directly and calculates SAR equivalent via ROE.
            $pkrAmount = $rawAmount;
            $sarAmount = ($exchangeRate > 1) ? round($rawAmount / $exchangeRate, 2) : 0.00;

            $isDebit = ($et->transaction_type === 'debit');

            if ($isDebit) {
                $debitPkr = $pkrAmount;
                $creditPkr = 0.00;
                $debitSar = $sarAmount;
                $creditSar = 0.00;
            } else {
                $debitPkr = 0.00;
                $creditPkr = $pkrAmount;
                $debitSar = 0.00;
                $creditSar = $sarAmount;
                $transactionsPaid += $pkrAmount;
            }

            // Determine Contra Account (Bank name, Account name, Supplier, or Cash)
            $isJv = ($et->payee_type === 'jv');
            $contraAccount = '—';
            if ($et->bank) {
                $contraAccount = $et->bank->name . ($et->bank->account_name ? ' (' . $et->bank->account_name . ')' : '');
            } elseif ($et->account) {
                $contraAccount = $et->account->account_name;
            } elseif ($isJv) {
                if ($et->supplier) {
                    $contraAccount = 'Supplier: ' . $et->supplier->name;
                } elseif (!empty($et->reference_no)) {
                    $siblingSup = ExpenseTransaction::where('payee_type', 'jv')
                        ->where('reference_no', $et->reference_no)
                        ->whereNotNull('supplier_id')
                        ->with('supplier')
                        ->first();
                    if ($siblingSup && $siblingSup->supplier) {
                        $contraAccount = 'Supplier: ' . $siblingSup->supplier->name;
                    } else {
                        $contraAccount = 'Journal Voucher Adjustment';
                    }
                } else {
                    $contraAccount = 'Journal Voucher Adjustment';
                }
            } else {
                $contraAccount = match(strtolower($et->payment_type)) {
                    'cash' => 'Cash in Hand',
                    'bank', 'bank_transfer' => 'Bank Account',
                    'cheque' => 'Cheque Clearance',
                    'online' => 'Online Banking',
                    default => $isJv ? 'Journal Voucher Adjustment' : ucwords(str_replace('_', ' ', $et->payment_type ?? 'Cash in Hand'))
                };
            }

            $rawEntries[] = [
                'date'             => $etDate,
                'timestamp'        => $etTimestamp,
                'entry_type'       => $isDebit ? 'charge' : 'receipt',
                'badge_class'      => $isJv ? 'bg-soft-purple text-purple' : ($isDebit ? 'bg-soft-danger text-danger' : 'bg-soft-info text-info'),
                'type_label'       => $isJv ? 'JV Voucher' : ($isDebit ? 'Client Debit / Charge' : 'Client Receipt'),
                'voucher_no'       => $et->reference_no ?: ('REC-' . $et->id),
                'ref_no'           => $et->reference_no ?: ('#REC-' . $et->id),
                'description'      => ($isJv ? 'JV: ' : ($isDebit ? 'Charge / Payment to Client via ' : 'Payment Received via ')) . ($isJv ? ($et->description ?: 'Journal Adjustment') : (ucwords(str_replace('_', ' ', $et->payment_type)) . ($et->description ? ' — ' . $et->description : ''))),
                'contra_account'   => $contraAccount,
                'client_id'        => $et->client_id,
                'client_name'      => $et->client->name ?? '—',
                'booking_id'       => null,
                'currency'         => $currency,
                'exchange_rate'    => $exchangeRate,
                'original_amount'  => $rawAmount,
                'debit'            => $debitPkr,
                'credit'           => $creditPkr,
                'debit_pkr'        => $debitPkr,
                'credit_pkr'       => $creditPkr,
                'debit_sar'        => $debitSar,
                'credit_sar'       => $creditSar,
                'payment_method'   => ucwords(str_replace('_', ' ', $et->payment_type)),
                'status'           => 'confirmed',
                'proof'            => $et->proof ? asset('assets/images/expense_proofs/' . $et->proof) : null,
            ];
        }

        // 5. Sort chronologically (earliest first)
        usort($rawEntries, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['timestamp'] <=> $b['timestamp'];
            }
            return strcmp($a['date'], $b['date']);
        });

        // 6. Dual-Currency Running Balance Line by Line (PKR & SAR)
        $runningBalancePkr = $openingBalancePkr;
        $runningBalanceSar = $openingBalanceSar;
        $periodDebitsPkr = 0;
        $periodCreditsPkr = 0;
        $periodDebitsSar = 0;
        $periodCreditsSar = 0;
        $statementLines = [];

        foreach ($rawEntries as $row) {
            $debPkr = (float) ($row['debit_pkr'] ?? $row['debit']);
            $crdPkr = (float) ($row['credit_pkr'] ?? $row['credit']);
            $debSar = (float) ($row['debit_sar'] ?? 0);
            $crdSar = (float) ($row['credit_sar'] ?? 0);

            $periodDebitsPkr += $debPkr;
            $periodCreditsPkr += $crdPkr;
            $runningBalancePkr += ($debPkr - $crdPkr);

            $periodDebitsSar += $debSar;
            $periodCreditsSar += $crdSar;
            $runningBalanceSar += ($debSar - $crdSar);

            // Synchronize SAR running balance with PKR running balance:
            // In a unified client account, a client who owes PKR (Dr) cannot simultaneously have an advance in SAR (Cr)
            $rowRoe = (!empty($row['exchange_rate']) && $row['exchange_rate'] > 1) ? (float)$row['exchange_rate'] : ($defaultRoe ?? 75.0);
            if ($runningBalancePkr != 0) {
                if (($runningBalancePkr > 0 && $runningBalanceSar < 0) || ($runningBalancePkr < 0 && $runningBalanceSar > 0) || $runningBalanceSar == 0) {
                    $runningBalanceSar = round($runningBalancePkr / $rowRoe, 2);
                }
            } elseif ($runningBalancePkr == 0) {
                $runningBalanceSar = 0.00;
            }

            $row['debit_pkr']            = $debPkr;
            $row['credit_pkr']           = $crdPkr;
            $row['debit_sar']            = $debSar;
            $row['credit_sar']           = $crdSar;
            $row['running_balance_pkr']  = $runningBalancePkr;
            $row['running_balance_sar']  = $runningBalanceSar;

            // Backward compatibility aliases:
            $row['debit']                = $debPkr;
            $row['credit']               = $crdPkr;
            $row['running_balance']      = $runningBalancePkr;
            $row['balance_type']         = $runningBalancePkr > 0 ? 'Dr' : ($runningBalancePkr < 0 ? 'Cr' : 'Nil');
            $row['balance_type_sar']     = $runningBalanceSar > 0 ? 'Dr' : ($runningBalanceSar < 0 ? 'Cr' : 'Nil');

            $statementLines[]            = $row;
        }

        $closingBalancePkr = $runningBalancePkr;
        $closingBalanceSar = $runningBalanceSar;
        $totalDebitsPkr    = ($openingBalancePkr > 0 ? $openingBalancePkr : 0) + $periodDebitsPkr;
        $totalCreditsPkr   = ($openingBalancePkr < 0 ? abs($openingBalancePkr) : 0) + $periodCreditsPkr;
        $totalDebitsSar    = ($openingBalanceSar > 0 ? $openingBalanceSar : 0) + $periodDebitsSar;
        $totalCreditsSar   = ($openingBalanceSar < 0 ? abs($openingBalanceSar) : 0) + $periodCreditsSar;

        // Legacy / backward-compat aliases:
        $openingBalance = $openingBalancePkr;
        $periodDebits   = $periodDebitsPkr;
        $periodCredits  = $periodCreditsPkr;
        $totalDebits    = $totalDebitsPkr;
        $totalCredits   = $totalCreditsPkr;
        $closingBalance = $closingBalancePkr;
        $balance        = $closingBalancePkr;
        $totalPaid      = $bookingReceived + $transactionsPaid;

        // 7. Multi-Currency Summary Breakdown
        $currencySummary = [
            'PKR' => [
                'received_original' => 0.0,
                'received_pkr'      => 0.0,
                'charges_original'  => 0.0,
                'count'             => 0,
            ],
            'SAR' => [
                'received_original' => 0.0,
                'received_pkr'      => 0.0,
                'charges_original'  => 0.0,
                'count'             => 0,
            ],
        ];

        foreach ($statementLines as $line) {
            $cur = strtoupper($line['currency'] ?? 'PKR');
            if (!isset($currencySummary[$cur])) {
                $currencySummary[$cur] = [
                    'received_original' => 0.0,
                    'received_pkr'      => 0.0,
                    'charges_original'  => 0.0,
                    'count'             => 0,
                ];
            }
            if ($line['credit_pkr'] > 0 || ($line['credit_sar'] ?? 0) > 0) {
                $currencySummary[$cur]['received_original'] += (float) ($line['original_amount'] ?? $line['credit_pkr']);
                $currencySummary[$cur]['received_pkr']      += (float) $line['credit_pkr'];
                $currencySummary[$cur]['count']             += 1;
            }
            if ($line['debit_pkr'] > 0 || ($line['debit_sar'] ?? 0) > 0) {
                $currencySummary[$cur]['charges_original']  += (float) ($line['original_amount'] ?? $line['debit_pkr']);
            }
        }

        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $bookingsCount = $bookings->count();
        $transactionsCount = count($statementLines);

        return view('transaction.ledger', compact(
            'client',
            'statementLines',
            'openingBalance',
            'periodDebits',
            'periodCredits',
            'totalDebits',
            'totalCredits',
            'closingBalance',
            'openingBalancePkr',
            'periodDebitsPkr',
            'periodCreditsPkr',
            'totalDebitsPkr',
            'totalCreditsPkr',
            'closingBalancePkr',
            'openingBalanceSar',
            'periodDebitsSar',
            'periodCreditsSar',
            'totalDebitsSar',
            'totalCreditsSar',
            'closingBalanceSar',
            'fromDate',
            'toDate',
            'clients',
            'bookings',
            'bookingsCount',
            'transactionsCount',
            'currencySummary',
            // Legacy / backward-compat variables:
            'transactions',
            'totalPaid',
            'totalBooking',
            'balance',
            'bookingReceived',
            'transactionsPaid'
        ));
    }
    public function invoiceFilter(Request $request)
    {
        $clients = Client::where('status', 'active')->get();

        $transactions = collect();

        if ($request->filled('client_id')) {
            $transactions = Transaction::where('client_id', $request->client_id)
                ->latest()
                ->get();
        }

        return view('transaction.invoice-filter', compact('clients', 'transactions'));
    }

    public function companyLedgerFilter()
    {
        $companies = Company::all();
        return view('transaction.company-ledger-filter', compact('companies'));
    }

    public function companyLedgerView(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $companyId = $request->company_id;
        $company   = Company::findOrFail($companyId);

        $bookings = Booking::where('company_id', $companyId)->latest()->get();

        $totalPax        = $bookings->sum('no_of_pax');
        $bookingsCount   = $bookings->count();

        $totalBookingPkr    = 0;
        $totalBookingSar    = 0;
        $bookingReceivedPkr = 0;
        $bookingReceivedSar = 0;

        foreach ($bookings as $b) {
            $cur = strtoupper($b->currency ?: 'PKR');
            $roe = (float) ($b->flight_roe > 1 ? $b->flight_roe : 75);
            $totAmt = (float) $b->total_amount;
            $recAmt = (float) $b->total_received;
            if ($cur === 'SAR') {
                $totalBookingSar += $totAmt;
                $totalBookingPkr += round($totAmt * $roe, 2);
                $bookingReceivedSar += $recAmt;
                $bookingReceivedPkr += round($recAmt * $roe, 2);
            } else {
                $totalBookingPkr += $totAmt;
                $bookingReceivedPkr += $recAmt;
            }
        }

        $transactionsQuery = Transaction::with('booking')->where('company_id', $companyId);

        if ($request->from_date && $request->to_date) {
            $transactionsQuery->whereBetween('payment_date', [
                $request->from_date,
                $request->to_date,
            ]);
        }

        $transactions        = $transactionsQuery->latest()->get();
        $transactionsPaidPkr = (float) $transactions->where('status', 'confirmed')->sum('amount');
        $transactionsPaidSar = 0.00;

        // Also check ExpenseTransaction for company if any
        $companyEtQuery = ExpenseTransaction::where('company_id', $companyId);
        if ($request->from_date && $request->to_date) {
            $companyEtQuery->whereBetween('date', [$request->from_date, $request->to_date]);
        }
        $companyEtList = $companyEtQuery->get();
        foreach ($companyEtList as $cet) {
            $cur = strtoupper($cet->currency ?: 'PKR');
            $rate = (float) ($cet->exchange_rate > 0 ? $cet->exchange_rate : 1);
            $raw = (float) $cet->amount;
            if ($cet->transaction_type === 'credit') {
                if ($cur === 'SAR') {
                    $transactionsPaidSar += $raw;
                    $transactionsPaidPkr += round($raw * $rate, 2);
                } else {
                    $transactionsPaidPkr += $raw;
                }
            }
        }

        $totalPaidPkr = $bookingReceivedPkr + $transactionsPaidPkr;
        $totalPaidSar = $bookingReceivedSar + $transactionsPaidSar;
        $balancePkr   = $totalBookingPkr - $totalPaidPkr;
        $balanceSar   = $totalBookingSar - $totalPaidSar;

        // Backward-compatibility:
        $totalBooking     = $totalBookingPkr;
        $bookingReceived  = $bookingReceivedPkr;
        $transactionsPaid = $transactionsPaidPkr;
        $totalPaid        = $totalPaidPkr;
        $balance          = $balancePkr;

        return view('transaction.company-ledger', compact(
            'company',
            'bookings',
            'transactions',
            'totalBooking',
            'bookingReceived',
            'transactionsPaid',
            'totalPaid',
            'balance',
            'totalBookingPkr',
            'bookingReceivedPkr',
            'transactionsPaidPkr',
            'totalPaidPkr',
            'balancePkr',
            'totalBookingSar',
            'bookingReceivedSar',
            'transactionsPaidSar',
            'totalPaidSar',
            'balanceSar',
            'totalPax',
            'bookingsCount'
        ));
    }

    public function invoice($id)
    {
        $transaction = Transaction::with([
            'client',
            'company',
            'booking.hotels',
        ])->findOrFail($id);

        $booking   = $transaction->booking;
        $hotels    = $booking ? $booking->hotels : collect();
        $clientId  = $transaction->client_id;
        $companyId = $transaction->company_id;

        if ($clientId) {
            $totalBooking = Booking::where('client_id', $clientId)->sum('total_amount');

            $totalPaid = Transaction::where('client_id', $clientId)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($transaction) {
                    $q->where('payment_date', '<', $transaction->payment_date)
                        ->orWhere(function ($q2) use ($transaction) {
                            $q2->where('payment_date', '=', $transaction->payment_date)
                                ->where('id', '<=', $transaction->id);
                        });
                })
                ->sum('amount');
        } else {
            $totalBooking = Booking::where('company_id', $companyId)->sum('total_amount');

            $totalPaid = Transaction::where('company_id', $companyId)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($transaction) {
                    $q->where('payment_date', '<', $transaction->payment_date)
                        ->orWhere(function ($q2) use ($transaction) {
                            $q2->where('payment_date', '=', $transaction->payment_date)
                                ->where('id', '<=', $transaction->id);
                        });
                })
                ->sum('amount');
        }

        $balance = $totalBooking - $totalPaid;

        return view('transaction.invoice', compact(
            'transaction',
            'booking',
            'hotels',
            'totalBooking',
            'totalPaid',
            'balance'
        ));
    }

    private function updateBookingBalance($bookingId)
    {
        if (!$bookingId) return;

        $booking = Booking::find($bookingId);
        if (!$booking) return;

        $transactionsPaid = Transaction::where('booking_id', $bookingId)
            ->where('status', 'confirmed')
            ->sum('amount');

        $booking->update([
            'balance' => $booking->total_amount - $booking->total_received - $transactionsPaid,
        ]);
    }
}
