@extends('layout.master')
@section('title', 'Companies')
@section('header-title', 'Companies')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="row mt-5">
                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-uppercase">All Companies</h6>
                        <div class="mb-1 d-flex gap-2">
                            {{-- @can('company_create') --}}
                            <a href="{{ route('company.create') }}"
                                class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                                style="font-size: 12px; padding: 4px 12px;">
                                <i data-feather="plus" style="width:14px; height:14px;"></i>
                                Add Company
                            </a>
                            {{-- @endcan --}}
                            {{-- @can('company_trash_view') --}}
                            <a href="{{ route('company.trash') }}"
                                class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                                style="font-size: 12px; padding: 4px 12px;">
                                <i data-feather="trash-2" style="width:14px; height:14px;"></i>
                                Trash
                                <span class="badge bg-danger text-white"
                                    style="font-size: 10px;">{{ $trashcount ?? 0 }}</span>
                            </a>
                            {{-- @endcan --}}
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
                                    @foreach ($companies as $company)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $company->company_name ?? 'N/A' }}</td>
                                            <td>{{ $company->company_code ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($company->established_on)->format('d-M-Y') }}</td>
                                            <td class="text-center text-center">
                                                <div class="d-inline-flex gap-2">
                                                    {{-- @can('company_show') --}}
                                                    <a href="{{ route('company.show', $company->id) }}"
                                                        class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center">
                                                        Show
                                                    </a>
                                                    {{-- @endcan --}}
                                                    {{-- @can('company_edit') --}}
                                                    <a href="{{ route('company.edit', $company->id) }}"
                                                        class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center">
                                                        Edit
                                                    </a>
                                                    {{-- @endcan --}}
                                                    {{-- @can('company_destroy') --}}
                                                    <form action="{{ route('company.delete', $company->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this Company?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center">
                                                            Delete
                                                        </button>
                                                    </form>
                                                    {{-- @endcan --}}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
