@extends('layout.master-layout')

@section('title', 'Events')
@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Events</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Create Event
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row">
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-calendar-event-line fs-24 text-primary"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Published</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-success">{{ number_format($stats['published']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Draft</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-draft-line fs-24 text-warning"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-warning">{{ number_format($stats['draft']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
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
                        <h4 class="mt-3 mb-0 text-danger">{{ number_format($stats['cancelled']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Completed</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-flag-line fs-24 text-info"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-info">{{ number_format($stats['completed']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    {{-- Card Header: Export Dropdown --}}
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0 flex-grow-1">All Events</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group">
                                <button type="button" class="btn btn-soft-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-download-2-line me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportCsvBtn">
                                            <i class="ri-file-text-line me-2"></i>Export as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportExcelBtn">
                                            <i class="ri-file-excel-2-line me-2 text-success"></i>Export as Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportPdfBtn">
                                            <i class="ri-file-pdf-2-line me-2 text-danger"></i>Export as PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Search & Filter Bar - All in one row --}}
                    <div class="card-body border-bottom pb-2 position-relative">
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label text-muted small mb-1">Search</label>
                                <div class="search-box">
                                    <input type="text" class="form-control" id="searchInput"
                                           placeholder="Search title, city, venue, host..."
                                           value="{{ request('search') }}">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-3">
                                <label class="form-label text-muted small mb-1">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="draft"      {{ request('status') === 'draft'      ? 'selected' : '' }}>Draft</option>
                                    <option value="published"  {{ request('status') === 'published'  ? 'selected' : '' }}>Published</option>
                                    <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
                                    <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-3">
                                <label class="form-label text-muted small mb-1">Type</label>
                                <select class="form-select" id="typeFilter">
                                    <option value="">All Types</option>
                                    <option value="featured"      {{ request('event_type') === 'featured'      ? 'selected' : '' }}>Featured</option>
                                    <option value="workshop"      {{ request('event_type') === 'workshop'      ? 'selected' : '' }}>Workshop</option>
                                    <option value="art_exhibition"{{ request('event_type') === 'art_exhibition'? 'selected' : '' }}>Art Exhibition</option>
                                    <option value="pop_up"        {{ request('event_type') === 'pop_up'        ? 'selected' : '' }}>Pop-Up</option>
                                    <option value="networking"    {{ request('event_type') === 'networking'    ? 'selected' : '' }}>Networking</option>
                                    <option value="other"         {{ request('event_type') === 'other'         ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Date Range</label>
                                <div class="d-flex gap-1">
                                    <input type="date" class="form-control" id="dateFrom" value="{{ request('date_from') }}" placeholder="From">
                                    <input type="date" class="form-control" id="dateTo" value="{{ request('date_to') }}" placeholder="To">
                                </div>
                            </div>
                            <div class="col-xl-1 col-md-3">
                                <label class="form-label text-muted small mb-1">&nbsp;</label>
                                <button type="button" id="resetFilters" class="btn btn-soft-danger w-100" title="Reset Filters">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div class="spinner-border spinner-border-sm text-primary d-none" id="filterSpinner" role="status" style="position: absolute; top: 10px; right: 10px;"></div>
                    </div>

                    {{-- DataTable --}}
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="eventsTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Event Info</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Registrations</th>
                                        <th class="text-center" style="width: 150px;">Action</th>
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

        @if(session('info'))
            Toast.info(@json(session('info')));
        @endif

        // ── DataTable ──────────────────────────────────────────────
        const table = $('#eventsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.events.data') }}',
                data: function (d) {
                    d.search_query = document.getElementById('searchInput').value;
                    d.status       = document.getElementById('statusFilter').value;
                    d.event_type   = document.getElementById('typeFilter').value;
                    d.date_from    = document.getElementById('dateFrom').value;
                    d.date_to      = document.getElementById('dateTo').value;
                }
            },
            columns: [
                { data: 'DT_RowIndex',        name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title_info',          name: 'title' },
                { data: 'event_type',          name: 'event_type', className: 'text-center' },
                { data: 'status',              name: 'status', className: 'text-center' },
                { data: 'date',                name: 'starts_at' },
                { data: 'registrations_count', name: 'registrations_count', className: 'text-center' },
                { data: 'action',              name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[4, 'desc']]
        });

        // ── Filter helpers ─────────────────────────────────────────
        function getFilterParams() {
            const params = new URLSearchParams();
            const search    = document.getElementById('searchInput').value;
            const status    = document.getElementById('statusFilter').value;
            const eventType = document.getElementById('typeFilter').value;
            const dateFrom  = document.getElementById('dateFrom').value;
            const dateTo    = document.getElementById('dateTo').value;
            if (search)    params.set('search',     search);
            if (status)    params.set('status',     status);
            if (eventType) params.set('event_type', eventType);
            if (dateFrom)  params.set('date_from',  dateFrom);
            if (dateTo)    params.set('date_to',    dateTo);
            return params;
        }

        function updateExportLinks() {
            const qs = getFilterParams().toString();
            document.getElementById('exportCsvBtn').href   = '{{ route('admin.events.export.csv') }}'   + (qs ? '?' + qs : '');
            document.getElementById('exportExcelBtn').href = '{{ route('admin.events.export.excel') }}' + (qs ? '?' + qs : '');
            document.getElementById('exportPdfBtn').href   = '{{ route('admin.events.export.pdf') }}'   + (qs ? '?' + qs : '');
        }

        function reloadTable() {
            updateExportLinks();
            table.ajax.reload(null, false);
        }

        // ── Live filter events ─────────────────────────────────────
        let searchTimeout = null;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(reloadTable, 300);
        });

        document.getElementById('statusFilter').addEventListener('change', reloadTable);
        document.getElementById('typeFilter').addEventListener('change', reloadTable);
        document.getElementById('dateFrom').addEventListener('change', reloadTable);
        document.getElementById('dateTo').addEventListener('change', reloadTable);

        // ── Reset filters ──────────────────────────────────────────
        document.getElementById('resetFilters').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('typeFilter').value   = '';
            document.getElementById('dateFrom').value     = '';
            document.getElementById('dateTo').value       = '';
            reloadTable();
        });

        // ── Delete ─────────────────────────────────────────────────
        $(document).on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            Alert.confirm('This will delete the event and all associated records.', {
                title:       'Delete Event?',
                type:        'danger',
                confirmText: 'Yes, delete it'
            }).then(function (confirmed) {
                if (!confirmed) return;
                $.ajax({
                    url: '/events/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        Toast.success('Event deleted successfully.');
                        table.ajax.reload(null, false);
                    },
                    error: function (err) {
                        Toast.error(err.responseJSON?.message || 'Delete failed.');
                    }
                });
            });
        });

        // ── Init export links ──────────────────────────────────────
        updateExportLinks();

    })();
</script>
@endpush
