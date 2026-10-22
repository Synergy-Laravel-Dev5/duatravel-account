<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Booking;
use App\Models\BookingPerson;
use App\Models\Flight;
use App\Models\TravelGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartureGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = TravelGroup::with(['airline', 'flight', 'persons.booking.company'])
            ->where('group_type', 'departure');

        if (isCompanyUser()) {
            $query->whereHas('persons.booking', function ($bq) {
                $bq->where('company_id', getAuthCompanyId());
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('group_name', 'like', "%{$search}%")
                  ->orWhere('flight_number', 'like', "%{$search}%")
                  ->orWhere('pnr', 'like', "%{$search}%");
            });
        }

        $groups = $query->latest()->paginate(15);

        return view('departure_group.index', compact('groups'));
    }

    public function create()
    {
        $airlines = Airline::where('status', 'active')->orderBy('name')->get();
        $flights  = Flight::with('airline')->where('status', 'active')->latest()->get();

        // Departure Group strictly includes Hajj bookings only (Umrah excluded)
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

        return view('departure_group.create', compact('airlines', 'flights', 'bookings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_name'     => 'required|string|max:200',
            'airline_id'     => 'nullable|exists:airlines,id',
            'flight_id'      => 'nullable|exists:flights,id',
            'flight_number'  => 'nullable|string|max:100',
            'flight_date'    => 'nullable|date',
            'flight_time'    => 'nullable|string|max:50',
            'pnr'            => 'nullable|string|max:100',
            'departure_city' => 'nullable|string|max:150',
            'arrival_city'   => 'nullable|string|max:150',
            'notes'          => 'nullable|string',
            'person_ids'     => 'nullable|array',
            'person_ids.*'   => 'exists:booking_persons,id',
        ]);

        $createdGroup = DB::transaction(function () use ($request) {
            $group = TravelGroup::create([
                'group_type'     => 'departure',
                'group_name'     => $request->group_name,
                'airline_id'     => $request->airline_id ?: null,
                'flight_id'      => $request->flight_id ?: null,
                'flight_number'  => $request->flight_number ?: null,
                'flight_date'    => $request->flight_date ?: null,
                'flight_time'    => $request->flight_time ?: null,
                'pnr'            => $request->pnr ?: null,
                'departure_city' => $request->departure_city ?: null,
                'arrival_city'   => $request->arrival_city ?: null,
                'notes'          => $request->notes ?: null,
                'status'         => 'active',
            ]);

            if ($request->has('person_ids') && is_array($request->person_ids)) {
                $persons = BookingPerson::whereIn('id', $request->person_ids)->get();
                $syncData = [];
                foreach ($persons as $p) {
                    $syncData[$p->id] = ['booking_id' => $p->booking_id];
                }
                $group->persons()->sync($syncData);
            }
            return $group;
        });

        logUserActivity('Departure Group Created', 'Departure Group: ' . $createdGroup->group_name, $createdGroup->id, 'DepartureGroup');

        return redirect()->route('departure-group.index')->with('success', 'Departure Group created successfully.');
    }

    public function show($id)
    {
        $group = TravelGroup::with(['airline', 'flight', 'persons.booking.company', 'persons.booking.client'])
            ->where('group_type', 'departure')
            ->findOrFail($id);

        return view('departure_group.show', compact('group'));
    }

    public function edit($id)
    {
        $group = TravelGroup::with('persons')
            ->where('group_type', 'departure')
            ->findOrFail($id);

        $airlines = Airline::where('status', 'active')->orderBy('name')->get();
        $flights  = Flight::with('airline')->where('status', 'active')->latest()->get();

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

        $selectedPersonIds = $group->persons->pluck('id')->toArray();

        return view('departure_group.edit', compact('group', 'airlines', 'flights', 'bookings', 'selectedPersonIds'));
    }

    public function update(Request $request, $id)
    {
        $group = TravelGroup::where('group_type', 'departure')->findOrFail($id);

        $request->validate([
            'group_name'     => 'required|string|max:200',
            'airline_id'     => 'nullable|exists:airlines,id',
            'flight_id'      => 'nullable|exists:flights,id',
            'flight_number'  => 'nullable|string|max:100',
            'flight_date'    => 'nullable|date',
            'flight_time'    => 'nullable|string|max:50',
            'pnr'            => 'nullable|string|max:100',
            'departure_city' => 'nullable|string|max:150',
            'arrival_city'   => 'nullable|string|max:150',
            'notes'          => 'nullable|string',
            'person_ids'     => 'nullable|array',
            'person_ids.*'   => 'exists:booking_persons,id',
        ]);

        DB::transaction(function () use ($request, $group) {
            $group->update([
                'group_name'     => $request->group_name,
                'airline_id'     => $request->airline_id ?: null,
                'flight_id'      => $request->flight_id ?: null,
                'flight_number'  => $request->flight_number ?: null,
                'flight_date'    => $request->flight_date ?: null,
                'flight_time'    => $request->flight_time ?: null,
                'pnr'            => $request->pnr ?: null,
                'departure_city' => $request->departure_city ?: null,
                'arrival_city'   => $request->arrival_city ?: null,
                'notes'          => $request->notes ?: null,
            ]);

            if ($request->has('person_ids') && is_array($request->person_ids)) {
                $persons = BookingPerson::whereIn('id', $request->person_ids)->get();
                $syncData = [];
                foreach ($persons as $p) {
                    $syncData[$p->id] = ['booking_id' => $p->booking_id];
                }
                $group->persons()->sync($syncData);
            } else {
                $group->persons()->detach();
            }
        });

        logUserActivity('Departure Group Updated', 'Departure Group: ' . $group->group_name, $group->id, 'DepartureGroup');

        return redirect()->route('departure-group.index')->with('success', 'Departure Group updated successfully.');
    }

    public function destroy($id)
    {
        $group = TravelGroup::where('group_type', 'departure')->findOrFail($id);
        $group->delete();

        logUserActivity('Departure Group Deleted', 'Departure Group: ' . $group->group_name, $group->id, 'DepartureGroup');

        return redirect()->route('departure-group.index')->with('success', 'Departure Group deleted successfully.');
    }
}
