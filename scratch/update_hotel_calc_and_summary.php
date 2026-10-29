<?php
echo "Updating Hotel calculation, exact passenger allocation, and summary table...\n";

function replaceCode($file, $search, $replace, $desc) {
    $c = file_get_contents($file);
    if (strpos($c, $search) !== false) {
        $c = str_replace($search, $replace, $c);
        file_put_contents($file, $c);
        echo "✓ {$desc} (CRLF)\n";
        return true;
    }
    $sLF = str_replace("\r\n", "\n", $search);
    $rLF = str_replace("\r\n", "\n", $replace);
    if (strpos($c, $sLF) !== false) {
        $c = str_replace($sLF, $rLF, $c);
        file_put_contents($file, $c);
        echo "✓ {$desc} (LF)\n";
        return true;
    }
    echo "✗ Failed: {$desc}\n";
    return false;
}

// -------------------------------------------------------------
// 1. Table headers & footers
// -------------------------------------------------------------
$oldTableHead = '<th style="color: #c2410c;">QTY</th>
                                                <th style="color: #c2410c;">Meal Plan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="hotelAccommodationSummaryRows">
                                            <tr>
                                                <td colspan="8" class="text-muted py-2">No hotels added yet.</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="fw-bold bg-white">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 fw-bold">Total</td>
                                                <td id="hotel_summary_total_nights" class="fw-bold text-dark fs-13">0</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>';

$newTableHead = '<th style="color: #c2410c;">QTY</th>
                                                <th style="color: #c2410c;">Meal Plan</th>
                                                <th style="color: #c2410c;" class="text-end">Total Cost</th>
                                                <th style="color: #c2410c;" class="text-end">Total Sale</th>
                                            </tr>
                                        </thead>
                                        <tbody id="hotelAccommodationSummaryRows">
                                            <tr>
                                                <td colspan="10" class="text-muted py-2">No hotels added yet.</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="fw-bold bg-white">
                                            <tr>
                                                <td colspan="4" class="text-end pe-3 fw-bold">Total</td>
                                                <td id="hotel_summary_total_nights" class="fw-bold text-dark fs-13">0</td>
                                                <td colspan="3"></td>
                                                <td id="hotel_summary_total_cost" class="text-end text-danger fw-bold fs-12">0.00 SAR</td>
                                                <td id="hotel_summary_total_sell" class="text-end text-success fw-bold fs-12">0.00 SAR</td>
                                            </tr>
                                        </tfoot>';

$cFile = __DIR__ . '/../resources/views/booking/create.blade.php';
$eFile = __DIR__ . '/../resources/views/booking/edit.blade.php';

replaceCode($cFile, $oldTableHead, $newTableHead, "Create: Hotel summary table headers & footers");
replaceCode($eFile, $oldTableHead, $newTableHead, "Edit: Hotel summary table headers & footers");

