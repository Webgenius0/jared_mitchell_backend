@extends('layout.master-layout')

@section('title', 'Products')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Products</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Create Product
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="productsTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
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

        const table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.products.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'product_info', name: 'name' },
                { data: 'category_name', name: 'category_name', searchable: false },
                { data: 'price_display', name: 'price', className: 'text-center' },
                { data: 'stock_info', name: 'stock', className: 'text-center' },
                { data: 'status', name: 'is_active', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[0, 'desc']]
        });

        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('This will permanently delete the product and all associated images.', {
                title: 'Delete Product?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (!confirmed) return;
                axios.delete(`{{ url('/products') }}/${id}`)
                    .then(res => {
                        Toast.success('Product deleted successfully.');
                        table.draw(false);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Delete failed.');
                    });
            });
        });
    })();
</script>
@endpush
@endsection
