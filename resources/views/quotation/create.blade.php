@extends('layout.master')
@section('title', 'Create Quotation')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Summernote Lite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        .quotation-nav-tabs .nav-link {
            font-weight: 600;
            color: #0f535e;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 20px;
            font-size: 14px;
            transition: all 0.2s ease-in-out;
            background: transparent;
        }
        .quotation-nav-tabs .nav-link:hover {
            color: #187280;
            border-bottom-color: #d0e3e6;
        }
        .quotation-nav-tabs .nav-link.active {
            color: #0f535e;
            border-bottom-color: #0f535e;
            background-color: transparent;
        }
        .card-repeater-item {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .meal-plan-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 18px;
            margin-top: 15px;
        }
        .note-editor.note-frame {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .note-editor.note-frame .note-toolbar {
            background-color: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 8px 12px !important;
        }
        .note-editor.note-frame .note-btn {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #1e293b !important;
            font-size: 13px !important;
            padding: 5px 9px !important;
            border-radius: 4px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .note-editor.note-frame .note-btn:hover {
            background: #e2e8f0 !important;
            color: #0f535e !important;
        }
        .note-editor.note-frame .note-editable {
            min-height: 200px !important;
            background-color: #ffffff;
            font-size: 14px;
            line-height: 1.6;
            color: #1e293b;
            padding: 16px !important;
        }
        .summary-card {
            background: linear-gradient(135deg, #0f535e, #187280);
            color: #ffffff;
            border-radius: 12px;
            padding: 20px;
        }
    </style>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                    <div>
                        <h4 class="fs-18 fw-semibold mb-1">Create VIP Quotation</h4>
                        <p class="text-muted fs-13 mb-0">Customized accommodations, transport sectors, ziyarat, passenger visas, ROE calculations & final summary.</p>
                    </div>
                    <div>
                        <a href="{{ route('quotation.index') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Quotations
                        </a>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Main Quotation Form -->
                <form action="{{ route('quotation.store') }}" method="POST" id="quotationForm">
                    @csrf

                    <!-- Lead / Client Information Card -->
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    @if (isset($selectedLead) && $selectedLead)
                                        <label class="form-label fw-semibold fs-13 mb-1 text-primary">
                                            <i class="mdi mdi-link-variant me-1"></i> Linked Lead
                                        </label>
                                        <input type="hidden" name="lead_id" value="{{ $selectedLead->id }}">
                                        <div class="p-1 px-2 border rounded bg-light d-flex align-items-center justify-content-between" style="min-height: 31px;">
                                            <span class="fs-13 fw-semibold text-primary text-truncate" title="Lead #{{ $selectedLead->id }} - {{ $selectedLead->contact_person }}">
                                                Lead #{{ $selectedLead->id }} - {{ $selectedLead->contact_person ?? 'Unnamed' }}
                                            </span>
                                            <span class="badge bg-success fs-10"><i class="mdi mdi-lock-outline"></i> Locked</span>
                                        </div>
                                    @else
                                        <label class="form-label fw-semibold fs-13 mb-1">Select Lead</label>
                                        <select name="lead_id" id="lead_select" class="form-select form-select-sm">
                                            <option value="">-- Standalone Quotation (No Lead) --</option>
                                            @foreach ($leads as $l)
                                                <option value="{{ $l->id }}"
                                                    data-name="{{ $l->contact_person }}" data-phone="{{ $l->phone }}" data-email="{{ $l->email }}" data-pax="{{ $l->number_of_pax ?? 1 }}">
                                                    Lead #{{ $l->id }} - {{ $l->contact_person ?? 'Unnamed' }} ({{ $l->phone ?? 'No Phone' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold fs-13 mb-1">Quotation Title / Ref</label>
                                    <input type="text" name="quotation_title" class="form-control form-control-sm"
                                        placeholder="e.g. VIP Umrah Package with Kaaba View"
                                        value="{{ (isset($selectedLead) && $selectedLead) ? ($selectedLead->contact_person . ' - VIP Quotation') : 'Quotation #' . date('ymd') . '-' . rand(100, 999) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Client Name</label>
                                    <input type="text" name="client_name" id="client_name_input" class="form-control form-control-sm"
                                        placeholder="Client / Passenger Name"
                                        value="{{ $selectedLead->contact_person ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Phone Number</label>
                                    <input type="text" name="client_phone" id="client_phone_input" class="form-control form-control-sm"
                                        placeholder="Contact Phone"
                                        value="{{ $selectedLead->phone ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Total Pax</label>
                                    <input type="number" name="total_pax" id="master_total_pax" class="form-control form-control-sm"
                                        min="1" value="{{ $selectedLead->number_of_pax ?? 1 }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Navigation Tabs -->
                    <div class="card mb-4">
                        <div class="card-header bg-white p-0 border-bottom">
                            <ul class="nav quotation-nav-tabs" id="quotationTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="accommodation-tab" data-bs-toggle="tab"
                                        data-bs-target="#accommodation-pane" type="button" role="tab">
                                        <i class="mdi mdi-hotel me-1"></i> 1. Accommodation
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="transfers-tab" data-bs-toggle="tab"
                                        data-bs-target="#transfers-pane" type="button" role="tab">
                                        <i class="mdi mdi-car-multiple me-1"></i> 2. Transfers
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tours-tab" data-bs-toggle="tab"
                                        data-bs-target="#tours-pane" type="button" role="tab">
                                        <i class="mdi mdi-map-marker-distance me-1"></i> 3. Tours & Ziyarat
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="flights-tab" data-bs-toggle="tab"
                                        data-bs-target="#flights-pane" type="button" role="tab">
                                        <i class="mdi mdi-train-car me-1"></i> 4. Domestic Flight / Train
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="visa-tab" data-bs-toggle="tab"
                                        data-bs-target="#visa-pane" type="button" role="tab">
                                        <i class="mdi mdi-passport me-1"></i> 5. Visa Charges
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="psf-tab" data-bs-toggle="tab"
                                        data-bs-target="#psf-pane" type="button" role="tab">
                                        <i class="mdi mdi-cash-multiple me-1"></i> 6. PSF
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="summary-tab" data-bs-toggle="tab"
                                        data-bs-target="#summary-pane" type="button" role="tab">
                                        <i class="mdi mdi-chart-box-outline me-1"></i> 7. Full Summary
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="quotationTabContent">

                                <!-- ========================================================================= -->
                                <!-- TAB 1: ACCOMMODATION (Hotel from CRUD + Room View + Conf # + Separate Meals) -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade show active" id="accommodation-pane" role="tabpanel">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fs-16 fw-semibold text-primary mb-0">Hotel Accommodations & Room Inventory</h5>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnAddNewAccommodation">
                                            <i class="mdi mdi-plus-circle me-1"></i> + Add New Accommodation
                                        </button>
                                    </div>

                                    <!-- Live Hotel Stock Summary Alert -->
                                    <div id="liveHotelStockBar" class="alert alert-info border-info p-2 px-3 mb-3 d-none fs-13">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="mdi mdi-information-outline me-1 fs-15 text-primary"></i>
                                                <strong>Live Hotel Stock: </strong>
                                                <span id="liveHotelStockContent">Loading...</span>
                                            </div>
                                            <a href="{{ route('room-inventory.index') }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                                <i class="mdi mdi-open-in-new me-1"></i> View Full Inventory
                                            </a>
                                        </div>
                                    </div>

                                    <div id="accommodationRepeaterContainer">

                                        <!-- Accommodation Card #1 -->
                                        <div class="card-repeater-item accommodation-item" data-index="0">
                                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                <span class="badge bg-primary fs-12">Accommodation Stay #1</span>
                                            </div>

                                            <!-- Row 1: Dates & City -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Check In</label>
                                                    <input type="text" name="accommodations[0][check_in]"
                                                        class="form-control form-control-sm flatpickr-checkin"
                                                        placeholder="mm/dd/yyyy">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Check Out</label>
                                                    <input type="text" name="accommodations[0][check_out]"
                                                        class="form-control form-control-sm flatpickr-checkout"
                                                        placeholder="mm/dd/yyyy">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Number Of Night</label>
                                                    <input type="number" name="accommodations[0][number_of_nights]"
                                                        class="form-control form-control-sm bg-light acc-nights"
                                                        placeholder="0" min="0" value="">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">City Name</label>
                                                    <select name="accommodations[0][city]" class="form-select form-select-sm acc-city-select">
                                                        <option value="Makkah">Makkah</option>
                                                        <option value="Madinah">Madinah</option>
                                                        <option value="Jeddah">Jeddah</option>
                                                        <option value="Riyadh">Riyadh</option>
                                                        <option value="Taif">Taif</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Row 2: Hotel (From Hotel CRUD), Room Type, Room View & Confirmation # -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-hotel me-1"></i> Hotel Name (Active)
                                                    </label>
                                                    <select name="accommodations[0][hotel_name]" class="form-select form-select-sm hotel-select">
                                                        <option value="" data-id="">Select Hotel</option>
                                                        @foreach ($hotels as $hotel)
                                                            <option value="{{ $hotel->name }}" data-id="{{ $hotel->id }}" data-city="{{ $hotel->place ? (is_object($hotel->place) ? $hotel->place->value : $hotel->place) : '' }}">
                                                                {{ $hotel->name }} @if($hotel->place) ({{ is_object($hotel->place) ? $hotel->place->value : $hotel->place }}) @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="accommodations[0][hotel_id]" class="acc-hotel-id" value="">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Room Type</label>
                                                    <select name="accommodations[0][room_type]" class="form-select form-select-sm acc-room-type">
                                                        <option value="Double">Double (2 Bed)</option>
                                                        <option value="Triple">Triple (3 Bed)</option>
                                                        <option value="Quad">Quad (4 Bed)</option>
                                                        <option value="Quint">Quint (5 Bed)</option>
                                                        <option value="Single">Single (1 Bed)</option>
                                                        <option value="Sharing">Sharing</option>
                                                        <option value="Suite">Suite / Family</option>
                                                    </select>
                                                </div>
                                                <!-- Client Requirement 1: Room View -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-eye-outline me-1"></i> Room View
                                                    </label>
                                                    <select name="accommodations[0][room_view]" class="form-select form-select-sm border-primary">
                                                        <option value="City View">City View</option>
                                                        <option value="Haram View">Haram View</option>
                                                        <option value="Kaaba View">Kaaba View</option>
                                                        <option value="Partial Haram View">Partial Haram View</option>
                                                        <option value="Partial Kaaba View">Partial Kaaba View</option>
                                                        <option value="Land View (Int.)">Land View (International)</option>
                                                        <option value="Sea View (Int.)">Sea View (International)</option>
                                                        <option value="Courtyard View">Courtyard View</option>
                                                    </select>
                                                </div>
                                                <!-- Client Requirement 1: Confirmation # -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-pound me-1"></i> Confirmation #
                                                    </label>
                                                    <input type="text" name="accommodations[0][confirmation_number]"
                                                        class="form-control form-control-sm border-primary"
                                                        placeholder="Hotel Booking Ref / Conf #">
                                                </div>
                                            </div>

                                            <!-- Row 3: Hotel Meal Plan, Rooms, Rates & Total Flag -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fs-13 fw-semibold">Meal Plan</label>
                                                    <select name="accommodations[0][meal_plan]" class="form-select form-select-sm">
                                                        <option value="Room Only">Room Only (RO)</option>
                                                        <option value="Bed & Breakfast">Bed & Breakfast (BB)</option>
                                                        <option value="Half Board">Half Board (HB)</option>
                                                        <option value="Full Board">Full Board (FB)</option>
                                                        <option value="Suhoor">Suhoor Included</option>
                                                        <option value="Iftar Dinner">Iftar Dinner Included</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label fs-13 fw-semibold mb-0 acc-rooms-label">No Of Room</label>
                                                        <span class="acc-stock-badge badge bg-soft-secondary text-secondary fs-10">Check Stock</span>
                                                    </div>
                                                    <input type="number" name="accommodations[0][no_of_rooms]"
                                                        class="form-control form-control-sm acc-rooms" min="0" placeholder="0" value="">
                                                    <div class="acc-stock-warning text-danger fs-11 mt-1 d-none"></div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-13 fw-semibold">Per Night Rate</label>
                                                    <input type="number" step="any" name="accommodations[0][per_night_rate]"
                                                        class="form-control form-control-sm acc-per-night" value="0">
                                                </div>
                                            </div>

                                            <!-- Row 3B: Sharing Bed Details (Active when Room Type is Sharing) -->
                                            <div class="acc-sharing-beds-box row g-2 mb-3 p-2 border rounded bg-light d-none">
                                                <div class="col-md-12 mb-1">
                                                    <span class="fs-12 fw-bold text-primary"><i class="mdi mdi-bed-empty me-1"></i> Sharing Bed Allocation (Gender Wise)</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-12 fw-semibold text-primary"><i class="mdi mdi-gender-male me-1"></i> Male Beds</label>
                                                    <input type="number" name="accommodations[0][male_beds]" class="form-control form-control-sm acc-male-beds text-center border-primary" min="0" placeholder="0" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-12 fw-semibold text-danger"><i class="mdi mdi-gender-female me-1"></i> Female Beds</label>
                                                    <input type="number" name="accommodations[0][female_beds]" class="form-control form-control-sm acc-female-beds text-center border-danger" min="0" placeholder="0" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-12 fw-semibold text-dark"><i class="mdi mdi-counter me-1"></i> Total Beds</label>
                                                    <input type="number" name="accommodations[0][no_of_beds]" class="form-control form-control-sm acc-total-beds text-center fw-bold bg-light" min="0" placeholder="0" value="0" readonly>
                                                </div>
                                                <div class="acc-sharing-warning text-danger fs-11 mt-1 col-md-12 d-none"></div>
                                            </div>

                                            <!-- Row 4A: Costing, Cost ROE & Supplier -->
                                            <div class="row g-2 mb-2 p-2 border rounded bg-soft-light align-items-center">
                                                <div class="col-md-2">
                                                    <span class="fs-12 fw-bold text-danger text-uppercase d-block mb-1"><i class="mdi mdi-cash-minus me-1"></i> Cost Side:</span>
                                                    <select name="accommodations[0][cost_currency]" class="form-select form-select-sm acc-cost-currency">
                                                        <option value="PKR" selected>PKR</option>
                                                        <option value="SAR">SAR</option>
                                                        <option value="USD">USD</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-12 fw-semibold text-danger mb-1"><i class="mdi mdi-swap-horizontal me-1"></i> Cost ROE</label>
                                                    <input type="number" step="0.0001" name="accommodations[0][cost_exchange_rate]"
                                                        class="form-control form-control-sm border-danger acc-cost-roe bg-light" readonly placeholder="1.00" value="1">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-12 fw-semibold text-danger mb-1">Cost Amount (Foreign)</label>
                                                    <input type="number" step="any" name="accommodations[0][cost_amount]"
                                                        class="form-control form-control-sm acc-cost" value="0">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-12 fw-semibold text-danger mb-1">Cost PKR</label>
                                                    <div class="fs-13 fw-bold text-danger pt-1 acc-cost-pkr-badge">PKR 0</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-12 fw-semibold text-danger mb-1"><i class="mdi mdi-truck-delivery-outline me-1"></i> Supplier / Vendor</label>
                                                    <select name="accommodations[0][supplier_id]" class="form-select form-select-sm">
                                                        <option value="">-- Select Supplier / Vendor --</option>
                                                        @foreach ($clients as $client)
                                                            @if($client->type == 'vendor' || $client->type == 'client')
                                                                <option value="{{ $client->id }}">{{ $client->name }} ({{ ucfirst($client->type) }}{{ $client->company_name ? ' - ' . $client->company_name : '' }})</option>
                                                            @endif
                                                        @endforeach
                                                        @foreach ($companies as $comp)
                                                            <option value="{{ $comp->id }}">{{ $comp->company_name }} (Company)</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Row 4B: Selling, Selling ROE & Margin -->
                                            <div class="row g-2 mb-3 p-2 border rounded bg-soft-success align-items-center">
                                                <div class="col-md-2">
                                                    <span class="fs-12 fw-bold text-success text-uppercase d-block mb-1"><i class="mdi mdi-cash-plus me-1"></i> Sale Side:</span>
                                                    <select name="accommodations[0][selling_currency]" class="form-select form-select-sm acc-selling-currency">
                                                        <option value="PKR" selected>PKR</option>
                                                        <option value="SAR">SAR</option>
                                                        <option value="USD">USD</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-12 fw-semibold text-success mb-1"><i class="mdi mdi-swap-horizontal me-1"></i> Selling ROE</label>
                                                    <input type="number" step="0.0001" name="accommodations[0][selling_exchange_rate]"
                                                        class="form-control form-control-sm border-success acc-selling-roe bg-light" readonly placeholder="1.00" value="1">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-12 fw-semibold text-success mb-1">Selling Amount (Foreign)</label>
                                                    <input type="number" step="any" name="accommodations[0][selling_amount]"
                                                        class="form-control form-control-sm acc-selling" value="0">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-12 fw-semibold text-success mb-1">Selling PKR</label>
                                                    <div class="fs-13 fw-bold text-success pt-1 acc-sale-pkr-badge">PKR 0</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-12 fw-semibold text-primary mb-1">Estimated Stay Profit</label>
                                                    <div class="fs-13 fw-bold text-primary pt-1 acc-profit-pkr-badge">PKR 0</div>
                                                </div>
                                            </div>

                                            <!-- Row 5: Deadlines -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fs-13 fw-semibold">Cancellation Deadline</label>
                                                    <input type="text" name="accommodations[0][cancellation_deadline]"
                                                        class="form-control form-control-sm flatpickr-date"
                                                        placeholder="mm/dd/yyyy">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fs-13 fw-semibold">Finalization Date</label>
                                                    <input type="text" name="accommodations[0][finalization_date]"
                                                        class="form-control form-control-sm flatpickr-date"
                                                        placeholder="mm/dd/yyyy">
                                                </div>
                                            </div>

                                            <!-- Client Requirement 3: Separate Meal Plan Facility (Cost/Sale Independent) -->
                                            <div class="meal-plan-card">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <h6 class="fs-14 fw-semibold text-dark mb-0">
                                                            <i class="mdi mdi-silverware-fork-knife text-warning me-1"></i>
                                                            Separate Meal Plans Facility (Cost / Sale independent of Hotel)
                                                        </h6>
                                                        <small class="text-muted">Option to add external meal plan facility (Cost & Sale separately).</small>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-add-meal" data-acc="0">
                                                        <i class="mdi mdi-plus me-1"></i> + Add Meal Plan
                                                    </button>
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm align-middle mb-0 bg-white" id="mealTable_0">
                                                        <thead class="table-light fs-12">
                                                            <tr>
                                                                <th>Meal Type</th>
                                                                <th>City</th>
                                                                <th>Supplier</th>
                                                                <th style="width: 80px;">Pax</th>
                                                                <th style="width: 80px;">Days</th>
                                                                <th style="width: 100px;">Ex. Rate</th>
                                                                <th style="width: 110px;">Cost (SAR)</th>
                                                                <th style="width: 110px;">Sale (SAR)</th>
                                                                <th>Total Cost (PKR)</th>
                                                                <th>Total Sale (PKR)</th>
                                                                <th style="width: 40px;"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="meal-tbody" id="mealTbody_0">
                                                            <tr>
                                                                <td>
                                                                    <select name="accommodations[0][meals][0][type]" class="form-select form-select-sm">
                                                                        <option value="Breakfast">Breakfast</option>
                                                                        <option value="Lunch">Lunch</option>
                                                                        <option value="Dinner" selected>Dinner</option>
                                                                        <option value="Half Board">Half Board</option>
                                                                        <option value="Full Board">Full Board</option>
                                                                        <option value="Suhoor">Suhoor</option>
                                                                        <option value="Iftar">Iftar Buffet</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="accommodations[0][meals][0][city]" class="form-control form-control-sm" placeholder="City" value="Makkah">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="accommodations[0][meals][0][provider]" class="form-control form-control-sm" placeholder="Supplier">
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="accommodations[0][meals][0][pax]" class="form-control form-control-sm meal-pax" min="0" placeholder="0" value="">
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="accommodations[0][meals][0][days]" class="form-control form-control-sm meal-days" min="0" placeholder="0" value="">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" name="accommodations[0][meals][0][ex_rate]" class="form-control form-control-sm meal-exrate" placeholder="Ex. Rate" value="">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="any" name="accommodations[0][meals][0][cost]" class="form-control form-control-sm meal-cost" value="0">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="any" name="accommodations[0][meals][0][sale]" class="form-control form-control-sm meal-sale" value="0">
                                                                </td>
                                                                <td class="meal-total-cost-pkr fw-semibold text-danger fs-12">0</td>
                                                                <td class="meal-total-sale-pkr fw-semibold text-success fs-12">0</td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-meal"><i class="mdi mdi-trash-can-outline fs-16"></i></button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-success" id="btnAddNewAccommodationBottom">
                                            <i class="mdi mdi-plus-circle me-1"></i> + Add New Accommodation
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab-btn" data-target="#transfers-tab">
                                            Next: Transfers <i class="mdi mdi-arrow-right ms-1"></i>
                                        </button>
                                    </div>

                                </div>

                                <!-- ========================================================================= -->
                                <!-- TAB 2: TRANSFERS (Sector from Route CRUD + Vehicle CRUD + ROE Purchase) -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade" id="transfers-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="fs-16 fw-semibold text-primary mb-0">Vehicle Transfers & Ground Transportation</h5>
                                            <small class="text-muted">Pick Sector route from Routes CRUD, vehicle from Vehicles CRUD, and configure individual ROE / Ex. Rate.</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnAddTransfer">
                                            <i class="mdi mdi-plus-circle me-1"></i> + Add Transfer
                                        </button>
                                    </div>

                                    <div id="transfersContainer">
                                        <div class="card-repeater-item transfer-item">
                                            <div class="row g-3">
                                                <!-- Client Requirement 1: Sector from CRUD -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-routes me-1"></i> Sector Route
                                                    </label>
                                                    <select name="transfers[0][sector]" class="form-select form-select-sm border-primary">
                                                        <option value="">Select Sector Route</option>
                                                        @foreach ($routes as $r)
                                                            <option value="{{ $r->start_place }} - {{ $r->end_place }}">{{ $r->start_place }} - {{ $r->end_place }}</option>
                                                        @endforeach
                                                        @foreach ($travelRoutes as $tr)
                                                            <option value="{{ $tr->name }}">{{ $tr->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- Vehicle Type from Vehicle CRUD -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-car me-1"></i> Vehicle Type
                                                    </label>
                                                    <select name="transfers[0][vehicle_type]" class="form-select form-select-sm">
                                                        <option value="">Select Vehicle Type</option>
                                                        @foreach ($vehicles as $v)
                                                            <option value="{{ $v->vehicle_type }}">{{ $v->vehicle_type }} ({{ $v->brand_name }} {{ $v->model_year }})</option>
                                                        @endforeach
                                                        <option value="Sedan Car (Camry/Sonata)">Sedan Car (Camry/Sonata)</option>
                                                        <option value="GMC / Yukon (SUV)">GMC / Yukon (SUV)</option>
                                                        <option value="Toyota Hiace (10 Seater)">Toyota Hiace (10 Seater)</option>
                                                        <option value="Toyota Coaster (20-30 Seater)">Toyota Coaster (20-30 Seater)</option>
                                                        <option value="Luxury Bus (49 Seater)">Luxury Bus (49 Seater)</option>
                                                        <option value="Private VIP Luxury">Private VIP Luxury</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold">Date & Time</label>
                                                    <input type="text" name="transfers[0][date_time]" class="form-control form-control-sm flatpickr-datetime"
                                                        placeholder="Select Date & Time">
                                                </div>
                                                <!-- Client Requirement 3: Quantity (Number of Vehicles) -->
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-car-multiple me-1"></i> No. of Vehicles
                                                    </label>
                                                    <input type="number" name="transfers[0][quantity]" class="form-control form-control-sm border-primary trans-qty" min="0" placeholder="0" value="">
                                                </div>
                                                <!-- Client Requirement 2: ROE (Ex. Rate in purchase) -->
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-currency-usd me-1"></i> ROE / Ex. Rate
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <select name="transfers[0][currency]" class="form-select trans-currency" style="max-width: 75px;">
                                                            <option value="PKR" selected>PKR</option>
                                                            <option value="SAR">SAR</option>
                                                            <option value="USD">USD</option>
                                                        </select>
                                                        <input type="number" step="0.01" name="transfers[0][ex_rate]" class="form-control trans-exrate bg-light" placeholder="1.00" value="1" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Cost (SAR / per vehicle)</label>
                                                    <input type="number" step="any" name="transfers[0][cost]" class="form-control form-control-sm trans-cost" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Selling (SAR / per vehicle)</label>
                                                    <input type="number" step="any" name="transfers[0][sale]" class="form-control form-control-sm trans-sale" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                                                    <select name="transfers[0][supplier_id]" class="form-select form-select-sm">
                                                        <option value="">-- Select Supplier / Vendor --</option>
                                                        @foreach ($clients as $client)
                                                            @if($client->type == 'vendor' || $client->type == 'client')
                                                                <option value="{{ $client->id }}">{{ $client->name }} ({{ ucfirst($client->type) }}{{ $client->company_name ? ' - ' . $client->company_name : '' }})</option>
                                                            @endif
                                                        @endforeach
                                                        @foreach ($companies as $comp)
                                                            <option value="{{ $comp->id }}">{{ $comp->company_name }} (Company)</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Confirmation #</label>
                                                    <input type="text" name="transfers[0][confirmation_number]" class="form-control form-control-sm" placeholder="Confirmation #">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-secondary prev-tab-btn" data-target="#accommodation-tab">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back: Accommodation
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab-btn" data-target="#tours-tab">
                                            Next: Tours & Ziyarat <i class="mdi mdi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- ========================================================================= -->
                                <!-- TAB 3: TOURS AND SIGHTSEEING (Clean Custom Input + ROE Purchase + Quantity) -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade" id="tours-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="fs-16 fw-semibold text-primary mb-0">Ziyarat, Tours & Sightseeing</h5>
                                            <small class="text-muted">Type custom tour names, sector sites, vehicle quantity, and supplier ROE.</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnAddTour">
                                            <i class="mdi mdi-plus-circle me-1"></i> + Add Tour / Ziyarat
                                        </button>
                                    </div>

                                    <div id="toursContainer">
                                        <div class="card-repeater-item tour-item">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Tour / Ziyarat Name</label>
                                                    <input type="text" name="tours[0][name]" class="form-control form-control-sm"
                                                        placeholder="e.g. Makkah Holy Places Ziyarat, Taif Tour">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold">City</label>
                                                    <select name="tours[0][city]" class="form-select form-select-sm">
                                                        <option value="Makkah">Makkah</option>
                                                        <option value="Madinah">Madinah</option>
                                                        <option value="Taif">Taif</option>
                                                        <option value="Jeddah">Jeddah</option>
                                                        <option value="Badr">Badr</option>
                                                    </select>
                                                </div>
                                                <!-- Client Requirement: Sector in Tours -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-routes me-1"></i> Sector / Sites
                                                    </label>
                                                    <input type="text" name="tours[0][sector]" class="form-control form-control-sm border-primary"
                                                        placeholder="e.g. Jabal Al Noor, Thawr, Arafat, Mina">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold">Tour Date</label>
                                                    <input type="text" name="tours[0][date]" class="form-control form-control-sm flatpickr-date" placeholder="Date">
                                                </div>
                                                <!-- Client Requirement: Quantity (Vehicles / Pax) -->
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-car me-1"></i> Quantity / Pax
                                                    </label>
                                                    <input type="number" name="tours[0][quantity]" class="form-control form-control-sm border-primary tour-qty" min="0" placeholder="0" value="">
                                                </div>

                                                <!-- Client Requirement: ROE / Ex Rate in Tours -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-currency-usd me-1"></i> ROE / Ex. Rate
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <select name="tours[0][currency]" class="form-select tour-currency" style="max-width: 75px;">
                                                            <option value="PKR" selected>PKR</option>
                                                            <option value="SAR">SAR</option>
                                                            <option value="USD">USD</option>
                                                        </select>
                                                        <input type="number" step="0.01" name="tours[0][ex_rate]" class="form-control tour-exrate bg-light" placeholder="1.00" value="1" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Cost Amount</label>
                                                    <input type="number" step="any" name="tours[0][cost]" class="form-control form-control-sm tour-cost" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Selling Amount</label>
                                                    <input type="number" step="any" name="tours[0][sale]" class="form-control form-control-sm tour-sale" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                                                    <select name="tours[0][supplier_id]" class="form-select form-select-sm">
                                                        <option value="">-- Select Supplier / Vendor --</option>
                                                        @foreach ($clients as $client)
                                                            @if($client->type == 'vendor' || $client->type == 'client')
                                                                <option value="{{ $client->id }}">{{ $client->name }} ({{ ucfirst($client->type) }}{{ $client->company_name ? ' - ' . $client->company_name : '' }})</option>
                                                            @endif
                                                        @endforeach
                                                        @foreach ($companies as $comp)
                                                            <option value="{{ $comp->id }}">{{ $comp->company_name }} (Company)</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-secondary prev-tab-btn" data-target="#transfers-tab">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back: Transfers
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab-btn" data-target="#flights-tab">
                                            Next: Flight / Train <i class="mdi mdi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- ========================================================================= -->
                                <!-- TAB 4: DOMESTIC FLIGHT / TRAIN (Text Input Mode + Clean Placeholders) -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade" id="flights-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fs-16 fw-semibold text-primary mb-0">Haramain High Speed Train & Domestic Flights</h5>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnAddTrain">
                                            <i class="mdi mdi-plus-circle me-1"></i> + Add Journey
                                        </button>
                                    </div>

                                    <div id="trainsContainer">
                                        <div class="card-repeater-item train-item">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">Transport Mode</label>
                                                    <input type="text" name="trains[0][type]" class="form-control form-control-sm"
                                                        placeholder="e.g. Haramain High Speed Train, Saudia Flight, Private Coach">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">From Station / Airport</label>
                                                    <input type="text" name="trains[0][from]" class="form-control form-control-sm"
                                                        placeholder="e.g. Makkah Station / Jeddah Airport">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">To Station / Airport</label>
                                                    <input type="text" name="trains[0][to]" class="form-control form-control-sm"
                                                        placeholder="e.g. Madinah Station / Riyadh Airport">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Travel Date & Time</label>
                                                    <input type="text" name="trains[0][date_time]" class="form-control form-control-sm flatpickr-datetime" placeholder="Date & Time">
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold">Class</label>
                                                    <select name="trains[0][class]" class="form-select form-select-sm">
                                                        <option value="Economy">Economy Class</option>
                                                        <option value="Business">Business Class</option>
                                                        <option value="First">First Class</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold">Tickets (Pax)</label>
                                                    <input type="number" name="trains[0][pax]" class="form-control form-control-sm train-pax" min="0" placeholder="0" value="">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE</label>
                                                    <div class="input-group input-group-sm">
                                                        <select name="trains[0][currency]" class="form-select train-currency" style="max-width: 65px;">
                                                            <option value="PKR" selected>PKR</option>
                                                            <option value="SAR">SAR</option>
                                                            <option value="USD">USD</option>
                                                        </select>
                                                        <input type="number" step="0.01" name="trains[0][ex_rate]" class="form-control form-control-sm train-exrate bg-light" placeholder="1.00" value="1" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Cost per Ticket</label>
                                                    <input type="number" step="any" name="trains[0][cost]" class="form-control form-control-sm train-cost" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Selling per Ticket</label>
                                                    <input type="number" step="any" name="trains[0][sale]" class="form-control form-control-sm train-sale" value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-secondary prev-tab-btn" data-target="#tours-tab">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back: Tours
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab-btn" data-target="#visa-tab">
                                            Next: Visa Charges <i class="mdi mdi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- ========================================================================= -->
                                <!-- TAB 5: VISA CHARGES (Passenger Name + Visa Type Input + Gender + ROE) -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade" id="visa-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="fs-16 fw-semibold text-primary mb-0">Visa Charges & Passenger Details</h5>
                                            <small class="text-muted">Add individual passenger names, custom visa category inputs, gender, and supplier ROE.</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnAddVisa">
                                            <i class="mdi mdi-plus-circle me-1"></i> + Add Passenger Visa
                                        </button>
                                    </div>

                                    <div id="visasContainer">
                                        <div class="card-repeater-item visa-item">
                                            <div class="row g-3">
                                                <!-- Passenger Name -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-account me-1"></i> Passenger Name
                                                    </label>
                                                    <input type="text" name="visas[0][passenger_name]" class="form-control form-control-sm border-primary"
                                                        placeholder="Full Name as on Passport">
                                                </div>
                                                <!-- Visa Pax Type -->
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-account-group me-1"></i> Pax Type
                                                    </label>
                                                    <select name="visas[0][passenger_type]" class="form-select form-select-sm border-primary">
                                                        <option value="Adult">Adult</option>
                                                        <option value="Child">Child (2-11 Yrs)</option>
                                                        <option value="Infant">Infant (Under 2 Yrs)</option>
                                                    </select>
                                                </div>
                                                <!-- Gender -->
                                                <div class="col-md-2">
                                                    <label class="form-label fs-13 fw-semibold text-primary">
                                                        <i class="mdi mdi-gender-male-female me-1"></i> Gender
                                                    </label>
                                                    <select name="visas[0][gender]" class="form-select form-select-sm border-primary">
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                    </select>
                                                </div>
                                                <!-- Visa Category Input -->
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary">Visa Category</label>
                                                    <input type="text" name="visas[0][type]" class="form-control form-control-sm"
                                                        placeholder="e.g. Umrah Tourist E-Visa, Visit Visa, Standard Visa">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE / Ex. Rate</label>
                                                    <div class="input-group input-group-sm">
                                                        <select name="visas[0][currency]" class="form-select visa-currency" style="max-width: 75px;">
                                                            <option value="PKR" selected>PKR</option>
                                                            <option value="SAR">SAR</option>
                                                            <option value="USD">USD</option>
                                                        </select>
                                                        <input type="number" step="0.01" name="visas[0][ex_rate]" class="form-control form-control-sm border-primary visa-exrate bg-light" placeholder="1.00" value="1" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Passport Number</label>
                                                    <input type="text" name="visas[0][passport_number]" class="form-control form-control-sm" placeholder="Passport #">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Cost per Visa</label>
                                                    <input type="number" step="any" name="visas[0][cost]" class="form-control form-control-sm visa-cost" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Selling per Visa</label>
                                                    <input type="number" step="any" name="visas[0][sale]" class="form-control form-control-sm visa-sale" value="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                                                    <select name="visas[0][supplier_id]" class="form-select form-select-sm">
                                                        <option value="">-- Select Supplier / Vendor --</option>
                                                        @foreach ($clients as $client)
                                                            @if($client->type == 'vendor' || $client->type == 'client')
                                                                <option value="{{ $client->id }}">{{ $client->name }} ({{ ucfirst($client->type) }}{{ $client->company_name ? ' - ' . $client->company_name : '' }})</option>
                                                            @endif
                                                        @endforeach
                                                        @foreach ($companies as $comp)
                                                            <option value="{{ $comp->id }}">{{ $comp->company_name }} (Company)</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-secondary prev-tab-btn" data-target="#flights-tab">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back: Flights/Train
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab-btn" data-target="#psf-tab">
                                            Next: 6. PSF <i class="mdi mdi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- ========================================================================= -->
                                <!-- TAB 6: PSF - DIRECT AMOUNT ENTRY -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade" id="psf-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="fs-16 fw-semibold text-primary mb-0">6. PSF</h5>
                                            <small class="text-muted">Enter PSF amount in PKR.</small>
                                        </div>
                                    </div>

                                    <div class="card border mb-3 shadow-none">
                                        <div class="card-header bg-light py-2 border-bottom">
                                            <h6 class="fs-14 fw-bold text-dark mb-0">
                                                <i class="mdi mdi-cash-multiple text-primary me-1"></i> PSF
                                            </h6>
                                        </div>
                                        <div class="card-body p-4">
                                            <input type="hidden" name="psf_description" value="PSF">
                                            <input type="hidden" name="psf_cost_pkr" id="psfCostAmt" value="0">
                                            <input type="hidden" name="psf_supplier_amount" id="psfSupplierAmt" value="0">

                                            <div class="row justify-content-center py-3">
                                                <div class="col-md-6 col-lg-5 text-center">
                                                    <label class="form-label fs-14 fw-semibold text-primary mb-2">
                                                        <i class="mdi mdi-cash-multiple me-1"></i> PSF Amount (PKR)
                                                    </label>
                                                    <div class="input-group input-group-lg shadow-sm">
                                                        <span class="input-group-text bg-primary text-white fw-bold">PKR</span>
                                                        <input type="number" step="any" min="0" name="psf_selling_pkr" id="psfSellingAmt"
                                                            class="form-control form-control-lg text-center fw-bold fs-18 border-primary"
                                                            placeholder="0" value="{{ old('psf_selling_pkr', 0) }}">
                                                    </div>
                                                    <small class="text-muted fs-12 mt-2 d-block">
                                                        Enter PSF amount in PKR.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-secondary prev-tab-btn" data-target="#visa-tab">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back: Visa Charges
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab-btn" data-target="#summary-tab">
                                            Next: 7. Full Summary <i class="mdi mdi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- ========================================================================= -->
                                <!-- TAB 7: COMPLETE SUMMARY & PROFIT/LOSS ANALYSIS + FULL-WIDTH SUMMERNOTE -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade" id="summary-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="fs-16 fw-semibold text-primary mb-0">Full Quotation Summary & Profit/Loss Analysis</h5>
                                            <small class="text-muted">Comprehensive cost and selling calculation across all 6 facilities with net profit/margin.</small>
                                        </div>
                                    </div>

                                    <!-- Complete Breakdown Table matching Tab Titles -->
                                    <div class="card border mb-4">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-bordered align-middle mb-0 fs-13">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Facility / Service</th>
                                                            <th>Description / Items</th>
                                                            <th class="text-end">Total Cost (PKR)</th>
                                                            <th class="text-end">Total Sale (PKR)</th>
                                                            <th class="text-end">Profit / Margin (PKR)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><i class="mdi mdi-hotel text-primary me-1"></i> 1. Accommodation & Hotels</td>
                                                            <td>Hotel Stays (Room views, confirmation numbers & room rates)</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblAccCost">PKR 0</td>
                                                            <td class="text-end fw-semibold text-success" id="tblAccSale">PKR 0</td>
                                                            <td class="text-end fw-bold" id="tblAccProfit">PKR 0</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-silverware-fork-knife text-warning me-1"></i> 2. Separate Meal Plans</td>
                                                            <td>Independent Catering, Dinners, Suhoor & Iftar services</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblMealCost">PKR 0</td>
                                                            <td class="text-end fw-semibold text-success" id="tblMealSale">PKR 0</td>
                                                            <td class="text-end fw-bold" id="tblMealProfit">PKR 0</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-car-multiple text-info me-1"></i> 3. Transfers & Transport</td>
                                                            <td>Intercity Sectors, Airport transfers & private transport</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblTransCost">PKR 0</td>
                                                            <td class="text-end fw-semibold text-success" id="tblTransSale">PKR 0</td>
                                                            <td class="text-end fw-bold" id="tblTransProfit">PKR 0</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-map-marker-distance text-secondary me-1"></i> 4. Tours & Sightseeing / Ziyarat</td>
                                                            <td>Holy Places Ziyarat, Taif & Historical Tours</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblTourCost">PKR 0</td>
                                                            <td class="text-end fw-semibold text-success" id="tblTourSale">PKR 0</td>
                                                            <td class="text-end fw-bold" id="tblTourProfit">PKR 0</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-train-car text-dark me-1"></i> 5. Domestic Flights & Fast Train</td>
                                                            <td>Haramain High Speed Train (HHR) & Domestic Tickets</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblTrainCost">PKR 0</td>
                                                            <td class="text-end fw-semibold text-success" id="tblTrainSale">PKR 0</td>
                                                            <td class="text-end fw-bold" id="tblTrainProfit">PKR 0</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-passport text-primary me-1"></i> 6. Visa Charges & Processing</td>
                                                            <td>Saudi Umrah / Visit Visas, Mofa and Passenger Issuance</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblVisaCost">PKR 0</td>
                                                            <td class="text-end fw-semibold text-success" id="tblVisaSale">PKR 0</td>
                                                            <td class="text-end fw-bold" id="tblVisaProfit">PKR 0</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-tag-outline text-info me-1"></i> 7. PSF</td>
                                                            <td>Airport & PSF / Taxes / Surcharges</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblPsfCost">PKR 0</td>
                                                            <td class="text-end fw-semibold text-success" id="tblPsfSale">PKR 0</td>
                                                            <td class="text-end fw-bold" id="tblPsfProfit">PKR 0</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="table-light fs-14">
                                                        <tr>
                                                            <th colspan="2" class="text-uppercase fw-bold">Grand Totals:</th>
                                                            <th class="text-end text-danger fs-15 fw-bold" id="grandCostSummary">PKR 0</th>
                                                            <th class="text-end text-success fs-15 fw-bold" id="grandSaleSummary">PKR 0</th>
                                                            <th class="text-end text-primary fs-15 fw-bold" id="grandProfitSummary">PKR 0</th>
                                                        </tr>
                                                        <tr class="table-warning border-top">
                                                            <th colspan="2" class="text-uppercase fw-bold text-dark"><i class="mdi mdi-handshake me-1"></i> Total Supplier Amount (Payable):</th>
                                                            <th colspan="3" class="text-end text-dark fs-15 fw-bold" id="summaryTotalSupplier">PKR 0</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metric Banner -->
                                    <div class="summary-card mb-4">
                                        <div class="row text-center">
                                            <div class="col-md-3 border-end">
                                                <span class="fs-13 text-white-50">Total Package Cost</span>
                                                <h3 class="fw-bold text-white mb-0" id="cardCost">PKR 0</h3>
                                            </div>
                                            <div class="col-md-3 border-end">
                                                <span class="fs-13 text-white-50">Total Package Sale</span>
                                                <h3 class="fw-bold text-white mb-0" id="cardSale">PKR 0</h3>
                                            </div>
                                            <div class="col-md-3 border-end">
                                                <span class="fs-13 text-white-50">Net Profit</span>
                                                <h3 class="fw-bold text-warning mb-0" id="cardProfit">PKR 0</h3>
                                                <small class="text-white-50" id="cardMargin">Margin: 0%</small>
                                            </div>
                                            <div class="col-md-3">
                                                <span class="fs-13 text-white-50">Selling Price Per Pax</span>
                                                <h3 class="fw-bold text-white mb-0" id="cardPerPax">PKR 0</h3>
                                                <small class="text-white-50">Total: <span id="summaryPaxCount">1</span> Person(s)</small>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-2 border-top border-white-50 text-center">
                                            <span class="fs-13 text-white-50"><i class="mdi mdi-handshake me-1"></i> Total Supplier Payable:</span>
                                            <strong class="text-white fs-15 ms-2" id="cardSupplier">PKR 0</strong>
                                        </div>
                                    </div>

                                    <!-- Full Page Inclusions & Terms with Summernote (Stacked Top and Bottom) -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <label class="form-label fs-14 fw-semibold text-primary">
                                                <i class="mdi mdi-check-all me-1"></i> Package Inclusions (Full Page)
                                            </label>
                                            <textarea name="inclusions" class="form-control summernote" rows="5"><ul>
    <li>Hotel accommodations as per itinerary with specified room views and confirmation reference.</li>
    <li>Dedicated ground transfers and intercity travel with air-conditioned vehicles.</li>
    <li>Saudi Visa processing with full medical insurance.</li>
    <li>Guided ziyarat tours in Makkah & Madinah.</li>
</ul></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fs-14 fw-semibold text-primary">
                                                <i class="mdi mdi-file-document-outline me-1"></i> Terms & Special Conditions (Full Page)
                                            </label>
                                            <textarea name="terms" class="form-control summernote" rows="5"><ul>
    <li>Rates are subject to availability and currency exchange fluctuations.</li>
    <li>50% advance payment required upon quotation confirmation.</li>
    <li>Standard hotel and airline cancellation policies apply.</li>
</ul></textarea>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-secondary prev-tab-btn" data-target="#psf-tab">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back: 6. PSF
                                        </button>
                                        <div>
                                            <button type="submit" name="status" value="draft" class="btn btn-secondary px-4 py-2 me-2">
                                                <i class="mdi mdi-content-save-outline me-1"></i> Save as Draft
                                            </button>
                                            <button type="submit" name="status" value="sent" class="btn btn-primary px-5 py-2 fw-semibold">
                                                <i class="mdi mdi-send-check-outline me-1"></i> Save & Generate Proposal
                                            </button>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                </form>

            </div>
@endsection

@section('scripts')
    <!-- JavaScript & Flatpickr & Summernote & Interactive Calculations -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {

            // Initialize Summernote Rich Text Editor
            function initSummernote() {
                if (typeof $ !== 'undefined' && $.fn.summernote) {
                    $('.summernote').each(function() {
                        if (!$(this).next('.note-editor').length) {
                            $(this).summernote({
                                placeholder: 'Enter details here...',
                                tabsize: 2,
                                height: 200,
                                minHeight: 160,
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                                    ['fontname', ['fontname']],
                                    ['fontsize', ['fontsize']],
                                    ['color', ['color']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['table', ['table']],
                                    ['insert', ['link', 'hr']],
                                    ['view', ['fullscreen', 'codeview', 'help']]
                                ]
                            });
                        }
                    });
                }
            }

            initSummernote();

            // Refresh Summernote whenever Summary tab is clicked / shown
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                initSummernote();
            });

            // Hotels data for dynamic generation
            const hotelsOptions = `
                <option value="" data-id="">Select Hotel</option>
                @foreach ($hotels as $hotel)
                    <option value="{{ $hotel->name }}" data-id="{{ $hotel->id }}" data-city="{{ $hotel->place ? (is_object($hotel->place) ? $hotel->place->value : $hotel->place) : '' }}">
                        {{ $hotel->name }} @if($hotel->place) ({{ is_object($hotel->place) ? $hotel->place->value : $hotel->place }}) @endif
                    </option>
                @endforeach
            `;

            const routesOptions = `
                <option value="">Select Sector Route</option>
                @foreach ($routes as $r)
                    <option value="{{ $r->start_place }} - {{ $r->end_place }}">{{ $r->start_place }} - {{ $r->end_place }}</option>
                @endforeach
                @foreach ($travelRoutes as $tr)
                    <option value="{{ $tr->name }}">{{ $tr->name }}</option>
                @endforeach
            `;

            const vehiclesOptions = `
                <option value="">Select Vehicle Type</option>
                @foreach ($vehicles as $v)
                    <option value="{{ $v->vehicle_type }}">{{ $v->vehicle_type }} ({{ $v->brand_name }} {{ $v->model_year }})</option>
                @endforeach
                <option value="Sedan Car (Camry/Sonata)">Sedan Car (Camry/Sonata)</option>
                <option value="GMC / Yukon (SUV)">GMC / Yukon (SUV)</option>
                <option value="Toyota Hiace (10 Seater)">Toyota Hiace (10 Seater)</option>
                <option value="Toyota Coaster (20-30 Seater)">Toyota Coaster (20-30 Seater)</option>
                <option value="Luxury Bus (49 Seater)">Luxury Bus (49 Seater)</option>
                <option value="Private VIP Luxury">Private VIP Luxury</option>
            `;

            const suppliersOptions = `
                <option value="">-- Select Supplier / Vendor --</option>
                @foreach ($clients as $client)
                    @if($client->type == 'vendor' || $client->type == 'client')
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ ucfirst($client->type) }}{{ $client->company_name ? ' - ' . $client->company_name : '' }})</option>
                    @endif
                @endforeach
                @foreach ($companies as $comp)
                    <option value="{{ $comp->id }}">{{ $comp->company_name }} (Company)</option>
                @endforeach
            `;

            // Initialize standard flatpickr
            function initPickers() {
                flatpickr(".flatpickr-date", { dateFormat: "m/d/Y" });
                flatpickr(".flatpickr-datetime", { enableTime: true, dateFormat: "m/d/Y h:i K" });

                document.querySelectorAll(".accommodation-item").forEach(function(item) {
                    const checkin = item.querySelector(".flatpickr-checkin");
                    const checkout = item.querySelector(".flatpickr-checkout");

                    if (checkin && !checkin._flatpickr) {
                        flatpickr(checkin, {
                            dateFormat: "m/d/Y",
                            onChange: function() { calculateNights(item); }
                        });
                    }
                    if (checkout && !checkout._flatpickr) {
                        flatpickr(checkout, {
                            dateFormat: "m/d/Y",
                            onChange: function() { calculateNights(item); }
                        });
                    }
                });
            }

            function calculateAccommodationCost(accItem) {
                if (!accItem) return;
                const nights = parseFloat(accItem.querySelector(".acc-nights")?.value) || 0;
                const rooms = parseFloat(accItem.querySelector(".acc-rooms")?.value) || 1;
                const perNightRate = parseFloat(accItem.querySelector(".acc-per-night")?.value) || 0;
                const costInput = accItem.querySelector(".acc-cost");

                if (costInput && (perNightRate > 0 || nights > 0)) {
                    const calculatedCost = nights * rooms * perNightRate;
                    costInput.value = calculatedCost > 0 ? calculatedCost : 0;
                }
            }

            function calculateNights(item) {
                const cin = item.querySelector(".flatpickr-checkin")?.value;
                const cout = item.querySelector(".flatpickr-checkout")?.value;
                const nightsInput = item.querySelector(".acc-nights");

                if (cin && cout && nightsInput) {
                    const d1 = new Date(cin);
                    const d2 = new Date(cout);
                    const diffDays = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                    if (diffDays > 0) {
                        nightsInput.value = diffDays;
                    }
                }
                calculateAccommodationCost(item);
                recalculateAll();
            }

            initPickers();

            // Live Room Stock & Availability Handler
            function checkStockForRow(row) {
                if (!row) return;
                const hotelSelect = row.querySelector('.hotel-select');
                const hotelIdInput = row.querySelector('.acc-hotel-id');
                const roomTypeSelect = row.querySelector('.acc-room-type') || row.querySelector('[name*="[room_type]"]');
                const checkInInput = row.querySelector('.flatpickr-checkin');
                const checkOutInput = row.querySelector('.flatpickr-checkout');
                const roomsInput = row.querySelector('.acc-rooms');
                const roomsLabel = row.querySelector('.acc-rooms-label');
                const badge = row.querySelector('.acc-stock-badge');
                const warning = row.querySelector('.acc-stock-warning');
                const sharingBox = row.querySelector('.acc-sharing-beds-box');
                const maleInput = row.querySelector('.acc-male-beds');
                const femaleInput = row.querySelector('.acc-female-beds');
                const totalBedsInput = row.querySelector('.acc-total-beds');
                const sharingWarning = row.querySelector('.acc-sharing-warning');

                if (!hotelSelect || !roomTypeSelect || !badge) return;

                const roomType = roomTypeSelect.value || 'Double';
                const isSharing = (roomType.toLowerCase().trim() === 'sharing');
                const unitLabel = isSharing ? 'bed(s)' : 'room(s)';

                // Toggle Sharing Bed Allocation Box & Sync UI
                if (sharingBox) {
                    if (isSharing) {
                        sharingBox.classList.remove('d-none');
                        if (roomsLabel) roomsLabel.textContent = 'No Of Beds (Total)';
                        if (roomsInput) {
                            roomsInput.setAttribute('readonly', 'readonly');
                            roomsInput.classList.add('bg-light');
                        }
                    } else {
                        sharingBox.classList.add('d-none');
                        if (roomsLabel) roomsLabel.textContent = 'No Of Room';
                        if (roomsInput) {
                            roomsInput.removeAttribute('readonly');
                            roomsInput.classList.remove('bg-light');
                        }
                    }
                }

                let hotelId = hotelIdInput ? hotelIdInput.value : '';
                let hotelName = hotelSelect.value;
                const selectedOpt = hotelSelect.options[hotelSelect.selectedIndex];
                if (selectedOpt && selectedOpt.dataset.id) {
                    hotelId = selectedOpt.dataset.id;
                    if (hotelIdInput) hotelIdInput.value = hotelId;
                }

                if (!hotelName && !hotelId) {
                    badge.className = 'acc-stock-badge badge bg-soft-secondary text-secondary fs-10';
                    badge.textContent = 'Select Hotel & Type';
                    if (warning) warning.classList.add('d-none');
                    if (sharingWarning) sharingWarning.classList.add('d-none');
                    if (roomsInput) {
                        roomsInput.removeAttribute('max');
                        roomsInput.classList.remove('is-invalid');
                        delete roomsInput.dataset.stockExceeded;
                    }
                    if (maleInput) maleInput.removeAttribute('max');
                    if (femaleInput) femaleInput.removeAttribute('max');
                    if (totalBedsInput) totalBedsInput.removeAttribute('max');
                    return;
                }

                const checkIn = checkInInput ? checkInInput.value : '';
                const checkOut = checkOutInput ? checkOutInput.value : '';
                let requested = parseInt(roomsInput ? roomsInput.value : 0) || 0;

                const url = new URL('{{ route("room-inventory.check-availability") }}', window.location.origin);
                if (hotelId) url.searchParams.set('hotel_id', hotelId);
                if (hotelName) url.searchParams.set('hotel_name', hotelName);
                url.searchParams.set('room_type', roomType);
                if (checkIn) url.searchParams.set('check_in', checkIn);
                if (checkOut) url.searchParams.set('check_out', checkOut);
                url.searchParams.set('requested_rooms', requested > 0 ? requested : 1);

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.has_inventory) {
                            badge.className = 'acc-stock-badge badge bg-soft-info text-info fs-10';
                            badge.textContent = 'Open Stock (No Limit)';
                            if (warning) warning.classList.add('d-none');
                            if (sharingWarning) sharingWarning.classList.add('d-none');
                            if (roomsInput) {
                                roomsInput.removeAttribute('max');
                                roomsInput.classList.remove('is-invalid');
                                delete roomsInput.dataset.stockExceeded;
                            }
                            if (maleInput) maleInput.removeAttribute('max');
                            if (femaleInput) femaleInput.removeAttribute('max');
                            if (totalBedsInput) totalBedsInput.removeAttribute('max');
                        } else {
                            const avail = parseInt(data.available) || 0;
                            const total = parseInt(data.total_stock) || 0;
                            const maleAvail = data.male_available !== undefined ? parseInt(data.male_available) : avail;
                            const femaleAvail = data.female_available !== undefined ? parseInt(data.female_available) : avail;

                            if (roomsInput) {
                                roomsInput.setAttribute('max', avail > 0 ? avail : 0);
                            }
                            if (isSharing) {
                                if (maleInput) maleInput.setAttribute('max', maleAvail > 0 ? maleAvail : 0);
                                if (femaleInput) femaleInput.setAttribute('max', femaleAvail > 0 ? femaleAvail : 0);
                                if (totalBedsInput) totalBedsInput.setAttribute('max', avail > 0 ? avail : 0);
                            }

                            if (avail <= 0) {
                                badge.className = 'acc-stock-badge badge bg-danger text-white fs-10';
                                badge.textContent = isSharing ? `Sold Out (0 Beds)` : `Sold Out (0 / ${total})`;
                                if (roomsInput && requested > 0) {
                                    roomsInput.value = 0;
                                }
                                if (maleInput) maleInput.value = 0;
                                if (femaleInput) femaleInput.value = 0;
                                if (totalBedsInput) totalBedsInput.value = 0;

                                calculateAccommodationCost(row);
                                recalculateAll();

                                const msg = `Sold Out! No ${roomType} ${unitLabel} available in stock. Auto-adjusted to 0.`;
                                if (warning) {
                                    warning.textContent = msg;
                                    warning.classList.remove('d-none');
                                }
                                if (sharingWarning) {
                                    sharingWarning.textContent = msg;
                                    sharingWarning.classList.remove('d-none');
                                }
                            } else {
                                badge.className = 'acc-stock-badge badge bg-soft-success text-success fs-10';
                                badge.textContent = isSharing 
                                    ? `Stock: ${avail} Beds (M: ${maleAvail}, F: ${femaleAvail})` 
                                    : `Stock: ${avail} / ${total} Avail`;

                                let hasClamped = false;
                                let clampMsg = '';

                                if (isSharing) {
                                    let mVal = parseInt(maleInput ? maleInput.value : 0) || 0;
                                    let fVal = parseInt(femaleInput ? femaleInput.value : 0) || 0;

                                    if (mVal > maleAvail) {
                                        mVal = Math.max(0, maleAvail);
                                        if (maleInput) maleInput.value = mVal;
                                        hasClamped = true;
                                        clampMsg = `Exceeds stock! Only ${maleAvail} Male Sharing bed(s) available in stock. Auto-adjusted to ${maleAvail}.`;
                                    }
                                    if (fVal > femaleAvail) {
                                        fVal = Math.max(0, femaleAvail);
                                        if (femaleInput) femaleInput.value = fVal;
                                        hasClamped = true;
                                        clampMsg = `Exceeds stock! Only ${femaleAvail} Female Sharing bed(s) available in stock. Auto-adjusted to ${femaleAvail}.`;
                                    }
                                    if ((mVal + fVal) > avail) {
                                        const overflow = (mVal + fVal) - avail;
                                        if (fVal >= overflow) {
                                            fVal -= overflow;
                                        } else {
                                            mVal -= (overflow - fVal);
                                            fVal = 0;
                                        }
                                        if (maleInput) maleInput.value = mVal;
                                        if (femaleInput) femaleInput.value = fVal;
                                        hasClamped = true;
                                        clampMsg = `Exceeds stock! Only ${avail} Sharing bed(s) available in stock. Auto-adjusted to ${avail}.`;
                                    }
                                    const totalBeds = mVal + fVal;
                                    if (totalBedsInput) totalBedsInput.value = totalBeds;
                                    if (roomsInput) roomsInput.value = totalBeds;
                                } else {
                                    if (requested > avail) {
                                        if (roomsInput) roomsInput.value = avail;
                                        hasClamped = true;
                                        clampMsg = `Exceeds stock! Only ${avail} ${roomType} room(s) available in stock. Auto-adjusted to ${avail}.`;
                                    }
                                }

                                if (hasClamped) {
                                    if (warning) {
                                        warning.textContent = clampMsg;
                                        warning.classList.remove('d-none');
                                    }
                                    if (sharingWarning) {
                                        sharingWarning.textContent = clampMsg;
                                        sharingWarning.classList.remove('d-none');
                                    }
                                    calculateAccommodationCost(row);
                                    recalculateAll();
                                } else {
                                    if (warning) warning.classList.add('d-none');
                                    if (sharingWarning) sharingWarning.classList.add('d-none');
                                }
                                if (roomsInput) {
                                    roomsInput.classList.remove('is-invalid');
                                    delete roomsInput.dataset.stockExceeded;
                                }
                            }
                        }
                    })
                    .catch(err => console.error(err));
            }

            function loadHotelSummary(hotelId) {
                const bar = document.getElementById('liveHotelStockBar');
                const content = document.getElementById('liveHotelStockContent');
                if (!hotelId || !bar || !content) {
                    if (bar) bar.classList.add('d-none');
                    return;
                }

                fetch('/admin/room-inventory/hotel-summary/' + hotelId)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.summary) return;
                        let parts = [];
                        for (const [type, info] of Object.entries(data.summary)) {
                            if (info.total_stock > 0) {
                                const color = info.available > 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
                                parts.push(`<strong>${type}:</strong> <span class="${color}">${info.available}/${info.total_stock} avail</span>`);
                            }
                        }
                        if (parts.length > 0) {
                            content.innerHTML = `${data.hotel ? data.hotel.name : 'Hotel'}: &nbsp; ` + parts.join(' &nbsp;|&nbsp; ');
                            bar.classList.remove('d-none');
                        } else {
                            content.innerHTML = `${data.hotel ? data.hotel.name : 'Hotel'}: &nbsp; <span class="text-muted">Open Stock (No specific limits defined)</span>`;
                            bar.classList.remove('d-none');
                        }
                    })
                    .catch(err => console.error(err));
            }

            // Auto-select city and check stock when hotel or room type or dates change
            document.addEventListener("change", function(e) {
                if (e.target && e.target.classList.contains("hotel-select")) {
                    const selectedOpt = e.target.options[e.target.selectedIndex];
                    const accItem = e.target.closest(".accommodation-item");
                    if (accItem) {
                        const hotelId = selectedOpt ? (selectedOpt.getAttribute("data-id") || '') : '';
                        const idInput = accItem.querySelector(".acc-hotel-id");
                        if (idInput) idInput.value = hotelId;

                        const city = selectedOpt ? selectedOpt.getAttribute("data-city") : null;
                        if (city) {
                            const citySelect = accItem.querySelector(".acc-city-select");
                            if (citySelect) {
                                for (let i = 0; i < citySelect.options.length; i++) {
                                    if (citySelect.options[i].value.toLowerCase() === city.toLowerCase()) {
                                        citySelect.selectedIndex = i;
                                        break;
                                    }
                                }
                            }
                        }

                        loadHotelSummary(hotelId);
                        checkStockForRow(accItem);
                    }
                }

                if (e.target && (e.target.classList.contains("acc-room-type") || e.target.name?.includes("[room_type]"))) {
                    const accItem = e.target.closest(".accommodation-item");
                    if (accItem) checkStockForRow(accItem);
                }

                if (e.target && (e.target.classList.contains("flatpickr-checkin") || e.target.classList.contains("flatpickr-checkout"))) {
                    const accItem = e.target.closest(".accommodation-item");
                    if (accItem) checkStockForRow(accItem);
                }
            });

            // Auto-clamping on direct input to room counts and sharing beds
            document.addEventListener("input", function(e) {
                // Clamping for acc-rooms (when not sharing)
                if (e.target && e.target.classList.contains("acc-rooms")) {
                    const accItem = e.target.closest(".accommodation-item");
                    const roomType = (accItem?.querySelector('.acc-room-type')?.value || '').toLowerCase().trim();
                    if (roomType !== 'sharing') {
                        const max = parseInt(e.target.getAttribute("max"));
                        let val = parseInt(e.target.value);
                        if (val < 0) { e.target.value = 0; val = 0; }
                        if (!isNaN(max) && !isNaN(val) && val > max) {
                            e.target.value = max;
                            const warn = accItem?.querySelector(".acc-stock-warning");
                            if (warn) {
                                warn.textContent = max === 0 
                                    ? `Sold Out! No rooms available in stock.`
                                    : `Exceeds stock! Maximum available stock is ${max} room(s). Auto-adjusted to ${max}.`;
                                warn.classList.remove('d-none');
                            }
                        }
                        if (accItem) checkStockForRow(accItem);
                    }
                }

                // Sharing Male & Female Bed calculation & Clamping
                if (e.target && (e.target.classList.contains("acc-male-beds") || e.target.classList.contains("acc-female-beds"))) {
                    const accItem = e.target.closest(".accommodation-item");
                    if (accItem) {
                        const maleInput = accItem.querySelector(".acc-male-beds");
                        const femaleInput = accItem.querySelector(".acc-female-beds");
                        const totalInput = accItem.querySelector(".acc-total-beds");
                        const roomsInput = accItem.querySelector(".acc-rooms");
                        const warn = accItem.querySelector(".acc-stock-warning");
                        const sharingWarn = accItem.querySelector(".acc-sharing-warning");

                        const isMale = e.target.classList.contains("acc-male-beds");
                        const genderLabel = isMale ? "Male" : "Female";
                        const maxVal = parseInt(e.target.getAttribute("max"));
                        let val = parseInt(e.target.value);

                        if (val < 0) {
                            e.target.value = 0;
                            val = 0;
                        }

                        // Real-time clamping against max available gender beds
                        if (!isNaN(maxVal) && !isNaN(val) && val > maxVal) {
                            e.target.value = maxVal;
                            val = maxVal;
                            const msg = maxVal === 0 
                                ? `Sold Out! No ${genderLabel} Sharing bed(s) available in stock. Auto-adjusted to 0.`
                                : `Exceeds stock! Maximum available ${genderLabel} Sharing bed(s) is ${maxVal}. Auto-adjusted to ${maxVal}.`;
                            if (warn) {
                                warn.textContent = msg;
                                warn.classList.remove('d-none');
                            }
                            if (sharingWarn) {
                                sharingWarn.textContent = msg;
                                sharingWarn.classList.remove('d-none');
                            }
                        } else {
                            if (warn) warn.classList.add('d-none');
                            if (sharingWarn) sharingWarn.classList.add('d-none');
                        }

                        let mBeds = parseInt(maleInput?.value) || 0;
                        let fBeds = parseInt(femaleInput?.value) || 0;

                        // Real-time clamping against total sharing beds available
                        const totalMax = parseInt(roomsInput?.getAttribute("max"));
                        if (!isNaN(totalMax) && (mBeds + fBeds) > totalMax) {
                            if (isMale) {
                                mBeds = Math.max(0, totalMax - fBeds);
                                if (maleInput) maleInput.value = mBeds;
                            } else {
                                fBeds = Math.max(0, totalMax - mBeds);
                                if (femaleInput) femaleInput.value = fBeds;
                            }
                            const msg = `Exceeds stock! Only ${totalMax} Sharing bed(s) available in total. Auto-adjusted to ${totalMax}.`;
                            if (warn) {
                                warn.textContent = msg;
                                warn.classList.remove('d-none');
                            }
                            if (sharingWarn) {
                                sharingWarn.textContent = msg;
                                sharingWarn.classList.remove('d-none');
                            }
                        }

                        const totalBeds = mBeds + fBeds;
                        if (totalInput) totalInput.value = totalBeds;
                        if (roomsInput) roomsInput.value = totalBeds;

                        calculateAccommodationCost(accItem);
                        recalculateAll();
                    }
                }
            });

            // Prevent Form Submission if Stock is Exceeded!
            document.getElementById('quotationForm')?.addEventListener('submit', function(e) {
                let hasError = false;
                let errorMsg = '';
                let firstErrorElem = null;

                document.querySelectorAll('.accommodation-item').forEach(function(row) {
                    if (hasError) return;
                    const roomType = (row.querySelector('.acc-room-type')?.value || '').toLowerCase().trim();
                    const roomsInput = row.querySelector('.acc-rooms');
                    const maxRooms = parseInt(roomsInput?.getAttribute('max'));
                    const valRooms = parseInt(roomsInput?.value || 0);

                    if (roomType === 'sharing') {
                        const maleInput = row.querySelector('.acc-male-beds');
                        const femaleInput = row.querySelector('.acc-female-beds');
                        const maxMale = parseInt(maleInput?.getAttribute('max'));
                        const valMale = parseInt(maleInput?.value || 0);
                        const maxFemale = parseInt(femaleInput?.getAttribute('max'));
                        const valFemale = parseInt(femaleInput?.value || 0);

                        if (!isNaN(maxMale) && valMale > maxMale) {
                            hasError = true;
                            errorMsg = `Cannot create quotation: Male sharing beds (${valMale}) exceeds available stock (${maxMale})!`;
                            firstErrorElem = maleInput;
                        } else if (!isNaN(maxFemale) && valFemale > maxFemale) {
                            hasError = true;
                            errorMsg = `Cannot create quotation: Female sharing beds (${valFemale}) exceeds available stock (${maxFemale})!`;
                            firstErrorElem = femaleInput;
                        } else if (!isNaN(maxRooms) && (valMale + valFemale) > maxRooms) {
                            hasError = true;
                            errorMsg = `Cannot create quotation: Total sharing beds (${valMale + valFemale}) exceeds available stock (${maxRooms})!`;
                            firstErrorElem = maleInput || roomsInput;
                        }
                    } else {
                        if (!isNaN(maxRooms) && valRooms > maxRooms) {
                            hasError = true;
                            errorMsg = `Cannot create quotation: Room count (${valRooms}) exceeds available stock (${maxRooms})!`;
                            firstErrorElem = roomsInput;
                        }
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    alert(errorMsg);
                    if (firstErrorElem) {
                        firstErrorElem.focus();
                        firstErrorElem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            // Next / Prev tab buttons
            document.querySelectorAll(".next-tab-btn, .prev-tab-btn").forEach(function(btn) {
                btn.addEventListener("click", function() {
                    const targetTabId = this.getAttribute("data-target");
                    const triggerEl = document.querySelector(targetTabId);
                    if (triggerEl) {
                        const tab = new bootstrap.Tab(triggerEl);
                        tab.show();
                    }
                });
            });

            // Lead selection auto fill
            const leadSelect = document.getElementById("lead_select");
            if (leadSelect) {
                leadSelect.addEventListener("change", function() {
                    const opt = this.options[this.selectedIndex];
                    if (opt && opt.value) {
                        if (document.getElementById("client_name_input")) document.getElementById("client_name_input").value = opt.dataset.name || '';
                        if (document.getElementById("client_phone_input")) document.getElementById("client_phone_input").value = opt.dataset.phone || '';
                        if (document.getElementById("master_total_pax") && opt.dataset.pax) {
                            document.getElementById("master_total_pax").value = opt.dataset.pax;
                            document.getElementById("summaryPaxCount").innerText = opt.dataset.pax;
                        }
                    }
                    recalculateAll();
                });
            }

            // Master Pax change
            const masterPaxInput = document.getElementById("master_total_pax");
            if (masterPaxInput) {
                masterPaxInput.addEventListener("input", function() {
                    document.getElementById("summaryPaxCount").innerText = this.value || 1;
                    recalculateAll();
                });
            }

            // Currency State Handler
            function handleCurrencyChange(selectElem) {
                const val = selectElem.value;
                const parentContainer = selectElem.closest('.input-group, .row, .card-repeater-item, tr');
                const roeInput = parentContainer?.querySelector('.acc-cost-roe, .acc-selling-roe, .acc-exrate, .trans-exrate, .tour-exrate, .train-exrate, .visa-exrate, .meal-exrate, #psfCostRoe, #psfSellingRoe');
                if (roeInput) {
                    if (val === 'PKR') {
                        roeInput.value = '1';
                        roeInput.setAttribute('readonly', 'readonly');
                        roeInput.classList.add('bg-light');
                        roeInput.placeholder = '1.00';
                    } else {
                        if (roeInput.value === '1') {
                            roeInput.value = '';
                        }
                        roeInput.removeAttribute('readonly');
                        roeInput.classList.remove('bg-light');
                        roeInput.placeholder = val === 'SAR' ? 'e.g. 76.50' : 'e.g. 278.50';
                        roeInput.focus();
                    }
                }
                recalculateAll();
            }

            document.addEventListener("change", function(e) {
                if (e.target.matches(".acc-cost-currency, .acc-selling-currency, #psfCostCurrency, #psfSellingCurrency, .acc-currency, .trans-currency, .tour-currency, .train-currency, .visa-currency, select[name*='[currency]']")) {
                    handleCurrencyChange(e.target);
                }
            });

            // Central Calculation Engine
            function recalculateAll() {
                let totalAccCostPkr = 0;
                let totalAccSalePkr = 0;
                let totalMealCostPkr = 0;
                let totalMealSalePkr = 0;
                let totalTransCostPkr = 0;
                let totalTransSalePkr = 0;
                let totalTourCostPkr = 0;
                let totalTourSalePkr = 0;
                let totalTrainCostPkr = 0;
                let totalTrainSalePkr = 0;
                let totalVisaCostPkr = 0;
                let totalVisaSalePkr = 0;
                let totalSupplierAmount = 0;

                const masterPax = parseFloat(document.getElementById("master_total_pax")?.value) || 1;

                // 1. Accommodations & Meals
                document.querySelectorAll(".accommodation-item").forEach(function(acc) {
                    const costCurr = acc.querySelector(".acc-cost-currency")?.value || 'PKR';
                    const rawCostEx = acc.querySelector(".acc-cost-roe")?.value;
                    const costRoe = (costCurr === 'PKR') ? 1 : (rawCostEx !== '' && !isNaN(parseFloat(rawCostEx)) ? parseFloat(rawCostEx) : 0);
                    const costAmt = parseFloat(acc.querySelector(".acc-cost")?.value) || 0;

                    const saleCurr = acc.querySelector(".acc-selling-currency")?.value || 'PKR';
                    const rawSaleEx = acc.querySelector(".acc-selling-roe")?.value;
                    const saleRoe = (saleCurr === 'PKR') ? 1 : (rawSaleEx !== '' && !isNaN(parseFloat(rawSaleEx)) ? parseFloat(rawSaleEx) : 0);
                    const saleAmt = parseFloat(acc.querySelector(".acc-selling")?.value) || 0;

                    const accCostPkr = costAmt * costRoe;
                    const accSalePkr = saleAmt * saleRoe;
                    const accProfitPkr = accSalePkr - accCostPkr;

                    totalAccCostPkr += accCostPkr;
                    totalAccSalePkr += accSalePkr;

                    const costBadge = acc.querySelector(".acc-cost-pkr-badge");
                    if (costBadge) costBadge.innerText = "PKR " + formatNum(accCostPkr);
                    const saleBadge = acc.querySelector(".acc-sale-pkr-badge");
                    if (saleBadge) saleBadge.innerText = "PKR " + formatNum(accSalePkr);
                    const profitBadge = acc.querySelector(".acc-profit-pkr-badge");
                    if (profitBadge) {
                        profitBadge.innerText = "PKR " + formatNum(accProfitPkr);
                        profitBadge.className = accProfitPkr >= 0 ? "fs-13 fw-bold text-success pt-1 acc-profit-pkr-badge" : "fs-13 fw-bold text-danger pt-1 acc-profit-pkr-badge";
                    }

                    const suppAmt = parseFloat(acc.querySelector(".acc-supplier")?.value) || 0;
                    totalSupplierAmount += suppAmt;

                    acc.querySelectorAll(".meal-tbody tr").forEach(function(row) {
                        const mPax = parseFloat(row.querySelector(".meal-pax")?.value) || 0;
                        const mDays = parseFloat(row.querySelector(".meal-days")?.value) || 0;
                        const rawMealEx = row.querySelector(".meal-exrate")?.value;
                        const mEx = rawMealEx !== '' && !isNaN(parseFloat(rawMealEx)) ? parseFloat(rawMealEx) : 1;
                        const mCost = parseFloat(row.querySelector(".meal-cost")?.value) || 0;
                        const mSale = parseFloat(row.querySelector(".meal-sale")?.value) || 0;

                        const rowCostPkr = mCost * (mPax > 0 ? mPax : 1) * (mDays > 0 ? mDays : 1) * mEx;
                        const rowSalePkr = mSale * (mPax > 0 ? mPax : 1) * (mDays > 0 ? mDays : 1) * mEx;

                        if (row.querySelector(".meal-total-cost-pkr")) row.querySelector(".meal-total-cost-pkr").innerText = formatNum(rowCostPkr);
                        if (row.querySelector(".meal-total-sale-pkr")) row.querySelector(".meal-total-sale-pkr").innerText = formatNum(rowSalePkr);

                        totalMealCostPkr += rowCostPkr;
                        totalMealSalePkr += rowSalePkr;
                    });
                });

                // 2. Transfers
                document.querySelectorAll(".transfer-item").forEach(function(tr) {
                    const qty = parseFloat(tr.querySelector(".trans-qty")?.value) || 0;
                    const curr = tr.querySelector(".trans-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = tr.querySelector(".trans-exrate")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(tr.querySelector(".trans-cost")?.value) || 0;
                    const sale = parseFloat(tr.querySelector(".trans-sale")?.value) || 0;

                    totalTransCostPkr += cost * (qty > 0 ? qty : 1) * ex;
                    totalTransSalePkr += sale * (qty > 0 ? qty : 1) * ex;
                });

                // 3. Tours
                document.querySelectorAll(".tour-item").forEach(function(t) {
                    const qty = parseFloat(t.querySelector(".tour-qty")?.value) || 0;
                    const curr = t.querySelector(".tour-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = t.querySelector(".tour-exrate")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(t.querySelector(".tour-cost")?.value) || 0;
                    const sale = parseFloat(t.querySelector(".tour-sale")?.value) || 0;

                    totalTourCostPkr += cost * (qty > 0 ? qty : 1) * ex;
                    totalTourSalePkr += sale * (qty > 0 ? qty : 1) * ex;
                });

                // 4. Trains / Flights
                document.querySelectorAll(".train-item").forEach(function(tr) {
                    const pax = parseFloat(tr.querySelector(".train-pax")?.value) || 0;
                    const curr = tr.querySelector(".train-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = tr.querySelector(".train-exrate")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(tr.querySelector(".train-cost")?.value) || 0;
                    const sale = parseFloat(tr.querySelector(".train-sale")?.value) || 0;

                    totalTrainCostPkr += cost * (pax > 0 ? pax : 1) * ex;
                    totalTrainSalePkr += sale * (pax > 0 ? pax : 1) * ex;
                });

                // 5. Visas
                document.querySelectorAll(".visa-item").forEach(function(v) {
                    const curr = v.querySelector(".visa-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = v.querySelector(".visa-exrate")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(v.querySelector(".visa-cost")?.value) || 0;
                    const sale = parseFloat(v.querySelector(".visa-sale")?.value) || 0;

                    totalVisaCostPkr += cost * ex;
                    totalVisaSalePkr += sale * ex;
                });

                // 6. Passenger Service Fee (PSF) - Direct Amount in PKR
                const psfCostPkr = parseFloat(document.getElementById("psfCostAmt")?.value) || 0;
                const psfSalePkr = parseFloat(document.getElementById("psfSellingAmt")?.value) || 0;
                const psfProfitPkr = psfSalePkr - psfCostPkr;

                const psfSuppAmt = parseFloat(document.getElementById("psfSupplierAmt")?.value) || 0;
                totalSupplierAmount += psfSuppAmt;

                if (document.getElementById("psfTotalProfitPkr")) {
                    document.getElementById("psfTotalProfitPkr").innerText = "PKR " + formatNum(psfProfitPkr);
                    document.getElementById("psfTotalProfitPkr").className = psfProfitPkr >= 0 ? "text-primary fw-bold mb-0 mt-1" : "text-danger fw-bold mb-0 mt-1";
                }

                // Table PSF row
                if (document.getElementById("tblPsfCost")) document.getElementById("tblPsfCost").innerText = "PKR " + formatNum(psfCostPkr);
                if (document.getElementById("tblPsfSale")) document.getElementById("tblPsfSale").innerText = "PKR " + formatNum(psfSalePkr);
                if (document.getElementById("tblPsfProfit")) document.getElementById("tblPsfProfit").innerText = "PKR " + formatNum(psfProfitPkr);

                // Update Table Breakdown
                document.getElementById("tblAccCost").innerText = "PKR " + formatNum(totalAccCostPkr);
                document.getElementById("tblAccSale").innerText = "PKR " + formatNum(totalAccSalePkr);
                document.getElementById("tblAccProfit").innerText = "PKR " + formatNum(totalAccSalePkr - totalAccCostPkr);

                document.getElementById("tblMealCost").innerText = "PKR " + formatNum(totalMealCostPkr);
                document.getElementById("tblMealSale").innerText = "PKR " + formatNum(totalMealSalePkr);
                document.getElementById("tblMealProfit").innerText = "PKR " + formatNum(totalMealSalePkr - totalMealCostPkr);

                document.getElementById("tblTransCost").innerText = "PKR " + formatNum(totalTransCostPkr);
                document.getElementById("tblTransSale").innerText = "PKR " + formatNum(totalTransSalePkr);
                document.getElementById("tblTransProfit").innerText = "PKR " + formatNum(totalTransSalePkr - totalTransCostPkr);

                document.getElementById("tblTourCost").innerText = "PKR " + formatNum(totalTourCostPkr);
                document.getElementById("tblTourSale").innerText = "PKR " + formatNum(totalTourSalePkr);
                document.getElementById("tblTourProfit").innerText = "PKR " + formatNum(totalTourSalePkr - totalTourCostPkr);

                document.getElementById("tblTrainCost").innerText = "PKR " + formatNum(totalTrainCostPkr);
                document.getElementById("tblTrainSale").innerText = "PKR " + formatNum(totalTrainSalePkr);
                document.getElementById("tblTrainProfit").innerText = "PKR " + formatNum(totalTrainSalePkr - totalTrainCostPkr);

                document.getElementById("tblVisaCost").innerText = "PKR " + formatNum(totalVisaCostPkr);
                document.getElementById("tblVisaSale").innerText = "PKR " + formatNum(totalVisaSalePkr);
                document.getElementById("tblVisaProfit").innerText = "PKR " + formatNum(totalVisaSalePkr - totalVisaCostPkr);

                // Grand Totals
                const grandCost = totalAccCostPkr + totalMealCostPkr + totalTransCostPkr + totalTourCostPkr + totalTrainCostPkr + totalVisaCostPkr + psfCostPkr;
                const grandSale = totalAccSalePkr + totalMealSalePkr + totalTransSalePkr + totalTourSalePkr + totalTrainSalePkr + totalVisaSalePkr + psfSalePkr;
                const grandProfit = grandSale - grandCost;
                const profitMargin = grandSale > 0 ? ((grandProfit / grandSale) * 100).toFixed(1) : 0;
                const perPaxSale = masterPax > 0 ? (grandSale / masterPax) : grandSale;

                document.getElementById("grandCostSummary").innerText = "PKR " + formatNum(grandCost);
                document.getElementById("grandSaleSummary").innerText = "PKR " + formatNum(grandSale);
                document.getElementById("grandProfitSummary").innerText = "PKR " + formatNum(grandProfit);

                if (document.getElementById("summaryTotalSupplier")) {
                    document.getElementById("summaryTotalSupplier").innerText = "PKR " + formatNum(totalSupplierAmount);
                }
                if (document.getElementById("cardSupplier")) {
                    document.getElementById("cardSupplier").innerText = "PKR " + formatNum(totalSupplierAmount);
                }

                // Metric Banner Cards
                document.getElementById("cardCost").innerText = "PKR " + formatNum(grandCost);
                document.getElementById("cardSale").innerText = "PKR " + formatNum(grandSale);
                document.getElementById("cardProfit").innerText = "PKR " + formatNum(grandProfit);
                document.getElementById("cardMargin").innerText = "Margin: " + profitMargin + "%";
                document.getElementById("cardPerPax").innerText = "PKR " + formatNum(perPaxSale);
            }

            function formatNum(num) {
                return Math.round(num).toLocaleString('en-US');
            }

            // Real-time calculation listeners
            document.addEventListener("input", function(e) {
                if (e.target.matches(".acc-nights, .acc-rooms, .acc-per-night")) {
                    const accItem = e.target.closest(".accommodation-item");
                    if (accItem) {
                        calculateAccommodationCost(accItem);
                    }
                }
                if (e.target.matches(".acc-cost, .acc-selling, .acc-cost-roe, .acc-selling-roe, .acc-supplier, .acc-nights, .acc-rooms, .acc-per-night, .acc-male-beds, .acc-female-beds, .meal-cost, .meal-sale, .meal-pax, .meal-days, .meal-exrate, .trans-qty, .trans-exrate, .trans-cost, .trans-sale, .tour-qty, .tour-exrate, .tour-cost, .tour-sale, .train-pax, .train-exrate, .train-cost, .train-sale, .visa-exrate, .visa-cost, .visa-sale, #psfQty, #psfCostAmt, #psfCostRoe, #psfSellingAmt, #psfSellingRoe, #psfSupplierAmt, #master_total_pax")) {
                    recalculateAll();
                }
            });

            // Dynamic Accommodation Add
            let accCounter = 1;
            function addAccommodation() {
                accCounter++;
                const container = document.getElementById("accommodationRepeaterContainer");
                const newCard = document.createElement("div");
                newCard.className = "card-repeater-item accommodation-item";
                newCard.dataset.index = accCounter - 1;

                newCard.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <span class="badge bg-primary fs-12">Accommodation Stay #${accCounter}</span>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-acc"><i class="mdi mdi-trash-can-outline"></i> Remove Stay</button>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Check In</label>
                            <input type="text" name="accommodations[${accCounter-1}][check_in]" class="form-control form-control-sm flatpickr-checkin" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Check Out</label>
                            <input type="text" name="accommodations[${accCounter-1}][check_out]" class="form-control form-control-sm flatpickr-checkout" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Number Of Night</label>
                            <input type="number" name="accommodations[${accCounter-1}][number_of_nights]" class="form-control form-control-sm bg-light acc-nights" placeholder="0" min="0" value="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">City Name</label>
                            <select name="accommodations[${accCounter-1}][city]" class="form-select form-select-sm acc-city-select">
                                <option value="Makkah">Makkah</option>
                                <option value="Madinah">Madinah</option>
                                <option value="Jeddah">Jeddah</option>
                                <option value="Riyadh">Riyadh</option>
                                <option value="Taif">Taif</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-hotel me-1"></i> Hotel Name (Active)</label>
                            <select name="accommodations[${accCounter-1}][hotel_name]" class="form-select form-select-sm hotel-select">
                                ${hotelsOptions}
                            </select>
                            <input type="hidden" name="accommodations[${accCounter-1}][hotel_id]" class="acc-hotel-id" value="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Room Type</label>
                            <select name="accommodations[${accCounter-1}][room_type]" class="form-select form-select-sm acc-room-type">
                                <option value="Double">Double (2 Bed)</option>
                                <option value="Triple">Triple (3 Bed)</option>
                                <option value="Quad">Quad (4 Bed)</option>
                                <option value="Quint">Quint (5 Bed)</option>
                                <option value="Single">Single (1 Bed)</option>
                                <option value="Sharing">Sharing</option>
                                <option value="Suite">Suite / Family</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-eye-outline me-1"></i> Room View</label>
                            <select name="accommodations[${accCounter-1}][room_view]" class="form-select form-select-sm border-primary">
                                <option value="City View">City View</option>
                                <option value="Haram View">Haram View</option>
                                <option value="Kaaba View">Kaaba View</option>
                                <option value="Partial Haram View">Partial Haram View</option>
                                <option value="Partial Kaaba View">Partial Kaaba View</option>
                                <option value="Land View (Int.)">Land View (International)</option>
                                <option value="Sea View (Int.)">Sea View (International)</option>
                                <option value="Courtyard View">Courtyard View</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-pound me-1"></i> Confirmation #</label>
                            <input type="text" name="accommodations[${accCounter-1}][confirmation_number]" class="form-control form-control-sm border-primary" placeholder="Confirmation #">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fs-13 fw-semibold">Meal Plan</label>
                            <select name="accommodations[${accCounter-1}][meal_plan]" class="form-select form-select-sm">
                                <option value="Room Only">Room Only (RO)</option>
                                <option value="Bed & Breakfast">Bed & Breakfast (BB)</option>
                                <option value="Half Board">Half Board (HB)</option>
                                <option value="Full Board">Full Board (FB)</option>
                                <option value="Suhoor">Suhoor Included</option>
                                <option value="Iftar Dinner">Iftar Dinner Included</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fs-13 fw-semibold mb-0 acc-rooms-label">No Of Room</label>
                                <span class="acc-stock-badge badge bg-soft-secondary text-secondary fs-10">Check Stock</span>
                            </div>
                            <input type="number" name="accommodations[${accCounter-1}][no_of_rooms]" class="form-control form-control-sm acc-rooms" min="0" placeholder="0" value="">
                            <div class="acc-stock-warning text-danger fs-11 mt-1 d-none"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-13 fw-semibold">Per Night Rate</label>
                            <input type="number" step="any" name="accommodations[${accCounter-1}][per_night_rate]" class="form-control form-control-sm acc-per-night" value="0">
                        </div>
                    </div>

                    <!-- Sharing Bed Details -->
                    <div class="acc-sharing-beds-box row g-2 mb-3 p-2 border rounded bg-light d-none">
                        <div class="col-md-12 mb-1">
                            <span class="fs-12 fw-bold text-primary"><i class="mdi mdi-bed-empty me-1"></i> Sharing Bed Allocation (Gender Wise)</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-12 fw-semibold text-primary"><i class="mdi mdi-gender-male me-1"></i> Male Beds</label>
                            <input type="number" name="accommodations[${accCounter-1}][male_beds]" class="form-control form-control-sm acc-male-beds text-center border-primary" min="0" placeholder="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-12 fw-semibold text-danger"><i class="mdi mdi-gender-female me-1"></i> Female Beds</label>
                            <input type="number" name="accommodations[${accCounter-1}][female_beds]" class="form-control form-control-sm acc-female-beds text-center border-danger" min="0" placeholder="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-12 fw-semibold text-dark"><i class="mdi mdi-counter me-1"></i> Total Beds</label>
                            <input type="number" name="accommodations[${accCounter-1}][no_of_beds]" class="form-control form-control-sm acc-total-beds text-center fw-bold bg-light" min="0" placeholder="0" value="0" readonly>
                        </div>
                        <div class="acc-sharing-warning text-danger fs-11 mt-1 col-md-12 d-none"></div>
                    </div>

                    <!-- Cost Side -->
                    <div class="row g-2 mb-2 p-2 border rounded bg-soft-light align-items-center">
                        <div class="col-md-2">
                            <span class="fs-12 fw-bold text-danger text-uppercase d-block mb-1"><i class="mdi mdi-cash-minus me-1"></i> Cost Side:</span>
                            <select name="accommodations[${accCounter-1}][cost_currency]" class="form-select form-select-sm acc-cost-currency">
                                <option value="PKR" selected>PKR</option>
                                <option value="SAR">SAR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 fw-semibold text-danger mb-1"><i class="mdi mdi-swap-horizontal me-1"></i> Cost ROE</label>
                            <input type="number" step="0.0001" name="accommodations[${accCounter-1}][cost_exchange_rate]" class="form-control form-control-sm border-danger acc-cost-roe bg-light" readonly placeholder="1.00" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-12 fw-semibold text-danger mb-1">Cost Amount (Foreign)</label>
                            <input type="number" step="any" name="accommodations[${accCounter-1}][cost_amount]" class="form-control form-control-sm acc-cost" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 fw-semibold text-danger mb-1">Cost PKR</label>
                            <div class="fs-13 fw-bold text-danger pt-1 acc-cost-pkr-badge">PKR 0</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-12 fw-semibold text-danger mb-1"><i class="mdi mdi-truck-delivery-outline me-1"></i> Supplier / Vendor</label>
                            <select name="accommodations[${accCounter-1}][supplier_id]" class="form-select form-select-sm">
                                ${suppliersOptions}
                            </select>
                        </div>
                    </div>

                    <!-- Sale Side -->
                    <div class="row g-2 mb-3 p-2 border rounded bg-soft-success align-items-center">
                        <div class="col-md-2">
                            <span class="fs-12 fw-bold text-success text-uppercase d-block mb-1"><i class="mdi mdi-cash-plus me-1"></i> Sale Side:</span>
                            <select name="accommodations[${accCounter-1}][selling_currency]" class="form-select form-select-sm acc-selling-currency">
                                <option value="PKR" selected>PKR</option>
                                <option value="SAR">SAR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 fw-semibold text-success mb-1"><i class="mdi mdi-swap-horizontal me-1"></i> Selling ROE</label>
                            <input type="number" step="0.0001" name="accommodations[${accCounter-1}][selling_exchange_rate]" class="form-control form-control-sm border-success acc-selling-roe bg-light" readonly placeholder="1.00" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-12 fw-semibold text-success mb-1">Selling Amount (Foreign)</label>
                            <input type="number" step="any" name="accommodations[${accCounter-1}][selling_amount]" class="form-control form-control-sm acc-selling" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 fw-semibold text-success mb-1">Selling PKR</label>
                            <div class="fs-13 fw-bold text-success pt-1 acc-sale-pkr-badge">PKR 0</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-12 fw-semibold text-primary mb-1">Estimated Stay Profit</label>
                            <div class="fs-13 fw-bold text-primary pt-1 acc-profit-pkr-badge">PKR 0</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-13 fw-semibold">Cancellation Deadline</label>
                            <input type="text" name="accommodations[${accCounter-1}][cancellation_deadline]" class="form-control form-control-sm flatpickr-date" placeholder="mm/dd/yyyy">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-13 fw-semibold">Finalization Date</label>
                            <input type="text" name="accommodations[${accCounter-1}][finalization_date]" class="form-control form-control-sm flatpickr-date" placeholder="mm/dd/yyyy">
                        </div>
                    </div>
                    <div class="meal-plan-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fs-14 fw-semibold text-dark mb-0"><i class="mdi mdi-silverware-fork-knife text-warning me-1"></i> Separate Meal Plans Facility</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-add-meal" data-acc="${accCounter-1}">+ Add Meal</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0 bg-white">
                                <thead class="table-light fs-12">
                                    <tr>
                                        <th>Meal Type</th><th>City</th><th>Supplier</th><th>Pax</th><th>Days</th><th>Ex. Rate</th><th>Cost</th><th>Sale</th><th>Total Cost</th><th>Total Sale</th><th></th>
                                    </tr>
                                </thead>
                                <tbody class="meal-tbody" id="mealTbody_${accCounter-1}">
                                    <tr>
                                        <td>
                                            <select name="accommodations[${accCounter-1}][meals][0][type]" class="form-select form-select-sm">
                                                <option value="Breakfast">Breakfast</option><option value="Lunch">Lunch</option><option value="Dinner" selected>Dinner</option><option value="Half Board">Half Board</option><option value="Full Board">Full Board</option><option value="Suhoor">Suhoor</option><option value="Iftar">Iftar Buffet</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="accommodations[${accCounter-1}][meals][0][city]" class="form-control form-control-sm" placeholder="City" value="Makkah"></td>
                                        <td><input type="text" name="accommodations[${accCounter-1}][meals][0][provider]" class="form-control form-control-sm" placeholder="Supplier"></td>
                                        <td><input type="number" name="accommodations[${accCounter-1}][meals][0][pax]" class="form-control form-control-sm meal-pax" min="0" placeholder="0" value=""></td>
                                        <td><input type="number" name="accommodations[${accCounter-1}][meals][0][days]" class="form-control form-control-sm meal-days" min="0" placeholder="0" value=""></td>
                                        <td><input type="number" step="0.01" name="accommodations[${accCounter-1}][meals][0][ex_rate]" class="form-control form-control-sm meal-exrate bg-light" placeholder="1.00" value="1" readonly></td>
                                        <td><input type="number" step="any" name="accommodations[${accCounter-1}][meals][0][cost]" class="form-control form-control-sm meal-cost" value="0"></td>
                                        <td><input type="number" step="any" name="accommodations[${accCounter-1}][meals][0][sale]" class="form-control form-control-sm meal-sale" value="0"></td>
                                        <td class="meal-total-cost-pkr fw-semibold text-danger fs-12">0</td>
                                        <td class="meal-total-sale-pkr fw-semibold text-success fs-12">0</td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-meal"><i class="mdi mdi-trash-can-outline fs-16"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;

                container.appendChild(newCard);
                initPickers();
                recalculateAll();
            }

            document.getElementById("btnAddNewAccommodation")?.addEventListener("click", addAccommodation);
            document.getElementById("btnAddNewAccommodationBottom")?.addEventListener("click", addAccommodation);

            // Dynamic Transfers Add
            let transCounter = 1;
            document.getElementById("btnAddTransfer")?.addEventListener("click", function() {
                transCounter++;
                const container = document.getElementById("transfersContainer");
                const div = document.createElement("div");
                div.className = "card-repeater-item transfer-item";
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                        <span class="badge bg-soft-info text-info">Transfer #${transCounter}</span>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="mdi mdi-trash-can-outline"></i> Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-routes me-1"></i> Sector Route</label>
                            <select name="transfers[${transCounter-1}][sector]" class="form-select form-select-sm border-primary">
                                ${routesOptions}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-car me-1"></i> Vehicle Type</label>
                            <select name="transfers[${transCounter-1}][vehicle_type]" class="form-select form-select-sm">
                                ${vehiclesOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold">Date & Time</label>
                            <input type="text" name="transfers[${transCounter-1}][date_time]" class="form-control form-control-sm flatpickr-datetime" placeholder="Date & Time">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-car-multiple me-1"></i> No. of Vehicles</label>
                            <input type="number" name="transfers[${transCounter-1}][quantity]" class="form-control form-control-sm border-primary trans-qty" min="0" placeholder="0" value="">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE / Ex. Rate</label>
                            <div class="input-group input-group-sm">
                                <select name="transfers[${transCounter-1}][currency]" class="form-select trans-currency" style="max-width: 75px;">
                                    <option value="PKR" selected>PKR</option>
                                    <option value="SAR">SAR</option>
                                    <option value="USD">USD</option>
                                </select>
                                <input type="number" step="0.01" name="transfers[${transCounter-1}][ex_rate]" class="form-control trans-exrate bg-light" placeholder="1.00" value="1" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Cost (per vehicle)</label>
                            <input type="number" step="any" name="transfers[${transCounter-1}][cost]" class="form-control form-control-sm trans-cost" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Selling (per vehicle)</label>
                            <input type="number" step="any" name="transfers[${transCounter-1}][sale]" class="form-control form-control-sm trans-sale" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                            <select name="transfers[${transCounter-1}][supplier_id]" class="form-select form-select-sm">
                                ${suppliersOptions}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Confirmation #</label>
                            <input type="text" name="transfers[${transCounter-1}][confirmation_number]" class="form-control form-control-sm" placeholder="Confirmation #">
                        </div>
                    </div>
                `;
                container.appendChild(div);
                initPickers();
                recalculateAll();
            });

            // Dynamic Tours Add
            let tourCounter = 1;
            document.getElementById("btnAddTour")?.addEventListener("click", function() {
                tourCounter++;
                const container = document.getElementById("toursContainer");
                const div = document.createElement("div");
                div.className = "card-repeater-item tour-item";
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                        <span class="badge bg-soft-secondary text-secondary">Tour #${tourCounter}</span>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="mdi mdi-trash-can-outline"></i> Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Tour / Ziyarat Name</label>
                            <input type="text" name="tours[${tourCounter-1}][name]" class="form-control form-control-sm" placeholder="e.g. Makkah Holy Places Ziyarat">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold">City</label>
                            <select name="tours[${tourCounter-1}][city]" class="form-select form-select-sm">
                                <option value="Makkah">Makkah</option>
                                <option value="Madinah">Madinah</option>
                                <option value="Taif">Taif</option>
                                <option value="Jeddah">Jeddah</option>
                                <option value="Badr">Badr</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-routes me-1"></i> Sector / Sites</label>
                            <input type="text" name="tours[${tourCounter-1}][sector]" class="form-control form-control-sm border-primary" placeholder="e.g. Jabal Al Noor, Thawr, Arafat, Mina">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold">Tour Date</label>
                            <input type="text" name="tours[${tourCounter-1}][date]" class="form-control form-control-sm flatpickr-date" placeholder="Date">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-car me-1"></i> Quantity / Pax</label>
                            <input type="number" name="tours[${tourCounter-1}][quantity]" class="form-control form-control-sm border-primary tour-qty" min="0" placeholder="0" value="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE / Ex. Rate</label>
                            <div class="input-group input-group-sm">
                                <select name="tours[${tourCounter-1}][currency]" class="form-select tour-currency" style="max-width: 75px;">
                                    <option value="PKR" selected>PKR</option>
                                    <option value="SAR">SAR</option>
                                    <option value="USD">USD</option>
                                </select>
                                <input type="number" step="0.01" name="tours[${tourCounter-1}][ex_rate]" class="form-control tour-exrate bg-light" placeholder="1.00" value="1" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Cost Amount</label>
                            <input type="number" step="any" name="tours[${tourCounter-1}][cost]" class="form-control form-control-sm tour-cost" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Selling Amount</label>
                            <input type="number" step="any" name="tours[${tourCounter-1}][sale]" class="form-control form-control-sm tour-sale" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                            <select name="tours[${tourCounter-1}][supplier_id]" class="form-select form-select-sm">
                                ${suppliersOptions}
                            </select>
                        </div>
                    </div>
                `;
                container.appendChild(div);
                initPickers();
                recalculateAll();
            });

            // Dynamic Trains / Flights Add
            let trainCounter = 1;
            document.getElementById("btnAddTrain")?.addEventListener("click", function() {
                trainCounter++;
                const container = document.getElementById("trainsContainer");
                const div = document.createElement("div");
                div.className = "card-repeater-item train-item";
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                        <span class="badge bg-soft-dark text-dark">Journey #${trainCounter}</span>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="mdi mdi-trash-can-outline"></i> Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary">Transport Mode</label>
                            <input type="text" name="trains[${trainCounter-1}][type]" class="form-control form-control-sm" placeholder="e.g. Haramain High Speed Train, Saudia Flight">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">From Station / Airport</label>
                            <input type="text" name="trains[${trainCounter-1}][from]" class="form-control form-control-sm" placeholder="e.g. Makkah Station / Jeddah Airport">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">To Station / Airport</label>
                            <input type="text" name="trains[${trainCounter-1}][to]" class="form-control form-control-sm" placeholder="e.g. Madinah Station / Riyadh Airport">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Travel Date & Time</label>
                            <input type="text" name="trains[${trainCounter-1}][date_time]" class="form-control form-control-sm flatpickr-datetime" placeholder="Date & Time">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold">Class</label>
                            <select name="trains[${trainCounter-1}][class]" class="form-select form-select-sm">
                                <option value="Economy">Economy Class</option>
                                <option value="Business">Business Class</option>
                                <option value="First">First Class</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold">Tickets (Pax)</label>
                            <input type="number" name="trains[${trainCounter-1}][pax]" class="form-control form-control-sm train-pax" min="0" placeholder="0" value="">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE</label>
                            <div class="input-group input-group-sm">
                                <select name="trains[${trainCounter-1}][currency]" class="form-select train-currency" style="max-width: 65px;">
                                    <option value="PKR" selected>PKR</option>
                                    <option value="SAR">SAR</option>
                                    <option value="USD">USD</option>
                                </select>
                                <input type="number" step="0.01" name="trains[${trainCounter-1}][ex_rate]" class="form-control form-control-sm train-exrate bg-light" placeholder="1.00" value="1" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Cost per Ticket</label>
                            <input type="number" step="any" name="trains[${trainCounter-1}][cost]" class="form-control form-control-sm train-cost" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold">Selling per Ticket</label>
                            <input type="number" step="any" name="trains[${trainCounter-1}][sale]" class="form-control form-control-sm train-sale" value="0">
                        </div>
                    </div>
                `;
                container.appendChild(div);
                initPickers();
                recalculateAll();
            });

            // Dynamic Visas Add
            let visaCounter = 1;
            document.getElementById("btnAddVisa")?.addEventListener("click", function() {
                visaCounter++;
                const container = document.getElementById("visasContainer");
                const div = document.createElement("div");
                div.className = "card-repeater-item visa-item";
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                        <span class="badge bg-soft-primary text-primary">Passenger #${visaCounter} Visa</span>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="mdi mdi-trash-can-outline"></i> Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-account me-1"></i> Passenger Name</label>
                            <input type="text" name="visas[${visaCounter-1}][passenger_name]" class="form-control form-control-sm border-primary" placeholder="Full Name as on Passport">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-account-group me-1"></i> Pax Type</label>
                            <select name="visas[${visaCounter-1}][passenger_type]" class="form-select form-select-sm border-primary">
                                <option value="Adult">Adult</option>
                                <option value="Child">Child (2-11 Yrs)</option>
                                <option value="Infant">Infant (Under 2 Yrs)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-gender-male-female me-1"></i> Gender</label>
                            <select name="visas[${visaCounter-1}][gender]" class="form-select form-select-sm border-primary">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold text-primary">Visa Category</label>
                            <input type="text" name="visas[${visaCounter-1}][type]" class="form-control form-control-sm" placeholder="e.g. Umrah Tourist E-Visa, Visit Visa">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE</label>
                            <div class="input-group input-group-sm">
                                <select name="visas[${visaCounter-1}][currency]" class="form-select visa-currency" style="max-width: 65px;">
                                    <option value="PKR" selected>PKR</option>
                                    <option value="SAR">SAR</option>
                                    <option value="USD">USD</option>
                                </select>
                                <input type="number" step="0.01" name="visas[${visaCounter-1}][ex_rate]" class="form-control form-control-sm border-primary visa-exrate bg-light" placeholder="1.00" value="1" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Passport Number</label>
                            <input type="text" name="visas[${visaCounter-1}][passport_number]" class="form-control form-control-sm" placeholder="Passport #">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Cost per Visa</label>
                            <input type="number" step="any" name="visas[${visaCounter-1}][cost]" class="form-control form-control-sm visa-cost" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Selling per Visa</label>
                            <input type="number" step="any" name="visas[${visaCounter-1}][sale]" class="form-control form-control-sm visa-sale" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                            <select name="visas[${visaCounter-1}][supplier_id]" class="form-select form-select-sm">
                                ${suppliersOptions}
                            </select>
                        </div>
                    </div>
                `;
                container.appendChild(div);
                recalculateAll();
            });

            // Remove dynamic cards
            document.addEventListener("click", function(e) {
                if (e.target.closest(".btn-remove-acc")) {
                    e.target.closest(".accommodation-item").remove();
                    recalculateAll();
                }
                if (e.target.closest(".btn-remove-meal")) {
                    e.target.closest("tr").remove();
                    recalculateAll();
                }
                if (e.target.closest(".btn-remove-item")) {
                    e.target.closest(".card-repeater-item").remove();
                    recalculateAll();
                }
            });

            // Add Separate Meal handler
            document.addEventListener("click", function(e) {
                if (e.target.closest(".btn-add-meal")) {
                    const btn = e.target.closest(".btn-add-meal");
                    const accIdx = btn.dataset.acc || 0;
                    const tbody = document.getElementById("mealTbody_" + accIdx) || btn.closest(".meal-plan-card").querySelector(".meal-tbody");
                    if (tbody) {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>
                                <select name="accommodations[${accIdx}][meals][][type]" class="form-select form-select-sm">
                                    <option value="Breakfast">Breakfast</option>
                                    <option value="Lunch">Lunch</option>
                                    <option value="Dinner" selected>Dinner</option>
                                    <option value="Half Board">Half Board</option>
                                    <option value="Full Board">Full Board</option>
                                    <option value="Suhoor">Suhoor</option>
                                    <option value="Iftar">Iftar Buffet</option>
                                </select>
                            </td>
                            <td><input type="text" name="accommodations[${accIdx}][meals][][city]" class="form-control form-control-sm" placeholder="City" value="Makkah"></td>
                            <td><input type="text" name="accommodations[${accIdx}][meals][][provider]" class="form-control form-control-sm" placeholder="Supplier"></td>
                            <td><input type="number" name="accommodations[${accIdx}][meals][][pax]" class="form-control form-control-sm meal-pax" min="0" placeholder="0" value=""></td>
                            <td><input type="number" name="accommodations[${accIdx}][meals][][days]" class="form-control form-control-sm meal-days" min="0" placeholder="0" value=""></td>
                            <td><input type="number" step="0.01" name="accommodations[${accIdx}][meals][][ex_rate]" class="form-control form-control-sm meal-exrate" placeholder="Ex. Rate" value=""></td>
                            <td><input type="number" step="any" name="accommodations[${accIdx}][meals][][cost]" class="form-control form-control-sm meal-cost" value="0"></td>
                            <td><input type="number" step="any" name="accommodations[${accIdx}][meals][][sale]" class="form-control form-control-sm meal-sale" value="0"></td>
                            <td class="meal-total-cost-pkr fw-semibold text-danger fs-12">0</td>
                            <td class="meal-total-sale-pkr fw-semibold text-success fs-12">0</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-meal"><i class="mdi mdi-trash-can-outline fs-16"></i></button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                        recalculateAll();
                    }
                }
            });

            // Ensure all PKR currency selects lock RoE to 1 and readonly
            document.querySelectorAll(".acc-cost-currency, .acc-selling-currency, .trans-currency, .tour-currency, .train-currency, .visa-currency, select[name*='[currency]']").forEach(function(sel) {
                if (sel.value === 'PKR') {
                    const parentContainer = sel.closest('.input-group, .row, .card-repeater-item, tr');
                    const roeInput = parentContainer?.querySelector('.acc-cost-roe, .acc-selling-roe, .acc-exrate, .trans-exrate, .tour-exrate, .train-exrate, .visa-exrate');
                    if (roeInput) {
                        roeInput.value = '1';
                        roeInput.setAttribute('readonly', 'readonly');
                        roeInput.classList.add('bg-light');
                        roeInput.placeholder = '1.00';
                    }
                }
            });

            // Initial calculation call
            recalculateAll();
            document.querySelectorAll('.accommodation-item').forEach(item => checkStockForRow(item));
        });
    </script>
@endsection
