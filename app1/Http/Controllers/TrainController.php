<?php

namespace App\Http\Controllers;

use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainController extends Controller
{
    public function index()
    {
        $trains = Train::latest()->get();
        $trashCount = Train::onlyTrashed()->count();

        return view('train.index', compact('trains', 'trashCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'nullable|string|max:255',
            'code'   => 'nullable|string|max:255|unique:trains,code',
            'status' => 'nullable|in:active,inactive',
        ], [
            'code.unique' => 'This Train Code is already registered.',
        ]);

        $train = Train::create($request->only(['name', 'code', 'status']));

        logUserActivity('Train Created', 'Name: ' . $train->name . ' | Code: ' . $train->code, $train->id, 'Train');

        return redirect()->route('train.index')->with('success', 'Train added successfully.');
    }

    public function update(Request $request, $id)
    {
        $train = Train::findOrFail($id);

        $request->validate([
            'name'   => 'nullable|string|max:255',
            'code'   => ['nullable', 'string', 'max:255', Rule::unique('trains', 'code')->ignore($train->id)],
            'status' => 'nullable|in:active,inactive',
        ], [
            'code.unique' => 'This Train Code is already registered.',
        ]);

        $train->update($request->only(['name', 'code', 'status']));

        logUserActivity('Train Updated', 'Name: ' . $train->name . ' | Code: ' . $train->code, $train->id, 'Train');

        return redirect()->route('train.index')->with('success', 'Train updated successfully.');
    }

    public function destroy($id)
    {
        $train = Train::findOrFail($id);

        logUserActivity('Train Deleted', 'Name: ' . $train->name . ' | Code: ' . $train->code, $train->id, 'Train');

        $train->delete();

        return redirect()->route('train.index')->with('success', 'Train moved to trash successfully.');
    }

    public function trash()
    {
        $trains = Train::onlyTrashed()->latest()->get();
        return view('train.trash', compact('trains'));
    }

    public function restore($id)
    {
        $train = Train::onlyTrashed()->findOrFail($id);
        $train->restore();

        logUserActivity('Train Restored', 'Name: ' . $train->name . ' | Code: ' . $train->code, $train->id, 'Train');

        return redirect()->route('train.index')->with('success', 'Train restored successfully.');
    }
}
