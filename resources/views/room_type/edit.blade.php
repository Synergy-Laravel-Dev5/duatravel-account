@extends('layout.master')
@section('title', 'Edit Room Type')
@section('header-title', 'Edit Room Type')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Edit Room Type: {{ $roomType->name }}</h4>
                    <a href="{{ route('room-type.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="mb-0 fw-semibold"><i class="mdi mdi-door me-1 text-primary"></i> Edit Room Type Information</h5>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $e)
                                                <li>{{ $e }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('room-type.update', $roomType->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Room Type Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" value="{{ old('name', $roomType->name) }}" class="form-control" placeholder="e.g. Single, Double, Triple, Quad, Suite" required>
                                            @error('name')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Code / Slug</label>
                                            <input type="text" name="code" value="{{ old('code', $roomType->code) }}" class="form-control" placeholder="e.g. quad, double, triple">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Capacity (No. of Beds / Pax) <span class="text-danger">*</span></label>
                                            <input type="number" name="capacity" value="{{ old('capacity', $roomType->capacity) }}" class="form-control" min="1" max="50" required>
                                            <small class="text-muted">Standard number of people or beds for this room type</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                            <select name="status" class="form-select" required>
                                                <option value="active" {{ old('status', $roomType->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $roomType->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control" rows="3" placeholder="Description of this room type">{{ old('description', $roomType->description) }}</textarea>
                                        </div>

                                        <div class="col-12 mt-4 text-end">
                                            <a href="{{ route('room-type.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="mdi mdi-content-save me-1"></i> Update Room Type
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
