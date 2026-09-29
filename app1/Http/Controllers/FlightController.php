<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Airline;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    public function index()
    {
        $flights = Flight::with('airline')->latest()->get();
        $trashCount = Flight::onlyTrashed()->count();

        return view('flight.index', compact('flights', 'trashCount'));
    }

    public function show($id)
    {
        $flight = Flight::with(['airline', 'sectors', 'pnrs'])->findOrFail($id);
        return view('flight.show', compact('flight'));
    }

    public function create()
    {
        $airlines = Airline::where('status', 'active')->latest()->get();
        return view('flight.create', compact('airlines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'airline_id'         => 'nullable|exists:airlines,id',
            'name'               => 'nullable|string|max:255',
            'outbound_flight_no' => 'nullable|string|max:255',
            'outbound_departure' => 'nullable|date',
            'outbound_arrival'   => 'nullable|date',
            'inbound_flight_no'  => 'nullable|string|max:255',
            'inbound_departure'  => 'nullable|date',
            'inbound_arrival'    => 'nullable|date',
            'economy_seats'      => 'nullable|integer|min:0',
            'business_seats'     => 'nullable|integer|min:0',
            'status'             => 'nullable|in:active,inactive',

            'sectors'                => 'nullable|array',
            'sectors.*.flight_no'    => 'nullable|string|max:255',
            'sectors.*.type'         => 'nullable|string|max:255',
            'sectors.*.destination'  => 'nullable|string|max:255',
            'sectors.*.departure'    => 'nullable|date',
            'sectors.*.arrival'      => 'nullable|date',

            'pnrs'             => 'nullable|array',
            'pnrs.*.pnr_type'  => 'nullable|string|max:255',
            'pnrs.*.pnr_name'  => 'nullable|string|max:255',
            'pnrs.*.capacity'  => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'airline_id',
            'name',
            'outbound_flight_no',
            'outbound_departure',
            'outbound_arrival',
            'inbound_flight_no',
            'inbound_departure',
            'inbound_arrival',
            'economy_seats',
            'business_seats',
            'status',
        ]);

        $flight = Flight::create($data);

        if ($request->has('sectors') && is_array($request->sectors)) {
            foreach ($request->sectors as $sector) {
                if (array_filter($sector)) {
                    $flight->sectors()->create($sector);
                }
            }
        }

        if ($request->has('pnrs') && is_array($request->pnrs)) {
            foreach ($request->pnrs as $pnr) {
                if (array_filter($pnr)) {
                    $flight->pnrs()->create($pnr);
                }
            }
        }

        logUserActivity('Flight Created', 'Name: ' . $flight->name, $flight->id, 'Flight');

        return redirect()->route('flight.index')->with('success', 'Flight created successfully.');
    }

    public function edit($id)
    {
        $flight = Flight::with(['sectors', 'pnrs'])->findOrFail($id);
        $airlines = Airline::where('status', 'active')->latest()->get();

        return view('flight.edit', compact('flight', 'airlines'));
    }

    public function update(Request $request, $id)
    {
        $flight = Flight::findOrFail($id);

        $request->validate([
            'airline_id'         => 'nullable|exists:airlines,id',
            'name'               => 'nullable|string|max:255',
            'outbound_flight_no' => 'nullable|string|max:255',
            'outbound_departure' => 'nullable|date',
            'outbound_arrival'   => 'nullable|date',
            'inbound_flight_no'  => 'nullable|string|max:255',
            'inbound_departure'  => 'nullable|date',
            'inbound_arrival'    => 'nullable|date',
            'economy_seats'      => 'nullable|integer|min:0',
            'business_seats'     => 'nullable|integer|min:0',
            'status'             => 'nullable|in:active,inactive',

            'sectors'                => 'nullable|array',
            'sectors.*.flight_no'    => 'nullable|string|max:255',
            'sectors.*.type'         => 'nullable|string|max:255',
            'sectors.*.destination'  => 'nullable|string|max:255',
            'sectors.*.departure'    => 'nullable|date',
            'sectors.*.arrival'      => 'nullable|date',

            'pnrs'             => 'nullable|array',
            'pnrs.*.pnr_type'  => 'nullable|string|max:255',
            'pnrs.*.pnr_name'  => 'nullable|string|max:255',
            'pnrs.*.capacity'  => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'airline_id',
            'name',
            'outbound_flight_no',
            'outbound_departure',
            'outbound_arrival',
            'inbound_flight_no',
            'inbound_departure',
            'inbound_arrival',
            'economy_seats',
            'business_seats',
            'status',
        ]);

        $flight->update($data);

        $flight->sectors()->delete();
        if ($request->has('sectors') && is_array($request->sectors)) {
            foreach ($request->sectors as $sector) {
                if (array_filter($sector)) {
                    $flight->sectors()->create($sector);
                }
            }
        }

        $flight->pnrs()->delete();
        if ($request->has('pnrs') && is_array($request->pnrs)) {
            foreach ($request->pnrs as $pnr) {
                if (array_filter($pnr)) {
                    $flight->pnrs()->create($pnr);
                }
            }
        }

        logUserActivity('Flight Updated', 'Name: ' . $flight->name, $flight->id, 'Flight');

        return redirect()->route('flight.index')->with('success', 'Flight updated successfully.');
    }

    public function destroy($id)
    {
        $flight = Flight::findOrFail($id);

        logUserActivity('Flight Deleted', 'Name: ' . $flight->name, $flight->id, 'Flight');

        $flight->delete();

        return redirect()->route('flight.index')->with('success', 'Flight moved to trash successfully.');
    }

    public function trash()
    {
        $flights = Flight::onlyTrashed()->with('airline')->latest()->get();
        return view('flight.trash', compact('flights'));
    }

    public function restore($id)
    {
        $flight = Flight::onlyTrashed()->findOrFail($id);
        $flight->restore();

        logUserActivity('Flight Restored', 'Name: ' . $flight->name, $flight->id, 'Flight');

        return redirect()->route('flight.index')->with('success', 'Flight restored successfully.');
    }
}
