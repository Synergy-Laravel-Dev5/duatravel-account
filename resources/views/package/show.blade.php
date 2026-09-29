@extends('layout.master')

@section('title', 'Package Details - ' . ($package->name ?? 'View Package'))

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .pkg-show-page {
            background-color: #f4f6f9;
        }
        .detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
            margin-bottom: 28px;
            overflow: hidden;
        }
        .detail-card-header {
            padding: 18px 24px;
            background: #fafbfc;
            border-bottom: 1px solid #eef1f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .detail-card-title {
            font-size: 15.5px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .detail-card-title i {
            color: #4f46e5;
            background: #eef2ff;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }
        .detail-card-body {
            padding: 24px;
        }
        .badge-arrival {
            background-color: #eef2ff;
            color: #4f46e5;
            font-weight: 700;
            font-size: 11px;
            padding: 6px 14px;
            border-radius: 30px;
            letter-spacing: 0.5px;
        }
        .badge-duration {
            background-color: #f0fdf4;
            color: #16a34a;
            font-weight: 700;
            font-size: 11px;
            padding: 6px 14px;
            border-radius: 30px;
            letter-spacing: 0.5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 24px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            padding: 14px 18px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }
        .info-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .info-value {
            font-size: 14.5px;
            font-weight: 600;
            color: #0f172a;
        }
        .sharing-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }
        .sharing-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            padding: 14px;
            border-bottom: 2px solid #e2e8f0;
        }
        .sharing-table td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #334155;
            font-weight: 600;
        }
        .sharing-table tr:hover td {
            background-color: #f8fafc;
        }
        .sharing-table td.header-cell {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-align: left;
            width: 20%;
        }
        .itinerary-image-container {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 10px;
        }
        .itinerary-image-container img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        .itinerary-image-container:hover img {
            transform: scale(1.03);
        }
        .itinerary-image-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            text-align: center;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }
        .section-separator {
            border-top: 1px dashed #cbd5e1;
            margin: 24px 0;
        }
        .list-unstyled-custom li {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13.5px;
            color: #334155;
        }
        .list-unstyled-custom li:last-child {
            border-bottom: none;
        }
        .table-custom-spacing th {
            padding: 14px 12px !important;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-custom-spacing td {
            padding: 14px 12px !important;
        }
    </style>

    <div class="content-page pkg-show-page">
        <div class="content">
            <div class="container-fluid" style="padding: 20px 24px;">

                {{-- Breadcrumbs & Top Actions --}}
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Package Detail Summary</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('package.edit', $package->id) }}" class="btn btn-success btn-sm px-3">
                            <i class="fa fa-edit me-1"></i> Edit Package
                        </a>
                        <a href="{{ route('package.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="fa fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                {{-- Row: Main Details --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="detail-card">
                            <div class="detail-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff; padding: 22px 28px;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="background: rgba(255,255,255,0.1); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #f59e0b;">
                                        <i class="fa fa-box-open" style="color: inherit; background: none; width: auto; height: auto;"></i>
                                    </div>
                                    <div>
                                        <h3 class="m-0 text-white fw-bold" style="letter-spacing: 0.5px;">{{ $package->name }}</h3>
                                        <span class="text-light opacity-75 fs-13">Package Code: {{ $package->code ?? 'No Code' }}</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    @if($package->medina_arrival)
                                        <span class="badge-arrival">{{ str_replace('_', ' ', strtoupper($package->medina_arrival)) }}</span>
                                    @endif
                                    @if($package->hajj_duration)
                                        <span class="badge-duration">{{ strtoupper($package->hajj_duration) }} HAJJ</span>
                                    @endif
                                </div>
                            </div>

                            <div class="detail-card-body">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Package #</span>
                                        <span class="info-value">{{ $package->package_number ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Code</span>
                                        <span class="info-value">{{ $package->code ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Category</span>
                                        <span class="info-value">{{ $package->category ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Zone</span>
                                        <span class="info-value">{{ $package->zone ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Days</span>
                                        <span class="info-value">{{ $package->days ?? '-' }} Days</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Year</span>
                                        <span class="info-value">{{ $package->year ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Maktab</span>
                                        <span class="info-value">{{ $package->maktab ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Maktab #</span>
                                        <span class="info-value">{{ $package->maktab_number ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Room & Category Information --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-bed"></i> Room & Category Types
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="info-grid" style="grid-template-columns: repeat(3, 1fr); gap: 20px;">
                                    <div class="info-item">
                                        <span class="info-label">Room Type</span>
                                        <span class="info-value">{{ $package->room_type ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Azizia Room Type</span>
                                        <span class="info-value">{{ $package->azizia_room_type ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Makkah Type</span>
                                        <span class="info-value">{{ $package->makkah_type ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Medinah Type</span>
                                        <span class="info-value">{{ $package->medinah_type ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Azizia Type</span>
                                        <span class="info-value">{{ $package->azizia_type ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Mina Type</span>
                                        <span class="info-value">{{ $package->mina_type ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
{{-- Row: Package Information --}}
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-box"></i> Package Information
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Package Type</span>
                                        <span class="info-value">{{ $package->package_type ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Package Amount</span>
                                        <span class="info-value">
                                            @if($package->package_amount)
                                                PKR {{ number_format($package->package_amount) }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Ticket Amount (PKR)</span>
                                        <span class="info-value">
                                            @if($package->ticket_amount)
                                                PKR {{ number_format($package->ticket_amount) }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Extra Facility Amount</span>
                                        <span class="info-value">
                                            @if($package->extra_facilities_amount)
                                                PKR {{ number_format($package->extra_facilities_amount) }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Other Extra Facility</span>
                                        <span class="info-value">{{ $package->other_extra_facilities ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Place of Departure</span>
                                        <span class="info-value">{{ $package->place_of_departure ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Flight Type</span>
                                        <span class="info-value">{{ $package->flight_type ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Flight Class</span>
                                        <span class="info-value">{{ $package->flight_class ?? '-' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Food Included</span>
                                        <span class="info-value">{{ ucfirst($package->food_included ?? '-') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Extra Facilities (Mina/Arafat)</span>
                                        <span class="info-value">{{ ucfirst($package->extra_facilities ?? '-') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Qurbani Included</span>
                                        <span class="info-value">{{ ucfirst($package->qurbani_included ?? '-') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Ticket Included</span>
                                        <span class="info-value">{{ ucfirst($package->ticket_included ?? '-') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Row: Accommodations (Multi) --}}
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-hotel"></i> Accommodation Details
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0 table-custom-spacing" style="font-size: 13px;">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>Place</th>
                                                <th>Hotel</th>
                                                <th>Acc. Type</th>
                                                <th>Rating</th>
                                                <th>Distance</th>
                                                <th>Dates & Duration</th>
                                                <th>Food Package</th>
                                                <th>Ziarat</th>
                                                <th>Details (Camp/Arafat/Sharing)</th>
                                                <th>Note</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($package->accommodations as $acc)
                                                <tr>
                                                    <td class="text-center font-bold">
                                                        <span class="badge bg-primary px-3 py-1.5 text-uppercase" style="font-weight: 700; font-size: 11px;">{{ str_replace('_', ' ', $acc->place ?? '-') }}</span>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $acc->hotel->name ?? '-' }}</strong>
                                                        @if($acc->hotel && $acc->hotel->city)
                                                            <br><small class="text-muted"><i class="fa fa-map-marker-alt"></i> {{ $acc->hotel->city }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ str_replace('_', ' ', $acc->accommodation_type ?? '-') }}</td>
                                                    <td class="text-center text-warning" style="font-size: 14px;">
                                                        @if($acc->saudi_star_rating)
                                                            @for($i = 0; $i < intval($acc->saudi_star_rating); $i++)
                                                                ★
                                                            @endfor
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="text-center fw-semibold">{{ $acc->distance ? $acc->distance . 'm' : '-' }}</td>
                                                    <td>
                                                        <span class="text-nowrap"><strong>Check-In:</strong> {{ $acc->check_in ? $acc->check_in->format('d M, Y') : '-' }}</span><br>
                                                        <span class="text-nowrap"><strong>Check-Out:</strong> {{ $acc->check_out ? $acc->check_out->format('d M, Y') : '-' }}</span><br>
                                                        @if($acc->azizia_date)
                                                            <span class="text-nowrap"><strong>Azizia Date:</strong> {{ $acc->azizia_date->format('d M, Y') }}</span><br>
                                                        @endif
                                                        <span class="badge bg-info-subtle text-info mt-1" style="font-weight: 600;">{{ $acc->days ?? '-' }} Days / {{ $acc->nights ?? '-' }} Nights</span>
                                                    </td>
                                                    <td class="text-center">{{ str_replace('_', ' ', $acc->food_package ?? '-') }}</td>
                                                    <td>
                                                        <small class="d-block"><strong>Makkah:</strong> {{ ucfirst($acc->makkah_ziarat ?? '-') }}</small>
                                                        <small class="d-block"><strong>Madinah:</strong> {{ ucfirst($acc->madinah_ziarat ?? '-') }}</small>
                                                    </td>
                                                    <td>
                                                        @if($acc->camp)<small class="d-block"><strong>Camp:</strong> {{ $acc->camp }}</small>@endif
                                                        @if($acc->arafat)<small class="d-block"><strong>Arafat:</strong> {{ $acc->arafat }}</small>@endif
                                                        @if($acc->shuttle)<small class="d-block"><strong>Shuttle:</strong> {{ $acc->shuttle }}</small>@endif
                                                        @if($acc->bedding)<small class="d-block"><strong>Bedding:</strong> {{ $acc->bedding }}</small>@endif
                                                        @if($acc->sharing || $acc->sharing_type)
                                                            <small class="d-block"><strong>Sharing:</strong> {{ $acc->sharing }} ({{ $acc->sharing_type }})</small>
                                                        @endif
                                                    </td>
                                                    <td><small class="text-muted">{{ $acc->note ?? '-' }}</small></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center py-4 text-muted">No accommodations registered for this package.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Transports (Multi) --}}
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-bus"></i> Transport Details
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0 table-custom-spacing" style="font-size: 13px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Route</th>
                                                <th>Arrival Date & Time</th>
                                                <th>Type</th>
                                                <th>Vehicle</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($package->transports as $tr)
                                                <tr>
                                                    <td><strong>{{ $tr->route ?? '-' }}</strong></td>
                                                    <td>
                                                        @if($tr->arrival_date)
                                                            {{ \Carbon\Carbon::parse($tr->arrival_date)->format('d M, Y') }}
                                                            @if($tr->arrival_time)
                                                                <small class="text-muted">({{ \Carbon\Carbon::parse($tr->arrival_time)->format('h:i A') }})</small>
                                                            @endif
                                                        @elseif($tr->arrival)
                                                            {{ $tr->arrival }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ $tr->type ?? '-' }}</td>
                                                    <td>{{ $tr->vehicle ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-3 text-muted">No road transport logs registered.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Flights & Trains (Multi) --}}
                <div class="row mt-2">
                    <div class="col-lg-6">
                        <div class="detail-card h-100">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-plane"></i> Flight Bookings
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Airline/Flight</th>
                                                <th>Route</th>
                                                <th>Schedule</th>
                                                <th>PNR / Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($package->transportFlights as $fl)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $fl->airline ?? '-' }}</strong>
                                                        <br><small class="text-muted">{{ $fl->flight_no ?? '-' }} ({{ $fl->flight_class ?? '-' }})</small>
                                                        @if($fl->is_preferred)
                                                            <br><span class="badge bg-success-subtle text-success mt-1">Preferred Flight</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>Origin:</strong> {{ $fl->origin ?? '-' }}
                                                        <br><strong>Dest:</strong> {{ $fl->destination ?? '-' }}
                                                    </td>
                                                    <td>
                                                        <strong>Dep:</strong> {{ $fl->departure_date ? \Carbon\Carbon::parse($fl->departure_date)->format('d M, Y') : '-' }} {{ $fl->departure_time ? \Carbon\Carbon::parse($fl->departure_time)->format('h:i A') : '' }}
                                                        <br><strong>Arr:</strong> {{ $fl->arrival_date ? \Carbon\Carbon::parse($fl->arrival_date)->format('d M, Y') : '-' }} {{ $fl->arrival_time ? \Carbon\Carbon::parse($fl->arrival_time)->format('h:i A') : '' }}
                                                    </td>
                                                    <td>
                                                        <strong>PNR:</strong> <code class="text-dark fw-bold">{{ $fl->pnr_no ?? '-' }}</code>
                                                        <br><strong>Amt:</strong> <span class="fw-semibold text-primary">{{ $fl->ticket_amount ? 'SAR ' . number_format($fl->ticket_amount, 2) : '-' }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">No flight logs registered.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-card h-100">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-train"></i> Train Details
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Railway/Train</th>
                                                <th>Route</th>
                                                <th>Schedule</th>
                                                <th>PNR / Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($package->transportTrains as $tr)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $tr->railway ?? '-' }}</strong>
                                                        <br><small class="text-muted">{{ $tr->train_no ?? '-' }} ({{ $tr->train_class ?? '-' }})</small>
                                                    </td>
                                                    <td>
                                                        <strong>Origin:</strong> {{ $tr->origin ?? '-' }}
                                                        <br><strong>Dest:</strong> {{ $tr->destination ?? '-' }}
                                                    </td>
                                                    <td>
                                                        <strong>Dep:</strong> {{ $tr->departure_date ? \Carbon\Carbon::parse($tr->departure_date)->format('d M, Y') : '-' }} {{ $tr->departure_time ? \Carbon\Carbon::parse($tr->departure_time)->format('h:i A') : '' }}
                                                        <br><strong>Arr:</strong> {{ $tr->arrival_date ? \Carbon\Carbon::parse($tr->arrival_date)->format('d M, Y') : '-' }} {{ $tr->arrival_time ? \Carbon\Carbon::parse($tr->arrival_time)->format('h:i A') : '' }}
                                                    </td>
                                                    <td>
                                                        <strong>PNR:</strong> <code class="text-dark fw-bold">{{ $tr->pnr_no ?? '-' }}</code>
                                                        <br><strong>Amt:</strong> <span class="fw-semibold text-primary">{{ $tr->ticket_amount ? 'SAR ' . number_format($tr->ticket_amount, 2) : '-' }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">No train logs registered.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Training, Giveaways & Addresses --}}
                <div class="row mt-2">
                    <div class="col-lg-4">
                        <div class="detail-card h-100">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-graduation-cap"></i> Training Sessions
                                </h5>
                            </div>
                            <div class="detail-card-body p-0">
                                <ul class="list-unstyled-custom mb-0">
                                    @forelse($package->trainingSessions as $sess)
                                        <li class="px-4">
                                            <div style="background-color: #f1f5f9; padding: 8px; border-radius: 6px; color: #475569;">
                                                <i class="fa fa-calendar-check"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $sess->name }}</strong>
                                                @if($sess->session_date)
                                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($sess->session_date)->format('d M, Y') }} {{ $sess->session_time ? 'at ' . \Carbon\Carbon::parse($sess->session_time)->format('h:i A') : '' }}</small>
                                                @endif
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-center py-4 text-muted justify-content-center">No training sessions linked.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="detail-card h-100">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-gift"></i> Giveaways & Notes
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <strong>Linked Giveaways:</strong>
                                <ul class="list-unstyled-custom mt-2 mb-3">
                                    @forelse($package->giveaways as $gv)
                                        <li class="px-0 py-2">
                                            <span class="badge bg-info-subtle text-info px-2.5 py-1.5" style="font-weight: 700; font-size: 11px;">{{ $gv->code }}</span>
                                            <span class="fw-semibold ms-2">{{ $gv->name }}</span>
                                        </li>
                                    @empty
                                        <li class="px-0 py-2 text-muted">No giveaways selected.</li>
                                    @endforelse
                                </ul>
                                @if($package->giveaway_note)
                                    <div class="section-separator"></div>
                                    <strong>Giveaway Notes:</strong>
                                    <p class="text-muted fs-13 mt-2 mb-0" style="line-height: 1.5;">{{ $package->giveaway_note }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="detail-card h-100">
                            <div class="detail-card-header">
                                <h5 class="detail-card-title">
                                    <i class="fa fa-map-marker-alt"></i> Mina Address
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                @if($package->maktabAddress)
                                    <div>
                                        <strong class="d-block fs-13 text-dark">Mina Address:</strong>
                                        <p class="text-muted fs-13 mt-1 mb-3" style="line-height: 1.5;">{{ $package->maktabAddress->maktab_address ?? '-' }}</p>

                                        <strong class="d-block fs-13 text-dark">Mina Address:</strong>
                                        <p class="text-muted fs-13 mt-1 mb-0" style="line-height: 1.5;">{{ $package->maktabAddress->office_address ?? '-' }}</p>
                                    </div>
                                @else
                                    <p class="text-muted text-center py-4 my-0">No address details registered.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row: Itinerary Description & Images --}}
                {{-- @if($package->itinerary)
                    <div class="row mt-2">
                        <div class="col-lg-12">
                            <div class="detail-card">
                                <div class="detail-card-header">
                                    <h5 class="detail-card-title">
                                        <i class="fa fa-map-signs"></i> Itinerary Details
                                    </h5>
                                </div>
                                <div class="detail-card-body">
                                    @if($package->itinerary->description)
                                        <p class="fs-14" style="white-space: pre-line; line-height: 1.7; color: #334155;">{{ $package->itinerary->description }}</p>
                                    @endif

                                    @php
                                        $imageFields = [
                                            'mina_image' => 'Mina',
                                            'arafat_image' => 'Arafat',
                                            'muzdalifah_image' => 'Muzdalifah',
                                            'makkah_mina_rami_day_one_image' => 'Makkah/Mina Rami Day 1',
                                            'mina_rami_day_two_image' => 'Mina Rami Day 2',
                                            'mina_makkah_rami_day_three_image' => 'Mina/Makkah Rami Day 3',
                                        ];
                                        $hasImages = false;
                                        foreach($imageFields as $field => $title) {
                                            if($package->itinerary->{$field}) {
                                                $hasImages = true;
                                            }
                                        }
                                    @endphp

                                    @if($hasImages)
                                        <div class="section-separator"></div>
                                        <h6 class="fw-bold text-dark mb-3">Itinerary Attachment Images:</h6>
                                        <div class="row g-4">
                                            @foreach($imageFields as $field => $title)
                                                @if($package->itinerary->{$field})
                                                    <div class="col-md-4 col-sm-6">
                                                        <div class="itinerary-image-container">
                                                            <img src="{{ asset('assets/images/packages/itinerary/' . $package->itinerary->{$field}) }}" alt="{{ $title }}">
                                                            <div class="itinerary-image-title">{{ $title }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif --}}

                {{-- Row: Terms & Conditions --}}
                @if($package->terms && $package->terms->content)
                    <div class="row mt-2">
                        <div class="col-lg-12">
                            <div class="detail-card">
                                <div class="detail-card-header">
                                    <h5 class="detail-card-title">
                                        <i class="fa fa-file-signature"></i> Terms & Conditions
                                    </h5>
                                </div>
                                <div class="detail-card-body">
                                    <div class="fs-14" style="white-space: pre-line; line-height: 1.7; color: #334155;">{{ $package->terms->content }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
