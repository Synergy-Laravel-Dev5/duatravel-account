@extends('layout.master')
@section('title', 'Edit Supplier')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Edit Supplier</h4>
                    <a href="{{ route('supplier.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to List
                    </a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('supplier.update', $supplier->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Supplier / Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Al Safwa Services" value="{{ old('name', $supplier->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" placeholder="e.g. Al Safwa Group KSA" value="{{ old('company_name', $supplier->company_name) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Service Type</label>
                                    <select name="service_type" class="form-select">
                                        <option value="all" {{ old('service_type', $supplier->service_type) == 'all' ? 'selected' : '' }}>All Services (Full Vendor)</option>
                                        <option value="hotel" {{ old('service_type', $supplier->service_type) == 'hotel' ? 'selected' : '' }}>Hotel / Accommodation</option>
                                        <option value="visa" {{ old('service_type', $supplier->service_type) == 'visa' ? 'selected' : '' }}>Visa Services</option>
                                        <option value="flight" {{ old('service_type', $supplier->service_type) == 'flight' ? 'selected' : '' }}>Flight / Airline Ticket</option>
                                        <option value="transport" {{ old('service_type', $supplier->service_type) == 'transport' ? 'selected' : '' }}>Transport / Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" name="contact_person" class="form-control" placeholder="e.g. Sheikh Ahmed" value="{{ old('contact_person', $supplier->contact_person) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" placeholder="e.g. +966 50 000 0000" value="{{ old('phone', $supplier->phone) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="e.g. info@supplier.com" value="{{ old('email', $supplier->email) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City</label>
                                    <input type="text" name="city" class="form-control" placeholder="e.g. Makkah / Karachi" value="{{ old('city', $supplier->city) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Country</label>
                                    <input type="text" name="country" class="form-control" placeholder="e.g. Saudi Arabia / Pakistan" value="{{ old('country', $supplier->country) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Opening Balance (PKR / SAR)</label>
                                    <input type="number" step="0.01" name="opening_balance" class="form-control" placeholder="0.00" value="{{ old('opening_balance', $supplier->opening_balance) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" {{ old('status', $supplier->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $supplier->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Office / Physical Address</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Enter complete address...">{{ old('address', $supplier->address) }}</textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-content-save me-1"></i> Update Supplier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
