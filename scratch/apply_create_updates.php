<?php

$create = file_get_contents('resources/views/booking/create.blade.php');

// 1. Update addNewHotel in create.blade.php
$targetAddNewHotelStart = <<<'JS'
        function addNewHotel(loc = 'makkah') {
            const noHotelAlert = document.getElementById('noHotelAlert');
            if (noHotelAlert) noHotelAlert.style.display = 'none';

            let locLabel = 'Makkah Hotel';
            let locVal = 'makkah';
            if (loc === 'madinah') { locLabel = 'Madinah Hotel'; locVal = 'madinah'; }
            else if (loc === 'other') { locLabel = 'Other Hotel'; locVal = 'other'; }
            else if (loc === 'jeddah') { locLabel = 'Jeddah Hotel'; locVal = 'jeddah'; }

            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
            const isPkr = (flightCurr === 'PKR');
            const hotelVoucherBase = document.getElementById('hotel_voucher_no')?.value || '{{ $defaultVouchers["hotel"] ?? ("HTL-" . date("Y") . "-00001") }}';
            const childVoucher = hotelVoucherBase + '-' + (hotelIdx + 1);

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="${hotelIdx}">
JS;

$replaceAddNewHotelStart = <<<'JS'
        function addNewHotel(loc = 'makkah') {
            const noHotelAlert = document.getElementById('noHotelAlert');
            if (noHotelAlert) noHotelAlert.style.display = 'none';

            let locLabel = 'Makkah Hotel';
            let locVal = 'makkah';
            if (loc === 'madinah') { locLabel = 'Madinah Hotel'; locVal = 'madinah'; }
            else if (loc === 'other') { locLabel = 'Other Hotel'; locVal = 'other'; }
            else if (loc === 'jeddah') { locLabel = 'Jeddah Hotel'; locVal = 'jeddah'; }

            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
            const isPkr = (flightCurr === 'PKR');
            const hotelVoucherBase = document.getElementById('hotel_voucher_no')?.value || '{{ $defaultVouchers["hotel"] ?? ("HTL-" . date("Y") . "-00001") }}';
            const childVoucher = hotelVoucherBase + '-' + (hotelIdx + 1);

            // Filter hotels by location
            const relevantHotelsOptions = buildHotelsOptions(loc === 'makkah' ? 'makkah' : (loc === 'madinah' ? 'madinah' : null));

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="${hotelIdx}" data-location="${locVal}" data-city="${locVal.toUpperCase()}">
JS;

if (strpos($create, $targetAddNewHotelStart) !== false) {
    $create = str_replace($targetAddNewHotelStart, $replaceAddNewHotelStart, $create);
    echo "Updated addNewHotel header & data-location in create.blade.php\n";
} else {
    echo "Warning: targetAddNewHotelStart not found\n";
}

// 2. In addNewHotel, replace select hotel-name-select to use relevantHotelsOptions
$targetHotelSelect = <<<'JS'
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select">
                            ${hotelsOptions}
                        </select>
JS;

$replaceHotelSelect = <<<'JS'
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select" onchange="updateHotelStaySummary()">
                            ${relevantHotelsOptions}
                        </select>
JS;

if (strpos($create, $targetHotelSelect) !== false) {
    $create = str_replace($targetHotelSelect, $replaceHotelSelect, $create);
    echo "Updated hotel-name-select in create.blade.php\n";
}

// 3. Add Meal Plan and Room View in addNewHotel template
$targetRoomInputs = <<<'JS'
                    <div class="col-md-2">
                        <label class="form-label">Hotel Room #</label>
                        <input type="text" name="hotels[${hotelIdx}][room_number]" class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="0">
                    </div>
JS;

$replaceRoomInputs = <<<'JS'
                    <div class="col-md-2">
                        <label class="form-label">Hotel Room #</label>
                        <input type="text" name="hotels[${hotelIdx}][room_number]" class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><i class="mdi mdi-silverware me-1 text-primary"></i> Meal Plan</label>
                        <select name="hotels[${hotelIdx}][meal_plan]" class="form-select hotel-meal-plan" onchange="updateHotelStaySummary()">
                            ${buildMealPlansOptions('BB')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><i class="mdi mdi-eye me-1 text-info"></i> Room View</label>
                        <select name="hotels[${hotelIdx}][room_view]" class="form-select hotel-room-view">
                            ${buildRoomViewsOptions('Standard View')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="0">
                    </div>
JS;

if (strpos($create, $targetRoomInputs) !== false) {
    $create = str_replace($targetRoomInputs, $replaceRoomInputs, $create);
    echo "Added Meal Plan and Room View inputs in addNewHotel in create.blade.php\n";
}

// 4. Update updateHotelStaySummary in create.blade.php
$targetSummaryUpdate = <<<'JS'
                // City
                const citySelect = b.querySelector('.hotel-city-select');
                const city = (citySelect?.value || b.getAttribute('data-city') || 'MAKKAH').toUpperCase();

                // Room Type, Rooms, Meal
                const roomType = (b.querySelector('.hotel-room-type')?.value || 'DOUBLE').toUpperCase();
                const mealPlan = (b.querySelector('.hotel-meal-plan')?.value || 'B/B').toUpperCase();
JS;

$replaceSummaryUpdate = <<<'JS'
                // City (from data-location or data-city)
                const locAttr = b.getAttribute('data-location') || b.getAttribute('data-city') || 'makkah';
                const city = locAttr.toUpperCase();

                // Room Type, Rooms, Meal Plan
                const roomType = (b.querySelector('.hotel-room-type')?.value || 'DOUBLE').toUpperCase();
                const mealPlan = (b.querySelector('.hotel-meal-plan')?.value || 'BB').toUpperCase();
JS;

if (strpos($create, $targetSummaryUpdate) !== false) {
    $create = str_replace($targetSummaryUpdate, $replaceSummaryUpdate, $create);
    echo "Updated updateHotelStaySummary city & meal plan reading in create.blade.php\n";
}

// 5. In passenger row generation (in create.blade.php), use flight_time-picker and default flight_date from main flight booking date
$targetPaxFlightRow = <<<'JS'
                    {{-- Point 11: Separate Flight Date & Time --}}
                    <td>
                        <div class="mb-1">
                            <input type="date" name="flight_persons[${i}][flight_date]" class="form-control form-control-sm person-flight-date" value="${pDate}">
                        </div>
                        <input type="time" name="flight_persons[${i}][flight_time]" class="form-control form-control-sm person-flight-time" value="${pTime}">
                    </td>
JS;

$replacePaxFlightRow = <<<'JS'
                    {{-- Point 11: Separate Flight Date & Time --}}
                    <td>
                        <div class="mb-1">
                            <input type="date" name="flight_persons[${i}][flight_date]" class="form-control form-control-sm person-flight-date" value="${pDate}" title="Flight date for this passenger">
                        </div>
                        <input type="text" name="flight_persons[${i}][flight_time]" class="form-control form-control-sm person-flight-time flight-time-picker" value="${pTime}" placeholder="hh:mm">
                    </td>
JS;

if (strpos($create, $targetPaxFlightRow) !== false) {
    $create = str_replace($targetPaxFlightRow, $replacePaxFlightRow, $create);
    echo "Updated person-flight-time to flight-time-picker in create.blade.php\n";
}

// 6. Add JS helper at the end of scripts for time formatting, cascading, and booking category
$targetScriptEnd = <<<'JS'
    });
</script>
@endsection
JS;

$replaceScriptEnd = <<<'JS'
        // --- TIME FORMAT & FLATPICKR HANDLING ---
        function initFlightTimePickers(format = '12') {
            const is24 = (format === '24');
            document.querySelectorAll('.flight-time-picker').forEach(el => {
                if (el._flatpickr) {
                    el._flatpickr.destroy();
                }
                flatpickr(el, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: is24 ? "H:i" : "h:i K",
                    time_24hr: is24,
                    allowInput: true
                });
            });
        }

        const initialTimeFmt = document.querySelector('input[name="flight_time_format"]:checked')?.value || '12';
        initFlightTimePickers(initialTimeFmt);

        document.querySelectorAll('.flight-time-format-radio').forEach(r => {
            r.addEventListener('change', function() {
                initFlightTimePickers(this.value);
            });
        });

        // --- CASCADE MAIN FLIGHT DETAILS TO PASSENGERS ---
        function syncMainFlightDetailsToPassengers() {
            const mainDate = document.getElementById('flight_booking_date')?.value || document.querySelector('input[name="departure_date"]')?.value || '';
            const mainSup  = document.querySelector('select[name="flight_supplier_id"]')?.value || '';
            const mainPnr  = document.querySelector('input[name="departure_pnr"]')?.value || '';
            const mainTime = document.querySelector('input[name="departure_time"]')?.value || '';

            document.querySelectorAll('.flight-person-row').forEach(row => {
                const dInput = row.querySelector('.person-flight-date');
                const sSelect = row.querySelector('.person-flight-supplier');
                const pInput = row.querySelector('.person-flight-pnr');
                const tInput = row.querySelector('.person-flight-time');

                if (dInput && !dInput.getAttribute('data-customized')) dInput.value = mainDate;
                if (sSelect && !sSelect.getAttribute('data-customized') && mainSup) $(sSelect).val(mainSup).trigger('change');
                if (pInput && !pInput.getAttribute('data-customized')) pInput.value = mainPnr;
                if (tInput && !tInput.getAttribute('data-customized')) tInput.value = mainTime;
            });
        }

        $('#flight_booking_date, input[name="departure_date"], select[name="flight_supplier_id"], input[name="departure_pnr"], input[name="departure_time"]').on('change input', syncMainFlightDetailsToPassengers);

        $(document).on('change input', '.person-flight-date, .person-flight-supplier, .person-flight-pnr, .person-flight-time', function() {
            this.setAttribute('data-customized', 'true');
        });

        // --- BOOKING CATEGORY SYNC ---
        $(document).on('change', '.booking-category-radio', function() {
            const cat = this.value;
            const currSelect = document.getElementById('flight_currency');
            const costRoe = document.getElementById('flight_cost_roe');
            const saleRoe = document.getElementById('flight_sale_roe');
            const vInput = document.getElementById('booking_voucher_input');
            const vDisp = document.getElementById('booking_voucher_disp');
            const fltVoucher = document.getElementById('flight_voucher_no')?.value;

            if (cat === 'domestic') {
                if (currSelect) $(currSelect).val('PKR').trigger('change');
                if (costRoe) costRoe.value = '1';
                if (saleRoe) saleRoe.value = '1';
                if (fltVoucher && vInput) {
                    vInput.value = fltVoucher;
                    if (vDisp) vDisp.textContent = fltVoucher;
                }
            } else {
                if (currSelect && currSelect.value === 'PKR') $(currSelect).val('SAR').trigger('change');
                if (costRoe && costRoe.value === '1') costRoe.value = '75';
                if (saleRoe && saleRoe.value === '1') saleRoe.value = '75';
            }
        });

    });
</script>
@endsection
JS;

if (strpos($create, $targetScriptEnd) !== false) {
    $create = str_replace($targetScriptEnd, $replaceScriptEnd, $create);
    echo "Added Flight & Category synchronization handlers to create.blade.php\n";
}

file_put_contents('resources/views/booking/create.blade.php', $create);
echo "Successfully updated resources/views/booking/create.blade.php\n";
