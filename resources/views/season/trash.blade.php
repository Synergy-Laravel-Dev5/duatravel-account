@extends('layout.master')

@section('title', 'Season Trash')

@section('content')
<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Title & Back -->
            <div class="py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-18 fw-semibold m-0 text-dark">
                        <i class="mdi mdi-delete-outline me-1 text-danger"></i> Season / <strong>Trash</strong>
                    </h4>
                    <p class="text-muted fs-13 mb-0">Restore deleted seasons or permanently remove them.</p>
                </div>
                <a href="{{ route('season.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Seasons
                </a>
            </div>

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Season Name</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    <th>Deleted At</th>
                                    <th class="text-end" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($seasons as $season)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $season->name }}</strong></td>
                                        <td>
                                            @if($season->season_type === 'hajj')
                                                <span class="badge bg-dark">🕋 HAJJ</span>
                                            @else
                                                <span class="badge" style="background-color: #d84816; color: white;">🕌 UMRAH</span>
                                            @endif
                                        </td>
                                        <td>{{ $season->year }}</td>
                                        <td>{{ $season->deleted_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('season.restore', $season->id) }}" class="btn btn-sm btn-outline-success" title="Restore Season">
                                                    <i class="mdi mdi-restore me-1"></i> Restore
                                                </a>
                                                <form action="{{ route('season.force-delete', $season->id) }}" method="POST" onsubmit="return confirm('Permanently delete season \'{{ $season->name }}\'? This cannot be undone!')" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Permanently Delete">
                                                        <i class="mdi mdi-delete-forever"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-delete-empty fs-24 d-block mb-1"></i>
                                            Trash is empty.
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
