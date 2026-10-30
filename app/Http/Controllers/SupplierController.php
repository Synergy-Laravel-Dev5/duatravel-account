<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Booking;
use App\Models\BookingPerson;
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
            'country'                  => 'nullable|string|max:100',
            'opening_balance'          => 'nullable|numeric',
            'opening_balance_pkr'      => 'nullable|numeric',
            'opening_balance_sar'      => 'nullable|numeric',
            'opening_balance_pkr_type' => 'nullable|in:debit,credit',
            'opening_balance_sar_type' => 'nullable|in:debit,credit',
            'opening_balance_roe'      => 'nullable|numeric',
            'status'                   => 'nullable|in:active,inactive',
        ]);

        $obPkr = $request->filled('opening_balance_pkr') ? (float)$request->opening_balance_pkr : ((float)($request->opening_balance ?? 0));
        $obSar = (float)($request->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : 75.0);
        if ($obRoe <= 1) {
            // When ROE is 1 or less, opening balance does NOT go to SAR
            $obSar = 0;
        } else {
            if ($obSar == 0 && $obPkr != 0) {
                $obSar = round($obPkr / $obRoe, 2);
            } elseif ($obPkr == 0 && $obSar != 0) {
                $obPkr = round($obSar * $obRoe, 2);
            }
        }

        $supplier = Supplier::create([
            'name'                     => $request->name,
            'company_name'             => $request->company_name,
            'service_type'             => $request->service_type ?? 'all',
            'contact_person'           => $request->contact_person,
            'phone'                    => $request->phone,
            'email'                    => $request->email,
            'address'                  => $request->address,
            'city'                     => $request->city,
            'country'                  => $request->country ?? 'Pakistan',
            'opening_balance'          => $obPkr,
            'opening_balance_pkr'      => $obPkr,
            'opening_balance_sar'      => $obSar,
            'opening_balance_pkr_type' => $request->opening_balance_pkr_type ?? 'credit',
            'opening_balance_sar_type' => $request->opening_balance_sar_type ?? 'credit',
            'opening_balance_roe'      => $obRoe,
            'status'                   => $request->status ?? 'active',
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
            'country'                  => 'nullable|string|max:100',
            'opening_balance'          => 'nullable|numeric',
            'opening_balance_pkr'      => 'nullable|numeric',
            'opening_balance_sar'      => 'nullable|numeric',
            'opening_balance_pkr_type' => 'nullable|in:debit,credit',
            'opening_balance_sar_type' => 'nullable|in:debit,credit',
            'opening_balance_roe'      => 'nullable|numeric',
            'status'                   => 'nullable|in:active,inactive',
        ]);

        $obPkr = $request->filled('opening_balance_pkr') ? (float)$request->opening_balance_pkr : ((float)($request->opening_balance ?? 0));
        $obSar = $request->filled('opening_balance_sar') ? (float)$request->opening_balance_sar : (float)($supplier->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : ($supplier->opening_balance_roe ?: 75.0));
        if ($obRoe <= 1) {
            // When ROE is 1 or less, opening balance does NOT go to SAR
            $obSar = 0;
        } else {
            if ($obSar == 0 && $obPkr != 0) {
                $obSar = round($obPkr / $obRoe, 2);
            } elseif ($obPkr == 0 && $obSar != 0) {
                $obPkr = round($obSar * $obRoe, 2);
            }
        }

        $supplier->update([
            'name'                     => $request->name,
            'company_name'             => $request->company_name,
            'service_type'             => $request->service_type ?? 'all',
            'contact_person'           => $request->contact_person,
            'phone'                    => $request->phone,
            'email'                    => $request->email,
            'address'                  => $request->address,
            'city'                     => $request->city,
            'country'                  => $request->country ?? 'Pakistan',
            'opening_balance'          => $obPkr,
            'opening_balance_pkr'      => $obPkr,
            'opening_balance_sar'      => $obSar,
            'opening_balance_pkr_type' => $request->opening_balance_pkr_type ?? 'credit',
            'opening_balance_sar_type' => $request->opening_balance_sar_type ?? 'credit',
            'opening_balance_roe'      => $obRoe,
            'status'                   => $request->status ?? 'active',
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
        $supplierId = ($id && is_numeric($id)) ? $id : ($request->supplier_id ?: $request->id);
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

        // 2. Flight Bookings (Supports both individual passenger flight suppliers and booking-level flight supplier)
        $flightQuery = Booking::with(['client', 'company', 'persons'])
            ->where(function ($q) use ($supplier) {
                $q->whereHas('persons', function ($pq) use ($supplier) {
                    $pq->where('flight_supplier_id', $supplier->id);
                })->orWhere(function ($mq) use ($supplier) {
                    $mq->where('flight_supplier_id', $supplier->id)
                       ->where(function ($sub) {
                           $sub->doesntHave('persons')
                               ->orWhereHas('persons', function ($pq) {
                                   $pq->whereNull('flight_supplier_id')->orWhere('flight_supplier_id', '');
                               });
                       });
                });
            })
            ->where(function ($q) {
                $q->where('flight_cost', '>', 0)
                  ->orWhereHas('persons', function ($pq) {
                      $pq->where('flight_cost', '>', 0);
                  });
            });
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
            $hRoe = (float) ($h->cost_roe > 0 ? $h->cost_roe : ($h->roe > 0 ? $h->roe : 75));
            if ($hCur === 'SAR') {
                $hCostSar = (float) $h->cost_per_night * (int) ($h->no_of_nights ?: 1) * (int) ($h->no_of_rooms ?: 1);
                if ($hCostSar <= 0 && $hRoe > 0) {
                    $hCostSar = round($hCostPkr / $hRoe, 2);
                }
            } elseif ($hCostPkr > 0 && $hRoe > 1) {
                $hCostSar = round($hCostPkr / $hRoe, 2);
            }
            $h->cost_pkr = $hCostPkr;
            $h->cost_sar = $hCostSar;
            $hotelTotalPkr += $hCostPkr;
            $hotelTotalSar += $hCostSar;
        }

        $flightTotalPkr = 0.00;
        $flightTotalSar = 0.00;
        foreach ($flightBookings as $fb) {
            $fCur = strtoupper($fb->flight_currency ?: ($fb->currency ?: 'PKR'));
            $fRoe = (float) ($fb->flight_cost_roe > 0 ? $fb->flight_cost_roe : ($fb->flight_roe > 0 ? $fb->flight_roe : 75));

            // Filter persons belonging to this supplier in this booking
            $supplierPersons = $fb->persons ? $fb->persons->filter(function ($p) use ($supplier, $fb) {
                if (!empty($p->flight_supplier_id)) {
                    return (int) $p->flight_supplier_id === (int) $supplier->id;
                }
                return (int) $fb->flight_supplier_id === (int) $supplier->id;
            }) : collect();

            $totalPaxCount = (int) ($fb->no_of_pax ?: ($fb->persons ? $fb->persons->count() : 1));
            if ($totalPaxCount < 1) $totalPaxCount = 1;

            $supplierPaxCount = $supplierPersons->count();
            if ($supplierPaxCount === 0 && (int) $fb->flight_supplier_id === (int) $supplier->id) {
                $supplierPaxCount = $totalPaxCount;
            }

            $personCostSum = $supplierPersons->sum(function ($p) {
                return (float) $p->flight_cost;
            });

            if ($personCostSum > 0) {
                if ($fCur === 'SAR') {
                    $fCostSar = $personCostSum;
                    $fCostPkr = round($personCostSum * ($fRoe > 1 ? $fRoe : 75), 2);
                } else {
                    $fCostPkr = $personCostSum;
                    $fCostSar = ($fRoe > 1) ? round($personCostSum / $fRoe, 2) : 0.00;
                }
                $costRate = $supplierPaxCount > 0 ? round($personCostSum / $supplierPaxCount, 2) : $personCostSum;
            } else {
                $ratio = ($totalPaxCount > 0) ? ($supplierPaxCount / $totalPaxCount) : 1;
                $fullCostPkr = (float) $fb->flight_cost;
                $fCostPkr = round($fullCostPkr * $ratio, 2);

                if ($fCur === 'SAR') {
                    $fullCostSar = (float) $fb->flight_cost_rate * $totalPaxCount;
                    if ($fullCostSar <= 0 && $fRoe > 0) {
                        $fullCostSar = round($fullCostPkr / $fRoe, 2);
                    }
                    $fCostSar = round($fullCostSar * $ratio, 2);
                } elseif ($fCostPkr > 0 && $fRoe > 1) {
                    $fCostSar = round($fCostPkr / $fRoe, 2);
                } else {
                    $fCostSar = 0.00;
                }
                $costRate = (float) $fb->flight_cost_rate;
            }

            $fb->supplier_pax_count = $supplierPaxCount;
            $fb->supplier_cost_rate = $costRate;
            $fb->cost_pkr = $fCostPkr;
            $fb->cost_sar = $fCostSar;

            // Details for description & table display
            if ($supplierPersons->isNotEmpty()) {
                $paxDetails = $supplierPersons->map(function ($p) {
                    $tkt = $p->flight_ticket ? " (Tkt: {$p->flight_ticket})" : '';
                    return ($p->full_name ?: 'Passenger') . $tkt;
                })->implode(', ');
                $fb->supplier_pax_details = $paxDetails;
                $pnrList = $supplierPersons->pluck('flight_pnr')->filter()->unique()->implode(', ');
                $fb->supplier_pnr = $pnrList ?: $fb->departure_pnr;
                $desc = 'Flight: ' . ($fb->departure_airline ?: 'Airline') . ' ' . ($fb->departure_flight ?: '') . ' (' . $supplierPaxCount . ' Pax) - ' . $paxDetails;
            } else {
                $fb->supplier_pax_details = $fb->client->name ?? ($fb->company->name ?? 'Guest');
                $fb->supplier_pnr = $fb->departure_pnr;
                $desc = 'Flight: ' . ($fb->departure_airline ?: 'Airline') . ' ' . ($fb->departure_flight ?: '') . ' (' . $supplierPaxCount . ' Pax) - ' . $fb->supplier_pax_details;
            }
            $fb->supplier_description = $desc;

            $flightTotalPkr += $fCostPkr;
            $flightTotalSar += $fCostSar;
        }

        $visaTotalPkr = 0.00;
        $visaTotalSar = 0.00;
        foreach ($visaBookings as $vb) {
            $vCostPkr = (float) $vb->cost_amount;
            $vCur = strtoupper($vb->currency ?: 'PKR');
            $vCostSar = 0.00;
            $vRoe = (float) ($vb->cost_roe > 0 ? $vb->cost_roe : ($vb->roe > 0 ? $vb->roe : 75));
            if ($vCur === 'SAR') {
                $vCostSar = (float) ($vb->cost_rate ?? 0);
                if ($vCostSar <= 0 && $vRoe > 0) {
                    $vCostSar = round($vCostPkr / $vRoe, 2);
                }
            } elseif ($vCostPkr > 0 && $vRoe > 1) {
                $vCostSar = round($vCostPkr / $vRoe, 2);
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
            $tRoe = (float) ($tb->cost_roe > 0 ? $tb->cost_roe : ($tb->roe > 0 ? $tb->roe : 75));
            if ($tCur === 'SAR') {
                $tCostSar = (float) ($tb->cost_rate ?? 0);
                if ($tCostSar <= 0 && $tRoe > 0) {
                    $tCostSar = round($tCostPkr / $tRoe, 2);
                }
            } elseif ($tCostPkr > 0 && $tRoe > 1) {
                $tCostSar = round($tCostPkr / $tRoe, 2);
            }
            $tb->cost_pkr = $tCostPkr;
            $tb->cost_sar = $tCostSar;
            $transportTotalPkr += $tCostPkr;
            $transportTotalSar += $tCostSar;
        }

        $bookingPayablePkr = $hotelTotalPkr + $flightTotalPkr + $visaTotalPkr + $transportTotalPkr;
        $bookingPayableSar = $hotelTotalSar + $flightTotalSar + $visaTotalSar + $transportTotalSar;

        // Determine Default Exchange Rate (ROE) from supplier opening balance, transactions or bookings
        $defaultRoe = (float) ($supplier->opening_balance_roe > 1 ? $supplier->opening_balance_roe : 75.0);
        if ($defaultRoe == 75.0) {
            foreach ($transactions as $t) {
                if ((float) $t->exchange_rate > 1) {
                    $defaultRoe = (float) $t->exchange_rate;
                    break;
                }
            }
        }
        if ($defaultRoe == 75.0) {
            foreach ($hotelBookings as $hb) {
                $r = (float) ($hb->cost_roe > 1 ? $hb->cost_roe : ($hb->roe > 1 ? $hb->roe : 0));
                if ($r > 1) { $defaultRoe = $r; break; }
            }
            if ($defaultRoe == 75.0) {
                foreach ($flightBookings as $fb) {
                    $r = (float) ($fb->flight_cost_roe > 1 ? $fb->flight_cost_roe : ($fb->flight_roe > 1 ? $fb->flight_roe : 0));
                    if ($r > 1) { $defaultRoe = $r; break; }
                }
            }
            if ($defaultRoe == 75.0) {
                foreach ($visaBookings as $vb) {
                    $r = (float) ($vb->cost_roe > 1 ? $vb->cost_roe : ($vb->roe > 1 ? $vb->roe : 0));
                    if ($r > 1) { $defaultRoe = $r; break; }
                }
            }
            if ($defaultRoe == 75.0) {
                foreach ($transportBookings as $tb) {
                    $r = (float) ($tb->cost_roe > 1 ? $tb->cost_roe : ($tb->roe > 1 ? $tb->roe : 0));
                    if ($r > 1) { $defaultRoe = $r; break; }
                }
            }
        }

        // Process Payments with Currency Conversion
        $totalPaidPkr = 0.00;
        $totalPaidSar = 0.00;
        foreach ($transactions as $t) {
            $cur = strtoupper($t->currency ?: 'PKR');
            $rate = (float) ($t->exchange_rate > 0 ? $t->exchange_rate : $defaultRoe);
            $raw = (float) $t->amount;
            $tPaidPkr = $raw;
            $tPaidSar = ($rate > 1) ? round($raw / $rate, 2) : 0.00;
            $t->amount_pkr = $tPaidPkr;
            $t->amount_sar = $tPaidSar;
            $totalPaidPkr += $tPaidPkr;
            $totalPaidSar += $tPaidSar;
        }

        // Initial Supplier Opening Balance (from supplier profile)
        $pkrSign = ($supplier->opening_balance_pkr_type === 'debit') ? -1 : 1;
        $sarSign = ($supplier->opening_balance_sar_type === 'debit') ? -1 : 1;
        $supplierInitialOpPkr = $pkrSign * (float) ($supplier->opening_balance_pkr ?: $supplier->opening_balance ?: 0);
        $supplierInitialOpSar = $sarSign * (float) ($supplier->opening_balance_sar ?: 0);

        // If opening balance was provided in PKR but not in SAR (or vice-versa), auto-sync so both currencies match ONLY if ROE > 1
        $supplierRoe = (float)($supplier->opening_balance_roe ?? 0);
        if ($supplierRoe > 1) {
            if ($supplierInitialOpSar == 0 && $supplierInitialOpPkr != 0) {
                $supplierInitialOpSar = round($supplierInitialOpPkr / $defaultRoe, 2);
            } elseif ($supplierInitialOpPkr == 0 && $supplierInitialOpSar != 0) {
                $supplierInitialOpPkr = round($supplierInitialOpSar * $defaultRoe, 2);
            }

            if ($supplierInitialOpPkr != 0) {
                if (($supplierInitialOpPkr > 0 && $supplierInitialOpSar < 0) || ($supplierInitialOpPkr < 0 && $supplierInitialOpSar > 0) || $supplierInitialOpSar == 0) {
                    $supplierInitialOpSar = round($supplierInitialOpPkr / $defaultRoe, 2);
                }
            } elseif ($supplierInitialOpPkr == 0) {
                $supplierInitialOpSar = 0.00;
            }
        } else {
            // ROE is 1 or less: PKR only, no SAR opening balance
            $supplierInitialOpSar = 0.00;
        }

        $openingBalancePkr = $supplierInitialOpPkr;
        $openingBalanceSar = $supplierInitialOpSar;

        if ($fromDate) {
            $priorHotelCreditsPkr = 0; $priorHotelCreditsSar = 0;
            $priorHotels = BookingHotel::where('supplier_id', $supplier->id)->whereHas('booking')->where('check_in', '<', $fromDate)->get();
            foreach ($priorHotels as $ph) {
                $hCur = strtoupper($ph->currency ?: 'PKR');
                $hRoe = (float) ($ph->cost_roe > 0 ? $ph->cost_roe : ($ph->roe > 0 ? $ph->roe : $defaultRoe));
                $phCostPkr = (float) $ph->cost_amount;
                $phCostSar = ($hCur === 'SAR') ? ((float) $ph->cost_per_night * (int) ($ph->no_of_nights ?: 1) * (int) ($ph->no_of_rooms ?: 1)) : (($hRoe > 1) ? round($phCostPkr / $hRoe, 2) : 0.00);
                if ($phCostSar <= 0 && $hRoe > 0 && $phCostPkr > 0) $phCostSar = round($phCostPkr / $hRoe, 2);
                $priorHotelCreditsPkr += $phCostPkr;
                $priorHotelCreditsSar += $phCostSar;
            }

            $priorFlightCreditsPkr = 0; $priorFlightCreditsSar = 0;
            $priorFlights = Booking::with('persons')
                ->where(function ($q) use ($supplier) {
                    $q->whereHas('persons', function ($pq) use ($supplier) {
                        $pq->where('flight_supplier_id', $supplier->id);
                    })->orWhere(function ($mq) use ($supplier) {
                        $mq->where('flight_supplier_id', $supplier->id)
                           ->where(function ($sub) {
                               $sub->doesntHave('persons')
                                   ->orWhereHas('persons', function ($pq) {
                                       $pq->whereNull('flight_supplier_id')->orWhere('flight_supplier_id', '');
                                   });
                           });
                    });
                })
                ->where(function ($q) {
                    $q->where('flight_cost', '>', 0)
                      ->orWhereHas('persons', function ($pq) {
                          $pq->where('flight_cost', '>', 0);
                      });
                })
                ->where('departure_date', '<', $fromDate)
                ->get();

            foreach ($priorFlights as $pf) {
                $fCur = strtoupper($pf->flight_currency ?: ($pf->currency ?: 'PKR'));
                $fRoe = (float) ($pf->flight_cost_roe > 0 ? $pf->flight_cost_roe : ($pf->flight_roe > 0 ? $pf->flight_roe : $defaultRoe));

                $supplierPersons = $pf->persons ? $pf->persons->filter(function ($p) use ($supplier, $pf) {
                    if (!empty($p->flight_supplier_id)) {
                        return (int) $p->flight_supplier_id === (int) $supplier->id;
                    }
                    return (int) $pf->flight_supplier_id === (int) $supplier->id;
                }) : collect();

                $totalPaxCount = (int) ($pf->no_of_pax ?: ($pf->persons ? $pf->persons->count() : 1));
                if ($totalPaxCount < 1) $totalPaxCount = 1;

                $supplierPaxCount = $supplierPersons->count();
                if ($supplierPaxCount === 0 && (int) $pf->flight_supplier_id === (int) $supplier->id) {
                    $supplierPaxCount = $totalPaxCount;
                }

                $personCostSum = $supplierPersons->sum(function ($p) {
                    return (float) $p->flight_cost;
                });

                if ($personCostSum > 0) {
                    if ($fCur === 'SAR') {
                        $pfCostSar = $personCostSum;
                        $pfCostPkr = round($personCostSum * ($fRoe > 1 ? $fRoe : 75), 2);
                    } else {
                        $pfCostPkr = $personCostSum;
                        $pfCostSar = ($fRoe > 1) ? round($personCostSum / $fRoe, 2) : 0.00;
                    }
                } else {
                    $ratio = ($totalPaxCount > 0) ? ($supplierPaxCount / $totalPaxCount) : 1;
                    $fullCostPkr = (float) $pf->flight_cost;
                    $pfCostPkr = round($fullCostPkr * $ratio, 2);

                    if ($fCur === 'SAR') {
                        $fullCostSar = (float) $pf->flight_cost_rate * $totalPaxCount;
                        if ($fullCostSar <= 0 && $fRoe > 0) {
                            $fullCostSar = round($fullCostPkr / $fRoe, 2);
                        }
                        $pfCostSar = round($fullCostSar * $ratio, 2);
                    } elseif ($pfCostPkr > 0 && $fRoe > 1) {
                        $pfCostSar = round($pfCostPkr / $fRoe, 2);
                    } else {
                        $pfCostSar = 0.00;
                    }
                }

                if ($pfCostSar <= 0 && $fRoe > 0 && $pfCostPkr > 0) $pfCostSar = round($pfCostPkr / $fRoe, 2);
                $priorFlightCreditsPkr += $pfCostPkr;
                $priorFlightCreditsSar += $pfCostSar;
            }

            $priorVisaCreditsPkr = 0; $priorVisaCreditsSar = 0;
            $priorVisas = BookingVisa::where('supplier_id', $supplier->id)->whereHas('booking')->where('created_at', '<', $fromDate . ' 00:00:00')->get();
            foreach ($priorVisas as $pv) {
                $vCur = strtoupper($pv->currency ?: 'PKR');
                $vRoe = (float) ($pv->cost_roe > 0 ? $pv->cost_roe : ($pv->roe > 0 ? $pv->roe : $defaultRoe));
                $pvCostPkr = (float) $pv->cost_amount;
                $pvCostSar = ($vCur === 'SAR') ? (float) ($pv->cost_rate ?? 0) : (($vRoe > 1) ? round($pvCostPkr / $vRoe, 2) : 0.00);
                if ($pvCostSar <= 0 && $vRoe > 0 && $pvCostPkr > 0) $pvCostSar = round($pvCostPkr / $vRoe, 2);
                $priorVisaCreditsPkr += $pvCostPkr;
                $priorVisaCreditsSar += $pvCostSar;
            }

            $priorTransportCreditsPkr = 0; $priorTransportCreditsSar = 0;
            $priorTransports = BookingTransport::where('supplier_id', $supplier->id)->whereHas('booking')->where('created_at', '<', $fromDate . ' 00:00:00')->get();
            foreach ($priorTransports as $pt) {
                $tCur = strtoupper($pt->currency ?: 'PKR');
                $tRoe = (float) ($pt->cost_roe > 0 ? $pt->cost_roe : ($pt->roe > 0 ? $pt->roe : $defaultRoe));
                $ptCostPkr = (float) $pt->cost_amount;
                $ptCostSar = ($tCur === 'SAR') ? (float) ($pt->cost_rate ?? 0) : (($tRoe > 1) ? round($ptCostPkr / $tRoe, 2) : 0.00);
                if ($ptCostSar <= 0 && $tRoe > 0 && $ptCostPkr > 0) $ptCostSar = round($ptCostPkr / $tRoe, 2);
                $priorTransportCreditsPkr += $ptCostPkr;
                $priorTransportCreditsSar += $ptCostSar;
            }

            $priorTxnDebitsPkr = 0; $priorTxnDebitsSar = 0;
            $priorTxnCreditsPkr = 0; $priorTxnCreditsSar = 0;
            $priorTxns = ExpenseTransaction::where('supplier_id', $supplier->id)->where('date', '<', $fromDate)->get();
            foreach ($priorTxns as $ptx) {
                $cur = strtoupper($ptx->currency ?: 'PKR');
                $rate = (float) ($ptx->exchange_rate > 0 ? $ptx->exchange_rate : $defaultRoe);
                $raw = (float) $ptx->amount;
                $isCred = ($ptx->transaction_type === 'credit');
                $amtPkr = $raw;
                $amtSar = ($rate > 1) ? round($raw / $rate, 2) : 0.00;
                if ($isCred) {
                    $priorTxnCreditsPkr += $amtPkr;
                    $priorTxnCreditsSar += $amtSar;
                } else {
                    $priorTxnDebitsPkr += $amtPkr;
                    $priorTxnDebitsSar += $amtSar;
                }
            }

            $priorCreditsPkr = $priorHotelCreditsPkr + $priorFlightCreditsPkr + $priorVisaCreditsPkr + $priorTransportCreditsPkr + $priorTxnCreditsPkr;
            $priorCreditsSar = $priorHotelCreditsSar + $priorFlightCreditsSar + $priorVisaCreditsSar + $priorTransportCreditsSar + $priorTxnCreditsSar;

            $openingBalancePkr = $supplierInitialOpPkr + ($priorCreditsPkr - $priorTxnDebitsPkr);
            $openingBalanceSar = $supplierInitialOpSar + ($priorCreditsSar - $priorTxnDebitsSar);

            if ($openingBalancePkr != 0) {
                if (($openingBalancePkr > 0 && $openingBalanceSar < 0) || ($openingBalancePkr < 0 && $openingBalanceSar > 0) || $openingBalanceSar == 0) {
                    $openingBalanceSar = round($openingBalancePkr / $defaultRoe, 2);
                }
            } elseif ($openingBalancePkr == 0) {
                $openingBalanceSar = 0.00;
            }
        }

        // 6. Build Chronological Statement Lines for Debit / Credit / Running Balance View
        $statementRows = [];

        foreach ($hotelBookings as $h) {
            $hDate = $h->booking_date 
                ?: ($h->booking->booking_date 
                    ?? ($h->booking->created_at ? $h->booking->created_at->format('Y-m-d') : ($h->check_in ?: date('Y-m-d'))));
            $hRoe = (float) ($h->cost_roe > 0 ? $h->cost_roe : ($h->roe > 0 ? $h->roe : $defaultRoe));
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
                'exchange_rate'  => $hRoe,
                'booking_id'     => $h->booking_id,
                'proof'          => null,
            ];
        }

        foreach ($flightBookings as $fb) {
            $fDate = $fb->flight_booking_date 
                ?: ($fb->booking_date 
                    ?? ($fb->created_at ? $fb->created_at->format('Y-m-d') : ($fb->departure_date ?: date('Y-m-d'))));
            $fRoe = (float) ($fb->flight_cost_roe > 0 ? $fb->flight_cost_roe : ($fb->flight_roe > 0 ? $fb->flight_roe : $defaultRoe));
            $statementRows[] = [
                'date'           => $fDate,
                'voucher_no'     => 'BK-' . $fb->id,
                'ref_no'         => $fb->voucher_number ?? ('#BK-' . $fb->id),
                'type_label'     => 'Flight Booking',
                'badge_class'    => 'bg-soft-info text-info border border-info border-opacity-25',
                'contra_account' => 'Flight Cost Payable',
                'description'    => $fb->supplier_description ?? ('Flight: ' . ($fb->departure_airline ?: 'Airline') . ' ' . ($fb->departure_flight ?: '') . ' (' . ($fb->supplier_pax_count ?? ($fb->no_of_pax ?: 1)) . ' Pax) - ' . ($fb->client->name ?? ($fb->company->name ?? 'Guest'))),
                'payment_method' => 'Credit Booking',
                'debit_pkr'      => 0,
                'credit_pkr'     => (float) $fb->cost_pkr,
                'debit_sar'      => 0,
                'credit_sar'     => (float) $fb->cost_sar,
                'exchange_rate'  => $fRoe,
                'booking_id'     => $fb->id,
                'proof'          => null,
            ];
        }

        foreach ($visaBookings as $vb) {
            $vDate = $vb->booking_date 
                ?: ($vb->booking->booking_date 
                    ?? ($vb->booking->created_at ? $vb->booking->created_at->format('Y-m-d') : ($vb->created_at ? $vb->created_at->format('Y-m-d') : date('Y-m-d'))));
            $vRoe = (float) ($vb->cost_roe > 0 ? $vb->cost_roe : ($vb->roe > 0 ? $vb->roe : $defaultRoe));
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
                'exchange_rate'  => $vRoe,
                'booking_id'     => $vb->booking_id,
                'proof'          => null,
            ];
        }

        foreach ($transportBookings as $tb) {
            $tDate = $tb->booking_date 
                ?: ($tb->booking->booking_date 
                    ?? ($tb->booking->created_at ? $tb->booking->created_at->format('Y-m-d') : ($tb->created_at ? $tb->created_at->format('Y-m-d') : date('Y-m-d'))));
            $tRoe = (float) ($tb->cost_roe > 0 ? $tb->cost_roe : ($tb->roe > 0 ? $tb->roe : $defaultRoe));
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
                'exchange_rate'  => $tRoe,
                'booking_id'     => $tb->booking_id,
                'proof'          => null,
            ];
        }

        foreach ($transactions as $t) {
            $tDate = $t->date ? \Carbon\Carbon::parse($t->date)->format('Y-m-d') : ($t->created_at ? $t->created_at->format('Y-m-d') : date('Y-m-d'));
            $isJv = ($t->payee_type === 'jv');
            $isCredit = ($t->transaction_type === 'credit');

            $contra = 'Cash in Hand';
            if ($isJv) {
                if ($t->client) {
                    $contra = 'Client: ' . $t->client->name;
                } elseif (!empty($t->reference_no)) {
                    $siblingCl = ExpenseTransaction::where('payee_type', 'jv')
                        ->where('reference_no', $t->reference_no)
                        ->whereNotNull('client_id')
                        ->with('client')
                        ->first();
                    if ($siblingCl && $siblingCl->client) {
                        $contra = 'Client: ' . $siblingCl->client->name;
                    } else {
                        $contra = 'Journal Voucher Adjustment';
                    }
                } else {
                    $contra = 'Journal Voucher Adjustment';
                }
            } elseif ($t->bank) {
                $contra = $t->bank->name . ($t->bank->account_name ? ' (' . $t->bank->account_name . ')' : '');
            }

            $tRate = (float) ($t->exchange_rate > 0 ? $t->exchange_rate : $defaultRoe);
            $typeLabel = $isJv ? ($isCredit ? 'JV Credit Entry' : 'JV Adjustment') : ($isCredit ? 'Supplier Credit' : 'Payment Paid');
            $badgeClass = $isJv 
                ? 'bg-soft-primary text-primary border border-primary border-opacity-25' 
                : ($isCredit ? 'bg-soft-success text-success border border-success border-opacity-25' : 'bg-soft-danger text-danger border border-danger border-opacity-25');

            $statementRows[] = [
                'date'           => $tDate,
                'voucher_no'     => $t->reference_no ?: ('EXP-' . $t->id),
                'ref_no'         => $t->reference_no ?: ('#EXP-' . $t->id),
                'type_label'     => $typeLabel,
                'badge_class'    => $badgeClass,
                'contra_account' => $contra,
                'description'    => ($isJv ? 'JV: ' : 'Disbursed / Paid: ') . ($t->description ?: ucwords(str_replace('_', ' ', $t->payment_type))),
                'payment_method' => $isJv ? 'Journal Voucher' : ucwords(str_replace('_', ' ', $t->payment_type ?: 'Cash')),
                'debit_pkr'      => $isCredit ? 0 : (float) $t->amount_pkr,
                'credit_pkr'     => $isCredit ? (float) $t->amount_pkr : 0,
                'debit_sar'      => $isCredit ? 0 : (float) $t->amount_sar,
                'credit_sar'     => $isCredit ? (float) $t->amount_sar : 0,
                'exchange_rate'  => $tRate,
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

            $rowRoe = (!empty($sRow['exchange_rate']) && (float)$sRow['exchange_rate'] > 1) 
                ? (float)$sRow['exchange_rate'] 
                : $defaultRoe;

            // Synchronize SAR running balance with PKR running balance:
            // A supplier cannot be owed PKR (Cr) while having an advance in SAR (Dr)
            if ($runningPkr != 0) {
                if (($runningPkr > 0 && $runningSar < 0) || ($runningPkr < 0 && $runningSar > 0) || $runningSar == 0) {
                    $runningSar = round($runningPkr / $rowRoe, 2);
                }
            } elseif ($runningPkr == 0) {
                $runningSar = 0.00;
            }

            $sRow['running_balance_pkr'] = $runningPkr;
            $sRow['running_balance_sar'] = $runningSar;
            $sRow['balance_type'] = $runningPkr >= 0 ? 'Cr' : 'Dr';
            $sRow['balance_type_sar'] = $runningSar >= 0 ? 'Cr' : 'Dr';
        }
        unset($sRow);

        $statementLines = collect($statementRows);

        $balanceDuePkr = $runningPkr;
        $balanceDueSar = $runningSar;

        $totalPayablePkr = ($openingBalancePkr > 0 ? $openingBalancePkr : 0) + $periodCreditsPkr;
        $totalPayableSar = ($openingBalanceSar > 0 ? $openingBalanceSar : 0) + $periodCreditsSar;
        $totalPaidPkr    = ($openingBalancePkr < 0 ? abs($openingBalancePkr) : 0) + $periodDebitsPkr;
        $totalPaidSar    = ($openingBalanceSar < 0 ? abs($openingBalanceSar) : 0) + $periodDebitsSar;

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
