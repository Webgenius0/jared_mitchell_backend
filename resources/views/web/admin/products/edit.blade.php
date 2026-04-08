@extends('layout.master-layout')

@section('title', 'Edit Product')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Product</h4>
                    <div>
                        <small class="text-muted">Display ID: {{ $product->display_id ?? 'auto' }} | SKU: {{ $product->sku ?? 'auto' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.products.update', [$product,$target]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 {{ in_array('name', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $product->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('type', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Product Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                        @php $currentType = old('type', $type ?? $product->type ?? 'physical'); @endphp
                                        <option value="physical" {{ $currentType === 'physical' ? 'selected' : '' }}>Physical</option>
                                        <option value="digital" {{ $currentType === 'digital' ? 'selected' : '' }}>Digital</option>
                                    </select>
                                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 {{ in_array('description', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('price', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                                           value="{{ old('price', $product->price) }}" required>
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('discount_price', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Discount Price</label>
                                    <input type="number" step="0.01" name="discount_price" class="form-control @error('discount_price') is-invalid @enderror"
                                           value="{{ old('discount_price', $product->discount_price) }}">
                                    @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('category', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Category</label>
                                    @php $currentCategory = old('category', $product->category ?? 'standard'); @endphp
                                    <select name="category" class="form-select @error('category') is-invalid @enderror">
                                        <option value="standard" {{ $currentCategory === 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="vendors" {{ $currentCategory === 'vendors' ? 'selected' : '' }}>Vendors</option>
                                        <option value="digital_tools" {{ $currentCategory === 'digital_tools' ? 'selected' : '' }}>Digital Tools</option>
                                    </select>
                                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('status', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Status</label>
                                    @php $currentStatus = old('status', $product->status ?? 'active'); @endphp
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('stock', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror"
                                           value="{{ old('stock', $product->stock) }}">
                                    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('target_audience', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Target Audience</label>
                                    <input type="text" name="target_audience" class="form-control @error('target_audience') is-invalid @enderror"
                                           value="{{ old('target_audience', $product->target_audience) }}">
                                    @error('target_audience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('delivery_type', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Delivery Type</label>
                                    <input type="text" name="delivery_type" class="form-control @error('delivery_type') is-invalid @enderror"
                                           value="{{ old('delivery_type', $product->delivery_type) }}">
                                    @error('delivery_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('image', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                                    <div class="form-text">PNG/JPG up to 2MB.</div>
                                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if($product->image)
                                        <div class="mt-2">
                                            <small class="text-muted d-block">Current image:</small>
                                            <img src="{{ asset('storage/' . $product->image) }}" class="img-thumbnail" style="max-height: 120px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100">Update Product</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-light w-100 mt-2">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
