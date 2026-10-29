@extends('layout.master')

@section('title', 'Add Meal Plan')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-0 text-dark">
                        <i class="mdi mdi-plus-circle text-primary me-1"></i> Add Meal Plan
                    </h4>
                    <p class="text-muted fs-13 mb-0">Define a new meal plan option available in hotel bookings.</p>
                </div>
                <a href="{{ route('meal-plans.index') }}" class="btn btn-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Meal Plans
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show fs-13" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('meal-plans.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fs-13 fw-semibold">Meal Plan Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="e.g. Bed & Breakfast, Half Board" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Code / Abbreviation <span class="text-danger">*</span></label>
                                <input type="text" name="code" value="{{ old('code') }}" class="form-control text-uppercase" placeholder="e.g. BB, HB, FB, RO" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fs-13 fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fs-13 fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Details about this meal plan...">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-content-save me-1"></i> Save Meal Plan
                                </button>
                                <a href="{{ route('meal-plans.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
