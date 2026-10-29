@extends('layout.master')
@section('title', 'Edit Staff Payroll')
@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE HEADER -->
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="mdi mdi-pencil-box text-primary me-1"></i> Edit Staff Payroll ({{ $payroll->payroll_number }})
                    </h4>
                    <small class="text-muted">Modify working days, off days, advance deduction, or payment status</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('payroll.show', $payroll->id) }}" class="btn btn-outline-info btn-sm">
                        <i class="mdi mdi-eye me-1"></i> View Pay Slip
                    </a>
                    <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            <!-- ERRORS / ALERTS -->
            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle me-1"></i>
                    <strong>Error:</strong> {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('payroll.update', $payroll->id) }}" method="POST" id="editPayrollForm">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    
                    <!-- LEFT COLUMN -->
                    <div class="col-lg-7">
                        
                        <!-- CARD 1: STAFF & PERIOD (READONLY INFO) -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-account-tie text-primary me-1"></i> Staff Member & Period
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6">
                                        <label class="form-label fs-12 fw-semibold text-muted mb-1">Staff Member</label>
                                        <div class="fs-14 fw-bold text-dark">
                                            {{ $payroll->staff->first_name ?? 'Staff' }} {{ $payroll->staff->last_name ?? '' }}
                                        </div>
                                        <small class="text-muted fs-11">
                                            {{ $payroll->staff->department->name ?? 'General' }} &bull; {{ $payroll->staff->designation->name ?? 'Staff' }}
                                        </small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fs-12 fw-semibold text-muted mb-1">Salary Month & Year</label>
                                        <div>
                                            <span class="badge bg-soft-primary text-primary fs-13 px-2 py-1 border">
                                                <i class="mdi mdi-calendar me-1"></i> {{ $payroll->salary_month }} {{ $payroll->salary_year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 2: ATTENDANCE & WORKING DAYS -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-calendar-clock text-primary me-1"></i> Attendance & Working Days Calculation
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fs-13 fw-semibold">Total Month Days <span class="text-danger">*</span></label>
                                        <input type="number" step="1" min="1" max="31" name="total_days" id="input_total_days"
                                            value="{{ old('total_days', $payroll->total_days) }}" class="form-control form-control-sm fw-bold" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fs-13 fw-semibold text-danger">Off / Absent Days <span class="text-danger">*</span></label>
                                        <input type="number" step="0.5" min="0" max="31" name="off_days" id="input_off_days"
                                            value="{{ old('off_days', $payroll->off_days) }}" class="form-control form-control-sm fw-bold border-danger" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fs-13 fw-semibold text-success">Actual Working Days</label>
                                        <input type="number" step="0.5" name="working_days" id="input_working_days"
                                            value="{{ old('working_days', $payroll->working_days) }}" class="form-control form-control-sm fw-bold bg-light text-success border-success" readonly required>
                                    </div>
                                </div>

                                <div class="alert alert-light border mt-3 mb-0 p-2 fs-12">
                                    <div class="row text-center g-2">
                                        <div class="col-4 border-end">
                                            <span class="text-muted d-block fs-11">Per Day Salary Rate</span>
                                            <strong class="text-dark fs-13" id="display_per_day">PKR 0.00</strong>
                                        </div>
                                        <div class="col-4 border-end">
                                            <span class="text-danger d-block fs-11">Off / Leave Deduction</span>
                                            <strong class="text-danger fs-13" id="display_absent_deduction">- PKR 0.00</strong>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-success d-block fs-11">Earned Gross Salary</span>
                                            <strong class="text-success fs-13" id="display_earned_salary">PKR 0.00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 3: ALLOWANCES & BONUSES -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-gift text-success me-1"></i> Allowances & Bonuses
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Allowance</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">PKR</span>
                                            <input type="number" step="any" min="0" name="allowance" id="input_allowance"
                                                value="{{ old('allowance', $payroll->allowance) }}" class="form-control form-control-sm">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Bonus</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">PKR</span>
                                            <input type="number" step="any" min="0" name="bonus" id="input_bonus"
                                                value="{{ old('bonus', $payroll->bonus) }}" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-lg-5">
                        
                        <!-- CARD 4: ADVANCE SALARY & DEDUCTIONS -->
                        <div class="card border-0 shadow-sm mb-3 border-top border-warning border-3">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-cash-minus text-warning me-1"></i> Advance & Deductions
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <label class="form-label fs-13 fw-bold text-dark">
                                        Advance Salary Deducted <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-soft-warning text-warning fw-bold">PKR</span>
                                        <input type="number" step="any" min="0" name="advance_salary" id="input_advance_salary"
                                            value="{{ old('advance_salary', $payroll->advance_salary) }}" class="form-control form-control-sm fw-bold text-danger fs-14" required>
                                    </div>
                                    <small class="text-muted fs-11">Advance deducted from monthly payroll</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-13 fw-semibold">Other Deductions</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">PKR</span>
                                        <input type="number" step="any" min="0" name="other_deduction" id="input_other_deduction"
                                            value="{{ old('other_deduction', $payroll->other_deduction) }}" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 5: NET SALARY PAYABLE -->
                        <div class="card border-0 shadow-sm mb-3 bg-soft-success border-success border-2">
                            <div class="card-body p-3 text-center">
                                <span class="text-uppercase text-muted fs-11 fw-bold tracking-wide">FINAL NET SALARY PAYABLE</span>
                                <h2 class="fs-26 fw-bolder text-success my-2" id="display_net_salary">PKR 0.00</h2>
                                
                                <div class="d-flex justify-content-between fs-11 text-muted border-top border-success border-opacity-25 pt-2 mt-2">
                                    <span>Basic: <strong id="sum_basic">0.00</strong></span>
                                    <span>Off Cut: <strong id="sum_off" class="text-danger">0.00</strong></span>
                                    <span>Adv Cut: <strong id="sum_adv" class="text-warning">0.00</strong></span>
                                </div>

                                <input type="hidden" name="basic_salary" id="hidden_basic_salary" value="{{ old('basic_salary', $payroll->basic_salary) }}">
                                <input type="hidden" name="net_salary" id="hidden_net_salary" value="{{ old('net_salary', $payroll->net_salary) }}">
                            </div>
                        </div>

                        <!-- CARD 6: DISBURSEMENT DETAILS -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-credit-card-check text-primary me-1"></i> Payment Details
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date" class="form-control form-control-sm"
                                            value="{{ old('payment_date', $payroll->payment_date ? $payroll->payment_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" id="payment_method_select" class="form-select form-select-sm" required>
                                            <option value="cash" {{ old('payment_method', $payroll->payment_method) === 'cash' ? 'selected' : '' }}>Cash in Hand</option>
                                            <option value="bank" {{ old('payment_method', $payroll->payment_method) === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="cheque" {{ old('payment_method', $payroll->payment_method) === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                            <option value="online" {{ old('payment_method', $payroll->payment_method) === 'online' ? 'selected' : '' }}>Online Banking</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12 {{ old('payment_method', $payroll->payment_method) !== 'cash' ? '' : 'd-none' }}" id="bank_select_box">
                                        <label class="form-label fs-13 fw-semibold">Disbursing Bank</label>
                                        <select name="bank_id" class="form-select form-select-sm">
                                            <option value="">-- Select Bank Account --</option>
                                            @foreach ($banks as $b)
                                                <option value="{{ $b->id }}" {{ old('bank_id', $payroll->bank_id) == $b->id ? 'selected' : '' }}>
                                                    {{ $b->name }}{{ $b->account_number ? ' (' . $b->account_number . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fs-13 fw-semibold">Status <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="status_paid" value="paid"
                                                    {{ old('status', $payroll->status) === 'paid' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-13 fw-semibold text-success" for="status_paid">
                                                    <i class="mdi mdi-check-circle me-1"></i> Paid
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="status_unpaid" value="unpaid"
                                                    {{ old('status', $payroll->status) === 'unpaid' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-13 fw-semibold text-danger" for="status_unpaid">
                                                    <i class="mdi mdi-clock-outline me-1"></i> Unpaid / Pending
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-check form-switch bg-light p-2 rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="auto_create_transaction" id="auto_create_transaction" value="1"
                                                {{ old('auto_create_transaction', $payroll->expense_transaction_id ? 1 : 0) ? 'checked' : '' }}>
                                            <label class="form-check-label fs-12 fw-bold text-dark" for="auto_create_transaction">
                                                <i class="mdi mdi-cash-register text-success me-1"></i> Auto-Record Payment in Cash Book & Transactions
                                            </label>
                                            <small class="text-muted d-block ms-4 fs-11">
                                                (اگر آپ Finance &rarr; Transactions سے الگ انٹری کرتے ہیں تو اسے بند رکھیں تاکہ رپورٹ میں ڈبل نہ ہو)
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fs-12 fw-semibold">Notes / Remarks</label>
                                        <textarea name="notes" class="form-control form-control-sm" rows="2">{{ old('notes', $payroll->notes) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTONS -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill py-2 fw-bold">
                                <i class="mdi mdi-content-save me-1"></i> Update Payroll
                            </button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-sm px-3">
                                Cancel
                            </a>
                        </div>

                    </div>

                </div>

            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalDaysInput = document.getElementById('input_total_days');
        const offDaysInput = document.getElementById('input_off_days');
        const workingDaysInput = document.getElementById('input_working_days');
        const hiddenBasicSalary = document.getElementById('hidden_basic_salary');

        const allowanceInput = document.getElementById('input_allowance');
        const bonusInput = document.getElementById('input_bonus');
        const advanceInput = document.getElementById('input_advance_salary');
        const otherDedInput = document.getElementById('input_other_deduction');
        const hiddenNet = document.getElementById('hidden_net_salary');

        const displayPerDay = document.getElementById('display_per_day');
        const displayAbsentDed = document.getElementById('display_absent_deduction');
        const displayEarned = document.getElementById('display_earned_salary');
        const displayNet = document.getElementById('display_net_salary');

        const sumBasic = document.getElementById('sum_basic');
        const sumOff = document.getElementById('sum_off');
        const sumAdv = document.getElementById('sum_adv');

        const payMethodSelect = document.getElementById('payment_method_select');
        const bankSelectBox = document.getElementById('bank_select_box');

        payMethodSelect.addEventListener('change', function() {
            if (this.value === 'cash') {
                bankSelectBox.classList.add('d-none');
            } else {
                bankSelectBox.classList.remove('d-none');
            }
        });

        function calculatePayroll() {
            const basic = parseFloat(hiddenBasicSalary.value) || 0;
            const totalDays = parseFloat(totalDaysInput.value) || 30;
            const offDays = parseFloat(offDaysInput.value) || 0;

            const workingDays = Math.max(0, totalDays - offDays);
            workingDaysInput.value = workingDays;

            const perDayRate = totalDays > 0 ? (basic / totalDays) : 0;
            displayPerDay.textContent = 'PKR ' + perDayRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const absentDeduction = perDayRate * offDays;
            displayAbsentDed.textContent = '- PKR ' + absentDeduction.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const earned = Math.max(0, basic - absentDeduction);
            displayEarned.textContent = 'PKR ' + earned.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const allowance = parseFloat(allowanceInput.value) || 0;
            const bonus = parseFloat(bonusInput.value) || 0;
            const gross = earned + allowance + bonus;

            const advance = parseFloat(advanceInput.value) || 0;
            const otherDed = parseFloat(otherDedInput.value) || 0;

            const netSalary = Math.max(0, gross - advance - otherDed);
            hiddenNet.value = netSalary.toFixed(2);
            displayNet.textContent = 'PKR ' + netSalary.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            sumBasic.textContent = basic.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            sumOff.textContent = absentDeduction.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            sumAdv.textContent = advance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        [totalDaysInput, offDaysInput, allowanceInput, bonusInput, advanceInput, otherDedInput].forEach(inp => {
            inp.addEventListener('input', calculatePayroll);
        });

        calculatePayroll();
    });
</script>
@endpush
