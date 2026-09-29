<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Company;
use App\Models\Package;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with(['company', 'user', 'package', 'client'])
            ->latest()
            ->get();
        return view('lead.index', compact('leads'));
    }

    public function create()
    {
        $companies = Company::all();
        $packages = Package::all();
        $users = User::all();
        return view('lead.create', compact('companies', 'users', 'packages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'package_id' => 'nullable|exists:packages,id',
            'number_of_pax' => 'nullable',
            'source' => 'nullable',
            'medium_of_contact' => 'nullable',
            'user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        Lead::create($data);
        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead created successfully');
    }

    public function show($id)
    {
        $lead = Lead::with([
            'company',
            'package',
            'user',
            'followUps',
            'quotations.accommodations',
            'client.package'
        ])->findOrFail($id);

        return view('lead.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $packages = Package::all();
        $companies = Company::all();
        $users = User::all();
        return view(
            'lead.edit',
            compact(
                'lead',
                'companies',
                'users',
                'packages'
            )
        );
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'package_id' => 'nullable|exists:packages,id',
            'number_of_pax' => 'nullable',
            'source' => 'nullable',
            'medium_of_contact' => 'nullable',
            'user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $lead->update($data);

        // If this lead was already converted to a client, sync package and contact info to Client record
        if ($lead->client) {
            $lead->client->update([
                'package_id'   => $lead->package_id,
                'name'         => $lead->contact_person ?? $lead->client->name,
                'company_name' => $lead->company->company_name ?? $lead->client->company_name,
                'phone'        => $lead->phone ?? $lead->client->phone,
                'email'        => $lead->email ?? $lead->client->email,
            ]);
        }

        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead updated successfully');
    }

    public function convertToClient($id)
    {
        $lead = Lead::with(['company', 'package'])->findOrFail($id);

        // Find existing client or create new client record
        $client = Client::where('lead_id', $lead->id)->first();
        if (!$client) {
            $client = Client::create([
                'lead_id'      => $lead->id,
                'package_id'   => $lead->package_id,
                'name'         => $lead->contact_person ?? ('Client from Lead #' . $lead->id),
                'company_name' => $lead->company->company_name ?? null,
                'phone'        => $lead->phone,
                'email'        => $lead->email,
                'type'         => 'client',
                'status'       => 'active',
            ]);

            if (function_exists('logUserActivity')) {
                logUserActivity('Converted Lead to Client', 'Lead #' . $lead->id . ' converted to Client: ' . $client->name, $client->id, 'Client');
            }
        } else {
            $client->update([
                'package_id'   => $lead->package_id,
                'name'         => $lead->contact_person ?? $client->name,
                'company_name' => $lead->company->company_name ?? $client->company_name,
                'phone'        => $lead->phone ?? $client->phone,
                'email'        => $lead->email ?? $client->email,
            ]);
        }

        return redirect()
            ->route('lead.show', $lead->id)
            ->with('success', 'Lead #' . $lead->id . ' (' . ($lead->contact_person ?? 'Lead') . ') has been successfully converted into a Client!');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead deleted successfully');
    }
}
