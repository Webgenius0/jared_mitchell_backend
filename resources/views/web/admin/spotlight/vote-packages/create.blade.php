@extends('layout.master-layout')

@section('title', 'Create Vote Package')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create Vote Package</h4>
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
                        <form action="{{ route('admin.spotlight.vote-packages.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Package Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" placeholder="e.g. Starter, Popular, Boost" required maxlength="100">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">The slug will be auto-generated from the name.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Votes Count <span class="text-danger">*</span></label>
                                    <input type="number" name="votes_count" class="form-control @error('votes_count') is-invalid @enderror"
                                           value="{{ old('votes_count', 1) }}" min="1" max="1000" required>
                                    @error('votes_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price ($) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price', 1.00) }}" min="0.01" max="99999.99" required>
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
                                              rows="2" maxlength="255" placeholder="Short description visible to users">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', 0) }}" min="0" max="999">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Lower numbers appear first.</div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isActive">Active</label>
                                        <div class="form-text">Inactive packages are hidden from users.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Create Package
                                </button>
                                <a href="{{ route('admin.spotlight.vote-packages.index') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Preview Card --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Preview</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">This is how the package will appear to users:</p>
                        <div class="border rounded p-3 text-center" id="previewBox">
                            <h4 class="mb-1" id="previewName">New Package</h4>
                            <h2 class="text-primary fw-bold mb-2">$<span id="previewPrice">1.00</span></h2>
                            <p class="mb-0">
                                <span id="previewVotes">1</span> Vote<span id="previewVotesPlural"></span>
                            </p>
                            <small class="text-muted" id="previewDesc"></small>
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

        const nameInput = document.querySelector('input[name="name"]');
        const votesInput = document.querySelector('input[name="votes_count"]');
        const priceInput = document.querySelector('input[name="price"]');
        const descInput = document.querySelector('textarea[name="description"]');

        const previewName = document.getElementById('previewName');
        const previewPrice = document.getElementById('previewPrice');
        const previewVotes = document.getElementById('previewVotes');
        const previewVotesPlural = document.getElementById('previewVotesPlural');
        const previewDesc = document.getElementById('previewDesc');

        function updatePreview() {
            previewName.textContent = nameInput.value || 'New Package';
            previewPrice.textContent = parseFloat(priceInput.value || 0).toFixed(2);
            previewVotes.textContent = votesInput.value || 0;
            previewVotesPlural.textContent = parseInt(votesInput.value) > 1 ? 's' : '';
            previewDesc.textContent = descInput.value || '';
        }

        nameInput.addEventListener('input', updatePreview);
        votesInput.addEventListener('input', updatePreview);
        priceInput.addEventListener('input', updatePreview);
        descInput.addEventListener('input', updatePreview);

        updatePreview();
    })();
</script>
@endpush
