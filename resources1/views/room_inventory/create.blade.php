@extends('layout.master')
@section('title', 'Add Room Stock Allotment')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .room-input-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .room-input-row:hover, .room-input-row:focus-within {
        background: #ffffff;
        border-color: #0f535e;
        box-shadow: 0 2px 8px rgba(15,83,94,0.06);
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-1 text-primary">
                        <i class="mdi mdi-plus-circle me-1"></i> Add Room Stock Allotment
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Add initial room stock (e.g. 10 Double, 5 Triple, 10 Quad) for a hotel before creating quotations.
                    </p>
                </div>
                <div>
                    <a href="{{ route('room-inventory.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Rooming List & Stock
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger fs-13">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('room-inventory.store') }}" method="POST" id="roomStockForm">
                @csrf

                <!-- Card 1: Hotel & Stay Period -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fs-15 fw-semibold text-primary mb-0">
                            <i class="mdi mdi-hotel me-1"></i> 1. Hotel & Allotment Information
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold text-primary">
                                    Hotel Name <span class="text-danger">*</span>
                                </label>
                                <select name="hotel_id" class="form-select form-select-sm" required id="hotel_select">
                                    <option value="">-- Select Hotel --</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }} @if($hotel->place) ({{ is_object($hotel->place) ? $hotel->place->value : $hotel->place }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Allotment / Batch Name</label>
                                <input type="text" name="batch_name" class="form-control form-control-sm"
                                    placeholder="e.g. Ramadan Allotment 2026, Oct 2026 Block"
                                    value="{{ old('batch_name', 'Stock Allotment ' . date('M Y')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                                <select name="supplier_id" class="form-select form-select-sm">
                                    <option value="">-- Direct Hotel / No Supplier --</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('supplier_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Validity / Check-In Date</label>
                                <input type="text" name="check_in" class="form-control form-control-sm flatpickr-date"
                                    placeholder="mm/dd/yyyy" value="{{ old('check_in') }}">
                                <small class="text-muted fs-11">Leave blank if open for entire season</small>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Validity / Check-Out Date</label>
                                <input type="text" name="check_out" class="form-control form-control-sm flatpickr-date"
                                    placeholder="mm/dd/yyyy" value="{{ old('check_out') }}">
                                <small class="text-muted fs-11">Leave blank if open for entire season</small>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fs-13 fw-semibold">Currency</label>
                                <select name="currency" class="form-select form-select-sm">
                                    <option value="SAR" selected>SAR (Saudi Riyal)</option>
                                    <option value="PKR">PKR (Pak Rupee)</option>
                                    <option value="USD">USD (US Dollar)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Notes / Reference</label>
                                <input type="text" name="notes" class="form-control form-control-sm"
                                    placeholder="Optional notes or contract reference" value="{{ old('notes') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Multi-Room Quantities Entry (Double, Triple, Quad, etc.) -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fs-15 fw-semibold text-primary mb-0">
                                <i class="mdi mdi-bed-double-outline me-1"></i> 2. Room Stock Quantities
                            </h5>
                            <span class="fs-12 text-muted">Enter number of rooms for Double, Triple, Quad, etc. to set up stock.</span>
                        </div>
                        <span class="badge bg-soft-info text-info fs-12">
                            Enter '0' or leave blank for room types not in stock
                        </span>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2 mb-2 text-muted fs-12 fw-semibold d-none d-md-flex">
                            <div class="col-md-2">Room Type</div>
                            <div class="col-md-2 text-primary">Number of Rooms (Stock)</div>
                            <div class="col-md-2">Room View</div>
                            <div class="col-md-2">Meal Plan</div>
                            <div class="col-md-2">Cost Rate / Night</div>
                            <div class="col-md-2">Selling Rate / Night</div>
                        </div>

                        @php
                            $roomTypes = [
                                'Double'  => ['label' => 'Double (2 Bed)', 'default_qty' => '10'],
                                'Triple'  => ['label' => 'Triple (3 Bed)', 'default_qty' => '5'],
                                'Quad'    => ['label' => 'Quad (4 Bed)',   'default_qty' => '10'],
                                'Quint'   => ['label' => 'Quint (5 Bed)',  'default_qty' => '0'],
                                'Single'  => ['label' => 'Single (1 Bed)', 'default_qty' => '0'],
                                'Sharing' => ['label' => 'Sharing',        'default_qty' => '0'],
                                'Suite'   => ['label' => 'Suite / Family', 'default_qty' => '0'],
                            ];
                        @endphp

                        @foreach($roomTypes as $type => $info)
                            <div class="room-input-row">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-2">
                                        <span class="fw-bold fs-14 text-dark">
                                            <i class="mdi mdi-bed me-1 text-primary"></i> {{ $info['label'] }}
                                        </span>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-primary text-white"><i class="mdi mdi-numeric"></i></span>
                                            <input type="number" name="rooms[{{ $type }}]"
                                                class="form-control form-control-sm border-primary fw-bold text-center"
                                                min="0" placeholder="0" value="{{ old('rooms.' . $type, $info['default_qty']) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="room_view_{{ $type }}" class="form-select form-select-sm">
                                            <option value="City View">City View</option>
                                            <option value="Haram View">Haram View</option>
                                            <option value="Kaaba View">Kaaba View</option>
                                            <option value="Partial Haram View">Partial Haram View</option>
                                            <option value="Courtyard View">Courtyard View</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="meal_plan_{{ $type }}" class="form-select form-select-sm">
                                            <option value="Room Only">Room Only (RO)</option>
                                            <option value="Bed & Breakfast">Bed & Breakfast (BB)</option>
                                            <option value="Half Board">Half Board (HB)</option>
                                            <option value="Full Board">Full Board (FB)</option>
                                            <option value="Suhoor">Suhoor Included</option>
                                            <option value="Iftar Dinner">Iftar Dinner Included</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="any" name="cost_rate_{{ $type }}"
                                            class="form-control form-control-sm" placeholder="Cost" value="0">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="any" name="selling_rate_{{ $type }}"
                                            class="form-control form-control-sm" placeholder="Selling" value="0">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center">
                            <span class="fs-13 text-muted">
                                <i class="mdi mdi-information-outline text-info"></i>
                                Once saved, this room stock will be active and available for quotation bookings.
                            </span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('room-inventory.index') }}" class="btn btn-secondary btn-sm px-3">Cancel</a>
                                <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold">
                                    <i class="mdi mdi-check-circle me-1"></i> Save Room Stock Allotment
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "m/d/Y",
            allowInput: true
        });
    });
</script>
@endsection
