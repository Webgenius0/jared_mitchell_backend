@extends('layout.master-layout')

@section('title', 'Businesses')
@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Businesses</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Businesses</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row">
                <div class="col-xl-2 col-md-4">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-store-2-line fs-24 text-primary"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0">{{ number_format($stats['total']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4">
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
                            <h4 class="mt-3 mb-0 text-success">{{ number_format($stats['active']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Inactive</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-pause-circle-line fs-24 text-secondary"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0 text-secondary">{{ number_format($stats['inactive']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Terminated</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-close-circle-line fs-24 text-danger"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0 text-danger">{{ number_format($stats['terminated']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Featured</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-star-fill fs-24 text-warning"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0 text-warning">{{ number_format($stats['featured']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">All Businesses</h5>
                        </div>

                        {{-- Filters --}}
                        <div class="card-body border-bottom pb-3">
                            <div class="row g-3">
                                <div class="col-xl-3 col-md-6">
                                    <div class="search-box">
                                        <input type="text" id="dtSearch" class="form-control search"
                                            placeholder="Search business, owner, location...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <select id="filterStatus" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <select id="filterCategory" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <select id="filterFeatured" class="form-select">
                                        <option value="">All</option>
                                        <option value="yes">Featured Only</option>
                                        <option value="no">Non-Featured</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <button type="button" id="resetFilters" class="btn btn-soft-danger w-100">
                                        <i class="ri-refresh-line me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- DataTable --}}
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="businessesTable" class="table table-bordered table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>Business</th>
                                            <th>Owner</th>
                                            <th>Location</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Featured</th>
                                            <th>Engagement</th>
                                            <th class="text-center" style="width:130px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- View Details Modal --}}
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Business Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="modalToggleStatusBtn" style="display:none;">
                        <i class="ri-pause-circle-line me-1"></i> Toggle Status
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';

            let currentBusinessId = null;

            @if (session('success'))
                Toast.success(@json(session('success')));
            @endif

            @if (session('error'))
                Toast.error(@json(session('error')));
            @endif

            @if (session('warning'))
                Toast.warning(@json(session('warning')));
            @endif

            @if (session('info'))
                Toast.info(@json(session('info')));
            @endif

            // DataTable Initialisation
            const table = $('#businessesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [
                    [0, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    processing: '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>',
                    emptyTable: '<div class="text-center py-5"><i class="ri-store-2-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No businesses found</p></div>',
                    zeroRecords: '<div class="text-center py-5"><i class="ri-search-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No matching records</p></div>',
                },
                ajax: {
                    url: '{{ route('admin.businesses.index') }}',
                    type: 'GET',
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                        d.business_category_id = $('#filterCategory').val();
                        d.is_featured = $('#filterFeatured').val();
                        d.search_term = $('#dtSearch').val();
                    },
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'business',
                        name: 'business_name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'owner',
                        name: 'owner_name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'location',
                        name: 'city',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'featured',
                        name: 'is_featured',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'engagement',
                        name: 'total_points',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
            });

            // Search with debounce
            let searchTimer;
            document.getElementById('dtSearch').addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => table.draw(), 400);
            });

            // Dropdown filters
            ['filterStatus', 'filterCategory', 'filterFeatured'].forEach(function(id) {
                document.getElementById(id).addEventListener('change', function() {
                    table.draw();
                });
            });

            // Reset
            document.getElementById('resetFilters').addEventListener('click', function() {
                document.getElementById('dtSearch').value = '';
                document.getElementById('filterStatus').value = '';
                document.getElementById('filterCategory').value = '';
                document.getElementById('filterFeatured').value = '';
                table.draw();
            });

            // View Details
            $(document).on('click', '.view-btn', function() {
                currentBusinessId = $(this).data('id');
                loadBusinessDetails(currentBusinessId);
                $('#viewModal').modal('show');
            });

            function loadBusinessDetails(id) {
                $('#viewModalBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

                axios.get('{{ url('businesses') }}/' + id)
                    .then(function(res) {
                        const d = res.data.data;
                        let html = buildDetailsHtml(d);
                        $('#viewModalBody').html(html);

                        // Toggle status button visibility
                        const toggleBtn = document.getElementById('modalToggleStatusBtn');
                        if (d.status === 'terminated') {
                            toggleBtn.style.display = 'none';
                        } else {
                            toggleBtn.style.display = 'inline-block';
                            const isActive = d.status === 'active';
                            toggleBtn.innerHTML = isActive ?
                                '<i class="ri-pause-circle-line me-1"></i> Deactivate' :
                                '<i class="ri-play-circle-line me-1"></i> Activate';
                        }
                    })
                    .catch(function() {
                        $('#viewModalBody').html(
                            '<div class="alert alert-danger">Failed to load business details.</div>');
                    });
            }

            function buildDetailsHtml(d) {
                const logoHtml = d.logo ?
                    '<img src="' + d.logo + '" class="img-fluid rounded" style="max-height:80px;" alt="Logo">' :
                    '<span class="text-muted">—</span>';

                const statusBadge = {
                    'active': '<span class="badge bg-success-subtle text-success">Active</span>',
                    'inactive': '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>',
                    'terminated': '<span class="badge bg-danger-subtle text-danger">Terminated</span>',
                } [d.status] || d.status;

                const featuredBadge = d.is_featured ?
                    '<span class="badge bg-warning-subtle text-warning"><i class="ri-star-fill me-1"></i>Featured</span>' :
                    '<span class="badge bg-light text-muted">Not Featured</span>';

                return `
            <div class="row">
                <div class="col-md-2 text-center mb-3">
                    ${logoHtml}
                </div>
                <div class="col-md-10">
                    <h5 class="mb-1">${d.business_name}</h5>
                    <p class="text-muted mb-0"><i class="ri-user-line me-1"></i>${d.owner_name}</p>
                    <p class="text-muted mb-0"><i class="ri-map-pin-line me-1"></i>${d.city}, ${d.state}</p>
                    <p class="mb-0 mt-1">${statusBadge} ${featuredBadge}</p>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="ri-information-line me-1"></i>Basic Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Category</th><td>${d.category_name || '—'}</td></tr>
                        <tr><th>Year Founded</th><td>${d.year_founded || '—'}</td></tr>
                        <tr><th>Website</th><td>${d.website ? '<a href="' + d.website + '" target="_blank">' + d.website + '</a>' : '—'}</td></tr>
                        <tr><th>Slug</th><td><code>${d.slug || '—'}</code></td></tr>
                    </table>

                    <h6 class="text-primary mt-3"><i class="ri-user-line me-1"></i>Owner Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Name</th><td>${d.user_name}</td></tr>
                        <tr><th>Email</th><td>${d.user_email}</td></tr>
                        <tr><th>Owner Name</th><td>${d.owner_name}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="ri-megaphone-line me-1"></i>Engagement</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <h5 class="mb-0 text-primary">${d.total_claps.toLocaleString()}</h5>
                                <small class="text-muted"><i class="ri-hand-heart-line"></i> Claps</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <h5 class="mb-0 text-info">${d.total_saves.toLocaleString()}</h5>
                                <small class="text-muted"><i class="ri-bookmark-line"></i> Saves</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <h5 class="mb-0 text-success">${d.total_shares.toLocaleString()}</h5>
                                <small class="text-muted"><i class="ri-share-line"></i> Shares</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <h5 class="mb-0 text-warning">${d.total_points.toLocaleString()}</h5>
                                <small class="text-muted"><i class="ri-fire-line"></i> Points</small>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-primary mt-3"><i class="ri-timer-line me-1"></i>Timestamps</h6>
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Created</th><td>${d.created_at || '—'}</td></tr>
                        <tr><th>Updated</th><td>${d.updated_at || '—'}</td></tr>
                    </table>
                </div>
            </div>

            ${d.description ? `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary"><i class="ri-file-text-line me-1"></i>Description</h6>
                        <p class="mb-0">${d.description}</p>
                    </div>
                </div>
                ` : ''}
        `;
            }

            // Toggle Status from Modal
            document.getElementById('modalToggleStatusBtn').addEventListener('click', function() {
                if (!currentBusinessId) return;

                const isActive = this.innerHTML.includes('Deactivate');
                const actionLabel = isActive ? 'deactivate' : 'activate';

                Alert.confirm('Are you sure you want to ' + actionLabel + ' this business?', {
                    title: (isActive ? 'Deactivate' : 'Activate') + ' Business?',
                    type: 'warning',
                    confirmText: 'Yes, ' + actionLabel,
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    axios.patch('{{ url('businesses') }}/' + currentBusinessId +
                            '/toggle-status')
                        .then(function(res) {
                            Toast.success(res.data.message);
                            $('#viewModal').modal('hide');
                            table.draw(false);
                        })
                        .catch(function(err) {
                            Toast.error(err.response?.data?.message || 'Failed to toggle status.');
                        });
                });
            });

            // Toggle Status from Table
            $(document).on('click', '.toggle-status-btn', function() {
                const id = $(this).data('id');
                const currentStatus = $(this).data('status');
                const isActive = currentStatus === 'active';
                const actionLabel = isActive ? 'deactivate' : 'activate';

                Alert.confirm('Are you sure you want to ' + actionLabel + ' this business?', {
                    title: (isActive ? 'Deactivate' : 'Activate') + ' Business?',
                    type: 'warning',
                    confirmText: 'Yes, ' + actionLabel,
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    axios.patch('{{ url('businesses') }}/' + id + '/toggle-status')
                        .then(function(res) {
                            Toast.success(res.data.message);
                            table.draw(false);
                        })
                        .catch(function(err) {
                            Toast.error(err.response?.data?.message || 'Failed to toggle status.');
                        });
                });
            });

            // Delete
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                Alert.confirm('This business will be permanently deleted and cannot be restored.', {
                    title: 'Delete Business?',
                    type: 'danger',
                    confirmText: 'Yes, delete it',
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    axios.delete('{{ url('businesses') }}/' + id)
                        .then(function(res) {
                            Toast.success(res.data.message);
                            table.draw(false);
                        })
                        .catch(function(err) {
                            Toast.error(err.response?.data?.message ||
                            'Failed to delete business.');
                        });
                });
            });

        })();
    </script>
@endpush
