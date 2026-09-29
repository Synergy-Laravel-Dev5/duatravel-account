<?php

namespace App\Http\Controllers;

use App\Models\Route as RouteModel;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = RouteModel::latest()->get();
        $trashCount = RouteModel::onlyTrashed()->count();

        return view('route.index', compact('routes', 'trashCount'));
    }

    public function create()
    {
        return view('route.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_place' => 'required|string|max:255',
            'end_place'   => 'required|string|max:255',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $route = RouteModel::create([
            'start_place' => $request->start_place,
            'end_place'   => $request->end_place,
            'status'      => $request->status ?? 'active',
        ]);

        logUserActivity('Route Created', 'Start: ' . $route->start_place . ' | End: ' . $route->end_place, $route->id, 'Route');

        return redirect()->route('route.index')->with('success', 'Route added successfully.');
    }

    public function edit($id)
    {
        $route = RouteModel::findOrFail($id);
        return view('route.edit', compact('route'));
    }

    public function update(Request $request, $id)
    {
        $route = RouteModel::findOrFail($id);

        $request->validate([
            'start_place' => 'required|string|max:255',
            'end_place'   => 'required|string|max:255',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $route->update([
            'start_place' => $request->start_place,
            'end_place'   => $request->end_place,
            'status'      => $request->status ?? 'active',
        ]);

        logUserActivity('Route Updated', 'Start: ' . $route->start_place . ' | End: ' . $route->end_place, $route->id, 'Route');

        return redirect()->route('route.index')->with('success', 'Route updated successfully.');
    }

    public function destroy($id)
    {
        $route = RouteModel::findOrFail($id);

        logUserActivity('Route Deleted', 'Start: ' . $route->start_place . ' | End: ' . $route->end_place, $route->id, 'Route');

        $route->delete();

        return redirect()->route('route.index')->with('success', 'Route moved to trash successfully.');
    }

    public function trash()
    {
        $routes = RouteModel::onlyTrashed()->latest()->get();
        return view('route.trash', compact('routes'));
    }

    public function restore($id)
    {
        $route = RouteModel::onlyTrashed()->findOrFail($id);
        $route->restore();

        logUserActivity('Route Restored', 'Start: ' . $route->start_place . ' | End: ' . $route->end_place, $route->id, 'Route');

        return redirect()->route('route.index')->with('success', 'Route restored successfully.');
    }
}
