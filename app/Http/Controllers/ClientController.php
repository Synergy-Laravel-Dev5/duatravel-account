<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Package;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request = null)
    {
        $request    = $request ?: request();
        $sessionPkg = session('dashboard_package', 'hajj');
        $year       = (int) session('dashboard_year', Carbon::now()->year);

        $filterPkg = $request->get('package_type', $sessionPkg);
        if (!in_array($filterPkg, ['hajj', 'umrah', 'all'])) {
            $filterPkg = $sessionPkg;
        }

        $query = Client::with(['package', 'lead'])->latest();

        if ($filterPkg !== 'all') {
            $query->where(function ($q) use ($filterPkg, $year) {
                // 1. Explicit package_type match
                $q->where('package_type', $filterPkg)
                  // 2. OR legacy / unassigned client with null/empty package_type
                  ->orWhere(function ($sub) use ($filterPkg, $year) {
                      $sub->where(function ($nullQ) {
                          $nullQ->whereNull('package_type')
                                ->orWhere('package_type', '');
                      })->where(function ($matchQ) use ($filterPkg, $year) {
                          // Has bookings matching this package
                          $matchQ->whereHas('bookings', function ($bq) use ($filterPkg, $year) {
                              $bq->where('package_type', $filterPkg);
                          })
                          // OR has assigned package matching this package
                          ->orWhereHas('package', function ($pq) use ($filterPkg) {
                              $pq->where('package_type', $filterPkg);
                          })
                          // OR has neither bookings nor package (freshly converted or unassigned client)
                          ->orWhere(function ($orphan) {
                              $orphan->whereDoesntHave('bookings')
                                     ->whereDoesntHave('package');
                          });
                      });
                  });
            });
        }

        $clients = $query->get();
        $trashCount = Client::onlyTrashed()->count();

        return view('client.index', compact('clients', 'trashCount', 'filterPkg', 'sessionPkg'));
    }

    public function create()
    {
        $packages = Package::all();
        return view('client.create', compact('packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'nullable|string|max:255',
            'package_id'      => 'nullable|exists:packages,id',
            'lead_id'         => 'nullable|exists:leads,id',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'cnic'            => 'nullable|string|unique:clients,cnic',
            'passport_number' => 'nullable|string|unique:clients,passport_number',
        ], [
            'name.required'           => 'Client name is required.',
            'package_id.exists'       => 'Selected package is invalid.',
            'cnic.unique'             => 'This CNIC is already registered with another client.',
            'passport_number.unique'  => 'This Passport Number is already registered with another client.',
        ]);

        $data = $request->only([
            'lead_id',
            'package_id',
            'name',
            'company_name',
            'passport_number',
            'cnic',
            'phone',
            'email',
            'status'
        ]);
        $data['type'] = $request->type ?? 'client';
        $data['package_type'] = $request->package_type ?: session('dashboard_package', 'hajj');

        $client = Client::create($data);

        logUserActivity('Client Created', 'Name: ' . $client->name, $client->id, 'Client');

        return redirect()->route('client.index')->with('success', 'Client added successfully.');
    }

    public function edit($id)
    {
        $client = Client::with('package', 'lead')->findOrFail($id);
        $packages = Package::all();
        return view('client.edit', compact('client', 'packages'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $request->validate([
            'name'            => 'required|string|max:255',
            'package_id'      => 'nullable|exists:packages,id',
            'lead_id'         => 'nullable|exists:leads,id',
            'company_name'    => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'cnic'            => ['nullable', 'string', Rule::unique('clients', 'cnic')->ignore($client->id)],
            'passport_number' => ['nullable', 'string', Rule::unique('clients', 'passport_number')->ignore($client->id)],
        ], [
            'name.required'           => 'Client name is required.',
            'package_id.exists'       => 'Selected package is invalid.',
            'cnic.unique'             => 'This CNIC is already registered with another client.',
            'passport_number.unique'  => 'This Passport Number is already registered with another client.',
        ]);

        $data = $request->only([
            'lead_id',
            'package_id',
            'name',
            'company_name',
            'passport_number',
            'cnic',
            'phone',
            'email',
            'status'
        ]);
        if ($request->has('type')) {
            $data['type'] = $request->type;
        }
        if ($request->has('package_type')) {
            $data['package_type'] = $request->package_type;
        }

        $client->update($data);

        logUserActivity('Client Updated', 'Name: ' . $client->name, $client->id, 'Client');

        return redirect()->route('client.index')->with('success', 'Client updated.');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        logUserActivity('Client Deleted', 'Name: ' . $client->name, $client->id, 'Client');

        $client->delete();

        return redirect()->route('client.index')->with('success', 'Moved to trash.');
    }

    public function trash()
    {
        $clients = Client::onlyTrashed()->latest()->get();
        return view('client.trash', compact('clients'));
    }

    public function restore($id)
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $client->restore();

        logUserActivity('Client Restored', 'Name: ' . $client->name, $client->id, 'Client');

        return redirect()->route('client.trash')->with('success', 'Client restored.');
    }
}
