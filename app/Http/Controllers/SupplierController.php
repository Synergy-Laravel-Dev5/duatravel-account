<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingVisa;
use App\Models\BookingTransport;
use App\Models\ExpenseTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        $trashCount = Supplier::onlyTrashed()->count();

        return view('suppliers.index', compact('suppliers', 'trashCount'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'nullable|string|max:255',
            'service_type'    => 'nullable|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'status'          => 'nullable|in:active,inactive',
        ]);

        $supplier = Supplier::create([
            'name'            => $request->name,
            'company_name'    => $request->company_name,
            'service_type'    => $request->service_type ?? 'all',
            'contact_person'  => $request->contact_person,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'address'         => $request->address,
            'city'            => $request->city,
            'country'         => $request->country ?? 'Pakistan',
            'opening_balance' => $request->opening_balance ?? 0,
            'status'          => $request->status ?? 'active',
        ]);

        if (function_exists('logUserActivity')) {
            logUserActivity('Supplier Created', 'Name: ' . $supplier->name . ' | Type: ' . $supplier->service_type, $supplier->id, 'Supplier');
        }

        return redirect()->route('supplier.index')->with('success', 'Supplier created successfully.');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'nullable|string|max:255',
            'service_type'    => 'nullable|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'status'          => 'nullable|in:active,inactive',
        ]);

        $supplier->update([
            'name'            => $request->name,
            'company_name'    => $request->company_name,
            'service_type'    => $request->service_type ?? 'all',
            'contact_person'  => $request->contact_person,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'address'         => $request->address,
            'city'            => $request->city,
            'country'         => $request->country ?? 'Pakistan',
            'opening_balance' => $request->opening_balance ?? 0,
            'status'          => $request->status ?? 'active',
        ]);

        if (function_exists('logUserActivity')) {
            logUserActivity('Supplier Updated', 'Name: ' . $supplier->name . ' | Type: ' . $supplier->service_type, $supplier->id, 'Supplier');
        }

        return redirect()->route('supplier.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        if (function_exists('logUserActivity')) {
            logUserActivity('Supplier Deleted', 'Name: ' . $supplier->name, $supplier->id, 'Supplier');
        }

        return redirect()->route('supplier.index')->with('success', 'Supplier deleted successfully.');
    }

    public function trash()
    {
        $suppliers = Supplier::onlyTrashed()->latest()->get();
        return view('suppliers.trash', compact('suppliers'));
    }

    public function restore($id)
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();

        return redirect()->route('supplier.trash')->with('success', 'Supplier restored successfully.');
    }

    public function ledgerFilter()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        return view('suppliers.ledger_filter', compact('suppliers'));
    }

    public function ledger(Request $request, $id = null)
    {
        $supplierId = $id ?: $request->supplier_id;
        if (!$supplierId) {
            return redirect()->route('supplier.ledger.filter')->with('error', 'Please select a supplier.');
        }

        $supplier = Supplier::findOrFail($supplierId);
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // 1. Hotel Bookings
        $hotelQuery = BookingHotel::with(['booking.client', 'booking.company'])
            ->where('supplier_id', $supplier->id)
            ->whereHas('booking');
        if ($fromDate && $toDate) {
            $hotelQuery->whereBetween('check_in', [$fromDate, $toDate]);
        }
        $hotelBookings = $hotelQuery->latest('check_in')->get();

        // 2. Flight Bookings
        $flightQuery = Booking::with(['client', 'company'])
            ->where('flight_supplier_id', $supplier->id)
            ->where('flight_cost', '>', 0);
        if ($fromDate && $toDate) {
            $flightQuery->whereBetween('departure_date', [$fromDate, $toDate]);
        }
        $flightBookings = $flightQuery->latest('departure_date')->get();

        // 3. Visa Bookings
        $visaQuery = BookingVisa::with(['booking.client', 'booking.company'])
            ->where('supplier_id', $supplier->id)
            ->whereHas('booking');
        if ($fromDate && $toDate) {
            $visaQuery->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
        }
        $visaBookings = $visaQuery->latest()->get();

        // 4. Transport Bookings
        $transportQuery = BookingTransport::with(['booking.client', 'booking.company'])
            ->where('supplier_id', $supplier->id)
            ->whereHas('booking');
        if ($fromDate && $toDate) {
            $transportQuery->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
        }
        $transportBookings = $transportQuery->latest()->get();

        // 5. Expense Transactions (Payments made to this supplier)
        $transQuery = ExpenseTransaction::with('bank')
            ->where('supplier_id', $supplier->id);
        if ($fromDate && $toDate) {
            $transQuery->whereBetween('date', [$fromDate, $toDate]);
        }
        $transactions = $transQuery->latest('date')->latest('id')->get();

        // Totals
        $hotelTotal = $hotelBookings->sum('cost_amount');
        $flightTotal = $flightBookings->sum('flight_cost');
        $visaTotal = $visaBookings->sum('cost_amount');
        $transportTotal = $transportBookings->sum('cost_amount');
        $bookingPayable = $hotelTotal + $flightTotal + $visaTotal + $transportTotal;

        $openingBalance = (float) $supplier->opening_balance;
        $totalPayable = $openingBalance + $bookingPayable;
        $totalPaid = $transactions->sum('amount');
        $balanceDue = $totalPayable - $totalPaid;

        return view('suppliers.ledger', compact(
            'supplier',
            'hotelBookings',
            'flightBookings',
            'visaBookings',
            'transportBookings',
            'transactions',
            'openingBalance',
            'hotelTotal',
            'flightTotal',
            'visaTotal',
            'transportTotal',
            'bookingPayable',
            'totalPayable',
            'totalPaid',
            'balanceDue',
            'fromDate',
            'toDate'
        ));
    }

    public function jsonList()
    {
        $suppliers = Supplier::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        return response()->json($suppliers);
    }
}
