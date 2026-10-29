<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Company;
use App\Models\ExpenseTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfitLossReportController extends Controller
{
    /**
     * Display Profit & Loss Report with Per-Booking/Person and Monthly Breakdown.
     */
    public function index(Request $request)
    {
        $activeDashboardPkg = session('dashboard_package', 'hajj');
        $defaultPackageType = in_array($activeDashboardPkg, ['hajj', 'umrah']) ? $activeDashboardPkg : 'hajj';
        $packageType = $request->has('package_type') ? $request->get('package_type') : $defaultPackageType;
        $year = $request->get('year', 'all'); // e.g. 2026, 2027, 'all'
        $month = $request->get('month', 'all'); // 1-12 or 'all'
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $clientId = $request->get('client_id');
        $search = $request->get('search');

        // Build Booking Query
        $bookingQuery = Booking::with([
            'client',
            'company',
            'package',
            'persons',
            'hotels',
            'transports',
            'visas',
            'flightSupplier'
        ]);

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
        } elseif ($packageType !== 'all') {
            $bookingQuery->where('package_type', $packageType);
        }

        if ($year !== 'all' && !empty($year)) {
            $bookingQuery->where(function ($q) use ($year) {
                $q->where('package_year', $year)
                  ->orWhereHas('hotels', function ($hq) use ($year) {
                      $hq->whereRaw("YEAR(COALESCE(booking_date, check_in)) = ?", [$year]);
                  })->orWhereHas('visas', function ($vq) use ($year) {
                      $vq->whereRaw("YEAR(booking_date) = ?", [$year]);
                  })->orWhereHas('transports', function ($tq) use ($year) {
                      $tq->whereRaw("YEAR(COALESCE(booking_date, travel_date)) = ?", [$year]);
                  })->orWhereRaw("YEAR(COALESCE(flight_booking_date, booking_date, DATE(created_at))) = ?", [$year]);
            });
        }

        if ($month !== 'all' && !empty($month)) {
            $bookingQuery->where(function ($q) use ($month) {
                $q->whereHas('hotels', function ($hq) use ($month) {
                    $hq->whereRaw("MONTH(COALESCE(booking_date, check_in)) = ?", [$month]);
                })->orWhereHas('visas', function ($vq) use ($month) {
                    $vq->whereRaw("MONTH(booking_date) = ?", [$month]);
                })->orWhereHas('transports', function ($tq) use ($month) {
                    $tq->whereRaw("MONTH(COALESCE(booking_date, travel_date)) = ?", [$month]);
                })->orWhereRaw("MONTH(COALESCE(flight_booking_date, booking_date, DATE(created_at))) = ?", [$month]);
            });
        }

        if (!empty($dateFrom)) {
            $bookingQuery->where(function ($q) use ($dateFrom) {
                $q->whereHas('hotels', function ($hq) use ($dateFrom) {
                    $hq->whereRaw("DATE(COALESCE(booking_date, check_in)) >= ?", [$dateFrom]);
                })->orWhereHas('visas', function ($vq) use ($dateFrom) {
                    $vq->whereRaw("DATE(booking_date) >= ?", [$dateFrom]);
                })->orWhereHas('transports', function ($tq) use ($dateFrom) {
                    $tq->whereRaw("DATE(COALESCE(booking_date, travel_date)) >= ?", [$dateFrom]);
                })->orWhereRaw("DATE(COALESCE(flight_booking_date, booking_date, DATE(created_at))) >= ?", [$dateFrom]);
            });
        }

        if (!empty($dateTo)) {
            $bookingQuery->where(function ($q) use ($dateTo) {
                $q->whereHas('hotels', function ($hq) use ($dateTo) {
                    $hq->whereRaw("DATE(COALESCE(booking_date, check_in)) <= ?", [$dateTo]);
                })->orWhereHas('visas', function ($vq) use ($dateTo) {
                    $vq->whereRaw("DATE(booking_date) <= ?", [$dateTo]);
                })->orWhereHas('transports', function ($tq) use ($dateTo) {
                    $tq->whereRaw("DATE(COALESCE(booking_date, travel_date)) <= ?", [$dateTo]);
                })->orWhereRaw("DATE(COALESCE(flight_booking_date, booking_date, DATE(created_at))) <= ?", [$dateTo]);
            });
        }

        if (!empty($clientId)) {
            $bookingQuery->where('client_id', $clientId);
        }

        if (!empty($search)) {
            $bookingQuery->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                  ->orWhere('haji_id', 'like', "%{$search}%")
                  ->orWhere('passport_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('persons', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('passport_number', 'like', "%{$search}%")
                         ->orWhere('haji_id', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $bookingQuery->orderByRaw("COALESCE(booking_date, flight_booking_date, DATE(created_at)) DESC")->get();

        // Process Costing & Profit per Booking
        $processedBookings = [];
        $totalSellingPkr = 0;
        $totalHotelSellPkr = 0;
        $totalFlightSellPkr = 0;
        $totalVisaSellPkr = 0;
        $totalTransSellPkr = 0;

        $totalDirectCostPkr = 0;
        $totalHotelCostPkr = 0;
        $totalFlightCostPkr = 0;
        $totalVisaCostPkr = 0;
        $totalTransCostPkr = 0;

        $totalGrossProfitPkr = 0;
        $totalReceivedPkr = 0;
        $totalBalancePkr = 0;
        $totalPaxCount = 0;

        $monthlyData = [];

        // Helper to check if a service date matches active period filter
        $isDateInFilter = function ($dateStr) use ($month, $year, $dateFrom, $dateTo) {
            if (!$dateStr) return false;
            $dt = Carbon::parse($dateStr);
            if ($year !== 'all' && !empty($year) && (int) $dt->format('Y') !== (int) $year) {
                return false;
            }
            if ($month !== 'all' && !empty($month) && (int) $dt->format('n') !== (int) $month) {
                return false;
            }
            if (!empty($dateFrom) && $dt->format('Y-m-d') < $dateFrom) {
                return false;
            }
            if (!empty($dateTo) && $dt->format('Y-m-d') > $dateTo) {
                return false;
            }
            return true;
        };

        $hasFilter = ($month !== 'all' && !empty($month)) || ($year !== 'all' && !empty($year)) || !empty($dateFrom) || !empty($dateTo);

        foreach ($bookings as $b) {
            $curr = $b->currency ?: 'PKR';
            $pax = max(1, (int) ($b->no_of_pax ?: $b->persons->count() ?: 1));

            $sellingRoe = 1;
            if ($curr === 'SAR') {
                $sellingRoe = (float) ($b->flight_roe > 1 ? $b->flight_roe : ($b->hotels->first()?->roe ?? 75));
            }

            // 1. Hotels Breakdown & Monthly Allocation
            $bHotelCostPkr = 0;
            $bHotelSellPkr = 0;
            $allHotelCostPkr = 0;
            $allHotelSellPkr = 0;
            $primaryHotelDate = null;

            foreach ($b->hotels as $h) {
                $hDate = $h->booking_date ?: ($h->check_in ?: ($b->booking_date ?: ($b->created_at ? $b->created_at->toDateString() : date('Y-m-d'))));
                if (!$primaryHotelDate) $primaryHotelDate = $hDate;
                $cDate = Carbon::parse($hDate);
                $mKey = $cDate->format('Y-m');
                $mLabel = $cDate->format('M Y');

                $hCurr = strtoupper($h->currency ?? 'SAR');
                $hCostRoe = ($hCurr === 'PKR') ? 1 : (float) ($h->cost_roe > 0 ? $h->cost_roe : ($h->roe > 0 ? $h->roe : 75));
                $hSaleRoe = ($hCurr === 'PKR') ? 1 : (float) ($h->sale_roe > 0 ? $h->sale_roe : ($h->roe > 0 ? $h->roe : 75));

                $cCost = (float) $h->cost_amount ?: ((float) ($h->cost_per_night * ($h->no_of_nights ?: 1) * ($h->no_of_rooms ?: 1)) * $hCostRoe);
                $cSell = (float) $h->selling_amount ?: ((float) ($h->selling_per_night * ($h->no_of_nights ?: 1) * ($h->no_of_rooms ?: 1)) * $hSaleRoe);

                // Add to Monthly Table for that specific hotel's month
                if (!isset($monthlyData[$mKey])) {
                    $monthlyData[$mKey] = [
                        'month_label'       => $mLabel,
                        'year_month'        => $mKey,
                        'bookings_count'    => 0,
                        'pax_count'         => 0,
                        'selling_pkr'       => 0,
                        'direct_cost_pkr'   => 0,
                        'hotel_cost_pkr'    => 0,
                        'flight_cost_pkr'   => 0,
                        'visa_cost_pkr'     => 0,
                        'transport_cost_pkr'=> 0,
                        'gross_profit_pkr'  => 0,
                        'expenses_pkr'      => 0,
                        'net_profit_pkr'    => 0,
                        '_booking_ids'      => [],
                    ];
                }
                if (!in_array($b->id, $monthlyData[$mKey]['_booking_ids'])) {
                    $monthlyData[$mKey]['_booking_ids'][] = $b->id;
                    $monthlyData[$mKey]['bookings_count']++;
                    $monthlyData[$mKey]['pax_count'] = ($monthlyData[$mKey]['pax_count'] ?? 0) + $pax;
                }
                $monthlyData[$mKey]['hotel_cost_pkr'] += $cCost;
                $monthlyData[$mKey]['direct_cost_pkr'] += $cCost;
                $monthlyData[$mKey]['selling_pkr'] += $cSell;
                $monthlyData[$mKey]['gross_profit_pkr'] += ($cSell - $cCost);

                $allHotelCostPkr += $cCost;
                $allHotelSellPkr += $cSell;

                if (!$hasFilter || $isDateInFilter($hDate)) {
                    $bHotelCostPkr += $cCost;
                    $bHotelSellPkr += $cSell;
                }
            }

            // 2. Flight Breakdown & Monthly Allocation
            $bFlightCostPkr = 0;
            $bFlightSellPkr = 0;
            $allFlightCostPkr = 0;
            $allFlightSellPkr = 0;
            $primaryFlightDate = null;

            if ((float) $b->flight_cost > 0 || (float) $b->flight_charges > 0 || (float) $b->flight_cost_rate > 0) {
                $fDate = $b->flight_booking_date ?: ($b->departure_date ?: ($b->booking_date ?: ($b->created_at ? $b->created_at->toDateString() : date('Y-m-d'))));
                $primaryFlightDate = $fDate;
                $cDate = Carbon::parse($fDate);
                $mKey = $cDate->format('Y-m');
                $mLabel = $cDate->format('M Y');

                $fCurr = strtoupper($b->flight_currency ?? 'SAR');
                $fRoe = ($fCurr === 'PKR') ? 1 : (float) ($b->flight_cost_roe > 0 ? $b->flight_cost_roe : ($b->flight_roe > 0 ? $b->flight_roe : 75));
                $fSaleRoe = ($fCurr === 'PKR') ? 1 : (float) ($b->flight_sale_roe > 0 ? $b->flight_sale_roe : ($b->flight_roe > 0 ? $b->flight_roe : 75));

                $fCost = (float) $b->flight_cost ?: ((float) ($b->flight_cost_rate > 0 ? ($b->flight_cost_rate * $pax) : 0) * $fRoe);
                $fSell = (float) $b->flight_charges ?: ((float) ($b->flight_selling_rate > 0 ? ($b->flight_selling_rate * $pax) : 0) * $fSaleRoe);

                if (!isset($monthlyData[$mKey])) {
                    $monthlyData[$mKey] = [
                        'month_label'       => $mLabel,
                        'year_month'        => $mKey,
                        'bookings_count'    => 0,
                        'pax_count'         => 0,
                        'selling_pkr'       => 0,
                        'direct_cost_pkr'   => 0,
                        'hotel_cost_pkr'    => 0,
                        'flight_cost_pkr'   => 0,
                        'visa_cost_pkr'     => 0,
                        'transport_cost_pkr'=> 0,
                        'gross_profit_pkr'  => 0,
                        'expenses_pkr'      => 0,
                        'net_profit_pkr'    => 0,
                        '_booking_ids'      => [],
                    ];
                }
                if (!in_array($b->id, $monthlyData[$mKey]['_booking_ids'])) {
                    $monthlyData[$mKey]['_booking_ids'][] = $b->id;
                    $monthlyData[$mKey]['bookings_count']++;
                    $monthlyData[$mKey]['pax_count'] = ($monthlyData[$mKey]['pax_count'] ?? 0) + $pax;
                }
                $monthlyData[$mKey]['flight_cost_pkr'] += $fCost;
                $monthlyData[$mKey]['direct_cost_pkr'] += $fCost;
                $monthlyData[$mKey]['selling_pkr'] += $fSell;
                $monthlyData[$mKey]['gross_profit_pkr'] += ($fSell - $fCost);

                $allFlightCostPkr += $fCost;
                $allFlightSellPkr += $fSell;

                if (!$hasFilter || $isDateInFilter($fDate)) {
                    $bFlightCostPkr += $fCost;
                    $bFlightSellPkr += $fSell;
                }
            }

            // 3. Visa Breakdown & Monthly Allocation
            $bVisaCostPkr = 0;
            $bVisaSellPkr = 0;
            $allVisaCostPkr = 0;
            $allVisaSellPkr = 0;
            $primaryVisaDate = null;

            foreach ($b->visas as $v) {
                $vDate = $v->booking_date ?: ($b->booking_date ?: ($b->created_at ? $b->created_at->toDateString() : date('Y-m-d')));
                if (!$primaryVisaDate) $primaryVisaDate = $vDate;
                $cDate = Carbon::parse($vDate);
                $mKey = $cDate->format('Y-m');
                $mLabel = $cDate->format('M Y');

                $vCurr = strtoupper($v->currency ?? 'SAR');
                $vCostRoe = ($vCurr === 'PKR') ? 1 : (float) ($v->cost_roe > 0 ? $v->cost_roe : ($v->roe > 0 ? $v->roe : 75));
                $vSaleRoe = ($vCurr === 'PKR') ? 1 : (float) ($v->sale_roe > 0 ? $v->sale_roe : ($v->roe > 0 ? $v->roe : 75));

                $cCost = (float) $v->cost_amount ?: ((float) $v->cost_rate * $vCostRoe);
                $cSell = (float) $v->selling_amount ?: ((float) $v->selling_rate * $vSaleRoe);

                if (!isset($monthlyData[$mKey])) {
                    $monthlyData[$mKey] = [
                        'month_label'       => $mLabel,
                        'year_month'        => $mKey,
                        'bookings_count'    => 0,
                        'pax_count'         => 0,
                        'selling_pkr'       => 0,
                        'direct_cost_pkr'   => 0,
                        'hotel_cost_pkr'    => 0,
                        'flight_cost_pkr'   => 0,
                        'visa_cost_pkr'     => 0,
                        'transport_cost_pkr'=> 0,
                        'gross_profit_pkr'  => 0,
                        'expenses_pkr'      => 0,
                        'net_profit_pkr'    => 0,
                        '_booking_ids'      => [],
                    ];
                }
                if (!in_array($b->id, $monthlyData[$mKey]['_booking_ids'])) {
                    $monthlyData[$mKey]['_booking_ids'][] = $b->id;
                    $monthlyData[$mKey]['bookings_count']++;
                    $monthlyData[$mKey]['pax_count'] = ($monthlyData[$mKey]['pax_count'] ?? 0) + $pax;
                }
                $monthlyData[$mKey]['visa_cost_pkr'] += $cCost;
                $monthlyData[$mKey]['direct_cost_pkr'] += $cCost;
                $monthlyData[$mKey]['selling_pkr'] += $cSell;
                $monthlyData[$mKey]['gross_profit_pkr'] += ($cSell - $cCost);

                $allVisaCostPkr += $cCost;
                $allVisaSellPkr += $cSell;

                if (!$hasFilter || $isDateInFilter($vDate)) {
                    $bVisaCostPkr += $cCost;
                    $bVisaSellPkr += $cSell;
                }
            }

            // 4. Transport Breakdown & Monthly Allocation
            $bTransCostPkr = 0;
            $bTransSellPkr = 0;
            $allTransCostPkr = 0;
            $allTransSellPkr = 0;
            $primaryTransDate = null;

            foreach ($b->transports as $t) {
                $tDate = $t->booking_date ?: ($t->travel_date ?: ($b->booking_date ?: ($b->created_at ? $b->created_at->toDateString() : date('Y-m-d'))));
                if (!$primaryTransDate) $primaryTransDate = $tDate;
                $cDate = Carbon::parse($tDate);
                $mKey = $cDate->format('Y-m');
                $mLabel = $cDate->format('M Y');

                $tCurr = strtoupper($t->currency ?? 'SAR');
                $tCostRoe = ($tCurr === 'PKR') ? 1 : (float) ($t->cost_roe > 0 ? $t->cost_roe : ($t->roe > 0 ? $t->roe : 75));
                $tSaleRoe = ($tCurr === 'PKR') ? 1 : (float) ($t->sale_roe > 0 ? $t->sale_roe : ($t->roe > 0 ? $t->roe : 75));

                $cCost = (float) $t->cost_amount ?: ((float) $t->cost_rate * $tCostRoe);
                $cSell = (float) $t->selling_amount ?: ((float) $t->selling_rate * $tSaleRoe);

                if (!isset($monthlyData[$mKey])) {
                    $monthlyData[$mKey] = [
                        'month_label'       => $mLabel,
                        'year_month'        => $mKey,
                        'bookings_count'    => 0,
                        'pax_count'         => 0,
                        'selling_pkr'       => 0,
                        'direct_cost_pkr'   => 0,
                        'hotel_cost_pkr'    => 0,
                        'flight_cost_pkr'   => 0,
                        'visa_cost_pkr'     => 0,
                        'transport_cost_pkr'=> 0,
                        'gross_profit_pkr'  => 0,
                        'expenses_pkr'      => 0,
                        'net_profit_pkr'    => 0,
                        '_booking_ids'      => [],
                    ];
                }
                if (!in_array($b->id, $monthlyData[$mKey]['_booking_ids'])) {
                    $monthlyData[$mKey]['_booking_ids'][] = $b->id;
                    $monthlyData[$mKey]['bookings_count']++;
                    $monthlyData[$mKey]['pax_count'] = ($monthlyData[$mKey]['pax_count'] ?? 0) + $pax;
                }
                $monthlyData[$mKey]['transport_cost_pkr'] += $cCost;
                $monthlyData[$mKey]['direct_cost_pkr'] += $cCost;
                $monthlyData[$mKey]['selling_pkr'] += $cSell;
                $monthlyData[$mKey]['gross_profit_pkr'] += ($cSell - $cCost);

                $allTransCostPkr += $cCost;
                $allTransSellPkr += $cSell;

                if (!$hasFilter || $isDateInFilter($tDate)) {
                    $bTransCostPkr += $cCost;
                    $bTransSellPkr += $cSell;
                }
            }

            // Residual package selling / additional services not attached to individual items
            $totalBookingSellPkr = (float) $b->total_amount * $sellingRoe;
            $allServiceSellSum = $allHotelSellPkr + $allFlightSellPkr + $allVisaSellPkr + $allTransSellPkr;
            $residualSell = max(0, $totalBookingSellPkr - $allServiceSellSum);

            if ($residualSell > 0) {
                $mainDate = $b->booking_date ?: ($b->created_at ? $b->created_at->toDateString() : date('Y-m-d'));
                $cDate = Carbon::parse($mainDate);
                $mKey = $cDate->format('Y-m');
                $mLabel = $cDate->format('M Y');
                if (!isset($monthlyData[$mKey])) {
                    $monthlyData[$mKey] = [
                        'month_label'       => $mLabel,
                        'year_month'        => $mKey,
                        'bookings_count'    => 0,
                        'pax_count'         => 0,
                        'selling_pkr'       => 0,
                        'direct_cost_pkr'   => 0,
                        'hotel_cost_pkr'    => 0,
                        'flight_cost_pkr'   => 0,
                        'visa_cost_pkr'     => 0,
                        'transport_cost_pkr'=> 0,
                        'gross_profit_pkr'  => 0,
                        'expenses_pkr'      => 0,
                        'net_profit_pkr'    => 0,
                        '_booking_ids'      => [],
                    ];
                }
                $monthlyData[$mKey]['selling_pkr'] += $residualSell;
                $monthlyData[$mKey]['gross_profit_pkr'] += $residualSell;

                if (!$hasFilter || $isDateInFilter($mainDate)) {
                    // add to current filtered row
                }
            }

            // Calculate Per-Booking row for Tab 1
            if ($hasFilter) {
                $bSellingPkr = $bHotelSellPkr + $bFlightSellPkr + $bVisaSellPkr + $bTransSellPkr;
                if ($allServiceSellSum == 0 && $totalBookingSellPkr > 0) {
                    $bSellingPkr = $totalBookingSellPkr;
                }
                $bDirectCostPkr = $bHotelCostPkr + $bFlightCostPkr + $bVisaCostPkr + $bTransCostPkr;
                $bGrossProfitPkr = $bSellingPkr - $bDirectCostPkr;
                $relevantDate = $bHotelCostPkr > 0 ? $primaryHotelDate : ($bFlightCostPkr > 0 ? $primaryFlightDate : ($bVisaCostPkr > 0 ? $primaryVisaDate : ($bTransCostPkr > 0 ? $primaryTransDate : $b->booking_date)));
            } else {
                $bSellingPkr = $totalBookingSellPkr ?: ($allHotelSellPkr + $allFlightSellPkr + $allVisaSellPkr + $allTransSellPkr);
                $bHotelCostPkr = $allHotelCostPkr;
                $bFlightCostPkr = $allFlightCostPkr;
                $bVisaCostPkr = $allVisaCostPkr;
                $bTransCostPkr = $allTransCostPkr;
                $bDirectCostPkr = $bHotelCostPkr + $bFlightCostPkr + $bVisaCostPkr + $bTransCostPkr;
                $bGrossProfitPkr = $bSellingPkr - $bDirectCostPkr;
                $relevantDate = $b->booking_date ?: ($primaryHotelDate ?: ($primaryFlightDate ?: ($b->created_at ? $b->created_at->toDateString() : date('Y-m-d'))));
            }

            $bMarginPct = $bSellingPkr > 0 ? round(($bGrossProfitPkr / $bSellingPkr) * 100, 1) : 0;
            $bPerPaxProfit = round($bGrossProfitPkr / $pax, 2);
            $bPerPaxSelling = round($bSellingPkr / $pax, 2);
            $bPerPaxCost = round($bDirectCostPkr / $pax, 2);

            $receivedPkr = (float) $b->total_received * $sellingRoe;
            $balancePkr = (float) $b->balance * $sellingRoe;

            if (!$hasFilter || ($bDirectCostPkr > 0 || $bSellingPkr > 0)) {
                $totalPaxCount += $pax;
                $totalSellingPkr += $bSellingPkr;
                $totalHotelSellPkr += $bHotelSellPkr;
                $totalFlightSellPkr += $bFlightSellPkr;
                $totalVisaSellPkr += $bVisaSellPkr;
                $totalTransSellPkr += $bTransSellPkr;

                $totalDirectCostPkr += $bDirectCostPkr;
                $totalHotelCostPkr += $bHotelCostPkr;
                $totalFlightCostPkr += $bFlightCostPkr;
                $totalVisaCostPkr += $bVisaCostPkr;
                $totalTransCostPkr += $bTransCostPkr;

                $totalGrossProfitPkr += $bGrossProfitPkr;
                $totalReceivedPkr += $receivedPkr;
                $totalBalancePkr += $balancePkr;

                $processedBookings[] = (object) [
                    'booking'            => $b,
                    'pax'                => $pax,
                    'currency'           => $curr,
                    'selling_raw'        => $bSellingPkr,
                    'selling_pkr'        => $bSellingPkr,
                    'hotel_sell_pkr'     => $bHotelSellPkr,
                    'flight_sell_pkr'    => $bFlightSellPkr,
                    'visa_sell_pkr'      => $bVisaSellPkr,
                    'transport_sell_pkr' => $bTransSellPkr,
                    'received_pkr'       => $receivedPkr,
                    'balance_pkr'        => $balancePkr,
                    'hotel_cost_pkr'     => $bHotelCostPkr,
                    'flight_cost_pkr'    => $bFlightCostPkr,
                    'visa_cost_pkr'      => $bVisaCostPkr,
                    'transport_cost_pkr' => $bTransCostPkr,
                    'direct_cost_pkr'    => $bDirectCostPkr,
                    'gross_profit_pkr'   => $bGrossProfitPkr,
                    'margin_pct'         => $bMarginPct,
                    'per_pax_profit'     => $bPerPaxProfit,
                    'per_pax_selling'    => $bPerPaxSelling,
                    'per_pax_cost'       => $bPerPaxCost,
                    'persons'            => $b->persons,
                    'relevant_date'      => $relevantDate,
                ];
            }
        }

        // Query Overhead Expenses from ExpenseTransaction
        $expenseQuery = ExpenseTransaction::whereNull('deleted_at');
        if ($packageType === 'hajj') {
            $expenseQuery->where(function ($q) {
                $q->where('hajj_umrah', 'hajj')
                  ->orWhereNull('hajj_umrah');
            });
        } elseif ($packageType === 'umrah') {
            $expenseQuery->where('hajj_umrah', 'umrah');
        } elseif ($packageType !== 'all') {
            $expenseQuery->where('hajj_umrah', $packageType);
        }
        if ($year !== 'all' && !empty($year)) {
            $expenseQuery->where(function ($q) use ($year) {
                $q->where('year', $year)->orWhereYear('date', $year);
            });
        }
        if ($month !== 'all' && !empty($month)) {
            $expenseQuery->whereMonth('date', $month);
        }
        if (!empty($dateFrom)) {
            $expenseQuery->whereDate('date', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $expenseQuery->whereDate('date', '<=', $dateTo);
        }

        $expenseTransactions = $expenseQuery->get();
        $totalOverheadExpensesPkr = 0;

        foreach ($expenseTransactions as $exp) {
            $rate = (float) ($exp->exchange_rate > 0 ? $exp->exchange_rate : 1);
            $expPkr = (float) $exp->amount * $rate;
            $totalOverheadExpensesPkr += $expPkr;

            $eDate = $exp->date ? Carbon::parse($exp->date) : Carbon::now();
            $mKey = $eDate->format('Y-m');
            $mLabel = $eDate->format('M Y');

            if (!isset($monthlyData[$mKey])) {
                $monthlyData[$mKey] = [
                    'month_label'       => $mLabel,
                    'year_month'        => $mKey,
                    'bookings_count'    => 0,
                    'pax_count'         => 0,
                    'selling_pkr'       => 0,
                    'direct_cost_pkr'   => 0,
                    'hotel_cost_pkr'    => 0,
                    'flight_cost_pkr'   => 0,
                    'visa_cost_pkr'     => 0,
                    'transport_cost_pkr'=> 0,
                    'gross_profit_pkr'  => 0,
                    'expenses_pkr'      => 0,
                    'net_profit_pkr'    => 0,
                ];
            }
            $monthlyData[$mKey]['expenses_pkr'] += $expPkr;
        }

        // Calculate Net Profit for Monthly Table
        krsort($monthlyData); // Sort months descending
        foreach ($monthlyData as $k => &$mItem) {
            $mItem['net_profit_pkr'] = $mItem['gross_profit_pkr'] - $mItem['expenses_pkr'];
            $mItem['gross_margin_pct'] = $mItem['selling_pkr'] > 0 ? round(($mItem['gross_profit_pkr'] / $mItem['selling_pkr']) * 100, 1) : 0;
            $mItem['net_margin_pct'] = $mItem['selling_pkr'] > 0 ? round(($mItem['net_profit_pkr'] / $mItem['selling_pkr']) * 100, 1) : 0;
        }
        unset($mItem);

        $netOperatingProfitPkr = $totalGrossProfitPkr - $totalOverheadExpensesPkr;
        $overallGrossMarginPct = $totalSellingPkr > 0 ? round(($totalGrossProfitPkr / $totalSellingPkr) * 100, 1) : 0;
        $overallNetMarginPct = $totalSellingPkr > 0 ? round(($netOperatingProfitPkr / $totalSellingPkr) * 100, 1) : 0;
        $avgProfitPerPax = $totalPaxCount > 0 ? round($totalGrossProfitPkr / $totalPaxCount, 2) : 0;

        $clients = Client::orderBy('name')->get();
        $years = Booking::select('package_year')->distinct()->whereNotNull('package_year')->pluck('package_year')->toArray();
        if (!in_array(date('Y'), $years)) $years[] = (int) date('Y');
        if (!in_array(date('Y') + 1, $years)) $years[] = (int) date('Y') + 1;
        rsort($years);

        return view('reports.profit_loss', compact(
            'processedBookings',
            'monthlyData',
            'totalSellingPkr',
            'totalHotelSellPkr',
            'totalFlightSellPkr',
            'totalVisaSellPkr',
            'totalTransSellPkr',
            'totalDirectCostPkr',
            'totalHotelCostPkr',
            'totalFlightCostPkr',
            'totalVisaCostPkr',
            'totalTransCostPkr',
            'totalGrossProfitPkr',
            'totalOverheadExpensesPkr',
            'netOperatingProfitPkr',
            'overallGrossMarginPct',
            'overallNetMarginPct',
            'totalReceivedPkr',
            'totalBalancePkr',
            'totalPaxCount',
            'avgProfitPerPax',
            'clients',
            'years',
            'packageType',
            'year',
            'month',
            'dateFrom',
            'dateTo',
            'clientId',
            'search'
        ));
    }

    /**
     * Export / Print PDF for Profit & Loss Report.
     */
    public function exportPdf(Request $request)
    {
        // Re-use index calculations or render dedicated PDF printable view
        return $this->index($request);
    }
}
