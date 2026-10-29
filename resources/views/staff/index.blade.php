@extends('layout.master')
@section('title', 'Staff Members')
@section('content')
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                <div class="py-3 d-flex align-items-center justify-content-between">
                    <h4 class="fs-18 fw-semibold m-0">Staff Members</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('payroll.create') }}" class="btn btn-outline-success btn-sm">
                            <i class="mdi mdi-cash-multiple me-1"></i> + Generate Payroll
                        </a>
                        <a href="{{ route('staff.create') }}" class="btn btn-primary btn-sm">
                            + Add Staff
                        </a>
                    </div>
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>S:NO</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Phone</th>
                                        <th>Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staff as $emp)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $emp->first_name }} {{ $emp->last_name }}</strong></td>
                                            <td>{{ $emp->department->name ?? '-' }}</td>
                                            <td>{{ $emp->designation->name ?? '-' }}</td>
                                            <td>{{ $emp->phone ?? '-' }}</td>
                                            <td>{{ number_format($emp->salary, 2) }}</td>
                                            <td>
                                                @if ($emp->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('payroll.create', ['staff_id' => $emp->id]) }}" class="btn btn-sm btn-soft-success" title="Generate Monthly Payroll">
                                                    <i class="mdi mdi-cash-check me-1"></i> Pay
                                                </a>
                                                <a href="{{ route('staff.edit', $emp->id) }}" class="btn btn-sm btn-info" title="Edit Staff">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('staff.destroy', $emp->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this staff member?')" title="Delete Staff">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No staff found.</td>
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
