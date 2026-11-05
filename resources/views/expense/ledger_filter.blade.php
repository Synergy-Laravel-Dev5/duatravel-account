@extends('layout.master')

@section('title', 'Expense Ledger (Dr / Cr) Filter')

@section('content')

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="row justify-content-center">
                    <div class="col-lg-8 col-12">
                        <div class="card shadow-sm border-0 mt-3">
                            <div class="card-body p-4">

                                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                    <div>
                                        <h4 class="fw-bold m-0 text-dark">
                                            <i class="mdi mdi-book-open-page-variant text-danger me-2"></i> Expense Ledger Filter (Dr / Cr)
                                        </h4>
                                        <p class="text-muted fs-12 mb-0">Generate double-entry Statement of Account with Debit, Credit & Running Balance for all business expenses.</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('reports.hub', ['tab' => 'expense']) }}" class="btn btn-danger btn-sm text-white">
                                            <i class="mdi mdi-layers-outline me-1"></i> All-In-One Reports Hub
                                        </a>
                                        <a href="{{ route('expense.transaction.index') }}" class="btn btn-outline-dark btn-sm">
                                            <i class="mdi mdi-receipt-text-outline me-1"></i> Transactions List
                                        </a>
                                    </div>
                                </div>

                                <form action="{{ route('expense.ledger.view') }}" method="GET">
                                    <div class="row g-3">

                                        <div class="col-12">
                                            <label class="form-label fw-bold">Select Expense Head / Category <span class="text-danger">*</span></label>
                                            <select name="expense_id" class="form-select form-select-lg fs-14" required>
                                                <option value="all">🌟 All Expenses (Consolidated Ledger Statement)</option>
                                                @foreach ($expenses as $exp)
                                                    <option value="{{ $exp->id }}">
                                                        {{ $exp->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Hajj / Umrah Season -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Hajj / Umrah Season</label>
                                            <select name="package_type" class="form-select">
                                                <option value="all">Consolidated (Hajj & Umrah)</option>
                                                <option value="umrah" {{ session('dashboard_package') === 'umrah' ? 'selected' : '' }}>Umrah Only</option>
                                                <option value="hajj" {{ session('dashboard_package') === 'hajj' ? 'selected' : '' }}>Hajj Only</option>
                                            </select>
                                        </div>

                                        <!-- Payment Source -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Paid Through Account <small class="text-muted">(Optional)</small></label>
                                            <select name="account_source" class="form-select">
                                                <option value="">-- All Accounts & Banks --</option>
                                                <optgroup label="Cash Accounts">
                                                    @foreach ($cashAccounts as $ca)
                                                        <option value="cash:{{ $ca->id }}">Cash: {{ $ca->account_name }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="Bank Accounts">
                                                    @foreach ($banks as $b)
                                                        <option value="bank:{{ $b->id }}">Bank: {{ $b->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </div>

                                        <!-- From Date -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">From Date <small class="text-muted">(Optional)</small></label>
                                            <input type="date" name="from_date" id="exp_from_date" class="form-control" value="{{ request('from_date') }}">
                                        </div>

                                        <!-- To Date -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">To Date <small class="text-muted">(Optional)</small></label>
                                            <input type="date" name="to_date" id="exp_to_date" class="form-control" value="{{ request('to_date') }}">
                                        </div>

                                        <!-- Date Presets -->
                                        <div class="col-12 pt-1">
                                            <div class="d-flex flex-wrap align-items-center gap-1">
                                                <span class="fs-11 text-muted fw-bold me-1">Date Presets:</span>
                                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 fs-11" onclick="setExpPreset('today')">Today</button>
                                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 fs-11" onclick="setExpPreset('this_week')">This Week</button>
                                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 fs-11" onclick="setExpPreset('this_month')">This Month</button>
                                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 fs-11" onclick="setExpPreset('last_month')">Last Month</button>
                                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 fs-11" onclick="setExpPreset('this_year')">This Year</button>
                                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 fs-11" onclick="setExpPreset('clear')">All Time</button>
                                            </div>
                                        </div>

                                        <!-- Search Keyword -->
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Search Narration / Voucher <small class="text-muted">(Optional)</small></label>
                                            <input type="text" name="search" class="form-control" placeholder="Search by voucher #, remark, payment ref...">
                                        </div>

                                        <div class="col-12 mt-4 text-end">
                                            <a href="{{ route('reports.hub', ['tab' => 'expense']) }}" class="btn btn-light me-2">Cancel</a>
                                            <button type="submit" class="btn btn-danger px-4 fw-bold">
                                                <i class="mdi mdi-book-open-page-variant me-1"></i> Generate Expense Ledger Statement
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

    <script>
        function setExpPreset(preset) {
            const fromInput = document.getElementById('exp_from_date');
            const toInput = document.getElementById('exp_to_date');
            if (!fromInput || !toInput) return;

            const now = new Date();
            const formatDate = (d) => {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            if (preset === 'today') {
                fromInput.value = formatDate(now);
                toInput.value = formatDate(now);
            } else if (preset === 'this_week') {
                const firstDay = new Date(now.setDate(now.getDate() - now.getDay() + 1));
                const lastDay = new Date(now.setDate(now.getDate() - now.getDay() + 7));
                fromInput.value = formatDate(firstDay);
                toInput.value = formatDate(lastDay);
            } else if (preset === 'this_month') {
                fromInput.value = formatDate(new Date(now.getFullYear(), now.getMonth(), 1));
                toInput.value = formatDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));
            } else if (preset === 'last_month') {
                fromInput.value = formatDate(new Date(now.getFullYear(), now.getMonth() - 1, 1));
                toInput.value = formatDate(new Date(now.getFullYear(), now.getMonth(), 0));
            } else if (preset === 'this_year') {
                fromInput.value = formatDate(new Date(now.getFullYear(), 0, 1));
                toInput.value = formatDate(new Date(now.getFullYear(), 11, 31));
            } else if (preset === 'clear') {
                fromInput.value = '';
                toInput.value = '';
            }
        }
    </script>
@endsection
