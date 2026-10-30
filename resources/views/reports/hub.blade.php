@extends('layout.master')

@section('title', 'Financial Reports & Statements Hub')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">

<style>
    .hub-metric-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 16px 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .hub-metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }
    .hub-metric-card.active {
        border-color: #2563eb !important;
        background: #f8fafc;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }
    .hub-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .nav-pills-hub .nav-link {
        font-weight: 600;
        font-size: 13px;
        padding: 10px 18px;
        border-radius: 8px;
        color: #475569;
        border: 1px solid #e2e8f0;
        background: #fff;
        margin-right: 8px;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    .nav-pills-hub .nav-link:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .nav-pills-hub .nav-link.active {
        background-color: #2563eb !important;
        color: #fff !important;
        border-color: #2563eb !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }
    .hub-filter-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .date-preset-btn {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        transition: background 0.15s;
    }
    .date-preset-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            {{-- Top Header --}}
            <div class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom mb-4">
                <div>
                    <h4 class="fs-18 fw-bold m-0 text-dark">
                        <i class="ri-dashboard-3-line text-primary me-2"></i> Financial Reports & Ledgers Hub
                    </h4>
                    <span class="text-muted fs-12">
                        Centralized Filter Center — Bank Statements, Cash Books, Client Ledgers, Supplier Ledgers & Expense Reports in one single place.
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('reports.balance-sheet') }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-book-open-page-variant me-1"></i> Balance Sheet
                    </a>
                    <a href="{{ route('reports.cash-flow') }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-swap-vertical-circle me-1"></i> Cash Flow
                    </a>
                    <a href="{{ route('report.profit-loss') }}" class="btn btn-outline-info btn-sm">
                        <i class="mdi mdi-chart-line me-1"></i> Profit & Loss (P&L)
                    </a>
                    <a href="{{ route('reports.cash') }}" class="btn btn-outline-success btn-sm">
                        <i class="mdi mdi-cash-multiple me-1"></i> Cash Book
                    </a>
                    <a href="{{ route('bank-transfer.report') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-bank me-1"></i> Bank Report
                    </a>
                    <a href="{{ route('expense.transaction.index') }}" class="btn btn-outline-danger btn-sm">
                        <i class="mdi mdi-receipt me-1"></i> Transactions
                    </a>
                </div>
            </div>

            {{-- 5 Metric Switcher Cards --}}
            <div class="row g-3 mb-4">
                {{-- 1. Bank Statements --}}
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="hub-metric-card border-start border-4 border-primary {{ $activeTab === 'bank' ? 'active' : '' }}" onclick="switchHubTab('bank')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="hub-icon-box bg-soft-primary text-primary">
                                <i class="ri-bank-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Bank Statements</div>
                                <div class="fs-16 fw-bold text-dark mt-1">{{ count($banks) }} Bank Account(s)</div>
                                <div class="fs-11 text-primary mt-1 fw-semibold">
                                    <i class="ri-filter-3-line me-1"></i> Statement & Transfer Filter
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Cash Book --}}
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="hub-metric-card border-start border-4 border-success {{ $activeTab === 'cash' ? 'active' : '' }}" onclick="switchHubTab('cash')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="hub-icon-box bg-soft-success text-success">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Cash Book & Report</div>
                                <div class="fs-16 fw-bold text-dark mt-1">{{ count($cashAccounts) }} Cash Account(s)</div>
                                <div class="fs-11 text-success mt-1 fw-semibold">
                                    <i class="ri-filter-3-line me-1"></i> Inflows, Outflows & Balances
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Client Ledger --}}
                <div class="col-xl col-md-4 col-sm-6">
                    <div class="hub-metric-card border-start border-4 border-info {{ $activeTab === 'client' ? 'active' : '' }}" onclick="switchHubTab('client')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="hub-icon-box bg-soft-info text-info">
                                <i class="ri-user-star-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Client Ledger (Dr/Cr)</div>
                                <div class="fs-16 fw-bold text-dark mt-1">{{ count($clients) }} Active Client(s)</div>
                                <div class="fs-11 text-info mt-1 fw-semibold">
                                    <i class="ri-filter-3-line me-1"></i> Debit / Credit Statement
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Supplier Ledger --}}
                <div class="col-xl col-md-6 col-sm-6">
                    <div class="hub-metric-card border-start border-4 border-warning {{ $activeTab === 'supplier' ? 'active' : '' }}" onclick="switchHubTab('supplier')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="hub-icon-box bg-soft-warning text-warning">
                                <i class="ri-building-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Supplier Ledger</div>
                                <div class="fs-16 fw-bold text-dark mt-1">{{ count($suppliers) }} Supplier(s)</div>
                                <div class="fs-11 text-warning mt-1 fw-semibold">
                                    <i class="ri-filter-3-line me-1"></i> Payables & Disbursement
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. Staff Ledger --}}
                <div class="col-xl col-md-6 col-sm-6">
                    <div class="hub-metric-card border-start border-4 {{ $activeTab === 'staff' ? 'active' : '' }}" onclick="switchHubTab('staff')" style="border-left-color: #8b5cf6 !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="hub-icon-box" style="background: #f5f3ff; color: #8b5cf6;">
                                <i class="ri-user-settings-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-11 fw-bold text-uppercase">Staff Ledger</div>
                                <div class="fs-16 fw-bold text-dark mt-1">{{ count($staffMembers) }} Staff Member(s)</div>
                                <div class="fs-11 mt-1 fw-semibold" style="color: #8b5cf6;">
                                    <i class="ri-filter-3-line me-1"></i> Salary & Advance Ledger
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Pills Navigation --}}
            <ul class="nav nav-pills nav-pills-hub mb-3" id="hubTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'bank' ? 'active' : '' }}" id="tab-btn-bank" data-bs-toggle="pill" data-bs-target="#pane-bank" type="button" role="tab">
                        <i class="ri-bank-line me-1"></i> Bank Transaction & Statement Reports Filter
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'cash' ? 'active' : '' }}" id="tab-btn-cash" data-bs-toggle="pill" data-bs-target="#pane-cash" type="button" role="tab">
                        <i class="ri-wallet-3-line me-1"></i> Cash Book & Cash Report
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'client' ? 'active' : '' }}" id="tab-btn-client" data-bs-toggle="pill" data-bs-target="#pane-client" type="button" role="tab">
                        <i class="ri-user-star-line me-1"></i> Client Ledger Filter (Dr / Cr)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'supplier' ? 'active' : '' }}" id="tab-btn-supplier" data-bs-toggle="pill" data-bs-target="#pane-supplier" type="button" role="tab">
                        <i class="ri-building-line me-1"></i> Search Supplier Ledger
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'staff' ? 'active' : '' }}" id="tab-btn-staff" data-bs-toggle="pill" data-bs-target="#pane-staff" type="button" role="tab">
                        <i class="ri-user-settings-line me-1" style="color: #8b5cf6;"></i> Staff Ledger Filter
                    </button>
                </li>
            </ul>

            {{-- Main Tab Panes Content --}}
            <div class="tab-content" id="hubTabContent">

                {{-- ======================================================== --}}
                {{-- TAB 1: BANK TRANSACTION & STATEMENT REPORTS FILTER --}}
                {{-- ======================================================== --}}
                <div class="tab-pane fade {{ $activeTab === 'bank' ? 'show active' : '' }}" id="pane-bank" role="tabpanel">
                    <div class="card hub-filter-card">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark m-0">
                                    <i class="mdi mdi-bank text-primary me-2"></i> Bank Transaction & Statement Reports Filter
                                </h5>
                                <p class="text-muted fs-12 mb-0 mt-1">Audit bank statement running ledger, inter-bank transfers, client deposits, and supplier disbursements.</p>
                            </div>
                            <span class="badge bg-soft-primary text-primary px-3 py-2 fs-12">
                                <i class="ri-bank-line me-1"></i> Bank Module
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('bank-transfer.report') }}" method="GET" id="form-bank-report">
                                <div class="row g-3">
                                    {{-- Bank Account Selection --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold fs-13">Bank Account</label>
                                        <select name="bank_id" class="form-select form-select-sm">
                                            <option value="">-- All Bank Accounts --</option>
                                            @foreach ($banks as $b)
                                                <option value="{{ $b->id }}" {{ request('bank_id') == $b->id ? 'selected' : '' }}>
                                                    {{ $b->name }} @if(!empty($b->account_name)) ({{ $b->account_name }}) @endif - {{ $b->currency ?? 'PKR' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Type Filter --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold fs-13">Transaction Type</label>
                                        <select name="type" class="form-select form-select-sm">
                                            <option value="all">All Transactions</option>
                                            <option value="opening_balance">Opening Balance (PKR)</option>
                                            <option value="deposit">Deposit (Client Receipt / Inward)</option>
                                            <option value="transfer">Transfer (Inter-Bank Transfer)</option>
                                            <option value="withdrawal">Withdrawal / Payment</option>
                                            <option value="expense">Expense / Supplier Payment</option>
                                            <option value="jv">JV Adjustment / Journal Voucher</option>
                                        </select>
                                    </div>

                                    {{-- Currency Filter --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold fs-13">Currency</label>
                                        <select name="currency" class="form-select form-select-sm">
                                            <option value="">All Currencies</option>
                                            <option value="PKR" selected>PKR (Pakistani Rupee)</option>
                                            <option value="SAR">SAR (Saudi Riyal)</option>
                                            <option value="USD">USD (US Dollar)</option>
                                        </select>
                                    </div>

                                    {{-- From Date --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">From Date</label>
                                        <input type="date" name="from_date" id="bank_from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                                    </div>

                                    {{-- To Date --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">To Date</label>
                                        <input type="date" name="to_date" id="bank_to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                                    </div>

                                    {{-- Search Keyword --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">Search Description / Reference</label>
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Ref no, cheque #, remarks, client, supplier..." value="{{ request('search') }}">
                                    </div>

                                    {{-- Quick Date Presets Bar --}}
                                    <div class="col-12 pt-1">
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <span class="fs-11 text-muted fw-bold me-1">Date Presets:</span>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('bank_from_date', 'bank_to_date', 'today')">Today</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('bank_from_date', 'bank_to_date', 'this_week')">This Week</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('bank_from_date', 'bank_to_date', 'this_month')">This Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('bank_from_date', 'bank_to_date', 'last_month')">Last Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('bank_from_date', 'bank_to_date', 'this_year')">This Year</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('bank_from_date', 'bank_to_date', 'clear')">All Time</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <a href="{{ route('bank.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="mdi mdi-arrow-left me-1"></i> Back to Banks
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                        <i class="mdi mdi-file-document-outline me-1"></i> Generate Bank Statement Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- TAB 2: CASH BOOK & CASH REPORT --}}
                {{-- ======================================================== --}}
                <div class="tab-pane fade {{ $activeTab === 'cash' ? 'show active' : '' }}" id="pane-cash" role="tabpanel">
                    <div class="card hub-filter-card">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark m-0">
                                    <i class="mdi mdi-cash-multiple text-success me-2"></i> Cash Book & Cash Report Filter
                                </h5>
                                <p class="text-muted fs-12 mb-0 mt-1">Audit cash payments, client receipts, supplier advances, staff salaries, and cash account opening balances.</p>
                            </div>
                            <span class="badge bg-soft-success text-success px-3 py-2 fs-12">
                                <i class="ri-wallet-3-line me-1"></i> Cash Book Register
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('reports.cash') }}" method="GET" id="form-cash-report">
                                <div class="row g-3">
                                    {{-- Flow (Cash In / Cash Out) --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold fs-13">Cash Flow Direction</label>
                                        <select name="flow" class="form-select form-select-sm">
                                            <option value="all">All Activity (Cash In & Cash Out)</option>
                                            <option value="inflow">Inflows Only (Cash In / Receipts / Credits)</option>
                                            <option value="outflow">Outflows Only (Cash Out / Expenses / Debits)</option>
                                        </select>
                                    </div>

                                    {{-- Package Season --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold fs-13">Hajj / Umrah Season</label>
                                        <select name="package_type" class="form-select form-select-sm">
                                            <option value="all">Consolidated (Hajj & Umrah)</option>
                                            <option value="umrah" {{ session('dashboard_package') === 'umrah' ? 'selected' : '' }}>Umrah Only</option>
                                            <option value="hajj" {{ session('dashboard_package') === 'hajj' ? 'selected' : '' }}>Hajj Only</option>
                                        </select>
                                    </div>

                                    {{-- Category --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold fs-13">Transaction Category</label>
                                        <select name="category" class="form-select form-select-sm">
                                            <option value="all">All Categories</option>
                                            <option value="opening_balance">Cash Account Opening Balances</option>
                                            <option value="booking_payment">Client Receipts (Booking Payments)</option>
                                            <option value="supplier_payment">Supplier Payments</option>
                                            <option value="client_refund">Client Refunds</option>
                                            <option value="staff_salary">Staff Salaries & Advances</option>
                                            <option value="office_expense">Office & Admin Expenses</option>
                                        </select>
                                    </div>

                                    {{-- Client Filter --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold fs-13">Client (Optional)</label>
                                        <select name="client_id" class="form-select form-select-sm">
                                            <option value="">-- All Clients --</option>
                                            @foreach ($clients as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}{{ $c->phone ? ' (' . $c->phone . ')' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- From Date --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">From Date</label>
                                        <input type="date" name="date_from" id="cash_from_date" class="form-control form-control-sm">
                                    </div>

                                    {{-- To Date --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold fs-13">To Date</label>
                                        <input type="date" name="date_to" id="cash_to_date" class="form-control form-control-sm">
                                    </div>

                                    {{-- Search Keyword --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">Search Keyword</label>
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Voucher #, party name, note, amount...">
                                    </div>

                                    {{-- Quick Date Presets Bar --}}
                                    <div class="col-12 pt-1">
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <span class="fs-11 text-muted fw-bold me-1">Date Presets:</span>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('cash_from_date', 'cash_to_date', 'today')">Today</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('cash_from_date', 'cash_to_date', 'this_week')">This Week</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('cash_from_date', 'cash_to_date', 'this_month')">This Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('cash_from_date', 'cash_to_date', 'last_month')">Last Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('cash_from_date', 'cash_to_date', 'this_year')">This Year</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('cash_from_date', 'cash_to_date', 'clear')">All Time</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <a href="{{ route('reports.cash') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="mdi mdi-refresh me-1"></i> View Live Cash Book
                                    </a>
                                    <button type="submit" class="btn btn-success px-4 shadow-sm">
                                        <i class="mdi mdi-cash-register me-1"></i> Generate Cash Book Statement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- TAB 3: CLIENT LEDGER FILTER (DR / CR) --}}
                {{-- ======================================================== --}}
                <div class="tab-pane fade {{ $activeTab === 'client' ? 'show active' : '' }}" id="pane-client" role="tabpanel">
                    <div class="card hub-filter-card">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark m-0">
                                    <i class="mdi mdi-book-open-page-variant text-info me-2"></i> Client Ledger Filter (Dr / Cr)
                                </h5>
                                <p class="text-muted fs-12 mb-0 mt-1">Generate complete double-entry statement of account with billed booking charges, payments received, and running balance.</p>
                            </div>
                            <span class="badge bg-soft-info text-info px-3 py-2 fs-12">
                                <i class="ri-user-star-line me-1"></i> Client Double-Entry Statement
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('transaction.ledger.view') }}" method="GET" id="form-client-ledger">
                                <div class="row g-3">
                                    {{-- Client Dropdown --}}
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold fs-13">
                                            Select Client <span class="text-danger">*</span>
                                        </label>
                                        <select name="client_id" class="form-select form-select-sm" required>
                                            <option value="">-- Choose Client --</option>
                                            <option value="all">🌟 All Clients (Consolidated Ledger Statement)</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                                    {{ $client->name }} {{ $client->company_name ? ' (' . $client->company_name . ')' : '' }} {{ $client->phone ? ' — ' . $client->phone : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted fs-11">Select a specific client or choose "All Clients" for full agency audit.</small>
                                    </div>

                                    {{-- From Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">From Date</label>
                                        <input type="date" name="from_date" id="client_from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                                    </div>

                                    {{-- To Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">To Date</label>
                                        <input type="date" name="to_date" id="client_to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                                    </div>

                                    {{-- Quick Date Presets Bar --}}
                                    <div class="col-12 pt-1">
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <span class="fs-11 text-muted fw-bold me-1">Date Presets:</span>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('client_from_date', 'client_to_date', 'today')">Today</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('client_from_date', 'client_to_date', 'this_week')">This Week</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('client_from_date', 'client_to_date', 'this_month')">This Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('client_from_date', 'client_to_date', 'last_month')">Last Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('client_from_date', 'client_to_date', 'this_year')">This Year</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('client_from_date', 'client_to_date', 'clear')">All Time</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <a href="{{ route('transaction.ledger.filter') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="mdi mdi-filter-variant me-1"></i> Dedicated Client Filter
                                    </a>
                                    <button type="submit" class="btn btn-info px-4 shadow-sm text-white fw-bold">
                                        <i class="mdi mdi-book-open-page-variant me-1"></i> View Client Ledger Statement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- TAB 4: SEARCH SUPPLIER LEDGER --}}
                {{-- ======================================================== --}}
                <div class="tab-pane fade {{ $activeTab === 'supplier' ? 'show active' : '' }}" id="pane-supplier" role="tabpanel">
                    <div class="card hub-filter-card">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark m-0">
                                    <i class="mdi mdi-truck-delivery text-warning me-2"></i> Search Supplier Ledger
                                </h5>
                                <p class="text-muted fs-12 mb-0 mt-1">Detailed vendor payable statements, booking payables, journal voucher adjustments, and debit payments.</p>
                            </div>
                            <span class="badge bg-soft-warning text-warning px-3 py-2 fs-12">
                                <i class="ri-building-line me-1"></i> Vendor Ledger
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('supplier.ledger.view') }}" method="GET" id="form-supplier-ledger">
                                <div class="row g-3">
                                    {{-- Supplier Dropdown --}}
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold fs-13">
                                            Select Supplier <span class="text-danger">*</span>
                                        </label>
                                        <select name="supplier_id" class="form-select form-select-sm" required>
                                            <option value="">-- Choose Supplier --</option>
                                            <option value="all">🌟 All Suppliers (Consolidated Statement)</option>
                                            @foreach ($suppliers as $s)
                                                <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                                    {{ $s->name }} @if(!empty($s->contact_person)) ({{ $s->contact_person }}) @endif @if(!empty($s->phone)) — {{ $s->phone }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted fs-11">Filter transaction ledger for an airline, hotel chain, visa provider, or transport supplier.</small>
                                    </div>

                                    {{-- From Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">From Date</label>
                                        <input type="date" name="from_date" id="supplier_from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                                    </div>

                                    {{-- To Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">To Date</label>
                                        <input type="date" name="to_date" id="supplier_to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                                    </div>

                                    {{-- Quick Date Presets Bar --}}
                                    <div class="col-12 pt-1">
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <span class="fs-11 text-muted fw-bold me-1">Date Presets:</span>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('supplier_from_date', 'supplier_to_date', 'today')">Today</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('supplier_from_date', 'supplier_to_date', 'this_week')">This Week</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('supplier_from_date', 'supplier_to_date', 'this_month')">This Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('supplier_from_date', 'supplier_to_date', 'last_month')">Last Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('supplier_from_date', 'supplier_to_date', 'this_year')">This Year</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('supplier_from_date', 'supplier_to_date', 'clear')">All Time</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <a href="{{ route('supplier.ledger.filter') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="mdi mdi-filter-variant me-1"></i> Dedicated Supplier Filter
                                    </a>
                                    <button type="submit" class="btn btn-warning px-4 shadow-sm text-dark fw-bold">
                                        <i class="mdi mdi-book-open-page-variant me-1"></i> View Supplier Ledger
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- TAB 5: STAFF LEDGER FILTER --}}
                {{-- ======================================================== --}}
                <div class="tab-pane fade {{ $activeTab === 'staff' ? 'show active' : '' }}" id="pane-staff" role="tabpanel">
                    <div class="card hub-filter-card">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark m-0">
                                    <i class="mdi mdi-badge-account-horizontal text-secondary me-2"></i> Staff Ledger Statement Filter
                                </h5>
                                <p class="text-muted fs-12 mb-0 mt-1">Complete statement of salaries accrued, monthly payrolls, salary advances, payments disbursed, and outstanding net balance.</p>
                            </div>
                            <span class="badge bg-soft-secondary text-secondary px-3 py-2 fs-12">
                                <i class="ri-user-star-line me-1"></i> Staff Statement
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('staff.ledger') }}" method="POST" id="form-staff-ledger">
                                @csrf
                                <div class="row g-3">
                                    {{-- Staff Member Dropdown --}}
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold fs-13">
                                            Select Staff Member <span class="text-danger">*</span>
                                        </label>
                                        <select name="staff_id" class="form-select form-select-sm" required>
                                            <option value="">-- Choose Staff Member --</option>
                                            @foreach ($staffMembers as $s)
                                                <option value="{{ $s->id }}" {{ request('staff_id') == $s->id ? 'selected' : '' }}>
                                                    {{ $s->first_name }} {{ $s->last_name }} 
                                                    @if(!empty($s->employee_id)) [{{ $s->employee_id }}] @endif
                                                    @if(!empty($s->designation)) — {{ is_object($s->designation) ? $s->designation->name : $s->designation }} @endif
                                                    @if(!empty($s->department)) ({{ is_object($s->department) ? $s->department->name : $s->department }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted fs-11">Filter individual employee payroll credits, cash advances, and salary payment ledgers.</small>
                                    </div>

                                    {{-- From Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">From Date</label>
                                        <input type="date" name="from_date" id="staff_from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                                    </div>

                                    {{-- To Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold fs-13">To Date</label>
                                        <input type="date" name="to_date" id="staff_to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                                    </div>

                                    {{-- Quick Date Presets Bar --}}
                                    <div class="col-12 pt-1">
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <span class="fs-11 text-muted fw-bold me-1">Date Presets:</span>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('staff_from_date', 'staff_to_date', 'today')">Today</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('staff_from_date', 'staff_to_date', 'this_week')">This Week</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('staff_from_date', 'staff_to_date', 'this_month')">This Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('staff_from_date', 'staff_to_date', 'last_month')">Last Month</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('staff_from_date', 'staff_to_date', 'this_year')">This Year</button>
                                            <button type="button" class="date-preset-btn" onclick="setDatePreset('staff_from_date', 'staff_to_date', 'clear')">All Time</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <a href="{{ route('staff.ledger.filter') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="mdi mdi-filter-variant me-1"></i> Dedicated Staff Filter
                                    </a>
                                    <button type="submit" class="btn btn-secondary px-4 shadow-sm text-white fw-bold">
                                        <i class="mdi mdi-book-open-page-variant me-1"></i> View Staff Ledger
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

<script>
    function switchHubTab(tabName) {
        // Activate pill button
        const btn = document.getElementById('tab-btn-' + tabName);
        if (btn) {
            const tabTrigger = new bootstrap.Tab(btn);
            tabTrigger.show();
        }

        // Highlight metric card
        document.querySelectorAll('.hub-metric-card').forEach(card => {
            card.classList.remove('active');
        });
        const activeCard = document.querySelector(`.hub-metric-card[onclick*="${tabName}"]`);
        if (activeCard) {
            activeCard.classList.add('active');
        }

        // Update URL query string without reloading page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    // Keep cards in sync when user clicks on nav pill tabs directly
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('#hubTabs button[data-bs-toggle="pill"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                const targetId = e.target.getAttribute('id'); // e.g. tab-btn-expense
                const tabName = targetId.replace('tab-btn-', '');
                document.querySelectorAll('.hub-metric-card').forEach(card => card.classList.remove('active'));
                const activeCard = document.querySelector(`.hub-metric-card[onclick*="${tabName}"]`);
                if (activeCard) activeCard.classList.add('active');

                const url = new URL(window.location);
                url.searchParams.set('tab', tabName);
                window.history.replaceState({}, '', url);
            });
        });
    });

    function setDatePreset(fromId, toId, preset) {
        const fromInput = document.getElementById(fromId);
        const toInput = document.getElementById(toId);
        if (!fromInput || !toInput) return;

        const now = new Date();
        const formatDate = (d) => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        if (preset === 'today') {
            const todayStr = formatDate(now);
            fromInput.value = todayStr;
            toInput.value = todayStr;
        } else if (preset === 'this_week') {
            const firstDay = new Date(now.setDate(now.getDate() - now.getDay() + 1));
            const lastDay = new Date(now.setDate(now.getDate() - now.getDay() + 7));
            fromInput.value = formatDate(firstDay);
            toInput.value = formatDate(lastDay);
        } else if (preset === 'this_month') {
            const first = new Date(now.getFullYear(), now.getMonth(), 1);
            const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            fromInput.value = formatDate(first);
            toInput.value = formatDate(last);
        } else if (preset === 'last_month') {
            const first = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const last = new Date(now.getFullYear(), now.getMonth(), 0);
            fromInput.value = formatDate(first);
            toInput.value = formatDate(last);
        } else if (preset === 'this_year') {
            const first = new Date(now.getFullYear(), 0, 1);
            const last = new Date(now.getFullYear(), 11, 31);
            fromInput.value = formatDate(first);
            toInput.value = formatDate(last);
        } else if (preset === 'clear') {
            fromInput.value = '';
            toInput.value = '';
        }
    }
</script>
@endsection
