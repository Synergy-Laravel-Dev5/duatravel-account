@extends('layout.master')
@section('title', 'Edit Haji Group - ' . $group->group_name)

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            @php
                $groupType = $group->group_type ?: 'general';
                $typeTitles = [
                    'flight'  => ['title' => 'Edit Flight Group', 'icon' => 'mdi-airplane', 'color' => 'info'],
                    'bus'     => ['title' => 'Edit Bus / Vehicle Group', 'icon' => 'mdi-bus', 'color' => 'primary'],
                    'hotel'   => ['title' => 'Edit Hotel Rooming Group', 'icon' => 'mdi-hotel', 'color' => 'success'],
                    'route'   => ['title' => 'Edit Travel Route Group', 'icon' => 'mdi-map-marker-path', 'color' => 'warning'],
                    'general' => ['title' => 'Edit Haji Pilgrim Group', 'icon' => 'mdi-account-group', 'color' => 'dark']
                ];
                $currentMeta = $typeTitles[$groupType] ?? $typeTitles['general'];
            @endphp

            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-1 text-{{ $currentMeta['color'] }}">
                        <i class="mdi {{ $currentMeta['icon'] }} me-1"></i> {{ $currentMeta['title'] }}: {{ $group->group_name }}
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Update group details, resource allocations, and pilgrim member assignments.
                    </p>
                </div>
                <div>
                    <a href="{{ route('haji-group.show', $group->id) }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Group Details
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

            <form action="{{ route('haji-group.update', $group->id) }}" method="POST" id="hajiGroupEditForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="group_type" value="{{ $groupType }}">

                <!-- Section 1: Basic Group Info -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fs-15 fw-semibold text-primary mb-0">
                            <i class="mdi mdi-information-outline me-1"></i> 1. Basic Group Information
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fs-13 fw-semibold text-primary">
                                    Group Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="group_name" class="form-control form-control-sm border-primary fw-semibold"
                                    required value="{{ old('group_name', $group->group_name) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Group Leader Name</label>
                                <input type="text" name="leader_name" class="form-control form-control-sm"
                                    value="{{ old('leader_name', $group->leader_name) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Leader Mobile Number</label>
                                <input type="text" name="leader_phone" class="form-control form-control-sm"
                                    value="{{ old('leader_phone', $group->leader_phone) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Resource Allocations -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fs-15 fw-semibold text-primary mb-0">
                            <i class="mdi {{ $currentMeta['icon'] }} me-1"></i> 2. {{ ucfirst($groupType) }} Resource Allocations
                        </h5>
                    </div>
                    <div class="card-body p-3">

                        @if($groupType === 'flight' || $groupType === 'general')
                            <!-- Flight Allocations -->
                            <div class="row g-3 {{ $groupType === 'general' ? 'mb-3 pb-3 border-bottom' : '' }}">
                                <div class="col-12">
                                    <span class="badge bg-soft-info text-info fs-12 fw-semibold mb-2">
                                        <i class="mdi mdi-airplane me-1"></i> Flight Details
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold text-dark"><i class="mdi mdi-airplane me-1 text-info"></i> Airline</label>
                                    <select name="airline_id" class="form-select form-select-sm">
                                        <option value="">-- Select Airline --</option>
                                        @foreach($airlines as $a)
                                            <option value="{{ $a->id }}" {{ old('airline_id', $group->airline_id) == $a->id ? 'selected' : '' }}>
                                                {{ $a->name }} ({{ $a->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold">Flight Number</label>
                                    <input type="text" name="flight_number" class="form-control form-control-sm"
                                        value="{{ old('flight_number', $group->flight_number) }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold">PNR Reference</label>
                                    <input type="text" name="pnr" class="form-control form-control-sm fw-bold text-uppercase"
                                        value="{{ old('pnr', $group->pnr) }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold">Flight Date</label>
                                    <input type="text" name="flight_date" class="form-control form-control-sm flatpickr-date"
                                        value="{{ old('flight_date', $group->flight_date ? $group->flight_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        @endif

                        @if($groupType === 'bus' || $groupType === 'general')
                            <!-- Bus / Vehicle Allocations -->
                            <div class="row g-3 {{ $groupType === 'general' ? 'mb-3 pb-3 border-bottom' : '' }}">
                                <div class="col-12">
                                    <span class="badge bg-soft-primary text-primary fs-12 fw-semibold mb-2">
                                        <i class="mdi mdi-bus me-1"></i> Bus & Vehicle Details (Strict Capacity Enforcement)
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold text-dark"><i class="mdi mdi-bus me-1 text-primary"></i> Vehicle / Transport Model</label>
                                    <select name="vehicle_id" id="vehicleSelectEdit" class="form-select form-select-sm">
                                        <option value="">-- Select Bus / Vehicle --</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" data-capacity="{{ $v->capacity ?? 45 }}" {{ old('vehicle_id', $group->vehicle_id) == $v->id ? 'selected' : '' }}>
                                                {{ $v->title }} ({{ $v->capacity ?? 45 }} Seats)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">Bus Name / Number</label>
                                    <input type="text" name="bus_name" class="form-control form-control-sm"
                                        value="{{ old('bus_name', $group->bus_name) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">Bus Capacity (Seats) <span class="text-danger">*</span></label>
                                    <input type="number" name="bus_capacity" id="busCapacityInputEdit" class="form-control form-control-sm fw-bold border-primary"
                                        min="1" value="{{ old('bus_capacity', $group->bus_capacity ?: 45) }}" required>
                                    <small class="text-muted fs-11">System will strictly block updating if selected pilgrims exceed this seat limit.</small>
                                </div>
                            </div>
                        @endif

                        @if($groupType === 'hotel' || $groupType === 'general')
                            <!-- Hotel Accommodation -->
                            <div class="row g-3 {{ $groupType === 'general' ? 'mb-3 pb-3 border-bottom' : '' }}">
                                <div class="col-12">
                                    <span class="badge bg-soft-success text-success fs-12 fw-semibold mb-2">
                                        <i class="mdi mdi-hotel me-1"></i> Hotel Details
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold text-dark"><i class="mdi mdi-hotel me-1 text-success"></i> Hotel Accommodation</label>
                                    <select name="hotel_id" class="form-select form-select-sm">
                                        <option value="">-- Select Hotel --</option>
                                        @foreach($hotels as $h)
                                            <option value="{{ $h->id }}" {{ old('hotel_id', $group->hotel_id) == $h->id ? 'selected' : '' }}>
                                                {{ $h->name }} ({{ $h->city ?? $h->place }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Target Room Type / Sharing Bed Requirement</label>
                                    <select name="room_type" class="form-select form-select-sm">
                                        <option value="">-- Select Room Type --</option>
                                        <option value="Sharing (Male)" {{ old('room_type', $group->room_type) == 'Sharing (Male)' ? 'selected' : '' }}>Sharing (Male Bed Allocation)</option>
                                        <option value="Sharing (Female)" {{ old('room_type', $group->room_type) == 'Sharing (Female)' ? 'selected' : '' }}>Sharing (Female Bed Allocation)</option>
                                        <option value="Double Room (Family)" {{ old('room_type', $group->room_type) == 'Double Room (Family)' ? 'selected' : '' }}>Double Room (2 Persons / Family)</option>
                                        <option value="Triple Room" {{ old('room_type', $group->room_type) == 'Triple Room' ? 'selected' : '' }}>Triple Room (3 Beds)</option>
                                        <option value="Quad Room" {{ old('room_type', $group->room_type) == 'Quad Room' ? 'selected' : '' }}>Quad Room (4 Beds)</option>
                                        <option value="Quint Room" {{ old('room_type', $group->room_type) == 'Quint Room' ? 'selected' : '' }}>Quint Room (5 Beds)</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if($groupType === 'route' || $groupType === 'general')
                            <!-- Travel Route & Itinerary -->
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="badge bg-soft-warning text-warning fs-12 fw-semibold mb-2">
                                        <i class="mdi mdi-map-marker-path me-1"></i> Route & Schedule Details
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold"><i class="mdi mdi-map-marker-path me-1 text-warning"></i> Bus Route</label>
                                    <select name="route_id" class="form-select form-select-sm">
                                        <option value="">-- Select Route --</option>
                                        @foreach($routes as $r)
                                            <option value="{{ $r->id }}" {{ old('route_id', $group->route_id) == $r->id ? 'selected' : '' }}>
                                                {{ $r->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold"><i class="mdi mdi-map me-1 text-primary"></i> Travel Route Itinerary</label>
                                    <select name="travel_route_id" class="form-select form-select-sm">
                                        <option value="">-- Select Travel Route --</option>
                                        @foreach($travelRoutes as $tr)
                                            <option value="{{ $tr->id }}" {{ old('travel_route_id', $group->travel_route_id) == $tr->id ? 'selected' : '' }}>
                                                {{ $tr->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Section 3: Select Pilgrims -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fs-15 fw-semibold text-primary mb-0 d-inline-block">
                                <i class="mdi mdi-account-multiple-plus me-1"></i> 3. Select Group Pilgrims
                            </h5>
                            <span id="selectedCountBadgeEdit" class="badge bg-primary fs-12 ms-2">Selected: 0 / Capacity: 45</span>
                            <br><span class="fs-12 text-muted">Check/uncheck pilgrims to include in this group.</span>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllPersonsEdit">
                            <label class="form-check-label fw-bold fs-13 text-primary" for="selectAllPersonsEdit">
                                Select All Pilgrims
                            </label>
                        </div>
                    </div>

                    <div class="card-body p-3">

                        <!-- Capacity Warning Banner -->
                        <div id="capacityWarningBannerEdit" class="alert alert-danger fs-13 d-none mb-3" role="alert">
                            <i class="mdi mdi-alert-circle me-1"></i>
                            <strong>Bus Capacity Limit Exceeded!</strong> You have selected <strong id="warnSelectedCountEdit">0</strong> pilgrims, but the bus capacity is <strong id="warnCapacityLimitEdit">45</strong> seats. Please uncheck extra pilgrims or increase bus capacity before saving.
                        </div>

                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle fs-13 mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">Select</th>
                                        <th>Booking #</th>
                                        <th>Haji Full Name</th>
                                        <th>CNIC / B-Form</th>
                                        <th>Passport #</th>
                                        <th>Gender</th>
                                        <th>Company / Client</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $bk)
                                        @foreach($bk->persons as $person)
                                            @php
                                                $isSelected = in_array($person->id, $selectedPersonIds);
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="person_ids[]" value="{{ $person->id }}"
                                                        class="form-check-input person-checkbox-edit" {{ $isSelected ? 'checked' : '' }}>
                                                </td>
                                                <td><span class="fw-semibold text-primary">{{ $bk->booking_number ?? ('#BK-' . $bk->id) }}</span></td>
                                                <td><strong>{{ $person->full_name ?: ($person->given_name . ' ' . $person->surname) }}</strong></td>
                                                <td>{{ $person->cnic ?: ($bk->cnic ?? '-') }}</td>
                                                <td>{{ $person->passport_number ?: ($bk->passport_number ?? '-') }}</td>
                                                <td>
                                                    <span class="badge {{ strtolower($person->gender) === 'female' ? 'bg-soft-danger text-danger' : 'bg-soft-primary text-primary' }} fs-11">
                                                        {{ ucfirst($person->gender ?? 'Male') }}
                                                    </span>
                                                </td>
                                                <td>{{ $bk->client->name ?? ($bk->company->company_name ?? '-') }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center">
                            <a href="{{ route('haji-group.show', $group->id) }}" class="btn btn-secondary btn-sm px-3">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                <i class="mdi mdi-check-circle me-1"></i> Update {{ ucfirst($groupType) }} Group
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

        const selectAllEdit = document.getElementById('selectAllPersonsEdit');
        const checkboxes = document.querySelectorAll('.person-checkbox-edit');
        const capInput = document.getElementById('busCapacityInputEdit');
        const countBadge = document.getElementById('selectedCountBadgeEdit');
        const warnBanner = document.getElementById('capacityWarningBannerEdit');
        const form = document.getElementById('hajiGroupEditForm');
        const groupType = "{{ $groupType }}";

        function updateCapacityStatsEdit() {
            const selectedCount = document.querySelectorAll('.person-checkbox-edit:checked').length;
            const cap = capInput ? (parseInt(capInput.value) || 45) : 45;

            if (countBadge) {
                countBadge.textContent = `Selected: ${selectedCount} / Capacity: ${cap}`;
                if ((groupType === 'bus' || groupType === 'general') && selectedCount > cap) {
                    countBadge.className = 'badge bg-danger fs-12 ms-2 fw-bold';
                } else {
                    countBadge.className = 'badge bg-primary fs-12 ms-2';
                }
            }

            if (warnBanner) {
                if ((groupType === 'bus' || groupType === 'general') && selectedCount > cap) {
                    warnBanner.classList.remove('d-none');
                    document.getElementById('warnSelectedCountEdit').textContent = selectedCount;
                    document.getElementById('warnCapacityLimitEdit').textContent = cap;
                } else {
                    warnBanner.classList.add('d-none');
                }
            }
        }

        if (selectAllEdit) {
            selectAllEdit.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAllEdit.checked);
                updateCapacityStatsEdit();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCapacityStatsEdit);
        });

        if (capInput) {
            capInput.addEventListener('input', updateCapacityStatsEdit);
        }

        const vehSelectEdit = document.getElementById('vehicleSelectEdit');
        if (vehSelectEdit) {
            vehSelectEdit.addEventListener('change', function () {
                const opt = vehSelectEdit.options[vehSelectEdit.selectedIndex];
                const cap = opt ? opt.getAttribute('data-capacity') : null;
                if (cap && capInput) {
                    capInput.value = cap;
                    updateCapacityStatsEdit();
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                const selectedCount = document.querySelectorAll('.person-checkbox-edit:checked').length;
                const cap = capInput ? (parseInt(capInput.value) || 45) : 45;
                const isBusGroup = (groupType === 'bus' || (document.querySelector('select[name="vehicle_id"]') && document.querySelector('select[name="vehicle_id"]').value));

                if (isBusGroup && selectedCount > cap) {
                    e.preventDefault();
                    alert(`Bus Capacity Limit Exceeded!\n\nYou have selected ${selectedCount} pilgrims, but bus capacity limit is ${cap} seats.\n\nPlease uncheck extra pilgrims to fit within ${cap} seats, or increase bus capacity before saving.`);
                }
            });
        }

        updateCapacityStatsEdit();
    });
</script>
@endsection
