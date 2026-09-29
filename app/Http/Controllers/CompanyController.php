<?php

namespace App\Http\Controllers;

use App\Enums\CurrencyType;
use App\Models\Company;
use App\Models\CompanyLogin;
use App\Mail\CompanyLoginMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class CompanyController extends Controller
{
    public function index()
    {
        $year = (int) session('dashboard_year', Carbon::now()->year);

        $companies = Company::with('login')
            ->where('company_year', $year)
            ->latest()
            ->get();

        $trashcount = Company::onlyTrashed()->count();

        return view('companies.index', compact('companies', 'trashcount', 'year'));
    }

    public function create()
    {
        $company = null;
        return view('companies.create', compact('company'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Company Details
            'company_name' => 'nullable|string|max:255',
            'company_code' => 'nullable|string|max:255|unique:companies,company_code',
            'currency_type' => ['nullable', new Enum(CurrencyType::class)],
            'established_on' => 'nullable|date',
            'quota' => 'nullable|integer|min:0',
            'website' => 'nullable|url|max:255',
            'company_status_on_establishment' => 'nullable|string|max:255',
            'current_company_status' => 'nullable|string|max:255',
            'company_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 5),

            // Files
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'company_stamp' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'letter_head_header' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'letter_head_footer' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'company_signature' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',

            // Chief Executive
            'ceo_name' => 'nullable|string|max:255',
            'ceo_cnic_front' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'ceo_cnic_back' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'ceo_shares_percent' => 'nullable|string|max:255',

            // Registration
            'mohra_enrollment_no' => 'nullable|string|max:255',
            'munazzam_no' => 'nullable|string|max:255',
            'cluster_enrollment_no' => 'nullable|string|max:255',
            'dts_no' => 'nullable|string|max:255',
            'dts_expiry' => 'nullable|date',
            'iata_no' => 'nullable|string|max:255',
            'iata_expiry' => 'nullable|date',
            'ntn' => 'nullable|string|max:255',

            // Login Details
            'login_email' => 'nullable|email|unique:company_logins,email',
            'login_password' => 'nullable|string|min:6',

            // Address
            'address' => 'nullable|array',
            'address.*.address_of' => 'nullable|string|max:255',
            'address.*.address' => 'nullable|string',
            'address.*.country' => 'nullable|string|max:255',
            'address.*.city' => 'nullable|string|max:255',
            'address.*.po_box' => 'nullable|string|max:255',
            'address.*.zip_code' => 'nullable|string|max:255',
            'address.*.is_preferred' => 'nullable|in:Yes,No',

            // Contact
            'contact' => 'nullable|array',
            'contact.*.contact_type' => 'nullable|string|max:255',
            'contact.*.contact' => 'nullable|string|max:255',
            'contact.*.is_preferred' => 'nullable|in:Yes,No',

            // Email
            'email' => 'nullable|array',
            'email.*.email_type' => 'nullable|string|max:255',
            'email.*.email' => 'nullable|email|max:255',
            'email.*.is_preferred' => 'nullable|in:Yes,No',

            // License
            'license' => 'nullable|array',
            'license.*.license_type' => 'nullable|string|max:255',
            'license.*.license_no' => 'nullable|string|max:255',
            'license.*.place_of_issue' => 'nullable|string|max:255',
            'license.*.date_of_issue' => 'nullable|date',
            'license.*.valid_upto' => 'nullable|date',
            'license.*.is_preferred' => 'nullable|in:Yes,No',

            // Directors (Multi)
            'directors' => 'nullable|array',
            'directors.*.name' => 'nullable|string|max:255',
            'directors.*.cnic' => 'nullable|string|max:255',
            'directors.*.cnic_expiry' => 'nullable|date',
            'directors.*.cnic_front' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'directors.*.cnic_back' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'directors.*.shares_percent' => 'nullable|numeric|min:0',

            'directors.*.photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'directors.*.detail' => 'nullable|string',

            // Banks (Multi)
            'banks' => 'nullable|array',
            'banks.*.bank_name' => 'nullable|string|max:255',
            'banks.*.account_title' => 'nullable|string|max:255',
            'banks.*.branch' => 'nullable|string|max:255',
            'banks.*.account_number' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->except([
                'company_logo',
                'company_stamp',
                'letter_head_header',
                'letter_head_footer',
                'company_signature',
                'ceo_cnic_front',
                'ceo_cnic_back',
                'address',
                'contact',
                'email',
                'license',
                'directors',
                'banks',
                'login_email',
                'login_password',
            ]);

            // File uploads for company
            $logoFields = [
                'company_logo' => 'companies/logo',
                'company_stamp' => 'companies/stamp',
                'letter_head_header' => 'companies/letter_head/header',
                'letter_head_footer' => 'companies/letter_head/footer',
                'company_signature' => 'companies/signature',
                'ceo_cnic_front' => 'companies/ceo',
                'ceo_cnic_back' => 'companies/ceo',
            ];

            foreach ($logoFields as $field => $path) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store($path, 'public');
                }
            }

            $company = Company::create($data);

            // ===== LOGIN DETAILS SAVE =====
            if ($request->filled('login_email')) {
                $plainPassword = $request->filled('login_password') ? $request->login_password : Str::random(10);
                CompanyLogin::create([
                    'company_id' => $company->id,
                    'email'      => $request->login_email,
                    'password'   => Hash::make($plainPassword),
                ]);
                Mail::to($request->login_email)->send(
                    new CompanyLoginMail($request->login_email, $plainPassword)
                );
            }

            // ADDRESS
            foreach ($request->input('address', []) as $addr) {
                if (empty($addr['address_of']) && empty($addr['address'])) continue;
                $company->addresses()->create([
                    'address_of'   => $addr['address_of'] ?? '',
                    'address'      => $addr['address'] ?? '',
                    'country'      => $addr['country'] ?? '',
                    'city'         => $addr['city'] ?? '',
                    'po_box'       => $addr['po_box'] ?? null,
                    'zip_code'     => $addr['zip_code'] ?? null,
                    'is_preferred' => ($addr['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            // CONTACT
            foreach ($request->input('contact', []) as $con) {
                if (empty($con['contact_type']) && empty($con['contact'])) continue;
                $company->contactNumbers()->create([
                    'contact_type' => $con['contact_type'] ?? '',
                    'contact'      => $con['contact'] ?? '',
                    'is_preferred' => ($con['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            // EMAIL
            foreach ($request->input('email', []) as $em) {
                if (empty($em['email_type']) && empty($em['email'])) continue;
                $company->emails()->create([
                    'email_type'   => $em['email_type'] ?? '',
                    'email'        => $em['email'] ?? '',
                    'is_preferred' => ($em['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            // LICENSE
            foreach ($request->input('license', []) as $lic) {
                if (empty($lic['license_type']) && empty($lic['license_no'])) continue;
                $company->licenses()->create([
                    'license_type'   => $lic['license_type'] ?? '',
                    'license_no'     => $lic['license_no'] ?? '',
                    'place_of_issue' => $lic['place_of_issue'] ?? '',
                    'date_of_issue'  => $lic['date_of_issue'] ?? null,
                    'valid_upto'     => $lic['valid_upto'] ?? null,
                    'is_preferred'   => ($lic['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            // DIRECTORS (Multi with files)
            $directorsData = $request->input('directors', []);
            $directorsFiles = $request->file('directors', []);
            foreach ($directorsData as $i => $dir) {
                if (empty($dir['name']) && empty($dir['cnic'])) continue;

                $dirData = [
                    'name' => $dir['name'] ?? '',
                    'cnic' => $dir['cnic'] ?? '',
                    'cnic_expiry' => $dir['cnic_expiry'] ?? null,
                    'shares_percent' => isset($dir['shares_percent']) && $dir['shares_percent'] !== '' ? (float) $dir['shares_percent'] : null,

                    'detail' => $dir['detail'] ?? '',
                ];

                if (isset($directorsFiles[$i]['cnic_front'])) {
                    $dirData['cnic_front'] = $directorsFiles[$i]['cnic_front']->store('companies/directors/cnic_front', 'public');
                }
                if (isset($directorsFiles[$i]['cnic_back'])) {
                    $dirData['cnic_back'] = $directorsFiles[$i]['cnic_back']->store('companies/directors/cnic_back', 'public');
                }
                if (isset($directorsFiles[$i]['photo'])) {
                    $dirData['photo'] = $directorsFiles[$i]['photo']->store('companies/directors/photo', 'public');
                }

                $company->directors()->create($dirData);
            }

            // BANKS (Multi)
            foreach ($request->input('banks', []) as $bank) {
                if (empty($bank['bank_name']) && empty($bank['account_number'])) continue;
                $company->banks()->create([
                    'bank_name' => $bank['bank_name'] ?? '',
                    'account_title' => $bank['account_title'] ?? '',
                    'branch' => $bank['branch'] ?? '',
                    'account_number' => $bank['account_number'] ?? '',
                ]);
            }

            DB::commit();

            return redirect()->route('company.index')->with('success', 'Company created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $company = Company::with(['login', 'directors', 'banks', 'addresses', 'contactNumbers', 'emails', 'licenses'])->findOrFail($id);
        return view('companies.create', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $request->validate([
            // Company Details
            'company_name' => 'nullable|string|max:255',
            'company_code' => 'nullable|string|max:255|unique:companies,company_code,' . $company->id,
            'currency_type' => ['nullable', new Enum(CurrencyType::class)],
            'established_on' => 'nullable|date',
            'quota' => 'nullable|integer|min:0',
            'website' => 'nullable|url|max:255',
            'company_status_on_establishment' => 'nullable|string|max:255',
            'current_company_status' => 'nullable|string|max:255',
            'company_year' => 'nullable|integer',

            // Files
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'company_stamp' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'letter_head_header' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'letter_head_footer' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'company_signature' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',

            // Chief Executive
            'ceo_name' => 'nullable|string|max:255',
            'ceo_cnic_front' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'ceo_cnic_back' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'ceo_shares_percent' => 'nullable|string|max:255',

            // Registration
            'mohra_enrollment_no' => 'nullable|string|max:255',
            'munazzam_no' => 'nullable|string|max:255',
            'cluster_enrollment_no' => 'nullable|string|max:255',
            'dts_no' => 'nullable|string|max:255',
            'dts_expiry' => 'nullable|date',
            'iata_no' => 'nullable|string|max:255',
            'iata_expiry' => 'nullable|date',
            'ntn' => 'nullable|string|max:255',

            // Login Details
            'login_email' => 'nullable|email|unique:company_logins,email,' . optional($company->login)->id,
            'login_password' => 'nullable|string|min:6',

            // Address
            'address' => 'nullable|array',
            'address.*.address_of' => 'nullable|string|max:255',
            'address.*.address' => 'nullable|string',
            'address.*.country' => 'nullable|string|max:255',
            'address.*.city' => 'nullable|string|max:255',
            'address.*.po_box' => 'nullable|string|max:255',
            'address.*.zip_code' => 'nullable|string|max:255',
            'address.*.is_preferred' => 'nullable|in:Yes,No',

            // Contact
            'contact' => 'nullable|array',
            'contact.*.contact_type' => 'nullable|string|max:255',
            'contact.*.contact' => 'nullable|string|max:255',
            'contact.*.is_preferred' => 'nullable|in:Yes,No',

            // Email
            'email' => 'nullable|array',
            'email.*.email_type' => 'nullable|string|max:255',
            'email.*.email' => 'nullable|email|max:255',
            'email.*.is_preferred' => 'nullable|in:Yes,No',

            // License
            'license' => 'nullable|array',
            'license.*.license_type' => 'nullable|string|max:255',
            'license.*.license_no' => 'nullable|string|max:255',
            'license.*.place_of_issue' => 'nullable|string|max:255',
            'license.*.date_of_issue' => 'nullable|date',
            'license.*.valid_upto' => 'nullable|date',
            'license.*.is_preferred' => 'nullable|in:Yes,No',

            // Directors (Multi)
            'directors' => 'nullable|array',
            'directors.*.id' => 'nullable',
            'directors.*.name' => 'nullable|string|max:255',
            'directors.*.cnic' => 'nullable|string|max:255',
            'directors.*.cnic_expiry' => 'nullable|date',
            'directors.*.shares_percent' => 'nullable|numeric|min:0',
            'directors.*.cnic_front' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'directors.*.cnic_back' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'directors.*.photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'directors.*.detail' => 'nullable|string',

            // Banks (Multi)
            'banks' => 'nullable|array',
            'banks.*.bank_name' => 'nullable|string|max:255',
            'banks.*.account_title' => 'nullable|string|max:255',
            'banks.*.branch' => 'nullable|string|max:255',
            'banks.*.account_number' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        $newFiles = [];

        try {
            $data = $request->except([
                'company_logo',
                'company_stamp',
                'letter_head_header',
                'letter_head_footer',
                'company_signature',
                'ceo_cnic_front',
                'ceo_cnic_back',
                'address',
                'contact',
                'email',
                'license',
                'directors',
                'banks',
                'login_email',
                'login_password',
                '_token',
                '_method',
            ]);

            $fileFields = [
                'company_logo' => 'companies/logo',
                'company_stamp' => 'companies/stamp',
                'letter_head_header' => 'companies/letter_head/header',
                'letter_head_footer' => 'companies/letter_head/footer',
                'company_signature' => 'companies/signature',
                'ceo_cnic_front' => 'companies/ceo',
                'ceo_cnic_back' => 'companies/ceo',
            ];

            $oldFilesToDelete = [];

            foreach ($fileFields as $field => $path) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store($path, 'public');
                    $newFiles[] = $data[$field];
                    if ($company->$field) {
                        $oldFilesToDelete[] = $company->$field;
                    }
                }
            }

            $company->update($data);

            if ($request->filled('login_email')) {
                $login = $company->login ?? new CompanyLogin(['company_id' => $company->id]);
                $login->email = $request->login_email;

                if ($request->filled('login_password')) {
                    $login->password = Hash::make($request->login_password);
                    $login->save();
                    Mail::to($login->email)->send(
                        new CompanyLoginMail($login->email, $request->login_password)
                    );
                } else {
                    $login->save();
                }
            }

            // Sync repeatable details
            $company->addresses()->delete();
            $company->contactNumbers()->delete();
            $company->emails()->delete();
            $company->licenses()->delete();

            foreach ($request->input('address', []) as $addr) {
                if (empty($addr['address_of']) && empty($addr['address'])) continue;
                $company->addresses()->create([
                    'address_of'   => $addr['address_of'] ?? '',
                    'address'      => $addr['address'] ?? '',
                    'country'      => $addr['country'] ?? '',
                    'city'         => $addr['city'] ?? '',
                    'po_box'       => $addr['po_box'] ?? null,
                    'zip_code'     => $addr['zip_code'] ?? null,
                    'is_preferred' => ($addr['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            foreach ($request->input('contact', []) as $con) {
                if (empty($con['contact_type']) && empty($con['contact'])) continue;
                $company->contactNumbers()->create([
                    'contact_type' => $con['contact_type'] ?? '',
                    'contact'      => $con['contact'] ?? '',
                    'is_preferred' => ($con['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            foreach ($request->input('email', []) as $em) {
                if (empty($em['email_type']) && empty($em['email'])) continue;
                $company->emails()->create([
                    'email_type'   => $em['email_type'] ?? '',
                    'email'        => $em['email'] ?? '',
                    'is_preferred' => ($em['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            foreach ($request->input('license', []) as $lic) {
                if (empty($lic['license_type']) && empty($lic['license_no'])) continue;
                $company->licenses()->create([
                    'license_type'   => $lic['license_type'] ?? '',
                    'license_no'     => $lic['license_no'] ?? '',
                    'place_of_issue' => $lic['place_of_issue'] ?? '',
                    'date_of_issue'  => $lic['date_of_issue'] ?? null,
                    'valid_upto'     => $lic['valid_upto'] ?? null,
                    'is_preferred'   => ($lic['is_preferred'] ?? 'No') === 'Yes',
                ]);
            }

            // Sync Directors (with file preservation)
            $directorsData = $request->input('directors', []);
            $directorsFiles = $request->file('directors', []);
            $submittedDirectorIds = [];

            foreach ($directorsData as $i => $dir) {
                if (empty($dir['name']) && empty($dir['cnic'])) continue;

                $dirId = $dir['id'] ?? null;
                $dirData = [
                    'name' => $dir['name'] ?? '',
                    'cnic' => $dir['cnic'] ?? '',
                    'cnic_expiry' => $dir['cnic_expiry'] ?? null,
                    'shares_percent' => isset($dir['shares_percent']) && $dir['shares_percent'] !== '' ? (float) $dir['shares_percent'] : null,
                    'detail' => $dir['detail'] ?? '',
                ];

                $existingDirector = null;
                if ($dirId) {
                    $existingDirector = $company->directors()->find($dirId);
                }

                // Handle file updates
                if (isset($directorsFiles[$i]['cnic_front'])) {
                    $dirData['cnic_front'] = $directorsFiles[$i]['cnic_front']->store('companies/directors/cnic_front', 'public');
                    if ($existingDirector && $existingDirector->cnic_front) {
                        $oldFilesToDelete[] = $existingDirector->cnic_front;
                    }
                }
                if (isset($directorsFiles[$i]['cnic_back'])) {
                    $dirData['cnic_back'] = $directorsFiles[$i]['cnic_back']->store('companies/directors/cnic_back', 'public');
                    if ($existingDirector && $existingDirector->cnic_back) {
                        $oldFilesToDelete[] = $existingDirector->cnic_back;
                    }
                }
                if (isset($directorsFiles[$i]['photo'])) {
                    $dirData['photo'] = $directorsFiles[$i]['photo']->store('companies/directors/photo', 'public');
                    if ($existingDirector && $existingDirector->photo) {
                        $oldFilesToDelete[] = $existingDirector->photo;
                    }
                }

                if ($existingDirector) {
                    $existingDirector->update($dirData);
                    $submittedDirectorIds[] = $existingDirector->id;
                } else {
                    $newDir = $company->directors()->create($dirData);
                    $submittedDirectorIds[] = $newDir->id;
                }
            }

            // Delete old directors not submitted and their files
            $directorsToDelete = $company->directors()->whereNotIn('id', $submittedDirectorIds)->get();
            foreach ($directorsToDelete as $d) {
                if ($d->cnic_front) $oldFilesToDelete[] = $d->cnic_front;
                if ($d->cnic_back) $oldFilesToDelete[] = $d->cnic_back;
                if ($d->photo) $oldFilesToDelete[] = $d->photo;
                $d->delete();
            }

            // Sync Banks (delete and recreate since no files are attached)
            $company->banks()->delete();
            foreach ($request->input('banks', []) as $bank) {
                if (empty($bank['bank_name']) && empty($bank['account_number'])) continue;
                $company->banks()->create([
                    'bank_name' => $bank['bank_name'] ?? '',
                    'account_title' => $bank['account_title'] ?? '',
                    'branch' => $bank['branch'] ?? '',
                    'account_number' => $bank['account_number'] ?? '',
                ]);
            }

            foreach ($oldFilesToDelete as $oldFile) {
                if (Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            DB::commit();
            return redirect()->route('company.index')->with('success', 'Company updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            foreach ($newFiles as $file) {
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return redirect()
                ->back()
                ->with('success', 'Company moved to trash successfully.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Something went wrong. ' . $e->getMessage());
        }
    }

    public function trash()
    {
        $companies = Company::onlyTrashed()->get();
        return view('companies.trash', compact('companies'));
    }

    public function restore($id)
    {
        try {
            $company = Company::onlyTrashed()->findOrFail($id);
            $company->restore();

            return redirect()
                ->route('company.index')
                ->with('success', 'Company restored successfully.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Something went wrong. ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $company = Company::with(['addresses', 'contactNumbers', 'emails', 'licenses', 'login', 'directors', 'banks'])
            ->findOrFail($id);

        return view('companies.show', compact('company'));
    }
}
