<?php

namespace App\Http\Controllers;

use App\Models\TravelRoute;
use App\Models\Route as RouteModel;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TravelRouteController extends Controller
{
    public function index()
    {
        $travelRoutes = TravelRoute::with(['vehicle', 'routes'])->latest()->get();
        $trashCount = TravelRoute::onlyTrashed()->count();

        return view('travel_route.index', compact('travelRoutes', 'trashCount'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('status', 'active')->latest()->get();
        $routes = RouteModel::where('status', 'active')->latest()->get();

        return view('travel_route.create', compact('vehicles', 'routes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'arrival_date' => 'nullable|date',
            'arrival_time' => 'nullable',
            'vehicle_id'   => 'nullable|exists:vehicles,id',
            'sharing_type' => 'nullable|in:Group,Private,Economy',
            'status'       => 'nullable|in:active,inactive',
            'routes'       => 'nullable|array',
            'routes.*'     => 'exists:routes,id',
        ]);

        $travelRoute = TravelRoute::create([
            'name'         => $request->name,
            'arrival_date' => $request->arrival_date,
            'arrival_time' => $request->arrival_time,
            'vehicle_id'   => $request->vehicle_id,
            'sharing_type' => $request->sharing_type,
            'status'       => $request->status ?? 'active',
        ]);

        if ($request->has('routes')) {
            $travelRoute->routes()->sync($request->routes);
        }

        logUserActivity('Travel Route Created', 'Name: ' . $travelRoute->name, $travelRoute->id, 'TravelRoute');

        return redirect()->route('travel-route.index')->with('success', 'Travel Route added successfully.');
    }

    public function show($id)
    {
        $travelRoute = TravelRoute::with(['vehicle', 'routes'])->findOrFail($id);
        return view('travel_route.show', compact('travelRoute'));
    }

    public function edit($id)
    {
        $travelRoute = TravelRoute::with('routes')->findOrFail($id);
        $vehicles = Vehicle::latest()->get();
        $routes = RouteModel::latest()->get();
        $selectedRouteIds = $travelRoute->routes->pluck('id')->toArray();

        return view('travel_route.edit', compact('travelRoute', 'vehicles', 'routes', 'selectedRouteIds'));
    }

    public function update(Request $request, $id)
    {
        $travelRoute = TravelRoute::findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:255',
            'arrival_date' => 'nullable|date',
            'arrival_time' => 'nullable',
            'vehicle_id'   => 'nullable|exists:vehicles,id',
            'sharing_type' => 'nullable|in:Group,Private,Economy',
            'status'       => 'nullable|in:active,inactive',
            'routes'       => 'nullable|array',
            'routes.*'     => 'exists:routes,id',
        ]);

        $travelRoute->update([
            'name'         => $request->name,
            'arrival_date' => $request->arrival_date,
            'arrival_time' => $request->arrival_time,
            'vehicle_id'   => $request->vehicle_id,
            'sharing_type' => $request->sharing_type,
            'status'       => $request->status ?? 'active',
        ]);

        $travelRoute->routes()->sync($request->routes ?? []);

        logUserActivity('Travel Route Updated', 'Name: ' . $travelRoute->name, $travelRoute->id, 'TravelRoute');

        return redirect()->route('travel-route.index')->with('success', 'Travel Route updated successfully.');
    }

    public function destroy($id)
    {
        $travelRoute = TravelRoute::findOrFail($id);

        logUserActivity('Travel Route Deleted', 'Name: ' . $travelRoute->name, $travelRoute->id, 'TravelRoute');

        $travelRoute->delete();

        return redirect()->route('travel-route.index')->with('success', 'Travel Route moved to trash successfully.');
    }

    public function trash()
    {
        $travelRoutes = TravelRoute::onlyTrashed()->with(['vehicle', 'routes'])->latest()->get();
        return view('travel_route.trash', compact('travelRoutes'));
    }

    public function restore($id)
    {
        $travelRoute = TravelRoute::onlyTrashed()->findOrFail($id);
        $travelRoute->restore();

        logUserActivity('Travel Route Restored', 'Name: ' . $travelRoute->name, $travelRoute->id, 'TravelRoute');

        return redirect()->route('travel-route.index')->with('success', 'Travel Route restored successfully.');
    }
}
