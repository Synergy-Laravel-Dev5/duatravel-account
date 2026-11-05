@extends('layout.master')
@section('title', 'Edit Booking')

@section('content')
@php
    $isHajj = (($booking->package_type ?? '') === 'hajj');
    $isDomestic = (($booking->booking_category ?? '') === 'domestic');
    $isInternational = (($booking->booking_category ?? '') === 'international');
    $defaultCat = $booking->booking_category ?? ($isHajj ? 'hajj' : 'umrah');
    $bCat = old('booking_category', $defaultCat);
    $isUmrah = !$isHajj;
    $reqTab = request('tab') ?: old('active_tab', ($isUmrah ? '#tab-details' : '#tab-package'));
    if (!str_starts_with($reqTab, '#')) {
        $reqTab = '#' . $reqTab;
    }
@endphp
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                    <input type="hidden" name="active_tab" id="active_tab" value="{{ $reqTab }}">
                    <input type="hidden" name="save_action" id="save_action" value="save_stay">

                    {{-- TAB NAV --}}
                    <ul class="nav nav-tabs booking-tabs mb-0" id="bookingTabs" role="tablist">
                        <li class="nav-item" id="tabNavPackage" style="{{ $isUmrah ? 'display:none;' : '' }}">
                            <a class="nav-link {{ $reqTab === '#tab-package' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-package">
                                <i class="mdi mdi-tag me-1"></i>Package
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $reqTab === '#tab-details' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-details">
                                <i class="mdi mdi-account me-1"></i>Booking Details
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link {{ $reqTab === '#tab-persons' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-persons"><i
                                    class="mdi mdi-account-group me-1"></i>Persons</a></li>
                        <li class="nav-item"><a class="nav-link {{ $reqTab === '#tab-flight' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-flight"><i
                                    class="mdi mdi-airplane me-1"></i>Flight</a></li>
                        <li class="nav-item"><a class="nav-link {{ $reqTab === '#tab-hotel' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-hotel"><i
                                    class="mdi mdi-hotel me-1"></i>Hotel</a></li>
                        <li class="nav-item"><a class="nav-link {{ $reqTab === '#tab-transport' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-transport"><i
                                    class="mdi mdi-bus me-1"></i>Transport</a></li>
                        <li class="nav-item"><a class="nav-link {{ $reqTab === '#tab-visa' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-visa"><i
                                    class="mdi mdi-passport me-1"></i>Visa</a></li>
                        <li class="nav-item"><a class="nav-link {{ $reqTab === '#tab-costing' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-costing"><i
                                    class="mdi mdi-cash me-1"></i>Costing</a></li>
                        <li class="nav-item"><a class="nav-link {{ $reqTab === '#tab-additional-services' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-additional-services"><i
                                    class="mdi mdi-plus-circle me-1"></i>Additional Services</a></li>
                    </ul>

                    <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white">

                        {{-- TAB 1: Package --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-package' ? 'show active' : '' }}" id="tab-package">
                            <!-- TOP ROW: Booking Date (First Field) & Package Info -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-secondary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-secondary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date_package_tab" id="booking_date_package_tab" class="form-control form-control-sm"
                                        value="{{ old('booking_date_package_tab', $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    <small class="text-muted fs-11">Package Service Booking Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-tag text-secondary me-1"></i> Package Selection
                                    </label>
                                    <div>
                                        <span class="badge bg-secondary fs-13 px-2 py-1 font-monospace">Package Booking</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                        <span>Select Package <span class="text-danger">*</span></span>
                                        <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshPackagesDropdown(true)" title="Reload Packages"><i class="mdi mdi-refresh"></i> Refresh</button>
                                    </label>
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
                                    <input type="hidden" name="package_type" id="package_type" value="{{ $booking->package_type ?? 'umrah' }}">
                                </div>

                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                    <i class="mdi mdi-content-save me-1"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 2: Booking Details --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-details' ? 'show active' : '' }}" id="tab-details">
                            <!-- TOP ROW: Booking Date (First Field) & Main Booking Voucher -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-primary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date" id="booking_date"
                                        class="form-control form-control-sm sync-booking-date"
                                        value="{{ old('booking_date', $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d')) }}" required>
                                    <small class="text-muted fs-11">Primary Booking Date (Synchronized across all tabs)</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-ticket-confirmation text-primary me-1"></i> Booking Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-primary fs-13 px-2 py-1 font-monospace" id="booking_voucher_disp">{{ $booking->voucher_number ?? ('BK-' . date('Y') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT)) }}</span>
                                    </div>
                                    <input type="hidden" name="voucher_number" id="booking_voucher_input" value="{{ $booking->voucher_number ?? ('BK-' . date('Y') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT)) }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3">

                                {{-- BOOKING CATEGORY / SECTOR TYPE (Umrah vs Domestic vs International) --}}
                                @if($isUmrah && ($booking->package_type ?? '') !== 'hajj')
                                <div class="col-12" id="bookingCategoryContainer">
                                    <div class="card border mb-2 bg-light shadow-sm">
                                        <div class="card-body p-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <span class="fw-bold text-dark fs-13">
                                                <i class="mdi mdi-format-list-bulleted-type text-primary me-1"></i> Booking Category / Sector:
                                            </span>
                                            <div class="d-flex flex-wrap gap-3">
                                                @php
                                                    $bCat = old('booking_category', $defaultCat);
                                                @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_umrah" value="umrah" {{ $bCat === 'umrah' ? 'checked' : '' }} onchange="applyCategoryToHotels(this.value)">
                                                    <label class="form-check-label fs-13 fw-semibold text-primary" for="bcat_umrah">
                                                        <i class="mdi mdi-mosque me-1"></i> Umrah Pilgrimage
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_domestic" value="domestic" {{ $bCat === 'domestic' ? 'checked' : '' }} onchange="applyCategoryToHotels(this.value)">
                                                    <label class="form-check-label fs-13 fw-semibold text-success" for="bcat_domestic">
                                                        <i class="mdi mdi-airplane me-1"></i> Domestic Flight (Pakistan)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_international" value="international" {{ $bCat === 'international' ? 'checked' : '' }} onchange="applyCategoryToHotels(this.value)">
                                                    <label class="form-check-label fs-13 fw-semibold text-info" for="bcat_international">
                                                        <i class="mdi mdi-earth me-1"></i> International Flight
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <input type="hidden" name="booking_category" value="{{ $booking->booking_category ?? 'hajj' }}">
                                <input type="hidden" name="package_type" value="{{ $booking->package_type ?? 'hajj' }}">
                                @endif
                                <input type="hidden" name="booking_for" id="booking_for_input" value="client">

                                {{-- CLIENT BLOCK (Primary Selection) --}}
                                <div class="col-md-4" id="clientBlock">
                                    <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                        <span>Client Name <span class="text-danger">*</span></span>
                                        <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshClientsDropdown(true)" title="Reload Clients"><i class="mdi mdi-refresh"></i> Refresh</button>
                                    </label>
                                    <select name="client_id" id="clientSelect" class="form-select select2">
                                        <option value="">-- Select Client --</option>
                                        @foreach ($clients as $cl)
                                            <option value="{{ $cl->id }}"
                                                data-name="{{ $cl->name }}"
                                                data-phone="{{ $cl->phone }}"
                                                data-cnic="{{ $cl->cnic }}"
                                                data-passport="{{ $cl->passport_number }}"
                                                data-email="{{ $cl->email }}"
                                                data-company="{{ $cl->company_name }}"
                                                data-emergency="{{ $cl->emergency_phone ?? '' }}"
                                                {{ old('client_id', $booking->client_id) == $cl->id ? 'selected' : '' }}>
                                                {{ $cl->name }}{{ $cl->company_name ? ' (' . $cl->company_name . ')' : '' }} - {{ $cl->phone ?? $cl->cnic }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-primary"><i class="mdi mdi-account-group me-1"></i> No. of Pax (Passengers) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-primary"><i class="mdi mdi-account-multiple"></i></span>
                                        <input type="number" name="no_of_pax" id="no_of_pax" class="form-control fw-bold fs-15 text-primary"
                                            value="{{ old('no_of_pax', $booking->no_of_pax ?: 1) }}" min="1" placeholder="Enter number of passengers">
                                    </div>
                                    @php
                                        $bTotalPax = (int) ($booking->no_of_pax ?: 1);
                                        $bChildren = (int) ($booking->children_count ?? 0);
                                        $bInfants  = (int) ($booking->infants_count ?? 0);
                                        $bAdults   = (int) ($booking->adults_count ?? 0);
                                        if (($bAdults + $bChildren + $bInfants) !== $bTotalPax) {
                                            $bAdults = max(1, $bTotalPax - $bChildren - $bInfants);
                                        }
                                    @endphp
                                    <input type="hidden" name="adults_count" id="pax_adults" value="{{ old('adults_count', $bAdults) }}">
                                    <input type="hidden" name="children_count" id="pax_children" value="{{ old('children_count', $bChildren) }}">
                                    <input type="hidden" name="infants_count" id="pax_infants" value="{{ old('infants_count', $bInfants) }}">
                                    <input type="hidden" name="pax_adults" id="hidden_pax_adults" value="{{ old('pax_adults', $bAdults) }}">
                                    <input type="hidden" name="pax_children" id="hidden_pax_children" value="{{ old('pax_children', $bChildren) }}">
                                    <input type="hidden" name="pax_infants" id="hidden_pax_infants" value="{{ old('pax_infants', $bInfants) }}">
                                    <small class="text-muted"><i class="mdi mdi-information-outline"></i> Set total passengers here. In the <strong>Persons</strong> tab, select whether each passenger is an Adult, Child, or Infant.</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Care Of</label>
                                    <input type="text" name="care_of" class="form-control"
                                        value="{{ old('care_of', $booking->care_of) }}" placeholder="Guardian / Agent">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Passport Number</label>
                                    <input type="text" name="passport_number" id="fill_passport" class="form-control"
                                        value="{{ old('passport_number', $booking->passport_number ?: ($booking->client->passport_number ?? '')) }}" placeholder="Passport #">
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
                                    <input type="text" name="emergency_phone" id="fill_emergency_phone" class="form-control"
                                        value="{{ old('emergency_phone', $booking->emergency_phone) }}" placeholder="+92 300 0000000">
                                </div>
                                <input type="hidden" name="currency" id="booking_currency" value="{{ $booking->currency ?? "PKR" }}">
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    @if(!$isUmrah)
                                        <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                                class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-next">Next <i
                                            class="mdi mdi-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 3: Persons --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-persons' ? 'show active' : '' }}" id="tab-persons">
                            <!-- TOP ROW: Booking Date (First Field) & Passengers Overview -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-primary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date_persons_tab" id="booking_date_persons_tab" class="form-control form-control-sm"
                                        value="{{ old('booking_date_persons_tab', $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    <small class="text-muted fs-11">Passenger Booking Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-account-group text-primary me-1"></i> Passenger Breakdown
                                    </label>
                                    <div>
                                        <span class="badge bg-primary fs-12">Total Pax: <span id="bar_pax_badge_header">{{ max(1, (int)($booking->no_of_pax ?: 1)) }}</span></span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">
                                <i class="mdi mdi-information me-1"></i>
                                Rows are based on No. of Pax. Main passenger is linked to client details.
                            </p>
                            <div class="p-2 px-3 mb-3 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <strong class="text-primary fs-13"><i class="mdi mdi-account-group me-1"></i> Passenger Details & Category</strong>
                                    <small class="text-muted d-block" style="font-size:11px;">Select whether each passenger is Adult, Child, or Infant. Flight, Hotel, Visa, and Costing tabs will automatically reflect their category.</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary fs-12">Total Pax: <span id="bar_pax_badge">{{ max(1, (int)($booking->no_of_pax ?: 1)) }}</span></span>
                                    <span class="badge bg-secondary fs-12">Adults: <span id="bar_pax_adults">{{ $bAdults }}</span></span>
                                    <span class="badge bg-info text-dark fs-12">Children: <span id="bar_pax_children">{{ $bChildren }}</span></span>
                                    <span class="badge bg-warning text-dark fs-12">Infants: <span id="bar_pax_infants">{{ $bInfants }}</span></span>
                                </div>
                            </div>
                            <div id="personsList">
                                @php
                                    $bPersonsList = $booking->persons;
                                    $bTotalPax = max(1, (int)($booking->no_of_pax ?: 1));
                                @endphp
                                @for ($i = 0; $i < $bTotalPax; $i++)
                                    @php
                                        $person = $bPersonsList[$i] ?? new \App\Models\BookingPerson();
                                    @endphp
    <div class="person-card" id="person_card_{{ $i }}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-primary" style="font-size:13px;">
                {{ $i === 0 ? 'Main Passenger' : 'Passenger ' . ($i + 1) }}
            </strong>
            <span class="badge {{ ($person->pax_type ?? 'adult') === 'child' ? 'bg-info text-dark' : (($person->pax_type ?? 'adult') === 'infant' ? 'bg-warning text-dark' : 'bg-primary') }} person-type-badge" id="person_type_badge_{{ $i }}">{{ ucfirst($person->pax_type ?? 'adult') }}</span>
        </div>
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:12px;">Passenger Type <span class="text-danger">*</span></label>
                <select name="persons[{{ $i }}][pax_type]" id="person_pax_type_{{ $i }}" class="form-select form-select-sm person-pax-type-select" onchange="onPersonPaxTypeChange({{ $i }}, this.value)">
                    <option value="adult" {{ ($person->pax_type ?? 'adult') === 'adult' ? 'selected' : '' }}>Adult (12+ yrs)</option>
                    <option value="child" {{ ($person->pax_type ?? '') === 'child' ? 'selected' : '' }}>Child (2-11 yrs)</option>
                    <option value="infant" {{ ($person->pax_type ?? '') === 'infant' ? 'selected' : '' }}>Infant (0-2 yrs)</option>
                </select>
            </div>
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
                    class="form-control form-control-sm" value="{{ old('persons.'.$i.'.passport_number', $person->passport_number ?: ($i === 0 ? ($booking->passport_number ?: ($booking->client->passport_number ?? '')) : '')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Date of Birth</label>
                <input type="date" name="persons[{{ $i }}][dob]" id="person_dob_{{ $i }}"
                    class="form-control form-control-sm" value="{{ $person->dob ? \Carbon\Carbon::parse($person->dob)->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Gender</label>
                <select name="persons[{{ $i }}][gender]" id="person_gender_{{ $i }}" class="form-select form-select-sm">
                    <option value="">-- Select --</option>
                    <option value="male" {{ strtolower($person->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ strtolower($person->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="col-md-3">
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
@endfor
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-next">Next <i
                                            class="mdi mdi-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 4: Flight --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-flight' ? 'show active' : '' }}" id="tab-flight">

                            <!-- TOP ROW: Booking Date Sync & Flight Voucher No -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-info shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-info me-1"></i> Booking Date
                                    </label>
                                    <input type="date" name="flight_booking_date" id="flight_booking_date" class="form-control form-control-sm"
                                        value="{{ old('flight_booking_date', $booking->flight_booking_date ? \Carbon\Carbon::parse($booking->flight_booking_date)->format('Y-m-d') : ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d'))) }}">
                                    <small class="text-muted fs-11">Auto-syncs with Flight Departure Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-airplane text-info me-1"></i> Flight Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-info fs-13 px-2 py-1 font-monospace">{{ $booking->flight_voucher_no ?? ($defaultVouchers['flight'] ?? ('FLT-' . date('Y') . '-00001')) }}</span>
                                    </div>
                                    <input type="hidden" name="flight_voucher_no" id="flight_voucher_no" value="{{ $booking->flight_voucher_no ?? ($defaultVouchers['flight'] ?? ('FLT-' . date('Y') . '-00001')) }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-airplane-takeoff me-1"></i> Flight Date Auto-Sync Active
                                    </span>
                                </div>
                            </div>

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
                                            <select name="flight_supplier_id" id="flight_supplier_id" class="form-select select2">
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
                                    </div>

                                    {{-- Master Flight Rates & Fast Fill Card --}}
                                    <div class="card bg-soft-primary border border-primary border-opacity-25 rounded-3 mb-3 p-3 shadow-none">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom border-primary border-opacity-10">
                                            <div>
                                                <h6 class="text-primary fw-bold mb-0">
                                                    <i class="mdi mdi-ticket-percent-outline me-1"></i> Master Flight Rates & Fast Auto-Fill
                                                </h6>
                                                <small class="text-muted fs-11">Set standard rates here and click "Apply to All Passengers" to fill all tickets instantly without typing each passenger individually.</small>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm fw-semibold" id="btnCascadeFlightRates">
                                                <i class="mdi mdi-arrow-down-bold-circle me-1"></i> Apply Rates & Details to All Passengers
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label fs-12 fw-bold text-dark mb-1">
                                                    Adult Ticket Rate (<span class="flight-curr-label">{{ $flightCurr }}</span>)
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white text-danger fw-semibold">Cost</span>
                                                    <input type="number" step="0.01" name="flight_adult_cost" id="flight_adult_cost" class="form-control text-end master-flight-rate" placeholder="0.00" value="{{ old('flight_adult_cost', $booking->flight_adult_cost ?? $booking->flight_cost_rate ?? 0) }}">
                                                    <span class="input-group-text bg-white text-success fw-semibold">Sell</span>
                                                    <input type="number" step="0.01" name="flight_adult_sell" id="flight_adult_sell" class="form-control text-end master-flight-rate" placeholder="0.00" value="{{ old('flight_adult_sell', $booking->flight_adult_sell ?? $booking->flight_selling_rate ?? 0) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fs-12 fw-bold text-dark mb-1">
                                                    Child Ticket Rate (<span class="flight-curr-label">{{ $flightCurr }}</span>)
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white text-danger fw-semibold">Cost</span>
                                                    <input type="number" step="0.01" name="flight_child_cost" id="flight_child_cost" class="form-control text-end master-flight-rate" placeholder="0.00" value="{{ old('flight_child_cost', $booking->flight_child_cost ?? 0) }}">
                                                    <span class="input-group-text bg-white text-success fw-semibold">Sell</span>
                                                    <input type="number" step="0.01" name="flight_child_sell" id="flight_child_sell" class="form-control text-end master-flight-rate" placeholder="0.00" value="{{ old('flight_child_sell', $booking->flight_child_sell ?? 0) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fs-12 fw-bold text-dark mb-1">
                                                    Infant Ticket Rate (<span class="flight-curr-label">{{ $flightCurr }}</span>)
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white text-danger fw-semibold">Cost</span>
                                                    <input type="number" step="0.01" name="flight_infant_cost" id="flight_infant_cost" class="form-control text-end master-flight-rate" placeholder="0.00" value="{{ old('flight_infant_cost', $booking->flight_infant_cost ?? 0) }}">
                                                    <span class="input-group-text bg-white text-success fw-semibold">Sell</span>
                                                    <input type="number" step="0.01" name="flight_infant_sell" id="flight_infant_sell" class="form-control text-end master-flight-rate" placeholder="0.00" value="{{ old('flight_infant_sell', $booking->flight_infant_sell ?? 0) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                    <div id="flightPersonsList">
                                        <div class="p-2 px-3 mb-2 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <div>
                                                <span class="fw-bold text-dark fs-12"><i class="mdi mdi-airplane me-1 text-primary"></i> Passenger-Wise Flight & Ticket Management:</span>
                                                <small class="text-muted d-block" style="font-size:11px;">Each passenger can have unique tickets, separate suppliers, Departure & Return tickets/costs/sells, custom flight details (Date/Time/PNR), and rates.</small>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <div class="input-group input-group-sm" style="width: auto;">
                                                    <span class="input-group-text bg-white fs-11">Adult Rate (<span class="flight-curr-label">{{ $flightCurr }}</span>):</span>
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
                                                        <th style="width: 14%;">Passenger & Passport</th>
                                                        <th style="width: 15%;">Pax Type & Trip</th>
                                                        <th style="width: 13%;">Supplier & PNR</th>
                                                        <th style="width: 18%;">Departure Ticket (Cost / Sell)</th>
                                                        <th style="width: 18%;">Return Ticket (Cost / Sell)</th>
                                                        <th style="width: 11%;" class="text-end">Total Rate (<span class="flight-curr-label">{{ $flightCurr }}</span>)</th>
                                                        <th style="width: 11%;">Status & Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="flightPersonsTableBody">
                                                    @php
                                                        $paxCount = max(1, (int)($booking->no_of_pax ?: 1), $booking->persons->count());
                                                        $fCurr = $flightCurr;
                                                        $fCostRoe = ($fCurr === 'PKR') ? 1 : (float)($flightCostRoe ?: 75);
                                                        $fSaleRoe = ($fCurr === 'PKR') ? 1 : (float)($flightSaleRoe ?: 75);
                                                    @endphp
                                                    @for($i = 0; $i < $paxCount; $i++)
                                                        @php
                                                            $pExisting = $booking->persons[$i] ?? null;
                                                            $pName = $pExisting->full_name ?? ($i === 0 ? ($booking->client_name ?? ($booking->client->name ?? 'Passenger 1')) : 'Passenger ' . ($i + 1));
                                                            $pPass = $pExisting->passport_number ?? ($i === 0 ? ($booking->passport_number ?? ($booking->client->passport_number ?? '')) : '');
                                                            $pType = $pExisting->pax_type ?? 'adult';
                                                            $pTripType = $pExisting->trip_type ?? 'round_trip';
                                                            $pDepTicket = $pExisting->departure_ticket ?? ($pExisting->flight_ticket ?? '');
                                                            $pDepCost = ($pExisting && (float)$pExisting->departure_cost > 0) ? $pExisting->departure_cost : '';
                                                            $pDepSell = ($pExisting && (float)$pExisting->departure_sell > 0) ? $pExisting->departure_sell : '';
                                                            $pRetTicket = $pExisting->return_ticket ?? '';
                                                            $pRetCost = ($pExisting && (float)$pExisting->return_cost > 0) ? $pExisting->return_cost : '';
                                                            $pRetSell = ($pExisting && (float)$pExisting->return_sell > 0) ? $pExisting->return_sell : '';
                                                            $pTicket = $pExisting->flight_ticket ?? $pDepTicket;
                                                            $pStatus = $pExisting->ticket_status ?? 'confirmed';
                                                            $pCost = ($pExisting && (float)$pExisting->flight_cost > 0) ? $pExisting->flight_cost : '';
                                                            $pSell = ($pExisting && (float)$pExisting->flight_sell > 0) ? $pExisting->flight_sell : '';
                                                            $mainSupId = $booking->flight_supplier_id ?? '';
                                                            $mainSupName = $suppliers->firstWhere('id', $mainSupId)->name ?? '';
                                                            $hasCustomSup = !empty($pExisting->flight_supplier_id) && $mainSupId && ($pExisting->flight_supplier_id != $mainSupId);
                                                            $pSupVal = $hasCustomSup ? $pExisting->flight_supplier_id : '';

                                                            $hasCustomPnr = !empty($pExisting->flight_pnr) && ($booking->departure_pnr ?? '') && ($pExisting->flight_pnr != $booking->departure_pnr);
                                                            $pPnr = $hasCustomPnr ? $pExisting->flight_pnr : ($booking->departure_pnr ?? '');

                                                            $mainDepDate = $booking->departure_date ? \Carbon\Carbon::parse($booking->departure_date)->format('Y-m-d') : '';
                                                            $hasCustomDate = !empty($pExisting->flight_date) && $mainDepDate && ($pExisting->flight_date != $mainDepDate);
                                                            $pDate = $hasCustomDate ? $pExisting->flight_date : $mainDepDate;

                                                            $hasCustomTime = !empty($pExisting->flight_time) && ($booking->departure_time ?? '') && ($pExisting->flight_time != $booking->departure_time);
                                                            $pTime = $hasCustomTime ? $pExisting->flight_time : ($booking->departure_time ?? '');

                                                            $defaultTrip = ($booking->flight_trip_type ?? 'round') === 'oneway' ? 'departure_only' : 'round_trip';
                                                            $hasCustomTrip = !empty($pExisting->trip_type) && ($pExisting->trip_type != $defaultTrip);
                                                            $pTripType = $hasCustomTrip ? $pExisting->trip_type : ($pExisting->trip_type ?? $defaultTrip);

                                                            if ($pCost === '' && $pDepCost === '' && $pRetCost === '') {
                                                                if ((float)$booking->flight_adult_cost > 0) $pCost = $booking->flight_adult_cost;
                                                                elseif ((float)$booking->flight_cost_rate > 0) $pCost = number_format((float)$booking->flight_cost_rate / $paxCount, 2, '.', '');
                                                                elseif ((float)$booking->flight_cost > 0 && $fCostRoe > 0) $pCost = number_format((float)$booking->flight_cost / ($paxCount * $fCostRoe), 2, '.', '');
                                                            }
                                                            if ($pSell === '' && $pDepSell === '' && $pRetSell === '') {
                                                                if ((float)$booking->flight_adult_sell > 0) $pSell = $booking->flight_adult_sell;
                                                                elseif ((float)$booking->flight_selling_rate > 0) $pSell = number_format((float)$booking->flight_selling_rate / $paxCount, 2, '.', '');
                                                                elseif ((float)$booking->flight_charges > 0 && $fSaleRoe > 0) $pSell = number_format((float)$booking->flight_charges / ($paxCount * $fSaleRoe), 2, '.', '');
                                                            }
                                                            $cVal = (float)$pCost ?: (((float)$pDepCost) + ((float)$pRetCost));
                                                            $sVal = (float)$pSell ?: (((float)$pDepSell) + ((float)$pRetSell));
                                                            $isCancelled = ($pStatus === 'cancelled');
                                                            $isRefunded = ($pStatus === 'refunded');
                                                        @endphp
                                                        <tr class="flight-person-row {{ $isCancelled ? 'table-danger' : ($isRefunded ? 'table-warning' : '') }}" data-idx="{{ $i }}" id="flight_person_row_{{ $i }}" style="{{ $isCancelled ? 'opacity:0.65; text-decoration: line-through;' : '' }}">
                                                            <td class="text-center text-muted fw-bold">{{ $i + 1 }}</td>
                                                            <td>
                                                                <strong class="text-primary person-flight-name-disp">{{ $pName }}</strong>
                                                                <input type="hidden" name="flight_persons[{{ $i }}][name]" class="person-flight-name" value="{{ $pName }}">
                                                                <div class="mt-1">
                                                                    <input type="text" name="flight_persons[{{ $i }}][passport]" class="form-control form-control-sm person-flight-passport" value="{{ $pPass }}" placeholder="Passport #">
                                                                </div>
                                                            </td>
                                                             <td>
                                                                <select name="flight_persons[{{ $i }}][pax_type]" class="form-select form-select-sm person-flight-pax-type" onchange="onFlightPaxTypeChange({{ $i }}, this.value)">
                                                                    <option value="adult" {{ $pType === 'adult' ? 'selected' : '' }}>Adult</option>
                                                                    <option value="child" {{ $pType === 'child' ? 'selected' : '' }}>Child</option>
                                                                    <option value="infant" {{ $pType === 'infant' ? 'selected' : '' }}>Infant</option>
                                                                </select>
                                                                <select name="flight_persons[{{ $i }}][trip_type]" class="form-select form-select-sm person-flight-trip-type mt-1 border-primary" onchange="onTripTypeChange({{ $i }}, this.value); onPersonFlightTravelInput(this);" data-customized="{{ $hasCustomTrip ? 'true' : 'false' }}" title="Choose Trip Type">
                                                                    <option value="round_trip" {{ $pTripType === 'round_trip' ? 'selected' : '' }}>Round Trip</option>
                                                                    <option value="departure_only" {{ $pTripType === 'departure_only' ? 'selected' : '' }}>Departure Only (One Way)</option>
                                                                    <option value="return_only" {{ $pTripType === 'return_only' ? 'selected' : '' }}>Return Only (Inbound)</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="flight_persons[{{ $i }}][flight_supplier_id]" class="form-select form-select-sm person-flight-supplier" data-customized="{{ $hasCustomSup ? 'true' : 'false' }}" onchange="onPersonFlightSupplierChange(this, {{ $i }})" title="Departure Supplier">
                                                                    <option value="">-- Dep: Same as Main {{ $mainSupName ? '(' . $mainSupName . ')' : 'Supplier' }} --</option>
                                                                    @foreach($suppliers as $s)
                                                                        <option value="{{ $s->id }}" {{ $s->id == $pSupVal ? 'selected' : '' }}>{{ $s->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <select name="flight_persons[{{ $i }}][return_flight_supplier_id]" class="form-select form-select-sm person-flight-ret-supplier mt-1" title="Return Supplier">
                                                                    <option value="">-- Ret: Same as Dep Supplier --</option>
                                                                    @foreach($suppliers as $s)
                                                                        <option value="{{ $s->id }}" {{ ($pExisting->return_flight_supplier_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <span class="badge {{ $hasCustomSup ? 'bg-soft-info text-primary border border-info border-opacity-25' : 'bg-light text-muted border' }} fs-10 mt-1 d-inline-block person-supplier-badge" id="person_sup_badge_{{ $i }}">
                                                                    <i class="mdi {{ $hasCustomSup ? 'mdi-account-cog' : 'mdi-link-variant' }} me-1"></i>{{ $hasCustomSup ? 'Individual Supplier' : 'Linked to Main Supplier' }}
                                                                </span>
                                                                <input type="text" name="flight_persons[{{ $i }}][flight_pnr]" class="form-control form-control-sm person-flight-pnr mt-1 {{ $hasCustomPnr ? 'border-info' : '' }}" value="{{ $pPnr }}" placeholder="PNR #" data-customized="{{ $hasCustomPnr ? 'true' : 'false' }}" oninput="onPersonFlightTravelInput(this)">
                                                            </td>
                                                            <td class="box-dep-ticket {{ $pTripType === 'return_only' ? 'opacity-25' : '' }}">
                                                                <div class="mb-1">
                                                                    <input type="text" name="flight_persons[{{ $i }}][departure_ticket]" class="form-control form-control-sm person-flight-ticket person-flight-dep-ticket" value="{{ $pDepTicket ?: $pTicket }}" placeholder="Departure Ticket #" maxlength="13" {{ $pTripType === 'return_only' ? 'disabled' : '' }}>
                                                                </div>
                                                                <div class="d-flex gap-1">
                                                                    <input type="number" step="0.01" name="flight_persons[{{ $i }}][departure_cost]" class="form-control form-control-sm text-end person-flight-dep-cost flight-rate-calc" value="{{ $pDepCost }}" placeholder="Cost" title="Departure Cost" {{ $pTripType === 'return_only' ? 'disabled' : '' }}>
                                                                    <input type="number" step="0.01" name="flight_persons[{{ $i }}][departure_sell]" class="form-control form-control-sm text-end person-flight-dep-sell flight-rate-calc" value="{{ $pDepSell }}" placeholder="Sell" title="Departure Sell" {{ $pTripType === 'return_only' ? 'disabled' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td class="box-ret-ticket {{ $pTripType === 'departure_only' ? 'opacity-25' : '' }}">
                                                                <div class="mb-1">
                                                                    <input type="text" name="flight_persons[{{ $i }}][return_ticket]" class="form-control form-control-sm person-flight-ticket person-flight-ret-ticket" value="{{ $pRetTicket }}" placeholder="Return Ticket #" maxlength="13" {{ $pTripType === 'departure_only' ? 'disabled' : '' }}>
                                                                </div>
                                                                <div class="d-flex gap-1">
                                                                    <input type="number" step="0.01" name="flight_persons[{{ $i }}][return_cost]" class="form-control form-control-sm text-end person-flight-ret-cost flight-rate-calc" value="{{ $pRetCost }}" placeholder="Cost" title="Return Cost" {{ $pTripType === 'departure_only' ? 'disabled' : '' }}>
                                                                    <input type="number" step="0.01" name="flight_persons[{{ $i }}][return_sell]" class="form-control form-control-sm text-end person-flight-ret-sell flight-rate-calc" value="{{ $pRetSell }}" placeholder="Sell" title="Return Sell" {{ $pTripType === 'departure_only' ? 'disabled' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input type="hidden" name="flight_persons[{{ $i }}][ticket]" class="person-flight-ticket-main" value="{{ $pTicket ?: ($pDepTicket ?: $pRetTicket) }}">
                                                                <input type="number" step="0.01" name="flight_persons[{{ $i }}][flight_cost]" class="form-control form-control-sm text-end person-flight-cost flight-rate-calc fw-bold text-danger bg-light" value="{{ $pCost ?: ($cVal > 0 ? number_format($cVal, 2, '.', '') : '') }}" placeholder="0.00" readonly>
                                                                <small class="text-danger d-block text-end person-flight-cost-pkr-disp" style="font-size:11px;">{{ number_format($cVal * $fCostRoe, 2) }} PKR</small>
                                                                <input type="number" step="0.01" name="flight_persons[{{ $i }}][flight_sell]" class="form-control form-control-sm text-end person-flight-sell flight-rate-calc fw-bold text-success bg-light mt-1" value="{{ $pSell ?: ($sVal > 0 ? number_format($sVal, 2, '.', '') : '') }}" placeholder="0.00" readonly>
                                                                <small class="text-success d-block text-end person-flight-sell-pkr-disp" style="font-size:11px;">{{ number_format($sVal * $fSaleRoe, 2) }} PKR</small>
                                                            </td>
                                                            <td>
                                                                <select name="flight_persons[{{ $i }}][ticket_status]" class="form-select form-select-sm person-flight-status mb-1 {{ $isCancelled ? 'bg-soft-danger text-danger border-danger fw-bold' : ($isRefunded ? 'bg-soft-warning text-warning border-warning fw-bold' : 'text-success fw-bold') }}" onchange="handleTicketStatusChange(this)">
                                                                    <option value="confirmed" {{ $pStatus === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                                    <option value="cancelled" {{ $pStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                                    <option value="refunded" {{ $pStatus === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                                </select>
                                                                <input type="date" name="flight_persons[{{ $i }}][flight_date]" class="form-control form-control-sm person-flight-date {{ $hasCustomDate ? 'border-info' : '' }}" value="{{ $pDate }}" title="Flight date" data-customized="{{ $hasCustomDate ? 'true' : 'false' }}" onchange="onPersonFlightTravelInput(this)">
                                                                <input type="text" name="flight_persons[{{ $i }}][flight_time]" class="form-control form-control-sm person-flight-time flight-time-picker mt-1 {{ $hasCustomTime ? 'border-info' : '' }}" value="{{ $pTime }}" placeholder="hh:mm" title="Flight time" data-customized="{{ $hasCustomTime ? 'true' : 'false' }}" onchange="onPersonFlightTravelInput(this)">
                                                            </td>
                                                        </tr>
                                                    @endfor
                                                </tbody>
                                                <tfoot class="table-light fw-bold fs-12 border-top">
                                                    <tr>
                                                        <td colspan="6" class="text-dark">Total Flight Tickets ({{ $paxCount }} Passengers)</td>
                                                        <td class="text-end text-danger">
                                                            <span id="tbl_flight_cost_curr_total">{{ $flightCurr === 'PKR' ? number_format($booking->flight_cost ?? 0, 2) . ' PKR' : number_format(($booking->flight_cost_roe > 0 ? $booking->flight_cost / $booking->flight_cost_roe : 0), 2) . ' ' . $flightCurr }}</span>
                                                            <br><small class="text-danger fw-normal" id="tbl_flight_cost_pkr_total">{{ number_format($booking->flight_cost ?? 0, 2) }} PKR</small>
                                                        </td>
                                                        <td class="text-end text-success">
                                                            <span id="tbl_flight_sell_curr_total">{{ $flightCurr === 'PKR' ? number_format($booking->flight_charges ?? 0, 2) . ' PKR' : number_format(($booking->flight_sale_roe > 0 ? $booking->flight_charges / $booking->flight_sale_roe : 0), 2) . ' ' . $flightCurr }}</span>
                                                            <br><small class="text-success fw-normal" id="tbl_flight_sell_pkr_total">{{ number_format($booking->flight_charges ?? 0, 2) }} PKR</small>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Flight Dual Currency Totals Display --}}
                                    <div class="row g-2 p-2 rounded bg-light border">
                                        <div class="col-md-3">
                                            <div class="border rounded p-2 bg-white text-center">
                                                <small class="text-muted d-block">Flight Cost ({{ $flightCurr === 'PKR' ? 'Foreign' : $flightCurr }})</small>
                                                <strong class="text-danger fs-14" id="flight_cost_sar_total">{{ $flightCurr === 'PKR' ? '—' : number_format(($booking->flight_cost_roe > 0 ? $booking->flight_cost / $booking->flight_cost_roe : 0), 2) . ' ' . $flightCurr }}</strong>
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
                                                <small class="text-muted d-block">Flight Sell ({{ $flightCurr === 'PKR' ? 'Foreign' : $flightCurr }})</small>
                                                <strong class="text-success fs-14" id="flight_sell_sar_total">{{ $flightCurr === 'PKR' ? '—' : number_format(($booking->flight_sale_roe > 0 ? $booking->flight_charges / $booking->flight_sale_roe : 0), 2) . ' ' . $flightCurr }}</strong>
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
                                </div>
                            </div>

                            {{-- DEPARTURE & TIME FORMAT (Point 6) --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12 d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2">
                                    <h6 class="text-dark fw-bold mb-0">
                                        <i class="mdi mdi-airplane-takeoff text-primary me-1"></i> Outbound Flight Schedule (Departure)
                                        @if(!empty($booking->flight_voucher_no))
                                            <span class="badge bg-soft-info text-info ms-2 fs-12"><i class="mdi mdi-ticket-confirmation me-1"></i>Voucher: {{ $booking->flight_voucher_no }}</span>
                                        @endif
                                    </h6>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center gap-2 me-2">
                                            <span class="fw-bold fs-12 text-dark"><i class="mdi mdi-swap-horizontal me-1 text-primary"></i> Trip Type:</span>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input flight-trip-type-radio" type="radio" name="flight_trip_type" id="trip_type_round" value="round" {{ old('flight_trip_type', $booking->flight_trip_type ?? 'round') !== 'oneway' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-12 fw-semibold text-primary" for="trip_type_round"><i class="mdi mdi-repeat me-1"></i>Round-Trip</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input flight-trip-type-radio" type="radio" name="flight_trip_type" id="trip_type_oneway" value="oneway" {{ old('flight_trip_type', $booking->flight_trip_type ?? 'round') === 'oneway' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-12 fw-semibold text-secondary" for="trip_type_oneway"><i class="mdi mdi-arrow-right-bold me-1"></i>One-Way</label>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold fs-12 text-dark"><i class="mdi mdi-clock-outline me-1"></i> Time:</span>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input flight-time-format-radio" type="radio" name="flight_time_format" id="time_format_12" value="12" {{ old('flight_time_format', $booking->flight_time_format ?? '12') == '12' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-12 fw-semibold" for="time_format_12">12-Hour</label>
                                            </div>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input flight-time-format-radio" type="radio" name="flight_time_format" id="time_format_24" value="24" {{ old('flight_time_format', $booking->flight_time_format ?? '12') == '24' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-12 fw-semibold" for="time_format_24">24-Hour</label>
                                            </div>
                                        </div>
                                    </div>
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
                                    <input type="text" name="departure_time" id="departure_time_input" class="form-control flight-time-picker"
                                        value="{{ old('departure_time', $booking->departure_time) }}" placeholder="hh:mm AM/PM">
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

                            {{-- RETURN FLIGHT / ARRIVAL SECTION --}}
                            <div class="row g-3 mb-4" id="returnFlightSection" style="{{ old('flight_trip_type', $booking->flight_trip_type ?? 'round') === 'oneway' ? 'display:none;' : '' }}">
                                <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <h6 class="text-primary fw-bold mb-0">
                                        <i class="mdi mdi-airplane-landing me-1"></i> Inbound Flight Schedule (Return Flight / Arrival Back)
                                    </h6>
                                    <span class="badge bg-soft-primary text-primary px-2 py-1 fs-11">
                                        <i class="mdi mdi-repeat me-1"></i> Return Journey
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Return</label>
                                    <input type="date" name="arrival_date" class="form-control"
                                        value="{{ old('arrival_date', $booking->arrival_date) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Return Flight #</label>
                                    <input type="text" name="arrival_flight" class="form-control"
                                        value="{{ old('arrival_flight', $booking->arrival_flight) }}"
                                        placeholder="PK-302">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time of Return</label>
                                    <input type="text" name="arrival_time" id="arrival_time_input" class="form-control flight-time-picker"
                                        value="{{ old('arrival_time', $booking->arrival_time) }}" placeholder="hh:mm AM/PM">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Return Airline</label>
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
                                    <label class="form-label d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-semibold text-primary"><i class="mdi mdi-office-building me-1"></i> Return Flight Supplier (If Different)</span>
                                    </label>
                                    <select name="return_flight_supplier_id" id="return_flight_supplier_id" class="form-select select2">
                                        <option value="">-- Same as Departure Flight Supplier --</option>
                                        @foreach ($suppliers as $sup)
                                            <option value="{{ $sup->id }}" {{ old('return_flight_supplier_id', $booking->return_flight_supplier_id ?? '') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted fs-11">Agar return flight ka vendor alag hai toh select karein</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Return PNR / Ticket #</label>
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


                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="mdi mdi-arrow-left me-1"></i> Prev
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-next">
                                        Next <i class="mdi mdi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 5: Hotel --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-hotel' ? 'show active' : '' }}" id="tab-hotel">
                            <!-- TOP ROW: Booking Date (First Field) & Hotel Voucher No -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-primary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date_hotel_tab" id="booking_date_hotel_tab"
                                        class="form-control form-control-sm"
                                        value="{{ old('booking_date_hotel_tab', !empty($booking->hotels[0]->booking_date) ? \Carbon\Carbon::parse($booking->hotels[0]->booking_date)->format('Y-m-d') : ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d'))) }}">
                                    <small class="text-muted fs-11">Hotel Service Booking Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-hotel text-success me-1"></i> Hotel Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-success fs-13 px-2 py-1 font-monospace">{{ $booking->hotel_voucher_no ?? ($defaultVouchers['hotel'] ?? ('HTL-' . date('Y') . '-00001')) }}</span>
                                    </div>
                                    <input type="hidden" name="hotel_voucher_no" id="hotel_voucher_no" value="{{ $booking->hotel_voucher_no ?? ($defaultVouchers['hotel'] ?? ('HTL-' . date('Y') . '-00001')) }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>

                            {{-- HOTEL ACCOMMODATION SUMMARY TABLE (As per user request Screenshot) --}}
                            <div id="hotelAccommodationSummaryBox" class="card border mb-3 shadow-sm">
                                <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 fs-13 fw-bold" style="color: #c2410c;">
                                        <i class="mdi mdi-bed text-warning me-1"></i> 🏨 Hotel | Accomodation
                                    </h6>
                                    <span class="badge bg-soft-warning text-dark border fs-11" id="hotel_summary_badge">Summary View</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0 text-center" style="font-size: 12px;">
                                        <thead class="fw-bold text-uppercase" style="background-color: #fff7ed; color: #c2410c;">
                                            <tr>
                                                <th class="text-start" style="color: #c2410c;">Hotel Name</th>
                                                <th style="color: #c2410c;">City</th>
                                                <th style="color: #c2410c;">Check In</th>
                                                <th style="color: #c2410c;">Check Out</th>
                                                <th style="color: #c2410c;">Nights</th>
                                                <th style="color: #c2410c;">Room Type</th>
                                                <th style="color: #c2410c;">QTY</th>
                                                <th style="color: #c2410c;">Meal Plan</th>
                                                <th style="color: #c2410c;" class="text-end">Total Cost</th>
                                                <th style="color: #c2410c;" class="text-end">Total Sale</th>
                                            </tr>
                                        </thead>
                                        <tbody id="hotelAccommodationSummaryRows">
                                            @forelse($booking->hotels as $hotel)
                                                @php
                                                    $hName = $hotel->hotel_name ?? '—';
                                                    $city = strtoupper($hotel->location ?? 'MAKKAH');
                                                    $cin = $hotel->check_in ? \Carbon\Carbon::parse($hotel->check_in)->format('Y-m-d') : '—';
                                                    $cout = $hotel->check_out ? \Carbon\Carbon::parse($hotel->check_out)->format('Y-m-d') : '—';
                                                    $nights = (int)($hotel->no_of_nights ?? 0);
                                                    $roomType = strtoupper($hotel->room_type ?? 'DOUBLE');
                                                    $rooms = (int)($hotel->no_of_rooms ?? 1);
                                                    $mealPlan = strtoupper($hotel->meal_plan ?? 'BB');
                                                    $curr = $hotel->currency ?? 'SAR';
                                                    $costForeign = ((float)($hotel->cost_per_night ?? 0)) * ($nights ?: 1) * ($rooms ?: 1);
                                                    $costPkr = (float)($hotel->cost_amount ?: ($curr === 'PKR' ? $costForeign : ($costForeign * ($hotel->cost_roe ?: 75))));
                                                    $sellForeign = ((float)($hotel->selling_per_night ?? 0)) * ($nights ?: 1) * ($rooms ?: 1);
                                                    $sellPkr = (float)($hotel->selling_amount ?: ($curr === 'PKR' ? $sellForeign : ($sellForeign * ($hotel->sale_roe ?: 75))));
                                                @endphp
                                                <tr>
                                                    <td class="text-start fw-bold text-dark">{{ $hName }}</td>
                                                    <td class="fw-semibold">{{ $city }}</td>
                                                    <td>{{ $cin }}</td>
                                                    <td>{{ $cout }}</td>
                                                    <td class="fw-bold text-dark">{{ $nights }}</td>
                                                    <td>{{ $roomType }}</td>
                                                    <td>{{ $rooms }}</td>
                                                    <td>{{ $mealPlan }}</td>
                                                    <td class="text-end text-danger fw-semibold">
                                                        <span>{{ number_format($costForeign, 2) }} {{ $curr }}</span><br>
                                                        <small class="text-muted fw-normal">{{ number_format($costPkr, 2) }} PKR</small>
                                                    </td>
                                                    <td class="text-end text-success fw-semibold">
                                                        <span>{{ number_format($sellForeign, 2) }} {{ $curr }}</span><br>
                                                        <small class="text-muted fw-normal">{{ number_format($sellPkr, 2) }} PKR</small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-muted py-2">No hotels added yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
@php
    $initHotelCostSar = 0;
    $initHotelCostPkr = 0;
    $initHotelSellSar = 0;
    $initHotelSellPkr = 0;
    $initTotalNights = 0;
    $initTotalRooms = 0;
    $initMinIn = null;
    $initMaxOut = null;
    $initCurrs = [];

    if ($booking->hotels) {
        foreach ($booking->hotels as $bh) {
            $bNights = (int)($bh->no_of_nights ?: 1);
            $bRooms  = (int)($bh->no_of_rooms ?: 1);
            $bCurr   = $bh->currency ?: 'SAR';
            $bCostR  = (float)($bh->cost_roe ?: 75);
            $bSaleR  = (float)($bh->sale_roe ?: 75);
            $bCostForeign = ((float)$bh->cost_per_night) * $bNights * $bRooms;
            $bSellForeign = ((float)$bh->selling_per_night) * $bNights * $bRooms;
            $bCostPkr = (float)($bh->cost_amount ?: ($bCurr === 'PKR' ? $bCostForeign : ($bCostForeign * $bCostR)));
            $bSellPkr = (float)($bh->selling_amount ?: ($bCurr === 'PKR' ? $bSellForeign : ($bSellForeign * $bSaleR)));

            $initHotelCostSar += $bCostForeign;
            $initHotelCostPkr += $bCostPkr;
            $initHotelSellSar += $bSellForeign;
            $initHotelSellPkr += $bSellPkr;
            $initTotalNights  += $bNights;
            $initTotalRooms   += $bRooms;
            $initCurrs[$bCurr] = true;

            if ($bh->check_in && (!$initMinIn || $bh->check_in < $initMinIn)) $initMinIn = $bh->check_in;
            if ($bh->check_out && (!$initMaxOut || $bh->check_out > $initMaxOut)) $initMaxOut = $bh->check_out;
        }
    }
    $initHotelCurrLabel = count($initCurrs) === 1 ? array_keys($initCurrs)[0] : (count($initCurrs) > 1 ? 'Mix' : 'SAR');
@endphp
                                        <tfoot class="fw-bold bg-white">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 fw-bold">Total</td>
                                                <td id="hotel_summary_total_nights" class="fw-bold text-dark fs-13">{{ $initTotalNights }}</td>
                                                <td colspan="3"></td>
                                                <td id="hotel_summary_total_cost" class="text-end text-danger fw-bold fs-12">{{ number_format($initHotelCostSar, 2) }} {{ $initHotelCurrLabel }}</td>
                                                <td id="hotel_summary_total_sell" class="text-end text-success fw-bold fs-12">{{ number_format($initHotelSellSar, 2) }} {{ $initHotelCurrLabel }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- Hotel Dual Currency Totals Display --}}
                            <div class="row g-2 p-2 rounded bg-light border mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Cost (SAR)</small>
                                        <strong class="text-danger fs-14" id="hotel_cost_sar_total">{{ number_format($initHotelCostSar, 2) }} {{ $initHotelCurrLabel }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Cost (PKR)</small>
                                        <strong class="text-danger fs-14" id="hotel_cost_pkr_total">{{ number_format($initHotelCostPkr, 2) }} PKR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Sell (SAR)</small>
                                        <strong class="text-success fs-14" id="hotel_sell_sar_total">{{ number_format($initHotelSellSar, 2) }} {{ $initHotelCurrLabel }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Hotel Sell (PKR)</small>
                                        <strong class="text-success fs-14" id="hotel_sell_pkr_total">{{ number_format($initHotelSellPkr, 2) }} PKR</strong>
                                    </div>
                                </div>
                                {{-- Point 10: Hotel Stay Summary --}}
                                <div class="col-12 mt-2">
                                    <div class="p-2 px-3 border rounded bg-white d-flex flex-wrap justify-content-between align-items-center shadow-sm">
                                        <div><i class="mdi mdi-calendar-range text-primary me-1"></i> <strong class="text-dark">Hotel Stay Summary:</strong> <span id="hotel_stay_summary_dates" class="badge {{ $initMinIn && $initMaxOut ? 'bg-success text-white' : 'bg-soft-primary text-primary' }} fs-12 ms-1">{{ $initMinIn && $initMaxOut ? ($initMinIn . ' to ' . $initMaxOut) : ($initMinIn ? 'Check-in: ' . $initMinIn : 'No check-in dates set') }}</span></div>
                                        <div class="text-muted small"><span id="hotel_stay_total_nights" class="fw-semibold text-dark">{{ $initTotalNights }}</span> Total Nights &bull; <span id="hotel_stay_total_rooms" class="fw-semibold text-dark">{{ $initTotalRooms }}</span> Total Rooms</div>
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
                                    <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="{{ $loop->index }}" data-location="{{ $hotel->location }}" data-city="{{ strtoupper($hotel->location ?? 'MAKKAH') }}" data-person-rates='@json($hotel->person_rates ?? [])'>
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="text-primary mb-0 fw-bold">
                                                    <i class="mdi mdi-hotel me-1"></i>
                                                    {{ $hotel->location === 'makkah' ? 'Makkah Hotel' : ($hotel->location === 'madinah' ? 'Madinah Hotel' : 'Other Hotel') }}
                                                </h6>
                                                @php
                                                    $hChildVoucher = $hotel->voucher_no ?? (($booking->hotel_voucher_no ?: ($defaultVouchers['hotel'] ?? ('HTL-' . date('Y') . '-00001'))) . '-' . ($loop->iteration));
                                                @endphp
                                                <span class="badge bg-soft-success text-success border border-success fs-12 px-2 py-1 font-monospace">
                                                    <i class="mdi mdi-ticket-confirmation me-1"></i>Voucher #: {{ $hChildVoucher }}
                                                </span>
                                                <input type="hidden" name="hotels[{{ $loop->index }}][voucher_no]" value="{{ $hChildVoucher }}">
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-hotel" onclick="removeHotelBlock(this)">
                                                <i class="mdi mdi-delete-outline me-1"></i> Remove
                                            </button>
                                        </div>
                                        <input type="hidden" name="hotels[{{ $loop->index }}][location]"
                                            value="{{ $hotel->location }}">

                                        {{-- TOP ROW: Booking Information & Single-Line Dates (Point 7) --}}
                                        <div class="p-2 mb-3 rounded bg-light border">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-5">
                                                    <label class="form-label fw-bold text-dark mb-1"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Stay Period (Check In & Check Out)</label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-white text-primary fw-semibold"><i class="mdi mdi-login me-1"></i> Check In</span>
                                                        <input type="date" name="hotels[{{ $loop->index }}][check_in]" class="form-control form-control-sm hotel-checkin" value="{{ old('hotels.'.$loop->index.'.check_in', $hotel->check_in) }}" onchange="calculateHotelNights({{ $loop->index }})">
                                                        <span class="input-group-text bg-white text-danger fw-semibold"><i class="mdi mdi-logout me-1"></i> Check Out</span>
                                                        <input type="date" name="hotels[{{ $loop->index }}][check_out]" class="form-control form-control-sm hotel-checkout" value="{{ old('hotels.'.$loop->index.'.check_out', $hotel->check_out) }}" onchange="calculateHotelNights({{ $loop->index }})">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label mb-1">Nights</label>
                                                    <input type="number" name="hotels[{{ $loop->index }}][no_of_nights]" class="form-control form-control-sm hotel-nights"
                                                        value="{{ old('hotels.'.$loop->index.'.no_of_nights', $hotel->no_of_nights ?: 1) }}" min="1"
                                                        oninput="calculateHotelRow(this.closest('.hotel-block'), 'nights')"
                                                        onchange="calculateHotelRow(this.closest('.hotel-block'), 'nights')">
                                                </div>
                                                <input type="hidden" name="hotels[{{ $loop->index }}][booking_date]" class="hotel-booking-date sync-booking-date" value="{{ old('hotels.'.$loop->index.'.booking_date', $hotel->booking_date ? \Carbon\Carbon::parse($hotel->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                                <div class="col-md-3">
                                                    <label class="form-label text-warning fw-bold mb-1"><i class="mdi mdi-calendar-clock me-1 text-warning"></i> Option Date</label>
                                                    <input type="date" name="hotels[{{ $loop->index }}][option_date]" class="form-control form-control-sm hotel-option-date border-warning" value="{{ old('hotels.'.$loop->index.'.option_date', $hotel->option_date ? \Carbon\Carbon::parse($hotel->option_date)->format('Y-m-d') : '') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Hotel Name</label>
                                                <select name="hotels[{{ $loop->index }}][hotel_name]" class="form-select hotel-name-select select2" onchange="updateHotelStaySummary()">
                                                    <option value="">Select Hotel</option>
                                                    @foreach($hotels as $h)
                                                        @php
                                                            $hPlace = strtolower($h->city ?? (is_object($h->place) ? $h->place->value : ($h->place ?? '')));
                                                            $loc = strtolower($hotel->location ?? '');
                                                            $domPlaces = ['karachi', 'lahore', 'islamabad', 'rawalpindi', 'peshawar', 'multan', 'faisalabad', 'quetta', 'sialkot'];
                                                            $pilgrimPlaces = ['makkah', 'madinah', 'azizia', 'mina/arafat', 'jeddah'];
                                                            if ($bCat === 'domestic') {
                                                                $isDom = false;
                                                                foreach ($domPlaces as $dp) { if (str_contains($hPlace, $dp)) { $isDom = true; break; } }
                                                                if (!$isDom) continue;
                                                            } elseif ($bCat === 'international') {
                                                                $isPilgrim = false;
                                                                $isDom = false;
                                                                foreach ($pilgrimPlaces as $pp) { if (str_contains($hPlace, $pp)) { $isPilgrim = true; break; } }
                                                                foreach ($domPlaces as $dp) { if (str_contains($hPlace, $dp)) { $isDom = true; break; } }
                                                                if ($isPilgrim || $isDom) continue;
                                                            } else {
                                                                if ($loc === 'makkah' && !str_contains($hPlace, 'makkah')) continue;
                                                                if ($loc === 'madinah' && !str_contains($hPlace, 'madinah')) continue;
                                                            }
                                                        @endphp
                                                        <option value="{{ $h->name }}" {{ old('hotels.'.$loop->index.'.hotel_name', $hotel->hotel_name) == $h->name ? 'selected' : '' }}>{{ $h->name }} ({{ $h->city ?? $h->place }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Hotel Supplier / Vendor</label>
                                                <select name="hotels[{{ $loop->index }}][supplier_id]" class="form-select hotel-supplier-select select2">
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
                                                <select name="hotels[{{ $loop->index }}][currency]" class="form-select hotel-currency fw-bold"
                                                    onchange="calculateHotelRow(this.closest('.hotel-block'))">
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
                                                        class="form-control hotel-cost-roe" value="{{ $hCostRoe }}" {{ $isHPkr ? 'readonly' : '' }}
                                                        oninput="calculateHotelRow(this.closest('.hotel-block'))"
                                                        onchange="calculateHotelRow(this.closest('.hotel-block'))">
                                                </div>
                                                <input type="hidden" name="hotels[{{ $loop->index }}][roe]" class="hotel-roe" value="{{ $hCostRoe }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-bold">Sale ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light hotel-curr-prefix">1 {{ $hCurr }} =</span>
                                                    <input type="number" step="0.0001" name="hotels[{{ $loop->index }}][sale_roe]"
                                                        class="form-control hotel-sale-roe" value="{{ $hSaleRoe }}" {{ $isHPkr ? 'readonly' : '' }}
                                                        oninput="calculateHotelRow(this.closest('.hotel-block'))"
                                                        onchange="calculateHotelRow(this.closest('.hotel-block'))">
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
                                                <label class="form-label">No. of Rooms</label>
                                                <input type="number" name="hotels[{{ $loop->index }}][no_of_rooms]"
                                                    class="form-control hotel-rooms" value="{{ old('hotels.'.$loop->index.'.no_of_rooms', $hotel->no_of_rooms ?: 1) }}"
                                                    min="1"
                                                    oninput="calculateHotelRow(this.closest('.hotel-block'), 'rooms')"
                                                    onchange="calculateHotelRow(this.closest('.hotel-block'), 'rooms')">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Hotel Room #</label>
                                                <input type="text" name="hotels[{{ $loop->index }}][room_number]"
                                                    class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505"
                                                    value="{{ old('hotels.'.$loop->index.'.room_number', $hotel->room_number) }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold"><i class="mdi mdi-silverware me-1 text-primary"></i> Meal Plan</label>
                                                <select name="hotels[{{ $loop->index }}][meal_plan]" class="form-select hotel-meal-plan" onchange="updateHotelStaySummary()">
                                                    @foreach($mealPlans as $mp)
                                                        <option value="{{ $mp->code }}" {{ old('hotels.'.$loop->index.'.meal_plan', $hotel->meal_plan ?? 'BB') == $mp->code ? 'selected' : '' }}>
                                                            {{ $mp->name }} ({{ $mp->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold"><i class="mdi mdi-eye me-1 text-info"></i> Room View</label>
                                                <select name="hotels[{{ $loop->index }}][room_view]" class="form-select hotel-room-view">
                                                    @foreach(['Standard View', 'City View', 'Haram View', 'Kaaba View', 'Partial Haram View', 'Sea / Mountain View'] as $rv)
                                                        <option value="{{ $rv }}" {{ old('hotels.'.$loop->index.'.room_view', $hotel->room_view ?? 'Standard View') == $rv ? 'selected' : '' }}>
                                                            {{ $rv }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][cost_per_night]"
                                                    class="form-control hotel-cost-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_per_night', $hotel->cost_per_night ?? 0) }}"
                                                    oninput="autoDivideHotelRates(this.closest('.hotel-block'), 'cost'); calculateHotelRow(this.closest('.hotel-block'), 'costRate')"
                                                    onchange="autoDivideHotelRates(this.closest('.hotel-block'), 'cost'); calculateHotelRow(this.closest('.hotel-block'), 'costRate')">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][selling_per_night]"
                                                    class="form-control hotel-sell-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.selling_per_night', $hotel->selling_per_night ?? 0) }}"
                                                    oninput="autoDivideHotelRates(this.closest('.hotel-block'), 'sell'); calculateHotelRow(this.closest('.hotel-block'), 'sellRate')"
                                                    onchange="autoDivideHotelRates(this.closest('.hotel-block'), 'sell'); calculateHotelRow(this.closest('.hotel-block'), 'sellRate')">
                                            </div>

                                            {{-- Main Cost Division & Additional Rooms Controls (Points 8 & 9) --}}
                                            <div class="col-12">
                                                <div class="d-flex flex-wrap align-items-center justify-content-between p-2 rounded bg-light border gap-2">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="divideHotelMainCost({{ $loop->index }})" title="Divide main cost equally among passengers">
                                                            <i class="mdi mdi-calculator me-1"></i> Divide / Allocate Main Cost
                                                        </button>
                                                        <button type="button" class="btn btn-xs btn-outline-success" onclick="addAdditionalRoomToHotel({{ $loop->index }})">
                                                            <i class="mdi mdi-plus-circle me-1"></i> + Add Additional Room
                                                        </button>
                                                    </div>
                                                    <div>
                                                        @if(!empty($hotel->voucher_no))
                                                            <span class="badge bg-soft-primary text-primary fs-11"><i class="mdi mdi-ticket-confirmation me-1"></i>Voucher #: {{ $hotel->voucher_no }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                {{-- Container for Additional Rooms --}}
                                                <div class="additional-rooms-container mt-2" id="additional_rooms_list_{{ $loop->index }}">
                                                    @php
                                                        $extraRooms = is_array($hotel->additional_rooms) ? $hotel->additional_rooms : (json_decode($hotel->additional_rooms ?? '', true) ?: []);
                                                        $extraCostPerNight = 0;
                                                        $extraSellPerNight = 0;
                                                        if (!empty($extraRooms)) {
                                                            foreach ($extraRooms as $xr) {
                                                                $extraCostPerNight += (float)($xr['cost_rate'] ?? 0);
                                                                $extraSellPerNight += (float)($xr['sell_rate'] ?? 0);
                                                            }
                                                        }
                                                        $hNights = (int)($hotel->no_of_nights ?? 1);
                                                        $hRooms = (int)($hotel->no_of_rooms ?? 1);
                                                        $hCostPerNight = (float)($hotel->cost_per_night ?? 0);
                                                        $hSellPerNight = (float)($hotel->selling_per_night ?? 0);
                                                        $totalCostCurr = $hNights * (($hRooms * $hCostPerNight) + $extraCostPerNight);
                                                        $totalSellCurr = $hNights * (($hRooms * $hSellPerNight) + $extraSellPerNight);
                                                    @endphp
                                                    @if(!empty($extraRooms))
                                                        @foreach($extraRooms as $rIdx => $rItem)
                                                            <div class="border rounded p-2 mb-2 bg-light additional-room-item" id="add_room_{{ $loop->parent->index }}_{{ $rIdx }}">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <strong class="text-primary fs-12"><i class="mdi mdi-door"></i> Additional Room</strong>
                                                                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('add_room_{{ $loop->parent->index }}_{{ $rIdx }}').remove(); calculateHotelCost(document.querySelector('.hotel-block[data-hotel-idx=\'{{ $loop->parent->index }}\']')); updateHotelStaySummary();">×</button>
                                                                </div>
                                                                <div class="row g-2">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label fs-11 mb-0">Room Type</label>
                                                                        <select name="hotels[{{ $loop->parent->index }}][extra_rooms][{{ $rIdx }}][room_type]" class="form-select form-select-sm">
                                                                            @foreach(['single' => 'Single', 'double' => 'Double', 'triple' => 'Triple', 'quad' => 'Quad', 'sharing' => 'Sharing', 'suite' => 'Suite'] as $rtK => $rtL)
                                                                                <option value="{{ $rtK }}" {{ ($rItem['room_type'] ?? '') == $rtK ? 'selected' : '' }}>{{ $rtL }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label fs-11 mb-0">Room #</label>
                                                                        <input type="text" name="hotels[{{ $loop->parent->index }}][extra_rooms][{{ $rIdx }}][room_number]" class="form-control form-control-sm" value="{{ $rItem['room_number'] ?? '' }}" placeholder="e.g. 502">
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label fs-11 mb-0">Cost Rate / Night ({{ $hCurr }})</label>
                                                                        <input type="number" step="0.01" name="hotels[{{ $loop->parent->index }}][extra_rooms][{{ $rIdx }}][cost_rate]" class="form-control form-control-sm add-room-cost" value="{{ $rItem['cost_rate'] ?? 0 }}" oninput="calculateHotelRow(this.closest('.hotel-block'))" onchange="calculateHotelRow(this.closest('.hotel-block'))">
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label fs-11 mb-0">Sell Rate / Night ({{ $hCurr }})</label>
                                                                        <input type="number" step="0.01" name="hotels[{{ $loop->parent->index }}][extra_rooms][{{ $rIdx }}][sell_rate]" class="form-control form-control-sm add-room-sell" value="{{ $rItem['sell_rate'] ?? 0 }}" oninput="calculateHotelRow(this.closest('.hotel-block'))" onchange="calculateHotelRow(this.closest('.hotel-block'))">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Total Cost (<span class="hotel-curr-label">{{ $hCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-danger"><span class="hotel-curr-name">{{ $hCurr }}:</span><strong class="hotel-cost-sar-disp">{{ number_format($totalCostCurr, 2) }} {{ $hCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="hotel-cost-pkr-disp">{{ number_format($hotel->cost_amount ?? ($totalCostCurr * ($hotel->cost_roe ?? 75)), 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="hotels[{{ $loop->index }}][cost_amount]"
                                                    class="hotel-cost-total"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_amount', $hotel->cost_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Total Sell (<span class="hotel-curr-label">{{ $hCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-success"><span class="hotel-curr-name">{{ $hCurr }}:</span><strong class="hotel-sell-sar-disp">{{ number_format($totalSellCurr, 2) }} {{ $hCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="hotel-sell-pkr-disp">{{ number_format($hotel->selling_amount ?? ($totalSellCurr * ($hotel->sale_roe ?? 75)), 2) }} PKR</strong></div>
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
                                                        @php
                                                            $hIdx = $loop->index;
                                                            $hNights = max(1, (int)($hotel->no_of_nights ?: 1));
                                                            $hSavedRates = is_array($hotel->person_rates) ? $hotel->person_rates : (json_decode($hotel->person_rates ?? '[]', true) ?: []);
                                                            $hPersonsList = $booking->persons;
                                                            $hPaxCount = max(1, (int)($booking->no_of_pax ?: ($hPersonsList ? $hPersonsList->count() : 1)));
                                                            $hDefaultCost = (float)($hotel->cost_per_night ?: 0);
                                                            $hDefaultSell = (float)($hotel->selling_per_night ?: 0);
                                                            $hRenderedCostTotCurr = 0;
                                                            $hRenderedSellTotCurr = 0;
                                                        @endphp
                                                        <tbody class="hotel-persons-tbody">
                                                            @for($p = 0; $p < $hPaxCount; $p++)
                                                                @php
                                                                    $pObj = $hPersonsList[$p] ?? null;
                                                                    $pName = $pObj ? ($pObj->full_name ?: trim(($pObj->given_name ?? '') . ' ' . ($pObj->surname ?? ''))) : "Passenger " . ($p + 1);
                                                                    if (empty($pName)) $pName = "Passenger " . ($p + 1);
                                                                    $pSaved = $hSavedRates[$p] ?? null;
                                                                    $pCost = isset($pSaved['cost_per_night']) ? $pSaved['cost_per_night'] : (isset($pSaved['cost']) ? $pSaved['cost'] : $hDefaultCost);
                                                                    $pSell = isset($pSaved['selling_per_night']) ? $pSaved['selling_per_night'] : (isset($pSaved['sell']) ? $pSaved['sell'] : $hDefaultSell);
                                                                    $hRenderedCostTotCurr += ((float)$pCost * $hNights);
                                                                    $hRenderedSellTotCurr += ((float)$pSell * $hNights);
                                                                @endphp
                                                                <tr class="hotel-person-row" data-person-idx="{{ $p }}">
                                                                    <td class="text-center fw-bold">{{ $p + 1 }}</td>
                                                                    <td>
                                                                        <span class="hotel-person-name fw-semibold text-dark">{{ $pName }}</span>
                                                                        <input type="hidden" name="hotels[{{ $hIdx }}][person_rates][{{ $p }}][name]" value="{{ $pName }}">
                                                                    </td>
                                                                    <td class="text-center font-monospace">{{ $hNights }}</td>
                                                                    <td>
                                                                        <input type="number" step="0.01" name="hotels[{{ $hIdx }}][person_rates][{{ $p }}][cost_per_night]"
                                                                            class="form-control form-control-sm text-end hotel-person-cost border-danger"
                                                                            value="{{ number_format((float)$pCost, 2, '.', '') }}"
                                                                            oninput="calculateHotelRow(this.closest('.hotel-block'), 'person')"
                                                                            onchange="calculateHotelRow(this.closest('.hotel-block'), 'person')">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" step="0.01" name="hotels[{{ $hIdx }}][person_rates][{{ $p }}][selling_per_night]"
                                                                            class="form-control form-control-sm text-end hotel-person-sell border-success"
                                                                            value="{{ number_format((float)$pSell, 2, '.', '') }}"
                                                                            oninput="calculateHotelRow(this.closest('.hotel-block'), 'person')"
                                                                            onchange="calculateHotelRow(this.closest('.hotel-block'), 'person')">
                                                                    </td>
                                                                </tr>
                                                            @endfor
                                                        </tbody>
                                                        <tfoot class="table-light fw-bold fs-12 border-top">
                                                            <tr>
                                                                <td colspan="3" class="text-dark">Hotel Passenger Totals:</td>
                                                                <td class="text-end text-danger">
                                                                    <span class="hotel-person-tbl-cost-curr">{{ number_format($hRenderedCostTotCurr, 2) }} {{ $hCurr }}</span>
                                                                    <br><small class="text-danger fw-normal hotel-person-tbl-cost-pkr">{{ number_format($hRenderedCostTotCurr * ($hCostRoe ?: 75), 2) }} PKR</small>
                                                                </td>
                                                                <td class="text-end text-success">
                                                                    <span class="hotel-person-tbl-sell-curr">{{ number_format($hRenderedSellTotCurr, 2) }} {{ $hCurr }}</span>
                                                                    <br><small class="text-success fw-normal hotel-person-tbl-sell-pkr">{{ number_format($hRenderedSellTotCurr * ($hSaleRoe ?: 75), 2) }} PKR</small>
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
                            <div class="d-flex flex-wrap gap-2 mt-2" id="hotelActionButtons">
                                <button type="button" class="btn btn-primary btn-sm btn-hotel-makkah" id="btnAddMakkahHotel" onclick="addNewHotel('makkah')" style="{{ in_array($bCat, ['domestic', 'international']) ? 'display: none !important;' : '' }}">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add Makkah Hotel
                                </button>
                                <button type="button" class="btn btn-success btn-sm btn-hotel-madinah" id="btnAddMadinahHotel" onclick="addNewHotel('madinah')" style="{{ in_array($bCat, ['domestic', 'international']) ? 'display: none !important;' : '' }}">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add Madinah Hotel
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-hotel-other" id="btnAddOtherHotel" onclick="addNewHotel('other')">
                                    <i class="mdi mdi-plus-circle me-1"></i> <span id="lblAddOtherHotel">{{ $bCat === 'domestic' ? '+ Add Domestic Hotel' : ($bCat === 'international' ? '+ Add International Hotel' : '+ Add Other Hotel') }}</span>
                                </button>
                                <button type="button" class="btn btn-info btn-sm text-white btn-hotel-select" id="addHotel" onclick="addNewHotel('other')" style="{{ in_array($bCat, ['domestic', 'international']) ? 'display: none !important;' : '' }}">
                                    <i class="mdi mdi-plus me-1"></i> <span id="lblAddHotelSelect">+ Add Hotel (Select)</span>
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-next">Next <i
                                            class="mdi mdi-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 6: Transport --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-transport' ? 'show active' : '' }}" id="tab-transport">
                            <!-- TOP ROW: Booking Date (First Field) & Transport Voucher No -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-warning shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-warning me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date_transport_tab" id="booking_date_transport_tab" class="form-control form-control-sm"
                                        value="{{ old('booking_date_transport_tab', !empty($booking->transports[0]->booking_date) ? \Carbon\Carbon::parse($booking->transports[0]->booking_date)->format('Y-m-d') : ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d'))) }}">
                                    <small class="text-muted fs-11">Transport Service Booking Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-bus text-warning me-1"></i> Transport Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-warning text-dark fs-13 px-2 py-1 font-monospace">{{ $booking->transport_voucher_no ?? ($defaultVouchers['transport'] ?? ('TRN-' . date('Y') . '-00001')) }}</span>
                                    </div>
                                    <input type="hidden" name="transport_voucher_no" id="transport_voucher_no" value="{{ $booking->transport_voucher_no ?? ($defaultVouchers['transport'] ?? ('TRN-' . date('Y') . '-00001')) }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>

                            {{-- TRANSPORT ROUTES SUMMARY TABLE (As per user request) --}}
                            <div id="transportRoutesSummaryBox" class="card border mb-3 shadow-sm">
                                <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 fs-13 fw-bold" style="color: #b45309;">
                                        <i class="mdi mdi-bus text-warning me-1"></i> 🚌 Transport | Routes & Transfers
                                    </h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 fw-semibold" id="btnAutoFillFlightToTransport" title="Auto-populate Route 1 (Departure) and Route 2 (Return) from Flight Schedule">
                                            <i class="mdi mdi-airplane-takeoff me-1"></i> Auto-Fill from Flight
                                        </button>
                                        <span class="badge bg-soft-warning text-dark border fs-11" id="transport_summary_badge">Summary View</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0 text-center" style="font-size: 12px;">
                                        <thead class="fw-bold text-uppercase" style="background-color: #fffbeb; color: #b45309;">
                                            <tr>
                                                <th class="text-start" style="color: #b45309;">Route</th>
                                                <th style="color: #b45309;">Vehicle</th>
                                                <th style="color: #b45309;">QTY</th>
                                                <th style="color: #b45309;">Travel Date & Time</th>
                                                <th style="color: #b45309;">Pax</th>
                                                <th style="color: #b45309;">Vendor / Supplier</th>
                                                <th style="color: #b45309;">Status</th>
                                                <th style="color: #b45309;" class="text-end">Total Cost</th>
                                                <th style="color: #b45309;" class="text-end">Total Sale</th>
                                            </tr>
                                        </thead>
                                        @php
                                            $bInitTrnCostSar = 0;
                                            $bInitTrnCostPkr = 0;
                                            $bInitTrnSellSar = 0;
                                            $bInitTrnSellPkr = 0;
                                            $bInitTrnCurrs = [];
                                            foreach ($booking->transports as $tItem) {
                                                $q = (int)($tItem->quantity ?: 1);
                                                $cSar = ((float)($tItem->cost_rate ?: 0)) * $q;
                                                $sSar = ((float)($tItem->selling_rate ?: 0)) * $q;
                                                $cRoe = (float)($tItem->cost_roe ?: ($tItem->roe ?: 75));
                                                $sRoe = (float)($tItem->sale_roe ?: ($tItem->roe ?: 75));
                                                $bInitTrnCostSar += $cSar;
                                                $bInitTrnCostPkr += ($tItem->currency === 'PKR' ? $cSar : ($cSar * $cRoe));
                                                $bInitTrnSellSar += $sSar;
                                                $bInitTrnSellPkr += ($tItem->currency === 'PKR' ? $sSar : ($sSar * $sRoe));
                                                $bInitTrnCurrs[$tItem->currency ?: 'SAR'] = true;
                                            }
                                            $bInitTrnCurr = count($bInitTrnCurrs) === 1 ? array_key_first($bInitTrnCurrs) : (count($bInitTrnCurrs) > 1 ? 'Mix' : 'SAR');
                                        @endphp
                                        <tbody id="transportRoutesSummaryRows">
                                            @forelse($booking->transports as $t)
                                                @php
                                                    $rName = $t->route ?? '—';
                                                    $vName = $t->vehicle ?? '—';
                                                    $supName = $t->supplier ? ($t->supplier->name ?? 'Vendor') : '—';
                                                    $tCurr = $t->currency ?? 'SAR';
                                                    $qty = (int)($t->quantity ?? 1);
                                                    $tDate = $t->travel_date ? \Carbon\Carbon::parse($t->travel_date)->format('Y-m-d') : ($t->date ? \Carbon\Carbon::parse($t->date)->format('Y-m-d') : '—');
                                                    $tTime = $t->travel_time ?? ($t->time ?? '');
                                                    $costSar = ((float)($t->cost_rate ?? 0)) * ($qty ?: 1);
                                                    $costPkr = (float)($t->cost_amount ?: ($tCurr === 'PKR' ? $costSar : ($costSar * ($t->cost_roe ?: ($t->roe ?: 75)))));
                                                    $sellSar = ((float)($t->selling_rate ?? 0)) * ($qty ?: 1);
                                                    $sellPkr = (float)($t->selling_amount ?: ($tCurr === 'PKR' ? $sellSar : ($sellSar * ($t->sale_roe ?: ($t->roe ?: 75)))));
                                                    $status = $t->status ?? 'CONFIRMED';
                                                @endphp
                                                <tr>
                                                    <td class="text-start fw-bold text-dark">{{ $rName }}</td>
                                                    <td class="fw-semibold">{{ $vName }}</td>
                                                    <td class="fw-bold text-primary">{{ $qty }}</td>
                                                    <td>{{ $tDate }} {{ $tTime }}</td>
                                                    <td>{{ $t->pax ?? '—' }}</td>
                                                    <td>{{ $supName }}</td>
                                                    <td><span class="badge bg-{{ $status === 'CONFIRMED' ? 'success' : ($status === 'CANCELLED' ? 'danger' : 'warning') }}">{{ $status }}</span></td>
                                                    <td class="text-end text-danger fw-semibold">
                                                        <span>{{ number_format($costSar, 2) }} {{ $tCurr }}</span><br>
                                                        <small class="text-muted fw-normal">{{ number_format($costPkr, 2) }} PKR</small>
                                                    </td>
                                                    <td class="text-end text-success fw-semibold">
                                                        <span>{{ number_format($sellSar, 2) }} {{ $tCurr }}</span><br>
                                                        <small class="text-muted fw-normal">{{ number_format($sellPkr, 2) }} PKR</small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-muted py-2">No routes added yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="fw-bold bg-white">
                                            <tr>
                                                <td colspan="2" class="text-end pe-3 fw-bold">Total</td>
                                                <td id="transport_summary_total_qty" class="fw-bold text-dark fs-13">{{ $booking->transports->sum('quantity') ?: 0 }}</td>
                                                <td colspan="4"></td>
                                                <td id="transport_summary_total_cost" class="text-end text-danger fw-bold fs-12">
                                                    <span>{{ number_format($bInitTrnCostSar, 2) }} {{ $bInitTrnCurr }}</span><br>
                                                    <small class="text-muted fw-normal">{{ number_format($bInitTrnCostPkr, 2) }} PKR</small>
                                                </td>
                                                <td id="transport_summary_total_sell" class="text-end text-success fw-bold fs-12">
                                                    <span>{{ number_format($bInitTrnSellSar, 2) }} {{ $bInitTrnCurr }}</span><br>
                                                    <small class="text-muted fw-normal">{{ number_format($bInitTrnSellPkr, 2) }} PKR</small>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- Transport Dual Currency Totals Display --}}
                            <div class="row g-2 p-2 rounded bg-light border mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Cost (SAR)</small>
                                        <strong class="text-danger fs-14" id="transport_cost_sar_total">{{ number_format($bInitTrnCostSar, 2) }} {{ $bInitTrnCurr }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Cost (PKR)</small>
                                        <strong class="text-danger fs-14" id="transport_cost_pkr_total">{{ number_format($bInitTrnCostPkr, 2) }} PKR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Sell (SAR)</small>
                                        <strong class="text-success fs-14" id="transport_sell_sar_total">{{ number_format($bInitTrnSellSar, 2) }} {{ $bInitTrnCurr }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Transport Sell (PKR)</small>
                                        <strong class="text-success fs-14" id="transport_sell_pkr_total">{{ number_format($bInitTrnSellPkr, 2) }} PKR</strong>
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
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="text-dark mb-0 fw-bold"><i class="mdi mdi-bus me-1 text-warning"></i> Route #{{ $loop->iteration }}</h6>
                                                @php
                                                    $trnChildVoucher = $t->voucher_no ?? (($booking->transport_voucher_no ?: ($defaultVouchers['transport'] ?? ('TRN-' . date('Y') . '-00001'))) . '-' . ($loop->iteration));
                                                @endphp
                                                <span class="badge bg-soft-warning text-dark border border-warning fs-12 px-2 py-1 font-monospace">
                                                    <i class="mdi mdi-ticket-confirmation me-1"></i>Voucher #: {{ $trnChildVoucher }}
                                                </span>
                                                <input type="hidden" name="transports[{{ $i }}][voucher_no]" value="{{ $trnChildVoucher }}">
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-route" onclick="removeRouteBlock(this)">
                                                <i class="mdi mdi-delete-outline me-1"></i> Remove
                                            </button>
                                        </div>
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
                                                <input type="number" name="transports[{{ $i }}][quantity]" class="form-control transport-qty"
                                                    value="{{ old('transports.'.$i.'.quantity', $t->quantity ?? 1) }}" min="1"
                                                    oninput="calculateTransportRow(this.closest('.route-block'))"
                                                    onchange="calculateTransportRow(this.closest('.route-block'))">
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
                                                <select name="transports[{{ $i }}][supplier_id]" class="form-select transport-supplier-select select2">
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
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label mb-0"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Date</label>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 btn-set-transport-dep" style="font-size:10px;" title="Set Flight Departure Date">Dep</button>
                                                        <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 btn-set-transport-arr" style="font-size:10px;" title="Set Flight Arrival Date">Arr</button>
                                                    </div>
                                                </div>
                                                <input type="date" name="transports[{{ $i }}][travel_date]"
                                                    class="form-control transport-travel-date" value="{{ old('transports.'.$i.'.travel_date', $t->travel_date ? \Carbon\Carbon::parse($t->travel_date)->format('Y-m-d') : '') }}">
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
                                            {{-- <div class="col-md-6">
                                                <label class="form-label text-muted"><i class="mdi mdi-calendar-check me-1"></i> Booking Date</label>
                                                <input type="date" name="transports[{{ $i }}][booking_date]"
                                                    class="form-control" value="{{ old('transports.'.$i.'.booking_date', $t->booking_date ? \Carbon\Carbon::parse($t->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                            </div> --}}
                                            <div class="col-md-2">
                                                <label class="form-label text-dark fw-bold">Currency</label>
                                                <select name="transports[{{ $i }}][currency]" class="form-select transport-currency fw-bold"
                                                    onchange="calculateTransportRow(this.closest('.route-block'))">
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
                                                        class="form-control transport-cost-roe" placeholder="75.00" value="{{ $tCostRoe }}" {{ $isTPkr ? 'readonly' : '' }}
                                                        oninput="calculateTransportRow(this.closest('.route-block'))"
                                                        onchange="calculateTransportRow(this.closest('.route-block'))">
                                                </div>
                                                <input type="hidden" name="transports[{{ $i }}][roe]" class="transport-roe" value="{{ $tCostRoe }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-bold">Sale ROE</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light transport-curr-prefix">1 {{ $tCurr }} =</span>
                                                    <input type="number" step="0.0001" name="transports[{{ $i }}][sale_roe]"
                                                        class="form-control transport-sale-roe" placeholder="75.00" value="{{ $tSaleRoe }}" {{ $isTPkr ? 'readonly' : '' }}
                                                        oninput="calculateTransportRow(this.closest('.route-block'))"
                                                        onchange="calculateTransportRow(this.closest('.route-block'))">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-danger fw-semibold">Cost Rate (<span class="transport-curr-label">{{ $tCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][cost_rate]"
                                                    class="form-control transport-cost-rate" placeholder="0.00" value="{{ old('transports.'.$i.'.cost_rate', $t->cost_rate ?? 0) }}"
                                                    oninput="calculateTransportRow(this.closest('.route-block'))"
                                                    onchange="calculateTransportRow(this.closest('.route-block'))">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Total Cost (<span class="transport-curr-label">{{ $tCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-danger"><span class="transport-curr-name">{{ $tCurr }}:</span><strong class="transport-cost-sar-disp">{{ number_format(($t->cost_rate ?? 0) * ($t->quantity ?? 1), 2) }} {{ $tCurr }}</strong></div>
                                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">{{ number_format($t->cost_amount ?? 0, 2) }} PKR</strong></div>
                                                </div>
                                                <input type="hidden" name="transports[{{ $i }}][cost_amount]"
                                                    class="transport-cost-amount" value="{{ old('transports.'.$i.'.cost_amount', $t->cost_amount ?? 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-success fw-semibold">Sell Rate (<span class="transport-curr-label">{{ $tCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][selling_rate]"
                                                    class="form-control transport-sell-rate" placeholder="0.00" value="{{ old('transports.'.$i.'.selling_rate', $t->selling_rate ?? 0) }}"
                                                    oninput="calculateTransportRow(this.closest('.route-block'))"
                                                    onchange="calculateTransportRow(this.closest('.route-block'))">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Total Sell (<span class="transport-curr-label">{{ $tCurr }}</span> & PKR)</label>
                                                <div class="p-1 px-2 border rounded bg-light">
                                                    <div class="d-flex justify-content-between small text-success"><span class="transport-curr-name">{{ $tCurr }}:</span><strong class="transport-sell-sar-disp">{{ number_format(($t->selling_rate ?? 0) * ($t->quantity ?? 1), 2) }} {{ $tCurr }}</strong></div>
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
                                                <input type="number" name="transports[0][quantity]" class="form-control transport-qty"
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
                                                <select name="transports[0][supplier_id]" class="form-select transport-supplier-select select2">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $sup)
                                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Row 2: Date, Pickup Time, Flight #, Pickup From, Destination, BRN --}}
                                            <div class="col-md-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label mb-0"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Date</label>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 btn-set-transport-dep" style="font-size:10px;" title="Set Flight Departure Date">Dep</button>
                                                        <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 btn-set-transport-arr" style="font-size:10px;" title="Set Flight Arrival Date">Arr</button>
                                                    </div>
                                                </div>
                                                <input type="date" name="transports[0][travel_date]" class="form-control transport-travel-date" value="{{ old('transports.0.travel_date') }}">
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
                                            {{-- <div class="col-md-6">
                                                <label class="form-label text-muted"><i class="mdi mdi-calendar-check me-1"></i> Booking Date</label>
                                                <input type="date" name="transports[0][booking_date]" class="form-control" value="{{ old('transports.0.booking_date', date('Y-m-d')) }}">
                                            </div> --}}
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

                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addRoute" onclick="addNewRoute()">+ Add
                                Route</button>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-next">Next <i
                                            class="mdi mdi-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 7: Visa --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-visa' ? 'show active' : '' }}" id="tab-visa">
                            <!-- TOP ROW: Booking Date (First Field) & Visa Voucher No -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-danger shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-danger me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date_visa_tab" id="booking_date_visa_tab" class="form-control form-control-sm"
                                        value="{{ old('booking_date_visa_tab', !empty($booking->visas[0]->booking_date) ? \Carbon\Carbon::parse($booking->visas[0]->booking_date)->format('Y-m-d') : ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d'))) }}">
                                    <small class="text-muted fs-11">Visa Service Booking Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-passport text-danger me-1"></i> Visa Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-danger fs-13 px-2 py-1 font-monospace">{{ $booking->visa_voucher_no ?? ($defaultVouchers['visa'] ?? ('VIS-' . date('Y') . '-00001')) }}</span>
                                    </div>
                                    <input type="hidden" name="visa_voucher_no" id="visa_voucher_no" value="{{ $booking->visa_voucher_no ?? ($defaultVouchers['visa'] ?? ('VIS-' . date('Y') . '-00001')) }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>

                            {{-- Visa Dual Currency Totals Display --}}
                            @php
                                $initVisaCostSar = 0;
                                $initVisaCostPkr = 0;
                                $initVisaSellSar = 0;
                                $initVisaSellPkr = 0;
                                foreach ($booking->visas ?? [] as $bv) {
                                    $bvCurr = strtoupper($bv->currency ?: 'SAR');
                                    $bvCostR = (float)($bv->cost_roe ?: ($bv->roe ?: 75));
                                    $bvSaleR = (float)($bv->sale_roe ?: ($bv->roe ?: 76));
                                    $bvCostRate = (float)($bv->cost_rate ?? 0);
                                    $bvSellRate = (float)($bv->selling_rate ?? 0);
                                    if ($bvCurr === 'PKR') {
                                        $initVisaCostPkr += $bvCostRate;
                                        $initVisaSellPkr += $bvSellRate;
                                        $initVisaCostSar += $bvCostR > 0 ? round($bvCostRate / $bvCostR, 2) : 0;
                                        $initVisaSellSar += $bvSaleR > 0 ? round($bvSellRate / $bvSaleR, 2) : 0;
                                    } else {
                                        $initVisaCostSar += $bvCostRate;
                                        $initVisaSellSar += $bvSellRate;
                                        $initVisaCostPkr += round($bvCostRate * $bvCostR, 2);
                                        $initVisaSellPkr += round($bvSellRate * $bvSaleR, 2);
                                    }
                                }
                            @endphp
                            <div class="row g-2 p-2 rounded bg-light border mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Cost (SAR)</small>
                                        <strong class="text-danger fs-14" id="visa_cost_sar_total">{{ number_format($initVisaCostSar, 2) }} SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Cost (PKR)</small>
                                        <strong class="text-danger fs-14" id="visa_cost_pkr_total">{{ number_format($initVisaCostPkr, 2) }} PKR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Sell (SAR)</small>
                                        <strong class="text-success fs-14" id="visa_sell_sar_total">{{ number_format($initVisaSellSar, 2) }} SAR</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 bg-white text-center shadow-sm">
                                        <small class="text-muted d-block fw-semibold">Total Visa Sell (PKR)</small>
                                        <strong class="text-success fs-14" id="visa_sell_pkr_total">{{ number_format($initVisaSellPkr, 2) }} PKR</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Point 14: Default Visa Supplier & Rates for All Passengers --}}
                            @php
                                $firstVisa = $booking->visas?->first();
                                $defVisaCurr = $firstVisa?->currency ?? 'SAR';
                                $defVisaCostRoe = $firstVisa?->cost_roe ?? $firstVisa?->roe ?? 75;
                                $defVisaSaleRoe = $firstVisa?->sale_roe ?? $firstVisa?->roe ?? 76;
                                $defVisaCostRate = $firstVisa?->cost_rate ?? '';
                                $defVisaSellRate = $firstVisa?->selling_rate ?? '';
                                $defVisaSupId = $firstVisa?->supplier_id ?? '';
                                $isDefVPkr = ($defVisaCurr === 'PKR');
                            @endphp
                            <div class="card border mb-3 bg-light shadow-sm">
                                <div class="card-header bg-soft-primary py-2 d-flex justify-content-between align-items-center">
                                    <strong class="text-primary fs-12"><i class="mdi mdi-account-multiple-check me-1"></i> Default Visa Supplier & Rate (Applies to all passengers by default)</strong>
                                    <span class="badge bg-primary fs-11">Bulk / Default Apply</span>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-md-4 col-xl-3">
                                            <label class="form-label fs-12 mb-1 fw-semibold">Default Visa Supplier</label>
                                            <select id="default_visa_supplier_id" class="form-select form-select-sm">
                                                <option value="">-- Select Default Supplier --</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->id }}" {{ $defVisaSupId == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-2 col-xl-2">
                                            <label class="form-label fs-12 mb-1 text-dark fw-bold">Currency</label>
                                            <select id="default_visa_currency" class="form-select form-select-sm fw-bold" onchange="handleDefaultVisaCurrencyChange(this.value)">
                                                <option value="SAR" {{ $defVisaCurr === 'SAR' ? 'selected' : '' }}>SAR (Riyal)</option>
                                                <option value="PKR" {{ $defVisaCurr === 'PKR' ? 'selected' : '' }}>PKR (Rupee)</option>
                                                <option value="USD" {{ $defVisaCurr === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                                <option value="MYR" {{ $defVisaCurr === 'MYR' ? 'selected' : '' }}>MYR (Ringgit)</option>
                                                <option value="AED" {{ $defVisaCurr === 'AED' ? 'selected' : '' }}>AED (Dirham)</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-3 col-xl-1">
                                            <label class="form-label fs-12 mb-1 text-danger fw-bold">Cost ROE</label>
                                            <input type="number" step="0.0001" id="default_visa_cost_roe" class="form-control form-control-sm text-center" value="{{ $isDefVPkr ? '1.0000' : $defVisaCostRoe }}" placeholder="75.00" {{ $isDefVPkr ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-6 col-md-3 col-xl-1">
                                            <label class="form-label fs-12 mb-1 text-success fw-bold">Sale ROE</label>
                                            <input type="number" step="0.0001" id="default_visa_sale_roe" class="form-control form-control-sm text-center" value="{{ $isDefVPkr ? '1.0000' : $defVisaSaleRoe }}" placeholder="76.00" {{ $isDefVPkr ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-6 col-md-4 col-xl-2">
                                            <label class="form-label fs-12 mb-1 text-danger fw-semibold">Default Cost Rate (<span class="def-visa-curr-label">{{ $defVisaCurr }}</span>)</label>
                                            <input type="number" step="0.01" id="default_visa_cost_rate" class="form-control form-control-sm text-end" value="{{ $defVisaCostRate }}" placeholder="0.00">
                                        </div>
                                        <div class="col-6 col-md-4 col-xl-2">
                                            <label class="form-label fs-12 mb-1 text-success fw-semibold">Default Sell Rate (<span class="def-visa-curr-label">{{ $defVisaCurr }}</span>)</label>
                                            <input type="number" step="0.01" id="default_visa_sell_rate" class="form-control form-control-sm text-end" value="{{ $defVisaSellRate }}" placeholder="0.00">
                                        </div>
                                        <div class="col-12 col-md-4 col-xl-1">
                                            <button type="button" class="btn btn-sm btn-primary w-100" id="btn_apply_default_visa" onclick="applyDefaultVisaToAll()" title="Apply to All Passengers">
                                                <i class="mdi mdi-check-all me-1"></i> Apply
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1 fs-11"><i class="mdi mdi-information-outline"></i> Select supplier, currency, ROE, and rates above to apply to all passengers at once. Individual passengers can still be edited below.</small>
                                </div>
                            </div>

                            <p class="text-muted small mb-3">
                                <i class="mdi mdi-information me-1"></i>
                                Edit visa details for each passenger below.
                            </p>
                            <div id="visasList">
                                @php
                                    $bVisasList = $booking->visas;
                                    $bTotalVisas = max(1, (int)($booking->no_of_pax ?: 1));
                                @endphp
                                @for ($i = 0; $i < $bTotalVisas; $i++)
                                    @php
                                        $visa = $bVisasList[$i] ?? new \App\Models\BookingVisa();
                                        $vCurr = old('visas.'.$i.'.currency', $visa->currency ?? 'SAR');
                                        $vCostRoe = old('visas.'.$i.'.cost_roe', $visa->cost_roe ?? $visa->roe ?? 75);
                                        $vSaleRoe = old('visas.'.$i.'.sale_roe', $visa->sale_roe ?? $visa->roe ?? 75);
                                        $isVPkr = ($vCurr === 'PKR');
                                    @endphp
                                    <div class="visa-card border rounded p-3 mb-2 bg-white shadow-sm" id="visa_card_{{ $i }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            @php
                                                $personObj = $booking->persons[$i] ?? null;
                                                $pType = $personObj->pax_type ?? 'adult';
                                                $vBadgeClass = $pType === 'child' ? 'bg-info text-dark' : ($pType === 'infant' ? 'bg-warning text-dark' : 'bg-primary');
                                                $vTypeLabel = strtoupper($pType);
                                            @endphp
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="text-warning" style="font-size:13px;">
                                                    <i class="mdi mdi-passport me-1"></i> Visa {{ $i + 1 }}: {{ $visa->given_name ?? 'Passenger ' . ($i + 1) }}
                                                </strong>
                                                <span class="badge {{ $vBadgeClass }} person-visa-type-badge" style="font-size:10px;">{{ $vTypeLabel }}</span>
                                                @php
                                                    $vChildVoucher = $visa->voucher_no ?? (($booking->visa_voucher_no ?: ($defaultVouchers['visa'] ?? ('VIS-' . date('Y') . '-00001'))) . '-' . ($i + 1));
                                                @endphp
                                                <span class="badge bg-soft-danger text-danger border border-danger fs-11 font-monospace">
                                                    <i class="mdi mdi-ticket-confirmation me-1"></i>Voucher #: {{ $vChildVoucher }}
                                                </span>
                                                <input type="hidden" name="visas[{{ $i }}][voucher_no]" value="{{ $vChildVoucher }}">
                                            </div>
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
                                                <select name="visas[{{ $i }}][supplier_id]" class="form-select form-select-sm visa-supplier-select select2">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $s)
                                                        <option value="{{ $s->id }}" {{ old('visas.'.$i.'.supplier_id', $visa->supplier_id) == $s->id ? 'selected' : '' }}>
                                                            {{ $s->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
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
                                            {{-- <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                                                <input type="date" name="visas[{{ $i }}][booking_date]"
                                                    class="form-control form-control-sm visa-booking-date"
                                                    value="{{ old('visas.'.$i.'.booking_date', $visa->booking_date ? \Carbon\Carbon::parse($visa->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                            </div> --}}

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
                                @endfor
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-next">Next <i
                                            class="mdi mdi-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 8: Costing --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-costing' ? 'show active' : '' }}" id="tab-costing">
                            <!-- TOP ROW: Booking Date (First Field) & Costing Overview -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-success shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-success me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date_costing_tab" id="booking_date_costing_tab" class="form-control form-control-sm"
                                        value="{{ old('booking_date_costing_tab', $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    <small class="text-muted fs-11">Costing Service Booking Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-cash text-success me-1"></i> Costing & Pricing Overview
                                    </label>
                                    <div>
                                        <span class="badge bg-success fs-13 px-2 py-1 font-monospace">Package Ledger</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>
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
                                    <input type="number" name="flight_charges" id="flight_charges"
                                        class="form-control calc bg-light"
                                        value="{{ old('flight_charges', $booking->flight_charges ?? 0) }}"
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
                                        $isFPkr = ($fCurr === 'PKR');
                                        $fSaleRoe = (float) ($booking->flight_sale_roe > 0 ? $booking->flight_sale_roe : 75);
                                        $fCostRoe = (float) ($booking->flight_cost_roe > 0 ? $booking->flight_cost_roe : 75);
                                        $bFlightForeign = !$isFPkr ? (float) ($booking->flight_selling_rate > 0 ? $booking->flight_selling_rate : (($booking->flight_charges > 0 && $fSaleRoe > 0) ? ($booking->flight_charges / $fSaleRoe) : 0)) : 0;
                                        $bFlightCostForeign = !$isFPkr ? (float) ($booking->flight_cost_rate > 0 ? $booking->flight_cost_rate : (($booking->flight_cost > 0 && $fCostRoe > 0) ? ($booking->flight_cost / $fCostRoe) : 0)) : 0;
                                        $bFlightCostPkr = (float) ($booking->flight_cost ?? 0);
                                        $bFlightSellPkr = (float) ($booking->flight_charges ?? 0);

                                        $bHotelForeign = 0;
                                        $bHotelCostForeign = 0;
                                        $bHotelCostPkr = 0;
                                        $bHotelSellPkr = 0;
                                        $hotelCurrsFound = [];
                                        foreach ($booking->hotels as $bh) {
                                            $hCurr = $bh->currency ?: 'SAR';
                                            $hSaleRoe = (float) ($bh->sale_roe > 0 ? $bh->sale_roe : ($bh->cost_roe > 0 ? $bh->cost_roe : 75));
                                            $hCostRoe = (float) ($bh->cost_roe > 0 ? $bh->cost_roe : ($bh->sale_roe > 0 ? $bh->sale_roe : 75));
                                            $hSpn = (float) $bh->selling_per_night;
                                            $hCpn = (float) $bh->cost_per_night;
                                            $hRooms = max(1, (int) $bh->no_of_rooms);
                                            $hNights = max(1, (int) $bh->no_of_nights);
                                            
                                            $hLineForeignSell = $hSpn > 0 ? ($hSpn * $hRooms * $hNights) : ((float)$bh->selling_amount > 0 && $hSaleRoe > 0 ? (float)$bh->selling_amount / $hSaleRoe : 0);
                                            $hLineForeignCost = $hCpn > 0 ? ($hCpn * $hRooms * $hNights) : ((float)$bh->cost_amount > 0 && $hCostRoe > 0 ? (float)$bh->cost_amount / $hCostRoe : 0);

                                            if ($hCurr === 'PKR') {
                                                $bHotelSellPkr += $hLineForeignSell;
                                                $bHotelCostPkr += $hLineForeignCost;
                                            } else {
                                                $bHotelForeign += $hLineForeignSell;
                                                $bHotelCostForeign += $hLineForeignCost;
                                                $bHotelSellPkr += ($hLineForeignSell * $hSaleRoe);
                                                $bHotelCostPkr += ($hLineForeignCost * $hCostRoe);
                                            }
                                            $hotelCurrsFound[] = $hCurr;
                                        }
                                        $hotelCurrTag = count(array_unique($hotelCurrsFound)) === 1 ? ($hotelCurrsFound[0] ?? 'SAR') : (count(array_unique($hotelCurrsFound)) > 1 ? 'Mix' : 'SAR');

                                        $bTransportForeign = 0;
                                        $bTransportCostForeign = 0;
                                        $bTransportCostPkr = 0;
                                        $bTransportSellPkr = 0;
                                        $transCurrsFound = [];
                                        foreach ($booking->transports as $bt) {
                                            $tCurr = $bt->currency ?: 'SAR';
                                            $tSaleRoe = (float) ($bt->sale_roe > 0 ? $bt->sale_roe : 75);
                                            $tCostRoe = (float) ($bt->cost_roe > 0 ? $bt->cost_roe : 75);
                                            $tQty = max(1, (int)$bt->quantity);
                                            $tSr = (float)$bt->selling_rate;
                                            $tCr = (float)$bt->cost_rate;

                                            $tLineForeignSell = $tSr > 0 ? ($tSr * $tQty) : ((float)$bt->selling_amount > 0 && $tSaleRoe > 0 ? (float)$bt->selling_amount / $tSaleRoe : 0);
                                            $tLineForeignCost = $tCr > 0 ? ($tCr * $tQty) : ((float)$bt->cost_amount > 0 && $tCostRoe > 0 ? (float)$bt->cost_amount / $tCostRoe : 0);

                                            if ($tCurr === 'PKR') {
                                                $bTransportSellPkr += $tLineForeignSell;
                                                $bTransportCostPkr += $tLineForeignCost;
                                            } else {
                                                $bTransportForeign += $tLineForeignSell;
                                                $bTransportCostForeign += $tLineForeignCost;
                                                $bTransportSellPkr += ($tLineForeignSell * $tSaleRoe);
                                                $bTransportCostPkr += ($tLineForeignCost * $tCostRoe);
                                            }
                                            $transCurrsFound[] = $tCurr;
                                        }
                                        $transportCurrTag = count(array_unique($transCurrsFound)) === 1 ? ($transCurrsFound[0] ?? 'SAR') : (count(array_unique($transCurrsFound)) > 1 ? 'Mix' : 'SAR');

                                        $bVisaForeign = 0;
                                        $bVisaCostForeign = 0;
                                        $bVisaCostPkr = 0;
                                        $bVisaSellPkr = 0;
                                        $visaCurrsFound = [];
                                        foreach ($booking->visas as $bv) {
                                            $vCurr = $bv->currency ?: 'SAR';
                                            $vSaleRoe = (float) ($bv->sale_roe > 0 ? $bv->sale_roe : 75);
                                            $vCostRoe = (float) ($bv->cost_roe > 0 ? $bv->cost_roe : 75);
                                            $vSr = (float)$bv->selling_rate;
                                            $vCr = (float)$bv->cost_rate;

                                            $vLineForeignSell = $vSr > 0 ? $vSr : ((float)$bv->selling_amount > 0 && $vSaleRoe > 0 ? (float)$bv->selling_amount / $vSaleRoe : 0);
                                            $vLineForeignCost = $vCr > 0 ? $vCr : ((float)$bv->cost_amount > 0 && $vCostRoe > 0 ? (float)$bv->cost_amount / $vCostRoe : 0);

                                            if ($vCurr === 'PKR') {
                                                $bVisaSellPkr += $vLineForeignSell;
                                                $bVisaCostPkr += $vLineForeignCost;
                                            } else {
                                                $bVisaForeign += $vLineForeignSell;
                                                $bVisaCostForeign += $vLineForeignCost;
                                                $bVisaSellPkr += ($vLineForeignSell * $vSaleRoe);
                                                $bVisaCostPkr += ($vLineForeignCost * $vCostRoe);
                                            }
                                            $visaCurrsFound[] = $vCurr;
                                        }
                                        $visaCurrTag = count(array_unique($visaCurrsFound)) === 1 ? ($visaCurrsFound[0] ?? 'SAR') : (count(array_unique($visaCurrsFound)) > 1 ? 'Mix' : 'SAR');

                                        $bOtherCostPkr = (float)($booking->additional_services_cost ?? 0);
                                        $bTotalCostPkr = $bFlightCostPkr + $bHotelCostPkr + $bTransportCostPkr + $bVisaCostPkr + $bOtherCostPkr;

                                        $bTotalForeign = (!$isFPkr ? $bFlightForeign : 0) + $bHotelForeign + $bTransportForeign + $bVisaForeign;
                                        $bTotalCostForeign = (!$isFPkr ? $bFlightCostForeign : 0) + $bHotelCostForeign + $bTransportCostForeign + $bVisaCostForeign;
                                        $activeCurrsFound = array_unique(array_filter(array_merge([$fCurr], $hotelCurrsFound, $transCurrsFound, $visaCurrsFound), fn($c) => $c !== 'PKR'));
                                        $mainForeignUnit = count($activeCurrsFound) === 1 ? reset($activeCurrsFound) : (count($activeCurrsFound) > 1 ? 'Mix' : 'SAR');
                                        $bBalanceForeign = ($bTotalForeign > 0 && (float)$booking->total_amount > 0) ? ($bTotalForeign * ((float)$booking->balance / (float)$booking->total_amount)) : 0;
                                    @endphp
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="card border-primary border-opacity-25 bg-light mb-0 shadow-sm">
                                                <div class="card-body p-3">
                                                    <h6 class="text-primary fw-bold mb-2"><i class="mdi mdi-cash-multiple me-1"></i> Foreign Currency (<span id="cost_foreign_curr_title">{{ $mainForeignUnit }}</span>) Total</h6>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Flight:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_sar_flight_cost">{{ $isFPkr ? '—' : number_format($bFlightCostForeign, 2) . ' ' . $fCurr }}</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_sar_flight">{{ $isFPkr ? '—' : number_format($bFlightForeign, 2) . ' ' . $fCurr }}</span></strong>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Hotel:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_sar_hotel_cost">{{ number_format($bHotelCostForeign, 2) }} {{ $hotelCurrTag }}</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_sar_hotel">{{ number_format($bHotelForeign, 2) }} {{ $hotelCurrTag }}</span></strong>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Transport:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_sar_transport_cost">{{ number_format($bTransportCostForeign, 2) }} {{ $transportCurrTag }}</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_sar_transport">{{ number_format($bTransportForeign, 2) }} {{ $transportCurrTag }}</span></strong>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Visa:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_sar_visa_cost">{{ number_format($bVisaCostForeign, 2) }} {{ $visaCurrTag }}</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_sar_visa">{{ number_format($bVisaForeign, 2) }} {{ $visaCurrTag }}</span></strong>
                                                        </span>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between text-danger mb-1">
                                                        <span>Total Direct Cost:</span>
                                                        <strong id="cost_sar_total_cost">{{ number_format($bTotalCostForeign, 2) }} {{ $mainForeignUnit }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-primary fs-15 fw-bold mb-1">
                                                        <span>Total Selling:</span>
                                                        <span id="cost_sar_total">{{ number_format($bTotalForeign, 2) }} {{ $mainForeignUnit }}</span>
                                                    </div>
                                                    <span id="cost_sar_balance" style="display:none;">{{ number_format($bBalanceForeign, 2) }} {{ $mainForeignUnit }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-success border-opacity-25 bg-light mb-0 shadow-sm">
                                                <div class="card-body p-3">
                                                    <h6 class="text-success fw-bold mb-2"><i class="mdi mdi-currency-usd me-1"></i> PKR (Pakistani Rupee) Total</h6>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Flight PKR:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_pkr_flight_cost">{{ number_format($bFlightCostPkr, 2) }} PKR</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_pkr_flight">{{ number_format($bFlightSellPkr, 2) }} PKR</span></strong>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Hotel PKR:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_pkr_hotel_cost">{{ number_format($bHotelCostPkr, 2) }} PKR</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_pkr_hotel">{{ number_format($booking->hotel_charges ?? 0, 2) }} PKR</span></strong>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Transport PKR:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_pkr_transport_cost">{{ number_format($bTransportCostPkr, 2) }} PKR</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_pkr_transport">{{ number_format($booking->transport_charges ?? 0, 2) }} PKR</span></strong>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Visa PKR:</span>
                                                        <span>
                                                            <small class="text-danger me-1">Cost: <span id="cost_pkr_visa_cost">{{ number_format($bVisaCostPkr, 2) }} PKR</span></small>
                                                            <span class="text-muted">|</span>
                                                            <strong class="text-success ms-1">Sell: <span id="cost_pkr_visa">{{ number_format($booking->visa_charges ?? 0, 2) }} PKR</span></strong>
                                                        </span>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between text-danger mb-1">
                                                        <span>Total Direct Cost (PKR):</span>
                                                        <strong id="cost_pkr_total_cost">{{ number_format($bTotalCostPkr, 2) }} PKR</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-success fs-15 fw-bold mb-1">
                                                        <span>Total Selling (PKR):</span>
                                                        <span id="cost_pkr_total">{{ number_format($booking->total_amount ?? 0, 2) }} PKR</span>
                                                    </div>
                                                    <span id="cost_pkr_balance" style="display:none;">{{ number_format($booking->balance ?? 0, 2) }} PKR</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm bg-white shadow-sm text-center align-middle mb-3">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th style="width: 150px;" class="text-start">Breakdown</th>
                                                    <th style="{{ $isUmrah ? 'display:none;' : '' }}">Pkg Cost × Pax</th>
                                                    <th>Hotel (PKR)</th>
                                                    <th>Visa (PKR)</th>
                                                    <th>Flight (PKR)</th>
                                                    <th>Transport (PKR)</th>
                                                    <th>Other & Services</th>
                                                    <th>Discount</th>
                                                    <th>Total (PKR)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="table-light">
                                                    <td class="fw-bold text-danger text-start"><i class="mdi mdi-arrow-down-bold text-danger me-1"></i> Cost (Purchase)</td>
                                                    <td id="cost_sum_pkg" style="{{ $isUmrah ? 'display:none;' : '' }}">0.00</td>
                                                    <td id="cost_sum_hotel" class="text-danger fw-semibold">{{ number_format($bHotelCostPkr, 2) }}</td>
                                                    <td id="cost_sum_visa" class="text-danger fw-semibold">{{ number_format($bVisaCostPkr, 2) }}</td>
                                                    <td id="cost_sum_flight" class="text-danger fw-semibold">{{ number_format($bFlightCostPkr, 2) }}</td>
                                                    <td id="cost_sum_transport" class="text-danger fw-semibold">{{ number_format($bTransportCostPkr, 2) }}</td>
                                                    <td id="cost_sum_other" class="text-danger fw-semibold">{{ number_format($bOtherCostPkr, 2) }}</td>
                                                    <td class="text-muted">—</td>
                                                    <td class="text-danger fw-bold" id="cost_sum_total">{{ number_format($bTotalCostPkr, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold text-success text-start"><i class="mdi mdi-arrow-up-bold text-success me-1"></i> Selling (Client)</td>
                                                    <td id="sum_pkg" style="{{ $isUmrah ? 'display:none;' : '' }}">
                                                        {{ number_format(($booking->package_cost ?? 0) * ($booking->no_of_pax ?? 0), 2) }}
                                                    </td>
                                                    <td id="sum_hotel" class="text-success fw-semibold">{{ number_format($booking->hotel_charges ?? 0, 2) }}</td>
                                                    <td id="sum_visa" class="text-success fw-semibold">{{ number_format($booking->visa_charges ?? 0, 2) }}</td>
                                                    <td id="sum_flight" class="text-success fw-semibold">{{ number_format($booking->flight_charges ?? 0, 2) }}</td>
                                                    <td id="sum_transport" class="text-success fw-semibold">{{ number_format($booking->transport_charges ?? 0, 2) }}</td>
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
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-save-booking px-4" onclick="submitBookingForm(this, 'save_close')">
                                        <i class="mdi mdi-check-all me-1"></i> Update & Close
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-next">Next <i class="mdi mdi-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 9: Additional Services --}}
                        <div class="tab-pane fade {{ $reqTab === '#tab-additional-services' ? 'show active' : '' }}" id="tab-additional-services">
                            <!-- TOP ROW: Booking Date (First Field) & Services -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-secondary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-secondary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date_services_tab" id="booking_date_services_tab" class="form-control form-control-sm"
                                        value="{{ old('booking_date_services_tab', $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                    <small class="text-muted fs-11">Services Booking Date</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-plus-circle text-secondary me-1"></i> Additional Services
                                    </label>
                                    <div>
                                        <span class="badge bg-secondary fs-13 px-2 py-1 font-monospace">Custom Services</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">
                              <div class="col-md-12">
    <label class="form-label">Service Details</label>

    <textarea name="additional_services_detail"
              id="additional_services_detail"
              class="form-control summernote">{{ old('additional_services_detail', trim(strip_tags($booking->additional_services_detail ?? '')) === '' ? '' : ($booking->additional_services_detail ?? '')) }}</textarea>
</div>

                                <div class="col-md-6">
                                    <label class="form-label text-danger fw-semibold"><i class="mdi mdi-tag-outline me-1"></i> Additional Service Cost (PKR)</label>
                                    <input type="number" name="additional_services_cost" id="additional_services_cost" class="form-control calc" placeholder="Cost Amount" step="0.01" value="{{ old('additional_services_cost', $booking->additional_services_cost ?? 0) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-success fw-semibold"><i class="mdi mdi-cash-multiple me-1"></i> Additional Service Sale (PKR)</label>
                                    <input type="number" name="additional_services_amount" id="additional_services_amount" class="form-control calc" placeholder="Sale Amount" step="0.01" value="{{ old('additional_services_amount', $booking->additional_services_amount ?? 0) }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="mdi mdi-arrow-left me-1"></i> Prev
                                </button>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('booking.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="button" class="btn btn-success btn-save-booking" onclick="submitBookingForm(this, 'save_stay')">
                                        <i class="mdi mdi-content-save me-1"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-primary btn-save-booking px-4" onclick="submitBookingForm(this, 'save_close')">
                                        <i class="mdi mdi-check-all me-1"></i> Update & Close
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

        /* Hide number spin buttons */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        input[type="number"] {
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }
    </style>
          {{-- Summernote JS --}}
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <script>
        const packagesData     = @json($packages);
        let clientsData        = @json($clients);
        const hotelsData       = @json($hotels);
        const suppliersData    = @json($suppliers);
        const mealPlansData    = @json($mealPlans ?? []);

        function buildHotelsOptions(filterCity = null) {
            let list = Array.isArray(hotelsData) ? hotelsData : [];
            const activeCategory = document.querySelector('input[name="booking_category"]:checked')?.value || '{{ $bCat }}';

            const domesticPlaces = ['karachi', 'lahore', 'islamabad', 'rawalpindi', 'peshawar', 'multan', 'faisalabad', 'quetta', 'sialkot', 'pakistan'];
            const pilgrimagePlaces = ['makkah', 'madinah', 'medina', 'madina', 'azizia', 'mina', 'arafat', 'jeddah', 'saudi'];

            if (activeCategory === 'domestic') {
                list = list.filter(h => {
                    const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || '').toLowerCase();
                    const a = (h.address || '').toLowerCase();
                    const n = (h.name || '').toLowerCase();
                    return domesticPlaces.some(dp => p.includes(dp) || a.includes(dp) || n.includes(dp));
                });
            } else if (activeCategory === 'international') {
                list = list.filter(h => {
                    const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || '').toLowerCase();
                    const a = (h.address || '').toLowerCase();
                    const n = (h.name || '').toLowerCase();
                    const isPilgrim = pilgrimagePlaces.some(pp => p.includes(pp) || a.includes(pp) || n.includes(pp));
                    const isDom = domesticPlaces.some(dp => p.includes(dp) || a.includes(dp) || n.includes(dp));
                    return !isPilgrim && !isDom;
                });
            } else {
                if (filterCity) {
                    const fc = filterCity.toLowerCase();
                    if (fc === 'makkah') {
                        list = list.filter(h => {
                            const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || '').toLowerCase();
                            const n = (h.name || '').toLowerCase();
                            const a = (h.address || '').toLowerCase();
                            return p.includes('makkah') || p.includes('mecca') || p.includes('azizia') ||
                                   n.includes('makkah') || n.includes('mecca') || n.includes('azizia') ||
                                   a.includes('makkah') || a.includes('mecca') || a.includes('azizia');
                        });
                    } else if (fc === 'madinah') {
                        list = list.filter(h => {
                            const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || '').toLowerCase();
                            const n = (h.name || '').toLowerCase();
                            const a = (h.address || '').toLowerCase();
                            return p.includes('madinah') || p.includes('medina') || p.includes('madina') ||
                                   n.includes('madinah') || n.includes('medina') || n.includes('madina') ||
                                   a.includes('madinah') || a.includes('medina') || a.includes('madina');
                        });
                    } else if (fc === 'other') {
                        list = list.filter(h => {
                            const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || '').toLowerCase();
                            const n = (h.name || '').toLowerCase();
                            const a = (h.address || '').toLowerCase();
                            const isMakkah = p.includes('makkah') || p.includes('mecca') || p.includes('azizia') || n.includes('makkah') || n.includes('mecca');
                            const isMadinah = p.includes('madinah') || p.includes('medina') || p.includes('madina') || n.includes('madinah') || n.includes('medina') || n.includes('madina');
                            return !isMakkah && !isMadinah;
                        });
                    }
                }
            }

            return `<option value="">Select Hotel</option>` +
                list.map(h => {
                    const p = ((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || '';
                    return `<option value="${h.name}">${h.name}${p ? ' (' + p + ')' : ''}</option>`;
                }).join('');
        }

        function buildMealPlansOptions(selectedCode = 'BB') {
            if (!Array.isArray(mealPlansData) || mealPlansData.length === 0) {
                return `
                    <option value="RO" ${selectedCode === 'RO' ? 'selected' : ''}>Room Only (RO)</option>
                    <option value="BB" ${selectedCode === 'BB' ? 'selected' : ''}>Bed & Breakfast (BB)</option>
                    <option value="HB" ${selectedCode === 'HB' ? 'selected' : ''}>Half Board (HB)</option>
                    <option value="FB" ${selectedCode === 'FB' ? 'selected' : ''}>Full Board (FB)</option>
                    <option value="SO" ${selectedCode === 'SO' ? 'selected' : ''}>Sahoor Only (SO)</option>
                    <option value="IO" ${selectedCode === 'IO' ? 'selected' : ''}>Iftar Only (IO)</option>
                    <option value="SI" ${selectedCode === 'SI' ? 'selected' : ''}>Sahoor & Iftar (SI)</option>
                `;
            }
            return mealPlansData.map(mp => {
                const sel = (selectedCode && (selectedCode.toUpperCase() === mp.code.toUpperCase() || selectedCode.toUpperCase() === mp.name.toUpperCase())) ? 'selected' : '';
                return `<option value="${mp.code}" ${sel}>${mp.name} (${mp.code})</option>`;
            }).join('');
        }

        function buildRoomViewsOptions(selected = 'Standard View') {
            const views = [
                'Standard View',
                'City View',
                'Haram View',
                'Kaaba View',
                'Partial Haram View',
                'Sea / Mountain View'
            ];
            return views.map(v => `<option value="${v}" ${selected === v ? 'selected' : ''}>${v}</option>`).join('');
        }

        function refreshHotelsDropdown(idx = null) {
            refreshMasterData(true);
        }
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

        // Point 12: Dropdown Auto-Refresh for Clients
        async function refreshClientsDropdown(showNotification = true) {
            try {
                const clientSelect = document.getElementById('clientSelect');
                const curVal = clientSelect ? clientSelect.value : '';
                const res = await fetch('{{ route("client.json-list") }}');
                const data = await res.json();
                if (Array.isArray(data)) {
                    if (clientSelect) {
                        let html = '<option value="">-- Select Client --</option>';
                        data.forEach(c => {
                            const compStr = c.company_name ? ` (${c.company_name})` : '';
                            const phoneCnic = c.phone || c.cnic || '';
                            const isSel = (c.id == curVal) ? 'selected' : '';
                            html += `<option value="${c.id}" data-name="${c.name}" data-phone="${c.phone || ''}" data-cnic="${c.cnic || ''}" data-passport="${c.passport_number || ''}" data-company="${c.company_name || ''}" ${isSel}>${c.name}${compStr} - ${phoneCnic}</option>`;
                        });
                        clientSelect.innerHTML = html;
                        if (curVal) clientSelect.value = curVal;
                        if (typeof $ !== 'undefined' && $(clientSelect).data('select2')) {
                            $(clientSelect).trigger('change.select2');
                        }
                    }
                    if (showNotification) {
                        showAutoRefreshToast('Clients dropdown refreshed without losing data!');
                    }
                }
            } catch(e) {
                console.error('Error refreshing clients:', e);
            }
        }

        // Point 12: Dropdown Auto-Refresh for Packages
        async function refreshPackagesDropdown(showNotification = true) {
            try {
                const pkgSelect = document.getElementById('packageSelect');
                const curVal = pkgSelect ? pkgSelect.value : '';
                const res = await fetch('{{ route("package.json-list") }}');
                const data = await res.json();
                if (Array.isArray(data)) {
                    if (pkgSelect) {
                        let html = '<option value="">-- Select Package --</option>';
                        data.forEach(p => {
                            const codeStr = p.code ? ` (${p.code})` : '';
                            const isSel = (p.id == curVal) ? 'selected' : '';
                            html += `<option value="${p.id}" ${isSel}>${p.name}${codeStr}</option>`;
                        });
                        pkgSelect.innerHTML = html;
                        if (curVal) pkgSelect.value = curVal;
                    }
                    if (showNotification) {
                        showAutoRefreshToast('Packages dropdown refreshed without losing data!');
                    }
                }
            } catch(e) {
                console.error('Error refreshing packages:', e);
            }
        }

        function showAutoRefreshToast(msg) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show p-2 px-3 mb-2 shadow position-fixed top-0 end-0 m-3';
            alertDiv.style.zIndex = '99999';
            alertDiv.innerHTML = `<i class="mdi mdi-check-circle me-1"></i> <strong>Refreshed!</strong> ${msg} <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3500);
        }

        function handleDefaultVisaCurrencyChange(val) {
            const costRoeInput = document.getElementById('default_visa_cost_roe');
            const saleRoeInput = document.getElementById('default_visa_sale_roe');
            const currLabels = document.querySelectorAll('.def-visa-curr-label');

            currLabels.forEach(el => el.textContent = val);

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
                    if (val === 'SAR') saleRoeInput.value = '76.0000';
                    else if (val === 'MYR') saleRoeInput.value = '64.0000';
                    else if (val === 'USD') saleRoeInput.value = '280.0000';
                    else if (val === 'AED') saleRoeInput.value = '77.0000';
                }
            }
        }

        // Point 14: Apply Default Visa to All Passengers
        function applyDefaultVisaToAll() {
            const defSup = document.getElementById('default_visa_supplier_id')?.value;
            const defCurr = document.getElementById('default_visa_currency')?.value || 'SAR';
            const isPkr = (defCurr === 'PKR');
            const defCostRoe = isPkr ? '1.0000' : (document.getElementById('default_visa_cost_roe')?.value || '75.0000');
            const defSaleRoe = isPkr ? '1.0000' : (document.getElementById('default_visa_sale_roe')?.value || '76.0000');
            const defCost = document.getElementById('default_visa_cost_rate')?.value;
            const defSell = document.getElementById('default_visa_sell_rate')?.value;

            document.querySelectorAll('#visasList .visa-card').forEach(card => {
                if (defSup) {
                    const supSel = card.querySelector('select[name*="[supplier_id]"]');
                    if (supSel) {
                        supSel.value = defSup;
                        if (typeof $ !== 'undefined') {
                            $(supSel).val(defSup).trigger('change');
                        }
                    }
                }

                const currSel = card.querySelector('.visa-currency') || card.querySelector('select[name*="[currency]"]');
                if (currSel) {
                    currSel.value = defCurr;
                }

                const costRoeInput = card.querySelector('.visa-cost-roe');
                const saleRoeInput = card.querySelector('.visa-sale-roe');
                const legacyRoe = card.querySelector('.visa-roe');

                if (isPkr) {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                    if (legacyRoe) legacyRoe.value = '1.0000';
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (defCostRoe !== '' && defCostRoe !== undefined) costRoeInput.value = defCostRoe;
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (defSaleRoe !== '' && defSaleRoe !== undefined) saleRoeInput.value = defSaleRoe;
                    }
                    if (legacyRoe && defCostRoe !== '' && defCostRoe !== undefined) legacyRoe.value = defCostRoe;
                }

                if (defCost !== '' && defCost !== undefined) {
                    const cInp = card.querySelector('.visa-cost-rate');
                    if (cInp) cInp.value = defCost;
                }
                if (defSell !== '' && defSell !== undefined) {
                    const sInp = card.querySelector('.visa-sell-rate');
                    if (sInp) sInp.value = defSell;
                }

                card.querySelectorAll('.visa-curr-label').forEach(el => el.textContent = defCurr);
                card.querySelectorAll('.visa-curr-prefix').forEach(el => el.textContent = `1 ${defCurr} =`);
                card.querySelectorAll('.visa-curr-name').forEach(el => el.textContent = `${defCurr}:`);

                if (typeof calculateVisaRow === 'function') {
                    calculateVisaRow(card);
                }
            });
            if (typeof calcTotal === 'function') {
                calcTotal();
            }
            showAutoRefreshToast('Default visa supplier, currency, ROE, and rates applied to all passengers!');
        }

        document.getElementById('default_visa_supplier_id')?.addEventListener('change', function() {
            const defSup = this.value;
            if (defSup) {
                document.querySelectorAll('#visasList .visa-card select[name*="[supplier_id]"]').forEach(supSel => {
                    supSel.value = defSup;
                    if (typeof $ !== 'undefined') {
                        $(supSel).val(defSup).trigger('change');
                    }
                });
            }
        });

        // Point 4: Live Unique Ticket Check
        function checkUniqueTicketNumbers() {
            const inputs = document.querySelectorAll('.person-flight-ticket');
            const seen = {};
            inputs.forEach(inp => {
                inp.classList.remove('is-invalid');
                const p = inp.parentElement;
                if (p) {
                    const existingWarn = p.querySelector('.ticket-dup-warn');
                    if (existingWarn) existingWarn.remove();
                }
            });

            inputs.forEach(inp => {
                const val = inp.value.trim().toLowerCase();
                if (val) {
                    if (seen[val]) {
                        inp.classList.add('is-invalid');
                        seen[val].classList.add('is-invalid');
                        const p = inp.parentElement;
                        if (p && !p.querySelector('.ticket-dup-warn')) {
                            const msg = document.createElement('small');
                            msg.className = 'text-danger d-block ticket-dup-warn fs-11 fw-semibold mt-1';
                            msg.innerHTML = '<i class="mdi mdi-alert-circle me-1"></i>Duplicate ticket!';
                            p.appendChild(msg);
                        }
                    } else {
                        seen[val] = inp;
                    }
                }
            });
        }

        // Client Select Auto-population (Robust for Select2 & Native Events)
        function populateClientDetails(target) {
            const el = (target instanceof HTMLElement) ? target : document.getElementById('clientSelect');
            if (!el) return;
            const clientId = parseInt(el.value);
            if (!clientId) return;

            let client = (typeof clientsData !== 'undefined' && Array.isArray(clientsData)) ? clientsData.find(c => c.id == clientId) : null;
            const selOpt = el.querySelector(`option[value="${clientId}"]`) || (el.options && el.selectedIndex >= 0 ? el.options[el.selectedIndex] : null);

            const passVal  = (client && client.passport_number) ? client.passport_number : (selOpt ? (selOpt.getAttribute('data-passport') || '') : '');
            const cnicVal  = (client && client.cnic) ? client.cnic : (selOpt ? (selOpt.getAttribute('data-cnic') || '') : '');
            const phoneVal = (client && client.phone) ? client.phone : (selOpt ? (selOpt.getAttribute('data-phone') || '') : '');
            const nameVal  = (client && client.name) ? client.name : (selOpt ? (selOpt.getAttribute('data-name') || '') : '');

            const fillPass  = document.getElementById('fill_passport');
            const fillCnic  = document.getElementById('fill_cnic');
            const fillPhone = document.getElementById('fill_phone');

            if (fillPass && passVal) fillPass.value = passVal;
            if (fillCnic && cnicVal) fillCnic.value = cnicVal;
            if (fillPhone && phoneVal) fillPhone.value = phoneVal;
            const fillEmerg = document.getElementById('fill_emergency_phone') || document.querySelector('input[name="emergency_phone"]');
            const emergVal  = (client && client.emergency_phone) ? client.emergency_phone : (selOpt ? (selOpt.getAttribute('data-emergency') || '') : '');
            if (fillEmerg && emergVal) fillEmerg.value = emergVal;

            // Auto-fill Primary Passenger (idx 0)
            const p0Name = document.getElementById('person_name_0');
            const p0Pass = document.getElementById('person_passport_0');
            const p0Cnic = document.getElementById('person_cnic_0');

            if (p0Name && (!p0Name.value || p0Name.value === '')) p0Name.value = nameVal;
            if (p0Pass && (!p0Pass.value || p0Pass.value === '')) p0Pass.value = passVal;
            if (p0Cnic && (!p0Cnic.value || p0Cnic.value === '')) p0Cnic.value = cnicVal;

            const parts = (nameVal || '').trim().split(' ');
            if (parts.length > 1) {
                const s0 = document.getElementById('person_surname_0');
                const g0 = document.getElementById('person_given_name_0');
                if (s0 && !s0.value) s0.value = parts[0];
                if (g0 && !g0.value) g0.value = parts.slice(1).join(' ');
            } else if (nameVal) {
                const g0 = document.getElementById('person_given_name_0');
                if (g0 && !g0.value) g0.value = nameVal;
            }

            // Immediately update flight row 0
            const fl0NameDisp = document.querySelector('#flight_person_row_0 .person-flight-name-disp');
            const fl0NameInput = document.querySelector('#flight_person_row_0 .person-flight-name');
            const fl0PassInput = document.querySelector('#flight_person_row_0 .person-flight-passport');
            if (fl0NameDisp && nameVal) fl0NameDisp.textContent = nameVal;
            if (fl0NameInput && nameVal) fl0NameInput.value = nameVal;
            if (fl0PassInput && passVal) fl0PassInput.value = passVal;

            if (typeof syncFlightPersons === 'function') syncFlightPersons();
            if (typeof syncAllHotelPersons === 'function') syncAllHotelPersons();
        }

        $(document).on('change select2:select select2:clear', '#clientSelect', function() {
            populateClientDetails(this);
        });
        document.getElementById('clientSelect')?.addEventListener('change', function() {
            populateClientDetails(this);
        });

        // Trigger on load if client is already pre-selected
        $(function() {
            const initClientEl = document.getElementById('clientSelect');
            if (initClientEl && initClientEl.value) {
                populateClientDetails(initClientEl);
            }
        });

        // Two-way live sync between Persons tab and Flight tab & Hotel tab
        $(document).on('input change', 'input[id^="person_surname_"], input[id^="person_given_name_"]', function() {
            const idx = this.id.split('_').pop();
            updatePersonFullName(idx);
        });

        $(document).on('input change', 'input[id^="person_name_"]', function() {
            const idx = this.id.split('_').pop();
            const val = this.value.trim();
            const flNameDisp = document.querySelector(`#flight_person_row_${idx} .person-flight-name-disp`);
            const flNameInput = document.querySelector(`#flight_person_row_${idx} .person-flight-name`);
            if (flNameDisp) flNameDisp.textContent = val || `Passenger ${parseInt(idx) + 1}`;
            if (flNameInput) flNameInput.value = val;

            document.querySelectorAll(`.hotel-person-row[data-person-idx="${idx}"]`).forEach(pRow => {
                const hNameDisp = pRow.querySelector('.person-hotel-name-disp');
                const hNameInput = pRow.querySelector('.person-hotel-name');
                if (hNameDisp) hNameDisp.textContent = val || `Passenger ${parseInt(idx) + 1}`;
                if (hNameInput) hNameInput.value = val;
            });
        });

        $(document).on('input change', 'input[id^="person_passport_"]', function() {
            const idx = this.id.split('_').pop();
            const passVal = this.value.trim();
            const flPassInput = document.querySelector(`#flight_person_row_${idx} .person-flight-passport`);
            if (flPassInput) flPassInput.value = passVal;

            if (idx === '0') {
                const fillPass = document.getElementById('fill_passport');
                if (fillPass) fillPass.value = passVal;
            }
        });

        $(document).on('input change', '.person-flight-passport', function() {
            const row = this.closest('.flight-person-row');
            if (!row) return;
            const idx = row.getAttribute('data-idx');
            const passVal = this.value.trim();
            const pPass = document.getElementById(`person_passport_${idx}`);
            if (pPass) pPass.value = passVal;

            if (idx === '0') {
                const fillPass = document.getElementById('fill_passport');
                if (fillPass) fillPass.value = passVal;
            }
        });

        $(document).on('input change', '#fill_passport', function() {
            const passVal = this.value.trim();
            const p0Pass = document.getElementById('person_passport_0');
            if (p0Pass) p0Pass.value = passVal;
            const fl0Pass = document.querySelector('#flight_person_row_0 .person-flight-passport');
            if (fl0Pass) fl0Pass.value = passVal;
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
            if (href === '#tab-transport') syncFlightDatesToTransport();
            if (href === '#tab-visa') {
                const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                const container = document.getElementById('visasList');
                if (container && container.querySelectorAll('.visa-card').length !== pax) rebuildVisasList(pax);
            }
            if (href === '#tab-persons') {
                const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                const container = document.getElementById('personsList');
                if (container && container.querySelectorAll('.person-card').length !== pax) rebuildPersonsList(pax);
            }
            if (href === '#tab-costing') {
                calculateFlight();
                calcTotal();
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function activateTabByHref(href) {
            if (!href) return;
            if (!href.startsWith('#')) href = '#' + href;
            const link = document.querySelector(`#bookingTabs .nav-link[href="${href}"]`);
            if (link) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    bootstrap.Tab.getOrCreateInstance(link).show();
                } else {
                    link.click();
                }
                const activeTabInput = document.getElementById('active_tab');
                if (activeTabInput) activeTabInput.value = href;
                if (history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', href);
                    history.replaceState(null, '', url);
                }
            }
        }

        function submitBookingForm(btn, action = 'save_stay') {
            const form = document.getElementById('bookingForm');
            if (!form) return false;

            // Set save action (save_stay or save_close)
            const saveActionInput = document.getElementById('save_action');
            if (saveActionInput) {
                saveActionInput.value = action;
            }

            // Determine active tab
            const activePane = btn?.closest('.tab-pane') || document.querySelector('.tab-pane.active') || document.querySelector('.tab-pane.show');
            const activeHref = activePane ? ('#' + activePane.id) : (document.querySelector('#bookingTabs .nav-link.active')?.getAttribute('href') || '#tab-details');
            const activeTabInput = document.getElementById('active_tab');
            if (activeTabInput) {
                activeTabInput.value = activeHref;
            }

            // 1. Client Validation
            const clientSelect = document.getElementById('clientSelect');
            if (!clientSelect || !clientSelect.value) {
                activateTabByHref('#tab-details');
                alert('Please select a Client before updating the booking.');
                setTimeout(() => {
                    if (typeof $ !== 'undefined' && $(clientSelect).data('select2')) {
                        $(clientSelect).select2('open');
                    } else if (clientSelect) {
                        clientSelect.focus();
                    }
                }, 250);
                return false;
            }

            // 2. Passengers Validation
            const adultsInput = document.getElementById('pax_adults');
            const paxAdults = parseInt(adultsInput?.value) || 0;
            const paxInput = document.getElementById('no_of_pax');
            const paxVal = parseInt(paxInput?.value) || 0;
            if (paxVal < 1 || paxAdults < 1) {
                activateTabByHref('#tab-details');
                alert('Please specify at least 1 passenger.');
                paxInput?.focus();
                return false;
            }

            // 3. FULL SYNCHRONIZATION of all tabs before submit
            try {
                if (typeof updatePaxCountsFromPersons === 'function') updatePaxCountsFromPersons();
                if (typeof syncFlightPersons === 'function') syncFlightPersons();
                if (typeof syncAllHotelPersons === 'function') syncAllHotelPersons();
                document.querySelectorAll('.hotel-block').forEach(r => {
                    if (typeof calculateHotelRow === 'function') calculateHotelRow(r);
                });
                document.querySelectorAll('.route-block').forEach(r => {
                    if (typeof calculateTransportRow === 'function') calculateTransportRow(r);
                });
                document.querySelectorAll('.visa-card').forEach(r => {
                    if (typeof calculateVisaRow === 'function') calculateVisaRow(r);
                });
                if (typeof calculateFlight === 'function') calculateFlight();
                if (typeof calcTotal === 'function') calcTotal();
            } catch (syncErr) {
                console.error('Error during pre-submit sync:', syncErr);
            }

            // 4. Update UI: show spinner & disable buttons
            const submitBtns = document.querySelectorAll('.btn-save-booking, button[type="submit"]');
            submitBtns.forEach(b => {
                b.disabled = true;
            });
            if (btn) {
                btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Saving...';
            }

            // 5. Submit the form
            try {
                HTMLFormElement.prototype.submit.call(form);
            } catch (e) {
                form.submit();
            }
            return true;
        }

        document.querySelectorAll('#bookingTabs .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const target = this.getAttribute('href');
                if (target === '#tab-flight') syncFlightPersons();
                if (target === '#tab-hotel') syncAllHotelPersons();
                if (target === '#tab-transport') syncFlightDatesToTransport(true);
                if (target === '#tab-visa') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    const container = document.getElementById('visasList');
                    if (container && container.querySelectorAll('.visa-card').length !== pax) rebuildVisasList(pax);
                }
                if (target === '#tab-persons') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    const container = document.getElementById('personsList');
                    if (container && container.querySelectorAll('.person-card').length !== pax) rebuildPersonsList(pax);
                }
                if (target === '#tab-costing') {
                    calculateFlight();
                    calcTotal();
                }
            });

            link.addEventListener('shown.bs.tab', function() {
                const target = this.getAttribute('href');
                if (target === '#tab-flight') syncFlightPersons();
                if (target === '#tab-hotel') syncAllHotelPersons();
                if (target === '#tab-transport') syncFlightDatesToTransport(true);
                if (target === '#tab-visa') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    const container = document.getElementById('visasList');
                    if (container && container.querySelectorAll('.visa-card').length !== pax) rebuildVisasList(pax);
                }
                if (target === '#tab-persons') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    const container = document.getElementById('personsList');
                    if (container && container.querySelectorAll('.person-card').length !== pax) rebuildPersonsList(pax);
                }
                if (target === '#tab-costing') {
                    calculateFlight();
                    calcTotal();
                }
                const activeTabInput = document.getElementById('active_tab');
                if (activeTabInput && target) activeTabInput.value = target;
                if (history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', target);
                    history.replaceState(null, '', url);
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
            newPax = Math.max(1, parseInt(newPax) || 1);
            let allCards = Array.from(container.querySelectorAll('.person-card'));

            if (newPax < allCards.length) {
                while (allCards.length > newPax) {
                    const cardToRemove = allCards.pop();
                    if (cardToRemove) cardToRemove.remove();
                }
                updatePaxCountsFromPersons();
                syncFlightPersons();
                syncAllHotelPersons();
                return;
            }

            const currentCount = allCards.length;
            for (let i = currentCount; i < newPax; i++) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="person-card" id="person_card_${i}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-primary" style="font-size:13px;">Passenger ${i + 1}</strong>
                            <span class="badge bg-primary person-type-badge" id="person_type_badge_${i}">Adult</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:12px;">Passenger Type <span class="text-danger">*</span></label>
                                <select name="persons[${i}][pax_type]" id="person_pax_type_${i}" class="form-select form-select-sm person-pax-type-select" onchange="onPersonPaxTypeChange(${i}, this.value)">
                                    <option value="adult" selected>Adult (12+ yrs)</option>
                                    <option value="child">Child (2-11 yrs)</option>
                                    <option value="infant">Infant (0-2 yrs)</option>
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Surname (Passport)</label>
                                <input type="text" name="persons[${i}][surname]" id="person_surname_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Given Name (Passport)</label>
                                <input type="text" name="persons[${i}][given_name]" id="person_given_name_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Full Name</label>
                                <input type="text" name="persons[${i}][full_name]" id="person_name_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Passport #</label>
                                <input type="text" name="persons[${i}][passport_number]" id="person_passport_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">DOB</label>
                                <input type="date" name="persons[${i}][dob]" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Gender</label>
                                <select name="persons[${i}][gender]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option><option value="male">Male</option><option value="female">Female</option>
                                </select></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">City</label>
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
            updatePaxCountsFromPersons();
            syncFlightPersons();
            syncAllHotelPersons();
        }

        function updatePaxCountsFromPersons() {
            let adults = 0;
            let children = 0;
            let infants = 0;

            const paxSelects = document.querySelectorAll('.person-pax-type-select');
            paxSelects.forEach((sel, idx) => {
                const val = sel.value || 'adult';
                if (val === 'child') children++;
                else if (val === 'infant') infants++;
                else adults++;

                const badge = document.getElementById(`person_type_badge_${idx}`);
                if (badge) {
                    if (val === 'child') {
                        badge.className = 'badge bg-info text-dark person-type-badge';
                        badge.textContent = 'Child';
                    } else if (val === 'infant') {
                        badge.className = 'badge bg-warning text-dark person-type-badge';
                        badge.textContent = 'Infant';
                    } else {
                        badge.className = 'badge bg-primary person-type-badge';
                        badge.textContent = 'Adult';
                    }
                }
            });

            if (paxSelects.length === 0) {
                adults = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            }

            const totalPaxVal = adults + children + infants;
            if (document.getElementById('bar_pax_badge')) document.getElementById('bar_pax_badge').textContent = totalPaxVal;
            if (document.getElementById('bar_pax_badge_header')) document.getElementById('bar_pax_badge_header').textContent = totalPaxVal;
            if (document.getElementById('bar_pax')) document.getElementById('bar_pax').textContent = `${totalPaxVal} (A:${adults}, C:${children}, I:${infants})`;

            if (document.getElementById('pax_adults')) document.getElementById('pax_adults').value = adults;
            if (document.getElementById('pax_children')) document.getElementById('pax_children').value = children;
            if (document.getElementById('pax_infants')) document.getElementById('pax_infants').value = infants;
            if (document.getElementById('hidden_pax_adults')) document.getElementById('hidden_pax_adults').value = adults;
            if (document.getElementById('hidden_pax_children')) document.getElementById('hidden_pax_children').value = children;
            if (document.getElementById('hidden_pax_infants')) document.getElementById('hidden_pax_infants').value = infants;

            if (document.getElementById('bar_pax_adults')) document.getElementById('bar_pax_adults').textContent = adults;
            if (document.getElementById('bar_pax_children')) document.getElementById('bar_pax_children').textContent = children;
            if (document.getElementById('bar_pax_infants')) document.getElementById('bar_pax_infants').textContent = infants;

            if (document.getElementById('flight_adult_pax_label')) document.getElementById('flight_adult_pax_label').textContent = adults;
            if (document.getElementById('flight_child_pax_label')) document.getElementById('flight_child_pax_label').textContent = children;
            if (document.getElementById('flight_infant_pax_label')) document.getElementById('flight_infant_pax_label').textContent = infants;

            calculateFlight();
            calcTotal();
        }

        function onPersonPaxTypeChange(idx, val) {
            // Update Flight row category
            const flRow = document.getElementById(`flight_person_row_${idx}`);
            if (flRow) {
                const flPaxTypeSel = flRow.querySelector('.person-flight-pax-type');
                if (flPaxTypeSel && flPaxTypeSel.value !== val) {
                    flPaxTypeSel.value = val;
                }
            }

            // Update Hotel badges
            document.querySelectorAll(`.hotel-person-row[data-person-idx="${idx}"] .person-hotel-type-badge`).forEach(b => {
                b.textContent = val.toUpperCase();
                b.className = `badge ${val === 'child' ? 'bg-info text-dark' : (val === 'infant' ? 'bg-warning text-dark' : 'bg-primary')} person-hotel-type-badge ms-1`;
            });

            // Update Visa badge
            const vBadge = document.querySelector(`#visa_card_${idx} .person-visa-type-badge`);
            if (vBadge) {
                vBadge.textContent = val.toUpperCase();
                vBadge.className = `badge ${val === 'child' ? 'bg-info text-dark' : (val === 'infant' ? 'bg-warning text-dark' : 'bg-primary')} person-visa-type-badge ms-1`;
            }

            updatePaxCountsFromPersons();
        }

        function onFlightPaxTypeChange(idx, val) {
            const pSel = document.getElementById(`person_pax_type_${idx}`);
            if (pSel && pSel.value !== val) {
                pSel.value = val;
            }
            onPersonPaxTypeChange(idx, val);
        }

        function syncFlightPersons() {
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const list = document.getElementById('flightPersonsList');
            if (!list) return;

            const curr = document.getElementById('flight_currency')?.value || 'SAR';
            const costRoe = (curr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_cost_roe')?.value) || parseFloat(document.getElementById('flight_roe')?.value) || 75);
            const saleRoe = (curr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_sale_roe')?.value) || costRoe || 75);
            const mainSupId = document.getElementById('flight_supplier_id')?.value || '';
            const depDate = document.querySelector('input[name="departure_date"]')?.value || '';
            const depTime = document.querySelector('input[name="departure_time"]')?.value || '';
            const depPnr  = document.querySelector('input[name="departure_pnr"]')?.value || '';

            // Preserve any existing DOM input values so user typing is not wiped out
            const existingRowData = {};
            list.querySelectorAll('.flight-person-row').forEach(row => {
                const idx = row.getAttribute('data-idx');
                if (idx !== null) {
                    const supEl = row.querySelector('.person-flight-supplier');
                    const pnrEl = row.querySelector('.person-flight-pnr');
                    const dateEl = row.querySelector('.person-flight-date');
                    const timeEl = row.querySelector('.person-flight-time');
                    const tripEl = row.querySelector('.person-flight-trip-type');

                    existingRowData[idx] = {
                        passport:           row.querySelector('.person-flight-passport')?.value,
                        pax_type:           row.querySelector('.person-flight-pax-type')?.value,
                        trip_type:          tripEl?.value,
                        dep_ticket:         row.querySelector('.person-flight-dep-ticket')?.value,
                        dep_cost:           row.querySelector('.person-flight-dep-cost')?.value,
                        dep_sell:           row.querySelector('.person-flight-dep-sell')?.value,
                        ret_ticket:         row.querySelector('.person-flight-ret-ticket')?.value,
                        ret_cost:           row.querySelector('.person-flight-ret-cost')?.value,
                        ret_sell:           row.querySelector('.person-flight-ret-sell')?.value,
                        ticket:             row.querySelector('.person-flight-ticket-main')?.value || row.querySelector('.person-flight-dep-ticket')?.value || row.querySelector('.person-flight-ticket')?.value,
                        ticket_status:      row.querySelector('.person-flight-status')?.value,
                        cost:               row.querySelector('.person-flight-cost')?.value,
                        sell:               row.querySelector('.person-flight-sell')?.value,
                        flight_supplier_id: supEl?.value || '',
                        return_flight_supplier_id: row.querySelector('.person-flight-ret-supplier')?.value || '',
                        flight_pnr:         pnrEl?.value,
                        flight_date:        dateEl?.value,
                        flight_time:        timeEl?.value,
                        custom_supplier:    supEl?.getAttribute('data-customized') === 'true' || (supEl && supEl.value !== ''),
                        custom_pnr:         pnrEl?.getAttribute('data-customized') === 'true',
                        custom_date:        dateEl?.getAttribute('data-customized') === 'true',
                        custom_time:        timeEl?.getAttribute('data-customized') === 'true',
                        custom_trip:        tripEl?.getAttribute('data-customized') === 'true',
                    };
                }
            });

            const clientPassVal = document.getElementById('fill_passport')?.value || document.getElementById('person_passport_0')?.value || (typeof clientsData !== 'undefined' && Array.isArray(clientsData) ? (clientsData.find(c => c.id == document.getElementById('clientSelect')?.value)?.passport_number || '') : '') || (typeof existingBooking !== 'undefined' && existingBooking ? (existingBooking.passport_number || (existingBooking.client ? existingBooking.client.passport_number : '')) : '');
            const mainSupSelect = document.getElementById('flight_supplier_id') || document.querySelector('select[name="flight_supplier_id"]');
            const mainSupName = (mainSupSelect && mainSupSelect.selectedIndex >= 0 && mainSupSelect.value) ? (mainSupSelect.options[mainSupSelect.selectedIndex]?.text?.replace(/^--.*--$/, '')?.trim() || '') : '';
            const mainTripVal = document.querySelector('input[name="flight_trip_type"]:checked')?.value;
            const defaultTripType = (mainTripVal === 'oneway' ? 'departure_only' : 'round_trip');

            let rowsHtml = '';
            for (let i = 0; i < pax; i++) {
                const pExisting = (typeof existingPersons !== 'undefined' && existingPersons && existingPersons[i]) ? existingPersons[i] : null;
                const personName = document.getElementById(`person_name_${i}`)?.value || (pExisting ? pExisting.full_name : '') || (i === 0 ? (document.querySelector('input[name="client_name"]')?.value || document.getElementById('clientSelect')?.selectedOptions[0]?.getAttribute('data-name') || '') : '') || `Passenger ${i + 1}`;
                const inDom = existingRowData[i];
                const domPass = document.getElementById(`person_passport_${i}`)?.value;
                const savedPass = (pExisting ? pExisting.passport_number : '');
                const rowPass = (inDom && inDom.passport !== undefined && inDom.passport !== '') ? inDom.passport : '';
                const personPass = domPass || (i === 0 ? clientPassVal : '') || savedPass || rowPass || '';

                if (i === 0 && personPass) {
                    const p0El = document.getElementById('person_passport_0');
                    if (p0El && !p0El.value) p0El.value = personPass;
                    const fillEl = document.getElementById('fill_passport');
                    if (fillEl && !fillEl.value) fillEl.value = personPass;
                }

                const personTypeFromDom = document.getElementById(`person_pax_type_${i}`)?.value;

                let isCustomSup  = false;
                let isCustomPnr  = false;
                let isCustomDate = false;
                let isCustomTime = false;
                let isCustomTrip = false;

                if (inDom) {
                    isCustomSup  = inDom.custom_supplier;
                    isCustomPnr  = inDom.custom_pnr;
                    isCustomDate = inDom.custom_date;
                    isCustomTime = inDom.custom_time;
                    isCustomTrip = inDom.custom_trip;
                } else if (pExisting) {
                    isCustomSup  = !!(pExisting.flight_supplier_id && mainSupId && pExisting.flight_supplier_id != mainSupId);
                    isCustomPnr  = !!(pExisting.flight_pnr && depPnr && pExisting.flight_pnr != depPnr);
                    isCustomDate = !!(pExisting.flight_date && depDate && pExisting.flight_date != depDate);
                    isCustomTime = !!(pExisting.flight_time && depTime && pExisting.flight_time != depTime);
                    isCustomTrip = !!(pExisting.trip_type && pExisting.trip_type != defaultTripType);
                }

                let pType      = personTypeFromDom || (inDom ? inDom.pax_type : (pExisting ? pExisting.pax_type : 'adult'));
                let pTripType  = isCustomTrip ? (inDom ? inDom.trip_type : (pExisting ? pExisting.trip_type : defaultTripType)) : defaultTripType;
                let pDepTicket = inDom ? inDom.dep_ticket : (pExisting ? (pExisting.departure_ticket || pExisting.flight_ticket || '') : '');
                let pDepCost   = inDom ? inDom.dep_cost : (pExisting && (parseFloat(pExisting.departure_cost) > 0) ? pExisting.departure_cost : '');
                let pDepSell   = inDom ? inDom.dep_sell : (pExisting && (parseFloat(pExisting.departure_sell) > 0) ? pExisting.departure_sell : '');
                let pRetTicket = inDom ? inDom.ret_ticket : (pExisting ? (pExisting.return_ticket || '') : '');
                let pRetCost   = inDom ? inDom.ret_cost : (pExisting && (parseFloat(pExisting.return_cost) > 0) ? pExisting.return_cost : '');
                let pRetSell   = inDom ? inDom.ret_sell : (pExisting && (parseFloat(pExisting.return_sell) > 0) ? pExisting.return_sell : '');
                let pTicket    = inDom ? inDom.ticket : (pExisting ? (pExisting.flight_ticket || pDepTicket || '') : '');
                let pStatus    = inDom ? (inDom.ticket_status || 'confirmed') : (pExisting ? (pExisting.ticket_status || 'confirmed') : 'confirmed');
                let pCost      = inDom ? inDom.cost : (pExisting && (parseFloat(pExisting.flight_cost) > 0) ? pExisting.flight_cost : '');
                let pSell      = inDom ? inDom.sell : (pExisting && (parseFloat(pExisting.flight_sell) > 0) ? pExisting.flight_sell : '');
                let pSup       = isCustomSup ? (inDom ? inDom.flight_supplier_id : (pExisting ? pExisting.flight_supplier_id : '')) : '';
                let pRetSup    = inDom ? (inDom.return_flight_supplier_id || '') : (pExisting ? (pExisting.return_flight_supplier_id || '') : '');
                let pPnr       = isCustomPnr ? (inDom ? inDom.flight_pnr : (pExisting ? pExisting.flight_pnr : depPnr)) : depPnr;
                let pDate      = isCustomDate ? (inDom ? inDom.flight_date : (pExisting ? pExisting.flight_date : depDate)) : depDate;
                let pTime      = isCustomTime ? (inDom ? inDom.flight_time : (pExisting ? pExisting.flight_time : depTime)) : depTime;

                // Smart defaults for legacy bookings if cost/sell is still blank
                if (pCost === '' && pDepCost === '' && pRetCost === '' && typeof existingBooking !== 'undefined' && existingBooking) {
                    if (existingBooking.flight_adult_cost > 0) {
                        pCost = existingBooking.flight_adult_cost;
                    } else if (existingBooking.flight_cost_rate > 0) {
                        pCost = (pax > 1 ? (parseFloat(existingBooking.flight_cost_rate) / pax).toFixed(2) : existingBooking.flight_cost_rate);
                    } else if (existingBooking.flight_cost > 0 && costRoe > 0) {
                        pCost = (parseFloat(existingBooking.flight_cost) / (pax * costRoe)).toFixed(2);
                    }
                }
                if (pSell === '' && pDepSell === '' && pRetSell === '' && typeof existingBooking !== 'undefined' && existingBooking) {
                    if (existingBooking.flight_adult_sell > 0) {
                        pSell = existingBooking.flight_adult_sell;
                    } else if (existingBooking.flight_selling_rate > 0) {
                        pSell = (pax > 1 ? (parseFloat(existingBooking.flight_selling_rate) / pax).toFixed(2) : existingBooking.flight_selling_rate);
                    } else if (existingBooking.flight_charges > 0 && saleRoe > 0) {
                        pSell = (parseFloat(existingBooking.flight_charges) / (pax * saleRoe)).toFixed(2);
                    }
                }

                const costVal = parseFloat(pCost) || ((parseFloat(pDepCost) || 0) + (parseFloat(pRetCost) || 0)) || 0;
                const sellVal = parseFloat(pSell) || ((parseFloat(pDepSell) || 0) + (parseFloat(pRetSell) || 0)) || 0;

                // Build supplier dropdown options
                let supOptionsHtml = `<option value="">-- Same as Main ${mainSupName ? '(' + mainSupName + ')' : 'Supplier'} --</option>`;
                let retSupOptionsHtml = `<option value="">-- Ret: Same as Dep Supplier --</option>`;
                if (typeof suppliersData !== 'undefined' && Array.isArray(suppliersData)) {
                    supOptionsHtml += suppliersData.map(s => `<option value="${s.id}" ${s.id == pSup ? 'selected' : ''}>${s.name}</option>`).join('');
                    retSupOptionsHtml += suppliersData.map(s => `<option value="${s.id}" ${s.id == pRetSup ? 'selected' : ''}>${s.name}</option>`).join('');
                }

                const isCancelled = (pStatus === 'cancelled');
                const isRefunded = (pStatus === 'refunded');

                rowsHtml += `
                <tr class="flight-person-row ${isCancelled ? 'table-danger' : (isRefunded ? 'table-warning' : '')}" data-idx="${i}" id="flight_person_row_${i}" style="${isCancelled ? 'opacity:0.65; text-decoration: line-through;' : ''}">
                    <td class="text-center text-muted fw-bold">${i + 1}</td>
                    <td>
                        <strong class="text-primary person-flight-name-disp">${personName}</strong>
                        <input type="hidden" name="flight_persons[${i}][name]" class="person-flight-name" value="${personName}">
                        <div class="mt-1">
                            <input type="text" name="flight_persons[${i}][passport]" class="form-control form-control-sm person-flight-passport" value="${personPass}" placeholder="Passport #">
                        </div>
                    </td>
                    <td>
                        <select name="flight_persons[${i}][pax_type]" class="form-select form-select-sm person-flight-pax-type" onchange="onFlightPaxTypeChange(${i}, this.value)">
                            <option value="adult" ${pType === 'adult' ? 'selected' : ''}>Adult</option>
                            <option value="child" ${pType === 'child' ? 'selected' : ''}>Child</option>
                            <option value="infant" ${pType === 'infant' ? 'selected' : ''}>Infant</option>
                        </select>
                        <select name="flight_persons[${i}][trip_type]" class="form-select form-select-sm person-flight-trip-type mt-1 border-primary" onchange="onTripTypeChange(${i}, this.value); onPersonFlightTravelInput(this);" data-customized="${isCustomTrip ? 'true' : 'false'}" title="Choose Trip Type">
                            <option value="round_trip" ${pTripType === 'round_trip' ? 'selected' : ''}>Round Trip</option>
                            <option value="departure_only" ${pTripType === 'departure_only' ? 'selected' : ''}>Departure Only (One Way)</option>
                            <option value="return_only" ${pTripType === 'return_only' ? 'selected' : ''}>Return Only (Inbound)</option>
                        </select>
                    </td>
                    <td>
                        <select name="flight_persons[${i}][flight_supplier_id]" class="form-select form-select-sm person-flight-supplier" data-customized="${isCustomSup && pSup ? 'true' : 'false'}" onchange="onPersonFlightSupplierChange(this, ${i})" title="Departure Supplier">
                            ${supOptionsHtml}
                        </select>
                        <select name="flight_persons[${i}][return_flight_supplier_id]" class="form-select form-select-sm person-flight-ret-supplier mt-1" title="Return Supplier">
                            ${retSupOptionsHtml}
                        </select>
                        <span class="badge ${isCustomSup && pSup ? 'bg-soft-info text-primary border border-info border-opacity-25' : 'bg-light text-muted border'} fs-10 mt-1 d-inline-block person-supplier-badge" id="person_sup_badge_${i}">
                            <i class="mdi ${isCustomSup && pSup ? 'mdi-account-cog' : 'mdi-link-variant'} me-1"></i>${isCustomSup && pSup ? 'Individual Supplier' : 'Linked to Main Supplier'}
                        </span>
                        <input type="text" name="flight_persons[${i}][flight_pnr]" class="form-control form-control-sm person-flight-pnr mt-1 ${isCustomPnr ? 'border-info' : ''}" value="${pPnr}" placeholder="PNR #" data-customized="${isCustomPnr ? 'true' : 'false'}" oninput="onPersonFlightTravelInput(this)">
                    </td>
                    {{-- Departure Ticket --}}
                    <td class="box-dep-ticket ${pTripType === 'return_only' ? 'opacity-25' : ''}">
                        <div class="mb-1">
                            <input type="text" name="flight_persons[${i}][departure_ticket]" class="form-control form-control-sm person-flight-ticket person-flight-dep-ticket" value="${pDepTicket || pTicket}" placeholder="Departure Ticket #" maxlength="13" ${pTripType === 'return_only' ? 'disabled' : ''}>
                        </div>
                        <div class="d-flex gap-1">
                            <input type="number" step="0.01" name="flight_persons[${i}][departure_cost]" class="form-control form-control-sm text-end person-flight-dep-cost flight-rate-calc" value="${pDepCost}" placeholder="Cost" title="Departure Cost" ${pTripType === 'return_only' ? 'disabled' : ''}>
                            <input type="number" step="0.01" name="flight_persons[${i}][departure_sell]" class="form-control form-control-sm text-end person-flight-dep-sell flight-rate-calc" value="${pDepSell}" placeholder="Sell" title="Departure Sell" ${pTripType === 'return_only' ? 'disabled' : ''}>
                        </div>
                    </td>
                    {{-- Return Ticket --}}
                    <td class="box-ret-ticket ${pTripType === 'departure_only' ? 'opacity-25' : ''}">
                        <div class="mb-1">
                            <input type="text" name="flight_persons[${i}][return_ticket]" class="form-control form-control-sm person-flight-ticket person-flight-ret-ticket" value="${pRetTicket}" placeholder="Return Ticket #" maxlength="13" ${pTripType === 'departure_only' ? 'disabled' : ''}>
                        </div>
                        <div class="d-flex gap-1">
                            <input type="number" step="0.01" name="flight_persons[${i}][return_cost]" class="form-control form-control-sm text-end person-flight-ret-cost flight-rate-calc" value="${pRetCost}" placeholder="Cost" title="Return Cost" ${pTripType === 'departure_only' ? 'disabled' : ''}>
                            <input type="number" step="0.01" name="flight_persons[${i}][return_sell]" class="form-control form-control-sm text-end person-flight-ret-sell flight-rate-calc" value="${pRetSell}" placeholder="Sell" title="Return Sell" ${pTripType === 'departure_only' ? 'disabled' : ''}>
                        </div>
                    </td>
                    {{-- Total Cost & Total Sell --}}
                    <td>
                        <input type="hidden" name="flight_persons[${i}][ticket]" class="person-flight-ticket-main" value="${pTicket || pDepTicket || pRetTicket}">
                        <input type="number" step="0.01" name="flight_persons[${i}][flight_cost]" class="form-control form-control-sm text-end person-flight-cost flight-rate-calc fw-bold text-danger bg-light" value="${pCost || (costVal > 0 ? costVal.toFixed(2) : '')}" placeholder="0.00" readonly>
                        <small class="text-danger d-block text-end person-flight-cost-pkr-disp" style="font-size:11px;">${(costVal * costRoe).toFixed(2)} PKR</small>
                        <input type="number" step="0.01" name="flight_persons[${i}][flight_sell]" class="form-control form-control-sm text-end person-flight-sell flight-rate-calc fw-bold text-success bg-light mt-1" value="${pSell || (sellVal > 0 ? sellVal.toFixed(2) : '')}" placeholder="0.00" readonly>
                        <small class="text-success d-block text-end person-flight-sell-pkr-disp" style="font-size:11px;">${(sellVal * saleRoe).toFixed(2)} PKR</small>
                    </td>
                    <td>
                        <select name="flight_persons[${i}][ticket_status]" class="form-select form-select-sm person-flight-status mb-1 ${isCancelled ? 'bg-soft-danger text-danger border-danger fw-bold' : (isRefunded ? 'bg-soft-warning text-warning border-warning fw-bold' : 'text-success fw-bold')}" onchange="handleTicketStatusChange(this)">
                            <option value="confirmed" ${pStatus === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option value="cancelled" ${pStatus === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            <option value="refunded" ${pStatus === 'refunded' ? 'selected' : ''}>Refunded</option>
                        </select>
                        <input type="date" name="flight_persons[${i}][flight_date]" class="form-control form-control-sm person-flight-date ${isCustomDate ? 'border-info' : ''}" value="${pDate}" title="Flight date" data-customized="${isCustomDate ? 'true' : 'false'}" onchange="onPersonFlightTravelInput(this)">
                        <input type="text" name="flight_persons[${i}][flight_time]" class="form-control form-control-sm person-flight-time flight-time-picker mt-1 ${isCustomTime ? 'border-info' : ''}" value="${pTime}" placeholder="hh:mm" title="Flight time" data-customized="${isCustomTime ? 'true' : 'false'}" onchange="onPersonFlightTravelInput(this)">
                    </td>
                </tr>`;
            }

            list.innerHTML = `
                <div class="p-2 px-3 mb-2 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <span class="fw-bold text-dark fs-12"><i class="mdi mdi-airplane me-1 text-primary"></i> Passenger-Wise Flight & Ticket Management:</span>
                        <small class="text-muted d-block" style="font-size:11px;">Each passenger can have unique tickets, separate suppliers, Departure & Return tickets/costs/sells, custom flight details (Date/Time/PNR), and rates.</small>
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
                                <th style="width: 14%;">Passenger & Passport</th>
                                <th style="width: 15%;">Pax Type & Trip</th>
                                <th style="width: 13%;">Supplier & PNR</th>
                                <th style="width: 18%;">Departure Ticket (Cost / Sell)</th>
                                <th style="width: 18%;">Return Ticket (Cost / Sell)</th>
                                <th style="width: 11%;" class="text-end">Total Rate (<span class="flight-curr-label">${curr}</span>)</th>
                                <th style="width: 11%;">Status & Date</th>
                            </tr>
                        </thead>
                        <tbody id="flightPersonsTableBody">
                            ${rowsHtml}
                        </tbody>
                        <tfoot class="table-light fw-bold fs-12 border-top">
                            <tr>
                                <td colspan="6" class="text-dark">Total Flight Tickets (${pax} Passengers)</td>
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
            checkUniqueTicketNumbers();
        }

        function handlePaxChange(newVal) {
            const val = Math.max(1, parseInt(newVal) || 1);
            const paxInput = document.getElementById('no_of_pax');
            if (paxInput && parseInt(paxInput.value) !== val) {
                paxInput.value = val;
            }
            if (document.getElementById('bar_pax')) {
                document.getElementById('bar_pax').textContent = val;
            }
            if (document.getElementById('bar_pax_badge')) {
                document.getElementById('bar_pax_badge').textContent = val;
            }
            if (document.getElementById('bar_pax_badge_header')) {
                document.getElementById('bar_pax_badge_header').textContent = val;
            }
            rebuildPersonsList(val);
            rebuildVisasList(val);
            updatePaxCountsFromPersons();
            syncFlightPersons();
            syncAllHotelPersons();
            calculateFlight();
            document.querySelectorAll('.hotel-block').forEach(r => calculateHotelRow(r));
            if (typeof calculateAllTransports === 'function') calculateAllTransports();
            calcTotal();
            if (typeof updateHotelStaySummary === 'function') updateHotelStaySummary();
            if (typeof updateTransportSummary === 'function') updateTransportSummary();
        }
        window.handlePaxChange = handlePaxChange;

        $(document).on('input change', '#no_of_pax', function() {
            handlePaxChange(this.value);
        });
        document.getElementById('no_of_pax')?.addEventListener('input', function() {
            handlePaxChange(this.value);
        });
        document.getElementById('no_of_pax')?.addEventListener('change', function() {
            handlePaxChange(this.value);
        });

        function rebuildVisasList(newPax) {
            const container = document.getElementById('visasList');
            if (!container) return;
            newPax = Math.max(1, parseInt(newPax) || 1);
            let allVisas = Array.from(container.querySelectorAll('.visa-card'));

            if (newPax < allVisas.length) {
                while (allVisas.length > newPax) {
                    const visaToRemove = allVisas.pop();
                    if (visaToRemove) visaToRemove.remove();
                }
                calcTotal();
                return;
            }

            const currentCount = allVisas.length;

            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);

            const defVisaCurr = document.getElementById('default_visa_currency')?.value;
            const defVisaCostRoe = document.getElementById('default_visa_cost_roe')?.value;
            const defVisaSaleRoe = document.getElementById('default_visa_sale_roe')?.value;
            const defVisaCostRate = document.getElementById('default_visa_cost_rate')?.value;
            const defVisaSellRate = document.getElementById('default_visa_sell_rate')?.value;
            const defVisaSup = document.getElementById('default_visa_supplier_id')?.value;

            const fallbackCurr = defVisaCurr || flightCurr;
            const isDefPkr = (fallbackCurr === 'PKR');
            const fallbackCostRoe = isDefPkr ? 1 : (defVisaCostRoe || defaultCostRoe);
            const fallbackSaleRoe = isDefPkr ? 1 : (defVisaSaleRoe || defaultSaleRoe || fallbackCostRoe);

            for (let i = currentCount; i < newPax; i++) {
                const v = (typeof existingVisas !== 'undefined' && existingVisas && existingVisas[i]) ? existingVisas[i] : {};
                const pName = document.getElementById(`person_name_${i}`)?.value || (v.given_name || '');
                const pPass = document.getElementById(`person_passport_${i}`)?.value || (v.passport_number || '');
                const pDob = document.getElementById(`person_dob_${i}`)?.value || (v.date_of_birth || '');
                const pType = document.getElementById(`person_pax_type_${i}`)?.value || 'adult';
                const vBadgeClass = pType === 'child' ? 'bg-info text-dark' : (pType === 'infant' ? 'bg-warning text-dark' : 'bg-primary');
                const vTypeLabel = pType.toUpperCase();

                const vCurr = v.currency || fallbackCurr;
                const vIsPkr = (vCurr === 'PKR');
                const costRoe = vIsPkr ? 1 : (v.cost_roe || v.roe || fallbackCostRoe);
                const saleRoe = vIsPkr ? 1 : (v.sale_roe || costRoe || fallbackSaleRoe);

                const costRate = parseFloat(v.cost_rate) || parseFloat(defVisaCostRate) || 0;
                const costAmt = parseFloat(v.cost_amount) || (costRate * parseFloat(costRoe));
                const sellRate = parseFloat(v.selling_rate) || parseFloat(defVisaSellRate) || 0;
                const sellAmt = parseFloat(v.selling_amount) || (sellRate * parseFloat(saleRoe));

                const vBookingDate = v.booking_date || '{{ date('Y-m-d') }}';
                const supId = v.supplier_id || defVisaSup || '';

                let supOptions = `<option value="">-- Select Supplier --</option>` +
                    (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}" ${s.id == supId ? 'selected' : ''}>${s.name}</option>`).join('') : '');

                const visaVoucherBase = document.getElementById('visa_voucher_no')?.value || '{{ $booking->visa_voucher_no ?? ($defaultVouchers["visa"] ?? ("VIS-" . date("Y") . "-00001")) }}';
                const vChildVoucher = v.voucher_no || (visaVoucherBase + '-' + (i + 1));

                container.insertAdjacentHTML('beforeend', `
                    <div class="visa-card border rounded p-3 mb-2 bg-white" id="visa_card_${i}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <strong class="text-warning" style="font-size:13px;">
                                    <i class="mdi mdi-passport me-1"></i> Visa ${i + 1}: ${pName || 'Passenger ' + (i + 1)}
                                </strong>
                                <span class="badge ${vBadgeClass} person-visa-type-badge" style="font-size:10px;">${vTypeLabel}</span>
                                <span class="badge bg-soft-danger text-danger border border-danger fs-11 font-monospace">
                                    <i class="mdi mdi-ticket-confirmation me-1"></i>Voucher #: ${vChildVoucher}
                                </span>
                                <input type="hidden" name="visas[${i}][voucher_no]" value="${vChildVoucher}">
                            </div>
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
                                <select name="visas[${i}][supplier_id]" class="form-select form-select-sm visa-supplier-select select2">
                                    ${supOptions}
                                </select></div>
                            <div class="col-md-6"><label class="form-label" style="font-size:12px;">Send To</label>
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
                                <input type="date" name="visas[${i}][booking_date]" class="form-control form-control-sm visa-booking-date" value="${vBookingDate}"></div>

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
            if (typeof initBookingSelect2 === 'function') {
                initBookingSelect2(container);
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
                    const statusSelect = row.querySelector('.person-flight-status');
                    const ticketStatus = statusSelect?.value || 'confirmed';
                    const isCancelled = (ticketStatus === 'cancelled');

                    const tripType = row.querySelector('.person-flight-trip-type')?.value || 'round_trip';
                    const depCost = parseFloat(row.querySelector('.person-flight-dep-cost')?.value) || 0;
                    const depSell = parseFloat(row.querySelector('.person-flight-dep-sell')?.value) || 0;
                    const retCost = parseFloat(row.querySelector('.person-flight-ret-cost')?.value) || 0;
                    const retSell = parseFloat(row.querySelector('.person-flight-ret-sell')?.value) || 0;

                    const costInput = row.querySelector('.person-flight-cost');
                    const sellInput = row.querySelector('.person-flight-sell');

                    let costVal = parseFloat(costInput?.value) || 0;
                    let sellVal = parseFloat(sellInput?.value) || 0;

                    if (tripType === 'departure_only') {
                        costVal = depCost;
                        sellVal = depSell;
                    } else if (tripType === 'return_only') {
                        costVal = retCost;
                        sellVal = retSell;
                    } else {
                        if (depCost > 0 || retCost > 0) costVal = depCost + retCost;
                        if (depSell > 0 || retSell > 0) sellVal = depSell + retSell;
                    }

                    if (costInput && (depCost > 0 || retCost > 0 || costVal > 0)) costInput.value = costVal > 0 ? costVal.toFixed(2) : '';
                    if (sellInput && (depSell > 0 || retSell > 0 || sellVal > 0)) sellInput.value = sellVal > 0 ? sellVal.toFixed(2) : '';

                    if (costVal > 0 || sellVal > 0) hasCustomPersonRates = true;

                    if (!isCancelled) {
                        totalCostCurr += costVal;
                        totalSellCurr += sellVal;
                    }

                    const costPkrDisp = row.querySelector('.person-flight-cost-pkr-disp');
                    const sellPkrDisp = row.querySelector('.person-flight-sell-pkr-disp');
                    if (costPkrDisp) costPkrDisp.textContent = (costVal * costRoe).toFixed(2) + ' PKR' + (isCancelled ? ' (Void)' : '');
                    if (sellPkrDisp) sellPkrDisp.textContent = (sellVal * saleRoe).toFixed(2) + ' PKR' + (isCancelled ? ' (Void)' : '');
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
            if (elCostSarTot) elCostSarTot.textContent = (curr === 'PKR') ? '—' : (totalCostCurr.toFixed(2) + ' ' + curr);
            const elCostPkrTot = document.getElementById('flight_cost_pkr_total');
            if (elCostPkrTot) elCostPkrTot.textContent = totalCostPkr.toFixed(2) + ' PKR';
            const elSellSarTot = document.getElementById('flight_sell_sar_total');
            if (elSellSarTot) elSellSarTot.textContent = (curr === 'PKR') ? '—' : (totalSellCurr.toFixed(2) + ' ' + curr);
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

        window.onTripTypeChange = function(idx, val) {
            const row = document.getElementById(`flight_person_row_${idx}`);
            if (!row) return;
            const depBox = row.querySelector('.box-dep-ticket');
            const retBox = row.querySelector('.box-ret-ticket');
            if (val === 'departure_only') {
                if (depBox) {
                    depBox.classList.remove('opacity-25');
                    depBox.querySelectorAll('input').forEach(i => i.disabled = false);
                }
                if (retBox) {
                    retBox.classList.add('opacity-25');
                    retBox.querySelectorAll('input').forEach(i => i.disabled = true);
                }
            } else if (val === 'return_only') {
                if (retBox) {
                    retBox.classList.remove('opacity-25');
                    retBox.querySelectorAll('input').forEach(i => i.disabled = false);
                }
                if (depBox) {
                    depBox.classList.add('opacity-25');
                    depBox.querySelectorAll('input').forEach(i => i.disabled = true);
                }
            } else {
                if (depBox) {
                    depBox.classList.remove('opacity-25');
                    depBox.querySelectorAll('input').forEach(i => i.disabled = false);
                }
                if (retBox) {
                    retBox.classList.remove('opacity-25');
                    retBox.querySelectorAll('input').forEach(i => i.disabled = false);
                }
            }
            calculateFlight();
        };

        // Helper to robustly select airline in dropdown by code, name or partial text
        function setSelectAirline(sel, airVal) {
            if (!sel || !airVal) return;
            const $sel = (typeof jQuery !== 'undefined') ? jQuery(sel) : null;
            if ($sel && $sel.length) {
                $sel.val(airVal);
                if ($sel.val()) return;

                const airLower = String(airVal).toLowerCase().trim();
                let matchedVal = null;
                $sel.find('option').each(function() {
                    const optVal = $(this).val();
                    const optText = $(this).text().toLowerCase().trim();
                    if (optVal && (optVal.toLowerCase().trim() === airLower || optText.includes(airLower) || airLower.includes(optVal.toLowerCase().trim()))) {
                        matchedVal = optVal;
                        return false;
                    }
                });
                if (matchedVal) {
                    $sel.val(matchedVal);
                } else {
                    $sel.append(new Option(airVal, airVal, true, true));
                }
            } else if (sel.options) {
                sel.value = airVal;
                if (sel.value) return;
                const airLower = String(airVal).toLowerCase().trim();
                for (let i = 0; i < sel.options.length; i++) {
                    const opt = sel.options[i];
                    const optVal = opt.value || '';
                    const optText = (opt.text || '').toLowerCase().trim();
                    if (optVal && (optVal.toLowerCase().trim() === airLower || optText.includes(airLower) || airLower.includes(optVal.toLowerCase().trim()))) {
                        sel.value = optVal;
                        return;
                    }
                }
                sel.add(new Option(airVal, airVal, true, true));
            }
        }

        // Auto-sync Flight Departure and Arrival dates/times into Transport routes
        function syncFlightDatesToTransport(force = false) {
            const depDate   = $('input[name="departure_date"]').val() || '';
            const depTime   = $('input[name="departure_time"]').val() || '';
            const depFlight = $('input[name="departure_flight"]').val() || '';
            const depAir    = $('select[name="departure_airline"]').val() || '';

            const arrDate   = $('input[name="arrival_date"]').val() || '';
            const arrTime   = $('input[name="arrival_time"]').val() || '';
            const arrFlight = $('input[name="arrival_flight"]').val() || '';
            const arrAir    = $('select[name="arrival_airline"]').val() || '';

            // 1. Sync Route 1 (Outbound / Arrival into KSA):
            const route0 = $('.route-block').first();
            if (route0.length && (depDate || depTime || depFlight || depAir)) {
                const r0Date = route0.find('input[name*="[travel_date]"]');
                if (r0Date.length && (force || !r0Date.val() || r0Date.attr('data-auto') === 'true')) {
                    if (depDate) r0Date.val(depDate).attr('data-auto', 'true').trigger('change');
                }
                const r0Time = route0.find('input[name*="[travel_time]"]');
                if (r0Time.length && (force || !r0Time.val() || r0Time.attr('data-auto') === 'true')) {
                    if (depTime) r0Time.val(depTime).attr('data-auto', 'true');
                }
                const r0Flight = route0.find('input[name*="[flight_no]"]');
                if (r0Flight.length && (force || !r0Flight.val() || r0Flight.attr('data-auto') === 'true')) {
                    if (depFlight) r0Flight.val(depFlight).attr('data-auto', 'true');
                }
                const r0Air = route0.find('select[name*="[airline]"]');
                if (r0Air.length && (force || !r0Air.val() || r0Air.attr('data-auto') === 'true')) {
                    if (depAir) {
                        setSelectAirline(r0Air, depAir);
                        r0Air.attr('data-auto', 'true');
                    }
                }
                const r0From = route0.find('input[name*="[pickup_from]"]');
                if (r0From.length && (force || !r0From.val() || r0From.attr('data-auto') === 'true')) {
                    r0From.val('JED APT').attr('data-auto', 'true');
                }
                const r0To = route0.find('input[name*="[destination]"]');
                if (r0To.length && (force || !r0To.val() || r0To.attr('data-auto') === 'true')) {
                    r0To.val('MAK HTL').attr('data-auto', 'true');
                }
            }

            // 2. Sync Route 2 (Inbound / Return from KSA):
            if (arrDate || arrTime || arrFlight || arrAir) {
                const routeBlocks = $('.route-block');
                if (routeBlocks.length >= 2) {
                    const routeReturn = routeBlocks.eq(1);
                    const rRetDate = routeReturn.find('input[name*="[travel_date]"]');
                    if (rRetDate.length && (force || !rRetDate.val() || rRetDate.attr('data-auto') === 'true')) {
                        if (arrDate) rRetDate.val(arrDate).attr('data-auto', 'true').trigger('change');
                    }
                    const rRetTime = routeReturn.find('input[name*="[travel_time]"]');
                    if (rRetTime.length && (force || !rRetTime.val() || rRetTime.attr('data-auto') === 'true')) {
                        if (arrTime) rRetTime.val(arrTime).attr('data-auto', 'true');
                    }
                    const rRetFlight = routeReturn.find('input[name*="[flight_no]"]');
                    if (rRetFlight.length && (force || !rRetFlight.val() || rRetFlight.attr('data-auto') === 'true')) {
                        if (arrFlight) rRetFlight.val(arrFlight).attr('data-auto', 'true');
                    }
                    const rRetAir = routeReturn.find('select[name*="[airline]"]');
                    if (rRetAir.length && (force || !rRetAir.val() || rRetAir.attr('data-auto') === 'true')) {
                        if (arrAir) {
                            setSelectAirline(rRetAir, arrAir);
                            rRetAir.attr('data-auto', 'true');
                        }
                    }
                    const rRetFrom = routeReturn.find('input[name*="[pickup_from]"]');
                    if (rRetFrom.length && (force || !rRetFrom.val() || rRetFrom.attr('data-auto') === 'true')) {
                        rRetFrom.val('MAK HTL').attr('data-auto', 'true');
                    }
                    const rRetTo = routeReturn.find('input[name*="[destination]"]');
                    if (rRetTo.length && (force || !rRetTo.val() || rRetTo.attr('data-auto') === 'true')) {
                        rRetTo.val('JED APT').attr('data-auto', 'true');
                    }
                }
            }

            if (typeof updateTransportSummary === 'function') updateTransportSummary();
        }

        $(document).on('input change', 'input[name="departure_date"], input[name="departure_time"], input[name="departure_flight"], select[name="departure_airline"], input[name="arrival_date"], input[name="arrival_time"], input[name="arrival_flight"], select[name="arrival_airline"]', function() {
            syncFlightDatesToTransport();
        });

        $(document).on('input change', '.route-block input[name*="[travel_date]"], .route-block input[name*="[travel_time]"], .route-block input[name*="[flight_no]"], .route-block select[name*="[airline]"]', function(e) {
            if (e.originalEvent) {
                $(this).attr('data-auto', 'false');
            }
        });

        $(document).on('click', '.btn-set-transport-dep', function(e) {
            e.preventDefault();
            const depDate = $('input[name="departure_date"]').val() || '';
            const depTime = $('input[name="departure_time"]').val() || '';
            const depFlight = $('input[name="departure_flight"]').val() || '';
            const depAir = $('select[name="departure_airline"]').val() || '';

            const block = $(this).closest('.route-block');
            if (depDate || depTime || depFlight || depAir) {
                if (depDate) block.find('input[name*="[travel_date]"]').val(depDate).attr('data-auto', 'true').trigger('change');
                if (depTime) block.find('input[name*="[travel_time]"]').val(depTime).attr('data-auto', 'true');
                if (depFlight) block.find('input[name*="[flight_no]"]').val(depFlight).attr('data-auto', 'true');
                if (depAir) {
                    setSelectAirline(block.find('select[name*="[airline]"]'), depAir);
                    block.find('select[name*="[airline]"]').attr('data-auto', 'true');
                }
                const pFrom = block.find('input[name*="[pickup_from]"]');
                if (pFrom.length && (!pFrom.val() || pFrom.attr('data-auto') === 'true')) pFrom.val('JED APT').attr('data-auto', 'true');
                const pTo = block.find('input[name*="[destination]"]');
                if (pTo.length && (!pTo.val() || pTo.attr('data-auto') === 'true')) pTo.val('MAK HTL').attr('data-auto', 'true');
                if (typeof updateTransportSummary === 'function') updateTransportSummary();
            } else {
                alert('Please enter Departure Date / Flight details in Flight tab first.');
            }
        });

        $(document).on('click', '.btn-set-transport-arr', function(e) {
            e.preventDefault();
            const arrDate = $('input[name="arrival_date"]').val() || '';
            const arrTime = $('input[name="arrival_time"]').val() || '';
            const arrFlight = $('input[name="arrival_flight"]').val() || '';
            const arrAir = $('select[name="arrival_airline"]').val() || '';

            const block = $(this).closest('.route-block');
            if (arrDate || arrTime || arrFlight || arrAir) {
                if (arrDate) block.find('input[name*="[travel_date]"]').val(arrDate).attr('data-auto', 'true').trigger('change');
                if (arrTime) block.find('input[name*="[travel_time]"]').val(arrTime).attr('data-auto', 'true');
                if (arrFlight) block.find('input[name*="[flight_no]"]').val(arrFlight).attr('data-auto', 'true');
                if (arrAir) {
                    setSelectAirline(block.find('select[name*="[airline]"]'), arrAir);
                    block.find('select[name*="[airline]"]').attr('data-auto', 'true');
                }
                const pFrom = block.find('input[name*="[pickup_from]"]');
                if (pFrom.length && (!pFrom.val() || pFrom.attr('data-auto') === 'true')) pFrom.val('MAK HTL').attr('data-auto', 'true');
                const pTo = block.find('input[name*="[destination]"]');
                if (pTo.length && (!pTo.val() || pTo.attr('data-auto') === 'true')) pTo.val('JED APT').attr('data-auto', 'true');
                if (typeof updateTransportSummary === 'function') updateTransportSummary();
            } else {
                alert('Please enter Return / Arrival Date / Flight details in Flight tab first.');
            }
        });

        $(document).on('click', '#btnAutoFillFlightToTransport', function(e) {
            e.preventDefault();
            syncFlightDatesToTransport(true);
            const depDate = $('input[name="departure_date"]').val();
            const arrDate = $('input[name="arrival_date"]').val();
            if (depDate || arrDate) {
                alert('Transport routes updated successfully from Flight details!');
            } else {
                alert('Please enter Flight Departure or Return details in the Flight tab first.');
            }
        });

        // Passenger passport sync across tabs
        $(document).on('input change', '.person-flight-passport', function() {
            const row = $(this).closest('.flight-person-row');
            const idx = row.attr('data-idx');
            const val = $(this).val();
            if (idx !== undefined) {
                const targetEl = document.getElementById(`person_passport_${idx}`);
                if (targetEl && targetEl.value !== val) targetEl.value = val;
                if (idx == 0) {
                    const fillPass = document.getElementById('fill_passport');
                    if (fillPass && fillPass.value !== val) fillPass.value = val;
                }
            }
        });

        $(document).on('input change', '#fill_passport, [id^="person_passport_"]', function() {
            const id = $(this).attr('id');
            const val = $(this).val();
            if (id === 'fill_passport' || id === 'person_passport_0') {
                const row0Pass = document.querySelector('#flight_person_row_0 .person-flight-passport');
                if (row0Pass && row0Pass.value !== val) row0Pass.value = val;
                if (id === 'fill_passport') {
                    const p0 = document.getElementById('person_passport_0');
                    if (p0 && p0.value !== val) p0.value = val;
                }
            } else {
                const match = id.match(/person_passport_(\d+)/);
                if (match) {
                    const rowPass = document.querySelector(`#flight_person_row_${match[1]} .person-flight-passport`);
                    if (rowPass && rowPass.value !== val) rowPass.value = val;
                }
            }
        });

        function handleTicketStatusChange(select) {
            const row = select.closest('tr');
            if (!row) return;
            const val = select.value;
            select.className = 'form-select form-select-sm person-flight-status ' + 
                (val === 'cancelled' ? 'bg-soft-danger text-danger border-danger fw-bold' : (val === 'refunded' ? 'bg-soft-warning text-warning border-warning fw-bold' : 'text-success fw-bold'));
            
            if (val === 'cancelled') {
                row.classList.add('table-danger');
                row.classList.remove('table-warning');
                row.style.opacity = '0.65';
                row.style.textDecoration = 'line-through';
            } else if (val === 'refunded') {
                row.classList.add('table-warning');
                row.classList.remove('table-danger');
                row.style.opacity = '0.85';
                row.style.textDecoration = 'none';
            } else {
                row.classList.remove('table-danger', 'table-warning');
                row.style.opacity = '1';
                row.style.textDecoration = 'none';
            }
            calculateFlight();
            if (typeof calcTotal === 'function') calcTotal();
        }

        // Master Flight Rates Cascade Button Handler
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.id === 'btnCascadeFlightRates' || e.target.closest('#btnCascadeFlightRates'))) {
                e.preventDefault();
                const adultCost = parseFloat(document.getElementById('flight_adult_cost')?.value) || 0;
                const adultSell = parseFloat(document.getElementById('flight_adult_sell')?.value) || 0;
                const childCost = parseFloat(document.getElementById('flight_child_cost')?.value) || 0;
                const childSell = parseFloat(document.getElementById('flight_child_sell')?.value) || 0;
                const infantCost = parseFloat(document.getElementById('flight_infant_cost')?.value) || 0;
                const infantSell = parseFloat(document.getElementById('flight_infant_sell')?.value) || 0;

                const mainSup = document.getElementById('flight_supplier_id')?.value || '';
                const depDate = document.querySelector('input[name="departure_date"]')?.value || '';
                const depTime = document.querySelector('input[name="departure_time"]')?.value || '';
                const depPnr  = document.querySelector('input[name="departure_pnr"]')?.value || '';

                let updatedCount = 0;
                document.querySelectorAll('.flight-person-row').forEach(row => {
                    const pType = row.querySelector('.person-flight-pax-type')?.value || 'adult';
                    const costInp = row.querySelector('.person-flight-cost');
                    const sellInp = row.querySelector('.person-flight-sell');

                    if (pType === 'adult') {
                        if (costInp && adultCost > 0) costInp.value = adultCost.toFixed(2);
                        if (sellInp && adultSell > 0) sellInp.value = adultSell.toFixed(2);
                    } else if (pType === 'child') {
                        if (costInp && childCost > 0) costInp.value = childCost.toFixed(2);
                        if (sellInp && childSell > 0) sellInp.value = childSell.toFixed(2);
                    } else if (pType === 'infant') {
                        if (costInp && infantCost > 0) costInp.value = infantCost.toFixed(2);
                        if (sellInp && infantSell > 0) sellInp.value = infantSell.toFixed(2);
                    }

                    const supSel  = row.querySelector('.person-flight-supplier');
                    const dateInp = row.querySelector('.person-flight-date');
                    const timeInp = row.querySelector('.person-flight-time');
                    const pnrInp  = row.querySelector('.person-flight-pnr');
                    const idx     = row.getAttribute('data-idx');

                    if (supSel) {
                        supSel.removeAttribute('data-customized');
                        supSel.value = '';
                        const badge = document.getElementById('person_sup_badge_' + idx);
                        if (badge) {
                            badge.className = 'badge bg-light text-muted border fs-10 mt-1 d-inline-block person-supplier-badge';
                            badge.innerHTML = '<i class="mdi mdi-link-variant me-1"></i>Linked to Main Supplier';
                        }
                    }
                    if (dateInp) {
                        dateInp.removeAttribute('data-customized');
                        dateInp.classList.remove('border-info');
                        if (depDate) dateInp.value = depDate;
                    }
                    if (timeInp) {
                        timeInp.removeAttribute('data-customized');
                        timeInp.classList.remove('border-info');
                        if (depTime) timeInp.value = depTime;
                    }
                    if (pnrInp) {
                        pnrInp.removeAttribute('data-customized');
                        pnrInp.classList.remove('border-info');
                        if (depPnr) pnrInp.value = depPnr;
                    }
                    updatedCount++;
                });

                calculateFlight();
                if (typeof calcTotal === 'function') calcTotal();
                if (typeof showAutoRefreshToast === 'function') {
                    showAutoRefreshToast(`Master flight rates, supplier & details applied to ${updatedCount} passengers!`);
                } else {
                    alert(`Master flight rates, supplier & details applied to ${updatedCount} passengers!`);
                }
            }
        });

        // Trip Type Toggle (One-Way vs Round-Trip)
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('flight-trip-type-radio')) {
                const returnSec = document.getElementById('returnFlightSection');
                if (!returnSec) return;
                if (e.target.value === 'oneway') {
                    returnSec.style.display = 'none';
                } else {
                    returnSec.style.display = 'flex';
                }
            }
        });

        // Hotels Dynamic Management
        var hotelIdx = {{ $booking->hotels->count() ?: 1 }};
        var isAddingHotel = false;

        function addNewHotel(loc = 'makkah') {
            if (isAddingHotel) return;
            isAddingHotel = true;
            setTimeout(() => { isAddingHotel = false; }, 350);
            try {
                const hotelsList = document.getElementById('hotelsList');
                if (!hotelsList) {
                    console.error('hotelsList element not found!');
                    return;
                }

                const existingHotels = document.querySelectorAll('#hotelsList .hotel-block');
                const nextHotelIdx = existingHotels.length;

                const noHotelAlert = document.getElementById('noHotelAlert');
                if (noHotelAlert) noHotelAlert.style.display = 'none';

                const activeCategory = document.querySelector('input[name="booking_category"]:checked')?.value || 'umrah';
                let locLabel = 'Makkah Hotel';
                let locVal = 'makkah';

                if (activeCategory === 'domestic') {
                    loc = 'other';
                    locLabel = 'Domestic Hotel';
                    locVal = 'other';
                } else if (activeCategory === 'international') {
                    loc = 'other';
                    locLabel = 'International Hotel';
                    locVal = 'other';
                } else {
                    if (loc === 'madinah') { locLabel = 'Madinah Hotel'; locVal = 'madinah'; }
                    else if (loc === 'other') { locLabel = 'Other Hotel'; locVal = 'other'; }
                    else if (loc === 'jeddah') { locLabel = 'Jeddah Hotel'; locVal = 'jeddah'; }
                }

                const flightCurr = (activeCategory === 'domestic') ? 'PKR' : (document.getElementById('flight_currency')?.value || 'SAR');
                const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
                const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
                const isPkr = (flightCurr === 'PKR');
                const hotelVoucherBase = document.getElementById('hotel_voucher_no')?.value || '{{ $booking->hotel_voucher_no ?? ($defaultVouchers["hotel"] ?? ("HTL-" . date("Y") . "-00001")) }}';
                const childVoucher = hotelVoucherBase + '-' + (nextHotelIdx + 1);

                // Filter hotels by location & category
                let relevantHotelsOptions = '';
                try {
                    relevantHotelsOptions = (activeCategory === 'domestic' || activeCategory === 'international')
                        ? buildHotelsOptions()
                        : buildHotelsOptions(loc === 'makkah' ? 'makkah' : (loc === 'madinah' ? 'madinah' : 'other'));
                } catch(e) {
                    console.error('buildHotelsOptions error:', e);
                    relevantHotelsOptions = typeof hotelsOptions !== 'undefined' ? hotelsOptions : '<option value="">Select Hotel</option>';
                }

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="${nextHotelIdx}" data-location="${locVal}" data-city="${locVal.toUpperCase()}">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="text-primary mb-0 fw-bold"><i class="mdi mdi-hotel me-1"></i> ${locLabel}</h6>
                        <span class="badge bg-soft-success text-success border border-success fs-12 px-2 py-1 font-monospace">
                            <i class="mdi mdi-ticket-confirmation me-1"></i>Voucher #: ${childVoucher}
                        </span>
                        <input type="hidden" name="hotels[${nextHotelIdx}][voucher_no]" value="${childVoucher}">
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-hotel" onclick="removeHotelBlock(this)"><i class="mdi mdi-delete-outline me-1"></i> Remove</button>
                </div>
                <input type="hidden" name="hotels[${nextHotelIdx}][location]" value="${locVal}">
                
                {{-- TOP ROW: Booking Information & Single-Line Dates (Point 7) --}}
                <div class="p-2 mb-3 rounded bg-light border">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark mb-1"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Stay Period (Check In & Check Out)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-primary fw-semibold"><i class="mdi mdi-login me-1"></i> Check In</span>
                                <input type="date" name="hotels[${nextHotelIdx}][check_in]" class="form-control form-control-sm hotel-checkin" onchange="calculateHotelNights(${nextHotelIdx})">
                                <span class="input-group-text bg-white text-danger fw-semibold"><i class="mdi mdi-logout me-1"></i> Check Out</span>
                                <input type="date" name="hotels[${nextHotelIdx}][check_out]" class="form-control form-control-sm hotel-checkout" onchange="calculateHotelNights(${nextHotelIdx})">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Nights</label>
                            <input type="number" name="hotels[${nextHotelIdx}][no_of_nights]" class="form-control form-control-sm hotel-nights" value="1" min="1"
                                oninput="calculateHotelRow(this.closest('.hotel-block'), 'nights')" onchange="calculateHotelRow(this.closest('.hotel-block'), 'nights')">
                        </div>
                        <input type="hidden" name="hotels[${nextHotelIdx}][booking_date]" class="hotel-booking-date sync-booking-date" value="{{ date('Y-m-d') }}">
                        <div class="col-md-3">
                            <label class="form-label text-warning fw-bold mb-1"><i class="mdi mdi-calendar-clock me-1 text-warning"></i> Option Date</label>
                            <input type="date" name="hotels[${nextHotelIdx}][option_date]" class="form-control form-control-sm hotel-option-date border-warning">
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Hotel Name</label>
                        <select name="hotels[${nextHotelIdx}][hotel_name]" class="form-select hotel-name-select select2" onchange="updateHotelStaySummary()">
                            ${relevantHotelsOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-flex justify-content-between align-items-center mb-1">
                            <span>Hotel Supplier / Vendor</span>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshSuppliersList(true)" title="Reload Suppliers"><i class="mdi mdi-refresh"></i> Refresh</button>
                        </label>
                        <select name="hotels[${nextHotelIdx}][supplier_id]" class="form-select hotel-supplier-select select2">
                            ${suppliersOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-dark fw-bold">Currency</label>
                        <select name="hotels[${nextHotelIdx}][currency]" class="form-select hotel-currency fw-bold"
                            onchange="calculateHotelRow(this.closest('.hotel-block'))">
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
                            <input type="number" step="0.0001" name="hotels[${nextHotelIdx}][cost_roe]" class="form-control hotel-cost-roe" value="${defaultCostRoe}" placeholder="75.00" ${isPkr ? 'readonly' : ''}
                                oninput="calculateHotelRow(this.closest('.hotel-block'))" onchange="calculateHotelRow(this.closest('.hotel-block'))">
                        </div>
                        <input type="hidden" name="hotels[${nextHotelIdx}][roe]" class="hotel-roe" value="${defaultCostRoe}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-success fw-bold">Sale ROE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light hotel-curr-prefix">1 ${flightCurr} =</span>
                            <input type="number" step="0.0001" name="hotels[${nextHotelIdx}][sale_roe]" class="form-control hotel-sale-roe" value="${defaultSaleRoe}" placeholder="75.00" ${isPkr ? 'readonly' : ''}
                                oninput="calculateHotelRow(this.closest('.hotel-block'))" onchange="calculateHotelRow(this.closest('.hotel-block'))">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Room Type</label>
                        <select name="hotels[${nextHotelIdx}][room_type]" class="form-select hotel-room-type">
                            <option value="single">Single</option>
                            <option value="double" selected>Double</option>
                            <option value="triple">Triple</option>
                            <option value="quad">Quad</option>
                            <option value="sharing">Sharing</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">No. of Rooms</label>
                        <input type="number" name="hotels[${nextHotelIdx}][no_of_rooms]" class="form-control hotel-rooms" value="1" min="1"
                            oninput="calculateHotelRow(this.closest('.hotel-block'), 'rooms')" onchange="calculateHotelRow(this.closest('.hotel-block'), 'rooms')">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hotel Room #</label>
                        <input type="text" name="hotels[${nextHotelIdx}][room_number]" class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><i class="mdi mdi-silverware me-1 text-primary"></i> Meal Plan</label>
                        <select name="hotels[${nextHotelIdx}][meal_plan]" class="form-select hotel-meal-plan" onchange="updateHotelStaySummary()">
                            ${buildMealPlansOptions('BB')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><i class="mdi mdi-eye me-1 text-info"></i> Room View</label>
                        <select name="hotels[${nextHotelIdx}][room_view]" class="form-select hotel-room-view">
                            ${buildRoomViewsOptions('Standard View')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${nextHotelIdx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="0"
                            oninput="autoDivideHotelRates(this.closest('.hotel-block'), 'cost'); calculateHotelRow(this.closest('.hotel-block'), 'costRate')"
                            onchange="autoDivideHotelRates(this.closest('.hotel-block'), 'cost'); calculateHotelRow(this.closest('.hotel-block'), 'costRate')">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${nextHotelIdx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="0"
                            oninput="autoDivideHotelRates(this.closest('.hotel-block'), 'sell'); calculateHotelRow(this.closest('.hotel-block'), 'sellRate')"
                            onchange="autoDivideHotelRates(this.closest('.hotel-block'), 'sell'); calculateHotelRow(this.closest('.hotel-block'), 'sellRate')">
                    </div>

                    {{-- Main Cost Division & Additional Rooms Controls (Points 8 & 9) --}}
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-2 rounded bg-light border gap-2">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-xs btn-outline-info" onclick="divideHotelMainCost(${nextHotelIdx})" title="Divide main cost equally among passengers">
                                    <i class="mdi mdi-calculator me-1"></i> Divide / Allocate Main Cost
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success btn-add-additional-room" onclick="addAdditionalRoomToHotel(${nextHotelIdx})">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add Additional Room
                                </button>
                            </div>
                        </div>
                        <div class="additional-rooms-container mt-2" id="additional_rooms_list_${nextHotelIdx}"></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Total Cost (<span class="hotel-curr-label">${flightCurr}</span> & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-danger"><span class="hotel-curr-name">${flightCurr}:</span><strong class="hotel-cost-sar-disp">0.00 ${flightCurr}</strong></div>
                            <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="hotel-cost-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${nextHotelIdx}][cost_amount]" class="hotel-cost-total" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Total Sell (<span class="hotel-curr-label">${flightCurr}</span> & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-success"><span class="hotel-curr-name">${flightCurr}:</span><strong class="hotel-sell-sar-disp">0.00 ${flightCurr}</strong></div>
                            <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="hotel-sell-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${nextHotelIdx}][selling_amount]" class="hotel-sell-total" value="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                        <input type="file" name="hotels[${nextHotelIdx}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
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
            hotelIdx = document.querySelectorAll('#hotelsList .hotel-block').length;
            try { syncBookingHotelDates(); } catch(e) { console.error('syncBookingHotelDates error:', e); }
            if (lastRow) {
                try { if (typeof initBookingSelect2 === 'function') initBookingSelect2(lastRow); } catch(e) { console.error('initBookingSelect2 error:', e); }
                try { syncHotelPersons(lastRow); } catch(e) { console.error('syncHotelPersons error:', e); }
                try { autoDivideHotelRates(lastRow); } catch(e) { console.error('autoDivideHotelRates error:', e); }
                try { calculateHotelRow(lastRow); } catch(e) { console.error('calculateHotelRow error:', e); }
            }
            try { calcTotal(); } catch(e) { console.error('calcTotal error:', e); }
            try { updateHotelStaySummary(); } catch(e) { console.error('updateHotelStaySummary error:', e); }
        } catch(err) {
            console.error('addNewHotel exception:', err);
        }
    }
    window.addNewHotel = addNewHotel;

        function removeHotelBlock(btn) {
            try {
                if (!btn) return;
                const el = (btn.jquery ? btn[0] : btn);
                const block = (el instanceof Element) ? el.closest('.hotel-block') : null;
                if (block) block.remove();
                if (document.querySelectorAll('#hotelsList .hotel-block').length === 0) {
                    const noHotelAlert = document.getElementById('noHotelAlert');
                    if (noHotelAlert) noHotelAlert.style.display = '';
                }
                try { if (typeof syncBookingHotelDates === 'function') syncBookingHotelDates(); } catch(e){}
                try { if (typeof updateHotelStaySummary === 'function') updateHotelStaySummary(); } catch(e){}
                try { if (typeof calcTotal === 'function') calcTotal(); } catch(e){}
            } catch(err) {
                console.error('removeHotelBlock error:', err);
            }
        }
        window.removeHotelBlock = removeHotelBlock;

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

                const pType = document.getElementById(`person_pax_type_${p}`)?.value || 'adult';
                const hBadgeClass = pType === 'child' ? 'bg-info text-dark' : (pType === 'infant' ? 'bg-warning text-dark' : 'bg-primary');
                const pTypeLabel = pType.toUpperCase();

                rowsHtml += `
                <tr class="hotel-person-row" data-person-idx="${p}">
                    <td class="text-center text-muted fw-bold">${p + 1}</td>
                    <td>
                        <strong class="text-primary person-hotel-name-disp">${personName}</strong>
                        <span class="badge ${hBadgeClass} person-hotel-type-badge ms-1" style="font-size:10px;">${pTypeLabel}</span>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][name]" class="person-hotel-name" value="${personName}">
                    </td>
                    <td class="text-center fw-semibold hotel-person-nights-disp">${nights}</td>
                    <td>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][person_rates][${p}][cost_per_night]"
                            class="form-control form-control-sm text-end hotel-person-cost" value="${pCost}" placeholder="0.00"
                            oninput="calculateHotelRow(this.closest('.hotel-block'), 'personRate')"
                            onchange="calculateHotelRow(this.closest('.hotel-block'), 'personRate')">
                        <small class="text-danger d-block text-end hotel-person-cost-disp" style="font-size:11px;">
                            ${totCostForeign.toFixed(2)} ${curr} (${totCostPkr.toFixed(2)} PKR)
                        </small>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][total_cost]" class="hotel-person-total-cost" value="${totCostPkr.toFixed(2)}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][person_rates][${p}][selling_per_night]"
                            class="form-control form-control-sm text-end hotel-person-sell" value="${pSell}" placeholder="0.00"
                            oninput="calculateHotelRow(this.closest('.hotel-block'), 'personRate')"
                            onchange="calculateHotelRow(this.closest('.hotel-block'), 'personRate')">
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

        function calculateHotelRow(row, source) {
            if (!row) return;
            const nights = parseFloat(row.querySelector('.hotel-nights')?.value) || 0;
            const rooms  = parseFloat(row.querySelector('.hotel-rooms')?.value) || 1;
            const curr   = row.querySelector('.hotel-currency')?.value || 'SAR';
            const isPkr  = (curr === 'PKR');

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

            // Sum additional rooms cost and sell rates per night
            let extraCostPerNight = 0;
            let extraSellPerNight = 0;
            row.querySelectorAll('.additional-room-item').forEach(extraRoom => {
                extraCostPerNight += parseFloat(extraRoom.querySelector('.add-room-cost')?.value) || 0;
                extraSellPerNight += parseFloat(extraRoom.querySelector('.add-room-sell')?.value) || 0;
            });

            const personRows = row.querySelectorAll('.hotel-person-row');
            let costCurr = 0;
            let sellCurr = 0;

            if (source === 'personRate') {
                // User manually typed directly in passenger rows
                let sumPersonCostForeign = 0;
                let sumPersonSellForeign = 0;
                personRows.forEach(pRow => {
                    const cInput = pRow.querySelector('.hotel-person-cost');
                    const sInput = pRow.querySelector('.hotel-person-sell');
                    const cRate  = parseFloat(cInput?.value) || 0;
                    const sRate  = parseFloat(sInput?.value) || 0;

                    const pTotCostCurr = cRate * nights;
                    const pTotCostPkr  = pTotCostCurr * costRoe;
                    const pTotSellCurr = sRate * nights;
                    const pTotSellPkr  = pTotSellCurr * saleRoe;

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

                costCurr = Math.round(sumPersonCostForeign * 100) / 100;
                sellCurr = Math.round(sumPersonSellForeign * 100) / 100;

                const crInput = row.querySelector('.hotel-cost-rate');
                const srInput = row.querySelector('.hotel-sell-rate');
                if (crInput && nights > 0 && rooms > 0) {
                    crInput.value = (Math.max(0, (costCurr / nights) - extraCostPerNight) / rooms).toFixed(2);
                }
                if (srInput && nights > 0 && rooms > 0) {
                    srInput.value = (Math.max(0, (sellCurr / nights) - extraSellPerNight) / rooms).toFixed(2);
                }
            } else {
                // User changed rooms, nights, cost-rate, sell-rate, additional rooms, or on initial load
                const costRate = parseFloat(row.querySelector('.hotel-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.hotel-sell-rate')?.value) || 0;
                costCurr = nights * ((rooms * costRate) + extraCostPerNight);
                sellCurr = nights * ((rooms * sellRate) + extraSellPerNight);

                if (personRows.length > 0) {
                    autoDivideHotelRates(row);
                    personRows.forEach(pRow => {
                        const cInput = pRow.querySelector('.hotel-person-cost');
                        const sInput = pRow.querySelector('.hotel-person-sell');
                        const cRate  = parseFloat(cInput?.value) || 0;
                        const sRate  = parseFloat(sInput?.value) || 0;

                        const pTotCostCurr = cRate * nights;
                        const pTotCostPkr  = pTotCostCurr * costRoe;
                        const pTotSellCurr = sRate * nights;
                        const pTotSellPkr  = pTotSellCurr * saleRoe;

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
            }

            const costPkr = costCurr * costRoe;
            const sellPkr = sellCurr * saleRoe;

            const costSarDisp    = row.querySelector('.hotel-cost-sar-disp');
            const costPkrDisp    = row.querySelector('.hotel-cost-pkr-disp');
            const costTotalInput = row.querySelector('.hotel-cost-total');

            const sellSarDisp    = row.querySelector('.hotel-sell-sar-disp');
            const sellPkrDisp    = row.querySelector('.hotel-sell-pkr-disp');
            const sellTotalInput = row.querySelector('.hotel-sell-total');

            if (costSarDisp) costSarDisp.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costTotalInput) costTotalInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellCurr.toFixed(2) + ' ' + curr;
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellTotalInput) sellTotalInput.value = sellPkr.toFixed(2);

            const tblCostCurr = row.querySelector('.hotel-person-tbl-cost-curr');
            const tblCostPkr  = row.querySelector('.hotel-person-tbl-cost-pkr');
            const tblSellCurr = row.querySelector('.hotel-person-tbl-sell-curr');
            const tblSellPkr  = row.querySelector('.hotel-person-tbl-sell-pkr');

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
            updateHotelStaySummary();
        }

        function calculateHotelCost(row) {
            calculateHotelRow(row);
        }

        function calculateHotelNights(idx) {
            const block = document.querySelector(`.hotel-block[data-hotel-idx="${idx}"]`);
            if (!block) return;
            const cin = block.querySelector('.hotel-checkin')?.value;
            const cout = block.querySelector('.hotel-checkout')?.value;
            if (cin && cout) {
                const d1 = new Date(cin + 'T00:00:00');
                const d2 = new Date(cout + 'T00:00:00');
                const diffTime = d2.getTime() - d1.getTime();
                const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                const nightsInput = block.querySelector('.hotel-nights');
                if (nightsInput) {
                    nightsInput.value = diffDays > 0 ? diffDays : 1;
                }
            }
            calculateHotelRow(block, 'nights');
            calcTotal();
            updateHotelStaySummary();
        }

        function updateHotelStaySummary() {
            let minIn = null;
            let maxOut = null;
            let totalNights = 0;
            let totalRooms = 0;
            let totalCostForeign = 0;
            let totalCostPkr = 0;
            let totalSellForeign = 0;
            let totalSellPkr = 0;
            let hotelCurrs = new Set();
            let summaryTableHtml = '';

            const blocks = document.querySelectorAll('.hotel-block');
            blocks.forEach(b => {
                const cin = b.querySelector('.hotel-checkin')?.value;
                const cout = b.querySelector('.hotel-checkout')?.value;
                const nights = parseInt(b.querySelector('.hotel-nights')?.value) || 0;
                const rooms = parseInt(b.querySelector('.hotel-rooms')?.value) || 0;

                let extraCostPerNight = 0;
                let extraSellPerNight = 0;
                let extraRoomsCount = 0;
                b.querySelectorAll('.additional-room-item').forEach(extraRoom => {
                    extraCostPerNight += parseFloat(extraRoom.querySelector('.add-room-cost')?.value) || 0;
                    extraSellPerNight += parseFloat(extraRoom.querySelector('.add-room-sell')?.value) || 0;
                    extraRoomsCount++;
                });

                totalNights += nights;
                totalRooms += (rooms + extraRoomsCount);
                if (cin && (!minIn || cin < minIn)) minIn = cin;
                if (cout && (!maxOut || cout > maxOut)) maxOut = cout;

                // Hotel Name
                let hName = '—';
                const nameSelect = b.querySelector('.hotel-name-select');
                const nameInput = b.querySelector('.hotel-name-input');
                if (nameSelect && nameSelect.value) {
                    hName = nameSelect.options[nameSelect.selectedIndex]?.text || nameSelect.value;
                } else if (nameInput && nameInput.value) {
                    hName = nameInput.value;
                }

                // City (from data-location or data-city)
                const locAttr = b.getAttribute('data-location') || b.getAttribute('data-city') || 'makkah';
                const city = locAttr.toUpperCase();

                // Room Type, Rooms, Meal Plan
                const roomType = (b.querySelector('.hotel-room-type, select[name*="[room_type]"]')?.value || 'DOUBLE').toUpperCase();
                const mealPlan = (b.querySelector('.hotel-meal-plan, select[name*="[meal_plan]"]')?.value || 'BB').toUpperCase();

                const curr = b.querySelector('.hotel-currency')?.value || 'SAR';
                const isPkr = (curr === 'PKR');
                const costRoe = isPkr ? 1 : (parseFloat(b.querySelector('.hotel-cost-roe')?.value) || parseFloat(b.querySelector('.hotel-roe')?.value) || 75);
                const saleRoe = isPkr ? 1 : (parseFloat(b.querySelector('.hotel-sale-roe')?.value) || costRoe || 75);

                const costRate = parseFloat(b.querySelector('.hotel-cost-rate, input[name*="[cost_per_night]"]')?.value) || 0;
                const sellRate = parseFloat(b.querySelector('.hotel-sell-rate, input[name*="[selling_per_night]"]')?.value) || 0;

                const cForeign = nights * ((rooms * costRate) + extraCostPerNight);
                const sForeign = nights * ((rooms * sellRate) + extraSellPerNight);
                const cPkr = cForeign * costRoe;
                const sPkr = sForeign * saleRoe;

                totalCostForeign += cForeign;
                totalCostPkr += cPkr;
                totalSellForeign += sForeign;
                totalSellPkr += sPkr;
                hotelCurrs.add(curr);

                const dispRooms = rooms + extraRoomsCount;
                summaryTableHtml += `<tr>
                    <td class="text-start fw-bold text-dark">${hName}</td>
                    <td class="fw-semibold">${city}</td>
                    <td>${cin || '—'}</td>
                    <td>${cout || '—'}</td>
                    <td class="fw-bold text-dark">${nights}</td>
                    <td>${roomType}</td>
                    <td>${dispRooms || 1}</td>
                    <td>${mealPlan}</td>
                    <td class="text-end text-danger fw-semibold">
                        <span>${cForeign.toFixed(2)} ${curr}</span><br>
                        <small class="text-muted fw-normal">${cPkr.toFixed(2)} PKR</small>
                    </td>
                    <td class="text-end text-success fw-semibold">
                        <span>${sForeign.toFixed(2)} ${curr}</span><br>
                        <small class="text-muted fw-normal">${sPkr.toFixed(2)} PKR</small>
                    </td>
                </tr>`;
            });

            const hotelCurrLabel = hotelCurrs.size === 1 ? Array.from(hotelCurrs)[0] : (hotelCurrs.size > 1 ? 'Mix' : 'SAR');

            const summaryTableBody = document.getElementById('hotelAccommodationSummaryRows');
            const summaryNightsTotal = document.getElementById('hotel_summary_total_nights');

            if (summaryTableBody) {
                if (blocks.length === 0) {
                    summaryTableBody.innerHTML = '<tr><td colspan="10" class="text-muted py-2">No hotels added yet.</td></tr>';
                } else {
                    summaryTableBody.innerHTML = summaryTableHtml;
                }
            }

            const summaryCostTotal = document.getElementById('hotel_summary_total_cost');
            const summarySellTotal = document.getElementById('hotel_summary_total_sell');
            if (summaryCostTotal) summaryCostTotal.innerHTML = `<span>${totalCostForeign.toFixed(2)} ${hotelCurrLabel}</span><br><small class="text-muted fw-normal">${totalCostPkr.toFixed(2)} PKR</small>`;
            if (summarySellTotal) summarySellTotal.innerHTML = `<span>${totalSellForeign.toFixed(2)} ${hotelCurrLabel}</span><br><small class="text-muted fw-normal">${totalSellPkr.toFixed(2)} PKR</small>`;
            if (summaryNightsTotal) {
                summaryNightsTotal.textContent = totalNights;
            }

            // Also update the 4 stat cards
            if (document.getElementById('hotel_cost_sar_total')) document.getElementById('hotel_cost_sar_total').textContent = `${totalCostForeign.toFixed(2)} ${hotelCurrLabel}`;
            if (document.getElementById('hotel_cost_pkr_total')) document.getElementById('hotel_cost_pkr_total').textContent = `${totalCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('hotel_sell_sar_total')) document.getElementById('hotel_sell_sar_total').textContent = `${totalSellForeign.toFixed(2)} ${hotelCurrLabel}`;
            if (document.getElementById('hotel_sell_pkr_total')) document.getElementById('hotel_sell_pkr_total').textContent = `${totalSellPkr.toFixed(2)} PKR`;

            const sumDisp = document.getElementById('hotel_stay_summary_dates');
            const nightsDisp = document.getElementById('hotel_stay_total_nights');
            const roomsDisp = document.getElementById('hotel_stay_total_rooms');
            if (sumDisp) {
                if (minIn && maxOut) {
                    sumDisp.textContent = `${minIn} to ${maxOut}`;
                    sumDisp.className = 'badge bg-success text-white fs-12 ms-1';
                } else if (minIn) {
                    sumDisp.textContent = `Check-in: ${minIn}`;
                    sumDisp.className = 'badge bg-soft-primary text-primary fs-12 ms-1';
                } else {
                    sumDisp.textContent = 'No check-in dates set';
                    sumDisp.className = 'badge bg-light text-muted fs-12 ms-1';
                }
            }
            if (nightsDisp) nightsDisp.textContent = totalNights;
            if (roomsDisp) roomsDisp.textContent = totalRooms;
        }

        // Transport Routes Summary View
        function updateTransportSummary() {
            const blocks = document.querySelectorAll('.route-block');
            let summaryHtml = '';
            let totalQty = 0;
            let totalCostForeign = 0;
            let totalCostPkr = 0;
            let totalSellForeign = 0;
            let totalSellPkr = 0;
            let transportCurrs = new Set();

            blocks.forEach((b, idx) => {
                const routeSelect = b.querySelector('.transport-route-select');
                let routeName = '—';
                if (routeSelect && routeSelect.value) {
                    routeName = routeSelect.options[routeSelect.selectedIndex]?.text?.trim() || routeSelect.value;
                }

                const vehSelect = b.querySelector('.transport-vehicle-select');
                let vehicle = '—';
                if (vehSelect && vehSelect.value) {
                    vehicle = vehSelect.options[vehSelect.selectedIndex]?.text?.trim() || vehSelect.value;
                }

                const qty = parseFloat(b.querySelector('input[name*="[quantity]"], .transport-qty')?.value) || 1;
                totalQty += qty;

                const date = b.querySelector('input[name*="[travel_date]"], input[name*="[date]"]')?.value || '—';
                const time = b.querySelector('input[name*="[travel_time]"], input[name*="[time]"]')?.value || '';
                const dateTime = time ? `${date} ${time}` : date;

                const pax = b.querySelector('input[name*="[pax]"]')?.value || '—';

                const supSelect = b.querySelector('select[name*="[supplier_id]"]');
                let supName = '—';
                if (supSelect && supSelect.value) {
                    supName = supSelect.options[supSelect.selectedIndex]?.text?.trim() || 'Vendor';
                }

                const status = b.querySelector('select[name*="[status]"]')?.value || 'CONFIRMED';

                const curr = b.querySelector('.transport-currency')?.value || 'SAR';
                const isPkr = (curr === 'PKR');
                const costRoe = isPkr ? 1 : (parseFloat(b.querySelector('.transport-cost-roe')?.value) || (curr === 'PKR' ? 1 : 75));
                const saleRoe = isPkr ? 1 : (parseFloat(b.querySelector('.transport-sale-roe')?.value) || costRoe);

                const costRate = parseFloat(b.querySelector('.transport-cost-rate, input[name*="[cost_rate]"]')?.value) || 0;
                const sellRate = parseFloat(b.querySelector('.transport-sell-rate, input[name*="[selling_rate]"]')?.value) || 0;

                const costCurr = costRate * qty;
                const costPkr = costCurr * costRoe;
                const sellCurr = sellRate * qty;
                const sellPkr = sellCurr * saleRoe;

                totalCostForeign += costCurr;
                totalCostPkr += costPkr;
                totalSellForeign += sellCurr;
                totalSellPkr += sellPkr;
                transportCurrs.add(curr);

                summaryHtml += `<tr>
                    <td class="text-start fw-bold text-dark">${routeName}</td>
                    <td class="fw-semibold">${vehicle}</td>
                    <td class="fw-bold text-primary">${qty}</td>
                    <td>${dateTime}</td>
                    <td>${pax}</td>
                    <td>${supName}</td>
                    <td><span class="badge bg-${status === 'CONFIRMED' ? 'success' : (status === 'CANCELLED' ? 'danger' : 'warning')}">${status}</span></td>
                    <td class="text-end text-danger fw-semibold">
                        <span>${costCurr.toFixed(2)} ${curr}</span><br>
                        <small class="text-muted fw-normal">${costPkr.toFixed(2)} PKR</small>
                    </td>
                    <td class="text-end text-success fw-semibold">
                        <span>${sellCurr.toFixed(2)} ${curr}</span><br>
                        <small class="text-muted fw-normal">${sellPkr.toFixed(2)} PKR</small>
                    </td>
                </tr>`;
            });

            const transportCurrLabel = transportCurrs.size === 1 ? Array.from(transportCurrs)[0] : (transportCurrs.size > 1 ? 'Mix' : 'SAR');

            const tbody = document.getElementById('transportRoutesSummaryRows');
            if (tbody) {
                if (blocks.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-muted py-2">No routes added yet.</td></tr>';
                } else {
                    tbody.innerHTML = summaryHtml;
                }
            }

            const qtyDisp = document.getElementById('transport_summary_total_qty');
            if (qtyDisp) qtyDisp.textContent = totalQty;

            const sumCostTotal = document.getElementById('transport_summary_total_cost');
            const sumSellTotal = document.getElementById('transport_summary_total_sell');
            if (sumCostTotal) sumCostTotal.innerHTML = `<span>${totalCostForeign.toFixed(2)} ${transportCurrLabel}</span><br><small class="text-muted fw-normal">${totalCostPkr.toFixed(2)} PKR</small>`;
            if (sumSellTotal) sumSellTotal.innerHTML = `<span>${totalSellForeign.toFixed(2)} ${transportCurrLabel}</span><br><small class="text-muted fw-normal">${totalSellPkr.toFixed(2)} PKR</small>`;

            // Also update the 4 stat cards
            if (document.getElementById('transport_cost_sar_total')) document.getElementById('transport_cost_sar_total').textContent = `${totalCostForeign.toFixed(2)} ${transportCurrLabel}`;
            if (document.getElementById('transport_cost_pkr_total')) document.getElementById('transport_cost_pkr_total').textContent = `${totalCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('transport_sell_sar_total')) document.getElementById('transport_sell_sar_total').textContent = `${totalSellForeign.toFixed(2)} ${transportCurrLabel}`;
            if (document.getElementById('transport_sell_pkr_total')) document.getElementById('transport_sell_pkr_total').textContent = `${totalSellPkr.toFixed(2)} PKR`;
        }

        // Integer-cent allocation helper so sum ALWAYS matches total exactly (no 99.99 or 200.01)
        function allocateExactAmount(totalAmount, count) {
            if (count <= 0) return [];
            const totalCents = Math.round(totalAmount * 100);
            const baseCents = Math.floor(totalCents / count);
            let remainder = totalCents - (baseCents * count);

            const amounts = [];
            for (let i = 0; i < count; i++) {
                const paxCents = baseCents + (i < remainder ? 1 : 0);
                amounts.push((paxCents / 100).toFixed(2));
            }
            return amounts;
        }

        // Point 8: Automatic & Manual Main Cost Division Across Passengers
        function autoDivideHotelRates(block, whichField) {
            if (!block) return;
            const personRows = block.querySelectorAll('.hotel-person-row');
            const paxCount = personRows.length > 0 ? personRows.length : (parseInt(document.getElementById('no_of_pax')?.value) || 1);
            if (paxCount <= 0) return;
            const rooms = parseFloat(block.querySelector('.hotel-rooms')?.value) || 1;

            let extraCostPerNight = 0;
            let extraSellPerNight = 0;
            block.querySelectorAll('.additional-room-item').forEach(extraRoom => {
                extraCostPerNight += parseFloat(extraRoom.querySelector('.add-room-cost')?.value) || 0;
                extraSellPerNight += parseFloat(extraRoom.querySelector('.add-room-sell')?.value) || 0;
            });

            if (!whichField || whichField === 'cost') {
                const costRate = parseFloat(block.querySelector('.hotel-cost-rate')?.value) || 0;
                const totalCostPerNight = (costRate * rooms) + extraCostPerNight;
                if (totalCostPerNight > 0) {
                    const costAmounts = allocateExactAmount(totalCostPerNight, paxCount);
                    personRows.forEach((el, idx) => {
                        const cInput = el.querySelector('.hotel-person-cost');
                        if (cInput) cInput.value = costAmounts[idx] || '0.00';
                    });
                } else {
                    personRows.forEach(el => {
                        const cInput = el.querySelector('.hotel-person-cost');
                        if (cInput) cInput.value = '0.00';
                    });
                }
            }

            if (!whichField || whichField === 'sell') {
                const sellRate = parseFloat(block.querySelector('.hotel-sell-rate')?.value) || 0;
                const totalSellPerNight = (sellRate * rooms) + extraSellPerNight;
                if (totalSellPerNight > 0) {
                    const sellAmounts = allocateExactAmount(totalSellPerNight, paxCount);
                    personRows.forEach((el, idx) => {
                        const sInput = el.querySelector('.hotel-person-sell');
                        if (sInput) sInput.value = sellAmounts[idx] || '0.00';
                    });
                } else {
                    personRows.forEach(el => {
                        const sInput = el.querySelector('.hotel-person-sell');
                        if (sInput) sInput.value = '0.00';
                    });
                }
            }
        }

        function divideHotelMainCost(idx) {
            const block = document.querySelector(`.hotel-block[data-hotel-idx="${idx}"]`);
            if (!block) return;
            autoDivideHotelRates(block);
            calculateHotelRow(block);
        }
        window.divideHotelMainCost = divideHotelMainCost;

        // Point 9: Add Additional Room to existing hotel
        var addRoomCounter = 100;
        var isAddingAdditionalRoom = false;
        function addAdditionalRoomToHotel(hotelIdx) {
            if (isAddingAdditionalRoom) return;
            isAddingAdditionalRoom = true;
            setTimeout(() => { isAddingAdditionalRoom = false; }, 350);
            try {
                let container = document.getElementById(`additional_rooms_list_${hotelIdx}`);
                if (!container) {
                    const block = document.querySelector(`.hotel-block[data-hotel-idx="${hotelIdx}"]`);
                    if (block) container = block.querySelector('.additional-rooms-container');
                }
                if (!container) {
                    console.warn('Could not find additional rooms container for hotelIdx:', hotelIdx);
                    return;
                }
                const curr = document.querySelector(`.hotel-block[data-hotel-idx="${hotelIdx}"] .hotel-currency`)?.value || 'SAR';
                const rId = addRoomCounter++;

                const html = `
                <div class="border rounded p-2 mb-2 bg-light additional-room-item" id="add_room_${hotelIdx}_${rId}">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-primary fs-12"><i class="mdi mdi-door"></i> Additional Room</strong>
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('add_room_${hotelIdx}_${rId}').remove(); const blk = document.querySelector('.hotel-block[data-hotel-idx=\\'${hotelIdx}\\']'); if (blk) { autoDivideHotelRates(blk); calculateHotelRow(blk); } updateHotelStaySummary();">×</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label fs-11 mb-0">Room Type</label>
                            <select name="hotels[${hotelIdx}][extra_rooms][${rId}][room_type]" class="form-select form-select-sm">
                                <option value="single">Single</option>
                                <option value="double" selected>Double</option>
                                <option value="triple">Triple</option>
                                <option value="quad">Quad</option>
                                <option value="sharing">Sharing</option>
                                <option value="suite">Suite</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-11 mb-0">Room #</label>
                            <input type="text" name="hotels[${hotelIdx}][extra_rooms][${rId}][room_number]" class="form-control form-control-sm" placeholder="e.g. 502">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-11 mb-0">Cost Rate / Night (${curr})</label>
                            <input type="number" step="0.01" name="hotels[${hotelIdx}][extra_rooms][${rId}][cost_rate]" class="form-control form-control-sm add-room-cost" value="0"
                                oninput="calculateHotelRow(this.closest('.hotel-block'))" onchange="calculateHotelRow(this.closest('.hotel-block'))">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-11 mb-0">Sell Rate / Night (${curr})</label>
                            <input type="number" step="0.01" name="hotels[${hotelIdx}][extra_rooms][${rId}][sell_rate]" class="form-control form-control-sm add-room-sell" value="0"
                                oninput="calculateHotelRow(this.closest('.hotel-block'))" onchange="calculateHotelRow(this.closest('.hotel-block'))">
                        </div>
                    </div>
                </div>`;
                container.insertAdjacentHTML('beforeend', html);
                const block = document.querySelector(`.hotel-block[data-hotel-idx="${hotelIdx}"]`);
                if (block) {
                    autoDivideHotelRates(block);
                    calculateHotelRow(block);
                }
                updateHotelStaySummary();
            } catch(err) {
                console.error('addAdditionalRoomToHotel error:', err);
            }
        }
        window.addAdditionalRoomToHotel = addAdditionalRoomToHotel;

        // Transports Dynamic Management
        var routeIdx = {{ $booking->transports->count() ?: 1 }};
        var isAddingRoute = false;

        function addNewRoute() {
            if (isAddingRoute) return;
            isAddingRoute = true;
            setTimeout(() => { isAddingRoute = false; }, 350);
            try {
                const routesList = document.getElementById('routesList');
                if (!routesList) {
                    console.error('routesList element not found!');
                    return;
                }
                const existingRoutes = document.querySelectorAll('#routesList .route-block');
                const nextRouteIdx = existingRoutes.length;

                const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
                const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
                const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
                const isPkr = (flightCurr === 'PKR');
                const trnVoucherBase = document.getElementById('transport_voucher_no')?.value || '{{ $booking->transport_voucher_no ?? ($defaultVouchers["transport"] ?? ("TRN-" . date("Y") . "-00001")) }}';
                const childVoucher = trnVoucherBase + '-' + (nextRouteIdx + 1);

                routesList.insertAdjacentHTML('beforeend', `
                <div class="route-block border rounded p-3 mb-2 bg-white shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="text-dark mb-0 fw-bold"><i class="mdi mdi-bus me-1 text-warning"></i> Route #${nextRouteIdx + 1}</h6>
                            <span class="badge bg-soft-warning text-dark border border-warning fs-12 px-2 py-1 font-monospace">
                                <i class="mdi mdi-ticket-confirmation me-1"></i>Voucher #: ${childVoucher}
                            </span>
                            <input type="hidden" name="transports[${nextRouteIdx}][voucher_no]" value="${childVoucher}">
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-route" onclick="removeRouteBlock(this)"><i class="mdi mdi-delete-outline me-1"></i> Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Route</label>
                            <select name="transports[${nextRouteIdx}][route]" class="form-select transport-route-select">
                                ${routesOptions}
                            </select></div>
                        <div class="col-md-2"><label class="form-label">Vehicle</label>
                            <select name="transports[${nextRouteIdx}][vehicle]" class="form-select transport-vehicle-select">
                                ${vehiclesOptions}
                            </select></div>
                        <div class="col-md-1"><label class="form-label">Qty</label>
                            <input type="number" name="transports[${nextRouteIdx}][quantity]" class="form-control transport-qty" value="1" min="1"
                                oninput="calculateTransportRow(this.closest('.route-block'))" onchange="calculateTransportRow(this.closest('.route-block'))"></div>
                        <div class="col-md-2"><label class="form-label">Type / Status</label>
                            <select name="transports[${nextRouteIdx}][status]" class="form-select">
                                <option value="CONFIRMED" selected>CONFIRMED</option>
                                <option value="PENDING">PENDING</option>
                                <option value="CANCELLED">CANCELLED</option>
                            </select></div>
                        <div class="col-md-1"><label class="form-label">Pax</label>
                            <input type="number" name="transports[${nextRouteIdx}][pax]" class="form-control" value="1" min="1"></div>
                        <div class="col-md-3"><label class="form-label">Transport Supplier / Vendor</label>
                            <select name="transports[${nextRouteIdx}][supplier_id]" class="form-select transport-supplier-select select2">
                                ${suppliersOptions}
                            </select></div>
                        <div class="col-md-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Date</label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 btn-set-transport-dep" style="font-size:10px;" title="Set Flight Departure Date">Dep</button>
                                    <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 btn-set-transport-arr" style="font-size:10px;" title="Set Flight Arrival Date">Arr</button>
                                </div>
                            </div>
                            <input type="date" name="transports[${nextRouteIdx}][travel_date]" class="form-control transport-travel-date">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-clock-outline me-1 text-primary"></i> Pickup Time</label>
                            <input type="text" name="transports[${nextRouteIdx}][travel_time]" class="form-control" placeholder="e.g. 0045 / 1200">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-airplane me-1 text-info"></i> Flight #</label>
                            <input type="text" name="transports[${nextRouteIdx}][flight_no]" class="form-control" placeholder="e.g. PK831">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-map-marker-outline me-1 text-success"></i> Pickup From</label>
                            <input type="text" name="transports[${nextRouteIdx}][pickup_from]" class="form-control transport-pickup-from" placeholder="e.g. JED APT">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-map-marker-check me-1 text-danger"></i> Destination</label>
                            <input type="text" name="transports[${nextRouteIdx}][destination]" class="form-control transport-destination" placeholder="e.g. MAK HTL">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-dark"><i class="mdi mdi-barcode me-1 text-secondary"></i> BRN #</label>
                            <input type="text" name="transports[${nextRouteIdx}][brn]" class="form-control" placeholder="BRN Number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="mdi mdi-airplane-takeoff me-1 text-info"></i> Airline</label>
                            <select name="transports[${nextRouteIdx}][airline]" class="form-select transport-airline-select">
                                ${airlinesOptions}
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label text-dark fw-bold">Currency</label>
                            <select name="transports[${nextRouteIdx}][currency]" class="form-select transport-currency fw-bold"
                                onchange="calculateTransportRow(this.closest('.route-block'))">
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
                                <input type="number" step="0.0001" name="transports[${nextRouteIdx}][cost_roe]" class="form-control transport-cost-roe" placeholder="75.00" value="${defaultCostRoe}" ${isPkr ? 'readonly' : ''}
                                    oninput="calculateTransportRow(this.closest('.route-block'))" onchange="calculateTransportRow(this.closest('.route-block'))">
                            </div>
                            <input type="hidden" name="transports[${nextRouteIdx}][roe]" class="transport-roe" value="${defaultCostRoe}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-success fw-bold">Sale ROE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light transport-curr-prefix">1 ${flightCurr} =</span>
                                <input type="number" step="0.0001" name="transports[${nextRouteIdx}][sale_roe]" class="form-control transport-sale-roe" placeholder="75.00" value="${defaultSaleRoe}" ${isPkr ? 'readonly' : ''}
                                    oninput="calculateTransportRow(this.closest('.route-block'))" onchange="calculateTransportRow(this.closest('.route-block'))">
                            </div>
                        </div>
                        <div class="col-md-2"><label class="form-label text-danger fw-semibold">Cost Rate (<span class="transport-curr-label">${flightCurr}</span>)</label>
                            <input type="number" step="0.01" name="transports[${nextRouteIdx}][cost_rate]" class="form-control transport-cost-rate" placeholder="0.00" value="0"
                                oninput="calculateTransportRow(this.closest('.route-block'))" onchange="calculateTransportRow(this.closest('.route-block'))"></div>
                        <div class="col-md-3"><label class="form-label text-danger fw-semibold">Total Cost (<span class="transport-curr-label">${flightCurr}</span> & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-danger"><span class="transport-curr-name">${flightCurr}:</span><strong class="transport-cost-sar-disp">0.00 ${flightCurr}</strong></div>
                                <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">0.00 PKR</strong></div>
                            </div>
                            <input type="hidden" name="transports[${nextRouteIdx}][cost_amount]" class="transport-cost-amount" value="0"></div>
                        <div class="col-md-2"><label class="form-label text-success fw-semibold">Sell Rate (<span class="transport-curr-label">${flightCurr}</span>)</label>
                            <input type="number" step="0.01" name="transports[${nextRouteIdx}][selling_rate]" class="form-control transport-sell-rate" placeholder="0.00" value="0"
                                oninput="calculateTransportRow(this.closest('.route-block'))" onchange="calculateTransportRow(this.closest('.route-block'))"></div>
                        <div class="col-md-3"><label class="form-label text-success fw-semibold">Total Sell (<span class="transport-curr-label">${flightCurr}</span> & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-success"><span class="transport-curr-name">${flightCurr}:</span><strong class="transport-sell-sar-disp">0.00 ${flightCurr}</strong></div>
                                <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="transport-sell-pkr-disp">0.00 PKR</strong></div>
                            </div>
                            <input type="hidden" name="transports[${nextRouteIdx}][selling_amount]" class="transport-sell-amount" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Notes</label>
                            <input type="text" name="transports[${nextRouteIdx}][notes]" class="form-control" placeholder="Notes"></div>
                        <div class="col-md-6"><label class="form-label">Transport Ticket / Receipt</label>
                            <input type="file" name="transports[${nextRouteIdx}][transport_ticket]" class="form-control" accept="image/*,.pdf"></div>
                    </div>
                </div>`);
            routeIdx = document.querySelectorAll('#routesList .route-block').length;
            const arrDate = document.querySelector('input[name="arrival_date"]')?.value || '';
            const arrTime = document.querySelector('input[name="arrival_time"]')?.value || '';
            const arrFlight = document.querySelector('input[name="arrival_flight"]')?.value || '';
            const arrAir = document.querySelector('select[name="arrival_airline"]')?.value || '';
            const allRoutes = document.querySelectorAll('.route-block');
            const lastRouteEl = allRoutes[allRoutes.length - 1];
            if (allRoutes.length >= 2 && lastRouteEl && (arrDate || arrTime || arrFlight || arrAir)) {
                if (arrDate) {
                    const dInput = lastRouteEl.querySelector('input[name*="[travel_date]"]');
                    if (dInput) { dInput.value = arrDate; dInput.setAttribute('data-auto', 'true'); }
                }
                if (arrTime) {
                    const tInput = lastRouteEl.querySelector('input[name*="[travel_time]"]');
                    if (tInput) { tInput.value = arrTime; tInput.setAttribute('data-auto', 'true'); }
                }
                if (arrFlight) {
                    const fInput = lastRouteEl.querySelector('input[name*="[flight_no]"]');
                    if (fInput) { fInput.value = arrFlight; fInput.setAttribute('data-auto', 'true'); }
                }
                if (arrAir) {
                    const aSelect = lastRouteEl.querySelector('select[name*="[airline]"]');
                    if (aSelect) {
                        if (typeof setSelectAirline === 'function') setSelectAirline(aSelect, arrAir);
                        else aSelect.value = arrAir;
                        aSelect.setAttribute('data-auto', 'true');
                    }
                }
            }
            if (typeof initBookingSelect2 === 'function' && lastRouteEl) initBookingSelect2(lastRouteEl);
            if (typeof calculateAllTransports === 'function') calculateAllTransports();
            if (typeof calcTotal === 'function') calcTotal();
            if (typeof updateTransportSummary === 'function') updateTransportSummary();
        } catch(err) {
            console.error('addNewRoute error:', err);
        }
    }
    window.addNewRoute = addNewRoute;

        function removeRouteBlock(btn) {
            try {
                if (!btn) return;
                const el = (btn.jquery ? btn[0] : btn);
                const block = (el instanceof Element) ? el.closest('.route-block') : null;
                if (block) block.remove();
                try { if (typeof calculateAllTransports === 'function') calculateAllTransports(); } catch(e){}
                try { if (typeof updateTransportSummary === 'function') updateTransportSummary(); } catch(e){}
                try { if (typeof calcTotal === 'function') calcTotal(); } catch(e){}
            } catch(err) {
                console.error('removeRouteBlock error:', err);
            }
        }
        window.removeRouteBlock = removeRouteBlock;

        document.getElementById('routesList')?.addEventListener('input', function(e) {
            updateTransportSummary();
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
            updateTransportSummary();
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

            const qty = parseFloat(row.querySelector('input[name*="[quantity]"], .transport-qty')?.value) || 1;
            const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;

            const costCurr = costRate * qty;
            const costPkr = costCurr * costRoe;
            const sellCurr = sellRate * qty;
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
            updateTransportSummary();
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
            // Non-intrusive: Keep for compatibility without force-clamping user-entered dates
            return;
        }

        function handleHotelDateCalc(target) {
            const row = target.closest('.hotel-block');
            if (!row) return;

            const checkInEl  = row.querySelector('input[name*="[check_in]"]');
            const checkOutEl = row.querySelector('input[name*="[check_out]"]');
            const nightsEl   = row.querySelector('input[name*="[no_of_nights]"]');

            if (!checkInEl || !checkOutEl || !nightsEl) return;

            const inVal  = checkInEl.value;
            const outVal = checkOutEl.value;
            const isNights = target === nightsEl;

            if (inVal && outVal && !isNights) {
                const dIn  = new Date(inVal + 'T00:00:00');
                const dOut = new Date(outVal + 'T00:00:00');
                const diff = Math.round((dOut.getTime() - dIn.getTime()) / (1000 * 3600 * 24));
                if (!isNaN(diff) && diff > 0) {
                    nightsEl.value = diff;
                }
            } else if (isNights && inVal) {
                const nVal = parseInt(nightsEl.value) || 1;
                const dIn = new Date(inVal + 'T00:00:00');
                dIn.setDate(dIn.getDate() + nVal);
                const year = dIn.getFullYear();
                const month = String(dIn.getMonth() + 1).padStart(2, '0');
                const day = String(dIn.getDate()).padStart(2, '0');
                checkOutEl.value = `${year}-${month}-${day}`;
            }

            calculateHotelRow(row, 'nights');
            calcTotal();
            updateHotelStaySummary();
        }

        // Main Total and Dual Currency Costing Calculation
        function calcTotal() {
            const parseNum = (v) => parseFloat(String(v || 0).replace(/,/g, '')) || 0;
            const adults   = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants  = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const pax      = Math.max(1, adults + children + infants);

            // 1. Flight Foreign & PKR (Cost & Selling)
            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            let flightForeign = 0;
            const flSellDisp = document.getElementById('flight_sell_sar_total')?.textContent || '';
            flightForeign = parseNum(flSellDisp);
            const flightSaleRoe = (flightCurr === 'PKR') ? 1 : (parseNum(document.getElementById('flight_sale_roe')?.value) || parseNum(document.getElementById('flight_cost_roe')?.value) || 75);
            const flightCostRoe = (flightCurr === 'PKR') ? 1 : (parseNum(document.getElementById('flight_cost_roe')?.value) || 75);

            let flightCostForeign = 0;
            const flCostDisp = document.getElementById('flight_cost_sar_total')?.textContent || '';
            flightCostForeign = parseNum(flCostDisp);
            let flightCostPkr = parseNum(document.getElementById('flight_cost')?.value);
            if (flightCostPkr === 0 && flightCostForeign > 0) {
                flightCostPkr = (flightCurr === 'PKR') ? flightCostForeign : (flightCostForeign * flightCostRoe);
            }

            let flightPkr = 0;
            const flSellPkrDisp = document.getElementById('flight_sell_pkr_total')?.textContent || '';
            if (flSellPkrDisp) {
                flightPkr = parseNum(flSellPkrDisp);
            }
            if (flightPkr === 0) {
                flightPkr = parseNum(document.getElementById('flight_charges')?.value);
            }
            if (flightPkr === 0 && flightForeign > 0) {
                flightPkr = (flightCurr === 'PKR') ? flightForeign : (flightForeign * flightSaleRoe);
            }

            if (flightForeign === 0 && flightPkr > 0 && flightCurr !== 'PKR' && flightSaleRoe > 0) {
                flightForeign = flightPkr / flightSaleRoe;
                const elFlDisp = document.getElementById('flight_sell_sar_total');
                if (elFlDisp) elFlDisp.textContent = flightForeign.toFixed(2) + ' ' + flightCurr;
            }

            // Always synchronize flight_charges input so it never stays 0
            const elCharges = document.getElementById('flight_charges');
            if (elCharges) elCharges.value = flightPkr.toFixed(2);
            const elChargesDisp = document.getElementById('flight_charges_disp');
            if (elChargesDisp) elChargesDisp.value = flightPkr.toFixed(2);

            // 2. Hotel Foreign & PKR
            // 2. Hotel Foreign & PKR
            let hotelCostForeign = 0;
            let hotelCostPkr = 0;
            let hotelSellForeign = 0;
            let hotelSellPkr = 0;
            let hotelCurrs = new Set();
            document.querySelectorAll('.hotel-block').forEach(row => {
                const nights   = parseFloat(row.querySelector('.hotel-nights, input[name*="[no_of_nights]"]')?.value) || 0;
                const rooms    = parseFloat(row.querySelector('.hotel-rooms, input[name*="[no_of_rooms]"]')?.value) || 1;
                const costRate = parseFloat(row.querySelector('.hotel-cost-rate, input[name*="[cost_per_night]"]')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.hotel-sell-rate, input[name*="[selling_per_night]"]')?.value) || 0;
                const curr     = row.querySelector('.hotel-currency')?.value || 'SAR';
                const isPkr    = (curr === 'PKR');
                const costRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.hotel-cost-roe')?.value) || parseFloat(row.querySelector('.hotel-roe')?.value) || 75);
                const saleRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.hotel-sale-roe')?.value) || costRoe || 75);

                let extraCostPerNight = 0;
                let extraSellPerNight = 0;
                row.querySelectorAll('.additional-room-item').forEach(extraRoom => {
                    extraCostPerNight += parseFloat(extraRoom.querySelector('.add-room-cost')?.value) || 0;
                    extraSellPerNight += parseFloat(extraRoom.querySelector('.add-room-sell')?.value) || 0;
                });

                const cForeign = nights * ((rooms * costRate) + extraCostPerNight);
                const sForeign = nights * ((rooms * sellRate) + extraSellPerNight);
                const cPkr = cForeign * costRoe;
                const sPkr = sForeign * saleRoe;

                const costTotalInput = row.querySelector('.hotel-cost-total');
                const sellTotalInput = row.querySelector('.hotel-sell-total');
                const costSarDisp    = row.querySelector('.hotel-cost-sar-disp');
                const costPkrDisp    = row.querySelector('.hotel-cost-pkr-disp');
                const sellSarDisp    = row.querySelector('.hotel-sell-sar-disp');
                const sellPkrDisp    = row.querySelector('.hotel-sell-pkr-disp');

                if (costSarDisp) costSarDisp.textContent = cForeign.toFixed(2) + ' ' + curr;
                if (costPkrDisp) costPkrDisp.textContent = cPkr.toFixed(2) + ' PKR';
                if (costTotalInput) costTotalInput.value = cPkr.toFixed(2);

                if (sellSarDisp) sellSarDisp.textContent = sForeign.toFixed(2) + ' ' + curr;
                if (sellPkrDisp) sellPkrDisp.textContent = sPkr.toFixed(2) + ' PKR';
                if (sellTotalInput) sellTotalInput.value = sPkr.toFixed(2);

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
                const qty      = parseFloat(row.querySelector('input[name*="[quantity]"], .transport-qty')?.value) || 1;
                const costRate = parseFloat(row.querySelector('.transport-cost-rate, input[name*="[cost_rate]"]')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.transport-sell-rate, input[name*="[selling_rate]"]')?.value) || 0;
                const curr     = row.querySelector('.transport-currency')?.value || 'SAR';
                const isPkr    = (curr === 'PKR');
                const costRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.transport-cost-roe')?.value) || parseFloat(row.querySelector('.transport-roe')?.value) || 75);
                const saleRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.transport-sale-roe')?.value) || costRoe || 75);

                const lineCost = costRate * qty;
                const lineSell = sellRate * qty;
                const lineCostPkr = lineCost * costRoe;
                const lineSellPkr = lineSell * saleRoe;

                const costSarDisp  = row.querySelector('.transport-cost-sar-disp');
                const costPkrDisp  = row.querySelector('.transport-cost-pkr-disp');
                const costAmtInput = row.querySelector('.transport-cost-amount');
                const sellSarDisp  = row.querySelector('.transport-sell-sar-disp');
                const sellPkrDisp  = row.querySelector('.transport-sell-pkr-disp');
                const sellAmtInput = row.querySelector('.transport-sell-amount');

                if (costSarDisp) costSarDisp.textContent = lineCost.toFixed(2) + ' ' + curr;
                if (costPkrDisp) costPkrDisp.textContent = lineCostPkr.toFixed(2) + ' PKR';
                if (costAmtInput) costAmtInput.value = lineCostPkr.toFixed(2);

                if (sellSarDisp) sellSarDisp.textContent = lineSell.toFixed(2) + ' ' + curr;
                if (sellPkrDisp) sellPkrDisp.textContent = lineSellPkr.toFixed(2) + ' PKR';
                if (sellAmtInput) sellAmtInput.value = lineSellPkr.toFixed(2);

                transportCostForeign += lineCost;
                transportCostPkr += lineCostPkr;

                transportSellForeign += lineSell;
                transportSellPkr += lineSellPkr;
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
                totalForeign = (flightCurr !== 'PKR' ? flightForeign : 0) + hotelSellForeign + transportSellForeign + visaSellForeign;
            } else {
                foreignUnit = (flightCurr !== 'PKR') ? flightCurr : 'SAR';
                totalForeign = (flightCurr !== 'PKR' ? flightForeign : 0) + hotelSellForeign + transportSellForeign + visaSellForeign;
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

            // Total Direct Costs PKR & Gross Profit
            const addServicesCost = parseFloat(document.getElementById('additional_services_cost')?.value) || 0;
            const totalDirectCostPkr = hotelCostPkr + flightCostPkr + visaCostPkr + transportCostPkr + addServicesCost;
            const grossProfitPkr = totalPkr - totalDirectCostPkr;

            // Total Direct Costs Foreign & Gross Profit
            const totalCostForeign = (flightCurr === foreignUnit ? flightCostForeign : 0)
                                   + (hotelCurrLabel === foreignUnit ? hotelCostForeign : 0)
                                   + (transportCurrLabel === foreignUnit ? transportCostForeign : 0)
                                   + (visaCurrLabel === foreignUnit ? visaCostForeign : 0);
            const grossProfitForeign = totalForeign - totalCostForeign;

            // Costing Dual Currency Cards - Foreign Currency Card
            if (document.getElementById('cost_foreign_curr_title')) document.getElementById('cost_foreign_curr_title').textContent = (activeCurrs.size === 1) ? Array.from(activeCurrs)[0] : (activeCurrs.size > 1 ? 'Multi-Curr' : flightCurr);
            if (document.getElementById('cost_sar_flight_cost')) {
                document.getElementById('cost_sar_flight_cost').textContent = (flightCurr === 'PKR') 
                    ? `—` 
                    : `${flightCostForeign.toFixed(2)} ${flightCurr}`;
            }
            if (document.getElementById('cost_sar_flight')) {
                document.getElementById('cost_sar_flight').textContent = (flightCurr === 'PKR') 
                    ? `—` 
                    : `${flightForeign.toFixed(2)} ${flightCurr}`;
            }
            if (document.getElementById('cost_sar_hotel_cost')) document.getElementById('cost_sar_hotel_cost').textContent = `${hotelCostForeign.toFixed(2)} ${hotelCurrLabel}`;
            if (document.getElementById('cost_sar_hotel')) document.getElementById('cost_sar_hotel').textContent = `${hotelSellForeign.toFixed(2)} ${hotelCurrLabel}`;
            if (document.getElementById('cost_sar_transport_cost')) document.getElementById('cost_sar_transport_cost').textContent = `${transportCostForeign.toFixed(2)} ${transportCurrLabel}`;
            if (document.getElementById('cost_sar_transport')) document.getElementById('cost_sar_transport').textContent = `${transportSellForeign.toFixed(2)} ${transportCurrLabel}`;
            if (document.getElementById('cost_sar_visa_cost')) document.getElementById('cost_sar_visa_cost').textContent = `${visaCostForeign.toFixed(2)} ${visaCurrLabel}`;
            if (document.getElementById('cost_sar_visa')) document.getElementById('cost_sar_visa').textContent = `${visaSellForeign.toFixed(2)} ${visaCurrLabel}`;
            if (document.getElementById('cost_sar_total_cost')) document.getElementById('cost_sar_total_cost').textContent = `${totalCostForeign.toFixed(2)} ${foreignUnit}`;
            if (document.getElementById('cost_sar_total')) document.getElementById('cost_sar_total').textContent = `${totalForeign.toFixed(2)} ${foreignUnit}`;
            if (document.getElementById('cost_sar_balance')) document.getElementById('cost_sar_balance').textContent = `${balanceForeign.toFixed(2)} ${foreignUnit}`;

            // Costing Dual Currency Cards - PKR Card
            if (document.getElementById('cost_pkr_flight_cost')) document.getElementById('cost_pkr_flight_cost').textContent = `${flightCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_flight')) document.getElementById('cost_pkr_flight').textContent = `${flightPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_hotel_cost')) document.getElementById('cost_pkr_hotel_cost').textContent = `${hotelCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_hotel')) document.getElementById('cost_pkr_hotel').textContent = `${hotelSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_transport_cost')) document.getElementById('cost_pkr_transport_cost').textContent = `${transportCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_transport')) document.getElementById('cost_pkr_transport').textContent = `${transportSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_visa_cost')) document.getElementById('cost_pkr_visa_cost').textContent = `${visaCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_visa')) document.getElementById('cost_pkr_visa').textContent = `${visaSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_total_cost')) document.getElementById('cost_pkr_total_cost').textContent = `${totalDirectCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_total')) document.getElementById('cost_pkr_total').textContent = `${totalPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_balance')) document.getElementById('cost_pkr_balance').textContent = `${balancePkr.toFixed(2)} PKR`;

            // Costing Table Breakdown - Cost Row
            if (document.getElementById('cost_sum_hotel')) document.getElementById('cost_sum_hotel').textContent = hotelCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_visa')) document.getElementById('cost_sum_visa').textContent = visaCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_flight')) document.getElementById('cost_sum_flight').textContent = flightCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_transport')) document.getElementById('cost_sum_transport').textContent = transportCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_other')) document.getElementById('cost_sum_other').textContent = addServicesCost.toFixed(2);
            if (document.getElementById('cost_sum_total')) document.getElementById('cost_sum_total').textContent = totalDirectCostPkr.toFixed(2);

            // Costing Table Breakdown - Selling Row
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
            if (e.target.matches('.hotel-cost-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'cost');
                calculateHotelRow(row, 'costRate');
            } else if (e.target.matches('.hotel-sell-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'sell');
                calculateHotelRow(row, 'sellRate');
            } else if (e.target.matches('.hotel-rooms, input[name*="[no_of_rooms]"]')) {
                const row = e.target.closest('.hotel-block');
                if (row) {
                    calculateHotelRow(row, 'rooms');
                    calcTotal();
                    updateHotelStaySummary();
                }
            } else if (e.target.matches('.hotel-nights, input[name*="[no_of_nights]"], .hotel-roe, .hotel-cost-roe, .hotel-sale-roe')) {
                const row = e.target.closest('.hotel-block');
                if (row) {
                    calculateHotelRow(row, 'nights');
                    calcTotal();
                    updateHotelStaySummary();
                }
            } else if (e.target.matches('.hotel-person-cost, .hotel-person-sell')) {
                const row = e.target.closest('.hotel-block');
                if (row) {
                    calculateHotelRow(row, 'personRate');
                    calcTotal();
                    updateHotelStaySummary();
                }
            }
            if (e.target.matches('.visa-roe, .visa-cost-roe, .visa-sale-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                if (row) calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-roe, .transport-sale-roe, .transport-cost-rate, .transport-sell-rate, .transport-qty, input[name*="[quantity]"]')) {
                const row = e.target.closest('.route-block');
                if (row) {
                    calculateTransportRow(row);
                    calculateAllTransports();
                    calcTotal();
                    updateTransportSummary();
                }
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
            if (e.target.matches('.hotel-cost-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'cost');
                calculateHotelRow(row, 'costRate');
            } else if (e.target.matches('.hotel-sell-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'sell');
                calculateHotelRow(row, 'sellRate');
            } else if (e.target.matches('.hotel-rooms, input[name*="[no_of_rooms]"]')) {
                const row = e.target.closest('.hotel-block');
                if (row) {
                    calculateHotelRow(row, 'rooms');
                    calcTotal();
                    updateHotelStaySummary();
                }
            } else if (e.target.matches('.hotel-nights, input[name*="[no_of_nights]"], .hotel-roe, .hotel-cost-roe, .hotel-sale-roe')) {
                const row = e.target.closest('.hotel-block');
                if (row) {
                    calculateHotelRow(row, 'nights');
                    calcTotal();
                    updateHotelStaySummary();
                }
            } else if (e.target.matches('.hotel-person-cost, .hotel-person-sell')) {
                const row = e.target.closest('.hotel-block');
                if (row) {
                    calculateHotelRow(row, 'personRate');
                    calcTotal();
                    updateHotelStaySummary();
                }
            }
            if (e.target.matches('.visa-roe, .visa-cost-roe, .visa-sale-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                if (row) calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-roe, .transport-sale-roe, .transport-cost-rate, .transport-sell-rate, .transport-qty, input[name*="[quantity]"]')) {
                const row = e.target.closest('.route-block');
                if (row) {
                    calculateTransportRow(row);
                    calculateAllTransports();
                    calcTotal();
                    updateTransportSummary();
                }
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
        const initPax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
        if (document.querySelectorAll('#personsList .person-card').length !== initPax) {
            rebuildPersonsList(initPax);
        }
        if (document.querySelectorAll('#visasList .visa-card').length !== initPax) {
            rebuildVisasList(initPax);
        }
        updatePaxCountsFromPersons();
        syncFlightPersons();
        syncAllHotelPersons();
        document.querySelectorAll('.hotel-block').forEach(r => calculateHotelRow(r));
        document.querySelectorAll('.route-block').forEach(r => calculateTransportRow(r));
        document.querySelectorAll('.visa-card').forEach(r => calculateVisaRow(r));
        syncBookingHotelDates();
        updateHotelStaySummary();
        updateTransportSummary();
        syncFlightDatesToTransport();
        calculateFlight();
        calcTotal();

        // Ensure summary tables & calculations refresh whenever tabs are activated
        $('a[data-bs-toggle="tab"][href="#tab-persons"]').on('shown.bs.tab', function() {
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const container = document.getElementById('personsList');
            if (container && container.querySelectorAll('.person-card').length !== pax) rebuildPersonsList(pax);
            if (typeof updatePaxCountsFromPersons === 'function') updatePaxCountsFromPersons();
        });
        $('a[data-bs-toggle="tab"][href="#tab-flight"]').on('shown.bs.tab', function() {
            if (typeof syncFlightPersons === 'function') syncFlightPersons();
        });
        $('a[data-bs-toggle="tab"][href="#tab-hotel"]').on('shown.bs.tab', function() {
            if (typeof syncAllHotelPersons === 'function') syncAllHotelPersons();
            if (typeof updateHotelStaySummary === 'function') updateHotelStaySummary();
            if (typeof calcTotal === 'function') calcTotal();
        });
        $('a[data-bs-toggle="tab"][href="#tab-transport"]').on('shown.bs.tab', function() {
            syncFlightDatesToTransport();
            if (typeof updateTransportSummary === 'function') updateTransportSummary();
            if (typeof calcTotal === 'function') calcTotal();
        });
        $('a[data-bs-toggle="tab"][href="#tab-visa"]').on('shown.bs.tab', function() {
            if (typeof calcTotal === 'function') calcTotal();
        });
        $('a[data-bs-toggle="tab"][href="#tab-costing"]').on('shown.bs.tab', function() {
            if (typeof calcTotal === 'function') calcTotal();
        });

        // Independent Tab Booking Dates (Allowing custom dates per service without overwriting each other)
        const depDateInput = document.querySelector('input[name="departure_date"]');
        const mainBkInput = document.getElementById('booking_date');
        if (mainBkInput) {
            mainBkInput.addEventListener('change', function() {
                // Only populate flight departure date if it is currently empty
                if (depDateInput && !depDateInput.value) {
                    depDateInput.value = this.value;
                }
            });
        }

        // Tab-specific date propagation to rows
        document.getElementById('booking_date_hotel_tab')?.addEventListener('change', function() {
            document.querySelectorAll('.hotel-booking-date').forEach(el => el.value = this.value);
        });
        document.getElementById('booking_date_transport_tab')?.addEventListener('change', function() {
            document.querySelectorAll('input[name*="[booking_date]"].transport-booking-date, .transport-booking-date').forEach(el => el.value = this.value);
        });
        document.getElementById('booking_date_visa_tab')?.addEventListener('change', function() {
            document.querySelectorAll('.visa-booking-date').forEach(el => el.value = this.value);
        });

        $(document).ready(function() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && jQuery.fn.summernote) {
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
            e.preventDefault();
            const submitter = e.submitter;
            const action = submitter?.classList.contains('btn-save-close') ? 'save_close' : 'save_stay';
            submitBookingForm(submitter, action);
        });

        // Initialize active tab from URL query param ?tab=... or hash
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const targetTab = urlParams.get('tab') || window.location.hash;
            if (targetTab) {
                setTimeout(() => activateTabByHref(targetTab), 50);
            }
        });

        // --- TIME FORMAT & FLATPICKR HANDLING ---
        function initFlightTimePickers(format = '12') {
            if (typeof flatpickr !== 'function') return;
            const is24 = (format === '24');
            document.querySelectorAll('.flight-time-picker').forEach(el => {
                if (el._flatpickr) {
                    el._flatpickr.destroy();
                }
                flatpickr(el, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: is24 ? "H:i" : "h:i K",
                    time_24hr: is24,
                    allowInput: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        $(instance.element).trigger('input').trigger('change');
                        if (typeof syncFlightDatesToTransport === 'function') {
                            syncFlightDatesToTransport();
                        }
                    }
                });
            });
        }

        const initialTimeFmt = document.querySelector('input[name="flight_time_format"]:checked')?.value || '12';
        initFlightTimePickers(initialTimeFmt);

        document.querySelectorAll('.flight-time-format-radio').forEach(r => {
            r.addEventListener('change', function() {
                initFlightTimePickers(this.value);
            });
        });

        // --- INDIVIDUAL FLIGHT DETAILS & MASTER CASCADE TO PASSENGERS ---
        function onPersonFlightSupplierChange(selectEl, idx) {
            const val = selectEl.value;
            const badge = document.getElementById('person_sup_badge_' + idx);
            if (val) {
                selectEl.setAttribute('data-customized', 'true');
                if (badge) {
                    badge.className = 'badge bg-soft-info text-primary border border-info border-opacity-25 fs-10 mt-1 d-inline-block person-supplier-badge';
                    badge.innerHTML = '<i class="mdi mdi-account-cog me-1"></i>Individual Supplier';
                }
            } else {
                selectEl.removeAttribute('data-customized');
                if (badge) {
                    badge.className = 'badge bg-light text-muted border fs-10 mt-1 d-inline-block person-supplier-badge';
                    badge.innerHTML = '<i class="mdi mdi-link-variant me-1"></i>Linked to Main Supplier';
                }
            }
        }

        function onPersonFlightTravelInput(inputEl) {
            inputEl.setAttribute('data-customized', 'true');
            inputEl.classList.add('border-info');
        }

        function syncMainFlightDetailsToPassengers() {
            const mainDate = document.getElementById('flight_booking_date')?.value || document.querySelector('input[name="departure_date"]')?.value || '';
            const mainSupSelect = document.getElementById('flight_supplier_id') || document.querySelector('select[name="flight_supplier_id"]');
            const mainSupId = mainSupSelect?.value || '';
            const mainSupName = (mainSupSelect && mainSupSelect.selectedIndex >= 0 && mainSupSelect.value) ? (mainSupSelect.options[mainSupSelect.selectedIndex]?.text?.replace(/^--.*--$/, '')?.trim() || '') : '';
            const mainPnr = document.querySelector('input[name="departure_pnr"]')?.value || '';
            const mainTime = document.querySelector('input[name="departure_time"]')?.value || '';
            const mainTrip = document.querySelector('input[name="flight_trip_type"]:checked')?.value === 'oneway' ? 'departure_only' : 'round_trip';

            document.querySelectorAll('.flight-person-row').forEach(row => {
                const idx = row.getAttribute('data-idx');
                const sSelect = row.querySelector('.person-flight-supplier');
                const pInput  = row.querySelector('.person-flight-pnr');
                const dInput  = row.querySelector('.person-flight-date');
                const tInput  = row.querySelector('.person-flight-time');
                const trSelect = row.querySelector('.person-flight-trip-type');

                // Supplier Sync
                if (sSelect) {
                    if (sSelect.options.length > 0) {
                        sSelect.options[0].text = `-- Same as Main ${mainSupName ? '(' + mainSupName + ')' : 'Supplier'} --`;
                    }
                    if (sSelect.getAttribute('data-customized') !== 'true') {
                        sSelect.value = '';
                        const badge = document.getElementById('person_sup_badge_' + idx);
                        if (badge) {
                            badge.className = 'badge bg-light text-muted border fs-10 mt-1 d-inline-block person-supplier-badge';
                            badge.innerHTML = '<i class="mdi mdi-link-variant me-1"></i>Linked to Main Supplier';
                        }
                    }
                }

                // PNR Sync
                if (pInput && pInput.getAttribute('data-customized') !== 'true') {
                    pInput.value = mainPnr;
                }

                // Date Sync
                if (dInput && dInput.getAttribute('data-customized') !== 'true') {
                    dInput.value = mainDate;
                }

                // Time Sync
                if (tInput && tInput.getAttribute('data-customized') !== 'true') {
                    tInput.value = mainTime;
                }

                // Trip Type Sync
                if (trSelect && trSelect.getAttribute('data-customized') !== 'true') {
                    if (trSelect.value !== mainTrip) {
                        trSelect.value = mainTrip;
                        if (typeof onTripTypeChange === 'function' && idx !== null) {
                            onTripTypeChange(idx, mainTrip);
                        }
                    }
                }
            });
        }

        $(document).on('change select2:select', '#flight_supplier_id, select[name="flight_supplier_id"]', syncMainFlightDetailsToPassengers);
        $(document).on('input change', '#flight_booking_date, input[name="departure_date"], input[name="departure_pnr"], input[name="departure_time"], input[name="flight_trip_type"]', syncMainFlightDetailsToPassengers);

        // --- SELECT2 INITIALIZATION HELPER ---
        function initBookingSelect2(context) {
            if (typeof jQuery === "undefined" || !jQuery.fn || typeof jQuery.fn.select2 !== 'function') return;
            const $ctx = context ? $(context) : $(document);
            $ctx.find('select.select2').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: $(this).attr('placeholder') || 'Search & Select...'
                    });
                }
            });
            $ctx.find('.select2-container').css('width', '100%');
        }

        // --- BOOKING CATEGORY SYNC & HOTEL BUTTON / DROPDOWN FILTERING ---
        function applyCategoryToHotels(cat) {
            const btnMakkah = document.getElementById('btnAddMakkahHotel');
            const btnMadinah = document.getElementById('btnAddMadinahHotel');
            const btnOther = document.getElementById('btnAddOtherHotel');
            const lblOther = document.getElementById('lblAddOtherHotel');
            const btnSelect = document.getElementById('addHotel');
            const alertMsg = document.querySelector('#noHotelAlert p');

            if (cat === 'domestic') {
                if (btnMakkah) btnMakkah.style.setProperty('display', 'none', 'important');
                if (btnMadinah) btnMadinah.style.setProperty('display', 'none', 'important');
                if (btnOther) btnOther.style.removeProperty('display');
                if (lblOther) lblOther.textContent = '+ Add Domestic Hotel';
                if (btnSelect) btnSelect.style.setProperty('display', 'none', 'important');
                if (alertMsg) alertMsg.textContent = 'Click the button below to add a domestic hotel.';
            } else if (cat === 'international') {
                if (btnMakkah) btnMakkah.style.setProperty('display', 'none', 'important');
                if (btnMadinah) btnMadinah.style.setProperty('display', 'none', 'important');
                if (btnOther) btnOther.style.removeProperty('display');
                if (lblOther) lblOther.textContent = '+ Add International Hotel';
                if (btnSelect) btnSelect.style.setProperty('display', 'none', 'important');
                if (alertMsg) alertMsg.textContent = 'Click the button below to add an international hotel.';
            } else {
                if (btnMakkah) btnMakkah.style.removeProperty('display');
                if (btnMadinah) btnMadinah.style.removeProperty('display');
                if (btnOther) btnOther.style.removeProperty('display');
                if (lblOther) lblOther.textContent = '+ Add Other Hotel';
                if (btnSelect) btnSelect.style.removeProperty('display');
                if (alertMsg) alertMsg.textContent = 'Click one of the buttons below to add a hotel for Makkah, Madinah or Other city.';
            }

            // Refresh options in existing hotel dropdowns
            document.querySelectorAll('.hotel-block').forEach(block => {
                const select = block.querySelector('select.hotel-name-select, select[name*="[hotel_name]"]');
                if (select) {
                    const currentVal = select.value;
                    const blockLoc = block.getAttribute('data-location') || 'other';
                    let filterLoc = null;
                    if (cat === 'umrah' || cat === 'hajj') {
                        if (blockLoc === 'makkah') filterLoc = 'makkah';
                        else if (blockLoc === 'madinah') filterLoc = 'madinah';
                    }
                    select.innerHTML = buildHotelsOptions(filterLoc);
                    if (currentVal) {
                        try {
                            if (!select.querySelector(`option[value="${CSS.escape(currentVal)}"]`)) {
                                const opt = document.createElement('option');
                                opt.value = currentVal;
                                opt.textContent = currentVal;
                                select.appendChild(opt);
                            }
                        } catch(e) {}
                        select.value = currentVal;
                    }
                    if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
                        if ($(select).hasClass('select2-hidden-accessible')) {
                            $(select).select2('destroy');
                        }
                        $(select).select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Select Hotel'
                        });
                    }
                }
            });
        }

        $(document).on('change', '.booking-category-radio', function() {
            const cat = this.value;
            const currSelect = document.getElementById('flight_currency');
            const costRoe = document.getElementById('flight_cost_roe');
            const saleRoe = document.getElementById('flight_sale_roe');
            const vInput = document.getElementById('booking_voucher_input');
            const vDisp = document.getElementById('booking_voucher_disp');
            const fltVoucher = document.getElementById('flight_voucher_no')?.value;

            if (cat === 'domestic') {
                if (currSelect) $(currSelect).val('PKR').trigger('change');
                if (costRoe) costRoe.value = '1';
                if (saleRoe) saleRoe.value = '1';
                if (fltVoucher && vInput) {
                    vInput.value = fltVoucher;
                    if (vDisp) vDisp.textContent = fltVoucher;
                }
            } else {
                if (currSelect && currSelect.value === 'PKR') $(currSelect).val('SAR').trigger('change');
                if (costRoe && costRoe.value === '1') costRoe.value = '75';
                if (saleRoe && saleRoe.value === '1') saleRoe.value = '75';
            }

            applyCategoryToHotels(cat);
        });

        // Initialize category hotels view on edit load
        const initCategory = document.querySelector('input[name="booking_category"]:checked')?.value || '{{ $bCat }}';
        applyCategoryToHotels(initCategory);

        // Initialize Select2 on all supplier and hotel dropdowns
        initBookingSelect2();
        $(document).on('shown.bs.tab', function() {
            initBookingSelect2();
        });

        // Trigger initial updateHotelStaySummary, updateTransportSummary, and calcTotal on edit page load
        setTimeout(function() {
            if (typeof calculateFlight === 'function') calculateFlight();
            document.querySelectorAll('.hotel-block').forEach(r => { if (typeof calculateHotelRow === 'function') calculateHotelRow(r); });
            if (typeof calculateAllTransports === 'function') calculateAllTransports();
            if (typeof updateHotelStaySummary === 'function') updateHotelStaySummary();
            if (typeof updateTransportSummary === 'function') updateTransportSummary();
            if (typeof calcTotal === 'function') calcTotal();
        }, 100);
    </script>
 
@endsection

 
    