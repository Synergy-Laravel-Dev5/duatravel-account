<?php

$edit = file_get_contents('resources/views/booking/edit.blade.php');

// 1. Add Booking Category in Tab 2
$targetVoucherCard = <<<'HTML'
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="mdi mdi-ticket-confirmation text-primary fs-18"></i>
                                            <strong class="text-dark">Booking Voucher No:</strong>
                                            <span class="badge bg-primary fs-13 px-2 py-1 font-monospace">{{ $booking->voucher_number ?? ('BK-' . date('Y') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT)) }}</span>
                                        </div>
                                        <input type="hidden" name="voucher_number" value="{{ $booking->voucher_number ?? ('BK-' . date('Y') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT)) }}">
                                    </div>
                                </div>
HTML;

$replacementVoucherCard = <<<'HTML'
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="mdi mdi-ticket-confirmation text-primary fs-18"></i>
                                            <strong class="text-dark">Booking Voucher No:</strong>
                                            <span class="badge bg-primary fs-13 px-2 py-1 font-monospace" id="booking_voucher_disp">{{ $booking->voucher_number ?? ('BK-' . date('Y') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT)) }}</span>
                                        </div>
                                        <input type="hidden" name="voucher_number" id="booking_voucher_input" value="{{ $booking->voucher_number ?? ('BK-' . date('Y') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT)) }}">
                                    </div>
                                </div>

                                {{-- BOOKING CATEGORY / SECTOR TYPE (Umrah vs Domestic vs International) --}}
                                <div class="col-12">
                                    <div class="card border mb-2 bg-light shadow-sm">
                                        <div class="card-body p-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <span class="fw-bold text-dark fs-13">
                                                <i class="mdi mdi-format-list-bulleted-type text-primary me-1"></i> Booking Category / Sector:
                                            </span>
                                            <div class="d-flex flex-wrap gap-3">
                                                @php
                                                    $bCat = old('booking_category', $booking->booking_category ?? 'umrah');
                                                @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_umrah" value="umrah" {{ $bCat === 'umrah' ? 'checked' : '' }}>
                                                    <label class="form-check-label fs-13 fw-semibold text-primary" for="bcat_umrah">
                                                        <i class="mdi mdi-mosque me-1"></i> Umrah Pilgrimage
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_domestic" value="domestic" {{ $bCat === 'domestic' ? 'checked' : '' }}>
                                                    <label class="form-check-label fs-13 fw-semibold text-success" for="bcat_domestic">
                                                        <i class="mdi mdi-airplane me-1"></i> Domestic Flight (Pakistan)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_international" value="international" {{ $bCat === 'international' ? 'checked' : '' }}>
                                                    <label class="form-check-label fs-13 fw-semibold text-info" for="bcat_international">
                                                        <i class="mdi mdi-earth me-1"></i> International Flight
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
HTML;

if (strpos($edit, $targetVoucherCard) !== false) {
    $edit = str_replace($targetVoucherCard, $replacementVoucherCard, $edit);
    echo "Added Booking Category card in edit.blade.php\n";
} else {
    echo "Warning: targetVoucherCard not found in edit.blade.php\n";
}

// 2. Replace Departure Time and Arrival Time inputs with flight-time-picker
$targetDepTime = <<<'HTML'
                                <div class="col-md-4">
                                    <label class="form-label">Time of Departure</label>
                                    <input type="time" name="departure_time" class="form-control"
                                        value="{{ old('departure_time', $booking->departure_time) }}">
                                </div>
HTML;

$replaceDepTime = <<<'HTML'
                                <div class="col-md-4">
                                    <label class="form-label">Time of Departure</label>
                                    <input type="text" name="departure_time" id="departure_time_input" class="form-control flight-time-picker"
                                        value="{{ old('departure_time', $booking->departure_time) }}" placeholder="hh:mm AM/PM">
                                </div>
HTML;

if (strpos($edit, $targetDepTime) !== false) {
    $edit = str_replace($targetDepTime, $replaceDepTime, $edit);
    echo "Replaced departure_time input with flight-time-picker in edit.blade.php\n";
}

$targetArrTime = <<<'HTML'
                                <div class="col-md-4">
                                    <label class="form-label">Time of Arrival</label>
                                    <input type="time" name="arrival_time" class="form-control"
                                        value="{{ old('arrival_time', $booking->arrival_time) }}">
                                </div>
HTML;

$replaceArrTime = <<<'HTML'
                                <div class="col-md-4">
                                    <label class="form-label">Time of Arrival</label>
                                    <input type="text" name="arrival_time" id="arrival_time_input" class="form-control flight-time-picker"
                                        value="{{ old('arrival_time', $booking->arrival_time) }}" placeholder="hh:mm AM/PM">
                                </div>
