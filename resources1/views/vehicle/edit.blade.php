@extends('layout.master')
@section('title', 'Edit Vehicle')
@section('header-title', 'Edit Vehicle')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Edit Vehicle</h4>
                    <a href="{{ route('vehicle.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Vehicle Information</h5>
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
                                <form action="{{ route('vehicle.update', $vehicle->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Vehicle Type</label>
                                                <input type="text" name="vehicle_type" value="{{ old('vehicle_type', $vehicle->vehicle_type) }}"
                                                    class="form-control" placeholder="Enter Vehicle Type (e.g. Bus, SUV)">
                                                @error('vehicle_type')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Brand / Model Name</label>
                                                <input type="text" name="brand_name" value="{{ old('brand_name', $vehicle->brand_name) }}"
                                                    class="form-control" placeholder="Enter Brand/Model Name (e.g. Mercedes Travego)">
                                                @error('brand_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Model Year</label>
                                                <input type="text" name="model_year" value="{{ old('model_year', $vehicle->model_year) }}"
                                                    class="form-control" placeholder="Enter Model Year (e.g. 2024)">
                                                @error('model_year')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Plate Number</label>
                                                <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}"
                                                    class="form-control" placeholder="Enter Plate Number">
                                                @error('plate_number')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Supplier Name</label>
                                                <input type="text" name="supplier_name" value="{{ old('supplier_name', $vehicle->supplier_name) }}"
                                                    class="form-control" placeholder="Enter Supplier Name">
                                                @error('supplier_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="active" {{ old('status', $vehicle->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status', $vehicle->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                @error('status')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary px-4">
                                                Update Vehicle
                                            </button>
                                            <a href="{{ route('vehicle.index') }}" class="btn btn-secondary">
                                                Cancel
                                            </a>
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
