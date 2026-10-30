<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\Company;
use App\Models\ExpenseTransaction;
use App\Models\Season;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function welcome()
    {
        $activeHajjSeason  = Season::hajj()->where('is_active', true)->first() ?? Season::hajj()->latest('year')->first();
        $activeUmrahSeason = Season::umrah()->where('is_active', true)->first() ?? Season::umrah()->latest('year')->first();

        $hajjCount   = Booking::where(function($q) {
            $q->where('package_type', 'hajj')
              ->orWhereNull('package_type')
              ->orWhere('package_type', '');
        })->where(function($q) {
            $q->whereNull('package_type')
              ->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
        })->count();

        $umrahCount  = Booking::whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah'])->count();

        $hajjRevenue  = Booking::where(function($q) {
            $q->where('package_type', 'hajj')
              ->orWhereNull('package_type')
              ->orWhere('package_type', '');
        })->where(function($q) {
            $q->whereNull('package_type')
              ->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
        })->sum('total_amount');

        $umrahRevenue = Booking::whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah'])->sum('total_amount');

        return view('welcome', compact('hajjCount', 'umrahCount', 'hajjRevenue', 'umrahRevenue', 'activeHajjSeason', 'activeUmrahSeason'));
    }

    public function index(Request $request)
    {
        if ($request->has('season_id')) {
            $season = Season::find($request->season_id);
            if ($season) {
                session([
                    'selected_season_id'     => $season->id,
                    'selected_season_name'   => $season->name,
                    'dashboard_package'      => $season->season_type,
                    'dashboard_year'         => $season->year,
                    'dashboard_company_year' => $season->year,
                ]);
            }
        }

        $package = strtolower(trim((string) $request->get('package', session('dashboard_package', 'hajj'))));
        if (!in_array($package, ['hajj', 'umrah'])) {
            $package = 'hajj';
        }
        $year = (int) $request->get('year', session('dashboard_year', Carbon::now()->year));

        session(['dashboard_package' => $package]);
        session(['dashboard_year'    => $year]);
        session(['dashboard_company_year' => $year]);

        // Keep season name & id synced with package & year
        $matchingSeason = Season::where('season_type', $package)->where('year', $year)->first();
        if ($matchingSeason) {
            session([
                'selected_season_id'   => $matchingSeason->id,
                'selected_season_name' => $matchingSeason->name,
            ]);
        } else {
            session([
                'selected_season_name' => strtoupper($package) . ' ' . $year,
            ]);
        }

        // Available years from Bookings, Expenses, and Companies
        $bookingYears = Booking::selectRaw('DISTINCT COALESCE(package_year, YEAR(COALESCE(booking_date, created_at))) as yr')
            ->pluck('yr')
            ->filter()
            ->map(fn($y) => (int)$y)
            ->toArray();

        $expenseYears = ExpenseTransaction::selectRaw('DISTINCT COALESCE(year, YEAR(date)) as yr')
            ->pluck('yr')
            ->filter()
            ->map(fn($y) => (int)$y)
            ->toArray();

        $companyYears = Company::selectRaw('DISTINCT company_year as yr')
            ->pluck('yr')
            ->filter()
            ->map(fn($y) => (int)$y)
            ->toArray();

        $availableYears = collect(array_merge($bookingYears, $expenseYears, $companyYears, [Carbon::now()->year]))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (!in_array($year, $availableYears) && count($availableYears) > 0) {
            $year = $availableYears[0];
            session(['dashboard_year' => $year]);
            session(['dashboard_company_year' => $year]);
        }

        $now           = Carbon::now();
        $weekAgo       = Carbon::now()->subDays(7);
        $lastWeekStart = Carbon::now()->subDays(14);
        $lastWeekEnd   = Carbon::now()->subDays(7);

        $totalUsers = User::count();
        $usersThisWeek = User::where('created_at', '>=', $weekAgo)->count();
        $usersLastWeek = User::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $usersTrend = $usersLastWeek > 0
            ? round((($usersThisWeek - $usersLastWeek) / $usersLastWeek) * 100, 1)
            : 0;

        $companyQuery = fn() => Company::where('company_year', $year);
        $totalCompanies = $companyQuery()->count();
        $companiesThisWeek = $companyQuery()->where('created_at', '>=', $weekAgo)->count();
        $companiesLastWeek = $companyQuery()->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $companiesTrend = $companiesLastWeek > 0
            ? round((($companiesThisWeek - $companiesLastWeek) / $companiesLastWeek) * 100, 1)
            : 0;

        // Base Booking Query Closure
        $applyPackage = function ($q) use ($package) {
            if ($package === 'hajj') {
                $q->where(function ($sq) {
                    $sq->where('package_type', 'hajj')
                       ->orWhereNull('package_type')
                       ->orWhere('package_type', '');
                })->where(function ($sq) {
                    $sq->whereNull('package_type')
                       ->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
                });
            } elseif ($package === 'umrah') {
                $q->whereRaw('LOWER(TRIM(package_type)) = ?', ['umrah']);
            } else {
                $q->where('package_type', $package);
            }
        };

        $applyYear = function ($q) use ($year) {
            $q->where(function ($sq) use ($year) {
                $sq->where('package_year', $year)
                   ->orWhereRaw("YEAR(COALESCE(booking_date, flight_booking_date, DATE(created_at))) = ?", [$year]);
            });
        };

        // Fetch All Matching Bookings with Relations
        $bookings = Booking::with([
            'client',
            'company',
            'hotels',
            'transports',
            'visas',
            'flightSupplier',
            'persons'
        ])
        ->where(function ($q) use ($applyPackage) {
            $applyPackage($q);
        })
        ->where(function ($q) use ($applyYear) {
            $applyYear($q);
        })
        ->get();

        $totalBookings = $bookings->count();

        $bookingsThisWeek = $bookings->filter(fn($b) => Carbon::parse($b->created_at) >= $weekAgo)->count();
        $bookingsLastWeek = $bookings->filter(fn($b) => Carbon::parse($b->created_at)->between($lastWeekStart, $lastWeekEnd))->count();
        $bookingsTrend = $bookingsLastWeek > 0
            ? round((($bookingsThisWeek - $bookingsLastWeek) / $bookingsLastWeek) * 100, 1)
            : 0;

        // Calculate Revenue, Direct Costs, and Initial Receipts from Bookings
        $totalRevenue = 0;
        $totalDirectCosts = 0;
        $totalHotelCost = 0;
        $totalFlightCost = 0;
        $totalVisaCost = 0;
        $totalTransportCost = 0;
        $bookingDirectReceived = 0;

        $revenueThisWeek = 0;
        $revenueLastWeek = 0;

        foreach ($bookings as $b) {
            $curr = strtoupper($b->currency ?? 'PKR');
            $sellingRoe = ($curr === 'PKR') ? 1 : (float) ($b->flight_roe > 0 ? $b->flight_roe : 75);
            $pax = max(1, (int) ($b->no_of_pax > 0 ? $b->no_of_pax : 1));

            $bHotelSell = 0;
            $bHotelCost = 0;
            foreach ($b->hotels as $h) {
                $hCurr = strtoupper($h->currency ?? 'SAR');
                $hCostRoe = ($hCurr === 'PKR') ? 1 : (float) ($h->cost_roe > 0 ? $h->cost_roe : ($h->roe > 0 ? $h->roe : 75));
                $hSaleRoe = ($hCurr === 'PKR') ? 1 : (float) ($h->sale_roe > 0 ? $h->sale_roe : ($h->roe > 0 ? $h->roe : 75));
                $cCost = (float) $h->cost_amount ?: ((float) $h->cost_rate * $hCostRoe);
                $cSell = (float) $h->selling_amount ?: ((float) $h->selling_rate * $hSaleRoe);
                $bHotelCost += $cCost;
                $bHotelSell += $cSell;
            }

            $bFlightSell = 0;
            $bFlightCost = 0;
            if ((float) $b->flight_cost > 0 || (float) $b->flight_charges > 0 || (float) $b->flight_cost_rate > 0) {
                $fCurr = strtoupper($b->flight_currency ?? 'SAR');
                $fCostRoe = ($fCurr === 'PKR') ? 1 : (float) ($b->flight_cost_roe > 0 ? $b->flight_cost_roe : ($b->flight_roe > 0 ? $b->flight_roe : 75));
                $fSaleRoe = ($fCurr === 'PKR') ? 1 : (float) ($b->flight_sale_roe > 0 ? $b->flight_sale_roe : ($b->flight_roe > 0 ? $b->flight_roe : 75));
                $bFlightCost = (float) $b->flight_cost ?: ((float) ($b->flight_cost_rate > 0 ? ($b->flight_cost_rate * $pax) : 0) * $fCostRoe);
                $bFlightSell = (float) $b->flight_charges ?: ((float) ($b->flight_selling_rate > 0 ? ($b->flight_selling_rate * $pax) : 0) * $fSaleRoe);
            }

            $bVisaSell = 0;
            $bVisaCost = 0;
            foreach ($b->visas as $v) {
                $vCurr = strtoupper($v->currency ?? 'SAR');
                $vCostRoe = ($vCurr === 'PKR') ? 1 : (float) ($v->cost_roe > 0 ? $v->cost_roe : ($v->roe > 0 ? $v->roe : 75));
                $vSaleRoe = ($vCurr === 'PKR') ? 1 : (float) ($v->sale_roe > 0 ? $v->sale_roe : ($v->roe > 0 ? $v->roe : 75));
                $cCost = (float) $v->cost_amount ?: ((float) $v->cost_rate * $vCostRoe);
                $cSell = (float) $v->selling_amount ?: ((float) $v->selling_rate * $vSaleRoe);
                $bVisaCost += $cCost;
                $bVisaSell += $cSell;
            }

            $bTransSell = 0;
            $bTransCost = 0;
            foreach ($b->transports as $t) {
                $tCurr = strtoupper($t->currency ?? 'SAR');
                $tCostRoe = ($tCurr === 'PKR') ? 1 : (float) ($t->cost_roe > 0 ? $t->cost_roe : ($t->roe > 0 ? $t->roe : 75));
                $tSaleRoe = ($tCurr === 'PKR') ? 1 : (float) ($t->sale_roe > 0 ? $t->sale_roe : ($t->roe > 0 ? $t->roe : 75));
                $cCost = (float) $t->cost_amount ?: ((float) $t->cost_rate * $tCostRoe);
                $cSell = (float) $t->selling_amount ?: ((float) $t->selling_rate * $tSaleRoe);
                $bTransCost += $cCost;
                $bTransSell += $cSell;
            }

            $bDirectCost = $bHotelCost + $bFlightCost + $bVisaCost + $bTransCost;
            $totalHotelCost += $bHotelCost;
            $totalFlightCost += $bFlightCost;
            $totalVisaCost += $bVisaCost;
            $totalTransportCost += $bTransCost;
            $totalDirectCosts += $bDirectCost;

            $bPkgSell = (float) $b->total_amount * $sellingRoe;
            $allServiceSellSum = $bHotelSell + $bFlightSell + $bVisaSell + $bTransSell;
            $bFinalSell = $bPkgSell > 0 ? $bPkgSell : $allServiceSellSum;
            $totalRevenue += $bFinalSell;

            $bookingDirectReceived += ((float) $b->total_received * $sellingRoe);

            $bCreatedAt = Carbon::parse($b->created_at);
            if ($bCreatedAt >= $weekAgo) {
                $revenueThisWeek += $bFinalSell;
            } elseif ($bCreatedAt->between($lastWeekStart, $lastWeekEnd)) {
                $revenueLastWeek += $bFinalSell;
            }
        }

        $revenueTrend = $revenueLastWeek > 0
            ? round((($revenueThisWeek - $revenueLastWeek) / $revenueLastWeek) * 100, 1)
            : 0;

        $grossProfit = $totalRevenue - $totalDirectCosts;

        // Query Overhead Expenses from ExpenseTransaction
        $expQuery = ExpenseTransaction::whereNull('deleted_at');
        if ($package === 'hajj') {
            $expQuery->where(function ($q) {
                $q->where('hajj_umrah', 'hajj')->orWhereNull('hajj_umrah');
            });
        } elseif ($package === 'umrah') {
            $expQuery->where('hajj_umrah', 'umrah');
        }

        $expQuery->where(function ($q) use ($year) {
            $q->where('year', $year)->orWhereYear('date', $year);
        });

        // Overhead only: DEBIT transactions excluding client credits & JV contra
        $expQuery->where('transaction_type', 'debit')
            ->where(function ($q) {
                $q->whereNotNull('expense_id')
                  ->orWhereIn('payee_type', ['expense', 'staff', 'general', 'office']);
            });

        $overheadTransactions = $expQuery->get();
        $totalExpensesPkr = 0;
        $totalExpensesSar = 0;
        $totalExpensesSarInPkr = 0;
        $expensesThisWeek = 0;
        $expensesLastWeek = 0;

        foreach ($overheadTransactions as $exp) {
            $isPkr = strtoupper($exp->currency ?? 'PKR') === 'PKR';
            $rate = (float) ($exp->exchange_rate > 0 ? $exp->exchange_rate : 75);
            $amt = (float) $exp->amount;
            $amtPkr = $isPkr ? $amt : ($amt * $rate);

            if ($isPkr) {
                $totalExpensesPkr += $amt;
            } else {
                $totalExpensesSar += $amt;
                $totalExpensesSarInPkr += ($amt * $rate);
            }

            $eDate = $exp->date ? Carbon::parse($exp->date) : Carbon::parse($exp->created_at);
            if ($eDate >= $weekAgo) {
                $expensesThisWeek += $amtPkr;
            } elseif ($eDate->between($lastWeekStart, $lastWeekEnd)) {
                $expensesLastWeek += $amtPkr;
            }
        }

        $totalOverheadExpenses = $totalExpensesPkr + $totalExpensesSarInPkr;
        // Total Operational Costs = Direct Booking Costs + Overhead Expenses
        $totalExpenses = $totalDirectCosts + $totalOverheadExpenses;

        $expensesTrend = $expensesLastWeek > 0
            ? round((($expensesThisWeek - $expensesLastWeek) / $expensesLastWeek) * 100, 1)
            : 0;

        // Net Operating Profit = Gross Profit - Overhead Expenses
        $profit = $grossProfit - $totalOverheadExpenses;
        $isProfit = $profit >= 0;

        // Total Received: Booking Advances + Confirmed Transactions + Client Expense Credits
        $bookingIds = $bookings->pluck('id')->toArray();
        $transactionsReceived = 0;
        $receivedThisWeek = 0;
        $receivedLastWeek = 0;

        if (!empty($bookingIds)) {
            $txList = Transaction::whereIn('booking_id', $bookingIds)
                ->where('status', 'confirmed')
                ->get();

            foreach ($txList as $tx) {
                $tAmt = (float) $tx->amount;
                $transactionsReceived += $tAmt;

                $tDate = $tx->payment_date ? Carbon::parse($tx->payment_date) : Carbon::parse($tx->created_at);
                if ($tDate >= $weekAgo) {
                    $receivedThisWeek += $tAmt;
                } elseif ($tDate->between($lastWeekStart, $lastWeekEnd)) {
                    $receivedLastWeek += $tAmt;
                }
            }
        }

        $clientCreditQuery = ExpenseTransaction::whereNull('deleted_at')
            ->where('payee_type', 'client')
            ->where('transaction_type', 'credit');

        if ($package === 'hajj') {
            $clientCreditQuery->where(function ($q) {
                $q->where('hajj_umrah', 'hajj')->orWhereNull('hajj_umrah');
            });
        } elseif ($package === 'umrah') {
            $clientCreditQuery->where('hajj_umrah', 'umrah');
        }

        $clientCreditQuery->where(function ($q) use ($year) {
            $q->where('year', $year)->orWhereYear('date', $year);
        });

        $expenseClientReceived = 0;
        foreach ($clientCreditQuery->get() as $cct) {
            $isPkr = strtoupper($cct->currency ?? 'PKR') === 'PKR';
            $rate = (float) ($cct->exchange_rate > 0 ? $cct->exchange_rate : 75);
            $amt = (float) $cct->amount;
            $amtPkr = $isPkr ? $amt : ($amt * $rate);
            $expenseClientReceived += $amtPkr;

            $cDate = $cct->date ? Carbon::parse($cct->date) : Carbon::parse($cct->created_at);
            if ($cDate >= $weekAgo) {
                $receivedThisWeek += $amtPkr;
            } elseif ($cDate->between($lastWeekStart, $lastWeekEnd)) {
                $receivedLastWeek += $amtPkr;
            }
        }

        $totalReceived = $bookingDirectReceived + $transactionsReceived + $expenseClientReceived;

        $receivedTrend = $receivedLastWeek > 0
            ? round((($receivedThisWeek - $receivedLastWeek) / $receivedLastWeek) * 100, 1)
            : 0;

        // Recent bookings
        $recentBookings = $bookings->sortByDesc(function ($b) {
            return $b->booking_date ?: ($b->created_at ? $b->created_at->toDateTimeString() : date('Y-m-d H:i:s'));
        })->take(6)->values();

        // Monthly bookings chart
        $monthlyData = collect(range(1, 12))->map(function ($m) use ($bookings) {
            return $bookings->filter(function ($b) use ($m) {
                $dt = Carbon::parse($b->booking_date ?: ($b->flight_booking_date ?: $b->created_at));
                return (int) $dt->format('n') === $m;
            })->count();
        })->values()->toArray();

        // Operational Reminders & Alerts
        $todayStr     = Carbon::today()->toDateString();
        $next7DaysStr = Carbon::today()->addDays(7)->toDateString();
        $next3DaysStr = Carbon::today()->addDays(3)->toDateString();

        $hotelCheckinReminders = BookingHotel::with(['booking.client'])
            ->whereNotNull('check_in')
            ->where('check_in', '>=', $todayStr)
            ->where('check_in', '<=', $next7DaysStr)
            ->orderBy('check_in')
            ->take(10)
            ->get();

        $hotelOptionDateReminders = BookingHotel::with(['booking.client', 'supplier'])
            ->whereNotNull('option_date')
            ->where('option_date', '>=', Carbon::today()->subDays(2)->toDateString())
            ->where('option_date', '<=', Carbon::today()->addDays(5)->toDateString())
            ->orderBy('option_date')
            ->take(10)
            ->get();

        $flightDepartureReminders = Booking::with(['client'])
            ->whereNotNull('departure_date')
            ->where('departure_date', '>=', $todayStr)
            ->where('departure_date', '<=', $next3DaysStr)
            ->orderBy('departure_date')
            ->orderBy('departure_time')
            ->take(10)
            ->get();

        $flightArrivalReminders = Booking::with(['client'])
            ->whereNotNull('arrival_date')
            ->where('arrival_date', '>=', $todayStr)
            ->where('arrival_date', '<=', $next3DaysStr)
            ->orderBy('arrival_date')
            ->orderBy('arrival_time')
            ->take(10)
            ->get();

        // Operational monthly counts
        $flightsThisMonth = $bookings->filter(function ($b) use ($now) {
            $dt = Carbon::parse($b->booking_date ?: ($b->flight_booking_date ?: $b->created_at));
            return (int) $dt->format('n') === (int) $now->month && ((float) $b->flight_charges > 0 || (float) $b->flight_cost > 0);
        })->count();

        $hotelsThisMonth = $bookings->filter(function ($b) use ($now) {
            $dt = Carbon::parse($b->booking_date ?: $b->created_at);
            return (int) $dt->format('n') === (int) $now->month && $b->hotels->count() > 0;
        })->count();

        $visasApproved = $bookings->filter(function ($b) {
            return $b->visas->count() > 0 || (float) $b->visa_charges > 0;
        })->count();

        return view('dashboard', compact(
            'package',
            'year',
            'availableYears',

            'totalUsers',
            'usersTrend',

            'totalDirectCosts',
            'totalHotelCost',
            'totalFlightCost',
            'totalVisaCost',
            'totalTransportCost',

            'totalOverheadExpenses',
            'totalExpenses',
            'expensesTrend',
            'totalExpensesPkr',
            'totalExpensesSar',
            'totalExpensesSarInPkr',

            'totalBookings',
            'bookingsTrend',

            'totalCompanies',
            'companiesTrend',

            'totalRevenue',
            'revenueTrend',

            'recentBookings',
            'monthlyData',

            'flightsThisMonth',
            'hotelsThisMonth',
            'visasApproved',

            'totalReceived',
            'receivedTrend',

            'grossProfit',
            'profit',
            'isProfit',

            'hotelCheckinReminders',
            'hotelOptionDateReminders',
            'flightDepartureReminders',
            'flightArrivalReminders'
        ));
    }
}
