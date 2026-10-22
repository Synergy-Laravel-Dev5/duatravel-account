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

        $lastId = ExpenseTransaction::withTrashed()->max('id') ?? 0;
        $autoRef = 'DT-EXP-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

        return view('expense_transaction.create', compact('expenses', 'banks', 'suppliers', 'clients', 'staffMembers', 'autoRef'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_id'          => 'nullable|exists:banks,id',
            'expense_id'       => 'nullable|exists:expenses,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'client_id'        => 'nullable|exists:clients,id',
            'staff_id'         => 'nullable|exists:staff,id',
            'hajj_umrah'       => 'required|in:hajj,umrah',
            'year'             => 'required|integer|digits:4|min:2000|max:2100',
            'amount'           => 'required|numeric|min:0.01',
            'payment_type'     => 'required|string|max:50',
            'transaction_type' => 'nullable|string',
            'payee_type'       => 'nullable|in:supplier,client,staff,expense,general,self,jv',
            'currency'         => 'required|string|max:10',
            'exchange_rate'    => 'nullable|numeric|min:0.0001',
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
            $exchangeRate = ($currency === 'PKR') ? 1 : floatval($request->exchange_rate ?: 1);
            $payeeType = $request->payee_type ?: 'supplier';

            $supplierId = ($payeeType === 'supplier') ? ($request->supplier_id ?: null) : null;
            $clientId   = ($payeeType === 'client') ? ($request->client_id ?: null) : null;
            $staffId    = ($payeeType === 'staff') ? ($request->staff_id ?: null) : null;
            $companyId  = ($payeeType === 'company') ? ($request->company_id ?: null) : null;
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

            $transaction = ExpenseTransaction::create([
                'bank_id'          => $request->bank_id ?: null,
                'expense_id'       => $expenseId,
                'supplier_id'      => $supplierId,
                'client_id'        => $clientId,
                'company_id'       => $companyId,
                'staff_id'         => $staffId,
                'hajj_umrah'       => $request->hajj_umrah,
                'year'             => $request->year,
                'amount'           => $request->amount,
                'payment_type'     => $request->payment_type,
                'transaction_type' => $request->transaction_type ?: 'debit',
                'payee_type'       => $payeeType,
                'currency'         => $currency,
                'exchange_rate'    => $exchangeRate,
                'reference_no'     => $request->reference_no,
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

        return view('expense_transaction.edit', compact('transaction', 'expenses', 'banks', 'suppliers', 'clients', 'staffMembers'));
    }

    public function update(Request $request, $id)
    {
        $transaction = ExpenseTransaction::findOrFail($id);

        $request->validate([
            'bank_id'          => 'nullable|exists:banks,id',
            'expense_id'       => 'nullable|exists:expenses,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'client_id'        => 'nullable|exists:clients,id',
            'staff_id'         => 'nullable|exists:staff,id',
            'hajj_umrah'       => 'required|in:hajj,umrah',
            'year'             => 'required|integer|digits:4|min:2000|max:2100',
            'amount'           => 'required|numeric|min:0.01',
            'payment_type'     => 'required|string|max:50',
            'transaction_type' => 'nullable|string',
            'payee_type'       => 'nullable|in:supplier,client,staff,expense,general,self,jv',
            'currency'         => 'required|string|max:10',
            'exchange_rate'    => 'nullable|numeric|min:0.0001',
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
            $exchangeRate = ($currency === 'PKR') ? 1 : floatval($request->exchange_rate ?: 1);
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

            $transaction->update([
                'bank_id'          => $request->bank_id ?: null,
                'expense_id'       => $expenseId,
                'supplier_id'      => $supplierId,
                'client_id'        => $clientId,
                'company_id'       => $companyId,
                'staff_id'         => $staffId,
                'hajj_umrah'       => $request->hajj_umrah,
                'year'             => $request->year,
                'amount'           => $request->amount,
                'payment_type'     => $request->payment_type,
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

        $transaction->delete();

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

                $supplierFlights = Booking::where('flight_supplier_id', $selectedSupplier->id)
                    ->when($request->filled('hajj_umrah') && $request->hajj_umrah !== 'all', fn($q) => $q->where('package_type', $request->hajj_umrah))
                    ->when($request->filled('year') && $request->year !== 'all', fn($q) => $q->where('package_year', $request->year))
                    ->with('client')
                    ->get();

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
                $totalFlightCost    = (float) $supplierFlights->sum('flight_cost');
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
