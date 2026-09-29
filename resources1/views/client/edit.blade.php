@extends('layout.master')
@section('title', 'Edit Client')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Edit Client</h4>
                    <a href="{{ route('client.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="mdi mdi-account-edit me-2 text-primary"></i>
                                    Update Client Information
                                </h5>
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
                                <form action="{{ route('client.update', $client->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Full Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name"
                                                    value="{{ old('name', $client->name) }}" class="form-control"
                                                    placeholder="Enter full name" required>
                                                @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" name="company_name"
                                                    value="{{ old('company_name', $client->company_name) }}"
                                                    class="form-control" placeholder="Enter company name">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Package</label>
                                            <select name="package_id" class="form-select">
                                                <option value="">Select Package</option>

                                                @foreach ($packages as $package)
                                                    <option value="{{ $package->id }}"
                                                        {{ old('package_id', $client->package_id) == $package->id ? 'selected' : '' }}>
                                                        {{ $package->name }} ({{ $package->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Passport Number</label>
                                                <input type="text" name="passport_number"
                                                    value="{{ old('passport_number', $client->passport_number) }}"
                                                    class="form-control" placeholder="AA1234567">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">CNIC Number</label>
                                                <input type="text" name="cnic"
                                                    value="{{ old('cnic', $client->cnic) }}" class="form-control"
                                                    placeholder="XXXXX-XXXXXXX-X">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Phone Number</label>
                                                <input type="text" name="phone"
                                                    value="{{ old('phone', $client->phone) }}" class="form-control"
                                                    placeholder="+92 300 0000000">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" name="email"
                                                    value="{{ old('email', $client->email) }}" class="form-control"
                                                    placeholder="client@example.com">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                                <select name="type" class="form-control" required>
                                                    <option value="client"
                                                        {{ old('type', $client->type) == 'client' ? 'selected' : '' }}>
                                                        Client</option>
                                                    <option value="vendor"
                                                        {{ old('type', $client->type) == 'vendor' ? 'selected' : '' }}>
                                                        Vendor</option>
                                                    <option value="customer"
                                                        {{ old('type', $client->type) == 'customer' ? 'selected' : '' }}>
                                                        Customer</option>
                                                </select>
                                                @error('type')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="active"
                                                        {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>
                                                        Active</option>
                                                    <option value="inactive"
                                                        {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>
                                                        Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-12 text-end">
                                            <a href="{{ route('client.index') }}" class="btn btn-secondary">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-success px-4">
                                                <i class="mdi mdi-content-save me-1"></i> Update Client
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
