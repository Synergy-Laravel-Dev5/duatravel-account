<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTransport;
use App\Models\BookingVisa;
use App\Models\BookingPerson;
use App\Models\Hotel;
use App\Models\Supplier;
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
        $checkInFrom    = $request->has('check_in_from') ? $request->get('check_in_from') : date('Y-m-d');
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

            if ($b->hotels && $b->hotels->count() > 0) {
                foreach ($b->hotels as $hIdx => $bh) {
                    $rooms = max(1, intval($bh->no_of_rooms ?: 1));
                    $totalRoomsInReport += $rooms;
                    $hRoe = (float) ($bh->roe ?: 1);
                    $hCostPKR = (float) ($bh->cost_amount ?: (($bh->cost_per_night ?: 0) * ($bh->no_of_nights ?: 1) * $rooms * $hRoe));
                    $hSalePKR = (float) ($bh->selling_amount ?: (($bh->selling_per_night ?: 0) * ($bh->no_of_nights ?: 1) * $rooms * $hRoe));
                    $hCostSAR = $hRoe > 0 ? ($hCostPKR / $hRoe) : 0;
                    $hSaleSAR = $hRoe > 0 ? ($hSalePKR / $hRoe) : 0;

                    $bHotelCostsPKR += $hCostPKR;
                    $bHotelSalesPKR += $hSalePKR;
                    $bHotelCostsSAR += $hCostSAR;
                    $bHotelSalesSAR += $hSaleSAR;

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
                        'supplier_name'  => $bh->supplier->name ?? '-',
                        'roe'            => $hRoe,
                        'cost_pkr'       => $hCostPKR,
                        'sale_pkr'       => $hSalePKR,
                        'margin_pkr'     => $hSalePKR - $hCostPKR,
                        'cost_sar'       => $hCostSAR,
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
                    'supplier_name'     => $b->flightSupplier->name ?? '-',
                    'roe'               => $fRoe,
                    'cost_pkr'          => $fCostPKR,
                    'sale_pkr'          => $fSalePKR,
                    'margin_pkr'        => $fSalePKR - $fCostPKR,
                    'cost_sar'          => $fCostSAR,
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
            $transportRoutesList = [];

            if ($b->transports && $b->transports->count() > 0) {
                foreach ($b->transports as $t) {
                    $tRoe = (float) ($t->roe ?: 1);
                    $tCostPKR = (float) ($t->cost_amount ?: (($t->cost_rate ?: 0) * $tRoe));
                    $tSalePKR = (float) ($t->selling_amount ?: (($t->selling_rate ?: 0) * $tRoe));
                    $tCostSAR = $tRoe > 0 ? ($tCostPKR / $tRoe) : 0;
                    $tSaleSAR = $tRoe > 0 ? ($tSalePKR / $tRoe) : 0;

                    $bTransportCostsPKR += $tCostPKR;
                    $bTransportSalesPKR += $tSalePKR;
                    $bTransportCostsSAR += $tCostSAR;
                    $bTransportSalesSAR += $tSaleSAR;

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
                        'supplier_name'  => $t->supplier->name ?? '-',
                        'notes'          => $t->notes,
                        'roe'            => $tRoe,
                        'cost_pkr'       => $tCostPKR,
                        'sale_pkr'       => $tSalePKR,
                        'margin_pkr'     => $tSalePKR - $tCostPKR,
                        'cost_sar'       => $tCostSAR,
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

            if ($b->visas && $b->visas->count() > 0) {
                foreach ($b->visas as $v) {
                    $vRoe = (float) ($v->roe ?: 1);
                    $vCostPKR = (float) ($v->cost_amount ?: (($v->cost_rate ?: 0) * $vRoe));
                    $vSalePKR = (float) ($v->selling_amount ?: (($v->selling_rate ?: 0) * $vRoe));
                    $vCostSAR = $vRoe > 0 ? ($vCostPKR / $vRoe) : 0;
                    $vSaleSAR = $vRoe > 0 ? ($vSalePKR / $vRoe) : 0;

                    $bVisaCostsPKR += $vCostPKR;
                    $bVisaSalesPKR += $vSalePKR;
                    $bVisaCostsSAR += $vCostSAR;
                    $bVisaSalesSAR += $vSaleSAR;

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
                        'supplier_name'  => $v->supplier->name ?? '-',
                        'visa_status'    => ucfirst($v->status ?: 'Approved'),
                        'roe'            => $vRoe,
                        'cost_pkr'       => $vCostPKR,
                        'sale_pkr'       => $vSalePKR,
                        'margin_pkr'     => $vSalePKR - $vCostPKR,
                        'cost_sar'       => $vCostSAR,
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

            $effRoe = $b->hotels->first()?->roe ?: ($fRoe > 1 ? $fRoe : $defRoe);
            $bTotalCostSAR = $effRoe > 0 ? ($bTotalCostPKR / $effRoe) : 0;
            $bTotalSaleSAR = $effRoe > 0 ? ($bTotalSalePKR / $effRoe) : 0;
            $bMarginSAR    = $effRoe > 0 ? ($bMarginPKR / $effRoe) : 0;

            $transportsSummary = implode(', ', $transportRoutesList);

            // If booking has hotels, create a combined row for each hotel entry
            if ($b->hotels && $b->hotels->count() > 0) {
                foreach ($b->hotels as $bhIdx => $bh) {
                    $hRoe = (float) ($bh->roe ?: 1);
                    $hCost = (float) ($bh->cost_amount ?: (($bh->cost_per_night ?: 0) * ($bh->no_of_nights ?: 1) * max(1, (int)$bh->no_of_rooms) * $hRoe));
                    $hSale = (float) ($bh->selling_amount ?: (($bh->selling_per_night ?: 0) * ($bh->no_of_nights ?: 1) * max(1, (int)$bh->no_of_rooms) * $hRoe));

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
                        'sale_pkr'             => $bhIdx === 0 ? $bTotalSalePKR : $hSale,
                        'margin_pkr'           => $bhIdx === 0 ? $bMarginPKR : ($hSale - $hCost),
                        'cost_sar'             => $bhIdx === 0 ? $bTotalCostSAR : ($hRoe > 0 ? $hCost / $hRoe : 0),
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
                    'sale_pkr'             => $bTotalSalePKR,
                    'margin_pkr'           => $bMarginPKR,
                    'cost_sar'             => $bTotalCostSAR,
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
        $hotelCostTotalPKR   = $hotelRecords->sum('cost_pkr');
        $hotelSaleTotalPKR   = $hotelRecords->sum('sale_pkr');
        $hotelMarginTotalPKR = $hotelRecords->sum('margin_pkr');
        $hotelCostTotalSAR   = $hotelRecords->sum('cost_sar');
        $hotelSaleTotalSAR   = $hotelRecords->sum('sale_sar');
        $hotelMarginTotalSAR = $hotelRecords->sum('margin_sar');

        // Flight Section Totals
        $flightCostTotalPKR   = $flightRecords->sum('cost_pkr');
        $flightSaleTotalPKR   = $flightRecords->sum('sale_pkr');
        $flightMarginTotalPKR = $flightRecords->sum('margin_pkr');
        $flightCostTotalSAR   = $flightRecords->sum('cost_sar');
        $flightSaleTotalSAR   = $flightRecords->sum('sale_sar');
        $flightMarginTotalSAR = $flightRecords->sum('margin_sar');

        // Transport Section Totals
        $transportCostTotalPKR   = $transportRecords->sum('cost_pkr');
        $transportSaleTotalPKR   = $transportRecords->sum('sale_pkr');
        $transportMarginTotalPKR = $transportRecords->sum('margin_pkr');
        $transportCostTotalSAR   = $transportRecords->sum('cost_sar');
        $transportSaleTotalSAR   = $transportRecords->sum('sale_sar');
        $transportMarginTotalSAR = $transportRecords->sum('margin_sar');

        // Visa Section Totals
        $visaCostTotalPKR   = $visaRecords->sum('cost_pkr');
        $visaSaleTotalPKR   = $visaRecords->sum('sale_pkr');
        $visaMarginTotalPKR = $visaRecords->sum('margin_pkr');
        $visaCostTotalSAR   = $visaRecords->sum('cost_sar');
        $visaSaleTotalSAR   = $visaRecords->sum('sale_sar');
        $visaMarginTotalSAR = $visaRecords->sum('margin_sar');

        // Grand Totals (Unique bookings summation to prevent double-count across multiple hotel rows)
        $totalCostPKR   = $hotelCostTotalPKR + $flightCostTotalPKR + $transportCostTotalPKR + $visaCostTotalPKR;
        $totalSalePKR   = $hotelSaleTotalPKR + $flightSaleTotalPKR + $transportSaleTotalPKR + $visaSaleTotalPKR;
        $totalMarginPKR = $totalSalePKR - $totalCostPKR;

        $totalCostSAR   = $hotelCostTotalSAR + $flightCostTotalSAR + $transportCostTotalSAR + $visaCostTotalSAR;
        $totalSaleSAR   = $hotelSaleTotalSAR + $flightSaleTotalSAR + $transportSaleTotalSAR + $visaSaleTotalSAR;
        $totalMarginSAR = $totalSaleSAR - $totalCostSAR;

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
            'hotelSaleTotalPKR',
            'hotelMarginTotalPKR',
            'hotelCostTotalSAR',
            'hotelSaleTotalSAR',
            'hotelMarginTotalSAR',
            'flightCostTotalPKR',
            'flightSaleTotalPKR',
            'flightMarginTotalPKR',
            'flightCostTotalSAR',
            'flightSaleTotalSAR',
            'flightMarginTotalSAR',
            'transportCostTotalPKR',
            'transportSaleTotalPKR',
            'transportMarginTotalPKR',
            'transportCostTotalSAR',
            'transportSaleTotalSAR',
            'transportMarginTotalSAR',
            'visaCostTotalPKR',
            'visaSaleTotalPKR',
            'visaMarginTotalPKR',
            'visaCostTotalSAR',
            'visaSaleTotalSAR',
            'visaMarginTotalSAR',
            'totalCostPKR',
            'totalSalePKR',
            'totalMarginPKR',
            'totalCostSAR',
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
