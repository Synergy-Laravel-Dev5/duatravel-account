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
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <span>Persons: <strong id="bar_pax">{{ $booking->no_of_pax }}</strong></span>
                        <span style="{{ $isUmrah ? 'display:none;' : '' }}">|</span>
                        <span style="{{ $isUmrah ? 'display:none;' : '' }}">Package Cost: <strong id="bar_adult">{{ number_format($booking->package_cost * $booking->no_of_pax, 2) }}</strong></span>
                        <span>|</span>
                        <span>Hotel: <strong id="bar_hotel">{{ number_format($booking->hotel_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span>Visa: <strong id="bar_visa">{{ number_format($booking->visa_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span>Flight: <strong id="bar_flight">{{ number_format($booking->flight_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span>Transport: <strong id="bar_transport">{{ number_format($booking->transport_charges ?? 0, 2) }}</strong></span>
                        <span>|</span>
                        <span class="text-primary fw-semibold">Total: <strong
                                id="bar_total">{{ number_format($booking->total_amount, 2) }}</strong></span>
                        <span>|</span>
                        <span class="text-danger fw-semibold">Balance: <strong
                                id="bar_balance">{{ number_format($booking->balance, 2) }}</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 bg-white px-2 py-1 rounded border">
                        <label for="top_currency_select" class="form-label mb-0 fs-12 fw-bold text-dark text-nowrap"><i class="mdi mdi-cash-multiple text-primary me-1"></i>Currency:</label>
                        <select id="top_currency_select" class="form-select form-select-sm py-0 px-2 fw-bold text-primary border-0 bg-transparent" style="width: 85px; height: 26px; cursor: pointer;">
                            <option value="PKR" {{ old('currency', $booking->currency ?? 'PKR') === 'PKR' ? 'selected' : '' }}>PKR (Rs)</option>
                            <option value="SAR" {{ old('currency', $booking->currency ?? 'PKR') === 'SAR' ? 'selected' : '' }}>SAR (SAR)</option>
                            <option value="USD" {{ old('currency', $booking->currency ?? 'PKR') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="AED" {{ old('currency', $booking->currency ?? 'PKR') === 'AED' ? 'selected' : '' }}>AED (AED)</option>
                        </select>
                    </div>
                </div>

                <form action="{{ route('booking.update', $booking->id) }}" method="POST" id="bookingForm" enctype="multipart/form-data">
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
                                    <select name="package_year" id="package_year" class="form-select" required>
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

                                <div class="col-md-4">
                                    <label class="form-label">No of Pax <span class="text-danger">*</span></label>
                                    <input type="number" name="no_of_pax" id="no_of_pax" class="form-control"
                                        value="{{ old('no_of_pax', $booking->no_of_pax) }}" min="1" required>
                                    <small class="text-muted">Persons, Flight & Visa tabs will update automatically</small>
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
                                    <label class="form-label">Booking Date <small class="text-muted">(Optional)</small></label>
                                    <input type="date" name="booking_date" id="booking_date" class="form-control"
                                        value="{{ old('booking_date', $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                                    <select name="currency" id="booking_currency" class="form-select">
                                        <option value="PKR" {{ old('currency', $booking->currency ?? 'PKR') === 'PKR' ? 'selected' : '' }}>PKR (Rs)</option>
                                        <option value="SAR" {{ old('currency', $booking->currency ?? 'PKR') === 'SAR' ? 'selected' : '' }}>SAR (SAR)</option>
                                        <option value="USD" {{ old('currency', $booking->currency ?? 'PKR') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="AED" {{ old('currency', $booking->currency ?? 'PKR') === 'AED' ? 'selected' : '' }}>AED (AED)</option>
                                    </select>
                                </div>
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
                                                <label class="form-label" style="font-size:12px;">Haji ID</label>
                                                <input type="text" name="persons[{{ $i }}][haji_id]" id="person_haji_id_{{ $i }}"
                                                    class="form-control form-control-sm" value="{{ old('persons.'.$i.'.haji_id', $person->haji_id) }}" placeholder="Haji ID">
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
                                                <label class="form-label" style="font-size:12px;">Father Name</label>
                                                <input type="text" name="persons[{{ $i }}][father_name]" id="person_father_name_{{ $i }}"
                                                    class="form-control form-control-sm" value="{{ $person->father_name }}" placeholder="Father Name">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Passport #</label>
                                                <input type="text" name="persons[{{ $i }}][passport_number]" id="person_passport_{{ $i }}"
                                                    class="form-control form-control-sm" value="{{ $person->passport_number }}">
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
                                                <label class="form-label" style="font-size:12px;">Blood Group</label>
                                                <select name="persons[{{ $i }}][blood_group]" id="person_blood_group_{{ $i }}" class="form-select form-select-sm">
                                                    <option value="">-- Select --</option>
                                                    @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                                        <option value="{{ $bg }}" {{ ($person->blood_group ?? '') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">CNIC</label>
                                                <input type="text" name="persons[{{ $i }}][cnic]"
                                                    id="person_cnic_{{ $i }}"
                                                    class="form-control form-control-sm"
                                                    value="{{ $person->cnic ?? '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size:12px;">Phone</label>
                                                <input type="text" name="persons[{{ $i }}][phone]"
                                                    id="person_phone_{{ $i }}"
                                                    class="form-control form-control-sm"
                                                    value="{{ $person->phone ?? '' }}">
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
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">CNIC Front</label>
                                                    @if($person->cnic_front)
                                                        <div class="mb-1"><a href="{{ asset('storage/' . $person->cnic_front) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                                                    @endif
                                                    <input type="hidden" name="persons[{{ $i }}][old_cnic_front]" value="{{ $person->cnic_front }}">
                                                    <input type="file" name="persons[{{ $i }}][cnic_front]" class="form-control form-control-sm" accept="image/*,.pdf">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">CNIC Back</label>
                                                    @if($person->cnic_back)
                                                        <div class="mb-1"><a href="{{ asset('storage/' . $person->cnic_back) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                                                    @endif
                                                    <input type="hidden" name="persons[{{ $i }}][old_cnic_back]" value="{{ $person->cnic_back }}">
                                                    <input type="file" name="persons[{{ $i }}][cnic_back]" class="form-control form-control-sm" accept="image/*,.pdf">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">Passport First Page</label>
                                                    @if($person->passport_photo)
                                                        <div class="mb-1"><a href="{{ asset('storage/' . $person->passport_photo) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                                                    @endif
                                                    <input type="hidden" name="persons[{{ $i }}][old_passport_photo]" value="{{ $person->passport_photo }}">
                                                    <input type="file" name="persons[{{ $i }}][passport_photo]" class="form-control form-control-sm" accept="image/*,.pdf">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">Picture of Haji</label>
                                                    @if($person->photo)
                                                        <div class="mb-1"><a href="{{ asset('storage/' . $person->photo) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                                                    @endif
                                                    <input type="hidden" name="persons[{{ $i }}][old_photo]" value="{{ $person->photo }}">
                                                    <input type="file" name="persons[{{ $i }}][photo]" class="form-control form-control-sm" accept="image/*">
                                                </div>
                                                <div class="col-md-4 mt-2">
                                                    <label class="form-label" style="font-size:11px;">Medical Fitness Certificate</label>
                                                    @if($person->medical_certificate)
                                                        <div class="mb-1"><a href="{{ asset('storage/' . $person->medical_certificate) }}" target="_blank" class="badge bg-primary text-white">View</a></div>
                                                    @endif
                                                    <input type="hidden" name="persons[{{ $i }}][old_medical_certificate]" value="{{ $person->medical_certificate }}">
                                                    <input type="file" name="persons[{{ $i }}][medical_certificate]" class="form-control form-control-sm" accept="image/*,.pdf">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Nominee Details --}}
                                        <div class="border-top mt-3 pt-3">
                                            <strong class="text-secondary d-block mb-2" style="font-size:12px;">
                                                <i class="mdi mdi-account-alert me-1"></i> Nominee Details
                                            </strong>
                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">Nominee Name</label>
                                                    <input type="text" name="persons[{{ $i }}][nominee_name]" class="form-control form-control-sm" placeholder="Nominee full name" value="{{ $person->nominee_name }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">Relation with Haji</label>
                                                    <input type="text" name="persons[{{ $i }}][nominee_relation]" class="form-control form-control-sm" placeholder="e.g. Son / Wife / Brother" value="{{ $person->nominee_relation }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">Nominee CNIC</label>
                                                    <input type="text" name="persons[{{ $i }}][nominee_cnic]" class="form-control form-control-sm" placeholder="XXXXX-XXXXXXX-X" value="{{ $person->nominee_cnic }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label" style="font-size:11px;">Nominee Mobile</label>
                                                    <input type="text" name="persons[{{ $i }}][nominee_mobile]" class="form-control form-control-sm" placeholder="+92 300 0000000" value="{{ $person->nominee_mobile }}">
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

                            {{-- SUPPLIER & COSTING / SELLING --}}
                            <div class="row g-3 mb-4 p-3 border rounded bg-light align-items-center">
                                <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2 mb-0">
                                    <h6 class="text-primary mb-0">
                                        <i class="mdi mdi-airplane me-1"></i> Flight Supplier & Rates
                                    </h6>
                                    <span class="badge bg-soft-primary text-primary px-2 py-1 fs-11">Currency: <span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span></span>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Flight Supplier / Vendor</label>
                                    <select name="flight_supplier_id" id="flight_supplier_id" class="form-select">
                                        <option value="">-- Select Flight Supplier --</option>
                                        @foreach ($suppliers as $sup)
                                            <option value="{{ $sup->id }}" {{ old('flight_supplier_id', $booking->flight_supplier_id) == $sup->id ? 'selected' : '' }}>
                                                {{ $sup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label text-primary">ROE</label>
                                    <input type="number" step="0.0001" name="flight_roe" id="flight_roe"
                                        class="form-control flight-roe" placeholder="1.00" value="{{ old('flight_roe', $booking->flight_roe ?? 1) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-danger">Cost Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" step="0.01" name="flight_cost_rate" id="flight_cost_rate"
                                        class="form-control flight-cost-rate" placeholder="0.00" value="{{ old('flight_cost_rate', $booking->flight_cost_rate ?? 0) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-danger">Total Flight Cost (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" step="0.01" name="flight_cost" id="flight_cost"
                                        class="form-control flight-cost-total bg-light" placeholder="0.00" value="{{ old('flight_cost', $booking->flight_cost ?? 0) }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-success">Selling Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" step="0.01" name="flight_selling_rate" id="flight_selling_rate"
                                        class="form-control flight-sell-rate" placeholder="0.00" value="{{ old('flight_selling_rate', $booking->flight_selling_rate ?? 0) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-success">Total Flight Sell (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" step="0.01" name="flight_charges" id="flight_charges"
                                        class="form-control flight-sell-total bg-light" placeholder="0.00" value="{{ old('flight_charges', $booking->flight_charges ?? 0) }}" readonly>
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
                                        <option value="">-- Select --</option>
                                        @foreach (['PIA', 'Air Arabia', 'Emirates', 'Qatar Airways', 'FlyDubai', 'Other'] as $air)
                                            <option
                                                {{ old('departure_airline', $booking->departure_airline) == $air ? 'selected' : '' }}>
                                                {{ $air }}
                                            </option>
                                        @endforeach
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
                                        <option value="">-- Select --</option>
                                        @foreach (['PIA', 'Air Arabia', 'Emirates', 'Qatar Airways', 'FlyDubai', 'Other'] as $air)
                                            <option
                                                {{ old('arrival_airline', $booking->arrival_airline) == $air ? 'selected' : '' }}>
                                                {{ $air }}
                                            </option>
                                        @endforeach
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
                            <div id="hotelsList">
                                @foreach ($booking->hotels as $hotel)
                                    <div class="hotel-block border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-primary mb-0">
                                                <i class="mdi mdi-hotel me-1"></i>
                                                {{ $hotel->location === 'makkah' ? 'Makkah' : ($hotel->location === 'madinah' ? 'Madinah' : 'Other Hotel') }}
                                            </h6>
                                            @if ($loop->index >= 2)
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm remove-hotel">× Remove</button>
                                            @endif
                                        </div>
                                        <input type="hidden" name="hotels[{{ $loop->index }}][location]"
                                            value="{{ $hotel->location }}">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Hotel Name</label>
                                                <select name="hotels[{{ $loop->index }}][hotel_name]" class="form-select hotel-name-select">
                                                    <option value="">Select Hotel</option>
                                                    @foreach($hotels as $h)
                                                        <option value="{{ $h->name }}" {{ old('hotels.'.$loop->index.'.hotel_name', $hotel->hotel_name) == $h->name ? 'selected' : '' }}>{{ $h->name }} ({{ $h->city ?? $h->place }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
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
                                                <label class="form-label text-primary">ROE</label>
                                                <input type="number" step="0.0001" name="hotels[{{ $loop->index }}][roe]"
                                                    class="form-control hotel-roe" value="{{ old('hotels.'.$loop->index.'.roe', $hotel->roe ?? 1) }}" placeholder="1.00">
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
                                                    class="form-control hotel-nights" value="{{ old('hotels.'.$loop->index.'.no_of_nights', $hotel->no_of_nights) }}"
                                                    min="1">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">No. of Rooms</label>
                                                <input type="number" name="hotels[{{ $loop->index }}][no_of_rooms]"
                                                    class="form-control hotel-rooms" value="{{ old('hotels.'.$loop->index.'.no_of_rooms', $hotel->no_of_rooms ?? 1) }}"
                                                    min="1">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Hotel Room #</label>
                                                <input type="text" name="hotels[{{ $loop->index }}][room_number]"
                                                    class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505"
                                                    value="{{ old('hotels.'.$loop->index.'.room_number', $hotel->room_number) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Check In</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][check_in]"
                                                    class="form-control hotel-checkin" value="{{ old('hotels.'.$loop->index.'.check_in', $hotel->check_in) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Check Out</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][check_out]"
                                                    class="form-control hotel-checkout" value="{{ old('hotels.'.$loop->index.'.check_out', $hotel->check_out) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cost Rate / Night (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][cost_per_night]"
                                                    class="form-control hotel-cost-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_per_night', $hotel->cost_per_night ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger">Total Hotel Cost (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][cost_amount]"
                                                    class="form-control hotel-cost-total bg-light" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_amount', $hotel->cost_amount ?? 0) }}" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Sell Rate / Night (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][selling_per_night]"
                                                    class="form-control hotel-sell-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.selling_per_night', $hotel->selling_per_night ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success">Total Hotel Sell (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][selling_amount]"
                                                    class="form-control hotel-sell-total bg-light" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.selling_amount', $hotel->selling_amount ?? 0) }}" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                                                @if($hotel->hotel_voucher)
                                                    <div class="mb-1"><a href="{{ asset('storage/' . $hotel->hotel_voucher) }}" target="_blank" class="badge bg-primary text-white">View Current Voucher</a></div>
                                                @endif
                                                <input type="hidden" name="hotels[{{ $loop->index }}][old_hotel_voucher]" value="{{ $hotel->hotel_voucher }}">
                                                <input type="file" name="hotels[{{ $loop->index }}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addHotel">+ Add
                                Hotel</button>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 6: Transport --}}
                        <div class="tab-pane fade" id="tab-transport">
                            <div id="routesList">
                                @forelse($booking->transports as $i => $t)
                                    <div class="route-block border rounded p-3 mb-2">
                                        @if ($loop->index > 0)
                                            <div class="d-flex justify-content-end mb-1">
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm remove-route">×</button>
                                            </div>
                                        @endif
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Route</label>
                                                <input type="text" name="transports[{{ $i }}][route]"
                                                    class="form-control" value="{{ $t->route }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Transport Type</label>
                                                <select name="transports[{{ $i }}][transport_type]"
                                                    class="form-select">
                                                    @foreach (['private_car' => 'Private Car', 'bus' => 'Bus / Coach', 'train' => 'Train', 'shared_van' => 'Shared Van', 'taxi' => 'Taxi', 'flight' => 'Flight'] as $val => $lbl)
                                                        <option value="{{ $val }}"
                                                            {{ $t->transport_type == $val ? 'selected' : '' }}>
                                                            {{ $lbl }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
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
                                            <div class="col-md-2">
                                                <label class="form-label text-primary">ROE</label>
                                                <input type="number" step="0.0001" name="transports[{{ $i }}][roe]"
                                                    class="form-control transport-roe" placeholder="1.00" value="{{ old('transports.'.$i.'.roe', $t->roe ?? 1) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger">Cost Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][cost_rate]"
                                                    class="form-control transport-cost-rate" placeholder="0.00" value="{{ old('transports.'.$i.'.cost_rate', $t->cost_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger">Total Cost Amount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][cost_amount]"
                                                    class="form-control transport-cost-amount bg-light" placeholder="0.00" value="{{ old('transports.'.$i.'.cost_amount', $t->cost_amount ?? 0) }}" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success">Selling Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][selling_rate]"
                                                    class="form-control transport-sell-rate" placeholder="0.00" value="{{ old('transports.'.$i.'.selling_rate', $t->selling_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success">Total Selling Amount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[{{ $i }}][selling_amount]"
                                                    class="form-control transport-sell-amount bg-light" placeholder="0.00" value="{{ old('transports.'.$i.'.selling_amount', $t->selling_amount ?? 0) }}" readonly>
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
                                                <label class="form-label">Transport Supplier / Vendor</label>
                                                <select name="transports[0][supplier_id]" class="form-select">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $sup)
                                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label text-primary">ROE</label>
                                                <input type="number" step="0.0001" name="transports[0][roe]"
                                                    class="form-control transport-roe" placeholder="1.00" value="1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger">Cost Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[0][cost_rate]"
                                                    class="form-control transport-cost-rate" placeholder="0.00" value="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger">Total Cost Amount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[0][cost_amount]"
                                                    class="form-control transport-cost-amount bg-light" placeholder="0.00" value="0" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success">Selling Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[0][selling_rate]"
                                                    class="form-control transport-sell-rate" placeholder="0.00" value="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success">Total Selling Amount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="transports[0][selling_amount]"
                                                    class="form-control transport-sell-amount bg-light" placeholder="0.00" value="0" readonly>
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
                            <p class="text-muted small mb-3">
                                <i class="mdi mdi-information me-1"></i>
                                Edit visa details for each passenger below.
                            </p>
                            <div id="visasList">
                                @foreach ($booking->visas as $i => $visa)
                                    <div class="visa-card" id="visa_card_{{ $i }}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-warning" style="font-size:13px;">
                                                <i class="mdi mdi-passport me-1"></i> Visa {{ $i + 1 }}: {{ $visa->given_name ?? 'Passenger ' . ($i + 1) }}
                                            </strong>
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
                                                <label class="form-label text-primary" style="font-size:12px;">ROE</label>
                                                <input type="number" step="0.0001" name="visas[{{ $i }}][roe]"
                                                    class="form-control form-control-sm visa-roe" placeholder="1.00"
                                                    value="{{ old('visas.'.$i.'.roe', $visa->roe ?? 1) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" style="font-size:12px;">Visa Attachment</label>
                                                @if($visa->visa_attachment)
                                                    <div class="mb-1"><a href="{{ asset('storage/' . $visa->visa_attachment) }}" target="_blank" class="badge bg-primary text-white">View Current Visa</a></div>
                                                @endif
                                                <input type="hidden" name="visas[{{ $i }}][old_visa_attachment]" value="{{ $visa->visa_attachment }}">
                                                <input type="file" name="visas[{{ $i }}][visa_attachment]" class="form-control form-control-sm" accept="image/*,.pdf">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger" style="font-size:12px;">Cost Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="visas[{{ $i }}][cost_rate]"
                                                    class="form-control form-control-sm visa-cost-rate" placeholder="0.00"
                                                    value="{{ old('visas.'.$i.'.cost_rate', $visa->cost_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-danger" style="font-size:12px;">Cost Amount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="visas[{{ $i }}][cost_amount]"
                                                    class="form-control form-control-sm visa-cost-amount bg-light" placeholder="0.00"
                                                    value="{{ old('visas.'.$i.'.cost_amount', $visa->cost_amount ?? 0) }}" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success" style="font-size:12px;">Selling Rate (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="visas[{{ $i }}][selling_rate]"
                                                    class="form-control form-control-sm visa-sell-rate" placeholder="0.00"
                                                    value="{{ old('visas.'.$i.'.selling_rate', $visa->selling_rate ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success" style="font-size:12px;">Selling Amount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                                <input type="number" step="0.01" name="visas[{{ $i }}][selling_amount]"
                                                    class="form-control form-control-sm visa-sell-amount bg-light" placeholder="0.00"
                                                    value="{{ old('visas.'.$i.'.selling_amount', $visa->selling_amount ?? 0) }}" readonly>
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
                                    <label class="form-label">Package Cost / Person (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="package_cost" id="package_cost"
                                        class="form-control calc"
                                        value="{{ old('package_cost', $booking->package_cost ?: '') }}"
                                        placeholder="Enter package cost" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hotel Charges (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="hotel_charges" id="hotel_charges"
                                        class="form-control calc bg-light"
                                        value="{{ old('hotel_charges', $booking->hotel_charges ?? 0) }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Visa Charges (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="visa_charges" id="visa_charges"
                                        class="form-control calc bg-light"
                                        value="{{ old('visa_charges', $booking->visa_charges ?: '') }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Flight Charges (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="flight_charges_disp" id="flight_charges_disp"
                                        class="form-control calc bg-light"
                                        value="{{ old('flight_charges', $booking->flight_charges ?: '') }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Transport Charges (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="transport_charges" id="transport_charges"
                                        class="form-control calc bg-light"
                                        value="{{ old('transport_charges', $booking->transport_charges ?? 0) }}"
                                        placeholder="0.00" step="0.01" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Qurbani & Other Charges (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="other_charges" id="other_charges"
                                        class="form-control calc"
                                        value="{{ old('other_charges', $booking->other_charges ?: '') }}"
                                        placeholder="Enter other charges" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Discount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="discount" id="discount"
                                        class="form-control calc"
                                        value="{{ old('discount', $booking->discount ?: '') }}"
                                        placeholder="Enter discount" step="0.01">
                                </div>

                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm bg-light">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>Persons</th>
                                                    <th style="{{ $isUmrah ? 'display:none;' : '' }}">Pkg Cost × Pax</th>
                                                    <th>Hotel (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</th>
                                                    <th>Visa (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</th>
                                                    <th>Flight (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</th>
                                                    <th>Transport (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</th>
                                                    <th>Other & Services (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</th>
                                                    <th>Discount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</th>
                                                    <th class="text-success">Total (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</th>
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
                                    <label class="form-label">Total Amount (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
                                    <input type="number" name="total_amount" id="total_amount"
                                        class="form-control bg-light fw-bold text-primary fs-16"
                                        value="{{ old('total_amount', $booking->total_amount ?: '') }}"
                                        placeholder="Auto calculated" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Received (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)</label>
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
                                        Balance Remaining (<span class="curr-symbol">{{ $booking->currency ?? 'PKR' }}</span>)
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
          {{-- jQuery + Summernote --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <script>
        const packagesData     = @json($packages);
        const clientsData      = @json($clients);
        const hotelsData       = @json($hotels);
        const suppliersData    = @json($suppliers);
        const existingVisas    = @json($booking->visas);
        const transactionsPaid = {{ $transactionsPaid ?? 0 }};

        const suppliersOptions = `<option value="">-- Select Supplier --</option>` +
            (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}">${s.name}</option>`).join('') : '');

        function updatePersonFullName(idx) {
            const sur   = document.getElementById(`person_surname_${idx}`)?.value || '';
            const given = document.getElementById(`person_given_name_${idx}`)?.value || '';
            const combined = (sur + ' ' + given).trim();
            const el = document.getElementById(`person_name_${idx}`);
            if (el) el.value = combined;
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
            if (document.getElementById('person_phone_0')) document.getElementById('person_phone_0').value = client.phone || '';

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

            if (pkg.transport_flights && pkg.transport_flights.length > 0) {
                const fl = pkg.transport_flights[0];
                const setV = (n, v) => { const e = document.querySelector('[name="' + n + '"]'); if (e) e.value = v || ''; };
                setV('departure_date', fl.departure_date);
                setV('departure_flight', fl.flight_no);
                setV('departure_time', fl.departure_time);
                setV('departure_airline', fl.airline);
                setV('departure_pnr', fl.pnr_no);
                setV('arrival_date', fl.arrival_date);
                setV('arrival_flight', fl.arrival_flight || fl.flight_no);
                setV('arrival_time', fl.arrival_time);
                setV('arrival_airline', fl.airline);
                setV('arrival_pnr', fl.pnr_no);
            }

            if (pkg.accommodations && pkg.accommodations.length > 0) {
                const hotelsList = document.getElementById('hotelsList');
                if (hotelsList) {
                    hotelsList.innerHTML = '';
                    pkg.accommodations.forEach((acc, idx) => {
                        const loc = acc.place ? acc.place.toLowerCase() : (idx === 0 ? 'makkah' : 'madinah');
                        const hotelName = acc.hotel ? acc.hotel.name : (acc.accommodation_type || '');
                        const roomType = acc.sharing_type || 'double';
                        let checkIn = acc.check_in ? acc.check_in.substring(0, 10) : '';
                        let checkOut = acc.check_out ? acc.check_out.substring(0, 10) : '';
                        let nights = acc.nights || acc.days || '';

                        if (checkIn && checkOut) {
                            const diff = Math.round((new Date(checkOut) - new Date(checkIn)) / 86400000);
                            if (!isNaN(diff) && diff >= 0) nights = diff;
                        } else if (checkIn && nights && !checkOut) {
                            const d = new Date(checkIn); d.setDate(d.getDate() + parseInt(nights));
                            checkOut = d.toISOString().split('T')[0];
                        }
                        if (!nights) nights = 1;

                        const costPerNight = parseFloat(acc.per_night_rate || acc.cost_amount || 0) || 0;
                        const costTotal = (nights * 1 * costPerNight).toFixed(2);
                        const sellPerNight = parseFloat(acc.selling_per_night_rate || acc.selling_amount || 0) || 0;
                        const sellTotal = (nights * 1 * sellPerNight).toFixed(2);

                        let hotelOptions = '<option value="">Select Hotel</option>';
                        hotelsData.forEach(h => {
                            const sel = (h.name === hotelName) ? 'selected' : '';
                            hotelOptions += `<option value="${h.name}" ${sel}>${h.name} (${h.city || h.place || ''})</option>`;
                        });

                        hotelsList.insertAdjacentHTML('beforeend', `
                            <div class="hotel-block border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-primary mb-0"><i class="mdi mdi-hotel me-1"></i> ${acc.place || 'Hotel Option ' + (idx + 1)}</h6>
                                    ${idx >= 2 ? '<button type="button" class="btn btn-outline-danger btn-sm remove-hotel">× Remove</button>' : ''}
                                </div>
                                <input type="hidden" name="hotels[${idx}][location]" value="${loc}">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Hotel Name</label>
                                        <select name="hotels[${idx}][hotel_name]" class="form-select hotel-name-select">${hotelOptions}</select></div>
                                    <div class="col-md-4"><label class="form-label">Hotel Supplier / Vendor</label>
                                        <select name="hotels[${idx}][supplier_id]" class="form-select">${suppliersOptions}</select></div>
                                    <div class="col-md-2"><label class="form-label text-primary">ROE</label>
                                        <input type="number" step="0.0001" name="hotels[${idx}][roe]" class="form-control hotel-roe" value="1" placeholder="1.00"></div>
                                    <div class="col-md-2"><label class="form-label">Room Type</label>
                                        <select name="hotels[${idx}][room_type]" class="form-select">
                                            <option value="single" ${roomType === 'single' ? 'selected' : ''}>Single</option>
                                            <option value="double" ${roomType === 'double' ? 'selected' : ''}>Double</option>
                                            <option value="triple" ${roomType === 'triple' ? 'selected' : ''}>Triple</option>
                                            <option value="quad" ${roomType === 'quad' ? 'selected' : ''}>Quad</option>
                                            <option value="sharing" ${roomType === 'sharing' ? 'selected' : ''}>Sharing</option>
                                            <option value="suite" ${roomType === 'suite' ? 'selected' : ''}>Suite</option>
                                        </select></div>
                                    <div class="col-md-2"><label class="form-label">Nights</label>
                                        <input type="number" name="hotels[${idx}][no_of_nights]" class="form-control hotel-nights" value="${nights}" min="1"></div>
                                    <div class="col-md-2"><label class="form-label">No. of Rooms</label>
                                        <input type="number" name="hotels[${idx}][no_of_rooms]" class="form-control hotel-rooms" value="1" min="1"></div>
                                    <div class="col-md-2"><label class="form-label">Hotel Room #</label>
                                        <input type="text" name="hotels[${idx}][room_number]" class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505"></div>
                                    <div class="col-md-3"><label class="form-label">Check In</label>
                                        <input type="date" name="hotels[${idx}][check_in]" class="form-control hotel-checkin" value="${checkIn}"></div>
                                    <div class="col-md-3"><label class="form-label">Check Out</label>
                                        <input type="date" name="hotels[${idx}][check_out]" class="form-control hotel-checkout" value="${checkOut}"></div>
                                    <div class="col-md-3"><label class="form-label">Cost Rate / Night</label>
                                        <input type="number" step="0.01" name="hotels[${idx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="${costPerNight}"></div>
                                    <div class="col-md-3"><label class="form-label text-danger">Total Hotel Cost</label>
                                        <input type="number" step="0.01" name="hotels[${idx}][cost_amount]" class="form-control hotel-cost-total bg-light" placeholder="0.00" value="${costTotal}" readonly></div>
                                    <div class="col-md-3"><label class="form-label">Sell Rate / Night</label>
                                        <input type="number" step="0.01" name="hotels[${idx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="${sellPerNight}"></div>
                                    <div class="col-md-3"><label class="form-label text-success">Total Hotel Sell</label>
                                        <input type="number" step="0.01" name="hotels[${idx}][selling_amount]" class="form-control hotel-sell-total bg-light" placeholder="0.00" value="${sellTotal}" readonly></div>
                                    <div class="col-md-6"><label class="form-label">Hotel Voucher</label>
                                        <input type="file" name="hotels[${idx}][hotel_voucher]" class="form-control" accept="image/*,.pdf"></div>
                                </div>
                            </div>
                        `);
                    });
                }
            }

            if (pkg.transports && pkg.transports.length > 0) {
                const routesList = document.getElementById('routesList');
                if (routesList) {
                    routesList.innerHTML = '';
                    pkg.transports.forEach((tr, idx) => {
                        routesList.insertAdjacentHTML('beforeend', `
                            <div class="route-block border rounded p-3 mb-2">
                                <div class="row g-3">
                                    <div class="col-md-3"><input type="text" name="transports[${idx}][route]" class="form-control" value="${tr.route || ''}" placeholder="Route"></div>
                                    <div class="col-md-3"><select name="transports[${idx}][transport_type]" class="form-select">
                                        <option value="private_car" ${tr.type === 'private_car' ? 'selected' : ''}>Private Car</option>
                                        <option value="bus" ${tr.type === 'bus' ? 'selected' : ''}>Bus / Coach</option>
                                        <option value="train" ${tr.type === 'train' ? 'selected' : ''}>Train</option>
                                        <option value="shared_van" ${tr.type === 'shared_van' ? 'selected' : ''}>Shared Van</option>
                                        <option value="taxi" ${tr.type === 'taxi' ? 'selected' : ''}>Taxi</option>
                                        <option value="flight" ${tr.type === 'flight' ? 'selected' : ''}>Flight</option>
                                    </select></div>
                                    <div class="col-md-4"><select name="transports[${idx}][supplier_id]" class="form-select">${suppliersOptions}</select></div>
                                    <div class="col-md-2"><input type="number" step="0.0001" name="transports[${idx}][roe]" class="form-control transport-roe" placeholder="1.00" value="1"></div>
                                    <div class="col-md-3"><input type="number" step="0.01" name="transports[${idx}][cost_rate]" class="form-control transport-cost-rate" placeholder="Cost Rate" value="0"></div>
                                    <div class="col-md-3"><input type="number" step="0.01" name="transports[${idx}][cost_amount]" class="form-control transport-cost-amount bg-light" placeholder="0.00" value="0" readonly></div>
                                    <div class="col-md-3"><input type="number" step="0.01" name="transports[${idx}][selling_rate]" class="form-control transport-sell-rate" placeholder="Sell Rate" value="0"></div>
                                    <div class="col-md-3"><input type="number" step="0.01" name="transports[${idx}][selling_amount]" class="form-control transport-sell-amount bg-light" placeholder="0.00" value="0" readonly></div>
                                    <div class="col-md-6"><input type="text" name="transports[${idx}][notes]" class="form-control" value="${tr.vehicle || ''}" placeholder="Optional Notes"></div>
                                    <div class="col-md-6"><input type="file" name="transports[${idx}][transport_ticket]" class="form-control" accept="image/*,.pdf"></div>
                                </div>
                            </div>
                        `);
                    });
                }
            }

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
                return;
            }

            for (let i = currentCount; i < newPax; i++) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="person-card" id="person_card_${i}">
                        <div class="mb-2"><strong class="text-primary" style="font-size:13px;">Passenger ${i + 1}</strong></div>
                        <div class="row g-2">
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Haji ID </label>
                                <input type="text" name="persons[${i}][haji_id]" class="form-control form-control-sm" placeholder="e.g. H-00123"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Surname</label>
                                <input type="text" name="persons[${i}][surname]" id="person_surname_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Given Name</label>
                                <input type="text" name="persons[${i}][given_name]" id="person_given_name_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Full Name</label>
                                <input type="text" name="persons[${i}][full_name]" id="person_name_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Father Name</label>
                                <input type="text" name="persons[${i}][father_name]" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Passport #</label>
                                <input type="text" name="persons[${i}][passport_number]" id="person_passport_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">DOB</label>
                                <input type="date" name="persons[${i}][dob]" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Gender</label>
                                <select name="persons[${i}][gender]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option><option value="male">Male</option><option value="female">Female</option>
                                </select></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Blood Group</label>
                                <select name="persons[${i}][blood_group]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option>
                                    ${['A+','A-','B+','B-','O+','O-','AB+','AB-'].map(b => `<option value="${b}">${b}</option>`).join('')}
                                </select></div>
                            <div class="col-md-4"><label class="form-label" style="font-size:12px;">CNIC</label>
                                <input type="text" name="persons[${i}][cnic]" id="person_cnic_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-4"><label class="form-label" style="font-size:12px;">Phone</label>
                                <input type="text" name="persons[${i}][phone]" id="person_phone_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-4"><label class="form-label" style="font-size:12px;">City</label>
                                <input type="text" name="persons[${i}][city]" class="form-control form-control-sm"></div>
                        </div>
                    </div>
                `);
            }
            syncFlightPersons();
        }

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

        function rebuildVisasList(newPax) {
            const container = document.getElementById('visasList');
            if (!container) return;
            const currentCount = container.querySelectorAll('.visa-card').length;

            if (newPax < currentCount) {
                for (let i = currentCount - 1; i >= newPax; i--) {
                    document.getElementById('visa_card_' + i)?.remove();
                }
                return;
            }

            const curr = document.getElementById('booking_currency')?.value || 'PKR';

            for (let i = currentCount; i < newPax; i++) {
                const v = existingVisas[i] || {};
                const pName = document.getElementById(`person_name_${i}`)?.value || (v.given_name || '');
                const pPass = document.getElementById(`person_passport_${i}`)?.value || (v.passport_number || '');
                const pDob = document.getElementById(`person_dob_${i}`)?.value || (v.date_of_birth || '');

                let supplierOptionsHtml = `<option value="">-- Select Supplier --</option>` +
                    (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}" ${s.id == v.supplier_id ? 'selected' : ''}>${s.name}</option>`).join('') : '');

                container.insertAdjacentHTML('beforeend', `
                    <div class="visa-card" id="visa_card_${i}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-warning" style="font-size:13px;">
                                <i class="mdi mdi-passport me-1"></i> Visa ${i + 1}: ${pName || 'Passenger ' + (i + 1)}
                            </strong>
                            <span class="badge bg-soft-warning text-dark fs-11">Currency: <span class="curr-symbol">${curr}</span></span>
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
                                    ${supplierOptionsHtml}
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
                            <div class="col-md-2"><label class="form-label text-primary" style="font-size:12px;">ROE</label>
                                <input type="number" step="0.0001" name="visas[${i}][roe]" class="form-control form-control-sm visa-roe" placeholder="1.00" value="${v.roe || 1}"></div>
                            <div class="col-md-4"><label class="form-label" style="font-size:12px;">Visa Attachment</label>
                                <input type="file" name="visas[${i}][visa_attachment]" class="form-control form-control-sm" accept="image/*,.pdf"></div>
                            <div class="col-md-3"><label class="form-label text-danger" style="font-size:12px;">Cost Rate (<span class="curr-symbol">${curr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][cost_rate]" class="form-control form-control-sm visa-cost-rate" placeholder="0.00" value="${v.cost_rate || 0}"></div>
                            <div class="col-md-3"><label class="form-label text-danger" style="font-size:12px;">Cost Amount (<span class="curr-symbol">${curr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][cost_amount]" class="form-control form-control-sm visa-cost-amount bg-light" placeholder="0.00" value="${v.cost_amount || 0}" readonly></div>
                            <div class="col-md-3"><label class="form-label text-success" style="font-size:12px;">Selling Rate (<span class="curr-symbol">${curr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][selling_rate]" class="form-control form-control-sm visa-sell-rate" placeholder="0.00" value="${v.selling_rate || 0}"></div>
                            <div class="col-md-3"><label class="form-label text-success" style="font-size:12px;">Selling Amount (<span class="curr-symbol">${curr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][selling_amount]" class="form-control form-control-sm visa-sell-amount bg-light" placeholder="0.00" value="${v.selling_amount || 0}" readonly></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Status</label>
                                <select name="visas[${i}][status]" class="form-select form-select-sm">
                                    <option value="pending" ${(v.status || 'pending') === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="submitted" ${v.status === 'submitted' ? 'selected' : ''}>Submitted</option>
                                    <option value="approved" ${v.status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="rejected" ${v.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                </select></div>
                        </div>
                    </div>
                `);
            }
        }

        document.getElementById('no_of_pax').addEventListener('input', function() {
            const val = parseInt(this.value);
            if (val >= 1) {
                rebuildPersonsList(val);
                rebuildVisasList(val);
            }
            calcTotal();
        });

        let hotelIdx = {{ $booking->hotels->count() ?: 2 }};

        document.getElementById('addHotel').addEventListener('click', function() {
            const curr = document.getElementById('booking_currency')?.value || 'PKR';
            document.getElementById('hotelsList').insertAdjacentHTML('beforeend', `
                <div class="hotel-block border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-primary mb-0"><i class="mdi mdi-hotel me-1"></i> Additional Hotel</h6>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-hotel">× Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Location</label>
                            <select name="hotels[${hotelIdx}][location]" class="form-select">
                                <option value="makkah">Makkah</option><option value="madinah">Madinah</option><option value="other">Other</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Hotel Name</label>
                            <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select">
                                <option value="">Select Hotel</option>
                                ${hotelsData.map(h => `<option value="${h.name}">${h.name} (${h.city || h.place || ''})</option>`).join('')}
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Hotel Supplier / Vendor</label>
                            <select name="hotels[${hotelIdx}][supplier_id]" class="form-select">
                                ${suppliersOptions}
                            </select></div>
                        <div class="col-md-2"><label class="form-label text-primary">ROE</label>
                            <input type="number" step="0.0001" name="hotels[${hotelIdx}][roe]" class="form-control hotel-roe" value="1" placeholder="1.00"></div>
                        <div class="col-md-2"><label class="form-label">Room Type</label>
                            <select name="hotels[${hotelIdx}][room_type]" class="form-select">
                                <option value="single">Single</option><option value="double" selected>Double</option><option value="triple">Triple</option>
                                <option value="quad">Quad</option><option value="sharing">Sharing</option><option value="suite">Suite</option>
                            </select></div>
                        <div class="col-md-2"><label class="form-label">Nights</label>
                            <input type="number" name="hotels[${hotelIdx}][no_of_nights]" class="form-control hotel-nights" value="1" min="1"></div>
                        <div class="col-md-2"><label class="form-label">No. of Rooms</label>
                            <input type="number" name="hotels[${hotelIdx}][no_of_rooms]" class="form-control hotel-rooms" value="1" min="1"></div>
                        <div class="col-md-2"><label class="form-label">Hotel Room #</label>
                            <input type="text" name="hotels[${hotelIdx}][room_number]" class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505"></div>
                        <div class="col-md-3"><label class="form-label">Check In</label>
                            <input type="date" name="hotels[${hotelIdx}][check_in]" class="form-control hotel-checkin"></div>
                        <div class="col-md-3"><label class="form-label">Check Out</label>
                            <input type="date" name="hotels[${hotelIdx}][check_out]" class="form-control hotel-checkout"></div>
                        <div class="col-md-3"><label class="form-label">Cost Rate / Night (<span class="curr-symbol">${curr}</span>)</label>
                            <input type="number" step="0.01" name="hotels[${hotelIdx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-danger">Total Hotel Cost (<span class="curr-symbol">${curr}</span>)</label>
                            <input type="number" step="0.01" name="hotels[${hotelIdx}][cost_amount]" class="form-control hotel-cost-total bg-light" placeholder="0.00" value="0" readonly></div>
                        <div class="col-md-3"><label class="form-label">Sell Rate / Night (<span class="curr-symbol">${curr}</span>)</label>
                            <input type="number" step="0.01" name="hotels[${hotelIdx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-success">Total Hotel Sell (<span class="curr-symbol">${curr}</span>)</label>
                            <input type="number" step="0.01" name="hotels[${hotelIdx}][selling_amount]" class="form-control hotel-sell-total bg-light" placeholder="0.00" value="0" readonly></div>
                        <div class="col-md-6"><label class="form-label">Hotel Voucher</label>
                            <input type="file" name="hotels[${hotelIdx}][hotel_voucher]" class="form-control" accept="image/*,.pdf"></div>
                    </div>
                </div>`);
            hotelIdx++;
        });

        document.getElementById('hotelsList').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-hotel')) {
                e.target.closest('.hotel-block').remove();
                let totalHotelSelling = 0;
                document.querySelectorAll('.hotel-sell-total').forEach(el => {
                    totalHotelSelling += parseFloat(el.value) || 0;
                });
                if (document.getElementById('hotel_charges')) {
                    document.getElementById('hotel_charges').value = totalHotelSelling.toFixed(2);
                }
                calcTotal();
            }
        });

        let routeIdx = {{ $booking->transports->count() ?: 1 }};

        document.getElementById('addRoute').addEventListener('click', function() {
            const curr = document.getElementById('booking_currency')?.value || 'PKR';
            document.getElementById('routesList').insertAdjacentHTML('beforeend', `
                <div class="route-block border rounded p-3 mb-2">
                    <div class="d-flex justify-content-end mb-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-route">×</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Route</label><input type="text" name="transports[${routeIdx}][route]" class="form-control" placeholder="Route"></div>
                        <div class="col-md-3"><label class="form-label">Transport Type</label><select name="transports[${routeIdx}][transport_type]" class="form-select">
                            <option value="private_car">Private Car</option><option value="bus">Bus / Coach</option>
                            <option value="train">Train</option><option value="shared_van">Shared Van</option>
                            <option value="taxi">Taxi</option><option value="flight">Flight</option>
                        </select></div>
                        <div class="col-md-4"><label class="form-label">Transport Supplier / Vendor</label><select name="transports[${routeIdx}][supplier_id]" class="form-select">
                            ${suppliersOptions}
                        </select></div>
                        <div class="col-md-2"><label class="form-label text-primary">ROE</label><input type="number" step="0.0001" name="transports[${routeIdx}][roe]" class="form-control transport-roe" placeholder="1.00" value="1"></div>
                        <div class="col-md-3"><label class="form-label text-danger">Cost Rate (<span class="curr-symbol">${curr}</span>)</label><input type="number" step="0.01" name="transports[${routeIdx}][cost_rate]" class="form-control transport-cost-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-danger">Total Cost Amount (<span class="curr-symbol">${curr}</span>)</label><input type="number" step="0.01" name="transports[${routeIdx}][cost_amount]" class="form-control transport-cost-amount bg-light" placeholder="0.00" value="0" readonly></div>
                        <div class="col-md-3"><label class="form-label text-success">Selling Rate (<span class="curr-symbol">${curr}</span>)</label><input type="number" step="0.01" name="transports[${routeIdx}][selling_rate]" class="form-control transport-sell-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-success">Total Selling Amount (<span class="curr-symbol">${curr}</span>)</label><input type="number" step="0.01" name="transports[${routeIdx}][selling_amount]" class="form-control transport-sell-amount bg-light" placeholder="0.00" value="0" readonly></div>
                        <div class="col-md-6"><label class="form-label">Notes</label><input type="text" name="transports[${routeIdx}][notes]" class="form-control" placeholder="Optional"></div>
                        <div class="col-md-6"><label class="form-label">Transport Ticket / Receipt</label><input type="file" name="transports[${routeIdx}][transport_ticket]" class="form-control" accept="image/*,.pdf"></div>
                    </div>
                </div>`);
            routeIdx++;
        });

        document.getElementById('routesList').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-route')) {
                e.target.closest('.route-block').remove();
                calculateAllTransports();
            }
        });

        // Calculate hotel row costs and sells
        function calculateHotelRow(row) {
            if (!row) return;
            const nights = parseFloat(row.querySelector('.hotel-nights')?.value) || 0;
            const rooms = parseFloat(row.querySelector('.hotel-rooms')?.value) || 0;
            const roe = parseFloat(row.querySelector('.hotel-roe')?.value) || 1;
            const costRate = parseFloat(row.querySelector('.hotel-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.hotel-sell-rate')?.value) || 0;

            const costTotalInput = row.querySelector('.hotel-cost-total');
            const sellTotalInput = row.querySelector('.hotel-sell-total');

            if (costTotalInput) costTotalInput.value = (nights * rooms * costRate * roe).toFixed(2);
            if (sellTotalInput) sellTotalInput.value = (nights * rooms * sellRate * roe).toFixed(2);

            let totalHotelSelling = 0;
            document.querySelectorAll('.hotel-sell-total').forEach(el => {
                totalHotelSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('hotel_charges')) {
                document.getElementById('hotel_charges').value = totalHotelSelling.toFixed(2);
            }
            calcTotal();
        }

        // Calculate visa row costs and sells
        function calculateVisaRow(row) {
            if (!row) return;
            const roe = parseFloat(row.querySelector('.visa-roe')?.value) || 1;
            const costRate = parseFloat(row.querySelector('.visa-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.visa-sell-rate')?.value) || 0;

            const costAmtInput = row.querySelector('.visa-cost-amount');
            const sellAmtInput = row.querySelector('.visa-sell-amount');

            if (costAmtInput) costAmtInput.value = (costRate * roe).toFixed(2);
            if (sellAmtInput) sellAmtInput.value = (sellRate * roe).toFixed(2);

            let totalVisaSelling = 0;
            document.querySelectorAll('.visa-sell-amount').forEach(el => {
                totalVisaSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('visa_charges')) {
                document.getElementById('visa_charges').value = totalVisaSelling.toFixed(2);
            }
            calcTotal();
        }

        // Calculate transport row costs and sells
        function calculateTransportRow(row) {
            if (!row) return;
            const roe = parseFloat(row.querySelector('.transport-roe')?.value) || 1;
            const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;

            const costAmtInput = row.querySelector('.transport-cost-amount');
            const sellAmtInput = row.querySelector('.transport-sell-amount');

            if (costAmtInput) costAmtInput.value = (costRate * roe).toFixed(2);
            if (sellAmtInput) sellAmtInput.value = (sellRate * roe).toFixed(2);

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

        // Flight Cost & Selling Calculation
        function calculateFlight() {
            const roe = parseFloat(document.getElementById('flight_roe')?.value) || 1;
            const costRate = parseFloat(document.getElementById('flight_cost_rate')?.value) || 0;
            const sellRate = parseFloat(document.getElementById('flight_selling_rate')?.value) || 0;

            const totalCost = costRate * roe;
            const totalSell = sellRate * roe;

            if (document.getElementById('flight_cost')) {
                document.getElementById('flight_cost').value = totalCost.toFixed(2);
            }
            if (document.getElementById('flight_charges')) {
                document.getElementById('flight_charges').value = totalSell.toFixed(2);
            }
            if (document.getElementById('flight_charges_disp')) {
                document.getElementById('flight_charges_disp').value = totalSell.toFixed(2);
            }

            calcTotal();
        }

        document.getElementById('flight_roe')?.addEventListener('input', calculateFlight);
        document.getElementById('flight_cost_rate')?.addEventListener('input', calculateFlight);
        document.getElementById('flight_selling_rate')?.addEventListener('input', calculateFlight);

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
        });

        // Hotel Date Sequential Chaining
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

        // Handle hotel date calculation & night count
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

        document.addEventListener('input', function(e) {
            if (e.target.matches('input[name*="[check_in]"], input[name*="[check_out]"], input[name*="[no_of_nights]"]')) {
                handleHotelDateCalc(e.target);
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[name*="[check_in]"], input[name*="[check_out]"], input[name*="[no_of_nights]"]')) {
                handleHotelDateCalc(e.target);
            }
        });

        function updateCurrencyUI(curr) {
            curr = curr || document.getElementById('booking_currency')?.value || 'PKR';
            
            const topSel = document.getElementById('top_currency_select');
            const formSel = document.getElementById('booking_currency');
            if (topSel && topSel.value !== curr) topSel.value = curr;
            if (formSel && formSel.value !== curr) formSel.value = curr;

            document.querySelectorAll('.curr-symbol').forEach(el => {
                el.textContent = curr;
            });

            // Ensure ROE fields are fully editable and default to 1 on SAR switch
            document.querySelectorAll('.hotel-roe, .visa-roe, .transport-roe, #flight_roe, .flight-roe').forEach(input => {
                input.removeAttribute('readonly');
                input.classList.remove('bg-light');
                if (curr === 'SAR') {
                    if (!input.value || input.value === '75' || input.value === '75.00') {
                        input.value = '1';
                    }
                }
            });

            // Re-calculate all items
            document.querySelectorAll('.hotel-block').forEach(row => calculateHotelRow(row));
            document.querySelectorAll('.visa-card').forEach(row => calculateVisaRow(row));
            document.querySelectorAll('.route-block').forEach(row => calculateTransportRow(row));
            calculateFlight();
            calcTotal();
        }

        document.getElementById('booking_currency')?.addEventListener('change', function() {
            updateCurrencyUI(this.value);
        });

        document.getElementById('top_currency_select')?.addEventListener('change', function() {
            updateCurrencyUI(this.value);
        });

        function calcTotal() {
            const curr = document.getElementById('booking_currency')?.value || 'PKR';
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const pkg = parseFloat(document.getElementById('package_cost')?.value) || 0;
            
            let hotel = 0;
            document.querySelectorAll('.hotel-sell-total').forEach(el => { hotel += parseFloat(el.value) || 0; });
            if (document.getElementById('hotel_charges')) document.getElementById('hotel_charges').value = hotel.toFixed(2);

            let visa = 0;
            document.querySelectorAll('.visa-sell-amount').forEach(el => { visa += parseFloat(el.value) || 0; });
            if (document.getElementById('visa_charges')) document.getElementById('visa_charges').value = visa.toFixed(2);

            const flight = parseFloat(document.getElementById('flight_charges')?.value) || 0;
            if (document.getElementById('flight_charges_disp')) document.getElementById('flight_charges_disp').value = flight.toFixed(2);

            let transport = 0;
            document.querySelectorAll('.transport-sell-amount').forEach(el => { transport += parseFloat(el.value) || 0; });
            if (document.getElementById('transport_charges')) document.getElementById('transport_charges').value = transport.toFixed(2);

            const other = parseFloat(document.getElementById('other_charges')?.value) || 0;
            const discount = parseFloat(document.getElementById('discount')?.value) || 0;
            const additional = parseFloat(document.getElementById('additional_services_amount')?.value) || 0;
            const received = parseFloat(document.getElementById('total_received')?.value) || 0;

            const pkgTotal = pkg * pax;
            const total = pkgTotal + hotel + visa + flight + transport + other + additional - discount;
            const balance = total - received - transactionsPaid;

            const setV = (id, v) => { const e = document.getElementById(id); if (e) e.value = v; };
            const setT = (id, v) => { const e = document.getElementById(id); if (e) e.textContent = v; };

            setV('total_amount', total.toFixed(2));
            setV('balance', balance.toFixed(2));

            // Summary Bar
            setT('bar_pax', pax);
            setT('bar_adult', `${curr} ${pkgTotal.toFixed(2)}`);
            setT('bar_hotel', `${curr} ${hotel.toFixed(2)}`);
            setT('bar_visa', `${curr} ${visa.toFixed(2)}`);
            setT('bar_flight', `${curr} ${flight.toFixed(2)}`);
            setT('bar_transport', `${curr} ${transport.toFixed(2)}`);
            setT('bar_total', `${curr} ${total.toFixed(2)}`);
            setT('bar_balance', `${curr} ${balance.toFixed(2)}`);

            // Costing Table Breakdown
            setT('sum_pax', pax);
            setT('sum_pkg', `${curr} ${pkgTotal.toFixed(2)}`);
            setT('sum_hotel', `${curr} ${hotel.toFixed(2)}`);
            setT('sum_visa', `${curr} ${visa.toFixed(2)}`);
            setT('sum_flight', `${curr} ${flight.toFixed(2)}`);
            setT('sum_transport', `${curr} ${transport.toFixed(2)}`);
            setT('sum_other', `${curr} ${(other + additional).toFixed(2)}`);
            setT('sum_discount', `${curr} ${discount.toFixed(2)}`);
            setT('sum_total', `${curr} ${total.toFixed(2)}`);
        }

        document.querySelectorAll('.calc').forEach(el => el.addEventListener('input', calcTotal));
        document.getElementById('no_of_pax')?.addEventListener('input', calcTotal);

        document.addEventListener('DOMContentLoaded', function() {
            syncFlightPersons();
            syncBookingHotelDates();
            updateCurrencyUI();
            calcTotal();
        });

        // ===== SUMMERNOTE INIT — Bulletproof =====
        (function () {
            var tries = 0;
            function boot() {
                if (typeof window.jQuery === 'undefined' || !window.jQuery.fn.summernote) {
                    if (tries++ < 200) return setTimeout(boot, 100);
                    console.error('❌ Summernote/jQuery not loaded');
                    return;
                }
                var $ = window.jQuery;

                function initSN() {
                    var el = document.getElementById('additional_services_detail');
                    if (!el) return;
                    if (el.nextElementSibling && el.nextElementSibling.classList.contains('note-editor')) return;
                    try {
                        $(el).summernote({
                            height: 200,
                            placeholder: 'Enter additional service details here...',
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['insert', ['link', 'picture']],
                                ['view', ['fullscreen', 'codeview']]
                            ]
                        });
                        console.log('✅ Summernote initialized');
                    } catch (e) {
                        console.error('Summernote init error:', e);
                    }
                }

                $(document).ready(function () {
                    $(document).on('show.bs.tab shown.bs.tab', 'a[href="#tab-additional-services"]', function () {
                        setTimeout(initSN, 80);
                    });
                    $(document).on('click', 'a[href="#tab-additional-services"]', function () {
                        setTimeout(initSN, 200);
                    });
                    var pane = document.getElementById('tab-additional-services');
                    if (pane && (pane.classList.contains('active') || pane.classList.contains('show'))) {
                        setTimeout(initSN, 200);
                    }
                });
            }
            boot();
        })();

        // Live Room Capacity Real-Time Checker
        function checkHotelRoomCapacity(input) {
            const row = input.closest('.hotel-row') || input.closest('.row');
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
            const locSelect = row.querySelector('select[name*="[location]"]');
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
    </script>
 
@endsection

 
    