@extends('layout.master')
@section('title', 'Group Details - ' . $group->group_name)

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Title & Top Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-1 text-primary">
                        <i class="mdi mdi-account-group me-1"></i> {{ $group->group_name }}
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Group Details, Resource Allocations, Pilgrim List, and Bus Capacity Management.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('haji-group.edit', $group->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-pencil me-1"></i> Edit Group
                    </a>
                    <a href="{{ route('haji-group.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Groups
                    </a>
                </div>
            </div>

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show fs-13" role="alert">
                    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show fs-13" role="alert">
                    <i class="mdi mdi-information me-1"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php
                $totalPilgrims = $group->groupPersons->count();
                $capacity = $group->bus_capacity ?: 45;
                $isOverflow = $totalPilgrims > $capacity;
                $overflowCount = $totalPilgrims - $capacity;
            @endphp

            <!-- BUS OVERFLOW & FAMILY PROTECTION WARNING CARD -->
            @if($isOverflow)
                <div class="card mb-3 border-danger shadow-sm bg-soft-danger">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="fs-15 fw-bold text-danger mb-1">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Bus Capacity Exceeded! ({{ $totalPilgrims }} Pilgrims in {{ $capacity }}-Seat Bus)
                                </h5>
                                <p class="mb-0 fs-13 text-dark">
                                    This group has <strong>{{ $totalPilgrims }} pilgrims</strong>, which is <strong>{{ $overflowCount }} more</strong> than the allocated bus capacity of {{ $capacity }} seats.
                                    Click <strong>Smart Split</strong> below to automatically isolate {{ $overflowCount }} single/solo travelers while keeping all family units intact together.
                                </p>
                            </div>
                            <div>
                                <form action="{{ route('haji-group.smart-split-bus', $group->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="bus_capacity" value="{{ $capacity }}">
                                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-3 py-2 shadow-sm"
                                        onclick="return confirm('Run Smart Split Engine? This will relocate {{ $overflowCount }} single travelers into a secondary group while keeping families together.');">
                                        <i class="mdi mdi-call-split me-1"></i> Run Smart Split Engine (Protect Families)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Group Allocations Cards Overview -->
            <div class="row g-3 mb-4">
                <!-- Bus Allocation Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-12 text-muted fw-semibold text-uppercase">Bus / Vehicle Allocation</span>
                            <i class="mdi mdi-bus fs-20 text-primary"></i>
                        </div>
                        <h5 class="fs-15 fw-bold text-dark mb-1">
                            {{ $group->bus_name ?: ($group->vehicle->title ?? 'Unassigned Bus') }}
                        </h5>
                        <div class="fs-12 text-muted">
                            Capacity: <strong class="{{ $isOverflow ? 'text-danger' : 'text-success' }}">{{ $capacity }} Seats</strong>
                            <br>Assigned Pilgrims: <strong class="text-dark">{{ $totalPilgrims }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Flight Allocation Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-12 text-muted fw-semibold text-uppercase">Flight & PNR</span>
                            <i class="mdi mdi-airplane fs-20 text-info"></i>
                        </div>
                        <h5 class="fs-15 fw-bold text-dark mb-1">
                            {{ $group->airline->name ?? '' }} {{ $group->flight_number ?: 'Unassigned Flight' }}
                        </h5>
                        <div class="fs-12 text-muted">
                            PNR: <strong class="text-dark">{{ $group->pnr ?: 'N/A' }}</strong>
                            <br>Date: {{ $group->flight_date ? $group->flight_date->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Hotel Accommodation Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-12 text-muted fw-semibold text-uppercase">Hotel Rooming</span>
                            <i class="mdi mdi-hotel fs-20 text-success"></i>
                        </div>
                        <h5 class="fs-15 fw-bold text-dark mb-1">
                            {{ $group->hotel->name ?? 'Unassigned Hotel' }}
                        </h5>
                        <div class="fs-12 text-muted">
                            Room Type: <strong class="text-dark">{{ $group->room_type ?: 'General' }}</strong>
                            <br>City: {{ $group->hotel->city ?? ($group->hotel->place ?? 'N/A') }}
                        </div>
                    </div>
                </div>

                <!-- Travel Route Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-12 text-muted fw-semibold text-uppercase">Travel Route & Itinerary</span>
                            <i class="mdi mdi-map-marker-path fs-20 text-warning"></i>
                        </div>
                        <h5 class="fs-15 fw-bold text-dark mb-1">
                            {{ $group->travelRoute->title ?? ($group->route->title ?? 'Unassigned Route') }}
                        </h5>
                        <div class="fs-12 text-muted">
                            Status: <span class="badge bg-success fs-10">Active</span>
                            <br>Leader: {{ $group->leader_name ?: 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pilgrim List Card & Transfer Actions -->
            <form action="{{ route('haji-group.transfer-persons', $group->id) }}" method="POST" id="transferForm">
                @csrf
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fs-15 fw-semibold text-primary mb-0">
                                <i class="mdi mdi-account-multiple me-1"></i> Group Pilgrims List ({{ $totalPilgrims }} Pilgrims)
                            </h5>
                            <span class="fs-12 text-muted">Family bookings are highlighted together. Select pilgrims to transfer to another group.</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <select name="target_group_id" class="form-select form-select-sm" style="width: 220px;" required>
                                <option value="">-- Select Target Group --</option>
                                @foreach($allGroups as $targetG)
                                    <option value="{{ $targetG->id }}">{{ $targetG->group_name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-warning btn-sm fw-semibold"
                                onclick="return confirm('Transfer selected pilgrims to target group?');">
                                <i class="mdi mdi-swap-horizontal me-1"></i> Transfer Selected
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle fs-13 mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" id="selectAllGroupPersons" class="form-check-input">
                                        </th>
                                        <th>#</th>
                                        <th>Haji Full Name</th>
                                        <th>CNIC / B-Form</th>
                                        <th>Passport #</th>
                                        <th>Gender</th>
                                        <th>Family / Booking Unit</th>
                                        <th>Company / Client</th>
                                        <th class="text-center">Seat #</th>
                                        <th class="text-center">Room / Bed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($group->groupPersons as $idx => $gp)
                                        @php
                                            $p = $gp->person;
                                            $bk = $gp->booking;
                                            $familyCount = $bk ? $bk->persons->count() : 1;
                                            $isFamily = $familyCount > 1;
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="group_person_ids[]" value="{{ $gp->id }}"
                                                    class="form-check-input gp-checkbox">
                                            </td>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $p->full_name ?: ($p->given_name . ' ' . $p->surname) }}</strong>
                                                @if(!empty($p->phone))
                                                    <br><small class="text-muted"><i class="mdi mdi-phone"></i> {{ $p->phone }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $p->cnic ?: ($bk->cnic ?? '-') }}</td>
                                            <td>{{ $p->passport_number ?: ($bk->passport_number ?? '-') }}</td>
                                            <td>
                                                <span class="badge {{ strtolower($p->gender ?? 'male') === 'female' ? 'bg-soft-danger text-danger' : 'bg-soft-primary text-primary' }} fs-11">
                                                    {{ ucfirst($p->gender ?? 'Male') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($isFamily)
                                                    <span class="badge bg-soft-success text-success fs-11">
                                                        <i class="mdi mdi-account-group me-1"></i> Family ({{ $familyCount }} Pax)
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-secondary text-secondary fs-11">
                                                        Single / Solo
                                                    </span>
                                                @endif
                                                <br><small class="text-muted">{{ $bk->booking_number ?? ('#BK-' . $bk->id) }}</small>
                                            </td>
                                            <td>{{ $bk->client->name ?? ($bk->company->company_name ?? '-') }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark fs-12">{{ $gp->seat_number ?: 'Auto' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark fs-12">{{ $gp->room_number ?: 'Open' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-account-off-outline fs-22 d-block mb-1"></i>
                                                No pilgrims added to this group yet. Click "Edit Group" to add pilgrims.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectAll = document.getElementById('selectAllGroupPersons');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.gp-checkbox');
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        }
    });
</script>
@endsection
