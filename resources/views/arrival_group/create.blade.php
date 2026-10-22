@extends('layout.master')

@section('title', 'Add Arrival Group')
@section('header-title', 'Add Arrival Group')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- PAGE TITLE -->
                <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 fw-semibold m-0">
                            <i class="mdi mdi-airplane-landing text-success me-1"></i> Add Arrival Group
                        </h4>
                    </div>
                    <div>
                        <a href="{{ route('arrival-group.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('arrival-group.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-success"><i class="mdi mdi-information-outline me-1"></i> Arrival Group Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Group Name <span class="text-danger">*</span></label>
                                            <input type="text" name="group_name" class="form-control" placeholder="e.g. SV-786 VIP Arrival Group 1" value="{{ old('group_name') }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Select Airline (From Airlines CRUD)</label>
                                            <select name="airline_id" id="airline_select" class="form-select select2">
                                                <option value="">-- Select Airline --</option>
                                                @foreach($airlines as $airline)
                                                    <option value="{{ $airline->id }}" {{ old('airline_id') == $airline->id ? 'selected' : '' }}>
                                                        {{ $airline->name }} ({{ $airline->code ?? $airline->iata_code ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Select Flight (From Flights CRUD)</label>
                                            <select name="flight_id" id="flight_select" class="form-select select2" onchange="autoFillFlightDetails(this)">
                                                <option value="">-- Select Flight (Optional) --</option>
                                                @foreach($flights as $flight)
                                                    <option value="{{ $flight->id }}" 
                                                        data-flightno="{{ $flight->outbound_flight_no ?? $flight->inbound_flight_no }}"
                                                        data-date="{{ $flight->outbound_departure ? $flight->outbound_departure->format('Y-m-d') : '' }}"
                                                        data-time="{{ $flight->outbound_departure ? $flight->outbound_departure->format('H:i') : '' }}"
                                                        {{ old('flight_id') == $flight->id ? 'selected' : '' }}>
                                                        {{ $flight->name }} — {{ $flight->outbound_flight_no ?? $flight->inbound_flight_no }} ({{ $flight->airline->name ?? '' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Flight Number</label>
                                            <input type="text" name="flight_number" id="flight_number_input" class="form-control" placeholder="e.g. SV-786" value="{{ old('flight_number') }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Flight Arrival Date</label>
                                            <input type="date" name="flight_date" id="flight_date_input" class="form-control" value="{{ old('flight_date') }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Flight Arrival Time</label>
                                            <input type="text" name="flight_time" id="flight_time_input" class="form-control" placeholder="e.g. 14:30 / 02:30 PM" value="{{ old('flight_time') }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">PNR Number</label>
                                            <input type="text" name="pnr" class="form-control" placeholder="e.g. ABC123PNR" value="{{ old('pnr') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Departure City (From)</label>
                                            <input type="text" name="departure_city" class="form-control" placeholder="e.g. Karachi / KHI" value="{{ old('departure_city') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Arrival City (To)</label>
                                            <input type="text" name="arrival_city" class="form-control" placeholder="e.g. Jeddah / JED" value="{{ old('arrival_city') }}">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Group Notes / Remarks</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="Special handling notes, VIP transport instructions, etc.">{{ old('notes') }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SIDE PANEL SUMMARY -->
                        <div class="col-lg-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0 text-white"><i class="mdi mdi-account-group me-1"></i> Group Summary</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted fs-13 mb-2">Select passengers from Bookings on the right to assign them to this Arrival Group.</p>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded border">
                                        <span class="fw-bold">Total Selected Pax:</span>
                                        <span class="badge bg-success fs-16 rounded-pill px-3" id="selectedPaxCount">0 Pax</span>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-success w-100 py-2 fs-15 fw-bold">
                                            <i class="mdi mdi-check-circle me-1"></i> Save Arrival Group
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MULTI-SELECT PASSENGERS FROM BOOKINGS -->
                        <div class="col-12 mt-3">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <h5 class="mb-0 text-primary">
                                        <i class="mdi mdi-account-multiple-check me-1"></i> Select Passengers from Bookings (Multi-Select: 5, 10, 16, 20+ Pilgrims)
                                    </h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" id="passengerSearchInput" class="form-control form-control-sm" placeholder="Search passenger, passport, cnic, booking..." style="width: 280px;" autocomplete="off" oninput="filterPassengers()" onkeyup="filterPassengers()">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSelectAllPassengers(true)">Select All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSelectAllPassengers(false)">Deselect All</button>
                                    </div>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    
                                    @forelse($bookings as $booking)
                                        @if($booking->persons->count() > 0)
                                            <div class="booking-group-box border rounded p-3 mb-3 bg-light" 
                                                data-bookingid="{{ $booking->id }}" 
                                                data-booking-number="{{ $booking->booking_number ?? ('BK-' . $booking->id) }}"
                                                data-company="{{ $booking->company->name ?? $booking->company->company_name ?? '' }}"
                                                data-client="{{ $booking->client->name ?? '' }}">
                                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                    <div>
                                                        <span class="badge bg-dark me-2">{{ $booking->booking_number ?? ('BK-' . $booking->id) }}</span>
                                                        <strong class="text-dark">{{ $booking->company->name ?? $booking->company->company_name ?? $booking->client->name ?? 'Company' }}</strong>
                                                        <span class="text-muted ms-2 fs-12">({{ ucfirst($booking->package_type) }} — {{ $booking->package_year ?? '' }})</span>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none p-0" onclick="toggleBookingPassengers({{ $booking->id }}, true)">Select Booking Pax</button>
                                                    </div>
                                                </div>

                                                <div class="row g-2">
                                                    @foreach($booking->persons as $person)
                                                        <div class="col-md-6 col-lg-4 passenger-item-col" 
                                                            data-pax-name="{{ $person->full_name }}"
                                                            data-passport="{{ $person->passport_number ?? '' }}"
                                                            data-cnic="{{ $person->cnic ?? '' }}"
                                                            data-phone="{{ $person->mobile_number ?? $person->phone ?? '' }}">
                                                            <div class="form-check p-2 bg-white border rounded d-flex align-items-center gap-2">
                                                                <input class="form-check-input ms-0 passenger-checkbox" type="checkbox" name="person_ids[]" value="{{ $person->id }}" id="person_{{ $person->id }}" data-booking="{{ $booking->id }}" onchange="updateSelectedPaxCount()" {{ is_array(old('person_ids')) && in_array($person->id, old('person_ids')) ? 'checked' : '' }}>
                                                                <label class="form-check-label w-100 cursor-pointer mb-0" for="person_{{ $person->id }}">
                                                                    <strong class="d-block text-dark fs-13 search-target">{{ $person->full_name }}</strong>
                                                                    <small class="text-muted d-block search-target">Passport: {{ $person->passport_number ?? 'N/A' }} | CNIC: {{ $person->cnic ?? 'N/A' }}</small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-center py-4 text-muted">No active bookings or passengers found.</div>
                                    @endforelse

                                    <div id="noPassengersMatchMessage" class="text-center py-4 text-muted" style="display: none;">
                                        <i class="mdi mdi-account-search-outline d-block fs-24 mb-1"></i>
                                        <span>No matching passengers or bookings found.</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        function autoFillFlightDetails(selectEl) {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.flightno) {
                if (selectedOpt.dataset.flightno) document.getElementById('flight_number_input').value = selectedOpt.dataset.flightno;
                if (selectedOpt.dataset.date) document.getElementById('flight_date_input').value = selectedOpt.dataset.date;
                if (selectedOpt.dataset.time) document.getElementById('flight_time_input').value = selectedOpt.dataset.time;
            }
        }

        function updateSelectedPaxCount() {
            const count = document.querySelectorAll('.passenger-checkbox:checked').length;
            document.getElementById('selectedPaxCount').textContent = count + ' Pax';
        }

        function toggleSelectAllPassengers(selectState) {
            const checkboxes = document.querySelectorAll('.passenger-checkbox');
            checkboxes.forEach(cb => {
                const itemCol = cb.closest('.passenger-item-col');
                const bookingBox = cb.closest('.booking-group-box');
                const isItemVisible = !itemCol || itemCol.style.display !== 'none';
                const isBookingVisible = !bookingBox || bookingBox.style.display !== 'none';
                if (isItemVisible && isBookingVisible) {
                    cb.checked = selectState;
                }
            });
            updateSelectedPaxCount();
        }

        function toggleBookingPassengers(bookingId, selectState) {
            const checkboxes = document.querySelectorAll(`.passenger-checkbox[data-booking="${bookingId}"]`);
            checkboxes.forEach(cb => {
                const itemCol = cb.closest('.passenger-item-col');
                const bookingBox = cb.closest('.booking-group-box');
                const isItemVisible = !itemCol || itemCol.style.display !== 'none';
                const isBookingVisible = !bookingBox || bookingBox.style.display !== 'none';
                if (isItemVisible && isBookingVisible) {
                    cb.checked = selectState;
                }
            });
            updateSelectedPaxCount();
        }

        function filterPassengers() {
            const input = document.getElementById('passengerSearchInput');
            const q = input ? input.value.trim().toLowerCase() : '';
            const bookingBoxes = document.querySelectorAll('.booking-group-box');
            const noMatchMsg = document.getElementById('noPassengersMatchMessage');

            if (q === '') {
                bookingBoxes.forEach(box => {
                    box.style.display = '';
                    box.querySelectorAll('.passenger-item-col').forEach(col => {
                        col.style.display = '';
                    });
                });
                if (noMatchMsg) noMatchMsg.style.display = 'none';
                return;
            }

            const qClean = q.replace(/[^a-z0-9]/gi, '');
            const terms = q.split(/\s+/).filter(Boolean);
            let visibleBookingsCount = 0;

            bookingBoxes.forEach(box => {
                const bookingNo = (box.getAttribute('data-booking-number') || '').toLowerCase();
                const bookingNoClean = bookingNo.replace(/[^a-z0-9]/gi, '');
                const bookingId = String(box.getAttribute('data-bookingid') || '').toLowerCase();
                const company = (box.getAttribute('data-company') || '').toLowerCase();
                const client = (box.getAttribute('data-client') || '').toLowerCase();

                // Match booking by booking number, ID, company, or client
                const bookingMatches = (
                    (bookingNo && bookingNo.includes(q)) ||
                    (bookingNoClean && qClean && bookingNoClean.includes(qClean) && qClean.length >= 3) ||
                    (bookingId && (bookingId === q || bookingNo.endsWith(q))) ||
                    (company && terms.every(t => company.includes(t))) ||
                    (client && terms.every(t => client.includes(t)))
                );

                const cols = box.querySelectorAll('.passenger-item-col');
                let matchingPaxCount = 0;

                cols.forEach(col => {
                    const paxName = (col.getAttribute('data-pax-name') || '').toLowerCase();
                    const passport = (col.getAttribute('data-passport') || '').toLowerCase();
                    const passportClean = passport.replace(/[^a-z0-9]/gi, '');
                    const cnic = (col.getAttribute('data-cnic') || '').toLowerCase();
                    const cnicClean = cnic.replace(/[^a-z0-9]/gi, '');
                    const phone = (col.getAttribute('data-phone') || '').toLowerCase();
                    const phoneClean = phone.replace(/[^a-z0-9]/gi, '');

                    // Match passenger by name, passport, CNIC, or phone
                    const paxMatches = (
                        (paxName && terms.every(t => paxName.includes(t))) ||
                        (passport && passport.includes(q)) ||
                        (passportClean && qClean && passportClean.includes(qClean)) ||
                        (cnic && cnic.includes(q)) ||
                        (cnicClean && qClean && cnicClean.includes(qClean)) ||
                        (phone && phone.includes(q)) ||
                        (phoneClean && qClean && phoneClean.includes(qClean))
                    );

                    if (paxMatches || bookingMatches) {
                        col.style.display = '';
                        matchingPaxCount++;
                    } else {
                        col.style.display = 'none';
                    }
                });

                // Show booking box ONLY if at least 1 passenger matches or booking matched
                if (matchingPaxCount > 0) {
                    box.style.display = '';
                    visibleBookingsCount++;
                } else {
                    box.style.display = 'none';
                }
            });

            if (noMatchMsg) {
                noMatchMsg.style.display = visibleBookingsCount === 0 ? 'block' : 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedPaxCount();
        });
    </script>

@endsection
