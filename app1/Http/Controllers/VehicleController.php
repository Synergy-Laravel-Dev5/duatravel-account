<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::latest()->get();
        $trashCount = Vehicle::onlyTrashed()->count();

        return view('vehicle.index', compact('vehicles', 'trashCount'));
    }

    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('vehicle.show', compact('vehicle'));
    }

    public function create()
    {
        return view('vehicle.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_type'  => 'nullable|string|max:255',
            'brand_name'    => 'nullable|string|max:255',
            'model_year'    => 'nullable|string|max:255',
            'plate_number'  => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'status'        => 'nullable|in:active,inactive',
        ]);

        $vehicle = Vehicle::create($request->only([
            'vehicle_type',
            'brand_name',
            'model_year',
            'plate_number',
            'supplier_name',
            'status',
        ]));

        logUserActivity('Vehicle Created', 'Type: ' . $vehicle->vehicle_type . ' | Brand: ' . $vehicle->brand_name, $vehicle->id, 'Vehicle');

        return redirect()->route('vehicle.index')->with('success', 'Vehicle added successfully.');
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('vehicle.edit', compact('vehicle'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'vehicle_type'  => 'nullable|string|max:255',
            'brand_name'    => 'nullable|string|max:255',
            'model_year'    => 'nullable|string|max:255',
            'plate_number'  => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'status'        => 'nullable|in:active,inactive',
        ]);

        $vehicle->update($request->only([
            'vehicle_type',
            'brand_name',
            'model_year',
            'plate_number',
            'supplier_name',
            'status',
        ]));

        logUserActivity('Vehicle Updated', 'Type: ' . $vehicle->vehicle_type . ' | Brand: ' . $vehicle->brand_name, $vehicle->id, 'Vehicle');

        return redirect()->route('vehicle.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        logUserActivity('Vehicle Deleted', 'Type: ' . $vehicle->vehicle_type . ' | Brand: ' . $vehicle->brand_name, $vehicle->id, 'Vehicle');

        $vehicle->delete();

        return redirect()->route('vehicle.index')->with('success', 'Vehicle moved to trash successfully.');
    }

    public function trash()
    {
        $vehicles = Vehicle::onlyTrashed()->latest()->get();
        return view('vehicle.trash', compact('vehicles'));
    }

    public function restore($id)
    {
        $vehicle = Vehicle::onlyTrashed()->findOrFail($id);
        $vehicle->restore();

        logUserActivity('Vehicle Restored', 'Type: ' . $vehicle->vehicle_type . ' | Brand: ' . $vehicle->brand_name, $vehicle->id, 'Vehicle');

        return redirect()->route('vehicle.index')->with('success', 'Vehicle restored successfully.');
    }
}
