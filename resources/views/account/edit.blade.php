@extends('layout.master')
@section('title', 'Edit Account')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fs-18 fw-semibold m-0">Edit Account</h4>
                        <p class="text-muted fs-13 mb-0">Modify Account, Loan Asset / Liability link, and Multi-Currency balances.</p>
                    </div>
                    <a href="{{ route('account.index') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Accounts
                    </a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="mdi mdi-alert-circle me-1"></i> Please check errors:</strong>
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
                        <form action="{{ route('account.update', $account->id) }}" method="POST" id="accountForm">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                
                                <!-- ACCOUNT TYPE -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Account Type <span class="text-danger">*</span></label>
                                    <select name="account_type" id="account_type_select" class="form-select" required>
                                        <option value="cash" {{ old('account_type', $account->account_type) == 'cash' ? 'selected' : '' }}>💵 Cash Account</option>
                                        <option value="loan_asset" {{ old('account_type', $account->account_type) == 'loan_asset' ? 'selected' : '' }}>📥 Loan Asset (Loan Given / Receivable)</option>
                                        <option value="loan_liability" {{ old('account_type', $account->account_type) == 'loan_liability' ? 'selected' : '' }}>📤 Loan Liability (Loan Taken / Payable)</option>
                                        <option value="bank" {{ old('account_type', $account->account_type) == 'bank' ? 'selected' : '' }}>🏦 Bank Account</option>
                                        <option value="general" {{ old('account_type', $account->account_type) == 'general' ? 'selected' : '' }}>📁 General Account</option>
                                    </select>
                                    <small class="text-muted fs-12" id="type_hint">Cash accounts represent physical cash in hand or cash registers.</small>
                                </div>

                                <!-- STATUS -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="active" {{ old('status', $account->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $account->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <!-- LOAN / ENTITY LINK CARD -->
                                @php
                                    $currentEntityType = old('entity_type', $account->entity_type);
                                    $currentEntityId = old('entity_id', $account->entity_id);
                                @endphp
                                <div class="col-12" id="entity_link_box">
                                    <div class="p-3 border rounded-3 bg-light shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold text-primary mb-0 fs-14">
                                                <i class="mdi mdi-account-switch me-1"></i> Link With Entity / Person / Party
                                            </label>
                                            <span class="badge bg-primary fs-11">Select Client, Staff, Supplier, or Bank</span>
                                        </div>
                                        <p class="text-muted fs-12 mb-3">
                                            Select who this loan or account belongs to. Selecting a party will automatically link the account.
                                        </p>

                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-4">
                                                <label class="form-label fs-13 fw-semibold">Party Category</label>
                                                <select name="entity_type" id="entity_type_select" class="form-select form-select-sm">
                                                    <option value="" {{ !$currentEntityType ? 'selected' : '' }}>-- None / Custom External Party --</option>
                                                    <option value="staff" {{ $currentEntityType == 'staff' ? 'selected' : '' }}>👤 Staff Member</option>
                                                    <option value="client" {{ $currentEntityType == 'client' ? 'selected' : '' }}>👥 Client</option>
                                                    <option value="supplier" {{ $currentEntityType == 'supplier' ? 'selected' : '' }}>🏢 Supplier</option>
                                                    <option value="bank" {{ $currentEntityType == 'bank' ? 'selected' : '' }}>🏦 Bank</option>
                                                </select>
                                            </div>

                                            <!-- STAFF SELECT -->
                                            <div class="col-md-8 entity-select-group {{ $currentEntityType == 'staff' ? '' : 'd-none' }}" id="grp_staff">
                                                <label class="form-label fs-13 fw-semibold">Select Staff Member</label>
                                                <select name="staff_id" id="staff_id_select" class="form-select form-select-sm">
                                                    <option value="">-- Choose Staff Member --</option>
                                                    @foreach($staffMembers as $st)
                                                        @php
                                                            $stName = trim($st->first_name . ' ' . $st->last_name);
                                                            $stRole = $st->designation->name ?? ($st->department->name ?? 'Staff');
                                                        @endphp
                                                        <option value="{{ $st->id }}" data-name="{{ $stName }}" {{ ($currentEntityType == 'staff' && $currentEntityId == $st->id) ? 'selected' : '' }}>
                                                            {{ $stName }} ({{ $stRole }}) - {{ $st->phone }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- CLIENT SELECT -->
                                            <div class="col-md-8 entity-select-group {{ $currentEntityType == 'client' ? '' : 'd-none' }}" id="grp_client">
                                                <label class="form-label fs-13 fw-semibold">Select Client</label>
                                                <select name="client_id" id="client_id_select" class="form-select form-select-sm">
                                                    <option value="">-- Choose Client --</option>
                                                    @foreach($clients as $cl)
                                                        <option value="{{ $cl->id }}" data-name="{{ $cl->name }}" {{ ($currentEntityType == 'client' && $currentEntityId == $cl->id) ? 'selected' : '' }}>
                                                            {{ $cl->name }}{{ $cl->phone ? ' (' . $cl->phone . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- SUPPLIER SELECT -->
                                            <div class="col-md-8 entity-select-group {{ $currentEntityType == 'supplier' ? '' : 'd-none' }}" id="grp_supplier">
                                                <label class="form-label fs-13 fw-semibold">Select Supplier</label>
                                                <select name="supplier_id" id="supplier_id_select" class="form-select form-select-sm">
                                                    <option value="">-- Choose Supplier --</option>
                                                    @foreach($suppliers as $sp)
                                                        <option value="{{ $sp->id }}" data-name="{{ $sp->name }}" {{ ($currentEntityType == 'supplier' && $currentEntityId == $sp->id) ? 'selected' : '' }}>
                                                            {{ $sp->name }}{{ $sp->company_name ? ' (' . $sp->company_name . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- BANK SELECT -->
                                            <div class="col-md-8 entity-select-group {{ $currentEntityType == 'bank' ? '' : 'd-none' }}" id="grp_bank">
                                                <label class="form-label fs-13 fw-semibold">Select Bank</label>
                                                <select name="bank_id" id="bank_id_select" class="form-select form-select-sm">
                                                    <option value="">-- Choose Bank --</option>
                                                    @foreach($banks as $bk)
                                                        <option value="{{ $bk->id }}" data-name="{{ $bk->name }}" {{ ($currentEntityType == 'bank' && $currentEntityId == $bk->id) ? 'selected' : '' }}>
                                                            {{ $bk->name }}{{ $bk->account_number ? ' (' . $bk->account_number . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- ACCOUNT NAME -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Account Title / Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_name" id="account_name_input" class="form-control" 
                                        value="{{ old('account_name', $account->account_name) }}" required>
                                    <small class="text-muted fs-11">This is the title that will display in transaction dropdowns, cash reports, and balance sheets.</small>
                                </div>

                                <!-- MULTI-CURRENCY OPENING BALANCE -->
                                <div class="col-12">
                                    <div class="p-3 border rounded-3 bg-light">
                                        <h6 class="text-primary fw-bold mb-2">
                                            <i class="mdi mdi-cash-multiple me-1"></i> Opening Balance (Multi-Currency)
                                        </h6>
                                        <p class="text-muted fs-12 mb-3">
                                            Enter the starting / initial loan balance. If starting in PKR, enter PKR and ROE to auto-calculate SAR.
                                        </p>
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold fs-13">Opening Balance (PKR)</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white fw-bold">PKR</span>
                                                    <input type="number" step="0.01" name="opening_balance_pkr" id="opening_balance_pkr" 
                                                        class="form-control form-control-sm fw-bold text-dark" 
                                                        value="{{ old('opening_balance_pkr', $account->opening_balance_pkr ?? 0) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold fs-13 text-center d-block">Exchange Rate (ROE)</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white"><i class="mdi mdi-swap-horizontal"></i></span>
                                                    <input type="number" step="0.0001" name="opening_balance_roe" id="opening_balance_roe" 
                                                        class="form-control form-control-sm text-center fw-bold" 
                                                        value="{{ old('opening_balance_roe', $account->opening_balance_roe ?? '75.00') }}">
                                                </div>
                                                <small class="text-muted fs-11 text-center d-block mt-1">1 SAR = X PKR</small>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold fs-13">Opening Balance (SAR)</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white fw-bold">SAR</span>
                                                    <input type="number" step="0.01" name="opening_balance_sar" id="opening_balance_sar" 
                                                        class="form-control form-control-sm fw-bold text-success" 
                                                        value="{{ old('opening_balance_sar', $account->opening_balance_sar ?? 0) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- NOTES / REMARKS -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notes / Description</label>
                                    <textarea name="notes" class="form-control" rows="2" 
                                        placeholder="Terms of loan, repayment conditions, remarks...">{{ old('notes', $account->notes) }}</textarea>
                                </div>

                            </div>
                            
                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-content-save me-1"></i> Update Account
                                </button>
                                <a href="{{ route('account.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accTypeSelect = document.getElementById('account_type_select');
            const typeHint = document.getElementById('type_hint');
            const accNameInput = document.getElementById('account_name_input');
            const entityTypeSelect = document.getElementById('entity_type_select');

            const grpStaff = document.getElementById('grp_staff');
            const grpClient = document.getElementById('grp_client');
            const grpSupplier = document.getElementById('grp_supplier');
            const grpBank = document.getElementById('grp_bank');

            const staffSelect = document.getElementById('staff_id_select');
            const clientSelect = document.getElementById('client_id_select');
            const supplierSelect = document.getElementById('supplier_id_select');
            const bankSelect = document.getElementById('bank_id_select');

            const pkrInput = document.getElementById('opening_balance_pkr');
            const roeInput = document.getElementById('opening_balance_roe');
            const sarInput = document.getElementById('opening_balance_sar');

            const hints = {
                'cash': 'Cash accounts represent physical cash in hand or cash drawers.',
                'loan_asset': 'Loan Asset (Receivable): Money given out as loan to staff, client, or third party (Asset for company).',
                'loan_liability': 'Loan Liability (Payable): Money borrowed or loan taken from bank, director, supplier, or investor (Liability to repay).',
                'bank': 'Bank Account: Commercial bank checking or savings account.',
                'general': 'General ledger head for miscellaneous accounting purposes.'
            };

            function onTypeChange() {
                const val = accTypeSelect.value;
                if (typeHint && hints[val]) {
                    typeHint.textContent = hints[val];
                }
            }

            function onEntityTypeChange() {
                const val = entityTypeSelect.value;
                if (grpStaff) grpStaff.classList.toggle('d-none', val !== 'staff');
                if (grpClient) grpClient.classList.toggle('d-none', val !== 'client');
                if (grpSupplier) grpSupplier.classList.toggle('d-none', val !== 'supplier');
                if (grpBank) grpBank.classList.toggle('d-none', val !== 'bank');
            }

            function suggestAccountName() {
                const accType = accTypeSelect.value;
                const entityType = entityTypeSelect.value;
                let partyName = '';

                if (entityType === 'staff' && staffSelect && staffSelect.selectedIndex > 0) {
                    partyName = staffSelect.options[staffSelect.selectedIndex].getAttribute('data-name') || staffSelect.options[staffSelect.selectedIndex].text;
                } else if (entityType === 'client' && clientSelect && clientSelect.selectedIndex > 0) {
                    partyName = clientSelect.options[clientSelect.selectedIndex].getAttribute('data-name') || clientSelect.options[clientSelect.selectedIndex].text;
                } else if (entityType === 'supplier' && supplierSelect && supplierSelect.selectedIndex > 0) {
                    partyName = supplierSelect.options[supplierSelect.selectedIndex].getAttribute('data-name') || supplierSelect.options[supplierSelect.selectedIndex].text;
                } else if (entityType === 'bank' && bankSelect && bankSelect.selectedIndex > 0) {
                    partyName = bankSelect.options[bankSelect.selectedIndex].getAttribute('data-name') || bankSelect.options[bankSelect.selectedIndex].text;
                }

                if (partyName) {
                    let prefix = 'Account';
                    if (accType === 'loan_asset') prefix = 'Loan Asset';
                    else if (accType === 'loan_liability') prefix = 'Loan Liability';
                    else if (accType === 'cash') prefix = 'Cash';
                    else if (accType === 'bank') prefix = 'Bank';

                    accNameInput.value = `${prefix} - ${entityType.charAt(0).toUpperCase() + entityType.slice(1)}: ${partyName.trim()}`;
                }
            }

            accTypeSelect.addEventListener('change', onTypeChange);
            entityTypeSelect.addEventListener('change', onEntityTypeChange);

            if (staffSelect) staffSelect.addEventListener('change', suggestAccountName);
            if (clientSelect) clientSelect.addEventListener('change', suggestAccountName);
            if (supplierSelect) supplierSelect.addEventListener('change', suggestAccountName);
            if (bankSelect) bankSelect.addEventListener('change', suggestAccountName);

            // 2-Way Opening Balance Calculator
            function calcFromPkr() {
                const pkr = parseFloat(pkrInput.value) || 0;
                const roe = parseFloat(roeInput.value) || 75;
                if (roe <= 1) {
                    sarInput.value = '0.00';
                } else if (roe > 0) {
                    sarInput.value = (pkr / roe).toFixed(2);
                }
            }

            function calcFromSar() {
                const sar = parseFloat(sarInput.value) || 0;
                const roe = parseFloat(roeInput.value) || 75;
                if (roe <= 1) {
                    sarInput.value = '0.00';
                } else if (roe > 0) {
                    pkrInput.value = (sar * roe).toFixed(2);
                }
            }

            if (pkrInput && roeInput && sarInput) {
                pkrInput.addEventListener('input', calcFromPkr);
                roeInput.addEventListener('input', calcFromPkr);
                sarInput.addEventListener('input', calcFromSar);
            }

            onTypeChange();
        });
    </script>
@endsection
