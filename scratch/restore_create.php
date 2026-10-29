<?php
$head = file_get_contents('scratch/head_create.blade.php');

// 1. In ROW 2, add cash_account_group right next to bank_select_group:
$old_row2_bank = '<div class="col-md-6">
                                <label class="form-label fs-13 fw-semibold">Bank</label>
                                <select name="bank_id" class="form-select form-select-sm">
                                    <option value="">-- Select Bank --</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id }}" {{ old(\'bank_id\') == $b->id ? \'selected\' : \'\' }}>
                                            {{ $b->name }}{{ !empty($b->account_name) ? \' - \' . $b->account_name : \'\' }}{{ !empty($b->account_number) && $b->account_number !== \'0\' ? \' (\' . $b->account_number . \')\' : \'\' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>';

$new_row2_bank = '<div class="col-md-6" id="bank_select_group">
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

                            <div class="col-md-6 d-none" id="cash_account_group">
                                <label class="form-label fs-13 fw-semibold"><i class="mdi mdi-cash-register text-success me-1"></i> Cash Account <span class="text-danger">*</span></label>
                                <select name="account_id" id="account_id_select" class="form-select form-select-sm">
                                    <option value="">-- Select Cash Account --</option>
                                    @if(isset($cashAccounts))
                                        @foreach ($cashAccounts as $ca)
                                            <option value="{{ $ca->id }}" {{ old(\'account_id\') == $ca->id ? \'selected\' : \'\' }}>
                                                {{ $ca->account_name }} ({{ strtoupper(str_replace(\'_\', \' \', $ca->account_type)) }}) — Opening: PKR {{ number_format($ca->opening_balance_pkr, 2) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>';

if (strpos($head, $old_row2_bank) !== false) {
    $head = str_replace($old_row2_bank, $new_row2_bank, $head);
    echo "Replaced row 2 bank/cash successfully.\n";
} else {
    echo "Could not find exact old_row2_bank, let's inspect.\n";
}

// 2. In JV Section, support client, supplier, or both:
$old_jv = '<!-- 7. JV VOUCHER SECTION -->
                                <div id="jvSection" class="{{ old(\'payee_type\') == \'jv\' ? \'\' : \'d-none\' }}">
                                    <div class="p-3 bg-white border rounded">
                                        <div class="row g-3 align-items-center mb-2">
                                            <div class="col-md-4">
                                                <label class="form-label fs-12 fw-semibold mb-1">JV Party Link (Optional)</label>
                                                <select id="jv_party_toggle" class="form-select form-select-sm">
                                                    <option value="none" {{ !old(\'jv_client_id\') && !old(\'jv_supplier_id\') ? \'selected\' : \'\' }}>General Journal / No Party</option>
                                                    <option value="client" {{ old(\'jv_client_id\') ? \'selected\' : \'\' }}>Client</option>
                                                    <option value="supplier" {{ old(\'jv_supplier_id\') ? \'selected\' : \'\' }}>Supplier</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8 {{ old(\'jv_client_id\') ? \'\' : \'d-none\' }}" id="jv_client_container">
                                                <label class="form-label fs-12 fw-semibold mb-1">Select Client</label>
                                                <select name="jv_client_id" class="form-select form-select-sm">
                                                    <option value="">-- Select Client --</option>
                                                    @foreach ($clients as $c)
                                                        <option value="{{ $c->id }}" {{ old(\'jv_client_id\') == $c->id ? \'selected\' : \'\' }}>
                                                            {{ $c->name }}{{ $c->phone ? \' (\' . $c->phone . \')\' : \'\' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-8 {{ old(\'jv_supplier_id\') ? \'\' : \'d-none\' }}" id="jv_supplier_container">
                                                <label class="form-label fs-12 fw-semibold mb-1">Select Supplier</label>
                                                <select name="jv_supplier_id" class="form-select form-select-sm">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $s)
                                                        <option value="{{ $s->id }}" {{ old(\'jv_supplier_id\') == $s->id ? \'selected\' : \'\' }}>
                                                            {{ $s->name }}{{ $s->company_name ? \' (\' . $s->company_name . \')\' : \'\' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="alert alert-info py-2 px-3 mb-0 fs-12 border-0 bg-soft-info text-info">
                                            <i class="mdi mdi-information-outline me-1"></i>
                                            <strong>Journal Voucher (JV):</strong> For non-cash adjustments, inter-account transfers, discounts, and ledger write-offs.
                                        </div>
                                    </div>
                                </div>';

$new_jv = '<!-- 7. JV VOUCHER SECTION -->
                                <div id="jvSection" class="{{ old(\'payee_type\') == \'jv\' ? \'\' : \'d-none\' }}">
                                    <div class="p-3 bg-white border rounded">
                                        <div class="row g-3 align-items-center mb-2">
                                            <div class="col-md-4">
                                                <label class="form-label fs-12 fw-semibold mb-1">JV Party Link</label>
                                                <select id="jv_party_toggle" name="jv_party_toggle" class="form-select form-select-sm">
                                                    <option value="none" {{ (!old(\'jv_client_id\') && !old(\'jv_supplier_id\') && old(\'jv_party_toggle\') !== \'both\') ? \'selected\' : \'\' }}>General Journal / No Party</option>
                                                    <option value="client" {{ old(\'jv_party_toggle\') == \'client\' || (old(\'jv_client_id\') && !old(\'jv_supplier_id\')) ? \'selected\' : \'\' }}>Client</option>
                                                    <option value="supplier" {{ old(\'jv_party_toggle\') == \'supplier\' || (!old(\'jv_client_id\') && old(\'jv_supplier_id\')) ? \'selected\' : \'\' }}>Supplier</option>
                                                    <option value="both" {{ old(\'jv_party_toggle\') == \'both\' || (old(\'jv_client_id\') && old(\'jv_supplier_id\')) ? \'selected\' : \'\' }}>Both (Client & Supplier Contra)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 {{ (old(\'jv_client_id\') || old(\'jv_party_toggle\') == \'client\' || old(\'jv_party_toggle\') == \'both\') ? \'\' : \'d-none\' }}" id="jv_client_container">
                                                <label class="form-label fs-12 fw-semibold mb-1">Select Client</label>
                                                <select name="jv_client_id" id="jv_client_id_select" class="form-select form-select-sm">
                                                    <option value="">-- Select Client --</option>
                                                    @foreach ($clients as $c)
                                                        <option value="{{ $c->id }}" {{ old(\'jv_client_id\') == $c->id ? \'selected\' : \'\' }}>
                                                            {{ $c->name }}{{ $c->phone ? \' (\' . $c->phone . \')\' : \'\' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 {{ (old(\'jv_supplier_id\') || old(\'jv_party_toggle\') == \'supplier\' || old(\'jv_party_toggle\') == \'both\') ? \'\' : \'d-none\' }}" id="jv_supplier_container">
                                                <label class="form-label fs-12 fw-semibold mb-1">Select Supplier</label>
                                                <select name="jv_supplier_id" id="jv_supplier_id_select" class="form-select form-select-sm">
                                                    <option value="">-- Select Supplier --</option>
                                                    @foreach ($suppliers as $s)
                                                        <option value="{{ $s->id }}" {{ old(\'jv_supplier_id\') == $s->id ? \'selected\' : \'\' }}>
                                                            {{ $s->name }}{{ $s->company_name ? \' (\' . $s->company_name . \')\' : \'\' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="alert alert-info py-2 px-3 mb-0 fs-12 border-0 bg-soft-info text-info">
                                            <i class="mdi mdi-information-outline me-1"></i>
                                            <strong>Journal Voucher (JV):</strong> For non-cash adjustments, client-supplier contra, inter-account transfers, discounts, and ledger write-offs.
                                        </div>
                                    </div>
                                </div>';

if (strpos($head, $old_jv) !== false) {
    $head = str_replace($old_jv, $new_jv, $head);
    echo "Replaced JV section successfully.\n";
} else {
    echo "Could not find exact old_jv, let's inspect.\n";
}

// 3. Add JS for Payment Type toggle and JV both toggle
$js_hook = "flatpickr(\".flatpickr-date\", { dateFormat: \"Y-m-d\", allowInput: true });";
$new_js = "flatpickr(\".flatpickr-date\", { dateFormat: \"Y-m-d\", allowInput: true });

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
        document.querySelectorAll('input[name=\"payment_type\"]').forEach(r => {
            r.addEventListener('change', togglePaymentTypeFields);
        });
        togglePaymentTypeFields();";

$head = str_replace($js_hook, $new_js, $head);

// Also JV party toggle JS update
$old_jv_js = "if (jvToggle) {
            jvToggle.addEventListener('change', function() {
                if (jvClientBox) jvClientBox.classList.add('d-none');
                if (jvSupplierBox) jvSupplierBox.classList.add('d-none');
                if (this.value === 'client' && jvClientBox) {
                    jvClientBox.classList.remove('d-none');
                } else if (this.value === 'supplier' && jvSupplierBox) {
                    jvSupplierBox.classList.remove('d-none');
                }
            });
        }";

$new_jv_js = "if (jvToggle) {
            jvToggle.addEventListener('change', function() {
                if (jvClientBox) jvClientBox.classList.add('d-none');
                if (jvSupplierBox) jvSupplierBox.classList.add('d-none');
                if (this.value === 'client' && jvClientBox) {
                    jvClientBox.classList.remove('d-none');
                } else if (this.value === 'supplier' && jvSupplierBox) {
                    jvSupplierBox.classList.remove('d-none');
                } else if (this.value === 'both') {
                    if (jvClientBox) jvClientBox.classList.remove('d-none');
                    if (jvSupplierBox) jvSupplierBox.classList.remove('d-none');
                }
            });
        }";

$head = str_replace($old_jv_js, $new_jv_js, $head);

file_put_contents('resources/views/expense_transaction/create.blade.php', $head);
echo "Written resources/views/expense_transaction/create.blade.php (" . strlen($head) . " bytes)\n";
