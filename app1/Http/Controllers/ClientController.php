<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Package;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        $package = session('dashboard_package', 'hajj');
        $year    = (int) session('dashboard_year', Carbon::now()->year);

        $clients = Client::where(function ($query) use ($package, $year) {

            $query->whereHas('bookings', function ($q) use ($package, $year) {
                $q->where('package_type', $package)
                    ->where('package_year', $year);
            })

                ->orWhereDoesntHave('bookings');
        })
            ->latest()
            ->get();

        $trashCount = Client::onlyTrashed()->count();

        return view('client.index', compact('clients', 'trashCount'));
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
            'type'            => 'required|in:client,vendor,customer',
            'company_name'    => 'nullable|string|max:255',
            'package_id'      => 'nullable|exists:packages,id',
            'lead_id'         => 'nullable|exists:leads,id',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'cnic'            => 'nullable|string|unique:clients,cnic',
            'passport_number' => 'nullable|string|unique:clients,passport_number',
        ], [
            'name.required'           => 'Client name is required.',
            'type.required'           => 'Please select a client type.',
            'package_id.exists'       => 'Selected package is invalid.',
            'cnic.unique'             => 'This CNIC is already registered with another client.',
            'passport_number.unique'  => 'This Passport Number is already registered with another client.',
        ]);

        $client = Client::create($request->only([
            'lead_id',
            'package_id',
            'name',
            'company_name',
            'passport_number',
            'cnic',
            'phone',
            'email',
            'type',
            'status'
        ]));

        logUserActivity('Client Created', 'Name: ' . $client->name . ' | Type: ' . $client->type, $client->id, 'Client');

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
            'type'            => 'required|in:client,vendor,customer',
            'package_id'      => 'nullable|exists:packages,id',
            'lead_id'         => 'nullable|exists:leads,id',
            'company_name'    => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'cnic'            => ['nullable', 'string', Rule::unique('clients', 'cnic')->ignore($client->id)],
            'passport_number' => ['nullable', 'string', Rule::unique('clients', 'passport_number')->ignore($client->id)],
        ], [
            'name.required'           => 'Client name is required.',
            'type.required'           => 'Please select a client type.',
            'package_id.exists'       => 'Selected package is invalid.',
            'cnic.unique'             => 'This CNIC is already registered with another client.',
            'passport_number.unique'  => 'This Passport Number is already registered with another client.',
        ]);

        $client->update($request->only([
            'lead_id',
            'package_id',
            'name',
            'company_name',
            'passport_number',
            'cnic',
            'phone',
            'email',
            'type',
            'status'
        ]));

        logUserActivity('Client Updated', 'Name: ' . $client->name . ' | Type: ' . $client->type, $client->id, 'Client');

        return redirect()->route('client.index')->with('success', 'Client updated.');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        logUserActivity('Client Deleted', 'Name: ' . $client->name . ' | Type: ' . $client->type, $client->id, 'Client');

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

        logUserActivity('Client Restored', 'Name: ' . $client->name . ' | Type: ' . $client->type, $client->id, 'Client');

        return redirect()->route('client.trash')->with('success', 'Client restored.');
    }
}
