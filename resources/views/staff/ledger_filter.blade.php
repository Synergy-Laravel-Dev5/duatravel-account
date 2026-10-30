@extends('layout.master')

@section('title', 'Staff Ledger Filter')
@section('header-title', 'Staff Ledger Filter')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- TOP BAR -->
                <div class="py-3 d-flex justify-content-between align-items-center">
                    <h4 class="fs-18 fw-semibold m-0 text-dark">
                        <i class="mdi mdi-badge-account-horizontal text-primary me-1"></i> Staff Ledger Filter
                    </h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('reports.hub', ['tab' => 'staff']) }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-layers-outline me-1"></i> All-In-One Reports Hub
                        </a>
                        <a href="{{ route('staff.index') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Staff
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-sm">

                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 text-dark fw-bold">
                                    <i class="mdi mdi-filter text-primary me-1"></i> Search Staff Ledger
                                </h5>
                                <p class="text-muted fs-12 mb-0">View complete statement of salaries accrued, monthly payrolls, advances taken, disbursements, and net balance.</p>
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

                                <form action="{{ route('staff.ledger') }}" method="POST">
                                    @csrf

                                    <div class="row g-3">

                                        <!-- Select Staff Member -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark fs-13">
                                                Select Staff Member <span class="text-danger">*</span>
                                            </label>
                                            <select name="staff_id" class="form-select form-select-sm select2" required>
                                                <option value="">-- Choose Staff Member --</option>
                                                @foreach ($staffMembers as $s)
                                                    <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>
                                                        {{ $s->first_name }} {{ $s->last_name }} ({{ $s->designation->name ?? 'Staff' }}{{ $s->department ? ' - ' . $s->department->name : '' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- From Date -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold fs-13">From Date</label>
                                            <input type="date" name="from_date" class="form-control form-control-sm"
                                                value="{{ old('from_date') }}">
                                            <small class="text-muted fs-11">Leave blank to view all historical transactions</small>
                                        </div>

                                        <!-- To Date -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold fs-13">To Date</label>
                                            <input type="date" name="to_date" class="form-control form-control-sm"
                                                value="{{ old('to_date') }}">
                                            <small class="text-muted fs-11">Leave blank for today</small>
                                        </div>

                                        <div class="col-12 text-end pt-3 border-top mt-4">
                                            <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary btn-sm me-2">Cancel</a>
                                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                                <i class="mdi mdi-magnify me-1"></i> Generate Ledger
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
