@extends('layout.master-layout')

@section('title', 'Contest Applications')
@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Contest Applications</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Contest Applications</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-file-list-3-line fs-24 text-primary"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-time-line fs-24 text-warning"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-warning">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Approved</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-success">{{ number_format($stats['approved']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Cancelled</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-close-circle-line fs-24 text-danger"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-danger">{{ number_format($stats['rejected']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">All Contest Applications</h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Business</th>
                                        <th>Owner</th>
                                        <th>Round Session</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th class="text-center" style="width: 180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $application)
                                    <tr>
                                        <td>{{ $loop->iteration + ($applications->currentPage() - 1) * $applications->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h6 class="fs-14 mb-1">{{ $application->business?->business_name ?? '—' }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h6 class="fs-14 mb-1">{{ $application->business?->user?->profile?->name ?? '—' }}</h6>
                                                    <p class="text-muted mb-0">{{ $application->business?->user?->email ?? '—' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $application->roundSession?->title ?? '—' }}</td>
                                        <td>
                                            @if($application->status == 'pending')
                                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                                            @elseif($application->status == 'approved')
                                                <span class="badge bg-success-subtle text-success">Approved</span>
                                            @elseif($application->status == 'rejected')
                                                <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($application->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $application->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                {{-- View --}}
                                                <button type="button" class="btn btn-sm btn-soft-info view-btn"
                                                    data-id="{{ $application->id }}" title="View Details">
                                                    <i class="ri-eye-fill"></i>
                                                </button>

                                                {{-- Approve --}}
                                                @if($application->status !== 'approved')
                                                <form action="{{ route('admin.contest-applications.approve', $application->id) }}" method="POST" class="approve-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-soft-success approve-btn" title="Approve">
                                                        <i class="ri-check-line"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                {{-- Cancel --}}
                                                @if($application->status !== 'rejected')
                                                <form action="{{ route('admin.contest-applications.cancel', $application->id) }}" method="POST" class="cancel-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-soft-warning cancel-btn" title="Cancel">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                {{-- Delete --}}
                                                <form action="{{ route('admin.contest-applications.destroy', $application->id) }}" method="POST" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger delete-btn" title="Delete">
                                                        <i class="ri-delete-bin-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No contest applications found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $applications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- View Details Modal --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contest Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer" id="viewModalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        'use strict';

        let currentApplicationId = null;

        @if (session('success'))
            Toast.success(@json(session('success')));
        @endif

        @if (session('error'))
            Toast.error(@json(session('error')));
        @endif

        @if (session('warning'))
            Toast.warning(@json(session('warning')));
        @endif

        // View Details
        $(document).on('click', '.view-btn', function() {
            currentApplicationId = $(this).data('id');
            loadApplicationDetails(currentApplicationId);
            $('#viewModal').modal('show');
        });

        function loadApplicationDetails(id) {
            $('#viewModalBody').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            $('#viewModalFooter').html(
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');

            axios.get('{{ url("contest-applications") }}/' + id)
                .then(function(res) {
                    const d = res.data.data;
                    let html = buildDetailsHtml(d);
                    $('#viewModalBody').html(html);

                    // Build footer with action buttons
                    let footerHtml = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';

                    if (d.status !== 'approved') {
                        footerHtml += ' <button type="button" class="btn btn-success modal-approve-btn" data-id="' + d.id + '">' +
                            '<i class="ri-check-line me-1"></i>Approve</button>';
                    }

                    if (d.status !== 'rejected') {
                        footerHtml += ' <button type="button" class="btn btn-warning modal-cancel-btn" data-id="' + d.id + '">' +
                            '<i class="ri-close-line me-1"></i>Cancel</button>';
                    }

                    footerHtml += ' <button type="button" class="btn btn-danger modal-delete-btn" data-id="' + d.id + '">' +
                        '<i class="ri-delete-bin-fill me-1"></i>Delete</button>';

                    $('#viewModalFooter').html(footerHtml);
                })
                .catch(function() {
                    $('#viewModalBody').html(
                        '<div class="alert alert-danger">Failed to load application details.</div>');
                });
        }

        function buildDetailsHtml(d) {
            const statusBadge = {
                'pending': '<span class="badge bg-warning-subtle text-warning">Pending</span>',
                'approved': '<span class="badge bg-success-subtle text-success">Approved</span>',
                'rejected': '<span class="badge bg-danger-subtle text-danger">Cancelled</span>',
            }[d.status] || '<span class="badge bg-secondary-subtle text-secondary">' + d.status + '</span>';

            const logoHtml = d.business_logo
                ? '<img src="' + d.business_logo + '" class="img-fluid rounded" style="max-height:80px;" alt="Logo">'
                : '<span class="text-muted">—</span>';

            return `
                <div class="row">
                    <div class="col-md-2 text-center mb-3">
                        ${logoHtml}
                    </div>
                    <div class="col-md-10">
                        <h5 class="mb-1">${d.business_name}</h5>
                        <p class="text-muted mb-0"><i class="ri-user-line me-1"></i>${d.owner_name}</p>
                        <p class="text-muted mb-0"><i class="ri-mail-line me-1"></i>${d.owner_email}</p>
                        <p class="mb-0 mt-1">${statusBadge}</p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="ri-information-line me-1"></i>Application Info</h6>
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">Application ID</th><td>#${d.id}</td></tr>
                            <tr><th>Round Session</th><td>${d.round_session_name}</td></tr>
                            <tr><th>Status</th><td>${statusBadge}</td></tr>
                            <tr><th>Applied Date</th><td>${d.created_at}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="ri-shield-check-line me-1"></i>Admin Info</h6>
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">Approved By</th><td>${d.approver_name}</td></tr>
                            <tr><th>Approved At</th><td>${d.approved_at || '—'}</td></tr>
                            <tr><th>Admin Note</th><td>${d.admin_note || '—'}</td></tr>
                            <tr><th>Last Updated</th><td>${d.updated_at}</td></tr>
                        </table>
                    </div>
                </div>
            `;
        }

        // Modal Approve
        $(document).on('click', '.modal-approve-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('Are you sure you want to approve this application?', {
                title: 'Approve Application?',
                type: 'success',
                confirmText: 'Yes, approve it'
            }).then(function(confirmed) {
                if (!confirmed) return;

                axios.patch('{{ url("contest-applications") }}/' + id + '/approve')
                    .then(function(res) {
                        Toast.success('Application approved successfully.');
                        $('#viewModal').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    })
                    .catch(function(err) {
                        Toast.error(err.response?.data?.message || 'Failed to approve application.');
                    });
            });
        });

        // Modal Cancel
        $(document).on('click', '.modal-cancel-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('Are you sure you want to cancel this application?', {
                title: 'Cancel Application?',
                type: 'warning',
                confirmText: 'Yes, cancel it'
            }).then(function(confirmed) {
                if (!confirmed) return;

                axios.patch('{{ url("contest-applications") }}/' + id + '/cancel')
                    .then(function(res) {
                        Toast.success('Application cancelled successfully.');
                        $('#viewModal').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    })
                    .catch(function(err) {
                        Toast.error(err.response?.data?.message || 'Failed to cancel application.');
                    });
            });
        });

        // Modal Delete
        $(document).on('click', '.modal-delete-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('This application will be permanently deleted and cannot be restored.', {
                title: 'Delete Application?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(function(confirmed) {
                if (!confirmed) return;

                axios.delete('{{ url("contest-applications") }}/' + id)
                    .then(function(res) {
                        Toast.success('Application deleted successfully.');
                        $('#viewModal').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    })
                    .catch(function(err) {
                        Toast.error(err.response?.data?.message || 'Failed to delete application.');
                    });
            });
        });

        // Table Approve confirmation
        $(document).on('click', '.approve-btn', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Alert.confirm('Are you sure you want to approve this application?', {
                title: 'Approve Application?',
                type: 'success',
                confirmText: 'Yes, approve it'
            }).then(confirmed => {
                if (confirmed) form.submit();
            });
        });

        // Table Cancel confirmation
        $(document).on('click', '.cancel-btn', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Alert.confirm('Are you sure you want to cancel this application?', {
                title: 'Cancel Application?',
                type: 'warning',
                confirmText: 'Yes, cancel it'
            }).then(confirmed => {
                if (confirmed) form.submit();
            });
        });

        // Table Delete confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Alert.confirm('This application will be permanently deleted.', {
                title: 'Delete Application?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (confirmed) form.submit();
            });
        });

    })();
</script>
@endpush
