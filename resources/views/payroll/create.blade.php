@extends('layout.master')
@section('title', 'Generate Staff Payroll')
@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE HEADER -->
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="mdi mdi-cash-plus text-primary me-1"></i> Generate Staff Payroll
                    </h4>
                    <small class="text-muted">Calculate salary based on working days & auto-deduct advance salary taken</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Payroll List
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

            <form action="{{ route('payroll.store') }}" method="POST" id="payrollForm">
                @csrf

                <div class="row g-3">
                    
                    <!-- LEFT COLUMN: STAFF & ATTENDANCE DETAILS -->
                    <div class="col-lg-7">
                        
                        <!-- CARD 1: STAFF & PERIOD SELECTION -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-account-tie text-primary me-1"></i> 1. Select Staff & Salary Period
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Staff Member <span class="text-danger">*</span></label>
                                        <select name="staff_id" id="staff_id_select" class="form-select form-select-sm" required>
                                            <option value="">-- Choose Staff Member --</option>
                                            @foreach ($staffMembers as $st)
                                                <option value="{{ $st->id }}"
                                                    data-salary="{{ $st->salary ?? 0 }}"
                                                    data-dept="{{ $st->department->name ?? 'General' }}"
                                                    data-desig="{{ $st->designation->name ?? 'Staff' }}"
                                                    {{ (old('staff_id', $preSelectedStaff) == $st->id) ? 'selected' : '' }}>
                                                    {{ $st->first_name }} {{ $st->last_name }} — {{ $st->designation->name ?? ($st->department->name ?? 'Staff') }} (PKR {{ number_format($st->salary, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fs-13 fw-semibold">Salary Month <span class="text-danger">*</span></label>
                                        <select name="salary_month" id="salary_month_select" class="form-select form-select-sm" required>
                                            @foreach ($months as $m)
                                                <option value="{{ $m }}" {{ old('salary_month', $currentMonth) === $m ? 'selected' : '' }}>
                                                    {{ $m }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fs-13 fw-semibold">Salary Year <span class="text-danger">*</span></label>
                                        <select name="salary_year" id="salary_year_select" class="form-select form-select-sm" required>
                                            @for ($y = date('Y') + 1; $y >= 2023; $y--)
                                                <option value="{{ $y }}" {{ old('salary_year', $currentYear) == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>

                                    <!-- Staff Detail Card (Auto-Filled) -->
                                    <div class="col-md-12">
                                        <div id="staffDetailCard" class="p-2 border rounded bg-light fs-12 d-none">
                                            <div class="row g-2">
                                                <div class="col-sm-4">
                                                    <strong>Department:</strong> <span id="lblStaffDept" class="text-dark">-</span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <strong>Designation:</strong> <span id="lblStaffDesig" class="text-dark">-</span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <strong>Monthly Base Salary:</strong> <span id="lblStaffSalary" class="text-success fw-bold">PKR 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="alreadyGeneratedAlert" class="alert alert-warning py-2 px-3 fs-12 border-0 mt-2 d-none">
                                            <i class="mdi mdi-alert me-1"></i>
                                            <strong>Warning:</strong> Payroll for this staff member has already been generated for this month.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 2: ATTENDANCE & WORKING DAYS (THE CORE FEATURE) -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-calendar-clock text-primary me-1"></i> 2. Attendance & Working Days Calculation
                                </h6>
                                <span class="badge bg-soft-info text-info fs-11">Per-Day Salary Model</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fs-13 fw-semibold">
                                            Total Month Days <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="1" min="1" max="31" name="total_days" id="input_total_days"
                                            value="{{ old('total_days', 30) }}" class="form-control form-control-sm fw-bold" required>
                                        <small class="text-muted fs-11">Calendar days in month</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fs-13 fw-semibold text-danger">
                                            Off / Absent Days <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" step="0.5" min="0" max="31" name="off_days" id="input_off_days"
                                            value="{{ old('off_days', 0) }}" class="form-control form-control-sm fw-bold border-danger" required>
                                        <small class="text-danger fs-11">e.g. 2 off = 2 days unpaid</small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fs-13 fw-semibold text-success">
                                            Actual Working Days
                                        </label>
                                        <input type="number" step="0.5" name="working_days" id="input_working_days"
                                            value="{{ old('working_days', 30) }}" class="form-control form-control-sm fw-bold bg-light text-success border-success" readonly required>
                                        <small class="text-muted fs-11">Total Days - Off Days</small>
                                    </div>
                                </div>

                                <div class="alert alert-light border mt-3 mb-0 p-2 fs-12">
                                    <div class="row text-center g-2">
                                        <div class="col-4 border-end">
                                            <span class="text-muted d-block fs-11">Per Day Salary Rate</span>
                                            <strong class="text-dark fs-13" id="display_per_day">PKR 0.00</strong>
                                            <input type="hidden" name="per_day_salary" id="hidden_per_day_salary" value="{{ old('per_day_salary', 0) }}">
                                        </div>
                                        <div class="col-4 border-end">
                                            <span class="text-danger d-block fs-11">Off / Leave Deduction</span>
                                            <strong class="text-danger fs-13" id="display_absent_deduction">- PKR 0.00</strong>
                                            <input type="hidden" name="absent_deduction" id="hidden_absent_deduction" value="{{ old('absent_deduction', 0) }}">
                                        </div>
                                        <div class="col-4">
                                            <span class="text-success d-block fs-11">Earned Gross Salary</span>
                                            <strong class="text-success fs-13" id="display_earned_salary">PKR 0.00</strong>
                                            <input type="hidden" name="earned_salary" id="hidden_earned_salary" value="{{ old('earned_salary', 0) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 3: ALLOWANCES & BONUSES -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-gift text-success me-1"></i> 3. Additional Allowances & Bonuses
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Allowance / Perks</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">PKR</span>
                                            <input type="number" step="any" min="0" name="allowance" id="input_allowance"
                                                value="{{ old('allowance', 0) }}" class="form-control form-control-sm" placeholder="0.00">
                                        </div>
                                        <small class="text-muted fs-11">Transport, food, or mobile allowance</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Incentive / Bonus</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">PKR</span>
                                            <input type="number" step="any" min="0" name="bonus" id="input_bonus"
                                                value="{{ old('bonus', 0) }}" class="form-control form-control-sm" placeholder="0.00">
                                        </div>
                                        <small class="text-muted fs-11">Overtime or special performance bonus</small>
                                    </div>
                                </div>
                                <input type="hidden" name="gross_salary" id="hidden_gross_salary" value="{{ old('gross_salary', 0) }}">
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: DEDUCTIONS, AUTO-ADVANCE & NET SALARY -->
                    <div class="col-lg-5">
                        
                        <!-- CARD 4: ADVANCE SALARY & DEDUCTIONS -->
                        <div class="card border-0 shadow-sm mb-3 border-top border-warning border-3">
                            <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-cash-minus text-warning me-1"></i> 4. Advance & Deductions
                                </h6>
                                <span class="badge bg-soft-warning text-warning fs-11">Auto-Cut Advance</span>
                            </div>
                            <div class="card-body p-3">
                                
                                <!-- AUTO DETECTED ADVANCE NOTIFICATION BOX -->
                                <div id="advanceDetectedBox" class="alert alert-soft-warning py-2 px-3 fs-12 border-0 mb-3 d-none">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="mdi mdi-lightning-bolt text-warning fs-16"></i>
                                        <strong>Auto-Detected Advance Payment:</strong>
                                    </div>
                                    <div id="advanceRecordsText" class="ms-3 mb-1 text-dark fs-11"></div>
                                    <small class="text-muted ms-3">
                                        <i class="mdi mdi-check-all text-success"></i> Auto-filled and deducted from this month's net payable salary.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-13 fw-bold text-dark">
                                        Advance Salary Deducted <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-soft-warning text-warning fw-bold">PKR</span>
                                        <input type="number" step="any" min="0" name="advance_salary" id="input_advance_salary"
                                            value="{{ old('advance_salary', 0) }}" class="form-control form-control-sm fw-bold text-danger fs-14" placeholder="0.00" required>
                                    </div>
                                    <small class="text-muted fs-11">Automatically fetched from advance payments taken this month</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-13 fw-semibold">Other Deductions / Penalties</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">PKR</span>
                                        <input type="number" step="any" min="0" name="other_deduction" id="input_other_deduction"
                                            value="{{ old('other_deduction', 0) }}" class="form-control form-control-sm" placeholder="0.00">
                                    </div>
                                    <small class="text-muted fs-11">Loan, damage, or late penalty deductions</small>
                                </div>

                                <input type="hidden" name="total_deductions" id="hidden_total_deductions" value="{{ old('total_deductions', 0) }}">
                            </div>
                        </div>

                        <!-- CARD 5: NET SALARY PAYABLE (GRAND SUMMARY) -->
                        <div class="card border-0 shadow-sm mb-3 bg-soft-success border-success border-2">
                            <div class="card-body p-3 text-center">
                                <span class="text-uppercase text-muted fs-11 fw-bold tracking-wide">FINAL NET SALARY PAYABLE</span>
                                <h2 class="fs-26 fw-bolder text-success my-2" id="display_net_salary">PKR 0.00</h2>
                                
                                <div class="d-flex justify-content-between fs-11 text-muted border-top border-success border-opacity-25 pt-2 mt-2">
                                    <span>Basic: <strong id="sum_basic">0.00</strong></span>
                                    <span>Off Cut: <strong id="sum_off" class="text-danger">0.00</strong></span>
                                    <span>Adv Cut: <strong id="sum_adv" class="text-warning">0.00</strong></span>
                                </div>

                                <input type="hidden" name="basic_salary" id="hidden_basic_salary" value="{{ old('basic_salary', 0) }}">
                                <input type="hidden" name="net_salary" id="hidden_net_salary" value="{{ old('net_salary', 0) }}">
                            </div>
                        </div>

                        <!-- CARD 6: DISBURSEMENT & PAYMENT SETTINGS -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom py-2">
                                <h6 class="fs-13 fw-bold text-dark m-0">
                                    <i class="mdi mdi-credit-card-check text-primary me-1"></i> 5. Payment Disbursement
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date" class="form-control form-control-sm"
                                            value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Voucher Ref #</label>
                                        <input type="text" name="payroll_number" class="form-control form-control-sm bg-light"
                                            value="{{ old('payroll_number', $autoPayrollNumber) }}" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fs-13 fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" id="payment_method_select" class="form-select form-select-sm" required>
                                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash in Hand</option>
                                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                            <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online Banking</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 {{ old('payment_method') && old('payment_method') !== 'cash' ? '' : 'd-none' }}" id="bank_select_box">
                                        <label class="form-label fs-13 fw-semibold">Disbursing Bank</label>
                                        <select name="bank_id" class="form-select form-select-sm">
                                            <option value="">-- Select Bank Account --</option>
                                            @foreach ($banks as $b)
                                                <option value="{{ $b->id }}" {{ old('bank_id') == $b->id ? 'selected' : '' }}>
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
                                                    {{ old('status', 'paid') === 'paid' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-13 fw-semibold text-success" for="status_paid">
                                                    <i class="mdi mdi-check-circle me-1"></i> Paid (Disburse Now)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status" id="status_unpaid" value="unpaid"
                                                    {{ old('status') === 'unpaid' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-13 fw-semibold text-danger" for="status_unpaid">
                                                    <i class="mdi mdi-clock-outline me-1"></i> Unpaid / Pending
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-check form-switch bg-light p-2 rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="auto_create_transaction" id="auto_create_transaction" value="1"
                                                {{ old('auto_create_transaction', 1) ? 'checked' : '' }}>
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
                                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                            placeholder="Optional comments / remarks for this payroll">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTONS -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill py-2 fw-bold" id="btnSubmitPayroll">
                                <i class="mdi mdi-content-save-check me-1"></i> Save & Generate Payroll
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
        const staffSelect = document.getElementById('staff_id_select');
        const monthSelect = document.getElementById('salary_month_select');
        const yearSelect = document.getElementById('salary_year_select');

        const totalDaysInput = document.getElementById('input_total_days');
        const offDaysInput = document.getElementById('input_off_days');
        const workingDaysInput = document.getElementById('input_working_days');

        const hiddenBasicSalary = document.getElementById('hidden_basic_salary');
        const hiddenPerDay = document.getElementById('hidden_per_day_salary');
        const hiddenAbsentDed = document.getElementById('hidden_absent_deduction');
        const hiddenEarned = document.getElementById('hidden_earned_salary');
        const hiddenGross = document.getElementById('hidden_gross_salary');
        const hiddenTotalDed = document.getElementById('hidden_total_deductions');
        const hiddenNet = document.getElementById('hidden_net_salary');

        const allowanceInput = document.getElementById('input_allowance');
        const bonusInput = document.getElementById('input_bonus');
        const advanceInput = document.getElementById('input_advance_salary');
        const otherDedInput = document.getElementById('input_other_deduction');

        const displayPerDay = document.getElementById('display_per_day');
        const displayAbsentDed = document.getElementById('display_absent_deduction');
        const displayEarned = document.getElementById('display_earned_salary');
        const displayNet = document.getElementById('display_net_salary');

        const sumBasic = document.getElementById('sum_basic');
        const sumOff = document.getElementById('sum_off');
        const sumAdv = document.getElementById('sum_adv');

        const staffDetailCard = document.getElementById('staffDetailCard');
        const lblStaffDept = document.getElementById('lblStaffDept');
        const lblStaffDesig = document.getElementById('lblStaffDesig');
        const lblStaffSalary = document.getElementById('lblStaffSalary');

        const advanceDetectedBox = document.getElementById('advanceDetectedBox');
        const advanceRecordsText = document.getElementById('advanceRecordsText');
        const alreadyGeneratedAlert = document.getElementById('alreadyGeneratedAlert');

        const payMethodSelect = document.getElementById('payment_method_select');
        const bankSelectBox = document.getElementById('bank_select_box');

        // Toggle bank select dropdown
        payMethodSelect.addEventListener('change', function() {
            if (this.value === 'cash') {
                bankSelectBox.classList.add('d-none');
            } else {
                bankSelectBox.classList.remove('d-none');
            }
        });

        // Live calculation engine
        function calculatePayroll() {
            const basic = parseFloat(hiddenBasicSalary.value) || 0;
            const totalDays = parseFloat(totalDaysInput.value) || 30;
            const offDays = parseFloat(offDaysInput.value) || 0;

            const workingDays = Math.max(0, totalDays - offDays);
            workingDaysInput.value = workingDays;

            // Per Day Salary Rate
            const perDayRate = totalDays > 0 ? (basic / totalDays) : 0;
            hiddenPerDay.value = perDayRate.toFixed(2);
            displayPerDay.textContent = 'PKR ' + perDayRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Off / Leave Deduction
            const absentDeduction = perDayRate * offDays;
            hiddenAbsentDed.value = absentDeduction.toFixed(2);
            displayAbsentDed.textContent = '- PKR ' + absentDeduction.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Earned Salary
            const earned = Math.max(0, basic - absentDeduction);
            hiddenEarned.value = earned.toFixed(2);
            displayEarned.textContent = 'PKR ' + earned.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Allowances & Bonuses
            const allowance = parseFloat(allowanceInput.value) || 0;
            const bonus = parseFloat(bonusInput.value) || 0;
            const gross = earned + allowance + bonus;
            hiddenGross.value = gross.toFixed(2);

            // Deductions
            const advance = parseFloat(advanceInput.value) || 0;
            const otherDed = parseFloat(otherDedInput.value) || 0;
            const totalDed = absentDeduction + advance + otherDed;
            hiddenTotalDed.value = totalDed.toFixed(2);

            // Net Payable Salary
            const netSalary = Math.max(0, gross - advance - otherDed);
            hiddenNet.value = netSalary.toFixed(2);
            displayNet.textContent = 'PKR ' + netSalary.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Bottom quick summary
            sumBasic.textContent = basic.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            sumOff.textContent = absentDeduction.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            sumAdv.textContent = advance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Fetch staff information & auto-detect advance taken
        function fetchStaffSalaryInfo() {
            const staffId = staffSelect.value;
            const month = monthSelect.value;
            const year = yearSelect.value;

            if (!staffId) {
                staffDetailCard.classList.add('d-none');
                advanceDetectedBox.classList.add('d-none');
                alreadyGeneratedAlert.classList.add('d-none');
                hiddenBasicSalary.value = 0;
                calculatePayroll();
                return;
            }

            fetch(`{{ route('payroll.staff-info') }}?staff_id=${staffId}&month=${month}&year=${year}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        lblStaffDept.textContent = data.staff.department;
                        lblStaffDesig.textContent = data.staff.designation;
                        lblStaffSalary.textContent = 'PKR ' + data.staff.basic_salary.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        staffDetailCard.classList.remove('d-none');

                        hiddenBasicSalary.value = data.staff.basic_salary;
                        totalDaysInput.value = data.days_in_month;

                        // Auto-fill advance salary if detected
                        if (data.advance_taken > 0) {
                            advanceInput.value = data.advance_taken.toFixed(2);
                            let recHtml = `Found <strong>PKR ${data.advance_taken.toLocaleString()}</strong> in ${data.advance_list.length} transaction(s):<ul class="mb-0 ps-3">`;
                            data.advance_list.forEach(r => {
                                recHtml += `<li>${r.date}: PKR ${r.amount.toLocaleString()} (${r.description})</li>`;
                            });
                            recHtml += '</ul>';
                            advanceRecordsText.innerHTML = recHtml;
                            advanceDetectedBox.classList.remove('d-none');
                        } else {
                            advanceInput.value = 0;
                            advanceDetectedBox.classList.add('d-none');
                        }

                        // Duplicate warning
                        if (data.already_generated) {
                            alreadyGeneratedAlert.classList.remove('d-none');
                        } else {
                            alreadyGeneratedAlert.classList.add('d-none');
                        }

                        calculatePayroll();
                    }
                })
                .catch(err => {
                    console.error('Failed to fetch staff salary info:', err);
                });
        }

        staffSelect.addEventListener('change', fetchStaffSalaryInfo);
        monthSelect.addEventListener('change', fetchStaffSalaryInfo);
        yearSelect.addEventListener('change', fetchStaffSalaryInfo);

        // Bind input events to live calculate
        [totalDaysInput, offDaysInput, allowanceInput, bonusInput, advanceInput, otherDedInput].forEach(inp => {
            inp.addEventListener('input', calculatePayroll);
        });

        // Trigger on initial page load if staff already selected
        if (staffSelect.value) {
            fetchStaffSalaryInfo();
        }
    });
</script>
@endpush
