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
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">Order</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categories as $category)
                                        <tr>
                                            <td>{{ $category->sort_order }}</td>
                                            <td>{{ $category->name }}</td>
                                            <td><code>{{ $category->slug }}</code></td>
                                            <td>
                                                <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button class="btn btn-sm btn-soft-info" onclick="editCategory({{ json_encode($category) }})">
                                                        <i class="ri-pencil-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-soft-danger" onclick="deleteCategory({{ $category->id }})">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
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
    const categoryForm = document.getElementById('categoryForm');
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    
    function resetForm() {
        categoryForm.reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('modalTitle').innerText = 'Add Artist Category';
        document.getElementById('saveBtn').innerText = 'Save Category';
    }

    function editCategory(cat) {
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
    }

    categoryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('categoryId').value;
        const url = id ? `{{ url('admin/cms/artist-categories') }}/${id}` : `{{ route('admin.cms.artist-categories.store') }}`;
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
                setTimeout(() => window.location.reload(), 800);
            })
            .catch(err => {
                const msg = err.response?.data?.message || 'Something went wrong.';
                Toast.error(msg);
                if (err.response?.data?.errors) {
                    console.error(err.response.data.errors);
                }
            });
    });

    function deleteCategory(id) {
        Alert.confirm('This will permanently delete the category.', {
            type: 'danger',
            confirmText: 'Yes, delete it'
        }).then(confirmed => {
            if (!confirmed) return;
            axios.delete(`{{ url('admin/cms/artist-categories') }}/${id}`)
                .then(res => {
                    Toast.success(res.data.message);
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch(err => {
                    Toast.error(err.response?.data?.message || 'Delete failed.');
                });
        });
    }
</script>
@endpush
