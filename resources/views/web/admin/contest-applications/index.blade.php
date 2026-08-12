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
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
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

@push('styles')
    {{-- GLightbox for full-size media preview in the details modal --}}
    <link href="{{ asset('admin/assets/libs/glightbox/css/glightbox.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .view-modal-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .view-modal-media-item {
            position: relative;
            display: block;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, .08);
            background: #f5f6f8;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .view-modal-media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, .14);
        }
        .view-modal-media-item img,
        .view-modal-media-item video {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }
        .view-modal-play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .28);
            transition: background .15s ease;
            pointer-events: none;
        }
        .view-modal-play-overlay i {
            font-size: 36px;
            color: rgba(255, 255, 255, .95);
            text-shadow: 0 2px 8px rgba(0, 0, 0, .45);
        }
        .view-modal-media-item:hover .view-modal-play-overlay {
            background: rgba(0, 0, 0, .15);
        }
        .view-modal-media-item .media-caption {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, .72), transparent);
            color: #fff;
            font-size: 11px;
            padding: 20px 8px 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('admin/assets/libs/glightbox/js/glightbox.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    (function() {
        'use strict';

        let currentApplicationId = null;
        let selectedIds = [];
        let searchTimeout = null;

        // ──────────────────────────────────────────────
        //  HTML HELPERS (used by the details modal)
        // ──────────────────────────────────────────────

        function esc(str) {
            return String(str == null ? '' : str).replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function isVideoUrl(url) {
            return /\.(mp4|webm|ogv|mov|m4v)(\?.*)?$/i.test(url || '');
        }

        function isVideoMime(mime) {
            return String(mime || '').toLowerCase().indexOf('video/') === 0;
        }

        function formatBytes(bytes) {
            if (bytes === null || bytes === undefined || isNaN(bytes)) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function detailRow(label, value) {
            const v = (value === null || value === undefined || value === '') ? '—' : value;
            return '<tr><th class="text-muted fw-normal" style="width:38%;vertical-align:top;">' + label + '</th><td>' + v + '</td></tr>';
        }

        function sectionTitle(icon, title) {
            return '<div class="d-flex align-items-center gap-2 mb-2">' +
                '<i class="' + icon + ' text-primary fs-16"></i>' +
                '<h6 class="mb-0 fw-semibold text-uppercase text-muted" style="font-size:.78rem;letter-spacing:.5px;">' + title + '</h6></div>';
        }

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

        // Destroy the lightbox when the modal closes so it does not leak listeners
        $('#viewModal').on('hidden.bs.modal', function() {
            if (window.__contestLightbox) {
                try { window.__contestLightbox.destroy(); } catch (e) {}
                window.__contestLightbox = null;
            }
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

                    // Re-init GLightbox for the freshly injected media links
                    if (window.GLightbox) {
                        try {
                            if (window.__contestLightbox) {
                                window.__contestLightbox.destroy();
                            }
                        } catch (e) {}
                        window.__contestLightbox = GLightbox({
                            selector: '.view-modal-glightbox',
                            touchNavigation: true,
                            loop: true,
                            closeButton: true,
                        });
                    }

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
            }[d.status] || '<span class="badge bg-secondary-subtle text-secondary">' + esc(d.status) + '</span>';

            let businessStatusBadge = '';
            if (d.business_status) {
                const map = {
                    'active': 'bg-success-subtle text-success',
                    'inactive': 'bg-secondary-subtle text-secondary',
                    'terminated': 'bg-danger-subtle text-danger',
                };
                const cls = map[d.business_status] || 'bg-secondary-subtle text-secondary';
                businessStatusBadge = '<span class="badge ' + cls + '">Business: ' + esc(d.business_status) + '</span>';
            }

            const featuredBadge = d.is_featured
                ? '<span class="badge bg-info-subtle text-info"><i class="ri-star-fill me-1"></i>Featured</span>'
                : '';

            const logoHtml = d.business_logo
                ? '<a href="' + esc(d.business_logo) + '" target="_blank" title="Open logo"><img src="' + esc(d.business_logo) + '" class="rounded border" style="width:72px;height:72px;object-fit:cover;" alt="Logo"></a>'
                : '<div class="d-flex align-items-center justify-content-center rounded border bg-light" style="width:72px;height:72px;"><i class="ri-store-2-line fs-24 text-muted"></i></div>';

            const statsHtml = [
                { icon: 'ri-hand-clap-line', label: 'Claps', value: d.total_claps, color: 'primary' },
                { icon: 'ri-heart-line', label: 'Saves', value: d.total_saves, color: 'success' },
                { icon: 'ri-share-forward-line', label: 'Shares', value: d.total_shares, color: 'info' },
                { icon: 'ri-trophy-line', label: 'Points', value: d.total_points, color: 'warning' },
            ].map(function (s) {
                return '<div class="col-6 col-md-3">' +
                    '<div class="card mb-0 shadow-none border text-center py-2">' +
                    '<div class="card-body py-1">' +
                    '<i class="' + s.icon + ' text-' + s.color + ' fs-18"></i>' +
                    '<h5 class="mb-0 mt-1">' + Number(s.value || 0).toLocaleString() + '</h5>' +
                    '<span class="text-muted small">' + s.label + '</span>' +
                    '</div></div></div>';
            }).join('');

            // AI review block
            let aiHtml = '';
            if (d.ai_verdict || d.ai_confidence || d.ai_reviewed_at || d.ai_score) {
                const verdictMap = {
                    'approve': '<span class="badge bg-success-subtle text-success">Approve</span>',
                    'reject': '<span class="badge bg-danger-subtle text-danger">Reject</span>',
                    'needs_review': '<span class="badge bg-warning-subtle text-warning">Needs Review</span>',
                };
                const verdictBadge = d.ai_verdict
                    ? (verdictMap[d.ai_verdict] || '<span class="badge bg-secondary-subtle text-secondary">' + esc(d.ai_verdict) + '</span>')
                    : '<span class="text-muted">—</span>';
                let aiScoreBadge = d.ai_score !== null && d.ai_score !== undefined
                    ? (function () {
                        const s = Number(d.ai_score);
                        const cls = s >= 70
                            ? 'bg-success-subtle text-success'
                            : (s >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                        return '<span class="badge ' + cls + '">' + esc(s.toFixed(1)) + ' / 100</span>';
                    })()
                    : null;
                // Accurate confidence (what the AI reported); adjusted value shown when different
                let aiConfidenceHtml = null;
                if (d.ai_confidence) {
                    aiConfidenceHtml = '<strong>' + esc(d.ai_confidence) + '</strong>';
                    if (d.ai_confidence_adjusted && d.ai_confidence_adjusted !== d.ai_confidence) {
                        aiConfidenceHtml += ' <small class="text-muted">(adjusted ' + esc(d.ai_confidence_adjusted) + ' for auto-review)</small>';
                    }
                }
                aiHtml = '<div class="row mt-3"><div class="col-12">' + sectionTitle('ri-robot-2-line', 'AI Review') +
                    '<table class="table table-sm table-borderless mb-0">' +
                    detailRow('AI Score', aiScoreBadge) +
                    detailRow('AI Verdict', verdictBadge) +
                    detailRow('AI Confidence', aiConfidenceHtml) +
                    detailRow('AI Reviewed At', esc(d.ai_reviewed_at)) +
                    '</table></div></div>';
            }

            // Single photo/video field
            let photoVideoHtml = '';
            if (d.photo_video) {
                photoVideoHtml = '<div class="row mt-3"><div class="col-12">' + sectionTitle('ri-video-line', 'Photo / Video') +
                    (isVideoUrl(d.photo_video)
                        ? '<video src="' + esc(d.photo_video) + '" controls class="w-100 rounded" style="max-height:340px;background:#000;"></video>'
                        : '<a href="' + esc(d.photo_video) + '" class="view-modal-glightbox" title="Photo / Video"><img src="' + esc(d.photo_video) + '" class="img-fluid rounded" style="max-height:340px;width:auto;" alt="Photo / Video"></a>') +
                    '</div></div>';
            }

            // Media gallery — every picture & video
            let galleryHtml = '';
            const media = Array.isArray(d.media) ? d.media : [];
            if (media.length) {
                galleryHtml = '<div class="row mt-3"><div class="col-12">' + sectionTitle('ri-image-line', 'Media Gallery (' + media.length + ')') +
                    '<div class="view-modal-media-grid">';
                media.forEach(function (m) {
                    if (!m.url) return;
                    const caption = esc(m.file_name || 'Media #' + m.id);
                    const sizeLabel = formatBytes(m.file_size);
                    const captionLine = sizeLabel ? caption + ' · ' + sizeLabel : caption;
                    if (isVideoMime(m.mime_type) || isVideoUrl(m.url)) {
                        galleryHtml += '<div class="view-modal-media-item" title="' + caption.replace(/"/g, '&quot;') + '">' +
                            '<video src="' + esc(m.url) + '" controls preload="metadata" muted></video>' +
                            '<div class="view-modal-play-overlay"><i class="ri-play-circle-fill"></i></div>' +
                            '<div class="media-caption"><i class="ri-video-line me-1"></i>' + captionLine + '</div></div>';
                    } else {
                        galleryHtml += '<a href="' + esc(m.url) + '" class="view-modal-media-item view-modal-glightbox" title="' + caption.replace(/"/g, '&quot;') + '">' +
                            '<img src="' + esc(m.url) + '" loading="lazy" alt="' + caption + '">' +
                            '<div class="media-caption">' + captionLine + '</div></a>';
                    }
                });
                galleryHtml += '</div>' +
                    '<div class="text-muted small mt-2"><i class="ri-zoom-in-line me-1"></i>Click an image to view full size</div>' +
                    '</div></div>';
            }

            // Application metadata (raw JSON)
            let metadataHtml = '';
            if (d.metadata && typeof d.metadata === 'object') {
                metadataHtml = '<div class="row mt-3"><div class="col-12">' + sectionTitle('ri-file-list-3-line', 'Application Metadata') +
                    '<pre class="bg-light rounded p-3 mb-0" style="max-height:240px;overflow:auto;font-size:.8rem;">' + esc(JSON.stringify(d.metadata, null, 2)) + '</pre>' +
                    '</div></div>';
            }

            // Owner social links
            let socialHtml = '';
            if (d.owner_social_links && typeof d.owner_social_links === 'object') {
                const links = Object.keys(d.owner_social_links).filter(function (k) { return d.owner_social_links[k]; });
                if (links.length) {
                    socialHtml = '<div class="mt-2">' + links.map(function (k) {
                        return '<a href="' + esc(d.owner_social_links[k]) + '" target="_blank" class="badge bg-light text-dark text-decoration-none me-1 mb-1 border"><i class="ri-external-link-line me-1"></i>' + esc(k) + '</a>';
                    }).join('') + '</div>';
                }
            }

            const ownerAvatarHtml = d.owner_avatar
                ? '<img src="' + esc(d.owner_avatar) + '" class="rounded-circle border mt-2" style="width:90px;height:90px;object-fit:cover;" alt="Owner avatar">'
                : '<div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border mt-2" style="width:90px;height:90px;"><i class="ri-user-line fs-24 text-muted"></i></div>';

            return `
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="flex-shrink-0">${logoHtml}</div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1">${esc(d.business_name)} ${featuredBadge}</h5>
                        <p class="text-muted mb-1"><i class="ri-user-line me-1"></i>${esc(d.owner_name)}${d.owner_email && d.owner_email !== '—' ? ' <span class="text-muted">· ' + esc(d.owner_email) + '</span>' : ''}</p>
                        <div class="d-flex gap-1 flex-wrap">${statusBadge} ${businessStatusBadge}</div>
                    </div>
                </div>

                <div class="row g-2 mb-3">${statsHtml}</div>

                <hr class="my-3">

                <div class="row">
                    <div class="col-md-6">
                        ${sectionTitle('ri-information-line', 'Application Info')}
                        <table class="table table-sm table-borderless mb-0">
                            ${detailRow('Application ID', '#' + d.id)}
                            ${detailRow('Season', esc(d.season_name))}
                            ${detailRow('Status', statusBadge)}
                            ${detailRow('Applied Date', esc(d.created_at))}
                            ${detailRow('Last Updated', esc(d.updated_at))}
                            ${detailRow('Admin Note', d.admin_note ? esc(d.admin_note) : null)}
                            ${detailRow('Rejected Reason', d.rejected_reason ? esc(d.rejected_reason) : null)}
                        </table>
                    </div>
                    <div class="col-md-6">
                        ${sectionTitle('ri-shield-check-line', 'Admin Info')}
                        <table class="table table-sm table-borderless mb-0">
                            ${detailRow('Approved By', esc(d.approver_name))}
                            ${detailRow('Approver ID', d.approver_id ? d.approver_id : null)}
                            ${detailRow('Approved At', esc(d.approved_at))}
                        </table>
                    </div>
                </div>

                ${aiHtml}

                <div class="row mt-3">
                    <div class="col-12">
                        ${sectionTitle('ri-store-2-line', 'Business Information')}
                        <table class="table table-sm table-borderless mb-0">
                            ${detailRow('Business Name', esc(d.business_name))}
                            ${detailRow('Owner / Founder', esc(d.owner_founder_name))}
                            ${detailRow('Slug', d.business_slug ? '<code>' + esc(d.business_slug) + '</code>' : null)}
                            ${detailRow('Status', businessStatusBadge || null)}
                            ${detailRow('Website / Social Media', d.website_social_media ? '<a href="' + esc(d.website_social_media) + '" target="_blank">' + esc(d.website_social_media) + ' <i class="ri-external-link-line"></i></a>' : null)}
                            ${detailRow('Revenue Stage', esc(d.revenue_stage))}
                            ${detailRow('Story', d.story ? esc(d.story) : null)}
                            ${detailRow('Mission', d.mission ? esc(d.mission) : null)}
                            ${detailRow('Community Impact Statement', d.community_impact_statement ? esc(d.community_impact_statement) : null)}
                            ${detailRow('Why They Deserve to Compete', d.why_they_deserve_to_compete ? esc(d.why_they_deserve_to_compete) : null)}
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        ${sectionTitle('ri-user-line', 'Owner Profile')}
                        <table class="table table-sm table-borderless mb-0">
                            ${detailRow('Name', esc(d.owner_name))}
                            ${detailRow('Username', d.owner_username ? '<code>' + esc(d.owner_username) + '</code>' : null)}
                            ${detailRow('Email', d.owner_email ? '<a href="mailto:' + esc(d.owner_email) + '">' + esc(d.owner_email) + '</a>' : null)}
                            ${detailRow('Address', esc(d.owner_address))}
                            ${detailRow('Website', d.owner_website ? '<a href="' + esc(d.owner_website) + '" target="_blank">' + esc(d.owner_website) + '</a>' : null)}
                            ${detailRow('Biography', esc(d.owner_biography))}
                        </table>
                        ${socialHtml}
                    </div>
                    <div class="col-md-6 text-center">${ownerAvatarHtml}</div>
                </div>

                ${photoVideoHtml}
                ${galleryHtml}
                ${metadataHtml}
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
