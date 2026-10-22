<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeasonController extends Controller
{
    /**
     * Display a listing of Hajj and Umrah Seasons.
     */
    public function index(Request $request)
    {
        $type   = $request->get('type', 'all');
        $status = $request->get('status', 'all');

        $query = Season::orderByDesc('year')->orderBy('season_type');

        if (in_array($type, ['hajj', 'umrah'])) {
            $query->where('season_type', $type);
        }

        if ($status === 'active') {
            $query->where('status', 'active');
        } elseif ($status === 'inactive') {
            $query->where('status', '!=', 'active');
        }

        $seasons = $query->get();
        $trashCount = Season::onlyTrashed()->count();

        $activeCount   = Season::where('status', 'active')->count();
        $inactiveCount = Season::where('status', '!=', 'active')->count();

        $activeSeason = Season::current();

        return view('season.index', compact('seasons', 'type', 'status', 'trashCount', 'activeCount', 'inactiveCount', 'activeSeason'));
    }

    /**
     * Show the form for creating a new Season.
     */
    public function create()
    {
        $currentYear = (int) date('Y');
        return view('season.create', compact('currentYear'));
    }

    /**
     * Store a newly created Season in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'season_type' => 'required|in:hajj,umrah',
            'year'        => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('seasons')->where(function ($query) use ($request) {
                    return $query->where('season_type', $request->season_type)
                                 ->whereNull('deleted_at');
                }),
            ],
            'hijri_year'  => 'nullable|string|max:50',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'required|in:active,upcoming,closed',
            'is_active'   => 'nullable|boolean',
            'notes'       => 'nullable|string',
        ], [
            'year.unique' => 'A ' . strtoupper($request->season_type) . ' season for year ' . $request->year . ' already exists.',
        ]);

        $isActive = $request->boolean('is_active');

        // If this is set as active default or it's the first season of this type
        $isFirst = Season::where('season_type', $request->season_type)->count() === 0;
        if ($isActive || $isFirst) {
            Season::where('season_type', $request->season_type)->update(['is_active' => false]);
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = false;
        }

        $season = Season::create($validated);

        if (function_exists('logUserActivity')) {
            logUserActivity('Season Created', "Created {$season->name} ({$season->season_type})", $season->id, 'Season');
        }

        return redirect()->route('season.index', ['type' => $season->season_type])
            ->with('success', "Season '{$season->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified Season.
     */
    public function edit($id)
    {
        $season = Season::findOrFail($id);
        return view('season.edit', compact('season'));
    }

    /**
     * Update the specified Season in storage.
     */
    public function update(Request $request, $id)
    {
        $season = Season::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'season_type' => 'required|in:hajj,umrah',
            'year'        => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('seasons')->where(function ($query) use ($request, $id) {
                    return $query->where('season_type', $request->season_type)
                                 ->where('id', '!=', $id)
                                 ->whereNull('deleted_at');
                }),
            ],
            'hijri_year'  => 'nullable|string|max:50',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'required|in:active,upcoming,closed',
            'is_active'   => 'nullable|boolean',
            'notes'       => 'nullable|string',
        ], [
            'year.unique' => 'A ' . strtoupper($request->season_type) . ' season for year ' . $request->year . ' already exists.',
        ]);

        if ($request->boolean('is_active')) {
            Season::where('season_type', $request->season_type)
                ->where('id', '!=', $id)
                ->update(['is_active' => false]);
            $validated['is_active'] = true;
        }

        $season->update($validated);

        // If currently active season in session was updated, refresh session
        if (session('selected_season_id') == $season->id) {
            session([
                'selected_season_name' => $season->name,
                'dashboard_package'    => $season->season_type,
                'dashboard_year'       => $season->year,
            ]);
        }

        if (function_exists('logUserActivity')) {
            logUserActivity('Season Updated', "Updated {$season->name} ({$season->season_type})", $season->id, 'Season');
        }

        return redirect()->route('season.index', ['type' => $season->season_type])
            ->with('success', "Season '{$season->name}' updated successfully.");
    }

    /**
     * Remove the specified Season from storage (soft delete).
     */
    public function destroy($id)
    {
        $season = Season::findOrFail($id);
        $name = $season->name;
        $season->delete();

        if (function_exists('logUserActivity')) {
            logUserActivity('Season Deleted', "Moved season {$name} to trash", $id, 'Season');
        }

        return redirect()->route('season.index')
            ->with('success', "Season '{$name}' moved to trash.");
    }

    /**
     * Display a listing of trashed Seasons.
     */
    public function trash()
    {
        $seasons = Season::onlyTrashed()->latest('deleted_at')->get();
        return view('season.trash', compact('seasons'));
    }

    /**
     * Restore the specified Season from trash.
     */
    public function restore($id)
    {
        $season = Season::onlyTrashed()->findOrFail($id);
        $season->restore();

        if (function_exists('logUserActivity')) {
            logUserActivity('Season Restored', "Restored season {$season->name}", $season->id, 'Season');
        }

        return redirect()->route('season.trash')
            ->with('success', "Season '{$season->name}' restored successfully.");
    }

    /**
     * Permanently delete the specified Season from trash.
     */
    public function forceDelete($id)
    {
        $season = Season::onlyTrashed()->findOrFail($id);
        $name = $season->name;
        $season->forceDelete();

        if (function_exists('logUserActivity')) {
            logUserActivity('Season Permanently Deleted', "Permanently deleted {$name}", $id, 'Season');
        }

        return redirect()->route('season.trash')
            ->with('success', "Season '{$name}' permanently deleted.");
    }

    /**
     * Set a season as the active default for its category (Hajj / Umrah).
     */
    public function setActive($id)
    {
        $season = Season::findOrFail($id);

        Season::where('season_type', $season->season_type)->update(['is_active' => false]);
        $season->update(['is_active' => true, 'status' => 'active']);

        return redirect()->back()
            ->with('success', "Season '{$season->name}' set as active default for " . strtoupper($season->season_type) . ".");
    }

    /**
     * Quick toggle season operational status between active and inactive.
     */
    public function toggleStatus($id)
    {
        $season = Season::findOrFail($id);
        $newStatus = ($season->status === 'active') ? 'closed' : 'active';
        $season->update(['status' => $newStatus]);

        if (function_exists('logUserActivity')) {
            logUserActivity('Season Status Toggled', "Changed {$season->name} status to {$newStatus}", $season->id, 'Season');
        }

        $label = ($newStatus === 'active') ? 'Active' : 'Inactive';
        return redirect()->back()
            ->with('success', "Season '{$season->name}' marked as {$label}.");
    }

    /**
     * One-click Season Switcher: Switches the entire system session to this season.
     */
    public function switchSeason($id, Request $request)
    {
        $season = Season::findOrFail($id);

        session([
            'selected_season_id'     => $season->id,
            'selected_season_name'   => $season->name,
            'dashboard_package'      => $season->season_type,
            'dashboard_year'         => $season->year,
            'dashboard_company_year' => $season->year,
        ]);

        if (function_exists('logUserActivity')) {
            logUserActivity('Season Switched', "Switched system to {$season->name} ({$season->year})", $season->id, 'Season');
        }

        if ($request->get('redirect_to') === 'dashboard' || url()->previous() === route('welcome') || url()->previous() === url('/')) {
            return redirect()->route('dashboard')
                ->with('success', "System switched to Season: {$season->name} (" . strtoupper($season->season_type) . " {$season->year}).");
        }

        return redirect()->back()
            ->with('success', "System switched to Season: {$season->name} (" . strtoupper($season->season_type) . " {$season->year}).");
    }
}