// -------------------------------------------------------------
// 2. autoDivideHotelRates & allocateExactAmount
// -------------------------------------------------------------
$oldAutoDivide = '        // Point 8: Automatic & Manual Main Cost Division Across Passengers
        function autoDivideHotelRates(block, whichField) {
            if (!block) return;
            const personRows = block.querySelectorAll(\'.hotel-person-row\');
            const paxCount = personRows.length > 0 ? personRows.length : (parseInt(document.getElementById(\'no_of_pax\')?.value) || 1);
            if (paxCount <= 0) return;

            if (!whichField || whichField === \'cost\') {
                const costRate = parseFloat(block.querySelector(\'.hotel-cost-rate\')?.value) || 0;
                const perPaxCost = (costRate > 0) ? (costRate / paxCount).toFixed(2) : \'0.00\';
                personRows.forEach(el => {
                    const cInput = el.querySelector(\'.hotel-person-cost\');
                    if (cInput) cInput.value = perPaxCost;
                });
            }

            if (!whichField || whichField === \'sell\') {
                const sellRate = parseFloat(block.querySelector(\'.hotel-sell-rate\')?.value) || 0;
                const perPaxSell = (sellRate > 0) ? (sellRate / paxCount).toFixed(2) : \'0.00\';
                personRows.forEach(el => {
                    const sInput = el.querySelector(\'.hotel-person-sell\');
                    if (sInput) sInput.value = perPaxSell;
                });
            }
        }';

$newAutoDivide = '        // Integer-cent allocation helper so sum ALWAYS matches total exactly (no 99.99 or 200.01)
        function allocateExactAmount(totalAmount, count) {
            if (count <= 0) return [];
            const totalCents = Math.round(totalAmount * 100);
            const baseCents = Math.floor(totalCents / count);
            let remainder = totalCents - (baseCents * count);

            const amounts = [];
            for (let i = 0; i < count; i++) {
                const paxCents = baseCents + (i < remainder ? 1 : 0);
                amounts.push((paxCents / 100).toFixed(2));
            }
            return amounts;
        }

        // Point 8: Automatic & Manual Main Cost Division Across Passengers
        function autoDivideHotelRates(block, whichField) {
            if (!block) return;
            const personRows = block.querySelectorAll(\'.hotel-person-row\');
            const paxCount = personRows.length > 0 ? personRows.length : (parseInt(document.getElementById(\'no_of_pax\')?.value) || 1);
            if (paxCount <= 0) return;

            if (!whichField || whichField === \'cost\') {
                const costRate = parseFloat(block.querySelector(\'.hotel-cost-rate\')?.value) || 0;
                if (costRate > 0) {
                    const costAmounts = allocateExactAmount(costRate, paxCount);
                    personRows.forEach((el, idx) => {
                        const cInput = el.querySelector(\'.hotel-person-cost\');
                        if (cInput) cInput.value = costAmounts[idx] || \'0.00\';
                    });
                } else {
                    personRows.forEach(el => {
                        const cInput = el.querySelector(\'.hotel-person-cost\');
                        if (cInput) cInput.value = \'0.00\';
                    });
                }
            }

            if (!whichField || whichField === \'sell\') {
                const sellRate = parseFloat(block.querySelector(\'.hotel-sell-rate\')?.value) || 0;
                if (sellRate > 0) {
                    const sellAmounts = allocateExactAmount(sellRate, paxCount);
                    personRows.forEach((el, idx) => {
                        const sInput = el.querySelector(\'.hotel-person-sell\');
                        if (sInput) sInput.value = sellAmounts[idx] || \'0.00\';
                    });
                } else {
                    personRows.forEach(el => {
                        const sInput = el.querySelector(\'.hotel-person-sell\');
                        if (sInput) sInput.value = \'0.00\';
                    });
                }
            }
        }';

replaceCode($cFile, $oldAutoDivide, $newAutoDivide, "Create: autoDivideHotelRates exact cents");
replaceCode($eFile, $oldAutoDivide, $newAutoDivide, "Edit: autoDivideHotelRates exact cents");

// -------------------------------------------------------------
// 3. calculateHotelRow rate preservation
// -------------------------------------------------------------
$oldCalcPersonRates = '            if (hasPersonRates) {
                costCurr = sumPersonCostForeign;
                sellCurr = sumPersonSellForeign;
                if (nights > 0) {
                    const crInput = row.querySelector(\'.hotel-cost-rate\');
                    if (crInput && source !== \'costRate\') {
                        crInput.value = (costCurr / nights).toFixed(2);
                    }
                    const srInput = row.querySelector(\'.hotel-sell-rate\');
                    if (srInput && source !== \'sellRate\') {
                        srInput.value = (sellCurr / nights).toFixed(2);
                    }
                }
            }';

$newCalcPersonRates = '            if (hasPersonRates) {
                costCurr = Math.round(sumPersonCostForeign * 100) / 100;
                sellCurr = Math.round(sumPersonSellForeign * 100) / 100;

                const crInput = row.querySelector(\'.hotel-cost-rate\');
                const srInput = row.querySelector(\'.hotel-sell-rate\');

                // If user entered a main costRate, snap sum to exact costRate if difference is rounding cent (< 0.05)
                if (crInput && source === \'costRate\') {
                    const enteredCost = parseFloat(crInput.value) || 0;
                    if (Math.abs(costCurr - (enteredCost * nights)) < 0.05) {
                        costCurr = enteredCost * nights;
                    }
                } else if (crInput && source !== \'costRate\') {
                    if (nights > 0) {
                        crInput.value = (Math.round((costCurr / nights) * 100) / 100).toFixed(2);
                    }
                }

                // If user entered a main sellRate, snap sum to exact sellRate if difference is rounding cent (< 0.05)
                if (srInput && source === \'sellRate\') {
                    const enteredSell = parseFloat(srInput.value) || 0;
                    if (Math.abs(sellCurr - (enteredSell * nights)) < 0.05) {
                        sellCurr = enteredSell * nights;
                    }
                } else if (srInput && source !== \'sellRate\') {
                    if (nights > 0) {
                        srInput.value = (Math.round((sellCurr / nights) * 100) / 100).toFixed(2);
                    }
                }
            }';

replaceCode($cFile, $oldCalcPersonRates, $newCalcPersonRates, "Create: calculateHotelRow rate preservation");
replaceCode($eFile, $oldCalcPersonRates, $newCalcPersonRates, "Edit: calculateHotelRow rate preservation");

// -------------------------------------------------------------
// 4. updateHotelStaySummary table row HTML
// -------------------------------------------------------------
$oldSummaryRow = '                summaryTableHtml += `<tr>
                    <td class="text-start fw-bold text-dark">${hName}</td>
                    <td class="fw-semibold">${city}</td>
                    <td>${cin || \'—\'}</td>
                    <td>${cout || \'—\'}</td>
                    <td class="fw-bold text-dark">${nights}</td>
                    <td>${roomType}</td>
                    <td>${rooms || 1}</td>
                    <td>${mealPlan}</td>
                </tr>`;
            });

            const sumDisp = document.getElementById(\'hotel_stay_summary_dates\');
            const nightsDisp = document.getElementById(\'hotel_stay_total_nights\');
            const roomsDisp = document.getElementById(\'hotel_stay_total_rooms\');
            const summaryTableBody = document.getElementById(\'hotelAccommodationSummaryRows\');
            const summaryNightsTotal = document.getElementById(\'hotel_summary_total_nights\');

            if (summaryTableBody) {
                if (blocks.length === 0) {
                    summaryTableBody.innerHTML = \'<tr><td colspan="8" class="text-muted py-2">No hotels added yet.</td></tr>\';
                } else {
                    summaryTableBody.innerHTML = summaryTableHtml;
                }
            }';

$newSummaryRow = '                const costSarText = b.querySelector(\'.hotel-cost-sar-disp\')?.textContent?.trim() || \'0.00 SAR\';
                const costPkrText = b.querySelector(\'.hotel-cost-pkr-disp\')?.textContent?.trim() || \'0.00 PKR\';
                const sellSarText = b.querySelector(\'.hotel-sell-sar-disp\')?.textContent?.trim() || \'0.00 SAR\';
                const sellPkrText = b.querySelector(\'.hotel-sell-pkr-disp\')?.textContent?.trim() || \'0.00 PKR\';

                summaryTableHtml += `<tr>
                    <td class="text-start fw-bold text-dark">${hName}</td>
                    <td class="fw-semibold">${city}</td>
                    <td>${cin || \'—\'}</td>
                    <td>${cout || \'—\'}</td>
                    <td class="fw-bold text-dark">${nights}</td>
                    <td>${roomType}</td>
                    <td>${rooms || 1}</td>
                    <td>${mealPlan}</td>
                    <td class="text-end text-danger fw-semibold">
                        <span>${costSarText}</span><br>
                        <small class="text-muted fw-normal">${costPkrText}</small>
                    </td>
                    <td class="text-end text-success fw-semibold">
                        <span>${sellSarText}</span><br>
                        <small class="text-muted fw-normal">${sellPkrText}</small>
                    </td>
                </tr>`;
            });

            const sumDisp = document.getElementById(\'hotel_stay_summary_dates\');
            const nightsDisp = document.getElementById(\'hotel_stay_total_nights\');
            const roomsDisp = document.getElementById(\'hotel_stay_total_rooms\');
            const summaryTableBody = document.getElementById(\'hotelAccommodationSummaryRows\');
            const summaryNightsTotal = document.getElementById(\'hotel_summary_total_nights\');

            if (summaryTableBody) {
                if (blocks.length === 0) {
                    summaryTableBody.innerHTML = \'<tr><td colspan="10" class="text-muted py-2">No hotels added yet.</td></tr>\';
                } else {
                    summaryTableBody.innerHTML = summaryTableHtml;
                }
            }

            const totCostSarText = document.getElementById(\'hotel_cost_sar_total\')?.textContent || \'0.00 SAR\';
            const totCostPkrText = document.getElementById(\'hotel_cost_pkr_total\')?.textContent || \'0.00 PKR\';
            const totSellSarText = document.getElementById(\'hotel_sell_sar_total\')?.textContent || \'0.00 SAR\';
            const totSellPkrText = document.getElementById(\'hotel_sell_pkr_total\')?.textContent || \'0.00 PKR\';

            const summaryCostTotal = document.getElementById(\'hotel_summary_total_cost\');
            const summarySellTotal = document.getElementById(\'hotel_summary_total_sell\');
            if (summaryCostTotal) summaryCostTotal.innerHTML = `<span>${totCostSarText}</span><br><small class="text-muted fw-normal">${totCostPkrText}</small>`;
            if (summarySellTotal) summarySellTotal.innerHTML = `<span>${totSellSarText}</span><br><small class="text-muted fw-normal">${totSellPkrText}</small>`;';

replaceCode($cFile, $oldSummaryRow, $newSummaryRow, "Create: updateHotelStaySummary table row HTML");
replaceCode($eFile, $oldSummaryRow, $newSummaryRow, "Edit: updateHotelStaySummary table row HTML");

echo "\nCompleted all replacements!\n";
