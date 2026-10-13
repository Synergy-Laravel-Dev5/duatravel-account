@extends('layout.master')
@section('title', 'New Booking')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">New Booking</h4>
                    <a href="{{ route('booking.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @php
                    $dashboardPkg = session('dashboard_package', 'umrah');
                    $isUmrah = ($dashboardPkg === 'umrah');
                @endphp

                {{-- Summary Bar --}}
                <div class="booking-summary-bar mb-3 px-3 py-2 d-flex flex-wrap gap-3 align-items-center justify-content-between rounded shadow-sm"
                    style="background:#f0f4ff; border:1px solid #d0d9f0; font-size:13px;">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span><i class="mdi mdi-account-group text-primary"></i> Pax: <strong id="bar_pax">1</strong></span>
                        <span style="{{ $isUmrah ? 'display:none;' : '' }}">|</span>
                        <span style="{{ $isUmrah ? 'display:none;' : '' }}">Package: <strong id="bar_adult">0.00</strong></span>
                        <span>|</span>
                        <span>Flight: <strong id="bar_flight" class="text-dark">SAR 0.00 / PKR 0.00</strong></span>
                        <span>|</span>
                        <span>Hotel: <strong id="bar_hotel" class="text-dark">SAR 0.00 / PKR 0.00</strong></span>
                        <span>|</span>
                        <span>Transport: <strong id="bar_transport" class="text-dark">SAR 0.00 / PKR 0.00</strong></span>
                        <span>|</span>
                        <span>Visa: <strong id="bar_visa" class="text-dark">SAR 0.00 / PKR 0.00</strong></span>
                        <span>|</span>
                        <span class="text-primary fw-bold">Total: <strong id="bar_total">SAR 0.00 / PKR 0.00</strong></span>
                        <span>|</span>
                        <span class="text-danger fw-bold">Balance: <strong id="bar_balance">SAR 0.00 / PKR 0.00</strong></span>
                    </div>
                </div>

                {{-- Draft Restore Banner Container --}}
                <div id="draftAlertContainer"></div>

                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm" enctype="multipart/form-data">
                    @csrf

                    {{-- TAB NAV --}}
                    <ul class="nav nav-tabs booking-tabs mb-0" id="bookingTabs" role="tablist">
                        <li class="nav-item" id="tabNavPackage" style="{{ $isUmrah ? 'display:none;' : '' }}">
                            <a class="nav-link {{ $isUmrah ? '' : 'active' }}" data-bs-toggle="tab" href="#tab-package">
                                <i class="mdi mdi-tag me-1"></i>Package
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $isUmrah ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-details">
                                <i class="mdi mdi-account me-1"></i>Booking Details
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-persons"><i
                                    class="mdi mdi-account-group me-1"></i>Persons</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-flight"><i
                                    class="mdi mdi-airplane me-1"></i>Flight</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-hotel"><i
                                    class="mdi mdi-hotel me-1"></i>Hotel</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-transport"><i
                                    class="mdi mdi-bus me-1"></i>Transport</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-visa"><i
                                    class="mdi mdi-passport me-1"></i>Visa</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-costing"><i
                                    class="mdi mdi-cash me-1"></i>Costing</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-additional-services"><i
                                    class="mdi mdi-plus-circle me-1"></i>Additional Services</a></li>
                    </ul>

                    <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white" id="bookingTabsContent">

                        {{-- TAB 1: Package --}}
                        <div class="tab-pane fade {{ $isUmrah ? '' : 'show active' }}" id="tab-package">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Select Package <span class="text-danger">*</span></label>
                                    <select name="package_id" id="packageSelect" class="form-select" onchange="onPackageSelectChange(this)">
                                        <option value="">-- Select Package --</option>
                                        @foreach ($packages as $pkg)
                                            <option value="{{ $pkg->id }}">
                                                {{ $pkg->name }}{{ $pkg->code ? ' (' . $pkg->code . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Year <span class="text-danger">*</span></label>
                                    <input type="number" name="package_year" id="package_year" class="form-control"
                                        value="{{ old('package_year', date('Y')) }}" min="2000"
                                        max="{{ date('Y') + 5 }}" required>
                                </div>
                                <div class="col-md-6" id="package_name_container" style="{{ $isUmrah ? 'display:none;' : '' }}">
                                    <label class="form-label">Package Name</label>
                                    <input type="text" name="package_name" id="package_name" class="form-control"
                                        placeholder="e.g. Economy Umrah 2025">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Booking Status</label>
                                    <select name="status" class="form-select" readonly>
                                        <option value="pending" selected>Pending</option>
                                    </select>
                                    <input type="hidden" name="package_type" id="package_type" value="{{ $dashboardPkg }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 2: Booking Details --}}
                        <div class="tab-pane fade {{ $isUmrah ? 'show active' : '' }}" id="tab-details">

                            <div class="row g-3">
                                <input type="hidden" name="booking_for" id="booking_for_input" value="client">

                                {{-- CLIENT BLOCK (Primary Selection) --}}
                                <div class="col-md-4" id="clientBlock">
                                    <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                    <select name="client_id" id="clientSelect" class="form-select select2" required>
                                        <option value="">-- Select Client --</option>
                                        @foreach ($clients as $cl)
                                            <option value="{{ $cl->id }}"
                                                data-name="{{ $cl->name }}"
                                                data-phone="{{ $cl->phone }}"
                                                data-cnic="{{ $cl->cnic }}"
                                                data-passport="{{ $cl->passport_number }}"
                                                data-email="{{ $cl->email }}"
                                                data-company="{{ $cl->company_name }}"
                                                {{ old('client_id') == $cl->id ? 'selected' : '' }}>
                                                {{ $cl->name }}{{ $cl->company_name ? ' (' . $cl->company_name . ')' : '' }} - {{ $cl->phone ?? $cl->cnic }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <div class="card bg-light border p-3 mb-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-primary mb-0"><i class="mdi mdi-account-group me-1"></i> Passengers Breakdown</h6>
                                            <span class="badge bg-primary fs-12">Total Pax: <span id="bar_pax_badge">1</span></span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Adults (12+ yrs) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account"></i></span>
                                                    <input type="number" name="adults_count" id="pax_adults" class="form-control pax-breakdown-calc"
                                                        value="{{ old('adults_count', 1) }}" min="1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Children (2-11 yrs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-child"></i></span>
                                                    <input type="number" name="children_count" id="pax_children" class="form-control pax-breakdown-calc"
                                                        value="{{ old('children_count', 0) }}" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Infants (0-2 yrs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-baby-carriage"></i></span>
                                                    <input type="number" name="infants_count" id="pax_infants" class="form-control pax-breakdown-calc"
                                                        value="{{ old('infants_count', 0) }}" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-primary">Total Passengers</label>
                                                <input type="number" name="no_of_pax" id="no_of_pax" class="form-control bg-white fw-bold text-primary"
                                                    value="{{ old('no_of_pax', 1) }}" min="1" readonly required>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2"><i class="mdi mdi-information-outline"></i> Persons, Flight, Hotel, Transport & Visa tabs will update automatically based on passenger breakdown.</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Care Of</label>
                                    <input type="text" name="care_of" class="form-control"
                                        placeholder="Guardian / Agent" value="{{ old('care_of') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Passport Number</label>
                                    <input type="text" name="passport_number" id="fill_passport" class="form-control"
                                        placeholder="Passport #" value="{{ old('passport_number') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">CNIC Number</label>
                                    <input type="text" name="cnic" id="fill_cnic" class="form-control"
                                        placeholder="CNIC #" value="{{ old('cnic') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" id="fill_phone" class="form-control"
                                        placeholder="Phone #" value="{{ old('phone') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Emergency Phone</label>
                                    <input type="text" name="emergency_phone" class="form-control"
                                        placeholder="+92 300 0000000" value="{{ old('emergency_phone') }}">
                                </div>
                                <input type="hidden" name="booking_date" id="booking_date" value="{{ old('booking_date', date('Y-m-d')) }}">
                                <input type="hidden" name="currency" id="booking_currency" value="PKR">
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <div>
                                    @if(!$isUmrah)
                                        <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                                class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 3: Persons --}}
                        <div class="tab-pane fade" id="tab-persons">
                            <div id="personsBookingForNote" class="alert alert-info py-2 px-3 {{ $isUmrah ? 'd-none' : '' }}"
                                style="font-size:13px;">
                                <i class="mdi mdi-information me-1"></i>
                                This is a company booking. The client dropdown is hidden for the main passenger. Please
                                enter the name and passport details manually.
                            </div>
                            <div id="personsList"></div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 4: Flight --}}
                        <div class="tab-pane fade" id="tab-flight">

                            {{-- SUPPLIER & COSTING / SELLING (Dual Currency SAR & PKR + Tier Rates) --}}
                            <div class="card mb-4 border shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h6 class="text-primary mb-0 font-weight-bold">
                                        <i class="mdi mdi-airplane me-1"></i> Flight Supplier & Passenger Tier Rates (SAR & PKR)
                                    </h6>
                                    <span class="badge bg-info text-white">Dual Currency (SAR & PKR)</span>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3 align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-semibold">Flight Supplier / Vendor</span>
                                                <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshSuppliersList(true)" title="Reload Suppliers"><i class="mdi mdi-refresh"></i> Refresh</button>
                                            </label>
                                            <select name="flight_supplier_id" id="flight_supplier_id" class="form-select">
                                                <option value="">-- Select Flight Supplier --</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-primary fw-bold">Flight ROE (Rate of Exchange: SAR to PKR) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white fw-bold">1 SAR =</span>
                                                <input type="number" step="0.0001" name="flight_roe" id="flight_roe"
                                                    class="form-control flight-roe fw-bold text-primary" placeholder="75.00" value="75">
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Passenger Tier Rates Table --}}
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm align-middle text-center mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 20%;">Pax Category</th>
                                                    <th style="width: 10%;">Pax Count</th>
                                                    <th style="width: 18%;" class="text-danger">Cost Rate (SAR)</th>
                                                    <th style="width: 17%;" class="text-danger">Total Cost (SAR)</th>
                                                    <th style="width: 18%;" class="text-success">Sell Rate (SAR)</th>
                                                    <th style="width: 17%;" class="text-success">Total Sell (SAR)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-start fw-semibold"><i class="mdi mdi-account text-primary me-1"></i> Adult (12+ yrs)</td>
                                                    <td><span class="badge bg-secondary fs-12" id="flight_adult_pax_label">1</span></td>
                                                    <td>
                                                        <input type="number" step="0.01" name="flight_adult_cost" id="flight_adult_cost"
                                                            class="form-control form-control-sm text-end flight-tier-calc" placeholder="0.00" value="0">
                                                    </td>
                                                    <td class="text-end fw-bold text-danger"><span id="flight_adult_cost_sar_disp">0.00</span> SAR</td>
                                                    <td>
                                                        <input type="number" step="0.01" name="flight_adult_sell" id="flight_adult_sell"
                                                            class="form-control form-control-sm text-end flight-tier-calc" placeholder="0.00" value="0">
                                                    </td>
                                                    <td class="text-end fw-bold text-success"><span id="flight_adult_sell_sar_disp">0.00</span> SAR</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-start fw-semibold"><i class="mdi mdi-account-child text-info me-1"></i> Child (2-11 yrs)</td>
                                                    <td><span class="badge bg-secondary fs-12" id="flight_child_pax_label">0</span></td>
                                                    <td>
                                                        <input type="number" step="0.01" name="flight_child_cost" id="flight_child_cost"
                                                            class="form-control form-control-sm text-end flight-tier-calc" placeholder="0.00" value="0">
                                                    </td>
                                                    <td class="text-end fw-bold text-danger"><span id="flight_child_cost_sar_disp">0.00</span> SAR</td>
                                                    <td>
                                                        <input type="number" step="0.01" name="flight_child_sell" id="flight_child_sell"
                                                            class="form-control form-control-sm text-end flight-tier-calc" placeholder="0.00" value="0">
                                                    </td>
                                                    <td class="text-end fw-bold text-success"><span id="flight_child_sell_sar_disp">0.00</span> SAR</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-start fw-semibold"><i class="mdi mdi-baby-carriage text-warning me-1"></i> Infant (0-2 yrs)</td>
                                                    <td><span class="badge bg-secondary fs-12" id="flight_infant_pax_label">0</span></td>
                                                    <td>
                                                        <input type="number" step="0.01" name="flight_infant_cost" id="flight_infant_cost"
                                                            class="form-control form-control-sm text-end flight-tier-calc" placeholder="0.00" value="0">
                                                    </td>
                                                    <td class="text-end fw-bold text-danger"><span id="flight_infant_cost_sar_disp">0.00</span> SAR</td>
                                                    <td>
                                                        <input type="number" step="0.01" name="flight_infant_sell" id="flight_infant_sell"
                                                            class="form-control form-control-sm text-end flight-tier-calc" placeholder="0.00" value="0">
                                                    </td>
                                                    <td class="text-end fw-bold text-success"><span id="flight_infant_sell_sar_disp">0.00</span> SAR</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Flight Dual Currency Totals Display --}}
                                    <div class="row g-2 p-2 rounded bg-light border">
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Cost (SAR)</small>
                                                <strong class="text-danger fs-14" id="flight_cost_sar_total">0.00 SAR</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Cost (PKR)</small>
                                                <strong class="text-danger fs-14" id="flight_cost_pkr_total">0.00 PKR</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Sell (SAR)</small>
                                                <strong class="text-success fs-14" id="flight_sell_sar_total">0.00 SAR</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Sell (PKR)</small>
                                                <strong class="text-success fs-14" id="flight_sell_pkr_total">0.00 PKR</strong>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hidden fields for Controller compatibility --}}
                                    <input type="hidden" name="flight_cost_rate" id="flight_cost_rate" value="0">
                                    <input type="hidden" name="flight_cost" id="flight_cost" value="0">
                                    <input type="hidden" name="flight_selling_rate" id="flight_selling_rate" value="0">
                                    <input type="hidden" name="flight_charges" id="flight_charges" value="0">
                                    <input type="hidden" name="flight_charges_disp" id="flight_charges_disp" value="0">
                                </div>
                            </div>

                            {{-- DEPARTURE --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted border-bottom pb-2">
                                        <i class="mdi mdi-airplane-takeoff me-1"></i> Departure Info
                                    </h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Departure</label>
                                    <input type="date" name="departure_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Departure Flight #</label>
                                    <input type="text" name="departure_flight" class="form-control"
                                        placeholder="PK-301">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time of Departure</label>
                                    <input type="time" name="departure_time" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Airline</label>
                                    <select name="departure_airline" class="form-select">
                                        <option value="">-- Select --</option>
                                        <option>PIA</option>
                                        <option>Air Arabia</option>
                                        <option>Emirates</option>
                                        <option>Qatar Airways</option>
                                        <option>FlyDubai</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Departure PNR / Ticket #</label>
                                    <input type="text" name="departure_pnr" class="form-control"
                                        placeholder="ABC123">
                                </div>
                            </div>

                            {{-- ARRIVAL --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted border-bottom pb-2">
                                        <i class="mdi mdi-airplane-landing me-1"></i> Arrival Info
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date of Arrival</label>
                                    <input type="date" name="arrival_date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Arrival Flight #</label>
                                    <input type="text" name="arrival_flight" class="form-control"
                                        placeholder="PK-302">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Time of Arrival</label>
                                    <input type="time" name="arrival_time" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Airline</label>
                                    <select name="arrival_airline" class="form-select">
                                        <option value="">-- Select --</option>
                                        <option>PIA</option>
                                        <option>Air Arabia</option>
                                        <option>Emirates</option>
                                        <option>Qatar Airways</option>
                                        <option>FlyDubai</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Arrival PNR / Ticket #</label>
                                    <input type="text" name="arrival_pnr" class="form-control" placeholder="XYZ456">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Upload Air Ticket / Flight Document</label>
                                    <input type="file" name="flight_attachment" class="form-control"
                                        accept="image/*,.pdf">
                                </div>
                            </div>

                            {{-- PASSENGER TICKETS --}}
                            <h6 class="text-muted border-bottom pb-2 mb-3">
                                Passenger Tickets
                                <small class="text-info ms-2">(Auto-generated from No. of Pax)</small>
                            </h6>
                            <div id="flightPersonsList"></div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="mdi mdi-arrow-left me-1"></i> Prev
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    Next <i class="mdi mdi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        {{-- TAB 5: Hotel --}}
                        <div class="tab-pane fade" id="tab-hotel">
                            {{-- Hotel Dual Currency Totals Display --}}
                            <div class="row g-2 p-2 rounded bg-light border mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Cost (SAR)</small>
                                        <strong class="text-danger fs-14" id="hotel_cost_sar_total">0.00 SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Cost (PKR)</small>
                                        <strong class="text-danger fs-14" id="hotel_cost_pkr_total">0.00 PKR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Sell (SAR)</small>
                                        <strong class="text-success fs-14" id="hotel_sell_sar_total">0.00 SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Sell (PKR)</small>
                                        <strong class="text-success fs-14" id="hotel_sell_pkr_total">0.00 PKR</strong>
                                    </div>
                                </div>
                            </div>
                            <div id="noHotelAlert" class="alert alert-light border py-4 text-center text-muted mb-3">
                                <i class="mdi mdi-hotel fs-36 text-primary d-block mb-1"></i>
                                <strong>No hotels added yet.</strong>
                                <p class="small text-muted mb-0">Click one of the buttons below to add a hotel for Makkah, Madinah or Other city.</p>
                            </div>
                            <div id="hotelsList"></div>

                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="btn btn-primary btn-sm" onclick="addNewHotel('makkah')">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add Makkah Hotel
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="addNewHotel('madinah')">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add Madinah Hotel
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addNewHotel('other')">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add Other Hotel
                                </button>
                                <button type="button" class="btn btn-info btn-sm text-white" id="addHotel">
                                    <i class="mdi mdi-plus me-1"></i> + Add Hotel (Select)
                                </button>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 6: Transport --}}
                        <div class="tab-pane fade" id="tab-transport">
                            {{-- Transport Dual Currency Totals Display --}}
                            <div class="row g-2 p-2 rounded bg-light border mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Cost (SAR)</small>
                                        <strong class="text-danger fs-14" id="transport_cost_sar_total">0.00 SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Cost (PKR)</small>
                                        <strong class="text-danger fs-14" id="transport_cost_pkr_total">0.00 PKR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Sell (SAR)</small>
                                        <strong class="text-success fs-14" id="transport_sell_sar_total">0.00 SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Sell (PKR)</small>
                                        <strong class="text-success fs-14" id="transport_sell_pkr_total">0.00 PKR</strong>
                                    </div>
                                </div>
                            </div>
                            <div id="routesList">
                                <div class="route-block border rounded p-3 mb-2">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Route</label>
                                            <input type="text" name="transports[0][route]" class="form-control"
                                                placeholder="Karachi → Makkah">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Transport Type</label>
                                            <select name="transports[0][transport_type]" class="form-select">
                                                <option value="private_car">Private Car</option>
                                                <option value="bus">Bus / Coach</option>
                                                <option value="train">Train</option>
                                                <option value="shared_van">Shared Van</option>
                                                <option value="taxi">Taxi</option>
                                                <option value="flight">Flight</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                                <span>Transport Supplier / Vendor</span>
                                                <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshSuppliersList(true)" title="Reload Suppliers"><i class="mdi mdi-refresh"></i> Refresh</button>
                                            </label>
                                            <select name="transports[0][supplier_id]" class="form-select">
                                                <option value="">-- Select Supplier --</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-primary fw-semibold">ROE</label>
                                            <input type="number" step="0.0001" name="transports[0][roe]"
                                                class="form-control transport-roe" placeholder="75.00" value="75">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-danger fw-semibold">Cost Rate (SAR)</label>
                                            <input type="number" step="0.01" name="transports[0][cost_rate]"
                                                class="form-control transport-cost-rate" placeholder="0.00" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-danger fw-semibold">Total Cost (SAR & PKR)</label>
                                            <div class="p-1 px-2 border rounded bg-light">
                                                <div class="d-flex justify-content-between small text-danger"><span>SAR:</span><strong class="transport-cost-sar-disp">0.00 SAR</strong></div>
                                                <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">0.00 PKR</strong></div>
                                            </div>
                                            <input type="hidden" name="transports[0][cost_amount]"
                                                class="transport-cost-amount" value="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-success fw-semibold">Sell Rate (SAR)</label>
                                            <input type="number" step="0.01" name="transports[0][selling_rate]"
                                                class="form-control transport-sell-rate" placeholder="0.00" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-success fw-semibold">Total Sell (SAR & PKR)</label>
                                            <div class="p-1 px-2 border rounded bg-light">
                                                <div class="d-flex justify-content-between small text-success"><span>SAR:</span><strong class="transport-sell-sar-disp">0.00 SAR</strong></div>
                                                <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="transport-sell-pkr-disp">0.00 PKR</strong></div>
                                            </div>
                                            <input type="hidden" name="transports[0][selling_amount]"
                                                class="transport-sell-amount" value="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Notes</label>
                                            <input type="text" name="transports[0][notes]" class="form-control"
                                                placeholder="Optional">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Transport Ticket / Receipt</label>
                                            <input type="file" name="transports[0][transport_ticket]"
                                                class="form-control" accept="image/*,.pdf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addRoute">+ Add
                                Route</button>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 7: Visa --}}
                        <div class="tab-pane fade" id="tab-visa">
                            {{-- Visa Dual Currency Totals Display --}}
                            <div class="row g-2 p-2 rounded bg-light border mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Cost (SAR)</small>
                                        <strong class="text-danger fs-14" id="visa_cost_sar_total">0.00 SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Cost (PKR)</small>
                                        <strong class="text-danger fs-14" id="visa_cost_pkr_total">0.00 PKR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Sell (SAR)</small>
                                        <strong class="text-success fs-14" id="visa_sell_sar_total">0.00 SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Sell (PKR)</small>
                                        <strong class="text-success fs-14" id="visa_sell_pkr_total">0.00 PKR</strong>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">
                                <i class="mdi mdi-information me-1"></i>
                                Names and passport numbers filled in Persons tab will auto-fill here. You can edit them.
                            </p>
                            <div id="visasList"></div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 8: Costing --}}
                        <div class="tab-pane fade" id="tab-costing">
                            <div class="row g-3">
                                <div class="col-md-3" id="costing_package_cost_col" style="{{ $isUmrah ? 'display:none;' : '' }}">
                                    <label class="form-label">Package Cost / Person (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="package_cost" id="package_cost"
                                        class="form-control calc" placeholder="Enter package cost" step="0.01" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hotel Charges (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="hotel_charges" id="hotel_charges"
                                        class="form-control calc bg-light" placeholder="0.00" step="0.01" value="0" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Visa Charges (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="visa_charges" id="visa_charges"
                                        class="form-control calc bg-light" placeholder="0.00" step="0.01" value="0" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Flight Charges (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="flight_charges_disp" id="flight_charges_disp"
                                        class="form-control calc bg-light" placeholder="0.00" step="0.01" value="0" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Transport Charges (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="transport_charges" id="transport_charges"
                                        class="form-control calc bg-light" placeholder="0.00" step="0.01" value="0" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Qurbani & Other Charges (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="other_charges" id="other_charges"
                                        class="form-control calc" placeholder="Enter other charges" step="0.01" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Discount (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="discount" id="discount"
                                        class="form-control calc" placeholder="Enter discount" step="0.01" value="0">
                                </div>

                                <div class="col-12">
                                    {{-- Dual Currency Summary Cards --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="card border-primary border-opacity-25 bg-light mb-0">
                                                <div class="card-body p-3">
                                                    <h6 class="text-primary fw-bold mb-2"><i class="mdi mdi-cash-multiple me-1"></i> SAR (Saudi Riyal) Total</h6>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Flight SAR:</span>
                                                        <strong id="cost_sar_flight">0.00 SAR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Hotel SAR:</span>
                                                        <strong id="cost_sar_hotel">0.00 SAR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Transport SAR:</span>
                                                        <strong id="cost_sar_transport">0.00 SAR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Visa SAR:</span>
                                                        <strong id="cost_sar_visa">0.00 SAR</strong>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between text-primary fs-15 fw-bold">
                                                        <span>Total Selling (SAR):</span>
                                                        <span id="cost_sar_total">0.00 SAR</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-danger fs-14 fw-semibold">
                                                        <span>Balance Remaining (SAR):</span>
                                                        <span id="cost_sar_balance">0.00 SAR</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-success border-opacity-25 bg-light mb-0">
                                                <div class="card-body p-3">
                                                    <h6 class="text-success fw-bold mb-2"><i class="mdi mdi-currency-usd me-1"></i> PKR (Pakistani Rupee) Total</h6>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Flight PKR:</span>
                                                        <strong id="cost_pkr_flight">0.00 PKR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Hotel PKR:</span>
                                                        <strong id="cost_pkr_hotel">0.00 PKR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Transport PKR:</span>
                                                        <strong id="cost_pkr_transport">0.00 PKR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Visa PKR:</span>
                                                        <strong id="cost_pkr_visa">0.00 PKR</strong>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between text-success fs-15 fw-bold">
                                                        <span>Total Selling (PKR):</span>
                                                        <span id="cost_pkr_total">0.00 PKR</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-danger fs-14 fw-semibold">
                                                        <span>Balance Remaining (PKR):</span>
                                                        <span id="cost_pkr_balance">0.00 PKR</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm bg-light">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>Persons</th>
                                                    <th style="{{ $isUmrah ? 'display:none;' : '' }}">Pkg Cost × Pax</th>
                                                    <th>Hotel (PKR)</th>
                                                    <th>Visa (PKR)</th>
                                                    <th>Flight (PKR)</th>
                                                    <th>Transport (PKR)</th>
                                                    <th>Other & Services (PKR)</th>
                                                    <th>Discount (PKR)</th>
                                                    <th class="text-success">Total (PKR)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong id="sum_pax">1</strong></td>
                                                    <td id="sum_pkg" style="{{ $isUmrah ? 'display:none;' : '' }}">0.00</td>
                                                    <td id="sum_hotel">0.00</td>
                                                    <td id="sum_visa">0.00</td>
                                                    <td id="sum_flight">0.00</td>
                                                    <td id="sum_transport">0.00</td>
                                                    <td id="sum_other">0.00</td>
                                                    <td id="sum_discount" class="text-danger">0.00</td>
                                                    <td class="text-success fw-bold" id="sum_total">0.00</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Total Amount (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="total_amount" id="total_amount"
                                        class="form-control bg-light fw-bold text-primary fs-16" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Received (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="total_received" id="total_received"
                                        class="form-control calc" placeholder="Enter amount received" step="0.01" value="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Balance Remaining (<span class="curr-symbol">PKR</span>)</label>
                                    <input type="number" name="balance" id="balance"
                                        class="form-control bg-light text-danger fw-bold fs-16" readonly>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="mdi mdi-arrow-left me-1"></i> Prev
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-next">Next <i class="mdi mdi-arrow-right ms-1"></i></button>
                                    <button type="submit" class="btn btn-success px-5">
                                        <i class="mdi mdi-content-save me-1"></i> Save Booking
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 9: Additional Services --}}
                        <div class="tab-pane fade" id="tab-additional-services">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Service Details</label>
                                    <textarea name="additional_services_detail" id="additional_services_detail" class="form-control summernote"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Service Amount</label>
                                    <input type="number" name="additional_services_amount" id="additional_services_amount" class="form-control calc" placeholder="Enter amount" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="mdi mdi-arrow-left me-1"></i> Prev
                                </button>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('booking.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success px-5">
                                        <i class="mdi mdi-content-save me-1"></i> Save Booking
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .booking-tabs .nav-link {
            color: #555;
            font-size: 13px;
            padding: 8px 14px;
            border-bottom: none;
        }

        .booking-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
            border-color: #dee2e6 #dee2e6 #fff;
            background: #fff;
        }

        .tab-content {
            min-height: 380px;
        }

        .person-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
        }

        .visa-card {
            background: #fff8f0;
            border: 1px solid #fde8c8;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
        const packagesData  = @json($packages);
        const clientsData   = @json($clients);
        let hotelsData      = @json($hotels);
        let suppliersData   = @json($suppliers);

        function buildSuppliersOptions() {
            return `<option value="">-- Select Supplier --</option>` +
                (Array.isArray(suppliersData) ? suppliersData.map(s => `<option value="${s.id}">${s.name}</option>`).join('') : '');
        }

        function buildHotelsOptions() {
            return `<option value="">Select Hotel</option>` +
                (Array.isArray(hotelsData) ? hotelsData.map(h => `<option value="${h.name}">${h.name} (${h.city || h.place || ''})</option>`).join('') : '');
        }

        let suppliersOptions = buildSuppliersOptions();
        let hotelsOptions    = buildHotelsOptions();

        async function refreshMasterData(showNotification = false) {
            try {
                const supPromise = fetch('{{ route('supplier.json-list') }}').then(r => r.ok ? r.json() : null).catch(() => null);
                const hotelPromise = fetch('{{ route('hotel.json-list') }}').then(r => r.ok ? r.json() : null).catch(() => null);

                const [newSuppliers, newHotels] = await Promise.all([supPromise, hotelPromise]);

                if (Array.isArray(newSuppliers) && newSuppliers.length > 0) {
                    suppliersData = newSuppliers;
                    suppliersOptions = buildSuppliersOptions();

                    document.querySelectorAll('select[name="flight_supplier_id"], select[name*="[supplier_id]"]').forEach(sel => {
                        const curVal = sel.value;
                        sel.innerHTML = suppliersOptions;
                        if (curVal) sel.value = curVal;
                    });
                }

                if (Array.isArray(newHotels) && newHotels.length > 0) {
                    hotelsData = newHotels;
                    hotelsOptions = buildHotelsOptions();

                    document.querySelectorAll('select.hotel-name-select, select[name*="[hotel_name]"]').forEach(sel => {
                        const curVal = sel.value;
                        sel.innerHTML = hotelsOptions;
                        if (curVal) sel.value = curVal;
                    });
                }

                if (showNotification) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show p-2 mb-2 shadow-sm';
                    alertDiv.innerHTML = `<i class="mdi mdi-check-circle me-1"></i> <strong>Data Refreshed!</strong> Updated suppliers & hotels. <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>`;
                    document.getElementById('draftAlertContainer')?.prepend(alertDiv);
                    setTimeout(() => alertDiv.remove(), 4000);
                }
            } catch(e) {
                console.error('Error refreshing master data:', e);
            }
        }

        async function refreshSuppliersList(showNotification = true) {
            await refreshMasterData(showNotification);
        }

        const tabLinks = Array.from(document.querySelectorAll('#bookingTabs .nav-link'));

        function activateTab(idx) {
            if (idx < 0 || idx >= tabLinks.length) return;
            tabLinks[idx].click();
            const href = tabLinks[idx].getAttribute('href');
            if (href === '#tab-visa') syncVisas();
            if (href === '#tab-flight') syncFlightPersons();
            if (href === '#tab-persons') {
                const container = document.getElementById('personsList');
                if (container && container.children.length === 0) rebuildPersons();
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-next, .btn-prev');
            if (!btn) return;
            const cur = tabLinks.findIndex(t => t.classList.contains('active'));
            btn.classList.contains('btn-next') ? activateTab(cur + 1) : activateTab(cur - 1);
        });

        // Client Select Auto-population
        document.getElementById('clientSelect')?.addEventListener('change', function() {
            const clientId = parseInt(this.value);
            if (!clientId) return;
            const client = clientsData.find(c => c.id === clientId);
            if (!client) return;

            if (document.getElementById('fill_passport')) document.getElementById('fill_passport').value = client.passport_number || '';
            if (document.getElementById('fill_cnic')) document.getElementById('fill_cnic').value = client.cnic || '';
            if (document.getElementById('fill_phone')) document.getElementById('fill_phone').value = client.phone || '';

            // Auto-fill Primary Passenger (idx 0)
            if (document.getElementById('person_name_0')) document.getElementById('person_name_0').value = client.name || '';
            if (document.getElementById('person_passport_0')) document.getElementById('person_passport_0').value = client.passport_number || '';
            if (document.getElementById('person_cnic_0')) document.getElementById('person_cnic_0').value = client.cnic || '';

            const parts = (client.name || '').trim().split(' ');
            if (parts.length > 1) {
                if (document.getElementById('person_surname_0')) document.getElementById('person_surname_0').value = parts[0];
                if (document.getElementById('person_given_name_0')) document.getElementById('person_given_name_0').value = parts.slice(1).join(' ');
            } else {
                if (document.getElementById('person_given_name_0')) document.getElementById('person_given_name_0').value = client.name || '';
            }

            syncFlightPersons();
            syncVisas();
        });

        function updatePersonFullName(idx) {
            const sur   = document.getElementById(`person_surname_${idx}`)?.value || '';
            const given = document.getElementById(`person_given_name_${idx}`)?.value || '';
            const combined = (sur + ' ' + given).trim();
            const el = document.getElementById(`person_name_${idx}`);
            if (el) el.value = combined;
        }

        function onPackageSelectChange(sel) {
            const pkgId = parseInt(sel.value);
            if (!pkgId) return;

            const pkg = packagesData.find(p => p.id === pkgId);
            if (!pkg) return;

            const isPkgUmrah = (pkg.package_type === 'umrah' || (!pkg.package_type && document.getElementById('package_type')?.value === 'umrah'));

            const pkgNameContainer = document.getElementById('package_name_container');
            const clientBlock = document.getElementById('clientBlock');
            const companyBlock = document.getElementById('companyBlock');
            const bookingForInput = document.getElementById('booking_for_input');
            const noteBlock = document.getElementById('personsBookingForNote');

            if (isPkgUmrah) {
                if (pkgNameContainer) pkgNameContainer.style.display = 'none';
                if (clientBlock) clientBlock.style.display = '';
                if (companyBlock) companyBlock.style.display = 'none';
                if (bookingForInput) bookingForInput.value = 'client';
                if (noteBlock) noteBlock.classList.add('d-none');
            } else {
                if (pkgNameContainer) pkgNameContainer.style.display = '';
                if (clientBlock) clientBlock.style.display = 'none';
                if (companyBlock) companyBlock.style.display = '';
                if (bookingForInput) bookingForInput.value = 'company';
                if (noteBlock) noteBlock.classList.remove('d-none');
            }

            if (document.getElementById('package_name')) {
                document.getElementById('package_name').value = pkg.name || '';
            }
            if (document.getElementById('package_year') && pkg.year) {
                document.getElementById('package_year').value = pkg.year;
            }

            const pkgCost = parseFloat(pkg.package_amount || pkg.adult_pkr || pkg.adult_sar || 0);
            if (document.getElementById('package_cost')) {
                document.getElementById('package_cost').value = pkgCost ? pkgCost.toFixed(2) : '0.00';
            }

            syncBookingHotelDates();
            calcTotal();
        }

        function buildPersonRow(idx, isFirst = false) {
            const label = isFirst ? 'Main Passenger' : `Passenger ${idx + 1}`;

            return `
            <div class="person-card" id="person_card_${idx}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-primary" style="font-size:13px;">${label}</strong>
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Surname (Passport)</label>
                        <input type="text" name="persons[${idx}][surname]" id="person_surname_${idx}"
                               class="form-control form-control-sm" placeholder="Surname" oninput="updatePersonFullName(${idx})">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Given Name (Passport)</label>
                        <input type="text" name="persons[${idx}][given_name]" id="person_given_name_${idx}"
                               class="form-control form-control-sm" placeholder="Given Name" oninput="updatePersonFullName(${idx})">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Full Name</label>
                        <input type="text" name="persons[${idx}][full_name]" id="person_name_${idx}"
                               class="form-control form-control-sm" placeholder="Full Name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Passport #</label>
                        <input type="text" name="persons[${idx}][passport_number]" id="person_passport_${idx}"
                               class="form-control form-control-sm" placeholder="Passport #">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;">Date of Birth</label>
                        <input type="date" name="persons[${idx}][dob]" id="person_dob_${idx}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;">Gender</label>
                        <select name="persons[${idx}][gender]" id="person_gender_${idx}" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;">City</label>
                        <input type="text" name="persons[${idx}][city]" id="person_city_${idx}"
                               class="form-control form-control-sm" placeholder="e.g. Lahore / Karachi">
                    </div>
                </div>

                <div class="border-top mt-3 pt-3">
                    <strong class="text-secondary d-block mb-2" style="font-size:12px;">
                        <i class="mdi mdi-file-document-multiple me-1"></i> Documents (Optional)
                    </strong>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:11px;">Passport First Page</label>
                            <input type="file" name="persons[${idx}][passport_photo]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:11px;">Picture of Passenger</label>
                            <input type="file" name="persons[${idx}][photo]" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:11px;">Medical Fitness Certificate</label>
                            <input type="file" name="persons[${idx}][medical_certificate]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                    </div>
                </div>
            </div>`;
        }

        function rebuildPersons() {
            const adults = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const pax = Math.max(1, adults + children + infants);

            if (document.getElementById('no_of_pax')) document.getElementById('no_of_pax').value = pax;
            if (document.getElementById('bar_pax_badge')) document.getElementById('bar_pax_badge').textContent = pax;

            if (document.getElementById('flight_adult_pax_label')) document.getElementById('flight_adult_pax_label').textContent = adults;
            if (document.getElementById('flight_child_pax_label')) document.getElementById('flight_child_pax_label').textContent = children;
            if (document.getElementById('flight_infant_pax_label')) document.getElementById('flight_infant_pax_label').textContent = infants;

            const list = document.getElementById('personsList');
            if (list) {
                list.innerHTML = '';
                for (let i = 0; i < pax; i++) {
                    list.insertAdjacentHTML('beforeend', buildPersonRow(i, i === 0));
                }
            }

            // If client selected, populate passenger 0
            const clientSel = document.getElementById('clientSelect');
            if (clientSel && clientSel.value) {
                const clientId = parseInt(clientSel.value);
                const client = clientsData.find(c => c.id === clientId);
                if (client) {
                    if (document.getElementById('person_name_0')) document.getElementById('person_name_0').value = client.name || '';
                    if (document.getElementById('person_passport_0')) document.getElementById('person_passport_0').value = client.passport_number || '';
                    if (document.getElementById('person_cnic_0')) document.getElementById('person_cnic_0').value = client.cnic || '';
                }
            }

            calculateFlight();
            calcTotal();
        }

        document.querySelectorAll('.pax-breakdown-calc').forEach(el => {
            el.addEventListener('input', function() {
                rebuildPersons();
                syncFlightPersons();
                syncVisas();
            });
        });

        document.getElementById('no_of_pax')?.addEventListener('input', function() {
            const total = parseInt(this.value) || 1;
            const curChild  = parseInt(document.getElementById('pax_children')?.value) || 0;
            const curInfant = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const nonAdults = curChild + curInfant;

            if (total >= nonAdults) {
                if (document.getElementById('pax_adults')) document.getElementById('pax_adults').value = Math.max(1, total - nonAdults);
            }
            rebuildPersons();
            syncFlightPersons();
            syncVisas();
        });

        function syncFlightPersons() {
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const list = document.getElementById('flightPersonsList');
            if (!list) return;
            list.innerHTML = '';
            for (let i = 0; i < pax; i++) {
                const personName = document.getElementById(`person_name_${i}`)?.value || `Passenger ${i + 1}`;
                const personPass = document.getElementById(`person_passport_${i}`)?.value || '';
                list.insertAdjacentHTML('beforeend', `
                <div class="border rounded p-3 mb-2">
                    <strong class="text-primary" style="font-size:13px;">Passenger ${i + 1}: ${personName}</strong>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:12px;">Passenger Name</label>
                            <input type="text" name="flight_persons[${i}][name]" class="form-control form-control-sm"
                                   value="${personName}" placeholder="Name on ticket">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:12px;">Passport #</label>
                            <input type="text" name="flight_persons[${i}][passport]" class="form-control form-control-sm"
                                   value="${personPass}" placeholder="Passport #">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:12px;">Ticket / Seat #</label>
                            <input type="text" name="flight_persons[${i}][ticket]" class="form-control form-control-sm"
                                   placeholder="e.g. 24A">
                        </div>
                    </div>
                </div>`);
            }
        }

        function syncVisas() {
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const list = document.getElementById('visasList');
            if (!list) return;

            const currentVisas = [];
            for (let i = 0; i < pax; i++) {
                currentVisas.push({
                    passport: document.querySelector(`input[name="visas[${i}][passport_number]"]`)?.value || document.getElementById(`person_passport_${i}`)?.value || '',
                    name: document.querySelector(`input[name="visas[${i}][given_name]"]`)?.value || document.getElementById(`person_name_${i}`)?.value || '',
                    dob: document.querySelector(`input[name="visas[${i}][date_of_birth]"]`)?.value || document.getElementById(`person_dob_${i}`)?.value || '',
                    supplier_id: document.querySelector(`select[name="visas[${i}][supplier_id]"]`)?.value || '',
                    company: document.querySelector(`input[name="visas[${i}][company]"]`)?.value || '',
                    send_to: document.querySelector(`select[name="visas[${i}][send_to]"]`)?.value || '',
                    roe: document.querySelector(`input[name="visas[${i}][roe]"]`)?.value || document.getElementById('flight_roe')?.value || '75',
                    cost_rate: document.querySelector(`input[name="visas[${i}][cost_rate]"]`)?.value || '0',
                    cost_amount: document.querySelector(`input[name="visas[${i}][cost_amount]"]`)?.value || '0',
                    selling_rate: document.querySelector(`input[name="visas[${i}][selling_rate]"]`)?.value || '0',
                    selling_amount: document.querySelector(`input[name="visas[${i}][selling_amount]"]`)?.value || '0',
                    status: document.querySelector(`select[name="visas[${i}][status]"]`)?.value || 'pending',
                });
            }

            list.innerHTML = '';
            for (let i = 0; i < pax; i++) {
                const v = currentVisas[i] || {};
                const personName = document.getElementById(`person_name_${i}`)?.value || v.name || '';
                const personPass = document.getElementById(`person_passport_${i}`)?.value || v.passport || '';
                const personDob = document.getElementById(`person_dob_${i}`)?.value || v.dob || '';
                const roe = v.roe || document.getElementById('flight_roe')?.value || '75';
                const costRate = parseFloat(v.cost_rate) || 0;
                const costAmt = (costRate * parseFloat(roe)).toFixed(2);
                const sellRate = parseFloat(v.selling_rate) || 0;
                const sellAmt = (sellRate * parseFloat(roe)).toFixed(2);
                const status = v.status || 'pending';
                const sendTo = v.send_to || '';
                const company = v.company || '';
                const supId = v.supplier_id || '';

                let supplierOptionsHtml = `<option value="">-- Select Supplier --</option>` +
                    (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}" ${s.id == supId ? 'selected' : ''}>${s.name}</option>`).join('') : '');

                list.insertAdjacentHTML('beforeend', `
                <div class="visa-card border rounded p-3 mb-2 bg-white" id="visa_card_${i}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary" style="font-size:13px;">Passenger ${i + 1}: ${personName || 'Main Passenger'}</strong>
                        <span class="badge bg-warning text-dark">${status.toUpperCase()}</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Passport Number</label>
                            <input type="text" name="visas[${i}][passport_number]" class="form-control form-control-sm" value="${personPass}" placeholder="Passport #">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Name (on Visa)</label>
                            <input type="text" name="visas[${i}][given_name]" class="form-control form-control-sm" value="${personName}" placeholder="Name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Date of Birth</label>
                            <input type="date" name="visas[${i}][date_of_birth]" class="form-control form-control-sm" value="${personDob}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Visa Supplier</label>
                            <select name="visas[${i}][supplier_id]" class="form-select form-select-sm">
                                ${supplierOptionsHtml}
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-primary fw-semibold" style="font-size:12px;">ROE</label>
                            <input type="number" step="0.0001" name="visas[${i}][roe]" class="form-control form-control-sm visa-roe" value="${roe}" placeholder="75.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-danger fw-semibold" style="font-size:12px;">Cost Rate (SAR)</label>
                            <input type="number" step="0.01" name="visas[${i}][cost_rate]" class="form-control form-control-sm visa-cost-rate" value="${costRate}" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-danger fw-semibold" style="font-size:12px;">Total Cost (SAR & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-danger"><span>SAR:</span><strong class="visa-cost-sar-disp">${costRate.toFixed(2)} SAR</strong></div>
                                <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="visa-cost-pkr-disp">${costAmt} PKR</strong></div>
                            </div>
                            <input type="hidden" name="visas[${i}][cost_amount]" class="visa-cost-amount" value="${costAmt}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-success fw-semibold" style="font-size:12px;">Sell Rate (SAR)</label>
                            <input type="number" step="0.01" name="visas[${i}][selling_rate]" class="form-control form-control-sm visa-sell-rate" value="${sellRate}" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-success fw-semibold" style="font-size:12px;">Total Sell (SAR & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-success"><span>SAR:</span><strong class="visa-sell-sar-disp">${sellRate.toFixed(2)} SAR</strong></div>
                                <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="visa-sell-pkr-disp">${sellAmt} PKR</strong></div>
                            </div>
                            <input type="hidden" name="visas[${i}][selling_amount]" class="visa-sell-amount" value="${sellAmt}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Visa Status</label>
                            <select name="visas[${i}][status]" class="form-select form-select-sm">
                                <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="applied" ${status === 'applied' ? 'selected' : ''}>Applied</option>
                                <option value="approved" ${status === 'approved' ? 'selected' : ''}>Approved</option>
                                <option value="rejected" ${status === 'rejected' ? 'selected' : ''}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Company / Sub-Agent</label>
                            <input type="text" name="visas[${i}][company]" class="form-control form-control-sm" value="${company}" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px;">Visa Attachment / Document</label>
                            <input type="file" name="visas[${i}][visa_attachment]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                    </div>
                </div>`);
            }
        }

        // Flight Calculation (SAR & PKR for Adult, Child, Infant)
        function calculateFlight() {
            const adults   = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants  = parseInt(document.getElementById('pax_infants')?.value) || 0;

            const roe = parseFloat(document.getElementById('flight_roe')?.value) || 75;

            const adultCostSar = parseFloat(document.getElementById('flight_adult_cost')?.value) || 0;
            const adultSellSar = parseFloat(document.getElementById('flight_adult_sell')?.value) || 0;

            const childCostSar = parseFloat(document.getElementById('flight_child_cost')?.value) || 0;
            const childSellSar = parseFloat(document.getElementById('flight_child_sell')?.value) || 0;

            const infantCostSar = parseFloat(document.getElementById('flight_infant_cost')?.value) || 0;
            const infantSellSar = parseFloat(document.getElementById('flight_infant_sell')?.value) || 0;

            const totalAdultCostSar = adults * adultCostSar;
            const totalAdultSellSar = adults * adultSellSar;

            const totalChildCostSar = children * childCostSar;
            const totalChildSellSar = children * childSellSar;

            const totalInfantCostSar = infants * infantCostSar;
            const totalInfantSellSar = infants * infantSellSar;

            const totalCostSar = totalAdultCostSar + totalChildCostSar + totalInfantCostSar;
            const totalSellSar = totalAdultSellSar + totalChildSellSar + totalInfantSellSar;

            const totalCostPkr = totalCostSar * roe;
            const totalSellPkr = totalSellSar * roe;

            // Badges / Pax labels
            const adultBadge = document.getElementById('flight_adult_pax_label');
            if (adultBadge) adultBadge.textContent = adults;
            const childBadge = document.getElementById('flight_child_pax_label');
            if (childBadge) childBadge.textContent = children;
            const infantBadge = document.getElementById('flight_infant_pax_label');
            if (infantBadge) infantBadge.textContent = infants;

            // Row displays
            const elAdultCostDisp = document.getElementById('flight_adult_cost_sar_disp');
            if (elAdultCostDisp) elAdultCostDisp.textContent = totalAdultCostSar.toFixed(2);
            const elAdultSellDisp = document.getElementById('flight_adult_sell_sar_disp');
            if (elAdultSellDisp) elAdultSellDisp.textContent = totalAdultSellSar.toFixed(2);

            const elChildCostDisp = document.getElementById('flight_child_cost_sar_disp');
            if (elChildCostDisp) elChildCostDisp.textContent = totalChildCostSar.toFixed(2);
            const elChildSellDisp = document.getElementById('flight_child_sell_sar_disp');
            if (elChildSellDisp) elChildSellDisp.textContent = totalChildSellSar.toFixed(2);

            const elInfantCostDisp = document.getElementById('flight_infant_cost_sar_disp');
            if (elInfantCostDisp) elInfantCostDisp.textContent = totalInfantCostSar.toFixed(2);
            const elInfantSellDisp = document.getElementById('flight_infant_sell_sar_disp');
            if (elInfantSellDisp) elInfantSellDisp.textContent = totalInfantSellSar.toFixed(2);

            // Summary cards
            const elCostSarTot = document.getElementById('flight_cost_sar_total');
            if (elCostSarTot) elCostSarTot.textContent = totalCostSar.toFixed(2) + ' SAR';
            const elCostPkrTot = document.getElementById('flight_cost_pkr_total');
            if (elCostPkrTot) elCostPkrTot.textContent = totalCostPkr.toFixed(2) + ' PKR';
            const elSellSarTot = document.getElementById('flight_sell_sar_total');
            if (elSellSarTot) elSellSarTot.textContent = totalSellSar.toFixed(2) + ' SAR';
            const elSellPkrTot = document.getElementById('flight_sell_pkr_total');
            if (elSellPkrTot) elSellPkrTot.textContent = totalSellPkr.toFixed(2) + ' PKR';

            // Backend compatibility hidden inputs
            const elCost = document.getElementById('flight_cost');
            if (elCost) elCost.value = totalCostPkr.toFixed(2);
            const elCharges = document.getElementById('flight_charges');
            if (elCharges) elCharges.value = totalSellPkr.toFixed(2);
            const elChargesDisp = document.getElementById('flight_charges_disp');
            if (elChargesDisp) elChargesDisp.value = totalSellPkr.toFixed(2);
            const elCostRate = document.getElementById('flight_cost_rate');
            if (elCostRate) elCostRate.value = totalCostSar.toFixed(2);
            const elSellRate = document.getElementById('flight_selling_rate');
            if (elSellRate) elSellRate.value = totalSellSar.toFixed(2);

            calcTotal();
        }

        // Hotels Dynamic Management
        let hotelIdx = 0;

        function addNewHotel(loc = 'makkah') {
            const noHotelAlert = document.getElementById('noHotelAlert');
            if (noHotelAlert) noHotelAlert.style.display = 'none';

            let locLabel = 'Makkah Hotel';
            let locVal = 'makkah';
            if (loc === 'madinah') { locLabel = 'Madinah Hotel'; locVal = 'madinah'; }
            else if (loc === 'other') { locLabel = 'Other Hotel'; locVal = 'other'; }
            else if (loc === 'jeddah') { locLabel = 'Jeddah Hotel'; locVal = 'jeddah'; }

            const defaultRoe = document.getElementById('flight_roe')?.value || 75;

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <h6 class="text-primary mb-0 fw-bold"><i class="mdi mdi-hotel me-1"></i> ${locLabel}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-hotel"><i class="mdi mdi-delete-outline me-1"></i> Remove</button>
                </div>
                <input type="hidden" name="hotels[${hotelIdx}][location]" value="${locVal}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Hotel Name</label>
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select">
                            ${hotelsOptions}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-flex justify-content-between align-items-center mb-1">
                            <span>Hotel Supplier / Vendor</span>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshSuppliersList(true)" title="Reload Suppliers"><i class="mdi mdi-refresh"></i> Refresh</button>
                        </label>
                        <select name="hotels[${hotelIdx}][supplier_id]" class="form-select">
                            ${suppliersOptions}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-primary fw-semibold">ROE (SAR to PKR)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">1 SAR =</span>
                            <input type="number" step="0.0001" name="hotels[${hotelIdx}][roe]" class="form-control hotel-roe" value="${defaultRoe}" placeholder="75.00">
                            <span class="input-group-text">PKR</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Room Type</label>
                        <select name="hotels[${hotelIdx}][room_type]" class="form-select">
                            <option value="single">Single</option>
                            <option value="double" selected>Double</option>
                            <option value="triple">Triple</option>
                            <option value="quad">Quad</option>
                            <option value="sharing">Sharing</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nights</label>
                        <input type="number" name="hotels[${hotelIdx}][no_of_nights]" class="form-control hotel-nights" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">No. of Rooms</label>
                        <input type="number" name="hotels[${hotelIdx}][no_of_rooms]" class="form-control hotel-rooms" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hotel Room #</label>
                        <input type="text" name="hotels[${hotelIdx}][room_number]" class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Check In</label>
                        <input type="date" name="hotels[${hotelIdx}][check_in]" class="form-control hotel-checkin">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Check Out</label>
                        <input type="date" name="hotels[${hotelIdx}][check_out]" class="form-control hotel-checkout">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Cost Rate / Night (SAR)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Total Cost (SAR & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-danger"><span>SAR:</span><strong class="hotel-cost-sar-disp">0.00 SAR</strong></div>
                            <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="hotel-cost-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][cost_amount]" class="hotel-cost-total" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Sell Rate / Night (SAR)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Total Sell (SAR & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-success"><span>SAR:</span><strong class="hotel-sell-sar-disp">0.00 SAR</strong></div>
                            <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="hotel-sell-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][selling_amount]" class="hotel-sell-total" value="0">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                        <input type="file" name="hotels[${hotelIdx}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
                    </div>
                </div>
            </div>`;

            document.getElementById('hotelsList').insertAdjacentHTML('beforeend', html);
            hotelIdx++;
            syncBookingHotelDates();
            const lastRow = document.querySelector('#hotelsList .hotel-block:last-child');
            if (lastRow) calculateHotelRow(lastRow);
            calcTotal();
        }

        document.getElementById('addHotel')?.addEventListener('click', function() {
            addNewHotel('other');
        });

        document.getElementById('hotelsList')?.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-hotel') || e.target.closest('.remove-hotel')) {
                e.target.closest('.hotel-block').remove();
                if (document.querySelectorAll('#hotelsList .hotel-block').length === 0) {
                    const noHotelAlert = document.getElementById('noHotelAlert');
                    if (noHotelAlert) noHotelAlert.style.display = '';
                }
                syncBookingHotelDates();
                calcTotal();
            }
        });

        function calculateHotelRow(row) {
            if (!row) return;
            const nights = parseFloat(row.querySelector('.hotel-nights')?.value) || 0;
            const rooms = parseFloat(row.querySelector('.hotel-rooms')?.value) || 0;
            const roe = parseFloat(row.querySelector('.hotel-roe')?.value) || 75;
            const costRate = parseFloat(row.querySelector('.hotel-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.hotel-sell-rate')?.value) || 0;

            const costSar = nights * rooms * costRate;
            const costPkr = costSar * roe;
            const sellSar = nights * rooms * sellRate;
            const sellPkr = sellSar * roe;

            const costSarDisp = row.querySelector('.hotel-cost-sar-disp');
            const costPkrDisp = row.querySelector('.hotel-cost-pkr-disp');
            const costTotalInput = row.querySelector('.hotel-cost-total');

            const sellSarDisp = row.querySelector('.hotel-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.hotel-sell-pkr-disp');
            const sellTotalInput = row.querySelector('.hotel-sell-total');

            if (costSarDisp) costSarDisp.textContent = costSar.toFixed(2) + ' SAR';
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costTotalInput) costTotalInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellSar.toFixed(2) + ' SAR';
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellTotalInput) sellTotalInput.value = sellPkr.toFixed(2);

            let totalHotelSelling = 0;
            document.querySelectorAll('.hotel-sell-total').forEach(el => {
                totalHotelSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('hotel_charges')) {
                document.getElementById('hotel_charges').value = totalHotelSelling.toFixed(2);
            }
            calcTotal();
        }

        // Transports Dynamic Management
        let routeIdx = 1;

        document.getElementById('addRoute')?.addEventListener('click', function() {
            document.getElementById('routesList').insertAdjacentHTML('beforeend', `
                <div class="route-block border rounded p-3 mb-2 bg-white">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Route</span>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-route">×</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Route</label>
                            <input type="text" name="transports[${routeIdx}][route]" class="form-control" placeholder="Route"></div>
                        <div class="col-md-3"><label class="form-label">Transport Type</label>
                            <select name="transports[${routeIdx}][transport_type]" class="form-select">
                                <option value="private_car">Private Car</option><option value="bus">Bus / Coach</option>
                                <option value="train">Train</option><option value="shared_van">Shared Van</option>
                                <option value="taxi">Taxi</option><option value="flight">Flight</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Transport Supplier / Vendor</label>
                            <select name="transports[${routeIdx}][supplier_id]" class="form-select">
                                ${suppliersOptions}
                            </select></div>
                        <div class="col-md-2"><label class="form-label text-primary fw-semibold">ROE</label>
                            <input type="number" step="0.0001" name="transports[${routeIdx}][roe]" class="form-control transport-roe" placeholder="75.00" value="75"></div>
                        <div class="col-md-2"><label class="form-label text-danger fw-semibold">Cost Rate (SAR)</label>
                            <input type="number" step="0.01" name="transports[${routeIdx}][cost_rate]" class="form-control transport-cost-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-danger fw-semibold">Total Cost (SAR & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-danger"><span>SAR:</span><strong class="transport-cost-sar-disp">0.00 SAR</strong></div>
                                <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">0.00 PKR</strong></div>
                            </div>
                            <input type="hidden" name="transports[${routeIdx}][cost_amount]" class="transport-cost-amount" value="0"></div>
                        <div class="col-md-2"><label class="form-label text-success fw-semibold">Sell Rate (SAR)</label>
                            <input type="number" step="0.01" name="transports[${routeIdx}][selling_rate]" class="form-control transport-sell-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-success fw-semibold">Total Sell (SAR & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-success"><span>SAR:</span><strong class="transport-sell-sar-disp">0.00 SAR</strong></div>
                                <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="transport-sell-pkr-disp">0.00 PKR</strong></div>
                            </div>
                            <input type="hidden" name="transports[${routeIdx}][selling_amount]" class="transport-sell-amount" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Notes</label>
                            <input type="text" name="transports[${routeIdx}][notes]" class="form-control" placeholder="Notes"></div>
                        <div class="col-md-6"><label class="form-label">Transport Ticket / Receipt</label>
                            <input type="file" name="transports[${routeIdx}][transport_ticket]" class="form-control" accept="image/*,.pdf"></div>
                    </div>
                </div>`);
            routeIdx++;
        });

        document.getElementById('routesList')?.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-route') || e.target.closest('.remove-route')) {
                e.target.closest('.route-block').remove();
                calculateAllTransports();
            }
        });

        function calculateTransportRow(row) {
            if (!row) return;
            const roe = parseFloat(row.querySelector('.transport-roe')?.value) || 75;
            const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;

            const costSar = costRate;
            const costPkr = costSar * roe;
            const sellSar = sellRate;
            const sellPkr = sellSar * roe;

            const costSarDisp = row.querySelector('.transport-cost-sar-disp');
            const costPkrDisp = row.querySelector('.transport-cost-pkr-disp');
            const costAmtInput = row.querySelector('.transport-cost-amount');

            const sellSarDisp = row.querySelector('.transport-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.transport-sell-pkr-disp');
            const sellAmtInput = row.querySelector('.transport-sell-amount');

            if (costSarDisp) costSarDisp.textContent = costSar.toFixed(2) + ' SAR';
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costAmtInput) costAmtInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellSar.toFixed(2) + ' SAR';
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellAmtInput) sellAmtInput.value = sellPkr.toFixed(2);

            calculateAllTransports();
        }

        function calculateAllTransports() {
            let totalTransportSelling = 0;
            document.querySelectorAll('.transport-sell-amount').forEach(el => {
                totalTransportSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('transport_charges')) {
                document.getElementById('transport_charges').value = totalTransportSelling.toFixed(2);
            }
            calcTotal();
        }

        function calculateVisaRow(row) {
            if (!row) return;
            const roe = parseFloat(row.querySelector('.visa-roe')?.value) || 75;
            const costRate = parseFloat(row.querySelector('.visa-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.visa-sell-rate')?.value) || 0;

            const costSar = costRate;
            const costPkr = costSar * roe;
            const sellSar = sellRate;
            const sellPkr = sellSar * roe;

            const costSarDisp = row.querySelector('.visa-cost-sar-disp');
            const costPkrDisp = row.querySelector('.visa-cost-pkr-disp');
            const costAmtInput = row.querySelector('.visa-cost-amount');

            const sellSarDisp = row.querySelector('.visa-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.visa-sell-pkr-disp');
            const sellAmtInput = row.querySelector('.visa-sell-amount');

            if (costSarDisp) costSarDisp.textContent = costSar.toFixed(2) + ' SAR';
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costAmtInput) costAmtInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellSar.toFixed(2) + ' SAR';
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellAmtInput) sellAmtInput.value = sellPkr.toFixed(2);

            let totalVisaSelling = 0;
            document.querySelectorAll('.visa-sell-amount').forEach(el => {
                totalVisaSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('visa_charges')) {
                document.getElementById('visa_charges').value = totalVisaSelling.toFixed(2);
            }
            calcTotal();
        }

        function syncBookingHotelDates() {
            const hotelBlocks = Array.from(document.querySelectorAll('.hotel-block'));
            let prevCheckoutDate = null;

            hotelBlocks.forEach((block, idx) => {
                const checkInEl  = block.querySelector('input[name*="[check_in]"]');
                const checkOutEl = block.querySelector('input[name*="[check_out]"]');

                if (checkInEl) {
                    if (idx > 0 && prevCheckoutDate) {
                        checkInEl.setAttribute('min', prevCheckoutDate);
                        if (checkInEl.value && checkInEl.value < prevCheckoutDate) {
                            checkInEl.value = prevCheckoutDate;
                        }
                    } else {
                        checkInEl.removeAttribute('min');
                    }
                }

                if (checkOutEl) {
                    const currentIn = checkInEl?.value;
                    if (currentIn) {
                        checkOutEl.setAttribute('min', currentIn);
                        if (checkOutEl.value && checkOutEl.value < currentIn) {
                            checkOutEl.value = currentIn;
                        }
                    } else if (idx > 0 && prevCheckoutDate) {
                        checkOutEl.setAttribute('min', prevCheckoutDate);
                    } else {
                        checkOutEl.removeAttribute('min');
                    }
                }

                if (checkOutEl && checkOutEl.value) {
                    prevCheckoutDate = checkOutEl.value;
                } else {
                    prevCheckoutDate = null;
                }
            });
        }

        function handleHotelDateCalc(target) {
            const row = target.closest('.hotel-block');
            if (!row) return;

            const checkInEl  = row.querySelector('input[name*="[check_in]"]');
            const checkOutEl = row.querySelector('input[name*="[check_out]"]');
            const nightsEl   = row.querySelector('input[name*="[no_of_nights]"]');

            if (!checkInEl || !checkOutEl || !nightsEl) return;

            const isCheckIn  = target === checkInEl;
            const isCheckOut = target === checkOutEl;
            const isNights   = target === nightsEl;

            if (!isCheckIn && !isCheckOut && !isNights) return;

            const inVal  = checkInEl.value;
            const outVal = checkOutEl.value;
            const nVal   = parseInt(nightsEl.value);

            if ((isCheckIn || isCheckOut) && inVal && outVal) {
                const dIn  = new Date(inVal);
                const dOut = new Date(outVal);
                const diff = Math.round((dOut.getTime() - dIn.getTime()) / (1000 * 3600 * 24));
                if (!isNaN(diff) && diff >= 0) {
                    nightsEl.value = diff;
                }
            } else if ((isCheckIn || isNights) && inVal && !isNaN(nVal) && nVal >= 0) {
                const dIn = new Date(inVal);
                dIn.setDate(dIn.getDate() + nVal);
                const year = dIn.getFullYear();
                const month = String(dIn.getMonth() + 1).padStart(2, '0');
                const day = String(dIn.getDate()).padStart(2, '0');
                checkOutEl.value = `${year}-${month}-${day}`;
            } else if (isCheckOut && outVal && !isNaN(nVal) && nVal >= 0 && !inVal) {
                const dOut = new Date(outVal);
                dOut.setDate(dOut.getDate() - nVal);
                const year = dOut.getFullYear();
                const month = String(dOut.getMonth() + 1).padStart(2, '0');
                const day = String(dOut.getDate()).padStart(2, '0');
                checkInEl.value = `${year}-${month}-${day}`;
            }

            syncBookingHotelDates();
            calculateHotelRow(row);
        }

        // Main Total and Dual Currency Costing Calculation
        function calcTotal() {
            const adults   = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants  = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const pax      = Math.max(1, adults + children + infants);

            // 1. Flight SAR & PKR
            let flightSar = 0;
            const flSellSarDisp = document.getElementById('flight_sell_sar_total')?.textContent || '';
            flightSar = parseFloat(flSellSarDisp) || 0;
            let flightPkr = parseFloat(document.getElementById('flight_charges')?.value) || 0;

            // 2. Hotel SAR & PKR
            let hotelCostSar = 0;
            let hotelCostPkr = 0;
            let hotelSellSar = 0;
            let hotelSellPkr = 0;
            document.querySelectorAll('.hotel-block').forEach(row => {
                const nights   = parseFloat(row.querySelector('.hotel-nights')?.value) || 0;
                const rooms    = parseFloat(row.querySelector('.hotel-rooms')?.value) || 0;
                const costRate = parseFloat(row.querySelector('.hotel-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.hotel-sell-rate')?.value) || 0;
                const roe      = parseFloat(row.querySelector('.hotel-roe')?.value) || 75;

                const cSar = nights * rooms * costRate;
                hotelCostSar += cSar;
                hotelCostPkr += cSar * roe;

                const sSar = nights * rooms * sellRate;
                hotelSellSar += sSar;
                hotelSellPkr += sSar * roe;
            });
            if (document.getElementById('hotel_charges')) document.getElementById('hotel_charges').value = hotelSellPkr.toFixed(2);
            if (document.getElementById('hotel_cost_sar_total')) document.getElementById('hotel_cost_sar_total').textContent = hotelCostSar.toFixed(2) + ' SAR';
            if (document.getElementById('hotel_cost_pkr_total')) document.getElementById('hotel_cost_pkr_total').textContent = hotelCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('hotel_sell_sar_total')) document.getElementById('hotel_sell_sar_total').textContent = hotelSellSar.toFixed(2) + ' SAR';
            if (document.getElementById('hotel_sell_pkr_total')) document.getElementById('hotel_sell_pkr_total').textContent = hotelSellPkr.toFixed(2) + ' PKR';

            // 3. Transport SAR & PKR
            let transportCostSar = 0;
            let transportCostPkr = 0;
            let transportSellSar = 0;
            let transportSellPkr = 0;
            document.querySelectorAll('.route-block').forEach(row => {
                const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;
                const roe      = parseFloat(row.querySelector('.transport-roe')?.value) || 75;

                transportCostSar += costRate;
                transportCostPkr += costRate * roe;

                transportSellSar += sellRate;
                transportSellPkr += sellRate * roe;
            });
            if (document.getElementById('transport_charges')) document.getElementById('transport_charges').value = transportSellPkr.toFixed(2);
            if (document.getElementById('transport_cost_sar_total')) document.getElementById('transport_cost_sar_total').textContent = transportCostSar.toFixed(2) + ' SAR';
            if (document.getElementById('transport_cost_pkr_total')) document.getElementById('transport_cost_pkr_total').textContent = transportCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('transport_sell_sar_total')) document.getElementById('transport_sell_sar_total').textContent = transportSellSar.toFixed(2) + ' SAR';
            if (document.getElementById('transport_sell_pkr_total')) document.getElementById('transport_sell_pkr_total').textContent = transportSellPkr.toFixed(2) + ' PKR';

            // 4. Visa SAR & PKR
            let visaCostSar = 0;
            let visaCostPkr = 0;
            let visaSellSar = 0;
            let visaSellPkr = 0;
            document.querySelectorAll('.visa-card').forEach(row => {
                const costRate = parseFloat(row.querySelector('.visa-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.visa-sell-rate')?.value) || 0;
                const roe      = parseFloat(row.querySelector('.visa-roe')?.value) || 75;

                visaCostSar += costRate;
                visaCostPkr += costRate * roe;

                visaSellSar += sellRate;
                visaSellPkr += sellRate * roe;
            });
            if (document.getElementById('visa_charges')) document.getElementById('visa_charges').value = visaSellPkr.toFixed(2);
            if (document.getElementById('visa_cost_sar_total')) document.getElementById('visa_cost_sar_total').textContent = visaCostSar.toFixed(2) + ' SAR';
            if (document.getElementById('visa_cost_pkr_total')) document.getElementById('visa_cost_pkr_total').textContent = visaCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('visa_sell_sar_total')) document.getElementById('visa_sell_sar_total').textContent = visaSellSar.toFixed(2) + ' SAR';
            if (document.getElementById('visa_sell_pkr_total')) document.getElementById('visa_sell_pkr_total').textContent = visaSellPkr.toFixed(2) + ' PKR';

            // Package, Other, Discount
            const pkgCost     = parseFloat(document.getElementById('package_cost')?.value) || 0;
            const pkgTotalPkr = pkgCost * pax;

            const other      = parseFloat(document.getElementById('other_charges')?.value) || 0;
            const discount   = parseFloat(document.getElementById('discount')?.value) || 0;
            const additional = parseFloat(document.getElementById('additional_services_amount')?.value) || 0;
            const received   = parseFloat(document.getElementById('total_received')?.value) || 0;

            const totalSar   = flightSar + hotelSellSar + transportSellSar + visaSellSar;
            const totalPkr   = pkgTotalPkr + hotelSellPkr + visaSellPkr + flightPkr + transportSellPkr + other + additional - discount;
            const balancePkr = totalPkr - received;

            const avgRoe     = totalSar > 0 ? (totalPkr / totalSar) : 75;
            const balanceSar = avgRoe > 0 ? (balancePkr / avgRoe) : 0;

            if (document.getElementById('total_amount')) document.getElementById('total_amount').value = totalPkr.toFixed(2);
            if (document.getElementById('balance')) document.getElementById('balance').value = balancePkr.toFixed(2);

            // Summary Bar
            if (document.getElementById('bar_pax')) document.getElementById('bar_pax').textContent = `${pax} (A:${adults}, C:${children}, I:${infants})`;
            if (document.getElementById('bar_pax_badge')) document.getElementById('bar_pax_badge').textContent = pax;
            if (document.getElementById('bar_adult')) document.getElementById('bar_adult').textContent = `PKR ${pkgTotalPkr.toFixed(2)}`;
            if (document.getElementById('bar_flight')) document.getElementById('bar_flight').textContent = `SAR ${flightSar.toFixed(2)} / PKR ${flightPkr.toFixed(2)}`;
            if (document.getElementById('bar_hotel')) document.getElementById('bar_hotel').textContent = `SAR ${hotelSellSar.toFixed(2)} / PKR ${hotelSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_transport')) document.getElementById('bar_transport').textContent = `SAR ${transportSellSar.toFixed(2)} / PKR ${transportSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_visa')) document.getElementById('bar_visa').textContent = `SAR ${visaSellSar.toFixed(2)} / PKR ${visaSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_total')) document.getElementById('bar_total').textContent = `SAR ${totalSar.toFixed(2)} / PKR ${totalPkr.toFixed(2)}`;
            if (document.getElementById('bar_balance')) document.getElementById('bar_balance').textContent = `SAR ${balanceSar.toFixed(2)} / PKR ${balancePkr.toFixed(2)}`;

            // Costing Dual Currency Cards
            if (document.getElementById('cost_sar_flight')) document.getElementById('cost_sar_flight').textContent = `${flightSar.toFixed(2)} SAR`;
            if (document.getElementById('cost_sar_hotel')) document.getElementById('cost_sar_hotel').textContent = `${hotelSellSar.toFixed(2)} SAR`;
            if (document.getElementById('cost_sar_transport')) document.getElementById('cost_sar_transport').textContent = `${transportSellSar.toFixed(2)} SAR`;
            if (document.getElementById('cost_sar_visa')) document.getElementById('cost_sar_visa').textContent = `${visaSellSar.toFixed(2)} SAR`;
            if (document.getElementById('cost_sar_total')) document.getElementById('cost_sar_total').textContent = `${totalSar.toFixed(2)} SAR`;
            if (document.getElementById('cost_sar_balance')) document.getElementById('cost_sar_balance').textContent = `${balanceSar.toFixed(2)} SAR`;

            if (document.getElementById('cost_pkr_flight')) document.getElementById('cost_pkr_flight').textContent = `${flightPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_hotel')) document.getElementById('cost_pkr_hotel').textContent = `${hotelSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_transport')) document.getElementById('cost_pkr_transport').textContent = `${transportSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_visa')) document.getElementById('cost_pkr_visa').textContent = `${visaSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_total')) document.getElementById('cost_pkr_total').textContent = `${totalPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_balance')) document.getElementById('cost_pkr_balance').textContent = `${balancePkr.toFixed(2)} PKR`;

            // Costing Table Breakdown
            if (document.getElementById('sum_pax')) document.getElementById('sum_pax').textContent = pax;
            if (document.getElementById('sum_pkg')) document.getElementById('sum_pkg').textContent = pkgTotalPkr.toFixed(2);
            if (document.getElementById('sum_hotel')) document.getElementById('sum_hotel').textContent = hotelSellPkr.toFixed(2);
            if (document.getElementById('sum_visa')) document.getElementById('sum_visa').textContent = visaSellPkr.toFixed(2);
            if (document.getElementById('sum_flight')) document.getElementById('sum_flight').textContent = flightPkr.toFixed(2);
            if (document.getElementById('sum_transport')) document.getElementById('sum_transport').textContent = transportSellPkr.toFixed(2);
            if (document.getElementById('sum_other')) document.getElementById('sum_other').textContent = (other + additional).toFixed(2);
            if (document.getElementById('sum_discount')) document.getElementById('sum_discount').textContent = discount.toFixed(2);
            if (document.getElementById('sum_total')) document.getElementById('sum_total').textContent = totalPkr.toFixed(2);
        }

        // Global Event Delegation for live calculations
        document.addEventListener('input', function(e) {
            if (e.target.matches('.hotel-nights, .hotel-rooms, .hotel-roe, .hotel-cost-rate, .hotel-sell-rate')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            }
            if (e.target.matches('.visa-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-rate, .transport-sell-rate')) {
                const row = e.target.closest('.route-block');
                calculateTransportRow(row);
            }
            if (e.target.matches('.flight-tier-calc, #flight_roe')) {
                calculateFlight();
            }
            if (e.target.matches('.calc, #package_cost, #other_charges, #discount, #additional_services_amount, #total_received')) {
                calcTotal();
            }
            if (e.target.matches('input[name*="[check_in]"], input[name*="[check_out]"], input[name*="[no_of_nights]"]')) {
                handleHotelDateCalc(e.target);
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.matches('.hotel-nights, .hotel-rooms, .hotel-roe, .hotel-cost-rate, .hotel-sell-rate')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            }
            if (e.target.matches('.visa-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-rate, .transport-sell-rate')) {
                const row = e.target.closest('.route-block');
                calculateTransportRow(row);
            }
            if (e.target.matches('.flight-tier-calc, #flight_roe')) {
                calculateFlight();
            }
            if (e.target.matches('.calc, #package_cost, #other_charges, #discount, #additional_services_amount, #total_received')) {
                calcTotal();
            }
            if (e.target.matches('input[name*="[check_in]"], input[name*="[check_out]"], input[name*="[no_of_nights]"]')) {
                handleHotelDateCalc(e.target);
            }
        });

        // Initialize on page load
        rebuildPersons();
        syncFlightPersons();
        syncVisas();
        syncBookingHotelDates();
        calculateFlight();
        calcTotal();

        $(document).ready(function() {
            if (typeof $ !== 'undefined' && $.fn.summernote) {
                $('a[href="#tab-additional-services"]').on('shown.bs.tab', function() {
                    if (!$('#additional_services_detail').next().hasClass('note-editor')) {
                        $('#additional_services_detail').summernote({
                            height: 200,
                            placeholder: 'Enter additional service details here...',
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['fontname', ['fontname']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['height', ['height']],
                                ['insert', ['link', 'picture', 'hr']],
                                ['view', ['fullscreen', 'codeview']],
                                ['help', ['help']]
                            ]
                        });
                    }
                });
                
                if ($('#tab-additional-services').hasClass('active')) {
                    $('#additional_services_detail').summernote({
                        height: 200,
                        placeholder: 'Enter additional service details here...',
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                        ]
                    });
                }
            }

            // Initialize Draft Auto-saver & Restorer
            BookingDraftManager.init();
        });

        // Booking Form Draft Manager
        const BookingDraftManager = {
            storageKey: 'dua_booking_create_draft_v6',
            saveTimer: null,

            init() {
                let isReload = false;
                try {
                    const navEntries = performance.getEntriesByType('navigation');
                    if (navEntries && navEntries.length > 0) {
                        isReload = (navEntries[0].type === 'reload');
                    } else if (window.performance && window.performance.navigation) {
                        isReload = (window.performance.navigation.type === 1);
                    }
                } catch(e) {
                    isReload = false;
                }

                if (isReload) {
                    this.restoreDraft();
                } else {
                    this.clearDraft(false);
                }

                const form = document.getElementById('bookingForm');
                if (form) {
                    form.addEventListener('input', () => this.scheduleSave());
                    form.addEventListener('change', () => this.scheduleSave());
                    form.addEventListener('submit', () => this.clearDraft(false));
                }

                document.querySelectorAll('#bookingTabs .nav-link').forEach(link => {
                    link.addEventListener('shown.bs.tab', () => this.scheduleSave());
                });

                window.addEventListener('focus', () => { refreshMasterData(false); });
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') { refreshMasterData(false); }
                });
            },

            scheduleSave() {
                clearTimeout(this.saveTimer);
                this.saveTimer = setTimeout(() => this.saveDraft(), 400);
            },

            saveDraft() {
                try {
                    const form = document.getElementById('bookingForm');
                    if (!form) return;

                    const formData = {};
                    const elements = form.querySelectorAll('input, select, textarea');

                    elements.forEach(el => {
                        if (!el.name || el.type === 'file' || el.type === 'submit' || el.name === '_token') return;
                        if (el.type === 'checkbox' || el.type === 'radio') {
                            formData[el.name] = el.checked;
                        } else {
                            formData[el.name] = el.value;
                        }
                    });

                    const hotelCount = document.querySelectorAll('.hotel-block').length;
                    const routeCount = document.querySelectorAll('.route-block').length;
                    const activeTab  = document.querySelector('#bookingTabs .nav-link.active')?.getAttribute('href') || '#tab-details';

                    if (typeof $ !== 'undefined' && $('#additional_services_detail').length && typeof $('#additional_services_detail').summernote === 'function') {
                        if ($('#additional_services_detail').next().hasClass('note-editor')) {
                            formData['additional_services_detail'] = $('#additional_services_detail').summernote('code');
                        }
                    }

                    const payload = {
                        timestamp: Date.now(),
                        activeTab: activeTab,
                        hotelCount: hotelCount,
                        routeCount: routeCount,
                        fields: formData
                    };

                    localStorage.setItem(this.storageKey, JSON.stringify(payload));
                } catch(e) {
                    console.warn('Draft save error:', e);
                }
            },

            restoreDraft() {
                try {
                    const raw = localStorage.getItem(this.storageKey);
                    if (!raw) return;

                    const draft = JSON.parse(raw);
                    if (!draft || !draft.fields) return;

                    if (Date.now() - draft.timestamp > 259200000) {
                        localStorage.removeItem(this.storageKey);
                        return;
                    }

                    const fields = draft.fields;

                    if (fields['pax_adults']) document.getElementById('pax_adults').value = fields['pax_adults'];
                    if (fields['pax_children']) document.getElementById('pax_children').value = fields['pax_children'];
                    if (fields['pax_infants']) document.getElementById('pax_infants').value = fields['pax_infants'];
                    if (fields['no_of_pax']) document.getElementById('no_of_pax').value = fields['no_of_pax'];
                    rebuildPersons();

                    const currentHotels = document.querySelectorAll('.hotel-block').length;
                    const targetHotels  = draft.hotelCount || 0;
                    for (let i = currentHotels; i < targetHotels; i++) {
                        addNewHotel('other');
                    }

                    const currentRoutes = document.querySelectorAll('.route-block').length;
                    const targetRoutes  = draft.routeCount || 1;
                    for (let i = currentRoutes; i < targetRoutes; i++) {
                        document.getElementById('addRoute')?.click();
                    }

                    Object.keys(fields).forEach(name => {
                        const el = document.querySelector(`[name="${name}"]`);
                        if (el && el.type !== 'file') {
                            if (el.type === 'checkbox' || el.type === 'radio') {
                                el.checked = Boolean(fields[name]);
                            } else {
                                el.value = fields[name];
                                if (typeof $ !== 'undefined' && $(el).hasClass('select2')) {
                                    $(el).val(fields[name]).trigger('change.select2');
                                }
                            }
                        }
                    });

                    syncFlightPersons();
                    syncVisas();
                    syncBookingHotelDates();

                    calculateFlight();
                    document.querySelectorAll('.hotel-block').forEach(r => calculateHotelRow(r));
                    document.querySelectorAll('.route-block').forEach(r => calculateTransportRow(r));
                    document.querySelectorAll('.visa-card').forEach(r => calculateVisaRow(r));
                    calcTotal();

                    if (draft.activeTab && draft.activeTab !== '#tab-package') {
                        const tabLink = document.querySelector(`#bookingTabs a[href="${draft.activeTab}"]`);
                        if (tabLink) setTimeout(() => tabLink.click(), 150);
                    }
                } catch(e) {
                    console.error('Draft restore error:', e);
                }
            },

            clearDraft(andReload = false) {
                localStorage.removeItem(this.storageKey);
                if (andReload) window.location.reload();
            },

            discardDraft() {
                if (confirm('Are you sure you want to discard this draft and reset all fields?')) {
                    this.clearDraft(true);
                }
            }
        };

        // Live Room Capacity Real-Time Checker
        function checkHotelRoomCapacity(input) {
            const row = input.closest('.hotel-row') || input.closest('.row') || input.closest('.hotel-block');
            if (!row) return;

            const roomNumber = input.value.trim();
            let fb = row.querySelector('.room-capacity-feedback');

            if (!roomNumber) {
                input.style.borderColor = '';
                if (fb) fb.innerHTML = '';
                return;
            }

            const hotelSelect = row.querySelector('select[name*="[hotel_name]"]') || row.querySelector('input[name*="[hotel_name]"]');
            const hotelName = hotelSelect ? (hotelSelect.options ? (hotelSelect.options[hotelSelect.selectedIndex]?.text || hotelSelect.value) : hotelSelect.value) : '';
            const locSelect = row.querySelector('select[name*="[location]"]') || row.querySelector('input[name*="[location]"]');
            const location = locSelect ? locSelect.value : '';
            const typeSelect = row.querySelector('select[name*="[room_type]"]');
            const roomType = typeSelect ? typeSelect.value : '';
            const checkInInput = row.querySelector('input[name*="[check_in]"]');
            const checkOutInput = row.querySelector('input[name*="[check_out]"]');
            const checkIn = checkInInput ? checkInInput.value : '';
            const checkOut = checkOutInput ? checkOutInput.value : '';

            if (!fb) {
                fb = document.createElement('div');
                fb.className = 'room-capacity-feedback mt-1';
                input.parentNode.appendChild(fb);
            }

            fb.innerHTML = '<span class="text-muted" style="font-size:10px;"><i class="mdi mdi-loading mdi-spin me-1"></i>Checking room...</span>';

            const params = new URLSearchParams({
                hotel_name: hotelName,
                room_number: roomNumber,
                location: location,
                room_type: roomType,
                check_in: checkIn,
                check_out: checkOut
            });

            fetch(`{{ route('api.hotel-room.check-capacity') }}?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        fb.innerHTML = '';
                        input.style.borderColor = '';
                        return;
                    }
                    if (data.is_overbooked || data.is_full) {
                        input.style.borderColor = '#dc3545';
                        fb.innerHTML = `<span class="badge bg-danger text-white d-block text-start p-1" style="font-size:10px; white-space:normal;"><i class="mdi mdi-alert-circle me-1"></i>${data.message}</span>`;
                    } else if (data.occupied > 0) {
                        input.style.borderColor = '#ffc107';
                        fb.innerHTML = `<span class="badge bg-warning text-dark d-block text-start p-1" style="font-size:10px; white-space:normal;"><i class="mdi mdi-information me-1"></i>${data.message}</span>`;
                    } else {
                        input.style.borderColor = '#198754';
                        fb.innerHTML = `<span class="badge bg-success text-white d-block text-start p-1" style="font-size:10px;"><i class="mdi mdi-check me-1"></i>Room ${data.room_number}: Free (${data.capacity} Beds)</span>`;
                    }
                })
                .catch(err => {
                    console.log('Room check error:', err);
                    if (fb) fb.innerHTML = '';
                });
        }

        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('hotel-room-number-input')) {
                clearTimeout(e.target._roomCheckTimer);
                e.target._roomCheckTimer = setTimeout(() => checkHotelRoomCapacity(e.target), 400);
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('hotel-room-number-input')) {
                checkHotelRoomCapacity(e.target);
            }
        });
    </script>
@endsection
