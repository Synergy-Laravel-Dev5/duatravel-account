@extends('layout.master')
@section('title', 'Edit Room Stock Allotment')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-1 text-primary">
                        <i class="mdi mdi-pencil me-1"></i> Edit Room Stock Allotment #{{ $inventory->id }}
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Update stock quantity, room type, dates, or rates for this inventory allotment.
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

            <form action="{{ route('room-inventory.update', $inventory->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fs-15 fw-semibold text-primary mb-0">
                            <i class="mdi mdi-hotel me-1"></i> Allotment Details
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold text-primary">Hotel Name <span class="text-danger">*</span></label>
                                <select name="hotel_id" class="form-select form-select-sm" required>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ (old('hotel_id', $inventory->hotel_id) == $hotel->id) ? 'selected' : '' }}>
                                            {{ $hotel->name }} @if($hotel->place) ({{ is_object($hotel->place) ? $hotel->place->value : $hotel->place }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Batch / Allotment Name</label>
                                <input type="text" name="batch_name" class="form-control form-control-sm"
                                    value="{{ old('batch_name', $inventory->batch_name) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Supplier / Vendor</label>
                                <select name="supplier_id" class="form-select form-select-sm">
                                    <option value="">-- Direct Hotel / No Supplier --</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ (old('supplier_id', $inventory->supplier_id) == $company->id) ? 'selected' : '' }}>
                                            {{ $company->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold text-primary">Room Type <span class="text-danger">*</span></label>
                                <select name="room_type" id="editRoomType" class="form-select form-select-sm" required>
                                    @foreach($roomTypes as $rt)
                                        <option value="{{ $rt }}" {{ (old('room_type', $inventory->room_type) == $rt) ? 'selected' : '' }}>
                                            {{ $rt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold text-primary">Total Stock Rooms / Beds <span class="text-danger">*</span></label>
                                <input type="number" id="editTotalRooms" name="total_rooms" class="form-control form-control-sm border-primary fw-bold"
                                    min="0" required value="{{ old('total_rooms', $inventory->total_rooms) }}">
                            </div>

                            <div class="col-md-3" id="editMaleBedsBox">
                                <label class="form-label fs-13 fw-semibold text-primary"><i class="mdi mdi-gender-male"></i> Male Beds (Sharing)</label>
                                <input type="number" id="editMaleBeds" name="male_beds" class="form-control form-control-sm border-primary"
                                    min="0" value="{{ old('male_beds', $inventory->male_beds ?? 0) }}">
                            </div>

                            <div class="col-md-3" id="editFemaleBedsBox">
                                <label class="form-label fs-13 fw-semibold text-danger"><i class="mdi mdi-gender-female"></i> Female Beds (Sharing)</label>
                                <input type="number" id="editFemaleBeds" name="female_beds" class="form-control form-control-sm border-danger"
                                    min="0" value="{{ old('female_beds', $inventory->female_beds ?? 0) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Room View</label>
                                <select name="room_view" class="form-select form-select-sm">
                                    <option value="City View" {{ (old('room_view', $inventory->room_view) == 'City View') ? 'selected' : '' }}>City View</option>
                                    <option value="Haram View" {{ (old('room_view', $inventory->room_view) == 'Haram View') ? 'selected' : '' }}>Haram View</option>
                                    <option value="Kaaba View" {{ (old('room_view', $inventory->room_view) == 'Kaaba View') ? 'selected' : '' }}>Kaaba View</option>
                                    <option value="Partial Haram View" {{ (old('room_view', $inventory->room_view) == 'Partial Haram View') ? 'selected' : '' }}>Partial Haram View</option>
                                    <option value="Courtyard View" {{ (old('room_view', $inventory->room_view) == 'Courtyard View') ? 'selected' : '' }}>Courtyard View</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Meal Plan</label>
                                <select name="meal_plan" class="form-select form-select-sm">
                                    <option value="Room Only" {{ (old('meal_plan', $inventory->meal_plan) == 'Room Only') ? 'selected' : '' }}>Room Only (RO)</option>
                                    <option value="Bed & Breakfast" {{ (old('meal_plan', $inventory->meal_plan) == 'Bed & Breakfast') ? 'selected' : '' }}>Bed & Breakfast (BB)</option>
                                    <option value="Half Board" {{ (old('meal_plan', $inventory->meal_plan) == 'Half Board') ? 'selected' : '' }}>Half Board (HB)</option>
                                    <option value="Full Board" {{ (old('meal_plan', $inventory->meal_plan) == 'Full Board') ? 'selected' : '' }}>Full Board (FB)</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Validity / Check-In Date</label>
                                <input type="text" name="check_in" class="form-control form-control-sm flatpickr-date"
                                    value="{{ old('check_in', $inventory->check_in ? $inventory->check_in->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Validity / Check-Out Date</label>
                                <input type="text" name="check_out" class="form-control form-control-sm flatpickr-date"
                                    value="{{ old('check_out', $inventory->check_out ? $inventory->check_out->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Currency</label>
                                <select name="currency" class="form-select form-select-sm">
                                    <option value="SAR" {{ (old('currency', $inventory->currency) == 'SAR') ? 'selected' : '' }}>SAR</option>
                                    <option value="PKR" {{ (old('currency', $inventory->currency) == 'PKR') ? 'selected' : '' }}>PKR</option>
                                    <option value="USD" {{ (old('currency', $inventory->currency) == 'USD') ? 'selected' : '' }}>USD</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active" {{ (old('status', $inventory->status) == 'active') ? 'selected' : '' }}>Active (Available for Quotations)</option>
                                    <option value="inactive" {{ (old('status', $inventory->status) == 'inactive') ? 'selected' : '' }}>Inactive / Suspended</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fs-13 fw-semibold">Notes</label>
                                <input type="text" name="notes" class="form-control form-control-sm"
                                    value="{{ old('notes', $inventory->notes) }}">
                            </div>
                        </div>

                        <div class="mt-4 pt-2 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('room-inventory.index') }}" class="btn btn-secondary btn-sm px-3">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                <i class="mdi mdi-check-circle me-1"></i> Update Allotment
                            </button>
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

        const roomTypeSelect = document.getElementById('editRoomType');
        const maleBox = document.getElementById('editMaleBedsBox');
        const femaleBox = document.getElementById('editFemaleBedsBox');
        const mInput = document.getElementById('editMaleBeds');
        const fInput = document.getElementById('editFemaleBeds');
        const totalInput = document.getElementById('editTotalRooms');

        function toggleSharingBeds() {
            const isSharing = roomTypeSelect.value.toLowerCase().includes('sharing');
            if (isSharing) {
                maleBox.style.display = '';
                femaleBox.style.display = '';
            } else {
                maleBox.style.display = 'none';
                femaleBox.style.display = 'none';
            }
        }

        function autoSum() {
            if (roomTypeSelect.value.toLowerCase().includes('sharing')) {
                const m = parseInt(mInput.value) || 0;
                const f = parseInt(fInput.value) || 0;
                totalInput.value = m + f;
            }
        }

        if (roomTypeSelect) {
            roomTypeSelect.addEventListener('change', toggleSharingBeds);
            toggleSharingBeds();
        }
        if (mInput && fInput) {
            mInput.addEventListener('input', autoSum);
            fInput.addEventListener('input', autoSum);
        }
    });
</script>
@endsection
