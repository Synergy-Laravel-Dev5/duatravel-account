<?php
echo "Applying tab banners and removing duplicate booking dates...\n";

// Helper function to replace CRLF or LF
function replaceText($file, $search, $replace, $desc) {
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
// 1. CREATE.BLADE.PHP
// -------------------------------------------------------------
$cFile = __DIR__ . '/../resources/views/booking/create.blade.php';

// TAB 1: Package top banner
$sPkg = '<div class="tab-pane fade {{ $isUmrah ? \'\' : \'show active\' }}" id="tab-package">
                            <div class="row g-3">';
$rPkg = '<div class="tab-pane fade {{ $isUmrah ? \'\' : \'show active\' }}" id="tab-package">
                            <!-- TOP ROW: Booking Date (First Field) & Package Info -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-secondary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-secondary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-sm sync-booking-date" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">
                                    <small class="text-muted fs-11">Synchronized across all tabs</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-tag text-secondary me-1"></i> Package Selection
                                    </label>
                                    <div>
                                        <span class="badge bg-secondary fs-13 px-2 py-1 font-monospace">Package Booking</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">';
replaceText($cFile, $sPkg, $rPkg, "Create: Tab Package Top Banner");

// TAB 2: Details top banner
$sDet = '                        {{-- TAB 2: Booking Details --}}
                        <div class="tab-pane fade {{ $isUmrah ? \'show active\' : \'\' }}" id="tab-details">

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="mdi mdi-ticket-confirmation text-primary fs-18"></i>
                                            <strong class="text-dark">Booking Voucher No:</strong>
                                            <span class="badge bg-primary fs-13 px-2 py-1 font-monospace">{{ $defaultVouchers[\'booking\'] ?? (\'BK-\' . date(\'Y\') . \'-00001\') }}</span>
                                        </div>
                                        <input type="hidden" name="booking_voucher_no" id="booking_voucher_no" value="{{ $defaultVouchers[\'booking\'] ?? (\'BK-\' . date(\'Y\') . \'-00001\') }}">
                                    </div>
                                </div>';

$rDet = '                        {{-- TAB 2: Booking Details --}}
                        <div class="tab-pane fade {{ $isUmrah ? \'show active\' : \'\' }}" id="tab-details">

                            <!-- TOP ROW: Booking Date (First Field) & Main Booking Voucher -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-primary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="booking_date" id="booking_date"
                                        class="form-control form-control-sm sync-booking-date"
                                        value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}" required>
                                    <small class="text-muted fs-11">Primary Booking Date (Synchronized across all tabs)</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-ticket-confirmation text-primary me-1"></i> Booking Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-primary fs-13 px-2 py-1 font-monospace">{{ $defaultVouchers[\'booking\'] ?? (\'BK-\' . date(\'Y\') . \'-00001\') }}</span>
                                    </div>
                                    <input type="hidden" name="booking_voucher_no" id="booking_voucher_no" value="{{ $defaultVouchers[\'booking\'] ?? (\'BK-\' . date(\'Y\') . \'-00001\') }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3">';
replaceText($cFile, $sDet, $rDet, "Create: Tab Details Top Banner");

// Remove duplicate lower booking date from Details tab in create
$sDupDet = '                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Booking Date</label>
                                    <input type="date" name="booking_date" id="booking_date" class="form-control" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">
                                </div>
                                <input type="hidden" name="currency" id="booking_currency" value="PKR">';

$rDupDet = '                                <input type="hidden" name="currency" id="booking_currency" value="PKR">';
replaceText($cFile, $sDupDet, $rDupDet, "Create: Remove duplicate booking date in Details tab");

// TAB 3: Persons top banner
$sPax = '                        {{-- TAB 3: Persons --}}
                        <div class="tab-pane fade" id="tab-persons">';
$rPax = '                        {{-- TAB 3: Persons --}}
                        <div class="tab-pane fade" id="tab-persons">
                            <!-- TOP ROW: Booking Date (First Field) & Passengers Overview -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-primary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-primary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-sm sync-booking-date" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">
                                    <small class="text-muted fs-11">Synchronized across all tabs</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-account-group text-primary me-1"></i> Passenger Breakdown
                                    </label>
                                    <div>
                                        <span class="badge bg-primary fs-12">Total: <span id="bar_pax_badge_header">1</span></span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>';
replaceText($cFile, $sPax, $rPax, "Create: Tab Persons Top Banner");

// TAB 4: Remove duplicate flight booking date visible input in flight tab
$sDupFlt = '                                        <div class="col-md-3">
                                            <label class="form-label"><i class="mdi mdi-calendar me-1 text-primary"></i> Flight Booking Date</label>
                                            <input type="date" name="flight_booking_date" id="flight_booking_date" class="form-control flight-booking-date" value="{{ old(\'flight_booking_date\', date(\'Y-m-d\')) }}">
                                        </div>';
$rDupFlt = '                                        <input type="hidden" name="flight_booking_date" id="flight_booking_date" class="sync-booking-date" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">';
replaceText($cFile, $sDupFlt, $rDupFlt, "Create: Remove duplicate flight booking date input");

// TAB 5: Hotel: Remove duplicate booking date from addNewHotel template in create
$sDupHtl = '                        <div class="col-md-2">
                            <label class="form-label mb-1"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                            <input type="date" name="hotels[${hotelIdx}][booking_date]" class="form-control form-control-sm hotel-booking-date" value="{{ date(\'Y-m-d\') }}">
                        </div>';
$rDupHtl = '                        <input type="hidden" name="hotels[${hotelIdx}][booking_date]" class="hotel-booking-date sync-booking-date" value="{{ date(\'Y-m-d\') }}">';
replaceText($cFile, $sDupHtl, $rDupHtl, "Create: Remove duplicate booking date in addNewHotel template");

// TAB 6: Transport top banner
$sTrn = '                        {{-- TAB 6: Transport --}}
                        <div class="tab-pane fade" id="tab-transport">
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-warning shadow-sm">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-bus text-warning fs-18"></i>
                                    <strong class="text-dark">Transport Voucher No:</strong>
                                    <span class="badge bg-warning text-dark fs-13 px-2 py-1 font-monospace">{{ $defaultVouchers[\'transport\'] ?? (\'TRN-\' . date(\'Y\') . \'-00001\') }}</span>
                                </div>
                                <input type="hidden" name="transport_voucher_no" id="transport_voucher_no" value="{{ $defaultVouchers[\'transport\'] ?? (\'TRN-\' . date(\'Y\') . \'-00001\') }}">
                            </div>';
$rTrn = '                        {{-- TAB 6: Transport --}}
                        <div class="tab-pane fade" id="tab-transport">
                            <!-- TOP ROW: Booking Date (First Field) & Transport Voucher No -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-warning shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-warning me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-sm sync-booking-date" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">
                                    <small class="text-muted fs-11">Synchronized across all tabs</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-bus text-warning me-1"></i> Transport Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-warning text-dark fs-13 px-2 py-1 font-monospace">{{ $defaultVouchers[\'transport\'] ?? (\'TRN-\' . date(\'Y\') . \'-00001\') }}</span>
                                    </div>
                                    <input type="hidden" name="transport_voucher_no" id="transport_voucher_no" value="{{ $defaultVouchers[\'transport\'] ?? (\'TRN-\' . date(\'Y\') . \'-00001\') }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>';
replaceText($cFile, $sTrn, $rTrn, "Create: Tab Transport Top Banner");

// TAB 7: Visa top banner
$sVis = '                        {{-- TAB 7: Visa --}}
                        <div class="tab-pane fade" id="tab-visa">
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-danger shadow-sm">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-passport text-danger fs-18"></i>
                                    <strong class="text-dark">Visa Voucher No:</strong>
                                    <span class="badge bg-danger fs-13 px-2 py-1 font-monospace">{{ $defaultVouchers[\'visa\'] ?? (\'VIS-\' . date(\'Y\') . \'-00001\') }}</span>
                                </div>
                                <input type="hidden" name="visa_voucher_no" id="visa_voucher_no" value="{{ $defaultVouchers[\'visa\'] ?? (\'VIS-\' . date(\'Y\') . \'-00001\') }}">
                            </div>';
$rVis = '                        {{-- TAB 7: Visa --}}
                        <div class="tab-pane fade" id="tab-visa">
                            <!-- TOP ROW: Booking Date (First Field) & Visa Voucher No -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-danger shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-danger me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-sm sync-booking-date" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">
                                    <small class="text-muted fs-11">Synchronized across all tabs</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-passport text-danger me-1"></i> Visa Voucher No:
                                    </label>
                                    <div>
                                        <span class="badge bg-danger fs-13 px-2 py-1 font-monospace">{{ $defaultVouchers[\'visa\'] ?? (\'VIS-\' . date(\'Y\') . \'-00001\') }}</span>
                                    </div>
                                    <input type="hidden" name="visa_voucher_no" id="visa_voucher_no" value="{{ $defaultVouchers[\'visa\'] ?? (\'VIS-\' . date(\'Y\') . \'-00001\') }}">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>';
replaceText($cFile, $sVis, $rVis, "Create: Tab Visa Top Banner");

// TAB 8: Costing top banner
$sCost = '                        {{-- TAB 8: Costing --}}
                        <div class="tab-pane fade" id="tab-costing">
                            <div class="row g-3">';
$rCost = '                        {{-- TAB 8: Costing --}}
                        <div class="tab-pane fade" id="tab-costing">
                            <!-- TOP ROW: Booking Date (First Field) & Costing Overview -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-success shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-success me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-sm sync-booking-date" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">
                                    <small class="text-muted fs-11">Synchronized across all tabs</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-cash text-success me-1"></i> Costing & Pricing Overview
                                    </label>
                                    <div>
                                        <span class="badge bg-success fs-13 px-2 py-1 font-monospace">Package Ledger</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">';
replaceText($cFile, $sCost, $rCost, "Create: Tab Costing Top Banner");

// TAB 9: Additional Services top banner
$sServ = '                        <div class="tab-pane fade" id="tab-additional-services">
                            <div class="row g-3">';
$rServ = '                        <div class="tab-pane fade" id="tab-additional-services">
                            <!-- TOP ROW: Booking Date (First Field) & Services -->
                            <div class="row g-2 align-items-center mb-3 p-2 px-3 rounded bg-light border border-start border-start-4 border-start-secondary shadow-sm">
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-calendar-check text-secondary me-1"></i> Booking Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-sm sync-booking-date" value="{{ old(\'booking_date\', date(\'Y-m-d\')) }}">
                                    <small class="text-muted fs-11">Synchronized across all tabs</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-12 fw-bold text-dark mb-1">
                                        <i class="mdi mdi-plus-circle text-secondary me-1"></i> Additional Services
                                    </label>
                                    <div>
                                        <span class="badge bg-secondary fs-13 px-2 py-1 font-monospace">Custom Services</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-soft-info text-info fs-12 py-2 px-3 border border-info border-opacity-25">
                                        <i class="mdi mdi-sync me-1"></i> Global Date Active
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3">';
replaceText($cFile, $sServ, $rServ, "Create: Tab Additional Services Top Banner");

echo "\nDone processing create.blade.php.\n";
