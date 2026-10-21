@extends('layout.master')

@section('title', 'Supplier Ledger - ' . $supplier->name)

@section('content')

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
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: #fff;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .tab-pane {
            display: block !important;
            opacity: 1 !important;
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
                                <div class="fs-16 fw-bold text-dark mt-1">PKR {{ number_format($openingBalance, 2) }}</div>
                                @if(($openingBalanceSar ?? 0) != 0)
                                    <div class="badge bg-secondary text-white fs-10 mt-1 col-sar">SAR {{ number_format($openingBalanceSar, 2) }}</div>
                                @endif
                                <small class="text-muted fs-11 d-block mt-1">Initial balance</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm ledger-stat-card bg-soft-primary">
                            <div class="card-body p-3 text-center">
                                <div class="text-primary fs-11 fw-bold text-uppercase">Total Cost (Payable)</div>
                                <div class="fs-16 fw-bold text-primary mt-1">PKR {{ number_format($totalPayable, 2) }}</div>
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
                                <div class="fs-16 fw-bold text-success mt-1">PKR {{ number_format($totalPaid, 2) }}</div>
                                @if(($totalPaidSar ?? 0) > 0)
                                    <div class="badge bg-soft-success text-success border fs-10 mt-1 col-sar">SAR {{ number_format($totalPaidSar, 2) }}</div>
                                @endif
                                <small class="text-muted fs-11 d-block mt-1">{{ $transactions->count() }} payments made</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm ledger-stat-card {{ $balanceDue > 0 ? 'bg-soft-danger' : 'bg-soft-success' }}">
                            <div class="card-body p-3 text-center">
                                <div class="{{ $balanceDue > 0 ? 'text-danger' : 'text-success' }} fs-11 fw-bold text-uppercase">
                                    {{ $balanceDue > 0 ? 'Net Balance Due' : ($balanceDue < 0 ? 'Advance Paid' : 'Balance Cleared') }}
                                </div>
                                <div class="fs-16 fw-bold {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }} mt-1">
                                    PKR {{ number_format(abs($balanceDue), 2) }}
                                </div>
                                @if(isset($balanceDueSar) && abs($balanceDueSar) > 0)
                                    <div class="badge {{ $balanceDueSar > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} fs-10 mt-1 col-sar">
                                        SAR {{ number_format(abs($balanceDueSar), 2) }} {{ $balanceDueSar > 0 ? 'Due' : 'Advance' }}
                                    </div>
                                @endif
                                <small class="text-muted fs-11 d-block mt-1">{{ $balanceDue > 0 ? 'Payable to supplier' : 'Up to date' }}</small>
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
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="toggleSupplierSarColumns" checked onchange="toggleSupplierSar(this.checked)">
                                <label class="form-check-label fs-12 fw-semibold text-muted" for="toggleSupplierSarColumns">
                                    <i class="mdi mdi-currency-usd text-warning"></i> Show SAR (Foreign Currency)
                                </label>
                            </div>
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
                                                <th class="text-end">Cost (PKR)</th>
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
                                                    <td class="text-end fw-bold text-dark">{{ number_format($h->cost_amount, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($h->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($h->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="12" class="text-end">Subtotal Hotel Payable:</td>
                                                <td class="text-end text-primary">PKR {{ number_format($hotelTotal, 2) }}</td>
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
                                                <th class="text-end">Cost (PKR)</th>
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
                                                    <td>{{ $fb->client->name ?? ($fb->company->name ?? '—') }}</td>
                                                    <td>{{ $fb->departure_airline ?? '-' }}</td>
                                                    <td>{{ $fb->departure_flight ?? '-' }}</td>
                                                    <td>{{ $fb->departure_date ? \Carbon\Carbon::parse($fb->departure_date)->format('d M, Y') : '-' }}</td>
                                                    <td><span class="badge bg-light text-dark">{{ $fb->departure_pnr ?? '-' }}</span></td>
                                                    <td class="text-center">{{ $fb->no_of_pax ?? 1 }}</td>
                                                    <td class="text-end">{{ number_format($fb->flight_cost_rate, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark">{{ number_format($fb->flight_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($fb->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($fb->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="10" class="text-end">Subtotal Flight Payable:</td>
                                                <td class="text-end text-primary">PKR {{ number_format($flightTotal, 2) }}</td>
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
                                                <th class="text-end">Cost (PKR)</th>
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
                                                    <td class="text-end fw-bold text-dark">{{ number_format($vb->cost_amount, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($vb->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($vb->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="9" class="text-end">Subtotal Visa Payable:</td>
                                                <td class="text-end text-primary">PKR {{ number_format($visaTotal, 2) }}</td>
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
                                                <th class="text-end">Cost (PKR)</th>
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
                                                    <td class="text-end fw-bold text-dark">{{ number_format($tb->cost_amount, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark col-sar">{{ ($tb->cost_sar ?? 0) > 0 ? ('SAR ' . number_format($tb->cost_sar, 2)) : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="8" class="text-end">Subtotal Transport Payable:</td>
                                                <td class="text-end text-primary">PKR {{ number_format($transportTotal, 2) }}</td>
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
                                                <th class="text-end text-success">Amount (PKR)</th>
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
                                                    <td class="text-end fw-bold text-success">PKR {{ number_format($t->amount_pkr ?? $t->amount, 2) }}</td>
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
                                                <td class="text-end text-success">PKR {{ number_format($totalPaid, 2) }}</td>
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
                                                    <th class="text-end text-dark">PKR</th>
                                                    <th class="text-end text-dark col-sar">SAR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Opening Balance:</td>
                                                    <td class="text-end fw-semibold">PKR {{ number_format($openingBalance, 2) }}</td>
                                                    <td class="text-end fw-semibold col-sar">{{ ($openingBalanceSar ?? 0) != 0 ? ('SAR ' . number_format($openingBalanceSar, 2)) : '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Bookings Cost:</td>
                                                    <td class="text-end fw-semibold">PKR {{ number_format($bookingPayable, 2) }}</td>
                                                    <td class="text-end fw-semibold col-sar">{{ ($bookingPayableSar ?? 0) > 0 ? ('SAR ' . number_format($bookingPayableSar, 2)) : '—' }}</td>
                                                </tr>
                                                <tr class="border-top">
                                                    <td><strong>Total Payable:</strong></td>
                                                    <td class="text-end fw-bold text-primary">PKR {{ number_format($totalPayable, 2) }}</td>
                                                    <td class="text-end fw-bold text-primary col-sar">{{ ($totalPayableSar ?? 0) > 0 ? ('SAR ' . number_format($totalPayableSar, 2)) : '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Paid / Disbursed:</td>
                                                    <td class="text-end fw-bold text-success">- PKR {{ number_format($totalPaid, 2) }}</td>
                                                    <td class="text-end fw-bold text-success col-sar">{{ ($totalPaidSar ?? 0) > 0 ? ('- SAR ' . number_format($totalPaidSar, 2)) : '—' }}</td>
                                                </tr>
                                                <tr class="border-top border-2">
                                                    <td><strong class="fs-15">Net Balance Due:</strong></td>
                                                    <td class="text-end fw-bold fs-15 {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                                        PKR {{ number_format($balanceDue, 2) }}
                                                    </td>
                                                    <td class="text-end fw-bold fs-15 col-sar {{ ($balanceDueSar ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                        @if(isset($balanceDueSar) && abs($balanceDueSar) > 0)
                                                            SAR {{ number_format(abs($balanceDueSar), 2) }} {{ $balanceDueSar > 0 ? 'Due' : 'Adv' }}
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
                                                <th colspan="3" class="text-center bg-soft-primary text-primary border-bottom-0 py-1">PKR (Pakistani Rupee)</th>
                                                <th colspan="3" class="text-center bg-soft-warning text-dark border-bottom-0 py-1 col-sar">SAR (Saudi Riyal)</th>
                                                <th rowspan="2" class="no-print text-center align-middle" style="width: 45px;">Proof</th>
                                            </tr>
                                            <tr>
                                                <th class="text-end text-success py-1" style="min-width: 95px;" title="Payments made to supplier">Debit (Dr) Paid</th>
                                                <th class="text-end text-danger py-1" style="min-width: 95px;" title="Services cost billed by supplier">Credit (Cr) Cost</th>
                                                <th class="text-end py-1 text-dark" style="min-width: 95px;">Balance PKR</th>
                                                <th class="text-end text-success py-1 col-sar" style="min-width: 85px;">Debit (Dr) Paid</th>
                                                <th class="text-end text-danger py-1 col-sar" style="min-width: 85px;">Credit (Cr) Cost</th>
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
                                                    <td class="text-end text-muted">—</td>
                                                    <td class="text-end fw-bold text-danger">
                                                        {{ number_format(abs($openingBalancePkr), 2) }}
                                                    </td>
                                                    <td class="text-end fw-bold text-dark">
                                                        {{ number_format(abs($openingBalancePkr), 2) }}
                                                        <small class="badge {{ $openingBalancePkr >= 0 ? 'bg-danger' : 'bg-success' }} text-white ms-1" style="font-size: 9px;">
                                                            {{ $openingBalancePkr >= 0 ? 'Cr' : 'Dr' }}
                                                        </small>
                                                    </td>
                                                    {{-- SAR Columns: Debit, Credit, Balance --}}
                                                    <td class="text-end text-muted col-sar">—</td>
                                                    <td class="text-end fw-bold text-danger col-sar">
                                                        {{ ($openingBalanceSar ?? 0) != 0 ? number_format(abs($openingBalanceSar), 2) : '—' }}
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
                                                        @if(!empty($row['booking_id']))
                                                            <a href="{{ route('booking.show', $row['booking_id']) }}" target="_blank" class="fw-semibold text-primary text-decoration-none">
                                                                {{ $row['description'] }}
                                                            </a>
                                                        @else
                                                            <span class="text-dark fw-medium">{{ $row['description'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                                            {{ $row['payment_method'] }}
                                                        </span>
                                                    </td>
                                                    <!-- PKR Debit (Paid) Column -->
                                                    <td class="text-end fw-bold {{ ($row['debit_pkr'] ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                                        {{ ($row['debit_pkr'] ?? 0) > 0 ? number_format($row['debit_pkr'], 2) : '—' }}
                                                    </td>
                                                    <!-- PKR Credit (Cost) Column -->
                                                    <td class="text-end fw-bold {{ ($row['credit_pkr'] ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                                        {{ ($row['credit_pkr'] ?? 0) > 0 ? number_format($row['credit_pkr'], 2) : '—' }}
                                                    </td>
                                                    <!-- Running Balance PKR Column -->
                                                    <td class="text-end fw-bold text-dark">
                                                        {{ number_format(abs($row['running_balance_pkr'] ?? 0), 2) }}
                                                        <span class="badge {{ ($row['running_balance_pkr'] ?? 0) > 0 ? 'bg-soft-danger text-danger' : (($row['running_balance_pkr'] ?? 0) < 0 ? 'bg-soft-success text-success' : 'bg-light text-muted') }} ms-1" style="font-size: 9px;">
                                                            {{ $row['balance_type'] }}
                                                        </span>
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
                                                    <td colspan="14" class="text-center py-5 text-muted">
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
                                                <td class="text-end text-success fs-13">PKR {{ number_format($periodDebitsPkr ?? 0, 2) }}</td>
                                                <td class="text-end text-danger fs-13">PKR {{ number_format($periodCreditsPkr ?? 0, 2) }}</td>
                                                <td class="text-end text-dark fs-13">PKR {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }}</td>
                                                <td class="text-end text-success fs-13 col-sar">SAR {{ number_format($periodDebitsSar ?? 0, 2) }}</td>
                                                <td class="text-end text-danger fs-13 col-sar">SAR {{ number_format($periodCreditsSar ?? 0, 2) }}</td>
                                                <td class="text-end text-dark fs-13 col-sar">SAR {{ number_format(abs($balanceDueSar ?? 0), 2) }}</td>
                                                <td class="no-print"></td>
                                            </tr>
                                            <tr style="background: #f1f5f9; border-top: 1px solid #cbd5e1;">
                                                <td colspan="7" class="text-end text-uppercase fs-13 text-dark">
                                                    Net Balance Due (Closing):
                                                </td>
                                                <td colspan="3" class="text-end fs-14 {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'text-danger' : 'text-success' }}">
                                                    PKR {{ number_format(abs($balanceDuePkr ?? $balanceDue), 2) }}
                                                    <span class="badge {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} fs-11 ms-1">
                                                        {{ ($balanceDuePkr ?? $balanceDue) > 0 ? 'Cr (Payable)' : (($balanceDuePkr ?? $balanceDue) < 0 ? 'Dr (Advance)' : 'Nil') }}
                                                    </span>
                                                </td>
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
    function toggleSupplierSar(show) {
        document.querySelectorAll('.col-sar').forEach(function(el) {
            el.style.display = show ? '' : 'none';
        });
        try {
            localStorage.setItem('supplier_ledger_show_sar', show ? 'true' : 'false');
        } catch(e) {}
    }

    document.addEventListener('DOMContentLoaded', function() {
        try {
            const savedSar = localStorage.getItem('supplier_ledger_show_sar');
            const toggle = document.getElementById('toggleSupplierSarColumns');
            if (toggle && savedSar !== null) {
                const show = savedSar === 'true';
                toggle.checked = show;
                toggleSupplierSar(show);
            }
        } catch(e) {}
    });

    function downloadLedgerPDF() {
        const element = document.getElementById('ledgerBox');
        const btn = document.getElementById('exportPDF');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Generating...';
        btn.disabled = true;

        const opt = {
            margin: 0.2,
            filename: 'Supplier-Ledger-{{ Str::slug($supplier->name) }}-{{ date("Y-m-d") }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' },
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }).catch(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>

@endsection
