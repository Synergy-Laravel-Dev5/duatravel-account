<?php
$content = file_get_contents('resources/views/expense_transaction/create.blade.php');

// 1. Add "Loan (Asset / Liability)" radio in category radio options
$targetRadio = '<label class="form-check-label fs-13 fw-semibold" style="color: #7c3aed;" for="payee_jv">';
$posRadio = strpos($content, $targetRadio);
if ($posRadio !== false) {
    // Find the end of this div </div
    $endDiv = strpos($content, '</div>', $posRadio);
    $endDiv = strpos($content, '</div>', $endDiv + 5);
    $loanRadioHtml = "\n                                        <div class=\"form-check\">"
        . "\n                                            <input class=\"form-check-input payee-type-radio\" type=\"radio\" name=\"payee_type\""
        . "\n                                                id=\"payee_loan\" value=\"loan\" {{ old('payee_type') == 'loan' ? 'checked' : '' }}>"
        . "\n                                            <label class=\"form-check-label fs-13 fw-semibold text-warning\" for=\"payee_loan\">"
        . "\n                                                <i class=\"mdi mdi-hand-coin me-1\"></i> Loan (Asset / Liability)"
        . "\n                                            </label>"
        . "\n                                        </div>";
    $content = substr_replace($content, $loanRadioHtml, $endDiv + 6, 0);
    echo "Added loan radio button\n";
}

