@extends('layout.master')

@section('title', 'Meal Plans Management')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <div>
                    <h4 class="fs-18 fw-semibold mb-0 text-dark">
                        <i class="mdi mdi-silverware-fork-knife text-primary me-1"></i> Meal Plans
                    </h4>
                    <p class="text-muted fs-13 mb-0">Manage meal plan packages (Room Only, Bed & Breakfast, Half Board, etc.) for hotel bookings.</p>
                </div>
                <a href="{{ route('meal-plans.create') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-plus-circle me-1"></i> Add Meal Plan
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show fs-13" role="alert">
                    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">#</th>
                                    <th>Meal Plan Name</th>
                                    <th>Code / Abbreviation</th>
                                    <th>Description</th>
                                    <th class="text-center">Status</th>
                                    <th style="width: 120px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mealPlans as $index => $mp)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <strong class="text-dark">{{ $mp->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary fw-bold font-monospace px-2 py-1 fs-12">
                                                {{ $mp->code }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-13">{{ $mp->description ?? '—' }}</td>
                                        <td class="text-center">
                                            @if($mp->status === 'active')
                                                <span class="badge bg-soft-success text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-soft-secondary text-secondary px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('meal-plans.edit', $mp->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                                <form action="{{ route('meal-plans.destroy', $mp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this meal plan?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete">
                                                        <i class="mdi mdi-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-silverware-clean fs-36 text-secondary d-block mb-1"></i>
                                            No meal plans found.
                                        </td>
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
