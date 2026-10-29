<?php
$content = file_get_contents('resources/views/expense_transaction/edit.blade.php');

// 1. Move Category / Payee card to the very top
$catMarker = '<!-- PAYEE / CATEGORY SELECTOR -->';
$posCat = strpos($content, $catMarker);
if ($posCat === false) {
    die("catMarker not found in edit.blade.php");
}

$submitMarker = '<!-- SUBMIT BUTTONS -->';
$posSubmit = strpos($content, $submitMarker);
if ($posSubmit === false) {
    die("submitMarker not found in edit.blade.php");
}

$catCard = substr($content, $posCat, $posSubmit - $posCat);
$contentWithoutCat = substr($content, 0, $posCat) . substr($content, $posSubmit);

// Find insert point after @method('PUT')
$putMarker = "@method('PUT')";
$posPut = strpos($contentWithoutCat, $putMarker);
$insertPos = strpos($contentWithoutCat, "\n", $posPut) + 1;

$newContent = substr($contentWithoutCat, 0, $insertPos) . "\n" . $catCard . "\n" . substr($contentWithoutCat, $insertPos);
echo "Category card moved to top in edit.blade.php\n";

// 2. Add Loan (Asset / Liability) radio in Category header
$targetRadio = 'id="payee_jv"';
$posRadio = strpos($newContent, $targetRadio);
if ($posRadio !== false) {
    $endDiv = strpos($newContent, '</div>', $posRadio);
    $endDiv = strpos($newContent, '</div>', $endDiv + 5);
    $loanRadioHtml = "\n                                        <div class=\"form-check\">"
        . "\n                                            <input class=\"form-check-input payee-type-radio\" type=\"radio\" name=\"payee_type\""
        . "\n                                                id=\"payee_loan\" value=\"loan\" {{ $currentPayee == 'loan' ? 'checked' : '' }}>"
        . "\n                                            <label class=\"form-check-label fs-13 fw-semibold text-warning\" for=\"payee_loan\">"
        . "\n                                                <i class=\"mdi mdi-hand-coin me-1\"></i> Loan (Asset / Liability)"
        . "\n                                            </label>"
        . "\n                                        </div>";
    $newContent = substr_replace($newContent, $loanRadioHtml, $endDiv + 6, 0);
    echo "Added loan radio in edit.blade.php\n";
}

