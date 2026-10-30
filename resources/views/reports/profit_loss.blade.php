@extends('layout.master')

@section('title', 'Profit & Loss Financial Report')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css">

<style>
    .pl-stat-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .pl-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .pl-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 2.5px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 12px 20px;
        transition: all 0.2s;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #0f535e;
    }
    .nav-tabs-custom .nav-link.active {
        color: #0f535e;
        border-bottom-color: #0f535e;
        background: transparent;
        font-weight: 700;
    }
    @media print {
        .no-print, .app-sidebar-menu, .topbar-custom, .footer {
            display: none !important;
        }
        .content-page {
            margin: 0 !important;
            padding: 0 !important;
        }
        .content {
            padding: 0 !important;
        }
        .container-fluid {
            width: 100% !important;
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        .tab-pane {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Page Title & Top Action Bar -->
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom mb-3 no-print">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="mdi mdi-chart-areaspline text-primary me-1"></i> Profit & Loss (P&L) Financial Report
                    </h4>
                    <span class="text-muted fs-12">
                        Per Booking, Per Pilgrim profitability breakdown and monthly operational P&L summary.
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="mdi mdi-printer me-1"></i> Print Report
                    </button>
                    <a href="{{ route('report.profit-loss.pdf', request()->query()) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                        <i class="mdi mdi-file-pdf me-1"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportTableToCSV('profit_loss_report.csv')">
                        <i class="mdi mdi-file-excel me-1"></i> Export Excel
                    </button>
                    <a href="{{ route('report.profit-loss') }}" class="btn btn-light btn-sm">
                        <i class="mdi mdi-refresh me-1"></i> Reset Filters
                    </a>
                </div>
            </div>

            <!-- 6 KPI Stat Summary Cards -->
            <div class="row g-3 mb-4">
                <!-- 1. Gross Revenue -->
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="pl-stat-card" style="border-left: 4px solid #0f535e;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fs-11 text-uppercase fw-bold">Total Revenue</span>
                            <div class="pl-icon-box" style="background: #e6f3f5; color: #0f535e;">
                                <i class="mdi mdi-currency-usd"></i>
                            </div>
                        </div>
                        <h4 class="mb-1 fw-bold text-dark fs-16">PKR {{ number_format($totalSellingPkr, 0) }}</h4>
                        <div class="text-muted fs-11">
                            {{ count($processedBookings) }} Bookings · {{ $totalPaxCount }} Pax
                        </div>
                    </div>
                </div>

                <!-- 2. Direct Supplier Costs -->
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="pl-stat-card" style="border-left: 4px solid #ef4444;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fs-11 text-uppercase fw-bold">Direct Costs</span>
                            <div class="pl-icon-box" style="background: #fee2e2; color: #ef4444;">
                                <i class="mdi mdi-truck-delivery-outline"></i>
                            </div>
                        </div>
                        <h4 class="mb-1 fw-bold text-danger fs-16">PKR {{ number_format($totalDirectCostPkr, 0) }}</h4>
                        <div class="text-muted fs-11">
                            Hotels + Flights + Visas + Trans
                        </div>
                    </div>
                </div>

                <!-- 3. Gross Profit -->
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="pl-stat-card" style="border-left: 4px solid #10b981;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fs-11 text-uppercase fw-bold">Gross Profit</span>
                            <div class="pl-icon-box" style="background: #dcfce7; color: #10b981;">
                                <i class="mdi mdi-trending-up"></i>
                            </div>
                        </div>
                        <h4 class="mb-1 fw-bold text-success fs-16">PKR {{ number_format($totalGrossProfitPkr, 0) }}</h4>
                        <div class="text-success fw-bold fs-11">
                            {{ $overallGrossMarginPct }}% Gross Margin
                        </div>
                    </div>
                </div>

                <!-- 4. General Overhead Expenses -->
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="pl-stat-card" style="border-left: 4px solid #f59e0b;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fs-11 text-uppercase fw-bold">Overhead Expenses</span>
                            <div class="pl-icon-box" style="background: #fef3c7; color: #f59e0b;">
                                <i class="mdi mdi-cash-minus"></i>
                            </div>
                        </div>
                        <h4 class="mb-1 fw-bold text-warning fs-16">PKR {{ number_format($totalOverheadExpensesPkr, 0) }}</h4>
                        <div class="text-muted fs-11">
                            Office / Staff / Utilities
                        </div>
                    </div>
                </div>

                <!-- 5. Per Pax Profit -->
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="pl-stat-card" style="border-left: 4px solid #8b5cf6;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fs-11 text-uppercase fw-bold">Avg Profit / Pax</span>
                            <div class="pl-icon-box" style="background: #ede9fe; color: #8b5cf6;">
                                <i class="mdi mdi-account-star-outline"></i>
                            </div>
                        </div>
                        <h4 class="mb-1 fw-bold text-dark fs-16">PKR {{ number_format($avgProfitPerPax, 0) }}</h4>
                        <div class="text-muted fs-11">
                            Total {{ $totalPaxCount }} Pilgrims
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-header bg-white py-2 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-dark fs-13">
                            <i class="mdi mdi-filter-variant me-1 text-primary"></i> Filter & Search Records
                        </span>
                        <span class="fs-12 text-muted">Showing {{ count($processedBookings) }} Bookings</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('report.profit-loss') }}" class="row g-2 align-items-end">
                        <!-- Package Type -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-11 text-muted mb-1">Package Type</label>
                            <select name="package_type" class="form-select form-select-sm">
                                <option value="hajj" {{ $packageType == 'hajj' ? 'selected' : '' }}>Hajj</option>
                                <option value="umrah" {{ $packageType == 'umrah' ? 'selected' : '' }}>Umrah</option>
                                <option value="all" {{ $packageType == 'all' ? 'selected' : '' }}>All Packages</option>
                            </select>
                        </div>

                        <!-- Package Year -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-11 text-muted mb-1">Year</label>
                            <select name="year" class="form-select form-select-sm">
                                <option value="all" {{ $year == 'all' ? 'selected' : '' }}>All Years</option>
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}" {{ (string)$year === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Month -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-11 text-muted mb-1">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <option value="all" {{ $month == 'all' ? 'selected' : '' }}>All Months</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (string)$month === (string)$m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-11 text-muted mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-11 text-muted mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                        </div>

                        <!-- Client -->
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-11 text-muted mb-1">Client</label>
                            <select name="client_id" class="form-select form-select-sm">
                                <option value="">All Clients</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ (string)$clientId === (string)$c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Search text -->
                        <div class="col-md-9 col-sm-12">
                            <label class="form-label fs-11 text-muted mb-1">Keyword Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by Voucher #, Haji ID, Passport #, Client Name, Passenger Name...">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-md-3 col-sm-12 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                                <i class="mdi mdi-filter me-1"></i> Apply Filter
                            </button>
                            <a href="{{ route('report.profit-loss') }}" class="btn btn-sm btn-light">
                                <i class="mdi mdi-close"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Tabs Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-tabs nav-tabs-custom border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-3 px-4 fw-bold fs-13" data-bs-toggle="tab" href="#tab-per-booking" role="tab">
                                <i class="mdi mdi-account-cash me-1 text-primary"></i> Per Booking & Pilgrim Profitability ({{ count($processedBookings) }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3 px-4 fw-bold fs-13" data-bs-toggle="tab" href="#tab-monthly-summary" role="tab">
                                <i class="mdi mdi-calendar-month me-1 text-success"></i> Monthly & Annual P&L Summary ({{ count($monthlyData) }} Months)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3 px-4 fw-bold fs-13" data-bs-toggle="tab" href="#tab-formal-statement" role="tab">
                                <i class="mdi mdi-file-document-outline me-1 text-warning"></i> Profit & Loss Summary Statement
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">
                        
                        <!-- ════════════════ TAB 1: PER BOOKING / PERSON PROFITABILITY ════════════════ -->
                        <div class="tab-pane active" id="tab-per-booking" role="tabpanel">
                            
                            <!-- DEDICATED SALES & COST SUMMARY BREAKDOWN CARDS -->
                            <div class="row g-3 p-3 bg-light border-bottom">
                                <!-- 1. Sales / Revenue Summary Block -->
                                <div class="col-lg-6 col-12">
                                    <div class="card border border-primary border-opacity-25 shadow-none mb-0 bg-white h-100">
                                        <div class="card-header py-2 px-3 bg-soft-primary border-bottom border-primary border-opacity-10 d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-primary fs-13">
                                                <i class="mdi mdi-cash-multiple me-1"></i> Sales Revenue Breakdown Summary
                                            </span>
                                            <span class="badge bg-primary fs-12 px-2 py-1">
                                                Total Sale: PKR {{ number_format($totalSellingPkr, 0) }}
                                            </span>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row g-2 text-center">
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Hotel Sale</small>
                                                        <strong class="text-dark fs-12">PKR {{ number_format($totalHotelSellPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Flight Sale</small>
                                                        <strong class="text-dark fs-12">PKR {{ number_format($totalFlightSellPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Visa Sale</small>
                                                        <strong class="text-dark fs-12">PKR {{ number_format($totalVisaSellPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Trans Sale</small>
                                                        <strong class="text-dark fs-12">PKR {{ number_format($totalTransSellPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Direct Costs Summary Block -->
                                <div class="col-lg-6 col-12">
                                    <div class="card border border-danger border-opacity-25 shadow-none mb-0 bg-white h-100">
                                        <div class="card-header py-2 px-3 bg-soft-danger border-bottom border-danger border-opacity-10 d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-danger fs-13">
                                                <i class="mdi mdi-truck-delivery-outline me-1"></i> Direct Costs Breakdown Summary
                                            </span>
                                            <span class="badge bg-danger fs-12 px-2 py-1">
                                                Total Cost: PKR {{ number_format($totalDirectCostPkr, 0) }}
                                            </span>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row g-2 text-center">
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Hotel Cost</small>
                                                        <strong class="text-danger fs-12">PKR {{ number_format($totalHotelCostPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Flight Cost</small>
                                                        <strong class="text-danger fs-12">PKR {{ number_format($totalFlightCostPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Visa Cost</small>
                                                        <strong class="text-danger fs-12">PKR {{ number_format($totalVisaCostPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 col-6">
                                                    <div class="p-2 border rounded bg-light">
                                                        <small class="text-muted fs-11 text-uppercase d-block mb-1">Trans Cost</small>
                                                        <strong class="text-danger fs-12">PKR {{ number_format($totalTransCostPkr, 0) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="plBookingTable" class="table table-hover align-middle table-nowrap mb-0 fs-12">
                                    <thead class="fs-11">
                                        <!-- Level 1 Grouped Header -->
                                        <tr class="text-uppercase text-center border-bottom">
                                            <th rowspan="2" style="width: 35px; vertical-align: middle;" class="text-center bg-light">#</th>
                                            <th rowspan="2" style="vertical-align: middle;" class="text-start bg-light">Voucher / Haji ID</th>
                                            <th rowspan="2" style="vertical-align: middle;" class="text-start bg-light">Client / Package</th>
                                            <th rowspan="2" style="width: 55px; vertical-align: middle;" class="text-center bg-light">Pax</th>
                                            
                                            <!-- SALES / REVENUE GROUP -->
                                            <th colspan="5" class="py-2 text-center text-white" style="background: #0f535e; border-right: 2px solid #0a3d45;">
                                                <i class="mdi mdi-cash-multiple me-1"></i> Sales Revenue Breakdown (PKR)
                                            </th>
                                            
                                            <!-- DIRECT COSTS GROUP -->
                                            <th colspan="5" class="py-2 text-center text-white" style="background: #be123c; border-right: 2px solid #9f1239;">
                                                <i class="mdi mdi-truck-delivery-outline me-1"></i> Direct Costs Breakdown (PKR)
                                            </th>
                                            
                                            <!-- PROFITABILITY GROUP -->
                                            <th colspan="3" class="py-2 text-center text-white" style="background: #047857;">
                                                <i class="mdi mdi-chart-line me-1"></i> Profitability Summary
                                            </th>
                                            
                                            <th rowspan="2" style="width: 45px; vertical-align: middle;" class="text-center bg-light no-print">Actions</th>
                                        </tr>
                                        <!-- Level 2 Sub-Columns Header -->
                                        <tr class="text-uppercase fs-10 text-muted border-bottom">
                                            <!-- Under Sales -->
                                            <th class="text-end" style="background: #f0fdf4; color: #166534;">Hotel Sale</th>
                                            <th class="text-end" style="background: #f0fdf4; color: #166534;">Flight Sale</th>
                                            <th class="text-end" style="background: #f0fdf4; color: #166534;">Visa Sale</th>
                                            <th class="text-end" style="background: #f0fdf4; color: #166534;">Trans Sale</th>
                                            <th class="text-end fw-bold" style="background: #dcfce7; color: #14532d; border-right: 2px solid #cbd5e1;">Total Sale</th>
                                            
                                            <!-- Under Direct Costs -->
                                            <th class="text-end" style="background: #fff1f2; color: #9f1239;">Hotel Cost</th>
                                            <th class="text-end" style="background: #fff1f2; color: #9f1239;">Flight Cost</th>
                                            <th class="text-end" style="background: #fff1f2; color: #9f1239;">Visa Cost</th>
                                            <th class="text-end" style="background: #fff1f2; color: #9f1239;">Trans Cost</th>
                                            <th class="text-end fw-bold" style="background: #fee2e2; color: #991b1b; border-right: 2px solid #cbd5e1;">Total Cost</th>
                                            
                                            <!-- Under Profitability -->
                                            <th class="text-end fw-bold text-success" style="background: #f0fdf4;">Gross Profit</th>
                                            <th class="text-center fw-bold text-success" style="background: #f0fdf4;">Margin %</th>
                                            <th class="text-end fw-bold text-success" style="background: #f0fdf4;">Profit / Pax</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($processedBookings as $row)
                                            @php
                                                $b = $row->booking;
                                                $isProfit = $row->gross_profit_pkr >= 0;
                                            @endphp
                                            <tr style="{{ !$isProfit ? 'background-color: #fef2f2;' : '' }}">
                                                <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $b->id) }}" class="fw-bold text-primary">
                                                        #{{ $b->voucher_number ?? $b->id }}
                                                    </a>
                                                    @if($b->haji_id)
                                                        <br><span class="badge bg-soft-primary text-primary px-1 py-0 fs-10">
                                                            {{ $b->haji_id }}
                                                        </span>
                                                    @endif
                                                    @php
                                                        $dispDate = $b->relevant_date ?? ($b->booking_date ?: ($b->flight_booking_date ?: $b->hotels->first()?->booking_date));
                                                    @endphp
                                                    <br><small class="text-muted">{{ $dispDate ? date('d M Y', strtotime($dispDate)) : '—' }}</small>
                                                </td>
                                                <td>
                                                    <strong class="text-dark">{{ $b->client->name ?? ($b->company->name ?? 'Client') }}</strong>
                                                    <br>
                                                    <span class="badge bg-light text-dark fs-10">
                                                        {{ strtoupper($b->package_type ?? 'Umrah') }} {{ $b->package_year ?? '' }}
                                                    </span>
                                                    @if($b->currency && $b->currency !== 'PKR')
                                                        <span class="badge bg-soft-warning text-warning fs-10">{{ $b->currency }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary text-white rounded-pill px-2 py-1 fs-11">
                                                        {{ $row->pax }} Pax
                                                    </span>
                                                </td>
                                                
                                                <!-- SALES BREAKDOWN -->
                                                <td class="text-end text-muted">
                                                    {{ $row->hotel_sell_pkr > 0 ? 'PKR ' . number_format($row->hotel_sell_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $row->flight_sell_pkr > 0 ? 'PKR ' . number_format($row->flight_sell_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $row->visa_sell_pkr > 0 ? 'PKR ' . number_format($row->visa_sell_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $row->transport_sell_pkr > 0 ? 'PKR ' . number_format($row->transport_sell_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end fw-bold text-dark" style="background: #f8fafc; border-right: 2px solid #e2e8f0;">
                                                    PKR {{ number_format($row->selling_pkr, 0) }}
                                                    @if($row->currency !== 'PKR')
                                                        <br><small class="text-muted fs-10">({{ $row->currency }} {{ number_format($row->selling_raw, 0) }})</small>
                                                    @endif
                                                </td>

                                                <!-- DIRECT COSTS BREAKDOWN -->
                                                <td class="text-end text-muted">
                                                    {{ $row->hotel_cost_pkr > 0 ? 'PKR ' . number_format($row->hotel_cost_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $row->flight_cost_pkr > 0 ? 'PKR ' . number_format($row->flight_cost_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $row->visa_cost_pkr > 0 ? 'PKR ' . number_format($row->visa_cost_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $row->transport_cost_pkr > 0 ? 'PKR ' . number_format($row->transport_cost_pkr, 0) : '—' }}
                                                </td>
                                                <td class="text-end fw-semibold text-danger" style="background: #fef2f2; border-right: 2px solid #e2e8f0;">
                                                    PKR {{ number_format($row->direct_cost_pkr, 0) }}
                                                </td>

                                                <!-- PROFITABILITY -->
                                                <td class="text-end fw-bold {{ $isProfit ? 'text-success' : 'text-danger' }}">
                                                    PKR {{ number_format($row->gross_profit_pkr, 0) }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $isProfit ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fw-bold px-2 py-1 fs-11">
                                                        {{ $row->margin_pct }}%
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold {{ $isProfit ? 'text-success' : 'text-danger' }}">
                                                    PKR {{ number_format($row->per_pax_profit, 0) }}
                                                </td>
                                                <td class="text-center no-print">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end fs-12">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('booking.show', $b->id) }}">
                                                                    <i class="mdi mdi-eye text-primary me-1"></i> View Voucher
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('pilgrim.bulk-badges', $b->id) }}" target="_blank">
                                                                    <i class="mdi mdi-card-account-details-outline text-secondary me-1"></i> Print Pilgrim Badges
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('booking.edit', $b->id) }}">
                                                                    <i class="mdi mdi-square-edit-outline text-warning me-1"></i> Edit Booking
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Sub-Row showing Pilgrim list under booking -->
                                            @if($row->persons && $row->persons->count() > 1)
                                                <tr class="bg-light fs-11 text-muted" style="border-bottom: 1.5px solid #e2e8f0;">
                                                    <td></td>
                                                    <td colspan="17">
                                                        <span class="fw-bold text-dark"><i class="mdi mdi-account-multiple me-1"></i> Pilgrims Breakdown ({{ $row->persons->count() }}):</span>
                                                        @foreach($row->persons as $p)
                                                            <span class="badge bg-white text-dark border me-1 my-1">
                                                                {{ $p->full_name }} ({{ $p->passport_number ?? 'Pass: —' }}) · Room: {{ $p->room_number ?? '—' }} · Profit: <strong class="text-success">PKR {{ number_format($row->per_pax_profit, 0) }}</strong>
                                                            </span>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="18" class="text-center py-4 text-muted">
                                                    <i class="mdi mdi-information-outline fs-24 d-block mb-1"></i>
                                                    No bookings found matching the selected filter criteria.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light fw-bold fs-12 border-top">
                                        <tr>
                                            <td colspan="3" class="text-uppercase text-dark">Total Summary</td>
                                            <td class="text-center">{{ $totalPaxCount }} Pax</td>
                                            
                                            <!-- Sales Totals -->
                                            <td class="text-end text-success">PKR {{ number_format($totalHotelSellPkr, 0) }}</td>
                                            <td class="text-end text-success">PKR {{ number_format($totalFlightSellPkr, 0) }}</td>
                                            <td class="text-end text-success">PKR {{ number_format($totalVisaSellPkr, 0) }}</td>
                                            <td class="text-end text-success">PKR {{ number_format($totalTransSellPkr, 0) }}</td>
                                            <td class="text-end text-primary fw-bold" style="background: #e0f2fe; border-right: 2px solid #cbd5e1;">PKR {{ number_format($totalSellingPkr, 0) }}</td>

                                            <!-- Cost Totals -->
                                            <td class="text-end text-muted">PKR {{ number_format($totalHotelCostPkr, 0) }}</td>
                                            <td class="text-end text-muted">PKR {{ number_format($totalFlightCostPkr, 0) }}</td>
                                            <td class="text-end text-muted">PKR {{ number_format($totalVisaCostPkr, 0) }}</td>
                                            <td class="text-end text-muted">PKR {{ number_format($totalTransCostPkr, 0) }}</td>
                                            <td class="text-end text-danger fw-bold" style="background: #fee2e2; border-right: 2px solid #cbd5e1;">PKR {{ number_format($totalDirectCostPkr, 0) }}</td>

                                            <!-- Profit Totals -->
                                            <td class="text-end text-success">PKR {{ number_format($totalGrossProfitPkr, 0) }}</td>
                                            <td class="text-center text-success">{{ $overallGrossMarginPct }}%</td>
                                            <td class="text-end text-success">Avg PKR {{ number_format($avgProfitPerPax, 0) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- ════════════════ TAB 2: MONTHLY & ANNUAL FINANCIAL SUMMARY ════════════════ -->
                        <div class="tab-pane" id="tab-monthly-summary" role="tabpanel">
                            <div class="table-responsive">
                                <table id="plMonthlyTable" class="table table-hover align-middle table-nowrap mb-0 fs-12">
                                    <thead class="table-light text-uppercase fs-11 text-muted">
                                        <tr>
                                            <th style="width: 35px;" class="text-center">#</th>
                                            <th>Month / Year</th>
                                            <th class="text-center">Bookings</th>
                                            <th class="text-center">Total Pax</th>
                                            <th class="text-end">Gross Revenue (PKR)</th>
                                            <th class="text-end">Direct Hotel Cost</th>
                                            <th class="text-end">Direct Flight Cost</th>
                                            <th class="text-end">Direct Visa Cost</th>
                                            <th class="text-end">Direct Trans Cost</th>
                                            <th class="text-end">Total Direct Cost</th>
                                            <th class="text-end">Gross Profit</th>
                                            <th class="text-end">Overhead Expenses</th>
                                            <th class="text-end">Net Operating Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($monthlyData as $mKey => $m)
                                            @php
                                                $isMonthProfit = $m['net_profit_pkr'] >= 0;
                                            @endphp
                                            <tr style="{{ !$isMonthProfit ? 'background-color: #fef2f2;' : '' }}">
                                                <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong class="text-dark fs-13">{{ $m['month_label'] }}</strong>
                                                    <br><small class="text-muted">{{ $m['year_month'] }}</small>
                                                </td>
                                                <td class="text-center fw-bold">{{ $m['bookings_count'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary text-white rounded-pill px-2 py-1">
                                                        {{ $m['pax_count'] }} Pax
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold text-primary">
                                                    PKR {{ number_format($m['selling_pkr'], 0) }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $m['hotel_cost_pkr'] > 0 ? 'PKR ' . number_format($m['hotel_cost_pkr'], 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $m['flight_cost_pkr'] > 0 ? 'PKR ' . number_format($m['flight_cost_pkr'], 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $m['visa_cost_pkr'] > 0 ? 'PKR ' . number_format($m['visa_cost_pkr'], 0) : '—' }}
                                                </td>
                                                <td class="text-end text-muted">
                                                    {{ $m['transport_cost_pkr'] > 0 ? 'PKR ' . number_format($m['transport_cost_pkr'], 0) : '—' }}
                                                </td>
                                                <td class="text-end fw-semibold text-danger">
                                                    PKR {{ number_format($m['direct_cost_pkr'], 0) }}
                                                </td>
                                                <td class="text-end fw-bold text-success">
                                                    PKR {{ number_format($m['gross_profit_pkr'], 0) }}
                                                    <br><small class="text-muted fs-10">({{ $m['gross_margin_pct'] }}%)</small>
                                                </td>
                                                <td class="text-end fw-semibold text-warning">
                                                    {{ $m['expenses_pkr'] > 0 ? 'PKR ' . number_format($m['expenses_pkr'], 0) : '—' }}
                                                </td>
                                                <td class="text-end fw-bold {{ $isMonthProfit ? 'text-success' : 'text-danger' }} fs-13">
                                                    PKR {{ number_format($m['net_profit_pkr'], 0) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="13" class="text-center py-4 text-muted">
                                                    <i class="mdi mdi-information-outline fs-24 d-block mb-1"></i>
                                                    No financial transaction or booking records found for the period.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light fw-bold fs-12 border-top">
                                        <tr>
                                            <td colspan="2" class="text-uppercase text-dark">Grand Total</td>
                                            <td class="text-center">{{ count($processedBookings) }}</td>
                                            <td class="text-center">{{ $totalPaxCount }} Pax</td>
                                            <td class="text-end text-primary">PKR {{ number_format($totalSellingPkr, 0) }}</td>
                                            <td colspan="4"></td>
                                            <td class="text-end text-danger">PKR {{ number_format($totalDirectCostPkr, 0) }}</td>
                                            <td class="text-end text-success">PKR {{ number_format($totalGrossProfitPkr, 0) }}</td>
                                            <td class="text-end text-warning">PKR {{ number_format($totalOverheadExpensesPkr, 0) }}</td>
                                            <td class="text-end {{ $netOperatingProfitPkr >= 0 ? 'text-success' : 'text-danger' }}">
                                                PKR {{ number_format($netOperatingProfitPkr, 0) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- ════════════════ TAB 3: PROFIT & LOSS SUMMARY STATEMENT (FORMAL FORMAT) ════════════════ -->
                        <div class="tab-pane" id="tab-formal-statement" role="tabpanel">
                            
                            <!-- Action Bar (hidden on print) -->
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light border-bottom no-print">
                                <div class="fs-13 text-muted">
                                    <i class="mdi mdi-information-outline me-1"></i> Standard accounting profit & loss summary statement matching official report layout.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="printFormalStatement()">
                                        <i class="mdi mdi-printer me-1"></i> Print Formal Statement
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success fw-semibold" onclick="exportStatementToCSV()">
                                        <i class="mdi mdi-file-excel me-1"></i> Export Statement CSV
                                    </button>
                                </div>
                            </div>

                            <!-- FORMAL STATEMENT CONTAINER -->
                            <div class="p-4 p-md-5 bg-white" id="printableFormalStatement" style="max-width: 950px; margin: 0 auto; color: #2d3436; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                
                                <!-- COMPANY HEADER -->
                                <div class="row align-items-center mb-2">
                                    <div class="col-12">
                                        <h1 class="fw-bold mb-1" style="font-family: 'Impact', 'Arial Black', sans-serif; color: #d35400; font-size: 32px; letter-spacing: 0.5px;">
                                            Dua Travels and Tours
                                        </h1>
                                        <div class="fs-12 text-dark fw-semibold" style="line-height: 1.45;">
                                            Office # 01 Plot # A-14 Block - 4 Saadabad CHS, Gulistan-e-Johar Karachi<br>
                                            Phone #: 021-34774262 / 021-34774263, Cell #: 0321-8259646<br>
                                            Email: dua.travel2012@gmail.com / dua.travel2012@hotmail.com
                                        </div>
                                    </div>
                                </div>

                                <!-- ORANGE TITLE BAR -->
                                <div class="py-2 px-3 mb-2 text-end text-white fw-bold fs-16 text-uppercase" style="background-color: #d35400; letter-spacing: 0.5px;">
                                    Profit and Loss Summary
                                </div>

                                <!-- DATE RANGE BAR -->
                                <div class="py-2 px-3 mb-3 bg-light border-top border-bottom text-dark fw-bold fs-13">
                                    {{ $statementPeriod }}
                                </div>

                                <!-- STATEMENT TABLE -->
                                <table class="table table-bordered table-sm align-middle fs-13 mb-0 statement-table" style="border-collapse: collapse; border: 1px solid #ddd;">
                                    <thead style="background-color: #f2f2f2;">
                                        <tr class="fw-bold" style="color: #c0392b;">
                                            <th style="width: 150px;" class="py-2 px-3">Account Code</th>
                                            <th class="py-2 px-3">Account Title</th>
                                            <th style="width: 180px;" class="text-end py-2 px-3">Amount (PKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- 40-10-000 SYSTEM ACCOUNTS (INCOME) -->
                                        @if(count($systemIncomeItems) > 0)
                                        <tr style="background-color: #fbfbfb;">
                                            <td class="px-3 fw-bold" style="color: #d35400;">40-10-000</td>
                                            <td class="px-3 fw-bold" style="color: #d35400;">System Accounts</td>
                                            <td></td>
                                        </tr>
                                        @foreach($systemIncomeItems as $inc)
                                        <tr>
                                            <td class="px-4 text-muted">{{ $inc['code'] }}</td>
                                            <td class="px-4">{{ $inc['title'] }}</td>
                                            <td class="text-end px-3 fw-semibold">{{ number_format($inc['amount'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="fw-bold" style="background-color: #fafafa; border-bottom: 2px solid #ddd;">
                                            <td colspan="2" class="text-end px-3" style="color: #d35400;">Total System Accounts:</td>
                                            <td class="text-end px-3" style="color: #d35400;">{{ number_format($totalSystemIncomePkr, 2) }}</td>
                                        </tr>
                                        @endif

                                        <!-- 50-00-000 DIRECT SERVICE COSTS -->
                                        @if(count($directCostItems) > 0)
                                        <tr style="background-color: #fbfbfb;">
                                            <td class="px-3 fw-bold" style="color: #d35400;">50-00-000</td>
                                            <td class="px-3 fw-bold" style="color: #d35400;">Direct Service Costs</td>
                                            <td></td>
                                        </tr>
                                        @foreach($directCostItems as $dci)
                                        <tr>
                                            <td class="px-4 text-muted">{{ $dci['code'] }}</td>
                                            <td class="px-4">{{ $dci['title'] }}</td>
                                            <td class="text-end px-3">{{ number_format($dci['amount'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="fw-bold" style="background-color: #fafafa; border-bottom: 2px solid #ddd;">
                                            <td colspan="2" class="text-end px-3" style="color: #d35400;">Total Direct Service Costs:</td>
                                            <td class="text-end px-3 text-danger">{{ number_format($totalDirectCostPkr, 2) }}</td>
                                        </tr>
                                        @endif

                                        <!-- 50-01-000 UTILITIES EXPENSES -->
                                        @if(count($utilItems) > 0)
                                        <tr style="background-color: #fbfbfb;">
                                            <td class="px-3 fw-bold" style="color: #d35400;">50-01-000</td>
                                            <td class="px-3 fw-bold" style="color: #d35400;">Utilities Expenses</td>
                                            <td></td>
                                        </tr>
                                        @foreach($utilItems as $ui)
                                        <tr>
                                            <td class="px-4 text-muted">{{ $ui['code'] }}</td>
                                            <td class="px-4">{{ $ui['title'] }}</td>
                                            <td class="text-end px-3">{{ number_format($ui['amount'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="fw-bold" style="background-color: #fafafa; border-bottom: 2px solid #ddd;">
                                            <td colspan="2" class="text-end px-3" style="color: #d35400;">Total Utilities Expenses:</td>
                                            <td class="text-end px-3" style="color: #d35400;">{{ number_format($totalUtilitiesPkr, 2) }}</td>
                                        </tr>
                                        @endif

                                        <!-- 50-02-000 EXPENSE -->
                                        @if(count($genItems) > 0)
                                        <tr style="background-color: #fbfbfb;">
                                            <td class="px-3 fw-bold" style="color: #d35400;">50-02-000</td>
                                            <td class="px-3 fw-bold" style="color: #d35400;">EXPENSE / Operating Expenses</td>
                                            <td></td>
                                        </tr>
                                        @foreach($genItems as $gi)
                                        <tr>
                                            <td class="px-4 text-muted">{{ $gi['code'] }}</td>
                                            <td class="px-4">{{ $gi['title'] }}</td>
                                            <td class="text-end px-3">{{ number_format($gi['amount'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="fw-bold" style="background-color: #fafafa; border-bottom: 2px solid #ddd;">
                                            <td colspan="2" class="text-end px-3" style="color: #d35400;">Total EXPENSE:</td>
                                            <td class="text-end px-3" style="color: #d35400;">{{ number_format($totalGeneralPkr, 2) }}</td>
                                        </tr>
                                        @endif

                                        <!-- 55-01-000 SALARIES AND WAGES EXPENSE -->
                                        @if(count($salaryItems) > 0)
                                        <tr style="background-color: #fbfbfb;">
                                            <td class="px-3 fw-bold" style="color: #d35400;">55-01-000</td>
                                            <td class="px-3 fw-bold" style="color: #d35400;">Salaries and Wages Expense</td>
                                            <td></td>
                                        </tr>
                                        @foreach($salaryItems as $si)
                                        <tr>
                                            <td class="px-4 text-muted">{{ $si['code'] }}</td>
                                            <td class="px-4">{{ $si['title'] }}</td>
                                            <td class="text-end px-3">{{ number_format($si['amount'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="fw-bold" style="background-color: #fafafa; border-bottom: 2px solid #ddd;">
                                            <td colspan="2" class="text-end px-3" style="color: #d35400;">Total Salaries and Wages Expense:</td>
                                            <td class="text-end px-3" style="color: #d35400;">{{ number_format($totalSalariesPkr, 2) }}</td>
                                        </tr>
                                        @endif

                                        @if(count($utilItems) == 0 && count($genItems) == 0 && count($salaryItems) == 0)
                                        <tr style="background-color: #fbfbfb;">
                                            <td class="px-3 fw-bold" style="color: #d35400;">50-02-000</td>
                                            <td class="px-3 fw-bold" style="color: #d35400;">Operating & Administrative Expenses</td>
                                            <td class="text-end px-3 text-muted">0.00</td>
                                        </tr>
                                        @endif

                                        <!-- GRAND TOTAL ROW -->
                                        <tr class="fw-bold fs-14" style="background-color: #f1f2f6; border-top: 2px solid #333;">
                                            <td colspan="2" class="text-end px-3 text-uppercase" style="color: #d35400;">Grand Total Expenses:</td>
                                            <td class="text-end px-3 text-danger">PKR {{ number_format($statementTotalExpenses, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- BOTTOM SUMMARY BOX (EXACT DESIGN MATCH) -->
                                <div class="row justify-content-end mt-4">
                                    <div class="col-md-5 col-sm-8 col-12">
                                        <table class="table table-bordered table-sm mb-0 shadow-sm" style="border: 2px solid #bbb;">
                                            <tbody>
                                                <tr>
                                                    <td class="px-3 py-2 fw-bold" style="color: #d35400; width: 50%;">Total Income</td>
                                                    <td class="px-3 py-2 text-end fw-bold text-dark fs-14">
                                                        PKR {{ number_format($statementTotalIncome, 2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="px-3 py-2 fw-bold" style="color: #d35400;">Total Expenses</td>
                                                    <td class="px-3 py-2 text-end fw-bold text-dark fs-14">
                                                        PKR {{ number_format($statementTotalExpenses, 2) }}
                                                    </td>
                                                </tr>
                                                <tr style="background-color: #e8f5e9;">
                                                    <td class="px-3 py-2 fw-bold fs-14" style="color: #2e7d32;">Net Profit</td>
                                                    <td class="px-3 py-2 text-end fw-bold fs-15 {{ $statementNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                                        PKR {{ number_format($statementNetProfit, 2) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #printableFormalStatement, #printableFormalStatement * {
        visibility: visible !important;
    }
    #printableFormalStatement {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 10px !important;
        box-shadow: none !important;
        border: none !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<script>
function printFormalStatement() {
    let formalTabLink = document.querySelector('a[href="#tab-formal-statement"]');
    if (formalTabLink && typeof bootstrap !== 'undefined') {
        bootstrap.Tab.getOrCreateInstance(formalTabLink).show();
    }
    setTimeout(function() {
        window.print();
    }, 250);
}

function exportStatementToCSV() {
    let table = document.querySelector('#printableFormalStatement table');
    if (!table) return;
    let csv = [];
    let rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        if (row.length > 0) {
            csv.push(row.join(','));
        }
    }

    let csvFile = new Blob([csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
    let downloadLink = document.createElement('a');
    downloadLink.download = 'Profit_and_Loss_Summary_Statement.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function exportTableToCSV(filename) {
    let activeTab = document.querySelector('.tab-pane.active');
    let table = activeTab.querySelector('table');
    let csv = [];
    let rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            // Ignore actions column
            if (cols[j].classList.contains('no-print')) continue;
            let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        if (row.length > 0) {
            csv.push(row.join(','));
        }
    }

    let csvFile = new Blob([csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
    let downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
@endsection
