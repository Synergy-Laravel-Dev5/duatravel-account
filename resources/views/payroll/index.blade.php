@extends('layout.master')
@section('title', 'Staff Payroll Management')
@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE HEADER -->
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="mdi mdi-cash-multiple text-primary me-1"></i> Staff Payroll Management
                    </h4>
                    <small class="text-muted">Monthly salary calculations based on working days & automatic advance deductions</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-plus-circle me-1"></i> + Generate New Payroll
                    </a>
                    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-account-group me-1"></i> View Staff Members
                    </a>
                </div>
            </div>

            <!-- SUCCESS / ERROR ALERTS -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle me-1"></i> {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- KPI SUMMARY CARDS -->
            <div class="row g-3 mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-primary border-4 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Net Paid Salaries</span>
                                    <h4 class="fs-18 fw-bold text-dark my-1">PKR {{ number_format($totalNetPaid, 2) }}</h4>
                                    <small class="text-success fs-11"><i class="mdi mdi-check"></i> Disbursed Amount</small>
                                </div>
                                <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-cash-check fs-22 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-warning border-4 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Advances Deducted</span>
                                    <h4 class="fs-18 fw-bold text-dark my-1">PKR {{ number_format($totalAdvanceDeducted, 2) }}</h4>
                                    <small class="text-warning fs-11"><i class="mdi mdi-minus-circle"></i> Auto-Deducted</small>
                                </div>
                                <div class="avatar-sm bg-soft-warning rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-cash-minus fs-22 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-danger border-4 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Off / Leave Deductions</span>
                                    <h4 class="fs-18 fw-bold text-dark my-1">PKR {{ number_format($totalAbsentDeduction, 2) }}</h4>
                                    <small class="text-danger fs-11"><i class="mdi mdi-calendar-remove"></i> Unpaid Days</small>
                                </div>
                                <div class="avatar-sm bg-soft-danger rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-calendar-clock fs-22 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm border-start border-info border-4 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fs-12 fw-semibold text-uppercase">Total Payroll Records</span>
                                    <h4 class="fs-18 fw-bold text-dark my-1">{{ number_format($totalPayrollsCount) }}</h4>
                                    <small class="text-info fs-11"><i class="mdi mdi-file-document-outline"></i> Vouchers Generated</small>
                                </div>
                                <div class="avatar-sm bg-soft-info rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-account-card-details fs-22 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTER CARD -->
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('payroll.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-12 fw-semibold mb-1">Salary Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <option value="">-- All Months --</option>
                                @foreach ($months as $m)
                                    <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-12 fw-semibold mb-1">Salary Year</label>
                            <select name="year" class="form-select form-select-sm">
                                @for ($y = date('Y') + 1; $y >= 2023; $y--)
                                    <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fs-12 fw-semibold mb-1">Staff Member</label>
                            <select name="staff_id" class="form-select form-select-sm">
                                <option value="">-- All Staff Members --</option>
                                @foreach ($staffList as $st)
                                    <option value="{{ $st->id }}" {{ (string)$selectedStaff === (string)$st->id ? 'selected' : '' }}>
                                        {{ $st->first_name }} {{ $st->last_name }} ({{ $st->department->name ?? 'Staff' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-12 fw-semibold mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- All Statuses --</option>
                                <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="unpaid" {{ $selectedStatus === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="draft" {{ $selectedStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="mdi mdi-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                                <i class="mdi mdi-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PAYROLL DATA TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0">
                            <thead class="table-light fs-12">
                                <tr>
                                    <th>Voucher #</th>
                                    <th>Staff Member</th>
                                    <th>Salary Month</th>
                                    <th class="text-center">Working Days</th>
                                    <th class="text-end">Basic Salary</th>
                                    <th class="text-end">Off Deduction</th>
                                    <th class="text-end">Adv Deducted</th>
                                    <th class="text-end">Net Salary</th>
                                    <th class="text-center">Payment Info</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="fs-13">
                                @forelse ($payrolls as $p)
                                    <tr>
                                        <td>
                                            <a href="{{ route('payroll.show', $p->id) }}" class="fw-bold text-primary">
                                                {{ $p->payroll_number }}
                                            </a>
                                            <small class="d-block text-muted fs-11">{{ $p->payment_date ? $p->payment_date->format('d M, Y') : '-' }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $p->staff->first_name ?? 'Staff' }} {{ $p->staff->last_name ?? '' }}</strong>
                                            <div class="fs-11 text-muted">
                                                {{ $p->staff->department->name ?? 'General' }} &bull; {{ $p->staff->designation->name ?? '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-info text-info border">
                                                <i class="mdi mdi-calendar me-1"></i> {{ $p->salary_month }} {{ $p->salary_year }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-dark">{{ $p->working_days }} / {{ $p->total_days }}</strong>
                                            @if ($p->off_days > 0)
                                                <span class="badge bg-soft-danger text-danger fs-11 d-block mt-1">
                                                    {{ $p->off_days }} Off / Absent
                                                </span>
                                            @else
                                                <span class="badge bg-soft-success text-success fs-11 d-block mt-1">
                                                    Full Attendance
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format($p->basic_salary, 2) }}
                                        </td>
                                        <td class="text-end text-danger fw-semibold">
                                            {{ $p->absent_deduction > 0 ? '-' . number_format($p->absent_deduction, 2) : '0.00' }}
                                        </td>
                                        <td class="text-end text-warning fw-semibold">
                                            {{ $p->advance_salary > 0 ? '-' . number_format($p->advance_salary, 2) : '0.00' }}
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success fs-14">PKR {{ number_format($p->net_salary, 2) }}</strong>
                                        </td>
                                        <td class="text-center fs-11">
                                            @if ($p->bank)
                                                <span class="badge bg-soft-primary text-primary">
                                                    <i class="mdi mdi-bank me-1"></i> {{ $p->bank->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-soft-success text-success">
                                                    <i class="mdi mdi-cash me-1"></i> Cash in Hand
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($p->status === 'paid')
                                                <span class="badge bg-success">
                                                    <i class="mdi mdi-check-circle me-1"></i> Paid
                                                </span>
                                            @elseif ($p->status === 'unpaid')
                                                <span class="badge bg-danger">
                                                    <i class="mdi mdi-close-circle me-1"></i> Unpaid
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    Draft
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-outline-primary" title="View Pay Slip">
                                                    <i class="mdi mdi-printer"></i> Slip
                                                </a>
                                                <a href="{{ route('payroll.edit', $p->id) }}" class="btn btn-outline-info" title="Edit Payroll">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('payroll.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payroll voucher?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete Payroll">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-cash-remove fs-24 d-block mb-1 text-muted"></i>
                                            No payroll records found for the selected period.
                                            <div class="mt-2">
                                                <a href="{{ route('payroll.create') }}" class="btn btn-sm btn-primary">
                                                    <i class="mdi mdi-plus-circle me-1"></i> Generate Payroll Now
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($payrolls->hasPages())
                        <div class="p-3 border-top">
                            {{ $payrolls->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
