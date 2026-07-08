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

                    {{-- Card Header with Bulk Actions --}}
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0 flex-grow-1">All Contest Applications</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group">
                                <button type="button" class="btn btn-soft-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-download-2-line me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item export-link" href="#" id="exportCsvBtn">
                                            <i class="ri-file-text-line me-2"></i>Export as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-link" href="#" id="exportExcelBtn">
                                            <i class="ri-file-excel-2-line me-2 text-success"></i>Export as Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-link" href="#" id="exportPdfBtn">
                                            <i class="ri-file-pdf-2-line me-2 text-danger"></i>Export as PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-soft-success btn-sm bulk-action-btn" data-action="approved" disabled>
                                <i class="ri-checkbox-circle-line me-1"></i> Bulk Approve
                            </button>
                            <button type="button" class="btn btn-soft-warning btn-sm bulk-action-btn" data-action="rejected" disabled>
                                <i class="ri-close-circle-line me-1"></i> Bulk Cancel
                            </button>
                            <button type="button" class="btn btn-soft-danger btn-sm bulk-delete-btn" disabled>
                                <i class="ri-delete-bin-line me-1"></i> Bulk Delete
                            </button>
                        </div>
                    </div>

                    {{-- Search & Filter Bar --}}
                    <div class="card-body border-bottom pb-2">
                        {{-- Row 1: Text filters & dropdowns --}}
                        <div class="row g-2 mb-2 align-items-end">
                            <div class="col-xl-4 col-md-6">
                                <label class="form-label text-muted small mb-1">Search</label>
                                <div class="search-box">
                                    <input type="text" class="form-control search" id="searchInput"
                                           placeholder="Search business, owner, email..."
                                           value="{{ request('search') }}">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label text-muted small mb-1">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Season</label>
                                <select class="form-select" id="seasonFilter">
                                    <option value="">All Seasons</option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Round</label>
                                <select class="form-select" id="roundFilter" disabled>
                                    <option value="">All Rounds</option>
                                </select>
                            </div>
                        </div>

                        {{-- Row 2: Date filters & actions --}}
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label text-muted small mb-1">From Date</label>
                                <input type="date" class="form-control" id="dateFrom"
                                       value="{{ request('date_from') }}" title="From date">
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label text-muted small mb-1">To Date</label>
                                <input type="date" class="form-control" id="dateTo"
                                       value="{{ request('date_to') }}" title="To date">
                            </div>
                            <div class="col-xl-5 col-md-8">
                                <label class="form-label text-muted small mb-1">Quick Presets</label>
                                <div class="d-flex gap-1 date-presets">
                                    <button type="button" class="btn btn-soft-info btn-sm date-preset-btn" data-preset="today">Today</button>
                                    <button type="button" class="btn btn-soft-info btn-sm date-preset-btn" data-preset="week">This Week</button>
                                    <button type="button" class="btn btn-soft-info btn-sm date-preset-btn" data-preset="month">This Month</button>
                                    <button type="button" class="btn btn-soft-info btn-sm date-preset-btn" data-preset="year">This Year</button>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">&nbsp;</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <button type="button" id="resetFilters" class="btn btn-soft-danger">
                                        <i class="ri-refresh-line me-1"></i> Reset Filters
                                    </button>
                                    <div class="spinner-border spinner-border-sm text-primary d-none" id="tableSpinner" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body" id="tableContainer">
                        @include('web.admin.contest-applications._table')
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

