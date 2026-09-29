@extends('layout.master')

@section('title', 'Company Ledger')
@section('header-title', 'Company Ledger')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container">

                <!-- TOP BAR -->
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0">Company Ledger</h4>
                    <a href="{{ route('transaction.index') }}" class="btn btn-secondary btn-sm">
                        Back
                    </a>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <!-- HEADER -->
                            <div class="card-header">
                                <h5 class="mb-0">Search Company Ledger</h5>
                            </div>

                            <div class="card-body">

                                <!-- GLOBAL ERRORS -->
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('transaction.company-ledger.view') }}" method="GET">

                                    <div class="row">

                                        <!-- COMPANY -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Select Company <span class="text-danger">*</span>
                                                </label>

                                                <select name="company_id"
                                                    class="form-select @error('company_id') is-invalid @enderror" required>
                                                    <option value="">-- Select Company --</option>

                                                    @foreach ($companies as $company)
                                                        <option value="{{ $company->id }}"
                                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                            {{ $company->company_name ?? $company->name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                @error('company_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- FROM DATE -->
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label class="form-label">From Date</label>

                                                <input type="date" name="from_date" value="{{ old('from_date') }}"
                                                    class="form-control">

                                                <small class="text-muted">
                                                    Optional — filters transactions only
                                                </small>
                                            </div>
                                        </div>

                                        <!-- TO DATE -->
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label class="form-label">To Date</label>

                                                <input type="date" name="to_date" value="{{ old('to_date') }}"
                                                    class="form-control">
                                            </div>
                                        </div>

                                    </div>

                                    <!-- BUTTONS -->
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="fas fa-search me-1"></i> Search
                                            </button>

                                            <a href="{{ route('transaction.index') }}" class="btn btn-secondary">
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
