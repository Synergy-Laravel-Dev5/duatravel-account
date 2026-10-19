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
        $packageType = $request->get('package_type', 'all'); // 'all', 'hajj', 'umrah'
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

        if ($packageType !== 'all') {
            $bookingQuery->where('package_type', $packageType);
        }

        if ($year !== 'all' && !empty($year)) {
            $bookingQuery->where(function ($q) use ($year) {
                $q->where('package_year', $year)
                  ->orWhereYear('booking_date', $year);
            });
        }

        if ($month !== 'all' && !empty($month)) {
            $bookingQuery->whereMonth('booking_date', $month);
        }

        if (!empty($dateFrom)) {
            $bookingQuery->whereDate('booking_date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $bookingQuery->whereDate('booking_date', '<=', $dateTo);
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

        $bookings = $bookingQuery->latest('booking_date')->get();

        // Process Costing & Profit per Booking
        $processedBookings = [];
        $totalSellingPkr = 0;
        $totalDirectCostPkr = 0;
        $totalGrossProfitPkr = 0;
        $totalReceivedPkr = 0;
        $totalBalancePkr = 0;
        $totalPaxCount = 0;

        $monthlyData = [];

        foreach ($bookings as $b) {
            $curr = $b->currency ?: 'PKR';
            $pax = max(1, (int) ($b->no_of_pax ?: $b->persons->count() ?: 1));
            $totalPaxCount += $pax;

            // Direct Costs Breakdown in PKR
            $hotelCostPkr = 0;
            foreach ($b->hotels as $h) {
                if ((float) $h->cost_amount > 0) {
                    $hotelCostPkr += (float) $h->cost_amount;
                } else {
                    $hCurr = strtoupper($h->currency ?? 'SAR');
                    $hRoe = ($hCurr === 'PKR') ? 1 : (float) ($h->cost_roe > 0 ? $h->cost_roe : ($h->roe > 0 ? $h->roe : 75));
                    $hotelCostPkr += (float) ($h->cost_per_night * ($h->no_of_nights ?: 1) * ($h->no_of_rooms ?: 1)) * $hRoe;
                }
            }

            $flightCostPkr = 0;
            if ((float) $b->flight_cost > 0) {
                $flightCostPkr = (float) $b->flight_cost;
            } else {
                $fCurr = strtoupper($b->flight_currency ?? 'SAR');
                $fRoe = ($fCurr === 'PKR') ? 1 : (float) ($b->flight_cost_roe > 0 ? $b->flight_cost_roe : ($b->flight_roe > 0 ? $b->flight_roe : 75));
                $flightCostPkr = (float) ($b->flight_cost_rate > 0 ? ($b->flight_cost_rate * $pax) : 0) * $fRoe;
            }

            $visaCostPkr = 0;
            foreach ($b->visas as $v) {
                if ((float) $v->cost_amount > 0) {
                    $visaCostPkr += (float) $v->cost_amount;
                } else {
                    $vCurr = strtoupper($v->currency ?? 'SAR');
                    $vRoe = ($vCurr === 'PKR') ? 1 : (float) ($v->cost_roe > 0 ? $v->cost_roe : ($v->roe > 0 ? $v->roe : 75));
                    $visaCostPkr += (float) $v->cost_rate * $vRoe;
                }
            }

            $transportCostPkr = 0;
            foreach ($b->transports as $t) {
                if ((float) $t->cost_amount > 0) {
                    $transportCostPkr += (float) $t->cost_amount;
                } else {
                    $tCurr = strtoupper($t->currency ?? 'SAR');
                    $tRoe = ($tCurr === 'PKR') ? 1 : (float) ($t->cost_roe > 0 ? $t->cost_roe : ($t->roe > 0 ? $t->roe : 75));
                    $transportCostPkr += (float) $t->cost_rate * $tRoe;
                }
            }

            $totalDirectCost = $hotelCostPkr + $flightCostPkr + $visaCostPkr + $transportCostPkr;

            // Selling in PKR
            $sellingRoe = 1;
            if ($curr === 'SAR') {
                $sellingRoe = (float) ($b->flight_roe > 1 ? $b->flight_roe : ($b->hotels->first()->roe ?? 75));
            }
            $sellingPkr = (float) $b->total_amount * $sellingRoe;
            $receivedPkr = (float) $b->total_received * $sellingRoe;
            $balancePkr = (float) $b->balance * $sellingRoe;

            $grossProfit = $sellingPkr - $totalDirectCost;
            $marginPct = $sellingPkr > 0 ? round(($grossProfit / $sellingPkr) * 100, 1) : 0;
            $perPaxProfit = round($grossProfit / $pax, 2);
            $perPaxSelling = round($sellingPkr / $pax, 2);
            $perPaxCost = round($totalDirectCost / $pax, 2);

            $totalSellingPkr += $sellingPkr;
            $totalDirectCostPkr += $totalDirectCost;
            $totalGrossProfitPkr += $grossProfit;
            $totalReceivedPkr += $receivedPkr;
            $totalBalancePkr += $balancePkr;

            $bDate = $b->booking_date ? Carbon::parse($b->booking_date) : Carbon::now();
            $monthKey = $bDate->format('Y-m');
            $monthLabel = $bDate->format('M Y');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'month_label'       => $monthLabel,
                    'year_month'        => $monthKey,
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

            $monthlyData[$monthKey]['bookings_count']++;
            $monthlyData[$monthKey]['pax_count'] += $pax;
            $monthlyData[$monthKey]['selling_pkr'] += $sellingPkr;
            $monthlyData[$monthKey]['direct_cost_pkr'] += $totalDirectCost;
            $monthlyData[$monthKey]['hotel_cost_pkr'] += $hotelCostPkr;
            $monthlyData[$monthKey]['flight_cost_pkr'] += $flightCostPkr;
            $monthlyData[$monthKey]['visa_cost_pkr'] += $visaCostPkr;
            $monthlyData[$monthKey]['transport_cost_pkr'] += $transportCostPkr;
            $monthlyData[$monthKey]['gross_profit_pkr'] += $grossProfit;

            $processedBookings[] = (object) [
                'booking'            => $b,
                'pax'                => $pax,
                'currency'           => $curr,
                'selling_raw'        => $b->total_amount,
                'selling_pkr'        => $sellingPkr,
                'received_pkr'       => $receivedPkr,
                'balance_pkr'        => $balancePkr,
                'hotel_cost_pkr'     => $hotelCostPkr,
                'flight_cost_pkr'    => $flightCostPkr,
                'visa_cost_pkr'      => $visaCostPkr,
                'transport_cost_pkr' => $transportCostPkr,
                'direct_cost_pkr'    => $totalDirectCost,
                'gross_profit_pkr'   => $grossProfit,
                'margin_pct'         => $marginPct,
                'per_pax_profit'     => $perPaxProfit,
                'per_pax_selling'    => $perPaxSelling,
                'per_pax_cost'       => $perPaxCost,
                'persons'            => $b->persons,
            ];
        }

        // Query Overhead Expenses from ExpenseTransaction
        $expenseQuery = ExpenseTransaction::whereNull('deleted_at');
        if ($packageType !== 'all') {
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
            'totalDirectCostPkr',
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
