@extends('layout.master')
@section('title', 'Create Flight')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Flight / <strong>Create</strong></h4>
                    <a href="{{ route('flight.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('flight.store') }}" method="POST" id="flightForm">
                            @csrf
                            <div class="d-flex justify-content-center mb-4">
                                <ul class="nav nav-pills custom-flight-tabs border-0" id="flightTabs" role="tablist"
                                    style="background: transparent;">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active px-4 py-2" id="flight-tab" data-bs-toggle="pill"
                                            data-bs-target="#flight-pane" type="button" role="tab"
                                            aria-controls="flight-pane" aria-selected="true">
                                            <i class="mdi mdi-airplane me-1"></i> FLIGHT
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link px-4 py-2" id="sector-tab" data-bs-toggle="pill"
                                            data-bs-target="#sector-pane" type="button" role="tab"
                                            aria-controls="sector-pane" aria-selected="false">
                                            <i class="mdi mdi-table-large me-1"></i> SECTOR
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link px-4 py-2" id="pnr-tab" data-bs-toggle="pill"
                                            data-bs-target="#pnr-pane" type="button" role="tab"
                                            aria-controls="pnr-pane" aria-selected="false">
                                            <i class="mdi mdi-at me-1"></i> PNR
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content" id="flightTabContent">
                                <div class="tab-pane fade show active" id="flight-pane" role="tabpanel"
                                    aria-labelledby="flight-tab">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div class="card-body p-4">
                                            @if ($errors->any())
                                                <div class="alert alert-danger">
                                                    <ul class="mb-0">
                                                        @foreach ($errors->all() as $e)
                                                            <li>{{ $e }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Airline</label>
                                                    <select name="airline_id" class="form-select">
                                                        <option value="">Select Airline</option>
                                                        @foreach ($airlines as $airline)
                                                            <option value="{{ $airline->id }}"
                                                                {{ old('airline_id') == $airline->id ? 'selected' : '' }}>
                                                                {{ $airline->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Name</label>
                                                    <input type="text" name="name" class="form-control"
                                                        placeholder="Enter Name..." value="{{ old('name') }}">
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Outbound
                                                        Flight #</label>
                                                    <input type="text" name="outbound_flight_no" class="form-control"
                                                        placeholder="Enter Outbound Flight #..."
                                                        value="{{ old('outbound_flight_no') }}">
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Outbound
                                                        Departure</label>
                                                    <input type="datetime-local" name="outbound_departure"
                                                        class="form-control" value="{{ old('outbound_departure') }}">
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Outbound
                                                        Arrival</label>
                                                    <input type="datetime-local" name="outbound_arrival"
                                                        class="form-control" value="{{ old('outbound_arrival') }}">
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Inbound
                                                        Flight #</label>
                                                    <input type="text" name="inbound_flight_no" class="form-control"
                                                        placeholder="Enter Inbound Flight #..."
                                                        value="{{ old('inbound_flight_no') }}">
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Inbound
                                                        Departure</label>
                                                    <input type="datetime-local" name="inbound_departure"
                                                        class="form-control" value="{{ old('inbound_departure') }}">
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Inbound
                                                        Arrival</label>
                                                    <input type="datetime-local" name="inbound_arrival"
                                                        class="form-control" value="{{ old('inbound_arrival') }}">
                                                </div>

                                                <div class="col-md-6">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Economy
                                                        Seats</label>
                                                    <input type="number" name="economy_seats" class="form-control"
                                                        placeholder="0" value="{{ old('economy_seats', 0) }}">
                                                </div>

                                                <div class="col-md-6">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Business
                                                        Seats</label>
                                                    <input type="number" name="business_seats" class="form-control"
                                                        placeholder="0" value="{{ old('business_seats', 0) }}">
                                                </div>

                                                <div class="col-md-6">
                                                    <label
                                                        class="form-label text-uppercase fw-semibold fs-11 text-muted">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active"
                                                            {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                                            Active</option>
                                                        <option value="inactive"
                                                            {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mt-4 text-end">
                                                <button type="button" class="btn btn-primary px-4 btn-next-tab"
                                                    data-target="#sector-tab">Next (Sector) &rarr;</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="sector-pane" role="tabpanel"
                                    aria-labelledby="sector-tab">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div
                                            class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                            <h5 class="mb-0 fw-semibold text-dark">Sector</h5>
                                            <button type="button" class="btn btn-outline-info btn-sm px-3"
                                                id="add-sector-row">
                                                Add <i class="mdi mdi-plus-box ms-1"></i>
                                            </button>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="table-responsive">
                                                <table class="table table-borderless align-middle" id="sectors-table">
                                                    <thead>
                                                        <tr class="text-uppercase fs-11 text-muted border-bottom">
                                                            <th style="width: 20%;">FlightNo</th>
                                                            <th style="width: 20%;">Type</th>
                                                            <th style="width: 20%;">Destination</th>
                                                            <th style="width: 20%;">Departure</th>
                                                            <th style="width: 20%;">Arrival</th>
                                                            <th style="width: 80px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-4 d-flex justify-content-between">
                                                <button type="button" class="btn btn-secondary px-4 btn-prev-tab"
                                                    data-target="#flight-tab">&larr; Back</button>
                                                <button type="button" class="btn btn-primary px-4 btn-next-tab"
                                                    data-target="#pnr-tab">Next (PNR) &rarr;</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pnr-pane" role="tabpanel" aria-labelledby="pnr-tab">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div
                                            class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                            <div>
                                                <h5 class="mb-1 fw-semibold text-dark">PNR</h5>
                                                <p class="mb-0 text-muted fs-12">
                                                    Economy: <strong id="econ-sum">0</strong> | Business: <strong
                                                        id="bus-sum">0</strong>
                                                </p>
                                            </div>
                                            <button type="button" class="btn btn-outline-info btn-sm px-3"
                                                id="add-pnr-row">
                                                Add <i class="mdi mdi-plus-box ms-1"></i>
                                            </button>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="table-responsive">
                                                <table class="table table-borderless align-middle" id="pnrs-table">
                                                    <thead>
                                                        <tr class="text-uppercase fs-11 text-muted border-bottom">
                                                            <th style="width: 30%;">PNR Type</th>
                                                            <th style="width: 40%;">PNR Name</th>
                                                            <th style="width: 20%;">Capacity</th>
                                                            <th style="width: 80px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-4 d-flex justify-content-between">
                                                <button type="button" class="btn btn-secondary px-4 btn-prev-tab"
                                                    data-target="#sector-tab">&larr; Back</button>
                                                <button type="submit" class="btn btn-success px-4">Create</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styling to match design layout precisely */
        .custom-flight-tabs .nav-link {
            background-color: transparent;
            color: #6c757d;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-radius: 6px;
            margin: 0 10px;
            border: none;
            transition: all 0.3s ease;
        }

        .custom-flight-tabs .nav-link.active {
            background-color: #171449 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px #171449;
        }

        .fs-11 {
            font-size: 11px;
        }

        .fs-12 {
            font-size: 12px;
        }

        .form-control,
        .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #cbd5e1;
            box-shadow: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Next/Prev Tab Navigation
            document.querySelectorAll('.btn-next-tab').forEach(button => {
                button.addEventListener('click', function() {
                    const targetTabSelector = this.getAttribute('data-target');
                    const targetTab = document.querySelector(targetTabSelector);
                    if (targetTab) {
                        bootstrap.Tab.getInstance(targetTab).show();
                    }
                });
            });

            document.querySelectorAll('.btn-prev-tab').forEach(button => {
                button.addEventListener('click', function() {
                    const targetTabSelector = this.getAttribute('data-target');
                    const targetTab = document.querySelector(targetTabSelector);
                    if (targetTab) {
                        bootstrap.Tab.getInstance(targetTab).show();
                    }
                });
            });

            // Make sure Bootstrap tabs initialize
            const tabElList = [].slice.call(document.querySelectorAll('#flightTabs button'));
            tabElList.map(function(tabEl) {
                return new bootstrap.Tab(tabEl);
            });

            // Add Dynamic Sector Rows
            let sectorIndex = 0;
            const sectorsBody = document.querySelector('#sectors-table tbody');

            function addSectorRow() {
                const row = document.createElement('tr');
                row.innerHTML = `
            <td>
                <input type="text" name="sectors[${sectorIndex}][flight_no]" class="form-control" placeholder="FlightNo...">
            </td>
            <td>
                <select name="sectors[${sectorIndex}][type]" class="form-select">
                    <option value="Outbound">Outbound</option>
                    <option value="Inbound">Inbound</option>
                </select>
            </td>
            <td>
                <input type="text" name="sectors[${sectorIndex}][destination]" class="form-control" placeholder="Destination...">
            </td>
            <td>
                <input type="datetime-local" name="sectors[${sectorIndex}][departure]" class="form-control">
            </td>
            <td>
                <input type="datetime-local" name="sectors[${sectorIndex}][arrival]" class="form-control">
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn">
                    <i class="mdi mdi-close-box"></i>
                </button>
            </td>
        `;
                sectorsBody.appendChild(row);
                sectorIndex++;
            }

            document.getElementById('add-sector-row').addEventListener('click', addSectorRow);

            // Add Dynamic PNR Rows
            let pnrIndex = 0;
            const pnrsBody = document.querySelector('#pnrs-table tbody');

            function addPnrRow() {
                const row = document.createElement('tr');
                row.innerHTML = `
            <td>
                <select name="pnrs[${pnrIndex}][pnr_type]" class="form-select pnr-type-select">
                    <option value="">Select Class...</option>
                    <option value="Economy">Economy</option>
                    <option value="Business">Business</option>
                </select>
            </td>
            <td>
                <input type="text" name="pnrs[${pnrIndex}][pnr_name]" class="form-control" placeholder="Enter PNR Name...">
            </td>
            <td>
                <input type="number" name="pnrs[${pnrIndex}][capacity]" class="form-control pnr-capacity-input" placeholder="0" min="0">
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn">
                    <i class="mdi mdi-close-box"></i>
                </button>
            </td>
        `;
                pnrsBody.appendChild(row);
                pnrIndex++;

                // Attach event listeners to update counts
                row.querySelector('.pnr-type-select').addEventListener('change', calculatePnrSums);
                row.querySelector('.pnr-capacity-input').addEventListener('input', calculatePnrSums);
            }

            document.getElementById('add-pnr-row').addEventListener('click', addPnrRow);

            // Remove row event delegation
            document.addEventListener('click', function(e) {
                if (e.target && e.target.closest('.remove-row-btn')) {
                    const btn = e.target.closest('.remove-row-btn');
                    btn.closest('tr').remove();
                    calculatePnrSums();
                }
            });

            // Calculate dynamic PNR counts for Economy/Business
            function calculatePnrSums() {
                let econSum = 0;
                let busSum = 0;

                document.querySelectorAll('#pnrs-table tbody tr').forEach(row => {
                    const type = row.querySelector('.pnr-type-select').value;
                    const capacity = parseInt(row.querySelector('.pnr-capacity-input').value) || 0;

                    if (type === 'Economy') {
                        econSum += capacity;
                    } else if (type === 'Business') {
                        busSum += capacity;
                    }
                });

                document.getElementById('econ-sum').textContent = econSum;
                document.getElementById('bus-sum').textContent = busSum;
            }

            // Initialize with 1 default row each
            addSectorRow();
            addPnrRow();
        });
    </script>
@endsection
