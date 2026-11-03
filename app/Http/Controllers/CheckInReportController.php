<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTransport;
use App\Models\BookingVisa;
use App\Models\BookingPerson;
use App\Models\Hotel;
use App\Models\Supplier;
use App\Models\ExpenseTransaction;
use Illuminate\Http\Request;

class CheckInReportController extends Controller
{
    public function index(Request $request)
    {
        $hotelName      = $request->get('hotel_name');
        $city           = $request->get('city');
        $activeDashboardPkg = session('dashboard_package', 'hajj');
        $defaultPackageType = in_array($activeDashboardPkg, ['hajj', 'umrah']) ? $activeDashboardPkg : null;
        $packageType    = $request->has('package_type') ? $request->get('package_type') : $defaultPackageType;
        $checkInFrom    = $request->get('check_in_from');
        $checkInTo      = $request->get('check_in_to');
        $search         = $request->get('search');
        $status         = $request->get('status');
        $currencyFilter = $request->get('currency_filter', 'all'); // 'all', 'pkr', 'sar'
        $viewMode       = $request->get('view_mode', 'full'); // 'full' (Cost + Sale), 'cost_only' (Without Sale), 'sale_only' (Without Cost)
        $activeTab      = $request->get('tab', 'all'); // 'all', 'hotels', 'flights', 'transports', 'visas'

        $hotels = Hotel::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();

        // 1. Fetch filtered bookings with all relations
        $bookingQuery = Booking::with([
            'client',
            'company',
            'persons',
            'hotels.supplier',
            'transports.supplier',
            'visas.supplier',
            'flightSupplier'
        ])
        ->whereNull('deleted_at')
        ->whereNotIn('status', ['cancelled', 'rejected']);

        if ($packageType === 'hajj') {
            $bookingQuery->where(function ($q) {
                $q->where('package_type', 'hajj')
                  ->orWhereNull('package_type')
                  ->orWhere('package_type', '');
            })->where(function ($q) {
                $q->whereNull('package_type')
                  ->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
            });
        } elseif ($packageType === 'umrah') {
            $bookingQuery->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
        } elseif ($packageType && $packageType !== 'all') {
            $bookingQuery->where('package_type', $packageType);
        }

        if ($status) {
            $bookingQuery->where('status', $status);
        }

        if ($hotelName) {
            $bookingQuery->whereHas('hotels', function ($hq) use ($hotelName) {
                $hq->where('hotel_name', 'like', "%{$hotelName}%");
            });
        }

        if ($city) {
            $bookingQuery->whereHas('hotels', function ($hq) use ($city) {
                $hq->where('location', 'like', "%{$city}%");
            });
        }

        if ($checkInFrom) {
            $cFrom = date('Y-m-d', strtotime($checkInFrom));
            $bookingQuery->where(function ($q) use ($cFrom) {
                $q->whereHas('hotels', function ($hq) use ($cFrom) {
                    $hq->whereDate('check_in', '>=', $cFrom);
                })->orWhereDate('departure_date', '>=', $cFrom);
            });
        }

        if ($checkInTo) {
            $cTo = date('Y-m-d', strtotime($checkInTo));
            $bookingQuery->where(function ($q) use ($cTo) {
                $q->whereHas('hotels', function ($hq) use ($cTo) {
                    $hq->whereDate('check_in', '<=', $cTo);
                })->orWhereDate('departure_date', '<=', $cTo);
            });
        }

        if ($search) {
            $sTerm = '%' . $search . '%';
            $bookingQuery->where(function ($bq) use ($sTerm) {
                $bq->where('voucher_number', 'LIKE', $sTerm)
                   ->orWhere('passport_number', 'LIKE', $sTerm)
                   ->orWhere('haji_id', 'LIKE', $sTerm)
                   ->orWhere('room_number', 'LIKE', $sTerm)
                   ->orWhere('departure_pnr', 'LIKE', $sTerm)
                   ->orWhere('departure_flight', 'LIKE', $sTerm)
                   ->orWhere('departure_airline', 'LIKE', $sTerm)
                   ->orWhereHas('client', function ($cq) use ($sTerm) {
                       $cq->where('name', 'LIKE', $sTerm)
                          ->orWhere('passport_number', 'LIKE', $sTerm)
                          ->orWhere('phone', 'LIKE', $sTerm);
                   })
                   ->orWhereHas('persons', function ($pq) use ($sTerm) {
                       $pq->where('full_name', 'LIKE', $sTerm)
                          ->orWhere('passport_number', 'LIKE', $sTerm)
                          ->orWhere('haji_id', 'LIKE', $sTerm)
                          ->orWhere('hb_number', 'LIKE', $sTerm)
                          ->orWhere('room_number', 'LIKE', $sTerm);
                   })
                   ->orWhereHas('hotels', function ($hq) use ($sTerm) {
                       $hq->where('hotel_name', 'LIKE', $sTerm)
                          ->orWhere('room_number', 'LIKE', $sTerm)
                          ->orWhere('room_type', 'LIKE', $sTerm);
                   })
                   ->orWhereHas('transports', function ($tq) use ($sTerm) {
                       $tq->where('route', 'LIKE', $sTerm)
                          ->orWhere('transport_type', 'LIKE', $sTerm);
                   });
            });
        }

        $allBookings = $bookingQuery->latest()->get();

        // 1.b Query ExpenseTransactions for suppliers (payments made to suppliers)
        $supplierTxnQuery = ExpenseTransaction::whereNull('deleted_at')
            ->whereNotNull('supplier_id');

        if ($packageType === 'hajj') {
            $supplierTxnQuery->where(function ($q) {
                $q->where('hajj_umrah', 'hajj')
                  ->orWhereNull('hajj_umrah')
                  ->orWhere('hajj_umrah', '');
            });
        } elseif ($packageType === 'umrah') {
            $supplierTxnQuery->where(function ($q) {
                $q->where('hajj_umrah', 'umrah')
                  ->orWhereNull('hajj_umrah')
                  ->orWhere('hajj_umrah', '');
            });
        }

        $supplierTxns = $supplierTxnQuery->get();

        $supplierPaidMapPKR = [];
        $supplierPaidMapSAR = [];

        foreach ($supplierTxns as $st) {
            $sId = (int) $st->supplier_id;
            $cur = strtoupper($st->currency ?: 'PKR');
            $rate = (float) ($st->exchange_rate > 0 ? $st->exchange_rate : 75.0);
            $amt = (float) $st->amount;

            $sign = ($st->transaction_type === 'credit') ? -1 : 1;
            $pkrAmt = $amt * $sign;

            if ($cur === 'SAR') {
                $sarAmt = $pkrAmt;
                $pkrAmt = ($rate > 1) ? round($pkrAmt * $rate, 2) : $pkrAmt;
            } else {
                $sarAmt = ($rate > 1) ? round($pkrAmt / $rate, 2) : $pkrAmt;
            }

            $supplierPaidMapPKR[$sId] = ($supplierPaidMapPKR[$sId] ?? 0) + $pkrAmt;
            $supplierPaidMapSAR[$sId] = ($supplierPaidMapSAR[$sId] ?? 0) + $sarAmt;
        }

        // Pools for FIFO allocation per service
        $hotelPoolPKR     = $supplierPaidMapPKR;
        $flightPoolPKR    = $supplierPaidMapPKR;
        $transportPoolPKR = $supplierPaidMapPKR;
        $visaPoolPKR      = $supplierPaidMapPKR;

        // 2. Structured collections for each individual service card & combined report
        $records          = collect(); // Master Combined Record
        $hotelRecords     = collect(); // Dedicated Hotels
        $flightRecords    = collect(); // Dedicated Flights
        $transportRecords = collect(); // Dedicated Transports
        $visaRecords      = collect(); // Dedicated Visas

        $totalRoomsInReport = 0;
        $seenFlightBookings = [];

        foreach ($allBookings as $b) {
            $clientName = $b->client->name ?? ($b->company->company_name ?? ($b->company->name ?? 'Guest'));
            $voucherNo  = $b->voucher_number ?: ('#BK-' . $b->id);
            $hajiId     = $b->haji_id ?: '-';
            $paxCount   = max(1, (int) ($b->no_of_pax ?: 1));

            // Default ROE helper
            $defRoe = 75.0;

            // ─────────────────────────────────────────────────────────────
            // A. PROCESS HOTELS
            // ─────────────────────────────────────────────────────────────
            $bHotelCostsPKR = 0;
            $bHotelSalesPKR = 0;
            $bHotelCostsSAR = 0;
            $bHotelSalesSAR = 0;
            $bHotelPaidPKR  = 0;

            if ($b->hotels && $b->hotels->count() > 0) {
                foreach ($b->hotels as $hIdx => $bh) {
                    $rooms = max(1, intval($bh->no_of_rooms ?: 1));
                    $totalRoomsInReport += $rooms;
                    $hRoe = (float) ($bh->roe ?: 1);
                    $hCostPKR = (float) ($bh->cost_amount ?: (($bh->cost_per_night ?: 0) * ($bh->no_of_nights ?: 1) * $rooms * $hRoe));
                    $hSalePKR = (float) ($bh->selling_amount ?: (($bh->selling_per_night ?: 0) * ($bh->no_of_nights ?: 1) * $rooms * $hRoe));
                    $hCostSAR = $hRoe > 0 ? ($hCostPKR / $hRoe) : 0;
                    $hSaleSAR = $hRoe > 0 ? ($hSalePKR / $hRoe) : 0;

                    $hSupplierId = (int) ($bh->supplier_id ?: 0);
                    $hPaidPKR = 0;
                    if ($hSupplierId > 0 && isset($hotelPoolPKR[$hSupplierId])) {
                        $hPaidPKR = min($hCostPKR, max(0, $hotelPoolPKR[$hSupplierId]));
                        $hotelPoolPKR[$hSupplierId] -= $hPaidPKR;
                    }
                    $hBalPKR  = max(0, $hCostPKR - $hPaidPKR);
                    $hPaidSAR = $hRoe > 0 ? round($hPaidPKR / $hRoe, 2) : 0;
                    $hBalSAR  = max(0, $hCostSAR - $hPaidSAR);

                    $bHotelCostsPKR += $hCostPKR;
                    $bHotelSalesPKR += $hSalePKR;
                    $bHotelCostsSAR += $hCostSAR;
                    $bHotelSalesSAR += $hSaleSAR;
                    $bHotelPaidPKR  += $hPaidPKR;

                    $hotelRecords->push((object)[
                        'booking_id'     => $b->id,
                        'reference_no'   => $voucherNo,
                        'package_type'   => ucfirst($b->package_type ?? 'Umrah'),
                        'client_name'    => $clientName,
                        'haji_id'        => $hajiId,
                        'passport'       => $b->passport_number ?: ($b->client->passport_number ?? '-'),
                        'phone'          => $b->phone ?: ($b->client->phone ?? '-'),
                        'hotel_name'     => $bh->hotel_name,
                        'city'           => ucfirst($bh->location ?: 'Makkah'),
                        'room_type'      => ucfirst($bh->room_type ?: 'Double'),
                        'room_number'    => $bh->room_number ?: $b->room_number ?: '-',
                        'no_of_rooms'    => $rooms,
                        'no_of_nights'   => $bh->no_of_nights ?: 1,
                        'check_in'       => $bh->check_in ? date('Y-m-d', strtotime($bh->check_in)) : null,
                        'check_out'      => $bh->check_out ? date('Y-m-d', strtotime($bh->check_out)) : null,
                        'option_date'    => $bh->option_date ? date('Y-m-d', strtotime($bh->option_date)) : null,
                        'supplier_id'    => $hSupplierId,
                        'supplier_name'  => $bh->supplier->name ?? '-',
                        'roe'            => $hRoe,
                        'cost_pkr'       => $hCostPKR,
                        'paid_pkr'       => $hPaidPKR,
                        'balance_pkr'    => $hBalPKR,
                        'sale_pkr'       => $hSalePKR,
                        'margin_pkr'     => $hSalePKR - $hCostPKR,
                        'cost_sar'       => $hCostSAR,
                        'paid_sar'       => $hPaidSAR,
                        'balance_sar'    => $hBalSAR,
                        'sale_sar'       => $hSaleSAR,
                        'margin_sar'     => $hSaleSAR - $hCostSAR,
                        'status'         => $b->status ?: 'Confirmed',
                    ]);
                }
            }

            // ─────────────────────────────────────────────────────────────
            // B. PROCESS FLIGHT
            // ─────────────────────────────────────────────────────────────
            $hasFlight = !empty($b->departure_flight) || !empty($b->departure_pnr) || !empty($b->departure_airline) || ($b->flight_cost > 0) || ($b->flight_charges > 0);
            $fRoe = (float) ($b->flight_roe ?: 1);
            $fCostPKR = (float) ($b->flight_cost ?: (($b->flight_cost_rate ?: 0) * $fRoe));
            $fSalePKR = (float) ($b->flight_charges ?: (($b->flight_selling_rate ?: 0) * $fRoe));
            $fCostSAR = $fRoe > 0 ? ($fCostPKR / $fRoe) : 0;
            $fSaleSAR = $fRoe > 0 ? ($fSalePKR / $fRoe) : 0;

            $fSupplierId = (int) ($b->flight_supplier_id ?: ($b->flightSupplier->id ?? 0));
            $fPaidPKR = 0;
            if ($fSupplierId > 0 && isset($flightPoolPKR[$fSupplierId])) {
                $fPaidPKR = min($fCostPKR, max(0, $flightPoolPKR[$fSupplierId]));
                $flightPoolPKR[$fSupplierId] -= $fPaidPKR;
            }
            $fBalPKR  = max(0, $fCostPKR - $fPaidPKR);
            $fPaidSAR = $fRoe > 0 ? round($fPaidPKR / $fRoe, 2) : 0;
            $fBalSAR  = max(0, $fCostSAR - $fPaidSAR);

            if ($hasFlight) {
                $flightRecords->push((object)[
                    'booking_id'        => $b->id,
                    'reference_no'      => $voucherNo,
                    'package_type'      => ucfirst($b->package_type ?? 'Umrah'),
                    'client_name'       => $clientName,
                    'haji_id'           => $hajiId,
                    'passport'          => $b->passport_number ?: ($b->client->passport_number ?? '-'),
                    'phone'             => $b->phone ?: ($b->client->phone ?? '-'),
                    'pax'               => $paxCount,
                    'departure_flight'  => $b->departure_flight ?: '-',
                    'departure_airline' => $b->departure_airline ?: '-',
                    'departure_pnr'     => $b->departure_pnr ?: '-',
                    'departure_date'    => $b->departure_date ? date('Y-m-d', strtotime($b->departure_date)) : null,
                    'arrival_date'      => $b->arrival_date ? date('Y-m-d', strtotime($b->arrival_date)) : null,
                    'supplier_id'       => $fSupplierId,
                    'supplier_name'     => $b->flightSupplier->name ?? '-',
                    'roe'               => $fRoe,
                    'cost_pkr'          => $fCostPKR,
                    'paid_pkr'          => $fPaidPKR,
                    'balance_pkr'       => $fBalPKR,
                    'sale_pkr'          => $fSalePKR,
                    'margin_pkr'        => $fSalePKR - $fCostPKR,
                    'cost_sar'          => $fCostSAR,
                    'paid_sar'          => $fPaidSAR,
                    'balance_sar'       => $fBalSAR,
                    'sale_sar'          => $fSaleSAR,
                    'margin_sar'        => $fSaleSAR - $fCostSAR,
                    'status'            => $b->status ?: 'Confirmed',
                ]);
            }

            // ─────────────────────────────────────────────────────────────
            // C. PROCESS TRANSPORTS
            // ─────────────────────────────────────────────────────────────
            $bTransportCostsPKR = 0;
            $bTransportSalesPKR = 0;
            $bTransportCostsSAR = 0;
            $bTransportSalesSAR = 0;
            $bTransportPaidPKR  = 0;
            $transportRoutesList = [];

            if ($b->transports && $b->transports->count() > 0) {
                foreach ($b->transports as $t) {
                    $tRoe = (float) ($t->roe ?: 1);
                    $tCostPKR = (float) ($t->cost_amount ?: (($t->cost_rate ?: 0) * $tRoe));
                    $tSalePKR = (float) ($t->selling_amount ?: (($t->selling_rate ?: 0) * $tRoe));
                    $tCostSAR = $tRoe > 0 ? ($tCostPKR / $tRoe) : 0;
                    $tSaleSAR = $tRoe > 0 ? ($tSalePKR / $tRoe) : 0;

                    $tSupplierId = (int) ($t->supplier_id ?: 0);
                    $tPaidPKR = 0;
                    if ($tSupplierId > 0 && isset($transportPoolPKR[$tSupplierId])) {
                        $tPaidPKR = min($tCostPKR, max(0, $transportPoolPKR[$tSupplierId]));
                        $transportPoolPKR[$tSupplierId] -= $tPaidPKR;
                    }
                    $tBalPKR  = max(0, $tCostPKR - $tPaidPKR);
                    $tPaidSAR = $tRoe > 0 ? round($tPaidPKR / $tRoe, 2) : 0;
                    $tBalSAR  = max(0, $tCostSAR - $tPaidSAR);

                    $bTransportCostsPKR += $tCostPKR;
                    $bTransportSalesPKR += $tSalePKR;
                    $bTransportCostsSAR += $tCostSAR;
                    $bTransportSalesSAR += $tSaleSAR;
                    $bTransportPaidPKR  += $tPaidPKR;

                    $rName = $t->route ?: 'Standard Route';
                    $vType = $t->transport_type ? ucfirst(str_replace('_', ' ', $t->transport_type)) : '-';
                    $transportRoutesList[] = "{$rName} ({$vType})";

                    $transportRecords->push((object)[
                        'booking_id'     => $b->id,
                        'reference_no'   => $voucherNo,
                        'package_type'   => ucfirst($b->package_type ?? 'Umrah'),
                        'client_name'    => $clientName,
                        'haji_id'        => $hajiId,
                        'passport'       => $b->passport_number ?: ($b->client->passport_number ?? '-'),
                        'phone'          => $b->phone ?: ($b->client->phone ?? '-'),
                        'pax'            => $paxCount,
                        'route'          => $rName,
                        'vehicle_type'   => $vType,
                        'supplier_id'    => $tSupplierId,
                        'supplier_name'  => $t->supplier->name ?? '-',
                        'notes'          => $t->notes,
                        'roe'            => $tRoe,
                        'cost_pkr'       => $tCostPKR,
                        'paid_pkr'       => $tPaidPKR,
                        'balance_pkr'    => $tBalPKR,
                        'sale_pkr'       => $tSalePKR,
                        'margin_pkr'     => $tSalePKR - $tCostPKR,
                        'cost_sar'       => $tCostSAR,
                        'paid_sar'       => $tPaidSAR,
                        'balance_sar'    => $tBalSAR,
                        'sale_sar'       => $tSaleSAR,
                        'margin_sar'     => $tSaleSAR - $tCostSAR,
                        'status'         => $b->status ?: 'Confirmed',
                    ]);
                }
            }

            // ─────────────────────────────────────────────────────────────
            // D. PROCESS VISAS
            // ─────────────────────────────────────────────────────────────
            $bVisaCostsPKR = 0;
            $bVisaSalesPKR = 0;
            $bVisaCostsSAR = 0;
            $bVisaSalesSAR = 0;
            $bVisaPaidPKR  = 0;

            if ($b->visas && $b->visas->count() > 0) {
                foreach ($b->visas as $v) {
                    $vRoe = (float) ($v->roe ?: 1);
                    $vCostPKR = (float) ($v->cost_amount ?: (($v->cost_rate ?: 0) * $vRoe));
                    $vSalePKR = (float) ($v->selling_amount ?: (($v->selling_rate ?: 0) * $vRoe));
                    $vCostSAR = $vRoe > 0 ? ($vCostPKR / $vRoe) : 0;
                    $vSaleSAR = $vRoe > 0 ? ($vSalePKR / $vRoe) : 0;

                    $vSupplierId = (int) ($v->supplier_id ?: 0);
                    $vPaidPKR = 0;
                    if ($vSupplierId > 0 && isset($visaPoolPKR[$vSupplierId])) {
                        $vPaidPKR = min($vCostPKR, max(0, $visaPoolPKR[$vSupplierId]));
                        $visaPoolPKR[$vSupplierId] -= $vPaidPKR;
                    }
                    $vBalPKR  = max(0, $vCostPKR - $vPaidPKR);
                    $vPaidSAR = $vRoe > 0 ? round($vPaidPKR / $vRoe, 2) : 0;
                    $vBalSAR  = max(0, $vCostSAR - $vPaidSAR);

                    $bVisaCostsPKR += $vCostPKR;
                    $bVisaSalesPKR += $vSalePKR;
                    $bVisaCostsSAR += $vCostSAR;
                    $bVisaSalesSAR += $vSaleSAR;
                    $bVisaPaidPKR  += $vPaidPKR;

                    $vPaxName = $v->given_name ?: $clientName;

                    $visaRecords->push((object)[
                        'booking_id'     => $b->id,
                        'reference_no'   => $voucherNo,
                        'package_type'   => ucfirst($b->package_type ?? 'Umrah'),
                        'client_name'    => $vPaxName,
                        'haji_id'        => $hajiId,
                        'passport'       => $v->passport_number ?: ($b->passport_number ?? '-'),
                        'phone'          => $b->phone ?: ($b->client->phone ?? '-'),
                        'send_to'        => $v->send_to ?: 'Embassy',
                        'supplier_id'    => $vSupplierId,
                        'supplier_name'  => $v->supplier->name ?? '-',
                        'visa_status'    => ucfirst($v->status ?: 'Approved'),
                        'roe'            => $vRoe,
                        'cost_pkr'       => $vCostPKR,
                        'paid_pkr'       => $vPaidPKR,
                        'balance_pkr'    => $vBalPKR,
                        'sale_pkr'       => $vSalePKR,
                        'margin_pkr'     => $vSalePKR - $vCostPKR,
                        'cost_sar'       => $vCostSAR,
                        'paid_sar'       => $vPaidSAR,
                        'balance_sar'    => $vBalSAR,
                        'sale_sar'       => $vSaleSAR,
                        'margin_sar'     => $vSaleSAR - $vCostSAR,
                        'status'         => $b->status ?: 'Confirmed',
                    ]);
                }
            } else {
                $bVisaSalesPKR = (float) ($b->visa_charges ?: 0);
            }

            // ─────────────────────────────────────────────────────────────
            // E. COMBINED MASTER GUEST RECORDS
            // ─────────────────────────────────────────────────────────────
            $pkgCost = (float) (($b->package_cost ?: 0) * $paxCount);
            $otherCost = (float) ($b->other_charges ?: 0);
            $additionalAmount = (float) ($b->additional_services_amount ?: 0);
            $discount = (float) ($b->discount ?: 0);

            $bTotalCostPKR = $bHotelCostsPKR + $fCostPKR + $bTransportCostsPKR + $bVisaCostsPKR + $pkgCost + $otherCost;
            $bTotalSalePKR = (float) ($b->total_amount ?: ($bHotelSalesPKR + $fSalePKR + $bTransportSalesPKR + $bVisaSalesPKR + $pkgCost + $otherCost + $additionalAmount - $discount));
            $bMarginPKR    = $bTotalSalePKR - $bTotalCostPKR;

            $bTotalPaidPKR = $bHotelPaidPKR + $fPaidPKR + $bTransportPaidPKR + $bVisaPaidPKR;
            $bTotalBalPKR  = max(0, $bTotalCostPKR - $bTotalPaidPKR);

            $effRoe = $b->hotels->first()?->roe ?: ($fRoe > 1 ? $fRoe : $defRoe);
            $bTotalCostSAR = $effRoe > 0 ? ($bTotalCostPKR / $effRoe) : 0;
            $bTotalSaleSAR = $effRoe > 0 ? ($bTotalSalePKR / $effRoe) : 0;
            $bMarginSAR    = $effRoe > 0 ? ($bMarginPKR / $effRoe) : 0;
            $bTotalPaidSAR = $effRoe > 0 ? round($bTotalPaidPKR / $effRoe, 2) : 0;
            $bTotalBalSAR  = max(0, $bTotalCostSAR - $bTotalPaidSAR);

            $transportsSummary = implode(', ', $transportRoutesList);

            // If booking has hotels, create a combined row for each hotel entry
            if ($b->hotels && $b->hotels->count() > 0) {
                foreach ($b->hotels as $bhIdx => $bh) {
                    $hRoe = (float) ($bh->roe ?: 1);
                    $hCost = (float) ($bh->cost_amount ?: (($bh->cost_per_night ?: 0) * ($bh->no_of_nights ?: 1) * max(1, (int)$bh->no_of_rooms) * $hRoe));
                    $hSale = (float) ($bh->selling_amount ?: (($bh->selling_per_night ?: 0) * ($bh->no_of_nights ?: 1) * max(1, (int)$bh->no_of_rooms) * $hRoe));

                    $rowPaidPKR = ($bhIdx === 0 ? $bTotalPaidPKR : 0);
                    $rowBalPKR  = max(0, ($bhIdx === 0 ? $bTotalCostPKR : $hCost) - $rowPaidPKR);
                    $rowPaidSAR = $hRoe > 0 ? round($rowPaidPKR / $hRoe, 2) : 0;
                    $rowBalSAR  = max(0, ($bhIdx === 0 ? $bTotalCostSAR : ($hRoe > 0 ? $hCost / $hRoe : 0)) - $rowPaidSAR);

                    $records->push((object)[
                        'booking_id'           => $b->id,
                        'booking_date'         => $b->booking_date ? date('Y-m-d', strtotime($b->booking_date)) : ($b->created_at ? $b->created_at->format('Y-m-d') : null),
                        'reference_no'         => $voucherNo,
                        'package_type'         => ucfirst($b->package_type ?? 'Umrah'),
                        'guest_name'           => $clientName,
                        'haji_id'              => $hajiId,
                        'room_number'          => $bh->room_number ?: $b->room_number ?: '-',
                        'passport_number'      => $b->passport_number ?: ($b->client->passport_number ?? '-'),
                        'phone'                => $b->phone ?: ($b->client->phone ?? '-'),
                        'hotel_name'           => $bh->hotel_name,
                        'city'                 => ucfirst($bh->location ?: 'Makkah'),
                        'room_type'            => ucfirst($bh->room_type ?: 'Double'),
                        'no_of_rooms'          => $bh->no_of_rooms ?: 1,
                        'check_in'             => $bh->check_in ? date('Y-m-d', strtotime($bh->check_in)) : null,
                        'check_out'            => $bh->check_out ? date('Y-m-d', strtotime($bh->check_out)) : null,
                        'option_date'          => $bh->option_date ? date('Y-m-d', strtotime($bh->option_date)) : null,
                        'nights'               => $bh->no_of_nights ?: 0,
                        'hotel_supplier'       => $bh->supplier->name ?? '-',
                        'hotel_cost'           => $hCost,
                        'hotel_sale'           => $hSale,
                        // Flight
                        'departure_date'       => $b->departure_date ? date('Y-m-d', strtotime($b->departure_date)) : null,
                        'departure_flight'     => $b->departure_flight ?: '-',
                        'departure_airline'    => $b->departure_airline ?: '-',
                        'departure_pnr'        => $b->departure_pnr ?: '-',
                        'arrival_date'         => $b->arrival_date ? date('Y-m-d', strtotime($b->arrival_date)) : null,
                        'flight_supplier'      => $b->flightSupplier->name ?? '-',
                        'flight_cost'          => $bhIdx === 0 ? $fCostPKR : 0,
                        'flight_sale'          => $bhIdx === 0 ? $fSalePKR : 0,
                        // Transports
                        'transport_routes'     => $transportsSummary ?: '-',
                        'transport_cost'       => $bhIdx === 0 ? $bTransportCostsPKR : 0,
                        'transport_sale'       => $bhIdx === 0 ? $bTransportSalesPKR : 0,
                        // Visa
                        'visa_cost'            => $bhIdx === 0 ? $bVisaCostsPKR : 0,
                        'visa_sale'            => $bhIdx === 0 ? $bVisaSalesPKR : 0,
                        // Financials
                        'cost_pkr'             => $bhIdx === 0 ? $bTotalCostPKR : $hCost,
                        'paid_pkr'             => $rowPaidPKR,
                        'balance_pkr'          => $rowBalPKR,
                        'sale_pkr'             => $bhIdx === 0 ? $bTotalSalePKR : $hSale,
                        'margin_pkr'           => $bhIdx === 0 ? $bMarginPKR : ($hSale - $hCost),
                        'cost_sar'             => $bhIdx === 0 ? $bTotalCostSAR : ($hRoe > 0 ? $hCost / $hRoe : 0),
                        'paid_sar'             => $rowPaidSAR,
                        'balance_sar'          => $rowBalSAR,
                        'sale_sar'             => $bhIdx === 0 ? $bTotalSaleSAR : ($hRoe > 0 ? $hSale / $hRoe : 0),
                        'margin_sar'           => $bhIdx === 0 ? $bMarginSAR : ($hRoe > 0 ? ($hSale - $hCost) / $hRoe : 0),
                        'effective_roe'        => $hRoe,
                        'status'               => $b->status ?: 'Confirmed',
                    ]);
                }
            } else {
                // If booking has no hotels (e.g. flight only or visa only), still show in master record
                $records->push((object)[
                    'booking_id'           => $b->id,
                    'booking_date'         => $b->booking_date ? date('Y-m-d', strtotime($b->booking_date)) : ($b->created_at ? $b->created_at->format('Y-m-d') : null),
                    'reference_no'         => $voucherNo,
                    'package_type'         => ucfirst($b->package_type ?? 'Umrah'),
                    'guest_name'           => $clientName,
                    'haji_id'              => $hajiId,
                    'room_number'          => $b->room_number ?: '-',
                    'passport_number'      => $b->passport_number ?: ($b->client->passport_number ?? '-'),
                    'phone'                => $b->phone ?: ($b->client->phone ?? '-'),
                    'hotel_name'           => '—',
                    'city'                 => '—',
                    'room_type'            => '—',
                    'no_of_rooms'          => 0,
                    'check_in'             => null,
                    'check_out'            => null,
                    'nights'               => 0,
                    'hotel_supplier'       => '-',
                    'hotel_cost'           => 0,
                    'hotel_sale'           => 0,
                    // Flight
                    'departure_date'       => $b->departure_date ? date('Y-m-d', strtotime($b->departure_date)) : null,
                    'departure_flight'     => $b->departure_flight ?: '-',
                    'departure_airline'    => $b->departure_airline ?: '-',
                    'departure_pnr'        => $b->departure_pnr ?: '-',
                    'arrival_date'         => $b->arrival_date ? date('Y-m-d', strtotime($b->arrival_date)) : null,
                    'flight_supplier'      => $b->flightSupplier->name ?? '-',
                    'flight_cost'          => $fCostPKR,
                    'flight_sale'          => $fSalePKR,
                    // Transports
                    'transport_routes'     => $transportsSummary ?: '-',
                    'transport_cost'       => $bTransportCostsPKR,
                    'transport_sale'       => $bTransportSalesPKR,
                    // Visa
                    'visa_cost'            => $bVisaCostsPKR,
                    'visa_sale'            => $bVisaSalesPKR,
                    // Financials
                    'cost_pkr'             => $bTotalCostPKR,
                    'paid_pkr'             => $bTotalPaidPKR,
                    'balance_pkr'          => $bTotalBalPKR,
                    'sale_pkr'             => $bTotalSalePKR,
                    'margin_pkr'           => $bMarginPKR,
                    'cost_sar'             => $bTotalCostSAR,
                    'paid_sar'             => $bTotalPaidSAR,
                    'balance_sar'          => $bTotalBalSAR,
                    'sale_sar'             => $bTotalSaleSAR,
                    'margin_sar'           => $bMarginSAR,
                    'effective_roe'        => $fRoe > 1 ? $fRoe : $defRoe,
                    'status'               => $b->status ?: 'Confirmed',
                ]);
            }
        }

        // 3. Financial Totals & KPI Metrics
        $today = date('Y-m-d');
        $totalCheckIns    = $hotelRecords->count();
        $totalHujjaj      = $allBookings->where('package_type', 'hajj')->count();
        $totalUmrah       = $allBookings->where('package_type', 'umrah')->count();
        $todayCheckIns    = $hotelRecords->filter(fn($r) => $r->check_in === $today)->count();
        $upcomingCheckIns = $hotelRecords->filter(fn($r) => $r->check_in && $r->check_in >= $today)->count();

        // Hotel Section Totals
        $hotelCostTotalPKR    = $hotelRecords->sum('cost_pkr');
        $hotelSaleTotalPKR    = $hotelRecords->sum('sale_pkr');
        $hotelMarginTotalPKR  = $hotelRecords->sum('margin_pkr');
        $hotelPaidTotalPKR    = $hotelRecords->sum('paid_pkr');
        $hotelBalanceTotalPKR = max(0, $hotelCostTotalPKR - $hotelPaidTotalPKR);
        $hotelCostTotalSAR    = $hotelRecords->sum('cost_sar');
        $hotelSaleTotalSAR    = $hotelRecords->sum('sale_sar');
        $hotelMarginTotalSAR  = $hotelRecords->sum('margin_sar');
        $hotelPaidTotalSAR    = $hotelRecords->sum('paid_sar');
        $hotelBalanceTotalSAR = max(0, $hotelCostTotalSAR - $hotelPaidTotalSAR);

        // Flight Section Totals
        $flightCostTotalPKR    = $flightRecords->sum('cost_pkr');
        $flightSaleTotalPKR    = $flightRecords->sum('sale_pkr');
        $flightMarginTotalPKR  = $flightRecords->sum('margin_pkr');
        $flightPaidTotalPKR    = $flightRecords->sum('paid_pkr');
        $flightBalanceTotalPKR = max(0, $flightCostTotalPKR - $flightPaidTotalPKR);
        $flightCostTotalSAR    = $flightRecords->sum('cost_sar');
        $flightSaleTotalSAR    = $flightRecords->sum('sale_sar');
        $flightMarginTotalSAR  = $flightRecords->sum('margin_sar');
        $flightPaidTotalSAR    = $flightRecords->sum('paid_sar');
        $flightBalanceTotalSAR = max(0, $flightCostTotalSAR - $flightPaidTotalSAR);

        // Transport Section Totals
        $transportCostTotalPKR    = $transportRecords->sum('cost_pkr');
        $transportSaleTotalPKR    = $transportRecords->sum('sale_pkr');
        $transportMarginTotalPKR  = $transportRecords->sum('margin_pkr');
        $transportPaidTotalPKR    = $transportRecords->sum('paid_pkr');
        $transportBalanceTotalPKR = max(0, $transportCostTotalPKR - $transportPaidTotalPKR);
        $transportCostTotalSAR    = $transportRecords->sum('cost_sar');
        $transportSaleTotalSAR    = $transportRecords->sum('sale_sar');
        $transportMarginTotalSAR  = $transportRecords->sum('margin_sar');
        $transportPaidTotalSAR    = $transportRecords->sum('paid_sar');
        $transportBalanceTotalSAR = max(0, $transportCostTotalSAR - $transportPaidTotalSAR);

        // Visa Section Totals
        $visaCostTotalPKR    = $visaRecords->sum('cost_pkr');
        $visaSaleTotalPKR    = $visaRecords->sum('sale_pkr');
        $visaMarginTotalPKR  = $visaRecords->sum('margin_pkr');
        $visaPaidTotalPKR    = $visaRecords->sum('paid_pkr');
        $visaBalanceTotalPKR = max(0, $visaCostTotalPKR - $visaPaidTotalPKR);
        $visaCostTotalSAR    = $visaRecords->sum('cost_sar');
        $visaSaleTotalSAR    = $visaRecords->sum('sale_sar');
        $visaMarginTotalSAR  = $visaRecords->sum('margin_sar');
        $visaPaidTotalSAR    = $visaRecords->sum('paid_sar');
        $visaBalanceTotalSAR = max(0, $visaCostTotalSAR - $visaPaidTotalSAR);

        // Grand Totals (Unique bookings summation to prevent double-count across multiple hotel rows)
        $totalCostPKR    = $hotelCostTotalPKR + $flightCostTotalPKR + $transportCostTotalPKR + $visaCostTotalPKR;
        $totalSalePKR    = $hotelSaleTotalPKR + $flightSaleTotalPKR + $transportSaleTotalPKR + $visaSaleTotalPKR;
        $totalMarginPKR  = $totalSalePKR - $totalCostPKR;
        $totalPaidPKR    = $hotelPaidTotalPKR + $flightPaidTotalPKR + $transportPaidTotalPKR + $visaPaidTotalPKR;
        $totalBalancePKR = max(0, $totalCostPKR - $totalPaidPKR);

        $totalCostSAR    = $hotelCostTotalSAR + $flightCostTotalSAR + $transportCostTotalSAR + $visaCostTotalSAR;
        $totalSaleSAR    = $hotelSaleTotalSAR + $flightSaleTotalSAR + $transportSaleTotalSAR + $visaSaleTotalSAR;
        $totalMarginSAR  = $totalSaleSAR - $totalCostSAR;
        $totalPaidSAR    = $hotelPaidTotalSAR + $flightPaidTotalSAR + $transportPaidTotalSAR + $visaPaidTotalSAR;
        $totalBalanceSAR = max(0, $totalCostSAR - $totalPaidSAR);

        return view('reports.checkin_report', compact(
            'records',
            'hotelRecords',
            'flightRecords',
            'transportRecords',
            'visaRecords',
            'hotels',
            'totalCheckIns',
            'totalHujjaj',
            'totalUmrah',
            'totalRoomsInReport',
            'todayCheckIns',
            'upcomingCheckIns',
            'hotelCostTotalPKR',
            'hotelPaidTotalPKR',
            'hotelBalanceTotalPKR',
            'hotelSaleTotalPKR',
            'hotelMarginTotalPKR',
            'hotelCostTotalSAR',
            'hotelPaidTotalSAR',
            'hotelBalanceTotalSAR',
            'hotelSaleTotalSAR',
            'hotelMarginTotalSAR',
            'flightCostTotalPKR',
            'flightPaidTotalPKR',
            'flightBalanceTotalPKR',
            'flightSaleTotalPKR',
            'flightMarginTotalPKR',
            'flightCostTotalSAR',
            'flightPaidTotalSAR',
            'flightBalanceTotalSAR',
            'flightSaleTotalSAR',
            'flightMarginTotalSAR',
            'transportCostTotalPKR',
            'transportPaidTotalPKR',
            'transportBalanceTotalPKR',
            'transportSaleTotalPKR',
            'transportMarginTotalPKR',
            'transportCostTotalSAR',
            'transportPaidTotalSAR',
            'transportBalanceTotalSAR',
            'transportSaleTotalSAR',
            'transportMarginTotalSAR',
            'visaCostTotalPKR',
            'visaPaidTotalPKR',
            'visaBalanceTotalPKR',
            'visaSaleTotalPKR',
            'visaMarginTotalPKR',
            'visaCostTotalSAR',
            'visaPaidTotalSAR',
            'visaBalanceTotalSAR',
            'visaSaleTotalSAR',
            'visaMarginTotalSAR',
            'totalCostPKR',
            'totalPaidPKR',
            'totalBalancePKR',
            'totalSalePKR',
            'totalMarginPKR',
            'totalCostSAR',
            'totalPaidSAR',
            'totalBalanceSAR',
            'totalSaleSAR',
            'totalMarginSAR',
            'hotelName',
            'city',
            'packageType',
            'checkInFrom',
            'checkInTo',
            'search',
            'status',
            'currencyFilter',
            'viewMode',
            'activeTab'
        ));
    }
}
