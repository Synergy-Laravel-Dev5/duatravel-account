@extends('layout.master')

@section('title', 'Add Transaction')

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
    .select2-container {
        width: 100% !important;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- PAGE TITLE -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-0 text-dark">
                        <i class="mdi mdi-cash-multiple text-primary me-1"></i> Add Transaction
                    </h4>
                    <p class="text-muted fs-13 mb-0">Record and manage client receipts, supplier payments, staff disbursements, JV adjustments, and expenses.</p>
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

            <form action="{{ route('expense.transaction.store') }}" method="POST" enctype="multipart/form-data" id="expenseForm">
                @csrf

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
                                        id="payee_supplier" value="supplier" {{ old('payee_type', 'supplier') == 'supplier' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold text-primary" for="payee_supplier">
                                        <i class="mdi mdi-truck-delivery me-1"></i> Supplier
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                        id="payee_client" value="client" {{ old('payee_type') == 'client' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold text-info" for="payee_client">
                                        <i class="mdi mdi-account me-1"></i> Client
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                        id="payee_staff" value="staff" {{ old('payee_type') == 'staff' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold text-success" for="payee_staff">
                                        <i class="mdi mdi-account-tie me-1"></i> Staff Member
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                        id="payee_expense" value="expense" {{ old('payee_type') == 'expense' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold text-secondary" for="payee_expense">
                                        <i class="mdi mdi-office-building me-1"></i> Office Expense
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                        id="payee_jv" value="jv" {{ old('payee_type') == 'jv' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold" style="color: #7c3aed;" for="payee_jv">
                                        <i class="mdi mdi-book-open-page-variant me-1"></i> JV Voucher
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                        id="payee_loan" value="loan" {{ old('payee_type') == 'loan' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold text-warning" for="payee_loan">
                                        <i class="mdi mdi-hand-coin me-1"></i> Loan (Asset / Liability)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payee-type-radio" type="radio" name="payee_type"
                                        id="payee_self" value="self" {{ old('payee_type') == 'self' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13 fw-semibold text-dark" for="payee_self">
                                        <i class="mdi mdi-account-circle me-1"></i> Self / Transfer
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 bg-light bg-opacity-50">

                        <!-- 1. SUPPLIER SECTION -->
                        <div id="supplierSection" class="{{ old('payee_type', 'supplier') == 'supplier' ? '' : 'd-none' }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Select Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" id="supplier_id_select" class="form-select form-select-sm select2">
                                        <option value="">-- Select Supplier --</option>
                                        @foreach ($suppliers as $s)
                                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}
                                                data-name="{{ $s->name }}"
                                                data-type="{{ ucfirst($s->supplier_type ?? 'N/A') }}"
                                                data-payable="{{ number_format($s->total_payable ?? 0, 2) }}"
                                                data-paid="{{ number_format($s->total_paid ?? 0, 2) }}"
                                                data-balance="{{ number_format($s->balance ?? 0, 2) }}">
                                                {{ $s->name }}{{ !empty($s->company_name) ? ' (' . $s->company_name . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border mb-0 d-none" id="supplierDetailCard">
                                        <div class="card-body p-2 fs-12">
                                            <div class="row g-1">
                                                <div class="col-6"><strong>Supplier:</strong> <span id="supName">-</span></div>
                                                <div class="col-6"><strong>Type:</strong> <span id="supType">-</span></div>
                                                <div class="col-4 text-danger"><strong>Payable:</strong> <span id="supPayable">0.00</span></div>
                                                <div class="col-4 text-success"><strong>Paid:</strong> <span id="supPaid">0.00</span></div>
                                                <div class="col-4 text-primary"><strong>Balance:</strong> <span id="supBalance">0.00</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. CLIENT SECTION -->
                        <div id="clientSection" class="{{ old('payee_type') == 'client' ? '' : 'd-none' }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Select Client <span class="text-danger">*</span></label>
                                    <select name="client_id" id="client_id_select" class="form-select form-select-sm select2">
                                        <option value="">-- Select Client --</option>
                                        @foreach ($clients as $c)
                                            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}
                                                data-name="{{ $c->name }}"
                                                data-phone="{{ $c->phone ?? 'N/A' }}"
                                                data-package="{{ ucfirst($c->package_type ?? 'N/A') }}"
                                                data-balance="{{ number_format($c->balance ?? 0, 2) }}">
                                                {{ $c->name }}{{ !empty($c->phone) ? ' (' . $c->phone . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border mb-0 d-none" id="clientDetailCard">
                                        <div class="card-body p-2 fs-12">
                                            <div class="row g-1">
                                                <div class="col-6"><strong>Client:</strong> <span id="cliName">-</span></div>
                                                <div class="col-6"><strong>Phone:</strong> <span id="cliPhone">-</span></div>
                                                <div class="col-6"><strong>Package:</strong> <span id="cliPackage">-</span></div>
                                                <div class="col-6 text-primary"><strong>Balance:</strong> <span id="cliBalance">0.00</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. STAFF PAYMENT SECTION -->
                        <div id="staffSection" class="{{ old('payee_type') == 'staff' ? '' : 'd-none' }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">Staff Member <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staff_id_select" class="form-select form-select-sm select2">
                                        <option value="">-- Select Staff Member --</option>
                                        @foreach ($staffMembers as $s)
                                            @php
                                                $fullName = trim($s->first_name . ' ' . $s->last_name);
                                            @endphp
                                            <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}
                                                data-name="{{ $fullName }}"
                                                data-designation="{{ $s->designation->name ?? 'Staff' }}"
                                                data-department="{{ $s->department->name ?? 'General' }}"
                                                data-salary="{{ number_format($s->basic_salary ?? 0, 2) }}"
                                                data-advance="{{ number_format($s->total_advance ?? 0, 2) }}">
                                                {{ $fullName }} ({{ $s->designation->name ?? 'Staff' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold">Salary Month & Year</label>
                                    <div class="row g-1">
                                        <div class="col-7">
                                            <select name="salary_month" class="form-select form-select-sm">
                                                @foreach (['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $k => $m)
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
                        <div id="expenseHeadSection" class="{{ old('payee_type') == 'expense' ? '' : 'd-none' }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Office Expense Head <span class="text-danger">*</span></label>
                                    <select name="expense_id" class="form-select form-select-sm select2">
                                        <option value="">-- Select Expense Category --</option>
                                        @foreach ($expenses as $e)
                                            <option value="{{ $e->id }}" {{ old('expense_id') == $e->id ? 'selected' : '' }}>
                                                {{ $e->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 5. LOAN (ASSET / LIABILITY) SECTION -->
                        <div id="loanSection" class="{{ old('payee_type') == 'loan' ? '' : 'd-none' }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label fs-13 fw-semibold">Select Loan Account <span class="text-danger">*</span></label>
                                    <select name="loan_account_id" id="loan_account_id_select" class="form-select form-select-sm select2">
                                        <option value="">-- Choose Loan Account --</option>
                                        @foreach ($cashAccounts->filter(fn($a) => in_array($a->account_type, ['loan_asset', 'loan_liability'])) as $la)
                                            <option value="{{ $la->id }}" {{ old('loan_account_id') == $la->id ? 'selected' : '' }}>
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
                        <div id="selfSection" class="{{ old('payee_type') == 'self' ? '' : 'd-none' }}">
                            <div class="alert alert-secondary py-2 px-3 mb-0 fs-13">
                                <i class="mdi mdi-information-outline me-1"></i>
                                <strong>Internal Transfer:</strong> Moving funds between company bank accounts or petty cash accounts.
                            </div>
                        </div>

                        <!-- 7. JV VOUCHER SECTION (ALL JV OPTIONS & SEARCHABLE DROPDOWNS) -->
                        <div id="jvSection" class="{{ old('payee_type') == 'jv' ? '' : 'd-none' }}">
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

                                        <input type="radio" class="btn-check jv-mode-radio" name="jv_mode" id="jv_mode_cms" value="client_multi_supplier" {{ old('jv_mode') === 'client_multi_supplier' ? 'checked' : '' }}>
                                        <label class="btn btn-sm btn-outline-warning text-dark fw-semibold" for="jv_mode_cms">
                                            <i class="mdi mdi-call-split me-1 text-warning"></i> 🏢 1 Client ➔ Multi-Supplier Split JV
                                        </label>
                                    </div>
                                </div>

                                <!-- MODE A: UNIVERSAL SEARCHABLE TRANSFER (FROM ACCOUNT ➔ TO ACCOUNT) -->
                                <div id="jv_box_universal" class="jv-box {{ old('jv_mode', 'universal') === 'universal' ? '' : 'd-none' }}">
                                    <div class="alert alert-success py-2 px-3 mb-3 fs-12 border-0 bg-soft-success text-success">
                                        <i class="mdi mdi-check-circle me-1"></i>
                                        <strong>Universal JV Transfer:</strong> Both dropdowns below include <strong>ALL Clients, Suppliers, Expense Heads, Banks, Staff Members, Cash & Loan Accounts</strong>. Easily search and transfer funds or balances between any two entities!
                                    </div>

                                    <!-- QUICK SAR CONVERTER BAR -->
                                    <div class="p-2 mb-3 rounded bg-light border d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-12 fw-bold text-dark"><i class="mdi mdi-swap-horizontal text-primary me-1"></i> Quick SAR Base Amount:</span>
                                            <div class="input-group input-group-sm" style="max-width: 170px;">
                                                <input type="number" step="0.01" id="jv_sar_base_amount" class="form-control form-control-sm text-center fw-bold" placeholder="e.g. 100.00">
                                                <span class="input-group-text bg-white fw-bold">SAR</span>
                                            </div>
                                            <small class="text-muted fs-11">(Auto-calculates From &amp; To PKR by each party's ROE)</small>
                                        </div>
                                    </div>

                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-5">
                                            <label class="form-label fs-13 fw-bold text-danger">
                                                <i class="mdi mdi-arrow-up-bold-circle me-1"></i> From Account / Entity (Credit / Out) <span class="text-danger">*</span>
                                            </label>
                                            <select name="jv_from_account" id="jv_from_account_select" class="form-select form-select-sm select2">
                                                <option value="">-- Search & Select From Account --</option>
                                                
                                                <optgroup label="👥 Clients">
                                                    @foreach($clients as $c)
                                                        <option value="client:{{ $c->id }}" {{ old('jv_from_account') == "client:{$c->id}" ? 'selected' : '' }}>
                                                            Client: {{ $c->name }}{{ $c->phone ? ' (' . $c->phone . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏢 Suppliers">
                                                    @foreach($suppliers as $s)
                                                        <option value="supplier:{{ $s->id }}" {{ old('jv_from_account') == "supplier:{$s->id}" ? 'selected' : '' }}>
                                                            Supplier: {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="💼 Expense Heads">
                                                    @foreach($expenses as $e)
                                                        <option value="expense:{{ $e->id }}" {{ old('jv_from_account') == "expense:{$e->id}" ? 'selected' : '' }}>
                                                            Expense: {{ $e->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏦 Banks">
                                                    @foreach($banks as $b)
                                                        <option value="bank:{{ $b->id }}" {{ old('jv_from_account') == "bank:{$b->id}" ? 'selected' : '' }}>
                                                            Bank: {{ $b->name }}{{ $b->account_number ? ' (' . $b->account_number . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="👤 Staff Members">
                                                    @foreach($staffMembers as $st)
                                                        <option value="staff:{{ $st->id }}" {{ old('jv_from_account') == "staff:{$st->id}" ? 'selected' : '' }}>
                                                            Staff: {{ $st->first_name }} {{ $st->last_name }} ({{ $st->designation->name ?? 'Staff' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="💵 Cash Accounts">
                                                    @foreach($cashAccounts->where('account_type', 'cash') as $ca)
                                                        <option value="account:{{ $ca->id }}" {{ old('jv_from_account') == "account:{$ca->id}" ? 'selected' : '' }}>
                                                            Cash: {{ $ca->account_name }} (Opening: PKR {{ number_format($ca->opening_balance_pkr, 2) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="📥 Loan Assets (Receivable)">
                                                    @foreach($cashAccounts->where('account_type', 'loan_asset') as $la)
                                                        <option value="account:{{ $la->id }}" {{ old('jv_from_account') == "account:{$la->id}" ? 'selected' : '' }}>
                                                            Loan Asset: {{ $la->account_name }} (Opening: PKR {{ number_format($la->opening_balance_pkr, 2) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="📤 Loan Liabilities (Payable)">
                                                    @foreach($cashAccounts->where('account_type', 'loan_liability') as $ll)
                                                        <option value="account:{{ $ll->id }}" {{ old('jv_from_account') == "account:{$ll->id}" ? 'selected' : '' }}>
                                                            Loan Liability: {{ $ll->account_name }} (Opening: PKR {{ number_format($ll->opening_balance_pkr, 2) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="📁 General Accounts">
                                                    @foreach($cashAccounts->whereNotIn('account_type', ['cash', 'loan_asset', 'loan_liability']) as $ga)
                                                        <option value="account:{{ $ga->id }}" {{ old('jv_from_account') == "account:{$ga->id}" ? 'selected' : '' }}>
                                                            General: {{ $ga->account_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                            <div class="mt-2 p-2 rounded bg-light border border-danger border-opacity-25">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label fs-11 fw-bold text-danger mb-1">
                                                            <i class="mdi mdi-currency-usd me-1"></i> From ROE <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-white text-danger fw-bold fs-11">1 SAR=</span>
                                                            <input type="number" step="0.0001" name="jv_from_roe" id="jv_from_roe"
                                                                value="{{ old('jv_from_roe', 76.00) }}"
                                                                class="form-control form-control-sm text-center fw-bold" placeholder="76.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fs-11 fw-bold text-danger mb-1">
                                                            From PKR Amount <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="number" step="0.01" name="jv_from_amount" id="jv_from_amount_input"
                                                            value="{{ old('jv_from_amount') }}"
                                                            class="form-control form-control-sm text-end fw-bold" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div id="from_account_sar_hint" class="fs-11 fw-semibold text-danger mt-1"></div>
                                            </div>
                                        </div>

                                        <div class="col-md-2 text-center pt-3">
                                            <span class="badge bg-primary fs-14 p-2 rounded-circle">➔</span>
                                        </div>

                                        <div class="col-md-5">
                                            <label class="form-label fs-13 fw-bold text-success">
                                                <i class="mdi mdi-arrow-down-bold-circle me-1"></i> To Account / Entity (Debit / In) <span class="text-danger">*</span>
                                            </label>
                                            <select name="jv_to_account" id="jv_to_account_select" class="form-select form-select-sm select2">
                                                <option value="">-- Search & Select To Account --</option>
                                                
                                                <optgroup label="👥 Clients">
                                                    @foreach($clients as $c)
                                                        <option value="client:{{ $c->id }}" {{ old('jv_to_account') == "client:{$c->id}" ? 'selected' : '' }}>
                                                            Client: {{ $c->name }}{{ $c->phone ? ' (' . $c->phone . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏢 Suppliers">
                                                    @foreach($suppliers as $s)
                                                        <option value="supplier:{{ $s->id }}" {{ old('jv_to_account') == "supplier:{$s->id}" ? 'selected' : '' }}>
                                                            Supplier: {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="💼 Expense Heads">
                                                    @foreach($expenses as $e)
                                                        <option value="expense:{{ $e->id }}" {{ old('jv_to_account') == "expense:{$e->id}" ? 'selected' : '' }}>
                                                            Expense: {{ $e->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="🏦 Banks">
                                                    @foreach($banks as $b)
                                                        <option value="bank:{{ $b->id }}" {{ old('jv_to_account') == "bank:{$b->id}" ? 'selected' : '' }}>
                                                            Bank: {{ $b->name }}{{ $b->account_number ? ' (' . $b->account_number . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="👤 Staff Members">
                                                    @foreach($staffMembers as $st)
                                                        <option value="staff:{{ $st->id }}" {{ old('jv_to_account') == "staff:{$st->id}" ? 'selected' : '' }}>
                                                            Staff: {{ $st->first_name }} {{ $st->last_name }} ({{ $st->designation->name ?? 'Staff' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="💵 Cash Accounts">
                                                    @foreach($cashAccounts->where('account_type', 'cash') as $ca)
                                                        <option value="account:{{ $ca->id }}" {{ old('jv_to_account') == "account:{$ca->id}" ? 'selected' : '' }}>
                                                            Cash: {{ $ca->account_name }} (Opening: PKR {{ number_format($ca->opening_balance_pkr, 2) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="📥 Loan Assets (Receivable)">
                                                    @foreach($cashAccounts->where('account_type', 'loan_asset') as $la)
                                                        <option value="account:{{ $la->id }}" {{ old('jv_to_account') == "account:{$la->id}" ? 'selected' : '' }}>
                                                            Loan Asset: {{ $la->account_name }} (Opening: PKR {{ number_format($la->opening_balance_pkr, 2) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="📤 Loan Liabilities (Payable)">
                                                    @foreach($cashAccounts->where('account_type', 'loan_liability') as $ll)
                                                        <option value="account:{{ $ll->id }}" {{ old('jv_to_account') == "account:{$ll->id}" ? 'selected' : '' }}>
                                                            Loan Liability: {{ $ll->account_name }} (Opening: PKR {{ number_format($ll->opening_balance_pkr, 2) }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>

                                                <optgroup label="📁 General Accounts">
                                                    @foreach($cashAccounts->whereNotIn('account_type', ['cash', 'loan_asset', 'loan_liability']) as $ga)
                                                        <option value="account:{{ $ga->id }}" {{ old('jv_to_account') == "account:{$ga->id}" ? 'selected' : '' }}>
                                                            General: {{ $ga->account_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                            <div class="mt-2 p-2 rounded bg-light border border-success border-opacity-25">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label fs-11 fw-bold text-success mb-1">
                                                            <i class="mdi mdi-currency-usd me-1"></i> To ROE <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-white text-success fw-bold fs-11">1 SAR=</span>
                                                            <input type="number" step="0.0001" name="jv_to_roe" id="jv_to_roe"
                                                                value="{{ old('jv_to_roe', 75.00) }}"
                                                                class="form-control form-control-sm text-center fw-bold" placeholder="75.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fs-11 fw-bold text-success mb-1">
                                                            To PKR Amount <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="number" step="0.01" name="jv_to_amount" id="jv_to_amount_input"
                                                            value="{{ old('jv_to_amount') }}"
                                                            class="form-control form-control-sm text-end fw-bold" placeholder="0.00">
                                                    </div>
                                                </div>
                                                <div id="to_account_sar_hint" class="fs-11 fw-semibold text-success mt-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODE B: SINGLE PARTY JV ADJUSTMENT -->
                                <div id="jv_box_single" class="jv-box {{ old('jv_mode') === 'single_party' ? '' : 'd-none' }}">
                                    <div class="alert alert-info py-2 px-3 mb-3 fs-12 border-0 bg-soft-info text-info">
                                        <i class="mdi mdi-information-outline me-1"></i>
                                        <strong>Single Party JV:</strong> Record direct write-offs, journal adjustments, or discounts for a single client, supplier, staff, or general account.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Single Party / Account <span class="text-danger">*</span></label>
                                            <select name="jv_single_party" id="jv_single_party_select" class="form-select form-select-sm select2">
                                                <option value="">-- Choose Party / Entity --</option>
                                                <optgroup label="👥 Clients">
                                                    @foreach($clients as $c)
                                                        <option value="client:{{ $c->id }}" {{ old('jv_single_party') == "client:{$c->id}" ? 'selected' : '' }}>Client: {{ $c->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="🏢 Suppliers">
                                                    @foreach($suppliers as $s)
                                                        <option value="supplier:{{ $s->id }}" {{ old('jv_single_party') == "supplier:{$s->id}" ? 'selected' : '' }}>Supplier: {{ $s->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="👤 Staff Members">
                                                    @foreach($staffMembers as $st)
                                                        <option value="staff:{{ $st->id }}" {{ old('jv_single_party') == "staff:{$st->id}" ? 'selected' : '' }}>Staff: {{ $st->first_name }} {{ $st->last_name }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="💼 Expense Heads">
                                                    @foreach($expenses as $e)
                                                        <option value="expense:{{ $e->id }}" {{ old('jv_single_party') == "expense:{$e->id}" ? 'selected' : '' }}>Expense: {{ $e->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODE C: CLIENT & SUPPLIER CONTRA -->
                                <div id="jv_box_contra" class="jv-box {{ old('jv_mode') === 'client_supplier' ? '' : 'd-none' }}">
                                    <div class="alert alert-primary py-2 px-3 mb-3 fs-12 border-0 bg-soft-primary text-primary">
                                        <i class="mdi mdi-account-switch me-1"></i>
                                        <strong>Client & Supplier Contra:</strong> Adjusts Client and Supplier against each other in 1 transaction without touching bank/cash.
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fs-13 fw-semibold text-primary">Select Client <span class="text-danger">*</span></label>
                                            <select name="jv_client_id" id="jv_contra_client" class="form-select form-select-sm select2">
                                                <option value="">-- Select Client --</option>
                                                @foreach ($clients as $c)
                                                    <option value="{{ $c->id }}" {{ old('jv_client_id') == $c->id ? 'selected' : '' }}>
                                                        {{ $c->name }}{{ $c->phone ? ' (' . $c->phone . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2 p-2 rounded bg-light border border-primary border-opacity-25">
                                                <label class="form-label fs-11 fw-bold text-primary mb-1">
                                                    <i class="mdi mdi-currency-usd me-1"></i> Client ROE (SAR / PKR Rate) <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white text-primary fw-bold fs-11">1 SAR =</span>
                                                    <input type="number" step="0.0001" name="jv_contra_client_roe" id="jv_contra_client_roe"
                                                        value="{{ old('jv_contra_client_roe', 76.00) }}"
                                                        class="form-control form-control-sm text-center fw-bold" placeholder="76.00">
                                                    <span class="input-group-text bg-white fs-11">PKR</span>
                                                </div>
                                                <div id="contra_client_sar_hint" class="fs-11 fw-semibold text-primary mt-1"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fs-13 fw-semibold text-warning">Select Supplier <span class="text-danger">*</span></label>
                                            <select name="jv_supplier_id" id="jv_contra_supplier" class="form-select form-select-sm select2">
                                                <option value="">-- Select Supplier --</option>
                                                @foreach ($suppliers as $s)
                                                    <option value="{{ $s->id }}" {{ old('jv_supplier_id') == $s->id ? 'selected' : '' }}>
                                                        {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2 p-2 rounded bg-light border border-warning border-opacity-25">
                                                <label class="form-label fs-11 fw-bold text-warning mb-1">
                                                    <i class="mdi mdi-currency-usd me-1"></i> Supplier ROE (SAR / PKR Rate) <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white text-warning fw-bold fs-11">1 SAR =</span>
                                                    <input type="number" step="0.0001" name="jv_contra_supplier_roe" id="jv_contra_supplier_roe"
                                                        value="{{ old('jv_contra_supplier_roe', 75.00) }}"
                                                        class="form-control form-control-sm text-center fw-bold" placeholder="75.00">
                                                    <span class="input-group-text bg-white fs-11">PKR</span>
                                                </div>
                                                <div id="contra_supplier_sar_hint" class="fs-11 fw-semibold text-warning mt-1"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fs-13 fw-semibold">Adjustment Flow</label>
                                            <select name="jv_cs_direction" class="form-select form-select-sm">
                                                <option value="client_to_supplier">Client Payment transferred to Supplier</option>
                                                <option value="supplier_to_client">Supplier Refund transferred to Client</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODE D: MULTI-CLIENT ADJUSTMENT -->
                                <div id="jv_box_multi" class="jv-box {{ old('jv_mode') === 'multi_client' ? '' : 'd-none' }}">
                                    <div class="alert alert-purple py-2 px-3 mb-3 fs-12 border-0 bg-soft-purple" style="color: #6b21a8; background-color: #f3e8ff;">
                                        <i class="mdi mdi-account-multiple me-1"></i>
                                        <strong>Multi-Client JV:</strong> Add multiple clients in a single voucher with custom amounts and debit/credit sides.
                                    </div>
                                    <table class="table table-sm table-bordered align-middle mb-2">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Client</th>
                                                <th style="width: 140px;">Type</th>
                                                <th style="width: 160px;">Amount</th>
                                                <th>Remarks / Reason</th>
                                                <th style="width: 40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="multiClientTbody">
                                            <tr>
                                                <td>
                                                    <select name="jv_clients[0][client_id]" class="form-select form-select-sm">
                                                        <option value="">-- Select Client --</option>
                                                        @foreach($clients as $c)
                                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="jv_clients[0][type]" class="form-select form-select-sm">
                                                        <option value="credit">Credit (Payment Received)</option>
                                                        <option value="debit">Debit (Charge / Penalty)</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="jv_clients[0][amount]" class="form-control form-control-sm text-end fw-bold" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="text" name="jv_clients[0][notes]" class="form-control form-control-sm" placeholder="Reason for adjustment">
                                                </td>
                                                <td class="text-center"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-outline-purple btn-sm" id="btnAddClientRow" style="color: #7c3aed; border-color: #7c3aed;">
                                        <i class="mdi mdi-plus-circle me-1"></i> + Add Another Client
                                    </button>
                                </div>

                                <!-- MODE E: 1 CLIENT TO MULTIPLE SUPPLIERS SPLIT JV -->
                                <div id="jv_box_cms" class="jv-box {{ old('jv_mode') === 'client_multi_supplier' ? '' : 'd-none' }}">
                                    <div class="alert alert-warning py-2 px-3 mb-3 fs-12 border-0 bg-soft-warning text-dark">
                                        <i class="mdi mdi-call-split me-1 text-warning"></i>
                                        <strong>1 Client ➔ Multi-Supplier Split JV:</strong> Pass JV where 1 Client provides an amount (e.g. PKR 500,000) and it is distributed / split among multiple suppliers with individual ROE and SAR calculations.
                                    </div>

                                    <!-- Client Source Info Box -->
                                    <div class="card border border-warning border-opacity-50 bg-light bg-opacity-25 mb-3 shadow-none">
                                        <div class="card-body p-3">
                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label fs-12 fw-bold text-dark">
                                                        <i class="mdi mdi-account-circle text-primary me-1"></i> Select Client <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="jv_cms_client_id" id="jv_cms_client_id" class="form-select form-select-sm select2">
                                                        <option value="">-- Choose Client --</option>
                                                        @foreach($clients as $c)
                                                            <option value="{{ $c->id }}" {{ old('jv_cms_client_id') == $c->id ? 'selected' : '' }}>
                                                                {{ $c->name }} ({{ $c->phone ?? 'No Phone' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-12 fw-bold text-dark">Transaction Direction</label>
                                                    <select name="jv_cms_direction" id="jv_cms_direction" class="form-select form-select-sm">
                                                        <option value="client_to_suppliers" selected>Client Pays ➔ Split to Suppliers</option>
                                                        <option value="suppliers_to_client">Suppliers Refund ➔ Paid to Client</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fs-12 fw-bold text-dark">Client ROE</label>
                                                    <input type="number" step="0.01" min="1" name="jv_cms_client_roe" id="jv_cms_client_roe" class="form-control form-control-sm text-center fw-bold" value="{{ old('jv_cms_client_roe', 76.00) }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-12 fw-bold text-dark">Total Client Amount (PKR) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" min="0" name="jv_cms_total_pkr" id="jv_cms_total_pkr" class="form-control form-control-sm text-end fw-bold" placeholder="e.g. 500000.00" value="{{ old('jv_cms_total_pkr') }}">
                                                    <div class="fs-11 text-muted text-end mt-1" id="cms_client_sar_badge">≈ 0.00 SAR</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Supplier Allocation Table -->
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-2" id="cmsSuppliersTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="min-width: 220px;">Supplier <span class="text-danger">*</span></th>
                                                    <th style="width: 170px;" class="text-end">Allocated PKR <span class="text-danger">*</span></th>
                                                    <th style="width: 110px;" class="text-center">Supplier ROE</th>
                                                    <th style="width: 140px;" class="text-end">Equivalent SAR</th>
                                                    <th>Particulars / Notes</th>
                                                    <th style="width: 40px;" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cmsSuppliersTbody">
                                                <tr>
                                                    <td>
                                                        <select name="jv_suppliers[0][supplier_id]" class="form-select form-select-sm cms-supplier-select">
                                                            <option value="">-- Select Supplier --</option>
                                                            @foreach($suppliers as $s)
                                                                <option value="{{ $s->id }}">{{ $s->name }} ({{ ucfirst($s->type ?? 'supplier') }})</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" name="jv_suppliers[0][amount]" class="form-control form-control-sm text-end fw-bold cms-sup-amount" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="1" name="jv_suppliers[0][roe]" class="form-control form-control-sm text-center fw-bold cms-sup-roe" value="75.00">
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="badge bg-soft-warning text-dark border border-warning fs-12 cms-sup-sar">0.00 SAR</span>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="jv_suppliers[0][notes]" class="form-control form-control-sm" placeholder="Split allocation remark">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-cms-row" style="display: none;"><i class="mdi mdi-close-circle fs-16"></i></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light fw-bold fs-12">
                                                <tr>
                                                    <td class="text-end">Total Split Allocated:</td>
                                                    <td class="text-end text-primary" id="cms_total_allocated_pkr">PKR 0.00</td>
                                                    <td class="text-center">—</td>
                                                    <td class="text-end text-warning" id="cms_total_allocated_sar">0.00 SAR</td>
                                                    <td colspan="2">
                                                        <span id="cms_balance_status" class="badge bg-soft-success text-success fs-11">
                                                            Balanced: PKR 0.00
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-semibold" id="btnAddSupplierRow">
                                            <i class="mdi mdi-plus-circle me-1"></i> + Add Another Supplier
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAutoDistributeSuppliers">
                                            <i class="mdi mdi-equal me-1"></i> Distribute Equally
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 2: TRANSACTION GENERAL DETAILS & AMOUNTS -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-2">
                        <label class="form-label fs-14 fw-bold mb-0 text-dark">
                            <i class="mdi mdi-calendar-clock text-primary me-1"></i> Step 2: Payment Details, Currency & Amounts
                        </label>
                    </div>

                    <div class="card-body p-4">

                        <!-- ROW 1: Date, Season, Payment Mode -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="date" id="trans_date" value="{{ old('date', date('Y-m-d')) }}"
                                        class="form-control form-control-sm flatpickr-date" placeholder="YYYY-MM-DD" required>
                                    <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fs-13 fw-semibold">Hajj / Umrah Season <span class="text-danger">*</span></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <select name="hajj_umrah" id="hajj_umrah_select" class="form-select form-select-sm" required>
                                            <option value="hajj" {{ old('hajj_umrah', session('dashboard_package', 'hajj')) == 'hajj' ? 'selected' : '' }}>Hajj</option>
                                            <option value="umrah" {{ old('hajj_umrah', session('dashboard_package', 'hajj')) == 'umrah' ? 'selected' : '' }}>Umrah</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="year" id="year_input"
                                            value="{{ old('year', session('dashboard_year', date('Y'))) }}"
                                            class="form-control form-control-sm" placeholder="Year" min="2000" max="2100" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4" id="col_payment_type">
                                <label class="form-label fs-13 fw-semibold">Payment Type <span class="text-danger">*</span></label>
                                <div id="normal_payment_type_radios" class="d-flex gap-3 pt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_bank" value="bank"
                                            {{ old('payment_type', 'bank') == 'bank' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_bank">Bank Transfer</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_cash" value="cash"
                                            {{ old('payment_type') == 'cash' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_cash">Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_cheque" value="cheque"
                                            {{ old('payment_type') == 'cheque' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_cheque">Cheque</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="pay_online" value="online"
                                            {{ old('payment_type') == 'online' ? 'checked' : '' }}>
                                        <label class="form-check-label fs-13" for="pay_online">Online</label>
                                    </div>
                                </div>
                                <div id="jv_payment_type_badge" class="d-none pt-1">
                                    <input class="form-check-input d-none" type="radio" name="payment_type" id="pay_jv" value="jv"
                                        {{ old('payment_type') == 'jv' ? 'checked' : '' }}>
                                    <span class="badge bg-soft-primary text-primary px-3 py-2 fs-12 border border-primary border-opacity-25">
                                        <i class="mdi mdi-book-open-page-variant me-1"></i> Journal Voucher (Adjustment / Transfer)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ROW 2: Bank / Cash Account, Currency, Exchange Rate -->
                        <div class="row g-3 mb-3" id="row_bank_currency">
                            <div class="col-md-6" id="bank_select_group">
                                <label class="form-label fs-13 fw-semibold">Bank</label>
                                <select name="bank_id" id="bank_id_select" class="form-select form-select-sm select2">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id }}" {{ old('bank_id') == $b->id ? 'selected' : '' }}>
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
                                            <option value="{{ $ca->id }}" 
                                                data-pkr="{{ $ca->opening_balance_pkr ?? 0 }}"
                                                data-sar="{{ $ca->opening_balance_sar ?? 0 }}"
                                                data-roe="{{ $ca->opening_balance_roe ?? 1 }}"
                                                {{ old('account_id') == $ca->id ? 'selected' : '' }}>
                                                {{ $ca->account_name }} ({{ strtoupper(str_replace('_', ' ', $ca->account_type)) }}) — Opening: PKR {{ number_format($ca->opening_balance_pkr, 2) }}{{ $ca->opening_balance_sar > 0 ? ' (SAR ' . number_format($ca->opening_balance_sar, 2) . ')' : '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div id="cash_account_balance_hint" class="mt-1 fs-12 fw-semibold text-success"></div>
                            </div>

                            <div class="col-md-6 {{ old('payee_type') === 'jv' ? 'd-none' : '' }}" id="currency_roe_group">
                                <label class="form-label fs-13 fw-semibold">Currency & Exchange Rate (ROE) <span class="text-danger">*</span></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <select name="currency" id="currency_select" class="form-select form-select-sm" required>
                                            <option value="PKR" {{ old('currency', 'PKR') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                            <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">1 SAR =</span>
                                            <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate_input"
                                                value="{{ old('exchange_rate', 76.00) }}" class="form-control form-control-sm text-center fw-bold"
                                                placeholder="76.00" {{ old('payee_type') === 'jv' ? '' : 'required' }}>
                                            <span class="input-group-text bg-white">PKR</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ROW 3: Total Amount, Transaction Type, Reference -->
                        <div class="row g-3 mb-3" id="row_amount_type_ref">
                            <div class="col-md-4" id="col_total_amount">
                                <label class="form-label fs-13 fw-semibold">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text fw-bold bg-white text-primary">PKR</span>
                                    <input type="number" step="any" name="amount" id="main_total_amount" value="{{ old('amount') }}"
                                        class="form-control form-control-sm fw-bold text-dark fs-14" placeholder="Enter Total Amount in PKR" required>
                                </div>
                                <small class="text-muted fs-11 d-block mt-1">Total amount goes in PKR. Live ROE calculates SAR equivalent for ledgers.</small>
                            </div>

                            <div class="col-md-4" id="col_transaction_type">
                                <label class="form-label fs-13 fw-semibold">Transaction Type</label>
                                <select name="transaction_type" class="form-select form-select-sm">
                                    <option value="debit" {{ old('transaction_type', 'debit') == 'debit' ? 'selected' : '' }}>Debit (Payment Out / Expense / Supplier Pay)</option>
                                    <option value="credit" {{ old('transaction_type') == 'credit' ? 'selected' : '' }}>Credit (Payment In / Client Receipt / Income)</option>
                                </select>
                            </div>

                            <div class="col-md-4" id="col_reference">
                                <label class="form-label fs-13 fw-semibold">Reference</label>
                                <input type="text" name="reference_no" id="reference_no_input" value="{{ old('reference_no', $autoRef) }}"
                                    class="form-control form-control-sm" placeholder="DT-EXP-000001">
                            </div>

                            <!-- Full width ROE conversion container placed cleanly on its own line -->
                            <div class="col-12 d-none" id="roe_helper_container">
                                <div id="roe_live_helper" class="p-2 rounded border bg-light fs-12"></div>
                            </div>
                        </div>

                        <!-- ROW 4: Proof of Payment -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fs-13 fw-semibold">Proof of Payment</label>
                                <input type="file" name="proof" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted fs-11">Supported formats: JPG, PNG, PDF (Max 5MB)</small>
                            </div>
                        </div>

                        <!-- ROW 5: Description -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fs-13 fw-semibold">Description / Notes</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Reason for Payment / Details / Remarks">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- SUBMIT BUTTONS -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="mdi mdi-plus-circle me-1"></i> Save Transaction
                            </button>
                            <a href="{{ route('expense.transaction.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".flatpickr-date", { dateFormat: "Y-m-d", allowInput: true });

        // Initialize Select2 with Bootstrap 5 theme
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Type to search...'
            });
        }

        // Auto references
        const expRef = "{{ $autoRef }}";
        const jvRef = "{{ $autoJvRef ?? ('DT-JV-' . substr($autoRef, 7)) }}";
        const refInput = document.getElementById('reference_no_input');

        // Toggle Bank / Cash dropdown based on payment type
        function togglePaymentTypeFields() {
            const isCash = document.getElementById('pay_cash')?.checked;
            const bankGroup = document.getElementById('bank_select_group');
            const cashGroup = document.getElementById('cash_account_group');
            if (isCash) {
                if (bankGroup) bankGroup.classList.add('d-none');
                if (cashGroup) {
                    cashGroup.classList.remove('d-none');
                    if (typeof $ !== 'undefined' && $.fn.select2) {
                        $('#account_id_select').select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Type to search...'
                        });
                    }
                    updateCashAccountBalanceHint();
                }
            } else {
                if (bankGroup) bankGroup.classList.remove('d-none');
                if (cashGroup) cashGroup.classList.add('d-none');
            }
        }

        function updateCashAccountBalanceHint() {
            const select = document.getElementById('account_id_select');
            const hint = document.getElementById('cash_account_balance_hint');
            if (!select || !hint) return;
            const opt = select.options[select.selectedIndex];
            if (select.value && opt) {
                const pkr = parseFloat(opt.getAttribute('data-pkr')) || 0;
                const sar = parseFloat(opt.getAttribute('data-sar')) || 0;
                let text = `<span class="badge bg-soft-success text-success px-2 py-1 fs-12"><i class="mdi mdi-cash-check me-1"></i> Opening Balance: <strong>PKR ${pkr.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>`;
                if (sar > 0) {
                    text += ` / SAR ${sar.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                }
                text += `</span>`;
                hint.innerHTML = text;
            } else {
                hint.innerHTML = '';
            }
        }

        $(document).on('change', '#account_id_select', updateCashAccountBalanceHint);

        document.querySelectorAll('input[name="payment_type"]').forEach(r => {
            r.addEventListener('change', togglePaymentTypeFields);
        });
        togglePaymentTypeFields();

        // Helper to safely render ROE conversion message in dedicated full-width container below inputs
        function setRoeHelperContent(html) {
            const container = document.getElementById('roe_helper_container');
            const el = document.getElementById('roe_live_helper');
            if (!container || !el) return;
            if (html && html.trim() !== '') {
                el.innerHTML = html;
                container.classList.remove('d-none');
            } else {
                el.innerHTML = '';
                container.classList.add('d-none');
            }
        }

        // Toggle Payment Details UI according to Payee Type (JV vs Normal)
        function updatePayeeTypeUI() {
            const payeeType = document.querySelector('input[name="payee_type"]:checked')?.value || 'supplier';
            const isJv = (payeeType === 'jv');
            const normalRadios = document.getElementById('normal_payment_type_radios');
            const jvBadge = document.getElementById('jv_payment_type_badge');
            const rowBankCurrency = document.getElementById('row_bank_currency');
            const colTxType = document.getElementById('col_transaction_type');
            const colAmount = document.getElementById('col_total_amount');
            const colRef = document.getElementById('col_reference');

            if (isJv) {
                if (normalRadios) normalRadios.classList.add('d-none');
                if (jvBadge) {
                    jvBadge.classList.remove('d-none');
                    const payJv = document.getElementById('pay_jv');
                    if (payJv) payJv.checked = true;
                }
                if (rowBankCurrency) rowBankCurrency.classList.add('d-none');
                if (colTxType) colTxType.classList.add('d-none');
                if (colAmount) {
                    colAmount.classList.remove('col-md-4');
                    colAmount.classList.add('col-md-6');
                }
                if (colRef) {
                    colRef.classList.remove('col-md-4');
                    colRef.classList.add('col-md-6');
                }
                if (refInput && (!refInput.value || refInput.value === expRef)) {
                    refInput.value = jvRef;
                }
            } else {
                if (normalRadios) normalRadios.classList.remove('d-none');
                if (jvBadge) jvBadge.classList.add('d-none');
                const checkedPayType = document.querySelector('input[name="payment_type"]:checked');
                if (!checkedPayType || checkedPayType.value === 'jv') {
                    const payBank = document.getElementById('pay_bank');
                    if (payBank) payBank.checked = true;
                }
                if (rowBankCurrency) rowBankCurrency.classList.remove('d-none');
                if (colTxType) colTxType.classList.remove('d-none');
                if (colAmount) {
                    colAmount.classList.remove('col-md-6');
                    colAmount.classList.add('col-md-4');
                }
                if (colRef) {
                    colRef.classList.remove('col-md-6');
                    colRef.classList.add('col-md-4');
                }
                if (refInput && (!refInput.value || refInput.value === jvRef)) {
                    refInput.value = expRef;
                }
                togglePaymentTypeFields();
            }
        }

        // Currency Toggle & ROE Helper (Amount is ALWAYS entered in PKR)
        const mainAmountInput = document.getElementById('main_total_amount');
        const currencySelect = document.getElementById('currency_select');
        const rateInput = document.getElementById('exchange_rate_input');
        const currencyRoeGroup = document.getElementById('currency_roe_group');

        function updateCurrencyAndLiveROE() {
            const isJv = document.querySelector('input[name="payee_type"]:checked')?.value === 'jv';

            // Hide the redundant bottom ROE input when JV is selected:
            if (currencyRoeGroup) {
                if (isJv) {
                    currencyRoeGroup.classList.add('d-none');
                    if (rateInput) {
                        rateInput.removeAttribute('required');
                        const fRoe = parseFloat(document.getElementById('jv_from_roe')?.value) || 76.0;
                        rateInput.value = fRoe;
                    }
                } else {
                    currencyRoeGroup.classList.remove('d-none');
                    if (rateInput) rateInput.setAttribute('required', 'required');
                }
            }

            const roundToTwo = (num) => Math.round((num + Number.EPSILON) * 100) / 100;
            const amtPkr = parseFloat(mainAmountInput?.value) || 0;

            if (isJv) {
                const jvMode = document.querySelector('input[name="jv_mode"]:checked')?.value || 'universal';
                if (jvMode === 'universal') {
                    const fromRoe = parseFloat(document.getElementById('jv_from_roe')?.value) || 76.0;
                    const toRoe = parseFloat(document.getElementById('jv_to_roe')?.value) || 75.0;

                    const fromAmtField = document.getElementById('jv_from_amount_input');
                    const toAmtField   = document.getElementById('jv_to_amount_input');

                    const fromAmt = fromAmtField && fromAmtField.value !== '' ? (parseFloat(fromAmtField.value) || 0) : amtPkr;
                    const toAmt   = toAmtField && toAmtField.value !== '' ? (parseFloat(toAmtField.value) || 0) : fromAmt;

                    const fromSarVal = fromRoe > 0 ? (fromAmt / fromRoe) : 0;
                    const toSarVal = toRoe > 0 ? (toAmt / toRoe) : 0;

                    const fromSar = fromSarVal.toFixed(2);
                    const toSar = toSarVal.toFixed(2);

                    const fromHint = document.getElementById('from_account_sar_hint');
                    const toHint = document.getElementById('to_account_sar_hint');
                    if (fromHint) fromHint.innerHTML = fromAmt > 0 ? `<i class="mdi mdi-calculator me-1"></i>≈ <strong>${Number(fromSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR</strong> (PKR ${Number(fromAmt).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})` : '';
                    if (toHint) toHint.innerHTML = toAmt > 0 ? `<i class="mdi mdi-calculator me-1"></i>≈ <strong>${Number(toSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR</strong> (PKR ${Number(toAmt).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})` : '';

                    if (fromAmt <= 0 && toAmt <= 0) {
                        setRoeHelperContent(`<span class="badge bg-soft-info text-dark fs-12 px-2 py-1 border border-info border-opacity-25">Universal JV Dual ROE (From: ${fromRoe} | To: ${toRoe})</span>`);
                    } else {
                        setRoeHelperContent(`<span class="badge bg-soft-info text-dark fs-12 px-2 py-1 border border-info border-opacity-25"><i class="mdi mdi-swap-horizontal me-1 text-primary"></i> From A/C: <strong class="text-danger">PKR ${Number(fromAmt).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong> (${Number(fromSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR @ ${fromRoe}) &nbsp;➔&nbsp; To A/C: <strong class="text-success">PKR ${Number(toAmt).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong> (${Number(toSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR @ ${toRoe})</span>`);
                    }
                    return;
                } else if (jvMode === 'client_supplier') {
                    const clientRoe = parseFloat(document.getElementById('jv_contra_client_roe')?.value) || 76.0;
                    const supplierRoe = parseFloat(document.getElementById('jv_contra_supplier_roe')?.value) || 75.0;
                    const clientSarVal = clientRoe > 0 ? (amtPkr / clientRoe) : 0;
                    const supplierSarVal = supplierRoe > 0 ? (amtPkr / supplierRoe) : 0;

                    const clientSar = clientSarVal.toFixed(2);
                    const supplierSar = supplierSarVal.toFixed(2);

                    const clientHint = document.getElementById('contra_client_sar_hint');
                    const supplierHint = document.getElementById('contra_supplier_sar_hint');
                    if (clientHint) clientHint.innerHTML = amtPkr > 0 ? `<i class="mdi mdi-calculator me-1"></i>≈ <strong>${Number(clientSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR</strong> (PKR ${Number(amtPkr).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})` : '';
                    if (supplierHint) supplierHint.innerHTML = amtPkr > 0 ? `<i class="mdi mdi-calculator me-1"></i>≈ <strong>${Number(supplierSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR</strong> (PKR ${Number(amtPkr).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})` : '';

                    let toAmtHidden = document.getElementById('jv_to_amount');
                    if (!toAmtHidden) {
                        toAmtHidden = document.createElement('input');
                        toAmtHidden.type = 'hidden';
                        toAmtHidden.name = 'jv_to_amount';
                        toAmtHidden.id = 'jv_to_amount';
                        document.querySelector('form').appendChild(toAmtHidden);
                    }
                    toAmtHidden.value = amtPkr;

                    let fromAmtHidden = document.getElementById('jv_from_amount');
                    if (!fromAmtHidden) {
                        fromAmtHidden = document.createElement('input');
                        fromAmtHidden.type = 'hidden';
                        fromAmtHidden.name = 'jv_from_amount';
                        fromAmtHidden.id = 'jv_from_amount';
                        document.querySelector('form').appendChild(fromAmtHidden);
                    }
                    fromAmtHidden.value = amtPkr;

                    if (amtPkr <= 0) {
                        setRoeHelperContent(`<span class="badge bg-soft-primary text-dark fs-12 px-2 py-1 border border-primary border-opacity-25">Client & Supplier Contra (Client ROE: ${clientRoe} | Supplier ROE: ${supplierRoe})</span>`);
                    } else {
                        setRoeHelperContent(`<span class="badge bg-soft-primary text-dark fs-12 px-2 py-1 border border-primary border-opacity-25"><i class="mdi mdi-account-switch me-1 text-primary"></i> Client: <strong class="text-primary">PKR ${Number(amtPkr).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong> (${Number(clientSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR @ ${clientRoe}) &nbsp;⇄&nbsp; Supplier: <strong class="text-warning">PKR ${Number(amtPkr).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong> (${Number(supplierSar).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR @ ${supplierRoe})</span>`);
                    }
                    return;
                } else if (jvMode === 'client_multi_supplier') {
                    updateCmsCalculations();
                    return;
                }
            }

            const curr = currencySelect.value;

            if (curr === 'PKR') {
                if (!rateInput.value || rateInput.value == 1) {
                    rateInput.value = '76.00';
                }
            } else if (curr === 'SAR') {
                if (!rateInput.value || rateInput.value == 1) {
                    rateInput.value = '76.00';
                }
            }

            // Calculate live conversion preview (Amount is ALWAYS PKR)
            const roe = parseFloat(rateInput?.value) || 76.0;

            if (amtPkr <= 0) {
                setRoeHelperContent('');
            } else if (roe > 1) {
                const foreignAmt = (amtPkr / roe).toFixed(2);
                setRoeHelperContent(`<span class="badge bg-soft-success text-success fs-12 px-2 py-1"><i class="mdi mdi-calculator me-1"></i> ≈ <strong>${Number(foreignAmt).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR</strong> (at 1 SAR = ${roe} PKR)</span>`);
            } else {
                setRoeHelperContent(`<span class="badge bg-soft-secondary text-secondary fs-12 px-2 py-1">PKR Only</span>`);
            }
        }

        currencySelect.addEventListener('change', updateCurrencyAndLiveROE);
        if (mainAmountInput) mainAmountInput.addEventListener('input', function() {
            const fField = document.getElementById('jv_from_amount_input');
            if (fField && (!fField.value || fField.value == 0 || fField.getAttribute('data-linked') !== 'false')) {
                fField.value = this.value;
            }
            updateCurrencyAndLiveROE();
        });

        // Bidirectional SAR Base Converter & Dual Amount Fields
        const jvSarBaseInput = document.getElementById('jv_sar_base_amount');
        const jvFromAmtField = document.getElementById('jv_from_amount_input');
        const jvToAmtField   = document.getElementById('jv_to_amount_input');

        if (jvSarBaseInput) {
            jvSarBaseInput.addEventListener('input', function() {
                const sarVal = parseFloat(this.value) || 0;
                if (sarVal > 0) {
                    const fRoe = parseFloat(document.getElementById('jv_from_roe')?.value) || 76.0;
                    const tRoe = parseFloat(document.getElementById('jv_to_roe')?.value) || 75.0;
                    if (jvFromAmtField) jvFromAmtField.value = (sarVal * fRoe).toFixed(2);
                    if (jvToAmtField) jvToAmtField.value = (sarVal * tRoe).toFixed(2);
                    if (mainAmountInput) mainAmountInput.value = (sarVal * fRoe).toFixed(2);
                }
                updateCurrencyAndLiveROE();
            });
        }

        if (jvFromAmtField) {
            jvFromAmtField.addEventListener('input', function() {
                this.setAttribute('data-linked', 'true');
                if (mainAmountInput) mainAmountInput.value = this.value;
                const fRoe = parseFloat(document.getElementById('jv_from_roe')?.value) || 76.0;
                const tRoe = parseFloat(document.getElementById('jv_to_roe')?.value) || 75.0;
                const fAmt = parseFloat(this.value) || 0;
                if (fAmt > 0 && fRoe > 0) {
                    const sar = fAmt / fRoe;
                    if (jvSarBaseInput) jvSarBaseInput.value = sar.toFixed(2);
                    if (jvToAmtField && jvToAmtField.getAttribute('data-customized') !== 'true') {
                        jvToAmtField.value = (sar * tRoe).toFixed(2);
                    }
                }
                updateCurrencyAndLiveROE();
            });
        }

        if (jvToAmtField) {
            jvToAmtField.addEventListener('input', function() {
                this.setAttribute('data-customized', 'true');
                updateCurrencyAndLiveROE();
            });
        }

        if (rateInput) rateInput.addEventListener('input', updateCurrencyAndLiveROE);
        ['jv_from_roe', 'jv_to_roe', 'jv_contra_client_roe', 'jv_contra_supplier_roe'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', function() {
                if (jvSarBaseInput && parseFloat(jvSarBaseInput.value) > 0) {
                    const sarVal = parseFloat(jvSarBaseInput.value);
                    const fRoe = parseFloat(document.getElementById('jv_from_roe')?.value) || 76.0;
                    const tRoe = parseFloat(document.getElementById('jv_to_roe')?.value) || 75.0;
                    if (jvFromAmtField) jvFromAmtField.value = (sarVal * fRoe).toFixed(2);
                    if (jvToAmtField) jvToAmtField.value = (sarVal * tRoe).toFixed(2);
                    if (mainAmountInput) mainAmountInput.value = (sarVal * fRoe).toFixed(2);
                }
                updateCurrencyAndLiveROE();
            });
        });
        updateCurrencyAndLiveROE();

        // Payee Type Toggle
        const payeeRadios = document.querySelectorAll('.payee-type-radio');
        const supplierSec = document.getElementById('supplierSection');
        const clientSec = document.getElementById('clientSection');
        const staffSec = document.getElementById('staffSection');
        const expenseSec = document.getElementById('expenseHeadSection');
        const loanSec = document.getElementById('loanSection');
        const selfSec = document.getElementById('selfSection');
        const jvSec = document.getElementById('jvSection');

        function switchPayeeSection(val) {
            if (supplierSec) supplierSec.classList.add('d-none');
            if (clientSec) clientSec.classList.add('d-none');
            if (staffSec) staffSec.classList.add('d-none');
            if (expenseSec) expenseSec.classList.add('d-none');
            if (loanSec) loanSec.classList.add('d-none');
            if (selfSec) selfSec.classList.add('d-none');
            if (jvSec) jvSec.classList.add('d-none');

            if (val === 'supplier' && supplierSec) {
                supplierSec.classList.remove('d-none');
                if (refInput && (!refInput.value || refInput.value === jvRef)) refInput.value = expRef;
            } else if (val === 'client' && clientSec) {
                clientSec.classList.remove('d-none');
                if (refInput && (!refInput.value || refInput.value === jvRef)) refInput.value = expRef;
            } else if (val === 'staff' && staffSec) {
                staffSec.classList.remove('d-none');
                if (refInput && (!refInput.value || refInput.value === jvRef)) refInput.value = expRef;
            } else if (val === 'expense' && expenseSec) {
                expenseSec.classList.remove('d-none');
                if (refInput && (!refInput.value || refInput.value === jvRef)) refInput.value = expRef;
            } else if (val === 'loan' && loanSec) {
                loanSec.classList.remove('d-none');
                if (refInput && (!refInput.value || refInput.value === jvRef)) refInput.value = expRef;
            } else if (val === 'self' && selfSec) {
                selfSec.classList.remove('d-none');
                if (refInput && (!refInput.value || refInput.value === jvRef)) refInput.value = expRef;
            } else if (val === 'jv' && jvSec) {
                jvSec.classList.remove('d-none');
                if (refInput) refInput.value = jvRef;
            }
            updatePayeeTypeUI();
            updateCurrencyAndLiveROE();
        }

        payeeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) switchPayeeSection(this.value);
            });
            radio.addEventListener('click', function() {
                if (this.checked) switchPayeeSection(this.value);
            });
        });

        // Initial trigger for checked payee radio on page load
        const checkedPayeeOnLoad = document.querySelector('input[name="payee_type"]:checked');
        if (checkedPayeeOnLoad) {
            switchPayeeSection(checkedPayeeOnLoad.value);
        }

        // JV Mode Radios Switcher
        const jvModeRadios = document.querySelectorAll('.jv-mode-radio');
        const jvBoxUniv = document.getElementById('jv_box_universal');
        const jvBoxSingle = document.getElementById('jv_box_single');
        const jvBoxContra = document.getElementById('jv_box_contra');
        const jvBoxMulti = document.getElementById('jv_box_multi');
        const jvBoxCms = document.getElementById('jv_box_cms');

        function switchJvBox(mode) {
            if (jvBoxUniv) jvBoxUniv.classList.add('d-none');
            if (jvBoxSingle) jvBoxSingle.classList.add('d-none');
            if (jvBoxContra) jvBoxContra.classList.add('d-none');
            if (jvBoxMulti) jvBoxMulti.classList.add('d-none');
            if (jvBoxCms) jvBoxCms.classList.add('d-none');

            if (mode === 'universal' && jvBoxUniv) jvBoxUniv.classList.remove('d-none');
            else if (mode === 'single_party' && jvBoxSingle) jvBoxSingle.classList.remove('d-none');
            else if (mode === 'client_supplier' && jvBoxContra) jvBoxContra.classList.remove('d-none');
            else if (mode === 'multi_client' && jvBoxMulti) jvBoxMulti.classList.remove('d-none');
            else if (mode === 'client_multi_supplier' && jvBoxCms) jvBoxCms.classList.remove('d-none');

            updatePayeeTypeUI();
            updateCurrencyAndLiveROE();
        }

        jvModeRadios.forEach(r => {
            r.addEventListener('change', function() {
                if (this.checked) switchJvBox(this.value);
            });
            r.addEventListener('click', function() {
                if (this.checked) switchJvBox(this.value);
            });
        });

        const checkedJvModeOnLoad = document.querySelector('input[name="jv_mode"]:checked');
        if (checkedJvModeOnLoad) {
            switchJvBox(checkedJvModeOnLoad.value);
        }

        // Multi-Client Add Row
        let clientRowCount = 1;
        const btnAddClientRow = document.getElementById('btnAddClientRow');
        const multiClientTbody = document.getElementById('multiClientTbody');
        if (btnAddClientRow && multiClientTbody) {
            btnAddClientRow.addEventListener('click', function() {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="jv_clients[${clientRowCount}][client_id]" class="form-select form-select-sm">
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="jv_clients[${clientRowCount}][type]" class="form-select form-select-sm">
                            <option value="credit">Credit (Payment Received)</option>
                            <option value="debit">Debit (Charge / Penalty)</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="jv_clients[${clientRowCount}][amount]" class="form-control form-control-sm text-end fw-bold" placeholder="0.00">
                    </td>
                    <td>
                        <input type="text" name="jv_clients[${clientRowCount}][notes]" class="form-control form-control-sm" placeholder="Reason for adjustment">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row"><i class="mdi mdi-close-circle fs-16"></i></button>
                    </td>
                `;
                multiClientTbody.appendChild(tr);
                clientRowCount++;
            });

            multiClientTbody.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-row')) {
                    e.target.closest('tr').remove();
                }
            });
        }

        // ========================================================
        // 1 CLIENT TO MULTI-SUPPLIER SPLIT JV LOGIC
        // ========================================================
        function updateCmsCalculations() {
            const clientRoeInput = document.getElementById('jv_cms_client_roe');
            const clientTotalPkrInput = document.getElementById('jv_cms_total_pkr');
            const clientSarBadge = document.getElementById('cms_client_sar_badge');

            const clientRoe = parseFloat(clientRoeInput?.value) || 76.0;
            const clientPkr = parseFloat(clientTotalPkrInput?.value) || 0;

            const clientSar = clientRoe > 0 ? (clientPkr / clientRoe) : 0;
            if (clientSarBadge) {
                clientSarBadge.innerHTML = clientPkr > 0
                    ? `≈ <strong>${Number(clientSar.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} SAR</strong> (@ ROE ${clientRoe})`
                    : '≈ 0.00 SAR';
            }

            // Sync with main total amount input
            if (mainAmountInput && clientPkr > 0) {
                mainAmountInput.value = clientPkr;
            }

            let totalAllocatedPkr = 0.0;
            let totalAllocatedSar = 0.0;

            const rows = document.querySelectorAll('#cmsSuppliersTbody tr');
            rows.forEach((r, idx) => {
                const amtInput = r.querySelector('.cms-sup-amount');
                const roeInput = r.querySelector('.cms-sup-roe');
                const sarSpan = r.querySelector('.cms-sup-sar');
                const removeBtn = r.querySelector('.btn-remove-cms-row');

                if (removeBtn) {
                    removeBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
                }

                const rAmt = parseFloat(amtInput?.value) || 0;
                const rRoe = parseFloat(roeInput?.value) || 75.0;
                const rSar = rRoe > 0 ? (rAmt / rRoe) : 0;

                if (sarSpan) {
                    sarSpan.textContent = rAmt > 0 ? (rSar.toFixed(2) + ' SAR') : '0.00 SAR';
                }

                totalAllocatedPkr += rAmt;
                totalAllocatedSar += rSar;
            });

            const totalPkrSpan = document.getElementById('cms_total_allocated_pkr');
            const totalSarSpan = document.getElementById('cms_total_allocated_sar');
            const statusSpan = document.getElementById('cms_balance_status');

            if (totalPkrSpan) totalPkrSpan.textContent = 'PKR ' + Number(totalAllocatedPkr.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            if (totalSarSpan) totalSarSpan.textContent = Number(totalAllocatedSar.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SAR';

            if (statusSpan) {
                const diff = Math.round((clientPkr - totalAllocatedPkr) * 100) / 100;
                if (clientPkr > 0 && Math.abs(diff) < 0.01) {
                    statusSpan.className = 'badge bg-soft-success text-success fs-11';
                    statusSpan.innerHTML = '<i class="mdi mdi-check-circle me-1"></i> Perfectly Balanced: PKR ' + Number(clientPkr.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (clientPkr > 0) {
                    if (diff > 0) {
                        statusSpan.className = 'badge bg-soft-danger text-danger fs-11';
                        statusSpan.innerHTML = '<i class="mdi mdi-alert-circle me-1"></i> Remaining to Allocate: PKR ' + Number(diff.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    } else {
                        statusSpan.className = 'badge bg-soft-danger text-danger fs-11';
                        statusSpan.innerHTML = '<i class="mdi mdi-alert-circle me-1"></i> Overallocated by: PKR ' + Number(Math.abs(diff).toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                } else {
                    statusSpan.className = 'badge bg-soft-secondary text-muted fs-11';
                    statusSpan.innerHTML = 'Enter Client PKR Amount';
                }
            }

            if (document.querySelector('input[name="payee_type"]:checked')?.value === 'jv' && document.querySelector('input[name="jv_mode"]:checked')?.value === 'client_multi_supplier') {
                setRoeHelperContent(`<span class="badge bg-soft-warning text-dark border border-warning fs-12 px-2 py-1"><i class="mdi mdi-call-split me-1 text-warning"></i> 1 Client (PKR ${Number(clientPkr.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}) ➔ Split to ${rows.length} Supplier(s) (Allocated: PKR ${Number(totalAllocatedPkr.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})</span>`);
            }
        }

        let supRowCount = 1;
        const btnAddSupplierRow = document.getElementById('btnAddSupplierRow');
        const cmsSuppliersTbody = document.getElementById('cmsSuppliersTbody');
        if (btnAddSupplierRow && cmsSuppliersTbody) {
            btnAddSupplierRow.addEventListener('click', function() {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="jv_suppliers[${supRowCount}][supplier_id]" class="form-select form-select-sm cms-supplier-select">
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ ucfirst($s->type ?? 'supplier') }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="jv_suppliers[${supRowCount}][amount]" class="form-control form-control-sm text-end fw-bold cms-sup-amount" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="1" name="jv_suppliers[${supRowCount}][roe]" class="form-control form-control-sm text-center fw-bold cms-sup-roe" value="75.00">
                    </td>
                    <td class="text-end">
                        <span class="badge bg-soft-warning text-dark border border-warning fs-12 cms-sup-sar">0.00 SAR</span>
                    </td>
                    <td>
                        <input type="text" name="jv_suppliers[${supRowCount}][notes]" class="form-control form-control-sm" placeholder="Split allocation remark">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-cms-row"><i class="mdi mdi-close-circle fs-16"></i></button>
                    </td>
                `;
                cmsSuppliersTbody.appendChild(tr);
                supRowCount++;
                updateCmsCalculations();
            });

            cmsSuppliersTbody.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-cms-row')) {
                    e.target.closest('tr').remove();
                    updateCmsCalculations();
                }
            });

            cmsSuppliersTbody.addEventListener('input', function(e) {
                if (e.target.classList.contains('cms-sup-amount') || e.target.classList.contains('cms-sup-roe')) {
                    updateCmsCalculations();
                }
            });
        }

        const clientTotalPkrInput = document.getElementById('jv_cms_total_pkr');
        const clientRoeInput = document.getElementById('jv_cms_client_roe');
        if (clientTotalPkrInput) clientTotalPkrInput.addEventListener('input', updateCmsCalculations);
        if (clientRoeInput) clientRoeInput.addEventListener('input', updateCmsCalculations);

        const btnAutoDistribute = document.getElementById('btnAutoDistributeSuppliers');
        if (btnAutoDistribute && cmsSuppliersTbody) {
            btnAutoDistribute.addEventListener('click', function() {
                const totalPkr = parseFloat(document.getElementById('jv_cms_total_pkr')?.value) || 0;
                const rows = cmsSuppliersTbody.querySelectorAll('tr');
                if (totalPkr > 0 && rows.length > 0) {
                    const equalShare = (totalPkr / rows.length).toFixed(2);
                    rows.forEach((r) => {
                        const amtInput = r.querySelector('.cms-sup-amount');
                        if (amtInput) amtInput.value = equalShare;
                    });
                    updateCmsCalculations();
                }
            });
        }

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