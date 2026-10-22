<?php

namespace App\Http\Controllers;

use App\Models\HajiGroup;
use App\Models\HajiGroupPerson;
use App\Models\Booking;
use App\Models\BookingPerson;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\Vehicle;
use App\Models\Hotel;
use App\Models\Route as BusRoute;
use App\Models\TravelRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HajiGroupController extends Controller
{
    public function index(Request $request)
    {
        $groupType = $request->get('type', 'all');

        $query = HajiGroup::with(['company', 'airline', 'flight', 'vehicle', 'hotel', 'route', 'travelRoute', 'persons']);

        if (isCompanyUser()) {
            $query->where('company_id', getAuthCompanyId());
        }

        if ($groupType !== 'all') {
            $query->where('group_type', $groupType);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('group_name', 'like', "%{$search}%")
                  ->orWhere('leader_name', 'like', "%{$search}%")
                  ->orWhere('pnr', 'like', "%{$search}%")
                  ->orWhere('bus_name', 'like', "%{$search}%");
            });
        }

        $groups = $query->latest()->paginate(15);

        return view('haji_group.index', compact('groups', 'groupType'));
    }

    public function create(Request $request)
    {
        $groupType = $request->get('type', 'flight');

        $airlines = Airline::where('status', 'active')->orderBy('name')->get();
        $flights  = Flight::with('airline')->where('status', 'active')->latest()->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('brand_name')->get();
        $hotels   = Hotel::where('status', 'active')->orderBy('name')->get();
        $routes   = BusRoute::orderBy('start_place')->get();
        $travelRoutes = TravelRoute::orderBy('name')->get();

        // Haji Group strictly includes Hajj bookings only (Umrah excluded)
        $bookingsQuery = Booking::with(['persons', 'company', 'client'])
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) {
                $q->whereNull('package_type')
                  ->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
            })
            ->latest();
        if (isCompanyUser()) {
            $bookingsQuery->where('company_id', getAuthCompanyId());
        }
        $bookings = $bookingsQuery->get();

        return view('haji_group.create', compact('airlines', 'flights', 'vehicles', 'hotels', 'routes', 'travelRoutes', 'bookings', 'groupType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_name'   => 'required|string|max:200',
            'person_ids'   => 'nullable|array',
            'person_ids.*' => 'exists:booking_persons,id',
        ]);

        $groupType = $request->group_type ?: 'general';
        $busCapacity = intval($request->bus_capacity ?: 45);
        $personIds = $request->input('person_ids', []);
        $personCount = is_array($personIds) ? count($personIds) : 0;

        // Strict Bus Capacity Limit Validation
        if (($groupType === 'bus' || $request->filled('vehicle_id')) && $busCapacity > 0 && $personCount > $busCapacity) {
            return back()->withInput()->withErrors([
                'bus_capacity' => "Bus Capacity Exceeded! You selected {$personCount} pilgrims, but bus capacity limit is {$busCapacity} seats. Please select max {$busCapacity} pilgrims or increase bus capacity limit."
            ]);
        }

        $group = DB::transaction(function () use ($request, $groupType, $busCapacity) {
            $createdGroup = HajiGroup::create([
                'group_name'      => $request->group_name,
                'group_type'      => $groupType,
                'company_id'      => isCompanyUser() ? getAuthCompanyId() : ($request->company_id ?: null),
                'leader_name'     => $request->leader_name ?: null,
                'leader_phone'    => $request->leader_phone ?: null,
                'airline_id'      => $request->airline_id ?: null,
                'flight_id'       => $request->flight_id ?: null,
                'flight_number'   => $request->flight_number ?: null,
                'pnr'             => $request->pnr ?: null,
                'flight_date'     => $request->flight_date ?: null,
                'vehicle_id'      => $request->vehicle_id ?: null,
                'bus_name'        => $request->bus_name ?: null,
                'bus_capacity'    => $busCapacity,
                'hotel_id'        => $request->hotel_id ?: null,
                'room_type'       => $request->room_type ?: null,
                'route_id'        => $request->route_id ?: null,
                'travel_route_id' => $request->travel_route_id ?: null,
                'status'          => $request->status ?: 'active',
                'notes'           => $request->notes ?: null,
            ]);

            if ($request->has('person_ids') && is_array($request->person_ids)) {
                $persons = BookingPerson::whereIn('id', $request->person_ids)->get();
                foreach ($persons as $p) {
                    HajiGroupPerson::create([
                        'haji_group_id'     => $createdGroup->id,
                        'booking_id'        => $p->booking_id,
                        'booking_person_id' => $p->id,
                    ]);
                }
            }

            return $createdGroup;
        });

        logUserActivity('Haji Group Created', 'Haji Group: ' . $group->group_name, $group->id, 'HajiGroup');

        return redirect()->route('haji-group.show', $group->id)->with('success', 'Haji Group created successfully!');
    }

    public function show($id)
    {
        $group = HajiGroup::with([
            'company',
            'airline',
            'flight',
            'vehicle',
            'hotel',
            'route',
            'travelRoute',
            'groupPersons.booking.company',
            'groupPersons.booking.client',
            'groupPersons.person'
        ])->findOrFail($id);

        $allGroups = HajiGroup::where('id', '!=', $group->id)->orderBy('group_name')->get();

        return view('haji_group.show', compact('group', 'allGroups'));
    }

    public function edit($id)
    {
        $group = HajiGroup::with('groupPersons')->findOrFail($id);

        $airlines = Airline::where('status', 'active')->orderBy('name')->get();
        $flights  = Flight::with('airline')->where('status', 'active')->latest()->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('brand_name')->get();
        $hotels   = Hotel::where('status', 'active')->orderBy('name')->get();
        $routes   = BusRoute::orderBy('start_place')->get();
        $travelRoutes = TravelRoute::orderBy('name')->get();

        // Haji Group strictly includes Hajj bookings only (Umrah excluded)
        $bookingsQuery = Booking::with(['persons', 'company', 'client'])
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) {
                $q->whereNull('package_type')
                  ->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
            })
            ->latest();
        if (isCompanyUser()) {
            $bookingsQuery->where('company_id', getAuthCompanyId());
        }
        $bookings = $bookingsQuery->get();

        $selectedPersonIds = $group->groupPersons->pluck('booking_person_id')->toArray();

        return view('haji_group.edit', compact(
            'group', 'airlines', 'flights', 'vehicles', 'hotels', 'routes', 'travelRoutes', 'bookings', 'selectedPersonIds'
        ));
    }

    public function update(Request $request, $id)
    {
        $group = HajiGroup::findOrFail($id);

        $request->validate([
            'group_name' => 'required|string|max:200',
        ]);

        $groupType = $group->group_type ?: ($request->group_type ?: 'general');
        $busCapacity = intval($request->bus_capacity ?: 45);
        $personIds = $request->input('person_ids', []);
        $personCount = is_array($personIds) ? count($personIds) : 0;

        // Strict Bus Capacity Limit Validation
        if (($groupType === 'bus' || $request->filled('vehicle_id')) && $busCapacity > 0 && $personCount > $busCapacity) {
            return back()->withInput()->withErrors([
                'bus_capacity' => "Bus Capacity Exceeded! You selected {$personCount} pilgrims, but bus capacity limit is {$busCapacity} seats. Please select max {$busCapacity} pilgrims or increase bus capacity limit."
            ]);
        }

        DB::transaction(function () use ($request, $group, $busCapacity) {
            $group->update([
                'group_name'      => $request->group_name,
                'leader_name'     => $request->leader_name ?: null,
                'leader_phone'    => $request->leader_phone ?: null,
                'airline_id'      => $request->airline_id ?: null,
                'flight_id'       => $request->flight_id ?: null,
                'flight_number'   => $request->flight_number ?: null,
                'pnr'             => $request->pnr ?: null,
                'flight_date'     => $request->flight_date ?: null,
                'vehicle_id'      => $request->vehicle_id ?: null,
                'bus_name'        => $request->bus_name ?: null,
                'bus_capacity'    => $busCapacity,
                'hotel_id'        => $request->hotel_id ?: null,
                'room_type'       => $request->room_type ?: null,
                'route_id'        => $request->route_id ?: null,
                'travel_route_id' => $request->travel_route_id ?: null,
                'status'          => $request->status ?: 'active',
                'notes'           => $request->notes ?: null,
            ]);

            if ($request->has('person_ids') && is_array($request->person_ids)) {
                HajiGroupPerson::where('haji_group_id', $group->id)->delete();
                $persons = BookingPerson::whereIn('id', $request->person_ids)->get();
                foreach ($persons as $p) {
                    HajiGroupPerson::create([
                        'haji_group_id'     => $group->id,
                        'booking_id'        => $p->booking_id,
                        'booking_person_id' => $p->id,
                    ]);
                }
            } else {
                HajiGroupPerson::where('haji_group_id', $group->id)->delete();
            }
        });

        logUserActivity('Haji Group Updated', 'Haji Group: ' . $group->group_name, $group->id, 'HajiGroup');

        return redirect()->route('haji-group.show', $group->id)->with('success', 'Haji Group updated successfully.');
    }

    public function destroy($id)
    {
        $group = HajiGroup::findOrFail($id);
        $group->delete();

        logUserActivity('Haji Group Deleted', 'Haji Group: ' . $group->group_name, $group->id, 'HajiGroup');

        return redirect()->route('haji-group.index')->with('success', 'Haji Group deleted successfully.');
    }

    /**
     * Smart Bus Split Engine with Family Protection Rule
     */
    public function smartSplitBus(Request $request, $id)
    {
        $group = HajiGroup::with(['groupPersons.person', 'groupPersons.booking'])->findOrFail($id);
        $busCapacity = intval($request->input('bus_capacity', $group->bus_capacity ?: 45));

        $totalPersons = $group->groupPersons->count();

        if ($totalPersons <= $busCapacity) {
            return back()->with('info', "Group size ({$totalPersons}) fits within bus capacity ({$busCapacity}). No split required!");
        }

        $overflowCount = $totalPersons - $busCapacity;

        // Group pilgrims by booking_id to preserve family units
        $bookingMap = [];
        foreach ($group->groupPersons as $gp) {
            $bId = $gp->booking_id;
            if (!isset($bookingMap[$bId])) {
                $bookingMap[$bId] = [];
            }
            $bookingMap[$bId][] = $gp;
        }

        // Separate single travelers vs family units
        $singleTravelers = [];
        $familyUnits = [];

        foreach ($bookingMap as $bId => $pList) {
            if (count($pList) === 1) {
                $singleTravelers[] = $pList[0];
            } else {
                $familyUnits[$bId] = $pList;
            }
        }

        // Select candidates to relocate (prefer single travelers first to protect families!)
        $relocateList = [];
        foreach ($singleTravelers as $sPerson) {
            if (count($relocateList) < $overflowCount) {
                $relocateList[] = $sPerson;
            }
        }

        // If single travelers were not enough to fulfill overflow, pick smallest family unit as fallback
        if (count($relocateList) < $overflowCount) {
            foreach ($familyUnits as $bId => $fList) {
                if (count($relocateList) >= $overflowCount) break;
                foreach ($fList as $fPerson) {
                    if (count($relocateList) < $overflowCount) {
                        $relocateList[] = $fPerson;
                    }
                }
            }
        }

        // Perform split: Move selected overflow pilgrims to a new secondary group
        $newGroupName = $group->group_name . ' - Bus 2 (Split Overflow)';

        DB::transaction(function () use ($group, $busCapacity, $relocateList, $newGroupName) {
            $secondaryGroup = HajiGroup::create([
                'group_name'   => $newGroupName,
                'group_type'   => 'bus',
                'company_id'   => $group->company_id,
                'bus_capacity' => $busCapacity,
                'notes'        => 'Auto-split overflow from ' . $group->group_name . ' to preserve family units.',
                'status'       => 'active',
            ]);

            $relocateIds = collect($relocateList)->pluck('id')->toArray();

            // Update relocated pilgrims
            HajiGroupPerson::whereIn('id', $relocateIds)->update([
                'haji_group_id' => $secondaryGroup->id,
            ]);

            $group->update(['bus_capacity' => $busCapacity]);
        });

        $movedCount = count($relocateList);
        return redirect()->route('haji-group.show', $id)
            ->with('success', "Smart Split Complete! {$movedCount} single/unaccompanied pilgrims relocated to secondary group '{$newGroupName}' while preserving family units!");
    }

    /**
     * Transfer selected pilgrims to another group
     */
    public function transferPersons(Request $request, $id)
    {
        $request->validate([
            'target_group_id'   => 'required|exists:haji_groups,id',
            'group_person_ids'   => 'required|array',
            'group_person_ids.*' => 'exists:haji_group_persons,id',
        ]);

        $targetGroupId = $request->target_group_id;
        $gpIds = $request->group_person_ids;

        $targetGroup = HajiGroup::findOrFail($targetGroupId);
        $currentCount = $targetGroup->groupPersons()->count();
        $transferCount = count($gpIds);
        $capacity = intval($targetGroup->bus_capacity ?: 45);

        // Enforce bus capacity on transfer
        if (($targetGroup->group_type === 'bus' || $targetGroup->vehicle_id) && ($currentCount + $transferCount) > $capacity) {
            return back()->with('error', "Cannot Transfer! Target bus group '{$targetGroup->group_name}' capacity ({$capacity} seats) will be exceeded. (Current: {$currentCount}, Transferring: {$transferCount}, Capacity: {$capacity}).");
        }

        HajiGroupPerson::whereIn('id', $gpIds)->update([
            'haji_group_id' => $targetGroupId,
        ]);

        $count = count($gpIds);

        return back()->with('success', "Successfully transferred {$count} pilgrim(s) to '{$targetGroup->group_name}'.");
    }
}
