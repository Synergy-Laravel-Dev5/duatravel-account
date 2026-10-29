@extends('layout.master')

@section('title', 'Daily Sales & Transactions Report')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE HEADER & DATE FILTER -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-2 gap-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-0 text-dark">
                        <i class="mdi mdi-calendar-today text-primary me-1"></i> Daily Sales & Transactions Report
                    </h4>
                    <p class="text-muted fs-13 mb-0">Real-time daily activity log showing all bookings, sales, receipts, disbursements, and expenses with exact timestamps.</p>
                </div>
                
                <!-- DATE SELECTOR -->
                <form action="{{ route('reports.daily') }}" method="GET" class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="mdi mdi-calendar"></i></span>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $reportDate }}" onchange="this.form.submit()">
                    </div>
                    <a href="{{ route('reports.daily', ['date' => date('Y-m-d')]) }}" class="btn btn-sm {{ $reportDate == date('Y-m-d') ? 'btn-primary' : 'btn-outline-primary' }}">
                        Today
                    </a>
                    <a href="{{ route('reports.daily', ['date' => date('Y-m-d', strtotime('-1 day'))]) }}" class="btn btn-sm {{ $reportDate == date('Y-m-d', strtotime('-1 day')) ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Yesterday
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-dark">
                        <i class="mdi mdi-printer me-1"></i> Print
                    </button>
                </form>
            </div>

            <!-- DAILY SUMMARY METRICS -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Total Sales Booked</span>
                                    <h4 class="fs-18 fw-bold text-primary mb-0 mt-1">PKR {{ number_format($totalSalesPkr, 2) }}</h4>
                                    <small class="text-muted">{{ number_format($totalSalesSar, 2) }} SAR &bull; {{ $bookings->count() }} Bookings ({{ $totalPaxToday }} Pax)</small>
                                </div>
                                <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-cart-arrow-down fs-20 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Total Inflow (Receipts)</span>
                                    <h4 class="fs-18 fw-bold text-success mb-0 mt-1">PKR {{ number_format($totalInflowPkr, 2) }}</h4>
                                    <small class="text-muted">{{ number_format($totalInflowSar, 2) }} SAR Inflow</small>
                                </div>
                                <div class="avatar-sm bg-soft-success rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-arrow-down-bold-circle-outline fs-20 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Total Outflow (Payments)</span>
                                    <h4 class="fs-18 fw-bold text-danger mb-0 mt-1">PKR {{ number_format($totalOutflowPkr, 2) }}</h4>
                                    <small class="text-muted">{{ number_format($totalOutflowSar, 2) }} SAR Outflow</small>
                                </div>
                                <div class="avatar-sm bg-soft-danger rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-arrow-up-bold-circle-outline fs-20 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 {{ $netCashFlowPkr >= 0 ? 'border-info' : 'border-warning' }}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Net Daily Cash Flow</span>
                                    <h4 class="fs-18 fw-bold {{ $netCashFlowPkr >= 0 ? 'text-info' : 'text-warning' }} mb-0 mt-1">
                                        PKR {{ number_format($netCashFlowPkr, 2) }}
                                    </h4>
                                    <small class="text-muted">{{ $netCashFlowPkr >= 0 ? 'Positive Balance' : 'Negative Cash Flow' }}</small>
                                </div>
                                <div class="avatar-sm {{ $netCashFlowPkr >= 0 ? 'bg-soft-info text-info' : 'bg-soft-warning text-warning' }} rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-scale-balance fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: DAILY BOOKINGS / SALES -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fs-14 fw-bold mb-0 text-dark">
                        <i class="mdi mdi-briefcase-check text-primary me-1"></i> 1. Today's Bookings & Sales ({{ $bookings->count() }})
                    </h5>
                    <span class="badge bg-soft-primary text-primary fs-12">{{ $dateObj->format('d M, Y (l)') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th># Booking</th>
                                    <th>Client / Company</th>
                                    <th>Category</th>
                                    <th>Pax</th>
                                    <th>Hotels & Flight Details</th>
                                    <th class="text-end">Total Sale (PKR)</th>
                                    <th class="text-end">Total Sale (SAR)</th>
                                    <th class="text-end">Advance Recv.</th>
                                    <th class="text-end">Balance Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $b)
                                    @php
                                        $roe = (float) ($b->flight_sale_roe > 1 ? $b->flight_sale_roe : ($b->flight_roe > 1 ? $b->flight_roe : 75));
                                        $amtPkr = strtoupper($b->currency) === 'SAR' ? round($b->total_amount * $roe, 2) : (float)$b->total_amount;
                                        $amtSar = strtoupper($b->currency) === 'SAR' ? (float)$b->total_amount : (($roe > 1) ? round($b->total_amount / $roe, 2) : 0);
                                        $recvPkr = strtoupper($b->currency) === 'SAR' ? round($b->total_received * $roe, 2) : (float)$b->total_received;
                                        $balPkr = $amtPkr - $recvPkr;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('booking.edit', $b->id) }}" class="fw-bold text-primary font-monospace">
                                                {{ $b->voucher_number ?: ('#BK-' . $b->id) }}
                                            </a>
                                            <small class="d-block text-muted">{{ $b->created_at ? $b->created_at->format('h:i A') : '' }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-dark">{{ $b->client->name ?? '—' }}</strong>
                                            @if($b->client && $b->client->phone)
                                                <small class="d-block text-muted">{{ $b->client->phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $b->booking_category === 'domestic' ? 'bg-soft-success text-success' : ($b->booking_category === 'international' ? 'bg-soft-info text-info' : 'bg-soft-primary text-primary') }} text-uppercase">
                                                {{ $b->booking_category ?: ($b->package_type ?: 'Umrah') }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ $b->no_of_pax ?: 1 }}</td>
                                        <td>
                                            @if($b->hotels && $b->hotels->count() > 0)
                                                <div class="text-truncate" style="max-width: 250px;">
                                                    <i class="mdi mdi-hotel text-warning me-1"></i>
                                                    {{ $b->hotels->pluck('hotel_name')->filter()->implode(', ') }}
                                                </div>
                                            @endif
                                            @if($b->departure_flight || $b->departure_airline)
                                                <small class="text-muted d-block">
                                                    <i class="mdi mdi-airplane text-info me-1"></i>
                                                    {{ $b->departure_airline }} {{ $b->departure_flight }} (PNR: {{ $b->departure_pnr ?? '—' }})
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($amtPkr, 2) }}</td>
                                        <td class="text-end fw-semibold text-muted">{{ number_format($amtSar, 2) }}</td>
                                        <td class="text-end text-success fw-semibold">{{ number_format($recvPkr, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($balPkr, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-3 text-muted">
                                            No bookings recorded for this day.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ALL DAILY TRANSACTIONS IN RIGHT TIME -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fs-14 fw-bold mb-0 text-dark">
                        <i class="mdi mdi-clock-check-outline text-success me-1"></i> 2. All Transactions in Right Time ({{ count($transactionsList) }})
                    </h5>
                    <span class="text-muted fs-12">Listed in exact chronological sequence (latest first)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Time</th>
                                    <th>Voucher / Ref #</th>
                                    <th>Flow</th>
                                    <th>Category / Type</th>
                                    <th>Party / Payee / Account</th>
                                    <th>Payment Mode</th>
                                    <th class="text-end">Amount (PKR)</th>
                                    <th class="text-end">Amount (SAR)</th>
                                    <th class="text-center">ROE</th>
                                    <th>Description / Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactionsList as $t)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-12">
                                                <i class="mdi mdi-clock-outline me-1 text-primary"></i> {{ $t['time'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="font-monospace text-dark">{{ $t['voucher_no'] }}</strong>
                                        </td>
                                        <td>
                                            @if($t['flow'] === 'inflow')
                                                <span class="badge bg-soft-success text-success px-2 py-1">
                                                    <i class="mdi mdi-arrow-down me-1"></i> Inflow (Cr)
                                                </span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger px-2 py-1">
                                                    <i class="mdi mdi-arrow-up me-1"></i> Outflow (Dr)
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $t['type_label'] }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-primary">{{ $t['party_name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $t['payment_method'] }}</span>
                                        </td>
                                        <td class="text-end fw-bold {{ $t['flow'] === 'inflow' ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($t['amount_pkr'], 2) }}
                                        </td>
                                        <td class="text-end fw-semibold text-muted">
                                            {{ $t['amount_sar'] > 0 ? number_format($t['amount_sar'], 2) : '—' }}
                                        </td>
                                        <td class="text-center text-muted fs-12">
                                            {{ $t['roe'] > 1 ? $t['roe'] : '—' }}
                                        </td>
                                        <td class="text-muted fs-12" style="max-width: 280px;">
                                            {{ $t['description'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-cash-multiple fs-36 text-secondary d-block mb-1"></i>
                                            No transactions recorded for this day.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($transactionsList) > 0)
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="6" class="text-end">TOTALS FOR {{ $dateObj->format('d M, Y') }}:</td>
                                        <td class="text-end">
                                            <span class="text-success d-block">+{{ number_format($totalInflowPkr, 2) }}</span>
                                            <span class="text-danger d-block">-{{ number_format($totalOutflowPkr, 2) }}</span>
                                            <span class="text-dark border-top pt-1 d-block">Net: {{ number_format($netCashFlowPkr, 2) }}</span>
                                        </td>
                                        <td class="text-end text-muted">
                                            <span class="text-success d-block">+{{ number_format($totalInflowSar, 2) }}</span>
                                            <span class="text-danger d-block">-{{ number_format($totalOutflowSar, 2) }}</span>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
