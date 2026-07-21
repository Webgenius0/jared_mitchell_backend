@extends('layout.master-layout')

@section('title', 'Spotlight Applications')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Spotlight Applications</h4>
                    <div class="page-title-right">
                        <span class="text-muted small">All applications across all weeks</span>
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
                                <i class="ri-file-list-3-line fs-24 text-primary"></i>
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
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-hourglass-line fs-24 text-warning"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-warning">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Selected</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-success">{{ number_format($stats['selected']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Rejected</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-close-circle-line fs-24 text-danger"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-danger">{{ number_format($stats['rejected']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Withdrawn</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-arrow-go-back-line fs-24 text-secondary"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-secondary">{{ number_format($stats['withdrawn']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Applications</h5>
                    </div>

                    {{-- Search & Filter Bar --}}
                    <div class="card-body border-bottom pb-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label text-muted small mb-1">Search</label>
                                <div class="search-box">
                                    <input type="text" class="form-control" id="searchInput"
                                           placeholder="Search by applicant name or email...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="selected">Selected</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="withdrawn">Withdrawn</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Spotlight Type</label>
                                <select class="form-select" id="typeFilter">
                                    <option value="">All Types</option>
                                    <option value="artist">Artist</option>
                                    <option value="business">Business</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-3">
                                <label class="form-label text-muted small mb-1">&nbsp;</label>
                                <button type="button" id="resetFilters" class="btn btn-soft-danger w-100">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="applicationsTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Week</th>
                                        <th>Applicant</th>
                                        <th>Spotlight</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th class="text-center" style="width: 80px;">Action</th>
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

        const table = $('#applicationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.spotlight.applications.index') }}',
                data: function (d) {
                    d.search_query = document.getElementById('searchInput').value;
                    d.status       = document.getElementById('statusFilter').value;
                    d.type         = document.getElementById('typeFilter').value;
                }
            },
            columns: [
                { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'week_label',   name: 'spotlight_week_id' },
                { data: 'applicant',    name: 'user_id', orderable: false },
                { data: 'spotlight_name', name: 'spotlightable_id' },
                { data: 'spotlight_type', name: 'spotlightable_type', className: 'text-center' },
                { data: 'status_badge',  name: 'status', className: 'text-center' },
                { data: 'applied_date',  name: 'applied_at' },
                { data: 'action',        name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[6, 'desc']]
        });

        // ── Filter events ──
        let searchTimeout = null;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => table.ajax.reload(null, false), 300);
        });

        document.getElementById('statusFilter').addEventListener('change', function () {
            table.ajax.reload(null, false);
        });

        document.getElementById('typeFilter').addEventListener('change', function () {
            table.ajax.reload(null, false);
        });

        document.getElementById('resetFilters').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('typeFilter').value = '';
            table.ajax.reload(null, false);
        });

        // ── SweetAlert for DataTable forms with data-confirm (delegated because rows are re-rendered) ──
        document.querySelector('#applicationsTable').addEventListener('submit', function (e) {
            var form = e.target.closest('form[data-confirm]');
            if (!form) return;

            e.preventDefault();

            var message = form.getAttribute('data-confirm');
            var type = form.getAttribute('data-confirm-type') || 'confirm';

            Alert.confirm(message, {
                type: type,
                confirmText: type === 'danger' ? 'Yes, reject' : type === 'warning' ? 'Yes, revert' : 'Yes, approve',
            }).then(function (confirmed) {
                if (confirmed) {
                    form.submit();
                }
            });
        });

    })();
</script>
@endpush
