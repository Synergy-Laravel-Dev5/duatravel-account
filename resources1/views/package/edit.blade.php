@extends('layout.master')

@section('title', 'Edit Package')

@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Package / <strong>Edit</strong></h4>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="m-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('package.update', $package->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <ul class="nav nav-pills mb-3">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill"
                                data-bs-target="#tab-package" type="button">PACKAGE</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                                data-bs-target="#tab-accommodation" type="button">ACCOMMODATION</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-transport"
                                type="button">TRANSPORT</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-training"
                                type="button">TRAINING / GIFTS</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-terms"
                                type="button">TERMS & CONDITION</button></li>
                        {{-- <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-itinerary"
                                type="button">ITINERARY</button></li> --}}
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-maktab"
                                type="button">MINA ADDRESS</button></li>
                    </ul>

                    <div class="tab-content card">

                        {{-- ================= PACKAGE ================= --}}
                        <div class="tab-pane fade show active card" id="tab-package">
                            <div class="card-body">
                                <h5 class="mb-3">Package Details</h5>
                                <div class="row g-3">
                                    {{-- <div class="col-md-3">
                                        <label class="form-label">Company</label>
                                        <select name="company_id" class="form-select">
                                            <option value="">-- Select Company --</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}"
                                                    {{ old('company_id', $package->company_id) == $company->id ? 'selected' : '' }}>
                                                    {{ $company->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div> --}}
                                    <div class="col-md-3">
                                        <label class="form-label">Package #</label>
                                        <input type="text" name="package_number" class="form-control"
                                            value="{{ old('package_number', $package->package_number) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Category</label>
                                        <input type="text" name="category" class="form-control"
                                            value="{{ old('category', $package->category) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Zone</label>
                                        <input type="text" name="zone" class="form-control"
                                            value="{{ old('zone', $package->zone) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Package Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $package->name) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Package Code</label>
                                        <input type="text" name="code" class="form-control"
                                            value="{{ old('code', $package->code) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Days</label>
                                        <input type="number" name="days" class="form-control"
                                            value="{{ old('days', $package->days) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Year</label>
                                        <input type="text" name="year" class="form-control"
                                            value="{{ old('year', $package->year) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Maktab</label>
                                        <input type="text" name="maktab" class="form-control"
                                            value="{{ old('maktab', $package->maktab) }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Maktab Number</label>
                                        <input type="text" name="maktab_number" class="form-control"
                                            value="{{ old('maktab_number', $package->maktab_number) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label d-block">Medina Arrival</label>
                                        @foreach (['before_hajj' => 'Before Hajj', 'after_hajj' => 'After Hajj'] as $val => $label)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="medina_arrival"
                                                    value="{{ $val }}"
                                                    {{ old('medina_arrival', $package->medina_arrival) == $val ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label d-block">Hajj Duration</label>
                                        @foreach (['short' => 'Short', 'long' => 'Long'] as $val => $label)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="hajj_duration"
                                                    value="{{ $val }}"
                                                    {{ old('hajj_duration', $package->hajj_duration) == $val ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">

                                    <div class="col-md-3">
                                        <label class="form-label">Package Type</label>
                                        <select name="package_type" class="form-select">
                                            <option value="">Select</option>
                                            @foreach(\App\Enums\PackageType::options() as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ old('package_type', $package->package_type) == $val ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Package Amount</label>
                                        <input type="text" name="package_amount" class="form-control amount-format"
                                            value="{{ old('package_amount', number_format($package->package_amount)) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Ticket Amount (PKR)</label>
                                        <input type="text" name="ticket_amount" class="form-control amount-format"
                                            value="{{ old('ticket_amount', number_format($package->ticket_amount)) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Extra Facility Amount</label>
                                        <input type="text" name="extra_facilities_amount" class="form-control"
                                            value="{{ old('extra_facilities_amount', $package->extra_facilities_amount) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Other Extra Facility</label>
                                        <input type="text" name="other_extra_facilities" class="form-control"
                                            value="{{ old('other_extra_facilities', $package->other_extra_facilities) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Place of Departure</label>
                                        <input type="text" name="place_of_departure" class="form-control"
                                            value="{{ old('place_of_departure', $package->place_of_departure) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Flight Type</label>
                                        <select name="flight_type" class="form-select">
                                            <option value="">Select Flight Type</option>
                                            @foreach(\App\Enums\FlightType::options() as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ old('flight_type', $package->flight_type) == $val ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Flight Class</label>
                                        <select name="flight_class" class="form-select">
                                            <option value="">Select Flight Class</option>
                                            @foreach(\App\Enums\FlightClass::options() as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ old('flight_class', $package->flight_class) == $val ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label d-block">Food Included</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="food_included"
                                                value="yes"
                                                {{ old('food_included', $package->food_included) == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="food_included"
                                                value="no"
                                                {{ old('food_included', $package->food_included) == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label d-block">Extra Facilities</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="extra_facilities"
                                                value="yes"
                                                {{ old('extra_facilities', $package->extra_facilities) == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="extra_facilities"
                                                value="no"
                                                {{ old('extra_facilities', $package->extra_facilities) == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label d-block">Qurbani Included</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="qurbani_included"
                                                value="yes"
                                                {{ old('qurbani_included', $package->qurbani_included) == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="qurbani_included"
                                                value="no"
                                                {{ old('qurbani_included', $package->qurbani_included) == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label d-block">Ticket Included</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="ticket_included"
                                                value="yes"
                                                {{ old('ticket_included', $package->ticket_included) == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="ticket_included"
                                                value="no"
                                                {{ old('ticket_included', $package->ticket_included) == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                </div>

                                <hr class="my-4">
                                <h5 class="mb-3">Package Category</h5>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Room Type</label>
                                        <input type="text" name="room_type" class="form-control"
                                            value="{{ old('room_type', $package->room_type) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Azizia Room Type</label>
                                        <input type="text" name="azizia_room_type" class="form-control"
                                            value="{{ old('azizia_room_type', $package->azizia_room_type) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Makkah Type</label>
                                        <select name="makkah_type" class="form-select">
                                            <option value="">-- Select --</option>
                                            @foreach ([1, 2, 3, 4, 5] as $val)
                                                <option value="{{ $val }}"
                                                    {{ old('makkah_type', $package->makkah_type) == $val ? 'selected' : '' }}>
                                                    {{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Medinah Type</label>
                                        <select name="medinah_type" class="form-select">
                                            <option value="">-- Select --</option>
                                            @foreach ([1, 2, 3, 4, 5] as $val)
                                                <option value="{{ $val }}"
                                                    {{ old('medinah_type', $package->medinah_type) == $val ? 'selected' : '' }}>
                                                    {{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Azizia Type</label>
                                        <select name="azizia_type" class="form-select">
                                            <option value="">-- Select --</option>
                                            @foreach ([1, 2, 3] as $val)
                                                <option value="{{ $val }}"
                                                    {{ old('azizia_type', $package->azizia_type) == $val ? 'selected' : '' }}>
                                                    {{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Mina Type</label>
                                        <input type="text" name="mina_type" class="form-control"
                                            value="{{ old('mina_type', $package->mina_type) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= ACCOMMODATION ================= --}}
                        <div class="tab-pane fade card" id="tab-accommodation">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="m-0">Accommodation Details</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        id="addAccommodationBtn">Add <i class="mdi mdi-plus"></i></button>
                                </div>

                                <div id="accommodationRows">
                                    @forelse ($package->accommodations as $i => $acc)
                                        <div class="accommodation-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-accommodation-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3"><label class="form-label">Place</label>
                                                    <select name="accommodations[{{ $i }}][place]"
                                                        class="form-select">
                                                        <option value="">-- Select Place --</option>
                                                        @foreach (\App\Enums\Place::options() as $val => $label)
                                                            <option value="{{ $val }}"
                                                                {{ $acc->place == $val ? 'selected' : '' }}>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Accommodation
                                                        Type</label>
                                                    <select name="accommodations[{{ $i }}][accommodation_type]"
                                                        class="form-select">
                                                        <option value="">-- Select Type --</option>
                                                        @foreach (\App\Enums\AccommodationType::options() as $val => $label)
                                                            <option value="{{ $val }}"
                                                                {{ $acc->accommodation_type == $val ? 'selected' : '' }}>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Saudi Star
                                                        Rating</label>
                                                    <select name="accommodations[{{ $i }}][saudi_star_rating]"
                                                        class="form-select">
                                                        <option value="">-- Select Rating --</option>
                                                        @foreach (\App\Enums\SaudiStarRating::options() as $val => $label)
                                                            <option value="{{ $val }}"
                                                                {{ $acc->saudi_star_rating == $val ? 'selected' : '' }}>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @foreach ($package->accommodations as $index => $accommodation)
                                                    <div class="col-md-3">
                                                        <label class="form-label">Hotel</label>
                                                        <select name="accommodations[{{ $index }}][hotel_id]"
                                                            class="form-select">
                                                            <option value="">-- Select Hotel --</option>
                                                            @foreach ($hotels as $hotel)
                                                                <option value="{{ $hotel->id }}"
                                                                    {{ $accommodation->hotel_id == $hotel->id ? 'selected' : '' }}>
                                                                    {{ $hotel->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endforeach

                                                <div class="col-md-3"><label class="form-label">Distance
                                                        (meter)
                                                    </label><input type="number"
                                                        name="accommodations[{{ $i }}][distance]"
                                                        class="form-control" value="{{ $acc->distance }}"></div>
                                                <div class="col-md-3"><label class="form-label">Check In</label><input
                                                        type="date"
                                                        name="accommodations[{{ $i }}][check_in]"
                                                        class="form-control"
                                                        value="{{ optional($acc->check_in)->format('Y-m-d') }}"></div>
                                                <div class="col-md-3"><label class="form-label">Check Out</label><input
                                                        type="date"
                                                        name="accommodations[{{ $i }}][check_out]"
                                                        class="form-control"
                                                        value="{{ optional($acc->check_out)->format('Y-m-d') }}"></div>
                                                <div class="col-md-3"><label class="form-label">Azizia Date</label><input
                                                        type="date"
                                                        name="accommodations[{{ $i }}][azizia_date]"
                                                        class="form-control"
                                                        value="{{ optional($acc->azizia_date)->format('Y-m-d') }}"></div>
                                                <div class="col-md-3"><label class="form-label">Food Package</label>
                                                    <select name="accommodations[{{ $i }}][food_package]"
                                                        class="form-select">
                                                        <option value="">-- Select Package --</option>
                                                        @foreach (\App\Enums\FoodPackage::options() as $val => $label)
                                                            <option value="{{ $val }}"
                                                                {{ $acc->food_package == $val ? 'selected' : '' }}>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4"><label class="form-label">Actual Check In
                                                        Time</label><input type="datetime-local"
                                                        name="accommodations[{{ $i }}][actual_check_in_time]"
                                                        class="form-control"
                                                        value="{{ optional($acc->actual_check_in_time)->format('Y-m-d\TH:i') }}">
                                                </div>
                                                <div class="col-md-4"><label class="form-label">Actual Check Out
                                                        Time</label><input type="datetime-local"
                                                        name="accommodations[{{ $i }}][actual_check_out_time]"
                                                        class="form-control"
                                                        value="{{ optional($acc->actual_check_out_time)->format('Y-m-d\TH:i') }}">
                                                </div>

                                                <div class="col-md-3"><label class="form-label">Days</label><input
                                                        type="number" name="accommodations[{{ $i }}][days]"
                                                        class="form-control" value="{{ $acc->days }}"></div>
                                                <div class="col-md-3"><label class="form-label">Nights</label><input
                                                        type="number" name="accommodations[{{ $i }}][nights]"
                                                        class="form-control" value="{{ $acc->nights }}"></div>

                                                <div class="col-md-3"><label class="form-label">Makkah Ziarat</label>
                                                    <select name="accommodations[{{ $i }}][makkah_ziarat]"
                                                        class="form-select">
                                                        <option value="">-- Select --</option>
                                                        <option value="yes"
                                                            {{ $acc->makkah_ziarat == 'yes' ? 'selected' : '' }}>Yes
                                                        </option>
                                                        <option value="no"
                                                            {{ $acc->makkah_ziarat == 'no' ? 'selected' : '' }}>No
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Madinah Ziarat</label>
                                                    <select name="accommodations[{{ $i }}][madinah_ziarat]"
                                                        class="form-select">
                                                        <option value="">-- Select --</option>
                                                        <option value="yes"
                                                            {{ $acc->madinah_ziarat == 'yes' ? 'selected' : '' }}>Yes
                                                        </option>
                                                        <option value="no"
                                                            {{ $acc->madinah_ziarat == 'no' ? 'selected' : '' }}>No
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4"><label class="form-label">Distribution</label><input
                                                        type="text"
                                                        name="accommodations[{{ $i }}][distribution]"
                                                        class="form-control" value="{{ $acc->distribution }}"></div>
                                                <div class="col-md-4"><label class="form-label">Camp</label><input
                                                        type="text" name="accommodations[{{ $i }}][camp]"
                                                        class="form-control" value="{{ $acc->camp }}"></div>
                                                <div class="col-md-4"><label class="form-label">Arafat</label><input
                                                        type="text" name="accommodations[{{ $i }}][arafat]"
                                                        class="form-control" value="{{ $acc->arafat }}"></div>

                                                <div class="col-md-4"><label class="form-label"> Azizia
                                                        Shuttle</label><input type="text"
                                                        name="accommodations[{{ $i }}][shuttle]"
                                                        class="form-control" value="{{ $acc->shuttle }}"></div>
                                                <div class="col-md-4"><label class="form-label">Bedding (Sofa
                                                        Mattress)</label><input type="text"
                                                        name="accommodations[{{ $i }}][bedding]"
                                                        class="form-control" value="{{ $acc->bedding }}"></div>
                                                <div class="col-md-4"><label class="form-label">Sharing (Room / Tent /
                                                        Camp)</label><input type="text"
                                                        name="accommodations[{{ $i }}][sharing]"
                                                        class="form-control" value="{{ $acc->sharing }}"></div>

                                                <div class="col-md-4"><label class="form-label">Sharing Type</label><input
                                                        type="text"
                                                        name="accommodations[{{ $i }}][sharing_type]"
                                                        class="form-control" value="{{ $acc->sharing_type }}"></div>
                                                <div class="col-md-8"><label class="form-label">Note</label><input
                                                        type="text" name="accommodations[{{ $i }}][note]"
                                                        class="form-control" value="{{ $acc->note }}"></div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="accommodation-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-accommodation-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3"><label class="form-label">Place</label>
                                                    <select name="accommodations[0][place]" class="form-select">
                                                        <option value="">-- Select Place --</option>
                                                        @foreach (\App\Enums\Place::options() as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Accommodation
                                                        Type</label>
                                                    <select name="accommodations[0][accommodation_type]"
                                                        class="form-select">
                                                        <option value="">-- Select Type --</option>
                                                        @foreach (\App\Enums\AccommodationType::options() as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Saudi Star
                                                        Rating</label>
                                                    <select name="accommodations[0][saudi_star_rating]"
                                                        class="form-select">
                                                        <option value="">-- Select Rating --</option>
                                                        @foreach (\App\Enums\SaudiStarRating::options() as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Hotel</label>
                                                    <select name="accommodations[0][hotel]" class="form-select">
                                                        <option value="">-- Select Hotel --</option>
                                                        @foreach (\App\Enums\Hotel::options() as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-3"><label class="form-label">Distance
                                                        (meter)</label><input type="number"
                                                        name="accommodations[0][distance]" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Check In</label><input
                                                        type="date" name="accommodations[0][check_in]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Check Out</label><input
                                                        type="date" name="accommodations[0][check_out]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Azizia Date</label><input
                                                        type="date" name="accommodations[0][azizia_date]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Food Package</label>
                                                    <select name="accommodations[0][food_package]" class="form-select">
                                                        <option value="">-- Select Package --</option>
                                                        @foreach (\App\Enums\FoodPackage::options() as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4"><label class="form-label">Actual Check In
                                                        Time</label><input type="datetime-local"
                                                        name="accommodations[0][actual_check_in_time]"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-4"><label class="form-label">Actual Check Out
                                                        Time</label><input type="datetime-local"
                                                        name="accommodations[0][actual_check_out_time]"
                                                        class="form-control">
                                                </div>

                                                <div class="col-md-3"><label class="form-label">Days</label><input
                                                        type="number" name="accommodations[0][days]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Nights</label><input
                                                        type="number" name="accommodations[0][nights]"
                                                        class="form-control"></div>

                                                <div class="col-md-3"><label class="form-label">Makkah Ziarat</label>
                                                    <select name="accommodations[0][makkah_ziarat]" class="form-select">
                                                        <option value="">-- Select --</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Madinah Ziarat</label>
                                                    <select name="accommodations[0][madinah_ziarat]" class="form-select">
                                                        <option value="">-- Select --</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4"><label class="form-label">Distribution</label><input
                                                        type="text" name="accommodations[0][distribution]"
                                                        class="form-control"></div>
                                                <div class="col-md-4"><label class="form-label">Camp</label><input
                                                        type="text" name="accommodations[0][camp]"
                                                        class="form-control"></div>
                                                <div class="col-md-4"><label class="form-label">Arafat</label><input
                                                        type="text" name="accommodations[0][arafat]"
                                                        class="form-control"></div>

                                                <div class="col-md-4"><label class="form-label"> Azizia
                                                        Shuttle</label><input type="text"
                                                        name="accommodations[0][shuttle]" class="form-control"></div>
                                                <div class="col-md-4"><label class="form-label">Bedding (Sofa
                                                        Mattress)</label><input type="text"
                                                        name="accommodations[0][bedding]" class="form-control"></div>
                                                <div class="col-md-4"><label class="form-label">Sharing (Room / Tent /
                                                        Camp)</label><input type="text"
                                                        name="accommodations[0][sharing]" class="form-control"></div>

                                                <div class="col-md-4"><label class="form-label">Sharing Type</label><input
                                                        type="text" name="accommodations[0][sharing_type]"
                                                        class="form-control"></div>
                                                <div class="col-md-8"><label class="form-label">Note</label><input
                                                        type="text" name="accommodations[0][note]"
                                                        class="form-control"></div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- ================= TRANSPORT ================= --}}
                        <div class="tab-pane fade card" id="tab-transport">
                            <div class="card-body">

                                {{-- ---- General Route (repeatable) ---- --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="m-0">Transport Details</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        id="addTransportBtn">Add <i class="mdi mdi-plus"></i></button>
                                </div>

                                <div id="transportRows">
                                    @forelse ($package->transports as $i => $t)
                                        <div class="transport-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-transport-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Route</label>
                                                    <select name="transports[{{ $i }}][route]" class="form-control transport-route-select">
                                                        <option value="">-- Select Travel Route --</option>
                                                        @foreach ($travelRoutes as $trRoute)
                                                            @php
                                                                $vName = $trRoute->vehicle ? ($trRoute->vehicle->vehicle_type . ($trRoute->vehicle->brand_name ? ' ' . $trRoute->vehicle->brand_name : '')) : '';
                                                            @endphp
                                                            <option value="{{ $trRoute->name }}"
                                                                {{ $t->route == $trRoute->name ? 'selected' : '' }}
                                                                data-type="{{ $trRoute->sharing_type }}"
                                                                data-vehicle="{{ $vName }}"
                                                                data-arrival-date="{{ $trRoute->arrival_date }}"
                                                                data-arrival-time="{{ $trRoute->arrival_time }}">
                                                                {{ $trRoute->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Arrival Date</label>
                                                    <input type="date" name="transports[{{ $i }}][arrival_date]"
                                                        class="form-control transport-arrival-date"
                                                        value="{{ optional($t->arrival_date)->format('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Arrival Time</label>
                                                    <input type="time" name="transports[{{ $i }}][arrival_time]"
                                                        class="form-control transport-arrival-time"
                                                        value="{{ $t->arrival_time }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Type</label>
                                                    <select name="transports[{{ $i }}][type]" class="form-control transport-type-select">
                                                        <option value="">-- Select Type --</option>
                                                        @foreach (['Group', 'Group Sharing', 'Sharing', 'Private', 'Economy'] as $optType)
                                                            <option value="{{ $optType }}" {{ $t->type == $optType ? 'selected' : '' }}>{{ $optType }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Vehicle</label>
                                                    <input type="text" name="transports[{{ $i }}][vehicle]"
                                                        class="form-control transport-vehicle-input" value="{{ $t->vehicle }}">
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="transport-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-transport-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Route</label>
                                                    <select name="transports[0][route]" class="form-control transport-route-select">
                                                        <option value="">-- Select Travel Route --</option>
                                                        @foreach ($travelRoutes as $trRoute)
                                                            @php
                                                                $vName = $trRoute->vehicle ? ($trRoute->vehicle->vehicle_type . ($trRoute->vehicle->brand_name ? ' ' . $trRoute->vehicle->brand_name : '')) : '';
                                                            @endphp
                                                            <option value="{{ $trRoute->name }}"
                                                                data-type="{{ $trRoute->sharing_type }}"
                                                                data-vehicle="{{ $vName }}"
                                                                data-arrival-date="{{ $trRoute->arrival_date }}"
                                                                data-arrival-time="{{ $trRoute->arrival_time }}">
                                                                {{ $trRoute->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Arrival Date</label>
                                                    <input type="date" name="transports[0][arrival_date]" class="form-control transport-arrival-date">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Arrival Time</label>
                                                    <input type="time" name="transports[0][arrival_time]" class="form-control transport-arrival-time">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Type</label>
                                                    <select name="transports[0][type]" class="form-control transport-type-select">
                                                        <option value="">-- Select Type --</option>
                                                        <option value="Group">Group</option>
                                                        <option value="Group Sharing">Group Sharing</option>
                                                        <option value="Sharing">Sharing</option>
                                                        <option value="Private">Private</option>
                                                        <option value="Economy">Economy</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Vehicle</label>
                                                    <input type="text" name="transports[0][vehicle]" class="form-control transport-vehicle-input">
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>

                                <hr class="my-4">

                                {{-- ---- Flight ---- --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="m-0">Flight</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addFlightBtn">Add
                                        <i class="mdi mdi-plus"></i></button>
                                </div>

                                <div id="flightRows">
                                    @forelse ($package->transportFlights as $i => $f)
                                        <div class="flight-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-flight-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3"><label class="form-label">Airline</label>
                                                    <select name="flights[{{ $i }}][airline]" class="form-control">
                                                        <option value="">-- Select Airline --</option>
                                                        @foreach ($airlines as $airline)
                                                            <option value="{{ $airline->name }}"
                                                                {{ old("flights.$i.airline", $f->airline) == $airline->name ? 'selected' : '' }}>
                                                                {{ $airline->name }} ({{ $airline->code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Flight No.</label><input
                                                        type="text" name="flights[{{ $i }}][flight_no]"
                                                        class="form-control" value="{{ $f->flight_no }}"></div>
                                                <div class="col-md-3"><label class="form-label">Flight Class</label><input
                                                        type="text" name="flights[{{ $i }}][flight_class]"
                                                        class="form-control" value="{{ $f->flight_class }}"></div>
                                                <div class="col-md-3"><label class="form-label">Origin</label><input
                                                        type="text" name="flights[{{ $i }}][origin]"
                                                        class="form-control" value="{{ $f->origin }}"></div>

                                                <div class="col-md-3"><label class="form-label">Destination</label><input
                                                        type="text" name="flights[{{ $i }}][destination]"
                                                        class="form-control" value="{{ $f->destination }}"></div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Date</label><input type="date"
                                                        name="flights[{{ $i }}][departure_date]"
                                                        class="form-control"
                                                        value="{{ optional($f->departure_date)->format('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Time</label><input type="time"
                                                        name="flights[{{ $i }}][departure_time]"
                                                        class="form-control" value="{{ $f->departure_time }}"></div>
                                                <div class="col-md-3"><label class="form-label">Arrival Date</label><input
                                                        type="date" name="flights[{{ $i }}][arrival_date]"
                                                        class="form-control"
                                                        value="{{ optional($f->arrival_date)->format('Y-m-d') }}">
                                                </div>

                                                <div class="col-md-3"><label class="form-label">Arrival Time</label><input
                                                        type="time" name="flights[{{ $i }}][arrival_time]"
                                                        class="form-control" value="{{ $f->arrival_time }}"></div>
                                                <div class="col-md-3"><label class="form-label">PNR No.</label><input
                                                        type="text" name="flights[{{ $i }}][pnr_no]"
                                                        class="form-control" value="{{ $f->pnr_no }}"></div>
                                                <div class="col-md-3"><label class="form-label">Ticket Amount
                                                        (SAR)
                                                    </label><input type="number" step="0.01"
                                                        name="flights[{{ $i }}][ticket_amount]"
                                                        class="form-control" value="{{ $f->ticket_amount }}"></div>
                                                <div class="col-md-3">
                                                    <label class="form-label d-block">Is Preferred</label>
                                                    @foreach (['1' => 'Yes', '0' => 'No'] as $val => $label)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="flights[{{ $i }}][is_preferred]"
                                                                value="{{ $val }}"
                                                                {{ (int) $f->is_preferred == (int) $val ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $label }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="flight-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-flight-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3"><label class="form-label">Airline</label>
                                                    <select name="flights[0][airline]" class="form-control">
                                                        <option value="">-- Select Airline --</option>
                                                        @foreach ($airlines as $airline)
                                                            <option value="{{ $airline->name }}">{{ $airline->name }} ({{ $airline->code }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Flight No.</label><input
                                                        type="text" name="flights[0][flight_no]" class="form-control">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Flight Class</label><input
                                                        type="text" name="flights[0][flight_class]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Origin</label><input
                                                        type="text" name="flights[0][origin]" class="form-control">
                                                </div>

                                                <div class="col-md-3"><label class="form-label">Destination</label><input
                                                        type="text" name="flights[0][destination]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Date</label><input type="date"
                                                        name="flights[0][departure_date]" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Time</label><input type="time"
                                                        name="flights[0][departure_time]" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Arrival Date</label><input
                                                        type="date" name="flights[0][arrival_date]"
                                                        class="form-control"></div>

                                                <div class="col-md-3"><label class="form-label">Arrival Time</label><input
                                                        type="time" name="flights[0][arrival_time]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">PNR No.</label><input
                                                        type="text" name="flights[0][pnr_no]" class="form-control">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Ticket Amount
                                                        (SAR)</label><input type="number" step="0.01"
                                                        name="flights[0][ticket_amount]" class="form-control"></div>
                                                <div class="col-md-3">
                                                    <label class="form-label d-block">Is Preferred</label>
                                                    @foreach (['1' => 'Yes', '0' => 'No'] as $val => $label)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="flights[0][is_preferred]"
                                                                value="{{ $val }}"
                                                                {{ $val == '0' ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $label }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>

                                <hr class="my-4">

                                {{-- ---- Train ---- --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="m-0">Train</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addTrainBtn">Add <i
                                            class="mdi mdi-plus"></i></button>
                                </div>

                                <div id="trainRows">
                                    @forelse ($package->transportTrains as $i => $tr)
                                        <div class="train-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-train-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Select Train</label>
                                                    <select name="trains[{{ $i }}][railway]" class="form-control">
                                                        <option value="">-- Select Train --</option>
                                                        @foreach ($trainsList as $train)
                                                            <option value="{{ $train->name }}" {{ $tr->railway == $train->name ? 'selected' : '' }}>
                                                                {{ $train->name }} @if($train->code) ({{ $train->code }}) @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Train No.</label><input
                                                        type="text" name="trains[{{ $i }}][train_no]"
                                                        class="form-control" value="{{ $tr->train_no }}"></div>
                                                <div class="col-md-3"><label class="form-label">Train Class</label><input
                                                        type="text" name="trains[{{ $i }}][train_class]"
                                                        class="form-control" value="{{ $tr->train_class }}"></div>
                                                <div class="col-md-3"><label class="form-label">Origin</label><input
                                                        type="text" name="trains[{{ $i }}][origin]"
                                                        class="form-control" value="{{ $tr->origin }}"></div>

                                                <div class="col-md-3"><label class="form-label">Destination</label><input
                                                        type="text" name="trains[{{ $i }}][destination]"
                                                        class="form-control" value="{{ $tr->destination }}"></div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Date</label><input type="date"
                                                        name="trains[{{ $i }}][departure_date]"
                                                        class="form-control"
                                                        value="{{ optional($tr->departure_date)->format('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Time</label><input type="time"
                                                        name="trains[{{ $i }}][departure_time]"
                                                        class="form-control" value="{{ $tr->departure_time }}"></div>
                                                <div class="col-md-3"><label class="form-label">Arrival Date</label><input
                                                        type="date" name="trains[{{ $i }}][arrival_date]"
                                                        class="form-control"
                                                        value="{{ optional($tr->arrival_date)->format('Y-m-d') }}">
                                                </div>

                                                <div class="col-md-3"><label class="form-label">Arrival Time</label><input
                                                        type="time" name="trains[{{ $i }}][arrival_time]"
                                                        class="form-control" value="{{ $tr->arrival_time }}"></div>
                                                <div class="col-md-3"><label class="form-label">PNR No.</label><input
                                                        type="text" name="trains[{{ $i }}][pnr_no]"
                                                        class="form-control" value="{{ $tr->pnr_no }}"></div>
                                                <div class="col-md-3"><label class="form-label">Ticket Amount
                                                        (SAR)
                                                    </label><input type="number" step="0.01"
                                                        name="trains[{{ $i }}][ticket_amount]"
                                                        class="form-control" value="{{ $tr->ticket_amount }}"></div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="train-row border rounded p-3 mb-3 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-train-row position-absolute top-0 end-0 m-2"><i
                                                    class="mdi mdi-delete"></i></button>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Select Train</label>
                                                    <select name="trains[0][railway]" class="form-control">
                                                        <option value="">-- Select Train --</option>
                                                        @foreach ($trainsList as $train)
                                                            <option value="{{ $train->name }}">{{ $train->name }} @if($train->code) ({{ $train->code }}) @endif</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Train No.</label><input
                                                        type="text" name="trains[0][train_no]" class="form-control">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Train Class</label><input
                                                        type="text" name="trains[0][train_class]"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Origin</label><input
                                                        type="text" name="trains[0][origin]" class="form-control">
                                                </div>

                                                <div class="col-md-3"><label class="form-label">Destination</label><input
                                                        type="text" name="trains[0][destination]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Date</label><input type="date" name="trains[0][departure_date]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Departure
                                                        Time</label><input type="time" name="trains[0][departure_time]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Arrival Date</label><input
                                                        type="date" name="trains[0][arrival_date]"
                                                        class="form-control"></div>

                                                <div class="col-md-3"><label class="form-label">Arrival Time</label><input
                                                        type="time" name="trains[0][arrival_time]"
                                                        class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">PNR No.</label><input
                                                        type="text" name="trains[0][pnr_no]" class="form-control">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">Ticket Amount
                                                        (SAR)</label><input type="number" step="0.01"
                                                        name="trains[0][ticket_amount]" class="form-control"></div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>

                            </div>
                        </div>

                        {{-- ================= TRAINING / GIFTS ================= --}}
                        <div class="tab-pane fade card" id="tab-training">
                            <div class="card-body">
                                <h5 class="mb-3">Training Session</h5>
                                @if ($trainingSessions->isEmpty())
                                    <p class="text-danger mb-3">
                                        <a href="{{ route('training-session.create') }}" target="_blank">Please create
                                            training sessions first</a>
                                    </p>
                                @else
                                    @php
                                        $selectedSessions = old(
                                            'training_sessions',
                                            $package->trainingSessions->pluck('id')->toArray(),
                                        );
                                    @endphp
                                    @foreach ($trainingSessions as $session)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="training_sessions[]"
                                                value="{{ $session->id }}" id="session-{{ $session->id }}"
                                                {{ in_array($session->id, $selectedSessions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="session-{{ $session->id }}">
                                                {{ $session->name }}
                                                @if ($session->session_date)
                                                    ({{ \Carbon\Carbon::parse($session->session_date)->format('d M, Y') }}
                                                    at
                                                    {{ $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('h:i A') : '' }})
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                @endif

                                <h5 class="mb-3">Giveaways</h5>
                                @php
                                    $selectedGiveaways = old('giveaways', $package->giveaways->pluck('id')->toArray());
                                @endphp
                                @foreach ($giveaways->where('code', 'GW-01') as $g)
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="giveaways[]"
                                            value="{{ $g->id }}" id="giveaway-{{ $g->id }}"
                                            {{ in_array($g->id, $selectedGiveaways) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="giveaway-{{ $g->id }}">{{ $g->code }} -
                                            {{ $g->name }}</label>
                                    </div>
                                @endforeach
                                <textarea name="giveaway_note" rows="3" class="form-control"
                                    placeholder="Write here what giveaway you are giving...">{{ old('giveaway_note', $package->giveaway_note) }}</textarea>
                            </div>
                        </div>

                        {{-- ================= TERMS & CONDITION ================= --}}
                        <div class="tab-pane fade card" id="tab-terms">
                            <div class="card-body">
                                <h5 class="mb-3">Terms & Condition</h5>
                                <textarea name="terms_content" rows="8" class="form-control" placeholder="Write your content here...">{{ old('terms_content', optional($package->terms)->content) }}</textarea>
                            </div>
                        </div>

                        {{-- ================= ITINERARY ================= --}}
                        {{-- <div class="tab-pane fade card" id="tab-itinerary">
                            <div class="card-body">
                                <h5 class="mb-3">Description</h5>
                                <textarea name="itinerary_description" rows="8" class="form-control mb-4"
                                    placeholder="Write your content here...">{{ old('itinerary_description', optional($package->itinerary)->description) }}</textarea>

                                <h5 class="mb-3">Images</h5>
                                <div class="row g-3">
                                    @foreach ([
            'mina_image' => 'MINA',
            'arafat_image' => 'ARAFAT',
            'muzdalifah_image' => 'MUZDALIFAH',
            'makkah_mina_rami_day_one_image' => 'MAKKAH / MINA RAMI - DAY ONE',
            'mina_rami_day_two_image' => 'MINA RAMI - DAY TWO',
            'mina_makkah_rami_day_three_image' => 'MINA / MAKKAH RAMI - DAY THREE',
        ] as $field => $label)
                                        <div class="col-md-4">
                                            <label class="form-label">{{ $label }}</label>
                                            @if ($package->itinerary && $package->itinerary->$field)
                                                <div class="mb-2">
                                                    <img src="{{ asset('assets/images/packages/itinerary/' . $package->itinerary->$field) }}"
                                                        alt="{{ $label }}" style="max-height:80px"
                                                        class="d-block rounded border">
                                                </div>
                                            @endif
                                            <input type="file" name="{{ $field }}" class="form-control">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div> --}}

                        {{-- ================= MAKTAB ADDRESS ================= --}}
                        <div class="tab-pane fade card" id="tab-maktab">
                            <div class="card-body">
                                <h5 class="mb-3">Mina Address</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Mina Address</label>
                                        <input type="text" name="maktab_address" class="form-control"
                                            value="{{ old('maktab_address', optional($package->maktabAddress)->maktab_address) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mina Address</label>
                                        <input type="text" name="office_address" class="form-control"
                                            value="{{ old('office_address', optional($package->maktabAddress)->office_address) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end" style="margin: 20px">
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script>
        (function() {
            let accIndex = {{ max($package->accommodations->count(), 1) }};

            document.getElementById('addAccommodationBtn').addEventListener('click', function() {
                const firstRow = document.querySelector('.accommodation-row');
                const clone = firstRow.cloneNode(true);

                clone.querySelectorAll('input, textarea, select').forEach(function(el) {
                    el.name = el.name.replace(/accommodations\[\d+\]/, 'accommodations[' + accIndex +
                        ']');
                    if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0;
                    } else if (el.type !== 'button') {
                        el.value = '';
                    }
                });

                document.getElementById('accommodationRows').appendChild(clone);
                accIndex++;
            });

            document.getElementById('accommodationRows').addEventListener('click', function(e) {
                if (e.target.closest('.remove-accommodation-row')) {
                    const row = e.target.closest('.accommodation-row');
                    if (document.querySelectorAll('.accommodation-row').length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll('input, textarea').forEach(el => el.value = '');
                        row.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
                    }
                }
            });
        })();

        // ---- Transport repeatable rows ----
        (function() {
            let transportIndex = {{ max($package->transports->count(), 1) }};

            document.getElementById('addTransportBtn').addEventListener('click', function() {
                const firstRow = document.querySelector('.transport-row');
                const clone = firstRow.cloneNode(true);

                clone.querySelectorAll('input, select').forEach(function(el) {
                    el.name = el.name.replace(/transports\[\d+\]/, 'transports[' + transportIndex + ']');
                    if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0;
                    } else {
                        el.value = '';
                    }
                });

                document.getElementById('transportRows').appendChild(clone);
                transportIndex++;
            });

            document.getElementById('transportRows').addEventListener('click', function(e) {
                if (e.target.closest('.remove-transport-row')) {
                    const row = e.target.closest('.transport-row');
                    if (document.querySelectorAll('.transport-row').length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll('input').forEach(el => el.value = '');
                        row.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
                    }
                }
            });

            // Auto populate details on Travel Route selection
            document.getElementById('transportRows').addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('transport-route-select')) {
                    const row = e.target.closest('.transport-row');
                    if (!row) return;
                    const opt = e.target.options[e.target.selectedIndex];
                    if (!opt || !opt.value) return;

                    const type = opt.getAttribute('data-type') || '';
                    const vehicle = opt.getAttribute('data-vehicle') || '';
                    const arrivalDate = opt.getAttribute('data-arrival-date') || '';
                    const arrivalTime = opt.getAttribute('data-arrival-time') || '';

                    const typeEl = row.querySelector('.transport-type-select');
                    const vehicleEl = row.querySelector('.transport-vehicle-input');
                    const dateEl = row.querySelector('.transport-arrival-date');
                    const timeEl = row.querySelector('.transport-arrival-time');

                    if (typeEl && type) {
                        typeEl.value = type;
                        if (!typeEl.value) {
                            for (let i = 0; i < typeEl.options.length; i++) {
                                if (typeEl.options[i].value.toLowerCase() === type.toLowerCase()) {
                                    typeEl.selectedIndex = i;
                                    break;
                                }
                            }
                        }
                    }
                    if (vehicleEl && vehicle) vehicleEl.value = vehicle;
                    if (dateEl && arrivalDate) dateEl.value = arrivalDate;
                    if (timeEl && arrivalTime) timeEl.value = arrivalTime;
                }
            });
        })();

        // ---- Flight repeatable rows ----
        (function() {
            let flightIndex = {{ max($package->transportFlights->count(), 1) }};

            document.getElementById('addFlightBtn').addEventListener('click', function() {
                const firstRow = document.querySelector('.flight-row');
                const clone = firstRow.cloneNode(true);

                clone.querySelectorAll('input, select').forEach(function(el) {
                    el.name = el.name.replace(/flights\[\d+\]/, 'flights[' + flightIndex + ']');
                    if (el.type === 'radio') {
                        el.checked = el.value === '0';
                    } else {
                        el.value = '';
                    }
                });

                document.getElementById('flightRows').appendChild(clone);
                flightIndex++;
            });

            document.getElementById('flightRows').addEventListener('click', function(e) {
                if (e.target.closest('.remove-flight-row')) {
                    const row = e.target.closest('.flight-row');
                    if (document.querySelectorAll('.flight-row').length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll(
                                'input[type="text"], input[type="date"], input[type="time"], input[type="number"], select'
                            )
                            .forEach(el => el.value = '');
                        row.querySelectorAll('input[type="radio"]').forEach(el => el.checked = el.value ===
                            '0');
                    }
                }
            });
        })();

        // ---- Train repeatable rows ----
        (function() {
            let trainIndex = {{ max($package->transportTrains->count(), 1) }};

            document.getElementById('addTrainBtn').addEventListener('click', function() {
                const firstRow = document.querySelector('.train-row');
                const clone = firstRow.cloneNode(true);

                clone.querySelectorAll('input, select').forEach(function(el) {
                    el.name = el.name.replace(/trains\[\d+\]/, 'trains[' + trainIndex + ']');
                    if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0;
                    } else {
                        el.value = '';
                    }
                });

                document.getElementById('trainRows').appendChild(clone);
                trainIndex++;
            });

            document.getElementById('trainRows').addEventListener('click', function(e) {
                if (e.target.closest('.remove-train-row')) {
                    const row = e.target.closest('.train-row');
                    if (document.querySelectorAll('.train-row').length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll('input').forEach(el => el.value = '');
                        row.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
                    }
                }
            });
        })();

        // ---- Check In / Check Out / Days / Nights Auto Calculation ----
        (function() {
            function handleDateNightsCalc(target) {
                const row = target.closest('.accommodation-row');
                if (!row) return;

                const checkInEl  = row.querySelector('input[name*="[check_in]"]');
                const checkOutEl = row.querySelector('input[name*="[check_out]"]');
                const nightsEl   = row.querySelector('input[name*="[nights]"]');
                const daysEl     = row.querySelector('input[name*="[days]"]');

                if (!checkInEl || !checkOutEl || !nightsEl) return;

                const isCheckIn  = target === checkInEl;
                const isCheckOut = target === checkOutEl;
                const isNights   = target === nightsEl;
                const isDays     = daysEl && target === daysEl;

                if (!isCheckIn && !isCheckOut && !isNights && !isDays) return;

                if (isDays && daysEl.value) {
                    const dVal = parseInt(daysEl.value);
                    if (!isNaN(dVal) && dVal >= 1) {
                        nightsEl.value = dVal - 1;
                    }
                }

                const inVal  = checkInEl.value;
                const outVal = checkOutEl.value;
                const nVal   = parseInt(nightsEl.value);

                if ((isCheckIn || isCheckOut) && inVal && outVal) {
                    const dIn  = new Date(inVal);
                    const dOut = new Date(outVal);
                    const diff = Math.round((dOut.getTime() - dIn.getTime()) / (1000 * 3600 * 24));
                    if (!isNaN(diff) && diff >= 0) {
                        nightsEl.value = diff;
                        if (daysEl) daysEl.value = diff + 1;
                    }
                } else if ((isCheckIn || isNights || isDays) && inVal && !isNaN(nVal) && nVal >= 0) {
                    const dIn = new Date(inVal);
                    dIn.setDate(dIn.getDate() + nVal);
                    const year = dIn.getFullYear();
                    const month = String(dIn.getMonth() + 1).padStart(2, '0');
                    const day = String(dIn.getDate()).padStart(2, '0');
                    checkOutEl.value = `${year}-${month}-${day}`;
                    if (daysEl) daysEl.value = nVal + 1;
                } else if (isCheckOut && outVal && !isNaN(nVal) && nVal >= 0 && !inVal) {
                    const dOut = new Date(outVal);
                    dOut.setDate(dOut.getDate() - nVal);
                    const year = dOut.getFullYear();
                    const month = String(dOut.getMonth() + 1).padStart(2, '0');
                    const day = String(dOut.getDate()).padStart(2, '0');
                    checkInEl.value = `${year}-${month}-${day}`;
                    if (daysEl) daysEl.value = nVal + 1;
                }
            }

            document.addEventListener('input', function(e) {
                handleDateNightsCalc(e.target);
            });
            document.addEventListener('change', function(e) {
                handleDateNightsCalc(e.target);
            });
        })();
    </script>
@endsection
