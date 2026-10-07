@extends('layout.master')
@section('title', 'Hotel Check-In & Service Operations Report')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .metric-card {
        border-radius: 12px;
        border: none;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .haji-badge {
        background-color: #0f535e;
        color: #fff;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .room-badge {
        background-color: #f1f5f9;
        color: #e51c23;
        border: 1px solid #fca5a5;
        font-weight: 800;
        font-size: 12px;
    }
    .nav-tabs-custom .nav-link {
        font-weight: 700;
        font-size: 13px;
        color: #4b5563;
        border: 1px solid transparent;
        border-radius: 8px 8px 0 0;
        padding: 10px 18px;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #0f535e;
        background-color: #f8fafc;
    }
    .nav-tabs-custom .nav-link.active {
        color: #0f535e;
        background-color: #ffffff;
        border-color: #e2e8f0 #e2e8f0 #ffffff;
        border-top: 3px solid #0f535e;
    }
    .service-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 1.25rem;
        background: #fff;
    }
    .service-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 18px;
        border-radius: 9px 9px 0 0;
    }
    @media print {
        .no-print, .left-side-menu, .navbar-custom, .footer, .nav-tabs-custom {
            display: none !important;
        }
        .content-page {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card, .service-card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
        table {
            font-size: 9px !important;
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

            {{-- Page Title & Quick Actions --}}
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2 flex-wrap gap-2 no-print">
                <div>
                    <h4 class="fs-18 fw-bold mb-1 text-primary">
                        <i class="mdi mdi-calendar-check me-1"></i> Check-In, Logistics & Operations Report
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Detailed service cards for Hotels, Flights, Transport & Visas with flexible Cost & Sale mode toggles.
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    {{-- View Mode Toggle Switcher --}}
                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                        <a href="{{ route('reports.checkin', array_merge(request()->query(), ['view_mode' => 'full'])) }}" 
                           class="btn {{ ($viewMode ?? 'full') === 'full' ? 'btn-primary' : 'btn-outline-primary' }}"
                           title="Show both Cost and Sale amounts with Net Margin">
                            <i class="mdi mdi-currency-usd me-1"></i> Full (Cost & Sale)
                        </a>
                        <a href="{{ route('reports.checkin', array_merge(request()->query(), ['view_mode' => 'cost_only'])) }}" 
                           class="btn {{ ($viewMode ?? '') === 'cost_only' ? 'btn-primary' : 'btn-outline-primary' }}"
                           title="Show only operational cost rates and totals (Without Sale)">
                            <i class="mdi mdi-cash-minus me-1"></i> Cost Only (Without Sale)
                        </a>
                        <a href="{{ route('reports.checkin', array_merge(request()->query(), ['view_mode' => 'sale_only'])) }}" 
                           class="btn {{ ($viewMode ?? '') === 'sale_only' ? 'btn-primary' : 'btn-outline-primary' }}"
                           title="Show only commercial sale rates and billing (Without Cost)">
                            <i class="mdi mdi-cash-plus me-1"></i> Sale Only (Without Cost)
                        </a>
                    </div>

                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportCheckInPDF()">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportCheckInExcel()">
                        <i class="mdi mdi-file-excel-box me-1"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-dark btn-sm" onclick="window.print()">
                        <i class="mdi mdi-printer me-1"></i> Print
                    </button>
                </div>
            </div>

            {{-- Summary KPI Metric Cards --}}
            <div class="row g-3 mb-3 no-print">
                {{-- Guests / Check-Ins Count --}}
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card metric-card p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-11 text-muted fw-bold text-uppercase">Total Guests / Check-Ins</span>
                                <h3 class="fs-22 fw-bold text-dark mb-0 mt-1">{{ number_format($totalCheckIns) }}</h3>
                                <small class="text-muted">{{ number_format($totalRoomsInReport) }} Rooms Allocated</small>
                            </div>
                            <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-account-group fs-22 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Financial Card 1 (Cost / Sale dependent on View Mode) --}}
                @if(($viewMode ?? 'full') !== 'sale_only')
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card metric-card p-3 h-100 border-start border-4 border-danger">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-11 text-muted fw-bold text-uppercase">Total Operational Cost</span>
                                <h3 class="fs-20 fw-bold text-danger mb-0 mt-1">Rs {{ number_format($totalCostPKR, 0) }}</h3>
                                @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                    <small class="text-muted fw-semibold">SAR {{ number_format($totalCostSAR, 2) }}</small>
                                @endif
                            </div>
                            <div class="avatar-sm bg-soft-danger rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-arrow-down-bold-circle fs-22 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Financial Card 2 (Sale) --}}
                @if(($viewMode ?? 'full') !== 'cost_only')
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card metric-card p-3 h-100 border-start border-4 border-info">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-11 text-muted fw-bold text-uppercase">Total Selling Revenue</span>
                                <h3 class="fs-20 fw-bold text-info mb-0 mt-1">Rs {{ number_format($totalSalePKR, 0) }}</h3>
                                @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                    <small class="text-muted fw-semibold">SAR {{ number_format($totalSaleSAR, 2) }}</small>
                                @endif
                            </div>
                            <div class="avatar-sm bg-soft-info rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-arrow-up-bold-circle fs-22 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Financial Card 3 (Net Margin) --}}
                @if(($viewMode ?? 'full') === 'full')
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card metric-card p-3 h-100 border-start border-4 {{ $totalMarginPKR >= 0 ? 'border-success' : 'border-danger' }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-11 text-muted fw-bold text-uppercase">Net Margin / Profit</span>
                                <h3 class="fs-20 fw-bold {{ $totalMarginPKR >= 0 ? 'text-success' : 'text-danger' }} mb-0 mt-1">
                                    Rs {{ number_format($totalMarginPKR, 0) }}
                                </h3>
                                @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                    <small class="text-success fw-semibold">SAR {{ number_format($totalMarginSAR, 2) }}</small>
                                @endif
                            </div>
                            <div class="avatar-sm bg-soft-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-trending-up fs-22 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                {{-- Schedule summary card when in cost_only or sale_only mode --}}
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card metric-card p-3 h-100 border-start border-4 border-warning">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-11 text-muted fw-bold text-uppercase">Check-In Schedule</span>
                                <div class="d-flex gap-2 mt-1">
                                    <span class="badge bg-danger fs-11">Today: {{ $todayCheckIns }}</span>
                                    <span class="badge bg-primary fs-11">Upcoming: {{ $upcomingCheckIns }}</span>
                                </div>
                                <small class="text-muted mt-1 d-block">Umrah: {{ $totalUmrah }} | Hajj: {{ $totalHujjaj }}</small>
                            </div>
                            <div class="avatar-sm bg-soft-warning rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-bell-ring fs-22 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Filter Form --}}
            <div class="card mb-3 border-0 shadow-sm no-print">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('reports.checkin') }}" id="reportFilterForm">
                        <input type="hidden" name="view_mode" value="{{ $viewMode ?? 'full' }}">
                        <input type="hidden" name="tab" value="{{ $activeTab ?? 'all' }}" id="activeTabInput">

                        <div class="row g-2 align-items-end">

                            {{-- Financial Mode Selector --}}
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-bold mb-1 text-primary">
                                    <i class="mdi mdi-eye-outline me-1"></i> Cost & Sale Mode
                                </label>
                                <select name="view_mode" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="full" {{ ($viewMode ?? 'full') === 'full' ? 'selected' : '' }}>💰 Full (Cost, Sale & Margin)</option>
                                    <option value="cost_only" {{ ($viewMode ?? '') === 'cost_only' ? 'selected' : '' }}>📉 Cost Only (Without Sale)</option>
                                    <option value="sale_only" {{ ($viewMode ?? '') === 'sale_only' ? 'selected' : '' }}>📈 Sale Only (Without Cost)</option>
                                </select>
                            </div>

                            {{-- Currency Filter --}}
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-bold mb-1 text-muted">Currency</label>
                                <select name="currency_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="all" {{ ($currencyFilter ?? 'all') === 'all' ? 'selected' : '' }}>🌟 Mix (PKR & SAR)</option>
                                    <option value="pkr" {{ ($currencyFilter ?? '') === 'pkr' ? 'selected' : '' }}>🇵🇰 PKR Only</option>
                                    <option value="sar" {{ ($currencyFilter ?? '') === 'sar' ? 'selected' : '' }}>🇸🇦 SAR Only</option>
                                </select>
                            </div>

                            {{-- Hotel Filter --}}
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-bold mb-1 text-muted">Hotel</label>
                                <select name="hotel_name" class="form-select form-select-sm">
                                    <option value="">-- All Hotels --</option>
                                    @foreach($hotels as $h)
                                        <option value="{{ $h->name }}" {{ ($hotelName == $h->name) ? 'selected' : '' }}>
                                            {{ $h->name }} @if($h->place) ({{ is_object($h->place) ? $h->place->value : $h->place }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- City Filter --}}
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-bold mb-1 text-muted">City / Location</label>
                                <select name="city" class="form-select form-select-sm">
                                    <option value="">-- All Cities --</option>
                                    <option value="makkah" {{ strtolower($city ?? '') === 'makkah' ? 'selected' : '' }}>Makkah</option>
                                    <option value="madinah" {{ strtolower($city ?? '') === 'madinah' ? 'selected' : '' }}>Madinah</option>
                                    <option value="azizia" {{ strtolower($city ?? '') === 'azizia' ? 'selected' : '' }}>Azizia</option>
                                    <option value="mina" {{ strtolower($city ?? '') === 'mina' ? 'selected' : '' }}>Mina / Arafat</option>
                                    <option value="jeddah" {{ strtolower($city ?? '') === 'jeddah' ? 'selected' : '' }}>Jeddah</option>
                                </select>
                            </div>

                            {{-- Package Type --}}
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-bold mb-1 text-muted">Package Type</label>
                                <select name="package_type" class="form-select form-select-sm">
                                    <option value="">-- All (Umrah & Hajj) --</option>
                                    <option value="umrah" {{ strtolower($packageType ?? '') === 'umrah' ? 'selected' : '' }}>Umrah</option>
                                    <option value="hajj" {{ strtolower($packageType ?? '') === 'hajj' ? 'selected' : '' }}>Hajj</option>
                                </select>
                            </div>

                            {{-- Date Range --}}
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-bold mb-1 text-muted">Check In From</label>
                                <input type="text" name="check_in_from" class="form-control form-control-sm flatpickr-date"
                                    placeholder="mm/dd/yyyy" value="{{ $checkInFrom }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-bold mb-1 text-muted">Check In To</label>
                                <input type="text" name="check_in_to" class="form-control form-control-sm flatpickr-date"
                                    placeholder="mm/dd/yyyy" value="{{ $checkInTo }}">
                            </div>

                            {{-- Search Bar --}}
                            <div class="col-md-8">
                                <label class="form-label fs-12 fw-bold mb-1 text-muted">Search Guest / Room / PNR / Route</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-magnify"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search by Guest Name, Haji ID, Room Number, Passport #, Flight PNR, Transport Route..."
                                        value="{{ $search }}">
                                </div>
                            </div>

                            {{-- Submit / Reset --}}
                            <div class="col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Filter Records">
                                    <i class="mdi mdi-filter me-1"></i> Apply
                                </button>
                                <a href="{{ route('reports.checkin') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- NAVIGATION TABS FOR SEPARATE SERVICE CARDS --}}
            <ul class="nav nav-tabs nav-tabs-custom mb-3 no-print" id="checkInTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link {{ ($activeTab ?? 'all') === 'all' ? 'active' : '' }}" 
                            id="tab-all-btn" data-bs-toggle="tab" data-bs-target="#tab-all" type="button" role="tab" onclick="setActiveTab('all')">
                        <i class="mdi mdi-view-dashboard-outline me-1"></i> 🌟 All Services Combined ({{ $records->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ ($activeTab ?? '') === 'hotels' ? 'active' : '' }}" 
                            id="tab-hotels-btn" data-bs-toggle="tab" data-bs-target="#tab-hotels" type="button" role="tab" onclick="setActiveTab('hotels')">
                        <i class="mdi mdi-hotel me-1 text-primary"></i> 🏨 Hotel Accommodations ({{ $hotelRecords->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ ($activeTab ?? '') === 'flights' ? 'active' : '' }}" 
                            id="tab-flights-btn" data-bs-toggle="tab" data-bs-target="#tab-flights" type="button" role="tab" onclick="setActiveTab('flights')">
                        <i class="mdi mdi-airplane me-1 text-info"></i> ✈️ Flight Schedule & Tickets ({{ $flightRecords->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ ($activeTab ?? '') === 'transports' ? 'active' : '' }}" 
                            id="tab-transports-btn" data-bs-toggle="tab" data-bs-target="#tab-transports" type="button" role="tab" onclick="setActiveTab('transports')">
                        <i class="mdi mdi-bus me-1 text-warning"></i> 🚐 Transportation Routes ({{ $transportRecords->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ ($activeTab ?? '') === 'visas' ? 'active' : '' }}" 
                            id="tab-visas-btn" data-bs-toggle="tab" data-bs-target="#tab-visas" type="button" role="tab" onclick="setActiveTab('visas')">
                        <i class="mdi mdi-card-account-details-outline me-1 text-success"></i> 🛂 Visa Processing ({{ $visaRecords->count() }})
                    </button>
                </li>
            </ul>

            {{-- TAB PANES CONTENT --}}
            <div class="tab-content" id="checkInTabContent">

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- TAB 1: ALL SERVICES COMBINED MASTER VIEW                       --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade {{ ($activeTab ?? 'all') === 'all' ? 'show active' : '' }}" id="tab-all" role="tabpanel">
                    <div class="service-card shadow-sm border-0 mb-4">
                        <div class="service-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fs-15 fw-bold text-dark mb-0">
                                    <i class="mdi mdi-layers-outline text-primary me-1"></i> Comprehensive Master Check-In & Logistics Register
                                </h5>
                                <small class="text-muted">
                                    Mode: <strong class="text-uppercase text-primary">{{ str_replace('_', ' ', $viewMode ?? 'full') }}</strong> &bull; Currency: <strong class="text-uppercase text-dark">{{ $currencyFilter }}</strong>
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-soft-primary text-primary px-2 py-1 fs-11">{{ $records->count() }} Total Records</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle fs-12 mb-0" id="tableMasterReport">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 35px;">#</th>
                                            <th>Guest & Haji ID</th>
                                            <th>Room & Contact</th>
                                            <th>Hotel Accommodation</th>
                                            <th>Check-In / Out</th>
                                            <th>Flight & PNR</th>
                                            <th>Transport Route</th>
                                            @if(($viewMode ?? 'full') !== 'sale_only')
                                                <th class="text-end bg-soft-danger">Total Cost</th>
                                            @endif
                                            @if(($viewMode ?? 'full') !== 'cost_only')
                                                <th class="text-end bg-soft-info">Total Sale</th>
                                            @endif
                                            @if(($viewMode ?? 'full') === 'full')
                                                <th class="text-end bg-soft-success">Net Margin</th>
                                            @endif
                                            <th class="text-center">Ref</th>
                                            <th class="text-center no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($records as $r)
                                            <tr>
                                                <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>

                                                {{-- Guest & Haji ID --}}
                                                <td>
                                                    <strong class="text-dark">{{ $r->guest_name }}</strong>
                                                    @if($r->haji_id && $r->haji_id !== '-')
                                                        <br><span class="badge haji-badge px-2 py-0 fs-10 mt-1">
                                                            <i class="mdi mdi-card-account-details-outline me-1"></i>{{ $r->haji_id }}
                                                        </span>
                                                    @endif
                                                    <br><small class="text-muted">Pass: {{ $r->passport_number }}</small>
                                                </td>

                                                {{-- Room & Contact --}}
                                                <td>
                                                    @if($r->room_number && $r->room_number !== '-')
                                                        <span class="badge room-badge px-2 py-1">
                                                            <i class="mdi mdi-door me-1"></i>{{ $r->room_number }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                    @if($r->phone && $r->phone !== '-')
                                                        <br><small class="text-muted"><i class="mdi mdi-phone fs-11"></i> {{ $r->phone }}</small>
                                                    @endif
                                                </td>

                                                {{-- Hotel Info --}}
                                                <td>
                                                    @if($r->hotel_name !== '—')
                                                        <strong class="text-primary">{{ $r->hotel_name }}</strong>
                                                        <br>
                                                        <span class="text-muted fs-11">{{ $r->city }} · {{ $r->room_type }}</span>
                                                        @if($r->no_of_rooms)
                                                            · <span class="badge bg-soft-info text-info">{{ $r->no_of_rooms }} Rm</span>
                                                        @endif
                                                        @if($r->hotel_supplier && $r->hotel_supplier !== '-')
                                                            <br><small class="text-secondary"><i class="mdi mdi-domain"></i> {{ $r->hotel_supplier }}</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>

                                                {{-- Stay Dates & Nights --}}
                                                <td>
                                                    @if($r->check_in)
                                                        <span class="fw-semibold text-dark">{{ date('d M Y', strtotime($r->check_in)) }}</span>
                                                        <br>
                                                        <span class="text-muted fs-11">to {{ $r->check_out ? date('d M Y', strtotime($r->check_out)) : '—' }}</span>
                                                        <br>
                                                        <span class="badge bg-light text-dark fs-10 fw-bold">{{ $r->nights }} Nights</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>

                                                {{-- Flight & PNR --}}
                                                <td>
                                                    @if($r->departure_flight !== '-' || $r->departure_pnr !== '-')
                                                        <div class="fs-11">
                                                            <strong>{{ $r->departure_airline }} {{ $r->departure_flight }}</strong>
                                                            @if($r->departure_pnr !== '-')
                                                                · <span class="badge bg-soft-primary text-primary">{{ $r->departure_pnr }}</span>
                                                            @endif
                                                            @if($r->departure_date)
                                                                <br><small class="text-muted">{{ date('d M Y', strtotime($r->departure_date)) }}</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-muted fs-11">—</span>
                                                    @endif
                                                </td>

                                                {{-- Transport Routes --}}
                                                <td>
                                                    @if($r->transport_routes && $r->transport_routes !== '-')
                                                        <span class="badge bg-soft-warning text-dark text-wrap fs-11 text-start" style="max-width: 180px;">
                                                            <i class="mdi mdi-bus me-1"></i>{{ $r->transport_routes }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted fs-11">—</span>
                                                    @endif
                                                </td>

                                                {{-- Cost Column --}}
                                                @if(($viewMode ?? 'full') !== 'sale_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-danger">SAR {{ number_format($r->cost_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($r->cost_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Sale Column --}}
                                                @if(($viewMode ?? 'full') !== 'cost_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-info">SAR {{ number_format($r->sale_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($r->sale_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Net Margin Column --}}
                                                @if(($viewMode ?? 'full') === 'full')
                                                <td class="text-end">
                                                    <span class="badge {{ $r->margin_pkr >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fs-11 fw-bold">
                                                        Rs {{ number_format($r->margin_pkr, 0) }}
                                                    </span>
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <br><small class="text-success fw-semibold">SAR {{ number_format($r->margin_sar, 2) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Ref --}}
                                                <td class="text-center">
                                                    <span class="badge {{ $r->package_type === 'Hajj' ? 'bg-warning text-dark' : 'bg-primary' }} fs-10 text-uppercase">
                                                        {{ $r->package_type }}
                                                    </span>
                                                    <br>
                                                    <a href="{{ route('booking.show', $r->booking_id) }}" class="fw-semibold text-primary fs-11" target="_blank">
                                                        {{ $r->reference_no }}
                                                    </a>
                                                </td>

                                                {{-- Action --}}
                                                <td class="text-center no-print">
                                                    <a href="{{ route('booking.show', $r->booking_id) }}" class="btn btn-xs btn-outline-info" title="View Booking" target="_blank">
                                                        <i class="mdi mdi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center py-5 text-muted">
                                                    <i class="mdi mdi-clipboard-text-off-outline fs-32 d-block mb-2 text-secondary"></i>
                                                    <h5>No Records Found</h5>
                                                    <p class="fs-13 text-muted mb-0">No bookings match the selected criteria.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- TAB 2: HOTEL ACCOMMODATIONS CARD                               --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade {{ ($activeTab ?? '') === 'hotels' ? 'show active' : '' }}" id="tab-hotels" role="tabpanel">
                    <div class="service-card shadow-sm border-0 mb-4">
                        <div class="service-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fs-15 fw-bold text-primary mb-0">
                                    <i class="mdi mdi-hotel me-1"></i> Hotel Accommodation & Room Allocations Card
                                </h5>
                                <small class="text-muted">Total Hotels Allocated: {{ $hotelRecords->count() }} | Rooms: {{ $totalRoomsInReport }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                @if(($viewMode ?? 'full') !== 'sale_only')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Hotel Cost: Rs {{ number_format($hotelCostTotalPKR, 0) }} (SAR {{ number_format($hotelCostTotalSAR, 2) }})
                                    </span>
                                @endif
                                @if(($viewMode ?? 'full') !== 'cost_only')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        Hotel Sale: Rs {{ number_format($hotelSaleTotalPKR, 0) }} (SAR {{ number_format($hotelSaleTotalSAR, 2) }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle fs-12 mb-0" id="tableHotelReport">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 35px;">#</th>
                                            <th>Booking Ref</th>
                                            <th>Client / Guest</th>
                                            <th>Hotel Name & City</th>
                                            <th>Room Details</th>
                                            <th>Check-In</th>
                                            <th>Check-Out</th>
                                            <th>Nights</th>
                                            <th>Supplier</th>
                                            @if(($viewMode ?? 'full') !== 'sale_only')
                                                <th class="text-end bg-soft-danger">Hotel Cost</th>
                                            @endif
                                            @if(($viewMode ?? 'full') !== 'cost_only')
                                                <th class="text-end bg-soft-info">Hotel Sale</th>
                                            @endif
                                            @if(($viewMode ?? 'full') === 'full')
                                                <th class="text-end bg-soft-success">Hotel Margin</th>
                                            @endif
                                            <th class="text-center no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hotelRecords as $hr)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $hr->booking_id) }}" class="fw-bold text-primary" target="_blank">
                                                        {{ $hr->reference_no }}
                                                    </a>
                                                    <br><span class="badge {{ $hr->package_type === 'Hajj' ? 'bg-warning text-dark' : 'bg-primary' }} fs-10">{{ $hr->package_type }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-dark">{{ $hr->client_name }}</strong>
                                                    @if($hr->haji_id && $hr->haji_id !== '-')
                                                        <br><span class="badge haji-badge px-2 py-0 fs-10 mt-1">{{ $hr->haji_id }}</span>
                                                    @endif
                                                    <br><small class="text-muted">Pass: {{ $hr->passport }}</small>
                                                </td>
                                                <td>
                                                    <strong class="text-dark">{{ $hr->hotel_name }}</strong>
                                                    <br><span class="badge bg-soft-secondary text-secondary fs-10">{{ $hr->city }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">{{ $hr->room_type }}</span>
                                                    @if($hr->room_number && $hr->room_number !== '-')
                                                        <span class="badge room-badge ms-1">{{ $hr->room_number }}</span>
                                                    @endif
                                                    <br><small class="text-muted">{{ $hr->no_of_rooms }} Room(s)</small>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $hr->check_in ? date('d M Y', strtotime($hr->check_in)) : '—' }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $hr->check_out ? date('d M Y', strtotime($hr->check_out)) : '—' }}</span>
                                                </td>
                                                <td class="text-center fw-bold">{{ $hr->no_of_nights }}</td>
                                                <td>
                                                    <small class="text-secondary"><i class="mdi mdi-domain me-1"></i>{{ $hr->supplier_name }}</small>
                                                </td>

                                                {{-- Hotel Cost --}}
                                                @if(($viewMode ?? 'full') !== 'sale_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-danger">SAR {{ number_format($hr->cost_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($hr->cost_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Hotel Sale --}}
                                                @if(($viewMode ?? 'full') !== 'cost_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-info">SAR {{ number_format($hr->sale_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($hr->sale_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Hotel Margin --}}
                                                @if(($viewMode ?? 'full') === 'full')
                                                <td class="text-end">
                                                    <span class="badge {{ $hr->margin_pkr >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fs-11 fw-bold">
                                                        Rs {{ number_format($hr->margin_pkr, 0) }}
                                                    </span>
                                                </td>
                                                @endif

                                                <td class="text-center no-print">
                                                    <a href="{{ route('booking.show', $hr->booking_id) }}" class="btn btn-xs btn-outline-info" target="_blank">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center py-4 text-muted">No hotel accommodation records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- TAB 3: FLIGHT SCHEDULE & TICKETING CARD                        --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade {{ ($activeTab ?? '') === 'flights' ? 'show active' : '' }}" id="tab-flights" role="tabpanel">
                    <div class="service-card shadow-sm border-0 mb-4">
                        <div class="service-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fs-15 fw-bold text-info mb-0">
                                    <i class="mdi mdi-airplane me-1"></i> Flight Schedules, PNRs & Ticketing Card
                                </h5>
                                <small class="text-muted">Total Flight Bookings: {{ $flightRecords->count() }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                @if(($viewMode ?? 'full') !== 'sale_only')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Flight Cost: Rs {{ number_format($flightCostTotalPKR, 0) }} (SAR {{ number_format($flightCostTotalSAR, 2) }})
                                    </span>
                                @endif
                                @if(($viewMode ?? 'full') !== 'cost_only')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        Flight Sale: Rs {{ number_format($flightSaleTotalPKR, 0) }} (SAR {{ number_format($flightSaleTotalSAR, 2) }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle fs-12 mb-0" id="tableFlightReport">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 35px;">#</th>
                                            <th>Booking Ref</th>
                                            <th>Client / Guest</th>
                                            <th>Airline & Flight #</th>
                                            <th>PNR</th>
                                            <th>Departure Date</th>
                                            <th>Arrival Date</th>
                                            <th>Supplier</th>
                                            @if(($viewMode ?? 'full') !== 'sale_only')
                                                <th class="text-end bg-soft-danger">Flight Cost</th>
                                            @endif
                                            @if(($viewMode ?? 'full') !== 'cost_only')
                                                <th class="text-end bg-soft-info">Flight Sale</th>
                                            @endif
                                            @if(($viewMode ?? 'full') === 'full')
                                                <th class="text-end bg-soft-success">Flight Margin</th>
                                            @endif
                                            <th class="text-center no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flightRecords as $fr)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $fr->booking_id) }}" class="fw-bold text-primary" target="_blank">
                                                        {{ $fr->reference_no }}
                                                    </a>
                                                    <br><span class="badge {{ $fr->package_type === 'Hajj' ? 'bg-warning text-dark' : 'bg-primary' }} fs-10">{{ $fr->package_type }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-dark">{{ $fr->client_name }}</strong>
                                                    @if($fr->haji_id && $fr->haji_id !== '-')
                                                        <br><span class="badge haji-badge px-2 py-0 fs-10 mt-1">{{ $fr->haji_id }}</span>
                                                    @endif
                                                    <br><small class="text-muted">Pax: {{ $fr->pax }} | Pass: {{ $fr->passport }}</small>
                                                </td>
                                                <td>
                                                    <strong class="text-primary">{{ $fr->departure_airline }}</strong>
                                                    <br><span class="badge bg-light text-dark border">{{ $fr->departure_flight }}</span>
                                                </td>
                                                <td>
                                                    @if($fr->departure_pnr !== '-')
                                                        <span class="badge bg-soft-primary text-primary fs-11 fw-bold">{{ $fr->departure_pnr }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $fr->departure_date ? date('d M Y', strtotime($fr->departure_date)) : '—' }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $fr->arrival_date ? date('d M Y', strtotime($fr->arrival_date)) : '—' }}</span>
                                                </td>
                                                <td>
                                                    <small class="text-secondary"><i class="mdi mdi-domain me-1"></i>{{ $fr->supplier_name }}</small>
                                                </td>

                                                {{-- Flight Cost --}}
                                                @if(($viewMode ?? 'full') !== 'sale_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-danger">SAR {{ number_format($fr->cost_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($fr->cost_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Flight Sale --}}
                                                @if(($viewMode ?? 'full') !== 'cost_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-info">SAR {{ number_format($fr->sale_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($fr->sale_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Flight Margin --}}
                                                @if(($viewMode ?? 'full') === 'full')
                                                <td class="text-end">
                                                    <span class="badge {{ $fr->margin_pkr >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fs-11 fw-bold">
                                                        Rs {{ number_format($fr->margin_pkr, 0) }}
                                                    </span>
                                                </td>
                                                @endif

                                                <td class="text-center no-print">
                                                    <a href="{{ route('booking.show', $fr->booking_id) }}" class="btn btn-xs btn-outline-info" target="_blank">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center py-4 text-muted">No flight records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- TAB 4: TRANSPORTATION & ROUTES CARD                            --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade {{ ($activeTab ?? '') === 'transports' ? 'show active' : '' }}" id="tab-transports" role="tabpanel">
                    <div class="service-card shadow-sm border-0 mb-4">
                        <div class="service-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fs-15 fw-bold text-warning mb-0">
                                    <i class="mdi mdi-bus me-1"></i> Transportation & Sector Routes Card
                                </h5>
                                <small class="text-muted">Total Transport Movements: {{ $transportRecords->count() }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                @if(($viewMode ?? 'full') !== 'sale_only')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Transport Cost: Rs {{ number_format($transportCostTotalPKR, 0) }} (SAR {{ number_format($transportCostTotalSAR, 2) }})
                                    </span>
                                @endif
                                @if(($viewMode ?? 'full') !== 'cost_only')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        Transport Sale: Rs {{ number_format($transportSaleTotalPKR, 0) }} (SAR {{ number_format($transportSaleTotalSAR, 2) }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle fs-12 mb-0" id="tableTransportReport">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 35px;">#</th>
                                            <th>Booking Ref</th>
                                            <th>Client / Guest</th>
                                            <th>Transport Route</th>
                                            <th>Vehicle Type</th>
                                            <th>Supplier</th>
                                            <th>Notes</th>
                                            @if(($viewMode ?? 'full') !== 'sale_only')
                                                <th class="text-end bg-soft-danger">Transport Cost</th>
                                            @endif
                                            @if(($viewMode ?? 'full') !== 'cost_only')
                                                <th class="text-end bg-soft-info">Transport Sale</th>
                                            @endif
                                            @if(($viewMode ?? 'full') === 'full')
                                                <th class="text-end bg-soft-success">Transport Margin</th>
                                            @endif
                                            <th class="text-center no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transportRecords as $tr)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $tr->booking_id) }}" class="fw-bold text-primary" target="_blank">
                                                        {{ $tr->reference_no }}
                                                    </a>
                                                    <br><span class="badge {{ $tr->package_type === 'Hajj' ? 'bg-warning text-dark' : 'bg-primary' }} fs-10">{{ $tr->package_type }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-dark">{{ $tr->client_name }}</strong>
                                                    @if($tr->haji_id && $tr->haji_id !== '-')
                                                        <br><span class="badge haji-badge px-2 py-0 fs-10 mt-1">{{ $tr->haji_id }}</span>
                                                    @endif
                                                    <br><small class="text-muted">Pax: {{ $tr->pax }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-soft-warning text-dark fs-12 fw-bold">
                                                        <i class="mdi mdi-map-marker-path me-1"></i>{{ $tr->route }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">{{ $tr->vehicle_type }}</span>
                                                </td>
                                                <td>
                                                    <small class="text-secondary"><i class="mdi mdi-domain me-1"></i>{{ $tr->supplier_name }}</small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $tr->notes ?: '—' }}</small>
                                                </td>

                                                {{-- Transport Cost --}}
                                                @if(($viewMode ?? 'full') !== 'sale_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-danger">SAR {{ number_format($tr->cost_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($tr->cost_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Transport Sale --}}
                                                @if(($viewMode ?? 'full') !== 'cost_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-info">SAR {{ number_format($tr->sale_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($tr->sale_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Transport Margin --}}
                                                @if(($viewMode ?? 'full') === 'full')
                                                <td class="text-end">
                                                    <span class="badge {{ $tr->margin_pkr >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fs-11 fw-bold">
                                                        Rs {{ number_format($tr->margin_pkr, 0) }}
                                                    </span>
                                                </td>
                                                @endif

                                                <td class="text-center no-print">
                                                    <a href="{{ route('booking.show', $tr->booking_id) }}" class="btn btn-xs btn-outline-info" target="_blank">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center py-4 text-muted">No transportation records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- TAB 5: VISA PROCESSING CARD                                   --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade {{ ($activeTab ?? '') === 'visas' ? 'show active' : '' }}" id="tab-visas" role="tabpanel">
                    <div class="service-card shadow-sm border-0 mb-4">
                        <div class="service-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fs-15 fw-bold text-success mb-0">
                                    <i class="mdi mdi-card-account-details-outline me-1"></i> Visa Applications & Processing Card
                                </h5>
                                <small class="text-muted">Total Visas Processed: {{ $visaRecords->count() }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                @if(($viewMode ?? 'full') !== 'sale_only')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Visa Cost: Rs {{ number_format($visaCostTotalPKR, 0) }} (SAR {{ number_format($visaCostTotalSAR, 2) }})
                                    </span>
                                @endif
                                @if(($viewMode ?? 'full') !== 'cost_only')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        Visa Sale: Rs {{ number_format($visaSaleTotalPKR, 0) }} (SAR {{ number_format($visaSaleTotalSAR, 2) }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle fs-12 mb-0" id="tableVisaReport">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 35px;">#</th>
                                            <th>Booking Ref</th>
                                            <th>Pilgrim / Applicant</th>
                                            <th>Passport #</th>
                                            <th>Processing Status</th>
                                            <th>Send To</th>
                                            <th>Supplier</th>
                                            @if(($viewMode ?? 'full') !== 'sale_only')
                                                <th class="text-end bg-soft-danger">Visa Cost</th>
                                            @endif
                                            @if(($viewMode ?? 'full') !== 'cost_only')
                                                <th class="text-end bg-soft-info">Visa Sale</th>
                                            @endif
                                            @if(($viewMode ?? 'full') === 'full')
                                                <th class="text-end bg-soft-success">Visa Margin</th>
                                            @endif
                                            <th class="text-center no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($visaRecords as $vr)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('booking.show', $vr->booking_id) }}" class="fw-bold text-primary" target="_blank">
                                                        {{ $vr->reference_no }}
                                                    </a>
                                                    <br><span class="badge {{ $vr->package_type === 'Hajj' ? 'bg-warning text-dark' : 'bg-primary' }} fs-10">{{ $vr->package_type }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-dark">{{ $vr->client_name }}</strong>
                                                    @if($vr->haji_id && $vr->haji_id !== '-')
                                                        <br><span class="badge haji-badge px-2 py-0 fs-10 mt-1">{{ $vr->haji_id }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <code class="text-dark fw-bold">{{ $vr->passport }}</code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-soft-success text-success fs-11">{{ $vr->visa_status }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ $vr->send_to }}</span>
                                                </td>
                                                <td>
                                                    <small class="text-secondary"><i class="mdi mdi-domain me-1"></i>{{ $vr->supplier_name }}</small>
                                                </td>

                                                {{-- Visa Cost --}}
                                                @if(($viewMode ?? 'full') !== 'sale_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-danger">SAR {{ number_format($vr->cost_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($vr->cost_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Visa Sale --}}
                                                @if(($viewMode ?? 'full') !== 'cost_only')
                                                <td class="text-end">
                                                    @if($currencyFilter === 'sar' || $currencyFilter === 'all')
                                                        <div class="fw-bold text-info">SAR {{ number_format($vr->sale_sar, 2) }}</div>
                                                    @endif
                                                    @if($currencyFilter === 'pkr' || $currencyFilter === 'all')
                                                        <small class="text-muted">Rs {{ number_format($vr->sale_pkr, 0) }}</small>
                                                    @endif
                                                </td>
                                                @endif

                                                {{-- Visa Margin --}}
                                                @if(($viewMode ?? 'full') === 'full')
                                                <td class="text-end">
                                                    <span class="badge {{ $vr->margin_pkr >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fs-11 fw-bold">
                                                        Rs {{ number_format($vr->margin_pkr, 0) }}
                                                    </span>
                                                </td>
                                                @endif

                                                <td class="text-center no-print">
                                                    <a href="{{ route('booking.show', $vr->booking_id) }}" class="btn btn-xs btn-outline-info" target="_blank">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center py-4 text-muted">No visa processing records found.</td>
                                            </tr>
                                        @endforelse
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

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "m/d/Y",
            allowInput: true
        });
    });

    function setActiveTab(tabName) {
        const input = document.getElementById('activeTabInput');
        if (input) input.value = tabName;
    }

    // ─── Export Active Card PDF ────────────────────────────────
    function exportCheckInPDF() {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            window.print();
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');

        const activePane = document.querySelector('.tab-pane.active');
        const activeTable = activePane ? activePane.querySelector('table') : document.getElementById('tableMasterReport');

        doc.setFontSize(15);
        doc.setTextColor(15, 83, 94);
        doc.text('Dua Travels - Check-In, Logistics & Operations Report', 14, 15);
        doc.setFontSize(9);
        doc.setTextColor(100);
        doc.text('Generated: ' + new Date().toLocaleString() + ' | Mode: {{ strtoupper(str_replace("_", " ", $viewMode ?? "full")) }} | Currency: {{ strtoupper($currencyFilter) }}', 14, 21);

        if (activeTable) {
            doc.autoTable({
                html: activeTable,
                startY: 25,
                theme: 'grid',
                headStyles: { fillColor: [15, 83, 94], textColor: 255, fontStyle: 'bold' },
                styles: { fontSize: 7, cellPadding: 2.5 }
            });
        }

        doc.save('CheckIn_Report_' + new Date().toISOString().slice(0,10) + '.pdf');
    }

    // ─── Export Active Card Excel ──────────────────────────────
    function exportCheckInExcel() {
        if (typeof XLSX === 'undefined') {
            alert('Excel library not loaded, using CSV print.');
            return;
        }
        const activePane = document.querySelector('.tab-pane.active');
        const activeTable = activePane ? activePane.querySelector('table') : document.getElementById('tableMasterReport');
        if (activeTable) {
            const wb = XLSX.utils.table_to_book(activeTable, { sheet: "CheckIn Report" });
            XLSX.writeFile(wb, 'CheckIn_Report_' + new Date().toISOString().slice(0,10) + '.xlsx');
        }
    }
</script>
@endsection