HTML;

if (strpos($edit, $targetArrTime) !== false) {
    $edit = str_replace($targetArrTime, $replaceArrTime, $edit);
    echo "Replaced arrival_time input with flight-time-picker in edit.blade.php\n";
}

// 3. Update existing hotel rows in edit.blade.php (add Meal Plan & Room View & data-location)
$targetHotelBlockStart = '<div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="{{ $loop->index }}" data-person-rates=\'@json($hotel->person_rates ?? [])\'>';
$replaceHotelBlockStart = '<div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="{{ $loop->index }}" data-location="{{ $hotel->location }}" data-city="{{ strtoupper($hotel->location ?? \'MAKKAH\') }}" data-person-rates=\'@json($hotel->person_rates ?? [])\'>';

if (strpos($edit, $targetHotelBlockStart) !== false) {
    $edit = str_replace($targetHotelBlockStart, $replaceHotelBlockStart, $edit);
    echo "Added data-location and data-city to hotel-block in edit.blade.php\n";
}

// Filter hotel name select in PHP Blade loop
$targetHotelNameSelect = <<<'HTML'
                                                <select name="hotels[{{ $loop->index }}][hotel_name]" class="form-select hotel-name-select">
                                                    <option value="">Select Hotel</option>
                                                    @foreach($hotels as $h)
                                                        <option value="{{ $h->name }}" {{ old('hotels.'.$loop->index.'.hotel_name', $hotel->hotel_name) == $h->name ? 'selected' : '' }}>{{ $h->name }} ({{ $h->city ?? $h->place }})</option>
                                                    @endforeach
                                                </select>
HTML;

$replaceHotelNameSelect = <<<'HTML'
                                                <select name="hotels[{{ $loop->index }}][hotel_name]" class="form-select hotel-name-select" onchange="updateHotelStaySummary()">
                                                    <option value="">Select Hotel</option>
                                                    @foreach($hotels as $h)
                                                        @php
                                                            $hPlace = strtolower($h->city ?? (is_object($h->place) ? $h->place->value : ($h->place ?? '')));
                                                            $loc = strtolower($hotel->location ?? '');
                                                            if ($loc === 'makkah' && !str_contains($hPlace, 'makkah')) continue;
                                                            if ($loc === 'madinah' && !str_contains($hPlace, 'madinah')) continue;
                                                        @endphp
                                                        <option value="{{ $h->name }}" {{ old('hotels.'.$loop->index.'.hotel_name', $hotel->hotel_name) == $h->name ? 'selected' : '' }}>{{ $h->name }} ({{ $h->city ?? $h->place }})</option>
                                                    @endforeach
                                                </select>
HTML;

if (strpos($edit, $targetHotelNameSelect) !== false) {
    $edit = str_replace($targetHotelNameSelect, $replaceHotelNameSelect, $edit);
    echo "Filtered hotel options by city in Blade foreach in edit.blade.php\n";
}

// Add Meal Plan and Room View to existing hotel row in edit.blade.php
$targetEditRoomInputs = <<<'HTML'
                                            <div class="col-md-2">
                                                <label class="form-label">Hotel Room #</label>
                                                <input type="text" name="hotels[{{ $loop->index }}][room_number]"
                                                    class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505"
                                                    value="{{ old('hotels.'.$loop->index.'.room_number', $hotel->room_number) }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][cost_per_night]"
                                                    class="form-control hotel-cost-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_per_night', $hotel->cost_per_night ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][selling_per_night]"
                                                    class="form-control hotel-sell-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.selling_per_night', $hotel->selling_per_night ?? 0) }}">
                                            </div>
HTML;

