<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Company;
use App\Models\Transaction;
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
            // booking_id is now required: the create form always shows a visible
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

        $transaction = Transaction::create([
            'client_id'        => $clientId,
            'company_id'       => $companyId,
            'booking_id'       => $bookingId,
            'payment_type'     => $request->payment_type,
            'amount'           => $request->amount,
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

        $transaction->update([
            'client_id'        => $clientId,
            'company_id'       => $companyId,
            'booking_id'       => $bookingId,
            'payment_type'     => $request->payment_type,
            'amount'           => $request->amount,
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
        $request->validate([
            'client_id' => 'required',
        ]);

        $clientId = $request->client_id;

        if ($clientId === 'all') {
            $query = Transaction::with(['booking', 'client', 'company']);

            if ($request->from_date && $request->to_date) {
                $query->whereBetween('payment_date', [
                    $request->from_date,
                    $request->to_date,
                ]);
            }

            $transactions     = $query->latest()->get();
            $transactionsPaid = $transactions->where('status', 'confirmed')->sum('amount');
            $bookingReceived  = Booking::sum('total_received');
            $totalBooking     = Booking::sum('total_amount');
            $totalPaid        = $bookingReceived + $transactionsPaid;
            $balance          = $totalBooking - $totalPaid;

            $client = (object)[
                'id'     => 'all',
                'name'   => 'All Clients',
                'phone'  => null,
                'email'  => null,
                'status' => 'active',
            ];

            return view('transaction.ledger', compact(
                'client',
                'transactions',
                'totalPaid',
                'totalBooking',
                'balance',
                'bookingReceived',
                'transactionsPaid'
            ));
        }

        if (!Client::where('id', $clientId)->exists()) {
            abort(404);
        }

        $client = Client::findOrFail($clientId);
        $query  = Transaction::with('booking')->where('client_id', $clientId);

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('payment_date', [
                $request->from_date,
                $request->to_date,
            ]);
        }

        $transactions     = $query->latest()->get();
        $transactionsPaid = $transactions->where('status', 'confirmed')->sum('amount');
        $bookingReceived  = Booking::where('client_id', $clientId)->sum('total_received');
        $totalBooking     = Booking::where('client_id', $clientId)->sum('total_amount');
        $totalPaid        = $bookingReceived + $transactionsPaid;
        $balance          = $totalBooking - $totalPaid;

        return view('transaction.ledger', compact(
            'client',
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

        $totalBooking    = $bookings->sum('total_amount');
        $bookingReceived = $bookings->sum('total_received');
        $totalPax        = $bookings->sum('no_of_pax');
        $bookingsCount   = $bookings->count();

        $transactionsQuery = Transaction::with('booking')->where('company_id', $companyId);

        if ($request->from_date && $request->to_date) {
            $transactionsQuery->whereBetween('payment_date', [
                $request->from_date,
                $request->to_date,
            ]);
        }

        $transactions     = $transactionsQuery->latest()->get();
        $transactionsPaid = $transactions->where('status', 'confirmed')->sum('amount');

        $totalPaid = $bookingReceived + $transactionsPaid;
        $balance   = $totalBooking - $totalPaid;

        return view('transaction.company-ledger', compact(
            'company',
            'bookings',
            'transactions',
            'totalBooking',
            'bookingReceived',
            'transactionsPaid',
            'totalPaid',
            'balance',
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
