<?php
$content = <<<'BLADE'
@extends('layout.master')

@section('title', 'Edit Transaction')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

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
    .payee-card-active {
        border-color: #0f535e !important;
        box-shadow: 0 4px 12px rgba(15, 83, 94, 0.1) !important;
    }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 31px;
        padding: 2px 6px;
        font-size: 13px;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE TITLE -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-0 text-dark">
                        <i class="mdi mdi-pencil-box-multiple text-primary me-1"></i> Edit Transaction #{{ $transaction->id }}
                    </h4>
                    <p class="text-muted fs-13 mb-0">Update transaction details, payee category, voucher type, and currency conversion.</p>
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

            @php
                $isLoan = $transaction->account_id && in_array($transaction->account?->account_type, ['loan_asset', 'loan_liability']);
                $currentPayee = old('payee_type', $transaction->payee_type ?? ($isLoan ? 'loan' : ($transaction->supplier_id ? 'supplier' : ($transaction->client_id ? 'client' : ($transaction->staff_id ? 'staff' : ($transaction->expense_id ? 'expense' : 'supplier'))))));
                if ($isLoan && !old('payee_type')) {
                    $currentPayee = 'loan';
                }
            @endphp

            <form action="{{ route('expense.transaction.update', $transaction->id) }}" method="POST" enctype="multipart/form-data" id="expenseForm">
                @csrf
                @method('PUT')

                <!-- STEP 1: TRANSACTION CATEGORY / PAYEE SELECTOR (AT TOP) -->
                <div class="card mb-3 border border-primary border-opacity-25 shadow-sm payee-card-active">
                    <div class="card-header bg-white border-bottom py-2">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <label class="form-label fs-14 fw-bold mb-0 text-dark">
                                <i class="mdi mdi-account-switch text-primary me-1"></i> Step 1: Transaction Category / Payee <span class="text-danger">*</span>
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
                                        id="payee_jv" value="jv" {{ $currentPayee == 'jv' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold" style="color: #7c3aed;" for="payee_jv">
                                        <i class="mdi mdi-book-open-page-variant me-1"></i> JV Voucher
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                        id="payee_loan" value="loan" {{ $currentPayee == 'loan' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold text-warning" for="payee_loan">
                                        <i class="mdi mdi-hand-coin me-1"></i> Loan (Asset / Liability)
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

                    <div class="card-body p-3 bg-white">

                        <!-- 1. SUPPLIER SECTION -->
                        <div id="supplierSection" class="{{ $currentPayee == 'supplier' ? '' : 'd-none' }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Select Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" id="supplier_id_select" class="form-select form-select-sm select2">
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
                                    <select name="client_id" id="client_id_select" class="form-select form-select-sm select2">
                                        <option value="">-- Select Client --</option>
                                        @foreach ($clients as $c)
                                            <option value="{{ $c->id }}"
                                                data-name="{{ $c->name }}"
                                                data-phone="{{ $c->phone ?? 'N/A' }}"
                                                data-package="{{ $c->package_name ?? 'Umrah / Hajj' }}"
                                                data-balance="{{ number_format($c->total_balance, 2) }}"
                                                {{ old('client_id', $transaction->client_id) == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }} {{ $c->company_name ? ' (' . $c->company_name . ')' : '' }} - {{ $c->phone ?? $c->cnic }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div id="clientDetailCard" class="p-2 border rounded bg-white fs-12 d-none shadow-sm">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Client:</strong> <span id="cliName" class="text-dark">-</span></span>
                                            <span><strong>Phone:</strong> <span id="cliPhone" class="text-dark">-</span></span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <span><strong>Package:</strong> <span id="cliPackage" class="badge bg-soft-info text-info">-</span></span>
                                            <span><strong>Balance:</strong> <span id="cliBalance" class="text-danger fw-bold">-</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. STAFF PAYMENT SECTION -->
                        <div id="staffSection" class="{{ $currentPayee == 'staff' ? '' : 'd-none' }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">Select Staff Member <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staff_id_select" class="form-select form-select-sm select2">
                                        <option value="">-- Select Staff Member --</option>
                                        @foreach ($staffMembers as $st)
                                            <option value="{{ $st->id }}"
                                                data-name="{{ $st->first_name }} {{ $st->last_name }}"
                                                data-designation="{{ $st->designation->name ?? 'Staff' }}"
                                                data-salary="{{ number_format($st->salary, 2) }}"
                                                data-advance="{{ number_format($st->advance_salary ?? 0, 2) }}"
                                                {{ old('staff_id', $transaction->staff_id) == $st->id ? 'selected' : '' }}>
                                                {{ $st->first_name }} {{ $st->last_name }} ({{ $st->designation->name ?? 'Staff' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">Salary Month / Year</label>
                                    <div class="row g-1">
                                        <div class="col-7">
                                            <select name="salary_month" class="form-select form-select-sm">
                                                @foreach (['01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December'] as $k => $m)
                                                    <option value="{{ $k }}" {{ old('salary_month', date('m')) == $k ? 'selected' : '' }}>{{ $m }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-5">
                                            <input type="number" name="salary_year" value="{{ old('salary_year', date('Y')) }}" class="form-control form-control-sm" min="2000" max="2100">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">Disbursement Type</label>
                                    <select name="staff_payment_type" class="form-select form-select-sm">
                                        <option value="salary">Monthly Salary Disbursement</option>
                                        <option value="advance">Advance Salary / Loan</option>
                                        <option value="commission">Commission / Bonus</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="card border mb-0 d-none" id="staffDetailCard">
                                        <div class="card-body p-2 fs-12">
                                            <div class="row g-1">
                                                <div class="col-3"><strong>Staff:</strong> <span id="stName">-</span></div>
                                                <div class="col-3"><strong>Designation:</strong> <span id="stDesignation">-</span></div>
                                                <div class="col-3 text-success"><strong>Basic Salary:</strong> <span id="stSalary">0.00</span></div>
                                                <div class="col-3 text-warning"><strong>Advance:</strong> <span id="stAdvance">0.00</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. EXPENSE HEAD SECTION -->
                        <div id="expenseHeadSection" class="{{ $currentPayee == 'expense' ? '' : 'd-none' }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Office Expense Head <span class="text-danger">*</span></label>
                                    <select name="expense_id" class="form-select form-select-sm select2">
                                        <option value="">-- Select Expense Category --</option>
                                        @foreach ($expenses as $e)
                                            <option value="{{ $e->id }}" {{ old('expense_id', $transaction->expense_id) == $e->id ? 'selected' : '' }}>
                                                {{ $e->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 5. LOAN (ASSET / LIABILITY) SECTION -->
                        <div id="loanSection" class="{{ $currentPayee == 'loan' ? '' : 'd-none' }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Select Loan Account <span class="text-danger">*</span></label>
                                    <select name="loan_account_id" id="loan_account_id_select" class="form-select form-select-sm select2">
                                        <option value="">-- Choose Loan Account --</option>
                                        @foreach ($cashAccounts->filter(fn($a) => in_array($a->account_type, ['loan_asset', 'loan_liability'])) as $la)
                                            <option value="{{ $la->id }}" {{ (old('loan_account_id', $transaction->account_id) == $la->id) ? 'selected' : '' }}>
                                                {{ $la->account_name }} ({{ $la->account_type === 'loan_asset' ? 'Loan Asset / Receivable' : 'Loan Liability / Payable' }}) — Opening: PKR {{ number_format($la->opening_balance_pkr, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('account.create') }}" target="_blank" class="btn btn-outline-warning btn-sm">
                                        <i class="mdi mdi-plus-circle me-1"></i> + Create New Loan Account
                                    </a>
                                    <small class="text-muted d-block mt-1">Track loans given out (Assets) or loans borrowed (Liabilities).</small>
                                </div>
                            </div>
                        </div>

                        <!-- 6. SELF / TRANSFER SECTION -->
                        <div id="selfSection" class="{{ $currentPayee == 'self' ? '' : 'd-none' }}">
                            <div class="alert alert-secondary py-2 px-3 mb-0 fs-13">
                                <i class="mdi mdi-information-outline me-1"></i>
                                <strong>Internal Transfer:</strong> Moving funds between company bank accounts or petty cash accounts.
                            </div>
                        </div>

                        <!-- 7. JV VOUCHER SECTION (ALL JV OPTIONS & SEARCHABLE DROPDOWNS) -->
                        <div id="jvSection" class="{{ $currentPayee == 'jv' ? '' : 'd-none' }}">
                            <div class="p-3 bg-white border rounded shadow-sm">
                                
                                <!-- JV Mode Selector Tabs / Radios -->
                                <div class="mb-3">
                                    <label class="form-label fs-12 fw-bold text-dark text-uppercase mb-2">
                                        <i class="mdi mdi-format-list-bulleted-type text-primary me-1"></i> Select JV Voucher Mode:
                                    </label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <input type="radio" class="btn-check jv-mode-radio" name="jv_mode" id="jv_mode_univ" value="universal" {{ old('jv_mode', 'universal') === 'universal' ? 'checked' : '' }}>
                                        <label class="btn btn-sm btn-outline-success" for="jv_mode_univ">
                                            <i class="mdi mdi-arrow-left-right-bold me-1"></i> 🌐 Universal JV (From A/C ➔ To A/C)
                                        </label>

                                        <input type="radio" class="btn-check jv-mode-radio" name="jv_mode" id="jv_mode_sp" value="single_party" {{ old('jv_mode') === 'single_party' ? 'checked' : '' }}>
                                        <label class="btn btn-sm btn-outline-info" for="jv_mode_sp">
                                            <i class="mdi mdi-account-circle me-1"></i> 👤 Single Party JV Adjustment
                                        </label>

                                        <input type="radio" class="btn-check jv-mode-radio" name="jv_mode" id="jv_mode_cs" value="client_supplier" {{ old('jv_mode') === 'client_supplier' ? 'checked' : '' }}>
                                        <label class="btn btn-sm btn-outline-primary" for="jv_mode_cs">
                                            <i class="mdi mdi-account-switch me-1"></i> ⇄ Client & Supplier Contra
                                        </label>

                                        <input type="radio" class="btn-check jv-mode-radio" name="jv_mode" id="jv_mode_mc" value="multi_client" {{ old('jv_mode') === 'multi_client' ? 'checked' : '' }}>
                                        <label class="btn btn-sm btn-outline-purple" style="color: #7c3aed; border-color: #7c3aed;" for="jv_mode_mc">
                                            <i class="mdi mdi-account-multiple me-1"></i> 👥 Multi-Client Adjustment
                                        </label>
                                    </div>
                                </div>

                                <!-- MODE A: UNIVERSAL SEARCHABLE TRANSFER (FROM ACCOUNT ➔ TO ACCOUNT) -->
                                <div id="jv_box_universal" class="jv-box {{ old('jv_mode', 'universal') === 'universal' ? '' : 'd-none' }}">
                                    <div class="alert alert-success py-2 px-3 mb-3 fs-12 border-0 bg-soft-success text-success">
                                        <i class="mdi mdi-check-circle me-1"></i>
                                        <strong>Universal JV Transfer:</strong> Both dropdowns below include <strong>ALL Clients, Suppliers, Expense Heads, Banks, Staff Members, Cash & Loan Accounts</strong>. Easily search and transfer funds or balances between any two entities!
                                    </div>

                                    <div class="row g-3">
                                        <!-- FROM ACCOUNT -->
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-bold text-danger">
                                                <i class="mdi mdi-arrow-up-circle me-1"></i> From Account (Source / Debit Party)
                                            </label>
                                            <select name="jv_from_account" id="jv_from_account_select" class="form-select form-select-sm select2">
                                                <option value="">-- Search & Select Source Account --</option>
                                                
                                                <optgroup label="👤 Clients">
                                                    @foreach ($clients as $c)
                                                        <option value="client:{{ $c->id }}" {{ old('jv_from_account') == "client:{$c->id}" || ($transaction->client_id == $c->id && $transaction->transaction_type === 'debit') ? 'selected' : '' }}>
                                                            Client: {{ $c->name }} ({{ $c->phone ?? 'Client' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🚚 Suppliers">
                                                    @foreach ($suppliers as $s)
                                                        <option value="supplier:{{ $s->id }}" {{ old('jv_from_account') == "supplier:{$s->id}" || ($transaction->supplier_id == $s->id && $transaction->transaction_type === 'debit') ? 'selected' : '' }}>
                                                            Supplier: {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }} [{{ strtoupper($s->service_type ?? 'Supplier') }}]
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="👔 Staff Members">
                                                    @foreach ($staffMembers as $st)
                                                        <option value="staff:{{ $st->id }}" {{ old('jv_from_account') == "staff:{$st->id}" || ($transaction->staff_id == $st->id && $transaction->transaction_type === 'debit') ? 'selected' : '' }}>
                                                            Staff: {{ $st->first_name }} {{ $st->last_name }} ({{ $st->designation->name ?? 'Staff' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏦 Bank Accounts">
                                                    @foreach ($banks as $b)
                                                        <option value="bank:{{ $b->id }}" {{ old('jv_from_account') == "bank:{$b->id}" || ($transaction->bank_id == $b->id && $transaction->transaction_type === 'debit') ? 'selected' : '' }}>
                                                            Bank: {{ $b->name }}{{ $b->account_number ? ' (' . $b->account_number . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏢 Expense Heads">
                                                    @foreach ($expenses as $e)
                                                        <option value="expense:{{ $e->id }}" {{ old('jv_from_account') == "expense:{$e->id}" || ($transaction->expense_id == $e->id && $transaction->transaction_type === 'debit') ? 'selected' : '' }}>
                                                            Expense: {{ $e->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="💵 Cash & Loan Accounts">
                                                    @foreach ($cashAccounts as $ca)
                                                        <option value="account:{{ $ca->id }}" {{ old('jv_from_account') == "account:{$ca->id}" || ($transaction->account_id == $ca->id && $transaction->transaction_type === 'debit') ? 'selected' : '' }}>
                                                            {{ $ca->account_name }} ({{ strtoupper(str_replace('_', ' ', $ca->account_type)) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </div>

                                        <!-- TO ACCOUNT -->
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-bold text-success">
                                                <i class="mdi mdi-arrow-down-circle me-1"></i> To Account (Destination / Credit Party)
                                            </label>
                                            <select name="jv_to_account" id="jv_to_account_select" class="form-select form-select-sm select2">
                                                <option value="">-- Search & Select Destination Account --</option>
                                                
                                                <optgroup label="👤 Clients">
                                                    @foreach ($clients as $c)
                                                        <option value="client:{{ $c->id }}" {{ old('jv_to_account') == "client:{$c->id}" || ($transaction->client_id == $c->id && $transaction->transaction_type === 'credit') ? 'selected' : '' }}>
                                                            Client: {{ $c->name }} ({{ $c->phone ?? 'Client' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🚚 Suppliers">
                                                    @foreach ($suppliers as $s)
                                                        <option value="supplier:{{ $s->id }}" {{ old('jv_to_account') == "supplier:{$s->id}" || ($transaction->supplier_id == $s->id && $transaction->transaction_type === 'credit') ? 'selected' : '' }}>
                                                            Supplier: {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }} [{{ strtoupper($s->service_type ?? 'Supplier') }}]
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="👔 Staff Members">
                                                    @foreach ($staffMembers as $st)
                                                        <option value="staff:{{ $st->id }}" {{ old('jv_to_account') == "staff:{$st->id}" || ($transaction->staff_id == $st->id && $transaction->transaction_type === 'credit') ? 'selected' : '' }}>
                                                            Staff: {{ $st->first_name }} {{ $st->last_name }} ({{ $st->designation->name ?? 'Staff' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏦 Bank Accounts">
                                                    @foreach ($banks as $b)
                                                        <option value="bank:{{ $b->id }}" {{ old('jv_to_account') == "bank:{$b->id}" || ($transaction->bank_id == $b->id && $transaction->transaction_type === 'credit') ? 'selected' : '' }}>
                                                            Bank: {{ $b->name }}{{ $b->account_number ? ' (' . $b->account_number . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏢 Expense Heads">
                                                    @foreach ($expenses as $e)
                                                        <option value="expense:{{ $e->id }}" {{ old('jv_to_account') == "expense:{$e->id}" || ($transaction->expense_id == $e->id && $transaction->transaction_type === 'credit') ? 'selected' : '' }}>
                                                            Expense: {{ $e->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="💵 Cash & Loan Accounts">
                                                    @foreach ($cashAccounts as $ca)
                                                        <option value="account:{{ $ca->id }}" {{ old('jv_to_account') == "account:{$ca->id}" || ($transaction->account_id == $ca->id && $transaction->transaction_type === 'credit') ? 'selected' : '' }}>
                                                            {{ $ca->account_name }} ({{ strtoupper(str_replace('_', ' ', $ca->account_type)) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODE B: SINGLE PARTY JV ADJUSTMENT -->
                                <div id="jv_box_single_party" class="jv-box {{ old('jv_mode') === 'single_party' ? '' : 'd-none' }}">
                                    <div class="alert alert-info py-2 px-3 mb-3 fs-12 border-0 bg-soft-info text-info">
                                        <i class="mdi mdi-information-outline me-1"></i>
                                        <strong>Single Party JV Adjustment:</strong> Directly adjust opening balances, discounts, or write-offs for any single client, supplier, staff, or general account.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Account / Party to Adjust <span class="text-danger">*</span></label>
                                            <select name="jv_single_party" id="jv_single_party_select" class="form-select form-select-sm select2">
                                                <option value="">-- Search Party to Adjust --</option>
                                                <optgroup label="👤 Clients">
                                                    @foreach ($clients as $c)
                                                        <option value="client:{{ $c->id }}" {{ old('jv_single_party') == "client:{$c->id}" || $transaction->client_id == $c->id ? 'selected' : '' }}>Client: {{ $c->name }} ({{ $c->phone ?? 'Client' }})</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="🚚 Suppliers">
                                                    @foreach ($suppliers as $s)
                                                        <option value="supplier:{{ $s->id }}" {{ old('jv_single_party') == "supplier:{$s->id}" || $transaction->supplier_id == $s->id ? 'selected' : '' }}>Supplier: {{ $s->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="👔 Staff Members">
                                                    @foreach ($staffMembers as $st)
                                                        <option value="staff:{{ $st->id }}" {{ old('jv_single_party') == "staff:{$st->id}" || $transaction->staff_id == $st->id ? 'selected' : '' }}>Staff: {{ $st->first_name }} {{ $st->last_name }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="🏢 Expense Heads">
                                                    @foreach ($expenses as $e)
                                                        <option value="expense:{{ $e->id }}" {{ old('jv_single_party') == "expense:{$e->id}" || $transaction->expense_id == $e->id ? 'selected' : '' }}>Expense: {{ $e->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="💵 Cash & Loan Accounts">
                                                    @foreach ($cashAccounts as $ca)
                                                        <option value="account:{{ $ca->id }}" {{ old('jv_single_party') == "account:{$ca->id}" || $transaction->account_id == $ca->id ? 'selected' : '' }}>{{ $ca->account_name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODE C: CLIENT & SUPPLIER CONTRA -->
                                <div id="jv_box_client_supplier" class="jv-box {{ old('jv_mode') === 'client_supplier' ? '' : 'd-none' }}">
                                    <div class="alert alert-primary py-2 px-3 mb-3 fs-12 border-0 bg-soft-primary text-primary">
                                        <i class="mdi mdi-swap-horizontal-bold me-1"></i>
                                        <strong>Contra Adjustment:</strong> Use when a client directly pays a supplier on your behalf, reducing both client receivable and supplier payable simultaneously.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Client (Receivable Reducer) <span class="text-danger">*</span></label>
                                            <select name="jv_client_id" id="jv_client_id_select" class="form-select form-select-sm select2">
                                                <option value="">-- Choose Client --</option>
                                                @foreach ($clients as $c)
                                                    <option value="{{ $c->id }}" {{ old('jv_client_id', $transaction->client_id) == $c->id ? 'selected' : '' }}>
                                                        {{ $c->name }} ({{ $c->phone ?? 'Client' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Supplier (Payable Reducer) <span class="text-danger">*</span></label>
                                            <select name="jv_supplier_id" id="jv_supplier_id_select" class="form-select form-select-sm select2">
                                                <option value="">-- Choose Supplier --</option>
                                                @foreach ($suppliers as $s)
                                                    <option value="{{ $s->id }}" {{ old('jv_supplier_id', $transaction->supplier_id) == $s->id ? 'selected' : '' }}>
                                                        {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODE D: MULTI-CLIENT ADJUSTMENT -->
                                <div id="jv_box_multi_client" class="jv-box {{ old('jv_mode') === 'multi_client' ? '' : 'd-none' }}">
                                    <div class="alert alert-purple py-2 px-3 mb-3 fs-12 border-0" style="background: #f3e8ff; color: #6b21a8;">
                                        <i class="mdi mdi-account-multiple-check me-1"></i>
                                        <strong>Multi-Client Adjustment:</strong> Distribute or record adjustments across multiple clients simultaneously in one voucher.
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle fs-13" id="multiClientTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 45%;">Client</th>
                                                    <th style="width: 25%;">Amount</th>
                                                    <th style="width: 20%;">Type</th>
                                                    <th style="width: 10%; text-align: center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="multiClientBody">
                                                <tr>
                                                    <td>
                                                        <select name="multi_clients[0][client_id]" class="form-select form-select-sm select2">
                                                            <option value="">-- Select Client --</option>
                                                            @foreach ($clients as $c)
                                                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'Client' }})</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" name="multi_clients[0][amount]" class="form-control form-control-sm" placeholder="Amount">
                                                    </td>
                                                    <td>
                                                        <select name="multi_clients[0][type]" class="form-select form-select-sm">
                                                            <option value="credit">Credit (Receive / Reduce Due)</option>
                                                            <option value="debit">Debit (Charge / Increase Due)</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1 disabled"><i class="mdi mdi-trash-can"></i></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddClientRow">
                                            <i class="mdi mdi-plus me-1"></i> + Add Another Client
                                        </button>
                                    </div>
                                </div>

                                @if(isset($relatedJvEntries) && $relatedJvEntries->count() > 0)
                                    <div class="mt-3 p-2 bg-light border rounded">
                                        <h6 class="fs-12 fw-bold text-muted mb-2 text-uppercase">
                                            <i class="mdi mdi-link-variant me-1"></i> Related Leg(s) in this Voucher (Ref: {{ $transaction->reference_no }}):
                                        </h6>
                                        <ul class="mb-0 fs-12 ps-3">
                                            @foreach($relatedJvEntries as $rje)
                                                <li>
                                                    <strong>Leg #{{ $rje->id }}:</strong> 
                                                    {{ strtoupper($rje->transaction_type) }} 
                                                    {{ number_format($rje->amount, 2) }} {{ $rje->currency }} — 
                                                    @if($rje->client) Client: {{ $rje->client->name }} @endif
                                                    @if($rje->supplier) Supplier: {{ $rje->supplier->name }} @endif
                                                    @if($rje->staff) Staff: {{ $rje->staff->first_name }} {{ $rje->staff->last_name }} @endif
                                                    @if($rje->account) Account: {{ $rje->account->account_name }} @endif
                                                    @if($rje->bank) Bank: {{ $rje->bank->name }} @endif
                                                    ({{ $rje->description }})
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                            </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 2: TRANSACTION & PAYMENT DETAILS -->
                <div class="card border shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom py-2">
                        <label class="form-label fs-14 fw-bold mb-0 text-dark">
                            <i class="mdi mdi-credit-card-settings-outline text-primary me-1"></i> Step 2: Date, Season, Payment Mode & Amount Details
                        </label>
                    </div>
                    <div class="card-body p-3">

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
                                <label class="form-label fs-13 fw-semibold">Payment Mode <span class="text-danger">*</span></label>
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

                        <!-- ROW 2: Bank / Cash Account, Currency & ROE -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6" id="bank_select_group">
                                <label class="form-label fs-13 fw-semibold">Bank</label>
                                <select name="bank_id" id="bank_id_select" class="form-select form-select-sm select2">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id }}" {{ old('bank_id', $transaction->bank_id) == $b->id ? 'selected' : '' }}>
                                            {{ $b->name }}{{ !empty($b->account_name) ? ' - ' . $b->account_name : '' }}{{ !empty($b->account_number) && $b->account_number !== '0' ? ' (' . $b->account_number . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 d-none" id="cash_account_group">
                                <label class="form-label fs-13 fw-semibold"><i class="mdi mdi-cash-register text-success me-1"></i> Cash Account <span class="text-danger">*</span></label>
                                <select name="account_id" id="account_id_select" class="form-select form-select-sm select2">
                                    <option value="">-- Select Cash Account --</option>
                                    @if(isset($cashAccounts))
                                        @foreach ($cashAccounts as $ca)
                                            <option value="{{ $ca->id }}" {{ old('account_id', $transaction->account_id) == $ca->id ? 'selected' : '' }}>
                                                {{ $ca->account_name }} ({{ strtoupper(str_replace('_', ' ', $ca->account_type)) }}) — Opening: PKR {{ number_format($ca->opening_balance_pkr, 2) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-13 fw-semibold">Currency & ROE (Rate of Exchange) <span class="text-danger">*</span></label>
                                <div class="row g-1">
                                    <div class="col-4">
                                        <select name="currency" id="currency_select" class="form-select form-select-sm" required>
                                            <option value="PKR" {{ old('currency', $transaction->currency) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                            <option value="SAR" {{ old('currency', $transaction->currency) == 'SAR' ? 'selected' : '' }}>SAR</option>
                                            <option value="USD" {{ old('currency', $transaction->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fs-12">ROE:</span>
                                            <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate_input"
                                                value="{{ old('exchange_rate', $transaction->exchange_rate ?: 75) }}"
                                                class="form-control form-control-sm" placeholder="75.00" title="Exchange rate for SAR conversion">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ROW 3: Amount, Transaction Type & Reference -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Amount (Always in PKR by Default) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text currency-symbol">{{ old('currency', $transaction->currency ?: 'PKR') }}</span>
                                    <input type="number" step="0.01" name="amount" id="amount_input"
                                        value="{{ old('amount', $transaction->amount) }}"
                                        class="form-control form-control-sm" placeholder="0.00" required>
                                </div>
                                <div id="roe_live_helper" class="mt-1 d-none"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Transaction Flow <span class="text-danger">*</span></label>
                                <select name="transaction_type" class="form-select form-select-sm">
                                    <option value="debit" {{ old('transaction_type', $transaction->transaction_type) == 'debit' ? 'selected' : '' }}>Debit (Payment Out / Expense / Supplier Pay)</option>
                                    <option value="credit" {{ old('transaction_type', $transaction->transaction_type) == 'credit' ? 'selected' : '' }}>Credit (Payment In / Client Receipt / Income)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Reference / Voucher No.</label>
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

                        <!-- ROW 5: Description / Notes -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fs-13 fw-semibold">Description / Notes</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Reason for Payment / Notes">{{ old('description', $transaction->description) }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- SUBMIT BUTTONS -->
                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="mdi mdi-content-save me-1"></i> Update Transaction
                    </button>
                    <a href="{{ route('expense.transaction.index') }}" class="btn btn-secondary px-4">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".flatpickr-date", { dateFormat: "Y-m-d", allowInput: true });

        // Initialize Select2 with Bootstrap 5 theme
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Search & Select --',
            allowClear: true
        });

        // Toggle Bank / Cash dropdown based on payment type
        function togglePaymentTypeFields() {
            const isCash = document.getElementById('pay_cash')?.checked;
            const bankGroup = document.getElementById('bank_select_group');
            const cashGroup = document.getElementById('cash_account_group');
            if (isCash) {
                if (bankGroup) bankGroup.classList.add('d-none');
                if (cashGroup) cashGroup.classList.remove('d-none');
            } else {
                if (bankGroup) bankGroup.classList.remove('d-none');
                if (cashGroup) cashGroup.classList.add('d-none');
            }
        }
        document.querySelectorAll('input[name="payment_type"]').forEach(r => {
            r.addEventListener('change', togglePaymentTypeFields);
        });
        togglePaymentTypeFields();

        // Payee Type Sections Toggle
        const payeeRadios = document.querySelectorAll('.payee-type-radio');
        const sections = {
            supplier: document.getElementById('supplierSection'),
            client: document.getElementById('clientSection'),
            staff: document.getElementById('staffSection'),
            expense: document.getElementById('expenseHeadSection'),
            loan: document.getElementById('loanSection'),
            self: document.getElementById('selfSection'),
            jv: document.getElementById('jvSection'),
        };

        function showPayeeSection(selectedType) {
            Object.keys(sections).forEach(type => {
                if (sections[type]) {
                    if (type === selectedType) {
                        sections[type].classList.remove('d-none');
                    } else {
                        sections[type].classList.add('d-none');
                    }
                }
            });
        }

        payeeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    showPayeeSection(this.value);
                }
            });
        });

        // JV Modes Toggle
        const jvBoxes = {
            universal: document.getElementById('jv_box_universal'),
            single_party: document.getElementById('jv_box_single_party'),
            client_supplier: document.getElementById('jv_box_client_supplier'),
            multi_client: document.getElementById('jv_box_multi_client')
        };
        document.querySelectorAll('.jv-mode-radio').forEach(r => {
            r.addEventListener('change', function() {
                Object.keys(jvBoxes).forEach(m => {
                    if (jvBoxes[m]) {
                        if (m === this.value) {
                            jvBoxes[m].classList.remove('d-none');
                        } else {
                            jvBoxes[m].classList.add('d-none');
                        }
                    }
                });
            });
        });

        // Multi-Client Add Row
        let mcIndex = 1;
        document.getElementById('btnAddClientRow')?.addEventListener('click', function() {
            const tableBody = document.getElementById('multiClientBody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <select name="multi_clients[${mcIndex}][client_id]" class="form-select form-select-sm select2">
                        <option value="">-- Select Client --</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'Client' }})</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="multi_clients[${mcIndex}][amount]" class="form-control form-control-sm" placeholder="Amount">
                </td>
                <td>
                    <select name="multi_clients[${mcIndex}][type]" class="form-select form-select-sm">
                        <option value="credit">Credit (Receive / Reduce Due)</option>
                        <option value="debit">Debit (Charge / Increase Due)</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1 btn-remove-row"><i class="mdi mdi-trash-can"></i></button>
                </td>
            `;
            tableBody.appendChild(newRow);
            $(newRow).find('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
            newRow.querySelector('.btn-remove-row').addEventListener('click', function() {
                newRow.remove();
            });
            mcIndex++;
        });

        // ROE Live Preview Helper
        function calcRoeLive() {
            const amt = parseFloat($('#amount_input').val()) || 0;
            const roe = parseFloat($('#exchange_rate_input').val()) || 0;
            const cur = $('#currency_select').val();
            const helper = $('#roe_live_helper');

            if (cur === 'PKR' && roe > 0 && amt > 0) {
                const sar = (amt / roe).toFixed(2);
                helper.html('<span class="badge bg-soft-success text-success fs-12 px-2 py-1"><i class="mdi mdi-calculator me-1"></i> <strong>' + Number(amt).toLocaleString() + ' PKR</strong> @ ROE <strong>' + roe + '</strong> = <strong class="text-dark">' + Number(sar).toLocaleString() + ' SAR</strong> (will record in client/supplier ledger)</span>').removeClass('d-none');
            } else if (cur === 'SAR' && roe > 0 && amt > 0) {
                const pkr = (amt * roe).toFixed(2);
                helper.html('<span class="badge bg-soft-info text-info fs-12 px-2 py-1"><i class="mdi mdi-calculator me-1"></i> <strong>' + Number(amt).toLocaleString() + ' SAR</strong> @ ROE <strong>' + roe + '</strong> = <strong class="text-dark">' + Number(pkr).toLocaleString() + ' PKR</strong></span>').removeClass('d-none');
            } else {
                helper.addClass('d-none').empty();
            }
        }

        $('#amount_input, #exchange_rate_input').on('input', calcRoeLive);
        $('#currency_select').on('change', function() {
            $('.currency-symbol').text(this.value);
            calcRoeLive();
        });
        calcRoeLive();

        // Supplier Detail Preview
        const supplierSelect = document.getElementById('supplier_id_select');
        const supplierCard = document.getElementById('supplierDetailCard');
        if (supplierSelect) {
            $(supplierSelect).on('change', function() {
                const opt = this.options[this.selectedIndex];
                if (this.value && opt) {
                    document.getElementById('supName').textContent = opt.getAttribute('data-name') || '-';
                    document.getElementById('supType').textContent = opt.getAttribute('data-type') || '-';
                    document.getElementById('supPayable').textContent = opt.getAttribute('data-payable') || '0.00';
                    document.getElementById('supPaid').textContent = opt.getAttribute('data-paid') || '0.00';
                    document.getElementById('supBalance').textContent = opt.getAttribute('data-balance') || '0.00';
                    supplierCard.classList.remove('d-none');
                } else {
                    supplierCard.classList.add('d-none');
                }
            });
            if (supplierSelect.value) $(supplierSelect).trigger('change');
        }

        // Client Detail Preview
        const clientSelect = document.getElementById('client_id_select');
        const clientCard = document.getElementById('clientDetailCard');
        if (clientSelect) {
            $(clientSelect).on('change', function() {
                const opt = this.options[this.selectedIndex];
                if (this.value && opt) {
                    document.getElementById('cliName').textContent = opt.getAttribute('data-name') || '-';
                    document.getElementById('cliPhone').textContent = opt.getAttribute('data-phone') || '-';
                    document.getElementById('cliPackage').textContent = opt.getAttribute('data-package') || '-';
                    document.getElementById('cliBalance').textContent = opt.getAttribute('data-balance') || '0.00';
                    clientCard.classList.remove('d-none');
                } else {
                    clientCard.classList.add('d-none');
                }
            });
            if (clientSelect.value) $(clientSelect).trigger('change');
        }

        // Staff Detail Preview
        const staffSelect = document.getElementById('staff_id_select');
        const staffCard = document.getElementById('staffDetailCard');
        if (staffSelect) {
            $(staffSelect).on('change', function() {
                const opt = this.options[this.selectedIndex];
                if (this.value && opt) {
                    document.getElementById('stName').textContent = opt.getAttribute('data-name') || '-';
                    document.getElementById('stDesignation').textContent = opt.getAttribute('data-designation') || '-';
                    document.getElementById('stSalary').textContent = opt.getAttribute('data-salary') || '0.00';
                    document.getElementById('stAdvance').textContent = opt.getAttribute('data-advance') || '0.00';
                    staffCard.classList.remove('d-none');
                } else {
                    staffCard.classList.add('d-none');
                }
            });
            if (staffSelect.value) $(staffSelect).trigger('change');
        }

    });
</script>
@endsection
BLADE;

file_put_contents('resources/views/expense_transaction/edit.blade.php', $content);
echo "Written edit.blade.php (" . strlen($content) . " bytes)\n";
