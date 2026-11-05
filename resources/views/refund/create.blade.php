@extends('layout.master')

@section('title', 'Record Refund')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    .refund-type-card {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
    }
    .refund-type-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .refund-type-card.active-client {
        border-color: #dc2626 !important;
        background: #fef2f2 !important;
        box-shadow: 0 3px 10px rgba(220, 38, 38, 0.12);
    }
    .refund-type-card.active-supplier {
        border-color: #16a34a !important;
        background: #f0fdf4 !important;
        box-shadow: 0 3px 10px rgba(22, 163, 74, 0.12);
    }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        padding: 4px 8px;
        font-size: 13px;
    }
    .select2-container {
        width: 100% !important;
    }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid py-3">

            <!-- PAGE HEADER -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fs-18 fw-bold mb-0 text-dark">
                        <i class="mdi mdi-cash-refund text-danger me-2"></i> Record Refund Transaction
                    </h4>
                    <p class="text-muted fs-13 mb-0">Record Client Refunds (payment outflow) or Supplier Refunds (recovery inflow) directly to accounting ledgers.</p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ route('refunds.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Refunds List
                    </a>
                </div>
            </div>

            <!-- VALIDATION ERRORS -->
            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm fs-13 mb-3" role="alert">
                    <strong><i class="mdi mdi-alert-circle me-1"></i> Please correct the following errors:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('refunds.store') }}" method="POST" id="refundForm">
                @csrf

                <!-- MAIN UNIFIED FORM CARD -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fs-14 fw-bold text-dark text-uppercase">
                            <i class="mdi mdi-file-document-edit-outline text-primary me-1"></i> Refund Voucher Form
                        </span>
                        <span class="badge bg-soft-primary text-primary px-3 py-1 fs-12 font-monospace" id="headerVoucherTag">
                            Voucher: {{ $defaultRef }}
                        </span>
                    </div>
                    <div class="card-body p-4">

                        <!-- SECTION 1: REFUND DIRECTION -->
                        <div class="mb-4">
                            <label class="form-label fs-13 fw-bold text-dark text-uppercase mb-2">
                                <i class="mdi mdi-swap-horizontal text-primary me-1"></i> 1. Select Refund Direction <span class="text-danger">*</span>
                            </label>
                            <div class="row g-3">
                                <!-- Client Refund Card -->
                                <div class="col-md-6">
                                    <div class="refund-type-card h-100 {{ old('refund_type', 'client') === 'client' ? 'active-client' : '' }}" id="cardClientRefund" onclick="selectRefundType('client')">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check m-0 me-3">
                                                <input class="form-check-input fs-16" type="radio" name="refund_type" id="refund_type_client" value="client" {{ old('refund_type', 'client') === 'client' ? 'checked' : '' }} onchange="selectRefundType('client')">
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 fw-bold text-danger fs-14">
                                                        <i class="mdi mdi-account-arrow-right me-1"></i> Client Refund (Payment Returned to Client / Outflow)
                                                    </h6>
                                                    <span class="badge bg-danger text-white fs-10 px-2 py-1">Outflow</span>
                                                </div>
                                                <p class="text-muted small mb-0 mt-1">
                                                    Returning payment/advance to pilgrim. Posts a <strong>Debit</strong> in Client Ledger and credits Cash/Bank.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Supplier Refund Card -->
                                <div class="col-md-6">
                                    <div class="refund-type-card h-100 {{ old('refund_type') === 'supplier' ? 'active-supplier' : '' }}" id="cardSupplierRefund" onclick="selectRefundType('supplier')">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check m-0 me-3">
                                                <input class="form-check-input fs-16" type="radio" name="refund_type" id="refund_type_supplier" value="supplier" {{ old('refund_type') === 'supplier' ? 'checked' : '' }} onchange="selectRefundType('supplier')">
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 fw-bold text-success fs-14">
                                                        <i class="mdi mdi-office-building-marker me-1"></i> Supplier Refund (Recovery Received from Vendor / Inflow)
                                                    </h6>
                                                    <span class="badge bg-success text-white fs-10 px-2 py-1">Inflow</span>
                                                </div>
                                                <p class="text-muted small mb-0 mt-1">
                                                    Receiving refund from airline/hotel/visa vendor. Posts a <strong>Credit</strong> in Supplier Ledger and debits Cash/Bank.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- SECTION 2: BENEFICIARY & BOOKING DETAILS -->
                        <div class="mb-4">
                            <label class="form-label fs-13 fw-bold text-dark text-uppercase mb-2">
                                <i class="mdi mdi-account-box-multiple text-primary me-1"></i> 2. Party & Booking Information
                            </label>
                            <div class="row g-3">
                                <!-- CLIENT SELECT (Shown for Client Refund) -->
                                <div class="col-md-6" id="clientPartySection">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-account text-primary me-1"></i> Client / Pilgrim <span class="text-danger">*</span>
                                    </label>
                                    <select name="client_id" id="clientSelect" class="form-select form-select-sm select2" data-placeholder="Search client by name or phone...">
                                        <option value="">-- Choose Client / Pilgrim --</option>
                                        @foreach($clients as $c)
                                            <option value="{{ $c->id }}"
                                                data-name="{{ $c->name }}"
                                                data-phone="{{ $c->phone ?? '—' }}"
                                                data-balance="{{ number_format($c->balance ?? 0, 2) }}"
                                                data-balance-raw="{{ (float)($c->balance ?? 0) }}"
                                                {{ old('client_id') == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }} [Bal: PKR {{ number_format($c->balance ?? 0, 2) }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- SUPPLIER SELECT (Shown for Supplier Refund) -->
                                <div class="col-md-4" id="supplierPartySection" style="display: none;">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-office-building text-info me-1"></i> Supplier / Vendor <span class="text-danger">*</span>
                                    </label>
                                    <select name="supplier_id" id="supplierSelect" class="form-select form-select-sm select2" data-placeholder="Search vendor by name...">
                                        <option value="">-- Choose Supplier / Vendor --</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}"
                                                data-name="{{ $s->name }}"
                                                data-type="{{ ucfirst($s->supplier_type ?? 'Vendor') }}"
                                                data-balance="{{ number_format($s->balance ?? 0, 2) }}"
                                                data-balance-raw="{{ (float)($s->balance ?? 0) }}"
                                                {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                                {{ $s->name }} [Payable: PKR {{ number_format($s->balance ?? 0, 2) }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- SUPPLIER SERVICE CATEGORY (Shown for Supplier Refund) -->
                                <div class="col-md-4" id="supplierServiceSection" style="display: none;">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-tag-outline text-secondary me-1"></i> Service Category <span class="text-danger">*</span>
                                    </label>
                                    <select name="service_type" id="serviceTypeSelect" class="form-select form-select-sm select2">
                                        <option value="flight" {{ old('service_type') === 'flight' ? 'selected' : '' }}>✈️ Flight Refund (Cancelled / Void Ticket)</option>
                                        <option value="hotel" {{ old('service_type') === 'hotel' ? 'selected' : '' }}>🏨 Hotel Refund (Cancelled Room / Stay)</option>
                                        <option value="transport" {{ old('service_type') === 'transport' ? 'selected' : '' }}>🚌 Transport Refund (Cancelled Transfer)</option>
                                        <option value="visa" {{ old('service_type') === 'visa' ? 'selected' : '' }}>🛂 Visa Refund (Rejected / Returned Visa)</option>
                                        <option value="general" {{ old('service_type', 'general') === 'general' ? 'selected' : '' }}>💼 General Vendor Adjustment</option>
                                    </select>
                                </div>

                                <!-- RELATED BOOKING (OPTIONAL) -->
                                <div class="col-md-6" id="bookingSelectCol">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-ticket-account text-secondary me-1"></i> Related Booking (Optional)
                                    </label>
                                    <select name="booking_id" id="bookingSelect" class="form-select form-select-sm select2" data-placeholder="Select booking (optional)...">
                                        <option value="">-- No specific booking (General Refund) --</option>
                                        @foreach($allBookings as $bk)
                                            <option value="{{ $bk->id }}" {{ old('booking_id') == $bk->id ? 'selected' : '' }}>
                                                Booking #{{ $bk->id }} - {{ $bk->client->name ?? 'Client' }} [{{ \Carbon\Carbon::parse($bk->booking_date)->format('d M Y') }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- SECTION 3: DATE, VOUCHER, CURRENCY & AMOUNTS -->
                        <div class="mb-4">
                            <label class="form-label fs-13 fw-bold text-dark text-uppercase mb-2">
                                <i class="mdi mdi-cash-multiple text-primary me-1"></i> 3. Amounts, Currency & Exchange Rate
                            </label>
                            <div class="row g-3">
                                <!-- REFUND DATE -->
                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-calendar text-primary me-1"></i> Refund Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="date" class="form-control form-control-sm" value="{{ old('date', date('Y-m-d')) }}" required>
                                </div>

                                <!-- VOUCHER / REFERENCE # -->
                                <div class="col-md-3">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-barcode text-secondary me-1"></i> Voucher / Reference #
                                    </label>
                                    <input type="text" name="reference_no" id="referenceNoInput" class="form-control form-control-sm font-monospace fw-bold" value="{{ old('reference_no', $defaultRef) }}" placeholder="e.g. REF-2026-0001">
                                </div>

                                <!-- CURRENCY -->
                                <div class="col-md-2">
                                    <label class="form-label fs-13 fw-semibold text-dark">Currency <span class="text-danger">*</span></label>
                                    <select name="currency" id="currencySelect" class="form-select form-select-sm fw-bold" onchange="onCurrencyChange()">
                                        <option value="PKR" {{ old('currency', 'PKR') === 'PKR' ? 'selected' : '' }}>PKR (Rupee)</option>
                                        <option value="SAR" {{ old('currency') === 'SAR' ? 'selected' : '' }}>SAR (Riyal)</option>
                                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                        <option value="AED" {{ old('currency') === 'AED' ? 'selected' : '' }}>AED (Dirham)</option>
                                    </select>
                                </div>

                                <!-- EXCHANGE RATE (ROE) -->
                                <div class="col-md-2">
                                    <label class="form-label fs-13 fw-semibold text-dark">ROE (1 Foreign = ? PKR)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.0001" name="exchange_rate" id="exchangeRateInput" class="form-control form-control-sm text-end fw-bold" value="{{ old('exchange_rate', '75.0000') }}" oninput="calcRefundAmounts()">
                                        <span class="input-group-text bg-light fs-11 fw-semibold">PKR</span>
                                    </div>
                                </div>

                                <!-- REFUND AMOUNT -->
                                <div class="col-md-2">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        Refund Amount (<span id="currLabel">PKR</span>) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" name="amount" id="amountInput" class="form-control form-control-sm text-end fw-bold fs-14" placeholder="0.00" value="{{ old('amount') }}" required oninput="calcRefundAmounts()">
                                </div>

                                <!-- CONVERTED SUMMARY BANNER -->
                                <div class="col-12">
                                    <div class="p-2 px-3 rounded bg-light border d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <span class="fs-13 text-muted">
                                            <i class="mdi mdi-calculator text-primary me-1"></i> Net PKR Ledger Impact:
                                        </span>
                                        <span class="fs-15 fw-bold text-dark font-monospace" id="amountPkrDisp">
                                            0.00 PKR
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- SECTION 4: PAYMENT CHANNEL & NOTES -->
                        <div class="mb-2">
                            <label class="form-label fs-13 fw-bold text-dark text-uppercase mb-2">
                                <i class="mdi mdi-bank text-primary me-1"></i> 4. Payment Account & Description
                            </label>
                            <div class="row g-3">
                                <!-- PAYMENT METHOD -->
                                <div class="col-md-4">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        Payment Method <span class="text-danger">*</span>
                                    </label>
                                    <select name="payment_type" id="paymentTypeSelect" class="form-select form-select-sm select2" onchange="onPaymentTypeChange()">
                                        <option value="cash" {{ old('payment_type', 'cash') === 'cash' ? 'selected' : '' }}>💵 Cash Counter / Cash Account</option>
                                        <option value="bank" {{ old('payment_type') === 'bank' ? 'selected' : '' }}>🏦 Bank Account (Transfer / Online / Cheque)</option>
                                    </select>
                                </div>

                                <!-- BANK ACCOUNT SELECT -->
                                <div class="col-md-8" id="bankSelectGroup" style="display: none;">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-bank text-primary me-1"></i> Bank Account <span class="text-danger">*</span>
                                    </label>
                                    <select name="bank_id" id="bankSelect" class="form-select form-select-sm select2" data-placeholder="Choose bank account...">
                                        <option value="">-- Choose Bank Account --</option>
                                        @foreach($banks as $b)
                                            <option value="{{ $b->id }}" {{ old('bank_id') == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }} - {{ $b->account_number }} ({{ $b->currency ?? 'PKR' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- CASH ACCOUNT SELECT -->
                                <div class="col-md-8" id="cashAccountGroup">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-cash-register text-success me-1"></i> Cash / Counter Account <span class="text-danger">*</span>
                                    </label>
                                    <select name="account_id" id="accountSelect" class="form-select form-select-sm select2" data-placeholder="Choose cash register...">
                                        <option value="">-- Default Cash Counter --</option>
                                        @foreach($cashAccounts as $ca)
                                            <option value="{{ $ca->id }}" {{ old('account_id') == $ca->id ? 'selected' : '' }}>
                                                {{ $ca->account_name }} ({{ $ca->account_type ?? 'Cash' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- DESCRIPTION / REASON -->
                                <div class="col-12">
                                    <label class="form-label fs-13 fw-semibold text-dark">
                                        <i class="mdi mdi-text-box-outline me-1 text-secondary"></i> Description / Reason for Refund
                                    </label>
                                    <textarea name="description" id="descInput" rows="2" class="form-control form-control-sm" placeholder="e.g. Ticket cancellation refund for passenger Ali / Visa rejection refund from vendor...">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer bg-light py-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('refunds.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="mdi mdi-close me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold" id="btnSubmitRefund">
                            <i class="mdi mdi-content-save-check me-1"></i> Post Refund to Ledger
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function selectRefundType(type) {
        document.getElementById('refund_type_' + type).checked = true;

        const cardCl = document.getElementById('cardClientRefund');
        const cardSup = document.getElementById('cardSupplierRefund');
        const clSec = document.getElementById('clientPartySection');
        const supSec = document.getElementById('supplierPartySection');
        const supSvc = document.getElementById('supplierServiceSection');
        const bkCol = document.getElementById('bookingSelectCol');
        const refInput = document.getElementById('referenceNoInput');
        const hdrTag = document.getElementById('headerVoucherTag');

        if (type === 'client') {
            cardCl.classList.add('active-client');
            cardSup.classList.remove('active-supplier');
            clSec.style.display = 'block';
            supSec.style.display = 'none';
            supSvc.style.display = 'none';
            if (bkCol) { bkCol.className = 'col-md-6'; }

            if (refInput && (!refInput.value || refInput.value.startsWith('REF-SUP'))) {
                refInput.value = refInput.value.replace('REF-SUP', 'REF-CL');
                if (hdrTag) hdrTag.textContent = 'Voucher: ' + refInput.value;
            }
        } else {
            cardSup.classList.add('active-supplier');
            cardCl.classList.remove('active-client');
            clSec.style.display = 'none';
            supSec.style.display = 'block';
            supSvc.style.display = 'block';
            if (bkCol) { bkCol.className = 'col-md-4'; }

            if (refInput && (!refInput.value || refInput.value.startsWith('REF-CL') || refInput.value.startsWith('REF-20'))) {
                refInput.value = refInput.value.replace(/REF-(CL|20\d\d)/, 'REF-SUP');
                if (hdrTag) hdrTag.textContent = 'Voucher: ' + refInput.value;
            }
        }
    }

    function onPaymentTypeChange() {
        const pType = document.getElementById('paymentTypeSelect').value;
        const bankGrp = document.getElementById('bankSelectGroup');
        const cashGrp = document.getElementById('cashAccountGroup');

        if (pType === 'bank') {
            bankGrp.style.display = 'block';
            cashGrp.style.display = 'none';
        } else {
            bankGrp.style.display = 'none';
            cashGrp.style.display = 'block';
        }
    }

    function onCurrencyChange() {
        const curr = document.getElementById('currencySelect').value;
        const roeInput = document.getElementById('exchangeRateInput');
        const currLabel = document.getElementById('currLabel');

        currLabel.textContent = curr;

        if (curr === 'PKR') {
            roeInput.value = '1.0000';
        } else if (curr === 'SAR') {
            if (!roeInput.value || roeInput.value === '1.0000' || roeInput.value === '1') roeInput.value = '75.0000';
        } else if (curr === 'USD') {
            if (!roeInput.value || roeInput.value === '1.0000' || roeInput.value === '75.0000') roeInput.value = '280.0000';
        }

        calcRefundAmounts();
    }

    function calcRefundAmounts() {
        const curr = document.getElementById('currencySelect').value;
        const amt = parseFloat(document.getElementById('amountInput').value) || 0;
        const roe = parseFloat(document.getElementById('exchangeRateInput').value) || 1;

        let pkr = amt;
        if (curr !== 'PKR') {
            pkr = amt * roe;
        }

        const disp = document.getElementById('amountPkrDisp');
        if (disp) {
            disp.textContent = Number(pkr.toFixed(2)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' PKR';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        const initialType = document.querySelector('input[name="refund_type"]:checked')?.value || 'client';
        selectRefundType(initialType);
        onPaymentTypeChange();
        calcRefundAmounts();
    });
</script>
@endsection
