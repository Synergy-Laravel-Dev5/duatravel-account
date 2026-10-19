@extends('layout.master')
@section('title', 'Edit Booking')

@section('content')
@php
    $dashboardPkg = old('package_type', $booking->package_type ?? 'umrah');
    $isUmrah = ($dashboardPkg === 'umrah');
@endphp
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Edit Booking</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-info btn-sm">
                            <i class="mdi mdi-eye me-1"></i> View
                        </a>
                        <a href="{{ route('booking.index') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

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

                {{-- Summary Bar --}}
                <div class="booking-summary-bar mb-3 px-3 py-2 d-flex flex-wrap gap-3 align-items-center justify-content-between rounded shadow-sm"
                    style="background:#f0f4ff; border:1px solid #d0d9f0; font-size:13px;">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span><i class="mdi mdi-account-group text-primary"></i> Pax: <strong id="bar_pax">{{ $booking->no_of_pax }}</strong></span>
                        <span style="{{ $isUmrah ? 'display:none;' : '' }}">|</span>
                        <span style="{{ $isUmrah ? 'display:none;' : '' }}">Package: <strong id="bar_adult">{{ number_format($booking->package_cost * $booking->no_of_pax, 2) }}</strong></span>
                        <span>|</span>
                        <span>Flight: <strong id="bar_flight" class="text-dark">SAR 0.00 / PKR {{ number_format($booking->flight_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span>Hotel: <strong id="bar_hotel" class="text-dark">SAR 0.00 / PKR {{ number_format($booking->hotel_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span>Transport: <strong id="bar_transport" class="text-dark">SAR 0.00 / PKR {{ number_format($booking->transport_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span>Visa: <strong id="bar_visa" class="text-dark">SAR 0.00 / PKR {{ number_format($booking->visa_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span class="text-primary fw-bold">Total: <strong id="bar_total">SAR 0.00 / PKR {{ number_format($booking->total_amount, 2) }}</strong></span>
                        <span>|</span>
                        <span class="text-danger fw-bold">Balance: <strong id="bar_balance">SAR 0.00 / PKR {{ number_format($booking->balance, 2) }}</strong></span>
                    </div>
                </div>

                <form action="{{ route('booking.update', $booking->id) }}" method="POST" id="bookingForm" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')

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

                    <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white">

                        {{-- TAB 1: Package --}}
                        <div class="tab-pane fade {{ $isUmrah ? '' : 'show active' }}" id="tab-package">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Select Package <span class="text-danger">*</span></label>
                                    <select name="package_id" id="packageSelect" class="form-select" onchange="onPackageSelectChange(this)">
                                        <option value="">-- Select Package --</option>
                                        @foreach ($packages as $pkg)
                                            <option value="{{ $pkg->id }}" {{ old('package_id', $booking->package_id) == $pkg->id ? 'selected' : '' }}>
                                                {{ $pkg->name }}{{ $pkg->code ? ' (' . $pkg->code . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Year <span class="text-danger">*</span></label>
                                    <select name="package_year" id="package_year" class="form-select">
                                        @php $currentYear = date('Y'); @endphp
                                        @for ($year = $currentYear - 1; $year <= $currentYear + 5; $year++)
                                            <option value="{{ $year }}"
                                                {{ old('package_year', $booking->package_year ?? $currentYear) == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-6" id="package_name_container" style="{{ $isUmrah ? 'display:none;' : '' }}">
                                    <label class="form-label">Package Name</label>
                                    <input type="text" name="package_name" id="package_name" class="form-control"
                                        value="{{ old('package_name', $booking->package_name) }}"
                                        placeholder="e.g. Economy Umrah 2025">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Booking Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>
                                            Confirmed</option>
                                        <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled</option>
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
                                                {{ old('client_id', $booking->client_id) == $cl->id ? 'selected' : '' }}>
                                                {{ $cl->name }}{{ $cl->company_name ? ' (' . $cl->company_name . ')' : '' }} - {{ $cl->phone ?? $cl->cnic }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <div class="card bg-light border p-3 mb-1">
                                        @php
                                            $bTotalPax = (int) ($booking->no_of_pax ?: 1);
                                            $bChildren = (int) ($booking->children_count ?? 0);
                                            $bInfants  = (int) ($booking->infants_count ?? 0);
                                            $bAdults   = (int) ($booking->adults_count ?? 0);
                                            if (($bAdults + $bChildren + $bInfants) !== $bTotalPax) {
                                                $bAdults = max(1, $bTotalPax - $bChildren - $bInfants);
                                            }
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-primary mb-0"><i class="mdi mdi-account-group me-1"></i> Passengers Breakdown</h6>
                                            <span class="badge bg-primary fs-12">Total Pax: <span id="bar_pax_badge">{{ $bTotalPax }}</span></span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Adults (12+ yrs) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account"></i></span>
                                                    <input type="number" name="adults_count" id="pax_adults" class="form-control pax-breakdown-calc"
                                                        value="{{ old('adults_count', $bAdults) }}" min="1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Children (2-11 yrs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-child"></i></span>
                                                    <input type="number" name="children_count" id="pax_children" class="form-control pax-breakdown-calc"
                                                        value="{{ old('children_count', $booking->children_count ?? 0) }}" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Infants (0-2 yrs)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-baby-carriage"></i></span>
                                                    <input type="number" name="infants_count" id="pax_infants" class="form-control pax-breakdown-calc"
                                                        value="{{ old('infants_count', $booking->infants_count ?? 0) }}" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-primary">Total Passengers</label>
                                                <input type="number" name="no_of_pax" id="no_of_pax" class="form-control bg-white fw-bold text-primary"
                                                    value="{{ old('no_of_pax', $booking->no_of_pax) }}" min="1" readonly required>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2"><i class="mdi mdi-information-outline"></i> Persons, Flight, Hotel, Transport & Visa tabs will update automatically based on passenger breakdown.</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Care Of</label>
                                    <input type="text" name="care_of" class="form-control"
                                        value="{{ old('care_of', $booking->care_of) }}" placeholder="Guardian / Agent">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Passport Number</label>
                                    <input type="text" name="passport_number" id="fill_passport" class="form-control"
                                        value="{{ old('passport_number', $booking->passport_number) }}" placeholder="Passport #">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">CNIC Number</label>
                                    <input type="text" name="cnic" id="fill_cnic" class="form-control"
                                        value="{{ old('cnic', $booking->cnic) }}" placeholder="CNIC #">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" id="fill_phone" class="form-control"
                                        value="{{ old('phone', $booking->phone) }}" placeholder="Phone #">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Phone</label>
                                    <input type="text" name="emergency_phone" class="form-control"
                                        value="{{ old('emergency_phone', $booking->emergency_phone) }}" placeholder="+92 300 0000000">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Booking Date</label>
                                    <input type="date" name="booking_date" id="booking_date" class="form-control"
                                        value="{{ old('booking_date', $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                </div>
                                <input type="hidden" name="currency" id="booking_currency" value="{{ $booking->currency ?? "PKR" }}">
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
                            <p class="text-muted small mb-3">
                                <i class="mdi mdi-information me-1"></i>
                                Rows are based on No. of Pax. Main passenger is linked to client details.
                            </p>
                            <div id="personsList">
                                @foreach ($booking->persons as $i => $person)
    <div class="person-card" id="person_card_{{ $i }}">
        <div class="mb-2">
            <strong class="text-primary" style="font-size:13px;">
                {{ $i === 0 ? 'Main Passenger' : 'Passenger ' . ($i + 1) }}
            </strong>
        </div>
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Surname (Passport)</label>
                <input type="text" name="persons[{{ $i }}][surname]" id="person_surname_{{ $i }}"
                    class="form-control form-control-sm" value="{{ $person->surname }}" placeholder="Surname" oninput="updatePersonFullName({{ $i }})">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Given Name (Passport)</label>
                <input type="text" name="persons[{{ $i }}][given_name]" id="person_given_name_{{ $i }}"
                    class="form-control form-control-sm" value="{{ $person->given_name }}" placeholder="Given Name" oninput="updatePersonFullName({{ $i }})">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Full Name</label>
                <input type="text" name="persons[{{ $i }}][full_name]" id="person_name_{{ $i }}"
                    class="form-control form-control-sm" value="{{ $person->full_name }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Passport #</label>
                <input type="text" name="persons[{{ $i }}][passport_number]" id="person_passport_{{ $i }}"
                    class="form-control form-control-sm" value="{{ $person->passport_number }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:12px;">Date of Birth</label>
                <input type="date" name="persons[{{ $i }}][dob]" id="person_dob_{{ $i }}"
                    class="form-control form-control-sm" value="{{ $person->dob ? \Carbon\Carbon::parse($person->dob)->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:12px;">Gender</label>
                <select name="persons[{{ $i }}][gender]" id="person_gender_{{ $i }}" class="form-select form-select-sm">
                    <option value="">-- Select --</option>
                    <option value="male" {{ strtolower($person->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ strtolower($person->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:12px;">City</label>
                <input type="text" name="persons[{{ $i }}][city]"
                    id="person_city_{{ $i }}"
                    class="form-control form-control-sm"
                    value="{{ $person->city ?? '' }}" placeholder="e.g. Lahore / Karachi">
            </div>
        </div>

        {{-- Passenger Documents --}}
        <div class="border-top mt-3 pt-3">
            <strong class="text-secondary d-block mb-2" style="font-size:12px;">
                <i class="mdi mdi-file-document-multiple me-1"></i> Documents (Optional)
            </strong>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:11px;">Passport First Page</label>
                    @if($person->passport_photo)
                        <div class="mb-1"><a href="{{ asset('storage/' . $person->passport_photo) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                    @endif
                    <input type="hidden" name="persons[{{ $i }}][old_passport_photo]" value="{{ $person->passport_photo }}">
                    <input type="file" name="persons[{{ $i }}][passport_photo]" class="form-control form-control-sm" accept="image/*,.pdf">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:11px;">Picture of Passenger</label>
                    @if($person->photo)
                        <div class="mb-1"><a href="{{ asset('storage/' . $person->photo) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                    @endif
                    <input type="hidden" name="persons[{{ $i }}][old_photo]" value="{{ $person->photo }}">
                    <input type="file" name="persons[{{ $i }}][photo]" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:11px;">Medical Fitness Certificate</label>
                    @if($person->medical_certificate)
                        <div class="mb-1"><a href="{{ asset('storage/' . $person->medical_certificate) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                    @endif
                    <input type="hidden" name="persons[{{ $i }}][old_medical_certificate]" value="{{ $person->medical_certificate }}">
                    <input type="file" name="persons[{{ $i }}][medical_certificate]" class="form-control form-control-sm" accept="image/*,.pdf">
                </div>
            </div>
        </div>
    </div>
@endforeach
                            </div>
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
                            @php
                                $flightCurr = old('flight_currency', $booking->flight_currency ?? 'SAR');
                                $flightCostRoe = old('flight_cost_roe', $booking->flight_cost_roe ?? $booking->flight_roe ?? 75);
                                $flightSaleRoe = old('flight_sale_roe', $booking->flight_sale_roe ?? $booking->flight_roe ?? 75);
                                $isFlightPkr = ($flightCurr === 'PKR');
                            @endphp
                            <div class="card mb-4 border shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h6 class="text-primary mb-0 font-weight-bold">
                                        <i class="mdi mdi-airplane me-1"></i> Flight Supplier & Passenger Tier Rates (<span class="flight-curr-label">{{ $flightCurr }}</span> & PKR)
                                    </h6>
                                    <span class="badge bg-info text-white" id="flight_currency_badge">{{ $isFlightPkr ? 'PKR Direct (ROE = 1)' : 'Multi-Currency & PKR' }}</span>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-semibold">Flight Supplier / Vendor</span>
                                                <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshSuppliersList(true)" title="Reload Suppliers"><i class="mdi mdi-refresh"></i> Refresh</button>
                                            </label>
                                            <select name="flight_supplier_id" id="flight_supplier_id" class="form-select">
                                                <option value="">-- Select Flight Supplier --</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->id }}" {{ old('flight_supplier_id', $booking->flight_supplier_id) == $sup->id ? 'selected' : '' }}>
                                                        {{ $sup->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-dark fw-bold"><i class="mdi mdi-currency-usd me-1"></i> Currency <span class="text-danger">*</span></label>
                                            <select name="flight_currency" id="flight_currency" class="form-select fw-bold">
                                                <option value="SAR" {{ $flightCurr === 'SAR' ? 'selected' : '' }}>SAR (Saudi Riyal)</option>
                                                <option value="PKR" {{ $flightCurr === 'PKR' ? 'selected' : '' }}>PKR (Pakistani Rupee)</option>
                                                <option value="USD" {{ $flightCurr === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                                <option value="MYR" {{ $flightCurr === 'MYR' ? 'selected' : '' }}>MYR (Malaysian Ringgit)</option>
                                                <option value="AED" {{ $flightCurr === 'AED' ? 'selected' : '' }}>AED (UAE Dirham)</option>
                                                <option value="EUR" {{ $flightCurr === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                                <option value="GBP" {{ $flightCurr === 'GBP' ? 'selected' : '' }}>GBP (British Pound)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-danger fw-bold"><i class="mdi mdi-tag-outline me-1"></i> Flight Cost ROE <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-danger text-white fw-bold flight-curr-prefix">1 {{ $flightCurr }} =</span>
                                                <input type="number" step="0.0001" name="flight_cost_roe" id="flight_cost_roe"
                                                    class="form-control flight-roe fw-bold text-danger" placeholder="75.00" value="{{ $flightCostRoe }}" {{ $isFlightPkr ? 'readonly' : '' }}>
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-success fw-bold"><i class="mdi mdi-cash-multiple me-1"></i> Flight Sale ROE <span class="text-success">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-success text-white fw-bold flight-curr-prefix">1 {{ $flightCurr }} =</span>
                                                <input type="number" step="0.0001" name="flight_sale_roe" id="flight_sale_roe"
                                                    class="form-control flight-roe fw-bold text-success" placeholder="75.00" value="{{ $flightSaleRoe }}" {{ $isFlightPkr ? 'readonly' : '' }}>
                                                <span class="input-group-text">PKR</span>
                                            </div>
                                            <input type="hidden" name="flight_roe" id="flight_roe" value="{{ $flightCostRoe }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label"><i class="mdi mdi-calendar me-1 text-primary"></i> Flight Booking Date</label>
                                            <input type="date" name="flight_booking_date" class="form-control" value="{{ old('flight_booking_date', $booking->flight_booking_date ? \Carbon\Carbon::parse($booking->flight_booking_date)->format('Y-m-d') : ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d'))) }}">
                                        </div>
                                    </div>

                                    {{-- Hidden fields for Controller compatibility --}}
                                    <input type="hidden" name="flight_adult_cost" id="flight_adult_cost" value="{{ old('flight_adult_cost', $booking->flight_adult_cost ?? $booking->flight_cost_rate ?? 0) }}">
                                    <input type="hidden" name="flight_adult_sell" id="flight_adult_sell" value="{{ old('flight_adult_sell', $booking->flight_adult_sell ?? $booking->flight_selling_rate ?? 0) }}">
                                    <input type="hidden" name="flight_child_cost" id="flight_child_cost" value="{{ old('flight_child_cost', $booking->flight_child_cost ?? 0) }}">
                                    <input type="hidden" name="flight_child_sell" id="flight_child_sell" value="{{ old('flight_child_sell', $booking->flight_child_sell ?? 0) }}">
                                    <input type="hidden" name="flight_infant_cost" id="flight_infant_cost" value="{{ old('flight_infant_cost', $booking->flight_infant_cost ?? 0) }}">
                                    <input type="hidden" name="flight_infant_sell" id="flight_infant_sell" value="{{ old('flight_infant_sell', $booking->flight_infant_sell ?? 0) }}">
                                    <span id="flight_adult_pax_label" style="display:none;"></span>
                                    <span id="flight_child_pax_label" style="display:none;"></span>
                                    <span id="flight_infant_pax_label" style="display:none;"></span>
                                    <span id="flight_adult_cost_sar_disp" style="display:none;"></span>
                                    <span id="flight_adult_sell_sar_disp" style="display:none;"></span>
                                    <span id="flight_child_cost_sar_disp" style="display:none;"></span>
                                    <span id="flight_child_sell_sar_disp" style="display:none;"></span>
                                    <span id="flight_infant_cost_sar_disp" style="display:none;"></span>
                                    <span id="flight_infant_sell_sar_disp" style="display:none;"></span>

                                    {{-- Individual Passenger Air Ticket Rates & Information --}}
                                    <div id="flightPersonsList"></div>

                                    {{-- Flight Dual Currency Totals Display --}}
                                    <div class="row g-2 p-2 rounded bg-light border">
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Cost (<span class="flight-curr-label">{{ $flightCurr }}</span>)</small>
                                                <strong class="text-danger fs-14" id="flight_cost_sar_total">0.00 {{ $flightCurr }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Cost (PKR)</small>
                                                <strong class="text-danger fs-14" id="flight_cost_pkr_total">{{ number_format($booking->flight_cost ?? 0, 2) }} PKR</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Sell (<span class="flight-curr-label">{{ $flightCurr }}</span>)</small>
                                                <strong class="text-success fs-14" id="flight_sell_sar_total">0.00 {{ $flightCurr }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Sell (PKR)</small>
                                                <strong class="text-success fs-14" id="flight_sell_pkr_total">{{ number_format($booking->flight_charges ?? 0, 2) }} PKR</strong>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hidden fields for Controller compatibility --}}
                                    <input type="hidden" name="flight_cost_rate" id="flight_cost_rate" value="{{ $booking->flight_cost_rate ?? 0 }}">
                                    <input type="hidden" name="flight_cost" id="flight_cost" value="{{ $booking->flight_cost ?? 0 }}">
                                    <input type="hidden" name="flight_selling_rate" id="flight_selling_rate" value="{{ $booking->flight_selling_rate ?? 0 }}">
                                    <input type="hidden" name="flight_charges" id="flight_charges" value="{{ $booking->flight_charges ?? 0 }}">
                                    <input type="hidden" name="flight_charges_disp" id="flight_charges_disp" value="{{ $booking->flight_charges ?? 0 }}">
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
                                    <input type="date" name="departure_date" class="form-control"
                                        value="{{ old('departure_date', $booking->departure_date) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Departure Flight #</label>
                                    <input type="text" name="departure_flight" class="form-control"
                                        value="{{ old('departure_flight', $booking->departure_flight) }}"
                                        placeholder="PK-301">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time of Departure</label>
                                    <input type="time" name="departure_time" class="form-control"
                                        value="{{ old('departure_time', $booking->departure_time) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Airline</label>
                                    <select name="departure_airline" class="form-select">
                                        <option value="">-- Select Airline --</option>
                                        @php
                                            $selectedDepAir = old('departure_airline', $booking->departure_airline ?? '');
                                            $foundDep = false;
                                        @endphp
                                        @if(isset($airlines))
                                            @foreach ($airlines as $air)
                                                @php
                                                    $isSel = ($selectedDepAir == $air->name || $selectedDepAir == $air->code || $selectedDepAir == $air->iata_code || (!empty($selectedDepAir) && stripos($air->name, $selectedDepAir) !== false));
                                                    if ($isSel) $foundDep = true;
                                                @endphp
                                                <option value="{{ $air->name }}" {{ $isSel ? 'selected' : '' }}>
                                                    {{ $air->name }}{{ $air->iata_code ? ' (' . $air->iata_code . ')' : '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                        @if (!empty($selectedDepAir) && !$foundDep)
                                            <option value="{{ $selectedDepAir }}" selected>{{ $selectedDepAir }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Departure PNR / Ticket #</label>
                                    <input type="text" name="departure_pnr" class="form-control"
                                        value="{{ old('departure_pnr', $booking->departure_pnr) }}" placeholder="ABC123">
                                </div>
                            </div>

                            {{-- ARRIVAL --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted border-bottom pb-2">
                                        <i class="mdi mdi-airplane-landing me-1"></i> Arrival Info
                                    </h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Arrival</label>
                                    <input type="date" name="arrival_date" class="form-control"
                                        value="{{ old('arrival_date', $booking->arrival_date) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Arrival Flight #</label>
                                    <input type="text" name="arrival_flight" class="form-control"
                                        value="{{ old('arrival_flight', $booking->arrival_flight) }}"
                                        placeholder="PK-302">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time of Arrival</label>
                                    <input type="time" name="arrival_time" class="form-control"
                                        value="{{ old('arrival_time', $booking->arrival_time) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Airline</label>
                                    <select name="arrival_airline" class="form-select">
                                        <option value="">-- Select Airline --</option>
                                        @php
                                            $selectedArrAir = old('arrival_airline', $booking->arrival_airline ?? '');
                                            $foundArr = false;
                                        @endphp
                                        @if(isset($airlines))
                                            @foreach ($airlines as $air)
                                                @php
                                                    $isSel = ($selectedArrAir == $air->name || $selectedArrAir == $air->code || $selectedArrAir == $air->iata_code || (!empty($selectedArrAir) && stripos($air->name, $selectedArrAir) !== false));
                                                    if ($isSel) $foundArr = true;
                                                @endphp
                                                <option value="{{ $air->name }}" {{ $isSel ? 'selected' : '' }}>
                                                    {{ $air->name }}{{ $air->iata_code ? ' (' . $air->iata_code . ')' : '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                        @if (!empty($selectedArrAir) && !$foundArr)
                                            <option value="{{ $selectedArrAir }}" selected>{{ $selectedArrAir }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Arrival PNR / Ticket #</label>
                                    <input type="text" name="arrival_pnr" class="form-control"
                                        value="{{ old('arrival_pnr', $booking->arrival_pnr) }}" placeholder="XYZ456">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Upload Air Ticket / Flight Document</label>
                                    @if($booking->flight_attachment)
                                        <div class="mb-1"><a href="{{ asset('storage/' . $booking->flight_attachment) }}" target="_blank" class="badge bg-primary text-white">View Current Ticket</a></div>
                                    @endif
                                    <input type="file" name="flight_attachment" class="form-control" accept="image/*,.pdf">
                                </div>
                            </div>


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
                            <div id="noHotelAlert" class="alert alert-light border py-4 text-center text-muted mb-3" style="{{ $booking->hotels && $booking->hotels->count() > 0 ? 'display:none;' : '' }}">
                                <i class="mdi mdi-hotel fs-36 text-primary d-block mb-1"></i>
                                <strong>No hotels added yet.</strong>
                                <p class="small text-muted mb-0">Click one of the buttons below to add a hotel for Makkah, Madinah or Other city.</p>
                            </div>
                            <div id="hotelsList">
                                @foreach ($booking->hotels as $hotel)
                                    @php
                                        $hCurr = old('hotels.'.$loop->index.'.currency', $hotel->currency ?? 'SAR');
                                        $hCostRoe = old('hotels.'.$loop->index.'.cost_roe', $hotel->cost_roe ?? $hotel->roe ?? 75);
                                        $hSaleRoe = old('hotels.'.$loop->index.'.sale_roe', $hotel->sale_roe ?? $hotel->roe ?? 75);
                                        $isHPkr = ($hCurr === 'PKR');
                                    @endphp
                                    <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="{{ $loop->index }}" data-person-rates='@json($hotel->person_rates ?? [])'>
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                            <h6 class="text-primary mb-0 fw-bold">
                                                <i class="mdi mdi-hotel me-1"></i>
                                                {{ $hotel->location === 'makkah' ? 'Makkah Hotel' : ($hotel->location === 'madinah' ? 'Madinah Hotel' : 'Other Hotel') }}
                                            </h6>
                                            @if ($loop->index >= 2)
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm remove-hotel">× Remove</button>
                                            @endif
                                        </div>
                                        <input type="hidden" name="hotels[{{ $loop->index }}][location]"
                                            value="{{ $hotel->location }}">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Hotel Name</label>
                                                <select name="hotels[{{ $loop->index }}][hotel_name]" class="form-select hotel-name-select">
                                                    <option value="">Select Hotel</option>
                                                    @foreach($hotels as $h)
                                                        <option value="{{ $h->name }}" {{ old('hotels.'.$loop->index.'.hotel_name', $hotel->hotel_name) == $h->name ? 'selected' : '' }}>{{ $h->name }} ({{ $h->city ?? $h->place }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Hotel Supplier / Vendor</label>
                                                <select name="hotels[{{ $loop->index }}][supplier_id]" class="form-select">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $s)
                                                        <option value="{{ $s->id }}" {{ old('hotels.'.$loop->index.'.supplier_id', $hotel->supplier_id) == $s->id ? 'selected' : '' }}>
                                                            {{ $s->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-dark fw-bold">Currency</label>
                                                <select name="hotels[{{ $loop->index }}][currency]" class="form-select hotel-currency fw-bold">
                                                    <option value="SAR" {{ $hCurr === 'SAR' ? 'selected' : '' }}>SAR (Riyal)</option>
                                                    <option value="PKR" {{ $hCurr === 'PKR' ? 'selected' : '' }}>PKR (Rupee)</option>
                                                    <option value="USD" {{ $hCurr === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                                    <option value="MYR" {{ $hCurr === 'MYR' ? 'selected' : '' }}>MYR (Ringgit)</option>
                                                    <option value="AED" {{ $hCurr === 'AED' ? 'selected' : '' }}>AED (Dirham)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-bold">Cost ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light hotel-curr-prefix">1 {{ $hCurr }} =</span>
                                                    <input type="number" step="0.0001" name="hotels[{{ $loop->index }}][cost_roe]"
                                                        class="form-control hotel-cost-roe" value="{{ $hCostRoe }}" {{ $isHPkr ? 'readonly' : '' }}>
                                                </div>
                                                <input type="hidden" name="hotels[{{ $loop->index }}][roe]" class="hotel-roe" value="{{ $hCostRoe }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-bold">Sale ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light hotel-curr-prefix">1 {{ $hCurr }} =</span>
                                                    <input type="number" step="0.0001" name="hotels[{{ $loop->index }}][sale_roe]"
                                                        class="form-control hotel-sale-roe" value="{{ $hSaleRoe }}" {{ $isHPkr ? 'readonly' : '' }}>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Room Type</label>
                                                <select name="hotels[{{ $loop->index }}][room_type]"
                                                    class="form-select">
                                                    @foreach (['single' => 'Single', 'double' => 'Double', 'triple' => 'Triple', 'quad' => 'Quad', 'sharing' => 'Sharing', 'suite' => 'Suite'] as $rtKey => $rtLabel)
                                                        <option value="{{ $rtKey }}"
                                                            {{ old('hotels.'.$loop->index.'.room_type', $hotel->room_type) == $rtKey ? 'selected' : '' }}>
                                                            {{ $rtLabel }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Nights</label>
                                                <input type="number" name="hotels[{{ $loop->index }}][no_of_nights]"
                                                    class="form-control hotel-nights" value="{{ old('hotels.'.$loop->index.'.no_of_nights', $hotel->no_of_nights ?: 1) }}"
                                                    min="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">No. of Rooms</label>
                                                <input type="number" name="hotels[{{ $loop->index }}][no_of_rooms]"
                                                    class="form-control hotel-rooms" value="{{ old('hotels.'.$loop->index.'.no_of_rooms', $hotel->no_of_rooms ?: 1) }}"
                                                    min="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Hotel Room #</label>
                                                <input type="text" name="hotels[{{ $loop->index }}][room_number]"
                                                    class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505"
                                                    value="{{ old('hotels.'.$loop->index.'.room_number', $hotel->room_number) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Check In</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][check_in]"
                                                    class="form-control hotel-checkin" value="{{ old('hotels.'.$loop->index.'.check_in', $hotel->check_in) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Check Out</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][check_out]"
                                                    class="form-control hotel-checkout" value="{{ old('hotels.'.$loop->index.'.check_out', $hotel->check_out) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-warning fw-bold"><i class="mdi mdi-calendar-clock me-1 text-warning"></i> Option Date</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][option_date]"
                                                    class="form-control hotel-option-date border-warning" value="{{ old('hotels.'.$loop->index.'.option_date', $hotel->option_date ? \Carbon\Carbon::parse($hotel->option_date)->format('Y-m-d') : '') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][booking_date]"
                                                    class="form-control hotel-booking-date" value="{{ old('hotels.'.$loop->index.'.booking_date', $hotel->booking_date ? \Carbon\Carbon::parse($hotel->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][cost_per_night]"
                                                    class="form-control hotel-cost-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_per_night', $hotel->cost_per_night ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Total Cost (<span class="hotel-curr-label">{{ $hCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-danger"><span class="hotel-curr-name">{{ $hCurr }}:</span><strong class="hotel-cost-sar-disp">{{ number_format(($hotel->cost_per_night ?? 0) * ($hotel->no_of_nights ?? 1) * ($hotel->no_of_rooms ?? 1), 2) }} {{ $hCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="hotel-cost-pkr-disp">{{ number_format($hotel->cost_amount ?? 0, 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="hotels[{{ $loop->index }}][cost_amount]"
                                                    class="hotel-cost-total"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_amount', $hotel->cost_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][selling_per_night]"
                                                    class="form-control hotel-sell-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.selling_per_night', $hotel->selling_per_night ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Total Sell (<span class="hotel-curr-label">{{ $hCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-success"><span class="hotel-curr-name">{{ $hCurr }}:</span><strong class="hotel-sell-sar-disp">{{ number_format(($hotel->selling_per_night ?? 0) * ($hotel->no_of_nights ?? 1) * ($hotel->no_of_rooms ?? 1), 2) }} {{ $hCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="hotel-sell-pkr-disp">{{ number_format($hotel->selling_amount ?? 0, 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="hotels[{{ $loop->index }}][selling_amount]"
                                                    class="hotel-sell-total"
                                                    value="{{ old('hotels.'.$loop->index.'.selling_amount', $hotel->selling_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                                                @if($hotel->hotel_voucher)
                                                    <div class="mb-1"><a href="{{ asset('storage/' . $hotel->hotel_voucher) }}" target="_blank" class="badge bg-primary text-white">View Current Voucher</a></div>
                                                @endif
                                                <input type="hidden" name="hotels[{{ $loop->index }}][old_hotel_voucher]" value="{{ $hotel->hotel_voucher }}">
                                                <input type="file" name="hotels[{{ $loop->index }}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
                                            </div>

                                            {{-- Individual Passenger Hotel Rates Table --}}
                                            <div class="col-12 hotel-persons-wrapper mt-3 pt-3 border-top">
                                                <div class="p-2 px-3 mb-2 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                    <div>
                                                        <span class="fw-bold text-dark fs-12"><i class="mdi mdi-account-multiple me-1 text-primary"></i> Individual Passenger Hotel Rates:</span>
                                                        <small class="text-muted d-block" style="font-size:11px;">Enter custom rates per night for each passenger independently below (e.g. Person 1 and Person 2 can have different rates).</small>
                                                    </div>
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        <div class="input-group input-group-sm" style="width: auto;">
                                                            <span class="input-group-text bg-white fs-11">Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>):</span>
                                                            <input type="number" step="0.01" class="form-control form-control-sm text-end quick-hotel-cost" placeholder="Cost" style="width: 80px;">
                                                            <input type="number" step="0.01" class="form-control form-control-sm text-end quick-hotel-sell" placeholder="Sell" style="width: 80px;">
                                                            <button type="button" class="btn btn-primary btn-sm btn-quick-hotel-apply"><i class="mdi mdi-check me-1"></i> Apply to All Pax</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="table-responsive border rounded bg-white shadow-sm mb-2">
                                                    <table class="table table-sm table-hover align-middle mb-0 hotel-persons-table">
                                                        <thead class="table-light fs-12">
                                                            <tr>
                                                                <th style="width: 35px;" class="text-center">#</th>
                                                                <th style="width: 25%;">Passenger Name</th>
                                                                <th style="width: 12%;" class="text-center">Nights</th>
                                                                <th style="width: 25%;" class="text-danger text-end">Cost / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</th>
                                                                <th style="width: 25%;" class="text-success text-end">Sell / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="hotel-persons-tbody">
                                                            {{-- Synced automatically via JS --}}
                                                        </tbody>
                                                        <tfoot class="table-light fw-bold fs-12 border-top">
                                                            <tr>
                                                                <td colspan="3" class="text-dark">Hotel Passenger Totals:</td>
                                                                <td class="text-end text-danger">
                                                                    <span class="hotel-person-tbl-cost-curr">0.00 {{ $hCurr }}</span>
                                                                    <br><small class="text-danger fw-normal hotel-person-tbl-cost-pkr">0.00 PKR</small>
                                                                </td>
                                                                <td class="text-end text-success">
                                                                    <span class="hotel-person-tbl-sell-curr">0.00 {{ $hCurr }}</span>
                                                                    <br><small class="text-success fw-normal hotel-person-tbl-sell-pkr">0.00 PKR</small>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
                                @forelse($booking->transports as $i => $t)
                                    @php
                                        $tCurr = old('transports.'.$i.'.currency', $t->currency ?? 'SAR');
                                        $tCostRoe = old('transports.'.$i.'.cost_roe', $t->cost_roe ?? $t->roe ?? 75);
                                        $tSaleRoe = old('transports.'.$i.'.sale_roe', $t->sale_roe ?? $t->roe ?? 75);
                                        $isTPkr = ($tCurr === 'PKR');
                                    @endphp
                                    <div class="route-block border rounded p-3 mb-2 bg-white shadow-sm">
                                        @if ($loop->index > 0)
                                            <div class="d-flex justify-content-end mb-1">
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm remove-route">×</button>
                                            </div>
                                        @endif
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Route</label>
                                                <select name="transports[{{ $i }}][route]" class="form-select transport-route-select">
                                                    <option value="">-- Select Route --</option>
                                                    @php
                                                        $selectedRoute = old('transports.'.$i.'.route', $t->route ?? '');
                                                        $foundRoute = false;
                                                    @endphp
                                                    @if(isset($routes))
                                                        @foreach ($routes as $r)
                                                            @php
                                                                $rVal = $r->formatted_route;
                                                                $isSel = ($selectedRoute == $rVal || $selectedRoute == ($r->start_place . ' → ' . $r->end_place) || $selectedRoute == ($r->start_place . ' to ' . $r->end_place));
                                                                if ($isSel) $foundRoute = true;
                                                            @endphp
                                                            <option value="{{ $rVal }}" {{ $isSel ? 'selected' : '' }}>
                                                                {{ $r->start_place }} → {{ $r->end_place }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                    @if (!empty($selectedRoute) && !$foundRoute)
                                                        <option value="{{ $selectedRoute }}" selected>{{ $selectedRoute }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Vehicle</label>
                                                @php
                                                    $selectedVeh = old('transports.'.$i.'.vehicle', $t->vehicle ?? '');
                                                    $foundVeh = false;
                                                @endphp
                                                <select name="transports[{{ $i }}][vehicle]" class="form-select transport-vehicle-select">
                                                    <option value="">-- Select Vehicle --</option>
                                                    @if(isset($vehicles))
                                                        @foreach ($vehicles as $veh)
                                                            @php
                                                                $vehLabel = $veh->vehicle_type . ($veh->brand_name ? ' (' . $veh->brand_name . ')' : '');
                                                                $vehVal = $veh->brand_name ?: $veh->vehicle_type;
                                                                $isSel = ($selectedVeh == $vehVal || $selectedVeh == $veh->vehicle_type || $selectedVeh == $vehLabel);
                                                                if ($isSel) $foundVeh = true;
                                                            @endphp
                                                            <option value="{{ $vehVal }}" {{ $isSel ? 'selected' : '' }}>
                                                                {{ $vehLabel }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                    @if(!empty($selectedVeh) && !$foundVeh)
                                                        <option value="{{ $selectedVeh }}" selected>{{ $selectedVeh }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Qty</label>
                                                <input type="number" name="transports[{{ $i }}][quantity]" class="form-control"
                                                    value="{{ old('transports.'.$i.'.quantity', $t->quantity ?? 1) }}" min="1">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Type / Status</label>
                                                <select name="transports[{{ $i }}][status]" class="form-select">
                                                    @foreach (['CONFIRMED', 'PENDING', 'CANCELLED'] as $st)
                                                        <option value="{{ $st }}" {{ old('transports.'.$i.'.status', $t->status ?? 'CONFIRMED') == $st ? 'selected' : '' }}>
                                                            {{ $st }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Pax</label>
                                                <input type="number" name="transports[{{ $i }}][pax]" class="form-control"
                                                    value="{{ old('transports.'.$i.'.pax', $t->pax ?? ($booking->total_passengers ?? 1)) }}" min="1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Transport Supplier / Vendor</label>
                                                <select name="transports[{{ $i }}][supplier_id]" class="form-select">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $sup)
                                                        <option value="{{ $sup->id }}" {{ old('transports.'.$i.'.supplier_id', $t->supplier_id) == $sup->id ? 'selected' : '' }}>
                                                            {{ $sup->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Row 2: Date, Pickup Time, Flight #, Pickup From, Destination, BRN --}}
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Date</label>
                                                <input type="date" name="transports[{{ $i }}][travel_date]"
                                                    class="form-control" value="{{ old('transports.'.$i.'.travel_date', $t->travel_date ? \Carbon\Carbon::parse($t->travel_date)->format('Y-m-d') : '') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-clock-outline me-1 text-primary"></i> Pickup Time</label>
                                                <input type="text" name="transports[{{ $i }}][travel_time]"
                                                    class="form-control" placeholder="e.g. 0045 / 1200" value="{{ old('transports.'.$i.'.travel_time', $t->travel_time) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-airplane me-1 text-info"></i> Flight #</label>
                                                <input type="text" name="transports[{{ $i }}][flight_no]"
                                                    class="form-control" placeholder="e.g. PK831"
                                                    value="{{ old('transports.'.$i.'.flight_no', $t->flight_no) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-map-marker-outline me-1 text-success"></i> Pickup From</label>
                                                <input type="text" name="transports[{{ $i }}][pickup_from]"
                                                    class="form-control transport-pickup-from" placeholder="e.g. JED APT"
                                                    value="{{ old('transports.'.$i.'.pickup_from', $t->pickup_from) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-map-marker-check me-1 text-danger"></i> Destination</label>
                                                <input type="text" name="transports[{{ $i }}][destination]"
                                                    class="form-control transport-destination" placeholder="e.g. MAK HTL"
                                                    value="{{ old('transports.'.$i.'.destination', $t->destination) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold text-dark"><i class="mdi mdi-barcode me-1 text-secondary"></i> BRN #</label>
                                                <input type="text" name="transports[{{ $i }}][brn]"
                                                    class="form-control" placeholder="BRN Number"
                                                    value="{{ old('transports.'.$i.'.brn', $t->brn) }}">
                                            </div>

                                            {{-- Row 3: Airline & Booking Date --}}
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="mdi mdi-airplane-takeoff me-1 text-info"></i> Airline</label>
                                                @php
                                                    $selectedAir = old('transports.'.$i.'.airline', $t->airline);
                                                    $foundAir = false;
                                                @endphp
                                                <select name="transports[{{ $i }}][airline]" class="form-select transport-airline-select">
                                                    <option value="">-- Select Airline (Optional) --</option>
                                                    @if(isset($airlines))
                                                        @foreach ($airlines as $air)
                                                            @php
                                                                $isSel = ($selectedAir == $air->name || $selectedAir == $air->iata_code || ($air->iata_code && $selectedAir == $air->name . ' (' . $air->iata_code . ')'));
                                                                if ($isSel) $foundAir = true;
                                                            @endphp
                                                            <option value="{{ $air->name }}" {{ $isSel ? 'selected' : '' }}>
                                                                {{ $air->name }}{{ $air->iata_code ? ' (' . $air->iata_code . ')' : '' }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                    @if($selectedAir && !$foundAir)
                                                        <option value="{{ $selectedAir }}" selected>{{ $selectedAir }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted"><i class="mdi mdi-calendar-check me-1"></i> Booking Date</label>
                                                <input type="date" name="transports[{{ $i }}][booking_date]"
                                                    class="form-control" value="{{ old('transports.'.$i.'.booking_date', $t->booking_date ? \Carbon\Carbon::parse($t->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-dark fw-bold">Currency</label>
                                                <select name="transports[{{ $i }}][currency]" class="form-select transport-currency fw-bold">
                                                    <option value="SAR" {{ $tCurr === 'SAR' ? 'selected' : '' }}>SAR (Riyal)</option>
                                                    <option value="PKR" {{ $tCurr === 'PKR' ? 'selected' : '' }}>PKR (Rupee)</option>
                                                    <option value="USD" {{ $tCurr === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                                    <option value="MYR" {{ $tCurr === 'MYR' ? 'selected' : '' }}>MYR (Ringgit)</option>
                                                    <option value="AED" {{ $tCurr === 'AED' ? 'selected' : '' }}>AED (Dirham)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-bold">Cost ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light transport-curr-prefix">1 {{ $tCurr }} =</span>
                                                    <input type="number" step="0.0001" name="transports[{{ $i }}][cost_roe]"
                                                        class="form-control transport-cost-roe" placeholder="75.00" value="{{ $tCostRoe }}" {{ $isTPkr ? 'readonly' : '' }}>
                                                </div>
                                                <input type="hidden" name="transports[{{ $i }}][roe]" class="transport-roe" value="{{ $tCostRoe }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-bold">Sale ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light transport-curr-prefix">1 {{ $tCurr }} =</span>
                                                    <input type="number" step="0.0001" name="transports[{{ $i }}][sale_roe]"
                                                        class="form-control transport-sale-roe" placeholder="75.00" value="{{ $tSaleRoe }}" {{ $isTPkr ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-semibold">Cost Rate (<span class="transport-curr-label">{{ $tCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][cost_rate]"
                                                    class="form-control transport-cost-rate" placeholder="0.00" value="{{ old('transports.'.$i.'.cost_rate', $t->cost_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Total Cost (<span class="transport-curr-label">{{ $tCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-danger"><span class="transport-curr-name">{{ $tCurr }}:</span><strong class="transport-cost-sar-disp">{{ number_format($t->cost_rate ?? 0, 2) }} {{ $tCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">{{ number_format($t->cost_amount ?? 0, 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="transports[{{ $i }}][cost_amount]"
                                                    class="transport-cost-amount" value="{{ old('transports.'.$i.'.cost_amount', $t->cost_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-semibold">Sell Rate (<span class="transport-curr-label">{{ $tCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][selling_rate]"
                                                    class="form-control transport-sell-rate" placeholder="0.00" value="{{ old('transports.'.$i.'.selling_rate', $t->selling_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Total Sell (<span class="transport-curr-label">{{ $tCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-success"><span class="transport-curr-name">{{ $tCurr }}:</span><strong class="transport-sell-sar-disp">{{ number_format($t->selling_rate ?? 0, 2) }} {{ $tCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="transport-sell-pkr-disp">{{ number_format($t->selling_amount ?? 0, 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="transports[{{ $i }}][selling_amount]"
                                                    class="transport-sell-amount" value="{{ old('transports.'.$i.'.selling_amount', $t->selling_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Notes</label>
                                                <input type="text" name="transports[{{ $i }}][notes]"
                                                    class="form-control" value="{{ $t->notes }}" placeholder="Optional">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Transport Ticket / Receipt</label>
                                                @if($t->transport_ticket)
                                                    <div class="mb-1"><a href="{{ asset('storage/' . $t->transport_ticket) }}" target="_blank" class="badge bg-primary text-white">View Current Ticket</a></div>
                                                @endif
                                                <input type="hidden" name="transports[{{ $i }}][old_transport_ticket]" value="{{ $t->transport_ticket }}">
                                                <input type="file" name="transports[{{ $i }}][transport_ticket]" class="form-control" accept="image/*,.pdf">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="route-block border rounded p-3 mb-2 bg-white shadow-sm">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Route</label>
                                                <select name="transports[0][route]" class="form-select transport-route-select">
                                                    <option value="">-- Select Route --</option>
                                                    @if(isset($routes))
                                                        @foreach ($routes as $r)
                                                            <option value="{{ $r->formatted_route }}" {{ old('transports.0.route') == $r->formatted_route ? 'selected' : '' }}>
                                                                {{ $r->start_place }} → {{ $r->end_place }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Vehicle</label>
                                                <select name="transports[0][vehicle]" class="form-select transport-vehicle-select">
                                                    <option value="">-- Select Vehicle --</option>
                                                    @if(isset($vehicles))
                                                        @foreach ($vehicles as $v)
                                                            <option value="{{ $v->brand_name ?? $v->vehicle_type }}" {{ old('transports.0.vehicle') == ($v->brand_name ?? $v->vehicle_type) ? 'selected' : '' }}>
                                                                {{ $v->vehicle_type }}{{ $v->brand_name ? ' (' . $v->brand_name . ')' : '' }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Qty</label>
                                                <input type="number" name="transports[0][quantity]" class="form-control"
                                                    value="{{ old('transports.0.quantity', 1) }}" min="1">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Type / Status</label>
                                                <select name="transports[0][status]" class="form-select">
                                                    @foreach (['CONFIRMED', 'PENDING', 'CANCELLED'] as $st)
                                                        <option value="{{ $st }}" {{ old('transports.0.status', 'CONFIRMED') == $st ? 'selected' : '' }}>
                                                            {{ $st }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Pax</label>
                                                <input type="number" name="transports[0][pax]" class="form-control"
                                                    value="{{ old('transports.0.pax', $booking->total_passengers ?? 1) }}" min="1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Transport Supplier / Vendor</label>
                                                <select name="transports[0][supplier_id]" class="form-select">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $sup)
                                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Row 2: Date, Pickup Time, Flight #, Pickup From, Destination, BRN --}}
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Date</label>
                                                <input type="date" name="transports[0][travel_date]" class="form-control" value="{{ old('transports.0.travel_date') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-clock-outline me-1 text-primary"></i> Pickup Time</label>
                                                <input type="text" name="transports[0][travel_time]" class="form-control" placeholder="e.g. 0045 / 1200" value="{{ old('transports.0.travel_time') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-airplane me-1 text-info"></i> Flight #</label>
                                                <input type="text" name="transports[0][flight_no]" class="form-control" placeholder="e.g. PK831" value="{{ old('transports.0.flight_no') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-map-marker-outline me-1 text-success"></i> Pickup From</label>
                                                <input type="text" name="transports[0][pickup_from]" class="form-control transport-pickup-from" placeholder="e.g. JED APT" value="{{ old('transports.0.pickup_from') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label"><i class="mdi mdi-map-marker-check me-1 text-danger"></i> Destination</label>
                                                <input type="text" name="transports[0][destination]" class="form-control transport-destination" placeholder="e.g. MAK HTL" value="{{ old('transports.0.destination') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold text-dark"><i class="mdi mdi-barcode me-1 text-secondary"></i> BRN #</label>
                                                <input type="text" name="transports[0][brn]" class="form-control" placeholder="BRN Number" value="{{ old('transports.0.brn') }}">
                                            </div>

                                            {{-- Row 3: Airline & Booking Date --}}
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="mdi mdi-airplane-takeoff me-1 text-info"></i> Airline</label>
                                                <select name="transports[0][airline]" class="form-select transport-airline-select">
                                                    <option value="">-- Select Airline (Optional) --</option>
                                                    @if(isset($airlines))
                                                        @foreach ($airlines as $air)
                                                            <option value="{{ $air->name }}" {{ old('transports.0.airline') == $air->name ? 'selected' : '' }}>
                                                                {{ $air->name }}{{ $air->iata_code ? ' (' . $air->iata_code . ')' : '' }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted"><i class="mdi mdi-calendar-check me-1"></i> Booking Date</label>
                                                <input type="date" name="transports[0][booking_date]" class="form-control" value="{{ old('transports.0.booking_date', date('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-dark fw-bold">Currency</label>
                                                <select name="transports[0][currency]" class="form-select transport-currency fw-bold">
                                                    <option value="SAR" selected>SAR (Riyal)</option>
                                                    <option value="PKR">PKR (Rupee)</option>
                                                    <option value="USD">USD (Dollar)</option>
                                                    <option value="MYR">MYR (Ringgit)</option>
                                                    <option value="AED">AED (Dirham)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-bold">Cost ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light transport-curr-prefix">1 SAR =</span>
                                                    <input type="number" step="0.0001" name="transports[0][cost_roe]"
                                                        class="form-control transport-cost-roe" placeholder="75.00" value="75">
                                                </div>
                                                <input type="hidden" name="transports[0][roe]" class="transport-roe" value="75">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-bold">Sale ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light transport-curr-prefix">1 SAR =</span>
                                                    <input type="number" step="0.0001" name="transports[0][sale_roe]"
                                                        class="form-control transport-sale-roe" placeholder="75.00" value="75">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-semibold">Cost Rate (<span class="transport-curr-label">SAR</span>)</label>
                                                <input type="number" step="0.01" name="transports[0][cost_rate]"
                                                    class="form-control transport-cost-rate" placeholder="0.00" value="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Total Cost (<span class="transport-curr-label">SAR</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-danger"><span class="transport-curr-name">SAR:</span><strong class="transport-cost-sar-disp">0.00 SAR</strong></div>
                                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">0.00 PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="transports[0][cost_amount]"
                                                    class="transport-cost-amount" value="0">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-semibold">Sell Rate (<span class="transport-curr-label">SAR</span>)</label>
                                                <input type="number" step="0.01" name="transports[0][selling_rate]"
                                                    class="form-control transport-sell-rate" placeholder="0.00" value="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Total Sell (<span class="transport-curr-label">SAR</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-success"><span class="transport-curr-name">SAR:</span><strong class="transport-sell-sar-disp">0.00 SAR</strong></div>
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
                                                <input type="file" name="transports[0][transport_ticket]" class="form-control" accept="image/*,.pdf">
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
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
                                Edit visa details for each passenger below.
                            </p>
                            <div id="visasList">
                                @foreach ($booking->visas as $i => $visa)
                                    @php
                                        $vCurr = old('visas.'.$i.'.currency', $visa->currency ?? 'SAR');
                                        $vCostRoe = old('visas.'.$i.'.cost_roe', $visa->cost_roe ?? $visa->roe ?? 75);
                                        $vSaleRoe = old('visas.'.$i.'.sale_roe', $visa->sale_roe ?? $visa->roe ?? 75);
                                        $isVPkr = ($vCurr === 'PKR');
                                    @endphp
                                    <div class="visa-card border rounded p-3 mb-2 bg-white shadow-sm" id="visa_card_{{ $i }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-warning" style="font-size:13px;">
                                                <i class="mdi mdi-passport me-1"></i> Visa {{ $i + 1 }}: {{ $visa->given_name ?? 'Passenger ' . ($i + 1) }}
                                            </strong>
                                            <span class="badge bg-warning text-dark">{{ strtoupper(old('visas.'.$i.'.status', $visa->status ?? 'pending')) }}</span>
                                        </div>
                                        <div class="row g-2 mt-1">
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Passport Number</label>
                                                <input type="text" name="visas[{{ $i }}][passport_number]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old('visas.'.$i.'.passport_number', $visa->passport_number) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Full Name</label>
                                                <input type="text" name="visas[{{ $i }}][given_name]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old('visas.'.$i.'.given_name', $visa->given_name) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Date of Birth</label>
                                                <input type="date" name="visas[{{ $i }}][date_of_birth]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old('visas.'.$i.'.date_of_birth', $visa->date_of_birth) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Visa Supplier / Vendor</label>
                                                <select name="visas[{{ $i }}][supplier_id]" class="form-select form-select-sm">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $s)
                                                        <option value="{{ $s->id }}" {{ old('visas.'.$i.'.supplier_id', $visa->supplier_id) == $s->id ? 'selected' : '' }}>
                                                            {{ $s->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Company</label>
                                                <input type="text" name="visas[{{ $i }}][company]"
                                                    class="form-control form-control-sm" value="{{ old('visas.'.$i.'.company', $visa->company) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Send To</label>
                                                <select name="visas[{{ $i }}][send_to]"
                                                    class="form-select form-select-sm">
                                                    <option value="">-- Select --</option>
                                                    @foreach (['shirka' => 'Shirka', 'consulate' => 'Consulate', 'both' => 'Both'] as $val => $lbl)
                                                        <option value="{{ $val }}"
                                                            {{ old('visas.'.$i.'.send_to', $visa->send_to) == $val ? 'selected' : '' }}>
                                                            {{ $lbl }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-dark fw-bold" style="font-size:12px;">Currency</label>
                                                <select name="visas[{{ $i }}][currency]" class="form-select form-select-sm visa-currency fw-bold">
                                                    <option value="SAR" {{ $vCurr === 'SAR' ? 'selected' : '' }}>SAR (Riyal)</option>
                                                    <option value="PKR" {{ $vCurr === 'PKR' ? 'selected' : '' }}>PKR (Rupee)</option>
                                                    <option value="USD" {{ $vCurr === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                                    <option value="MYR" {{ $vCurr === 'MYR' ? 'selected' : '' }}>MYR (Ringgit)</option>
                                                    <option value="AED" {{ $vCurr === 'AED' ? 'selected' : '' }}>AED (Dirham)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-bold" style="font-size:12px;">Cost ROE</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light visa-curr-prefix">1 {{ $vCurr }} =</span>
                                                    <input type="number" step="0.0001" name="visas[{{ $i }}][cost_roe]"
                                                        class="form-control form-control-sm visa-cost-roe" placeholder="75.00"
                                                        value="{{ $vCostRoe }}" {{ $isVPkr ? 'readonly' : '' }}>
                                                </div>
                                                <input type="hidden" name="visas[{{ $i }}][roe]" class="visa-roe" value="{{ $vCostRoe }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-bold" style="font-size:12px;">Sale ROE</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light visa-curr-prefix">1 {{ $vCurr }} =</span>
                                                    <input type="number" step="0.0001" name="visas[{{ $i }}][sale_roe]"
                                                        class="form-control form-control-sm visa-sale-roe" placeholder="75.00"
                                                        value="{{ $vSaleRoe }}" {{ $isVPkr ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-semibold" style="font-size:12px;">Cost Rate (<span class="visa-curr-label">{{ $vCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="visas[{{ $i }}][cost_rate]"
                                                    class="form-control form-control-sm visa-cost-rate" placeholder="0.00"
                                                    value="{{ old('visas.'.$i.'.cost_rate', $visa->cost_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold" style="font-size:12px;">Total Cost (<span class="visa-curr-label">{{ $vCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-danger"><span class="visa-curr-name">{{ $vCurr }}:</span><strong class="visa-cost-sar-disp">{{ number_format($visa->cost_rate ?? 0, 2) }} {{ $vCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="visa-cost-pkr-disp">{{ number_format($visa->cost_amount ?? (($visa->cost_rate ?? 0) * $vCostRoe), 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="visas[{{ $i }}][cost_amount]"
                                                    class="visa-cost-amount"
                                                    value="{{ old('visas.'.$i.'.cost_amount', $visa->cost_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-semibold" style="font-size:12px;">Sell Rate (<span class="visa-curr-label">{{ $vCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="visas[{{ $i }}][selling_rate]"
                                                    class="form-control form-control-sm visa-sell-rate" placeholder="0.00"
                                                    value="{{ old('visas.'.$i.'.selling_rate', $visa->selling_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold" style="font-size:12px;">Total Sell (<span class="visa-curr-label">{{ $vCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-success"><span class="visa-curr-name">{{ $vCurr }}:</span><strong class="visa-sell-sar-disp">{{ number_format($visa->selling_rate ?? 0, 2) }} {{ $vCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="visa-sell-pkr-disp">{{ number_format($visa->selling_amount ?? (($visa->selling_rate ?? 0) * $vSaleRoe), 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="visas[{{ $i }}][selling_amount]"
                                                    class="visa-sell-amount"
                                                    value="{{ old('visas.'.$i.'.selling_amount', $visa->selling_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                                                <input type="date" name="visas[{{ $i }}][booking_date]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old('visas.'.$i.'.booking_date', $visa->booking_date ? \Carbon\Carbon::parse($visa->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Status</label>
                                                <select name="visas[{{ $i }}][status]"
                                                    class="form-select form-select-sm">
                                                    @foreach (['pending' => 'Pending', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $lbl)
                                                        <option value="{{ $val }}"
                                                            {{ old('visas.'.$i.'.status', $visa->status) == $val ? 'selected' : '' }}>
                                                            {{ $lbl }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" style="font-size:12px;">Visa Attachment</label>
                                                @if($visa->visa_attachment)
                                                    <div class="mb-1"><a href="{{ asset('storage/' . $visa->visa_attachment) }}" target="_blank" class="badge bg-primary text-white">View Current Visa</a></div>
                                                @endif
                                                <input type="hidden" name="visas[{{ $i }}][old_visa_attachment]" value="{{ $visa->visa_attachment }}">
                                                <input type="file" name="visas[{{ $i }}][visa_attachment]" class="form-control form-control-sm" accept="image/*,.pdf">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
                                    <label class="form-label">Package Cost / Person </label>
                                    <input type="number" name="package_cost" id="package_cost"
                                        class="form-control calc"
                                        value="{{ old('package_cost', $booking->package_cost ?: '') }}"
                                        placeholder="Enter package cost" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hotel Charges </label>
                                    <input type="number" name="hotel_charges" id="hotel_charges"
                                        class="form-control calc bg-light"
                                        value="{{ old('hotel_charges', $booking->hotel_charges ?? 0) }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Visa Charges </label>
                                    <input type="number" name="visa_charges" id="visa_charges"
                                        class="form-control calc bg-light"
                                        value="{{ old('visa_charges', $booking->visa_charges ?: '') }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Flight Charges </label>
                                    <input type="number" name="flight_charges_disp" id="flight_charges_disp"
                                        class="form-control calc bg-light"
                                        value="{{ old('flight_charges', $booking->flight_charges ?: '') }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Transport Charges </label>
                                    <input type="number" name="transport_charges" id="transport_charges"
                                        class="form-control calc bg-light"
                                        value="{{ old('transport_charges', $booking->transport_charges ?? 0) }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Qurbani & Other Charges </label>
                                    <input type="number" name="other_charges" id="other_charges"
                                        class="form-control calc"
                                        value="{{ old('other_charges', $booking->other_charges ?: '') }}"
                                        placeholder="Enter other charges" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Discount </label>
                                    <input type="number" name="discount" id="discount"
                                        class="form-control calc"
                                        value="{{ old('discount', $booking->discount ?: '') }}"
                                        placeholder="Enter discount" step="0.01">
                                </div>

                                <div class="col-12">
                                    {{-- Dual Currency Summary Cards --}}
                                    @php
                                        $fCurr = $booking->flight_currency ?: 'SAR';
                                        $fSaleRoe = (float) ($booking->flight_sale_roe > 0 ? $booking->flight_sale_roe : 75);
                                        $bFlightForeign = (float) ($booking->flight_selling_rate > 0 ? $booking->flight_selling_rate : (($booking->flight_charges > 0 && $fSaleRoe > 0) ? ($booking->flight_charges / $fSaleRoe) : 0));

                                        $bHotelForeign = 0;
                                        $hotelCurrsFound = [];
                                        foreach ($booking->hotels as $bh) {
                                            $hCurr = $bh->currency ?: 'SAR';
                                            $hSaleRoe = (float) ($bh->sale_roe > 0 ? $bh->sale_roe : ($bh->cost_roe > 0 ? $bh->cost_roe : 75));
                                            $hSpn = (float) $bh->selling_per_night;
                                            $hRooms = max(1, (int) $bh->no_of_rooms);
                                            $hNights = max(1, (int) $bh->no_of_nights);
                                            if ($hSpn > 0) {
                                                $bHotelForeign += ($hSpn * $hRooms * $hNights);
                                            } elseif ((float)$bh->selling_amount > 0 && $hSaleRoe > 0) {
                                                $bHotelForeign += ((float)$bh->selling_amount / $hSaleRoe);
                                            }
                                            $hotelCurrsFound[] = $hCurr;
                                        }
                                        $hotelCurrTag = count(array_unique($hotelCurrsFound)) === 1 ? ($hotelCurrsFound[0] ?? 'SAR') : (count(array_unique($hotelCurrsFound)) > 1 ? 'Mix' : 'SAR');

                                        $bTransportForeign = 0;
                                        $transCurrsFound = [];
                                        foreach ($booking->transports as $bt) {
                                            $tCurr = $bt->currency ?: 'SAR';
                                            $tSaleRoe = (float) ($bt->sale_roe > 0 ? $bt->sale_roe : 75);
                                            if ((float)$bt->selling_rate > 0) {
                                                $bTransportForeign += (float)$bt->selling_rate;
                                            } elseif ((float)$bt->selling_amount > 0 && $tSaleRoe > 0) {
                                                $bTransportForeign += ((float)$bt->selling_amount / $tSaleRoe);
                                            }
                                            $transCurrsFound[] = $tCurr;
                                        }
                                        $transportCurrTag = count(array_unique($transCurrsFound)) === 1 ? ($transCurrsFound[0] ?? 'SAR') : (count(array_unique($transCurrsFound)) > 1 ? 'Mix' : 'SAR');

                                        $bVisaForeign = 0;
                                        $visaCurrsFound = [];
                                        foreach ($booking->visas as $bv) {
                                            $vCurr = $bv->currency ?: 'SAR';
                                            $vSaleRoe = (float) ($bv->sale_roe > 0 ? $bv->sale_roe : 75);
                                            if ((float)$bv->selling_rate > 0) {
                                                $bVisaForeign += (float)$bv->selling_rate;
                                            } elseif ((float)$bv->selling_amount > 0 && $vSaleRoe > 0) {
                                                $bVisaForeign += ((float)$bv->selling_amount / $vSaleRoe);
                                            }
                                            $visaCurrsFound[] = $vCurr;
                                        }
                                        $visaCurrTag = count(array_unique($visaCurrsFound)) === 1 ? ($visaCurrsFound[0] ?? 'SAR') : (count(array_unique($visaCurrsFound)) > 1 ? 'Mix' : 'SAR');

                                        $bTotalForeign = $bFlightForeign + $bHotelForeign + $bTransportForeign + $bVisaForeign;
                                        $activeCurrsFound = array_unique(array_filter(array_merge([$fCurr], $hotelCurrsFound, $transCurrsFound, $visaCurrsFound), fn($c) => $c !== 'PKR'));
                                        $mainForeignUnit = count($activeCurrsFound) === 1 ? reset($activeCurrsFound) : (count($activeCurrsFound) > 1 ? 'Mix' : 'SAR');
                                        $bBalanceForeign = ($bTotalForeign > 0 && (float)$booking->total_amount > 0) ? ($bTotalForeign * ((float)$booking->balance / (float)$booking->total_amount)) : 0;
                                    @endphp
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="card border-primary border-opacity-25 bg-light mb-0">
                                                <div class="card-body p-3">
                                                    <h6 class="text-primary fw-bold mb-2"><i class="mdi mdi-cash-multiple me-1"></i> Foreign Currency (<span id="cost_foreign_curr_title">{{ $mainForeignUnit }}</span>) Total</h6>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Flight:</span>
                                                        <strong id="cost_sar_flight">{{ number_format($bFlightForeign, 2) }} {{ $fCurr }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Hotel:</span>
                                                        <strong id="cost_sar_hotel">{{ number_format($bHotelForeign, 2) }} {{ $hotelCurrTag }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Transport:</span>
                                                        <strong id="cost_sar_transport">{{ number_format($bTransportForeign, 2) }} {{ $transportCurrTag }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Visa:</span>
                                                        <strong id="cost_sar_visa">{{ number_format($bVisaForeign, 2) }} {{ $visaCurrTag }}</strong>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between text-primary fs-15 fw-bold">
                                                        <span>Total Selling:</span>
                                                        <span id="cost_sar_total">{{ number_format($bTotalForeign, 2) }} {{ $mainForeignUnit }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-danger fs-14 fw-semibold">
                                                        <span>Balance Remaining:</span>
                                                        <span id="cost_sar_balance">{{ number_format($bBalanceForeign, 2) }} {{ $mainForeignUnit }}</span>
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
                                                        <strong id="cost_pkr_flight">{{ number_format($booking->flight_charges ?? 0, 2) }} PKR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Hotel PKR:</span>
                                                        <strong id="cost_pkr_hotel">{{ number_format($booking->hotel_charges ?? 0, 2) }} PKR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Transport PKR:</span>
                                                        <strong id="cost_pkr_transport">{{ number_format($booking->transport_charges ?? 0, 2) }} PKR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Visa PKR:</span>
                                                        <strong id="cost_pkr_visa">{{ number_format($booking->visa_charges ?? 0, 2) }} PKR</strong>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between text-success fs-15 fw-bold">
                                                        <span>Total Selling (PKR):</span>
                                                        <span id="cost_pkr_total">{{ number_format($booking->total_amount ?? 0, 2) }} PKR</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-danger fs-14 fw-semibold">
                                                        <span>Balance Remaining (PKR):</span>
                                                        <span id="cost_pkr_balance">{{ number_format($booking->balance ?? 0, 2) }} PKR</span>
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
                                                    <td><strong id="sum_pax">{{ $booking->no_of_pax }}</strong></td>
                                                    <td id="sum_pkg" style="{{ $isUmrah ? 'display:none;' : '' }}">
                                                        {{ number_format(($booking->package_cost ?? 0) * ($booking->no_of_pax ?? 0), 2) }}
                                                    </td>
                                                    <td id="sum_hotel">{{ number_format($booking->hotel_charges ?? 0, 2) }}</td>
                                                    <td id="sum_visa">{{ number_format($booking->visa_charges ?? 0, 2) }}</td>
                                                    <td id="sum_flight">{{ number_format($booking->flight_charges ?? 0, 2) }}</td>
                                                    <td id="sum_transport">{{ number_format($booking->transport_charges ?? 0, 2) }}</td>
                                                    <td id="sum_other">{{ number_format(($booking->other_charges ?? 0) + ($booking->additional_services_amount ?? 0), 2) }}</td>
                                                    <td id="sum_discount" class="text-danger">{{ number_format($booking->discount ?? 0, 2) }}</td>
                                                    <td class="text-success fw-bold" id="sum_total">
                                                        {{ number_format($booking->total_amount ?? 0, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Total Amount </label>
                                    <input type="number" name="total_amount" id="total_amount"
                                        class="form-control bg-light fw-bold text-primary fs-16"
                                        value="{{ old('total_amount', $booking->total_amount ?: '') }}"
                                        placeholder="Auto calculated" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Received </label>
                                    <input type="number" name="total_received" id="total_received"
                                        class="form-control calc"
                                        value="{{ old('total_received', $booking->total_received ?: '') }}"
                                        placeholder="Enter amount received" step="0.01">
                                </div>

                                @php
                                    $bookingBalance = $booking->balance ?? 0;
                                    $bookingAbsBalance = abs($bookingBalance);
                                    $bookingBalanceSign = $bookingBalance < 0 ? '+' : '';
                                    $bookingBalanceText =
                                        $bookingBalance != 0
                                            ? $bookingBalanceSign . number_format($bookingAbsBalance, 0)
                                            : '';
                                @endphp

                                <div class="col-md-4">
                                    <label class="form-label">
                                        Balance Remaining 
                                        @if ($bookingBalance > 0)
                                            <span class="badge bg-danger ms-1" style="font-size:10px;">Due</span>
                                        @elseif ($bookingBalance < 0)
                                            <span class="badge bg-success ms-1" style="font-size:10px;">Advance</span>
                                        @elseif ($booking->balance !== null)
                                            <span class="badge bg-success ms-1" style="font-size:10px;">Clear</span>
                                        @endif
                                    </label>

                                    <input type="number" name="balance" id="balance"
                                        class="form-control bg-light text-danger fw-bold fs-16"
                                        value="{{ old('balance', $booking->balance ?: '') }}" placeholder="Auto calculated" readonly>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="mdi mdi-arrow-left me-1"></i> Prev
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-next">Next <i class="mdi mdi-arrow-right ms-1"></i></button>
                                    <button type="submit" class="btn btn-success px-5">
                                        <i class="mdi mdi-content-save me-1"></i> Update Booking
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 9: Additional Services --}}
                        <div class="tab-pane fade" id="tab-additional-services">
                            <div class="row g-3">
                              <div class="col-md-12">
    <label class="form-label">Service Details</label>

    <textarea name="additional_services_detail"
              id="additional_services_detail"
              class="form-control summernote">{{ old('additional_services_detail', trim(strip_tags($booking->additional_services_detail ?? '')) === '' ? '' : ($booking->additional_services_detail ?? '')) }}</textarea>
</div>

                                <div class="col-md-4">
                                    <label class="form-label">Service Amount</label>
                                    <input type="number" name="additional_services_amount" id="additional_services_amount" class="form-control calc" placeholder="Enter amount" step="0.01" value="{{ old('additional_services_amount', $booking->additional_services_amount ?? 0) }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="mdi mdi-arrow-left me-1"></i> Prev
                                </button>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('booking.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success px-5">
                                        <i class="mdi mdi-content-save me-1"></i> Update Booking
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>{{-- end tab-content --}}
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
          {{-- Summernote JS --}}
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <script>
        const packagesData     = @json($packages);
        const clientsData      = @json($clients);
        const hotelsData       = @json($hotels);
        const suppliersData    = @json($suppliers);
        const airlinesData     = @json($airlines ?? []);
        const routesData       = @json($routes ?? []);
        const vehiclesData     = @json($vehicles ?? []);
        const existingVisas    = @json($booking->visas);
        const existingPersons  = @json($booking->persons);
        const existingBooking  = @json($booking);
        const transactionsPaid = {{ $transactionsPaid ?? 0 }};

        const suppliersOptions = `<option value="">-- Select Supplier --</option>` +
            (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}">${s.name}</option>`).join('') : '');

        const hotelsOptions = `<option value="">Select Hotel</option>` +
            (typeof hotelsData !== 'undefined' ? hotelsData.map(h => `<option value="${h.name}">${h.name} (${h.city || h.place || ''})</option>`).join('') : '');

        function buildRoutesOptions() {
            return `<option value="">-- Select Route --</option>` +
                (Array.isArray(routesData) ? routesData.map(r => {
                    const val = (r.start_place && r.end_place) ? `${r.start_place} TO ${r.end_place}` : (r.formatted_route || '');
                    const txt = (r.start_place && r.end_place) ? `${r.start_place} → ${r.end_place}` : (r.formatted_route || '');
                    return `<option value="${val}">${txt}</option>`;
                }).join('') : '');
        }
        let routesOptions = buildRoutesOptions();

        function buildAirlinesOptions() {
            return `<option value="">-- Select Airline (Optional) --</option>` +
                (Array.isArray(airlinesData) ? airlinesData.map(a => `<option value="${a.name}">${a.name}${a.iata_code ? ' (' + a.iata_code + ')' : ''}</option>`).join('') : '');
        }
        let airlinesOptions = buildAirlinesOptions();

        function buildVehiclesOptions() {
            return `<option value="">-- Select Vehicle --</option>` +
                (Array.isArray(vehiclesData) ? vehiclesData.map(v => {
                    const val = v.brand_name || v.vehicle_type || '';
                    const label = v.vehicle_type + (v.brand_name ? ` (${v.brand_name})` : '');
                    return `<option value="${val}">${label}</option>`;
                }).join('') : '');
        }
        let vehiclesOptions  = buildVehiclesOptions();

        function updatePersonFullName(idx) {
            const sur   = document.getElementById(`person_surname_${idx}`)?.value || '';
            const given = document.getElementById(`person_given_name_${idx}`)?.value || '';
            const combined = (sur + ' ' + given).trim();
            const el = document.getElementById(`person_name_${idx}`);
            if (el) el.value = combined;

            // Sync with flight table passenger name
            const flNameDisp = document.querySelector(`#flight_person_row_${idx} .person-flight-name-disp`);
            const flNameInput = document.querySelector(`#flight_person_row_${idx} .person-flight-name`);
            if (flNameDisp && combined) flNameDisp.textContent = combined;
            if (flNameInput && combined) flNameInput.value = combined;

            // Sync with all hotel tables passenger names
            document.querySelectorAll(`.hotel-person-row[data-person-idx="${idx}"]`).forEach(pRow => {
                const hNameDisp = pRow.querySelector('.person-hotel-name-disp');
                const hNameInput = pRow.querySelector('.person-hotel-name');
                if (hNameDisp && combined) hNameDisp.textContent = combined;
                if (hNameInput && combined) hNameInput.value = combined;
            });
        }

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
        });

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

        const tabLinks = Array.from(document.querySelectorAll('#bookingTabs .nav-link'));

        function activateTab(idx) {
            if (idx < 0 || idx >= tabLinks.length) return;
            tabLinks[idx].click();
            const href = tabLinks[idx].getAttribute('href');
            if (href === '#tab-flight') syncFlightPersons();
            if (href === '#tab-visa') {
                const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                rebuildVisasList(pax);
            }
            if (href === '#tab-persons') {
                const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                const container = document.getElementById('personsList');
                if (container && container.children.length === 0) rebuildPersonsList(pax);
            }
            if (href === '#tab-costing') {
                calcTotal();
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.querySelectorAll('#bookingTabs .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const target = this.getAttribute('href');
                if (target === '#tab-flight') syncFlightPersons();
                if (target === '#tab-visa') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    rebuildVisasList(pax);
                }
                if (target === '#tab-persons') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    const container = document.getElementById('personsList');
                    if (container && container.children.length === 0) rebuildPersonsList(pax);
                }
                if (target === '#tab-costing') {
                    calcTotal();
                }
            });
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-next, .btn-prev');
            if (!btn) return;
            const cur = tabLinks.findIndex(t => t.classList.contains('active'));
            btn.classList.contains('btn-next') ? activateTab(cur + 1) : activateTab(cur - 1);
        });

        function rebuildPersonsList(newPax) {
            const container = document.getElementById('personsList');
            if (!container) return;
            const currentCount = container.querySelectorAll('.person-card').length;

            if (newPax < currentCount) {
                for (let i = currentCount - 1; i >= newPax; i--) {
                    document.getElementById('person_card_' + i)?.remove();
                }
                syncFlightPersons();
                syncAllHotelPersons();
                return;
            }

            for (let i = currentCount; i < newPax; i++) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="person-card" id="person_card_${i}">
                        <div class="mb-2"><strong class="text-primary" style="font-size:13px;">Passenger ${i + 1}</strong></div>
                        <div class="row g-2">
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Surname (Passport)</label>
                                <input type="text" name="persons[${i}][surname]" id="person_surname_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Given Name (Passport)</label>
                                <input type="text" name="persons[${i}][given_name]" id="person_given_name_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Full Name</label>
                                <input type="text" name="persons[${i}][full_name]" id="person_name_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Passport #</label>
                                <input type="text" name="persons[${i}][passport_number]" id="person_passport_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-4"><label class="form-label" style="font-size:12px;">DOB</label>
                                <input type="date" name="persons[${i}][dob]" class="form-control form-control-sm"></div>
                            <div class="col-md-4"><label class="form-label" style="font-size:12px;">Gender</label>
                                <select name="persons[${i}][gender]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option><option value="male">Male</option><option value="female">Female</option>
                                </select></div>
                            <div class="col-md-4"><label class="form-label" style="font-size:12px;">City</label>
                                <input type="text" name="persons[${i}][city]" class="form-control form-control-sm" placeholder="e.g. Lahore / Karachi"></div>
                        </div>

                        <div class="border-top mt-3 pt-3">
                            <strong class="text-secondary d-block mb-2" style="font-size:12px;">
                                <i class="mdi mdi-file-document-multiple me-1"></i> Documents (Optional)
                            </strong>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:11px;">Passport First Page</label>
                                    <input type="file" name="persons[${i}][passport_photo]" class="form-control form-control-sm" accept="image/*,.pdf">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:11px;">Picture of Passenger</label>
                                    <input type="file" name="persons[${i}][photo]" class="form-control form-control-sm" accept="image/*">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:11px;">Medical Fitness Certificate</label>
                                    <input type="file" name="persons[${i}][medical_certificate]" class="form-control form-control-sm" accept="image/*,.pdf">
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }
            syncFlightPersons();
            syncAllHotelPersons();
        }

        function syncFlightPersons() {
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const list = document.getElementById('flightPersonsList');
            if (!list) return;

            const curr = document.getElementById('flight_currency')?.value || 'SAR';
            const costRoe = (curr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_cost_roe')?.value) || parseFloat(document.getElementById('flight_roe')?.value) || 75);
            const saleRoe = (curr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_sale_roe')?.value) || costRoe || 75);

            // Preserve any existing DOM input values so user typing is not wiped out
            const existingRowData = {};
            list.querySelectorAll('.flight-person-row').forEach(row => {
                const idx = row.getAttribute('data-idx');
                if (idx !== null) {
                    existingRowData[idx] = {
                        pax_type: row.querySelector('.person-flight-pax-type')?.value,
                        ticket:   row.querySelector('.person-flight-ticket')?.value,
                        cost:     row.querySelector('.person-flight-cost')?.value,
                        sell:     row.querySelector('.person-flight-sell')?.value,
                    };
                }
            });

            let rowsHtml = '';
            for (let i = 0; i < pax; i++) {
                const pExisting = (typeof existingPersons !== 'undefined' && existingPersons && existingPersons[i]) ? existingPersons[i] : null;
                const personName = document.getElementById(`person_name_${i}`)?.value || (pExisting ? pExisting.full_name : '') || `Passenger ${i + 1}`;
                const personPass = document.getElementById(`person_passport_${i}`)?.value || (pExisting ? pExisting.passport_number : '') || '';

                const inDom = existingRowData[i];

                let pType = 'adult';
                let pTicket = '';
                let pCost = '';
                let pSell = '';

                if (inDom) {
                    pType   = inDom.pax_type || 'adult';
                    pTicket = inDom.ticket || '';
                    pCost   = inDom.cost !== undefined ? inDom.cost : '';
                    pSell   = inDom.sell !== undefined ? inDom.sell : '';
                } else if (pExisting) {
                    pType   = pExisting.pax_type || 'adult';
                    pTicket = pExisting.flight_ticket || '';
                    pCost   = (pExisting.flight_cost > 0) ? pExisting.flight_cost : '';
                    pSell   = (pExisting.flight_sell > 0) ? pExisting.flight_sell : '';
                }

                // Smart defaults for legacy bookings if cost/sell is still blank
                if (pCost === '' && typeof existingBooking !== 'undefined' && existingBooking) {
                    if (existingBooking.flight_adult_cost > 0) {
                        pCost = existingBooking.flight_adult_cost;
                    } else if (existingBooking.flight_cost_rate > 0) {
                        pCost = (pax > 1 ? (parseFloat(existingBooking.flight_cost_rate) / pax).toFixed(2) : existingBooking.flight_cost_rate);
                    } else if (existingBooking.flight_cost > 0 && costRoe > 0) {
                        pCost = (parseFloat(existingBooking.flight_cost) / (pax * costRoe)).toFixed(2);
                    }
                }
                if (pSell === '' && typeof existingBooking !== 'undefined' && existingBooking) {
                    if (existingBooking.flight_adult_sell > 0) {
                        pSell = existingBooking.flight_adult_sell;
                    } else if (existingBooking.flight_selling_rate > 0) {
                        pSell = (pax > 1 ? (parseFloat(existingBooking.flight_selling_rate) / pax).toFixed(2) : existingBooking.flight_selling_rate);
                    } else if (existingBooking.flight_charges > 0 && saleRoe > 0) {
                        pSell = (parseFloat(existingBooking.flight_charges) / (pax * saleRoe)).toFixed(2);
                    }
                }

                const costVal = parseFloat(pCost) || 0;
                const sellVal = parseFloat(pSell) || 0;

                rowsHtml += `
                <tr class="flight-person-row" data-idx="${i}" id="flight_person_row_${i}">
                    <td class="text-center text-muted fw-bold">${i + 1}</td>
                    <td>
                        <strong class="text-primary person-flight-name-disp">${personName}</strong>
                        <input type="hidden" name="flight_persons[${i}][name]" class="person-flight-name" value="${personName}">
                    </td>
                    <td>
                        <select name="flight_persons[${i}][pax_type]" class="form-select form-select-sm person-flight-pax-type">
                            <option value="adult" ${pType === 'adult' ? 'selected' : ''}>Adult</option>
                            <option value="child" ${pType === 'child' ? 'selected' : ''}>Child</option>
                            <option value="infant" ${pType === 'infant' ? 'selected' : ''}>Infant</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="flight_persons[${i}][passport]" class="form-control form-control-sm person-flight-passport" value="${personPass}" placeholder="Passport #">
                    </td>
                    <td>
                        <input type="text" name="flight_persons[${i}][ticket]" class="form-control form-control-sm person-flight-ticket" value="${pTicket}" placeholder="Seat / Ticket #">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="flight_persons[${i}][flight_cost]" class="form-control form-control-sm text-end person-flight-cost flight-rate-calc" value="${pCost}" placeholder="0.00">
                        <small class="text-danger d-block text-end person-flight-cost-pkr-disp" style="font-size:11px;">${(costVal * costRoe).toFixed(2)} PKR</small>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="flight_persons[${i}][flight_sell]" class="form-control form-control-sm text-end person-flight-sell flight-rate-calc" value="${pSell}" placeholder="0.00">
                        <small class="text-success d-block text-end person-flight-sell-pkr-disp" style="font-size:11px;">${(sellVal * saleRoe).toFixed(2)} PKR</small>
                    </td>
                </tr>`;
            }

            list.innerHTML = `
                <div class="p-2 px-3 mb-2 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <span class="fw-bold text-dark fs-12"><i class="mdi mdi-account-multiple-check me-1 text-primary"></i> Individual Passenger Air Ticket Rates:</span>
                        <small class="text-muted d-block" style="font-size:11px;">Enter custom rates for each passenger independently below (e.g. Person 1 and Person 2 can have different rates).</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-white fs-11">Adult Rate (<span class="flight-curr-label">${curr}</span>):</span>
                            <input type="number" step="0.01" id="quick_adult_cost" class="form-control form-control-sm text-end" placeholder="Cost" style="width: 80px;">
                            <input type="number" step="0.01" id="quick_adult_sell" class="form-control form-control-sm text-end" placeholder="Sell" style="width: 80px;">
                            <button type="button" class="btn btn-primary btn-sm" id="btnQuickApplyAdults"><i class="mdi mdi-check me-1"></i> Apply to All Adults</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive border rounded bg-white shadow-sm mb-3">
                    <table class="table table-sm table-hover align-middle mb-0" id="flightPersonsTable">
                        <thead class="table-light fs-12">
                            <tr>
                                <th style="width: 30px;" class="text-center">#</th>
                                <th style="width: 22%;">Passenger Name</th>
                                <th style="width: 110px;">Category</th>
                                <th style="width: 15%;">Passport #</th>
                                <th style="width: 15%;">Ticket / Seat #</th>
                                <th style="width: 15%;" class="text-danger text-end">Cost (<span class="flight-curr-label">${curr}</span>)</th>
                                <th style="width: 15%;" class="text-success text-end">Sell (<span class="flight-curr-label">${curr}</span>)</th>
                            </tr>
                        </thead>
                        <tbody id="flightPersonsTableBody">
                            ${rowsHtml}
                        </tbody>
                        <tfoot class="table-light fw-bold fs-12 border-top">
                            <tr>
                                <td colspan="4" class="text-dark">Total Flight Tickets (${pax} Passengers)</td>
                                <td class="text-end text-muted small">Total:</td>
                                <td class="text-end text-danger">
                                    <span id="tbl_flight_cost_curr_total">0.00 ${curr}</span>
                                    <br><small class="text-danger fw-normal" id="tbl_flight_cost_pkr_total">0.00 PKR</small>
                                </td>
                                <td class="text-end text-success">
                                    <span id="tbl_flight_sell_curr_total">0.00 ${curr}</span>
                                    <br><small class="text-success fw-normal" id="tbl_flight_sell_pkr_total">0.00 PKR</small>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;

            calculateFlight();
        }

        function syncPaxInputs() {
            const adults   = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants  = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const pax      = Math.max(1, adults + children + infants);

            if (document.getElementById('no_of_pax')) document.getElementById('no_of_pax').value = pax;
            if (document.getElementById('bar_pax_badge')) document.getElementById('bar_pax_badge').textContent = pax;

            if (document.getElementById('flight_adult_pax_label')) document.getElementById('flight_adult_pax_label').textContent = adults;
            if (document.getElementById('flight_child_pax_label')) document.getElementById('flight_child_pax_label').textContent = children;
            if (document.getElementById('flight_infant_pax_label')) document.getElementById('flight_infant_pax_label').textContent = infants;

            rebuildPersonsList(pax);
            rebuildVisasList(pax);
            calculateFlight();
            calcTotal();
        }

        document.querySelectorAll('.pax-breakdown-calc').forEach(el => {
            el.addEventListener('input', syncPaxInputs);
        });

        document.getElementById('no_of_pax')?.addEventListener('input', function() {
            const val = parseInt(this.value);
            if (val >= 1) {
                rebuildPersonsList(val);
                rebuildVisasList(val);
            }
            calculateFlight();
            calcTotal();
        });

        function rebuildVisasList(newPax) {
            const container = document.getElementById('visasList');
            if (!container) return;
            const currentCount = container.querySelectorAll('.visa-card').length;

            if (newPax < currentCount) {
                for (let i = currentCount - 1; i >= newPax; i--) {
                    document.getElementById('visa_card_' + i)?.remove();
                }
                calcTotal();
                return;
            }

            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);

            for (let i = currentCount; i < newPax; i++) {
                const v = (typeof existingVisas !== 'undefined' && existingVisas && existingVisas[i]) ? existingVisas[i] : {};
                const pName = document.getElementById(`person_name_${i}`)?.value || (v.given_name || '');
                const pPass = document.getElementById(`person_passport_${i}`)?.value || (v.passport_number || '');
                const pDob = document.getElementById(`person_dob_${i}`)?.value || (v.date_of_birth || '');

                const vCurr = v.currency || flightCurr;
                const vIsPkr = (vCurr === 'PKR');
                const costRoe = vIsPkr ? 1 : (v.cost_roe || v.roe || defaultCostRoe);
                const saleRoe = vIsPkr ? 1 : (v.sale_roe || costRoe || defaultSaleRoe);

                const costRate = parseFloat(v.cost_rate) || 0;
                const costAmt = parseFloat(v.cost_amount) || (costRate * parseFloat(costRoe));
                const sellRate = parseFloat(v.selling_rate) || 0;
                const sellAmt = parseFloat(v.selling_amount) || (sellRate * parseFloat(saleRoe));

                const vBookingDate = v.booking_date || '{{ date('Y-m-d') }}';

                let supOptions = `<option value="">-- Select Supplier --</option>` +
                    (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}" ${s.id == v.supplier_id ? 'selected' : ''}>${s.name}</option>`).join('') : '');

                container.insertAdjacentHTML('beforeend', `
                    <div class="visa-card border rounded p-3 mb-2 bg-white" id="visa_card_${i}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-warning" style="font-size:13px;">
                                <i class="mdi mdi-passport me-1"></i> Visa ${i + 1}: ${pName || 'Passenger ' + (i + 1)}
                            </strong>
                            <span class="badge bg-warning text-dark">${((v.status || 'pending')).toUpperCase()}</span>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Passport #</label>
                                <input type="text" name="visas[${i}][passport_number]" class="form-control form-control-sm" value="${pPass}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Full Name</label>
                                <input type="text" name="visas[${i}][given_name]" class="form-control form-control-sm" value="${pName}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">DOB</label>
                                <input type="date" name="visas[${i}][date_of_birth]" class="form-control form-control-sm" value="${pDob}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Visa Supplier / Vendor</label>
                                <select name="visas[${i}][supplier_id]" class="form-select form-select-sm">
                                    ${supOptions}
                                </select></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Company</label>
                                <input type="text" name="visas[${i}][company]" class="form-control form-control-sm" value="${v.company || ''}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Send To</label>
                                <select name="visas[${i}][send_to]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option>
                                    <option value="shirka" ${v.send_to === 'shirka' ? 'selected' : ''}>Shirka</option>
                                    <option value="consulate" ${v.send_to === 'consulate' ? 'selected' : ''}>Consulate</option>
                                    <option value="both" ${v.send_to === 'both' ? 'selected' : ''}>Both</option>
                                </select></div>
                            <div class="col-md-2">
                                <label class="form-label text-dark fw-bold" style="font-size:12px;">Currency</label>
                                <select name="visas[${i}][currency]" class="form-select form-select-sm visa-currency fw-bold">
                                    <option value="SAR" ${vCurr === 'SAR' ? 'selected' : ''}>SAR</option>
                                    <option value="PKR" ${vCurr === 'PKR' ? 'selected' : ''}>PKR</option>
                                    <option value="USD" ${vCurr === 'USD' ? 'selected' : ''}>USD</option>
                                    <option value="MYR" ${vCurr === 'MYR' ? 'selected' : ''}>MYR</option>
                                    <option value="AED" ${vCurr === 'AED' ? 'selected' : ''}>AED</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-danger fw-bold" style="font-size:12px;">Cost ROE</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light visa-curr-prefix">1 ${vCurr} =</span>
                                    <input type="number" step="0.0001" name="visas[${i}][cost_roe]" class="form-control visa-cost-roe" placeholder="75.00" value="${costRoe}" ${vIsPkr ? 'readonly' : ''}>
                                </div>
                                <input type="hidden" name="visas[${i}][roe]" class="visa-roe" value="${costRoe}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-success fw-bold" style="font-size:12px;">Sale ROE</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light visa-curr-prefix">1 ${vCurr} =</span>
                                    <input type="number" step="0.0001" name="visas[${i}][sale_roe]" class="form-control visa-sale-roe" placeholder="75.00" value="${saleRoe}" ${vIsPkr ? 'readonly' : ''}>
                                </div>
                            </div>
                            <div class="col-md-2"><label class="form-label text-danger fw-semibold" style="font-size:12px;">Cost Rate (<span class="visa-curr-label">${vCurr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][cost_rate]" class="form-control form-control-sm visa-cost-rate" placeholder="0.00" value="${costRate}"></div>
                            <div class="col-md-3"><label class="form-label text-danger fw-semibold" style="font-size:12px;">Total Cost (<span class="visa-curr-label">${vCurr}</span> & PKR)</label>
                                <div class="p-1 px-2 border rounded bg-light">
                                    <div class="d-flex justify-content-between small text-danger"><span class="visa-curr-name">${vCurr}:</span><strong class="visa-cost-sar-disp">${costRate.toFixed(2)} ${vCurr}</strong></div>
                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="visa-cost-pkr-disp">${costAmt.toFixed(2)} PKR</strong></div>
                                </div>
                                <input type="hidden" name="visas[${i}][cost_amount]" class="visa-cost-amount" value="${costAmt.toFixed(2)}"></div>
                            <div class="col-md-2"><label class="form-label text-success fw-semibold" style="font-size:12px;">Sell Rate (<span class="visa-curr-label">${vCurr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][selling_rate]" class="form-control form-control-sm visa-sell-rate" placeholder="0.00" value="${sellRate}"></div>
                            <div class="col-md-3"><label class="form-label text-success fw-semibold" style="font-size:12px;">Total Sell (<span class="visa-curr-label">${vCurr}</span> & PKR)</label>
                                <div class="p-1 px-2 border rounded bg-light">
                                    <div class="d-flex justify-content-between small text-success"><span class="visa-curr-name">${vCurr}:</span><strong class="visa-sell-sar-disp">${sellRate.toFixed(2)} ${vCurr}</strong></div>
                                    <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="visa-sell-pkr-disp">${sellAmt.toFixed(2)} PKR</strong></div>
                                </div>
                                <input type="hidden" name="visas[${i}][selling_amount]" class="visa-sell-amount" value="${sellAmt.toFixed(2)}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                                <input type="date" name="visas[${i}][booking_date]" class="form-control form-control-sm" value="${vBookingDate}"></div>

                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Status</label>
                                <select name="visas[${i}][status]" class="form-select form-select-sm">
                                    <option value="pending" ${(v.status || 'pending') === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="applied" ${v.status === 'applied' ? 'selected' : ''}>Applied</option>
                                    <option value="approved" ${v.status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="rejected" ${v.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                </select></div>
                            <div class="col-md-6"><label class="form-label" style="font-size:12px;">Visa Attachment</label>
                                <input type="file" name="visas[${i}][visa_attachment]" class="form-control form-control-sm" accept="image/*,.pdf"></div>
                        </div>
                    </div>
                `);
            }
            calcTotal();
        }

        // Flight Currency Change Event Listener
        document.getElementById('flight_currency')?.addEventListener('change', function() {
            const val = this.value;
            const costRoeInput = document.getElementById('flight_cost_roe');
            const saleRoeInput = document.getElementById('flight_sale_roe');
            const legacyRoeInput = document.getElementById('flight_roe');

            if (val === 'PKR') {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                if (legacyRoeInput) legacyRoeInput.value = '1.0000';
            } else {
                if (costRoeInput) {
                    costRoeInput.readOnly = false;
                    if (val === 'SAR') costRoeInput.value = '75.0000';
                    else if (val === 'MYR') costRoeInput.value = '63.0000';
                    else if (val === 'USD') costRoeInput.value = '278.0000';
                    else if (val === 'AED') costRoeInput.value = '76.0000';
                }
                if (saleRoeInput) {
                    saleRoeInput.readOnly = false;
                    if (val === 'SAR') saleRoeInput.value = '75.0000';
                    else if (val === 'MYR') saleRoeInput.value = '63.0000';
                    else if (val === 'USD') saleRoeInput.value = '278.0000';
                    else if (val === 'AED') saleRoeInput.value = '76.0000';
                }
                if (legacyRoeInput && costRoeInput) legacyRoeInput.value = costRoeInput.value;
            }
            calculateFlight();
        });

        // Flight Calculation (Foreign & PKR for Individual Passengers and Dual ROE)
        function calculateFlight() {
            const adults   = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants  = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const pax      = Math.max(1, adults + children + infants);

            const curr = document.getElementById('flight_currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = document.getElementById('flight_cost_roe');
            const saleRoeInput = document.getElementById('flight_sale_roe');
            const legacyRoeInput = document.getElementById('flight_roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(legacyRoeInput?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            if (legacyRoeInput) legacyRoeInput.value = costRoe;

            // Update UI currency labels
            document.querySelectorAll('.flight-curr-label').forEach(el => el.textContent = curr);
            const prefixEl = document.getElementById('flight_curr_prefix');
            if (prefixEl) prefixEl.textContent = `1 ${curr} =`;
            const prefixSaleEl = document.getElementById('flight_sale_curr_prefix');
            if (prefixSaleEl) prefixSaleEl.textContent = `1 ${curr} =`;

            // Check individual passenger rows in flightPersonsTable
            const personRows = document.querySelectorAll('.flight-person-row');
            let totalCostCurr = 0;
            let totalSellCurr = 0;
            let hasCustomPersonRates = false;

            if (personRows.length > 0) {
                personRows.forEach(row => {
                    const costInput = row.querySelector('.person-flight-cost');
                    const sellInput = row.querySelector('.person-flight-sell');
                    const costVal = parseFloat(costInput?.value) || 0;
                    const sellVal = parseFloat(sellInput?.value) || 0;

                    if (costVal > 0 || sellVal > 0) hasCustomPersonRates = true;

                    totalCostCurr += costVal;
                    totalSellCurr += sellVal;

                    const costPkrDisp = row.querySelector('.person-flight-cost-pkr-disp');
                    const sellPkrDisp = row.querySelector('.person-flight-sell-pkr-disp');
                    if (costPkrDisp) costPkrDisp.textContent = (costVal * costRoe).toFixed(2) + ' PKR';
                    if (sellPkrDisp) sellPkrDisp.textContent = (sellVal * saleRoe).toFixed(2) + ' PKR';
                });
            }

            // Fallback: If no individual person rates were entered, check tier inputs or existing flight_charges / flight_cost
            if (!hasCustomPersonRates) {
                const adultCostCurr = parseFloat(document.getElementById('flight_adult_cost')?.value) || 0;
                const adultSellCurr = parseFloat(document.getElementById('flight_adult_sell')?.value) || 0;
                const childCostCurr = parseFloat(document.getElementById('flight_child_cost')?.value) || 0;
                const childSellCurr = parseFloat(document.getElementById('flight_child_sell')?.value) || 0;
                const infantCostCurr = parseFloat(document.getElementById('flight_infant_cost')?.value) || 0;
                const infantSellCurr = parseFloat(document.getElementById('flight_infant_sell')?.value) || 0;

                const tierCostCurr = (adults * adultCostCurr) + (children * childCostCurr) + (infants * infantCostCurr);
                const tierSellCurr = (adults * adultSellCurr) + (children * childSellCurr) + (infants * infantSellCurr);

                if (tierCostCurr > 0 || tierSellCurr > 0) {
                    totalCostCurr = tierCostCurr;
                    totalSellCurr = tierSellCurr;
                } else {
                    const existingCostPkr = parseFloat(document.getElementById('flight_cost')?.value) || 0;
                    const existingSellPkr = parseFloat(document.getElementById('flight_charges')?.value) || 0;
                    if (existingCostPkr > 0 && costRoe > 0) totalCostCurr = existingCostPkr / costRoe;
                    if (existingSellPkr > 0 && saleRoe > 0) totalSellCurr = existingSellPkr / saleRoe;
                }
            }

            const totalCostPkr = totalCostCurr * costRoe;
            const totalSellPkr = totalSellCurr * saleRoe;

            // Badges / Pax labels
            const adultBadge = document.getElementById('flight_adult_pax_label');
            if (adultBadge) adultBadge.textContent = adults;
            const childBadge = document.getElementById('flight_child_pax_label');
            if (childBadge) childBadge.textContent = children;
            const infantBadge = document.getElementById('flight_infant_pax_label');
            if (infantBadge) infantBadge.textContent = infants;

            // Table footer totals
            const tblCostCurr = document.getElementById('tbl_flight_cost_curr_total');
            if (tblCostCurr) tblCostCurr.textContent = totalCostCurr.toFixed(2) + ' ' + curr;
            const tblCostPkr = document.getElementById('tbl_flight_cost_pkr_total');
            if (tblCostPkr) tblCostPkr.textContent = totalCostPkr.toFixed(2) + ' PKR';
            const tblSellCurr = document.getElementById('tbl_flight_sell_curr_total');
            if (tblSellCurr) tblSellCurr.textContent = totalSellCurr.toFixed(2) + ' ' + curr;
            const tblSellPkr = document.getElementById('tbl_flight_sell_pkr_total');
            if (tblSellPkr) tblSellPkr.textContent = totalSellPkr.toFixed(2) + ' PKR';

            // Row displays for tier table
            const elAdultCostDisp = document.getElementById('flight_adult_cost_sar_disp');
            if (elAdultCostDisp) elAdultCostDisp.textContent = totalCostCurr.toFixed(2);
            const elAdultSellDisp = document.getElementById('flight_adult_sell_sar_disp');
            if (elAdultSellDisp) elAdultSellDisp.textContent = totalSellCurr.toFixed(2);

            // Summary cards
            const elCostSarTot = document.getElementById('flight_cost_sar_total');
            if (elCostSarTot) elCostSarTot.textContent = totalCostCurr.toFixed(2) + ' ' + curr;
            const elCostPkrTot = document.getElementById('flight_cost_pkr_total');
            if (elCostPkrTot) elCostPkrTot.textContent = totalCostPkr.toFixed(2) + ' PKR';
            const elSellSarTot = document.getElementById('flight_sell_sar_total');
            if (elSellSarTot) elSellSarTot.textContent = totalSellCurr.toFixed(2) + ' ' + curr;
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
            if (elCostRate) elCostRate.value = totalCostCurr.toFixed(2);
            const elSellRate = document.getElementById('flight_selling_rate');
            if (elSellRate) elSellRate.value = totalSellCurr.toFixed(2);

            calcTotal();
        }

        // Hotels Dynamic Management
        let hotelIdx = {{ $booking->hotels->count() ?: 1 }};

        function addNewHotel(loc = 'makkah') {
            const noHotelAlert = document.getElementById('noHotelAlert');
            if (noHotelAlert) noHotelAlert.style.display = 'none';

            let locLabel = 'Makkah Hotel';
            let locVal = 'makkah';
            if (loc === 'madinah') { locLabel = 'Madinah Hotel'; locVal = 'madinah'; }
            else if (loc === 'other') { locLabel = 'Other Hotel'; locVal = 'other'; }
            else if (loc === 'jeddah') { locLabel = 'Jeddah Hotel'; locVal = 'jeddah'; }

            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
            const isPkr = (flightCurr === 'PKR');

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="${hotelIdx}">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <h6 class="text-primary mb-0 fw-bold"><i class="mdi mdi-hotel me-1"></i> ${locLabel}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-hotel"><i class="mdi mdi-delete-outline me-1"></i> Remove</button>
                </div>
                <input type="hidden" name="hotels[${hotelIdx}][location]" value="${locVal}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Hotel Name</label>
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select">
                            ${hotelsOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-flex justify-content-between align-items-center mb-1">
                            <span>Hotel Supplier / Vendor</span>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshSuppliersList(true)" title="Reload Suppliers"><i class="mdi mdi-refresh"></i> Refresh</button>
                        </label>
                        <select name="hotels[${hotelIdx}][supplier_id]" class="form-select">
                            ${suppliersOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-dark fw-bold">Currency</label>
                        <select name="hotels[${hotelIdx}][currency]" class="form-select hotel-currency fw-bold">
                            <option value="SAR" ${flightCurr === 'SAR' ? 'selected' : ''}>SAR (Riyal)</option>
                            <option value="PKR" ${flightCurr === 'PKR' ? 'selected' : ''}>PKR (Rupee)</option>
                            <option value="USD" ${flightCurr === 'USD' ? 'selected' : ''}>USD (Dollar)</option>
                            <option value="MYR" ${flightCurr === 'MYR' ? 'selected' : ''}>MYR (Ringgit)</option>
                            <option value="AED" ${flightCurr === 'AED' ? 'selected' : ''}>AED (Dirham)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-danger fw-bold">Cost ROE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light hotel-curr-prefix">1 ${flightCurr} =</span>
                            <input type="number" step="0.0001" name="hotels[${hotelIdx}][cost_roe]" class="form-control hotel-cost-roe" value="${defaultCostRoe}" placeholder="75.00" ${isPkr ? 'readonly' : ''}>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][roe]" class="hotel-roe" value="${defaultCostRoe}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-success fw-bold">Sale ROE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light hotel-curr-prefix">1 ${flightCurr} =</span>
                            <input type="number" step="0.0001" name="hotels[${hotelIdx}][sale_roe]" class="form-control hotel-sale-roe" value="${defaultSaleRoe}" placeholder="75.00" ${isPkr ? 'readonly' : ''}>
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
                    <div class="col-md-2">
                        <label class="form-label text-warning fw-bold"><i class="mdi mdi-calendar-clock me-1 text-warning"></i> Option Date</label>
                        <input type="date" name="hotels[${hotelIdx}][option_date]" class="form-control hotel-option-date border-warning">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                        <input type="date" name="hotels[${hotelIdx}][booking_date]" class="form-control hotel-booking-date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Total Cost (<span class="hotel-curr-label">${flightCurr}</span> & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-danger"><span class="hotel-curr-name">${flightCurr}:</span><strong class="hotel-cost-sar-disp">0.00 ${flightCurr}</strong></div>
                            <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="hotel-cost-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][cost_amount]" class="hotel-cost-total" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Total Sell (<span class="hotel-curr-label">${flightCurr}</span> & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-success"><span class="hotel-curr-name">${flightCurr}:</span><strong class="hotel-sell-sar-disp">0.00 ${flightCurr}</strong></div>
                            <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="hotel-sell-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][selling_amount]" class="hotel-sell-total" value="0">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                        <input type="file" name="hotels[${hotelIdx}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
                    </div>

                    {{-- Individual Passenger Hotel Rates Table --}}
                    <div class="col-12 hotel-persons-wrapper mt-3 pt-3 border-top">
                        <div class="p-2 px-3 mb-2 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="fw-bold text-dark fs-12"><i class="mdi mdi-account-multiple me-1 text-primary"></i> Individual Passenger Hotel Rates:</span>
                                <small class="text-muted d-block" style="font-size:11px;">Enter custom rates per night for each passenger independently below (e.g. Person 1 and Person 2 can have different rates).</small>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-white fs-11">Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>):</span>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end quick-hotel-cost" placeholder="Cost" style="width: 80px;">
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end quick-hotel-sell" placeholder="Sell" style="width: 80px;">
                                    <button type="button" class="btn btn-primary btn-sm btn-quick-hotel-apply"><i class="mdi mdi-check me-1"></i> Apply to All Pax</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive border rounded bg-white shadow-sm mb-2">
                            <table class="table table-sm table-hover align-middle mb-0 hotel-persons-table">
                                <thead class="table-light fs-12">
                                    <tr>
                                        <th style="width: 35px;" class="text-center">#</th>
                                        <th style="width: 25%;">Passenger Name</th>
                                        <th style="width: 12%;" class="text-center">Nights</th>
                                        <th style="width: 25%;" class="text-danger text-end">Cost / Night (<span class="hotel-curr-label">${flightCurr}</span>)</th>
                                        <th style="width: 25%;" class="text-success text-end">Sell / Night (<span class="hotel-curr-label">${flightCurr}</span>)</th>
                                    </tr>
                                </thead>
                                <tbody class="hotel-persons-tbody">
                                </tbody>
                                <tfoot class="table-light fw-bold fs-12 border-top">
                                    <tr>
                                        <td colspan="3" class="text-dark">Hotel Passenger Totals:</td>
                                        <td class="text-end text-danger">
                                            <span class="hotel-person-tbl-cost-curr">0.00 ${flightCurr}</span>
                                            <br><small class="text-danger fw-normal hotel-person-tbl-cost-pkr">0.00 PKR</small>
                                        </td>
                                        <td class="text-end text-success">
                                            <span class="hotel-person-tbl-sell-curr">0.00 ${flightCurr}</span>
                                            <br><small class="text-success fw-normal hotel-person-tbl-sell-pkr">0.00 PKR</small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

            document.getElementById('hotelsList').insertAdjacentHTML('beforeend', html);
            const lastRow = document.querySelector('#hotelsList .hotel-block:last-child');
            hotelIdx++;
            syncBookingHotelDates();
            if (lastRow) {
                syncHotelPersons(lastRow);
                calculateHotelRow(lastRow);
            }
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

        document.getElementById('hotelsList')?.addEventListener('change', function(e) {
            if (e.target.classList.contains('hotel-currency')) {
                const row = e.target.closest('.hotel-block');
                const val = e.target.value;
                const costRoeInput = row.querySelector('.hotel-cost-roe');
                const saleRoeInput = row.querySelector('.hotel-sale-roe');
                if (val === 'PKR') {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (val === 'SAR') costRoeInput.value = '75.0000';
                        else if (val === 'MYR') costRoeInput.value = '63.0000';
                        else if (val === 'USD') costRoeInput.value = '278.0000';
                        else if (val === 'AED') costRoeInput.value = '76.0000';
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (val === 'SAR') saleRoeInput.value = '75.0000';
                        else if (val === 'MYR') saleRoeInput.value = '63.0000';
                        else if (val === 'USD') saleRoeInput.value = '278.0000';
                        else if (val === 'AED') saleRoeInput.value = '76.0000';
                    }
                }
                calculateHotelRow(row);
            }
        });

        function syncHotelPersons(hotelBlock) {
            if (!hotelBlock) return;
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const tbody = hotelBlock.querySelector('.hotel-persons-tbody');
            if (!tbody) return;

            const hotelIdx = hotelBlock.getAttribute('data-hotel-idx') || 0;
            const curr = hotelBlock.querySelector('.hotel-currency')?.value || 'SAR';
            const nights = parseFloat(hotelBlock.querySelector('.hotel-nights')?.value) || 1;
            const isPkr = (curr === 'PKR');
            const costRoe = isPkr ? 1 : (parseFloat(hotelBlock.querySelector('.hotel-cost-roe')?.value) || parseFloat(hotelBlock.querySelector('.hotel-roe')?.value) || 75);
            const saleRoe = isPkr ? 1 : (parseFloat(hotelBlock.querySelector('.hotel-sale-roe')?.value) || costRoe || 75);

            // Preserve any existing DOM input values
            const existingDomData = {};
            tbody.querySelectorAll('.hotel-person-row').forEach(row => {
                const pIdx = row.getAttribute('data-person-idx');
                if (pIdx !== null) {
                    existingDomData[pIdx] = {
                        cost: row.querySelector('.hotel-person-cost')?.value,
                        sell: row.querySelector('.hotel-person-sell')?.value,
                    };
                }
            });

            // Check if saved rates exist in data-person-rates attribute
            let savedPersonRates = [];
            try {
                const rawSaved = hotelBlock.getAttribute('data-person-rates');
                if (rawSaved) {
                    savedPersonRates = typeof rawSaved === 'string' ? JSON.parse(rawSaved) : rawSaved;
                }
            } catch(e) {}

            let rowsHtml = '';
            for (let p = 0; p < pax; p++) {
                const pExisting = (typeof existingPersons !== 'undefined' && existingPersons && existingPersons[p]) ? existingPersons[p] : null;
                const personName = document.getElementById(`person_name_${p}`)?.value || (pExisting ? pExisting.full_name : '') || `Passenger ${p + 1}`;
                const inDom = existingDomData[p];
                const inSaved = (savedPersonRates && savedPersonRates[p]) ? savedPersonRates[p] : null;

                let pCost = '';
                let pSell = '';

                if (inDom) {
                    pCost = inDom.cost !== undefined ? inDom.cost : '';
                    pSell = inDom.sell !== undefined ? inDom.sell : '';
                } else if (inSaved) {
                    pCost = inSaved.cost_per_night !== undefined ? inSaved.cost_per_night : (inSaved.cost || '');
                    pSell = inSaved.selling_per_night !== undefined ? inSaved.selling_per_night : (inSaved.sell || '');
                }

                // If still empty and hotel has cost_per_night / selling_per_night from earlier
                if (pCost === '') {
                    const hCostRate = parseFloat(hotelBlock.querySelector('.hotel-cost-rate')?.value) || 0;
                    if (hCostRate > 0) {
                        pCost = (pax > 1 ? (hCostRate / pax).toFixed(2) : hCostRate.toFixed(2));
                    }
                }
                if (pSell === '') {
                    const hSellRate = parseFloat(hotelBlock.querySelector('.hotel-sell-rate')?.value) || 0;
                    if (hSellRate > 0) {
                        pSell = (pax > 1 ? (hSellRate / pax).toFixed(2) : hSellRate.toFixed(2));
                    }
                }

                const costNum = parseFloat(pCost) || 0;
                const sellNum = parseFloat(pSell) || 0;
                const totCostForeign = costNum * nights;
                const totCostPkr = totCostForeign * costRoe;
                const totSellForeign = sellNum * nights;
                const totSellPkr = totSellForeign * saleRoe;

                rowsHtml += `
                <tr class="hotel-person-row" data-person-idx="${p}">
                    <td class="text-center text-muted fw-bold">${p + 1}</td>
                    <td>
                        <strong class="text-primary person-hotel-name-disp">${personName}</strong>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][name]" class="person-hotel-name" value="${personName}">
                    </td>
                    <td class="text-center fw-semibold hotel-person-nights-disp">${nights}</td>
                    <td>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][person_rates][${p}][cost_per_night]"
                            class="form-control form-control-sm text-end hotel-person-cost" value="${pCost}" placeholder="0.00">
                        <small class="text-danger d-block text-end hotel-person-cost-disp" style="font-size:11px;">
                            ${totCostForeign.toFixed(2)} ${curr} (${totCostPkr.toFixed(2)} PKR)
                        </small>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][total_cost]" class="hotel-person-total-cost" value="${totCostPkr.toFixed(2)}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][person_rates][${p}][selling_per_night]"
                            class="form-control form-control-sm text-end hotel-person-sell" value="${pSell}" placeholder="0.00">
                        <small class="text-success d-block text-end hotel-person-sell-disp" style="font-size:11px;">
                            ${totSellForeign.toFixed(2)} ${curr} (${totSellPkr.toFixed(2)} PKR)
                        </small>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][total_selling]" class="hotel-person-total-sell" value="${totSellPkr.toFixed(2)}">
                    </td>
                </tr>`;
            }

            tbody.innerHTML = rowsHtml;
        }

        function syncAllHotelPersons() {
            document.querySelectorAll('.hotel-block').forEach((block, idx) => {
                block.setAttribute('data-hotel-idx', idx);
                syncHotelPersons(block);
                calculateHotelRow(block);
            });
        }

        function calculateHotelRow(row) {
            if (!row) return;
            const nights = parseFloat(row.querySelector('.hotel-nights')?.value) || 0;
            const rooms = parseFloat(row.querySelector('.hotel-rooms')?.value) || 1;
            const curr = row.querySelector('.hotel-currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = row.querySelector('.hotel-cost-roe');
            const saleRoeInput = row.querySelector('.hotel-sale-roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(row.querySelector('.hotel-roe')?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            const legacyRoe = row.querySelector('.hotel-roe');
            if (legacyRoe) legacyRoe.value = costRoe;

            row.querySelectorAll('.hotel-curr-label').forEach(el => el.textContent = curr);
            row.querySelectorAll('.hotel-curr-prefix').forEach(el => el.textContent = `1 ${curr} =`);
            row.querySelectorAll('.hotel-curr-name').forEach(el => el.textContent = `${curr}:`);
            row.querySelectorAll('.hotel-person-nights-disp').forEach(el => el.textContent = nights);

            const personRows = row.querySelectorAll('.hotel-person-row');
            let sumPersonCostForeign = 0;
            let sumPersonSellForeign = 0;
            let hasPersonRates = false;

            if (personRows.length > 0) {
                personRows.forEach(pRow => {
                    const cInput = pRow.querySelector('.hotel-person-cost');
                    const sInput = pRow.querySelector('.hotel-person-sell');
                    const cRate = parseFloat(cInput?.value) || 0;
                    const sRate = parseFloat(sInput?.value) || 0;

                    if (cRate > 0 || sRate > 0) hasPersonRates = true;

                    const pTotCostCurr = cRate * nights;
                    const pTotCostPkr = pTotCostCurr * costRoe;
                    const pTotSellCurr = sRate * nights;
                    const pTotSellPkr = pTotSellCurr * saleRoe;

                    sumPersonCostForeign += pTotCostCurr;
                    sumPersonSellForeign += pTotSellCurr;

                    const cDisp = pRow.querySelector('.hotel-person-cost-disp');
                    const sDisp = pRow.querySelector('.hotel-person-sell-disp');
                    if (cDisp) cDisp.textContent = `${pTotCostCurr.toFixed(2)} ${curr} (${pTotCostPkr.toFixed(2)} PKR)`;
                    if (sDisp) sDisp.textContent = `${pTotSellCurr.toFixed(2)} ${curr} (${pTotSellPkr.toFixed(2)} PKR)`;

                    const hidCost = pRow.querySelector('.hotel-person-total-cost');
                    const hidSell = pRow.querySelector('.hotel-person-total-sell');
                    if (hidCost) hidCost.value = pTotCostPkr.toFixed(2);
                    if (hidSell) hidSell.value = pTotSellPkr.toFixed(2);
                });
            }

            let costCurr = 0;
            let sellCurr = 0;

            if (hasPersonRates) {
                costCurr = sumPersonCostForeign;
                sellCurr = sumPersonSellForeign;
                if (nights > 0) {
                    const crInput = row.querySelector('.hotel-cost-rate');
                    if (crInput) crInput.value = (costCurr / nights).toFixed(2);
                    const srInput = row.querySelector('.hotel-sell-rate');
                    if (srInput) srInput.value = (sellCurr / nights).toFixed(2);
                }
            } else {
                const costRate = parseFloat(row.querySelector('.hotel-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.hotel-sell-rate')?.value) || 0;
                costCurr = nights * rooms * costRate;
                sellCurr = nights * rooms * sellRate;
            }

            const costPkr = costCurr * costRoe;
            const sellPkr = sellCurr * saleRoe;

            const costSarDisp = row.querySelector('.hotel-cost-sar-disp');
            const costPkrDisp = row.querySelector('.hotel-cost-pkr-disp');
            const costTotalInput = row.querySelector('.hotel-cost-total');

            const sellSarDisp = row.querySelector('.hotel-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.hotel-sell-pkr-disp');
            const sellTotalInput = row.querySelector('.hotel-sell-total');

            if (costSarDisp) costSarDisp.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costTotalInput) costTotalInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellCurr.toFixed(2) + ' ' + curr;
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellTotalInput) sellTotalInput.value = sellPkr.toFixed(2);

            const tblCostCurr = row.querySelector('.hotel-person-tbl-cost-curr');
            const tblCostPkr = row.querySelector('.hotel-person-tbl-cost-pkr');
            const tblSellCurr = row.querySelector('.hotel-person-tbl-sell-curr');
            const tblSellPkr = row.querySelector('.hotel-person-tbl-sell-pkr');

            if (tblCostCurr) tblCostCurr.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (tblCostPkr) tblCostPkr.textContent = costPkr.toFixed(2) + ' PKR';
            if (tblSellCurr) tblSellCurr.textContent = sellCurr.toFixed(2) + ' ' + curr;
            if (tblSellPkr) tblSellPkr.textContent = sellPkr.toFixed(2) + ' PKR';

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
        let routeIdx = {{ $booking->transports->count() ?: 1 }};

        document.getElementById('addRoute')?.addEventListener('click', function() {
            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
            const isPkr = (flightCurr === 'PKR');

            document.getElementById('routesList').insertAdjacentHTML('beforeend', `
                <div class="route-block border rounded p-3 mb-2 bg-white shadow-sm">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small fw-bold">Route #${routeIdx + 1}</span>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-route">×</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Route</label>
                            <select name="transports[${routeIdx}][route]" class="form-select transport-route-select">
                                ${routesOptions}
                            </select></div>
                        <div class="col-md-2"><label class="form-label">Vehicle</label>
                            <select name="transports[${routeIdx}][vehicle]" class="form-select transport-vehicle-select">
                                ${vehiclesOptions}
                            </select></div>
                        <div class="col-md-1"><label class="form-label">Qty</label>
                            <input type="number" name="transports[${routeIdx}][quantity]" class="form-control" value="1" min="1"></div>
                        <div class="col-md-2"><label class="form-label">Type / Status</label>
                            <select name="transports[${routeIdx}][status]" class="form-select">
                                <option value="CONFIRMED" selected>CONFIRMED</option>
                                <option value="PENDING">PENDING</option>
                                <option value="CANCELLED">CANCELLED</option>
                            </select></div>
                        <div class="col-md-1"><label class="form-label">Pax</label>
                            <input type="number" name="transports[${routeIdx}][pax]" class="form-control" value="1" min="1"></div>
                        <div class="col-md-3"><label class="form-label">Transport Supplier / Vendor</label>
                            <select name="transports[${routeIdx}][supplier_id]" class="form-select">
                                ${suppliersOptions}
                            </select></div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Date</label>
                            <input type="date" name="transports[${routeIdx}][travel_date]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-clock-outline me-1 text-primary"></i> Pickup Time</label>
                            <input type="text" name="transports[${routeIdx}][travel_time]" class="form-control" placeholder="e.g. 0045 / 1200">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-airplane me-1 text-info"></i> Flight #</label>
                            <input type="text" name="transports[${routeIdx}][flight_no]" class="form-control" placeholder="e.g. PK831">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-map-marker-outline me-1 text-success"></i> Pickup From</label>
                            <input type="text" name="transports[${routeIdx}][pickup_from]" class="form-control transport-pickup-from" placeholder="e.g. JED APT">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-map-marker-check me-1 text-danger"></i> Destination</label>
                            <input type="text" name="transports[${routeIdx}][destination]" class="form-control transport-destination" placeholder="e.g. MAK HTL">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-dark"><i class="mdi mdi-barcode me-1 text-secondary"></i> BRN #</label>
                            <input type="text" name="transports[${routeIdx}][brn]" class="form-control" placeholder="BRN Number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="mdi mdi-airplane-takeoff me-1 text-info"></i> Airline</label>
                            <select name="transports[${routeIdx}][airline]" class="form-select transport-airline-select">
                                ${airlinesOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="mdi mdi-calendar-check me-1"></i> Booking Date</label>
                            <input type="date" name="transports[${routeIdx}][booking_date]" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-dark fw-bold">Currency</label>
                            <select name="transports[${routeIdx}][currency]" class="form-select transport-currency fw-bold">
                                <option value="SAR" ${flightCurr === 'SAR' ? 'selected' : ''}>SAR (Riyal)</option>
                                <option value="PKR" ${flightCurr === 'PKR' ? 'selected' : ''}>PKR (Rupee)</option>
                                <option value="USD" ${flightCurr === 'USD' ? 'selected' : ''}>USD (Dollar)</option>
                                <option value="MYR" ${flightCurr === 'MYR' ? 'selected' : ''}>MYR (Ringgit)</option>
                                <option value="AED" ${flightCurr === 'AED' ? 'selected' : ''}>AED (Dirham)</option>
                            </select></div>
                        <div class="col-md-2">
                            <label class="form-label text-danger fw-bold">Cost ROE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light transport-curr-prefix">1 ${flightCurr} =</span>
                                <input type="number" step="0.0001" name="transports[${routeIdx}][cost_roe]" class="form-control transport-cost-roe" placeholder="75.00" value="${defaultCostRoe}" ${isPkr ? 'readonly' : ''}>
                            </div>
                            <input type="hidden" name="transports[${routeIdx}][roe]" class="transport-roe" value="${defaultCostRoe}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-success fw-bold">Sale ROE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light transport-curr-prefix">1 ${flightCurr} =</span>
                                <input type="number" step="0.0001" name="transports[${routeIdx}][sale_roe]" class="form-control transport-sale-roe" placeholder="75.00" value="${defaultSaleRoe}" ${isPkr ? 'readonly' : ''}>
                            </div>
                        </div>
                        <div class="col-md-2"><label class="form-label text-danger fw-semibold">Cost Rate (<span class="transport-curr-label">${flightCurr}</span>)</label>
                            <input type="number" step="0.01" name="transports[${routeIdx}][cost_rate]" class="form-control transport-cost-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-danger fw-semibold">Total Cost (<span class="transport-curr-label">${flightCurr}</span> & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-danger"><span class="transport-curr-name">${flightCurr}:</span><strong class="transport-cost-sar-disp">0.00 ${flightCurr}</strong></div>
                                <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">0.00 PKR</strong></div>
                            </div>
                            <input type="hidden" name="transports[${routeIdx}][cost_amount]" class="transport-cost-amount" value="0"></div>
                        <div class="col-md-2"><label class="form-label text-success fw-semibold">Sell Rate (<span class="transport-curr-label">${flightCurr}</span>)</label>
                            <input type="number" step="0.01" name="transports[${routeIdx}][selling_rate]" class="form-control transport-sell-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-success fw-semibold">Total Sell (<span class="transport-curr-label">${flightCurr}</span> & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-success"><span class="transport-curr-name">${flightCurr}:</span><strong class="transport-sell-sar-disp">0.00 ${flightCurr}</strong></div>
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

        document.getElementById('routesList')?.addEventListener('change', function(e) {
            if (e.target.classList.contains('transport-route-select')) {
                const row = e.target.closest('.route-block');
                const selectedText = e.target.options[e.target.selectedIndex]?.text || e.target.value || '';
                let pickup = '';
                let dest = '';
                if (selectedText.includes('→')) {
                    const parts = selectedText.split('→');
                    pickup = parts[0]?.trim();
                    dest = parts[1]?.trim();
                } else if (selectedText.toUpperCase().includes(' TO ')) {
                    const parts = selectedText.split(/ to /i);
                    pickup = parts[0]?.trim();
                    dest = parts[1]?.trim();
                } else if (selectedText.includes('-')) {
                    const parts = selectedText.split('-');
                    pickup = parts[0]?.trim();
                    dest = parts[1]?.trim();
                }
                const pickupInput = row?.querySelector('.transport-pickup-from');
                const destInput = row?.querySelector('.transport-destination');
                if (pickupInput && pickup) pickupInput.value = pickup;
                if (destInput && dest) destInput.value = dest;
            }

            if (e.target.classList.contains('transport-currency')) {
                const row = e.target.closest('.route-block');
                const val = e.target.value;
                const costRoeInput = row.querySelector('.transport-cost-roe');
                const saleRoeInput = row.querySelector('.transport-sale-roe');
                if (val === 'PKR') {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (val === 'SAR') costRoeInput.value = '75.0000';
                        else if (val === 'MYR') costRoeInput.value = '63.0000';
                        else if (val === 'USD') costRoeInput.value = '278.0000';
                        else if (val === 'AED') costRoeInput.value = '76.0000';
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (val === 'SAR') saleRoeInput.value = '75.0000';
                        else if (val === 'MYR') saleRoeInput.value = '63.0000';
                        else if (val === 'USD') saleRoeInput.value = '278.0000';
                        else if (val === 'AED') saleRoeInput.value = '76.0000';
                    }
                }
                calculateTransportRow(row);
            }
        });

        function calculateTransportRow(row) {
            if (!row) return;
            const curr = row.querySelector('.transport-currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = row.querySelector('.transport-cost-roe');
            const saleRoeInput = row.querySelector('.transport-sale-roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(row.querySelector('.transport-roe')?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            const legacyRoe = row.querySelector('.transport-roe');
            if (legacyRoe) legacyRoe.value = costRoe;

            row.querySelectorAll('.transport-curr-label').forEach(el => el.textContent = curr);
            row.querySelectorAll('.transport-curr-prefix').forEach(el => el.textContent = `1 ${curr} =`);
            row.querySelectorAll('.transport-curr-name').forEach(el => el.textContent = `${curr}:`);

            const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;

            const costCurr = costRate;
            const costPkr = costCurr * costRoe;
            const sellCurr = sellRate;
            const sellPkr = sellCurr * saleRoe;

            const costSarDisp = row.querySelector('.transport-cost-sar-disp');
            const costPkrDisp = row.querySelector('.transport-cost-pkr-disp');
            const costAmtInput = row.querySelector('.transport-cost-amount');

            const sellSarDisp = row.querySelector('.transport-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.transport-sell-pkr-disp');
            const sellAmtInput = row.querySelector('.transport-sell-amount');

            if (costSarDisp) costSarDisp.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costAmtInput) costAmtInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellCurr.toFixed(2) + ' ' + curr;
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

        document.getElementById('visasList')?.addEventListener('change', function(e) {
            if (e.target.classList.contains('visa-currency')) {
                const row = e.target.closest('.visa-card');
                const val = e.target.value;
                const costRoeInput = row.querySelector('.visa-cost-roe');
                const saleRoeInput = row.querySelector('.visa-sale-roe');
                if (val === 'PKR') {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (val === 'SAR') costRoeInput.value = '75.0000';
                        else if (val === 'MYR') costRoeInput.value = '63.0000';
                        else if (val === 'USD') costRoeInput.value = '278.0000';
                        else if (val === 'AED') costRoeInput.value = '76.0000';
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (val === 'SAR') saleRoeInput.value = '75.0000';
                        else if (val === 'MYR') saleRoeInput.value = '63.0000';
                        else if (val === 'USD') saleRoeInput.value = '278.0000';
                        else if (val === 'AED') saleRoeInput.value = '76.0000';
                    }
                }
                calculateVisaRow(row);
            }
        });

        function calculateVisaRow(row) {
            if (!row) return;
            const curr = row.querySelector('.visa-currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = row.querySelector('.visa-cost-roe');
            const saleRoeInput = row.querySelector('.visa-sale-roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(row.querySelector('.visa-roe')?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            const legacyRoe = row.querySelector('.visa-roe');
            if (legacyRoe) legacyRoe.value = costRoe;

            row.querySelectorAll('.visa-curr-label').forEach(el => el.textContent = curr);
            row.querySelectorAll('.visa-curr-prefix').forEach(el => el.textContent = `1 ${curr} =`);
            row.querySelectorAll('.visa-curr-name').forEach(el => el.textContent = `${curr}:`);

            const costRate = parseFloat(row.querySelector('.visa-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.visa-sell-rate')?.value) || 0;

            const costCurr = costRate;
            const costPkr = costCurr * costRoe;
            const sellCurr = sellRate;
            const sellPkr = sellCurr * saleRoe;

            const costSarDisp = row.querySelector('.visa-cost-sar-disp');
            const costPkrDisp = row.querySelector('.visa-cost-pkr-disp');
            const costAmtInput = row.querySelector('.visa-cost-amount');

            const sellSarDisp = row.querySelector('.visa-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.visa-sell-pkr-disp');
            const sellAmtInput = row.querySelector('.visa-sell-amount');

            if (costSarDisp) costSarDisp.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costAmtInput) costAmtInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellCurr.toFixed(2) + ' ' + curr;
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

            // 1. Flight Foreign & PKR
            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            let flightForeign = 0;
            const flSellDisp = document.getElementById('flight_sell_sar_total')?.textContent || '';
            flightForeign = parseFloat(flSellDisp) || 0;
            let flightPkr = parseFloat(document.getElementById('flight_charges')?.value) || 0;
            const flightSaleRoe = (flightCurr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_sale_roe')?.value) || parseFloat(document.getElementById('flight_cost_roe')?.value) || 75);

            if (flightForeign === 0 && flightPkr > 0 && flightCurr !== 'PKR' && flightSaleRoe > 0) {
                flightForeign = flightPkr / flightSaleRoe;
                const elFlDisp = document.getElementById('flight_sell_sar_total');
                if (elFlDisp) elFlDisp.textContent = flightForeign.toFixed(2) + ' ' + flightCurr;
            }

            // 2. Hotel Foreign & PKR
            let hotelCostForeign = 0;
            let hotelCostPkr = 0;
            let hotelSellForeign = 0;
            let hotelSellPkr = 0;
            let hotelCurrs = new Set();
            document.querySelectorAll('.hotel-block').forEach(row => {
                const costTotalInput = row.querySelector('.hotel-cost-total');
                const sellTotalInput = row.querySelector('.hotel-sell-total');
                const costSarDisp = row.querySelector('.hotel-cost-sar-disp');
                const sellSarDisp = row.querySelector('.hotel-sell-sar-disp');
                const curr = row.querySelector('.hotel-currency')?.value || 'SAR';

                const cPkr = parseFloat(costTotalInput?.value) || 0;
                const sPkr = parseFloat(sellTotalInput?.value) || 0;
                const cForeign = parseFloat(costSarDisp?.textContent) || 0;
                const sForeign = parseFloat(sellSarDisp?.textContent) || 0;

                hotelCostForeign += cForeign;
                hotelCostPkr += cPkr;
                hotelSellForeign += sForeign;
                hotelSellPkr += sPkr;
                hotelCurrs.add(curr);
            });
            const hotelCurrLabel = hotelCurrs.size === 1 ? Array.from(hotelCurrs)[0] : (hotelCurrs.size > 1 ? 'Mix' : 'SAR');
            if (document.getElementById('hotel_charges')) document.getElementById('hotel_charges').value = hotelSellPkr.toFixed(2);
            if (document.getElementById('hotel_cost_sar_total')) document.getElementById('hotel_cost_sar_total').textContent = hotelCostForeign.toFixed(2) + ' ' + hotelCurrLabel;
            if (document.getElementById('hotel_cost_pkr_total')) document.getElementById('hotel_cost_pkr_total').textContent = hotelCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('hotel_sell_sar_total')) document.getElementById('hotel_sell_sar_total').textContent = hotelSellForeign.toFixed(2) + ' ' + hotelCurrLabel;
            if (document.getElementById('hotel_sell_pkr_total')) document.getElementById('hotel_sell_pkr_total').textContent = hotelSellPkr.toFixed(2) + ' PKR';

            // 3. Transport Foreign & PKR
            let transportCostForeign = 0;
            let transportCostPkr = 0;
            let transportSellForeign = 0;
            let transportSellPkr = 0;
            let transportCurrs = new Set();
            document.querySelectorAll('.route-block').forEach(row => {
                const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;
                const curr     = row.querySelector('.transport-currency')?.value || 'SAR';
                const isPkr    = (curr === 'PKR');
                const costRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.transport-cost-roe')?.value) || parseFloat(row.querySelector('.transport-roe')?.value) || 75);
                const saleRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.transport-sale-roe')?.value) || costRoe || 75);

                transportCostForeign += costRate;
                transportCostPkr += costRate * costRoe;

                transportSellForeign += sellRate;
                transportSellPkr += sellRate * saleRoe;
                transportCurrs.add(curr);
            });
            const transportCurrLabel = transportCurrs.size === 1 ? Array.from(transportCurrs)[0] : (transportCurrs.size > 1 ? 'Mix' : 'SAR');
            if (document.getElementById('transport_charges')) document.getElementById('transport_charges').value = transportSellPkr.toFixed(2);
            if (document.getElementById('transport_cost_sar_total')) document.getElementById('transport_cost_sar_total').textContent = transportCostForeign.toFixed(2) + ' ' + transportCurrLabel;
            if (document.getElementById('transport_cost_pkr_total')) document.getElementById('transport_cost_pkr_total').textContent = transportCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('transport_sell_sar_total')) document.getElementById('transport_sell_sar_total').textContent = transportSellForeign.toFixed(2) + ' ' + transportCurrLabel;
            if (document.getElementById('transport_sell_pkr_total')) document.getElementById('transport_sell_pkr_total').textContent = transportSellPkr.toFixed(2) + ' PKR';

            // 4. Visa Foreign & PKR
            let visaCostForeign = 0;
            let visaCostPkr = 0;
            let visaSellForeign = 0;
            let visaSellPkr = 0;
            let visaCurrs = new Set();
            document.querySelectorAll('.visa-card').forEach(row => {
                const costRate = parseFloat(row.querySelector('.visa-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.visa-sell-rate')?.value) || 0;
                const curr     = row.querySelector('.visa-currency')?.value || 'SAR';
                const isPkr    = (curr === 'PKR');
                const costRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.visa-cost-roe')?.value) || parseFloat(row.querySelector('.visa-roe')?.value) || 75);
                const saleRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.visa-sale-roe')?.value) || costRoe || 75);

                visaCostForeign += costRate;
                visaCostPkr += costRate * costRoe;

                visaSellForeign += sellRate;
                visaSellPkr += sellRate * saleRoe;
                visaCurrs.add(curr);
            });
            const visaCurrLabel = visaCurrs.size === 1 ? Array.from(visaCurrs)[0] : (visaCurrs.size > 1 ? 'Mix' : 'SAR');
            if (document.getElementById('visa_charges')) document.getElementById('visa_charges').value = visaSellPkr.toFixed(2);
            if (document.getElementById('visa_cost_sar_total')) document.getElementById('visa_cost_sar_total').textContent = visaCostForeign.toFixed(2) + ' ' + visaCurrLabel;
            if (document.getElementById('visa_cost_pkr_total')) document.getElementById('visa_cost_pkr_total').textContent = visaCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('visa_sell_sar_total')) document.getElementById('visa_sell_sar_total').textContent = visaSellForeign.toFixed(2) + ' ' + visaCurrLabel;
            if (document.getElementById('visa_sell_pkr_total')) document.getElementById('visa_sell_pkr_total').textContent = visaSellPkr.toFixed(2) + ' PKR';

            // Package, Other, Discount
            const pkgCost     = parseFloat(document.getElementById('package_cost')?.value) || 0;
            const pkgTotalPkr = pkgCost * pax;

            const other      = parseFloat(document.getElementById('other_charges')?.value) || 0;
            const discount   = parseFloat(document.getElementById('discount')?.value) || 0;
            const additional = parseFloat(document.getElementById('additional_services_amount')?.value) || 0;
            const received   = parseFloat(document.getElementById('total_received')?.value) || 0;

            const totalPkr   = pkgTotalPkr + hotelSellPkr + visaSellPkr + flightPkr + transportSellPkr + other + additional - discount;
            const balancePkr = totalPkr - received - transactionsPaid;

            let activeCurrs = new Set();
            if (flightForeign > 0) activeCurrs.add(flightCurr);
            if (hotelSellForeign > 0) hotelCurrs.forEach(c => activeCurrs.add(c));
            if (transportSellForeign > 0) transportCurrs.forEach(c => activeCurrs.add(c));
            if (visaSellForeign > 0) visaCurrs.forEach(c => activeCurrs.add(c));
            activeCurrs.delete('PKR');

            let totalForeign = 0;
            let foreignUnit = 'SAR';
            if (activeCurrs.size === 1) {
                foreignUnit = Array.from(activeCurrs)[0];
                totalForeign = (flightCurr === foreignUnit ? flightForeign : 0)
                             + (hotelCurrLabel === foreignUnit ? hotelSellForeign : 0)
                             + (transportCurrLabel === foreignUnit ? transportSellForeign : 0)
                             + (visaCurrLabel === foreignUnit ? visaSellForeign : 0);
            } else if (activeCurrs.size > 1) {
                foreignUnit = 'Mix';
                totalForeign = flightForeign + hotelSellForeign + transportSellForeign + visaSellForeign;
            } else {
                foreignUnit = (flightCurr !== 'PKR') ? flightCurr : 'SAR';
                totalForeign = flightForeign + hotelSellForeign + transportSellForeign + visaSellForeign;
            }

            const avgRoe = totalForeign > 0 ? (totalPkr / totalForeign) : (parseFloat(document.getElementById('flight_sale_roe')?.value) || 75);
            const balanceForeign = avgRoe > 0 ? (balancePkr / avgRoe) : 0;

            if (document.getElementById('total_amount')) document.getElementById('total_amount').value = totalPkr.toFixed(2);
            if (document.getElementById('balance')) document.getElementById('balance').value = balancePkr.toFixed(2);

            // Summary Bar
            if (document.getElementById('bar_pax')) document.getElementById('bar_pax').textContent = `${pax} (A:${adults}, C:${children}, I:${infants})`;
            if (document.getElementById('bar_pax_badge')) document.getElementById('bar_pax_badge').textContent = pax;
            if (document.getElementById('bar_adult')) document.getElementById('bar_adult').textContent = `PKR ${pkgTotalPkr.toFixed(2)}`;
            if (document.getElementById('bar_flight')) document.getElementById('bar_flight').textContent = (flightCurr === 'PKR') ? `PKR ${flightPkr.toFixed(2)}` : `${flightCurr} ${flightForeign.toFixed(2)} / PKR ${flightPkr.toFixed(2)}`;
            if (document.getElementById('bar_hotel')) document.getElementById('bar_hotel').textContent = (hotelCurrLabel === 'PKR') ? `PKR ${hotelSellPkr.toFixed(2)}` : `${hotelCurrLabel} ${hotelSellForeign.toFixed(2)} / PKR ${hotelSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_transport')) document.getElementById('bar_transport').textContent = (transportCurrLabel === 'PKR') ? `PKR ${transportSellPkr.toFixed(2)}` : `${transportCurrLabel} ${transportSellForeign.toFixed(2)} / PKR ${transportSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_visa')) document.getElementById('bar_visa').textContent = (visaCurrLabel === 'PKR') ? `PKR ${visaSellPkr.toFixed(2)}` : `${visaCurrLabel} ${visaSellForeign.toFixed(2)} / PKR ${visaSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_total')) document.getElementById('bar_total').textContent = (activeCurrs.size === 0 && flightCurr === 'PKR') ? `PKR ${totalPkr.toFixed(2)}` : `${foreignUnit} ${totalForeign.toFixed(2)} / PKR ${totalPkr.toFixed(2)}`;
            if (document.getElementById('bar_balance')) document.getElementById('bar_balance').textContent = (activeCurrs.size === 0 && flightCurr === 'PKR') ? `PKR ${balancePkr.toFixed(2)}` : `${foreignUnit} ${balanceForeign.toFixed(2)} / PKR ${balancePkr.toFixed(2)}`;

            // Costing Dual Currency Cards
            if (document.getElementById('cost_foreign_curr_title')) document.getElementById('cost_foreign_curr_title').textContent = (activeCurrs.size === 1) ? Array.from(activeCurrs)[0] : (activeCurrs.size > 1 ? 'Multi-Curr' : flightCurr);
            if (document.getElementById('cost_sar_flight')) document.getElementById('cost_sar_flight').textContent = `${flightForeign.toFixed(2)} ${flightCurr}`;
            if (document.getElementById('cost_sar_hotel')) document.getElementById('cost_sar_hotel').textContent = `${hotelSellForeign.toFixed(2)} ${hotelCurrLabel}`;
            if (document.getElementById('cost_sar_transport')) document.getElementById('cost_sar_transport').textContent = `${transportSellForeign.toFixed(2)} ${transportCurrLabel}`;
            if (document.getElementById('cost_sar_visa')) document.getElementById('cost_sar_visa').textContent = `${visaSellForeign.toFixed(2)} ${visaCurrLabel}`;
            if (document.getElementById('cost_sar_total')) document.getElementById('cost_sar_total').textContent = `${totalForeign.toFixed(2)} ${foreignUnit}`;
            if (document.getElementById('cost_sar_balance')) document.getElementById('cost_sar_balance').textContent = `${balanceForeign.toFixed(2)} ${foreignUnit}`;

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
            if (e.target.matches('.hotel-nights, .hotel-rooms, .hotel-roe, .hotel-cost-roe, .hotel-sale-roe, .hotel-cost-rate, .hotel-sell-rate, .hotel-person-cost, .hotel-person-sell')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            }
            if (e.target.matches('.visa-roe, .visa-cost-roe, .visa-sale-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-roe, .transport-sale-roe, .transport-cost-rate, .transport-sell-rate')) {
                const row = e.target.closest('.route-block');
                calculateTransportRow(row);
            }
            if (e.target.matches('.flight-rate-calc, .flight-tier-calc, #flight_roe, #flight_cost_roe, #flight_sale_roe, .person-flight-pax-type')) {
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
            if (e.target.matches('.hotel-nights, .hotel-rooms, .hotel-roe, .hotel-cost-roe, .hotel-sale-roe, .hotel-cost-rate, .hotel-sell-rate, .hotel-person-cost, .hotel-person-sell')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            }
            if (e.target.matches('.visa-roe, .visa-cost-roe, .visa-sale-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-roe, .transport-sale-roe, .transport-cost-rate, .transport-sell-rate')) {
                const row = e.target.closest('.route-block');
                calculateTransportRow(row);
            }
            if (e.target.matches('.flight-rate-calc, .flight-tier-calc, #flight_roe, #flight_cost_roe, #flight_sale_roe, .person-flight-pax-type')) {
                calculateFlight();
            }
            if (e.target.matches('.calc, #package_cost, #other_charges, #discount, #additional_services_amount, #total_received')) {
                calcTotal();
            }
            if (e.target.matches('input[name*="[check_in]"], input[name*="[check_out]"], input[name*="[no_of_nights]"]')) {
                handleHotelDateCalc(e.target);
            }
        });

        // Quick Apply Adult Rate Handler
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.id === 'btnQuickApplyAdults' || e.target.closest('#btnQuickApplyAdults'))) {
                e.preventDefault();
                const costVal = parseFloat(document.getElementById('quick_adult_cost')?.value) || 0;
                const sellVal = parseFloat(document.getElementById('quick_adult_sell')?.value) || 0;

                document.querySelectorAll('.flight-person-row').forEach(row => {
                    const type = row.querySelector('.person-flight-pax-type')?.value;
                    if (type === 'adult' || !type) {
                        const cInput = row.querySelector('.person-flight-cost');
                        if (cInput && costVal > 0) cInput.value = costVal.toFixed(2);
                        const sInput = row.querySelector('.person-flight-sell');
                        if (sInput && sellVal > 0) sInput.value = sellVal.toFixed(2);
                    }
                });
                calculateFlight();
            }
        });

        // Quick Apply Hotel Pax Handler
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-quick-hotel-apply');
            if (btn) {
                e.preventDefault();
                const hotelBlock = btn.closest('.hotel-block');
                if (!hotelBlock) return;
                const costVal = hotelBlock.querySelector('.quick-hotel-cost')?.value;
                const sellVal = hotelBlock.querySelector('.quick-hotel-sell')?.value;

                hotelBlock.querySelectorAll('.hotel-person-cost').forEach(cInput => {
                    if (costVal !== '' && !isNaN(parseFloat(costVal))) cInput.value = parseFloat(costVal).toFixed(2);
                });
                hotelBlock.querySelectorAll('.hotel-person-sell').forEach(sInput => {
                    if (sellVal !== '' && !isNaN(parseFloat(sellVal))) sInput.value = parseFloat(sellVal).toFixed(2);
                });
                calculateHotelRow(hotelBlock);
            }
        });

        // Initialize on page load
        syncFlightPersons();
        syncAllHotelPersons();
        document.querySelectorAll('.hotel-block').forEach(r => calculateHotelRow(r));
        document.querySelectorAll('.route-block').forEach(r => calculateTransportRow(r));
        document.querySelectorAll('.visa-card').forEach(r => calculateVisaRow(r));
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
        });

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
                check_out: checkOut,
                booking_id: '{{ $booking->id }}'
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

        document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
            calculateFlight();
            calcTotal();

            const clientSelect = document.getElementById('clientSelect');
            if (clientSelect && !clientSelect.value) {
                e.preventDefault();
                alert('Please select a Client before updating the booking.');
                const tabBtn = document.querySelector('a[href="#tab-details"]');
                if (tabBtn) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                        new bootstrap.Tab(tabBtn).show();
                    } else {
                        tabBtn.click();
                    }
                }
                clientSelect.focus();
                return false;
            }
        });
    </script>
 
@endsection

 
    