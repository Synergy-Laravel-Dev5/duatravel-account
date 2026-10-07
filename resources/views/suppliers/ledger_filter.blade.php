@extends('layout.master')

@section('title', 'Supplier Ledger Filter')
@section('header-title', 'Supplier Ledger Filter')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0 text-dark">
                        <i class="mdi mdi-book-open-variant text-primary me-1"></i> Supplier Ledger Filter
                    </h4>
                    <a href="{{ route('supplier.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Suppliers
                    </a>
                </div>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-sm">

                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 text-dark fw-bold">
                                    <i class="mdi mdi-filter text-primary me-1"></i> Search Supplier Ledger
                                </h5>
                                <p class="text-muted fs-12 mb-0">View complete statement of payables (hotels, flights, visas, transports) and payments made to the supplier.</p>
                            </div>

                            <div class="card-body p-4">

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

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show fs-13" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('supplier.ledger.view') }}" method="POST">
                                    @csrf

                                    <div class="row g-3">

                                        <!-- SUPPLIER -->
                                        <div class="col-12">
                                            <label class="form-label fs-13 fw-semibold">
                                                Select Supplier <span class="text-danger">*</span>
                                            </label>
                                            <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                                <option value="">-- Select Supplier / Vendor --</option>
                                                @foreach ($suppliers as $s)
                                                    <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                                        {{ $s->name }} {{ $s->company_name ? ' (' . $s->company_name . ')' : '' }} - {{ strtoupper($s->service_type ?? 'Supplier') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('supplier_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- FROM DATE -->
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">From Date</label>
                                            <input type="date" name="from_date" class="form-control form-control-sm"
                                                value="{{ old('from_date') }}">
                                        </div>

                                        <!-- TO DATE -->
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">To Date</label>
                                            <input type="date" name="to_date" class="form-control form-control-sm"
                                                value="{{ old('to_date') }}">
                                        </div>

                                    </div>

                                    <!-- SUBMIT -->
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="mdi mdi-book-open-page-variant me-1"></i> View Supplier Ledger
                                        </button>
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
