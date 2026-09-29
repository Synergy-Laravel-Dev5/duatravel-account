@extends('layout.master')
@section('title', 'Rooming List & Stock Summary')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .metric-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .room-type-card {
        border-radius: 10px;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        padding: 16px;
        margin-bottom: 15px;
        transition: all 0.2s;
    }
    .room-type-card:hover {
        border-color: #0f535e;
        box-shadow: 0 3px 12px rgba(15,83,94,0.08);
    }
    .status-progress {
        height: 7px;
        border-radius: 4px;
        background-color: #e2e8f0;
        overflow: hidden;
    }
    .nav-inventory-tabs .nav-link {
        font-weight: 600;
        color: #64748b;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 12px 20px;
        font-size: 14px;
    }
    .nav-inventory-tabs .nav-link.active {
        color: #0f535e;
        border-bottom-color: #0f535e;
        background: transparent;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Page Title & Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-1 text-primary">
                        <i class="mdi mdi-hotel me-1"></i> Rooming List & Room Stock Management
                    </h4>
                    <p class="text-muted fs-13 mb-0">
                        Live room summary (Double, Triple, Quad, etc.), room stock tracking, and quotation rooming list.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('room-inventory.create') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus-circle me-1"></i> + Add Room Stock Allotment
                    </a>
                    <a href="{{ route('quotation.create') }}" class="btn btn-outline-success btn-sm">
                        <i class="mdi mdi-file-document-plus-outline me-1"></i> New Quotation
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
            @if($errors->any())
                <div class="alert alert-danger fs-13">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filter Card -->
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('room-inventory.index') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fs-12 fw-semibold mb-1 text-muted">Filter by Hotel</label>
                                <select name="hotel_id" class="form-select form-select-sm">
                                    <option value="">-- All Hotels (Entire Stock) --</option>
                                    @foreach($hotels as $h)
                                        <option value="{{ $h->id }}" {{ ($hotelId == $h->id) ? 'selected' : '' }}>
                                            {{ $h->name }} @if($h->place) ({{ is_object($h->place) ? $h->place->value : $h->place }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-semibold mb-1 text-muted">City</label>
                                <select name="city" class="form-select form-select-sm">
                                    <option value="">-- All Cities --</option>
                                    <option value="Makkah" {{ ($city == 'Makkah') ? 'selected' : '' }}>Makkah</option>
                                    <option value="Madinah" {{ ($city == 'Madinah') ? 'selected' : '' }}>Madinah</option>
                                    <option value="Jeddah" {{ ($city == 'Jeddah') ? 'selected' : '' }}>Jeddah</option>
                                    <option value="Riyadh" {{ ($city == 'Riyadh') ? 'selected' : '' }}>Riyadh</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-semibold mb-1 text-muted">Room Type</label>
                                <select name="room_type" class="form-select form-select-sm">
                                    <option value="">-- All Types --</option>
                                    @foreach(['Double', 'Triple', 'Quad', 'Quint', 'Single', 'Sharing', 'Suite'] as $rt)
                                        <option value="{{ $rt }}" {{ ($roomType == $rt) ? 'selected' : '' }}>{{ $rt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-semibold mb-1 text-muted">Check In From</label>
                                <input type="text" name="check_in" class="form-control form-control-sm flatpickr-date"
                                    placeholder="mm/dd/yyyy" value="{{ $checkIn }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-12 fw-semibold mb-1 text-muted">Check Out To</label>
                                <input type="text" name="check_out" class="form-control form-control-sm flatpickr-date"
                                    placeholder="mm/dd/yyyy" value="{{ $checkOut }}">
                            </div>
                            <div class="col-md-1 d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm w-100" title="Search">
                                    <i class="mdi mdi-filter"></i>
                                </button>
                                <a href="{{ route('room-inventory.index') }}" class="btn btn-light btn-sm" title="Reset">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Top Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card metric-card bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted fw-semibold text-uppercase">Total Stock Rooms</span>
                                <h3 class="fs-22 fw-bold text-dark mb-0 mt-1">{{ number_format($totalStock) }}</h3>
                            </div>
                            <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-warehouse fs-22 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card metric-card bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted fw-semibold text-uppercase">Booked in Quotations</span>
                                <h3 class="fs-22 fw-bold text-danger mb-0 mt-1">{{ number_format($totalBooked) }}</h3>
                            </div>
                            <div class="avatar-sm bg-soft-danger rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-calendar-check fs-22 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card metric-card bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted fw-semibold text-uppercase">Available / Remaining</span>
                                <h3 class="fs-22 fw-bold text-success mb-0 mt-1">{{ number_format($totalAvailable) }}</h3>
                            </div>
                            <div class="avatar-sm bg-soft-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-bed-empty fs-22 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card metric-card bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted fw-semibold text-uppercase">Occupancy Rate</span>
                                <h3 class="fs-22 fw-bold text-info mb-0 mt-1">{{ $overallOccupancy }}%</h3>
                            </div>
                            <div class="avatar-sm bg-soft-info rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-chart-donut fs-22 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROOM SUMMARY BREAKDOWN (Double, Triple, Quad, etc.) -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fs-15 fw-semibold text-primary mb-0">
                            <i class="mdi mdi-chart-box-outline me-1"></i> Room Type Stock Summary
                        </h5>
                        <span class="fs-12 text-muted">Stock availability & booked count for Double, Triple, Quad, and all room types</span>
                    </div>
                    @if($hotelId)
                        @php $currentH = $hotels->firstWhere('id', $hotelId); @endphp
                        <span class="badge bg-soft-primary text-primary fs-12">
                            <i class="mdi mdi-hotel me-1"></i> Showing: {{ $currentH->name ?? 'Selected Hotel' }}
                        </span>
                    @else
                        <span class="badge bg-soft-secondary text-secondary fs-12">
                            <i class="mdi mdi-earth me-1"></i> All Hotels Combined
                        </span>
                    @endif
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        @foreach($summary as $rType => $data)
                            @php
                                $badgeClass = 'bg-success';
                                if ($data['total_stock'] == 0) {
                                    $badgeClass = 'bg-secondary';
                                } elseif ($data['available'] == 0) {
                                    $badgeClass = 'bg-danger';
                                } elseif ($data['percentage'] >= 80) {
                                    $badgeClass = 'bg-warning text-dark';
                                }
                            @endphp
                            <div class="col-md-3 col-sm-6">
                                <div class="room-type-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fs-14 fw-bold mb-0 text-dark">
                                            <i class="mdi mdi-bed me-1 text-primary"></i> {{ $rType }}
                                        </h6>
                                        <span class="badge {{ $badgeClass }} fs-11">
                                            @if($data['total_stock'] == 0)
                                                No Stock
                                            @elseif($data['available'] == 0)
                                                Sold Out
                                            @else
                                                {{ $data['available'] }} Avail
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between fs-12 mb-1">
                                        <span class="text-muted">Total Stock: <strong class="text-dark">{{ $data['total_stock'] }}</strong></span>
                                        <span class="text-muted">Booked: <strong class="text-danger">{{ $data['booked'] }}</strong></span>
                                    </div>
                                    <div class="status-progress mb-2">
                                        <div class="progress-bar {{ ($data['available'] == 0 && $data['total_stock'] > 0) ? 'bg-danger' : 'bg-primary' }}"
                                            style="width: {{ $data['percentage'] }}%;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between fs-11 text-muted">
                                        <span>Available: <strong class="text-success">{{ $data['available'] }}</strong></span>
                                        <span>Booked: <strong>{{ $data['percentage'] }}%</strong></span>
                                    </div>
                                    @if(strtolower($rType) === 'sharing' && (!empty($data['male_stock']) || !empty($data['female_stock'])))
                                        <div class="fs-11 text-muted mt-1 pt-1 border-top d-flex justify-content-between">
                                            <span><i class="mdi mdi-gender-male text-primary"></i> M: <strong>{{ $data['male_available'] }}/{{ $data['male_stock'] }}</strong></span>
                                            <span><i class="mdi mdi-gender-female text-danger"></i> F: <strong>{{ $data['female_available'] }}/{{ $data['female_stock'] }}</strong></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- MAIN TABS: ROOMING LIST & INVENTORIES -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-inventory-tabs" id="roomInventoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="rooming-list-tab" data-bs-toggle="tab"
                                data-bs-target="#rooming-list-pane" type="button" role="tab">
                                <i class="mdi mdi-clipboard-list-outline me-1"></i> 1. Rooming List (Booked in Quotations)
                                <span class="badge bg-primary rounded-pill ms-1">{{ count($roomingList) }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="allotments-tab" data-bs-toggle="tab"
                                data-bs-target="#allotments-pane" type="button" role="tab">
                                <i class="mdi mdi-warehouse me-1"></i> 2. Stock Allotments & Inventory
                                <span class="badge bg-secondary rounded-pill ms-1">{{ count($inventories) }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-3">
                    <div class="tab-content" id="roomInventoryContent">

                        <!-- TAB 1: ROOMING LIST -->
                        <div class="tab-pane fade show active" id="rooming-list-pane" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fs-14 fw-semibold text-dark mb-0">
                                    <i class="mdi mdi-format-list-bulleted text-primary me-1"></i> Quotation Rooming List
                                </h6>
                                <span class="fs-12 text-muted">All confirmed and active quotations consuming room inventory</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle fs-13 mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Quotation #</th>
                                            <th>Client / Passenger</th>
                                            <th>Hotel Name</th>
                                            <th>City</th>
                                            <th>Room Type</th>
                                            <th class="text-center">Rooms Booked</th>
                                            <th>Stay Dates</th>
                                            <th class="text-center">Nights</th>
                                            <th>Quotation Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($roomingList as $item)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('quotation.show', $item->quotation_id) }}" class="fw-semibold text-primary">
                                                        {{ $item->quotation->quotation_number ?? ('#QT-' . $item->quotation_id) }}
                                                    </a>
                                                    @if($item->confirmation_number)
                                                        <br><small class="text-muted"><i class="mdi mdi-pound"></i> Ref: {{ $item->confirmation_number }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $item->quotation->client_name ?? 'N/A' }}</strong>
                                                    @if(!empty($item->quotation->client_phone))
                                                        <br><small class="text-muted"><i class="mdi mdi-phone"></i> {{ $item->quotation->client_phone }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">{{ $item->hotel_name ?: ($item->hotel->name ?? 'Hotel') }}</span>
                                                    @if($item->room_view)
                                                        <br><span class="badge bg-soft-info text-info fs-10">{{ $item->room_view }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->city ?? ($item->hotel->place ? (is_object($item->hotel->place) ? $item->hotel->place->value : $item->hotel->place) : '-') }}</td>
                                                <td>
                                                    <span class="badge bg-soft-primary text-primary fs-11">
                                                        {{ $item->room_type }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger fs-12 px-2 py-1">
                                                        {{ $item->no_of_rooms }} Room(s)
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fs-12">
                                                        {{ $item->check_in ? date('d M Y', strtotime($item->check_in)) : 'Open' }}
                                                        <i class="mdi mdi-arrow-right text-muted"></i>
                                                        {{ $item->check_out ? date('d M Y', strtotime($item->check_out)) : 'Open' }}
                                                    </span>
                                                </td>
                                                <td class="text-center fw-semibold">{{ $item->number_of_nights ?: '-' }}</td>
                                                <td>
                                                    @php
                                                        $st = strtolower($item->quotation->status ?? 'draft');
                                                        $stClass = ($st === 'confirmed' || $st === 'accepted' || $st === 'approved') ? 'bg-success' : 'bg-warning text-dark';
                                                    @endphp
                                                    <span class="badge {{ $stClass }} fs-11 text-uppercase">{{ $st }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('quotation.show', $item->quotation_id) }}" class="btn btn-xs btn-outline-info" title="View Quotation">
                                                        <i class="mdi mdi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">
                                                    <i class="mdi mdi-information-outline fs-22 d-block mb-1"></i>
                                                    No booked rooms found in quotations matching the selected filter.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: INVENTORY / ALLOTMENTS -->
                        <div class="tab-pane fade" id="allotments-pane" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fs-14 fw-semibold text-dark mb-0">
                                    <i class="mdi mdi-warehouse text-primary me-1"></i> Configured Room Stock Allotments
                                </h6>
                                <a href="{{ route('room-inventory.create') }}" class="btn btn-primary btn-sm">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add New Stock
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm align-middle fs-13 mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Hotel Name</th>
                                            <th>Batch / Reference</th>
                                            <th>Room Type</th>
                                            <th>View / Meals</th>
                                            <th>Valid Period (Dates)</th>
                                            <th class="text-center">Total Stock</th>
                                            <th class="text-center">Booked</th>
                                            <th class="text-center">Remaining</th>
                                            <th>Supplier</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($inventories as $inv)
                                            @php
                                                $avail = \App\Models\RoomInventory::getAvailability($inv->hotel_id, $inv->room_type, $inv->check_in, $inv->check_out);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $inv->hotel->name ?? 'Hotel #' . $inv->hotel_id }}</strong>
                                                    @if($inv->hotel && $inv->hotel->place)
                                                        <br><small class="text-muted">{{ is_object($inv->hotel->place) ? $inv->hotel->place->value : $inv->hotel->place }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $inv->batch_name ?: 'General Stock' }}</td>
                                                <td>
                                                    <span class="badge bg-soft-primary text-primary fs-11">{{ $inv->room_type }}</span>
                                                </td>
                                                <td>
                                                    <span class="fs-11">{{ $inv->room_view ?: 'City View' }}</span>
                                                    @if($inv->meal_plan)
                                                        <br><small class="text-muted">{{ $inv->meal_plan }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($inv->check_in && $inv->check_out)
                                                        <span class="fs-12">{{ date('d M Y', strtotime($inv->check_in)) }} - {{ date('d M Y', strtotime($inv->check_out)) }}</span>
                                                    @else
                                                        <span class="badge bg-light text-dark fs-11">Open / Perpetual</span>
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold">{{ $inv->total_rooms }}</td>
                                                <td class="text-center text-danger fw-semibold">{{ $avail['booked'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ $avail['available'] > 0 ? 'bg-success' : 'bg-danger' }} fs-11">
                                                        {{ $avail['available'] }} Available
                                                    </span>
                                                </td>
                                                <td>{{ $inv->supplier->company_name ?? '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ $inv->status === 'active' ? 'bg-success' : 'bg-secondary' }} fs-10 text-uppercase">
                                                        {{ $inv->status }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('room-inventory.edit', $inv->id) }}" class="btn btn-outline-primary" title="Edit Allotment">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                        <form action="{{ route('room-inventory.delete', $inv->id) }}" method="POST"
                                                            onsubmit="return confirm('Are you sure you want to delete this room allotment?');" style="display:inline-block;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Delete Allotment">
                                                                <i class="mdi mdi-trash-can-outline"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center py-4 text-muted">
                                                    <i class="mdi mdi-warehouse fs-22 d-block mb-1"></i>
                                                    No room stock allotments added yet. Click "+ Add Room Stock Allotment" to add your hotel room stocks.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

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