{{-- Confirm Action Modal --}}
<div class="modal fade" id="confirmActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmActionTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmActionText">Are you sure?</p>
                <div class="mb-3">
                    <label for="confirmActionNote" class="form-label">Admin Note <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="confirmActionNote" rows="3"
                        placeholder="Enter a note explaining this action..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmActionBtn">Confirm</button>
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
        let selectedIds = [];
        let searchTimeout = null;

        @if (session('success'))
            Toast.success(@json(session('success')));
        @endif

        @if (session('error'))
            Toast.error(@json(session('error')));
        @endif

        @if (session('warning'))
            Toast.warning(@json(session('warning')));
        @endif

        // ──────────────────────────────────────────────
        //  LIVE SEARCH & FILTER (debounced AJAX)
        // ──────────────────────────────────────────────

        function getFilterParams() {
            const params = new URLSearchParams();
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const seasonId = document.getElementById('seasonFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            if (search) params.set('search', search);
            if (status) params.set('status', status);
            if (seasonId) params.set('season_id', seasonId);
            if (dateFrom) params.set('date_from', dateFrom);
            if (dateTo) params.set('date_to', dateTo);
            return params;
        }

        function updateExportLinks() {
            const params = getFilterParams();
            const qs = params.toString();
            document.getElementById('exportCsvBtn').href = '{{ route("admin.contest-applications.export.csv") }}' + (qs ? '?' + qs : '');
            document.getElementById('exportExcelBtn').href = '{{ route("admin.contest-applications.export.excel") }}' + (qs ? '?' + qs : '');
            document.getElementById('exportPdfBtn').href = '{{ route("admin.contest-applications.export.pdf") }}' + (qs ? '?' + qs : '');
        }

        function fetchTable(page) {
            const params = getFilterParams();
            if (page) params.set('page', page);

            document.getElementById('tableSpinner').classList.remove('d-none');

            axios.get('{{ route("admin.contest-applications.index") }}?' + params.toString())
                .then(function(res) {
                    document.getElementById('tableContainer').innerHTML = res.data;
                    updateBulkButtons();
                })
                .catch(function() {
                    Toast.error('Failed to load table data.');
                })
                .finally(function() {
                    document.getElementById('tableSpinner').classList.add('d-none');
                });
        }

        // Debounced search input (300ms delay, resets to page 1)
        document.getElementById('searchInput').addEventListener('input', function() {
            updateExportLinks();
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() { fetchTable(1); }, 300);
        });

        // Instant filter on status change (resets to page 1)
        document.getElementById('statusFilter').addEventListener('change', function() {
            updateExportLinks();
            clearTimeout(searchTimeout);
            fetchTable(1);
        });

        // Season dropdown — loads rounds via AJAX and triggers filter
        document.getElementById('seasonFilter').addEventListener('change', function() {
            const seasonId = this.value;
            const roundSelect = document.getElementById('roundFilter');

            // Clear and disable round dropdown
            roundSelect.innerHTML = '<option value="">All Rounds</option>';
            roundSelect.disabled = true;

            if (seasonId) {
                axios.get('{{ url("contest-applications/rounds-by-season") }}/' + seasonId)
                    .then(function(res) {
                        res.data.forEach(function(round) {
                            const opt = document.createElement('option');
                            opt.value = round.id;
                            opt.textContent = 'Round ' + round.round_number + ': ' + (round.title || '');
                            roundSelect.appendChild(opt);
                        });
                        roundSelect.disabled = false;
                    })
                    .catch(function() {
                        Toast.error('Failed to load rounds.');
                    });
            }

            updateExportLinks();
            clearTimeout(searchTimeout);
            fetchTable(1);
        });

        // Round filter — triggers filter (season_id is already passed via getFilterParams)
        document.getElementById('roundFilter').addEventListener('change', function() {
            updateExportLinks();
            clearTimeout(searchTimeout);
            fetchTable(1);
        });

        // ──────────────────────────────────────────────
        //  DATE PRESETS
        // ──────────────────────────────────────────────

        $(document).on('click', '.date-preset-btn', function() {
            const preset = $(this).data('preset');
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            let from, to;

            switch (preset) {
                case 'today':
                    from = today;
                    to = today;
                    break;
                case 'week':
                    const dayOfWeek = now.getDay();
                    const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Monday as start
                    const monday = new Date(now);
                    monday.setDate(now.getDate() - diff);
                    from = monday.toISOString().split('T')[0];
                    to = today;
                    break;
                case 'month':
                    from = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-01';
                    to = today;
                    break;
                case 'year':
                    from = now.getFullYear() + '-01-01';
                    to = today;
                    break;
            }

            document.getElementById('dateFrom').value = from;
            document.getElementById('dateTo').value = to;
            updateExportLinks();
            clearTimeout(searchTimeout);
            fetchTable(1);
        });

        // Reset filters (resets to page 1)
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('seasonFilter').value = '';
            document.getElementById('roundFilter').innerHTML = '<option value="">All Rounds</option>';
            document.getElementById('roundFilter').disabled = true;
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            updateExportLinks();
            clearTimeout(searchTimeout);
            fetchTable(1);
        });

        // AJAX pagination — intercept clicks on pagination links
        $(document).on('click', '.pagination-links a', function(e) {
            const url = new URL(this.href);
            const page = url.searchParams.get('page');
            if (page) {
                e.preventDefault();
                fetchTable(page);
            }
        });

        // Sync export link href with initial filter values (e.g. date_from/date_to in URL)
        updateExportLinks();

        // Pre-populate rounds if a season is pre-selected from URL params on page load
        const initialSeason = document.getElementById('seasonFilter').value;
        if (initialSeason) {
            document.getElementById('seasonFilter').dispatchEvent(new Event('change'));
        }

        // ──────────────────────────────────────────────
        //  CHECKBOX SELECTION (event delegation survives AJAX reloads)
        // ──────────────────────────────────────────────

        $(document).on('change', '#selectAll', function() {
            const checked = this.checked;
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
            updateBulkButtons();
        });

        $(document).on('change', '.row-checkbox', function() {
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                const allChecked = document.querySelectorAll('.row-checkbox:checked').length === document.querySelectorAll('.row-checkbox').length;
                selectAll.checked = allChecked;
            }
            updateBulkButtons();
        });

        function updateBulkButtons() {
            selectedIds = [];
            document.querySelectorAll('.row-checkbox:checked').forEach(cb => selectedIds.push(cb.value));

            const hasSelection = selectedIds.length > 0;
            document.querySelectorAll('.bulk-action-btn').forEach(btn => btn.disabled = !hasSelection);
            const bulkDelete = document.querySelector('.bulk-delete-btn');
            if (bulkDelete) bulkDelete.disabled = !hasSelection;
        }

        // ──────────────────────────────────────────────
        //  BULK ACTIONS
        // ──────────────────────────────────────────────

        $(document).on('click', '.bulk-action-btn', function() {
            const action = $(this).data('action');
            const label = action === 'approved' ? 'approve' : 'cancel';

            if (selectedIds.length === 0) {
                Toast.warning('Please select at least one application.');
                return;
            }

            Alert.confirm(`Are you sure you want to ${label} ${selectedIds.length} application(s)?`, {
                title: `Bulk ${label.charAt(0).toUpperCase() + label.slice(1)}?`,
                type: action === 'approved' ? 'success' : 'warning',
                confirmText: `Yes, ${label} them`
            }).then(function(confirmed) {
                if (!confirmed) return;

                axios.post('{{ route("admin.contest-applications.bulk-status") }}', {
                    ids: selectedIds,
                    status: action
                }).then(function(res) {
                    Toast.success(res.data.message);
                    setTimeout(() => location.reload(), 1000);
                }).catch(function(err) {
                    Toast.error(err.response?.data?.message || `Failed to ${label} applications.`);
                });
            });
        });

        $(document).on('click', '.bulk-delete-btn', function() {
            if (selectedIds.length === 0) {
                Toast.warning('Please select at least one application.');
                return;
            }

            Alert.confirm(`This will permanently delete ${selectedIds.length} application(s). This action cannot be undone.`, {
                title: 'Delete Applications?',
                type: 'danger',
                confirmText: 'Yes, delete them'
            }).then(function(confirmed) {
                if (!confirmed) return;

                axios.post('{{ route("admin.contest-applications.bulk-destroy") }}', {
                    ids: selectedIds
                }).then(function(res) {
                    Toast.success(res.data.message);
                    setTimeout(() => location.reload(), 1000);
                }).catch(function(err) {
                    Toast.error(err.response?.data?.message || 'Failed to delete applications.');
                });
            });
        });

        // ──────────────────────────────────────────────
        //  SINGLE STATUS UPDATE (from dropdown)
        // ──────────────────────────────────────────────

        $(document).on('click', '.status-update-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const status = $(this).data('status');
            const label = status === 'approved' ? 'approve' : 'cancel';

            if (status === 'approved') {
                // Approve — simple confirmation, optional note
                Alert.confirm(`Are you sure you want to approve this application?`, {
                    title: 'Approve Application?',
                    type: 'success',
                    confirmText: 'Yes, approve it'
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    axios.patch('{{ url("contest-applications") }}/' + id + '/status', {
                        status: 'approved'
                    }).then(function(res) {
                        Toast.success(res.data.message);
                        setTimeout(() => location.reload(), 1000);
                    }).catch(function(err) {
                        Toast.error(err.response?.data?.message || 'Failed to approve application.');
                    });
                });
            } else {
                // Cancel — open confirmation modal with required reason
                $('#confirmActionTitle').text('Cancel Application');
                $('#confirmActionText').text('Please provide a reason for cancelling this application.');
                $('#confirmActionNote').val('');
                $('#confirmActionBtn')
                    .removeClass('btn-success btn-warning btn-danger')
                    .addClass('btn-warning')
                    .text('Yes, cancel it')
                    .data('id', id)
                    .data('status', 'rejected')
                    .removeData('action');
                $('#confirmActionModal').modal('show');
            }
        });

        // ──────────────────────────────────────────────
        //  SINGLE DELETE (from dropdown)
        // ──────────────────────────────────────────────

        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Alert.confirm('This application will be permanently deleted.', {
                title: 'Delete Application?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(function(confirmed) {
                if (!confirmed) return;

                axios.delete('{{ url("contest-applications") }}/' + id)
                    .then(function(res) {
                        Toast.success(res.data.message);
                        setTimeout(() => location.reload(), 1000);
                    })
                    .catch(function(err) {
                        Toast.error(err.response?.data?.message || 'Failed to delete application.');
                    });
            });
        });

        // ──────────────────────────────────────────────
        //  VIEW DETAILS MODAL
        // ──────────────────────────────────────────────

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
                        footerHtml += ' <button type="button" class="btn btn-success modal-status-btn" data-id="' + d.id + '" data-status="approved">' +
                            '<i class="ri-check-line me-1"></i>Approve</button>';
                    }

                    if (d.status !== 'rejected') {
                        footerHtml += ' <button type="button" class="btn btn-warning modal-status-btn" data-id="' + d.id + '" data-status="rejected">' +
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
                            <tr><th>Season</th><td>${d.season_name}</td></tr>
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

        // Open confirmation modal for status updates (approve/cancel)
        $(document).on('click', '.modal-status-btn', function() {
            const id = $(this).data('id');
            const status = $(this).data('status');
            const label = status === 'approved' ? 'approve' : 'cancel';
            const title = status === 'approved' ? 'Approve Application' : 'Cancel Application';
            const isApprove = status === 'approved';

            $('#confirmActionTitle').text(title);
            $('#confirmActionText').text(`Are you sure you want to ${label} this application?`);
            $('#confirmActionNote').val('');
            $('#confirmActionBtn')
                .removeClass('btn-success btn-warning btn-danger')
                .addClass(isApprove ? 'btn-success' : 'btn-warning')
                .text(`Yes, ${label} it`)
                .data('id', id)
                .data('status', status)
                .removeData('action');

            $('#viewModal').modal('hide');
            $('#confirmActionModal').modal('show');
        });

        // Open confirmation modal for delete
        $(document).on('click', '.modal-delete-btn', function() {
            const id = $(this).data('id');

            $('#confirmActionTitle').text('Delete Application');
            $('#confirmActionText').text('This application will be permanently deleted and cannot be restored.');
            $('#confirmActionNote').val('').closest('.mb-3').hide();
            $('#confirmActionBtn')
                .removeClass('btn-success btn-warning btn-danger')
                .addClass('btn-danger')
                .text('Yes, delete it')
                .data('id', id)
                .data('action', 'delete')
                .removeData('status');

            $('#viewModal').modal('hide');
            $('#confirmActionModal').modal('show');
        });

        // Show note field for status actions
        $(document).on('shown.bs.modal', '#confirmActionModal', function() {
            if ($('#confirmActionBtn').data('action') === 'delete') {
                $('#confirmActionNote').closest('.mb-3').hide();
            } else {
                $('#confirmActionNote').closest('.mb-3').show();
            }
        });

        // Single handler for confirmation modal — reads data-action or data-status
        $(document).on('click', '#confirmActionBtn', function() {
            const $btn = $(this);
            const id = $btn.data('id');
            const action = $btn.data('action');
            const status = $btn.data('status');

            if (action === 'delete') {
                axios.delete('{{ url("contest-applications") }}/' + id)
                    .then(function(res) {
                        Toast.success(res.data.message);
                        $('#confirmActionModal').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    })
                    .catch(function(err) {
                        Toast.error(err.response?.data?.message || 'Failed to delete application.');
                    });
                return;
            }

            // Status update (approve/cancel)
            const label = status === 'approved' ? 'approve' : 'cancel';
            const note = $('#confirmActionNote').val().trim();
            const payload = { status: status };
            if (note) payload.admin_note = note;

            axios.patch('{{ url("contest-applications") }}/' + id + '/status', payload)
                .then(function(res) {
                    Toast.success(res.data.message);
                    $('#confirmActionModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(function(err) {
                    Toast.error(err.response?.data?.message || `Failed to ${label} application.`);
                });
        });

    })();
</script>
@endpush
