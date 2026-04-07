@extends('layout.master-layout')
@section('title', 'Products')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Products</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Products</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        {{-- Stats Cards --}}
        <div class="row">
            <x-admin.stats-card icon="ri-store-2-line"        label="Total Products"    :count="$totalProducts"    color="primary" />
            <x-admin.stats-card icon="ri-checkbox-circle-line" label="Active Products"   :count="$activeProducts"   color="success" />
            <x-admin.stats-card icon="ri-close-circle-line"    label="Inactive Products" :count="$inactiveProducts" color="danger" />
            <x-admin.stats-card icon="ri-shapes-line"          label="Product Types"     :count="$uniqueTypes"      color="warning" />
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    {{-- Card Header --}}
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">All Products</h5>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                            <i class="ri-add-line align-bottom me-1"></i> Add Product
                        </a>
                    </div>

                    {{-- Custom Filters --}}
                    <div class="card-body border-bottom pb-3">
                        <div class="row g-3">
                            <div class="col-xl-4 col-md-6">
                                <div class="search-box">
                                    <input type="text" id="dtSearch" class="form-control search"
                                           placeholder="Search by name, SKU, display id...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <select id="filterType" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
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
                                <select id="filterCategory" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
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
                            <table id="productsTable"
                                   class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Created</th>
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

    const table = $('#productsTable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        order      : [[4, 'desc']],
        lengthMenu : [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom        : "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-end'i>>t<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: {
            processing  : '<div class="text-center py-3"><img src="{{ asset('default/loader.gif') }}" style="width:48px;" alt="Loading…"></div>',
            emptyTable  : '<div class="text-center py-5"><i class="ri-box-3-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No products found</p></div>',
            zeroRecords : '<div class="text-center py-5"><i class="ri-search-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No matching records</p></div>',
        },
        ajax: {
            url  : '{{ route('admin.products.data') }}',
            type : 'GET',
            data : function (d) {
                d.type     = $('#filterType').val();
                d.status   = $('#filterStatus').val();
                d.category = $('#filterCategory').val();
            },
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product',     name: 'product',     orderable: true,  searchable: true  },
            { data: 'price',       name: 'price',       orderable: true,  searchable: false },
            { data: 'status',      name: 'status',      orderable: false, searchable: false, className: 'text-center' },
            { data: 'created_at',  name: 'created_at',  orderable: true,  searchable: false },
            { data: 'action',      name: 'action',      orderable: false, searchable: false, className: 'text-center' },
        ],
    });

    // Debounced search
    let searchTimer;
    document.getElementById('dtSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(function () {
            table.search(val).draw();
        }, 400);
    });

    // Filters
    ['filterType', 'filterStatus', 'filterCategory'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            table.draw();
        });
    });

    // Reset
    document.getElementById('resetFilters').addEventListener('click', function () {
        document.getElementById('dtSearch').value = '';
        ['filterType', 'filterStatus', 'filterCategory'].forEach(id => document.getElementById(id).value = '');
        table.search('').draw();
    });
})();
</script>
@endpush
