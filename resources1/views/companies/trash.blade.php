@extends('layout.master')
@section('title', 'Trashed Companies')
@section('header-title', 'Trashed Companies')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="row mt-5">
                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-uppercase">Trashed Companies</h6>
                        <div class="mb-1 d-flex gap-2">
                            <a href="{{ route('company.index') }}"
                                class="btn btn-secondary btn-sm d-flex align-items-center gap-1"
                                style="font-size: 12px; padding: 4px 12px;">
                                <i data-feather="arrow-left" style="width:14px; height:14px;"></i>
                                Back to Companies
                            </a>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="text-center">
                                    <tr>
                                        <th>S:NO</th>
                                        <th>Company Name</th>
                                        <th>Company Code</th>
                                        <th>Established Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    @forelse ($companies as $company)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $company->company_name ?? 'N/A' }}</td>
                                            <td>{{ $company->company_code ?? 'N/A' }}</td>
                                            <td>{{ $company->established_on ? \Carbon\Carbon::parse($company->established_on)->format('d-M-Y') : 'N/A' }}
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-2">
                                                    {{-- @can('company_restore') --}}
                                                    <a href="{{ route('company.restore', $company->id) }}"
                                                        class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center"
                                                        onclick="return confirm('Are you sure you want to restore this Company?');">
                                                        <i data-feather="rotate-ccw" style="width:16px; height:16px;"></i>
                                                    </a>
                                                    {{-- @endcan --}}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No trashed companies found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
