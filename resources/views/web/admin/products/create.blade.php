@extends('layout.master-layout')

@section('title', 'Create Product')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create Product</h4>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.products.store',$target) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 {{ in_array('name', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="Product name" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('type', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Product Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="physical" {{ old('type', $type ?? 'physical') === 'physical' ? 'selected' : '' }}>Physical</option>
                                        <option value="digital" {{ old('type', $type ?? 'physical') === 'digital' ? 'selected' : '' }}>Digital</option>
                                    </select>
                                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 {{ in_array('description', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                                            placeholder="Short description">{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('price', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                                        value="{{ old('price') }}" placeholder="0.00" required>
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('discount_price', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Discount Price</label>
                                    <input type="number" step="0.01" name="discount_price" class="form-control @error('discount_price') is-invalid @enderror"
                                        value="{{ old('discount_price') }}" placeholder="0.00">
                                    @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('category', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select @error('category') is-invalid @enderror">
                                        <option value="standard" {{ old('category', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="vendors" {{ old('category') === 'vendors' ? 'selected' : '' }}>Vendors</option>
                                        <option value="digital_tools" {{ old('category') === 'digital_tools' ? 'selected' : '' }}>Digital Tools</option>
                                    </select>
                                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('status', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('stock', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror"
                                        value="{{ old('stock', 0) }}" placeholder="0">
                                    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('target_audience', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Target Audience</label>
                                    <input type="text" name="target_audience" class="form-control @error('target_audience') is-invalid @enderror"
                                        value="{{ old('target_audience') }}" placeholder="e.g. Teens, Artists">
                                    @error('target_audience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('delivery_type', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Delivery Type</label>
                                    <input type="text" name="delivery_type" class="form-control @error('delivery_type') is-invalid @enderror"
                                        value="{{ old('delivery_type') }}" placeholder="e.g. Pickup, Shipping">
                                    @error('delivery_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 {{ in_array('image', $components) ? '' : 'd-none' }}">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                                    <div class="form-text">PNG/JPG up to 2MB.</div>
                                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100">Save Product</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-light w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
