@extends('layout.master')
@section('title', 'Edit Lead')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <h4 class="fs-18 fw-semibold py-3">Edit Lead</h4>
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('lead.update', $lead->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" name="contact_person" class="form-control"
                                        placeholder="Enter contact person"
                                        value="{{ old('contact_person', $lead->contact_person) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control"
                                        placeholder="Enter phone number" value="{{ old('phone', $lead->phone) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="Enter email"
                                        value="{{ old('email', $lead->email) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company</label>
                                    <select name="company_id" class="form-select">
                                        <option value="">Select Company</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ old('company_id', $lead->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Package</label>
                                    <select name="package_id" class="form-select">
                                        <option value="">Select Package</option>
                                        @foreach ($packages as $package)
                                            <option value="{{ $package->id }}"
                                                {{ old('package_id', $lead->package_id) == $package->id ? 'selected' : '' }}>
                                                {{ $package->package_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Number Of Person</label>
                                    <input type="number" name="number_of_pax" class="form-control"
                                        placeholder="Enter Number Of Person"
                                        value="{{ old('number_of_pax', $lead->number_of_pax) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lead Source</label>
                                    <select name="source" class="form-select">
                                        <option value="">Select Source</option>
                                        @foreach (\App\Enums\LeadSource::cases() as $source)
                                            <option value="{{ $source->value }}"
                                                {{ old('source', $lead->source) == $source->value ? 'selected' : '' }}>
                                                {{ ucfirst($source->value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Medium Of Contact</label>
                                    <select name="medium_of_contact" class="form-select">
                                        <option value="">Select Medium</option>
                                        @foreach (\App\Enums\MediumOfContact::cases() as $medium)
                                            <option value="{{ $medium->value }}"
                                                {{ old('medium_of_contact', $lead->medium_of_contact) == $medium->value ? 'selected' : '' }}>
                                                {{ ucfirst($medium->value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Assign User</label>

                                    <select name="user_id" class="form-select">
                                        <option value="">Select User</option>

                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ old('user_id', $lead->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="4" class="form-control" placeholder="Enter description">{{ old('description', $lead->description) }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>
                            <a href="{{ route('lead.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
