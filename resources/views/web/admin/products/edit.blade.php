@extends('layout.master-layout')

@section('title', 'Edit Product')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Product: {{ $product->name }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                {{-- Main Content Column --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug <span class="text-muted fw-normal">(Optional — auto-generated if empty)</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" placeholder="e.g. my-awesome-product">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="brand" class="form-label">Brand</label>
                                        <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" value="{{ old('brand', $product->brand) }}" placeholder="e.g. Nike">
                                        @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $id => $name)
                                                <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="short_description" class="form-label">Short Description</label>
                                <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="3" maxlength="500" placeholder="Brief product summary...">{{ old('short_description', $product->short_description) }}</textarea>
                                @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Full Description</label>
                                <div id="descriptionEditor" class="snow-editor @error('description') is-invalid @enderror" style="height: 260px;"></div>
                                <input type="hidden" id="description" name="description" value="{{ old('description', $product->description) }}">
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Pricing Card --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pricing</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Regular Price ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sale_price" class="form-label">Sale Price ($) <span class="text-muted fw-normal">(Optional)</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" placeholder="Leave blank if no sale">
                                        @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="text-muted">Must be less than regular price.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Card --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Inventory</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Product Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="physical" {{ old('type', $product->type) == 'physical' ? 'selected' : '' }}>Physical</option>
                                            <option value="digital" {{ old('type', $product->type) == 'digital' ? 'selected' : '' }}>Digital</option>
                                            <option value="service" {{ old('type', $product->type) == 'service' ? 'selected' : '' }}>Service</option>
                                        </select>
                                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock Quantity</label>
                                        <input type="number" min="0" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}">
                                        @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="mb-3 form-check form-switch form-switch-md">
                                        <input class="form-check-input" type="checkbox" id="track_stock" name="track_stock" value="1" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="track_stock">Track Stock</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vendor Information Card --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Vendor Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_name" class="form-label">Vendor Name</label>
                                        <input type="text" class="form-control @error('vendor_name') is-invalid @enderror" id="vendor_name" name="vendor_name" value="{{ old('vendor_name', $product->vendor_name) }}" placeholder="e.g. ABC Supplies">
                                        @error('vendor_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_email" class="form-label">Vendor Email</label>
                                        <input type="email" class="form-control @error('vendor_email') is-invalid @enderror" id="vendor_email" name="vendor_email" value="{{ old('vendor_email', $product->vendor_email) }}" placeholder="vendor@example.com">
                                        @error('vendor_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_phone" class="form-label">Vendor Phone</label>
                                        <input type="text" class="form-control @error('vendor_phone') is-invalid @enderror" id="vendor_phone" name="vendor_phone" value="{{ old('vendor_phone', $product->vendor_phone) }}" placeholder="+1 (555) 123-4567">
                                        @error('vendor_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_address" class="form-label">Vendor Address</label>
                                        <input type="text" class="form-control @error('vendor_address') is-invalid @enderror" id="vendor_address" name="vendor_address" value="{{ old('vendor_address', $product->vendor_address) }}" placeholder="123 Main St, City">
                                        @error('vendor_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="vendor_details" class="form-label">Vendor Details</label>
                                <textarea class="form-control @error('vendor_details') is-invalid @enderror" id="vendor_details" name="vendor_details" rows="3" placeholder="Additional vendor notes...">{{ old('vendor_details', $product->vendor_details) }}</textarea>
                                @error('vendor_details') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Column --}}
                <div class="col-lg-4">
                    {{-- Thumbnail Card --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Product Thumbnail</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <div class="position-relative d-inline-block">
                                    <div class="position-absolute top-100 start-100 translate-middle">
                                        <label for="thumbnail" class="mb-0" data-bs-toggle="tooltip" title="Select Thumbnail">
                                            <div class="avatar-xs">
                                                <div class="avatar-title bg-light border rounded-circle text-muted cursor-pointer">
                                                    <i class="ri-image-fill"></i>
                                                </div>
                                            </div>
                                        </label>
                                        <input class="form-control d-none" id="thumbnail" name="thumbnail" type="file" accept="image/png, image/gif, image/jpeg">
                                    </div>
                                    <div class="avatar-xl bg-light rounded shadow">
                                        <img src="{{ $product->thumbnail ? asset('/' . $product->thumbnail) : asset('admin/assets/images/default/no-img.png') }}" id="thumbnail_preview" class="avatar-xl rounded object-fit-cover" style="width: 150px; height: 150px;">
                                    </div>
                                </div>
                                <p class="text-muted mt-2 small">Recommended: 500x500px (Max 2MB)</p>
                                @if($product->thumbnail)
                                    <div class="mt-2">
                                        <a href="{{ asset('/' . $product->thumbnail) }}" target="_blank" class="btn btn-sm btn-soft-primary">View Current</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Gallery Images Card --}}
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Product Gallery</h5>
                            @if($product->images->count() > 0)
                                <span class="badge bg-info">{{ $product->images->count() }} images</span>
                            @endif
                        </div>
                        <div class="card-body">
                            {{-- Existing Images --}}
                            @if($product->images->count() > 0)
                                <label class="form-label text-muted small">Check images to delete:</label>
                                <div class="row g-2 mb-3">
                                    @foreach($product->images as $image)
                                        <div class="col-4 col-md-3">
                                            <div class="position-relative">
                                                <img src="{{ asset('/' . $image->image) }}" alt="Gallery Image" class="img-thumbnail" style="height: 100px; width: 100%; object-fit: cover;">
                                                <div class="form-check position-absolute top-0 start-0 m-1">
                                                    <input class="form-check-input" type="checkbox" name="delete_images[]" value="{{ $image->id }}" id="delete_img_{{ $image->id }}">
                                                    <label class="form-check-label small text-white" for="delete_img_{{ $image->id }}" style="text-shadow: 0 0 3px rgba(0,0,0,0.8);">Delete</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                            @endif

                            {{-- Upload New Images --}}
                            <div class="mb-3">
                                <label for="images" class="form-label">Add New Images</label>
                                <input type="file" class="form-control @error('images.*') is-invalid @enderror @error('images') is-invalid @enderror" id="images" name="images[]" multiple accept="image/png, image/gif, image/jpeg">
                                @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @error('images.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">You can select multiple images. Each max 2MB. Supported: PNG, GIF, JPEG</small>
                            </div>
                            {{-- Gallery preview container --}}
                            <div id="gallery_preview" class="row g-2 mt-2"></div>
                        </div>
                    </div>

                    {{-- Status Card --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Status & Visibility</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 form-check form-switch form-switch-md">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active (Visible to customers)</label>
                            </div>
                            <div class="mb-3 form-check form-switch form-switch-md">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Mark as Featured</label>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Card --}}
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100">Update Product</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-light w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thumbnail Preview
        document.getElementById('thumbnail').addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('thumbnail_preview').src = event.target.result;
            }
            if (e.target.files[0]) {
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Auto-generate slug from name
        document.getElementById('name').addEventListener('blur', function() {
            const slugInput = document.getElementById('slug');
            if (!slugInput.value || slugInput.value === '{{ $product->slug }}' && this.value !== '{{ $product->name }}') {
                slugInput.value = this.value.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        // ── Quill editor ─────────────────────────────────────────────
        const descEditorEl = document.getElementById('descriptionEditor');
        const descInput = document.getElementById('description');
        const descEditor = Quill.find(descEditorEl);

        if (descEditor && descInput.value) {
            descEditor.clipboard.dangerouslyPasteHTML(descInput.value);
        }

        if (descEditor) {
            descEditor.on('text-change', function() {
                descInput.value = descEditor.getSemanticHTML();
            });
        }

        // Gallery Images Preview
        document.getElementById('images').addEventListener('change', function(e) {
            const container = document.getElementById('gallery_preview');
            container.innerHTML = '';
            Array.from(e.target.files).forEach(function(file, index) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const col = document.createElement('div');
                    col.className = 'col-4 col-md-3';
                    col.innerHTML = '<div class="position-relative">' +
                        '<img src="' + event.target.result + '" class="img-thumbnail" style="height: 100px; width: 100%; object-fit: cover;">' +
                        '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" style="font-size: 10px;">' + (index + 1) + '</span>' +
                        '</div>';
                    container.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>
@endpush
@endsection
