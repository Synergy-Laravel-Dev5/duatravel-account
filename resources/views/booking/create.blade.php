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

                {{-- Summary Bar --}}
                <div class="booking-summary-bar mb-3 px-3 py-2 d-flex flex-wrap gap-3 align-items-center rounded"
                    style="background:#f0f4ff; border:1px solid #d0d9f0; font-size:13px;">
                    <span>Persons: <strong id="bar_pax">1</strong></span>
                    <span>|</span>
                    <span>Adult Costing: <strong id="bar_adult">0.00</strong></span>
                    <span>|</span>
                    <span>Visa: <strong id="bar_visa">0.00</strong></span>
                    <span>|</span>
                    <span>Flight: <strong id="bar_flight">0.00</strong></span>
                    <span>|</span>
                    <span class="text-primary fw-semibold">Total: <strong id="bar_total">0.00</strong></span>
                    <span>|</span>
                    <span class="text-danger fw-semibold">Balance: <strong id="bar_balance">0.00</strong></span>
                </div>

                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm" enctype="multipart/form-data">
                    @csrf

                    {{-- TAB NAV --}}
                    <ul class="nav nav-tabs booking-tabs mb-0" id="bookingTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-package"><i
                                    class="mdi mdi-tag me-1"></i>Package</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-details"><i
                                    class="mdi mdi-account me-1"></i>Booking Details</a></li>
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
                        <div class="tab-pane fade show active" id="tab-package">
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
                                <div class="col-md-6">
                                    <label class="form-label">Package Name</label>
                                    <input type="text" name="package_name" id="package_name" class="form-control"
                                        placeholder="e.g. Economy Umrah 2025">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Booking Status</label>
                                    <select name="status" class="form-select" readonly>
                                        <option value="pending" selected>Pending</option>
                                    </select>
                                    <input type="hidden" name="package_type" id="package_type" value="{{ session('dashboard_package', 'umrah') }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 2: Booking Details --}}
                        <div class="tab-pane fade" id="tab-details">

                            <div class="row g-3">
                                <input type="hidden" name="booking_for" value="company">

                                {{-- COMPANY BLOCK --}}
                                <div class="col-md-4" id="companyBlock">
                                    <label class="form-label">Company <span class="text-danger">*</span></label>
                                    <select name="company_id" id="companySelect" class="form-select" required>
                                        <option value="">-- Select Company --</option>
                                        @foreach ($companies as $co)
                                            <option value="{{ $co->id }}">{{ $co->name ?? $co->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">No of Pax <span class="text-danger">*</span></label>
                                    <input type="number" name="no_of_pax" id="no_of_pax" class="form-control"
                                        value="1" min="1" required>
                                    <small class="text-muted">Persons, Flight & Visa tabs will update automatically</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Care Of</label>
                                    <input type="text" name="care_of" class="form-control"
                                        placeholder="Guardian / Agent">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Passport Number</label>
                                    <input type="text" name="passport_number" id="fill_passport" class="form-control"
                                        placeholder="Passport #">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">CNIC Number</label>
                                    <input type="text" name="cnic" id="fill_cnic" class="form-control"
                                        placeholder="CNIC #">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" id="fill_phone" class="form-control"
                                        placeholder="Phone #">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Emergency Phone</label>
                                    <input type="text" name="emergency_phone" class="form-control"
                                        placeholder="+92 300 0000000">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev"><i
                                        class="mdi mdi-arrow-left me-1"></i> Prev</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i
                                        class="mdi mdi-arrow-right ms-1"></i></button>
                            </div>
                        </div>

                        {{-- TAB 3: Persons --}}
                        <div class="tab-pane fade" id="tab-persons">
                            <div id="personsBookingForNote" class="alert alert-info py-2 px-3 d-none"
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
                                <div class="col-md-3">
                                    <label class="form-label">Arrival PNR / Ticket #</label>
                                    <input type="text" name="arrival_pnr" class="form-control" placeholder="XYZ456">
                                </div>
                                <div class="col-md-6">
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
                            <div id="hotelsList">
                                @foreach ([['makkah', 'Makkah'], ['madinah', 'Madinah']] as [$val, $label])
                                    <div class="hotel-block border rounded p-3 mb-3">
                                        <h6 class="text-primary mb-3">{{ $label }}</h6>
                                        <input type="hidden" name="hotels[{{ $loop->index }}][location]"
                                            value="{{ $val }}">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Hotel Name</label>
                                                <select name="hotels[{{ $loop->index }}][hotel_name]" class="form-select">
                                                    <option value="">Select Hotel</option>
                                                    @foreach($hotels as $h)
                                                        <option value="{{ $h->name }}">{{ $h->name }} ({{ $h->city }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Nights</label>
                                                <input type="number" name="hotels[{{ $loop->index }}][no_of_nights]"
                                                    class="form-control" value="1" min="1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Room Type</label>
                                                <select name="hotels[{{ $loop->index }}][room_type]"
                                                    class="form-select">
                                                    <option value="single">Single</option>
                                                    <option value="double">Double</option>
                                                    <option value="triple">Triple</option>
                                                    <option value="quad">Quad</option>
                                                    <option value="suite">Suite</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">No. of Rooms</label>
                                                <input type="number" name="hotels[{{ $loop->index }}][no_of_rooms]"
                                                    class="form-control" value="1" min="1">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Check In</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][check_in]"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Check Out</label>
                                                <input type="date" name="hotels[{{ $loop->index }}][check_out]"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                                                <input type="file" name="hotels[{{ $loop->index }}][hotel_voucher]"
                                                    class="form-control" accept="image/*,.pdf">
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
                                <div class="route-block border rounded p-3 mb-2">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label">Route</label>
                                            <input type="text" name="transports[0][route]" class="form-control"
                                                placeholder="Karachi → Makkah">
                                        </div>
                                        <div class="col-md-4">
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
                                        <div class="col-md-3">
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
                                <div class="col-md-3">
                                    <label class="form-label">Package Cost (per person)</label>
                                    <input type="number" name="package_cost" id="package_cost"
                                        class="form-control calc" placeholder="Enter package cost" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Visa Charges (total)</label>
                                    <input type="number" name="visa_charges" id="visa_charges"
                                        class="form-control calc" placeholder="Enter visa charges" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Flight Charges (total)</label>
                                    <input type="number" name="flight_charges" id="flight_charges"
                                        class="form-control calc" placeholder="Enter flight charges" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Qurbani & Other Charges</label>
                                    <input type="number" name="other_charges" id="other_charges"
                                        class="form-control calc" placeholder="Enter other charges" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Discount</label>
                                    <input type="number" name="discount" id="discount"
                                        class="form-control calc" placeholder="Enter discount" step="0.01">
                                </div>

                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm bg-light">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>Persons</th>
                                                    <th>Pkg Cost × Pax</th>
                                                    <th>Visa</th>
                                                    <th>Flight</th>
                                                    <th>Other</th>
                                                    <th class="text-success">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong id="sum_pax">1</strong></td>
                                                    <td id="sum_pkg">0.00</td>
                                                    <td id="sum_visa">0.00</td>
                                                    <td id="sum_flight">0.00</td>
                                                    <td id="sum_other">0.00</td>
                                                    <td class="text-success fw-bold" id="sum_total">0.00</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Total Amount</label>
                                    <input type="number" name="total_amount" id="total_amount"
                                        class="form-control bg-light fw-bold" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Received</label>
                                    <input type="number" name="total_received" id="total_received"
                                        class="form-control calc" placeholder="Enter amount received" step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Balance Remaining</label>
                                    <input type="number" name="balance" id="balance"
                                        class="form-control bg-light text-danger fw-bold" readonly>
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
        const packagesData = @json($packages);
        const clientsData  = @json($clients);
        const hotelsData   = @json($hotels);

        const tabLinks = Array.from(document.querySelectorAll('#bookingTabs .nav-link'));

        function activateTab(idx) {
            if (idx < 0 || idx >= tabLinks.length) return;
            tabLinks[idx].click();
            if (tabLinks[idx].getAttribute('href') === '#tab-visa') syncVisas();
            if (tabLinks[idx].getAttribute('href') === '#tab-flight') syncFlightPersons();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-next, .btn-prev');
            if (!btn) return;
            const cur = tabLinks.findIndex(t => t.classList.contains('active'));
            btn.classList.contains('btn-next') ? activateTab(cur + 1) : activateTab(cur - 1);
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
                if (document.querySelector('input[name="departure_date"]')) {
                    document.querySelector('input[name="departure_date"]').value = fl.departure_date || '';
                }
                if (document.querySelector('input[name="departure_flight"]')) {
                    document.querySelector('input[name="departure_flight"]').value = fl.flight_no || '';
                }
                if (document.querySelector('input[name="departure_time"]')) {
                    document.querySelector('input[name="departure_time"]').value = fl.departure_time || '';
                }
                if (document.querySelector('select[name="departure_airline"]')) {
                    document.querySelector('select[name="departure_airline"]').value = fl.airline || '';
                }
                if (document.querySelector('input[name="departure_pnr"]')) {
                    document.querySelector('input[name="departure_pnr"]').value = fl.pnr_no || '';
                }

                if (document.querySelector('input[name="arrival_date"]')) {
                    document.querySelector('input[name="arrival_date"]').value = fl.arrival_date || '';
                }
                if (document.querySelector('input[name="arrival_flight"]')) {
                    document.querySelector('input[name="arrival_flight"]').value = fl.arrival_flight || fl.flight_no || '';
                }
                if (document.querySelector('input[name="arrival_time"]')) {
                    document.querySelector('input[name="arrival_time"]').value = fl.arrival_time || '';
                }
                if (document.querySelector('select[name="arrival_airline"]')) {
                    document.querySelector('select[name="arrival_airline"]').value = fl.airline || '';
                }
                if (document.querySelector('input[name="arrival_pnr"]')) {
                    document.querySelector('input[name="arrival_pnr"]').value = fl.pnr_no || '';
                }
            }

            // if (pkg.accommodations && pkg.accommodations.length > 0) {
            //     const hotelsList = document.getElementById('hotelsList');
            //     if (hotelsList) {
            //         hotelsList.innerHTML = '';
            //         pkg.accommodations.forEach((acc, idx) => {
            //             const loc = acc.place ? acc.place.toLowerCase() : (idx === 0 ? 'makkah' : 'madinah');
            //             const hotelName = acc.hotel ? acc.hotel.name : (acc.accommodation_type || '');
            //             const roomType = acc.sharing_type || 'double';
            //             let checkIn = acc.check_in ? acc.check_in.substring(0, 10) : '';
            //             let checkOut = acc.check_out ? acc.check_out.substring(0, 10) : '';
            //             let nights = acc.nights || acc.days || '';

            //             if (checkIn && checkOut) {
            //                 const dIn = new Date(checkIn);
            //                 const dOut = new Date(checkOut);
            //                 const diff = Math.round((dOut.getTime() - dIn.getTime()) / (1000 * 3600 * 24));
            //                 if (!isNaN(diff) && diff >= 0) nights = diff;
            //             } else if (checkIn && nights && !checkOut) {
            //                 const dIn = new Date(checkIn);
            //                 dIn.setDate(dIn.getDate() + parseInt(nights));
            //                 const year = dIn.getFullYear();
            //                 const month = String(dIn.getMonth() + 1).padStart(2, '0');
            //                 const day = String(dIn.getDate()).padStart(2, '0');
            //                 checkOut = `${year}-${month}-${day}`;
            //             } else if (checkOut && nights && !checkIn) {
            //                 const dOut = new Date(checkOut);
            //                 dOut.setDate(dOut.getDate() - parseInt(nights));
            //                 const year = dOut.getFullYear();
            //                 const month = String(dOut.getMonth() + 1).padStart(2, '0');
            //                 const day = String(dOut.getDate()).padStart(2, '0');
            //                 checkIn = `${year}-${month}-${day}`;
            //             }
            //             if (!nights) nights = 1;

            //             const hotelId = acc.hotel ? acc.hotel.id : '';
            //             let hotelOptions = '<option value="">Select Hotel</option>';
            //             if (typeof hotelsData !== 'undefined' && hotelsData.length > 0) {
            //                 hotelsData.forEach(h => {
            //                     const selected = h.id === hotelId ? 'selected' : '';
            //                     hotelOptions += `<option value="${h.name}" ${selected}>${h.name} (${h.city})</option>`;
            //                 });
            //             }

            //             hotelsList.insertAdjacentHTML('beforeend', `
            //                 <div class="hotel-block border rounded p-3 mb-3">
            //                     <div class="d-flex justify-content-between mb-2">
            //                         <h6 class="text-primary mb-0">${acc.place || 'Hotel Option ' + (idx + 1)}</h6>
            //                         ${idx >= 2 ? '<button type="button" class="btn btn-outline-danger btn-sm remove-hotel">× Remove</button>' : ''}
            //                     </div>
            //                     <input type="hidden" name="hotels[${idx}][location]" value="${loc}">
            //                     <div class="row g-3">
            //                         <div class="col-md-4">
            //                             <label class="form-label">Hotel Name</label>
            //                             <select name="hotels[${idx}][hotel_name]" class="form-select">
            //                                 ${hotelOptions}
            //                             </select>
            //                         </div>
            //                         <div class="col-md-2">
            //                             <label class="form-label">Nights</label>
            //                             <input type="number" name="hotels[${idx}][no_of_nights]" class="form-control" value="${nights}" min="1">
            //                         </div>
            //                         <div class="col-md-3">
            //                             <label class="form-label">Room Type</label>
            //                             <select name="hotels[${idx}][room_type]" class="form-select">
            //                                 <option value="single" ${roomType === 'single' ? 'selected' : ''}>Single</option>
            //                                 <option value="double" ${roomType === 'double' ? 'selected' : ''}>Double</option>
            //                                 <option value="triple" ${roomType === 'triple' ? 'selected' : ''}>Triple</option>
            //                                 <option value="quad" ${roomType === 'quad' ? 'selected' : ''}>Quad</option>
            //                                 <option value="suite" ${roomType === 'suite' ? 'selected' : ''}>Suite</option>
            //                             </select>
            //                         </div>
            //                         <div class="col-md-3">
            //                             <label class="form-label">No. of Rooms</label>
            //                             <input type="number" name="hotels[${idx}][no_of_rooms]" class="form-control" value="1" min="1">
            //                         </div>
            //                         <div class="col-md-3">
            //                             <label class="form-label">Check In</label>
            //                             <input type="date" name="hotels[${idx}][check_in]" class="form-control" value="${checkIn}">
            //                         </div>
            //                         <div class="col-md-3">
            //                             <label class="form-label">Check Out</label>
            //                             <input type="date" name="hotels[${idx}][check_out]" class="form-control" value="${checkOut}">
            //                         </div>
            //                         <div class="col-md-6">
            //                             <label class="form-label">Hotel Voucher / Booking Confirmation</label>
            //                             <input type="file" name="hotels[${idx}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
            //                         </div>
            //                     </div>
            //                 </div>
            //             `);
            //         });
            //     }
            // }

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
                const dIn = new Date(checkIn);
                const dOut = new Date(checkOut);
                const diff = Math.round((dOut.getTime() - dIn.getTime()) / (1000 * 3600 * 24));
                if (!isNaN(diff) && diff >= 0) nights = diff;
            } else if (checkIn && nights && !checkOut) {
                const dIn = new Date(checkIn);
                dIn.setDate(dIn.getDate() + parseInt(nights));
                const year = dIn.getFullYear();
                const month = String(dIn.getMonth() + 1).padStart(2, '0');
                const day = String(dIn.getDate()).padStart(2, '0');
                checkOut = `${year}-${month}-${day}`;
            } else if (checkOut && nights && !checkIn) {
                const dOut = new Date(checkOut);
                dOut.setDate(dOut.getDate() - parseInt(nights));
                const year = dOut.getFullYear();
                const month = String(dOut.getMonth() + 1).padStart(2, '0');
                const day = String(dOut.getDate()).padStart(2, '0');
                checkIn = `${year}-${month}-${day}`;
            }
            if (!nights) nights = 1;

            // FIX: Use the hotel NAME to match, since the select option value is the hotel name
            const selectedHotelName = acc.hotel ? acc.hotel.name : (acc.accommodation_type || '');

            let hotelOptions = '<option value="">Select Hotel</option>';
            if (typeof hotelsData !== 'undefined' && hotelsData.length > 0) {
                hotelsData.forEach(h => {
                    // FIX: Compare by name (since option value is the hotel name)
                    const isSelected = (h.name === selectedHotelName) ? 'selected' : '';
                    hotelOptions += `<option value="${h.name}" ${isSelected}>${h.name} (${h.place || ''})</option>`;
                });
            }

            hotelsList.insertAdjacentHTML('beforeend', `
                <div class="hotel-block border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="text-primary mb-0">${acc.place || 'Hotel Option ' + (idx + 1)}</h6>
                        ${idx >= 2 ? '<button type="button" class="btn btn-outline-danger btn-sm remove-hotel">× Remove</button>' : ''}
                    </div>
                    <input type="hidden" name="hotels[${idx}][location]" value="${loc}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Hotel Name</label>
                            <select name="hotels[${idx}][hotel_name]" class="form-select">
                                ${hotelOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Nights</label>
                            <input type="number" name="hotels[${idx}][no_of_nights]" class="form-control" value="${nights}" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Room Type</label>
                            <select name="hotels[${idx}][room_type]" class="form-select">
                                <option value="single" ${roomType === 'single' ? 'selected' : ''}>Single</option>
                                <option value="double" ${roomType === 'double' ? 'selected' : ''}>Double</option>
                                <option value="triple" ${roomType === 'triple' ? 'selected' : ''}>Triple</option>
                                <option value="quad" ${roomType === 'quad' ? 'selected' : ''}>Quad</option>
                                <option value="suite" ${roomType === 'suite' ? 'selected' : ''}>Suite</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No. of Rooms</label>
                            <input type="number" name="hotels[${idx}][no_of_rooms]" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Check In</label>
                            <input type="date" name="hotels[${idx}][check_in]" class="form-control" value="${checkIn}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Check Out</label>
                            <input type="date" name="hotels[${idx}][check_out]" class="form-control" value="${checkOut}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                            <input type="file" name="hotels[${idx}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
                        </div>
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
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">Route ${idx + 1}</span>
                                    ${idx > 0 ? '<button type="button" class="btn btn-outline-danger btn-sm remove-route">×</button>' : ''}
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Route</label>
                                        <input type="text" name="transports[${idx}][route]" class="form-control" value="${tr.route || ''}" placeholder="Route">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Transport Type</label>
                                        <select name="transports[${idx}][transport_type]" class="form-select">
                                            <option value="private_car" ${tr.type === 'private_car' ? 'selected' : ''}>Private Car</option>
                                            <option value="bus" ${tr.type === 'bus' ? 'selected' : ''}>Bus / Coach</option>
                                            <option value="train" ${tr.type === 'train' ? 'selected' : ''}>Train</option>
                                            <option value="shared_van" ${tr.type === 'shared_van' ? 'selected' : ''}>Shared Van</option>
                                            <option value="taxi" ${tr.type === 'taxi' ? 'selected' : ''}>Taxi</option>
                                            <option value="flight" ${tr.type === 'flight' ? 'selected' : ''}>Flight</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Notes</label>
                                        <input type="text" name="transports[${idx}][notes]" class="form-control" value="${tr.vehicle || ''}" placeholder="Optional">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Transport Ticket / Receipt</label>
                                        <input type="file" name="transports[${idx}][transport_ticket]" class="form-control" accept="image/*,.pdf">
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                }
            }

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
                        <label class="form-label" style="font-size:12px;">Father Name</label>
                        <input type="text" name="persons[${idx}][father_name]" id="person_father_name_${idx}"
                               class="form-control form-control-sm" placeholder="Father Name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Passport #</label>
                        <input type="text" name="persons[${idx}][passport_number]" id="person_passport_${idx}"
                               class="form-control form-control-sm" placeholder="Passport #">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Date of Birth</label>
                        <input type="date" name="persons[${idx}][dob]" id="person_dob_${idx}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Gender</label>
                        <select name="persons[${idx}][gender]" id="person_gender_${idx}" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Blood Group</label>
                        <select name="persons[${idx}][blood_group]" id="person_blood_group_${idx}" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;">CNIC</label>
                        <input type="text" name="persons[${idx}][cnic]" id="person_cnic_${idx}"
                               class="form-control form-control-sm" placeholder="XXXXX-XXXXXXX-X">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:12px;">Phone</label>
                        <input type="text" name="persons[${idx}][phone]" id="person_phone_${idx}"
                               class="form-control form-control-sm" placeholder="+92 300 0000000">
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
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">CNIC Front</label>
                            <input type="file" name="persons[${idx}][cnic_front]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">CNIC Back</label>
                            <input type="file" name="persons[${idx}][cnic_back]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">Passport First Page</label>
                            <input type="file" name="persons[${idx}][passport_photo]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">Picture of Haji</label>
                            <input type="file" name="persons[${idx}][photo]" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label" style="font-size:11px;">Medical Fitness Certificate</label>
                            <input type="file" name="persons[${idx}][medical_certificate]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                    </div>
                </div>

                <div class="border-top mt-3 pt-3">
                    <strong class="text-secondary d-block mb-2" style="font-size:12px;">
                        <i class="mdi mdi-account-alert me-1"></i> Nominee Details
                    </strong>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">Nominee Name</label>
                            <input type="text" name="persons[${idx}][nominee_name]" class="form-control form-control-sm" placeholder="Nominee full name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">Relation with Haji</label>
                            <input type="text" name="persons[${idx}][nominee_relation]" class="form-control form-control-sm" placeholder="e.g. Son / Wife / Brother">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">Nominee CNIC</label>
                            <input type="text" name="persons[${idx}][nominee_cnic]" class="form-control form-control-sm" placeholder="XXXXX-XXXXXXX-X">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:11px;">Nominee Mobile</label>
                            <input type="text" name="persons[${idx}][nominee_mobile]" class="form-control form-control-sm" placeholder="+92 300 0000000">
                        </div>
                    </div>
                </div>
            </div>`;
        }

        function rebuildPersons() {
            const pax = parseInt(document.getElementById('no_of_pax').value) || 1;
            const list = document.getElementById('personsList');
            list.innerHTML = '';
            for (let i = 0; i < pax; i++) {
                list.insertAdjacentHTML('beforeend', buildPersonRow(i, i === 0));
            }
            calcTotal();
        }

        document.getElementById('no_of_pax').addEventListener('input', function() {
            rebuildPersons();
            syncFlightPersons();
            syncVisas();
        });

        rebuildPersons();

        function syncFlightPersons() {
            const pax = parseInt(document.getElementById('no_of_pax').value) || 1;
            const list = document.getElementById('flightPersonsList');
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
            const pax = parseInt(document.getElementById('no_of_pax').value) || 1;
            const list = document.getElementById('visasList');
            list.innerHTML = '';
            for (let i = 0; i < pax; i++) {
                const personName = document.getElementById(`person_name_${i}`)?.value || '';
                const personPass = document.getElementById(`person_passport_${i}`)?.value || '';
                list.insertAdjacentHTML('beforeend', `
                <div class="visa-card" id="visa_card_${i}">
                    <strong class="text-warning" style="font-size:13px;">
                        <i class="mdi mdi-passport me-1"></i> Visa ${i + 1}: ${personName || 'Passenger ' + (i + 1)}
                    </strong>
                    <div class="row g-2 mt-1">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Passport Number</label>
                            <input type="text" name="visas[${i}][passport_number]"
                                   class="form-control form-control-sm" value="${personPass}" placeholder="Passport #">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Full Name</label>
                            <input type="text" name="visas[${i}][given_name]"
                                   class="form-control form-control-sm" value="${personName}" placeholder="Full Name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Date of Birth</label>
                            <input type="date" name="visas[${i}][date_of_birth]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Company</label>
                            <input type="text" name="visas[${i}][company]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Send To</label>
                            <select name="visas[${i}][send_to]" class="form-select form-select-sm">
                                <option value="">-- Select --</option>
                                <option value="shirka">Shirka</option>
                                <option value="consulate">Consulate</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;">Status</label>
                            <select name="visas[${i}][status]" class="form-select form-select-sm">
                                <option value="pending">Pending</option>
                                <option value="submitted">Submitted</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px;">Visa Attachment (e.g. Visa PDF / Image)</label>
                            <input type="file" name="visas[${i}][visa_attachment]" class="form-control form-control-sm" accept="image/*,.pdf">
                        </div>
                    </div>
                </div>`);
            }
        }

        document.querySelector('a[href="#tab-visa"]').addEventListener('click', syncVisas);
        document.querySelector('a[href="#tab-flight"]').addEventListener('click', syncFlightPersons);

        let hotelIdx = 2;
        document.getElementById('addHotel').addEventListener('click', function() {
            document.getElementById('hotelsList').insertAdjacentHTML('beforeend', `
            <div class="hotel-block border rounded p-3 mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="text-primary mb-0">Additional Hotel</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-hotel">× Remove</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <select name="hotels[${hotelIdx}][location]" class="form-select">
                            <option value="makkah">Makkah</option>
                            <option value="madinah">Madinah</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hotel Name</label>
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select">
                            <option value="">Select Hotel</option>
                            ${hotelsData ? hotelsData.map(h => `<option value="${h.name}">${h.name} (${h.place || ''})</option>`).join('') : ''}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nights</label>
                        <input type="number" name="hotels[${hotelIdx}][no_of_nights]" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Room Type</label>
                        <select name="hotels[${hotelIdx}][room_type]" class="form-select">
                            <option value="single">Single</option>
                            <option value="double">Double</option>
                            <option value="triple">Triple</option>
                            <option value="quad">Quad</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">No. of Rooms</label>
                        <input type="number" name="hotels[${hotelIdx}][no_of_rooms]" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check In</label>
                        <input type="date" name="hotels[${hotelIdx}][check_in]" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check Out</label>
                        <input type="date" name="hotels[${hotelIdx}][check_out]" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                        <input type="file" name="hotels[${hotelIdx}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
                    </div>
                </div>
            </div>`);
            hotelIdx++;
        });

        document.getElementById('hotelsList').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-hotel')) e.target.closest('.hotel-block').remove();
        });

        let routeIdx = 1;
        document.getElementById('addRoute').addEventListener('click', function() {
            document.getElementById('routesList').insertAdjacentHTML('beforeend', `
            <div class="route-block border rounded p-3 mb-2">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Additional Route</span>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-route">×</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <input type="text" name="transports[${routeIdx}][route]" class="form-control" placeholder="Route">
                    </div>
                    <div class="col-md-4">
                        <select name="transports[${routeIdx}][transport_type]" class="form-select">
                            <option value="private_car">Private Car</option>
                            <option value="bus">Bus / Coach</option>
                            <option value="train">Train</option>
                            <option value="shared_van">Shared Van</option>
                            <option value="taxi">Taxi</option>
                            <option value="flight">Flight</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="transports[${routeIdx}][notes]" class="form-control" placeholder="Notes">
                    </div>
                    <div class="col-md-6">
                        <input type="file" name="transports[${routeIdx}][transport_ticket]" class="form-control" accept="image/*,.pdf">
                    </div>
                </div>
            </div>`);
            routeIdx++;
        });

        document.getElementById('routesList').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-route')) e.target.closest('.route-block').remove();
        });

        function calcTotal() {
            const pax = parseInt(document.getElementById('no_of_pax') ? document.getElementById('no_of_pax').value : 1) || 1;
            const pkg = parseFloat(document.getElementById('package_cost') ? document.getElementById('package_cost').value : 0) || 0;
            const visa = parseFloat(document.getElementById('visa_charges') ? document.getElementById('visa_charges').value : 0) || 0;
            const flight = parseFloat(document.getElementById('flight_charges') ? document.getElementById('flight_charges').value : 0) || 0;
            const other = parseFloat(document.getElementById('other_charges') ? document.getElementById('other_charges').value : 0) || 0;
            const discount = parseFloat(document.getElementById('discount') ? document.getElementById('discount').value : 0) || 0;
            const additional = parseFloat(document.getElementById('additional_services_amount') ? document.getElementById('additional_services_amount').value : 0) || 0;
            const received = parseFloat(document.getElementById('total_received') ? document.getElementById('total_received').value : 0) || 0;

            const pkgTotal = pkg * pax;
            const total = pkgTotal + visa + flight + other + additional - discount;
            const balance = total - received;

            if (document.getElementById('total_amount')) document.getElementById('total_amount').value = total.toFixed(2);
            if (document.getElementById('balance')) document.getElementById('balance').value = balance.toFixed(2);

            if (document.getElementById('bar_pax')) document.getElementById('bar_pax').textContent = pax;
            if (document.getElementById('bar_adult')) document.getElementById('bar_adult').textContent = pkgTotal.toFixed(2);
            if (document.getElementById('bar_visa')) document.getElementById('bar_visa').textContent = visa.toFixed(2);
            if (document.getElementById('bar_flight')) document.getElementById('bar_flight').textContent = flight.toFixed(2);
            if (document.getElementById('bar_total')) document.getElementById('bar_total').textContent = total.toFixed(2);
            if (document.getElementById('bar_balance')) document.getElementById('bar_balance').textContent = balance.toFixed(2);

            if (document.getElementById('sum_pax')) document.getElementById('sum_pax').textContent = pax;
            if (document.getElementById('sum_pkg')) document.getElementById('sum_pkg').textContent = pkgTotal.toFixed(2);
            if (document.getElementById('sum_visa')) document.getElementById('sum_visa').textContent = visa.toFixed(2);
            if (document.getElementById('sum_flight')) document.getElementById('sum_flight').textContent = flight.toFixed(2);
            if (document.getElementById('sum_other')) document.getElementById('sum_other').textContent = (other + additional - discount).toFixed(2);
            if (document.getElementById('sum_total')) document.getElementById('sum_total').textContent = total.toFixed(2);
        }

        document.querySelectorAll('.calc').forEach(el => el.addEventListener('input', calcTotal));
        document.getElementById('no_of_pax').addEventListener('input', calcTotal);

        syncFlightPersons();
        syncVisas();

        // ---- Hotel Check In / Check Out / Nights Auto Calculation ----
        (function() {
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

                if (isCheckIn || isCheckOut) {
                    const todayStr = new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0];
                    if (target.value && target.value < todayStr) {
                        alert("You cannot select a past date.");
                        target.value = '';
                        return;
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
            }

            document.addEventListener('input', function(e) {
                handleHotelDateCalc(e.target);
            });
            document.addEventListener('change', function(e) {
                handleHotelDateCalc(e.target);
            });
        })();

      $(document).ready(function() {
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        // Initialize Summernote only when the tab is shown
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
        
        // If tab is active by default
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
    </script>
@endsection
