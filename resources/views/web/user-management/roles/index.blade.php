@extends('layout.master-layout')
@section('title', 'Role Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Role Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Roles</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    {{-- Card Header --}}
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">All Roles</h5>
                        @can('create roles')
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                                <i class="ri-add-line align-bottom me-1"></i> Create Role
                            </a>
                        @endcan
                    </div>

                    {{-- Custom Search --}}
                    <div class="card-body border-bottom pb-3">
                        <div class="row g-3">
                            <div class="col-xl-5 col-md-6">
                                <div class="search-box">
                                    <input type="text" id="dtSearch" class="form-control search"
                                           placeholder="Search by role name...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-3">
                                <button type="button" id="resetFilters" class="btn btn-soft-danger w-100">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- DataTable --}}
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="rolesTable"
                                   class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Role Name</th>
                                        <th>Permissions</th>
                                        <th class="text-center">Users</th>
                                        <th>Guard</th>
                                        <th class="text-center" style="width:120px;">Actions</th>
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
    const table = $('#rolesTable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        order      : [[1, 'asc']],
        lengthMenu : [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom        : "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-end'i>>t<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",

        language: {
            processing  : '<div class="text-center py-3"><img src="{{ asset('default/loader.gif') }}" style="width:48px;" alt="Loading…"></div>',
            emptyTable  : '<div class="text-center py-5"><i class="ri-shield-user-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No roles found</p></div>',
            zeroRecords : '<div class="text-center py-5"><i class="ri-search-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No matching records</p></div>',
        },

        ajax: {
            url  : '{{ route('admin.roles.getdata') }}',
            type : 'GET',
        },

        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false },
            { data: 'role',         name: 'role',          orderable: true,  searchable: true  },
            { data: 'permissions',  name: 'permissions',   orderable: false, searchable: false },
            { data: 'users',        name: 'users',         orderable: false, searchable: false, className: 'text-center' },
            { data: 'guard',        name: 'guard',         orderable: false, searchable: false },
            { data: 'action',       name: 'action',        orderable: false, searchable: false, className: 'text-center' },
        ],
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

    // ── Reset ─────────────────────────────────────────────────────────────
    document.getElementById('resetFilters').addEventListener('click', function () {
        document.getElementById('dtSearch').value = '';
        table.search('').draw();
    });

})();
</script>
@endpush

