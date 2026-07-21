@extends('layout.master-layout')

@section('title', 'Edit Vote Package')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Vote Package: {{ $package->name }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.spotlight.vote-packages.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Back to Packages
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Package Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.spotlight.vote-packages.update', $package->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Package Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $package->name) }}" required maxlength="100">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Current slug: <code>{{ $package->slug }}</code></div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Votes Count <span class="text-danger">*</span></label>
                                    <input type="number" name="votes_count" class="form-control @error('votes_count') is-invalid @enderror"
                                           value="{{ old('votes_count', $package->votes_count) }}" min="1" max="1000" required>
                                    @error('votes_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price ($) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price', $package->price) }}" min="0.01" max="99999.99" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                              rows="2" maxlength="255" placeholder="Short description visible to users">{{ old('description', $package->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', $package->sort_order) }}" min="0" max="999">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Lower numbers appear first.</div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isActive">Active</label>
                                        <div class="form-text">Inactive packages are hidden from users.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Update Package
                                </button>
                                <a href="{{ route('admin.spotlight.vote-packages.index') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Package Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small d-block">Slug</label>
                            <code>{{ $package->slug }}</code>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block">Total Purchases</label>
                            <strong>{{ $package->purchases()->count() }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block">Created</label>
                            <strong>{{ $package->created_at->format('M d, Y h:i A') }}</strong>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small d-block">Last Updated</label>
                            <strong>{{ $package->updated_at->format('M d, Y h:i A') }}</strong>
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
    })();
</script>
@endpush
