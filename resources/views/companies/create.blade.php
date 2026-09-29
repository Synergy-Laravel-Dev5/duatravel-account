@extends('layout.master')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="row" style="margin-top: 10px">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form
                                    action="{{ isset($company) && $company ? route('company.update', $company->id) : route('company.store') }}"
                                    method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @if (isset($company) && $company)
                                        @method('PUT')
                                    @endif
                                    @if ($errors->has('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ $errors->first('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    @endif
                                    <div class="tab-content">
                                        <ul class="nav nav-tabs mb-4" id="companyTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="company-tab" data-bs-toggle="tab"
                                                    data-bs-target="#company-details" type="button">
                                                    Company Details
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab"
                                                    data-bs-target="#contact-details" type="button">
                                                    Contact Details
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="licence-tab" data-bs-toggle="tab"
                                                    data-bs-target="#licence-details" type="button">
                                                    Licence Details
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="login-tab" data-bs-toggle="tab"
                                                    data-bs-target="#login-details" type="button">
                                                    Login Details
                                                </button>
                                            </li>
                                        </ul>
                                        <div class="tab-pane fade show active" id="company-details">
                                            <h5 class="mb-3"><b>Company Details</b></h5>
                                            <div class="row">
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="company_name"><b>Company Name</b><span
                                                                class="text-danger"></span></label>
                                                        <input type="text" id="company_name" name="company_name"
                                                            placeholder="Enter Company Name"
                                                            value="{{ old('company_name', $company->company_name ?? '') }}"
                                                            class="form-control">
                                                        @error('company_name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="company_code"><b>Company Code</b><span
                                                                class="text-danger"></span></label>
                                                        <input type="text" id="company_code" name="company_code"
                                                            placeholder="Enter Company Code"
                                                            value="{{ old('company_code', $company->company_code ?? '') }}"
                                                            class="form-control">
                                                        @error('company_code')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="currency_type">
                                                            <b>Currency Type</b><span class="text-danger"></span>
                                                        </label>

                                                        <select id="currency_type" name="currency_type"
                                                            class="form-control">
                                                            <option value="">Select Currency</option>

                                                            @foreach (\App\Enums\CurrencyType::options() as $value => $label)
                                                                <option value="{{ $value }}"
                                                                    {{ old('currency_type', isset($company) ? $company->currency_type?->value : '') == $value ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        @error('currency_type')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <label>Year <span class="text-danger">*</span></label>
                                                    <input type="number" name="company_year" class="form-control"
                                                        value="{{ old('company_year', $company->company_year ?? date('Y')) }}" min="2000"
                                                        max="{{ date('Y') + 5 }}">
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="established_on"><b>Established On</b><span
                                                                class="text-danger"></span></label>
                                                        <input type="date" id="established_on" name="established_on"
                                                            value="{{ old('established_on', optional($company->established_on ?? null)->format('Y-m-d')) }}"
                                                            class="form-control">
                                                        @error('established_on')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="quota"><b>Quota</b><span
                                                                class="text-danger"></span></label>
                                                        <input type="number" id="quota" name="quota"
                                                            placeholder="Enter Quota"
                                                            value="{{ old('quota', $company->quota ?? '') }}"
                                                            class="form-control" {{ isset($company) ? 'readonly' : '' }}>
                                                        @error('quota')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="website"><b>Website</b></label>
                                                        <input type="text" id="website" name="website"
                                                            placeholder="Enter Website"
                                                            value="{{ old('website', $company->website ?? '') }}"
                                                            class="form-control">
                                                        @error('website')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="company_status_on_establishment"><b>Status on
                                                                Establishment</b></label>
                                                        <input type="text" id="company_status_on_establishment"
                                                            name="company_status_on_establishment"
                                                            placeholder="Enter Status on Establishment"
                                                            value="{{ old('company_status_on_establishment', $company->company_status_on_establishment ?? '') }}"
                                                            class="form-control">
                                                        @error('company_status_on_establishment')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="current_company_status"><b>Current Company
                                                                Status</b></label>
                                                        <input type="text" id="current_company_status"
                                                            name="current_company_status"
                                                            placeholder="Enter Current Status"
                                                            value="{{ old('current_company_status', $company->current_company_status ?? '') }}"
                                                            class="form-control">
                                                        @error('current_company_status')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                @php
                                                    $imageFields = [
                                                        'company_logo' => 'Company Logo',
                                                        'company_stamp' => 'Company Stamp',
                                                        'letter_head_header' => 'Letter Head Header',
                                                        'letter_head_footer' => 'Letter Head Footer',
                                                        'company_signature' => 'Company Signature',
                                                    ];
                                                @endphp
                                                @foreach ($imageFields as $field => $label)
                                                    <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                        <div class="mb-3">
                                                            <label
                                                                for="{{ $field }}"><b>{{ $label }}</b></label>
                                                            <input type="file" id="{{ $field }}"
                                                                name="{{ $field }}" accept="image/*"
                                                                class="form-control image-input"
                                                                data-preview="preview_{{ $field }}">
                                                            <div class="mt-2">
                                                                <img id="preview_{{ $field }}"
                                                                    src="{{ !empty($company?->$field) ? Storage::url($company->$field) : '' }}"
                                                                    style="width:150px;height:150px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;{{ !empty($company?->$field) ? '' : 'display:none;' }}"
                                                                    onerror="this.style.display='none';">
                                                            </div>
                                                            @error($field)
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <h5 class="mb-3"><b>Company Registrations</b></h5>
                                            <div class="row">
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="mohra_enrollment_no"><b>Mora Enrollment No</b></label>
                                                        <input type="text" id="mohra_enrollment_no"
                                                            name="mohra_enrollment_no"
                                                            placeholder="Enter Mora Enrollment No"
                                                            value="{{ old('mohra_enrollment_no', $company->mohra_enrollment_no ?? '') }}"
                                                            class="form-control">
                                                        @error('mohra_enrollment_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="munazzam_no"><b>Munazzam No</b></label>
                                                        <input type="text" id="munazzam_no" name="munazzam_no"
                                                            placeholder="Enter Munazzam No"
                                                            value="{{ old('munazzam_no', $company->munazzam_no ?? '') }}"
                                                            class="form-control">
                                                        @error('munazzam_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="cluster_enrollment_no"><b>Cluster Enrollment
                                                                No</b></label>
                                                        <input type="text" id="cluster_enrollment_no"
                                                            name="cluster_enrollment_no"
                                                            placeholder="Enter Cluster Enrollment No"
                                                            value="{{ old('cluster_enrollment_no', $company->cluster_enrollment_no ?? '') }}"
                                                            class="form-control">
                                                        @error('cluster_enrollment_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="dts_no"><b>DTS No</b></label>
                                                        <input type="text" id="dts_no" name="dts_no"
                                                            placeholder="Enter DTS No"
                                                            value="{{ old('dts_no', $company->dts_no ?? '') }}"
                                                            class="form-control">
                                                        @error('dts_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="dts_expiry"><b>DTS Expiry</b></label>
                                                        <input type="date" id="dts_expiry" name="dts_expiry"
                                                            value="{{ old('dts_expiry', optional($company->dts_expiry ?? null)->format('Y-m-d')) }}"
                                                            class="form-control">
                                                        @error('dts_expiry')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="iata_no"><b>IATA No</b></label>
                                                        <input type="text" id="iata_no" name="iata_no"
                                                            placeholder="Enter IATA No"
                                                            value="{{ old('iata_no', $company->iata_no ?? '') }}"
                                                            class="form-control">
                                                        @error('iata_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="iata_expiry"><b>IATA Expiry</b></label>
                                                        <input type="date" id="iata_expiry" name="iata_expiry"
                                                            value="{{ old('iata_expiry', optional($company->iata_expiry ?? null)->format('Y-m-d')) }}"
                                                            class="form-control">
                                                        @error('iata_expiry')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="ntn"><b>NTN</b></label>
                                                        <input type="text" id="ntn" name="ntn"
                                                            placeholder="Enter NTN"
                                                            value="{{ old('ntn', $company->ntn ?? '') }}"
                                                            class="form-control">
                                                        @error('ntn')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <h5 class="mb-3"><b>Chief Executive Details</b></h5>
                                            <div class="row">
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                                    <div class="mb-3">
                                                        <label for="ceo_name"><b>CEO Name</b></label>
                                                        <input type="text" id="ceo_name" name="ceo_name"
                                                            placeholder="Enter CEO Name"
                                                            value="{{ old('ceo_name', $company->ceo_name ?? '') }}"
                                                            class="form-control">
                                                        @error('ceo_name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                                    <div class="mb-3">
                                                        <label for="ceo_cnic_front"><b>CNIC Front</b></label>
                                                        <input type="file" id="ceo_cnic_front" name="ceo_cnic_front"
                                                            accept="image/*" class="form-control image-input"
                                                            data-preview="preview_ceo_cnic_front">
                                                        <div class="mt-2">
                                                            <img id="preview_ceo_cnic_front"
                                                                src="{{ !empty($company->ceo_cnic_front) ? Storage::url($company->ceo_cnic_front) : '' }}"
                                                                style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;{{ !empty($company->ceo_cnic_front) ? '' : 'display:none;' }}"
                                                                onerror="this.style.display='none';">
                                                        </div>
                                                        @error('ceo_cnic_front')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                                    <div class="mb-3">
                                                        <label for="ceo_cnic_back"><b>CNIC Back</b></label>
                                                        <input type="file" id="ceo_cnic_back" name="ceo_cnic_back"
                                                            accept="image/*" class="form-control image-input"
                                                            data-preview="preview_ceo_cnic_back">
                                                        <div class="mt-2">
                                                            <img id="preview_ceo_cnic_back"
                                                                src="{{ !empty($company->ceo_cnic_back) ? Storage::url($company->ceo_cnic_back) : '' }}"
                                                                style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;{{ !empty($company->ceo_cnic_back) ? '' : 'display:none;' }}"
                                                                onerror="this.style.display='none';">
                                                        </div>
                                                        @error('ceo_cnic_back')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                                    <div class="mb-3">
                                                        <label for="ceo_shares_percent"><b>Percentage of Shares
                                                                (%)</b></label>
                                                        <input type="text" id="ceo_shares_percent"
                                                            name="ceo_shares_percent" placeholder="e.g. 50%"
                                                            value="{{ old('ceo_shares_percent', $company->ceo_shares_percent ?? '') }}"
                                                            class="form-control">
                                                        @error('ceo_shares_percent')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0"><b>Director Details</b></h5>
                                            </div>
                                            <div id="directors-container">
                                                @php
                                                    $dirs = old(
                                                        'directors',
                                                        (isset($company) && $company->directors->isNotEmpty())
                                                            ? $company->directors->toArray()
                                                            : [
                                                                [
                                                                    'name' => '',
                                                                    'cnic' => '',
                                                                    'cnic_expiry' => '',
                                                                    'cnic_front' => '',
                                                                    'cnic_back' => '',
                                                                    'photo' => '',
                                                                    'detail' => '',
                                                                ],
                                                            ],
                                                    );
                                                @endphp
                                                @foreach ($dirs as $i => $dir)
                                                    <div class="row director-row border-bottom pb-3 mb-3"
                                                        data-index="{{ $i }}">
                                                        <input type="hidden" name="directors[{{ $i }}][id]"
                                                            value="{{ $dir['id'] ?? '' }}">
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>Director Name</b></label>
                                                            <input type="text"
                                                                name="directors[{{ $i }}][name]"
                                                                value="{{ $dir['name'] ?? '' }}" class="form-control"
                                                                placeholder="Enter Director Name">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>Director CNIC</b></label>
                                                            <input type="text"
                                                                name="directors[{{ $i }}][cnic]"
                                                                value="{{ $dir['cnic'] ?? '' }}" class="form-control"
                                                                placeholder="Enter Director CNIC">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>Director CNIC Expiry</b></label>
                                                            <input type="date"
                                                                name="directors[{{ $i }}][cnic_expiry]"
                                                                value="{{ !empty($dir['cnic_expiry']) ? \Carbon\Carbon::parse($dir['cnic_expiry'])->format('Y-m-d') : '' }}"
                                                                class="form-control">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>CNIC Front</b></label>
                                                            <input type="file"
                                                                name="directors[{{ $i }}][cnic_front]"
                                                                accept="image/*" class="form-control image-input"
                                                                data-preview="preview_dir_cnic_front_{{ $i }}">
                                                            <div class="mt-2">
                                                                <img id="preview_dir_cnic_front_{{ $i }}"
                                                                    src="{{ !empty($dir['cnic_front']) ? Storage::url($dir['cnic_front']) : '' }}"
                                                                    style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;{{ !empty($dir['cnic_front']) ? '' : 'display:none;' }}"
                                                                    onerror="this.style.display='none';">
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>CNIC Back</b></label>
                                                            <input type="file"
                                                                name="directors[{{ $i }}][cnic_back]"
                                                                accept="image/*" class="form-control image-input"
                                                                data-preview="preview_dir_cnic_back_{{ $i }}">
                                                            <div class="mt-2">
                                                                <img id="preview_dir_cnic_back_{{ $i }}"
                                                                    src="{{ !empty($dir['cnic_back']) ? Storage::url($dir['cnic_back']) : '' }}"
                                                                    style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;{{ !empty($dir['cnic_back']) ? '' : 'display:none;' }}"
                                                                    onerror="this.style.display='none';">
                                                            </div>

                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">

                                                            <div class="mb-3">
                                                                <label><b>Percentage of Shares (%)</b></label>

                                                                <input type="number" step="0.01"
                                                                    name="directors[{{ $i }}][shares_percent]"
                                                                    value="{{ old("directors.$i.shares_percent", $dir['shares_percent'] ?? '') }}"
                                                                    class="form-control" placeholder="Enter Shares">

                                                                @error("directors.$i.shares_percent")
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror

                                                                @error("directors.$i.shares_percent")
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>Director Photo</b></label>
                                                            <input type="file"
                                                                name="directors[{{ $i }}][photo]"
                                                                accept="image/*" class="form-control image-input"
                                                                data-preview="preview_dir_photo_{{ $i }}">
                                                            <div class="mt-2">
                                                                <img id="preview_dir_photo_{{ $i }}"
                                                                    src="{{ !empty($dir['photo']) ? Storage::url($dir['photo']) : '' }}"
                                                                    style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;{{ !empty($dir['photo']) ? '' : 'display:none;' }}"
                                                                    onerror="this.style.display='none';">
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-sm-11 mb-2">
                                                            <label><b>Director Detail</b></label>
                                                            <textarea name="directors[{{ $i }}][detail]" rows="2" class="form-control"
                                                                placeholder="Enter Director Detail">{{ $dir['detail'] ?? '' }}</textarea>
                                                        </div>
                                                        <div class="col-12 col-sm-1 d-flex align-items-end mb-2">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-btn"
                                                                onclick="removeRow(this)">
                                                                <i class="material-icons-outlined"
                                                                    style="font-size:14px;">delete</i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mb-4">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="addRow('directors')">
                                                    Add Director
                                                </button>
                                            </div>

                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0"><b>Bank Details</b></h5>
                                            </div>
                                            <div id="banks-container">
                                                @php
                                                    $banksList = old(
                                                        'banks',
                                                        (isset($company) && $company->banks->isNotEmpty())
                                                            ? $company->banks->toArray()
                                                            : [
                                                                [
                                                                    'bank_name' => '',
                                                                    'account_title' => '',
                                                                    'branch' => '',
                                                                    'account_number' => '',
                                                                ],
                                                            ],
                                                    );
                                                @endphp
                                                @foreach ($banksList as $i => $bank)
                                                    <div class="row bank-row border-bottom pb-2 mb-2"
                                                        data-index="{{ $i }}">
                                                        <div class="col-12 col-sm-6 col-md-3 mb-2">
                                                            <label><b>Bank Name</b></label>
                                                            <input type="text"
                                                                name="banks[{{ $i }}][bank_name]"
                                                                value="{{ $bank['bank_name'] ?? '' }}"
                                                                class="form-control" placeholder="Enter Bank Name">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-3 mb-2">
                                                            <label><b>Account Title</b></label>
                                                            <input type="text"
                                                                name="banks[{{ $i }}][account_title]"
                                                                value="{{ $bank['account_title'] ?? '' }}"
                                                                class="form-control" placeholder="Enter Account Title">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-3 mb-2">
                                                            <label><b>Branch</b></label>
                                                            <input type="text"
                                                                name="banks[{{ $i }}][branch]"
                                                                value="{{ $bank['branch'] ?? '' }}" class="form-control"
                                                                placeholder="Enter Branch">
                                                        </div>
                                                        <div class="col-12 col-sm-5 col-md-2 mb-2">
                                                            <label><b>Account Number</b></label>
                                                            <input type="text"
                                                                name="banks[{{ $i }}][account_number]"
                                                                value="{{ $bank['account_number'] ?? '' }}"
                                                                class="form-control" placeholder="Enter Account Number">
                                                        </div>
                                                        <div class="col-12 col-sm-1 d-flex align-items-end mb-2">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-btn"
                                                                onclick="removeRow(this)">
                                                                <i class="material-icons-outlined"
                                                                    style="font-size:14px;">delete</i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mb-4">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="addRow('banks')">
                                                    Add Bank Details
                                                </button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="contact-details">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0"><b>Address</b></h5>
                                            </div>
                                            <div id="address-container">
                                                @php $addresses = old('address', (isset($company) && $company->addresses->isNotEmpty()) ? $company->addresses->toArray() : [['address_of'=>'','address'=>'','country'=>'','city'=>'','po_box'=>'','zip_code'=>'','is_preferred'=>false]]); @endphp
                                                @foreach ($addresses as $i => $addr)
                                                    <div class="row address-row border-bottom pb-2 mb-2"
                                                        data-index="{{ $i }}">
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>Address Of</b><span
                                                                    class="text-danger"></span></label>
                                                            <select name="address[{{ $i }}][address_of]"
                                                                class="form-control">
                                                                <option value="">Select...</option>
                                                                @foreach (['Head Office', 'Branch Office', 'Site Office', 'Warehouse'] as $opt)
                                                                    <option value="{{ $opt }}"
                                                                        {{ ($addr['address_of'] ?? '') == $opt ? 'selected' : '' }}>
                                                                        {{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>Address</b><span class="text-danger"></span></label>
                                                            <input type="text"
                                                                name="address[{{ $i }}][address]"
                                                                value="{{ $addr['address'] ?? '' }}"
                                                                class="form-control" placeholder="Enter Address">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>Country</b><span class="text-danger"></span></label>
                                                            <input type="text"
                                                                name="address[{{ $i }}][country]"
                                                                value="{{ $addr['country'] ?? '' }}"
                                                                class="form-control" placeholder="Enter Country">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-3">
                                                            <label><b>City</b><span class="text-danger"></span></label>
                                                            <input type="text"
                                                                name="address[{{ $i }}][city]"
                                                                value="{{ $addr['city'] ?? '' }}" class="form-control"
                                                                placeholder="Enter City">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-3">
                                                            <label><b>PO Box</b></label>
                                                            <input type="text"
                                                                name="address[{{ $i }}][po_box]"
                                                                value="{{ $addr['po_box'] ?? '' }}"
                                                                class="form-control">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-3">
                                                            <label><b>Zip</b></label>
                                                            <input type="text"
                                                                name="address[{{ $i }}][zip_code]"
                                                                value="{{ $addr['zip_code'] ?? '' }}"
                                                                class="form-control">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-3">
                                                            <label><b>Preferred?</b></label>
                                                            <select name="address[{{ $i }}][is_preferred]"
                                                                class="form-control">
                                                                <option value="Yes"
                                                                    {{ (($addr['is_preferred'] ?? null) == 1 || ($addr['is_preferred'] ?? null) === 'Yes') ? 'selected' : '' }}>
                                                                    Yes</option>
                                                                <option value="No"
                                                                    {{ (($addr['is_preferred'] ?? null) == 0 || ($addr['is_preferred'] ?? null) === 'No' || is_null($addr['is_preferred'] ?? null)) ? 'selected' : '' }}>
                                                                    No
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-3 col-md-1 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-btn"
                                                                onclick="removeRow(this)">
                                                                <i class="material-icons-outlined"
                                                                    style="font-size:14px;">delete</i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mb-4">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="addRow('address')">
                                                    Add
                                                </button>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0"><b>Contact</b></h5>
                                            </div>
                                            <div id="contact-container">
                                                @php $contacts = old('contact', (isset($company) && $company->contactNumbers->isNotEmpty()) ? $company->contactNumbers->toArray() : [['contact_type'=>'','contact'=>'','is_preferred'=>false]]); @endphp
                                                @foreach ($contacts as $i => $con)
                                                    <div class="row contact-row border-bottom pb-2 mb-2"
                                                        data-index="{{ $i }}">
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>Contact Type</b><span
                                                                    class="text-danger"></span></label>
                                                            <select name="contact[{{ $i }}][contact_type]"
                                                                class="form-control">
                                                                <option value="">Select...</option>
                                                                @foreach (['Mobile', 'Landline', 'Fax', 'WhatsApp'] as $opt)
                                                                    <option value="{{ $opt }}"
                                                                        {{ ($con['contact_type'] ?? '') == $opt ? 'selected' : '' }}>
                                                                        {{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-5">
                                                            <label><b>Contact</b><span class="text-danger"></span></label>
                                                            <input type="text"
                                                                name="contact[{{ $i }}][contact]"
                                                                value="{{ $con['contact'] ?? '' }}" class="form-control"
                                                                placeholder="Enter Contact">
                                                        </div>
                                                        <div class="col-9 col-md-2">
                                                            <label><b>Preferred?</b></label>
                                                            <select name="contact[{{ $i }}][is_preferred]"
                                                                class="form-control">
                                                                <option value="Yes"
                                                                    {{ (($con['is_preferred'] ?? null) == 1 || ($con['is_preferred'] ?? null) === 'Yes') ? 'selected' : '' }}>
                                                                    Yes</option>
                                                                <option value="No"
                                                                    {{ (($con['is_preferred'] ?? null) == 0 || ($con['is_preferred'] ?? null) === 'No' || is_null($con['is_preferred'] ?? null)) ? 'selected' : '' }}>
                                                                    No
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-3 col-md-1 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-btn"
                                                                onclick="removeRow(this)">
                                                                <i class="material-icons-outlined"
                                                                    style="font-size:14px;">delete</i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mb-4">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="addRow('contact')">
                                                    Add
                                                </button>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0"><b>Email</b></h5>
                                            </div>
                                            <div id="email-container">
                                                @php $emails = old('email', (isset($company) && $company->emails->isNotEmpty()) ? $company->emails->toArray() : [['email_type'=>'','email'=>'','is_preferred'=>false]]); @endphp
                                                @foreach ($emails as $i => $em)
                                                    <div class="row email-row border-bottom pb-2 mb-2"
                                                        data-index="{{ $i }}">
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>Email Type</b><span
                                                                    class="text-danger"></span></label>
                                                            <select name="email[{{ $i }}][email_type]"
                                                                class="form-control">
                                                                <option value="">Select...</option>
                                                                @foreach (['Official', 'Personal', 'Accounts', 'Billing'] as $opt)
                                                                    <option value="{{ $opt }}"
                                                                        {{ ($em['email_type'] ?? '') == $opt ? 'selected' : '' }}>
                                                                        {{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-5">
                                                            <label><b>Email</b><span class="text-danger"></span></label>
                                                            <input type="email"
                                                                name="email[{{ $i }}][email]"
                                                                value="{{ $em['email'] ?? '' }}" class="form-control"
                                                                placeholder="Enter Email Address">
                                                        </div>
                                                        <div class="col-9 col-md-2">
                                                            <label><b>Preferred?</b></label>
                                                            <select name="email[{{ $i }}][is_preferred]"
                                                                class="form-control">
                                                                <option value="Yes"
                                                                    {{ (($em['is_preferred'] ?? null) == 1 || ($em['is_preferred'] ?? null) === 'Yes') ? 'selected' : '' }}>
                                                                    Yes</option>
                                                                <option value="No"
                                                                    {{ (($em['is_preferred'] ?? null) == 0 || ($em['is_preferred'] ?? null) === 'No' || is_null($em['is_preferred'] ?? null)) ? 'selected' : '' }}>
                                                                    No
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-3 col-md-1 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-btn"
                                                                onclick="removeRow(this)">
                                                                <i class="material-icons-outlined"
                                                                    style="font-size:14px;">delete</i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mb-4">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="addRow('email')">
                                                    Add
                                                </button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="licence-details">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0"><b>Licenses</b></h5>
                                            </div>
                                            <div id="license-container">
                                                @php $licenses = old('license', (isset($company) && $company->licenses->isNotEmpty()) ? $company->licenses->toArray() : [['license_type'=>'','license_no'=>'','place_of_issue'=>'','date_of_issue'=>'','valid_upto'=>'','is_preferred'=>false]]); @endphp
                                                @foreach ($licenses as $i => $lic)
                                                    <div class="row license-row border-bottom pb-2 mb-2"
                                                        data-index="{{ $i }}">
                                                        <div class="col-12 col-sm-6 col-md-4 mb-2">
                                                            <label><b>License Type</b><span
                                                                    class="text-danger"></span></label>
                                                            <select name="license[{{ $i }}][license_type]"
                                                                class="form-control">
                                                                <option value="">Select...</option>
                                                                @foreach (['Form A', 'Form 9', 'Form 21', 'Article and Memorandum', 'Incorporation'] as $opt)
                                                                    <option value="{{ $opt }}"
                                                                        {{ ($lic['license_type'] ?? '') == $opt ? 'selected' : '' }}>
                                                                        {{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>License No</b><span
                                                                    class="text-danger"></span></label>
                                                            <input type="text"
                                                                name="license[{{ $i }}][license_no]"
                                                                value="{{ $lic['license_no'] ?? '' }}"
                                                                class="form-control" placeholder="Enter License No">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>Place of Issue</b><span
                                                                    class="text-danger"></span></label>
                                                            <input type="text"
                                                                name="license[{{ $i }}][place_of_issue]"
                                                                value="{{ $lic['place_of_issue'] ?? '' }}"
                                                                class="form-control" placeholder="Enter Place of Issue">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>Date of Issue</b><span
                                                                    class="text-danger"></span></label>
                                                            <input type="date"
                                                                name="license[{{ $i }}][date_of_issue]"
                                                                value="{{ !empty($lic['date_of_issue']) ? \Carbon\Carbon::parse($lic['date_of_issue'])->format('Y-m-d') : '' }}"
                                                                class="form-control">
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <label><b>Valid Upto</b><span
                                                                    class="text-danger"></span></label>
                                                            <input type="date"
                                                                name="license[{{ $i }}][valid_upto]"
                                                                value="{{ !empty($lic['valid_upto']) ? \Carbon\Carbon::parse($lic['valid_upto'])->format('Y-m-d') : '' }}"
                                                                class="form-control">
                                                        </div>
                                                        <div class="col-9 col-md-4">
                                                            <label><b>Preferred?</b></label>
                                                            <select name="license[{{ $i }}][is_preferred]"
                                                                class="form-control">
                                                                <option value="Yes"
                                                                    {{ (($lic['is_preferred'] ?? null) == 1 || ($lic['is_preferred'] ?? null) === 'Yes') ? 'selected' : '' }}>
                                                                    Yes</option>
                                                                <option value="No"
                                                                    {{ (($lic['is_preferred'] ?? null) == 0 || ($lic['is_preferred'] ?? null) === 'No' || is_null($lic['is_preferred'] ?? null)) ? 'selected' : '' }}>
                                                                    No
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-3 col-md-1 d-flex align-items-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger remove-btn"
                                                                onclick="removeRow(this)">
                                                                <i class="material-icons-outlined"
                                                                    style="font-size:14px;">delete</i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mb-4">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="addRow('license')">
                                                    Add
                                                </button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="login-details">
                                            <h5 class="mb-3"><b>Login Details</b></h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Login Email <span class="text-danger">*</span></label>
                                                    <input type="email" name="login_email" class="form-control"
                                                        value="{{ old('login_email', isset($company) && $company->login ? $company->login->email : '') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Password
                                                        {{ isset($company) ? '(leave blank to keep same)' : '' }}</label>
                                                    <input type="text" name="login_password" class="form-control"
                                                        placeholder="{{ isset($company) ? 'Leave blank to keep current password' : 'Enter password' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mt-3 d-flex justify-content-end">
                                            <button type="submit"
                                                class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-1">
                                                {{ isset($company) && $company ? 'Update Company' : 'Create Company' }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addRow(type) {
            const container = document.getElementById(type + '-container');
            const rows = container.querySelectorAll('.row');
            const newIndex = rows.length;
            const firstRow = rows[0].cloneNode(true);

            firstRow.setAttribute('data-index', newIndex);

            // Reset IDs, names and preview elements
            firstRow.querySelectorAll('input, select, textarea').forEach(function(el) {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
                }

                // Clear values
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                } else {
                    el.value = '';
                }

                // If it is a hidden id field for directors, clear it
                if (el.getAttribute('type') === 'hidden' && name && name.includes('[id]')) {
                    el.value = '';
                }

                // Update data-preview target for image inputs
                const dataPreview = el.getAttribute('data-preview');
                if (dataPreview) {
                    const newPreviewId = dataPreview.replace(/_\d+$/, '') + '_' + newIndex;
                    el.setAttribute('data-preview', newPreviewId);
                }
            });

            // Handle image elements inside cloned row
            firstRow.querySelectorAll('img').forEach(function(img) {
                const id = img.getAttribute('id');
                if (id) {
                    const newId = id.replace(/_\d+$/, '') + '_' + newIndex;
                    img.setAttribute('id', newId);
                    img.src = '';
                    img.style.display = 'none';
                }
            });

            const oldBtnCol = firstRow.querySelector('.remove-btn')?.closest('div');
            if (oldBtnCol) {
                oldBtnCol.remove();
            }

            const btnCol = document.createElement('div');
            btnCol.className = 'col-12 col-sm-1 d-flex align-items-end mb-2';
            btnCol.innerHTML =
                '<button type="button" class="btn btn-sm btn-outline-danger remove-btn" onclick="removeRow(this)"><i class="material-icons-outlined" style="font-size:14px;">delete</i></button>';

            // Adjust col class depending on row structure
            if (type === 'address' || type === 'contact' || type === 'email' || type === 'license') {
                btnCol.className = 'col-3 col-md-1 d-flex align-items-end';
            }

            firstRow.appendChild(btnCol);

            container.appendChild(firstRow);
            toggleRemoveButtons(type + '-container');
        }

        function removeRow(btn) {
            const row = btn.closest('.row');
            const container = row.parentElement;
            if (container.querySelectorAll('.row').length > 1) {
                row.remove();
                toggleRemoveButtons(container.id);
            }
        }

        function toggleRemoveButtons(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const rows = container.querySelectorAll('.row');
            rows.forEach(function(row) {
                const btn = row.querySelector('.remove-btn');
                if (!btn) return;
                btn.style.display = rows.length > 1 ? 'inline-flex' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleRemoveButtons('address-container');
            toggleRemoveButtons('contact-container');
            toggleRemoveButtons('email-container');
            toggleRemoveButtons('license-container');
            toggleRemoveButtons('directors-container');
            toggleRemoveButtons('banks-container');
        });
    </script>
    <script>
        // Event delegation for dynamically added file inputs
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('image-input')) {
                var input = e.target;
                var previewId = input.getAttribute('data-preview');
                var previewEl = document.getElementById(previewId);
                var file = e.target.files[0];

                if (file && previewEl) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        previewEl.src = event.target.result;
                        previewEl.style.display = 'inline-block';
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
@endsection
