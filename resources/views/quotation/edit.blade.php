@extends('layout.master')
@section('title', 'Edit Quotation')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        .summary-card {
            background: linear-gradient(135deg, #0f535e, #187280);
            color: #ffffff;
            border-radius: 12px;
            padding: 20px;
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
    </style>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                    <div>
                        <h4 class="fs-18 fw-semibold mb-1">Edit Quotation <span class="text-primary">{{ $quotation->quotation_number }}</span></h4>
                        <p class="text-muted fs-13 mb-0">Modify accommodations, separate meals, transfers, rates, and package pricing.</p>
                    </div>
                    <div>
                        <a href="{{ route('quotation.show', $quotation->id) }}" class="btn btn-info btn-sm me-1 text-white">
                            <i class="mdi mdi-eye me-1"></i> View Proposal
                        </a>
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

                <form action="{{ route('quotation.update', $quotation->id) }}" method="POST" id="quotationEditForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="quotation_id_input" value="{{ $quotation->id }}">

                    <!-- Lead / Client Information Card -->
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold fs-13 mb-1 text-primary">
                                        <i class="mdi mdi-link-variant me-1"></i> Linked Lead
                                    </label>
                                    @if ($quotation->lead_id)
                                        <input type="hidden" name="lead_id" value="{{ $quotation->lead_id }}">
                                        <div class="p-1 px-2 border rounded bg-light d-flex align-items-center justify-content-between" style="min-height: 31px;">
                                            <span class="fs-13 fw-semibold text-primary text-truncate">
                                                Lead #{{ $quotation->lead_id }} - {{ $quotation->lead->contact_person ?? 'Unnamed' }}
                                            </span>
                                            <span class="badge bg-success fs-10"><i class="mdi mdi-lock-outline"></i> Locked</span>
                                        </div>
                                    @else
                                        <select name="lead_id" id="lead_select" class="form-select form-select-sm">
                                            <option value="">-- Standalone Quotation (No Lead) --</option>
                                            @foreach ($leads as $l)
                                                <option value="{{ $l->id }}" {{ $quotation->lead_id == $l->id ? 'selected' : '' }}>
                                                    Lead #{{ $l->id }} - {{ $l->contact_person ?? 'Unnamed' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold fs-13 mb-1">Quotation Title / Ref</label>
                                    <input type="text" name="quotation_title" class="form-control form-control-sm"
                                        value="{{ old('quotation_title', $quotation->quotation_title) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Client Name</label>
                                    <input type="text" name="client_name" id="client_name_input" class="form-control form-control-sm"
                                        value="{{ old('client_name', $quotation->client_name) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Phone Number</label>
                                    <input type="text" name="client_phone" id="client_phone_input" class="form-control form-control-sm"
                                        value="{{ old('client_phone', $quotation->client_phone) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Total Pax</label>
                                    <input type="number" name="total_pax" id="master_total_pax" class="form-control form-control-sm"
                                        min="1" value="{{ old('total_pax', $quotation->total_pax) }}">
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

                                <!-- TAB 1: ACCOMMODATIONS -->
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
                                        @forelse($quotation->accommodations as $index => $acc)
                                            <div class="card-repeater-item accommodation-item" data-index="{{ $index }}">
                                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <span class="badge bg-primary fs-12">Accommodation Stay #{{ $index + 1 }}</span>
                                                    @if($index > 0)
                                                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-acc"><i class="mdi mdi-trash-can-outline"></i> Remove Stay</button>
                                                    @endif
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Check In</label>
                                                        <input type="text" name="accommodations[{{ $index }}][check_in]"
                                                            class="form-control form-control-sm flatpickr-checkin"
                                                            value="{{ $acc->check_in ? date('m/d/Y', strtotime($acc->check_in)) : '' }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Check Out</label>
                                                        <input type="text" name="accommodations[{{ $index }}][check_out]"
                                                            class="form-control form-control-sm flatpickr-checkout"
                                                            value="{{ $acc->check_out ? date('m/d/Y', strtotime($acc->check_out)) : '' }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Number Of Night</label>
                                                        <input type="number" name="accommodations[{{ $index }}][number_of_nights]"
                                                            class="form-control form-control-sm bg-light acc-nights"
                                                            placeholder="0" value="{{ $acc->number_of_nights }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">City Name</label>
                                                        <select name="accommodations[{{ $index }}][city]" class="form-select form-select-sm acc-city-select">
                                                            <option value="Makkah" {{ $acc->city == 'Makkah' ? 'selected' : '' }}>Makkah</option>
                                                            <option value="Madinah" {{ $acc->city == 'Madinah' ? 'selected' : '' }}>Madinah</option>
                                                            <option value="Jeddah" {{ $acc->city == 'Jeddah' ? 'selected' : '' }}>Jeddah</option>
                                                            <option value="Riyadh" {{ $acc->city == 'Riyadh' ? 'selected' : '' }}>Riyadh</option>
                                                            <option value="Taif" {{ $acc->city == 'Taif' ? 'selected' : '' }}>Taif</option>
                                                            <option value="Other" {{ $acc->city == 'Other' ? 'selected' : '' }}>Other</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-hotel me-1"></i> Hotel Name (Active)</label>
                                                        <select name="accommodations[{{ $index }}][hotel_name]" class="form-select form-select-sm hotel-select">
                                                            <option value="" data-id="">Select Hotel</option>
                                                            @foreach ($hotels as $hotel)
                                                                <option value="{{ $hotel->name }}" data-id="{{ $hotel->id }}" {{ ($acc->hotel_id == $hotel->id || $acc->hotel_name == $hotel->name) ? 'selected' : '' }}
                                                                    data-city="{{ $hotel->place ? (is_object($hotel->place) ? $hotel->place->value : $hotel->place) : '' }}">
                                                                    {{ $hotel->name }} @if($hotel->place) ({{ is_object($hotel->place) ? $hotel->place->value : $hotel->place }}) @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="accommodations[{{ $index }}][hotel_id]" class="acc-hotel-id" value="{{ $acc->hotel_id ?? '' }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Room Type</label>
                                                        <select name="accommodations[{{ $index }}][room_type]" class="form-select form-select-sm acc-room-type">
                                                            <option value="Double" {{ $acc->room_type == 'Double' ? 'selected' : '' }}>Double (2 Bed)</option>
                                                            <option value="Triple" {{ $acc->room_type == 'Triple' ? 'selected' : '' }}>Triple (3 Bed)</option>
                                                            <option value="Quad" {{ $acc->room_type == 'Quad' ? 'selected' : '' }}>Quad (4 Bed)</option>
                                                            <option value="Quint" {{ $acc->room_type == 'Quint' ? 'selected' : '' }}>Quint (5 Bed)</option>
                                                            <option value="Single" {{ $acc->room_type == 'Single' ? 'selected' : '' }}>Single (1 Bed)</option>
                                                            <option value="Sharing" {{ $acc->room_type == 'Sharing' ? 'selected' : '' }}>Sharing</option>
                                                            <option value="Suite" {{ $acc->room_type == 'Suite' ? 'selected' : '' }}>Suite / Family</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-eye-outline me-1"></i> Room View</label>
                                                        <select name="accommodations[{{ $index }}][room_view]" class="form-select form-select-sm border-primary">
                                                            <option value="City View" {{ $acc->room_view == 'City View' ? 'selected' : '' }}>City View</option>
                                                            <option value="Haram View" {{ $acc->room_view == 'Haram View' ? 'selected' : '' }}>Haram View</option>
                                                            <option value="Kaaba View" {{ $acc->room_view == 'Kaaba View' ? 'selected' : '' }}>Kaaba View</option>
                                                            <option value="Partial Haram View" {{ $acc->room_view == 'Partial Haram View' ? 'selected' : '' }}>Partial Haram View</option>
                                                            <option value="Partial Kaaba View" {{ $acc->room_view == 'Partial Kaaba View' ? 'selected' : '' }}>Partial Kaaba View</option>
                                                            <option value="Land View (Int.)" {{ $acc->room_view == 'Land View (Int.)' ? 'selected' : '' }}>Land View (International)</option>
                                                            <option value="Sea View (Int.)" {{ $acc->room_view == 'Sea View (Int.)' ? 'selected' : '' }}>Sea View (International)</option>
                                                            <option value="Courtyard View" {{ $acc->room_view == 'Courtyard View' ? 'selected' : '' }}>Courtyard View</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-pound me-1"></i> Confirmation #</label>
                                                        <input type="text" name="accommodations[{{ $index }}][confirmation_number]"
                                                            class="form-control form-control-sm border-primary"
                                                            value="{{ $acc->confirmation_number }}">
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-13 fw-semibold">Meal Plan (Hotel Included)</label>
                                                        <select name="accommodations[{{ $index }}][meal_plan]" class="form-select form-select-sm">
                                                            <option value="Room Only" {{ $acc->meal_plan == 'Room Only' ? 'selected' : '' }}>Room Only (RO)</option>
                                                            <option value="Bed & Breakfast" {{ $acc->meal_plan == 'Bed & Breakfast' ? 'selected' : '' }}>Bed & Breakfast (BB)</option>
                                                            <option value="Half Board" {{ $acc->meal_plan == 'Half Board' ? 'selected' : '' }}>Half Board (HB)</option>
                                                            <option value="Full Board" {{ $acc->meal_plan == 'Full Board' ? 'selected' : '' }}>Full Board (FB)</option>
                                                            <option value="Suhoor" {{ $acc->meal_plan == 'Suhoor' ? 'selected' : '' }}>Suhoor Included</option>
                                                            <option value="Iftar Dinner" {{ $acc->meal_plan == 'Iftar Dinner' ? 'selected' : '' }}>Iftar Dinner Included</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <label class="form-label fs-13 fw-semibold mb-0 acc-rooms-label">{{ $acc->room_type == 'Sharing' ? 'No Of Beds (Total)' : 'No Of Room' }}</label>
                                                            <span class="acc-stock-badge badge bg-soft-secondary text-secondary fs-10">Check Stock</span>
                                                        </div>
                                                        <input type="number" name="accommodations[{{ $index }}][no_of_rooms]"
                                                            class="form-control form-control-sm acc-rooms {{ $acc->room_type == 'Sharing' ? 'bg-light' : '' }}" min="0" placeholder="0" value="{{ $acc->no_of_rooms }}" {{ $acc->room_type == 'Sharing' ? 'readonly' : '' }}>
                                                        <div class="acc-stock-warning text-danger fs-11 mt-1 d-none"></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-13 fw-semibold">Per Night Rate</label>
                                                        <input type="number" step="any" name="accommodations[{{ $index }}][per_night_rate]"
                                                            class="form-control form-control-sm acc-per-night" value="{{ $acc->per_night_rate ?? 0 }}">
                                                    </div>
                                                </div>

                                                <!-- Row 3B: Sharing Bed Details -->
                                                <div class="acc-sharing-beds-box row g-2 mb-3 p-2 border rounded bg-light {{ $acc->room_type == 'Sharing' ? '' : 'd-none' }}">
                                                    <div class="col-md-12 mb-1">
                                                        <span class="fs-12 fw-bold text-primary"><i class="mdi mdi-bed-empty me-1"></i> Sharing Bed Allocation (Gender Wise)</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-12 fw-semibold text-primary"><i class="mdi mdi-gender-male me-1"></i> Male Beds</label>
                                                        <input type="number" name="accommodations[{{ $index }}][male_beds]" class="form-control form-control-sm acc-male-beds text-center border-primary" min="0" placeholder="0" value="{{ $acc->male_beds ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-12 fw-semibold text-danger"><i class="mdi mdi-gender-female me-1"></i> Female Beds</label>
                                                        <input type="number" name="accommodations[{{ $index }}][female_beds]" class="form-control form-control-sm acc-female-beds text-center border-danger" min="0" placeholder="0" value="{{ $acc->female_beds ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-12 fw-semibold text-dark"><i class="mdi mdi-counter me-1"></i> Total Beds</label>
                                                        <input type="number" name="accommodations[{{ $index }}][no_of_beds]" class="form-control form-control-sm acc-total-beds text-center fw-bold bg-light" min="0" placeholder="0" value="{{ $acc->no_of_beds ?? 0 }}" readonly>
                                                    </div>
                                                    <div class="acc-sharing-warning text-danger fs-11 mt-1 col-md-12 d-none"></div>
                                                </div>

                                                <!-- Row 4A: Cost Side -->
                                                <div class="row g-2 mb-2 p-2 border rounded bg-soft-light align-items-center">
                                                    <div class="col-md-2">
                                                        <span class="fs-12 fw-bold text-danger text-uppercase d-block mb-1"><i class="mdi mdi-cash-minus me-1"></i> Cost Side:</span>
                                                        <select name="accommodations[{{ $index }}][cost_currency]" class="form-select form-select-sm acc-cost-currency">
                                                            <option value="PKR" {{ ($acc->cost_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                            <option value="SAR" {{ ($acc->cost_currency ?? ($acc->currency ?? 'PKR')) == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                            <option value="USD" {{ ($acc->cost_currency ?? ($acc->currency ?? 'PKR')) == 'USD' ? 'selected' : '' }}>USD</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-12 fw-semibold text-danger mb-1"><i class="mdi mdi-swap-horizontal me-1"></i> Cost ROE</label>
                                                        <input type="number" step="0.0001" name="accommodations[{{ $index }}][cost_exchange_rate]"
                                                            class="form-control form-control-sm border-danger acc-cost-roe {{ ($acc->cost_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 'bg-light' : '' }}" placeholder="1.00"
                                                            value="{{ ($acc->cost_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 1 : ($acc->cost_exchange_rate ?? ($acc->exchange_rate ?? 1)) }}" {{ ($acc->cost_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 'readonly' : '' }}>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-12 fw-semibold text-danger mb-1">Cost Amount (Foreign)</label>
                                                        <input type="number" step="any" name="accommodations[{{ $index }}][cost_amount]"
                                                            class="form-control form-control-sm acc-cost" value="{{ $acc->cost_amount ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-12 fw-semibold text-danger mb-1">Cost PKR</label>
                                                        <div class="fs-13 fw-bold text-danger pt-1 acc-cost-pkr-badge">PKR {{ number_format($acc->cost_amount_pkr ?? 0) }}</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-12 fw-semibold text-danger mb-1"><i class="mdi mdi-truck-delivery-outline me-1"></i> Supplier / Vendor</label>
                                                        <select name="accommodations[{{ $index }}][supplier_id]" class="form-select form-select-sm">
                                                            <option value="">-- Select Supplier / Vendor --</option>
                                                            @foreach ($clients as $client)
                                                                @if($client->type == 'vendor' || $client->type == 'client')
                                                                    <option value="{{ $client->id }}" {{ $acc->supplier_id == $client->id ? 'selected' : '' }}>
                                                                        {{ $client->name }} ({{ ucfirst($client->type) }}{{ $client->company_name ? ' - ' . $client->company_name : '' }})
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                            @foreach ($companies as $comp)
                                                                <option value="{{ $comp->id }}" {{ $acc->supplier_id == $comp->id ? 'selected' : '' }}>{{ $comp->company_name }} (Company)</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Row 4B: Sale Side -->
                                                <div class="row g-2 mb-3 p-2 border rounded bg-soft-success align-items-center">
                                                    <div class="col-md-2">
                                                        <span class="fs-12 fw-bold text-success text-uppercase d-block mb-1"><i class="mdi mdi-cash-plus me-1"></i> Sale Side:</span>
                                                        <select name="accommodations[{{ $index }}][selling_currency]" class="form-select form-select-sm acc-selling-currency">
                                                            <option value="PKR" {{ ($acc->selling_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                            <option value="SAR" {{ ($acc->selling_currency ?? ($acc->currency ?? 'PKR')) == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                            <option value="USD" {{ ($acc->selling_currency ?? ($acc->currency ?? 'PKR')) == 'USD' ? 'selected' : '' }}>USD</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-12 fw-semibold text-success mb-1"><i class="mdi mdi-swap-horizontal me-1"></i> Selling ROE</label>
                                                        <input type="number" step="0.0001" name="accommodations[{{ $index }}][selling_exchange_rate]"
                                                            class="form-control form-control-sm border-success acc-selling-roe {{ ($acc->selling_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 'bg-light' : '' }}" placeholder="1.00"
                                                            value="{{ ($acc->selling_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 1 : ($acc->selling_exchange_rate ?? ($acc->exchange_rate ?? 1)) }}" {{ ($acc->selling_currency ?? ($acc->currency ?? 'PKR')) == 'PKR' ? 'readonly' : '' }}>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-12 fw-semibold text-success mb-1">Selling Amount (Foreign)</label>
                                                        <input type="number" step="any" name="accommodations[{{ $index }}][selling_amount]"
                                                            class="form-control form-control-sm acc-selling" value="{{ $acc->selling_amount ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-12 fw-semibold text-success mb-1">Selling PKR</label>
                                                        <div class="fs-13 fw-bold text-success pt-1 acc-sale-pkr-badge">PKR {{ number_format($acc->selling_amount_pkr ?? 0) }}</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-12 fw-semibold text-primary mb-1">Estimated Stay Profit</label>
                                                        <div class="fs-13 fw-bold text-primary pt-1 acc-profit-pkr-badge">PKR {{ number_format(($acc->selling_amount_pkr ?? 0) - ($acc->cost_amount_pkr ?? 0)) }}</div>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fs-13 fw-semibold">Cancellation Deadline</label>
                                                        <input type="text" name="accommodations[{{ $index }}][cancellation_deadline]"
                                                            class="form-control form-control-sm flatpickr-date"
                                                            value="{{ $acc->cancellation_deadline ? date('m/d/Y', strtotime($acc->cancellation_deadline)) : '' }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fs-13 fw-semibold">Finalization Date</label>
                                                        <input type="text" name="accommodations[{{ $index }}][finalization_date]"
                                                            class="form-control form-control-sm flatpickr-date"
                                                            value="{{ $acc->finalization_date ? date('m/d/Y', strtotime($acc->finalization_date)) : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted">No accommodations added yet.</p>
                                        @endforelse
                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-primary next-tab-btn" data-target="#transfers-tab">
                                            Next: Transfers <i class="mdi mdi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- TAB 2: TRANSFERS -->
                                <div class="tab-pane fade" id="transfers-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fs-16 fw-semibold text-primary mb-0">Vehicle Transfers & Ground Transportation</h5>
                                    </div>

                                    <div id="transfersContainer">
                                        @forelse($quotation->transfers as $index => $tr)
                                            <div class="card-repeater-item transfer-item">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-routes me-1"></i> Sector Route</label>
                                                        <select name="transfers[{{ $index }}][sector]" class="form-select form-select-sm border-primary">
                                                            <option value="">Select Sector Route</option>
                                                            @foreach ($routes as $r)
                                                                <option value="{{ $r->start_place }} - {{ $r->end_place }}" {{ $tr->sector_route == ($r->start_place . ' - ' . $r->end_place) ? 'selected' : '' }}>
                                                                    {{ $r->start_place }} - {{ $r->end_place }}
                                                                </option>
                                                            @endforeach
                                                            @foreach ($travelRoutes as $travelR)
                                                                <option value="{{ $travelR->name }}" {{ $tr->sector_route == $travelR->name ? 'selected' : '' }}>{{ $travelR->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-car me-1"></i> Vehicle Type</label>
                                                        <select name="transfers[{{ $index }}][vehicle_type]" class="form-select form-select-sm">
                                                            <option value="">Select Vehicle Type</option>
                                                            @foreach ($vehicles as $v)
                                                                <option value="{{ $v->vehicle_type }}" {{ $tr->vehicle_type == $v->vehicle_type ? 'selected' : '' }}>
                                                                    {{ $v->vehicle_type }} ({{ $v->brand_name }} {{ $v->model_year }})
                                                                </option>
                                                            @endforeach
                                                            <option value="Sedan Car (Camry/Sonata)" {{ $tr->vehicle_type == 'Sedan Car (Camry/Sonata)' ? 'selected' : '' }}>Sedan Car (Camry/Sonata)</option>
                                                            <option value="GMC / Yukon (SUV)" {{ $tr->vehicle_type == 'GMC / Yukon (SUV)' ? 'selected' : '' }}>GMC / Yukon (SUV)</option>
                                                            <option value="Toyota Hiace (10 Seater)" {{ $tr->vehicle_type == 'Toyota Hiace (10 Seater)' ? 'selected' : '' }}>Toyota Hiace (10 Seater)</option>
                                                            <option value="Toyota Coaster (20-30 Seater)" {{ $tr->vehicle_type == 'Toyota Coaster (20-30 Seater)' ? 'selected' : '' }}>Toyota Coaster (20-30 Seater)</option>
                                                            <option value="Luxury Bus (49 Seater)" {{ $tr->vehicle_type == 'Luxury Bus (49 Seater)' ? 'selected' : '' }}>Luxury Bus (49 Seater)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold">No. of Vehicles</label>
                                                        <input type="number" name="transfers[{{ $index }}][quantity]" class="form-control form-control-sm border-primary trans-qty" min="0" placeholder="0" value="{{ $tr->quantity }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE / Ex. Rate</label>
                                                        <div class="input-group input-group-sm">
                                                            <select name="transfers[{{ $index }}][currency]" class="form-select trans-currency" style="max-width: 75px;">
                                                                <option value="PKR" {{ ($tr->currency ?? 'PKR') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                                <option value="SAR" {{ ($tr->currency ?? '') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                                <option value="USD" {{ ($tr->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                                            </select>
                                                            <input type="number" step="0.01" name="transfers[{{ $index }}][ex_rate]" class="form-control form-control-sm trans-exrate {{ ($tr->currency ?? 'PKR') == 'PKR' ? 'bg-light' : '' }}" {{ ($tr->currency ?? 'PKR') == 'PKR' ? 'readonly' : '' }} placeholder="1.00" value="{{ ($tr->currency ?? 'PKR') == 'PKR' ? ($tr->exchange_rate ?: 1) : $tr->exchange_rate }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold">Cost (per vehicle)</label>
                                                        <input type="number" step="any" name="transfers[{{ $index }}][cost]" class="form-control form-control-sm trans-cost" value="{{ $tr->cost_amount ?? ($tr->cost_per_vehicle ?? 0) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Selling (per vehicle)</label>
                                                        <input type="number" step="any" name="transfers[{{ $index }}][sale]" class="form-control form-control-sm trans-sale" value="{{ $tr->selling_amount ?? ($tr->selling_per_vehicle ?? 0) }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                                                        <select name="transfers[{{ $index }}][supplier_id]" class="form-select form-select-sm">
                                                            <option value="">-- Select Supplier / Vendor --</option>
                                                            @foreach ($clients as $client)
                                                                @if($client->type == 'vendor' || $client->type == 'client')
                                                                    <option value="{{ $client->id }}" {{ $tr->supplier_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                                                @endif
                                                            @endforeach
                                                            @foreach ($companies as $comp)
                                                                <option value="{{ $comp->id }}" {{ $tr->supplier_id == $comp->id ? 'selected' : '' }}>{{ $comp->company_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted">No transfers added yet.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- TAB 3: TOURS -->
                                <div class="tab-pane fade" id="tours-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fs-16 fw-semibold text-primary mb-0">Ziyarat, Tours & Sightseeing</h5>
                                    </div>
                                    <div id="toursContainer">
                                        @forelse($quotation->tours as $index => $t)
                                            <div class="card-repeater-item tour-item">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Tour / Ziyarat Name</label>
                                                        <input type="text" name="tours[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $t->tour_name }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-routes me-1"></i> Sector / Sites</label>
                                                        <input type="text" name="tours[{{ $index }}][sector]" class="form-control form-control-sm border-primary" value="{{ $t->sector }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold">Quantity / Pax</label>
                                                        <input type="number" name="tours[{{ $index }}][quantity]" class="form-control form-control-sm border-primary tour-qty" min="0" placeholder="0" value="{{ $t->quantity }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE</label>
                                                        <div class="input-group input-group-sm">
                                                            <select name="tours[{{ $index }}][currency]" class="form-select tour-currency" style="max-width: 65px;">
                                                                <option value="PKR" {{ ($t->currency ?? 'PKR') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                                <option value="SAR" {{ ($t->currency ?? '') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                                <option value="USD" {{ ($t->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                                            </select>
                                                            <input type="number" step="0.01" name="tours[{{ $index }}][ex_rate]" class="form-control form-control-sm tour-exrate {{ ($t->currency ?? 'PKR') == 'PKR' ? 'bg-light' : '' }}" {{ ($t->currency ?? 'PKR') == 'PKR' ? 'readonly' : '' }} placeholder="1.00" value="{{ ($t->currency ?? 'PKR') == 'PKR' ? ($t->exchange_rate ?: 1) : $t->exchange_rate }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Cost Amount</label>
                                                        <input type="number" step="any" name="tours[{{ $index }}][cost]" class="form-control form-control-sm tour-cost" value="{{ $t->cost_amount ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Selling Amount</label>
                                                        <input type="number" step="any" name="tours[{{ $index }}][sale]" class="form-control form-control-sm tour-sale" value="{{ $t->selling_amount ?? 0 }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted">No tours added yet.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- TAB 4: FLIGHTS / TRAIN -->
                                <div class="tab-pane fade" id="flights-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fs-16 fw-semibold text-primary mb-0">Haramain Train & Domestic Flights</h5>
                                    </div>
                                    <div id="trainsContainer">
                                        @forelse($quotation->flightsTrains as $index => $ft)
                                            <div class="card-repeater-item train-item">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary">Transport Mode</label>
                                                        <input type="text" name="trains[{{ $index }}][type]" class="form-control form-control-sm" value="{{ $ft->service_type ?? $ft->mode_type }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">From</label>
                                                        <input type="text" name="trains[{{ $index }}][from]" class="form-control form-control-sm" value="{{ $ft->from_location ?? $ft->from_station }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold">To</label>
                                                        <input type="text" name="trains[{{ $index }}][to]" class="form-control form-control-sm" value="{{ $ft->to_location ?? $ft->to_station }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE</label>
                                                        <div class="input-group input-group-sm">
                                                            <select name="trains[{{ $index }}][currency]" class="form-select train-currency" style="max-width: 65px;">
                                                                <option value="PKR" {{ ($ft->currency ?? 'PKR') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                                <option value="SAR" {{ ($ft->currency ?? '') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                                <option value="USD" {{ ($ft->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                                            </select>
                                                            <input type="number" step="0.01" name="trains[{{ $index }}][ex_rate]" class="form-control form-control-sm train-exrate {{ ($ft->currency ?? 'PKR') == 'PKR' ? 'bg-light' : '' }}" {{ ($ft->currency ?? 'PKR') == 'PKR' ? 'readonly' : '' }} placeholder="1.00" value="{{ ($ft->currency ?? 'PKR') == 'PKR' ? ($ft->exchange_rate ?: 1) : $ft->exchange_rate }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold">Selling</label>
                                                        <input type="number" step="any" name="trains[{{ $index }}][sale]" class="form-control form-control-sm" value="{{ $ft->selling_amount ?? ($ft->selling_price_sar ?? 0) }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted">No flight/train journeys added yet.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- TAB 5: VISA CHARGES -->
                                <div class="tab-pane fade" id="visa-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fs-16 fw-semibold text-primary mb-0">Visa Charges & Passenger Details</h5>
                                    </div>
                                    <div id="visasContainer">
                                        @forelse($quotation->visas as $index => $v)
                                            <div class="card-repeater-item visa-item">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-account me-1"></i> Passenger Name</label>
                                                        <input type="text" name="visas[{{ $index }}][passenger_name]" class="form-control form-control-sm border-primary" value="{{ $v->passenger_name }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary">Pax Type</label>
                                                        <select name="visas[{{ $index }}][passenger_type]" class="form-select form-select-sm">
                                                            <option value="Adult" {{ $v->passenger_type == 'Adult' ? 'selected' : '' }}>Adult</option>
                                                            <option value="Child" {{ $v->passenger_type == 'Child' ? 'selected' : '' }}>Child</option>
                                                            <option value="Infant" {{ $v->passenger_type == 'Infant' ? 'selected' : '' }}>Infant</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary">Gender</label>
                                                        <select name="visas[{{ $index }}][gender]" class="form-select form-select-sm">
                                                            <option value="Male" {{ $v->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                                            <option value="Female" {{ $v->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold text-primary">Visa Category</label>
                                                        <input type="text" name="visas[{{ $index }}][type]" class="form-control form-control-sm" value="{{ $v->visa_type }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE</label>
                                                        <div class="input-group input-group-sm">
                                                            <select name="visas[{{ $index }}][currency]" class="form-select visa-currency" style="max-width: 65px;">
                                                                <option value="PKR" {{ ($v->currency ?? 'PKR') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                                <option value="SAR" {{ ($v->currency ?? '') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                                <option value="USD" {{ ($v->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                                            </select>
                                                            <input type="number" step="0.01" name="visas[{{ $index }}][ex_rate]" class="form-control form-control-sm border-primary visa-exrate {{ ($v->currency ?? 'PKR') == 'PKR' ? 'bg-light' : '' }}" {{ ($v->currency ?? 'PKR') == 'PKR' ? 'readonly' : '' }} placeholder="1.00" value="{{ ($v->currency ?? 'PKR') == 'PKR' ? ($v->exchange_rate ?: 1) : $v->exchange_rate }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted">No visa passengers added yet.</p>
                                        @endforelse
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
                                                            placeholder="0" value="{{ old('psf_selling_pkr', $quotation->psf_selling_pkr ?? ($quotation->psf_selling_amount ?? 0)) }}">
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
                                <!-- TAB 7: FULL SUMMARY & INCLUSIONS / TERMS -->
                                <!-- ========================================================================= -->
                                <div class="tab-pane fade" id="summary-pane" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="fs-16 fw-semibold text-primary mb-0">7. Full Quotation Summary & Profit/Loss Analysis</h5>
                                            <small class="text-muted">Comprehensive cost and selling calculation across all facilities with net profit/margin.</small>
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
                                                            <td class="text-end fw-semibold text-danger" id="tblAccCost">PKR {{ number_format($quotation->accommodations->sum('cost_amount_pkr')) }}</td>
                                                            <td class="text-end fw-semibold text-success" id="tblAccSale">PKR {{ number_format($quotation->accommodations->sum('selling_amount_pkr')) }}</td>
                                                            <td class="text-end fw-bold" id="tblAccProfit">PKR {{ number_format($quotation->accommodations->sum('selling_amount_pkr') - $quotation->accommodations->sum('cost_amount_pkr')) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-silverware-fork-knife text-warning me-1"></i> 2. Separate Meal Plans</td>
                                                            <td>Independent Catering, Dinners, Suhoor & Iftar services</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblMealCost">PKR {{ number_format($quotation->mealPlans->sum('total_cost_pkr')) }}</td>
                                                            <td class="text-end fw-semibold text-success" id="tblMealSale">PKR {{ number_format($quotation->mealPlans->sum('total_sale_pkr')) }}</td>
                                                            <td class="text-end fw-bold" id="tblMealProfit">PKR {{ number_format($quotation->mealPlans->sum('total_sale_pkr') - $quotation->mealPlans->sum('total_cost_pkr')) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-car-multiple text-info me-1"></i> 3. Transfers & Transport</td>
                                                            <td>Intercity Sectors, Airport transfers & private transport</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblTransCost">PKR {{ number_format($quotation->transfers->sum('cost_amount_pkr')) }}</td>
                                                            <td class="text-end fw-semibold text-success" id="tblTransSale">PKR {{ number_format($quotation->transfers->sum('selling_amount_pkr')) }}</td>
                                                            <td class="text-end fw-bold" id="tblTransProfit">PKR {{ number_format($quotation->transfers->sum('selling_amount_pkr') - $quotation->transfers->sum('cost_amount_pkr')) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-map-marker-distance text-secondary me-1"></i> 4. Tours & Sightseeing / Ziyarat</td>
                                                            <td>Holy Places Ziyarat, Taif & Historical Tours</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblTourCost">PKR {{ number_format($quotation->tours->sum('cost_amount_pkr')) }}</td>
                                                            <td class="text-end fw-semibold text-success" id="tblTourSale">PKR {{ number_format($quotation->tours->sum('selling_amount_pkr')) }}</td>
                                                            <td class="text-end fw-bold" id="tblTourProfit">PKR {{ number_format($quotation->tours->sum('selling_amount_pkr') - $quotation->tours->sum('cost_amount_pkr')) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-train-car text-dark me-1"></i> 5. Domestic Flights & Fast Train</td>
                                                            <td>Haramain High Speed Train (HHR) & Domestic Tickets</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblTrainCost">PKR {{ number_format($quotation->flightsTrains->sum('cost_amount_pkr')) }}</td>
                                                            <td class="text-end fw-semibold text-success" id="tblTrainSale">PKR {{ number_format($quotation->flightsTrains->sum('selling_amount_pkr')) }}</td>
                                                            <td class="text-end fw-bold" id="tblTrainProfit">PKR {{ number_format($quotation->flightsTrains->sum('selling_amount_pkr') - $quotation->flightsTrains->sum('cost_amount_pkr')) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-passport text-primary me-1"></i> 6. Visa Charges & Processing</td>
                                                            <td>Saudi Umrah / Visit Visas, Mofa and Passenger Issuance</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblVisaCost">PKR {{ number_format($quotation->visas->sum('cost_amount_pkr')) }}</td>
                                                            <td class="text-end fw-semibold text-success" id="tblVisaSale">PKR {{ number_format($quotation->visas->sum('selling_amount_pkr')) }}</td>
                                                            <td class="text-end fw-bold" id="tblVisaProfit">PKR {{ number_format($quotation->visas->sum('selling_amount_pkr') - $quotation->visas->sum('cost_amount_pkr')) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><i class="mdi mdi-tag-outline text-info me-1"></i> 7. PSF</td>
                                                            <td>Airport & PSF / Taxes / Surcharges</td>
                                                            <td class="text-end fw-semibold text-danger" id="tblPsfCost">PKR {{ number_format($quotation->psf_cost_pkr ?? 0) }}</td>
                                                            <td class="text-end fw-semibold text-success" id="tblPsfSale">PKR {{ number_format($quotation->psf_selling_pkr ?? 0) }}</td>
                                                            <td class="text-end fw-bold" id="tblPsfProfit">PKR {{ number_format(($quotation->psf_selling_pkr ?? 0) - ($quotation->psf_cost_pkr ?? 0)) }}</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="table-light fs-14">
                                                        <tr>
                                                            <th colspan="2" class="text-uppercase fw-bold">Grand Totals:</th>
                                                            <th class="text-end text-danger fs-15 fw-bold" id="grandCostSummary">PKR {{ number_format($quotation->total_cost_pkr) }}</th>
                                                            <th class="text-end text-success fs-15 fw-bold" id="grandSaleSummary">PKR {{ number_format($quotation->total_sale_pkr) }}</th>
                                                            <th class="text-end text-primary fs-15 fw-bold" id="grandProfitSummary">PKR {{ number_format($quotation->total_profit_pkr) }}</th>
                                                        </tr>
                                                        <tr class="table-warning border-top">
                                                            <th colspan="2" class="text-uppercase fw-bold text-dark"><i class="mdi mdi-handshake me-1"></i> Total Supplier Amount (Payable):</th>
                                                            <th colspan="3" class="text-end text-dark fs-15 fw-bold" id="summaryTotalSupplier">PKR {{ number_format($quotation->total_supplier_amount ?? 0) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metric Banner -->
                                    <div class="summary-card mb-4" style="background: linear-gradient(135deg, #1b1f4b 0%, #2c326e 100%); padding: 20px; border-radius: 8px; color: #fff;">
                                        <div class="row text-center">
                                            <div class="col-md-3 border-end">
                                                <span class="fs-13 text-white-50">Total Package Cost</span>
                                                <h3 class="fw-bold text-white mb-0" id="cardCost">PKR {{ number_format($quotation->total_cost_pkr) }}</h3>
                                            </div>
                                            <div class="col-md-3 border-end">
                                                <span class="fs-13 text-white-50">Total Package Sale</span>
                                                <h3 class="fw-bold text-white mb-0" id="cardSale">PKR {{ number_format($quotation->total_sale_pkr) }}</h3>
                                            </div>
                                            <div class="col-md-3 border-end">
                                                <span class="fs-13 text-white-50">Net Profit</span>
                                                <h3 class="fw-bold text-warning mb-0" id="cardProfit">PKR {{ number_format($quotation->total_profit_pkr) }}</h3>
                                                <small class="text-white-50" id="cardMargin">Margin: {{ $quotation->margin_percentage }}%</small>
                                            </div>
                                            <div class="col-md-3">
                                                <span class="fs-13 text-white-50">Selling Price Per Pax</span>
                                                <h3 class="fw-bold text-white mb-0" id="cardPerPax">PKR {{ number_format($quotation->per_pax_sale_pkr) }}</h3>
                                                <small class="text-white-50">Total: <span id="summaryPaxCount">{{ $quotation->total_pax ?: 1 }}</span> Person(s)</small>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-2 border-top border-white-50 text-center">
                                            <span class="fs-13 text-white-50"><i class="mdi mdi-handshake me-1"></i> Total Supplier Payable:</span>
                                            <strong class="text-white fs-15 ms-2" id="cardSupplier">PKR {{ number_format($quotation->total_supplier_amount ?? 0) }}</strong>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <label class="form-label fs-14 fw-semibold text-primary">
                                                <i class="mdi mdi-check-all me-1"></i> Package Inclusions (Full Page)
                                            </label>
                                            <textarea name="inclusions" class="form-control summernote" rows="5">{!! old('inclusions', $quotation->inclusions) !!}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fs-14 fw-semibold text-primary">
                                                <i class="mdi mdi-file-document-outline me-1"></i> Terms & Special Conditions (Full Page)
                                            </label>
                                            <textarea name="terms" class="form-control summernote" rows="5">{!! old('terms', $quotation->terms_and_conditions) !!}</textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-secondary prev-tab-btn" data-target="#psf-tab">
                                            <i class="mdi mdi-arrow-left me-1"></i> Back: 6. PSF
                                        </button>
                                        <div>
                                            <a href="{{ route('quotation.show', $quotation->id) }}" class="btn btn-secondary me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary px-4 py-2">
                                                <i class="mdi mdi-content-save-check me-1"></i> Update Quotation
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </form>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {
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

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                initSummernote();
            });

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
            }

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

            document.addEventListener("input", function(e) {
                if (e.target.matches(".acc-nights, .acc-rooms, .acc-per-night")) {
                    const accItem = e.target.closest(".accommodation-item");
                    if (accItem) {
                        calculateAccommodationCost(accItem);
                    }
                }
            });

            const hotelsOptions = `
                <option value="" data-id="">Select Hotel</option>
                @foreach ($hotels as $hotel)
                    <option value="{{ $hotel->name }}" data-id="{{ $hotel->id }}" data-city="{{ $hotel->place ? (is_object($hotel->place) ? $hotel->place->value : $hotel->place) : '' }}">
                        {{ $hotel->name }} @if($hotel->place) ({{ is_object($hotel->place) ? $hotel->place->value : $hotel->place }}) @endif
                    </option>
                @endforeach
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
                const quotationId = document.getElementById('quotation_id_input') ? document.getElementById('quotation_id_input').value : '{{ $quotation->id }}';

                const url = new URL('{{ route("room-inventory.check-availability") }}', window.location.origin);
                if (hotelId) url.searchParams.set('hotel_id', hotelId);
                if (hotelName) url.searchParams.set('hotel_name', hotelName);
                url.searchParams.set('room_type', roomType);
                if (checkIn) url.searchParams.set('check_in', checkIn);
                if (checkOut) url.searchParams.set('check_out', checkOut);
                url.searchParams.set('requested_rooms', requested > 0 ? requested : 1);
                if (quotationId) url.searchParams.set('exclude_quotation_id', quotationId);

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
            document.getElementById('quotationEditForm')?.addEventListener('submit', function(e) {
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
                            errorMsg = `Cannot update quotation: Male sharing beds (${valMale}) exceeds available stock (${maxMale})!`;
                            firstErrorElem = maleInput;
                        } else if (!isNaN(maxFemale) && valFemale > maxFemale) {
                            hasError = true;
                            errorMsg = `Cannot update quotation: Female sharing beds (${valFemale}) exceeds available stock (${maxFemale})!`;
                            firstErrorElem = femaleInput;
                        } else if (!isNaN(maxRooms) && (valMale + valFemale) > maxRooms) {
                            hasError = true;
                            errorMsg = `Cannot update quotation: Total sharing beds (${valMale + valFemale}) exceeds available stock (${maxRooms})!`;
                            firstErrorElem = maleInput || roomsInput;
                        }
                    } else {
                        if (!isNaN(maxRooms) && valRooms > maxRooms) {
                            hasError = true;
                            errorMsg = `Cannot update quotation: Room count (${valRooms}) exceeds available stock (${maxRooms})!`;
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

            flatpickr(".flatpickr-date", { dateFormat: "m/d/Y" });
            flatpickr(".flatpickr-datetime", { enableTime: true, dateFormat: "m/d/Y h:i K" });

            document.querySelectorAll(".next-tab-btn").forEach(function(btn) {
                btn.addEventListener("click", function() {
                    const targetTabId = this.getAttribute("data-target");
                    const triggerEl = document.querySelector(targetTabId);
                    if (triggerEl) {
                        const tab = new bootstrap.Tab(triggerEl);
                        tab.show();
                    }
                });
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
                    const qty = parseFloat(tr.querySelector(".trans-qty, input[name*='[quantity]']")?.value) || 0;
                    const curr = tr.querySelector(".trans-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = tr.querySelector(".trans-exrate, input[name*='[ex_rate]']")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(tr.querySelector(".trans-cost, input[name*='[cost]']")?.value) || 0;
                    const sale = parseFloat(tr.querySelector(".trans-sale, input[name*='[sale]']")?.value) || 0;

                    totalTransCostPkr += cost * (qty > 0 ? qty : 1) * ex;
                    totalTransSalePkr += sale * (qty > 0 ? qty : 1) * ex;
                });

                // 3. Tours
                document.querySelectorAll(".tour-item").forEach(function(t) {
                    const qty = parseFloat(t.querySelector(".tour-qty, input[name*='[quantity]']")?.value) || 0;
                    const curr = t.querySelector(".tour-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = t.querySelector(".tour-exrate, input[name*='[ex_rate]']")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(t.querySelector(".tour-cost, input[name*='[cost]']")?.value) || 0;
                    const sale = parseFloat(t.querySelector(".tour-sale, input[name*='[sale]']")?.value) || 0;

                    totalTourCostPkr += cost * (qty > 0 ? qty : 1) * ex;
                    totalTourSalePkr += sale * (qty > 0 ? qty : 1) * ex;
                });

                // 4. Trains / Flights
                document.querySelectorAll(".train-item").forEach(function(tr) {
                    const pax = parseFloat(tr.querySelector(".train-pax, input[name*='[pax]']")?.value) || 0;
                    const curr = tr.querySelector(".train-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = tr.querySelector(".train-exrate, input[name*='[ex_rate]']")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(tr.querySelector(".train-cost, input[name*='[cost]']")?.value) || 0;
                    const sale = parseFloat(tr.querySelector(".train-sale, input[name*='[sale]']")?.value) || 0;

                    totalTrainCostPkr += cost * (pax > 0 ? pax : 1) * ex;
                    totalTrainSalePkr += sale * (pax > 0 ? pax : 1) * ex;
                });

                // 5. Visas
                document.querySelectorAll(".visa-item").forEach(function(v) {
                    const curr = v.querySelector(".visa-currency, select[name*='[currency]']")?.value || 'PKR';
                    const rawEx = v.querySelector(".visa-exrate, input[name*='[ex_rate]']")?.value;
                    const ex = (curr === 'PKR') ? 1 : (rawEx !== '' && !isNaN(parseFloat(rawEx)) ? parseFloat(rawEx) : 0);
                    const cost = parseFloat(v.querySelector(".visa-cost, input[name*='[cost]']")?.value) || 0;
                    const sale = parseFloat(v.querySelector(".visa-sale, input[name*='[sale]']")?.value) || 0;

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
            let accCounter = {{ $quotation->accommodations->count() > 0 ? $quotation->accommodations->count() : 1 }};
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
                `;

                container.appendChild(newCard);
                flatpickr(".flatpickr-date", { dateFormat: "m/d/Y" });
                const cin = newCard.querySelector(".flatpickr-checkin");
                const cout = newCard.querySelector(".flatpickr-checkout");
                if (cin) flatpickr(cin, { dateFormat: "m/d/Y", onChange: function() { calculateNights(newCard); } });
                if (cout) flatpickr(cout, { dateFormat: "m/d/Y", onChange: function() { calculateNights(newCard); } });
                recalculateAll();
            }

            document.getElementById("btnAddNewAccommodation")?.addEventListener("click", addAccommodation);

            // Remove accommodation
            document.addEventListener("click", function(e) {
                if (e.target.closest(".btn-remove-acc")) {
                    const item = e.target.closest(".accommodation-item");
                    if (item) {
                        item.remove();
                        recalculateAll();
                    }
                }
            });

            // Prevent Form Submission if Stock is Exceeded!
            document.getElementById('quotationForm')?.addEventListener('submit', function(e) {
                const exceededInputs = document.querySelectorAll('.acc-rooms[data-stock-exceeded="true"]');
                if (exceededInputs.length > 0) {
                    e.preventDefault();
                    alert('Cannot save quotation: One or more accommodation rooms exceed available hotel stock! Please adjust the room count to available stock before submitting.');
                    exceededInputs[0].focus();
                    exceededInputs[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });

            // Check stock for all existing rows on load
            document.querySelectorAll(".accommodation-item").forEach(function(item) {
                checkStockForRow(item);
            });

            const firstHotelSelect = document.querySelector(".accommodation-item .hotel-select");
            if (firstHotelSelect && firstHotelSelect.value) {
                const opt = firstHotelSelect.options[firstHotelSelect.selectedIndex];
                const hid = opt ? opt.getAttribute("data-id") : null;
                if (hid) loadHotelSummary(hid);
            }

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

            // Initial calculation on load
            recalculateAll();
        });
    </script>
@endsection
