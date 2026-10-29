<?php
$content = file_get_contents('resources/views/expense_transaction/create.blade.php');

// In JS section, locate "// Payee Type Toggle"
$payeeToggleMarker = '// Payee Type Toggle';
$posPayeeToggle = strpos($content, $payeeToggleMarker);
if ($posPayeeToggle === false) {
    die("payeeToggleMarker not found");
}

// Find where payeeRadios.forEach ends
$payeeLoopMarker = 'payeeRadios.forEach(radio => {';
$posPayeeLoop = strpos($content, $payeeLoopMarker, $posPayeeToggle);
$posPayeeLoopEnd = strpos($content, '});', $posPayeeLoop) + 3;

$newPayeeJs = '// Payee Type Toggle
        const payeeRadios = document.querySelectorAll(\'.payee-type-radio\');
        const supplierSec = document.getElementById(\'supplierSection\');
        const clientSec = document.getElementById(\'clientSection\');
        const staffSec = document.getElementById(\'staffSection\');
        const expenseSec = document.getElementById(\'expenseHeadSection\');
        const selfSec = document.getElementById(\'selfSection\');
        const jvSec = document.getElementById(\'jvSection\');
        const loanSec = document.getElementById(\'loanSection\');

        const defaultExpRef = "{{ $autoRef }}";
        const defaultJvRef = "{{ $autoJvRef }}";
        const refInput = document.getElementById(\'reference_no_input\');

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

                // Auto toggle reference number
                if (refInput) {
                    if (this.value === \'jv\') {
                        if (!refInput.value || refInput.value.startsWith(\'DT-EXP-\')) {
                            refInput.value = defaultJvRef;
                        }
                    } else {
                        if (!refInput.value || refInput.value.startsWith(\'DT-JV-\')) {
                            refInput.value = defaultExpRef;
                        }
                    }
                }
            });
        });';

$content = substr_replace($content, $newPayeeJs, $posPayeeToggle, $posPayeeLoopEnd - $posPayeeToggle);

// Now update JV Mode Switching
$jvModeMarker = '// JV Mode Switching';
$posJvMode = strpos($content, $jvModeMarker);
$posJvLoopEnd = strpos($content, '});', $posJvMode);
$posJvLoopEnd = strpos($content, '});', $posJvLoopEnd + 3) + 3;

$newJvModeJs = '// JV Mode Switching
        const jvModeRadios = document.querySelectorAll(\'.jv-mode-radio\');
        const jvPanelUniv = document.getElementById(\'jv_panel_univ\');
        const jvPanelCs = document.getElementById(\'jv_panel_cs\');
        const jvPanelMc = document.getElementById(\'jv_panel_mc\');
        const jvPanelSp = document.getElementById(\'jv_panel_sp\');
        const jvPanelGen = document.getElementById(\'jv_panel_gen\');

        function updateJvMode(mode) {
            if (jvPanelUniv) jvPanelUniv.classList.add(\'d-none\');
            if (jvPanelCs) jvPanelCs.classList.add(\'d-none\');
            if (jvPanelMc) jvPanelMc.classList.add(\'d-none\');
            if (jvPanelSp) jvPanelSp.classList.add(\'d-none\');
            if (jvPanelGen) jvPanelGen.classList.add(\'d-none\');

            if (mode === \'universal\' && jvPanelUniv) {
                jvPanelUniv.classList.remove(\'d-none\');
            } else if (mode === \'client_supplier\' && jvPanelCs) {
                jvPanelCs.classList.remove(\'d-none\');
            } else if (mode === \'multi_client\' && jvPanelMc) {
                jvPanelMc.classList.remove(\'d-none\');
                if (document.querySelectorAll(\'#multiClientTableBody tr\').length === 0) {
                    addMultiClientRow();
                }
            } else if (mode === \'single_party\' && jvPanelSp) {
                jvPanelSp.classList.remove(\'d-none\');
            } else if (mode === \'general\' && jvPanelGen) {
                jvPanelGen.classList.remove(\'d-none\');
            }
        }

        jvModeRadios.forEach(radio => {
            radio.addEventListener(\'change\', function() {
                updateJvMode(this.value);
            });
        });';

$content = substr_replace($content, $newJvModeJs, $posJvMode, $posJvLoopEnd - $posJvMode);

// Add Universal JV entities, Payment Type toggle, and Live ROE preview to the end of script
$posScriptEnd = strrpos($content, '</script>');
$additionalJs = '
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

        // Universal JV Dynamic Entities
        const universalEntities = {
            client: [
                @foreach($clients as $c)
                    { id: "{{ $c->id }}", name: "{{ addslashes($c->name) }}{{ $c->company_name ? \' (\' . addslashes($c->company_name) . \')\' : \'\' }} - {{ $c->phone ?? $c->cnic }}" },
                @endforeach
            ],
            supplier: [
                @foreach($suppliers as $s)
                    { id: "{{ $s->id }}", name: "{{ addslashes($s->name) }}{{ $s->company_name ? \' (\' . addslashes($s->company_name) . \')\' : \'\' }} - {{ strtoupper($s->service_type ?? \'Supplier\') }}" },
                @endforeach
            ],
            staff: [
                @foreach($staffMembers as $st)
                    { id: "{{ $st->id }}", name: "{{ addslashes($st->first_name . \' \' . $st->last_name) }} ({{ $st->designation->name ?? \'Staff\' }})" },
                @endforeach
            ],
            bank: [
                @foreach($banks as $b)
                    { id: "{{ $b->id }}", name: "{{ addslashes($b->name) }}{{ $b->account_name ? \' - \' . addslashes($b->account_name) : \'\' }}" },
                @endforeach
            ],
            cash: [
                @foreach($cashAccounts->filter(fn($a) => in_array($a->account_type, [\'cash\', \'general\'])) as $ca)
                    { id: "{{ $ca->id }}", name: "{{ addslashes($ca->account_name) }} (Opening: PKR {{ number_format($ca->opening_balance_pkr, 2) }})" },
                @endforeach
            ],
            loan: [
                @foreach($cashAccounts->filter(fn($a) => in_array($a->account_type, [\'loan_asset\', \'loan_liability\'])) as $la)
                    { id: "{{ $la->id }}", name: "{{ addslashes($la->account_name) }} ({{ $la->account_type === \'loan_asset\' ? \'Loan Asset\' : \'Loan Liability\' }})" },
                @endforeach
            ],
            expense: [
                @foreach($expenses as $e)
                    { id: "{{ $e->id }}", name: "{{ addslashes($e->name) }} (Office Expense Head)" },
                @endforeach
            ]
        };

        function populateUniversalDropdown(typeSelectId, targetSelectId, selectedVal = null) {
            const typeSelect = document.getElementById(typeSelectId);
            const targetSelect = document.getElementById(targetSelectId);
            if (!typeSelect || !targetSelect) return;
            const type = typeSelect.value;
            const items = universalEntities[type] || [];
            let html = `<option value="">-- Choose ${type.charAt(0).toUpperCase() + type.slice(1)} --</option>`;
            items.forEach(it => {
                const isSel = (selectedVal && selectedVal == it.id) ? \'selected\' : \'\';
                html += `<option value="${it.id}" ${isSel}>${it.name}</option>`;
            });
            targetSelect.innerHTML = html;
        }

        document.querySelectorAll(\'.jv-entity-type-select\').forEach(sel => {
            sel.addEventListener(\'change\', function() {
                const targetId = this.getAttribute(\'data-target\');
                populateUniversalDropdown(this.id, targetId);
            });
        });

        populateUniversalDropdown(\'jv_from_type\', \'jv_from_id\');
        populateUniversalDropdown(\'jv_to_type\', \'jv_to_id\');
';

$content = substr_replace($content, $additionalJs, $posScriptEnd, 0);

file_put_contents('resources/views/expense_transaction/create.blade.php', $content);
echo "Script updated successfully in create.blade.php\n";
