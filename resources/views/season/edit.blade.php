@extends('layout.master')

@section('title', 'Edit Season - ' . $season->name)

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Title & Back -->
            <div class="py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-18 fw-semibold m-0 text-dark">
                        <i class="mdi mdi-pencil-box-outline me-1 text-primary"></i> Edit Season: {{ $season->name }}
                    </h4>
                    <p class="text-muted fs-13 mb-0">Update season attributes, dates, operational status or active default.</p>
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
                            <form action="{{ route('season.update', $season->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Season Type Selection -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Season Type <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="season_type" id="type_hajj" value="hajj" {{ old('season_type', $season->season_type) === 'hajj' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="type_hajj">
                                                🕋 Hajj Season
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="season_type" id="type_umrah" value="umrah" {{ old('season_type', $season->season_type) === 'umrah' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="type_umrah">
                                                🕌 Umrah Season
                                            </label>
                                        </div>
                                    </div>
                                    <small class="text-muted">The system categorizes bookings, vouchers and manifests according to this season type.</small>
                                </div>

                                <div class="row g-3">
                                    <!-- Year -->
                                    <div class="col-md-6">
                                        <label for="year" class="form-label fw-semibold">Year (Gregorian) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('year') is-invalid @enderror" id="year" name="year" min="2000" max="2100" value="{{ old('year', $season->year) }}" required>
                                        <small class="text-muted">Year used across accounts and reports.</small>
                                        @error('year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Hijri Year -->
                                    <div class="col-md-6">
                                        <label for="hijri_year" class="form-label fw-semibold">Hijri Year</label>
                                        <input type="text" class="form-control @error('hijri_year') is-invalid @enderror" id="hijri_year" name="hijri_year" placeholder="e.g. 1447H or 1447H-1448H" value="{{ old('hijri_year', $season->hijri_year) }}">
                                        @error('hijri_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Season Name -->
                                    <div class="col-12">
                                        <label for="name" class="form-label fw-semibold">Season Title / Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $season->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Start Date -->
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $season->start_date ? $season->start_date->format('Y-m-d') : '') }}">
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label fw-semibold">End Date</label>
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $season->end_date ? $season->end_date->format('Y-m-d') : '') }}">
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Operational Status <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-2">
                                            <input type="radio" class="btn-check" name="status" id="status_active" value="active" {{ old('status', $season->status) === 'active' ? 'checked' : '' }} autocomplete="off">
                                            <label class="btn btn-outline-success flex-fill fw-bold py-2" for="status_active">
                                                <i class="mdi mdi-check-circle me-1"></i> Active
                                            </label>

                                            <input type="radio" class="btn-check" name="status" id="status_upcoming" value="upcoming" {{ old('status', $season->status) === 'upcoming' ? 'checked' : '' }} autocomplete="off">
                                            <label class="btn btn-outline-info flex-fill fw-bold py-2" for="status_upcoming">
                                                <i class="mdi mdi-clock-outline me-1"></i> Upcoming
                                            </label>

                                            <input type="radio" class="btn-check" name="status" id="status_inactive" value="closed" {{ old('status', $season->status) === 'closed' ? 'checked' : '' }} autocomplete="off">
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
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $season->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="is_active">
                                                Set as Default Active Season for this type
                                            </label>
                                            <small class="text-muted d-block">When users log in, the system will automatically default to this season.</small>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-12">
                                        <label for="notes" class="form-label fw-semibold">Notes / Description</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $season->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-4 pt-2 border-top d-flex gap-2 justify-content-between">
                                    <a href="{{ route('season.switch', $season->id) }}" class="btn btn-outline-primary">
                                        <i class="mdi mdi-swap-horizontal me-1"></i> Switch System to this Season Now
                                    </a>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('season.index') }}" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="mdi mdi-check-circle-outline me-1"></i> Update Season
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title m-0 fs-14">Season Details & System Impact</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0 fs-13">
                                <li class="mb-2">
                                    <strong>Created At:</strong> {{ $season->created_at->format('d M Y, h:i A') }}
                                </li>
                                <li class="mb-2">
                                    <strong>Last Updated:</strong> {{ $season->updated_at->format('d M Y, h:i A') }}
                                </li>
                                <li class="mb-2">
                                    <strong>Default Active:</strong>
                                    @if($season->is_active)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </li>
                                <li class="mb-0">
                                    <strong>System Session:</strong>
                                    @if(session('selected_season_id') == $season->id)
                                        <span class="badge bg-primary">Currently Active in Session</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Not in Current Session</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
