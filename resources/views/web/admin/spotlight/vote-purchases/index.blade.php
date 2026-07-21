@extends('layout.master-layout')

@section('title', 'Vote Purchases')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Vote Purchases</h4>
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
                                <i class="ri-shopping-cart-2-line fs-24 text-primary"></i>
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
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Completed</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-success">{{ number_format($stats['completed']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Refunded</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-refund-2-line fs-24 text-danger"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-danger">{{ number_format($stats['refunded']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Vote Purchases</h5>
                    </div>

                    {{-- Search & Filter Bar --}}
                    <div class="card-body border-bottom pb-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label text-muted small mb-1">Search</label>
                                <div class="search-box">
                                    <input type="text" class="form-control" id="searchInput"
                                           placeholder="Search user or nominee..."
                                           value="{{ request('search') }}">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Package</label>
                                <select class="form-select" id="packageFilter">
                                    <option value="">All Packages</option>
                                    @foreach(App\Models\Spotlight\SpotlightVotePurchase::PACKAGES as $key => $pkg)
                                        <option value="{{ $key }}" {{ request('package') === $key ? 'selected' : '' }}>{{ $pkg['label'] }}</option>
                                    @endforeach
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
                            <table id="votePurchasesTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>User</th>
                                        <th>Nominee</th>
                                        <th>Package</th>
                                        <th class="text-center">Votes</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
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

        const table = $('#votePurchasesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.spotlight.vote-purchases.index') }}',
                data: function (d) {
                    d.search_query = document.getElementById('searchInput').value;
                    d.status       = document.getElementById('statusFilter').value;
                    d.package      = document.getElementById('packageFilter').value;
                }
            },
            columns: [
                { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'user_name',    name: 'user_id' },
                { data: 'nominee_name', name: 'nominee_id' },
                { data: 'package_label', name: 'package' },
                { data: 'votes_count',  name: 'votes_count', className: 'text-center' },
                { data: 'amount_formatted', name: 'amount_paid', className: 'text-center' },
                { data: 'status_badge',  name: 'status', className: 'text-center' },
                { data: 'action',        name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[0, 'desc']]
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

        document.getElementById('packageFilter').addEventListener('change', function () {
            table.ajax.reload(null, false);
        });

        document.getElementById('resetFilters').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('packageFilter').value = '';
            table.ajax.reload(null, false);
        });

    })();
</script>
@endpush
