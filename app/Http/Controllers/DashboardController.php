<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Company;
use App\Models\ExpenseTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function welcome()
    {
        $hajjCount   = Booking::where('package_type', 'hajj')->count();
        $umrahCount  = Booking::where('package_type', 'umrah')->count();

        $hajjRevenue  = Booking::where('package_type', 'hajj')->sum('total_amount');
        $umrahRevenue = Booking::where('package_type', 'umrah')->sum('total_amount');

        return view('welcome', compact('hajjCount', 'umrahCount', 'hajjRevenue', 'umrahRevenue'));
    }
    
    public function index(Request $request)
    {
        $package = $request->get('package', session('dashboard_package', 'hajj'));
        $year    = (int) $request->get('year', session('dashboard_year', Carbon::now()->year));

        session(['dashboard_package' => $package]);
        session(['dashboard_year'    => $year]);
        session(['dashboard_company_year' => $year]);

        $bookingYears = Booking::where('package_type', $package)
            ->selectRaw('package_year as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        $expenseYears = ExpenseTransaction::where('hajj_umrah', $package)
            ->selectRaw('year as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        $companyYears = Company::selectRaw('company_year as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        $availableYears = collect(array_merge($bookingYears, $expenseYears, $companyYears))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (!in_array($year, $availableYears) && count($availableYears) > 0) {
            $year = $availableYears[0];
            session(['dashboard_year' => $year]);
            session(['dashboard_company_year' => $year]);
        }

        if (!in_array(Carbon::now()->year, $availableYears)) {
            array_unshift($availableYears, Carbon::now()->year);
        }

        $now           = Carbon::now();
        $weekAgo       = Carbon::now()->subDays(7);
        $lastWeekStart = Carbon::now()->subDays(14);
        $lastWeekEnd   = Carbon::now()->subDays(7);

        $totalUsers = User::count();

        $usersThisWeek = User::where('created_at', '>=', $weekAgo)->count();

        $usersLastWeek = User::whereBetween('created_at', [
            $lastWeekStart,
            $lastWeekEnd
        ])->count();

        $usersTrend = $usersLastWeek > 0
            ? round((($usersThisWeek - $usersLastWeek) / $usersLastWeek) * 100, 1)
            : 0;

        $baseQuery = fn() => Booking::where('package_type', $package)
            ->where('package_year', $year);

        $totalBookings = $baseQuery()->count();

        $bookingsThisWeek = $baseQuery()
            ->where('created_at', '>=', $weekAgo)
            ->count();

        $bookingsLastWeek = $baseQuery()
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        $bookingsTrend = $bookingsLastWeek > 0
            ? round((($bookingsThisWeek - $bookingsLastWeek) / $bookingsLastWeek) * 100, 1)
            : 0;

        $companyQuery = fn() => Company::where('company_year', $year);

        $totalCompanies = $companyQuery()->count();

        $companiesThisWeek = $companyQuery()
            ->where('created_at', '>=', $weekAgo)
            ->count();

        $companiesLastWeek = $companyQuery()
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        $companiesTrend = $companiesLastWeek > 0
            ? round((($companiesThisWeek - $companiesLastWeek) / $companiesLastWeek) * 100, 1)
            : 0;
        $totalRevenue = $baseQuery()->sum('total_amount');

        $revenueThisWeek = $baseQuery()
            ->where('created_at', '>=', $weekAgo)
            ->sum('total_amount');

        $revenueLastWeek = $baseQuery()
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('total_amount');

        $revenueTrend = $revenueLastWeek > 0
            ? round((($revenueThisWeek - $revenueLastWeek) / $revenueLastWeek) * 100, 1)
            : 0;

        $expQuery = fn() => ExpenseTransaction::where('hajj_umrah', $package)
            ->where('year', $year);

        $totalExpensesPkr = $expQuery()->where('currency', 'PKR')->sum('amount');

        $totalExpensesSar = $expQuery()->where('currency', 'SAR')->sum('amount');

        $totalExpensesSarInPkr = $expQuery()->where('currency', 'SAR')
            ->selectRaw('SUM(amount * exchange_rate) as total')
            ->value('total') ?? 0;

        $totalExpenses = $totalExpensesPkr + $totalExpensesSarInPkr;

        $expensesThisWeek = $expQuery()
            ->where('date', '>=', $weekAgo->toDateString())
            ->sum('amount');

        $expensesLastWeek = $expQuery()
            ->whereBetween('date', [
                $lastWeekStart->toDateString(),
                $lastWeekEnd->toDateString()
            ])
            ->sum('amount');

        $expensesTrend = $expensesLastWeek > 0
            ? round((($expensesThisWeek - $expensesLastWeek) / $expensesLastWeek) * 100, 1)
            : 0;

        $recentBookings = $baseQuery()
            ->with(['client', 'company'])
            ->latest()
            ->take(6)
            ->get();

        $monthlyData = $this->monthlyBookings($year, $package);

        $flightsThisMonth = $baseQuery()
            ->whereMonth('created_at', $now->month)
            ->where('flight_charges', '>', 0)
            ->count();

        $hotelsThisMonth = $baseQuery()
            ->whereMonth('created_at', $now->month)
            ->whereHas('hotels')
            ->count();

        $visasApproved = $baseQuery()
            ->where('visa_charges', '>', 0)
            ->count();

        $transactionsReceived = \App\Models\Transaction::whereHas('booking', function ($q) use ($package, $year) {
            $q->where('package_type', $package)
                ->where('package_year', $year);
        })
            ->where('status', 'confirmed')
            ->sum('amount');

        $bookingDirectReceived = $baseQuery()->sum('total_received');

        $totalReceived = $transactionsReceived + $bookingDirectReceived;

        $profit   = $totalRevenue - $totalExpenses;
        $isProfit = $profit >= 0;

        $receivedThisWeek = \App\Models\Transaction::where('status', 'confirmed')
            ->whereHas('booking', function ($q) use ($package, $year) {
                $q->where('package_type', $package)
                    ->where('package_year', $year);
            })
            ->where('payment_date', '>=', $weekAgo->toDateString())
            ->sum('amount');

        $receivedLastWeek = \App\Models\Transaction::where('status', 'confirmed')
            ->whereHas('booking', function ($q) use ($package, $year) {
                $q->where('package_type', $package)
                    ->where('package_year', $year);
            })
            ->whereBetween('payment_date', [
                $lastWeekStart->toDateString(),
                $lastWeekEnd->toDateString()
            ])
            ->sum('amount');

        $receivedTrend = $receivedLastWeek > 0
            ? round((($receivedThisWeek - $receivedLastWeek) / $receivedLastWeek) * 100, 1)
            : 0;

        return view('dashboard', compact(
            'package',
            'year',
            'availableYears',

            'totalUsers',
            'usersTrend',

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

            'profit',
            'isProfit',
        ));
    }

    private function monthlyBookings(int $year, string $packageType): array
    {
        $rows = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->where('package_year', $year)
            ->where('package_type', $packageType)
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))
            ->map(fn($m) => $rows[$m] ?? 0)
            ->values()
            ->toArray();
    }
}
