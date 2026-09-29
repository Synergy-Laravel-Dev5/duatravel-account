<?php

namespace App\Http\Controllers;

use App\Models\RoomInventory;
use App\Models\Hotel;
use App\Models\Company;
use App\Models\QuotationAccommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomInventoryController extends Controller
{
    public function index(Request $request)
    {
        $hotelId = $request->get('hotel_id');
        $city = $request->get('city');
        $roomType = $request->get('room_type');
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');

        $hotels = Hotel::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $companies = Company::orderBy('company_name')->get();

        // 1. Calculate Room Type Summary Breakdown
        $summary = RoomInventory::getHotelSummary($hotelId, $checkIn, $checkOut);

        // Overall Totals
        $totalStock = array_sum(array_column($summary, 'total_stock'));
        $totalBooked = array_sum(array_column($summary, 'booked'));
        $totalAvailable = array_sum(array_column($summary, 'available'));
        $overallOccupancy = $totalStock > 0 ? min(100, round(($totalBooked / $totalStock) * 100)) : 0;

        // 2. Fetch Rooming List (Bookings made in quotations)
        $roomingListQuery = QuotationAccommodation::with([
            'quotation.lead',
            'quotation.client',
            'quotation.company',
            'hotel'
        ])
        ->whereHas('quotation', function ($q) {
            $q->whereNull('deleted_at')->whereNotIn('status', ['cancelled', 'rejected']);
        });

        if ($hotelId) {
            $hotel = Hotel::find($hotelId);
            $roomingListQuery->where(function ($q) use ($hotelId, $hotel) {
                $q->where('hotel_id', $hotelId);
                if ($hotel) {
                    $q->orWhere('hotel_name', $hotel->name);
                }
            });
        }

        if ($city) {
            $roomingListQuery->where('city', $city);
        }

        if ($roomType) {
            $roomingListQuery->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower($roomType)]);
        }

        if ($checkIn && $checkOut) {
            $cIn = date('Y-m-d', strtotime($checkIn));
            $cOut = date('Y-m-d', strtotime($checkOut));
            $roomingListQuery->where(function ($q) use ($cIn, $cOut) {
                $q->where('check_in', '<=', $cOut)->where('check_out', '>=', $cIn);
            });
        }

        $roomingList = $roomingListQuery->latest()->get();

        // 3. Fetch Configured Inventory Stock Records
        $inventoryQuery = RoomInventory::with(['hotel', 'supplier'])->latest();

        if ($hotelId) {
            $inventoryQuery->where('hotel_id', $hotelId);
        }

        if ($roomType) {
            $inventoryQuery->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower($roomType)]);
        }

        if ($city) {
            $inventoryQuery->whereHas('hotel', function ($q) use ($city) {
                $q->where('place', $city);
            });
        }

        $inventories = $inventoryQuery->get();

        return view('room_inventory.index', compact(
            'hotels',
            'companies',
            'summary',
            'totalStock',
            'totalBooked',
            'totalAvailable',
            'overallOccupancy',
            'roomingList',
            'inventories',
            'hotelId',
            'city',
            'roomType',
            'checkIn',
            'checkOut'
        ));
    }

    public function create()
    {
        $hotels = Hotel::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $companies = Company::orderBy('company_name')->get();
        $roomTypes = ['Double', 'Triple', 'Quad', 'Quint', 'Single', 'Sharing', 'Suite'];

        return view('room_inventory.create', compact('hotels', 'companies', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
        ]);

        DB::beginTransaction();
        try {
            $hotelId = $request->hotel_id;
            $batchName = $request->batch_name ?: 'Stock Allotment ' . date('d-M-Y');
            $checkIn = !empty($request->check_in) ? date('Y-m-d', strtotime($request->check_in)) : null;
            $checkOut = !empty($request->check_out) ? date('Y-m-d', strtotime($request->check_out)) : null;
            $supplierId = $request->supplier_id ?: null;
            $currency = $request->currency ?: 'SAR';
            $notes = $request->notes ?: null;

            // Check if submitted via bulk multi-room entry (Double, Triple, Quad, etc.)
            $roomCounts = $request->input('rooms', []);

            // Also support individual fields: double_rooms, triple_rooms, quad_rooms, etc.
            if (empty($roomCounts)) {
                $types = ['Double', 'Triple', 'Quad', 'Quint', 'Single', 'Sharing', 'Suite'];
                foreach ($types as $t) {
                    $field = strtolower($t) . '_rooms';
                    if ($request->filled($field) && intval($request->$field) > 0) {
                        $roomCounts[$t] = intval($request->$field);
                    }
                }
            }

            // If single room inventory form submitted
            if (empty($roomCounts) && $request->filled('room_type') && $request->filled('total_rooms')) {
                $roomCounts[$request->room_type] = intval($request->total_rooms);
            }

            if (empty($roomCounts)) {
                return back()->withInput()->withErrors(['error' => 'Please enter at least one room quantity (e.g. 10 Double, 5 Triple, etc.).']);
            }

            $createdCount = 0;
            foreach ($roomCounts as $roomType => $quantity) {
                $qty = intval($quantity);
                if ($qty <= 0) {
                    continue;
                }

                $costRate = floatval($request->input("cost_rate_{$roomType}", $request->cost_rate ?? 0));
                $sellingRate = floatval($request->input("selling_rate_{$roomType}", $request->selling_rate ?? 0));
                $roomView = $request->input("room_view_{$roomType}", $request->room_view ?? 'City View');
                $mealPlan = $request->input("meal_plan_{$roomType}", $request->meal_plan ?? 'Room Only');

                RoomInventory::create([
                    'hotel_id'     => $hotelId,
                    'batch_name'   => $batchName,
                    'room_type'    => $roomType,
                    'room_view'    => $roomView,
                    'meal_plan'    => $mealPlan,
                    'check_in'     => $checkIn,
                    'check_out'    => $checkOut,
                    'total_rooms'  => $qty,
                    'cost_rate'    => $costRate,
                    'selling_rate' => $sellingRate,
                    'currency'     => $currency,
                    'supplier_id'  => $supplierId,
                    'status'       => 'active',
                    'notes'        => $notes,
                ]);

                $createdCount++;
            }

            DB::commit();

            return redirect()
                ->route('room-inventory.index')
                ->with('success', "Room inventory added successfully! ({$createdCount} room types provisioned)");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to save room inventory: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $inventory = RoomInventory::findOrFail($id);
        $hotels = Hotel::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $companies = Company::orderBy('company_name')->get();
        $roomTypes = ['Double', 'Triple', 'Quad', 'Quint', 'Single', 'Sharing', 'Suite'];

        return view('room_inventory.edit', compact('inventory', 'hotels', 'companies', 'roomTypes'));
    }

    public function update(Request $request, $id)
    {
        $inventory = RoomInventory::findOrFail($id);

        $request->validate([
            'hotel_id'    => 'required|exists:hotels,id',
            'room_type'   => 'required|string',
            'total_rooms' => 'required|integer|min:0',
        ]);

        $inventory->update([
            'hotel_id'     => $request->hotel_id,
            'batch_name'   => $request->batch_name ?: $inventory->batch_name,
            'room_type'    => $request->room_type,
            'room_view'    => $request->room_view ?: 'City View',
            'meal_plan'    => $request->meal_plan ?: 'Room Only',
            'check_in'     => !empty($request->check_in) ? date('Y-m-d', strtotime($request->check_in)) : null,
            'check_out'    => !empty($request->check_out) ? date('Y-m-d', strtotime($request->check_out)) : null,
            'total_rooms'  => intval($request->total_rooms),
            'cost_rate'    => floatval($request->cost_rate ?? 0),
            'selling_rate' => floatval($request->selling_rate ?? 0),
            'currency'     => $request->currency ?: 'SAR',
            'supplier_id'  => $request->supplier_id ?: null,
            'status'       => $request->status ?: 'active',
            'notes'        => $request->notes ?: null,
        ]);

        return redirect()
            ->route('room-inventory.index')
            ->with('success', 'Room inventory allotment updated successfully!');
    }

    public function destroy($id)
    {
        $inventory = RoomInventory::findOrFail($id);
        $inventory->delete();

        return redirect()
            ->route('room-inventory.index')
            ->with('success', 'Room inventory record deleted successfully.');
    }

    /**
     * AJAX endpoint for live availability check during Quotation Create / Edit
     */
    public function checkAvailability(Request $request)
    {
        $hotelId = $request->hotel_id;
        $hotelName = $request->hotel_name;
        $roomType = $request->room_type ?: 'Double';
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $excludeQuotationId = $request->quotation_id;
        $requestedRooms = intval($request->requested_rooms ?? 1);

        // Resolve hotel_id if only hotel_name was sent
        if (!$hotelId && $hotelName) {
            $hotel = Hotel::where('name', $hotelName)->first();
            if ($hotel) {
                $hotelId = $hotel->id;
            }
        }

        $result = RoomInventory::getAvailability($hotelId, $roomType, $checkIn, $checkOut, $excludeQuotationId);

        $result['can_fulfill'] = !$result['has_inventory'] || ($result['available'] >= $requestedRooms);
        $result['requested'] = $requestedRooms;

        if ($result['has_inventory'] && !$result['can_fulfill']) {
            $result['warning'] = "Only {$result['available']} {$roomType} room(s) available in stock! You requested {$requestedRooms}.";
        }

        return response()->json($result);
    }

    /**
     * AJAX endpoint to get hotel room stock summary
     */
    public function hotelSummary(Request $request, $hotelId)
    {
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;

        $summary = RoomInventory::getHotelSummary($hotelId, $checkIn, $checkOut);
        $hotel = Hotel::find($hotelId);

        return response()->json([
            'hotel'   => $hotel,
            'summary' => $summary,
        ]);
    }
}