@extends('layout.master')

@section('title', 'Supplier Ledger - ' . $supplier->name)

@section('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
    .ledger-stat-card {
        border-radius: 8px;
        transition: transform 0.15s ease;
    }
    .ledger-stat-card:hover {
        transform: translateY(-2px);
    }
    .statement-table thead th {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #64748b;
        background: #f8fafc;
        vertical-align: middle;
    }
    .statement-table tbody td {
        font-size: 12px;
        vertical-align: middle;
    }
    .opening-row {
        background: #fffbeb !important;
        font-weight: 600;
    }

    .btn-currency-toggle {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 50rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        user-select: none;
    }
    .btn-check:checked + .btn-pkr-toggle {
        background-color: #198754 !important;
        border-color: #198754 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(25, 135, 84, 0.35) !important;
    }
    .btn-check:not(:checked) + .btn-pkr-toggle {
        background-color: #ffffff !important;
        border-color: #86efac !important;
        color: #15803d !important;
        opacity: 0.7;
    }
    .btn-check:not(:checked) + .btn-pkr-toggle:hover {
        opacity: 1;
        background-color: #f0fdf4 !important;
    }
    .btn-check:checked + .btn-sar-toggle {
        background-color: #f59e0b !important;
        border-color: #d97706 !important;
        color: #1f2937 !important;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35) !important;
    }
    .btn-check:not(:checked) + .btn-sar-toggle {
        background-color: #ffffff !important;
        border-color: #fde68a !important;
        color: #b45309 !important;
        opacity: 0.7;
    }
    .btn-check:not(:checked) + .btn-sar-toggle:hover {
        opacity: 1;
        background-color: #fffbeb !important;
    }

    @media print {
        .app-sidebar-menu,
        .topbar-custom,
        #sidebar-menu,
        .left-side-menu,
        .navbar-custom,
        .footer,
        .page-title-box,
        .no-print {
            display: none !important;
        }
        body {
            background: #fff !important;
            font-size: 11px !important;
            color: #000 !important;
        }
        .content-page,
        .content,
        .container-fluid {
            margin: 0 !important;
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            margin-bottom: 10px !important;
            page-break-inside: avoid;
        }
        /* CRITICAL FIX: Only print the currently active tab-pane! Do NOT stack both! */
        .tab-content > .tab-pane {
            display: none !important;
        }
        .tab-content > .tab-pane.active {
            display: block !important;
            opacity: 1 !important;
        }
        .table {
            width: 100% !important;
            font-size: 10px !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            padding: 4px 6px !important;
            border: 1px solid #cbd5e1 !important;
        }
        .col-roe.d-none {
            display: none !important;
        }
        .col-pkr.d-none {
            display: none !important;
        }
        .col-sar.d-none {
            display: none !important;
        }
        .d-print-block {
            display: block !important;
        }
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- TOP BAR -->
            <div class="py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 no-print">
                <div>
                    <h4 class="fs-18 fw-semibold m-0 text-dark">
                        <i class="mdi mdi-truck-delivery text-primary me-1"></i> Supplier Ledger — {{ $supplier->name }}
                    </h4>
                    <p class="text-muted fs-12 mb-0">Complete statement of cost payables across all bookings and payments made.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('expense.transaction.create') }}?payee_type=supplier&supplier_id={{ $supplier->id }}" class="btn btn-success btn-sm">
                        <i class="mdi mdi-cash-multiple me-1"></i> + Pay Supplier
                    </a>
                    <button id="exportExcel" onclick="downloadLedgerExcel()" class="btn btn-success btn-sm shadow-sm">
                        <i class="mdi mdi-file-excel me-1"></i> Export Excel
                    </button>
                    <button id="exportPDF" onclick="downloadLedgerPDF()" class="btn btn-danger btn-sm shadow-sm">
                        <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                    </button>
                    <button onclick="window.print()" class="btn btn-secondary btn-sm shadow-sm">
                        <i class="mdi mdi-printer me-1"></i> Print
                    </button>
                    <a href="{{ route('supplier.ledger.filter') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-filter me-1"></i> Filter
                    </a>
                    <a href="{{ route('supplier.index') }}" class="btn btn-dark btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            <!-- LEDGER CONTENT (for Print and PDF) -->
            <div id="ledgerBox">

                <!-- PRINT HEADER ONLY -->
                <div class="d-none d-print-block mb-3 p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1" style="color: #1e293b;">DUA TRAVEL &amp; TOURS</h3>
                            <p class="text-muted mb-0 fs-12">Hajj, Umrah &amp; Tourism Accounting System</p>
                        </div>
                        <div class="text-end fs-12">
                            <div><strong>Printed On:</strong> {{ now()->format('d M, Y h:i A') }}</div>
                            <div><strong>Statement:</strong> {{ $supplier->name }} (Supplier Ledger)</div>
                            @if($fromDate || $toDate)
                                <div><strong>Period:</strong> {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : 'Start' }} to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M, Y') : 'Today' }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SUPPLIER INFO CARD -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4 class="fw-bold text-dark mb-1">{{ $supplier->name }}</h4>
                                @if($supplier->company_name)
                                    <div class="text-muted fs-13 mb-1"><i class="mdi mdi-domain me-1"></i> {{ $supplier->company_name }}</div>
                                @endif
                                <div class="fs-12 text-muted">
                                    <span class="badge bg-soft-primary text-primary text-capitalize me-2">{{ $supplier->service_type ?? 'All Services' }}</span>
                                    @if($supplier->phone) <span><i class="mdi mdi-phone me-1"></i>{{ $supplier->phone }}</span> @endif
                                    @if($supplier->email) <span class="ms-3"><i class="mdi mdi-email me-1"></i>{{ $supplier->email }}</span> @endif
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                @if($supplier->city || $supplier->country)
                                    <div class="fs-12 text-muted mb-1">
                                        <i class="mdi mdi-map-marker me-1"></i>{{ $supplier->city }}{{ $supplier->city && $supplier->country ? ', ' : '' }}{{ $supplier->country }}
                                    </div>
                                @endif
                                <div class="fs-12 text-muted">
                                    <strong>Statement Date:</strong> {{ now()->format('d M, Y h:i A') }}
                                </div>
                                @if($fromDate && $toDate)
                                    <div class="badge bg-soft-info text-info mt-1">
                                        Filtered: {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUMMARY METRIC CARDS -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm ledger-stat-card bg-light">
                            <div class="card-body p-3 text-center">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Opening Balance</div>
                                <div class="fs-16 fw-bold text-dark mt-1 col-pkr">PKR {{ number_format(abs($openingBalancePkr ?? $openingBalance), 2) }} <small class="badge {{ ($openingBalancePkr ?? $openingBalance) >= 0 ? 'bg-danger' : 'bg-success' }} text-white" style="font-size: 10px;">{{ ($openingBalancePkr ?? $openingBalance) >= 0 ? 'Cr' : 'Dr' }}</small></div>
                                @if(($openingBalanceSar ?? 0) != 0)
                                    <div class="badge bg-secondary text-white fs-10 mt-1 col-sar">SAR {{ number_format(abs($openingBalanceSar), 2) }} {{ ($openingBalanceSar ?? 0) >= 0 ? 'Cr' : 'Dr' }}</div>
                                @endif
                                <small class="text-muted fs-11 d-block mt-1">Initial balance</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm ledger-stat-card bg-soft-primary">
                            <div class="card-body p-3 text-center">
                                <div class="text-primary fs-11 fw-bold text-uppercase">Total Cost (Payable)</div>
                                <div class="fs-16 fw-bold text-primary mt-1 col-pkr">PKR {{ number_format($totalPayablePkr ?? $totalPayable, 2) }}</div>
                                @if(($totalPayableSar ?? 0) > 0)
                                    <div class="badge bg-warning text-dark fs-10 mt-1 col-sar">SAR {{ number_format($totalPayableSar, 2) }}</div>
                                @endif
                                <small class="text-muted fs-11 d-block mt-1">{{ $hotelBookings->count() + $flightBookings->count() + $visaBookings->count() + $transportBookings->count() }} booking services</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm ledger-stat-card bg-soft-success">
                            <div class="card-body p-3 text-center">
                                <div class="text-success fs-11 fw-bold text-uppercase">Total Paid</div>
                                <div class="fs-16 fw-bold text-success mt-1 col-pkr">PKR {{ number_format($totalPaidPkr ?? $totalPaid, 2) }}</div>
                                @if(($totalPaidSar ?? 0) > 0)
                                    <div class="badge bg-soft-success text-success border fs-10 mt-1 col-sar">SAR {{ number_format($totalPaidSar, 2) }}</div>
                                @endif
                                <small class="text-muted fs-11 d-block mt-1">{{ $transactions->count() }} payments made</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm ledger-stat-card {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'bg-soft-danger' : 'bg-soft-success' }}">
                            <div class="card-body p-3 text-center">
                                <div class="{{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'text-danger' : 'text-success' }} fs-11 fw-bold text-uppercase">
                                    {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'Net Balance Due' : (($balanceDuePkr ?? $balanceDue) < 0 ? 'Advance Paid' : 'Balance Cleared') }}
                                </div>
                                <div class="fs-16 fw-bold {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'text-danger' : 'text-success' }} mt-1 col-pkr">
                                    PKR {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }}
                                </div>
                                @if(isset($balanceDueSar) && abs($balanceDueSar) > 0)
                                    <div class="badge {{ $balanceDueSar > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} fs-10 mt-1 col-sar">
                                        SAR {{ number_format(abs($balanceDueSar), 2) }} {{ $balanceDueSar > 0 ? 'Due' : 'Advance' }}
                                    </div>
                                @endif
                                <small class="text-muted fs-11 d-block mt-1">{{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'Payable to supplier' : 'Up to date' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VIEW SWITCHER TABS (TWO TYPES OF VIEWS) -->
                <div class="card border-0 shadow-sm mb-3 no-print">
                    <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <ul class="nav nav-pills" id="supplierLedgerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold fs-13 px-3 py-1-5" id="breakdown-tab" data-bs-toggle="pill" data-bs-target="#view-breakdown" type="button" role="tab" aria-controls="view-breakdown" aria-selected="true">
                                    <i class="mdi mdi-format-list-bulleted-type me-1"></i> View 1: Services Breakdown (Payables by Category)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold fs-13 px-3 py-1-5" id="statement-tab" data-bs-toggle="pill" data-bs-target="#view-statement" type="button" role="tab" aria-controls="view-statement" aria-selected="false">
                                    <i class="mdi mdi-scale-balance me-1"></i> View 2: Statement of Account (Debit / Credit / Balance View)
                                </button>
                            </li>
                        </ul>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <input type="checkbox" class="btn-check" id="toggleSupplierPkrColumns" checked autocomplete="off" onchange="toggleSupplierPkr(this.checked)">
                            <label class="btn-currency-toggle btn-pkr-toggle shadow-sm border" for="toggleSupplierPkrColumns" id="lblToggleSupplierPkr" title="Toggle PKR Columns in Ledger">
                                <i class="mdi mdi-check-circle fs-14" id="iconToggleSupplierPkr"></i> Show PKR
                            </label>

                            <input type="checkbox" class="btn-check" id="toggleSupplierSarColumns" checked autocomplete="off" onchange="toggleSupplierSar(this.checked)">
                            <label class="btn-currency-toggle btn-sar-toggle shadow-sm border" for="toggleSupplierSarColumns" id="lblToggleSupplierSar" title="Toggle SAR Columns in Ledger">
                                <i class="mdi mdi-check-circle fs-14" id="iconToggleSupplierSar"></i> Show SAR
                            </label>

                            <input type="checkbox" class="btn-check" id="toggleSupplierRoeColumns" checked autocomplete="off" onchange="toggleSupplierRoe(this.checked)">
                            <label class="btn-currency-toggle btn-roe-toggle shadow-sm border" for="toggleSupplierRoeColumns" id="lblToggleSupplierRoe" title="Toggle ROE Column in Ledger">
                                <i class="mdi mdi-check-circle fs-14" id="iconToggleSupplierRoe"></i> Show ROE
                            </label>

                            @if(($totalPayableSar ?? 0) > 0 || ($totalPaidSar ?? 0) > 0)
                                <span class="badge bg-warning text-dark px-3 py-1-5 fs-12 col-sar shadow-sm border border-warning rounded-pill">
                                    <i class="mdi mdi-cash-multiple me-1"></i>SAR Balance: {{ number_format(abs($balanceDueSar ?? 0), 2) }} {{ ($balanceDueSar ?? 0) > 0 ? 'Cr' : 'Dr' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="supplierLedgerTabContent">

                    <!-- ========================================== -->
                    <!-- VIEW 1: SERVICES BREAKDOWN VIEW (Payables) -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade show active" id="view-breakdown" role="tabpanel" aria-labelledby="breakdown-tab">

                        <!-- SERVICES BREAKDOWN SUMMARY -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <h6 class="fs-13 fw-bold text-dark mb-2">Cost Breakdown by Service:</h6>
                                <div class="row g-2 text-center fs-12">
                                    <div class="col-3 border-end">
                                        <span class="text-muted d-block">Hotels Cost:</span>
                                        <strong class="text-dark">PKR {{ number_format($hotelTotal, 2) }}</strong>
                                        @if(($hotelTotalSar ?? 0) > 0)
                                            <div class="badge bg-warning text-dark fs-10 mt-1 d-inline-block col-sar">SAR {{ number_format($hotelTotalSar, 2) }}</div>
                                        @endif
                                        <span class="text-muted fs-11 d-block">({{ $hotelBookings->count() }} items)</span>
                                    </div>
                                    <div class="col-3 border-end">
                                        <span class="text-muted d-block">Flights Cost:</span>
                                        <strong class="text-dark">PKR {{ number_format($flightTotal, 2) }}</strong>
                                        @if(($flightTotalSar ?? 0) > 0)
                                            <div class="badge bg-warning text-dark fs-10 mt-1 d-inline-block col-sar">SAR {{ number_format($flightTotalSar, 2) }}</div>
                                        @endif
                                        <span class="text-muted fs-11 d-block">({{ $flightBookings->count() }} items)</span>
                                    </div>
                                    <div class="col-3 border-end">
                                        <span class="text-muted d-block">Visas Cost:</span>
                                        <strong class="text-dark">PKR {{ number_format($visaTotal, 2) }}</strong>
                                        @if(($visaTotalSar ?? 0) > 0)
                                            <div class="badge bg-warning text-dark fs-10 mt-1 d-inline-block col-sar">SAR {{ number_format($visaTotalSar, 2) }}</div>
                                        @endif
                                        <span class="text-muted fs-11 d-block">({{ $visaBookings->count() }} items)</span>
                                    </div>
                                    <div class="col-3">
                                        <span class="text-muted d-block">Transports Cost:</span>
                                        <strong class="text-dark">PKR {{ number_format($transportTotal, 2) }}</strong>
                                        @if(($transportTotalSar ?? 0) > 0)
                                            <div class="badge bg-warning text-dark fs-10 mt-1 d-inline-block col-sar">SAR {{ number_format($transportTotalSar, 2) }}</div>
                                        @endif
                                        <span class="text-muted fs-11 d-block">({{ $transportBookings->count() }} items)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 1. HOTEL BOOKINGS TABLE -->
                        @if($hotelBookings->count() > 0)
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold m-0 text-dark">
                                    <i class="mdi mdi-office-building text-primary me-1"></i> Hotel Bookings (Payable)
                                </h6>
                                <span class="badge bg-soft-primary text-primary">{{ $hotelBookings->count() }} Records — PKR {{ number_format($hotelTotal, 2) }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Booking #</th>
                                                <th>Booking Date</th>
                                                <th>Client / Guest</th>
                                                <th>Hotel Name</th>
                                                <th>Location</th>
                                                <th>Room Type</th>
                                                <th class="text-center">Rooms</th>
                                                <th class="text-center">Nights</th>
                                                <th>Check In</th>
                                                <th>Check Out</th>
                                                <th class="text-end">Cost / Night</th>
                                                <th class="text-end col-pkr">Cost (PKR)</th>
                                                <th class="text-end col-sar">Cost (SAR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hotelBookings as $h)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($h->booking)
                                                            <a href="{{ route('booking.show', $h->booking->id) }}" target="_blank" class="fw-semibold text-primary">
                                                                #{{ $h->booking->id }}
                                                            </a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $h->booking_date ? \Carbon\Carbon::parse($h->booking_date)->format('d M, Y') : ($h->booking->booking_date ? \Carbon\Carbon::parse($h->booking->booking_date)->format('d M, Y') : '—') }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $h->booking->client->name ?? ($h->booking->company->name ?? '—') }}</td>
                                                    <td><strong>{{ $h->hotel_name ?? '-' }}</strong></td>
                                                    <td>{{ $h->location ? ucfirst($h->location) : '-' }}</td>
                                                    <td>{{ $h->room_type ? ucfirst($h->room_type) : '-' }}</td>
                                                    <td class="text-center">{{ $h->no_of_rooms ?? 1 }}</td>
                                                    <td class="text-center">{{ $h->no_of_nights ?? 1 }}</td>
                                                    <td>{{ $h->check_in ? \Carbon\Carbon::parse($h->check_in)->format('d M, Y') : '-' }}</td>
                                                    <td>{{ $h->check_out ? \Carbon\Carbon::parse($h->check_out)->format('d M, Y') : '-' }}</td>
                                                    <td class="text-end">{{ number_format($h->cost_per_night, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-pkr">{{ number_format($h->cost_amount, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($h->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($h->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="12" class="text-end">Subtotal Hotel Payable:</td>
                                                <td class="text-end text-primary col-pkr">PKR {{ number_format($hotelTotal, 2) }}</td>
                                                <td class="text-end text-primary col-sar">{{ ($hotelTotalSar ?? 0) > 0 ? ('SAR ' . number_format($hotelTotalSar, 2)) : '—' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 2. FLIGHT BOOKINGS TABLE -->
                        @if($flightBookings->count() > 0)
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold m-0 text-dark">
                                    <i class="mdi mdi-airplane text-info me-1"></i> Flight Bookings (Payable)
                                </h6>
                                <span class="badge bg-soft-info text-info">{{ $flightBookings->count() }} Records — PKR {{ number_format($flightTotal, 2) }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Booking #</th>
                                                <th>Booking Date</th>
                                                <th>Client / Guest</th>
                                                <th>Airline</th>
                                                <th>Departure Flight</th>
                                                <th>Departure Date</th>
                                                <th>PNR</th>
                                                <th class="text-center">Pax</th>
                                                <th class="text-end">Cost Rate</th>
                                                <th class="text-end col-pkr">Cost (PKR)</th>
                                                <th class="text-end col-sar">Cost (SAR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($flightBookings as $fb)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <a href="{{ route('booking.show', $fb->id) }}" target="_blank" class="fw-semibold text-primary">
                                                            #{{ $fb->id }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $fb->flight_booking_date ? \Carbon\Carbon::parse($fb->flight_booking_date)->format('d M, Y') : ($fb->booking_date ? \Carbon\Carbon::parse($fb->booking_date)->format('d M, Y') : ($fb->booking->booking_date ? \Carbon\Carbon::parse($fb->booking->booking_date)->format('d M, Y') : '—')) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium text-dark">{{ $fb->client->name ?? ($fb->company->name ?? '—') }}</div>
                                                        @if(!empty($fb->supplier_pax_details))
                                                            <small class="text-muted d-block fs-11"><i class="mdi mdi-account-outline"></i> {{ $fb->supplier_pax_details }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $fb->departure_airline ?? '-' }}</td>
                                                    <td>{{ $fb->departure_flight ?? '-' }}</td>
                                                    <td>{{ $fb->departure_date ? \Carbon\Carbon::parse($fb->departure_date)->format('d M, Y') : '-' }}</td>
                                                    <td><span class="badge bg-light text-dark">{{ $fb->supplier_pnr ?? ($fb->departure_pnr ?? '-') }}</span></td>
                                                    <td class="text-center">{{ $fb->supplier_pax_count ?? ($fb->no_of_pax ?? 1) }}</td>
                                                    <td class="text-end">{{ number_format($fb->supplier_cost_rate ?? $fb->flight_cost_rate, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-pkr">{{ number_format($fb->cost_pkr, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($fb->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($fb->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="10" class="text-end">Subtotal Flight Payable:</td>
                                                <td class="text-end text-primary col-pkr">PKR {{ number_format($flightTotal, 2) }}</td>
                                                <td class="text-end text-primary col-sar">{{ ($flightTotalSar ?? 0) > 0 ? ('SAR ' . number_format($flightTotalSar, 2)) : '—' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 3. VISA BOOKINGS TABLE -->
                        @if($visaBookings->count() > 0)
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold m-0 text-dark">
                                    <i class="mdi mdi-card-account-details text-success me-1"></i> Visa Bookings (Payable)
                                </h6>
                                <span class="badge bg-soft-success text-success">{{ $visaBookings->count() }} Records — PKR {{ number_format($visaTotal, 2) }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Booking #</th>
                                                <th>Booking Date</th>
                                                <th>Client</th>
                                                <th>Pilgrim Name</th>
                                                <th>Passport #</th>
                                                <th>Send To</th>
                                                <th>Status</th>
                                                <th class="text-end">Cost Rate</th>
                                                <th class="text-end col-pkr">Cost (PKR)</th>
                                                <th class="text-end col-sar">Cost (SAR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($visaBookings as $vb)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($vb->booking)
                                                            <a href="{{ route('booking.show', $vb->booking->id) }}" target="_blank" class="fw-semibold text-primary">
                                                                #{{ $vb->booking->id }}
                                                            </a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $vb->booking_date ? \Carbon\Carbon::parse($vb->booking_date)->format('d M, Y') : ($vb->booking->booking_date ? \Carbon\Carbon::parse($vb->booking->booking_date)->format('d M, Y') : '—') }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $vb->booking->client->name ?? ($vb->booking->company->name ?? '—') }}</td>
                                                    <td>{{ $vb->given_name ?? '-' }}</td>
                                                    <td><strong>{{ $vb->passport_number ?? '-' }}</strong></td>
                                                    <td>{{ $vb->send_to ?? '-' }}</td>
                                                    <td><span class="badge bg-soft-info text-info">{{ ucfirst($vb->status ?? 'pending') }}</span></td>
                                                    <td class="text-end">{{ number_format($vb->cost_rate, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-pkr">{{ number_format($vb->cost_amount, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($vb->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($vb->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="9" class="text-end">Subtotal Visa Payable:</td>
                                                <td class="text-end text-primary col-pkr">PKR {{ number_format($visaTotal, 2) }}</td>
                                                <td class="text-end text-primary col-sar">{{ ($visaTotalSar ?? 0) > 0 ? ('SAR ' . number_format($visaTotalSar, 2)) : '—' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 4. TRANSPORT BOOKINGS TABLE -->
                        @if($transportBookings->count() > 0)
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold m-0 text-dark">
                                    <i class="mdi mdi-bus text-warning me-1"></i> Transport Bookings (Payable)
                                </h6>
                                <span class="badge bg-soft-warning text-warning">{{ $transportBookings->count() }} Records — PKR {{ number_format($transportTotal, 2) }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Booking #</th>
                                                <th>Booking Date</th>
                                                <th>Client</th>
                                                <th>Route</th>
                                                <th>Vehicle / Transport Type</th>
                                                <th>Notes</th>
                                                <th class="text-end">Cost Rate</th>
                                                <th class="text-end col-pkr">Cost (PKR)</th>
                                                <th class="text-end col-sar">Cost (SAR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transportBookings as $tb)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($tb->booking)
                                                            <a href="{{ route('booking.show', $tb->booking->id) }}" target="_blank" class="fw-semibold text-primary">
                                                                #{{ $tb->booking->id }}
                                                            </a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $tb->booking_date ? \Carbon\Carbon::parse($tb->booking_date)->format('d M, Y') : ($tb->booking->booking_date ? \Carbon\Carbon::parse($tb->booking->booking_date)->format('d M, Y') : '—') }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $tb->booking->client->name ?? ($tb->booking->company->name ?? '—') }}</td>
                                                    <td><strong>{{ $tb->route ?? '-' }}</strong></td>
                                                    <td>{{ $tb->transport_type ? ucfirst($tb->transport_type) : '-' }}</td>
                                                    <td>{{ $tb->notes ?? '-' }}</td>
                                                    <td class="text-end">{{ number_format($tb->cost_rate, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-pkr">{{ number_format($tb->cost_amount, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($tb->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($tb->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="8" class="text-end">Subtotal Transport Payable:</td>
                                                <td class="text-end text-primary col-pkr">PKR {{ number_format($transportTotal, 2) }}</td>
                                                <td class="text-end text-primary col-sar">{{ ($transportTotalSar ?? 0) > 0 ? ('SAR ' . number_format($transportTotalSar, 2)) : '—' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 5. PAYMENTS / EXPENSE TRANSACTIONS TABLE -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold m-0 text-dark">
                                    <i class="mdi mdi-cash-check text-success me-1"></i> Payments Made to Supplier (Paid)
                                </h6>
                                <span class="badge bg-soft-success text-success">{{ $transactions->count() }} Payments — PKR {{ number_format($totalPaid, 2) }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle fs-12 mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Payment Date</th>
                                                <th>Reference #</th>
                                                <th>Bank / Contra A/C</th>
                                                <th>Payment Type</th>
                                                <th>Description / Remarks</th>
                                                <th>Proof</th>
                                                <th class="text-end text-success col-pkr">Amount (PKR)</th>
                                                <th class="text-end text-success col-sar">Amount (SAR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($transactions as $t)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $t->date ? \Carbon\Carbon::parse($t->date)->format('d M, Y') : '-' }}</td>
                                                    <td>
                                                        <span class="badge bg-soft-primary text-primary">
                                                            {{ $t->reference_no ?? ('DT-EXP-' . str_pad($t->id, 5, '0', STR_PAD_LEFT)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($t->bank)
                                                            <strong>{{ $t->bank->name }}</strong> ({{ $t->bank->account_name }})
                                                        @else
                                                            <span class="badge bg-soft-success text-success border border-success border-opacity-25 px-2 py-1"><i class="mdi mdi-cash me-1"></i> Cash in Hand</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark text-capitalize">{{ $t->payment_type }}</span>
                                                    </td>
                                                    <td>{{ $t->description ?? '—' }}</td>
                                                    <td>
                                                        @if($t->proof)
                                                            <a href="{{ asset('assets/images/expense_proofs/' . $t->proof) }}" target="_blank" class="badge bg-soft-info text-info">
                                                                <i class="mdi mdi-eye"></i> View
                                                            </a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="text-end fw-bold text-success col-pkr">PKR {{ number_format($t->amount_pkr ?? $t->amount, 2) }}</td>
                                                    <td class="text-end fw-bold text-success col-sar">{{ ($t->amount_sar ?? 0) > 0 ? ('SAR ' . number_format($t->amount_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-3">No payments recorded for this supplier yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="7" class="text-end">Total Paid:</td>
                                                <td class="text-end text-success col-pkr">PKR {{ number_format($totalPaid, 2) }}</td>
                                                <td class="text-end text-success col-sar">{{ ($totalPaidSar ?? 0) > 0 ? ('SAR ' . number_format($totalPaidSar, 2)) : '—' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- FINAL RECONCILIATION SUMMARY -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4 bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="fw-bold text-dark mb-1">Final Ledger Balance Summary</h5>
                                        <p class="text-muted fs-12 mb-0">Reconciliation of opening balance, booking cost payables, and transactions paid across PKR & SAR.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless mb-0 fs-13">
                                            <thead>
                                                <tr class="border-bottom">
                                                    <th>Description</th>
                                                    <th class="text-end text-dark col-pkr">PKR</th>
                                                    <th class="text-end text-dark col-sar">SAR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Opening Balance:</td>
                                                    <td class="text-end fw-semibold col-pkr">PKR {{ number_format(abs($openingBalancePkr ?? $openingBalance), 2) }} {{ ($openingBalancePkr ?? $openingBalance) >= 0 ? 'Cr' : 'Dr' }}</td>
                                                    <td class="text-end fw-semibold col-sar">{{ ($openingBalanceSar ?? 0) != 0 ? ('SAR ' . number_format(abs($openingBalanceSar), 2) . ' ' . (($openingBalanceSar ?? 0) >= 0 ? 'Cr' : 'Dr')) : '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Bookings Cost:</td>
                                                    <td class="text-end fw-semibold col-pkr">PKR {{ number_format($bookingPayable, 2) }}</td>
                                                    <td class="text-end fw-semibold col-sar">{{ ($bookingPayableSar ?? 0) > 0 ? ('SAR ' . number_format($bookingPayableSar, 2)) : '—' }}</td>
                                                </tr>
                                                <tr class="border-top">
                                                    <td><strong>Total Payable:</strong></td>
                                                    <td class="text-end fw-bold text-primary col-pkr">PKR {{ number_format($totalPayablePkr ?? $totalPayable, 2) }}</td>
                                                    <td class="text-end fw-bold text-primary col-sar">{{ ($totalPayableSar ?? 0) > 0 ? ('SAR ' . number_format($totalPayableSar, 2)) : '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Paid / Disbursed:</td>
                                                    <td class="text-end fw-bold text-success col-pkr">- PKR {{ number_format($totalPaidPkr ?? $totalPaid, 2) }}</td>
                                                    <td class="text-end fw-bold text-success col-sar">{{ ($totalPaidSar ?? 0) > 0 ? ('- SAR ' . number_format($totalPaidSar, 2)) : '—' }}</td>
                                                </tr>
                                                <tr class="border-top border-2">
                                                    <td><strong class="fs-15">Net Balance Due:</strong></td>
                                                    <td class="text-end fw-bold fs-15 col-pkr {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'text-danger' : 'text-success' }}">
                                                        PKR {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }} {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'Cr (Payable)' : (($balanceDuePkr ?? $balanceDue) < 0 ? 'Dr (Advance)' : 'Nil') }}
                                                    </td>
                                                    <td class="text-end fw-bold fs-15 col-sar {{ ($balanceDueSar ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                        @if(isset($balanceDueSar) && abs($balanceDueSar) > 0)
                                                            SAR {{ number_format(abs($balanceDueSar), 2) }} {{ $balanceDueSar > 0 ? 'Cr (Payable)' : 'Dr (Advance)' }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ============================================================ -->
                    <!-- VIEW 2: STATEMENT OF ACCOUNT (DEBIT / CREDIT / BALANCE VIEW) -->
                    <!-- ============================================================ -->
                    <div class="tab-pane fade" id="view-statement" role="tabpanel" aria-labelledby="statement-tab">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h6 class="fs-13 fw-bold m-0 text-dark">
                                    <i class="mdi mdi-scale-balance text-primary me-1"></i> Supplier Chronological Statement of Account (Dr / Cr Ledger)
                                </h6>
                                <span class="badge bg-light text-dark border">
                                    {{ $statementLines->count() }} Ledger Transactions
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table statement-table table-bordered table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="text-center align-middle" style="width: 35px;">#</th>
                                                <th rowspan="2" class="align-middle" style="width: 95px;">Date</th>
                                                <th rowspan="2" class="align-middle" style="width: 100px;">Voucher #</th>
                                                <th rowspan="2" class="align-middle" style="width: 120px;">Type</th>
                                                <th rowspan="2" class="align-middle" style="width: 130px;">Contra A/C</th>
                                                <th rowspan="2" class="align-middle">Particulars / Narration</th>
                                                <th rowspan="2" class="align-middle" style="width: 90px;">Method</th>
                                                <th colspan="3" class="text-center bg-soft-primary text-primary border-bottom-0 py-1 col-pkr">PKR (Pakistani Rupee)</th>
                                                <th rowspan="2" class="text-center align-middle col-roe" style="width: 75px;">ROE</th>
                                                <th colspan="3" class="text-center bg-soft-warning text-dark border-bottom-0 py-1 col-sar">SAR (Saudi Riyal)</th>
                                                <th rowspan="2" class="no-print text-center align-middle" style="width: 45px;">Proof</th>
                                            </tr>
                                            <tr>
                                                <th class="text-end text-success py-1 col-pkr" style="min-width: 95px;" title="Payments made to supplier">Debit (Dr) Paid</th>
                                                <th class="text-end text-danger py-1 col-pkr" style="min-width: 95px;" title="Services cost billed by supplier">Credit (Cr) Cost</th>
                                                <th class="text-end py-1 text-dark col-pkr" style="min-width: 95px;">Balance PKR</th>
                                                <th class="text-end text-success py-1 col-sar" style="min-width: 85px;" title="Payments made to supplier">Debit (Dr) Paid</th>
                                                <th class="text-end text-danger py-1 col-sar" style="min-width: 85px;" title="Services cost billed by supplier">Credit (Cr) Cost</th>
                                                <th class="text-end py-1 text-dark col-sar" style="min-width: 85px;">Balance SAR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Opening Balance Row --}}
                                            @if(($openingBalancePkr ?? 0) != 0 || ($openingBalanceSar ?? 0) != 0)
                                                <tr class="opening-row">
                                                    <td class="text-center text-muted">—</td>
                                                    <td>{{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M, Y') : '—' }}</td>
                                                    <td><span class="badge bg-warning text-dark border">OPENING</span></td>
                                                    <td><span class="badge bg-soft-warning text-dark">B/F Balance</span></td>
                                                    <td><span class="badge bg-light text-dark border">Opening Balance</span></td>
                                                    <td><strong>Opening Balance Brought Forward</strong></td>
                                                    <td>—</td>
                                                    {{-- PKR Columns: Debit, Credit, Balance --}}
                                                    <td class="text-end text-muted col-pkr">—</td>
                                                    <td class="text-end fw-bold text-danger col-pkr">
                                                        {{ $openingBalancePkr >= 0 ? number_format(abs($openingBalancePkr), 2) : '—' }}
                                                    </td>
                                                    <td class="text-end fw-bold text-dark col-pkr">
                                                        {{ number_format(abs($openingBalancePkr), 2) }}
                                                        <small class="badge {{ $openingBalancePkr >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                            {{ $openingBalancePkr >= 0 ? 'Cr' : 'Dr' }}
                                                        </small>
                                                    </td>
                                                    {{-- ROE Column --}}
                                                    <td class="text-center col-roe">—</td>
                                                    {{-- SAR Columns: Debit, Credit, Balance --}}
                                                    <td class="text-end text-muted col-sar">—</td>
                                                    <td class="text-end fw-bold text-danger col-sar">
                                                        {{ ($openingBalanceSar ?? 0) >= 0 ? number_format(abs($openingBalanceSar ?? 0), 2) : '—' }}
                                                    </td>
                                                    <td class="text-end fw-bold text-dark col-sar">
                                                        {{ number_format(abs($openingBalanceSar ?? 0), 2) }}
                                                        @if(($openingBalanceSar ?? 0) != 0)
                                                            <small class="badge {{ ($openingBalanceSar ?? 0) >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                                {{ ($openingBalanceSar ?? 0) >= 0 ? 'Cr' : 'Dr' }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td class="no-print text-center">—</td>
                                                </tr>
                                            @endif

                                            @forelse($statementLines as $row)
                                                <tr>
                                                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                                                    <td class="fw-medium text-nowrap">
                                                        {{ \Carbon\Carbon::parse($row['date'])->format('d M, Y') }}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark fw-bold border">{{ $row['voucher_no'] }}</span>
                                                        @if(!empty($row['ref_no']) && $row['ref_no'] !== $row['voucher_no'])
                                                            <small class="text-muted d-block">{{ $row['ref_no'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $row['badge_class'] }} px-2 py-1">
                                                            {{ $row['type_label'] }}
                                                        </span>
                                                    </td>
                                                    <!-- Contra Account Column -->
                                                    <td>
                                                        @if(!empty($row['contra_account']) && $row['contra_account'] !== '—')
                                                            @if(str_contains(strtolower($row['contra_account']), 'cash'))
                                                                <span class="badge bg-soft-success text-success border border-success border-opacity-25 px-2 py-1">
                                                                    <i class="mdi mdi-cash me-1"></i> Cash in Hand
                                                                </span>
                                                            @else
                                                                <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25 px-2 py-1 text-wrap" style="max-width: 140px; font-size: 11px;">
                                                                    <i class="mdi mdi-bank me-1"></i>{{ $row['contra_account'] }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $cleanSupDesc = preg_replace('/\s*[\(\[]\s*[^)\]]*@\s*ROE[^)\]]*[\)\]]/i', '', $row['description'] ?? '');
                                                            $cleanSupDesc = preg_replace('/\s*@\s*ROE\s*[0-9]+(\.[0-9]+)?/i', '', $cleanSupDesc);
                                                        @endphp
                                                        @if(!empty($row['booking_id']))
                                                            <a href="{{ route('booking.show', $row['booking_id']) }}" target="_blank" class="fw-semibold text-primary text-decoration-none">
                                                                {{ $cleanSupDesc }}
                                                            </a>
                                                        @else
                                                            <span class="text-dark fw-medium">{{ $cleanSupDesc }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                                            {{ $row['payment_method'] }}
                                                        </span>
                                                    </td>
                                                    <!-- PKR Debit (Paid) Column -->
                                                    <td class="text-end fw-bold col-pkr {{ ($row['debit_pkr'] ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                                        {{ ($row['debit_pkr'] ?? 0) > 0 ? number_format($row['debit_pkr'], 2) : '—' }}
                                                    </td>
                                                    <!-- PKR Credit (Cost) Column -->
                                                    <td class="text-end fw-bold col-pkr {{ ($row['credit_pkr'] ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                                        {{ ($row['credit_pkr'] ?? 0) > 0 ? number_format($row['credit_pkr'], 2) : '—' }}
                                                    </td>
                                                    <!-- Running Balance PKR Column -->
                                                    <td class="text-end fw-bold text-dark col-pkr">
                                                        {{ number_format(abs($row['running_balance_pkr'] ?? 0), 2) }}
                                                        <span class="badge {{ ($row['running_balance_pkr'] ?? 0) > 0 ? 'bg-soft-danger text-danger' : (($row['running_balance_pkr'] ?? 0) < 0 ? 'bg-soft-success text-success' : 'bg-light text-muted') }} ms-1" style="font-size: 9px;">
                                                            {{ $row['balance_type'] }}
                                                        </span>
                                                    </td>
                                                    <!-- ROE Column -->
                                                    <td class="text-center col-roe">
                                                        @if(!empty($row['exchange_rate']) && (float)$row['exchange_rate'] > 1)
                                                            <span class="badge bg-soft-info text-info border border-info border-opacity-25 px-2 py-1 font-monospace fs-11" title="Rate of Exchange">
                                                                {{ number_format((float)$row['exchange_rate'], 2) }}
                                                            </span>
                                                        @elseif(!empty($row['currency']) && $row['currency'] === 'SAR')
                                                            <span class="badge bg-light text-muted border px-1 font-monospace fs-10">1.00</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <!-- SAR Debit (Paid) Column -->
                                                    <td class="text-end fw-bold col-sar {{ ($row['debit_sar'] ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                                        {{ ($row['debit_sar'] ?? 0) > 0 ? number_format($row['debit_sar'], 2) : '—' }}
                                                    </td>
                                                    <!-- SAR Credit (Cost) Column -->
                                                    <td class="text-end fw-bold col-sar {{ ($row['credit_sar'] ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                                        {{ ($row['credit_sar'] ?? 0) > 0 ? number_format($row['credit_sar'], 2) : '—' }}
                                                    </td>
                                                    <!-- Running Balance SAR Column -->
                                                    <td class="text-end fw-bold text-dark col-sar">
                                                        {{ number_format(abs($row['running_balance_sar'] ?? 0), 2) }}
                                                        @if(($row['running_balance_sar'] ?? 0) != 0)
                                                            <span class="badge {{ ($row['running_balance_sar'] ?? 0) > 0 ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success' }} ms-1" style="font-size: 9px;">
                                                                {{ $row['balance_type_sar'] ?? (($row['running_balance_sar'] ?? 0) > 0 ? 'Cr' : 'Dr') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <!-- Proof Column -->
                                                    <td class="no-print text-center">
                                                        @if(!empty($row['proof']))
                                                            <a href="{{ $row['proof'] }}" target="_blank" class="btn btn-xs btn-outline-info" title="View Proof">
                                                                <i class="mdi mdi-file-document-outline"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="15" class="text-center py-5 text-muted">
                                                        <i class="mdi mdi-book-remove-outline fs-36 text-muted d-block mb-2"></i>
                                                        <h6 class="fw-bold mb-1">No Ledger Records Found</h6>
                                                        <p class="fs-12 mb-0">No booking costs or payment disbursements recorded for this supplier in the selected period.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr style="border-top: 2px solid #cbd5e1;">
                                                <td colspan="7" class="text-end text-uppercase fs-12">Period Totals:</td>
                                                <td class="text-end text-success fs-13 col-pkr">PKR {{ number_format($periodDebitsPkr ?? 0, 2) }}</td>
                                                <td class="text-end text-danger fs-13 col-pkr">PKR {{ number_format($periodCreditsPkr ?? 0, 2) }}</td>
                                                <td class="text-end text-dark fs-13 col-pkr">PKR {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }}</td>
                                                <td class="text-center col-roe">—</td>
                                                <td class="text-end text-success fs-13 col-sar">SAR {{ number_format($periodDebitsSar ?? 0, 2) }}</td>
                                                <td class="text-end text-danger fs-13 col-sar">SAR {{ number_format($periodCreditsSar ?? 0, 2) }}</td>
                                                <td class="text-end text-dark fs-13 col-sar">SAR {{ number_format(abs($balanceDueSar ?? 0), 2) }}</td>
                                                <td class="no-print"></td>
                                            </tr>
                                            <tr style="background: #f1f5f9; border-top: 1px solid #cbd5e1;">
                                                <td colspan="7" class="text-end text-uppercase fs-13 text-dark">
                                                    Net Balance Due (Closing):
                                                </td>
                                                <td colspan="3" class="text-end fs-14 col-pkr {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'text-danger' : 'text-success' }}">
                                                    PKR {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }}
                                                    <span class="badge {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} fs-11 ms-1">
                                                        {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'Cr (Payable)' : (($balanceDuePkr ?? $balanceDue) < 0 ? 'Dr (Advance)' : 'Nil') }}
                                                    </span>
                                                </td>
                                                <td class="text-center col-roe">—</td>
                                                <td colspan="3" class="text-end fs-14 col-sar {{ ($balanceDueSar ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                    @if(($balanceDueSar ?? 0) != 0)
                                                        SAR {{ number_format(abs($balanceDueSar), 2) }}
                                                        <span class="badge {{ ($balanceDueSar ?? 0) > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} fs-11 ms-1">
                                                            {{ ($balanceDueSar ?? 0) > 0 ? 'Cr (Payable)' : 'Dr (Advance)' }}
                                                        </span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="no-print"></td>
                                            </tr>
                                        </tfoot>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function updateSupplierPkrButtonUI(show) {
        const icon = document.getElementById('iconToggleSupplierPkr');
        if (icon) {
            icon.className = show ? 'mdi mdi-check-circle fs-14' : 'mdi mdi-eye-off-outline fs-14';
        }
    }

    function updateSupplierSarButtonUI(show) {
        const icon = document.getElementById('iconToggleSupplierSar');
        if (icon) {
            icon.className = show ? 'mdi mdi-check-circle fs-14' : 'mdi mdi-eye-off-outline fs-14';
        }
    }

    function updateSupplierRoeButtonUI(show) {
        const icon = document.getElementById('iconToggleSupplierRoe');
        if (icon) {
            icon.className = show ? 'mdi mdi-check-circle fs-14' : 'mdi mdi-eye-off-outline fs-14';
        }
    }

    function toggleSupplierPkr(show) {
        document.querySelectorAll('.col-pkr').forEach(function(el) {
            if (show) {
                el.classList.remove('d-none');
                el.style.display = '';
            } else {
                el.classList.add('d-none');
                el.style.display = 'none';
            }
        });
        updateSupplierPkrButtonUI(show);
        try {
            localStorage.setItem('supplier_ledger_show_pkr', show ? '1' : '0');
        } catch(e) {}
    }

    function toggleSupplierSar(show) {
        document.querySelectorAll('.col-sar').forEach(function(el) {
            if (show) {
                el.classList.remove('d-none');
                el.style.display = '';
            } else {
                el.classList.add('d-none');
                el.style.display = 'none';
            }
        });
        updateSupplierSarButtonUI(show);
        try {
            localStorage.setItem('supplier_ledger_show_sar', show ? '1' : '0');
        } catch(e) {}
    }

    function toggleSupplierRoe(show) {
        document.querySelectorAll('.col-roe').forEach(function(el) {
            if (show) {
                el.classList.remove('d-none');
                el.style.display = '';
            } else {
                el.classList.add('d-none');
                el.style.display = 'none';
            }
        });
        updateSupplierRoeButtonUI(show);
        try {
            localStorage.setItem('supplier_ledger_show_roe', show ? '1' : '0');
        } catch(e) {}
    }

    document.addEventListener('DOMContentLoaded', function() {
        try {
            const savedPkr = localStorage.getItem('supplier_ledger_show_pkr');
            const togglePkr = document.getElementById('toggleSupplierPkrColumns');
            if (togglePkr && savedPkr !== null) {
                const showPkr = (savedPkr === '1' || savedPkr === 'true');
                togglePkr.checked = showPkr;
                toggleSupplierPkr(showPkr);
            } else if (togglePkr) {
                updateSupplierPkrButtonUI(togglePkr.checked);
            }

            const savedSar = localStorage.getItem('supplier_ledger_show_sar');
            const toggleSar = document.getElementById('toggleSupplierSarColumns');
            if (toggleSar && savedSar !== null) {
                const showSar = (savedSar === '1' || savedSar === 'true');
                toggleSar.checked = showSar;
                toggleSupplierSar(showSar);
            } else if (toggleSar) {
                updateSupplierSarButtonUI(toggleSar.checked);
            }

            const savedRoe = localStorage.getItem('supplier_ledger_show_roe');
            const toggleRoe = document.getElementById('toggleSupplierRoeColumns');
            if (toggleRoe && savedRoe !== null) {
                const showRoe = (savedRoe === '1' || savedRoe === 'true');
                toggleRoe.checked = showRoe;
                toggleSupplierRoe(showRoe);
            } else if (toggleRoe) {
                updateSupplierRoeButtonUI(toggleRoe.checked);
            }
        } catch(e) {}
    });

    function downloadLedgerExcel() {
        if (typeof XLSX === 'undefined') {
            alert('Excel export library is loading, please try again.');
            return;
        }

        const wb = XLSX.utils.book_new();

        const showRoe = document.getElementById('toggleSupplierRoeColumns') ? document.getElementById('toggleSupplierRoeColumns').checked : true;
        const showPkr = document.getElementById('toggleSupplierPkrColumns') ? document.getElementById('toggleSupplierPkrColumns').checked : true;
        const showSar = document.getElementById('toggleSupplierSarColumns') ? document.getElementById('toggleSupplierSarColumns').checked : true;

        let headers = ["#", "Date", "Voucher #", "Type", "Contra A/C", "Particulars / Narration", "Payment Method"];
        if (showPkr) headers.push("Debit (Dr) Paid PKR", "Credit (Cr) Cost PKR", "Balance PKR");
        if (showRoe) headers.push("ROE");
        if (showSar) headers.push("Debit (Dr) Paid SAR", "Credit (Cr) Cost SAR", "Balance SAR");

        const rows = [];

        @if(($openingBalancePkr ?? 0) != 0 || ($openingBalanceSar ?? 0) != 0)
        let openRow = ["—", "{{ $fromDate ?? '—' }}", "OPENING", "Opening Balance", "—", "Opening Balance Brought Forward", "—"];
        if (showPkr) openRow.push(0, {{ $openingBalancePkr >= 0 ? abs($openingBalancePkr) : 0 }}, {{ abs($openingBalancePkr) }});
        if (showRoe) openRow.push(1.00);
        if (showSar) openRow.push(0, {{ ($openingBalanceSar ?? 0) >= 0 ? abs($openingBalanceSar ?? 0) : 0 }}, {{ abs($openingBalanceSar ?? 0) }});
        rows.push(openRow);
        @endif

        @foreach($statementLines as $row)
        @php
            $cleanSupRowDesc = preg_replace('/\s*[\(\[]\s*[^)\]]*@\s*ROE[^)\]]*[\)\]]/i', '', $row['description'] ?? '');
            $cleanSupRowDesc = preg_replace('/\s*@\s*ROE\s*[0-9]+(\.[0-9]+)?/i', '', $cleanSupRowDesc);
        @endphp
        let r{{ $loop->iteration }} = [
            {{ $loop->iteration }},
            "{{ $row['date'] }}",
            "{{ addslashes($row['voucher_no']) }}",
            "{{ addslashes($row['type_label']) }}",
            "{{ addslashes($row['contra_account'] ?? '—') }}",
            "{{ addslashes($cleanSupRowDesc) }}",
            "{{ addslashes($row['payment_method']) }}"
        ];
        if (showPkr) r{{ $loop->iteration }}.push(
            {{ (float) ($row['debit_pkr'] ?? 0) }},
            {{ (float) ($row['credit_pkr'] ?? 0) }},
            {{ (float) abs($row['running_balance_pkr'] ?? 0) }}
        );
        if (showRoe) r{{ $loop->iteration }}.push({{ (float) ($row['exchange_rate'] ?? 1) }});
        if (showSar) r{{ $loop->iteration }}.push(
            {{ (float) ($row['debit_sar'] ?? 0) }},
            {{ (float) ($row['credit_sar'] ?? 0) }},
            {{ (float) abs($row['running_balance_sar'] ?? 0) }}
        );
        rows.push(r{{ $loop->iteration }});
        @endforeach

        rows.push([]);
        let totRow = ["TOTALS", "", "", "", "", "Period Totals:", ""];
        if (showPkr) totRow.push(
            {{ (float) ($periodDebitsPkr ?? 0) }},
            {{ (float) ($periodCreditsPkr ?? 0) }},
            {{ (float) abs($balanceDuePkr ?? $balanceDue) }}
        );
        if (showRoe) totRow.push("");
        if (showSar) totRow.push(
            {{ (float) ($periodDebitsSar ?? 0) }},
            {{ (float) ($periodCreditsSar ?? 0) }},
            {{ (float) abs($balanceDueSar ?? 0) }}
        );
        rows.push(totRow);

        const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
        XLSX.utils.book_append_sheet(wb, ws, "Statement of Account");

        XLSX.writeFile(wb, "Supplier_Ledger_{{ Str::slug($supplier->name) }}_{{ date('Ymd_His') }}.xlsx");
    }

    function downloadLedgerPDF() {
        if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
            window.print();
            return;
        }

        const btn = document.getElementById('exportPDF');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Generating...';
        btn.disabled = true;

        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
            const pageW = doc.internal.pageSize.getWidth();

            // Header Banner
            doc.setFillColor(30, 41, 59);
            doc.rect(0, 0, pageW, 22, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(14);
            doc.setFont(undefined, 'bold');
            doc.text("DUA TRAVEL & TOURS — SUPPLIER STATEMENT OF ACCOUNT (LEDGER)", 14, 10);
            doc.setFontSize(8.5);
            doc.setFont(undefined, 'normal');
            doc.text("Supplier: {{ addslashes($supplier->name) }} @if($supplier->company_name)({{ addslashes($supplier->company_name) }})@endif | Service: {{ ucfirst($supplier->service_type ?? 'All Services') }} | Printed: " + new Date().toLocaleString(), 14, 17);

            // Summary Info Line
            doc.setTextColor(30, 30, 30);
            doc.setFontSize(8.5);
            doc.setFont(undefined, 'bold');
            doc.text("PKR — Total Cost: {{ number_format($totalPayablePkr ?? $totalPayable, 2) }} | Total Paid: {{ number_format($totalPaidPkr ?? $totalPaid, 2) }} | Net Due: {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }} ({{ ($balanceDuePkr ?? $balanceDue) >= 0 ? 'Cr Payable' : 'Dr Advance' }})" +
                     @if(($totalPayableSar ?? 0) > 0 || ($totalPaidSar ?? 0) > 0)
                     "   |   SAR — Total Cost: {{ number_format($totalPayableSar ?? 0, 2) }} | Total Paid: {{ number_format($totalPaidSar ?? 0, 2) }} | Net Due: {{ number_format(abs($balanceDueSar ?? 0), 2) }} ({{ ($balanceDueSar ?? 0) >= 0 ? 'Cr Payable' : 'Dr Advance' }})"
                     @else
                     ""
                     @endif
                     , 14, 28);

            const tableRows = [];

            @if(($openingBalancePkr ?? $openingBalance) != 0 || ($openingBalanceSar ?? 0) != 0)
            tableRows.push([
                "—",
                "{{ $fromDate ?? '—' }}",
                "OPENING",
                "Opening Balance",
                "—",
                "Opening Balance Brought Forward",
                "—",
                "{{ ($openingBalancePkr ?? $openingBalance) < 0 ? number_format(abs($openingBalancePkr ?? $openingBalance), 2) : '—' }}",
                "{{ ($openingBalancePkr ?? $openingBalance) > 0 ? number_format($openingBalancePkr ?? $openingBalance, 2) : '—' }}",
                "{{ number_format(abs($openingBalancePkr ?? $openingBalance), 2) }} {{ ($openingBalancePkr ?? $openingBalance) >= 0 ? 'Cr' : 'Dr' }}",
                "—",
                "{{ ($openingBalanceSar ?? 0) < 0 ? number_format(abs($openingBalanceSar), 2) : '—' }}",
                "{{ ($openingBalanceSar ?? 0) > 0 ? number_format($openingBalanceSar, 2) : '—' }}",
                "{{ number_format(abs($openingBalanceSar ?? 0), 2) }} {{ ($openingBalanceSar ?? 0) >= 0 ? 'Cr' : 'Dr' }}"
            ]);
            @endif

            @foreach($statementLines as $row)
            @php
                $cleanRowDesc = preg_replace('/\s*[\(\[]\s*[^)\]]*@\s*ROE[^)\]]*[\)\]]/i', '', $row['description'] ?? '');
                $cleanRowDesc = preg_replace('/\s*@\s*ROE\s*[0-9]+(\.[0-9]+)?/i', '', $cleanRowDesc);
            @endphp
            tableRows.push([
                "{{ $loop->iteration }}",
                "{{ \Carbon\Carbon::parse($row['date'])->format('d M, Y') }}",
                "{{ addslashes($row['voucher_no']) }}",
                "{{ addslashes($row['type_label']) }}",
                "{{ addslashes($row['contra_account'] ?? '—') }}",
                "{{ addslashes($cleanRowDesc) }}",
                "{{ addslashes($row['payment_method']) }}",
                "{{ ($row['debit_pkr'] ?? 0) > 0 ? number_format($row['debit_pkr'], 2) : '—' }}",
                "{{ ($row['credit_pkr'] ?? 0) > 0 ? number_format($row['credit_pkr'], 2) : '—' }}",
                "{{ number_format(abs($row['running_balance_pkr'] ?? 0), 2) }} {{ $row['balance_type'] }}",
                "{{ !empty($row['exchange_rate']) && (float)$row['exchange_rate'] > 1 ? number_format((float)$row['exchange_rate'], 2) : '—' }}",
                "{{ ($row['debit_sar'] ?? 0) > 0 ? number_format($row['debit_sar'], 2) : '—' }}",
                "{{ ($row['credit_sar'] ?? 0) > 0 ? number_format($row['credit_sar'], 2) : '—' }}",
                "{{ number_format(abs($row['running_balance_sar'] ?? 0), 2) }} {{ ($row['running_balance_sar'] ?? 0) != 0 ? ($row['balance_type_sar'] ?? '') : '' }}"
            ]);
            @endforeach

            tableRows.push([
                "", "", "", "", "", "TOTALS:", "",
                "PKR {{ number_format($periodDebitsPkr ?? 0, 2) }}",
                "PKR {{ number_format($periodCreditsPkr ?? 0, 2) }}",
                "PKR {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }}",
                "—",
                "SAR {{ number_format($periodDebitsSar ?? 0, 2) }}",
                "SAR {{ number_format($periodCreditsSar ?? 0, 2) }}",
                "SAR {{ number_format(abs($balanceDueSar ?? 0), 2) }}"
            ]);

            const showRoePdf = document.getElementById('supplierToggleRoe') ? document.getElementById('supplierToggleRoe').checked : true;
            const showPkrPdf = document.getElementById('supplierTogglePkr') ? document.getElementById('supplierTogglePkr').checked : true;
            const showSarPdf = document.getElementById('supplierToggleSar') ? document.getElementById('supplierToggleSar').checked : true;

            let pdfHead = ["#", "Date", "Voucher", "Type", "Contra A/C", "Description", "Method"];
            if (showPkrPdf) pdfHead.push("Dr Paid (PKR)", "Cr Cost (PKR)", "Bal (PKR)");
            if (showRoePdf) pdfHead.push("ROE");
            if (showSarPdf) pdfHead.push("Dr Paid (SAR)", "Cr Cost (SAR)", "Bal (SAR)");

            let colStyles = {};
            let colIdx = 7;
            if (showPkrPdf) {
                colStyles[colIdx] = { halign: 'right', textColor: [0, 130, 0] };
                colStyles[colIdx + 1] = { halign: 'right', textColor: [180, 0, 0] };
                colStyles[colIdx + 2] = { halign: 'right', fontStyle: 'bold' };
                colIdx += 3;
            }
            if (showRoePdf) {
                colStyles[colIdx] = { halign: 'center' };
                colIdx += 1;
            }
            if (showSarPdf) {
                colStyles[colIdx] = { halign: 'right', textColor: [0, 130, 0] };
                colStyles[colIdx + 1] = { halign: 'right', textColor: [180, 0, 0] };
                colStyles[colIdx + 2] = { halign: 'right', fontStyle: 'bold' };
            }

            const finalPdfRows = tableRows.map(row => {
                let r = [row[0], row[1], row[2], row[3], row[4], row[5], row[6]];
                if (showPkrPdf) r.push(row[7], row[8], row[9]);
                if (showRoePdf) r.push(row[10]);
                if (showSarPdf) r.push(row[11], row[12], row[13]);
                return r;
            });

            doc.autoTable({
                startY: 32,
                head: [pdfHead],
                body: finalPdfRows,
                theme: 'striped',
                headStyles: { fillColor: [30, 41, 59], textColor: 255, fontSize: 7.5, fontStyle: 'bold' },
                bodyStyles: { fontSize: 7 },
                columnStyles: colStyles,
                margin: { left: 10, right: 10 }
            });

            doc.save("Supplier_Ledger_{{ Str::slug($supplier->name) }}_{{ date('Ymd') }}.pdf");
        } catch(err) {
            console.error('PDF error:', err);
            window.print();
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>

@endsection