$replaceEditRoomInputs = <<<'HTML'
                                            <div class="col-md-2">
                                                <label class="form-label">Hotel Room #</label>
                                                <input type="text" name="hotels[{{ $loop->index }}][room_number]"
                                                    class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505"
                                                    value="{{ old('hotels.'.$loop->index.'.room_number', $hotel->room_number) }}">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold"><i class="mdi mdi-silverware me-1 text-primary"></i> Meal Plan</label>
                                                <select name="hotels[{{ $loop->index }}][meal_plan]" class="form-select hotel-meal-plan" onchange="updateHotelStaySummary()">
                                                    @foreach($mealPlans as $mp)
                                                        <option value="{{ $mp->code }}" {{ old('hotels.'.$loop->index.'.meal_plan', $hotel->meal_plan ?? 'BB') == $mp->code ? 'selected' : '' }}>
                                                            {{ $mp->name }} ({{ $mp->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold"><i class="mdi mdi-eye me-1 text-info"></i> Room View</label>
                                                <select name="hotels[{{ $loop->index }}][room_view]" class="form-select hotel-room-view">
                                                    @foreach(['Standard View', 'City View', 'Haram View', 'Kaaba View', 'Partial Haram View', 'Sea / Mountain View'] as $rv)
                                                        <option value="{{ $rv }}" {{ old('hotels.'.$loop->index.'.room_view', $hotel->room_view ?? 'Standard View') == $rv ? 'selected' : '' }}>
                                                            {{ $rv }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][cost_per_night]"
                                                    class="form-control hotel-cost-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.cost_per_night', $hotel->cost_per_night ?? 0) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">{{ $hCurr }}</span>)</label>
                                                <input type="number" step="0.01" name="hotels[{{ $loop->index }}][selling_per_night]"
                                                    class="form-control hotel-sell-rate" placeholder="0.00"
                                                    value="{{ old('hotels.'.$loop->index.'.selling_per_night', $hotel->selling_per_night ?? 0) }}">
                                            </div>
HTML;

if (strpos($edit, $targetEditRoomInputs) !== false) {
    $edit = str_replace($targetEditRoomInputs, $replaceEditRoomInputs, $edit);
    echo "Added Meal Plan and Room View to existing hotel rows in edit.blade.php\n";
}

// 4. Update JS definitions in edit.blade.php
$targetEditHotelsJs = <<<'JS'
        const hotelsData       = @json($hotels);
        const suppliersData    = @json($suppliers);
JS;

$replaceEditHotelsJs = <<<'JS'
        const hotelsData       = @json($hotels);
        const suppliersData    = @json($suppliers);
        const mealPlansData    = @json($mealPlans ?? []);

        function buildHotelsOptions(filterCity = null) {
            let list = Array.isArray(hotelsData) ? hotelsData : [];
            if (filterCity) {
                const fc = filterCity.toLowerCase();
                list = list.filter(h => {
                    const p = (h.city || (typeof h.place === 'object' ? h.place?.value : h.place) || '').toLowerCase();
                    return p.includes(fc);
                });
            }
            return `<option value="">Select Hotel</option>` +
                list.map(h => {
                    const p = (h.city || (typeof h.place === 'object' ? h.place?.value : h.place) || '');
                    return `<option value="${h.name}">${h.name}${p ? ' (' + p + ')' : ''}</option>`;
                }).join('');
        }

        function buildMealPlansOptions(selectedCode = 'BB') {
            if (!Array.isArray(mealPlansData) || mealPlansData.length === 0) {
                return `
                    <option value="RO" ${selectedCode === 'RO' ? 'selected' : ''}>Room Only (RO)</option>
                    <option value="BB" ${selectedCode === 'BB' ? 'selected' : ''}>Bed & Breakfast (BB)</option>
                    <option value="HB" ${selectedCode === 'HB' ? 'selected' : ''}>Half Board (HB)</option>
                    <option value="FB" ${selectedCode === 'FB' ? 'selected' : ''}>Full Board (FB)</option>
                    <option value="SO" ${selectedCode === 'SO' ? 'selected' : ''}>Sahoor Only (SO)</option>
                    <option value="IO" ${selectedCode === 'IO' ? 'selected' : ''}>Iftar Only (IO)</option>
                    <option value="SI" ${selectedCode === 'SI' ? 'selected' : ''}>Sahoor & Iftar (SI)</option>
                `;
            }
            return mealPlansData.map(mp => {
                const sel = (selectedCode && (selectedCode.toUpperCase() === mp.code.toUpperCase() || selectedCode.toUpperCase() === mp.name.toUpperCase())) ? 'selected' : '';
                return `<option value="${mp.code}" ${sel}>${mp.name} (${mp.code})</option>`;
            }).join('');
        }

        function buildRoomViewsOptions(selected = 'Standard View') {
            const views = [
                'Standard View',
                'City View',
                'Haram View',
                'Kaaba View',
                'Partial Haram View',
                'Sea / Mountain View'
            ];
            return views.map(v => `<option value="${v}" ${selected === v ? 'selected' : ''}>${v}</option>`).join('');
        }
JS;

if (strpos($edit, $targetEditHotelsJs) !== false) {
    $edit = str_replace($targetEditHotelsJs, $replaceEditHotelsJs, $edit);
    echo "Added JS buildHotelsOptions & meal plans options in edit.blade.php\n";
}

// 5. Update addNewHotel in edit.blade.php
$targetEditAddNewHotel = <<<'JS'
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
            const hotelVoucherBase = document.getElementById('hotel_voucher_no')?.value || 'HTL-{{ date("Y") }}-{{ str_pad($booking->id, 5, "0", STR_PAD_LEFT) }}';
            const childVoucher = hotelVoucherBase + '-' + (hotelIdx + 1);

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="${hotelIdx}">
JS;

$replaceEditAddNewHotel = <<<'JS'
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
            const hotelVoucherBase = document.getElementById('hotel_voucher_no')?.value || 'HTL-{{ date("Y") }}-{{ str_pad($booking->id, 5, "0", STR_PAD_LEFT) }}';
            const childVoucher = hotelVoucherBase + '-' + (hotelIdx + 1);

            const relevantHotelsOptions = buildHotelsOptions(loc === 'makkah' ? 'makkah' : (loc === 'madinah' ? 'madinah' : null));

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="${hotelIdx}" data-location="${locVal}" data-city="${locVal.toUpperCase()}">
JS;

if (strpos($edit, $targetEditAddNewHotel) !== false) {
    $edit = str_replace($targetEditAddNewHotel, $replaceEditAddNewHotel, $edit);
    echo "Updated addNewHotel header & data-location in edit.blade.php\n";
}

// 6. In addNewHotel in edit.blade.php, replace hotel select and room inputs
$targetEditJsSelect = <<<'JS'
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select">
                            ${hotelsOptions}
                        </select>
JS;

$replaceEditJsSelect = <<<'JS'
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select" onchange="updateHotelStaySummary()">
                            ${relevantHotelsOptions}
                        </select>
JS;

if (strpos($edit, $targetEditJsSelect) !== false) {
    $edit = str_replace($targetEditJsSelect, $replaceEditJsSelect, $edit);
    echo "Updated hotel-name-select in addNewHotel in edit.blade.php\n";
}

$targetEditJsRoomInputs = <<<'JS'
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

$replaceEditJsRoomInputs = <<<'JS'
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

if (strpos($edit, $targetEditJsRoomInputs) !== false) {
    $edit = str_replace($targetEditJsRoomInputs, $replaceEditJsRoomInputs, $edit);
    echo "Added Meal Plan and Room View in addNewHotel in edit.blade.php\n";
}

// 7. Update updateHotelStaySummary in edit.blade.php
$targetEditSummaryUpdate = <<<'JS'
                // City
                const citySelect = b.querySelector('.hotel-city-select');
                const city = (citySelect?.value || b.getAttribute('data-city') || 'MAKKAH').toUpperCase();

                // Room Type, Rooms, Meal
                const roomType = (b.querySelector('.hotel-room-type')?.value || 'DOUBLE').toUpperCase();
                const mealPlan = (b.querySelector('.hotel-meal-plan')?.value || 'B/B').toUpperCase();
JS;

$replaceEditSummaryUpdate = <<<'JS'
                // City (from data-location or data-city)
                const locAttr = b.getAttribute('data-location') || b.getAttribute('data-city') || 'makkah';
                const city = locAttr.toUpperCase();

                // Room Type, Rooms, Meal Plan
                const roomType = (b.querySelector('.hotel-room-type')?.value || 'DOUBLE').toUpperCase();
                const mealPlan = (b.querySelector('.hotel-meal-plan')?.value || 'BB').toUpperCase();
JS;

if (strpos($edit, $targetEditSummaryUpdate) !== false) {
    $edit = str_replace($targetEditSummaryUpdate, $replaceEditSummaryUpdate, $edit);
    echo "Updated updateHotelStaySummary city & meal plan in edit.blade.php\n";
}

// 8. In passenger row in edit.blade.php, update flight_time to flight-time-picker
$targetEditPaxFlightRow = <<<'JS'
                        <input type="time" name="flight_persons[${i}][flight_time]" class="form-control form-control-sm person-flight-time" value="${pTime}">
JS;

$replaceEditPaxFlightRow = <<<'JS'
                        <input type="text" name="flight_persons[${i}][flight_time]" class="form-control form-control-sm person-flight-time flight-time-picker" value="${pTime}" placeholder="hh:mm">
JS;

if (strpos($edit, $targetEditPaxFlightRow) !== false) {
    $edit = str_replace($targetEditPaxFlightRow, $replaceEditPaxFlightRow, $edit);
    echo "Updated person-flight-time in edit.blade.php\n";
}

// 9. Add end JS helpers in edit.blade.php
$targetEditEnd = <<<'JS'
    </script>
@endsection
JS;

$replaceEditEnd = <<<'JS'
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

        // Call initial updateHotelStaySummary so summary box is rendered on load
        if (typeof updateHotelStaySummary === 'function') {
            setTimeout(updateHotelStaySummary, 200);
        }
    </script>
@endsection
JS;

if (strpos($edit, $targetEditEnd) !== false) {
    $edit = str_replace($targetEditEnd, $replaceEditEnd, $edit);
    echo "Added JS helpers and initial summary trigger in edit.blade.php\n";
}

file_put_contents('resources/views/booking/edit.blade.php', $edit);
echo "Successfully updated resources/views/booking/edit.blade.php\n";
