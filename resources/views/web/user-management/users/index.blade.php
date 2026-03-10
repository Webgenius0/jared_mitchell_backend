@extends('layout.master-layout')
@section('title', 'User Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">User Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        {{-- Stats Cards --}}
        <div class="row">
            <x-admin.stats-card icon="ri-team-line"         label="Total Users"    :count="$totalUsers"    color="primary" />
            <x-admin.stats-card icon="ri-user-follow-line"  label="Active Users"   :count="$activeUsers"   color="success" />
            <x-admin.stats-card icon="ri-user-unfollow-line" label="Inactive Users" :count="$inactiveUsers" color="danger" />
            <x-admin.stats-card icon="ri-shield-user-line"  label="Total Roles"    :count="$totalRoles"    color="warning" />
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    {{-- Card Header --}}
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">All Users</h5>
                        @can('create users')
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                                <i class="ri-add-line align-bottom me-1"></i> Add New User
                            </a>
                        @endcan
                    </div>

                    {{-- Custom Filters --}}
                    <div class="card-body border-bottom pb-3">
                        <div class="row g-3">
                            <div class="col-xl-4 col-md-6">
                                <div class="search-box">
                                    <input type="text" id="dtSearch" class="form-control search"
                                           placeholder="Search by name or email...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <select id="filterRole" class="form-select">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <select id="filterStatus" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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
                            <table id="usersTable"
                                   class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>User</th>
                                        <th>Roles</th>
                                        <th class="text-center">Status</th>
                                        <th>Created</th>
                                        <th class="text-center" style="width:130px;">Actions</th>
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
<x-admin.confirm-delete-modal />

<script>
(function () {
    'use strict';

    // ── DataTable Initialisation ──────────────────────────────────────────
    const table = $('#usersTable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        order      : [[4, 'desc']],
        lengthMenu : [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom        : "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-end'i>>t<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",

        language: {
            processing    : '<div class="text-center py-3"><img src="{{ asset('default/loader.gif') }}" style="width:48px;" alt="Loading…"></div>',
            emptyTable    : '<div class="text-center py-5"><i class="ri-user-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No users found</p></div>',
            zeroRecords   : '<div class="text-center py-5"><i class="ri-search-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No matching records</p></div>',
        },

        ajax: {
            url  : '{{ route('admin.users.getdata') }}',
            type : 'GET',
            data : function (d) {
                // Append custom filter values to every server request
                d.role   = $('#filterRole').val();
                d.status = $('#filterStatus').val();
            },
        },

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user',        name: 'user',         orderable: true,  searchable: true  },
            { data: 'roles',       name: 'roles',        orderable: false, searchable: false },
            { data: 'status',      name: 'status',       orderable: false, searchable: false, className: 'text-center' },
            { data: 'created_at',  name: 'created_at',   orderable: true,  searchable: false },
            { data: 'action',      name: 'action',       orderable: false, searchable: false, className: 'text-center' },
        ],

        // Re-bind status toggles after every draw (new DOM nodes)
        drawCallback: function () {
            bindStatusToggles();
        },
    });

    // ── Custom search with 400 ms debounce ───────────────────────────────
    let searchTimer;
    document.getElementById('dtSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(function () {
            table.search(val).draw();
        }, 400);
    });

    // ── Dropdown filters ─────────────────────────────────────────────────
    ['filterRole', 'filterStatus'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            table.draw();
        });
    });

    // ── Reset ─────────────────────────────────────────────────────────────
    document.getElementById('resetFilters').addEventListener('click', function () {
        document.getElementById('dtSearch').value     = '';
        document.getElementById('filterRole').value   = '';
        document.getElementById('filterStatus').value = '';
        table.search('').draw();
    });

    // ── Status Toggle (AJAX) ──────────────────────────────────────────────
    function bindStatusToggles() {
        document.querySelectorAll('.status-toggle:not([data-bound])').forEach(function (toggle) {
            toggle.dataset.bound = '1';
            toggle.addEventListener('change', function () {
                const url      = this.dataset.url;
                const checkbox = this;

                axios.post(url)
                    .then(function (res) {
                        Toast.fromResponse(res.data);
                    })
                    .catch(function (err) {
                        checkbox.checked = !checkbox.checked;
                        const data = err.response?.data;
                        Toast.error(data?.message || 'Failed to change status.');
                    });
            });
        });
    }

})();
</script>
@endpush

