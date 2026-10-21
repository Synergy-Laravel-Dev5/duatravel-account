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

        // Totals (Dual Currency: PKR & SAR)
        $hotelTotalPkr = 0.00;
        $hotelTotalSar = 0.00;
        foreach ($hotelBookings as $h) {
            $hCostPkr = (float) $h->cost_amount;
            $hCur = strtoupper($h->currency ?: 'PKR');
            $hCostSar = 0.00;
            if ($hCur === 'SAR') {
                $hCostSar = (float) $h->cost_per_night * (int) ($h->no_of_nights ?: 1) * (int) ($h->no_of_rooms ?: 1);
                if ($hCostSar <= 0 && $h->cost_roe > 0) {
                    $hCostSar = round($hCostPkr / (float) $h->cost_roe, 2);
                }
            }
            $h->cost_pkr = $hCostPkr;
            $h->cost_sar = $hCostSar;
            $hotelTotalPkr += $hCostPkr;
            $hotelTotalSar += $hCostSar;
        }

        $flightTotalPkr = 0.00;
        $flightTotalSar = 0.00;
        foreach ($flightBookings as $fb) {
            $fCostPkr = (float) $fb->flight_cost;
            $fCur = strtoupper($fb->currency ?: 'PKR');
            $fCostSar = 0.00;
            if ($fCur === 'SAR') {
                $fCostSar = (float) $fb->flight_cost_rate * (int) ($fb->no_of_pax ?: 1);
                if ($fCostSar <= 0 && $fb->flight_roe > 0) {
                    $fCostSar = round($fCostPkr / (float) $fb->flight_roe, 2);
                }
            }
            $fb->cost_pkr = $fCostPkr;
            $fb->cost_sar = $fCostSar;
            $flightTotalPkr += $fCostPkr;
            $flightTotalSar += $fCostSar;
        }

        $visaTotalPkr = 0.00;
        $visaTotalSar = 0.00;
        foreach ($visaBookings as $vb) {
            $vCostPkr = (float) $vb->cost_amount;
            $vCur = strtoupper($vb->currency ?: 'PKR');
            $vCostSar = 0.00;
            if ($vCur === 'SAR') {
                $vCostSar = (float) ($vb->cost_rate ?? 0);
                if ($vCostSar <= 0 && ($vb->cost_roe ?? 0) > 0) {
                    $vCostSar = round($vCostPkr / (float) $vb->cost_roe, 2);
                }
            }
            $vb->cost_pkr = $vCostPkr;
            $vb->cost_sar = $vCostSar;
            $visaTotalPkr += $vCostPkr;
            $visaTotalSar += $vCostSar;
        }

        $transportTotalPkr = 0.00;
        $transportTotalSar = 0.00;
        foreach ($transportBookings as $tb) {
            $tCostPkr = (float) $tb->cost_amount;
            $tCur = strtoupper($tb->currency ?: 'PKR');
            $tCostSar = 0.00;
            if ($tCur === 'SAR') {
                $tCostSar = (float) ($tb->cost_rate ?? 0);
                if ($tCostSar <= 0 && ($tb->cost_roe ?? 0) > 0) {
                    $tCostSar = round($tCostPkr / (float) $tb->cost_roe, 2);
                }
            }
            $tb->cost_pkr = $tCostPkr;
            $tb->cost_sar = $tCostSar;
            $transportTotalPkr += $tCostPkr;
            $transportTotalSar += $tCostSar;
        }

        $bookingPayablePkr = $hotelTotalPkr + $flightTotalPkr + $visaTotalPkr + $transportTotalPkr;
        $bookingPayableSar = $hotelTotalSar + $flightTotalSar + $visaTotalSar + $transportTotalSar;

        // Process Payments with Currency Conversion
        $totalPaidPkr = 0.00;
        $totalPaidSar = 0.00;
        foreach ($transactions as $t) {
            $cur = strtoupper($t->currency ?: 'PKR');
            $rate = (float) ($t->exchange_rate > 0 ? $t->exchange_rate : 1);
            $raw = (float) $t->amount;
            if ($cur === 'SAR') {
                $tPaidSar = $raw;
                $tPaidPkr = round($raw * $rate, 2);
            } else {
                $tPaidSar = 0.00;
                $tPaidPkr = $raw;
            }
            $t->amount_pkr = $tPaidPkr;
            $t->amount_sar = $tPaidSar;
            $totalPaidPkr += $tPaidPkr;
            $totalPaidSar += $tPaidSar;
        }

        $openingBalancePkr = (float) $supplier->opening_balance;
        $openingBalanceSar = 0.00;

        $totalPayablePkr = $openingBalancePkr + $bookingPayablePkr;
        $totalPayableSar = $openingBalanceSar + $bookingPayableSar;

        $balanceDuePkr = $totalPayablePkr - $totalPaidPkr;
        $balanceDueSar = $totalPayableSar - $totalPaidSar;

        // 6. Build Chronological Statement Lines for Debit / Credit / Running Balance View
        $statementRows = [];

        foreach ($hotelBookings as $h) {
            $hDate = $h->booking_date 
                ?: ($h->booking->booking_date 
                    ?? ($h->booking->created_at ? $h->booking->created_at->format('Y-m-d') : ($h->check_in ?: date('Y-m-d'))));
            $statementRows[] = [
                'date'           => $hDate,
                'voucher_no'     => 'BK-' . ($h->booking_id ?: $h->id),
                'ref_no'         => $h->booking->voucher_number ?? ('#BK-' . ($h->booking_id ?: $h->id)),
                'type_label'     => 'Hotel Booking',
                'badge_class'    => 'bg-soft-primary text-primary border border-primary border-opacity-25',
                'contra_account' => 'Hotel Cost Payable',
                'description'    => 'Hotel: ' . ($h->hotel_name ?? 'Hotel') . ' (' . ($h->no_of_nights ?: 1) . 'N, ' . ($h->no_of_rooms ?: 1) . 'R) - ' . ($h->booking->client->name ?? ($h->booking->company->name ?? 'Guest')),
                'payment_method' => 'Credit Booking',
                'debit_pkr'      => 0,
                'credit_pkr'     => (float) $h->cost_pkr,
                'debit_sar'      => 0,
                'credit_sar'     => (float) $h->cost_sar,
                'booking_id'     => $h->booking_id,
                'proof'          => null,
            ];
        }

        foreach ($flightBookings as $fb) {
            $fDate = $fb->flight_booking_date 
                ?: ($fb->booking_date 
                    ?? ($fb->created_at ? $fb->created_at->format('Y-m-d') : ($fb->departure_date ?: date('Y-m-d'))));
            $statementRows[] = [
                'date'           => $fDate,
                'voucher_no'     => 'BK-' . $fb->id,
                'ref_no'         => $fb->voucher_number ?? ('#BK-' . $fb->id),
                'type_label'     => 'Flight Booking',
                'badge_class'    => 'bg-soft-info text-info border border-info border-opacity-25',
                'contra_account' => 'Flight Cost Payable',
                'description'    => 'Flight: ' . ($fb->departure_airline ?: 'Airline') . ' ' . ($fb->departure_flight ?: '') . ' (' . ($fb->no_of_pax ?: 1) . ' Pax) - ' . ($fb->client->name ?? ($fb->company->name ?? 'Guest')),
                'payment_method' => 'Credit Booking',
                'debit_pkr'      => 0,
                'credit_pkr'     => (float) $fb->cost_pkr,
                'debit_sar'      => 0,
                'credit_sar'     => (float) $fb->cost_sar,
                'booking_id'     => $fb->id,
                'proof'          => null,
            ];
        }

        foreach ($visaBookings as $vb) {
            $vDate = $vb->booking_date 
                ?: ($vb->booking->booking_date 
                    ?? ($vb->booking->created_at ? $vb->booking->created_at->format('Y-m-d') : ($vb->created_at ? $vb->created_at->format('Y-m-d') : date('Y-m-d'))));
            $statementRows[] = [
                'date'           => $vDate,
                'voucher_no'     => 'BK-' . ($vb->booking_id ?: $vb->id),
                'ref_no'         => $vb->booking->voucher_number ?? ('#BK-' . ($vb->booking_id ?: $vb->id)),
                'type_label'     => 'Visa Booking',
                'badge_class'    => 'bg-soft-success text-success border border-success border-opacity-25',
                'contra_account' => 'Visa Cost Payable',
                'description'    => 'Visa: ' . ($vb->given_name ?: 'Pilgrim') . ' (Pass: ' . ($vb->passport_number ?: '-') . ') - ' . ($vb->booking->client->name ?? ($vb->booking->company->name ?? 'Guest')),
                'payment_method' => 'Credit Booking',
                'debit_pkr'      => 0,
                'credit_pkr'     => (float) $vb->cost_pkr,
                'debit_sar'      => 0,
                'credit_sar'     => (float) $vb->cost_sar,
                'booking_id'     => $vb->booking_id,
                'proof'          => null,
            ];
        }

        foreach ($transportBookings as $tb) {
            $tDate = $tb->booking_date 
                ?: ($tb->booking->booking_date 
                    ?? ($tb->booking->created_at ? $tb->booking->created_at->format('Y-m-d') : ($tb->created_at ? $tb->created_at->format('Y-m-d') : date('Y-m-d'))));
            $statementRows[] = [
                'date'           => $tDate,
                'voucher_no'     => 'BK-' . ($tb->booking_id ?: $tb->id),
                'ref_no'         => $tb->booking->voucher_number ?? ('#BK-' . ($tb->booking_id ?: $tb->id)),
                'type_label'     => 'Transport Booking',
                'badge_class'    => 'bg-soft-warning text-dark border border-warning border-opacity-25',
                'contra_account' => 'Transport Cost Payable',
                'description'    => 'Transport: ' . ($tb->route ?: 'Route') . ' (' . ($tb->transport_type ?: 'Vehicle') . ') - ' . ($tb->booking->client->name ?? ($tb->booking->company->name ?? 'Guest')),
                'payment_method' => 'Credit Booking',
                'debit_pkr'      => 0,
                'credit_pkr'     => (float) $tb->cost_pkr,
                'debit_sar'      => 0,
                'credit_sar'     => (float) $tb->cost_sar,
                'booking_id'     => $tb->booking_id,
                'proof'          => null,
            ];
        }

        foreach ($transactions as $t) {
            $tDate = $t->date ? \Carbon\Carbon::parse($t->date)->format('Y-m-d') : ($t->created_at ? $t->created_at->format('Y-m-d') : date('Y-m-d'));
            $contra = $t->bank ? ($t->bank->name . ($t->bank->account_name ? ' (' . $t->bank->account_name . ')' : '')) : 'Cash in Hand';
            $statementRows[] = [
                'date'           => $tDate,
                'voucher_no'     => $t->reference_no ?: ('EXP-' . $t->id),
                'ref_no'         => $t->reference_no ?: ('#EXP-' . $t->id),
                'type_label'     => 'Payment Paid',
                'badge_class'    => 'bg-soft-danger text-danger border border-danger border-opacity-25',
                'contra_account' => $contra,
                'description'    => 'Disbursed / Paid: ' . ($t->description ?: ucwords(str_replace('_', ' ', $t->payment_type))),
                'payment_method' => ucwords(str_replace('_', ' ', $t->payment_type ?: 'Cash')),
                'debit_pkr'      => (float) $t->amount_pkr,
                'credit_pkr'     => 0,
                'debit_sar'      => (float) $t->amount_sar,
                'credit_sar'     => 0,
                'booking_id'     => null,
                'proof'          => $t->proof ? asset('assets/images/expense_proofs/' . $t->proof) : null,
            ];
        }

        // Sort chronologically ascending
        usort($statementRows, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        // Compute running balance
        $runningPkr = $openingBalancePkr;
        $runningSar = $openingBalanceSar;
        $periodDebitsPkr = 0;
        $periodCreditsPkr = 0;
        $periodDebitsSar = 0;
        $periodCreditsSar = 0;

        foreach ($statementRows as &$sRow) {
            $periodDebitsPkr += $sRow['debit_pkr'];
            $periodCreditsPkr += $sRow['credit_pkr'];
            $periodDebitsSar += $sRow['debit_sar'];
            $periodCreditsSar += $sRow['credit_sar'];

            $runningPkr += ($sRow['credit_pkr'] - $sRow['debit_pkr']);
            $runningSar += ($sRow['credit_sar'] - $sRow['debit_sar']);

            $sRow['running_balance_pkr'] = $runningPkr;
            $sRow['running_balance_sar'] = $runningSar;
            $sRow['balance_type'] = $runningPkr >= 0 ? 'Cr' : 'Dr';
            $sRow['balance_type_sar'] = $runningSar >= 0 ? 'Cr' : 'Dr';
        }
        unset($sRow);

        $statementLines = collect($statementRows);

        // Backward compatibility:
        $hotelTotal     = $hotelTotalPkr;
        $flightTotal    = $flightTotalPkr;
        $visaTotal      = $visaTotalPkr;
        $transportTotal = $transportTotalPkr;
        $bookingPayable = $bookingPayablePkr;
        $openingBalance = $openingBalancePkr;
        $totalPayable   = $totalPayablePkr;
        $totalPaid      = $totalPaidPkr;
        $balanceDue     = $balanceDuePkr;

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
            'hotelTotalPkr',
            'flightTotalPkr',
            'visaTotalPkr',
            'transportTotalPkr',
            'bookingPayablePkr',
            'openingBalancePkr',
            'totalPayablePkr',
            'totalPaidPkr',
            'balanceDuePkr',
            'hotelTotalSar',
            'flightTotalSar',
            'visaTotalSar',
            'transportTotalSar',
            'bookingPayableSar',
            'openingBalanceSar',
            'totalPayableSar',
            'totalPaidSar',
            'balanceDueSar',
            'fromDate',
            'toDate',
            'statementLines',
            'periodDebitsPkr',
            'periodCreditsPkr',
            'periodDebitsSar',
            'periodCreditsSar'
        ));
    }

    public function jsonList()
    {
        $suppliers = Supplier::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        return response()->json($suppliers);
    }
}
