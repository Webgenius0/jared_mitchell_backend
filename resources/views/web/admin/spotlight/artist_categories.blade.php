@extends('layout.master-layout')
@section('title', 'Artist Categories')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Artist Categories</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.artist-spotlights.index') }}">Artist Spotlights</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Manage Categories</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
                            <i class="ri-add-line align-bottom me-1"></i> Add Category
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categoriesTable" class="table table-bordered align-middle table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 80px;">Order</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 150px;">Actions</th>
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

{{-- Category Modal --}}
<div class="modal fade border-0" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalTitle">Add Artist Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryId">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" required placeholder="e.g. Visual Artist">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug (Optional)</label>
                        <input type="text" class="form-control" id="categorySlug" placeholder="Auto-generated if empty">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="categoryOrder" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="categoryStatus" checked>
                                <label class="form-check-label" for="categoryStatus">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        // ── DataTable Initialisation ──────────────────────────────────────────
        const table = $('#categoriesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.artist-categories.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'sort_order', name: 'sort_order' },
                { data: 'name', name: 'name' },
                { data: 'slug', name: 'slug', render: d => `<code>${d}</code>` },
                { data: 'is_active', name: 'is_active', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            }
        });

        const categoryForm = document.getElementById('categoryForm');
        const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        
        window.resetForm = function() {
            categoryForm.reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('modalTitle').innerText = 'Add Artist Category';
            document.getElementById('saveBtn').innerText = 'Save Category';
        };

        $(document).on('click', '.edit-btn', function() {
            const cat = $(this).data('category');
            resetForm();
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('categoryName').value = cat.name;
            document.getElementById('categorySlug').value = cat.slug;
            document.getElementById('categoryDescription').value = cat.description;
            document.getElementById('categoryOrder').value = cat.sort_order;
            document.getElementById('categoryStatus').checked = !!cat.is_active;
            document.getElementById('modalTitle').innerText = 'Edit Category';
            document.getElementById('saveBtn').innerText = 'Update Category';
            categoryModal.show();
        });

        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('categoryId').value;
            const url = id ? `{{ url('admin/artist-categories') }}/${id}` : `{{ route('admin.artist-categories.store') }}`;
            const method = id ? 'put' : 'post';
            
            const data = {
                name: document.getElementById('categoryName').value,
                slug: document.getElementById('categorySlug').value,
                description: document.getElementById('categoryDescription').value,
                sort_order: document.getElementById('categoryOrder').value,
                is_active: document.getElementById('categoryStatus').checked,
            };

            axios[method](url, data)
                .then(res => {
                    Toast.success(res.data.message);
                    categoryModal.hide();
                    table.draw(false);
                })
                .catch(err => {
                    const msg = err.response?.data?.message || 'Something went wrong.';
                    Toast.error(msg);
                });
        });

        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('This will permanently delete the category.', {
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (!confirmed) return;
                axios.delete(`{{ url('admin/artist-categories') }}/${id}`)
                    .then(res => {
                        Toast.success(res.data.message);
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