// 2. Add Loan Section inside the category body (before <!-- 7. JV VOUCHER SECTION -->)
$jvSecMarker = '<!-- 7. JV VOUCHER SECTION -->';
$posJvSec = strpos($content, $jvSecMarker);
if ($posJvSec !== false) {
    $loanSectionHtml = '<!-- 6.5. LOAN SECTION -->
                                <div id="loanSection" class="{{ old(\'payee_type\') == \'loan\' ? \'\' : \'d-none\' }}">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Loan Account <span class="text-danger">*</span></label>
                                            <select name="loan_account_id" id="loan_account_id_select" class="form-select form-select-sm">
                                                <option value="">-- Choose Loan Account --</option>
                                                @foreach ($cashAccounts->filter(fn($a) => in_array($a->account_type, [\'loan_asset\', \'loan_liability\'])) as $la)
                                                    <option value="{{ $la->id }}" {{ old(\'loan_account_id\') == $la->id ? \'selected\' : \'\' }}>
                                                        {{ $la->account_name }} ({{ $la->account_type === \'loan_asset\' ? \'Loan Asset / Receivable\' : \'Loan Liability / Payable\' }}) — Opening: PKR {{ number_format($la->opening_balance_pkr, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route(\'account.create\') }}" target="_blank" class="btn btn-outline-warning btn-sm">
                                                <i class="mdi mdi-plus-circle me-1"></i> + Create New Loan Account
                                            </a>
                                            <small class="text-muted d-block mt-1">Record loans given (Assets) or loans borrowed (Liabilities).</small>
                                        </div>
                                    </div>
                                </div>

                                ';
    $content = substr_replace($content, $loanSectionHtml, $posJvSec, 0);
    echo "Added loan section\n";
}

// 3. Add Universal JV mode radio
$targetJvMode = '<input type="radio" class="btn-check jv-mode-radio" name="jv_mode" id="jv_mode_cs"';
$posJvMode = strpos($content, $targetJvMode);
if ($posJvMode !== false) {
    $univRadio = '<input type="radio" class="btn-check jv-mode-radio" name="jv_mode" id="jv_mode_univ" value="universal" {{ old(\'jv_mode\', \'universal\') === \'universal\' ? \'checked\' : \'\' }}>
                                                <label class="btn btn-sm btn-outline-success" for="jv_mode_univ">
                                                    <i class="mdi mdi-arrow-left-right-bold me-1"></i> 🌐 Universal JV (From A/C ➔ To A/C)
                                                </label>

                                                ';
    $content = substr_replace($content, $univRadio, $posJvMode, 0);
    echo "Added Universal JV radio\n";
}

// 4. Add Universal JV Panel
$targetCsPanel = '<!-- 1. CLIENT & SUPPLIER (BOTH IN 1 JV) -->';
$posCsPanel = strpos($content, $targetCsPanel);
if ($posCsPanel !== false) {
    $univPanel = '<!-- 0. UNIVERSAL JV (FROM A/C TO A/C - ALL ENTITIES) -->
                                        <div id="jv_panel_univ" class="jv-subpanel {{ old(\'jv_mode\', \'universal\') === \'universal\' ? \'\' : \'d-none\' }}">
                                            <div class="alert alert-soft-success py-2 px-3 fs-12 border-0 mb-3">
                                                <i class="mdi mdi-check-decagram text-success me-1"></i> <strong>Universal JV Voucher (Any Account ➔ Any Account):</strong>
                                                Select source and destination accounts from <strong>Client, Supplier, Staff, Bank, Cash Counter, Office Expense, or Loan (Asset/Liability)</strong>. The system records balanced debit and credit entries simultaneously.
                                            </div>
                                            <div class="row g-3 align-items-center">
                                                <!-- FROM ACCOUNT -->
                                                <div class="col-md-5">
                                                    <div class="p-3 border rounded bg-white shadow-sm">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label fs-12 fw-bold text-danger text-uppercase mb-0">
                                                                <i class="mdi mdi-upload me-1"></i> From Account (Source / Credit -)
                                                            </label>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label fs-11 text-muted mb-1">Entity Type</label>
                                                            <select id="jv_from_type" name="jv_from_type" class="form-select form-select-sm jv-entity-type-select" data-target="jv_from_id">
                                                                <option value="client">Client</option>
                                                                <option value="supplier">Supplier</option>
                                                                <option value="staff">Staff Member</option>
                                                                <option value="bank">Bank</option>
                                                                <option value="cash">Cash Counter / Account</option>
                                                                <option value="loan">Loan (Asset / Liability)</option>
                                                                <option value="expense">Expense Head</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="form-label fs-11 text-muted mb-1">Select Account / Party <span class="text-danger">*</span></label>
                                                            <select id="jv_from_id" name="jv_from_id" class="form-select form-select-sm">
                                                                <!-- Populated dynamically via JS -->
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- DIRECTION ARROW -->
                                                <div class="col-md-2 text-center d-flex flex-column justify-content-center">
                                                    <div class="text-success fs-28"><i class="mdi mdi-arrow-right-bold-circle"></i></div>
                                                    <small class="text-dark fs-11 fw-bold text-uppercase">Transfer To</small>
                                                </div>

                                                <!-- TO ACCOUNT -->
                                                <div class="col-md-5">
                                                    <div class="p-3 border rounded bg-white shadow-sm">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label fs-12 fw-bold text-success text-uppercase mb-0">
                                                                <i class="mdi mdi-download me-1"></i> To Account (Destination / Debit +)
                                                            </label>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label fs-11 text-muted mb-1">Entity Type</label>
                                                            <select id="jv_to_type" name="jv_to_type" class="form-select form-select-sm jv-entity-type-select" data-target="jv_to_id">
                                                                <option value="supplier" selected>Supplier</option>
                                                                <option value="client">Client</option>
                                                                <option value="staff">Staff Member</option>
                                                                <option value="bank">Bank</option>
                                                                <option value="cash">Cash Counter / Account</option>
                                                                <option value="loan">Loan (Asset / Liability)</option>
                                                                <option value="expense">Expense Head</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="form-label fs-11 text-muted mb-1">Select Account / Party <span class="text-danger">*</span></label>
                                                            <select id="jv_to_id" name="jv_to_id" class="form-select form-select-sm">
                                                                <!-- Populated dynamically via JS -->
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        ';
    $content = substr_replace($content, $univPanel, $posCsPanel, 0);
    echo "Added Universal JV Panel\n";
}

// 5. Update Bank & Cash Account selection in Row 2
$bankRowMarker = '<!-- ROW 2: Bank, Currency, Exchange Rate -->';
$posBankRow = strpos($content, $bankRowMarker);
if ($posBankRow !== false) {
    // Find next <!-- ROW 3: Total Amount
    $row3Marker = '<!-- ROW 3: Total Amount';
    $posRow3 = strpos($content, $row3Marker, $posBankRow);
    $newRow2 = '<!-- ROW 2: Bank, Cash Account, Currency, Exchange Rate -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6" id="bank_select_group" style="{{ old(\'payment_type\', \'bank\') === \'cash\' ? \'display:none;\' : \'\' }}">
                                <label class="form-label fs-13 fw-semibold">Bank</label>
                                <select name="bank_id" id="bank_id_select" class="form-select form-select-sm">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id }}" {{ old(\'bank_id\') == $b->id ? \'selected\' : \'\' }}>
                                            {{ $b->name }}{{ !empty($b->account_name) ? \' - \' . $b->account_name : \'\' }}{{ !empty($b->account_number) && $b->account_number !== \'0\' ? \' (\' . $b->account_number . \')\' : \'\' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6" id="cash_account_group" style="{{ old(\'payment_type\', \'bank\') === \'cash\' ? \'\' : \'display:none;\' }}">
                                <label class="form-label fs-13 fw-semibold">
                                    <i class="mdi mdi-cash-multiple text-success me-1"></i> Cash / Counter Account <span class="text-danger">*</span>
                                </label>
                                <select name="account_id" id="cash_account_id_select" class="form-select form-select-sm">
                                    <option value="">-- Select Cash Account --</option>
                                    @foreach ($cashAccounts->filter(fn($a) => in_array($a->account_type, [\'cash\', \'general\'])) as $ca)
                                        <option value="{{ $ca->id }}" {{ old(\'account_id\') == $ca->id ? \'selected\' : ($ca->account_name === \'Cash\' ? \'selected\' : \'\') }}>
                                            {{ $ca->account_name }} — Opening: PKR {{ number_format($ca->opening_balance_pkr, 2) }} / SAR {{ number_format($ca->opening_balance_sar, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted fs-11">Transactions will impact this cash account balance.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-13 fw-semibold">Currency <span class="text-danger">*</span></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <select name="currency" id="currency_select" class="form-select form-select-sm" required>
                                            <option value="PKR" {{ old(\'currency\', \'PKR\') == \'PKR\' ? \'selected\' : \'\' }}>PKR</option>
                                            <option value="SAR" {{ old(\'currency\') == \'SAR\' ? \'selected\' : \'\' }}>SAR</option>
                                            <option value="USD" {{ old(\'currency\') == \'USD\' ? \'selected\' : \'\' }}>USD</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate_input"
                                            value="{{ old(\'exchange_rate\', 1) }}" class="form-control form-control-sm bg-light"
                                            placeholder="Ex. Rate" readonly required>
                                    </div>
                                </div>
                            </div>

                            <!-- Live ROE Preview Box -->
                            <div class="col-12" id="roe_live_preview_box" style="display: none;">
                                <div class="p-2 px-3 rounded border border-info bg-soft-info d-flex align-items-center gap-2 fs-13">
                                    <i class="mdi mdi-calculator text-info fs-18"></i>
                                    <div>
                                        <strong class="text-dark">Currency Conversion Preview:</strong>
                                        <span id="roe_live_preview_text" class="fw-bold text-primary ms-1"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        ';
    $content = substr_replace($content, $newRow2, $posBankRow, $posRow3 - $posBankRow);
    echo "Updated Row 2 (Bank / Cash / Live ROE)\n";
}

file_put_contents('resources/views/expense_transaction/create.blade.php', $content);
echo "File updated successfully.\n";
