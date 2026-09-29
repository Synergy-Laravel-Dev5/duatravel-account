@extends('layout.master')
@section('title', 'Add Travel Route')
@section('header-title', 'Add Travel Route')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Add New Travel Route</h4>
                    <a href="{{ route('travel-route.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Travel Route Information</h5>
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
                                <form action="{{ route('travel-route.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Route Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" value="{{ old('name') }}"
                                                    class="form-control" placeholder="Enter Route Name (e.g. Jeddah to Makkah Transfer)" required>
                                                @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Vehicle</label>
                                                <select name="vehicle_id" class="form-select">
                                                    <option value="">Select Vehicle</option>
                                                    @foreach($vehicles as $vehicle)
                                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                            {{ $vehicle->vehicle_type ?? 'Vehicle' }} - {{ $vehicle->brand_name ?? '' }} ({{ $vehicle->plate_number ?? 'No Plate' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('vehicle_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Arrival Date</label>
                                                <input type="date" name="arrival_date" value="{{ old('arrival_date') }}"
                                                    class="form-control">
                                                @error('arrival_date')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Arrival Time</label>
                                                <input type="time" name="arrival_time" value="{{ old('arrival_time') }}"
                                                    class="form-control">
                                                @error('arrival_time')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Group Sharing / Sharing Type</label>
                                                <select name="sharing_type" class="form-select">
                                                    <option value="">Select Sharing Type</option>
                                                    <option value="Group" {{ old('sharing_type') == 'Group' ? 'selected' : '' }}>Group</option>
                                                    <option value="Private" {{ old('sharing_type') == 'Private' ? 'selected' : '' }}>Private</option>
                                                    <option value="Economy" {{ old('sharing_type') == 'Economy' ? 'selected' : '' }}>Economy</option>
                                                </select>
                                                @error('sharing_type')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                @error('status')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="card border">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 fw-bold">Select Routes (Places)</h6>
                                                    <small class="text-muted">Check the routes to include in this Travel Route</small>
                                                </div>
                                                <div class="card-body">
                                                    @forelse($routes as $route)
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="routes[]"
                                                                value="{{ $route->id }}" id="route_{{ $route->id }}"
                                                                {{ is_array(old('routes')) && in_array($route->id, old('routes')) ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-medium" for="route_{{ $route->id }}">
                                                                {{ $route->formatted_route }}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <p class="text-muted mb-0">No routes available. Please <a href="{{ route('route.create') }}" target="_blank">create routes first</a>.</p>
                                                    @endforelse
                                                    @error('routes')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary px-4">Save Travel Route</button>
                                            <a href="{{ route('travel-route.index') }}" class="btn btn-secondary">Cancel</a>
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
