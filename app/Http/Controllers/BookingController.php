<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Company;
use App\Models\Package;
use App\Models\Supplier;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function index()
    {
        $package = session('dashboard_package', 'hajj');
       $year = (int) session('dashboard_year', 2027);

        if (!Booking::where('package_type', $package)->where('package_year', $year)->exists()) {
            $maxYear = Booking::where('package_type', $package)->max('package_year');
            if ($maxYear) {
                $year = $maxYear;
                session(['dashboard_year' => $year]);
            }
        }

        $bookings = Booking::with(['client', 'company'])
            ->where('package_type', $package)
            ->where('package_year', $year)
            ->latest()
            ->get();

        $trashCount = Booking::onlyTrashed()->count();

        return view('booking.index', compact('bookings', 'trashCount', 'package', 'year'));
    }

    public function create()
    {
        // Auto-sync: if any active clients belong to trashed leads, move them to trash
        $trashedLeadIds = \App\Models\Lead::onlyTrashed()->pluck('id');
        if ($trashedLeadIds->isNotEmpty()) {
            Client::whereIn('lead_id', $trashedLeadIds)->delete();
        }

        $sessionPkg = session('dashboard_package', 'hajj');
        $clientsQ = Client::where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        })
        ->whereDoesntHave('lead', function ($lq) {
            $lq->onlyTrashed();
        });

        if (in_array($sessionPkg, ['hajj', 'umrah'])) {
            $clientsQ->where(function ($q) use ($sessionPkg) {
                $q->where('package_type', $sessionPkg)
                  ->orWhereNull('package_type')
                  ->orWhere('package_type', '');
            });
        }

        $clients   = $clientsQ->orderBy('name')->get();
        $companies = Company::all();
        $packages  = Package::with(['accommodations.hotel', 'transports', 'transportFlights'])->latest()->get();
        $hotels    = \App\Models\Hotel::all();
        $suppliers = Supplier::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $airlines  = \App\Models\Airline::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $routes    = \App\Models\Route::where('status', 'active')->orWhereNull('status')->orderBy('start_place')->get();
        $vehicles  = \App\Models\Vehicle::where('status', 'active')->orWhereNull('status')->orderBy('vehicle_type')->get();
        $mealPlans = \App\Models\MealPlan::where('status', 'active')->orderBy('name')->get();
        $years     = [date('Y'), date('Y') + 1, date('Y') + 2];

        $curYear  = date('Y');
        $defaultVouchers = [
            'booking'   => self::getNextVoucherNo('booking', $curYear),
            'flight'    => self::getNextVoucherNo('flight', $curYear),
            'hotel'     => self::getNextVoucherNo('hotel', $curYear),
            'transport' => self::getNextVoucherNo('transport', $curYear),
            'visa'      => self::getNextVoucherNo('visa', $curYear),
        ];

        return view('booking.create', compact('clients', 'companies', 'packages', 'hotels', 'suppliers', 'years', 'airlines', 'routes', 'vehicles', 'mealPlans', 'defaultVouchers'));
    }

    /**
     * Get next sequential voucher number independently per service type and year
     */
    public static function getNextVoucherNo(string $type, string $year = null): string
    {
        $year = $year ?: date('Y');
        $map = [
            'booking'   => ['prefix' => 'BK',  'col' => 'voucher_number'],
            'hotel'     => ['prefix' => 'HTL', 'col' => 'hotel_voucher_no'],
            'flight'    => ['prefix' => 'FLT', 'col' => 'flight_voucher_no'],
            'transport' => ['prefix' => 'TRN', 'col' => 'transport_voucher_no'],
            'visa'      => ['prefix' => 'VIS', 'col' => 'visa_voucher_no'],
        ];

        if (!isset($map[$type])) {
            return 'BK-' . $year . '-00001';
        }

        $prefix = $map[$type]['prefix'];
        $col    = $map[$type]['col'];

        $existing = Booking::withTrashed()->where($col, 'LIKE', $prefix . '-' . $year . '-%')->pluck($col);
        $maxNum = 0;
        foreach ($existing as $val) {
            if (preg_match('/' . preg_quote($prefix, '/') . '-' . $year . '-(\d+)/', (string)$val, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return $prefix . '-' . $year . '-' . str_pad($maxNum + 1, 5, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        if (!\Schema::hasColumn('bookings', 'additional_services_amount')) {
            \Schema::table('bookings', function ($table) {
                $table->decimal('additional_services_amount', 15, 2)->default(0);
                $table->text('additional_services_detail')->nullable();
            });
        }

            $additionalServicesDetail = $request->additional_services_detail;

            if (trim(strip_tags($additionalServicesDetail ?? '')) === '') {
            $additionalServicesDetail = null;
            }

        \Log::info('Store Request: ', $request->all());
        $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'package_id'   => 'nullable|exists:packages,id',
            'package_type' => 'nullable|string',
            'haji_id'      => 'nullable|string|max:100',
            'room_number'  => 'nullable|string|max:100',
        ]);

        // Point 4: Validate Unique Ticket Numbers
        $submittedTickets = [];
        $personsInput = $request->persons ?? [];
        $flightPersonsInput = $request->flight_persons ?? [];
        $maxRows = max(count($personsInput), count($flightPersonsInput));

        for ($i = 0; $i < $maxRows; $i++) {
            $t = trim((string) ($personsInput[$i]['flight_ticket'] ?? ($flightPersonsInput[$i]['ticket'] ?? ($flightPersonsInput[$i]['flight_ticket'] ?? ''))));
            if ($t !== '') {
                $tLower = strtolower($t);
                if (in_array($tLower, $submittedTickets, true)) {
                    return back()->withInput()->with('error', "Duplicate ticket number '{$t}' found in the booking form. Each passenger must have a unique ticket number.");
                }
                $submittedTickets[] = $tLower;

                if (\Illuminate\Support\Facades\DB::table('booking_persons')->where('flight_ticket', $t)->exists()) {
                    return back()->withInput()->with('error', "Ticket number '{$t}' is already registered in another booking. Every ticket number must be unique.");
                }
            }
        }

        $bookingFor = 'client';
        $clientId  = $request->client_id;
        $companyId = null;
        $hajiId    = $request->haji_id ?? ($request->persons[0]['haji_id'] ?? null);
        $roomNumber = $request->room_number ?? ($request->persons[0]['room_number'] ?? null);

        $flightCharges = (float) ($request->flight_charges ?: ($request->flight_charges_disp ?: 0));
        if ($flightCharges <= 0 && (float) $request->flight_selling_rate > 0) {
            $flightRoe = (float) ($request->flight_sale_roe ?: ($request->flight_roe ?: 75));
            $flightCharges = (float) $request->flight_selling_rate * ($request->flight_currency === 'PKR' ? 1 : $flightRoe);
        }

        $total = (($request->package_cost ?? 0) * ($request->no_of_pax ?? 1))
            + ($request->hotel_charges ?? 0)
            + ($request->visa_charges ?? 0)
            + ($request->transport_charges ?? 0)
            + $flightCharges
            + ($request->other_charges ?? 0)
            + ($request->additional_services_amount ?? 0)
            - ($request->discount ?? 0);

        $hasHotels = $request->has('hotels') && is_array($request->hotels) && count(array_filter($request->hotels, function($h) {
            return !empty($h['hotel_id']) || !empty($h['hotel_name']) || (float)($h['cost_per_night'] ?? 0) > 0 || (float)($h['selling_per_night'] ?? 0) > 0 || (float)($h['cost_amount'] ?? 0) > 0 || (float)($h['selling_amount'] ?? 0) > 0;
        })) > 0;

        $hasFlight = (float)($request->flight_charges ?? 0) > 0
            || (float)($request->flight_cost ?? 0) > 0
            || (float)($request->flight_cost_rate ?? 0) > 0
            || (float)($request->flight_selling_rate ?? 0) > 0
            || !empty($request->flight_supplier_id)
            || !empty($request->flight_number)
            || ($request->has('flight_routes') && count($request->flight_routes) > 0);

        $hasVisas = $request->has('visas') && is_array($request->visas) && count(array_filter($request->visas, function($v) {
            return !empty($v['passport_number']) || !empty($v['given_name']) || !empty($v['supplier_id']) || (float)($v['cost_rate'] ?? 0) > 0 || (float)($v['selling_rate'] ?? 0) > 0;
        })) > 0;

        $hasTransports = $request->has('transports') && is_array($request->transports) && count(array_filter($request->transports, function($t) {
            return !empty($t['route']) || !empty($t['supplier_id']) || (float)($t['cost_rate'] ?? 0) > 0 || (float)($t['selling_rate'] ?? 0) > 0;
        })) > 0;

        $resolvedBookingDate = $request->booking_date;
        $specificServiceDate = null;
        if ($hasFlight && !empty($request->flight_booking_date)) {
            $specificServiceDate = $request->flight_booking_date;
        } elseif ($hasVisas && !empty($request->input('visas.0.booking_date'))) {
            $specificServiceDate = $request->input('visas.0.booking_date');
        } elseif ($hasHotels && !empty($request->input('hotels.0.booking_date'))) {
            $specificServiceDate = $request->input('hotels.0.booking_date');
        } elseif ($hasTransports && !empty($request->input('transports.0.booking_date'))) {
            $specificServiceDate = $request->input('transports.0.booking_date');
        }

        if (empty($resolvedBookingDate)) {
            $resolvedBookingDate = $specificServiceDate ?: now()->toDateString();
        }
        $finalBookingDate = date('Y-m-d', strtotime($resolvedBookingDate));

        $calcAdults = 0;
        $calcChildren = 0;
        $calcInfants = 0;
        if (!empty($request->persons) && is_array($request->persons)) {
            foreach ($request->persons as $p) {
                $pt = strtolower($p['pax_type'] ?? 'adult');
                if ($pt === 'child') $calcChildren++;
                elseif ($pt === 'infant') $calcInfants++;
                else $calcAdults++;
            }
        }
        $finalAdults = $calcAdults > 0 ? $calcAdults : ($request->pax_adults ?? ($request->adults_count ?? 1));
        $finalChildren = ($calcAdults > 0 || $calcChildren > 0 || $calcInfants > 0) ? $calcChildren : ($request->pax_children ?? ($request->children_count ?? 0));
        $finalInfants = ($calcAdults > 0 || $calcChildren > 0 || $calcInfants > 0) ? $calcInfants : ($request->pax_infants ?? ($request->infants_count ?? 0));

        $bookingData = array_merge(
            $request->except(['persons', 'hotels', 'transports', 'visas', 'flight_persons', '_token', 'cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate', 'flight_attachment']),
            [
                'booking_for'         => $bookingFor,
                'client_id'           => $clientId,
                'company_id'          => $companyId,
                'booking_date'        => $finalBookingDate,
                'flight_booking_date' => $hasFlight ? (!empty($request->flight_booking_date) ? date('Y-m-d', strtotime($request->flight_booking_date)) : $finalBookingDate) : null,
                'currency'            => $request->currency ?? 'PKR',
                'haji_id'             => $hajiId,
                'room_number'         => $roomNumber,
                'flight_supplier_id'  => $request->flight_supplier_id,
                'flight_currency'     => $request->flight_currency ?? 'SAR',
                'flight_cost_roe'     => $request->flight_cost_roe ?? ($request->flight_roe ?? 75),
                'flight_sale_roe'     => $request->flight_sale_roe ?? ($request->flight_roe ?? 75),
                'flight_roe'          => $request->flight_cost_roe ?? ($request->flight_roe ?? 75),
                'flight_cost_rate'    => $request->flight_cost_rate ?? 0,
                'flight_cost'         => $request->flight_cost ?? 0,
                'flight_selling_rate' => $request->flight_selling_rate ?? 0,
                'pax_adults'          => $finalAdults,
                'pax_children'        => $finalChildren,
                'pax_infants'         => $finalInfants,
                'adults_count'        => $finalAdults,
                'children_count'      => $finalChildren,
                'infants_count'       => $finalInfants,
                'no_of_pax'           => $request->no_of_pax ?? max(1, count($request->persons ?? [])),
                'flight_adult_cost'   => $request->flight_adult_cost ?? 0,
                'flight_adult_sell'   => $request->flight_adult_sell ?? 0,
                'flight_child_cost'   => $request->flight_child_cost ?? 0,
                'flight_child_sell'   => $request->flight_child_sell ?? 0,
                'flight_infant_cost'  => $request->flight_infant_cost ?? 0,
                'flight_infant_sell'  => $request->flight_infant_sell ?? 0,
                'package_cost'        => $request->package_cost ?? 0,
                'hotel_charges'       => $request->hotel_charges ?? 0,
                'visa_charges'        => $request->visa_charges ?? 0,
                'transport_charges'   => $request->transport_charges ?? 0,
                'flight_charges'      => $flightCharges,
                'other_charges'       => $request->other_charges ?? 0,
                'discount'            => $request->discount ?? 0,
                'additional_services_amount' => $request->additional_services_amount ?? 0,
                'additional_services_detail' => $additionalServicesDetail,           
                'total_received'      => $request->total_received ?? 0,
                'total_amount'        => $total,
                'balance'             => $total - ($request->total_received ?? 0),
                'flight_time_format'  => $request->flight_time_format ?? '12',
            ]
        );

        if ($request->hasFile('flight_attachment')) {
            $bookingData['flight_attachment'] = $request->file('flight_attachment')->store('bookings/flight_attachment', 'public');
        }

        $bookingCategory = $request->booking_category ?: 'umrah';
        $bookingData['booking_category'] = $bookingCategory;

        // Defensive column check against missing columns on older database schemas
        $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('bookings');
        $bookingData = array_intersect_key($bookingData, array_flip($existingCols));

        $booking = Booking::create($bookingData);

        // Point 23: Separate Voucher Numbers based on actual booked services
        $curYear  = date('Y');
        $flightVoucher    = $hasFlight ? ($request->flight_voucher_no ?: self::getNextVoucherNo('flight', $curYear)) : null;
        $hotelVoucher     = $hasHotels ? ($request->hotel_voucher_no ?: self::getNextVoucherNo('hotel', $curYear)) : null;
        $transportVoucher = $hasTransports ? ($request->transport_voucher_no ?: self::getNextVoucherNo('transport', $curYear)) : null;
        $visaVoucher      = $hasVisas ? ($request->visa_voucher_no ?: self::getNextVoucherNo('visa', $curYear)) : null;

        $isFlightOnly = ($bookingCategory === 'domestic' || $bookingCategory === 'international' || (!$hasHotels && $hasFlight && !$hasVisas && !$hasTransports));
        $bookingVoucher = $request->voucher_number ?: ($isFlightOnly ? $flightVoucher : self::getNextVoucherNo('booking', $curYear));

        $booking->update([
            'voucher_number'       => $bookingVoucher,
            'flight_voucher_no'    => $flightVoucher,
            'hotel_voucher_no'     => $hotelVoucher,
            'transport_voucher_no' => $transportVoucher,
            'visa_voucher_no'      => $visaVoucher,
        ]);

        if ($request->has('persons')) {
            foreach ($request->persons as $idx => $person) {
                $surname   = $person['surname'] ?? null;
                $givenName = $person['given_name'] ?? null;
                $paxName   = $person['full_name'] ?? null;

                if (empty($paxName) && ($givenName || $surname)) {
                    $paxName = trim(($surname ?? '') . ' ' . ($givenName ?? ''));
                }

                if (empty($paxName)) {
                    if ($idx === 0 && $booking->client) {
                        $paxName = $booking->client->name;
                    } else {
                        $paxName = 'Passenger ' . ($idx + 1);
                    }
                }

                $flPerson = $request->input("flight_persons.$idx", []);
                $paxType  = $person['pax_type'] ?? ($flPerson['pax_type'] ?? 'adult');
                $flTicket = $person['flight_ticket'] ?? ($flPerson['ticket'] ?? ($flPerson['flight_ticket'] ?? null));
                $flCost   = (float) ($person['flight_cost'] ?? ($flPerson['cost_rate'] ?? ($flPerson['flight_cost'] ?? 0)));
                $flSell   = (float) ($person['flight_sell'] ?? ($flPerson['sell_rate'] ?? ($flPerson['flight_sell'] ?? 0)));
                $flSupId  = $flPerson['flight_supplier_id'] ?? ($person['flight_supplier_id'] ?? ($request->flight_supplier_id ?: null));
                $flPnr    = $flPerson['flight_pnr'] ?? ($person['flight_pnr'] ?? ($request->departure_pnr ?: null));
                $flDate   = $flPerson['flight_date'] ?? ($person['flight_date'] ?? ($request->departure_date ?: null));
                $flTime   = $flPerson['flight_time'] ?? ($person['flight_time'] ?? ($request->departure_time ?: null));
                $flTicketStatus = $flPerson['ticket_status'] ?? ($person['ticket_status'] ?? 'confirmed');

                $personData = [
                    'full_name'          => $paxName,
                    'haji_id'            => $person['haji_id'] ?? ($idx === 0 ? $hajiId : null),
                    'pax_type'           => $paxType,
                    'flight_ticket'      => $flTicket,
                    'ticket_status'      => $flTicketStatus,
                    'flight_cost'        => $flCost,
                    'flight_sell'        => $flSell,
                    'flight_supplier_id' => $flSupId ?: null,
                    'flight_pnr'         => $flPnr ?: null,
                    'flight_date'        => !empty($flDate) ? date('Y-m-d', strtotime($flDate)) : null,
                    'flight_time'        => $flTime ?: null,
                    'surname'            => $surname,
                    'given_name'         => $givenName,
                    'father_name'        => $person['father_name'] ?? null,
                    'dob'                => $person['dob'] ?? null,
                    'gender'             => $person['gender'] ?? null,
                    'city'               => $person['city'] ?? null,
                    'blood_group'        => $person['blood_group'] ?? null,
                    'passport_number'    => $person['passport_number'] ?? null,
                    'room_number'        => $person['room_number'] ?? ($idx === 0 ? $roomNumber : null),
                    'cnic'               => $person['cnic'] ?? null,
                    'phone'              => $person['phone'] ?? null,
                    'nominee_name'       => $person['nominee_name'] ?? null,
                    'nominee_relation'   => $person['nominee_relation'] ?? null,
                    'nominee_cnic'       => $person['nominee_cnic'] ?? null,
                    'nominee_mobile'     => $person['nominee_mobile'] ?? null,
                ];

                $docFields = ['cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate'];

                foreach ($docFields as $field) {
                    if ($request->hasFile("persons.$idx.$field")) {
                        $path = $request->file("persons.$idx.$field")
                            ->store('bookings/persons/' . $field, 'public');
                        $personData[$field] = $path;
                    }
                }

                $personCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_persons');
                $personData = array_intersect_key($personData, array_flip($personCols));
                $booking->persons()->create($personData);
            }
        }

        if ($request->has('hotels')) {
            $hotelCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_hotels');
            foreach ($request->hotels as $idx => $hotel) {
                if (!empty($hotel['hotel_name'])) {
                    $hotelData = $hotel;
                    if ($request->hasFile("hotels.$idx.hotel_voucher")) {
                        $hotelData['hotel_voucher'] = $request->file("hotels.$idx.hotel_voucher")->store('bookings/hotels', 'public');
                    }
                    if (isset($hotelData['person_rates']) && is_string($hotelData['person_rates'])) {
                        $hotelData['person_rates'] = json_decode($hotelData['person_rates'], true);
                    }
                    foreach (['check_in', 'check_out', 'option_date', 'booking_date'] as $dCol) {
                        if (isset($hotelData[$dCol]) && trim((string)$hotelData[$dCol]) === '') {
                            $hotelData[$dCol] = null;
                        }
                    }
                    if (empty($hotelData['booking_date'])) {
                        $hotelData['booking_date'] = !empty($hotelData['check_in']) ? $hotelData['check_in'] : $finalBookingDate;
                    }
                    if (isset($hotelData['supplier_id']) && empty($hotelData['supplier_id'])) {
                        $hotelData['supplier_id'] = null;
                    }
                    if (empty($hotelData['no_of_rooms']) || !is_numeric($hotelData['no_of_rooms'])) {
                        $hotelData['no_of_rooms'] = 1;
                    }
                    if (empty($hotelData['no_of_nights']) || !is_numeric($hotelData['no_of_nights'])) {
                        $hotelData['no_of_nights'] = 1;
                    }
                    if (empty($hotelData['room_type'])) {
                        $hotelData['room_type'] = 'double';
                    }

                    // Point 9: Additional rooms
                    if (isset($hotel['extra_rooms'])) {
                        $hotelData['additional_rooms'] = is_array($hotel['extra_rooms']) ? $hotel['extra_rooms'] : json_decode($hotel['extra_rooms'], true);
                    } elseif (isset($hotel['additional_rooms'])) {
                        $hotelData['additional_rooms'] = is_array($hotel['additional_rooms']) ? $hotel['additional_rooms'] : json_decode($hotel['additional_rooms'], true);
                    }
                    // Point 23: Child voucher number
                    $hotelData['voucher_no'] = $hotel['voucher_no'] ?? ($hotelVoucher ? ($hotelVoucher . '-' . ($idx + 1)) : null);

                    $hotelData = array_intersect_key($hotelData, array_flip($hotelCols));
                    $booking->hotels()->create($hotelData);
                }
            }
        }

        if ($request->has('transports')) {
            $transportCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_transports');
            foreach ($request->transports as $idx => $transport) {
                if (!empty($transport['route'])) {
                    $transportData = $transport;
                    if ($request->hasFile("transports.$idx.transport_ticket")) {
                        $transportData['transport_ticket'] = $request->file("transports.$idx.transport_ticket")->store('bookings/transports', 'public');
                    }
                    foreach (['booking_date', 'option_date', 'travel_date', 'travel_time'] as $dCol) {
                        if (isset($transportData[$dCol]) && trim((string)$transportData[$dCol]) === '') {
                            $transportData[$dCol] = null;
                        }
                    }
                    if (empty($transportData['booking_date'])) {
                        $transportData['booking_date'] = !empty($transportData['travel_date']) ? $transportData['travel_date'] : $finalBookingDate;
                    }
                    if (isset($transportData['supplier_id']) && empty($transportData['supplier_id'])) {
                        $transportData['supplier_id'] = null;
                    }
                    if (empty($transportData['quantity']) || !is_numeric($transportData['quantity'])) {
                        $transportData['quantity'] = 1;
                    }
                    if (empty($transportData['pax']) || !is_numeric($transportData['pax'])) {
                        $transportData['pax'] = 1;
                    }
                    if (empty($transportData['status'])) {
                        $transportData['status'] = 'CONFIRMED';
                    }
                    if (empty($transportData['transport_type'])) {
                        $vStr = strtolower($transportData['vehicle'] ?? '');
                        if (strpos($vStr, 'bus') !== false || strpos($vStr, 'coach') !== false) {
                            $transportData['transport_type'] = 'bus';
                        } elseif (strpos($vStr, 'train') !== false) {
                            $transportData['transport_type'] = 'train';
                        } elseif (strpos($vStr, 'flight') !== false) {
                            $transportData['transport_type'] = 'flight';
                        } elseif (strpos($vStr, 'van') !== false || strpos($vStr, 'hiace') !== false) {
                            $transportData['transport_type'] = 'shared_van';
                        } else {
                            $transportData['transport_type'] = 'private_car';
                        }
                    }
                    // Point 23: Child voucher number
                    $transportData['voucher_no'] = $transport['voucher_no'] ?? ($transportVoucher ? ($transportVoucher . '-' . ($idx + 1)) : null);

                    $transportData = array_intersect_key($transportData, array_flip($transportCols));
                    $booking->transports()->create($transportData);
                }
            }
        }

        if ($request->has('visas')) {
            $visaCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_visas');
            foreach ($request->visas as $idx => $visa) {
                if (!empty($visa['passport_number']) || !empty($visa['given_name']) || (float)($visa['cost_rate'] ?? 0) > 0 || (float)($visa['selling_rate'] ?? 0) > 0) {
                    $visaData = $visa;
                    if ($request->hasFile("visas.$idx.visa_attachment")) {
                        $visaData['visa_attachment'] = $request->file("visas.$idx.visa_attachment")->store('bookings/visas', 'public');
                    }
                    foreach (['date_of_birth', 'booking_date', 'option_date'] as $dCol) {
                        if (isset($visaData[$dCol]) && trim((string)$visaData[$dCol]) === '') {
                            $visaData[$dCol] = null;
                        }
                    }
                    if (empty($visaData['booking_date'])) {
                        $visaData['booking_date'] = $finalBookingDate;
                    }
                    // Point 23: Child voucher number
                    $visaData['voucher_no'] = $visa['voucher_no'] ?? ($visaVoucher ? ($visaVoucher . '-' . ($idx + 1)) : null);

                    $visaData = array_intersect_key($visaData, array_flip($visaCols));
                    $booking->visas()->create($visaData);
                }
            }
        }

        logUserActivity(
            'Booking Created',
            'Type: ' . $booking->booking_for . ' | Package: ' . $booking->package_type . ' | Total: ' . $booking->total_amount,
            $booking->id,
            'Booking'
        );

        return redirect()->route('booking.index')->with('success', 'Booking created successfully!');
    }

    public function show($id)
    {
        $booking = Booking::with(['package', 'client', 'company', 'persons', 'hotels.supplier', 'transports.supplier', 'visas.supplier', 'flightSupplier'])->findOrFail($id);
        return view('booking.show', compact('booking'));
    }

    public function edit($id)
    {
        // Auto-sync: if any active clients belong to trashed leads, move them to trash
        $trashedLeadIds = \App\Models\Lead::onlyTrashed()->pluck('id');
        if ($trashedLeadIds->isNotEmpty()) {
            Client::whereIn('lead_id', $trashedLeadIds)->delete();
        }

        $booking   = Booking::with(['package', 'persons', 'hotels.supplier', 'transports', 'visas.supplier', 'flightSupplier', 'client', 'company'])->findOrFail($id);
        $clients   = Client::where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->where(function ($q) use ($booking) {
                $q->where('id', $booking->client_id)
                  ->orWhere(function ($sub) {
                      $sub->whereDoesntHave('lead', function ($lq) {
                          $lq->onlyTrashed();
                      });
                  });
            })
            ->orderBy('name')
            ->get();
        $companies = Company::all();
        $packages  = Package::with(['accommodations.hotel', 'transports', 'transportFlights'])->latest()->get();
        $hotels    = \App\Models\Hotel::all();
        $suppliers = Supplier::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $airlines  = \App\Models\Airline::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $routes    = \App\Models\Route::where('status', 'active')->orWhereNull('status')->orderBy('start_place')->get();
        $vehicles  = \App\Models\Vehicle::where('status', 'active')->orWhereNull('status')->orderBy('vehicle_type')->get();
        $mealPlans = \App\Models\MealPlan::where('status', 'active')->orderBy('name')->get();
        $years     = [date('Y'), date('Y') + 1, date('Y') + 2];

        $transactionsPaid = Transaction::where('client_id', $booking->client_id)
            ->where('status', 'confirmed')
            ->sum('amount');

        $curYear = date('Y');
        if (empty($booking->voucher_number)) {
            $booking->voucher_number = self::getNextVoucherNo('booking', $curYear);
        }
        $defaultVouchers = [
            'booking'   => self::getNextVoucherNo('booking', $curYear),
            'flight'    => self::getNextVoucherNo('flight', $curYear),
            'hotel'     => self::getNextVoucherNo('hotel', $curYear),
            'transport' => self::getNextVoucherNo('transport', $curYear),
            'visa'      => self::getNextVoucherNo('visa', $curYear),
        ];

        return view('booking.edit', compact('booking', 'clients', 'companies', 'packages', 'hotels', 'suppliers', 'years', 'transactionsPaid', 'airlines', 'routes', 'vehicles', 'mealPlans', 'defaultVouchers'));
    }

    public function update(Request $request, $id)
    {
        \Log::info('Update Request ID ' . $id . ': ', $request->all());
        $booking = Booking::findOrFail($id);

        $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'package_id'   => 'nullable',
            'package_type' => 'nullable|string',
            'haji_id'      => 'nullable|string|max:100',
            'room_number'  => 'nullable|string|max:100',
        ]);

        // Point 4: Validate Unique Ticket Numbers
        $submittedTickets = [];
        $personsInput = $request->persons ?? [];
        $flightPersonsInput = $request->flight_persons ?? [];
        $maxRows = max(count($personsInput), count($flightPersonsInput));

        for ($i = 0; $i < $maxRows; $i++) {
            $t = trim((string) ($personsInput[$i]['flight_ticket'] ?? ($flightPersonsInput[$i]['ticket'] ?? ($flightPersonsInput[$i]['flight_ticket'] ?? ''))));
            if ($t !== '') {
                $tLower = strtolower($t);
                if (in_array($tLower, $submittedTickets, true)) {
                    return back()->withInput()->with('error', "Duplicate ticket number '{$t}' found in the booking form. Each passenger must have a unique ticket number.");
                }
                $submittedTickets[] = $tLower;

                if (\Illuminate\Support\Facades\DB::table('booking_persons')->where('flight_ticket', $t)->where('booking_id', '!=', $booking->id)->exists()) {
                    return back()->withInput()->with('error', "Ticket number '{$t}' is already registered in another booking. Every ticket number must be unique.");
                }
            }
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $bookingFor = 'client';
            $clientId   = $request->client_id;
            $companyId  = null;
            $hajiId     = $request->haji_id ?? ($request->persons[0]['haji_id'] ?? null);
            $roomNumber = $request->room_number ?? ($request->persons[0]['room_number'] ?? null);

            $flightCharges = (float) ($request->flight_charges ?: ($request->flight_charges_disp ?: 0));
            if ($flightCharges <= 0 && (float) $request->flight_selling_rate > 0) {
                $flightRoe = (float) ($request->flight_sale_roe ?: ($request->flight_roe ?: ($booking->flight_sale_roe ?: 75)));
                $flightCharges = (float) $request->flight_selling_rate * ($request->flight_currency === 'PKR' ? 1 : $flightRoe);
            }

            $total = (($request->package_cost ?? 0) * ($request->no_of_pax ?? 1))
                + ($request->hotel_charges ?? 0)
                + ($request->visa_charges ?? 0)
                + ($request->transport_charges ?? 0)
                + $flightCharges
                + ($request->other_charges ?? 0)
                + ($request->additional_services_amount ?? 0)
                - ($request->discount ?? 0);

            $additionalServicesDetail = $request->additional_services_detail;
            if (trim(strip_tags($additionalServicesDetail ?? '')) === '') {
                $additionalServicesDetail = null;
            }

            $hasHotels = $request->has('hotels') && is_array($request->hotels) && count(array_filter($request->hotels, function($h) {
                return !empty($h['hotel_id']) || !empty($h['hotel_name']) || (float)($h['cost_per_night'] ?? 0) > 0 || (float)($h['selling_per_night'] ?? 0) > 0 || (float)($h['cost_amount'] ?? 0) > 0 || (float)($h['selling_amount'] ?? 0) > 0;
            })) > 0;

            $hasFlight = (float)($request->flight_charges ?? 0) > 0
                || (float)($request->flight_cost ?? 0) > 0
                || (float)($request->flight_cost_rate ?? 0) > 0
                || (float)($request->flight_selling_rate ?? 0) > 0
                || !empty($request->flight_supplier_id)
                || !empty($request->flight_number)
                || ($request->has('flight_routes') && count($request->flight_routes) > 0);

            $hasVisas = $request->has('visas') && is_array($request->visas) && count(array_filter($request->visas, function($v) {
                return !empty($v['passport_number']) || !empty($v['given_name']) || !empty($v['supplier_id']) || (float)($v['cost_rate'] ?? 0) > 0 || (float)($v['selling_rate'] ?? 0) > 0;
            })) > 0;

            $hasTransports = $request->has('transports') && is_array($request->transports) && count(array_filter($request->transports, function($t) {
                return !empty($t['route']) || !empty($t['supplier_id']) || (float)($t['cost_rate'] ?? 0) > 0 || (float)($t['selling_rate'] ?? 0) > 0;
            })) > 0;

            // Date Resolution:
            // Prioritize primary booking_date submitted by user, or any of the synchronized tab date fields
            $resolvedBookingDate = $request->booking_date 
                ?: ($request->booking_date_package_tab 
                ?: ($request->booking_date_hotel_tab 
                ?: ($request->booking_date_flight_tab 
                ?: ($request->booking_date_passenger_tab 
                ?: ($request->booking_date_transport_tab 
                ?: ($request->booking_date_visa_tab 
                ?: ($request->flight_booking_date 
                ?: ($booking->booking_date ?: now()->toDateString()))))))));

            $finalBookingDate = date('Y-m-d', strtotime($resolvedBookingDate));

            $calcAdults = 0;
            $calcChildren = 0;
            $calcInfants = 0;
            if (!empty($request->persons) && is_array($request->persons)) {
                foreach ($request->persons as $p) {
                    $pt = strtolower($p['pax_type'] ?? 'adult');
                    if ($pt === 'child') $calcChildren++;
                    elseif ($pt === 'infant') $calcInfants++;
                    else $calcAdults++;
                }
            }
            $finalAdults = $calcAdults > 0 ? $calcAdults : ($request->pax_adults ?? ($request->adults_count ?? ($booking->pax_adults ?? 1)));
            $finalChildren = ($calcAdults > 0 || $calcChildren > 0 || $calcInfants > 0) ? $calcChildren : ($request->pax_children ?? ($request->children_count ?? ($booking->pax_children ?? 0)));
            $finalInfants = ($calcAdults > 0 || $calcChildren > 0 || $calcInfants > 0) ? $calcInfants : ($request->pax_infants ?? ($request->infants_count ?? ($booking->pax_infants ?? 0)));

            $bookingData = array_merge(
                $request->except(['persons', 'hotels', 'transports', 'visas', 'flight_persons', '_token', '_method', 'cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate', 'flight_attachment']),
                [
                    'booking_for'         => $bookingFor,
                    'client_id'           => $clientId,
                    'company_id'          => $companyId,
                    'booking_date'        => $finalBookingDate,
                    'flight_booking_date' => $hasFlight ? (!empty($request->flight_booking_date) ? date('Y-m-d', strtotime($request->flight_booking_date)) : $finalBookingDate) : null,
                    'currency'            => $request->currency ?? ($booking->currency ?? 'PKR'),
                    'haji_id'             => $hajiId,
                    'room_number'         => $roomNumber,
                    'flight_supplier_id'  => !empty($request->flight_supplier_id) ? $request->flight_supplier_id : null,
                    'flight_currency'     => $request->flight_currency ?? ($booking->flight_currency ?? 'SAR'),
                    'flight_cost_roe'     => $request->flight_cost_roe ?? ($request->flight_roe ?? ($booking->flight_cost_roe ?? 75)),
                    'flight_sale_roe'     => $request->flight_sale_roe ?? ($request->flight_roe ?? ($booking->flight_sale_roe ?? 75)),
                    'flight_roe'          => $request->flight_cost_roe ?? ($request->flight_roe ?? ($booking->flight_roe ?? 75)),
                    'flight_cost_rate'    => $request->flight_cost_rate ?? 0,
                    'flight_cost'         => $request->flight_cost ?? 0,
                    'flight_selling_rate' => $request->flight_selling_rate ?? 0,
                    'pax_adults'          => $finalAdults,
                    'pax_children'        => $finalChildren,
                    'pax_infants'         => $finalInfants,
                    'adults_count'        => $finalAdults,
                    'children_count'      => $finalChildren,
                    'infants_count'       => $finalInfants,
                    'no_of_pax'           => $request->no_of_pax ?? max(1, count($request->persons ?? [])),
                    'flight_adult_cost'   => $request->flight_adult_cost ?? ($booking->flight_adult_cost ?? 0),
                    'flight_adult_sell'   => $request->flight_adult_sell ?? ($booking->flight_adult_sell ?? 0),
                    'flight_child_cost'   => $request->flight_child_cost ?? ($booking->flight_child_cost ?? 0),
                    'flight_child_sell'   => $request->flight_child_sell ?? ($booking->flight_child_sell ?? 0),
                    'flight_infant_cost'  => $request->flight_infant_cost ?? ($booking->flight_infant_cost ?? 0),
                    'flight_infant_sell'  => $request->flight_infant_sell ?? ($booking->flight_infant_sell ?? 0),
                    'package_cost'        => $request->package_cost ?? 0,
                    'hotel_charges'       => $request->hotel_charges ?? 0,
                    'visa_charges'        => $request->visa_charges ?? 0,
                    'transport_charges'   => $request->transport_charges ?? 0,
                    'flight_charges'      => $flightCharges,
                    'other_charges'       => $request->other_charges ?? 0,
                    'discount'            => $request->discount ?? 0,
                    'additional_services_amount' => $request->additional_services_amount ?? 0,
                    'additional_services_detail' => $additionalServicesDetail,
                    'total_received'      => $request->total_received ?? 0,
                    'total_amount'        => $total,
                    'balance'             => $total - ($request->total_received ?? 0),
                ]
            );

            // Clean date fields to prevent MySQL strict mode 1292 errors
            foreach (['flight_booking_date', 'flight_option_date', 'departure_date', 'arrival_date', 'booking_date'] as $dateCol) {
                if (isset($bookingData[$dateCol]) && trim((string)$bookingData[$dateCol]) === '') {
                    $bookingData[$dateCol] = null;
                }
            }
            if (isset($bookingData['package_id']) && empty($bookingData['package_id'])) {
                $bookingData['package_id'] = null;
            }

            if ($request->hasFile('flight_attachment')) {
                $bookingData['flight_attachment'] = $request->file('flight_attachment')->store('bookings/flight_attachment', 'public');
            } else {
                $bookingData['flight_attachment'] = $request->input('old_flight_attachment');
            }

            $curYear  = date('Y');
            $bookingCategory  = $request->booking_category ?: ($booking->booking_category ?: 'umrah');
            $flightVoucher    = $hasFlight ? ($booking->flight_voucher_no ?: ($request->flight_voucher_no ?: self::getNextVoucherNo('flight', $curYear))) : null;
            $hotelVoucher     = $hasHotels ? ($booking->hotel_voucher_no ?: ($request->hotel_voucher_no ?: self::getNextVoucherNo('hotel', $curYear))) : null;
            $transportVoucher = $hasTransports ? ($booking->transport_voucher_no ?: ($request->transport_voucher_no ?: self::getNextVoucherNo('transport', $curYear))) : null;
            $visaVoucher      = $hasVisas ? ($booking->visa_voucher_no ?: ($request->visa_voucher_no ?: self::getNextVoucherNo('visa', $curYear))) : null;

            $isFlightOnly = ($bookingCategory === 'domestic' || $bookingCategory === 'international' || (!$hasHotels && $hasFlight && !$hasVisas && !$hasTransports));
            $bookingVoucher   = $request->voucher_number ?: ($booking->voucher_number ?: self::getNextVoucherNo('booking', $curYear));

            $bookingData['booking_category']     = $bookingCategory;
            $bookingData['voucher_number']       = $bookingVoucher;
            $bookingData['flight_voucher_no']    = $flightVoucher;
            $bookingData['hotel_voucher_no']     = $hotelVoucher;
            $bookingData['transport_voucher_no'] = $transportVoucher;
            $bookingData['visa_voucher_no']      = $visaVoucher;
            $bookingData['flight_time_format']   = $request->flight_time_format ?? ($booking->flight_time_format ?? '12');

            // Defensive column check against missing columns on older database schemas
            $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('bookings');
            $bookingData = array_intersect_key($bookingData, array_flip($existingCols));

            $booking->update($bookingData);

            $booking->persons()->delete();
            if ($request->has('persons')) {
                $personCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_persons');
                foreach ($request->persons as $idx => $person) {
                    $surname   = $person['surname'] ?? null;
                    $givenName = $person['given_name'] ?? null;
                    $paxName   = $person['full_name'] ?? null;

                    if (empty($paxName) && ($givenName || $surname)) {
                        $paxName = trim(($surname ?? '') . ' ' . ($givenName ?? ''));
                    }

                    if (empty($paxName)) {
                        if ($idx === 0 && $booking->client) {
                            $paxName = $booking->client->name;
                        } else {
                            $paxName = 'Passenger ' . ($idx + 1);
                        }
                    }

                    $flPerson = $request->input("flight_persons.$idx", []);
                    $paxType  = $person['pax_type'] ?? ($flPerson['pax_type'] ?? 'adult');
                    $flTicket = $person['flight_ticket'] ?? ($flPerson['ticket'] ?? ($flPerson['flight_ticket'] ?? null));
                    $flCost   = (float) ($person['flight_cost'] ?? ($flPerson['cost_rate'] ?? ($flPerson['flight_cost'] ?? 0)));
                    $flSell   = (float) ($person['flight_sell'] ?? ($flPerson['sell_rate'] ?? ($flPerson['flight_sell'] ?? 0)));
                    $flSupId  = $flPerson['flight_supplier_id'] ?? ($person['flight_supplier_id'] ?? ($request->flight_supplier_id ?: null));
                    $flPnr    = $flPerson['flight_pnr'] ?? ($person['flight_pnr'] ?? ($request->departure_pnr ?: null));
                    $flDate   = $flPerson['flight_date'] ?? ($person['flight_date'] ?? ($request->departure_date ?: null));
                    $flTime   = $flPerson['flight_time'] ?? ($person['flight_time'] ?? ($request->departure_time ?: null));
                    $flTicketStatus = $flPerson['ticket_status'] ?? ($person['ticket_status'] ?? 'confirmed');

                    $personData = [
                        'full_name'          => $paxName,
                        'haji_id'            => $person['haji_id'] ?? ($idx === 0 ? $hajiId : null),
                        'pax_type'           => $paxType,
                        'flight_ticket'      => $flTicket,
                        'ticket_status'      => $flTicketStatus,
                        'flight_cost'        => $flCost,
                        'flight_sell'        => $flSell,
                        'flight_supplier_id' => $flSupId ?: null,
                        'flight_pnr'         => $flPnr ?: null,
                        'flight_date'        => !empty($flDate) ? date('Y-m-d', strtotime($flDate)) : null,
                        'flight_time'        => $flTime ?: null,
                        'surname'            => $surname,
                        'given_name'         => $givenName,
                        'father_name'        => $person['father_name'] ?? null,
                        'dob'                => !empty($person['dob']) ? $person['dob'] : null,
                        'gender'             => $person['gender'] ?? null,
                        'city'               => $person['city'] ?? null,
                        'blood_group'        => $person['blood_group'] ?? null,
                        'passport_number'    => $person['passport_number'] ?? null,
                        'room_number'        => $person['room_number'] ?? ($idx === 0 ? $roomNumber : null),
                        'cnic'               => $person['cnic'] ?? null,
                        'phone'              => $person['phone'] ?? null,
                        'nominee_name'       => $person['nominee_name'] ?? null,
                        'nominee_relation'   => $person['nominee_relation'] ?? null,
                        'nominee_cnic'       => $person['nominee_cnic'] ?? null,
                        'nominee_mobile'     => $person['nominee_mobile'] ?? null,
                    ];

                    $docFields = ['cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate'];
                    foreach ($docFields as $field) {
                        if ($request->hasFile("persons.$idx.$field")) {
                            $personData[$field] = $request->file("persons.$idx.$field")->store('bookings/persons/' . $field, 'public');
                        } else {
                            $personData[$field] = $person['old_' . $field] ?? null;
                        }
                    }

                    $personData = array_intersect_key($personData, array_flip($personCols));
                    $booking->persons()->create($personData);
                }
            }

            $booking->hotels()->delete();
            if ($request->has('hotels')) {
                $hotelCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_hotels');
                foreach ($request->hotels as $idx => $hotel) {
                    if (!empty($hotel['hotel_name'])) {
                        $hotelData = $hotel;
                        if ($request->hasFile("hotels.$idx.hotel_voucher")) {
                            $hotelData['hotel_voucher'] = $request->file("hotels.$idx.hotel_voucher")->store('bookings/hotels', 'public');
                        } else {
                            $hotelData['hotel_voucher'] = $hotel['old_hotel_voucher'] ?? null;
                        }
                        
                        unset($hotelData['old_hotel_voucher']);

                        foreach (['check_in', 'check_out', 'option_date', 'booking_date'] as $dCol) {
                            if (isset($hotelData[$dCol]) && trim((string)$hotelData[$dCol]) === '') {
                                $hotelData[$dCol] = null;
                            }
                        }
                        if (empty($hotelData['booking_date'])) {
                            $hotelData['booking_date'] = !empty($hotelData['check_in']) ? $hotelData['check_in'] : $finalBookingDate;
                        }
                        if (isset($hotelData['supplier_id']) && empty($hotelData['supplier_id'])) {
                            $hotelData['supplier_id'] = null;
                        }
                        if (isset($hotelData['person_rates']) && is_string($hotelData['person_rates'])) {
                            $hotelData['person_rates'] = json_decode($hotelData['person_rates'], true);
                        }
                        if (empty($hotelData['no_of_rooms']) || !is_numeric($hotelData['no_of_rooms'])) {
                            $hotelData['no_of_rooms'] = 1;
                        }
                        if (empty($hotelData['no_of_nights']) || !is_numeric($hotelData['no_of_nights'])) {
                            $hotelData['no_of_nights'] = 1;
                        }
                        if (empty($hotelData['room_type'])) {
                            $hotelData['room_type'] = 'double';
                        }

                        // Point 9: Additional rooms
                        if (isset($hotel['extra_rooms'])) {
                            $hotelData['additional_rooms'] = is_array($hotel['extra_rooms']) ? $hotel['extra_rooms'] : json_decode($hotel['extra_rooms'], true);
                        } elseif (isset($hotel['additional_rooms'])) {
                            $hotelData['additional_rooms'] = is_array($hotel['additional_rooms']) ? $hotel['additional_rooms'] : json_decode($hotel['additional_rooms'], true);
                        }
                        // Point 23: Child voucher number
                        $hotelData['voucher_no'] = $hotel['voucher_no'] ?? ($hotelVoucher ? ($hotelVoucher . '-' . ($idx + 1)) : null);

                        $hotelData = array_intersect_key($hotelData, array_flip($hotelCols));
                        $booking->hotels()->create($hotelData);
                    }
                }
            }

            $booking->transports()->delete();
            if ($request->has('transports')) {
                $transportCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_transports');
                foreach ($request->transports as $idx => $transport) {
                    if (!empty($transport['route'])) {
                        $transportData = $transport;
                        if ($request->hasFile("transports.$idx.transport_ticket")) {
                            $transportData['transport_ticket'] = $request->file("transports.$idx.transport_ticket")->store('bookings/transports', 'public');
                        } else {
                            $transportData['transport_ticket'] = $transport['old_transport_ticket'] ?? null;
                        }
                        unset($transportData['old_transport_ticket']);

                        foreach (['booking_date', 'option_date', 'travel_date', 'travel_time'] as $dCol) {
                            if (isset($transportData[$dCol]) && trim((string)$transportData[$dCol]) === '') {
                                $transportData[$dCol] = null;
                            }
                        }
                        if (empty($transportData['booking_date'])) {
                            $transportData['booking_date'] = !empty($transportData['travel_date']) ? $transportData['travel_date'] : $finalBookingDate;
                        }
                        if (isset($transportData['supplier_id']) && empty($transportData['supplier_id'])) {
                            $transportData['supplier_id'] = null;
                        }
                        if (empty($transportData['quantity']) || !is_numeric($transportData['quantity'])) {
                            $transportData['quantity'] = 1;
                        }
                        if (empty($transportData['pax']) || !is_numeric($transportData['pax'])) {
                            $transportData['pax'] = 1;
                        }
                        if (empty($transportData['status'])) {
                            $transportData['status'] = 'CONFIRMED';
                        }
                        if (empty($transportData['transport_type'])) {
                            $vStr = strtolower($transportData['vehicle'] ?? '');
                            if (strpos($vStr, 'bus') !== false || strpos($vStr, 'coach') !== false) {
                                $transportData['transport_type'] = 'bus';
                            } elseif (strpos($vStr, 'train') !== false) {
                                $transportData['transport_type'] = 'train';
                            } elseif (strpos($vStr, 'flight') !== false) {
                                $transportData['transport_type'] = 'flight';
                            } elseif (strpos($vStr, 'van') !== false || strpos($vStr, 'hiace') !== false) {
                                $transportData['transport_type'] = 'shared_van';
                            } else {
                                $transportData['transport_type'] = 'private_car';
                            }
                        }
                        // Point 23: Child voucher number
                        $transportData['voucher_no'] = $transport['voucher_no'] ?? ($transportVoucher ? ($transportVoucher . '-' . ($idx + 1)) : null);

                        $transportData = array_intersect_key($transportData, array_flip($transportCols));
                        $booking->transports()->create($transportData);
                    }
                }
            }

            $booking->visas()->delete();
            if ($request->has('visas')) {
                $visaCols = \Illuminate\Support\Facades\Schema::getColumnListing('booking_visas');
                foreach ($request->visas as $idx => $visa) {
                    if (!empty($visa['passport_number']) || !empty($visa['given_name']) || (float)($visa['cost_rate'] ?? 0) > 0 || (float)($visa['selling_rate'] ?? 0) > 0) {
                        $visaData = $visa;
                        if ($request->hasFile("visas.$idx.visa_attachment")) {
                            $visaData['visa_attachment'] = $request->file("visas.$idx.visa_attachment")->store('bookings/visas', 'public');
                        } else {
                            $visaData['visa_attachment'] = $visa['old_visa_attachment'] ?? null;
                        }
                        unset($visaData['old_visa_attachment']);

                        foreach (['date_of_birth', 'booking_date', 'option_date'] as $dCol) {
                            if (isset($visaData[$dCol]) && trim((string)$visaData[$dCol]) === '') {
                                $visaData[$dCol] = null;
                            }
                        }
                        if (empty($visaData['booking_date'])) {
                            $visaData['booking_date'] = $finalBookingDate;
                        }
                        if (isset($visaData['supplier_id']) && empty($visaData['supplier_id'])) {
                            $visaData['supplier_id'] = null;
                        }
                        // Point 23: Child voucher number
                        $visaData['voucher_no'] = $visa['voucher_no'] ?? ($visaVoucher ? ($visaVoucher . '-' . ($idx + 1)) : null);

                        $visaData = array_intersect_key($visaData, array_flip($visaCols));
                        $booking->visas()->create($visaData);
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            logUserActivity('Booking Updated', 'Package: ' . $booking->package_type . ' | Total: ' . $booking->total_amount, $booking->id, 'Booking');

            $activeTab = $request->input('active_tab', '#tab-details');
            $saveAction = $request->input('save_action', 'save_stay');

            if ($saveAction === 'save_close') {
                return redirect()->route('booking.index')->with('success', 'Booking updated successfully!');
            }

            return redirect()->route('booking.edit', ['id' => $booking->id, 'tab' => $activeTab])
                ->with('success', 'Booking changes saved successfully!');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('Booking Update Error (ID ' . $id . '): ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Error updating booking: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $booking = Booking::with(['client', 'company'])->findOrFail($id);

        logUserActivity(
            'Booking Deleted',
            'Client: ' . ($booking->client->name ?? $booking->company->name ?? 'N/A') . ' | Package: ' . $booking->package_type,
            $booking->id,
            'Booking'
        );

        $booking->delete();

        return redirect()->route('booking.index')->with('success', 'Moved to trash.');
    }

    public function trash()
    {
        $bookings = Booking::onlyTrashed()->with(['client', 'company'])->latest()->get();
        return view('booking.trash', compact('bookings'));
    }

    public function restore($id)
    {
        $booking = Booking::onlyTrashed()->with(['client', 'company'])->findOrFail($id);
        $booking->restore();

        logUserActivity(
            'Booking Restored',
            'Client: ' . ($booking->client->name ?? $booking->company->name ?? 'N/A') . ' | Package: ' . $booking->package_type,
            $booking->id,
            'Booking'
        );

        return redirect()->route('booking.index')->with('success', 'Booking restored.');
    }

    public function agreement(Booking $booking)
    {
        $booking->load(['client', 'company', 'persons', 'hotels', 'transports', 'visas']);
        return view('booking.agreement', compact('booking'));
    }

    public function saveAgreementSignature(Request $request)
    {
        $booking = Booking::findOrFail($request->booking_id);

        $signature = str_replace('data:image/png;base64,', '', $request->signature);
        $signature = str_replace(' ', '+', $signature);

        $fileName = 'signatures/' . uniqid() . '.png';

        Storage::disk('public')->put($fileName, base64_decode($signature));

        $booking->update(['agreement_signature' => $fileName]);

        return response()->json(['success' => true, 'path' => $fileName]);
    }

    public function voucher(Booking $booking)
    {
        $booking->load(['client', 'company', 'persons', 'hotels']);
        return view('booking.voucher', compact('booking'));
    }

    public function umrahInvoice(Request $request, Booking $booking)
    {
        $booking->load([
            'client',
            'company',
            'package',
            'persons',
            'hotels',
            'transports',
            'visas',
        ]);

        $personId = $request->input('person_id');
        $selectedPerson = null;
        if ($personId && $personId !== 'all') {
            $selectedPerson = $booking->persons->firstWhere('id', (int) $personId);
        }

        $showRates = (int) $request->input('show_rates', 0); // 0 = without rates (customer copy), 1 = with rates
        $currencyMode = $request->input('currency_mode', 'mix'); // 'mix' (SAR+PKR), 'pkr', 'sar'

        return view('booking.umrah_invoice', compact('booking', 'selectedPerson', 'showRates', 'currencyMode'));
    }
}
