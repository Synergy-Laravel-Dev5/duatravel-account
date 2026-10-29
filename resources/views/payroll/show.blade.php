@extends('layout.master')
@section('title', 'Salary Slip - ' . $payroll->payroll_number)
@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE ACTIONS (Hidden during print) -->
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 d-print-none">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="mdi mdi-receipt text-primary me-1"></i> Salary Pay Slip
                    </h4>
                    <small class="text-muted">Voucher: {{ $payroll->payroll_number }} &bull; {{ $payroll->salary_month }} {{ $payroll->salary_year }}</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm px-3" onclick="window.print();">
                        <i class="mdi mdi-printer me-1"></i> Print Pay Slip
                    </button>
                    <a href="{{ route('payroll.edit', $payroll->id) }}" class="btn btn-outline-info btn-sm">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Payrolls
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-print-none" role="alert">
                    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- PRINTABLE SALARY SLIP CARD -->
            <div class="card border shadow-sm salary-slip-card mb-4" id="printableSalarySlip">
                <div class="card-body p-4 p-md-5">

                    <!-- SLIP HEADER -->
                    <div class="border-bottom pb-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h3 class="fw-bold text-primary m-0 tracking-wide">DUATRAVEL</h3>
                                <p class="text-muted fs-12 mb-1">Hajj, Umrah & Tourism Services</p>
                                <small class="text-muted fs-11">
                                    <i class="mdi mdi-map-marker"></i> Main Office &bull; Karachi, Pakistan
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-soft-primary text-primary fs-12 px-3 py-2 border border-primary border-opacity-25 text-uppercase">
                                    Salary Pay Slip
                                </span>
                                <h5 class="fw-bold text-dark mt-2 mb-0">{{ $payroll->payroll_number }}</h5>
                                <small class="text-muted fs-11">Issue Date: {{ $payroll->payment_date ? $payroll->payment_date->format('d M, Y') : date('d M, Y') }}</small>
                            </div>
                        </div>

                        <div class="bg-light p-2 rounded text-center mt-3 border">
                            <span class="fs-13 fw-bold text-dark text-uppercase tracking-wider">
                                SALARY FOR THE PERIOD OF: <span class="text-primary">{{ strtoupper($payroll->salary_month) }} {{ $payroll->salary_year }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- EMPLOYEE INFORMATION GRID -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3 fs-13">
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Employee Name:</span>
                                    <strong class="text-dark fs-14">
                                        {{ $payroll->staff->first_name ?? 'Staff' }} {{ $payroll->staff->last_name ?? '' }}
                                    </strong>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Department:</span>
                                    <strong class="text-dark">{{ $payroll->staff->department->name ?? 'General' }}</strong>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Designation:</span>
                                    <strong class="text-dark">{{ $payroll->staff->designation->name ?? 'Staff Member' }}</strong>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Payment Status:</span>
                                    @if ($payroll->status === 'paid')
                                        <span class="badge bg-success">PAID</span>
                                    @else
                                        <span class="badge bg-danger">UNPAID</span>
                                    @endif
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Phone / Mobile:</span>
                                    <span class="text-dark">{{ $payroll->staff->phone ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Monthly Base Salary:</span>
                                    <span class="text-dark fw-bold">PKR {{ number_format($payroll->basic_salary, 2) }}</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Payment Method:</span>
                                    <span class="text-dark fw-semibold text-uppercase">
                                        {{ ucwords(str_replace('_', ' ', $payroll->payment_method)) }}
                                        {{ $payroll->bank ? ' (' . $payroll->bank->name . ')' : '' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="text-muted fs-11 d-block">Disbursement Date:</span>
                                    <span class="text-dark">{{ $payroll->payment_date ? $payroll->payment_date->format('d M, Y') : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ATTENDANCE & WORKING DAYS SUMMARY -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="p-3 border rounded bg-white">
                                <h6 class="fs-12 fw-bold text-uppercase text-muted mb-2">
                                    <i class="mdi mdi-calendar-check text-primary me-1"></i> Attendance & Working Days Details
                                </h6>
                                <div class="row text-center g-2 fs-13">
                                    <div class="col-md-3 col-6 border-end">
                                        <span class="text-muted fs-11 d-block">Total Month Days</span>
                                        <strong class="text-dark fs-15">{{ $payroll->total_days }} Days</strong>
                                    </div>
                                    <div class="col-md-3 col-6 border-end">
                                        <span class="text-danger fs-11 d-block">Off / Absent Days</span>
                                        <strong class="text-danger fs-15">{{ $payroll->off_days }} Days</strong>
                                    </div>
                                    <div class="col-md-3 col-6 border-end">
                                        <span class="text-success fs-11 d-block">Actual Working Days</span>
                                        <strong class="text-success fs-15">{{ $payroll->working_days }} Days</strong>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <span class="text-muted fs-11 d-block">Daily Salary Rate</span>
                                        <strong class="text-primary fs-15">PKR {{ number_format($payroll->per_day_salary, 2) }} / day</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SALARY BREAKDOWN: EARNINGS VS DEDUCTIONS -->
                    <div class="row g-4 mb-4">
                        
                        <!-- EARNINGS TABLE -->
                        <div class="col-md-6">
                            <div class="border rounded h-100">
                                <div class="bg-soft-success py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                    <span class="fs-13 fw-bold text-success text-uppercase">Earnings / Additions</span>
                                    <i class="mdi mdi-arrow-up-circle text-success fs-16"></i>
                                </div>
                                <table class="table table-sm table-borderless mb-0 fs-13">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted ps-3 py-2">Monthly Base Salary</td>
                                            <td class="text-end pe-3 py-2 fw-semibold">PKR {{ number_format($payroll->basic_salary, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-dark ps-3 py-2 fw-semibold">
                                                Earned Salary <small class="text-muted">({{ $payroll->working_days }} days @ {{ number_format($payroll->per_day_salary, 2) }})</small>
                                            </td>
                                            <td class="text-end pe-3 py-2 fw-bold text-success">PKR {{ number_format($payroll->earned_salary, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-3 py-2">Allowances / Perks</td>
                                            <td class="text-end pe-3 py-2">PKR {{ number_format($payroll->allowance, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-3 py-2">Incentive / Bonus</td>
                                            <td class="text-end pe-3 py-2">PKR {{ number_format($payroll->bonus, 2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="border-top bg-light">
                                        <tr>
                                            <th class="ps-3 py-2 text-dark">Total Gross Earnings:</th>
                                            <th class="text-end pe-3 py-2 text-success fs-14">PKR {{ number_format($payroll->gross_salary, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- DEDUCTIONS TABLE -->
                        <div class="col-md-6">
                            <div class="border rounded h-100">
                                <div class="bg-soft-danger py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                    <span class="fs-13 fw-bold text-danger text-uppercase">Deductions / Advance</span>
                                    <i class="mdi mdi-arrow-down-circle text-danger fs-16"></i>
                                </div>
                                <table class="table table-sm table-borderless mb-0 fs-13">
                                    <tbody>
                                        <tr>
                                            <td class="text-danger ps-3 py-2">
                                                Off / Absent Cut <small class="text-muted">({{ $payroll->off_days }} days @ {{ number_format($payroll->per_day_salary, 2) }})</small>
                                            </td>
                                            <td class="text-end pe-3 py-2 text-danger fw-semibold">
                                                - PKR {{ number_format($payroll->absent_deduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr class="bg-soft-warning">
                                            <td class="text-dark ps-3 py-2 fw-bold">
                                                <i class="mdi mdi-cash-minus text-warning me-1"></i> Advance Salary Deducted
                                            </td>
                                            <td class="text-end pe-3 py-2 text-danger fw-bold">
                                                - PKR {{ number_format($payroll->advance_salary, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-3 py-2">Other Deductions / Penalties</td>
                                            <td class="text-end pe-3 py-2 text-danger">
                                                - PKR {{ number_format($payroll->other_deduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-3 py-2">&nbsp;</td>
                                            <td class="text-end pe-3 py-2">&nbsp;</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="border-top bg-light">
                                        <tr>
                                            <th class="ps-3 py-2 text-dark">Total Deductions:</th>
                                            <th class="text-end pe-3 py-2 text-danger fs-14">- PKR {{ number_format($payroll->total_deductions, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- NET PAYABLE SALARY BANNER -->
                    <div class="card bg-soft-success border-success border-2 mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <span class="fs-12 fw-bold text-uppercase text-muted d-block">NET SALARY PAYABLE TO EMPLOYEE:</span>
                                    <small class="text-muted fs-11">
                                        Gross Earnings ({{ number_format($payroll->gross_salary, 2) }}) - Total Deductions ({{ number_format($payroll->total_deductions, 2) }})
                                    </small>
                                </div>
                                <div class="text-end">
                                    <h2 class="fs-28 fw-bolder text-success m-0">PKR {{ number_format($payroll->net_salary, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NOTES / REMARKS -->
                    @if ($payroll->notes)
                        <div class="p-3 border rounded bg-light mb-4 fs-12">
                            <strong>Remarks / Notes:</strong> {{ $payroll->notes }}
                        </div>
                    @endif

                    <!-- SIGNATURE BLOCKS -->
                    <div class="row pt-5 mt-4 text-center fs-12">
                        <div class="col-4">
                            <div class="border-top border-dark mx-4 pt-2">
                                <strong>Prepared By</strong>
                                <small class="text-muted d-block">Accounts Dept</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark mx-4 pt-2">
                                <strong>Verified & Approved</strong>
                                <small class="text-muted d-block">Management / HR</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark mx-4 pt-2">
                                <strong>Employee Signature</strong>
                                <small class="text-muted d-block">Received with thanks</small>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER SYSTEM NOTE -->
                    <div class="text-center text-muted fs-11 border-top pt-3 mt-4">
                        This is a computer-generated salary pay slip issued by Duatravel Account Management System.
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    body {
        background: #fff !important;
        font-size: 12px !important;
    }
    .content-page, .content, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
    }
    .d-print-none, .left-side-menu, .topbar, footer {
        display: none !important;
    }
    .salary-slip-card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
    }
    .card-body {
        padding: 10px !important;
    }
}
</style>
@endsection
