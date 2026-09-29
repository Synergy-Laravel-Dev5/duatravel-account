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
                                    <button class="nav-link" id="summary-tab" data-bs-toggle="tab"
                                        data-bs-target="#summary-pane" type="button" role="tab">
                                        <i class="mdi mdi-chart-box-outline me-1"></i> 6. Full Summary
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
                                                            <label class="form-label fs-13 fw-semibold mb-0">No Of Room</label>
                                                            <span class="acc-stock-badge badge bg-soft-secondary text-secondary fs-10">Check Stock</span>
                                                        </div>
                                                        <input type="number" name="accommodations[{{ $index }}][no_of_rooms]"
                                                            class="form-control form-control-sm acc-rooms" min="0" placeholder="0" value="{{ $acc->no_of_rooms }}">
                                                        <div class="acc-stock-warning text-danger fs-11 mt-1 d-none"></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-13 fw-semibold">Per Night Rate</label>
                                                        <input type="number" step="any" name="accommodations[{{ $index }}][per_night_rate]"
                                                            class="form-control form-control-sm acc-per-night" value="{{ $acc->per_night_rate ?? 0 }}">
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold">Currency</label>
                                                        <select name="accommodations[{{ $index }}][currency]" class="form-select form-select-sm acc-currency">
                                                            <option value="PKR" {{ ($acc->currency ?? 'PKR') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                            <option value="SAR" {{ ($acc->currency ?? '') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                            <option value="USD" {{ ($acc->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-currency-usd me-1"></i> ROE / Ex. Rate</label>
                                                        <input type="number" step="0.01" name="accommodations[{{ $index }}][exchange_rate]"
                                                            class="form-control form-control-sm border-primary acc-exrate {{ ($acc->currency ?? 'PKR') == 'PKR' ? 'bg-light' : '' }}" {{ ($acc->currency ?? 'PKR') == 'PKR' ? 'readonly' : '' }} placeholder="1.00" value="{{ ($acc->currency ?? 'PKR') == 'PKR' ? ($acc->exchange_rate ?: 1) : $acc->exchange_rate }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fs-13 fw-semibold">Cost Amount</label>
                                                        <input type="number" step="any" name="accommodations[{{ $index }}][cost_amount]"
                                                            class="form-control form-control-sm acc-cost" value="{{ $acc->cost_amount ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Selling Amount</label>
                                                        <input type="number" step="any" name="accommodations[{{ $index }}][selling_amount]"
                                                            class="form-control form-control-sm acc-selling" value="{{ $acc->selling_amount ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-13 fw-semibold">Supplier Amount</label>
                                                        <input type="number" step="any" name="accommodations[{{ $index }}][supplier_amount]"
                                                            class="form-control form-control-sm" value="{{ $acc->supplier_amount ?? 0 }}">
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-13 fw-semibold">Select Supplier / Vendor</label>
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
                                                    <div class="col-md-4">
                                                        <label class="form-label fs-13 fw-semibold">Cancellation Deadline</label>
                                                        <input type="text" name="accommodations[{{ $index }}][cancellation_deadline]"
                                                            class="form-control form-control-sm flatpickr-date"
                                                            value="{{ $acc->cancellation_deadline ? date('m/d/Y', strtotime($acc->cancellation_deadline)) : '' }}">
                                                    </div>
                                                    <div class="col-md-4">
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
                                </div>

                                <!-- TAB 6: FULL SUMMARY & INCLUSIONS / TERMS -->
                                <div class="tab-pane fade" id="summary-pane" role="tabpanel">
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

                                    <div class="text-end">
                                        <a href="{{ route('quotation.show', $quotation->id) }}" class="btn btn-secondary me-2">Cancel</a>
                                        <button type="submit" class="btn btn-primary px-4 py-2">
                                            <i class="mdi mdi-content-save-check me-1"></i> Update Quotation
                                        </button>
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

            function handleCurrencyChange(selectElem) {
                const val = selectElem.value;
                const parentContainer = selectElem.closest('.input-group, .row, .card-repeater-item, tr');
                const roeInput = parentContainer?.querySelector('.acc-exrate, .trans-exrate, .tour-exrate, .train-exrate, .visa-exrate, .meal-exrate');
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
            }

            document.addEventListener("change", function(e) {
                if (e.target.matches(".acc-currency, .trans-currency, .tour-currency, .train-currency, .visa-currency, select[name*='[currency]']")) {
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

            // Live Room Stock & Availability Handler
            function checkStockForRow(row) {
                if (!row) return;
                const hotelSelect = row.querySelector('.hotel-select');
                const hotelIdInput = row.querySelector('.acc-hotel-id');
                const roomTypeSelect = row.querySelector('.acc-room-type') || row.querySelector('[name*="[room_type]"]');
                const checkInInput = row.querySelector('.flatpickr-checkin');
                const checkOutInput = row.querySelector('.flatpickr-checkout');
                const roomsInput = row.querySelector('.acc-rooms');
                const badge = row.querySelector('.acc-stock-badge');
                const warning = row.querySelector('.acc-stock-warning');

                if (!hotelSelect || !roomTypeSelect || !badge) return;

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
                    if (roomsInput) {
                        roomsInput.classList.remove('is-invalid');
                        delete roomsInput.dataset.stockExceeded;
                    }
                    return;
                }

                const roomType = roomTypeSelect.value || 'Double';
                const checkIn = checkInInput ? checkInInput.value : '';
                const checkOut = checkOutInput ? checkOutInput.value : '';
                const requested = parseInt(roomsInput ? roomsInput.value : 0) || 0;
                const quotationId = document.getElementById('quotation_id_input') ? document.getElementById('quotation_id_input').value : '';

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
                            if (roomsInput) {
                                roomsInput.classList.remove('is-invalid');
                                delete roomsInput.dataset.stockExceeded;
                            }
                        } else {
                            const avail = data.available;
                            const total = data.total_stock;
                            
                            if (avail <= 0) {
                                badge.className = 'acc-stock-badge badge bg-danger text-white fs-10';
                                badge.textContent = `Sold Out (0 / ${total})`;
                            } else {
                                badge.className = 'acc-stock-badge badge bg-soft-success text-success fs-10';
                                badge.textContent = `Stock: ${avail} / ${total} Avail`;
                            }

                            if (requested > avail) {
                                if (roomsInput) {
                                    roomsInput.classList.add('is-invalid');
                                    roomsInput.dataset.stockExceeded = "true";
                                }
                                if (warning) {
                                    warning.textContent = `Exceeds stock! Only ${avail} ${roomType} room(s) available in stock.`;
                                    warning.classList.remove('d-none');
                                }
                            } else {
                                if (roomsInput) {
                                    roomsInput.classList.remove('is-invalid');
                                    delete roomsInput.dataset.stockExceeded;
                                }
                                if (warning) warning.classList.add('d-none');
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

            document.addEventListener("input", function(e) {
                if (e.target && e.target.classList.contains("acc-rooms")) {
                    const accItem = e.target.closest(".accommodation-item");
                    if (accItem) checkStockForRow(accItem);
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
        });
    </script>
@endsection
