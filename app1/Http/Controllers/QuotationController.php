<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationAccommodation;
use App\Models\QuotationMealPlan;
use App\Models\QuotationTransfer;
use App\Models\QuotationTour;
use App\Models\QuotationFlightTrain;
use App\Models\QuotationVisa;
use App\Models\Lead;
use App\Models\RoomInventory;
use App\Models\Hotel;
use App\Models\Company;
use App\Models\TravelRoute;
use App\Models\Route as RouteModel;
use App\Models\Vehicle;
use App\Models\Package;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $quotations = Quotation::with(['lead', 'client', 'company', 'package'])
            ->latest()
            ->get();
            
        return view('quotation.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        $leadId = $request->get('lead_id');
        $selectedLead = $leadId ? Lead::with(['company', 'package', 'user'])->find($leadId) : null;

        $leads = Lead::all();
        $hotels = Hotel::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $companies = Company::all();
        $travelRoutes = TravelRoute::all();
        $routes = RouteModel::where('status', 'active')->orWhereNull('status')->get();
        $vehicles = Vehicle::where('status', 'active')->orWhereNull('status')->get();
        $packages = Package::all();
        $clients = Client::all();

        return view('quotation.create', compact(
            'selectedLead',
            'leads',
            'hotels',
            'companies',
            'travelRoutes',
            'routes',
            'vehicles',
            'packages',
            'clients'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Generate unique quotation number
            $year = date('y');
            $count = Quotation::whereYear('created_at', date('Y'))->count() + 1;
            $quotationNumber = 'QT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Fetch lead or client if available for fallback name
            $clientName = $request->client_name;
            if (empty($clientName) && $request->filled('lead_id')) {
                $lead = Lead::find($request->lead_id);
                if ($lead) {
                    $clientName = $lead->customer_name ?? ($lead->contact_person ?? null);
                }
            }
            if (empty($clientName)) {
                $clientName = 'Valued Client';
            }

            $quotationTitle = $request->quotation_title ?: ('Quotation ' . $quotationNumber);

            // 0. Validate Room Inventory Stock Limits (Prevent Overbooking)
            if ($request->has('accommodations') && is_array($request->accommodations)) {
                foreach ($request->accommodations as $accData) {
                    $noOfRooms = !empty($accData['no_of_rooms']) ? intval($accData['no_of_rooms']) : 0;
                    if ($noOfRooms <= 0) {
                        continue;
                    }

                    $hotelId = !empty($accData['hotel_id']) ? $accData['hotel_id'] : null;
                    $hotelName = $accData['hotel_name'] ?? null;
                    if (!$hotelId && $hotelName) {
                        $h = Hotel::where('name', $hotelName)->first();
                        if ($h) {
                            $hotelId = $h->id;
                        }
                    }

                    if ($hotelId) {
                        $roomType = $accData['room_type'] ?? 'Double';
                        $checkIn = !empty($accData['check_in']) ? date('Y-m-d', strtotime($accData['check_in'])) : null;
                        $checkOut = !empty($accData['check_out']) ? date('Y-m-d', strtotime($accData['check_out'])) : null;

                        $avail = RoomInventory::getAvailability($hotelId, $roomType, $checkIn, $checkOut);
                        if ($avail['has_inventory'] && $avail['available'] < $noOfRooms) {
                            $hName = $hotelName ?: ('Hotel #' . $hotelId);
                            DB::rollBack();
                            return back()->withInput()->withErrors([
                                'error' => "Booking Blocked: Only {$avail['available']} {$roomType} room(s) available in stock for '{$hName}'! (You requested {$noOfRooms} rooms, Total Stock: {$avail['total_stock']}, Already Booked: {$avail['booked']})."
                            ]);
                        }
                    }
                }
            }

            // 1. Create Main Quotation (100% nullable & safe)
            $quotation = Quotation::create([
                'quotation_number'    => $quotationNumber,
                'quotation_title'     => $quotationTitle,
                'lead_id'             => $request->filled('lead_id') ? $request->lead_id : null,
                'client_id'           => $request->filled('client_id') ? $request->client_id : null,
                'client_name'         => $clientName,
                'client_phone'        => $request->client_phone ?: null,
                'client_email'        => $request->client_email ?: null,
                'company_id'          => $request->filled('company_id') ? $request->company_id : null,
                'package_id'          => $request->filled('package_id') ? $request->package_id : null,
                'user_id'             => auth()->id() ?: null,
                'total_pax'           => $request->filled('total_pax') ? intval($request->total_pax) : 1,
                'adults_count'        => $request->filled('adults_count') ? intval($request->adults_count) : 1,
                'children_count'      => $request->filled('children_count') ? intval($request->children_count) : 0,
                'infants_count'       => $request->filled('infants_count') ? intval($request->infants_count) : 0,
                'inclusions'          => $request->inclusions ?: null,
                'exclusions'          => $request->exclusions ?: null,
                'terms_and_conditions'=> $request->terms ?: ($request->terms_and_conditions ?: null),
                'remarks'             => $request->remarks ?: null,
                'status'              => $request->status ?? 'draft',
                'valid_until'         => !empty($request->valid_until) ? date('Y-m-d', strtotime($request->valid_until)) : null,
            ]);

            $totalCostPkr = 0;
            $totalSalePkr = 0;

            // 2. Save Accommodations & Separate Meals
            if ($request->has('accommodations') && is_array($request->accommodations)) {
                foreach ($request->accommodations as $accData) {
                    if (
                        empty($accData['hotel_name']) &&
                        empty($accData['hotel_id']) &&
                        empty($accData['cost_amount']) &&
                        empty($accData['selling_amount']) &&
                        empty($accData['check_in']) &&
                        empty($accData['check_out']) &&
                        empty($accData['confirmation_number'])
                    ) {
                        continue;
                    }

                    $curr = $accData['currency'] ?? 'PKR';
                    $rawEx = $accData['exchange_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);

                    $costAmt = floatval($accData['cost_amount'] ?? 0);
                    $saleAmt = floatval($accData['selling_amount'] ?? 0);
                    $costPkr = $costAmt * $exRate;
                    $salePkr = $saleAmt * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    $accHotelId = !empty($accData['hotel_id']) ? $accData['hotel_id'] : null;
                    if (!$accHotelId && !empty($accData['hotel_name'])) {
                        $h = Hotel::where('name', $accData['hotel_name'])->first();
                        if ($h) {
                            $accHotelId = $h->id;
                        }
                    }

                    $acc = QuotationAccommodation::create([
                        'quotation_id'          => $quotation->id,
                        'city'                  => $accData['city'] ?? 'Makkah',
                        'hotel_name'            => $accData['hotel_name'] ?? null,
                        'hotel_id'              => $accHotelId,
                        'check_in'              => !empty($accData['check_in']) ? date('Y-m-d', strtotime($accData['check_in'])) : null,
                        'check_out'             => !empty($accData['check_out']) ? date('Y-m-d', strtotime($accData['check_out'])) : null,
                        'number_of_nights'      => !empty($accData['number_of_nights']) ? intval($accData['number_of_nights']) : 0,
                        'room_type'             => $accData['room_type'] ?? 'Double',
                        'room_view'             => $accData['room_view'] ?? 'City View',
                        'confirmation_number'   => $accData['confirmation_number'] ?? null,
                        'meal_plan'             => $accData['meal_plan'] ?? 'Room Only',
                        'no_of_rooms'           => !empty($accData['no_of_rooms']) ? intval($accData['no_of_rooms']) : 0,
                        'per_night_rate'        => floatval($accData['per_night_rate'] ?? 0),
                        'add_total_rate'        => false,
                        'currency'              => $curr,
                        'exchange_rate'         => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'           => $costAmt,
                        'cost_amount_pkr'       => $costPkr,
                        'selling_amount'        => $saleAmt,
                        'selling_amount_pkr'    => $salePkr,
                        'supplier_amount'       => floatval($accData['supplier_amount'] ?? 0),
                        'supplier_id'           => !empty($accData['supplier_id']) ? $accData['supplier_id'] : null,
                        'cancellation_deadline' => !empty($accData['cancellation_deadline']) ? date('Y-m-d', strtotime($accData['cancellation_deadline'])) : null,
                        'finalization_date'     => !empty($accData['finalization_date']) ? date('Y-m-d', strtotime($accData['finalization_date'])) : null,
                    ]);

                    // Save Separate Meal Plans for this accommodation
                    if (isset($accData['meals']) && is_array($accData['meals'])) {
                        foreach ($accData['meals'] as $meal) {
                            if (
                                empty($meal['type']) &&
                                empty($meal['cost']) &&
                                empty($meal['sale']) &&
                                empty($meal['provider'])
                            ) {
                                continue;
                            }

                            $mPax = !empty($meal['pax']) ? intval($meal['pax']) : 0;
                            $mDays = !empty($meal['days']) ? intval($meal['days']) : 0;
                            $rawMealEx = $meal['ex_rate'] ?? null;
                            $mEx = ($rawMealEx !== null && $rawMealEx !== '') ? floatval($rawMealEx) : (($meal['currency'] ?? 'PKR') === 'PKR' ? 1 : 0);
                            $mCostRate = floatval($meal['cost'] ?? 0);
                            $mSaleRate = floatval($meal['sale'] ?? 0);

                            $mCostPkr = $mCostRate * $mPax * $mDays * $mEx;
                            $mSalePkr = $mSaleRate * $mPax * $mDays * $mEx;

                            $totalCostPkr += $mCostPkr;
                            $totalSalePkr += $mSalePkr;

                            QuotationMealPlan::create([
                                'quotation_id'               => $quotation->id,
                                'quotation_accommodation_id' => $acc->id,
                                'meal_type'                  => $meal['type'] ?? 'Dinner',
                                'city'                       => $meal['city'] ?? $acc->city,
                                'provider_name'              => $meal['provider'] ?? null,
                                'supplier_id'                => !empty($meal['supplier_id']) ? $meal['supplier_id'] : null,
                                'pax'                        => $mPax,
                                'days'                       => $mDays,
                                'currency'                   => $meal['currency'] ?? 'PKR',
                                'exchange_rate'              => ($rawMealEx !== null && $rawMealEx !== '') ? floatval($rawMealEx) : (($meal['currency'] ?? 'PKR') === 'PKR' ? 1 : null),
                                'cost_rate'                  => $mCostRate,
                                'total_cost_pkr'             => $mCostPkr,
                                'selling_rate'               => $mSaleRate,
                                'total_sale_pkr'             => $mSalePkr,
                                'remarks'                    => $meal['remarks'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // 3. Save Transfers (with Sector, Ex. Rate, Vehicle Quantity)
            if ($request->has('transfers') && is_array($request->transfers)) {
                foreach ($request->transfers as $tr) {
                    if (
                        empty($tr['sector']) &&
                        empty($tr['route']) &&
                        empty($tr['vehicle_type']) &&
                        empty($tr['cost']) &&
                        empty($tr['sale']) &&
                        empty($tr['date_time']) &&
                        empty($tr['confirmation_number'])
                    ) {
                        continue;
                    }

                    $sector = $tr['sector'] ?? ($tr['route'] ?? null);
                    $qty = !empty($tr['quantity']) ? intval($tr['quantity']) : 0;
                    $curr = $tr['currency'] ?? 'PKR';
                    $rawEx = $tr['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);

                    $costAmt = floatval($tr['cost'] ?? 0);
                    $saleAmt = floatval($tr['sale'] ?? 0);
                    $costPkr = $costAmt * ($qty > 0 ? $qty : 1) * $exRate;
                    $salePkr = $saleAmt * ($qty > 0 ? $qty : 1) * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationTransfer::create([
                        'quotation_id'        => $quotation->id,
                        'sector'              => $sector,
                        'vehicle_type'        => $tr['vehicle_type'] ?? 'Sedan Car',
                        'transfer_date'       => $tr['date_time'] ?? null,
                        'quantity'            => $qty,
                        'supplier_id'         => !empty($tr['supplier_id']) ? $tr['supplier_id'] : null,
                        'currency'            => $curr,
                        'exchange_rate'       => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'         => $costAmt,
                        'cost_amount_pkr'     => $costPkr,
                        'selling_amount'      => $saleAmt,
                        'selling_amount_pkr'  => $salePkr,
                        'confirmation_number' => $tr['confirmation_number'] ?? null,
                    ]);
                }
            }

            // 4. Save Tours and Sightseeing
            if ($request->has('tours') && is_array($request->tours)) {
                foreach ($request->tours as $tour) {
                    if (
                        empty($tour['name']) &&
                        empty($tour['sector']) &&
                        empty($tour['cost']) &&
                        empty($tour['sale']) &&
                        empty($tour['date'])
                    ) {
                        continue;
                    }

                    $qty = !empty($tour['quantity']) ? intval($tour['quantity']) : (!empty($tour['pax']) ? intval($tour['pax']) : 0);
                    $curr = $tour['currency'] ?? 'PKR';
                    $rawEx = $tour['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);

                    $costAmt = floatval($tour['cost'] ?? 0);
                    $saleAmt = floatval($tour['sale'] ?? 0);
                    $costPkr = $costAmt * ($qty > 0 ? $qty : 1) * $exRate;
                    $salePkr = $saleAmt * ($qty > 0 ? $qty : 1) * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationTour::create([
                        'quotation_id'       => $quotation->id,
                        'tour_name'          => $tour['name'] ?? 'Holy Places Ziyarat',
                        'city'               => $tour['city'] ?? 'Makkah',
                        'sector'             => $tour['sector'] ?? null,
                        'tour_date'          => $tour['date'] ?? null,
                        'vehicle_type'       => $tour['vehicle_type'] ?? null,
                        'quantity'           => $qty,
                        'guide_included'     => ($tour['guide'] ?? 'Yes') === 'Yes',
                        'supplier_id'        => !empty($tour['supplier_id']) ? $tour['supplier_id'] : null,
                        'currency'           => $curr,
                        'exchange_rate'      => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'        => $costAmt,
                        'cost_amount_pkr'    => $costPkr,
                        'selling_amount'     => $saleAmt,
                        'selling_amount_pkr' => $salePkr,
                    ]);
                }
            }

            // 5. Save Domestic Flight / Train
            if ($request->has('trains') && is_array($request->trains)) {
                foreach ($request->trains as $tr) {
                    if (
                        empty($tr['type']) &&
                        empty($tr['from']) &&
                        empty($tr['to']) &&
                        empty($tr['cost']) &&
                        empty($tr['sale']) &&
                        empty($tr['date_time'])
                    ) {
                        continue;
                    }

                    $pax = !empty($tr['pax']) ? intval($tr['pax']) : 0;
                    $curr = $tr['currency'] ?? 'PKR';
                    $rawEx = $tr['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);

                    $costAmt = floatval($tr['cost'] ?? 0);
                    $saleAmt = floatval($tr['sale'] ?? 0);
                    $costPkr = $costAmt * ($pax > 0 ? $pax : 1) * $exRate;
                    $salePkr = $saleAmt * ($pax > 0 ? $pax : 1) * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationFlightTrain::create([
                        'quotation_id'       => $quotation->id,
                        'service_type'       => $tr['type'] ?? 'Haramain High Speed Train',
                        'from_location'      => $tr['from'] ?? null,
                        'to_location'        => $tr['to'] ?? null,
                        'travel_date'        => $tr['date_time'] ?? null,
                        'class_type'         => $tr['class'] ?? 'Economy',
                        'pax_count'          => $pax,
                        'ticket_number_pnr'  => $tr['ticket_number_pnr'] ?? null,
                        'supplier_id'        => !empty($tr['supplier_id']) ? $tr['supplier_id'] : null,
                        'currency'           => $curr,
                        'exchange_rate'      => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'        => $costAmt,
                        'cost_amount_pkr'    => $costPkr,
                        'selling_amount'     => $saleAmt,
                        'selling_amount_pkr' => $salePkr,
                    ]);
                }
            }

            // 6. Save Visa Charges
            if ($request->has('visas') && is_array($request->visas)) {
                foreach ($request->visas as $visa) {
                    if (
                        empty($visa['passenger_name']) &&
                        empty($visa['passport_number']) &&
                        empty($visa['cost']) &&
                        empty($visa['sale'])
                    ) {
                        continue;
                    }

                    $curr = $visa['currency'] ?? 'PKR';
                    $rawEx = $visa['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);

                    $costAmt = floatval($visa['cost'] ?? 0);
                    $saleAmt = floatval($visa['sale'] ?? 0);
                    $costPkr = $costAmt * $exRate;
                    $salePkr = $saleAmt * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationVisa::create([
                        'quotation_id'       => $quotation->id,
                        'passenger_name'     => $visa['passenger_name'] ?? 'Passenger',
                        'passenger_type'     => $visa['passenger_type'] ?? ($visa['pax_type'] ?? 'Adult'),
                        'gender'             => $visa['gender'] ?? 'Male',
                        'passport_number'    => $visa['passport_number'] ?? null,
                        'visa_type'          => $visa['type'] ?? 'Umrah Tourist E-Visa',
                        'country'            => $visa['country'] ?? 'Saudi Arabia',
                        'supplier_id'        => !empty($visa['supplier_id']) ? $visa['supplier_id'] : null,
                        'currency'           => $curr,
                        'exchange_rate'      => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'        => $costAmt,
                        'cost_amount_pkr'    => $costPkr,
                        'selling_amount'     => $saleAmt,
                        'selling_amount_pkr' => $salePkr,
                        'remarks'            => $visa['remarks'] ?? null,
                    ]);
                }
            }

            // Calculate profit and margin
            $profitPkr = $totalSalePkr - $totalCostPkr;
            $margin = $totalSalePkr > 0 ? round(($profitPkr / $totalSalePkr) * 100, 2) : 0;
            $totalPax = intval($quotation->total_pax ?: 1);
            $perPaxSale = $totalPax > 0 ? round($totalSalePkr / $totalPax, 2) : 0;

            // Update Quotation totals
            $quotation->update([
                'total_cost_pkr'   => $totalCostPkr,
                'total_sale_pkr'   => $totalSalePkr,
                'total_profit_pkr' => $profitPkr,
                'margin_percentage'=> $margin,
                'per_pax_sale_pkr' => $perPaxSale,
            ]);

            DB::commit();

            if ($request->filled('lead_id')) {
                return redirect()
                    ->route('lead.show', $request->lead_id)
                    ->with('success', 'Quotation ' . $quotationNumber . ' created successfully!');
            }

            return redirect()
                ->route('quotation.show', $quotation->id)
                ->with('success', 'Quotation ' . $quotationNumber . ' created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create quotation: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $quotation = Quotation::with([
            'lead',
            'client',
            'company',
            'package',
            'accommodations.mealPlans',
            'mealPlans',
            'transfers',
            'tours',
            'flightsTrains',
            'visas'
        ])->findOrFail($id);

        return view('quotation.show', compact('quotation'));
    }

    public function edit($id)
    {
        $quotation = Quotation::with([
            'lead',
            'accommodations.mealPlans',
            'mealPlans',
            'transfers',
            'tours',
            'flightsTrains',
            'visas'
        ])->findOrFail($id);

        $leads = Lead::all();
        $hotels = Hotel::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $companies = Company::all();
        $travelRoutes = TravelRoute::all();
        $routes = RouteModel::where('status', 'active')->orWhereNull('status')->get();
        $vehicles = Vehicle::where('status', 'active')->orWhereNull('status')->get();
        $packages = Package::all();
        $clients = Client::all();

        return view('quotation.edit', compact(
            'quotation',
            'leads',
            'hotels',
            'companies',
            'travelRoutes',
            'routes',
            'vehicles',
            'packages',
            'clients'
        ));
    }

    public function update(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);

        // 1. Validate Room Inventory Stock Limits (Excluding current quotation)
        if ($request->has('accommodations') && is_array($request->accommodations)) {
            foreach ($request->accommodations as $accData) {
                $noOfRooms = !empty($accData['no_of_rooms']) ? intval($accData['no_of_rooms']) : 0;
                if ($noOfRooms <= 0) {
                    continue;
                }

                $hotelId = !empty($accData['hotel_id']) ? $accData['hotel_id'] : null;
                $hotelName = $accData['hotel_name'] ?? null;
                if (!$hotelId && $hotelName) {
                    $h = Hotel::where('name', $hotelName)->first();
                    if ($h) {
                        $hotelId = $h->id;
                    }
                }

                if ($hotelId) {
                    $roomType = $accData['room_type'] ?? 'Double';
                    $checkIn = !empty($accData['check_in']) ? date('Y-m-d', strtotime($accData['check_in'])) : null;
                    $checkOut = !empty($accData['check_out']) ? date('Y-m-d', strtotime($accData['check_out'])) : null;

                    $avail = RoomInventory::getAvailability($hotelId, $roomType, $checkIn, $checkOut, $quotation->id);
                    if ($avail['has_inventory'] && $avail['available'] < $noOfRooms) {
                        $hName = $hotelName ?: ('Hotel #' . $hotelId);
                        return back()->withInput()->withErrors([
                            'error' => "Booking Blocked: Only {$avail['available']} {$roomType} room(s) available in stock for '{$hName}'! (You requested {$noOfRooms} rooms, Total Stock: {$avail['total_stock']}, Already Booked: {$avail['booked']})."
                        ]);
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            $quotation->update([
                'quotation_title'     => $request->quotation_title ?: $quotation->quotation_title,
                'lead_id'             => $request->filled('lead_id') ? $request->lead_id : $quotation->lead_id,
                'client_name'         => $request->client_name ?: ($quotation->client_name ?: 'Valued Client'),
                'client_phone'        => $request->client_phone ?: null,
                'client_email'        => $request->client_email ?: null,
                'total_pax'           => $request->filled('total_pax') ? intval($request->total_pax) : ($quotation->total_pax ?: 1),
                'status'              => $request->status ?? $quotation->status,
                'inclusions'          => $request->inclusions ?: null,
                'exclusions'          => $request->exclusions ?: null,
                'terms_and_conditions'=> $request->terms ?: ($request->terms_and_conditions ?: null),
                'remarks'             => $request->remarks ?: null,
            ]);

            $totalCostPkr = 0;
            $totalSalePkr = 0;

            // Re-sync Accommodations
            if ($request->has('accommodations') && is_array($request->accommodations)) {
                $quotation->accommodations()->delete();
                $quotation->mealPlans()->whereNotNull('quotation_accommodation_id')->delete();

                foreach ($request->accommodations as $accData) {
                    if (
                        empty($accData['hotel_name']) &&
                        empty($accData['hotel_id']) &&
                        empty($accData['cost_amount']) &&
                        empty($accData['selling_amount']) &&
                        empty($accData['check_in']) &&
                        empty($accData['check_out']) &&
                        empty($accData['confirmation_number'])
                    ) {
                        continue;
                    }

                    $curr = $accData['currency'] ?? 'PKR';
                    $rawEx = $accData['exchange_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);

                    $costAmt = floatval($accData['cost_amount'] ?? 0);
                    $saleAmt = floatval($accData['selling_amount'] ?? 0);
                    $costPkr = $costAmt * $exRate;
                    $salePkr = $saleAmt * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    $accHotelId = !empty($accData['hotel_id']) ? $accData['hotel_id'] : null;
                    if (!$accHotelId && !empty($accData['hotel_name'])) {
                        $h = Hotel::where('name', $accData['hotel_name'])->first();
                        if ($h) {
                            $accHotelId = $h->id;
                        }
                    }

                    $acc = QuotationAccommodation::create([
                        'quotation_id'          => $quotation->id,
                        'city'                  => $accData['city'] ?? 'Makkah',
                        'hotel_name'            => $accData['hotel_name'] ?? null,
                        'hotel_id'              => $accHotelId,
                        'check_in'              => !empty($accData['check_in']) ? date('Y-m-d', strtotime($accData['check_in'])) : null,
                        'check_out'             => !empty($accData['check_out']) ? date('Y-m-d', strtotime($accData['check_out'])) : null,
                        'number_of_nights'      => !empty($accData['number_of_nights']) ? intval($accData['number_of_nights']) : 0,
                        'room_type'             => $accData['room_type'] ?? 'Double',
                        'room_view'             => $accData['room_view'] ?? 'City View',
                        'confirmation_number'   => $accData['confirmation_number'] ?? null,
                        'meal_plan'             => $accData['meal_plan'] ?? 'Room Only',
                        'no_of_rooms'           => !empty($accData['no_of_rooms']) ? intval($accData['no_of_rooms']) : 0,
                        'per_night_rate'        => floatval($accData['per_night_rate'] ?? 0),
                        'add_total_rate'        => false,
                        'currency'              => $curr,
                        'exchange_rate'         => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'           => $costAmt,
                        'cost_amount_pkr'       => $costPkr,
                        'selling_amount'        => $saleAmt,
                        'selling_amount_pkr'    => $salePkr,
                        'supplier_amount'       => floatval($accData['supplier_amount'] ?? 0),
                        'supplier_id'           => !empty($accData['supplier_id']) ? $accData['supplier_id'] : null,
                        'cancellation_deadline' => !empty($accData['cancellation_deadline']) ? date('Y-m-d', strtotime($accData['cancellation_deadline'])) : null,
                        'finalization_date'     => !empty($accData['finalization_date']) ? date('Y-m-d', strtotime($accData['finalization_date'])) : null,
                    ]);

                    // Separate Meal Plans
                    if (isset($accData['meals']) && is_array($accData['meals'])) {
                        foreach ($accData['meals'] as $meal) {
                            if (empty($meal['type']) && empty($meal['cost']) && empty($meal['sale']) && empty($meal['provider'])) {
                                continue;
                            }
                            $mPax = !empty($meal['pax']) ? intval($meal['pax']) : 0;
                            $mDays = !empty($meal['days']) ? intval($meal['days']) : 0;
                            $rawMealEx = $meal['ex_rate'] ?? null;
                            $mEx = ($rawMealEx !== null && $rawMealEx !== '') ? floatval($rawMealEx) : (($meal['currency'] ?? 'PKR') === 'PKR' ? 1 : 0);
                            $mCostRate = floatval($meal['cost'] ?? 0);
                            $mSaleRate = floatval($meal['sale'] ?? 0);

                            $mCostPkr = $mCostRate * $mPax * $mDays * $mEx;
                            $mSalePkr = $mSaleRate * $mPax * $mDays * $mEx;

                            $totalCostPkr += $mCostPkr;
                            $totalSalePkr += $mSalePkr;

                            QuotationMealPlan::create([
                                'quotation_id'               => $quotation->id,
                                'quotation_accommodation_id' => $acc->id,
                                'meal_type'                  => $meal['type'] ?? 'Dinner',
                                'city'                       => $meal['city'] ?? $acc->city,
                                'provider_name'              => $meal['provider'] ?? null,
                                'supplier_id'                => !empty($meal['supplier_id']) ? $meal['supplier_id'] : null,
                                'pax'                        => $mPax,
                                'days'                       => $mDays,
                                'currency'                   => $meal['currency'] ?? 'PKR',
                                'exchange_rate'              => ($rawMealEx !== null && $rawMealEx !== '') ? floatval($rawMealEx) : (($meal['currency'] ?? 'PKR') === 'PKR' ? 1 : null),
                                'cost_rate'                  => $mCostRate,
                                'total_cost_pkr'             => $mCostPkr,
                                'selling_rate'               => $mSaleRate,
                                'total_sale_pkr'             => $mSalePkr,
                                'remarks'                    => $meal['remarks'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // Re-sync Transfers
            if ($request->has('transfers') && is_array($request->transfers)) {
                $quotation->transfers()->delete();
                foreach ($request->transfers as $tr) {
                    if (empty($tr['sector']) && empty($tr['route']) && empty($tr['vehicle_type']) && empty($tr['cost']) && empty($tr['sale']) && empty($tr['date_time'])) {
                        continue;
                    }
                    $qty = !empty($tr['quantity']) ? intval($tr['quantity']) : 0;
                    $curr = $tr['currency'] ?? 'PKR';
                    $rawEx = $tr['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);
                    $costAmt = floatval($tr['cost'] ?? 0);
                    $saleAmt = floatval($tr['sale'] ?? 0);
                    $costPkr = $costAmt * ($qty > 0 ? $qty : 1) * $exRate;
                    $salePkr = $saleAmt * ($qty > 0 ? $qty : 1) * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationTransfer::create([
                        'quotation_id'         => $quotation->id,
                        'sector'               => $tr['sector'] ?? ($tr['route'] ?? null),
                        'vehicle_type'         => $tr['vehicle_type'] ?? 'Sedan Car',
                        'transfer_date'        => $tr['date_time'] ?? null,
                        'quantity'             => $qty,
                        'supplier_id'          => !empty($tr['supplier_id']) ? $tr['supplier_id'] : null,
                        'currency'             => $curr,
                        'exchange_rate'        => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'          => $costAmt,
                        'cost_amount_pkr'      => $costPkr,
                        'selling_amount'       => $saleAmt,
                        'selling_amount_pkr'   => $salePkr,
                        'confirmation_number'  => $tr['confirmation_number'] ?? null,
                    ]);
                }
            }

            // Re-sync Tours
            if ($request->has('tours') && is_array($request->tours)) {
                $quotation->tours()->delete();
                foreach ($request->tours as $tour) {
                    if (empty($tour['name']) && empty($tour['sector']) && empty($tour['cost']) && empty($tour['sale']) && empty($tour['date'])) {
                        continue;
                    }
                    $qty = !empty($tour['quantity']) ? intval($tour['quantity']) : 0;
                    $curr = $tour['currency'] ?? 'PKR';
                    $rawEx = $tour['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);
                    $costAmt = floatval($tour['cost'] ?? 0);
                    $saleAmt = floatval($tour['sale'] ?? 0);
                    $costPkr = $costAmt * ($qty > 0 ? $qty : 1) * $exRate;
                    $salePkr = $saleAmt * ($qty > 0 ? $qty : 1) * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationTour::create([
                        'quotation_id'       => $quotation->id,
                        'tour_name'          => $tour['name'] ?? null,
                        'city'               => $tour['city'] ?? 'Makkah',
                        'sector'             => $tour['sector'] ?? null,
                        'tour_date'          => $tour['date'] ?? null,
                        'vehicle_type'       => $tour['vehicle_type'] ?? null,
                        'quantity'           => $qty,
                        'guide_included'     => ($tour['guide'] ?? 'Yes') === 'Yes',
                        'supplier_id'        => !empty($tour['supplier_id']) ? $tour['supplier_id'] : null,
                        'currency'           => $curr,
                        'exchange_rate'      => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'        => $costAmt,
                        'cost_amount_pkr'    => $costPkr,
                        'selling_amount'     => $saleAmt,
                        'selling_amount_pkr' => $salePkr,
                    ]);
                }
            }

            // Re-sync Flights / Trains
            if ($request->has('trains') && is_array($request->trains)) {
                $quotation->flightsTrains()->delete();
                foreach ($request->trains as $tr) {
                    if (empty($tr['type']) && empty($tr['from']) && empty($tr['to']) && empty($tr['cost']) && empty($tr['sale']) && empty($tr['date_time'])) {
                        continue;
                    }
                    $pax = !empty($tr['pax']) ? intval($tr['pax']) : 0;
                    $curr = $tr['currency'] ?? 'PKR';
                    $rawEx = $tr['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);
                    $costAmt = floatval($tr['cost'] ?? 0);
                    $saleAmt = floatval($tr['sale'] ?? 0);
                    $costPkr = $costAmt * ($pax > 0 ? $pax : 1) * $exRate;
                    $salePkr = $saleAmt * ($pax > 0 ? $pax : 1) * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationFlightTrain::create([
                        'quotation_id'       => $quotation->id,
                        'service_type'       => $tr['type'] ?? 'Haramain High Speed Train',
                        'from_location'      => $tr['from'] ?? null,
                        'to_location'        => $tr['to'] ?? null,
                        'travel_date'        => $tr['date_time'] ?? null,
                        'class_type'         => $tr['class'] ?? 'Economy',
                        'pax_count'          => $pax,
                        'ticket_number_pnr'  => $tr['ticket_number_pnr'] ?? null,
                        'supplier_id'        => !empty($tr['supplier_id']) ? $tr['supplier_id'] : null,
                        'currency'           => $curr,
                        'exchange_rate'      => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'        => $costAmt,
                        'cost_amount_pkr'    => $costPkr,
                        'selling_amount'     => $saleAmt,
                        'selling_amount_pkr' => $salePkr,
                    ]);
                }
            }

            // Re-sync Visas
            if ($request->has('visas') && is_array($request->visas)) {
                $quotation->visas()->delete();
                foreach ($request->visas as $visa) {
                    if (empty($visa['passenger_name']) && empty($visa['passport_number']) && empty($visa['cost']) && empty($visa['sale'])) {
                        continue;
                    }
                    $curr = $visa['currency'] ?? 'PKR';
                    $rawEx = $visa['ex_rate'] ?? null;
                    $exRate = ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : 0);
                    $costAmt = floatval($visa['cost'] ?? 0);
                    $saleAmt = floatval($visa['sale'] ?? 0);
                    $costPkr = $costAmt * $exRate;
                    $salePkr = $saleAmt * $exRate;

                    $totalCostPkr += $costPkr;
                    $totalSalePkr += $salePkr;

                    QuotationVisa::create([
                        'quotation_id'       => $quotation->id,
                        'passenger_name'     => $visa['passenger_name'] ?? null,
                        'passenger_type'     => $visa['passenger_type'] ?? 'Adult',
                        'gender'             => $visa['gender'] ?? 'Male',
                        'passport_number'    => $visa['passport_number'] ?? null,
                        'visa_type'          => $visa['type'] ?? 'Umrah Tourist E-Visa',
                        'country'            => 'Saudi Arabia',
                        'supplier_id'        => !empty($visa['supplier_id']) ? $visa['supplier_id'] : null,
                        'currency'           => $curr,
                        'exchange_rate'      => ($rawEx !== null && $rawEx !== '') ? floatval($rawEx) : ($curr === 'PKR' ? 1 : null),
                        'cost_amount'        => $costAmt,
                        'cost_amount_pkr'    => $costPkr,
                        'selling_amount'     => $saleAmt,
                        'selling_amount_pkr' => $salePkr,
                        'remarks'            => $visa['remarks'] ?? null,
                    ]);
                }
            }

            // Calculate profit, margin, and per pax sale
            if ($totalSalePkr > 0 || $totalCostPkr > 0) {
                $profitPkr = $totalSalePkr - $totalCostPkr;
                $margin = $totalSalePkr > 0 ? round(($profitPkr / $totalSalePkr) * 100, 2) : 0;
                $totalPax = intval($quotation->total_pax ?: 1);
                $perPaxSale = $totalPax > 0 ? round($totalSalePkr / $totalPax, 2) : 0;

                $quotation->update([
                    'total_cost_pkr'   => $totalCostPkr,
                    'total_sale_pkr'   => $totalSalePkr,
                    'total_profit_pkr' => $profitPkr,
                    'margin_percentage'=> $margin,
                    'per_pax_sale_pkr' => $perPaxSale,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('quotation.show', $quotation->id)
                ->with('success', 'Quotation updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update quotation: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->delete();

        return redirect()
            ->route('quotation.index')
            ->with('success', 'Quotation deleted successfully!');
    }

    public function pdf(Request $request, $id)
    {
        $quotation = Quotation::with([
            'lead',
            'client',
            'package',
            'accommodations.mealPlans',
            'mealPlans',
            'transfers',
            'tours',
            'flightsTrains',
            'visas'
        ])->findOrFail($id);

        $company = Company::with(['addresses', 'contactNumbers', 'emails', 'licenses'])->first();

        // Print/Direct View HTML in browser
        if ($request->has('print')) {
            return view('quotation.pdf', compact('quotation', 'company'));
        }

        // Generate VIP PDF
        $pdf = Pdf::loadView('quotation.pdf', compact('quotation', 'company'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'sans-serif',
            ]);

        $fileName = 'Quotation-' . ($quotation->quotation_number ?: $quotation->id) . '.pdf';

        if ($request->has('stream') || $request->has('view')) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    public function trash()
    {
        $quotations = Quotation::onlyTrashed()->get();
        return view('quotation.trash', compact('quotations'));
    }

    public function restore($id)
    {
        $quotation = Quotation::withTrashed()->findOrFail($id);
        $quotation->restore();

        return redirect()
            ->route('quotation.index')
            ->with('success', 'Quotation restored successfully!');
    }
}
