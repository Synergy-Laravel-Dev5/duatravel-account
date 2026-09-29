@extends('layout.master')
@section('title', 'Quotation Details')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- Top Header Actions -->
                <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                    <div>
                        <h4 class="fs-18 fw-semibold mb-1">
                            Quotation Proposal <span class="text-primary">{{ $quotation->quotation_number }}</span>
                        </h4>
                        <span class="badge bg-{{ $quotation->status == 'confirmed' ? 'success' : ($quotation->status == 'sent' ? 'info' : 'warning') }} px-2 py-1">
                            Status: {{ ucfirst($quotation->status) }}
                        </span>
                    </div>
                    <div class="d-inline-flex gap-2 align-items-center">
                        <a href="{{ route('quotation.pdf', $quotation->id) }}" class="btn btn-danger btn-sm" download>
                            <i class="mdi mdi-file-pdf-box me-1"></i> Download PDF
                        </a>
                        <a href="{{ route('quotation.pdf', ['id' => $quotation->id, 'print' => 1]) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="mdi mdi-printer me-1"></i> Print / Preview
                        </a>
                        <a href="{{ route('quotation.edit', $quotation->id) }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-pencil me-1"></i> Edit Quotation
                        </a>
                        <a href="{{ route('quotation.index') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body p-4">

                        <!-- Proposal Header -->
                        <div class="row border-bottom pb-3 mb-4 align-items-center">
                            <div class="col-md-6 d-flex align-items-center">
                                <img src="{{ session('dashboard_package') == 'hajj' ? asset('assets/images/logo/kgm.png') : asset('assets/images/logo/logo.png') }}"
                                     onerror="this.src='{{ asset('assets/images/logo.jpeg') }}'"
                                     alt="Logo" style="height: 60px; max-width: 80px; object-fit: contain; margin-right: 15px;">
                                <div>
                                    <h4 class="fw-bold text-primary mb-1">{{ session('dashboard_package') == 'hajj' ? 'KGM (Karwan-e-Ghulamane Mustafa)' : 'Dua Travels & Tours' }}</h4>
                                    <p class="text-muted fs-13 mb-0">Hajj & Umrah Tour Operators & Luxury Services</p>
                                    <p class="text-muted fs-13 mb-0">Proposal Ref: <strong>{{ $quotation->quotation_number }}</strong> | Title: {{ $quotation->quotation_title }}</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <h5 class="fw-bold text-dark mb-1">Client / Lead Information</h5>
                                <p class="fw-semibold mb-0">{{ $quotation->client_name ?? (optional($quotation->lead)->contact_person ?? 'Valued Client') }}</p>
                                <p class="text-muted fs-13 mb-0">Phone: {{ $quotation->client_phone ?? (optional($quotation->lead)->phone ?? '-') }} | Pax: <strong>{{ $quotation->total_pax }} Person(s)</strong></p>
                                <p class="text-muted fs-13 mb-0">Date: {{ $quotation->created_at ? $quotation->created_at->format('d M Y, h:i A') : '-' }}</p>
                            </div>
                        </div>

                        <!-- 1. Accommodations -->
                        @if ($quotation->accommodations && $quotation->accommodations->count() > 0)
                            <h5 class="fs-15 fw-bold text-primary mb-2">
                                <i class="mdi mdi-hotel me-1"></i> 1. Hotel Accommodations & Room Views
                            </h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>City</th>
                                            <th>Hotel Name</th>
                                            <th>Check-In / Out</th>
                                            <th>Nights</th>
                                            <th>Room Type</th>
                                            <th>Room View</th>
                                            <th>Confirmation #</th>
                                            <th>Meal Plan</th>
                                            <th>Rooms</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->accommodations as $acc)
                                            <tr>
                                                <td class="fw-semibold">{{ $acc->city }}</td>
                                                <td>{{ $acc->hotel_name ?? '-' }}</td>
                                                <td>
                                                    {{ $acc->check_in ? date('d M Y', strtotime($acc->check_in)) : '-' }} &rarr;
                                                    {{ $acc->check_out ? date('d M Y', strtotime($acc->check_out)) : '-' }}
                                                </td>
                                                <td>{{ $acc->number_of_nights }} N</td>
                                                <td>
                                                    {{ $acc->room_type }}
                                                    @if($acc->room_type === 'Sharing' && ($acc->no_of_beds || $acc->male_beds || $acc->female_beds))
                                                        <br><span class="badge bg-soft-info text-info">Beds: {{ $acc->no_of_beds }} ({{ $acc->male_beds }}M / {{ $acc->female_beds }}F)</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ $acc->room_view }}</span></td>
                                                <td><code>{{ $acc->confirmation_number ?? '-' }}</code></td>
                                                <td>{{ $acc->meal_plan }}</td>
                                                <td>{{ $acc->no_of_rooms }}</td>
                                                <td class="text-end fw-bold text-success">PKR {{ number_format($acc->selling_amount_pkr) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- 2. Separate Meal Plan Facility -->
                        @if ($quotation->mealPlans && $quotation->mealPlans->count() > 0)
                            <h5 class="fs-15 fw-bold text-primary mb-2">
                                <i class="mdi mdi-silverware-fork-knife me-1"></i> 2. Separate Meal Plan Facilities
                            </h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Meal Facility Type</th>
                                            <th>City</th>
                                            <th>Supplier / Provider</th>
                                            <th>Pax</th>
                                            <th>Days</th>
                                            <th>Rate / Person</th>
                                            <th>ROE</th>
                                            <th class="text-end">Total Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->mealPlans as $meal)
                                            <tr>
                                                <td class="fw-semibold">{{ $meal->meal_type }}</td>
                                                <td>{{ $meal->city }}</td>
                                                <td>{{ $meal->provider_name ?? '-' }}</td>
                                                <td>{{ $meal->pax }}</td>
                                                <td>{{ $meal->days }}</td>
                                                <td>{{ $meal->currency }} {{ number_format($meal->selling_rate, 2) }}</td>
                                                <td>{{ number_format($meal->exchange_rate, 2) }}</td>
                                                <td class="text-end fw-bold text-success">PKR {{ number_format($meal->total_sale_pkr) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- 3. Transfers -->
                        @if ($quotation->transfers && $quotation->transfers->count() > 0)
                            <h5 class="fs-15 fw-bold text-primary mb-2">
                                <i class="mdi mdi-car-multiple me-1"></i> 3. Ground Transfers & Transport Sectors
                            </h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sector Route</th>
                                            <th>Vehicle Type</th>
                                            <th>Date & Time</th>
                                            <th>Quantity (Vehicles)</th>
                                            <th>Confirmation #</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->transfers as $tr)
                                            <tr>
                                                <td class="fw-semibold text-primary">{{ $tr->sector }}</td>
                                                <td>{{ $tr->vehicle_type }}</td>
                                                <td>{{ $tr->transfer_date ?? '-' }}</td>
                                                <td>{{ $tr->quantity }} Vehicle(s)</td>
                                                <td><code>{{ $tr->confirmation_number ?? '-' }}</code></td>
                                                <td class="text-end fw-bold text-success">PKR {{ number_format($tr->selling_amount_pkr) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- 4. Tours & Sightseeing -->
                        @if ($quotation->tours && $quotation->tours->count() > 0)
                            <h5 class="fs-15 fw-bold text-primary mb-2">
                                <i class="mdi mdi-map-marker-distance me-1"></i> 4. Tours, Sightseeing & Holy Ziyarat
                            </h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tour / Ziyarat Name</th>
                                            <th>City</th>
                                            <th>Sector / Sites</th>
                                            <th>Tour Date</th>
                                            <th>Quantity / Pax</th>
                                            <th>Guide</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->tours as $tour)
                                            <tr>
                                                <td class="fw-semibold">{{ $tour->tour_name }}</td>
                                                <td>{{ $tour->city }}</td>
                                                <td>{{ $tour->sector ?? '-' }}</td>
                                                <td>{{ $tour->tour_date ?? '-' }}</td>
                                                <td>{{ $tour->quantity }}</td>
                                                <td><span class="badge bg-soft-success text-success">{{ $tour->guide_included ? 'Guide Included' : 'No Guide' }}</span></td>
                                                <td class="text-end fw-bold text-success">PKR {{ number_format($tour->selling_amount_pkr) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- 5. Domestic Flights / Train -->
                        @if ($quotation->flightsTrains && $quotation->flightsTrains->count() > 0)
                            <h5 class="fs-15 fw-bold text-primary mb-2">
                                <i class="mdi mdi-train-car me-1"></i> 5. Domestic Flight / High Speed Train
                            </h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Service Type</th>
                                            <th>From &rarr; To</th>
                                            <th>Travel Date</th>
                                            <th>Class</th>
                                            <th>Tickets</th>
                                            <th>Ticket / PNR #</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->flightsTrains as $ft)
                                            <tr>
                                                <td class="fw-semibold">{{ $ft->service_type }}</td>
                                                <td>{{ $ft->from_location }} &rarr; {{ $ft->to_location }}</td>
                                                <td>{{ $ft->travel_date ?? '-' }}</td>
                                                <td>{{ $ft->class_type }}</td>
                                                <td>{{ $ft->pax_count }} Pax</td>
                                                <td><code>{{ $ft->ticket_number_pnr ?? '-' }}</code></td>
                                                <td class="text-end fw-bold text-success">PKR {{ number_format($ft->selling_amount_pkr) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- 6. Visa Charges -->
                        @if ($quotation->visas && $quotation->visas->count() > 0)
                            <h5 class="fs-15 fw-bold text-primary mb-2">
                                <i class="mdi mdi-passport me-1"></i> 6. Saudi Visa Issuance & Passenger Details
                            </h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Passenger Name</th>
                                            <th>Type</th>
                                            <th>Gender</th>
                                            <th>Passport #</th>
                                            <th>Visa Category</th>
                                            <th>ROE</th>
                                            <th class="text-end">Selling Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->visas as $visa)
                                            <tr>
                                                <td class="fw-semibold">{{ $visa->passenger_name }}</td>
                                                <td><span class="badge bg-soft-primary text-primary">{{ $visa->passenger_type }}</span></td>
                                                <td>{{ $visa->gender }}</td>
                                                <td><code>{{ $visa->passport_number ?? '-' }}</code></td>
                                                <td>{{ $visa->visa_type }}</td>
                                                 <td>{{ number_format($visa->exchange_rate, 2) }}</td>
                                                <td class="text-end fw-bold text-success">PKR {{ number_format($visa->selling_amount_pkr) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- 7. Passenger Service Fee (PSF) -->
                        @if ($quotation->psf_cost_pkr > 0 || $quotation->psf_selling_pkr > 0 || $quotation->psf_description)
                            <h5 class="fs-15 fw-bold text-primary mb-2">
                                <i class="mdi mdi-cash-multiple me-1"></i> 7. PSF
                            </h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle fs-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Description</th>
                                            <th>Cost Amount (PKR)</th>
                                            @if ($quotation->psf_supplier_amount > 0)
                                                <th>Supplier Payable (PKR)</th>
                                            @endif
                                            <th class="text-end">Selling Price (PKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">{{ $quotation->psf_description ?: 'PSF' }}</td>
                                            <td class="fw-semibold text-danger">PKR {{ number_format($quotation->psf_cost_pkr) }}</td>
                                            @if ($quotation->psf_supplier_amount > 0)
                                                <td class="fw-semibold text-warning">PKR {{ number_format($quotation->psf_supplier_amount) }}</td>
                                            @endif
                                            <td class="text-end fw-bold text-success">PKR {{ number_format($quotation->psf_selling_pkr) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- 8. Complete Summary & Grand Totals -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="card border border-secondary shadow-none h-100">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="fs-14 fw-bold mb-0">Inclusions & Conditions</h6>
                                    </div>
                                    <div class="card-body fs-13">
                                        <p class="fw-semibold text-primary mb-1"><i class="mdi mdi-check-all me-1"></i> Inclusions:</p>
                                        <div class="text-muted mb-3">{!! $quotation->inclusions ?? '• Standard Luxury Package Inclusions' !!}</div>

                                        <p class="fw-semibold text-primary mb-1"><i class="mdi mdi-file-document-outline me-1"></i> Terms & Conditions:</p>
                                        <div class="text-muted mb-0">{!! $quotation->terms_and_conditions ?? '• Rates subject to room availability upon confirmation.' !!}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border border-primary shadow-none h-100">
                                    <div class="card-header bg-primary bg-opacity-10 py-2">
                                        <h6 class="fs-14 fw-bold text-primary mb-0">Financial & Pricing Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless fs-13 mb-3">
                                            <tr>
                                                <td>Total Cost (Purchase):</td>
                                                <td class="text-end fw-semibold text-danger">PKR {{ number_format($quotation->total_cost_pkr) }}</td>
                                            </tr>
                                            @if ($quotation->total_supplier_amount > 0)
                                                <tr>
                                                    <td>Total Supplier Payable:</td>
                                                    <td class="text-end fw-semibold text-warning">PKR {{ number_format($quotation->total_supplier_amount) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>Total Selling Price:</td>
                                                <td class="text-end fw-semibold text-success">PKR {{ number_format($quotation->total_sale_pkr) }}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td class="fw-bold">Total Net Profit:</td>
                                                <td class="text-end fw-bold text-primary">PKR {{ number_format($quotation->total_profit_pkr) }} ({{ $quotation->margin_percentage }}%)</td>
                                            </tr>
                                        </table>

                                        <div class="bg-primary text-white rounded p-3 text-center">
                                            <span class="fs-12 text-white-50">Selling Price Per Person</span>
                                            <h3 class="text-warning fw-bold mb-0">PKR {{ number_format($quotation->per_pax_sale_pkr) }}</h3>
                                            <small class="text-white-50">For {{ $quotation->total_pax }} Pax Total</small>
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
@endsection
