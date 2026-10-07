@extends('layout.master')

@section('title', 'Edit Transaction')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .section-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
    }
    .form-check-input:checked {
        background-color: #0f535e;
        border-color: #0f535e;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE TITLE -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-0 text-dark">
                        <i class="mdi mdi-pencil-box-multiple text-primary me-1"></i> Edit Transaction
                    </h4>
                    <p class="text-muted fs-13 mb-0">Update transaction details, payee type, and payment record.</p>
                </div>
                <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Transactions
                </a>
            </div>

            <!-- ALERTS -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show fs-13" role="alert">
                    <strong><i class="mdi mdi-alert-circle me-1"></i> Please check the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <form action="{{ route('expense.transaction.update', $transaction->id) }}" method="POST" enctype="multipart/form-data" id="expenseForm">
                        @csrf
                        @method('PUT')

                        <!-- ROW 1: Date, Season, Payment Type -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="date" id="trans_date"
                                        value="{{ old('date', $transaction->date ? date('Y-m-d', strtotime($transaction->date)) : date('Y-m-d')) }}"
                                        class="form-control form-control-sm flatpickr-date" placeholder="YYYY-MM-DD" required>
                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Hajj / Umrah Season <span class="text-danger">*</span></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <select name="hajj_umrah" id="hajj_umrah_select" class="form-select form-select-sm" required>
                                            <option value="hajj" {{ old('hajj_umrah', $transaction->hajj_umrah) == 'hajj' ? 'selected' : '' }}>Hajj</option>
                                            <option value="umrah" {{ old('hajj_umrah', $transaction->hajj_umrah) == 'umrah' ? 'selected' : '' }}>Umrah</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="year" id="year_input"
                                            value="{{ old('year', $transaction->year) }}"
                                            class="form-control form-control-sm" placeholder="Year" min="2000" max="2100" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Payment Type <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 pt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_bank" value="bank"
                                            {{ old('payment_type', $transaction->payment_type) == 'bank' || old('payment_type', $transaction->payment_type) == 'accounts' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_bank">Bank Transfer</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_cash" value="cash"
                                            {{ old('payment_type', $transaction->payment_type) == 'cash' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_cash">Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_cheque" value="cheque"
                                            {{ old('payment_type', $transaction->payment_type) == 'cheque' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_cheque">Cheque</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_online" value="online"
                                            {{ old('payment_type', $transaction->payment_type) == 'online' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_online">Online</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ROW 2: Bank, Currency, Exchange Rate -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fs-13 fw-semibold">Bank</label>
                                <select name="bank_id" class="form-select form-select-sm">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id }}" {{ old('bank_id', $transaction->bank_id) == $b->id ? 'selected' : '' }}>
                                            {{ $b->name }}{{ !empty($b->account_name) ? ' - ' . $b->account_name : '' }}{{ !empty($b->account_number) && $b->account_number !== '0' ? ' (' . $b->account_number . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-13 fw-semibold">Currency <span class="text-danger">*</span></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <select name="currency" id="currency_select" class="form-select form-select-sm" required>
                                            <option value="PKR" {{ old('currency', $transaction->currency) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                            <option value="SAR" {{ old('currency', $transaction->currency) == 'SAR' ? 'selected' : '' }}>SAR</option>
                                            <option value="USD" {{ old('currency', $transaction->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate_input"
                                            value="{{ old('exchange_rate', $transaction->exchange_rate ?? 1) }}"
                                            class="form-control form-control-sm {{ ($transaction->currency ?? 'PKR') == 'PKR' ? 'bg-light' : '' }}"
                                            placeholder="Ex. Rate" {{ ($transaction->currency ?? 'PKR') == 'PKR' ? 'readonly' : '' }} required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ROW 3: Total Amount, Transaction Type, Reference -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text currency-symbol">{{ $transaction->currency ?? 'PKR' }}</span>
                                    <input type="number" step="any" name="amount" id="main_total_amount"
                                        value="{{ old('amount', $transaction->amount) }}"
                                        class="form-control form-control-sm fw-bold text-dark" placeholder="Enter Total Amount" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Transaction Type</label>
                                <select name="transaction_type" class="form-select form-select-sm">
                                    <option value="debit" {{ old('transaction_type', $transaction->transaction_type) == 'debit' ? 'selected' : '' }}>Debit (Payment Out / Expense / Supplier Pay)</option>
                                    <option value="credit" {{ old('transaction_type', $transaction->transaction_type) == 'credit' ? 'selected' : '' }}>Credit (Payment In / Client Receipt / Income)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Reference</label>
                                <input type="text" name="reference_no"
                                    value="{{ old('reference_no', $transaction->reference_no) }}"
                                    class="form-control form-control-sm" placeholder="DT-EXP-000001">
                            </div>
                        </div>

                        <!-- ROW 4: Proof of Payment -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fs-13 fw-semibold">Proof of Payment</label>
                                <div class="d-flex gap-3 align-items-center">
                                    <input type="file" name="proof" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                                    @if ($transaction->proof)
                                        <a href="{{ asset('assets/images/expense_proofs/' . $transaction->proof) }}"
                                            target="_blank" class="btn btn-sm btn-outline-info text-nowrap">
                                            <i class="mdi mdi-eye me-1"></i> View Current Proof
                                        </a>
                                    @endif
                                </div>
                                <small class="text-muted fs-11">Supported formats: JPG, PNG, PDF (Max 5MB)</small>
                            </div>
                        </div>

                        <!-- ROW 5: Description -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fs-13 fw-semibold">Description / Notes</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Reason for Payment / Notes">{{ old('description', $transaction->description) }}</textarea>
                            </div>
                        </div>

                        <!-- PAYEE / CATEGORY SELECTOR -->
                        @php
                            $currentPayee = old('payee_type', $transaction->payee_type ?? ($transaction->supplier_id ? 'supplier' : ($transaction->client_id ? 'client' : ($transaction->staff_id ? 'staff' : ($transaction->company_id ? 'company' : ($transaction->expense_id ? 'expense' : 'supplier'))))));
                        @endphp
                        <div class="card mb-4 border bg-light">
                            <div class="card-header bg-white border-bottom py-2">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <label class="form-label fs-13 fw-bold mb-0 text-dark">
                                        <i class="mdi mdi-account-switch text-primary me-1"></i> Transaction Category / Payee
                                    </label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                                id="payee_supplier" value="supplier" {{ $currentPayee == 'supplier' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-13 fw-semibold text-primary" for="payee_supplier">
                                                <i class="mdi mdi-truck-delivery me-1"></i> Supplier
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                                id="payee_client" value="client" {{ $currentPayee == 'client' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-13 fw-semibold text-info" for="payee_client">
                                                <i class="mdi mdi-account me-1"></i> Client
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                                id="payee_staff" value="staff" {{ $currentPayee == 'staff' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-13 fw-semibold text-success" for="payee_staff">
                                                <i class="mdi mdi-account-tie me-1"></i> Staff Member
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                                id="payee_expense" value="expense" {{ $currentPayee == 'expense' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-13 fw-semibold text-secondary" for="payee_expense">
                                                <i class="mdi mdi-office-building me-1"></i> Office Expense
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                                id="payee_self" value="self" {{ $currentPayee == 'self' ? 'checked' : '' }}>
                                            <label class="form-check-label fs-13 fw-semibold text-dark" for="payee_self">
                                                <i class="mdi mdi-account-circle me-1"></i> Self / Transfer
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-3">

                                <!-- 1. SUPPLIER SECTION -->
                                <div id="supplierSection" class="{{ $currentPayee == 'supplier' ? '' : 'd-none' }}">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Supplier <span class="text-danger">*</span></label>
                                            <select name="supplier_id" id="supplier_id_select" class="form-select form-select-sm">
                                                <option value="">-- Select Supplier --</option>
                                                @foreach ($suppliers as $s)
                                                    <option value="{{ $s->id }}"
                                                        data-name="{{ $s->name }}"
                                                        data-company="{{ $s->company_name }}"
                                                        data-type="{{ $s->service_type ?? 'Supplier' }}"
                                                        data-phone="{{ $s->phone ?? 'N/A' }}"
                                                        data-payable="{{ number_format($s->total_payable, 2) }}"
                                                        data-paid="{{ number_format($s->total_paid, 2) }}"
                                                        data-balance="{{ number_format($s->balance_due, 2) }}"
                                                        {{ old('supplier_id', $transaction->supplier_id) == $s->id ? 'selected' : '' }}>
                                                        {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }} - {{ strtoupper($s->service_type ?? 'Supplier') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="supplierDetailCard" class="p-2 border rounded bg-white fs-12 d-none shadow-sm">
                                                <div class="d-flex justify-content-between">
                                                    <span><strong>Supplier:</strong> <span id="supName" class="text-dark">-</span></span>
                                                    <span><strong>Service:</strong> <span id="supType" class="badge bg-soft-primary text-primary">-</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <span><strong>Total Payable:</strong> <span id="supPayable" class="text-danger fw-bold">-</span></span>
                                                    <span><strong>Total Paid:</strong> <span id="supPaid" class="text-success fw-bold">-</span></span>
                                                    <span><strong>Balance Due:</strong> <span id="supBalance" class="text-primary fw-bold">-</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. CLIENT SECTION -->
                                <div id="clientSection" class="{{ $currentPayee == 'client' ? '' : 'd-none' }}">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Client <span class="text-danger">*</span></label>
                                            <select name="client_id" id="client_id_select" class="form-select form-select-sm">
                                                <option value="">-- Select Client --</option>
                                                @foreach ($clients as $c)
                                                    <option value="{{ $c->id }}"
                                                        data-name="{{ $c->name }}"
                                                        data-phone="{{ $c->phone ?? 'N/A' }}"
                                                        data-cnic="{{ $c->cnic ?? 'N/A' }}"
                                                        data-company="{{ $c->company_name ?? 'N/A' }}"
                                                        {{ old('client_id', $transaction->client_id) == $c->id ? 'selected' : '' }}>
                                                        {{ $c->name }}{{ $c->company_name ? ' (' . $c->company_name . ')' : '' }} - {{ $c->phone ?? $c->cnic }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="clientDetailCard" class="p-2 border rounded bg-white fs-12 d-none shadow-sm">
                                                <div class="d-flex justify-content-between">
                                                    <span><strong>Client:</strong> <span id="clName" class="text-dark">-</span></span>
                                                    <span><strong>Phone:</strong> <span id="clPhone" class="text-dark">-</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <span><strong>CNIC:</strong> <span id="clCnic" class="text-dark">-</span></span>
                                                    <span><strong>Company:</strong> <span id="clCompany" class="text-muted">-</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. STAFF PAYMENT SECTION -->
                                <div id="staffSection" class="{{ $currentPayee == 'staff' ? '' : 'd-none' }}">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Staff Member <span class="text-danger">*</span></label>
                                            <select name="staff_id" id="staff_id_select" class="form-select form-select-sm">
                                                <option value="">-- Select Staff Member --</option>
                                                @foreach ($staffMembers as $st)
                                                    <option value="{{ $st->id }}"
                                                        data-name="{{ $st->first_name }} {{ $st->last_name }}"
                                                        data-dept="{{ $st->department->name ?? 'N/A' }}"
                                                        data-desig="{{ $st->designation->name ?? 'N/A' }}"
                                                        data-salary="{{ $st->salary ?? 0 }}"
                                                        data-phone="{{ $st->phone ?? 'N/A' }}"
                                                        {{ old('staff_id', $transaction->staff_id) == $st->id ? 'selected' : '' }}>
                                                        {{ $st->first_name }} {{ $st->last_name }}{{ $st->designation ? ' (' . $st->designation->name . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="staffDetailCard" class="p-2 border rounded bg-white fs-12 d-none shadow-sm">
                                                <div class="d-flex justify-content-between">
                                                    <span><strong>Department:</strong> <span id="staffDept" class="text-dark">-</span></span>
                                                    <span><strong>Designation:</strong> <span id="staffDesig" class="text-dark">-</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <span><strong>Phone:</strong> <span id="staffPhone" class="text-dark">-</span></span>
                                                    <span><strong>Base Salary:</strong> <span id="staffSalary" class="text-success fw-bold">-</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Staff Payment Options Box -->
                                    <div id="staffPaymentControls" class="p-3 border rounded bg-white d-none mt-2">
                                        <h6 class="fs-13 fw-bold text-primary mb-2">
                                            <i class="mdi mdi-cash-check me-1"></i> Staff Quick Fill
                                        </h6>
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-6">
                                                <label class="form-label fs-12 fw-semibold mb-1">For Salary Month / Period</label>
                                                <div class="row g-1">
                                                    <div class="col-7">
                                                        <select id="staff_pay_month" class="form-select form-select-sm">
                                                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                                                                <option value="{{ $m }}" {{ date('F') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="number" id="staff_pay_year" class="form-control form-select-sm" value="{{ date('Y') }}" min="2000" max="2100">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fs-12 fw-semibold mb-1">Quick Pay Amount</label>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-outline-success btn-sm flex-fill" id="btnFillFullSalary" title="Auto-fill monthly base salary">
                                                        <i class="mdi mdi-cash-multiple me-1"></i> Full Salary
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning btn-sm flex-fill" id="btnFillHalfSalary" title="Auto-fill 50% advance salary">
                                                        <i class="mdi mdi-lightning-bolt me-1"></i> 50% Adv
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- 5. EXPENSE HEAD SECTION -->
                                <div id="expenseHeadSection" class="{{ $currentPayee == 'expense' ? '' : 'd-none' }}">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Expense Head <span class="text-danger">*</span></label>
                                            <select name="expense_id" id="expense_head_select" class="form-select form-select-sm">
                                                <option value="">-- Select Expense Head --</option>
                                                @foreach ($expenses as $expense)
                                                    <option value="{{ $expense->id }}" {{ old('expense_id', $transaction->expense_id) == $expense->id ? 'selected' : '' }}>
                                                        {{ $expense->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. SELF SECTION -->
                                <div id="selfSection" class="{{ $currentPayee == 'self' ? '' : 'd-none' }}">
                                    <p class="text-muted fs-13 mb-0">
                                        <i class="mdi mdi-information-outline me-1"></i>
                                        Self / internal transaction or transfer between business funds.
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- SUBMIT BUTTONS -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="mdi mdi-content-save me-1"></i> Update Transaction
                            </button>
                            <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary px-4">
                                Back
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".flatpickr-date", { dateFormat: "Y-m-d", allowInput: true });

        // Currency Toggle
        const currencySelect = document.getElementById('currency_select');
        const rateInput = document.getElementById('exchange_rate_input');
        const currencySymbols = document.querySelectorAll('.currency-symbol');

        function updateCurrency() {
            const curr = currencySelect.value;
            currencySymbols.forEach(el => el.textContent = curr);

            if (curr === 'PKR') {
                rateInput.value = 1;
                rateInput.setAttribute('readonly', true);
                rateInput.classList.add('bg-light');
            } else {
                rateInput.removeAttribute('readonly');
                rateInput.classList.remove('bg-light');
                if (rateInput.value == 1) {
                    rateInput.value = '';
                }
            }
        }
        currencySelect.addEventListener('change', updateCurrency);

        // Payee Type Toggle
        const payeeRadios = document.querySelectorAll('.payee-type-radio');
        const supplierSec = document.getElementById('supplierSection');
        const clientSec = document.getElementById('clientSection');
        const staffSec = document.getElementById('staffSection');
        const expenseSec = document.getElementById('expenseHeadSection');
        const selfSec = document.getElementById('selfSection');

        payeeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (supplierSec) supplierSec.classList.add('d-none');
                if (clientSec) clientSec.classList.add('d-none');
                if (staffSec) staffSec.classList.add('d-none');
                if (expenseSec) expenseSec.classList.add('d-none');
                if (selfSec) selfSec.classList.add('d-none');

                if (this.value === 'supplier' && supplierSec) {
                    supplierSec.classList.remove('d-none');
                } else if (this.value === 'client' && clientSec) {
                    clientSec.classList.remove('d-none');
                } else if (this.value === 'staff' && staffSec) {
                    staffSec.classList.remove('d-none');
                } else if (this.value === 'expense' && expenseSec) {
                    expenseSec.classList.remove('d-none');
                } else if (this.value === 'self' && selfSec) {
                    selfSec.classList.remove('d-none');
                }
            });
        });

        // Supplier Detail Preview
        const supplierSelect = document.getElementById('supplier_id_select');
        const supplierCard = document.getElementById('supplierDetailCard');
        function updateSupplierCard() {
            if (!supplierSelect) return;
            const opt = supplierSelect.options[supplierSelect.selectedIndex];
            if (supplierSelect.value && opt) {
                document.getElementById('supName').textContent = opt.getAttribute('data-name') || '-';
                document.getElementById('supType').textContent = opt.getAttribute('data-type') || '-';
                document.getElementById('supPayable').textContent = opt.getAttribute('data-payable') || '0.00';
                document.getElementById('supPaid').textContent = opt.getAttribute('data-paid') || '0.00';
                document.getElementById('supBalance').textContent = opt.getAttribute('data-balance') || '0.00';
                supplierCard.classList.remove('d-none');
            } else {
                supplierCard.classList.add('d-none');
            }
        }
        if (supplierSelect) {
            supplierSelect.addEventListener('change', updateSupplierCard);
            updateSupplierCard();
        }

        // Client Detail Preview
        const clientSelect = document.getElementById('client_id_select');
        const clientCard = document.getElementById('clientDetailCard');
        function updateClientCard() {
            if (!clientSelect) return;
            const opt = clientSelect.options[clientSelect.selectedIndex];
            if (clientSelect.value && opt) {
                document.getElementById('clName').textContent = opt.getAttribute('data-name') || '-';
                document.getElementById('clPhone').textContent = opt.getAttribute('data-phone') || '-';
                document.getElementById('clCnic').textContent = opt.getAttribute('data-cnic') || '-';
                document.getElementById('clCompany').textContent = opt.getAttribute('data-company') || '-';
                clientCard.classList.remove('d-none');
            } else {
                clientCard.classList.add('d-none');
            }
        }
        if (clientSelect) {
            clientSelect.addEventListener('change', updateClientCard);
            updateClientCard();
        }

        // Staff Detail Preview & Payment Controls
        const staffSelect = document.getElementById('staff_id_select');
        const staffCard = document.getElementById('staffDetailCard');
        const staffControls = document.getElementById('staffPaymentControls');

        function updateStaffCard() {
            if (!staffSelect) return;
            const opt = staffSelect.options[staffSelect.selectedIndex];
            if (staffSelect.value && opt) {
                document.getElementById('staffDept').textContent = opt.getAttribute('data-dept') || 'N/A';
                document.getElementById('staffDesig').textContent = opt.getAttribute('data-desig') || 'N/A';
                document.getElementById('staffPhone').textContent = opt.getAttribute('data-phone') || 'N/A';
                const salary = parseFloat(opt.getAttribute('data-salary') || 0);
                document.getElementById('staffSalary').textContent = salary.toLocaleString('en-US', { minimumFractionDigits: 2 });

                staffCard.classList.remove('d-none');
                staffControls.classList.remove('d-none');
            } else {
                staffCard.classList.add('d-none');
                staffControls.classList.add('d-none');
            }
        }
        if (staffSelect) {
            staffSelect.addEventListener('change', updateStaffCard);
            updateStaffCard();
        }

        // Quick Salary Buttons
        const mainAmount = document.getElementById('main_total_amount');
        document.getElementById('btnFillFullSalary')?.addEventListener('click', function() {
            const opt = staffSelect.options[staffSelect.selectedIndex];
            if (opt && opt.getAttribute('data-salary')) {
                mainAmount.value = opt.getAttribute('data-salary');
            }
        });

        document.getElementById('btnFillHalfSalary')?.addEventListener('click', function() {
            const opt = staffSelect.options[staffSelect.selectedIndex];
            if (opt && opt.getAttribute('data-salary')) {
                const half = parseFloat(opt.getAttribute('data-salary')) / 2;
                mainAmount.value = half.toFixed(2);
            }
        });
    });
</script>
@endsection
