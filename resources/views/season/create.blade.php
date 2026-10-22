@extends('layout.master')

@section('title', 'Add New Season')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Title & Back -->
            <div class="py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-18 fw-semibold m-0 text-dark">
                        <i class="mdi mdi-calendar-plus me-1 text-primary"></i> Add New Season
                    </h4>
                    <p class="text-muted fs-13 mb-0">Define a new operational & financial season for Hajj or Umrah.</p>
                </div>
                <a href="{{ route('season.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Seasons
                </a>
            </div>

            <!-- Error Alerts -->
            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('season.store') }}" method="POST" id="seasonForm">
                                @csrf

                                <!-- Season Type Selection -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Season Type <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="season_type" id="type_hajj" value="hajj" {{ old('season_type', 'hajj') === 'hajj' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="type_hajj">
                                                🕋 Hajj Season
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="season_type" id="type_umrah" value="umrah" {{ old('season_type') === 'umrah' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="type_umrah">
                                                🕌 Umrah Season
                                            </label>
                                        </div>
                                    </div>
                                    <small class="text-muted">The system will categorize all bookings, vouchers, rooming manifests and accounts under this season type.</small>
                                </div>

                                <div class="row g-3">
                                    <!-- Year -->
                                    <div class="col-md-6">
                                        <label for="year" class="form-label fw-semibold">Year (Gregorian) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('year') is-invalid @enderror" id="year" name="year" min="2000" max="2100" value="{{ old('year', $currentYear ?? date('Y')) }}" required>
                                        <small class="text-muted">Primary year used for filtering reports & transactions.</small>
                                        @error('year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Hijri Year -->
                                    <div class="col-md-6">
                                        <label for="hijri_year" class="form-label fw-semibold">Hijri Year</label>
                                        <input type="text" class="form-control @error('hijri_year') is-invalid @enderror" id="hijri_year" name="hijri_year" placeholder="e.g. 1447H or 1447H-1448H" value="{{ old('hijri_year') }}">
                                        <small class="text-muted">Displayed on reports, pilgrim badges & invoices.</small>
                                        @error('hijri_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Season Name -->
                                    <div class="col-12">
                                        <label for="name" class="form-label fw-semibold">Season Title / Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="e.g. Hajj 2026 or Umrah 2026-2027" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Start Date -->
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}">
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Operational Status <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-2">
                                            <input type="radio" class="btn-check" name="status" id="status_active" value="active" {{ old('status', 'active') === 'active' ? 'checked' : '' }} autocomplete="off">
                                            <label class="btn btn-outline-success flex-fill fw-bold py-2" for="status_active">
                                                <i class="mdi mdi-check-circle me-1"></i> Active
                                            </label>

                                            <input type="radio" class="btn-check" name="status" id="status_upcoming" value="upcoming" {{ old('status') === 'upcoming' ? 'checked' : '' }} autocomplete="off">
                                            <label class="btn btn-outline-info flex-fill fw-bold py-2" for="status_upcoming">
                                                <i class="mdi mdi-clock-outline me-1"></i> Upcoming
                                            </label>

                                            <input type="radio" class="btn-check" name="status" id="status_inactive" value="closed" {{ old('status') === 'closed' ? 'checked' : '' }} autocomplete="off">
                                            <label class="btn btn-outline-danger flex-fill fw-bold py-2" for="status_inactive">
                                                <i class="mdi mdi-close-circle me-1"></i> Inactive
                                            </label>
                                        </div>
                                        @error('status')
                                            <div class="text-danger fs-12 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Is Active Default -->
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="is_active">
                                                Set as Default Active Season for this type
                                            </label>
                                            <small class="text-muted d-block">When users log in, the system will automatically default to this season.</small>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-12">
                                        <label for="notes" class="form-label fw-semibold">Notes / Description</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Optional notes about quota, Ministry guidelines, package details...">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-4 pt-2 border-top d-flex gap-2 justify-content-end">
                                    <a href="{{ route('season.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-check-circle-outline me-1"></i> Save Season
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Guidance Info Box -->
                <div class="col-lg-4">
                    <div class="card border-primary border-opacity-25">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                <i class="mdi mdi-lightbulb-on-outline me-1"></i> How Seasons Drive the System
                            </h5>
                            <p class="fs-13 text-muted">
                                Dua Travels operates across multiple seasons. Whenever you or any staff member switches season:
                            </p>
                            <ul class="fs-13 text-muted ps-3 mb-3">
                                <li><strong>Dashboard:</strong> Displays revenue, pilgrims count, and summaries for the season.</li>
                                <li><strong>Bookings & Clients:</strong> Filtered by the active package type and year.</li>
                                <li><strong>Expense Transactions & Cash Book:</strong> Shows vouchers, supplier ledgers, and contra accounts matching the season.</li>
                                <li><strong>Profit & Loss:</strong> Automatically calculates gross and net profit for that season.</li>
                                <li><strong>Room Allocation Manifest:</strong> Shows room assignments specific to that Hajj or Umrah season.</li>
                            </ul>
                            <div class="alert alert-info py-2 px-3 fs-12 mb-0">
                                <strong>Tip:</strong> You can quickly switch between active seasons at any time from the top header switcher.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('name');
        const yearInput = document.getElementById('year');
        const typeHajj = document.getElementById('type_hajj');
        const typeUmrah = document.getElementById('type_umrah');

        let isManualName = nameInput.value.trim().length > 0;

        nameInput.addEventListener('input', function() {
            if (nameInput.value.trim().length > 0) {
                isManualName = true;
            }
        });

        function autoUpdateName() {
            if (isManualName) return;
            const yr = yearInput.value || new Date().getFullYear();
            const isUmrah = typeUmrah.checked;
            if (isUmrah) {
                const nextYr = parseInt(yr) + 1;
                nameInput.value = 'Umrah ' + yr + '-' + nextYr;
            } else {
                nameInput.value = 'Hajj ' + yr;
            }
        }

        yearInput.addEventListener('input', autoUpdateName);
        typeHajj.addEventListener('change', autoUpdateName);
        typeUmrah.addEventListener('change', autoUpdateName);

        if (!isManualName) {
            autoUpdateName();
        }
    });
</script>
@endsection
