<?php

// 1. UPDATE CREATE.BLADE.PHP
$create = file_get_contents('resources/views/booking/create.blade.php');

// A. Add Booking Category in Tab 2 (right after Booking Voucher No card)
$targetVoucherCard = <<<'HTML'
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="mdi mdi-ticket-confirmation text-primary fs-18"></i>
                                            <strong class="text-dark">Booking Voucher No:</strong>
                                            <span class="badge bg-primary fs-13 px-2 py-1 font-monospace">{{ $defaultVouchers['booking'] ?? ('BK-' . date('Y') . '-00001') }}</span>
                                        </div>
                                        <input type="hidden" name="voucher_number" value="{{ $defaultVouchers['booking'] ?? ('BK-' . date('Y') . '-00001') }}">
                                    </div>
                                </div>
HTML;

$replacementVoucherCard = <<<'HTML'
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="mdi mdi-ticket-confirmation text-primary fs-18"></i>
                                            <strong class="text-dark">Booking Voucher No:</strong>
                                            <span class="badge bg-primary fs-13 px-2 py-1 font-monospace" id="booking_voucher_disp">{{ $defaultVouchers['booking'] ?? ('BK-' . date('Y') . '-00001') }}</span>
                                        </div>
                                        <input type="hidden" name="voucher_number" id="booking_voucher_input" value="{{ $defaultVouchers['booking'] ?? ('BK-' . date('Y') . '-00001') }}">
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
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_umrah" value="umrah" {{ old('booking_category', 'umrah') === 'umrah' ? 'checked' : '' }}>
                                                    <label class="form-check-label fs-13 fw-semibold text-primary" for="bcat_umrah">
                                                        <i class="mdi mdi-mosque me-1"></i> Umrah Pilgrimage
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_domestic" value="domestic" {{ old('booking_category') === 'domestic' ? 'checked' : '' }}>
                                                    <label class="form-check-label fs-13 fw-semibold text-success" for="bcat_domestic">
                                                        <i class="mdi mdi-airplane me-1"></i> Domestic Flight (Pakistan)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input booking-category-radio" type="radio" name="booking_category" id="bcat_international" value="international" {{ old('booking_category') === 'international' ? 'checked' : '' }}>
                                                    <label class="form-check-label fs-13 fw-semibold text-info" for="bcat_international">
                                                        <i class="mdi mdi-earth me-1"></i> International Flight
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
HTML;

if (strpos($create, $targetVoucherCard) !== false) {
    $create = str_replace($targetVoucherCard, $replacementVoucherCard, $create);
    echo "Added Booking Category card in create.blade.php\n";
} else {
    echo "Warning: targetVoucherCard not found in create.blade.php\n";
}

// B. Replace Time of Departure and Arrival inputs with flight-time-picker
$targetDepTime = '<input type="time" name="departure_time" class="form-control">';
$replaceDepTime = '<input type="text" name="departure_time" id="departure_time_input" class="form-control flight-time-picker" placeholder="hh:mm AM/PM">';
if (strpos($create, $targetDepTime) !== false) {
    $create = str_replace($targetDepTime, $replaceDepTime, $create);
    echo "Replaced departure_time input with flight-time-picker in create.blade.php\n";
}

$targetArrTime = '<input type="time" name="arrival_time" class="form-control">';
$replaceArrTime = '<input type="text" name="arrival_time" id="arrival_time_input" class="form-control flight-time-picker" placeholder="hh:mm AM/PM">';
if (strpos($create, $targetArrTime) !== false) {
    $create = str_replace($targetArrTime, $replaceArrTime, $create);
    echo "Replaced arrival_time input with flight-time-picker in create.blade.php\n";
}

// C. In JS: add mealPlansData & helper functions
$targetJsData = <<<'JS'
        let suppliersData   = @json($suppliers);
        let airlinesData    = @json($airlines ?? []);
JS;

$replaceJsData = <<<'JS'
        let suppliersData   = @json($suppliers);
        let mealPlansData   = @json($mealPlans ?? []);
        let airlinesData    = @json($airlines ?? []);
JS;

if (strpos($create, $targetJsData) !== false) {
    $create = str_replace($targetJsData, $replaceJsData, $create);
    echo "Added mealPlansData in create.blade.php\n";
}

// D. Replace buildHotelsOptions
$targetBuildHotels = <<<'JS'
        function buildHotelsOptions() {
            return `<option value="">Select Hotel</option>` +
                (Array.isArray(hotelsData) ? hotelsData.map(h => `<option value="${h.name}">${h.name} (${h.city || h.place || ''})</option>`).join('') : '');
        }
JS;

$replaceBuildHotels = <<<'JS'
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

if (strpos($create, $targetBuildHotels) !== false) {
    $create = str_replace($targetBuildHotels, $replaceBuildHotels, $create);
    echo "Replaced buildHotelsOptions & added buildMealPlansOptions/buildRoomViewsOptions in create.blade.php\n";
}

file_put_contents('resources/views/booking/create.blade.php', $create);
echo "Written create.blade.php\n";
