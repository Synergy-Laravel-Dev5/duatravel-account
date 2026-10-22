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
        // Auto-sync: if any active clients belong to already trashed leads, move them to trash too
        $trashedLeadIds = Lead::onlyTrashed()->pluck('id');
        if ($trashedLeadIds->isNotEmpty()) {
            Client::whereIn('lead_id', $trashedLeadIds)->delete();
        }

        $leads = Lead::with(['company', 'user', 'package', 'client'])
            ->latest()
            ->get();
        $trashCount = Lead::onlyTrashed()->count();

        return view('lead.index', compact('leads', 'trashCount'));
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

    public function convertToClient(Request $request, $id)
    {
        $lead = Lead::with(['company', 'package'])->findOrFail($id);

        // Determine package_type: check request input, lead's package, or current dashboard package
        $pkgType = $request->get('package_type');
        if (!$pkgType) {
            if ($lead->package && !empty($lead->package->package_type)) {
                $pkgType = $lead->package->package_type;
            } else {
                $pkgType = session('dashboard_package', 'hajj');
            }
        }
        $pkgType = in_array(strtolower($pkgType), ['hajj', 'umrah']) ? strtolower($pkgType) : 'hajj';

        // Find existing client or create new client record
        $client = Client::where('lead_id', $lead->id)->first();
        if (!$client) {
            $client = Client::create([
                'lead_id'      => $lead->id,
                'package_id'   => $lead->package_id,
                'package_type' => $pkgType,
                'name'         => $lead->contact_person ?? ('Client from Lead #' . $lead->id),
                'company_name' => $lead->company->company_name ?? null,
                'phone'        => $lead->phone,
                'email'        => $lead->email,
                'type'         => 'client',
                'status'       => 'active',
            ]);

            if (function_exists('logUserActivity')) {
                logUserActivity('Converted Lead to Client', 'Lead #' . $lead->id . ' converted to Client: ' . $client->name . ' (' . ucfirst($pkgType) . ')', $client->id, 'Client');
            }
        } else {
            $updateData = [
                'package_id'   => $lead->package_id,
                'name'         => $lead->contact_person ?? $client->name,
                'company_name' => $lead->company->company_name ?? $client->company_name,
                'phone'        => $lead->phone ?? $client->phone,
                'email'        => $lead->email ?? $client->email,
            ];
            if (empty($client->package_type)) {
                $updateData['package_type'] = $pkgType;
            }
            $client->update($updateData);
        }

        return redirect()
            ->route('lead.show', $lead->id)
            ->with('success', 'Lead #' . $lead->id . ' (' . ($lead->contact_person ?? 'Lead') . ') has been successfully converted into an ' . ucfirst($pkgType) . ' Client!');
    }

    public function destroy(Lead $lead)
    {
        // If this lead was converted to a client, move that client to trash as well
        if ($lead->client) {
            $lead->client->delete();
            if (function_exists('logUserActivity')) {
                logUserActivity('Client Deleted (via Lead)', 'Client #' . $lead->client->id . ' deleted because Lead #' . $lead->id . ' was moved to trash', $lead->client->id, 'Client');
            }
        }

        $lead->delete();

        if (function_exists('logUserActivity')) {
            logUserActivity('Lead Moved to Trash', 'Lead #' . $lead->id . ' (' . ($lead->contact_person ?? 'Lead') . ') moved to trash', $lead->id, 'Lead');
        }

        return redirect()
            ->route('lead.index')
            ->with('success', 'Lead moved to trash successfully.');
    }

    public function trash()
    {
        // Auto-sync: if any active clients belong to trashed leads, move them to trash
        $trashedLeadIds = Lead::onlyTrashed()->pluck('id');
        if ($trashedLeadIds->isNotEmpty()) {
            Client::whereIn('lead_id', $trashedLeadIds)->delete();
        }

        $leads = Lead::onlyTrashed()
            ->with(['company', 'package', 'client'])
            ->latest('deleted_at')
            ->get();

        return view('lead.trash', compact('leads'));
    }

    public function restore($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);
        $lead->restore();

        // Also restore converted client if one existed
        $client = Client::onlyTrashed()->where('lead_id', $lead->id)->first();
        if ($client) {
            $client->restore();
            if (function_exists('logUserActivity')) {
                logUserActivity('Client Restored (via Lead)', 'Client #' . $client->id . ' restored because Lead #' . $lead->id . ' was restored', $client->id, 'Client');
            }
        }

        if (function_exists('logUserActivity')) {
            logUserActivity('Lead Restored', 'Lead #' . $lead->id . ' restored from trash', $lead->id, 'Lead');
        }

        return redirect()
            ->route('lead.trash')
            ->with('success', 'Lead restored successfully.');
    }

    public function forceDelete($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);

        $client = Client::withTrashed()->where('lead_id', $lead->id)->first();
        if ($client) {
            $client->forceDelete();
        }

        $lead->forceDelete();

        if (function_exists('logUserActivity')) {
            logUserActivity('Lead Permanently Deleted', 'Lead #' . $id . ' permanently deleted from trash', $id, 'Lead');
        }

        return redirect()
            ->route('lead.trash')
            ->with('success', 'Lead permanently deleted.');
    }
}
