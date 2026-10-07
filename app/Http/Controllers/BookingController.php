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
        $clients   = Client::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $companies = Company::all();
        $packages  = Package::with(['accommodations.hotel', 'transports', 'transportFlights'])->latest()->get();
        $hotels    = \App\Models\Hotel::all();
        $suppliers = Supplier::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $years     = [date('Y'), date('Y') + 1, date('Y') + 2];

        return view('booking.create', compact('clients', 'companies', 'packages', 'hotels', 'suppliers', 'years'));
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

        $bookingFor = 'client';
        $clientId  = $request->client_id;
        $companyId = null;
        $hajiId    = $request->haji_id ?? ($request->persons[0]['haji_id'] ?? null);
        $roomNumber = $request->room_number ?? ($request->persons[0]['room_number'] ?? null);

        $total = (($request->package_cost ?? 0) * ($request->no_of_pax ?? 1))
            + ($request->hotel_charges ?? 0)
            + ($request->visa_charges ?? 0)
            + ($request->transport_charges ?? 0)
            + ($request->flight_charges ?? 0)
            + ($request->other_charges ?? 0)
            + ($request->additional_services_amount ?? 0)
            - ($request->discount ?? 0);

        $bookingData = array_merge(
            $request->except(['persons', 'hotels', 'transports', 'visas', 'flight_persons', '_token', 'cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate', 'flight_attachment']),
            [
                'booking_for'         => $bookingFor,
                'client_id'           => $clientId,
                'company_id'          => $companyId,
                'booking_date'        => $request->booking_date ? date('Y-m-d', strtotime($request->booking_date)) : now()->toDateString(),
                'currency'            => $request->currency ?? 'PKR',
                'haji_id'             => $hajiId,
                'room_number'         => $roomNumber,
                'flight_roe'          => $request->flight_roe ?? 1,
                'flight_cost_rate'    => $request->flight_cost_rate ?? 0,
                'flight_cost'         => $request->flight_cost ?? 0,
                'flight_selling_rate' => $request->flight_selling_rate ?? 0,
                'package_cost'        => $request->package_cost ?? 0,
                'hotel_charges'       => $request->hotel_charges ?? 0,
                'visa_charges'        => $request->visa_charges ?? 0,
                'transport_charges'   => $request->transport_charges ?? 0,
                'flight_charges'      => $request->flight_charges ?? 0,
                'other_charges'       => $request->other_charges ?? 0,
                'discount'            => $request->discount ?? 0,
                'additional_services_amount' => $request->additional_services_amount ?? 0,
                'additional_services_detail' => $additionalServicesDetail,           
                'total_received'      => $request->total_received ?? 0,
                'total_amount'        => $total,
                'balance'             => $total - ($request->total_received ?? 0),
            ]
        );

        if ($request->hasFile('flight_attachment')) {
            $bookingData['flight_attachment'] = $request->file('flight_attachment')->store('bookings/flight_attachment', 'public');
        }

        $booking = Booking::create($bookingData);

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

                $personData = [
                    'full_name'        => $paxName,
                    'haji_id'          => $person['haji_id'] ?? ($idx === 0 ? $hajiId : null),
                    'surname'          => $surname,
                    'given_name'       => $givenName,
                    'father_name'      => $person['father_name'] ?? null,
                    'dob'              => $person['dob'] ?? null,
                    'gender'           => $person['gender'] ?? null,
                    'city'             => $person['city'] ?? null,
                    'blood_group'      => $person['blood_group'] ?? null,
                    'passport_number'  => $person['passport_number'] ?? null,
                    'room_number'      => $person['room_number'] ?? ($idx === 0 ? $roomNumber : null),
                    'cnic'             => $person['cnic'] ?? null,
                    'phone'            => $person['phone'] ?? null,
                    'nominee_name'     => $person['nominee_name'] ?? null,
                    'nominee_relation' => $person['nominee_relation'] ?? null,
                    'nominee_cnic'     => $person['nominee_cnic'] ?? null,
                    'nominee_mobile'   => $person['nominee_mobile'] ?? null,
                ];

                $docFields = ['cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate'];

                foreach ($docFields as $field) {
                    if ($request->hasFile("persons.$idx.$field")) {
                        $path = $request->file("persons.$idx.$field")
                            ->store('bookings/persons/' . $field, 'public');
                        $personData[$field] = $path;
                    }
                }

                $booking->persons()->create($personData);
            }
        }

        if ($request->has('hotels')) {
            foreach ($request->hotels as $idx => $hotel) {
                if (!empty($hotel['hotel_name'])) {
                    $hotelData = $hotel;
                    if ($request->hasFile("hotels.$idx.hotel_voucher")) {
                        $hotelData['hotel_voucher'] = $request->file("hotels.$idx.hotel_voucher")->store('bookings/hotels', 'public');
                    }
                    $booking->hotels()->create($hotelData);
                }
            }
        }

        if ($request->has('transports')) {
            foreach ($request->transports as $idx => $transport) {
                if (!empty($transport['route'])) {
                    $transportData = $transport;
                    if ($request->hasFile("transports.$idx.transport_ticket")) {
                        $transportData['transport_ticket'] = $request->file("transports.$idx.transport_ticket")->store('bookings/transports', 'public');
                    }
                    $booking->transports()->create($transportData);
                }
            }
        }

        if ($request->has('visas')) {
            foreach ($request->visas as $idx => $visa) {
                if (!empty($visa['passport_number'])) {
                    $visaData = $visa;
                    if ($request->hasFile("visas.$idx.visa_attachment")) {
                        $visaData['visa_attachment'] = $request->file("visas.$idx.visa_attachment")->store('bookings/visas', 'public');
                    }
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
        $booking   = Booking::with(['package', 'persons', 'hotels.supplier', 'transports', 'visas.supplier', 'flightSupplier', 'client', 'company'])->findOrFail($id);
        $clients   = Client::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $companies = Company::all();
        $packages  = Package::with(['accommodations.hotel', 'transports', 'transportFlights'])->latest()->get();
        $hotels    = \App\Models\Hotel::all();
        $suppliers = Supplier::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $years     = [date('Y'), date('Y') + 1, date('Y') + 2];

        $transactionsPaid = Transaction::where('client_id', $booking->client_id)
            ->where('status', 'confirmed')
            ->sum('amount');

        return view('booking.edit', compact('booking', 'clients', 'companies', 'packages', 'hotels', 'suppliers', 'years', 'transactionsPaid'));
    }

    public function update(Request $request, $id)
    {
        if (!\Schema::hasColumn('bookings', 'additional_services_amount')) {
            \Schema::table('bookings', function ($table) {
                $table->decimal('additional_services_amount', 15, 2)->default(0)->after('other_charges');
                $table->text('additional_services_detail')->nullable()->after('additional_services_amount');
            });
        }

        \Log::info('Update Request ID ' . $id . ': ', $request->all());
        $booking = Booking::findOrFail($id);

        $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'package_id'   => 'nullable|exists:packages,id',
            'package_type' => 'nullable|string',
            'haji_id'      => 'nullable|string|max:100',
            'room_number'  => 'nullable|string|max:100',
        ]);

        $bookingFor = 'client';
        $clientId  = $request->client_id;
        $companyId = null;
        $hajiId    = $request->haji_id ?? ($request->persons[0]['haji_id'] ?? null);
        $roomNumber = $request->room_number ?? ($request->persons[0]['room_number'] ?? null);

        $total = (($request->package_cost ?? 0) * ($request->no_of_pax ?? 1))
            + ($request->hotel_charges ?? 0)
            + ($request->visa_charges ?? 0)
            + ($request->transport_charges ?? 0)
            + ($request->flight_charges ?? 0)
            + ($request->other_charges ?? 0)
            + ($request->additional_services_amount ?? 0)
            - ($request->discount ?? 0);

        $additionalServicesDetail = $request->additional_services_detail;
        if (trim(strip_tags($additionalServicesDetail ?? '')) === '') {
            $additionalServicesDetail = null;
        }

        $bookingData = array_merge(
            $request->except(['persons', 'hotels', 'transports', 'visas', 'flight_persons', '_token', '_method', 'cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate', 'flight_attachment']),
            [
                'booking_for'         => $bookingFor,
                'client_id'           => $clientId,
                'company_id'          => $companyId,
                'booking_date'        => $request->booking_date ? date('Y-m-d', strtotime($request->booking_date)) : $booking->booking_date,
                'currency'            => $request->currency ?? ($booking->currency ?? 'PKR'),
                'haji_id'             => $hajiId,
                'room_number'         => $roomNumber,
                'flight_roe'          => $request->flight_roe ?? 1,
                'flight_cost_rate'    => $request->flight_cost_rate ?? 0,
                'flight_cost'         => $request->flight_cost ?? 0,
                'flight_selling_rate' => $request->flight_selling_rate ?? 0,
                'package_cost'        => $request->package_cost ?? 0,
                'hotel_charges'       => $request->hotel_charges ?? 0,
                'visa_charges'        => $request->visa_charges ?? 0,
                'transport_charges'   => $request->transport_charges ?? 0,
                'flight_charges'      => $request->flight_charges ?? 0,
                'other_charges'       => $request->other_charges ?? 0,
                'discount'            => $request->discount ?? 0,
                'additional_services_amount' => $request->additional_services_amount ?? 0,
                'additional_services_detail' => $additionalServicesDetail,
                'total_received'      => $request->total_received ?? 0,
                'total_amount'        => $total,
                'balance'             => $total - ($request->total_received ?? 0),
            ]
        );

        if ($request->hasFile('flight_attachment')) {
            $bookingData['flight_attachment'] = $request->file('flight_attachment')->store('bookings/flight_attachment', 'public');
        } else {
            $bookingData['flight_attachment'] = $request->input('old_flight_attachment');
        }

        $booking->update($bookingData);

        $booking->persons()->delete();
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

                $personData = [
                    'full_name'        => $paxName,
                    'haji_id'          => $person['haji_id'] ?? ($idx === 0 ? $hajiId : null),
                    'surname'          => $surname,
                    'given_name'       => $givenName,
                    'father_name'      => $person['father_name'] ?? null,
                    'dob'              => $person['dob'] ?? null,
                    'gender'           => $person['gender'] ?? null,
                    'city'             => $person['city'] ?? null,
                    'blood_group'      => $person['blood_group'] ?? null,
                    'passport_number'  => $person['passport_number'] ?? null,
                    'room_number'      => $person['room_number'] ?? ($idx === 0 ? $roomNumber : null),
                    'cnic'             => $person['cnic'] ?? null,
                    'phone'            => $person['phone'] ?? null,
                    'nominee_name'     => $person['nominee_name'] ?? null,
                    'nominee_relation' => $person['nominee_relation'] ?? null,
                    'nominee_cnic'     => $person['nominee_cnic'] ?? null,
                    'nominee_mobile'   => $person['nominee_mobile'] ?? null,
                ];

                $docFields = ['cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate'];
                foreach ($docFields as $field) {
                    if ($request->hasFile("persons.$idx.$field")) {
                        $personData[$field] = $request->file("persons.$idx.$field")->store('bookings/persons/' . $field, 'public');
                    } else {
                        $personData[$field] = $person['old_' . $field] ?? null;
                    }
                }

                $booking->persons()->create($personData);
            }
        }

        $booking->hotels()->delete();
        if ($request->has('hotels')) {
            foreach ($request->hotels as $idx => $hotel) {
                if (!empty($hotel['hotel_name'])) {
                    $hotelData = $hotel;
                    if ($request->hasFile("hotels.$idx.hotel_voucher")) {
                        $hotelData['hotel_voucher'] = $request->file("hotels.$idx.hotel_voucher")->store('bookings/hotels', 'public');
                    } else {
                        $hotelData['hotel_voucher'] = $hotel['old_hotel_voucher'] ?? null;
                    }
                    
                    unset($hotelData['old_hotel_voucher']);
                    $booking->hotels()->create($hotelData);
                }
            }
        }

        $booking->transports()->delete();
        if ($request->has('transports')) {
            foreach ($request->transports as $idx => $transport) {
                if (!empty($transport['route'])) {
                    $transportData = $transport;
                    if ($request->hasFile("transports.$idx.transport_ticket")) {
                        $transportData['transport_ticket'] = $request->file("transports.$idx.transport_ticket")->store('bookings/transports', 'public');
                    } else {
                        $transportData['transport_ticket'] = $transport['old_transport_ticket'] ?? null;
                    }
                    unset($transportData['old_transport_ticket']);
                    $booking->transports()->create($transportData);
                }
            }
        }

        $booking->visas()->delete();
        if ($request->has('visas')) {
            foreach ($request->visas as $idx => $visa) {
                if (!empty($visa['passport_number'])) {
                    $visaData = $visa;
                    if ($request->hasFile("visas.$idx.visa_attachment")) {
                        $visaData['visa_attachment'] = $request->file("visas.$idx.visa_attachment")->store('bookings/visas', 'public');
                    } else {
                        $visaData['visa_attachment'] = $visa['old_visa_attachment'] ?? null;
                    }
                    unset($visaData['old_visa_attachment']);
                    $booking->visas()->create($visaData);
                }
            }
        }

        logUserActivity('Booking Updated', 'Package: ' . $booking->package_type . ' | Total: ' . $booking->total_amount, $booking->id, 'Booking');

        return redirect()->route('booking.index')->with('success', 'Booking updated!');
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
}