// 3. Add Loan Section inside Category card body (before <!-- 7. JV VOUCHER SECTION -->)
$jvSecMarker = '<!-- 7. JV VOUCHER SECTION -->';
$posJvSec = strpos($newContent, $jvSecMarker);
if ($posJvSec !== false) {
    $loanSectionHtml = '<!-- 6.5. LOAN SECTION -->
                                <div id="loanSection" class="{{ $currentPayee == \'loan\' ? \'\' : \'d-none\' }}">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label fs-13 fw-semibold">Select Loan Account <span class="text-danger">*</span></label>
                                            <select name="loan_account_id" id="loan_account_id_select" class="form-select form-select-sm">
                                                <option value="">-- Choose Loan Account --</option>
                                                @foreach ($cashAccounts->filter(fn($a) => in_array($a->account_type, [\'loan_asset\', \'loan_liability\'])) as $la)
                                                    <option value="{{ $la->id }}" {{ old(\'loan_account_id\', $transaction->account_id) == $la->id ? \'selected\' : \'\' }}>
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
    $newContent = substr_replace($newContent, $loanSectionHtml, $posJvSec, 0);
    echo "Added loan section in edit.blade.php\n";
}

// 4. Update Row 2 (Bank / Cash Account, Currency, ROE Live Preview)
$bankRowMarker = '<!-- ROW 2: Bank, Currency, Exchange Rate -->';
$posBankRow = strpos($newContent, $bankRowMarker);
if ($posBankRow !== false) {
    $row3Marker = '<!-- ROW 3: Total Amount';
    $posRow3 = strpos($newContent, $row3Marker, $posBankRow);
    $newRow2 = '<!-- ROW 2: Bank, Cash Account, Currency, Exchange Rate -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6" id="bank_select_group" style="{{ old(\'payment_type\', $transaction->payment_type) === \'cash\' ? \'display:none;\' : \'\' }}">
                                <label class="form-label fs-13 fw-semibold">Bank</label>
                                <select name="bank_id" id="bank_id_select" class="form-select form-select-sm">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id }}" {{ old(\'bank_id\', $transaction->bank_id) == $b->id ? \'selected\' : \'\' }}>
                                            {{ $b->name }}{{ !empty($b->account_name) ? \' - \' . $b->account_name : \'\' }}{{ !empty($b->account_number) && $b->account_number !== \'0\' ? \' (\' . $b->account_number . \')\' : \'\' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6" id="cash_account_group" style="{{ old(\'payment_type\', $transaction->payment_type) === \'cash\' ? \'\' : \'display:none;\' }}">
                                <label class="form-label fs-13 fw-semibold">
                                    <i class="mdi mdi-cash-multiple text-success me-1"></i> Cash / Counter Account <span class="text-danger">*</span>
                                </label>
                                <select name="account_id" id="cash_account_id_select" class="form-select form-select-sm">
                                    <option value="">-- Select Cash Account --</option>
                                    @foreach ($cashAccounts->filter(fn($a) => in_array($a->account_type, [\'cash\', \'general\'])) as $ca)
                                        <option value="{{ $ca->id }}" {{ old(\'account_id\', $transaction->account_id) == $ca->id ? \'selected\' : ($ca->account_name === \'Cash\' ? \'selected\' : \'\') }}>
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
                                            <option value="PKR" {{ old(\'currency\', $transaction->currency) == \'PKR\' ? \'selected\' : \'\' }}>PKR</option>
                                            <option value="SAR" {{ old(\'currency\', $transaction->currency) == \'SAR\' ? \'selected\' : \'\' }}>SAR</option>
                                            <option value="USD" {{ old(\'currency\', $transaction->currency) == \'USD\' ? \'selected\' : \'\' }}>USD</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate_input"
                                            value="{{ old(\'exchange_rate\', $transaction->exchange_rate ?? 1) }}"
                                            class="form-control form-control-sm {{ ($transaction->currency ?? \'PKR\') == \'PKR\' ? \'bg-light\' : \'\' }}"
                                            placeholder="Ex. Rate" {{ ($transaction->currency ?? \'PKR\') == \'PKR\' ? \'readonly\' : \'\' }} required>
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
    $newContent = substr_replace($newContent, $newRow2, $posBankRow, $posRow3 - $posBankRow);
    echo "Updated Row 2 in edit.blade.php\n";
}

// 5. Update JavaScript in edit.blade.php
$posScript = strpos($newContent, '<script>');
if ($posScript !== false) {
    // Locate payeeRadios
    $posPayee = strpos($newContent, 'const payeeRadios', $posScript);
    if ($posPayee !== false) {
        $posPayeeEnd = strpos($newContent, '});', $posPayee) + 3;
        $updatedPayeeJs = 'const payeeRadios = document.querySelectorAll(\'.payee-type-radio\');
        const supplierSec = document.getElementById(\'supplierSection\');
        const clientSec = document.getElementById(\'clientSection\');
        const staffSec = document.getElementById(\'staffSection\');
        const expenseSec = document.getElementById(\'expenseHeadSection\');
        const selfSec = document.getElementById(\'selfSection\');
        const jvSec = document.getElementById(\'jvSection\');
        const loanSec = document.getElementById(\'loanSection\');

        payeeRadios.forEach(radio => {
            radio.addEventListener(\'change\', function() {
                if (supplierSec) supplierSec.classList.add(\'d-none\');
                if (clientSec) clientSec.classList.add(\'d-none\');
                if (staffSec) staffSec.classList.add(\'d-none\');
                if (expenseSec) expenseSec.classList.add(\'d-none\');
                if (selfSec) selfSec.classList.add(\'d-none\');
                if (jvSec) jvSec.classList.add(\'d-none\');
                if (loanSec) loanSec.classList.add(\'d-none\');

                if (this.value === \'supplier\' && supplierSec) {
                    supplierSec.classList.remove(\'d-none\');
                } else if (this.value === \'client\' && clientSec) {
                    clientSec.classList.remove(\'d-none\');
                } else if (this.value === \'staff\' && staffSec) {
                    staffSec.classList.remove(\'d-none\');
                } else if (this.value === \'expense\' && expenseSec) {
                    expenseSec.classList.remove(\'d-none\');
                } else if (this.value === \'self\' && selfSec) {
                    selfSec.classList.remove(\'d-none\');
                } else if (this.value === \'jv\' && jvSec) {
                    jvSec.classList.remove(\'d-none\');
                } else if (this.value === \'loan\' && loanSec) {
                    loanSec.classList.remove(\'d-none\');
                }
            });
        });';
        $newContent = substr_replace($newContent, $updatedPayeeJs, $posPayee, $posPayeeEnd - $posPayee);
        echo "Updated payeeRadios in edit.blade.php\n";
    }

    // Add Payment Type toggle & Live ROE preview before </script>
    $posScriptEnd = strrpos($newContent, '</script>');
    $extraJs = '
        // Payment Type Toggle (Bank vs Cash Account)
        const paymentTypeRadios = document.querySelectorAll(\'input[name="payment_type"]\');
        const bankSelectGroup = document.getElementById(\'bank_select_group\');
        const cashAccountGroup = document.getElementById(\'cash_account_group\');

        function updatePaymentMode(type) {
            if (type === \'cash\') {
                if (bankSelectGroup) bankSelectGroup.style.display = \'none\';
                if (cashAccountGroup) cashAccountGroup.style.display = \'block\';
            } else {
                if (bankSelectGroup) bankSelectGroup.style.display = \'block\';
                if (cashAccountGroup) cashAccountGroup.style.display = \'none\';
            }
        }
        paymentTypeRadios.forEach(r => {
            r.addEventListener(\'change\', function() { updatePaymentMode(this.value); });
        });

        // Live ROE Calculation Preview
        const amountInput = document.getElementById(\'main_total_amount\');
        const roePreviewBox = document.getElementById(\'roe_live_preview_box\');
        const roePreviewText = document.getElementById(\'roe_live_preview_text\');

        function updateLiveRoePreview() {
            if (!amountInput || !roePreviewBox || !roePreviewText) return;
            const amt = parseFloat(amountInput.value) || 0;
            const curr = currencySelect ? currencySelect.value : \'PKR\';
            const rate = parseFloat(rateInput ? rateInput.value : 1) || 1;

            if (amt > 0 && curr === \'SAR\' && rate > 0) {
                const equivPkr = amt * rate;
                roePreviewText.textContent = `${amt.toLocaleString(\'en-US\', {minimumFractionDigits: 2})} SAR × ${rate.toFixed(2)} ROE = PKR ${equivPkr.toLocaleString(\'en-US\', {minimumFractionDigits: 2})}`;
                roePreviewBox.style.display = \'block\';
            } else if (amt > 0 && curr === \'PKR\' && rate > 1) {
                const equivSar = amt / rate;
                roePreviewText.textContent = `PKR ${amt.toLocaleString(\'en-US\', {minimumFractionDigits: 2})} ÷ ${rate.toFixed(2)} ROE = ${equivSar.toLocaleString(\'en-US\', {minimumFractionDigits: 2})} SAR`;
                roePreviewBox.style.display = \'block\';
            } else {
                roePreviewBox.style.display = \'none\';
            }
        }

        if (amountInput) amountInput.addEventListener(\'input\', updateLiveRoePreview);
        if (rateInput) rateInput.addEventListener(\'input\', updateLiveRoePreview);
        if (currencySelect) currencySelect.addEventListener(\'change\', updateLiveRoePreview);
        updateLiveRoePreview();
';
    $newContent = substr_replace($newContent, $extraJs, $posScriptEnd, 0);
    echo "Added payment toggle and ROE preview in edit.blade.php\n";
}

file_put_contents('resources/views/expense_transaction/edit.blade.php', $newContent);
echo "Successfully updated edit.blade.php\n";
