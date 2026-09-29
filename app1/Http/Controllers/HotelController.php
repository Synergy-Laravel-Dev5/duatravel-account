<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Enums\Place;
use App\Enums\AccommodationType;
use App\Enums\AccommodationCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function index()
    {
        $hotels = Hotel::latest()->get();
        $trashCount = Hotel::onlyTrashed()->count();

        return view('hotel.index', compact('hotels', 'trashCount'));
    }

    public function show($id)
    {
        $hotel = Hotel::findOrFail($id);
        return view('hotel.show', compact('hotel'));
    }


    public function create()
    {
        $places = Place::options();
        $types = AccommodationType::options();
        $categories = AccommodationCategory::options();

        return view('hotel.create', compact('places', 'types', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hotel_number'           => 'nullable|string|max:255',
            'code'                   => 'nullable|string|max:255|unique:hotels,code',
            'name'                   => 'nullable|string|max:255',
            'address'                => 'nullable|string',
            'contact'                => 'nullable|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'place'                  => ['nullable', new Enum(Place::class)],
            'accommodation_type'     => ['nullable', new Enum(AccommodationType::class)],
            'accommodation_category' => ['nullable', new Enum(AccommodationCategory::class)],
            'logo'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'status'                 => 'nullable|in:active,inactive',
        ], [
            'code.unique' => 'This Hotel Code is already registered.',
        ]);

        $data = $request->only([
            'hotel_number',
            'code',
            'name',
            'address',
            'contact',
            'email',
            'place',
            'accommodation_type',
            'accommodation_category',
            'status',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('hotels/logo', 'public');
        }

        $hotel = Hotel::create($data);

        logUserActivity('Hotel Created', 'Name: ' . $hotel->name . ' | Code: ' . $hotel->code, $hotel->id, 'Hotel');

        return redirect()->route('hotel.index')->with('success', 'Hotel added successfully.');
    }

    public function edit($id)
    {
        $hotel = Hotel::findOrFail($id);
        $places = Place::options();
        $types = AccommodationType::options();
        $categories = AccommodationCategory::options();

        return view('hotel.edit', compact('hotel', 'places', 'types', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);

        $request->validate([
            'hotel_number'           => 'nullable|string|max:255',
            'code'                   => ['nullable', 'string', 'max:255', Rule::unique('hotels', 'code')->ignore($hotel->id)],
            'name'                   => 'nullable|string|max:255',
            'address'                => 'nullable|string',
            'contact'                => 'nullable|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'place'                  => ['nullable', new Enum(Place::class)],
            'accommodation_type'     => ['nullable', new Enum(AccommodationType::class)],
            'accommodation_category' => ['nullable', new Enum(AccommodationCategory::class)],
            'logo'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'status'                 => 'nullable|in:active,inactive',
        ], [
            'code.unique' => 'This Hotel Code is already registered.',
        ]);

        $data = $request->only([
            'hotel_number',
            'code',
            'name',
            'address',
            'contact',
            'email',
            'place',
            'accommodation_type',
            'accommodation_category',
            'status',
        ]);

        if ($request->hasFile('logo')) {
            if ($hotel->logo) {
                Storage::disk('public')->delete($hotel->logo);
            }
            $data['logo'] = $request->file('logo')->store('hotels/logo', 'public');
        }

        $hotel->update($data);

        logUserActivity('Hotel Updated', 'Name: ' . $hotel->name . ' | Code: ' . $hotel->code, $hotel->id, 'Hotel');

        return redirect()->route('hotel.index')->with('success', 'Hotel updated successfully.');
    }

    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);

        logUserActivity('Hotel Deleted', 'Name: ' . $hotel->name . ' | Code: ' . $hotel->code, $hotel->id, 'Hotel');

        $hotel->delete();

        return redirect()->route('hotel.index')->with('success', 'Hotel moved to trash successfully.');
    }

    public function trash()
    {
        $hotels = Hotel::onlyTrashed()->latest()->get();
        return view('hotel.trash', compact('hotels'));
    }

    public function restore($id)
    {
        $hotel = Hotel::onlyTrashed()->findOrFail($id);
        $hotel->restore();

        logUserActivity('Hotel Restored', 'Name: ' . $hotel->name . ' | Code: ' . $hotel->code, $hotel->id, 'Hotel');

        return redirect()->route('hotel.index')->with('success', 'Hotel restored successfully.');
    }
}
