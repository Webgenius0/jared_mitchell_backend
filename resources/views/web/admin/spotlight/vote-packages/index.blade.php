@extends('layout.master-layout')

@section('title', 'Vote Packages')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Vote Packages</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.spotlight.vote-packages.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Create Package
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row">
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Packages</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-box-3-line fs-24 text-primary"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0">{{ $stats['total_packages'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Active</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-success">{{ $stats['total_active'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Inactive</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-close-circle-line fs-24 text-danger"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-danger">{{ $stats['total_inactive'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Vote Packages</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Name</th>
                                        <th>Votes</th>
                                        <th>Price</th>
                                        <th>Description</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Purchases</th>
                                        <th class="text-center" style="width: 160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($packages as $pkg)
                                    <tr>
                                        <td>{{ $pkg->id }}</td>
                                        <td>
                                            <strong>{{ $pkg->name }}</strong>
                                            @if($pkg->slug)
                                                <br><small class="text-muted">Slug: {{ $pkg->slug }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center"><strong>{{ number_format($pkg->votes_count) }}</strong></td>
                                        <td><strong>${{ number_format($pkg->price, 2) }}</strong></td>
                                        <td>{{ $pkg->description ?? '—' }}</td>
                                        <td class="text-center">
                                            @if($pkg->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $pkg->purchases_count }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ route('admin.spotlight.vote-packages.edit', $pkg->id) }}" class="btn btn-sm btn-soft-primary" title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </a>

                                                <form action="{{ route('admin.spotlight.vote-packages.toggle-active', $pkg->id) }}" method="POST" class="d-inline" data-confirm="{{ $pkg->is_active ? 'Deactivate' : 'Activate' }} &quot;{{ $pkg->name }}&quot;?">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-soft-{{ $pkg->is_active ? 'warning' : 'success' }}" title="{{ $pkg->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ri-{{ $pkg->is_active ? 'pause' : 'play' }}-line"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.spotlight.vote-packages.destroy', $pkg->id) }}" method="POST" class="d-inline" data-confirm="Delete &quot;{{ $pkg->name }}&quot;? This cannot be undone." data-confirm-type="danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="ri-inbox-line fs-3 d-block mb-2"></i>
                                            No vote packages found.
                                            <a href="{{ route('admin.spotlight.vote-packages.create') }}">Create the first one</a>.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $packages->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        @if(session('success'))
            Toast.success(@json(session('success')));
        @endif

        @if(session('error'))
            Toast.error(@json(session('error')));
        @endif

        @if(session('warning'))
            Toast.warning(@json(session('warning')));
        @endif

        // SweetAlert confirm for forms with data-confirm
        document.querySelectorAll('.table-responsive form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var message = form.getAttribute('data-confirm');
                var type = form.getAttribute('data-confirm-type') || 'warning';

                Alert.confirm(message, {
                    type: type,
                    confirmText: type === 'danger' ? 'Yes, delete' : type === 'warning' ? 'Yes, proceed' : 'Yes, confirm',
                }).then(function (confirmed) {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
        });

    })();
</script>
@endpush
