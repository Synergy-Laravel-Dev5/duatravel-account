<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Package;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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
            'name'              => 'required|string|max:255',
            'company_name'      => 'nullable|string|max:255',
            'package_id'        => 'nullable|exists:packages,id',
            'lead_id'           => 'nullable|exists:leads,id',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'cnic'              => 'nullable|string|unique:clients,cnic',
            'passport_number'   => 'nullable|string|unique:clients,passport_number',
            'cnic_document'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'passport_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'opening_balance_pkr'      => 'nullable|numeric',
            'opening_balance_sar'      => 'nullable|numeric',
            'opening_balance_roe'      => 'nullable|numeric',
            'opening_balance_pkr_type' => 'nullable|in:debit,credit',
            'opening_balance_sar_type' => 'nullable|in:debit,credit',
        ], [
            'name.required'             => 'Client name is required.',
            'package_id.exists'         => 'Selected package is invalid.',
            'cnic.unique'               => 'This CNIC is already registered with another client.',
            'passport_number.unique'    => 'This Passport Number is already registered with another client.',
            'cnic_document.max'         => 'CNIC document size must not exceed 10MB.',
            'passport_document.max'     => 'Passport document size must not exceed 10MB.',
            'cnic_document.mimes'       => 'CNIC document must be a file of type: pdf, jpg, jpeg, png, webp.',
            'passport_document.mimes'   => 'Passport document must be a file of type: pdf, jpg, jpeg, png, webp.',
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
            'status',
            'opening_balance_pkr',
            'opening_balance_sar',
            'opening_balance_pkr_type',
            'opening_balance_sar_type',
            'opening_balance_roe',
        ]);

        $obPkr = (float)($request->opening_balance_pkr ?? 0);
        $obSar = (float)($request->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : 75.0);

        if ($obRoe <= 1) {
            // When ROE is 1 or less, balance is PKR-only and should NOT go to SAR
            $data['opening_balance_sar'] = 0;
            $data['opening_balance_sar_type'] = $request->opening_balance_pkr_type ?? 'debit';
        } else {
            if ($obSar == 0 && $obPkr != 0) {
                $data['opening_balance_sar'] = round($obPkr / $obRoe, 2);
                $data['opening_balance_sar_type'] = $request->opening_balance_pkr_type ?? 'debit';
            } elseif ($obPkr == 0 && $obSar != 0) {
                $data['opening_balance_pkr'] = round($obSar * $obRoe, 2);
                $data['opening_balance_pkr_type'] = $request->opening_balance_sar_type ?? 'debit';
            }
        }
        $data['opening_balance_roe'] = $obRoe;
        $data['type'] = $request->type ?? 'client';
        $data['package_type'] = $request->package_type ?: session('dashboard_package', 'hajj');

        $uploadDir = public_path('assets/images/client_documents');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($request->hasFile('cnic_document')) {
            $file     = $request->file('cnic_document');
            $filename = time() . '_cnic.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['cnic_document'] = $filename;
        }

        if ($request->hasFile('passport_document')) {
            $file     = $request->file('passport_document');
            $filename = time() . '_passport.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['passport_document'] = $filename;
        }

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
            'name'              => 'required|string|max:255',
            'package_id'        => 'nullable|exists:packages,id',
            'lead_id'           => 'nullable|exists:leads,id',
            'company_name'      => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'cnic'              => ['nullable', 'string', Rule::unique('clients', 'cnic')->ignore($client->id)],
            'passport_number'   => ['nullable', 'string', Rule::unique('clients', 'passport_number')->ignore($client->id)],
            'cnic_document'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'passport_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'opening_balance_pkr'      => 'nullable|numeric',
            'opening_balance_sar'      => 'nullable|numeric',
            'opening_balance_roe'      => 'nullable|numeric',
            'opening_balance_pkr_type' => 'nullable|in:debit,credit',
            'opening_balance_sar_type' => 'nullable|in:debit,credit',
        ], [
            'name.required'             => 'Client name is required.',
            'package_id.exists'         => 'Selected package is invalid.',
            'cnic.unique'               => 'This CNIC is already registered with another client.',
            'passport_number.unique'    => 'This Passport Number is already registered with another client.',
            'cnic_document.max'         => 'CNIC document size must not exceed 10MB.',
            'passport_document.max'     => 'Passport document size must not exceed 10MB.',
            'cnic_document.mimes'       => 'CNIC document must be a file of type: pdf, jpg, jpeg, png, webp.',
            'passport_document.mimes'   => 'Passport document must be a file of type: pdf, jpg, jpeg, png, webp.',
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
            'status',
            'opening_balance_pkr',
            'opening_balance_sar',
            'opening_balance_pkr_type',
            'opening_balance_sar_type',
            'opening_balance_roe',
        ]);

        $obPkr = (float)($request->opening_balance_pkr ?? 0);
        $obSar = (float)($request->opening_balance_sar ?? 0);
        $obRoe = (float)($request->opening_balance_roe > 0 ? $request->opening_balance_roe : 75.0);

        if ($obRoe <= 1) {
            // When ROE is 1 or less, balance is PKR-only and should NOT go to SAR
            $data['opening_balance_sar'] = 0;
            $data['opening_balance_sar_type'] = $request->opening_balance_pkr_type ?? 'debit';
        } else {
            if ($obSar == 0 && $obPkr != 0) {
                $data['opening_balance_sar'] = round($obPkr / $obRoe, 2);
                $data['opening_balance_sar_type'] = $request->opening_balance_pkr_type ?? 'debit';
            } elseif ($obPkr == 0 && $obSar != 0) {
                $data['opening_balance_pkr'] = round($obSar * $obRoe, 2);
                $data['opening_balance_pkr_type'] = $request->opening_balance_sar_type ?? 'debit';
            }
        }
        $data['opening_balance_roe'] = $obRoe;
        if ($request->has('type')) {
            $data['type'] = $request->type;
        }
        if ($request->has('package_type')) {
            $data['package_type'] = $request->package_type;
        }

        $uploadDir = public_path('assets/images/client_documents');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($request->hasFile('cnic_document')) {
            if ($client->cnic_document) {
                $oldPath = $uploadDir . '/' . basename($client->cnic_document);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file     = $request->file('cnic_document');
            $filename = time() . '_cnic.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['cnic_document'] = $filename;
        }

        if ($request->hasFile('passport_document')) {
            if ($client->passport_document) {
                $oldPath = $uploadDir . '/' . basename($client->passport_document);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file     = $request->file('passport_document');
            $filename = time() . '_passport.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['passport_document'] = $filename;
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

    public function jsonList()
    {
        $clients = Client::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'company_name', 'phone', 'cnic', 'passport_number']);
        return response()->json($clients);
    }
}
