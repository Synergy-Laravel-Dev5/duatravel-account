<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::latest()->get();
        $trashCount = RoomType::onlyTrashed()->count();

        return view('room_type.index', compact('roomTypes', 'trashCount'));
    }

    public function create()
    {
        return view('room_type.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150|unique:room_types,name',
            'code'        => 'nullable|string|max:100',
            'capacity'    => 'required|integer|min:1|max:50',
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
        ]);

        try {
            $data = $request->all();
            if (empty($data['code'])) {
                $data['code'] = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9_]/', '', $request->name)));
            }

            $roomType = RoomType::create($data);

            if (function_exists('logUserActivity')) {
                logUserActivity('Room Type Created', 'Name: ' . $roomType->name . ' | Capacity: ' . $roomType->capacity, $roomType->id, 'RoomType');
            }

            return redirect()->route('room-type.index')->with('success', 'Room Type created successfully.');
        } catch (Exception $e) {
            Log::error('Room Type creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error creating room type: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $roomType = RoomType::findOrFail($id);

        return view('room_type.edit', compact('roomType'));
    }

    public function update(Request $request, $id)
    {
        $roomType = RoomType::findOrFail($id);

        $request->validate([
            'name'        => ['required', 'string', 'max:150', Rule::unique('room_types', 'name')->ignore($roomType->id)],
            'code'        => 'nullable|string|max:100',
            'capacity'    => 'required|integer|min:1|max:50',
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
        ]);

        try {
            $data = $request->all();
            if (empty($data['code'])) {
                $data['code'] = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9_]/', '', $request->name)));
            }

            $roomType->update($data);

            if (function_exists('logUserActivity')) {
                logUserActivity('Room Type Updated', 'Name: ' . $roomType->name . ' | Capacity: ' . $roomType->capacity, $roomType->id, 'RoomType');
            }

            return redirect()->route('room-type.index')->with('success', 'Room Type updated successfully.');
        } catch (Exception $e) {
            Log::error('Room Type update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error updating room type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $roomType = RoomType::findOrFail($id);
            $roomType->delete();

            if (function_exists('logUserActivity')) {
                logUserActivity('Room Type Deleted', 'Name: ' . $roomType->name, $roomType->id, 'RoomType');
            }

            return redirect()->route('room-type.index')->with('success', 'Room Type moved to trash successfully.');
        } catch (Exception $e) {
            Log::error('Room Type deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting room type.');
        }
    }

    public function trash()
    {
        $roomTypes = RoomType::onlyTrashed()->latest()->get();

        return view('room_type.trash', compact('roomTypes'));
    }

    public function restore($id)
    {
        try {
            $roomType = RoomType::onlyTrashed()->findOrFail($id);
            $roomType->restore();

            if (function_exists('logUserActivity')) {
                logUserActivity('Room Type Restored', 'Name: ' . $roomType->name, $roomType->id, 'RoomType');
            }

            return redirect()->route('room-type.trash')->with('success', 'Room Type restored successfully.');
        } catch (Exception $e) {
            Log::error('Room Type restore failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error restoring room type.');
        }
    }
}
