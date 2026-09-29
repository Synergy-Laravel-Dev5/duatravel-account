<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Package;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AirlineController extends Controller
{
    public function index()
    {
        $airlines = Airline::latest()->get();
        $trashCount = Airline::onlyTrashed()->count();

        return view('airline.index', compact('airlines', 'trashCount'));
    }

    public function show($id)
    {
        $airline = Airline::findOrFail($id);
        return view('airline.show', compact('airline'));
    }

    public function create()
    {
        return view('airline.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'nullable|string|max:255',
            'code'       => 'nullable|string|max:255|unique:airlines,code',
            'iata_code'  => 'nullable|string|max:50',
            'icao_code'  => 'nullable|string|max:50',
            'country'    => 'nullable|string|max:255',
            'call_sign'  => 'nullable|string|max:255',
            'logo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'ffnumber'   => 'nullable|string|max:255',
            'status'     => 'nullable|in:active,inactive',
        ], [
            'code.unique' => 'This Airline Code is already registered.',
        ]);

        $data = $request->only([
            'name',
            'code',
            'iata_code',
            'icao_code',
            'country',
            'call_sign',
            'ffnumber',
            'status',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('airlines/logo', 'public');
        }

        $airline = Airline::create($data);

        logUserActivity('Airline Created', 'Name: ' . $airline->name . ' | Code: ' . $airline->code, $airline->id, 'Airline');

        return redirect()->route('airline.index')->with('success', 'Airline added successfully.');
    }


    public function edit($id)
    {
        $airline = Airline::findOrFail($id);
        return view('airline.edit', compact('airline'));
    }



    public function update(Request $request, $id)
    {
        $airline = Airline::findOrFail($id);

        $request->validate([
            'name'       => 'nullable|string|max:255',
            'code'       => ['nullable', 'string', 'max:255', Rule::unique('airlines', 'code')->ignore($airline->id)],
            'iata_code'  => 'nullable|string|max:50',
            'icao_code'  => 'nullable|string|max:50',
            'country'    => 'nullable|string|max:255',
            'call_sign'  => 'nullable|string|max:255',
            'logo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'ffnumber'   => 'nullable|string|max:255',
            'status'     => 'nullable|in:active,inactive',
        ], [
            'code.unique' => 'This Airline Code is already registered.',
        ]);

        $data = $request->only([
            'name',
            'code',
            'iata_code',
            'icao_code',
            'country',
            'call_sign',
            'ffnumber',
            'status',
        ]);

        if ($request->hasFile('logo')) {
            if ($airline->logo) {
                Storage::disk('public')->delete($airline->logo);
            }
            $data['logo'] = $request->file('logo')->store('airlines/logo', 'public');
        }

        $airline->update($data);

        logUserActivity('Airline Updated', 'Name: ' . $airline->name . ' | Code: ' . $airline->code, $airline->id, 'Airline');



        return redirect()->route('airline.index')->with('success', 'Airline updated successfully.');
    }

    public function destroy($id)
    {
        $airline = Airline::findOrFail($id);

        logUserActivity('Airline Deleted', 'Name: ' . $airline->name . ' | Code: ' . $airline->code, $airline->id, 'Airline');

        $airline->delete();

        return redirect()->route('airline.index')->with('success', 'Airline moved to trash successfully.');
    }

    public function trash()
    {
        $airlines = Airline::onlyTrashed()->latest()->get();
        return view('airline.trash', compact('airlines'));
    }

    public function restore($id)
    {
        $airline = Airline::onlyTrashed()->findOrFail($id);
        $airline->restore();

        logUserActivity('Airline Restored', 'Name: ' . $airline->name . ' | Code: ' . $airline->code, $airline->id, 'Airline');

        return redirect()->route('airline.index')->with('success', 'Airline restored successfully.');
    }
}
