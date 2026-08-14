@extends('layout.master-layout')

@section('title', $product->name)

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $product->name }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                {{-- Thumbnail Card --}}
                <div class="card">
                    <div class="card-body text-center">
                        @if($product->thumbnail)
                            <img src="{{ asset('/' . $product->thumbnail) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 250px; object-fit: cover;">
                        @else
                            <div class="py-5 bg-light rounded">
                                <i class="ri-image-line" style="font-size: 4rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No thumbnail</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Status Summary --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Status:</span>
                            <span>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Featured:</span>
                            <span>
                                @if($product->is_featured)
                                    <span class="badge bg-warning text-dark">Featured</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Type:</span>
                            <span class="badge bg-info">{{ ucfirst($product->type) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Stock:</span>
                            <span>
                                @if($product->track_stock)
                                    <span class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }}">{{ $product->stock }} units</span>
                                @else
                                    <span class="badge bg-secondary">Unlimited</span>
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Category:</span>
                            <span>{{ $product->category ? $product->category->name : '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card">
                    <div class="card-body d-flex gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary flex-fill">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                        <button type="button" class="btn btn-danger flex-fill delete-btn" data-id="{{ $product->id }}">
                            <i class="ri-delete-bin-line me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                {{-- Basic Info --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Name:</div>
                            <div class="col-md-9">{{ $product->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Slug:</div>
                            <div class="col-md-9"><code>{{ $product->slug }}</code></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Brand:</div>
                            <div class="col-md-9">{{ $product->brand ?: '—' }}</div>
                        </div>
                        @if($product->short_description)
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Short Description:</div>
                            <div class="col-md-9">{{ $product->short_description }}</div>
                        </div>
                        @endif
                        @if($product->description)
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Description:</div>
                            {{-- Quill stores HTML — render as-is (same as events show page) --}}
                            <div class="col-md-9">{!! $product->description !!}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Pricing Info --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pricing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Regular Price:</div>
                            <div class="col-md-9">${{ number_format($product->price, 2) }}</div>
                        </div>
                        @if($product->sale_price)
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Sale Price:</div>
                            <div class="col-md-9 text-danger fw-semibold">${{ number_format($product->sale_price, 2) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-semibold text-muted">Discount:</div>
                            <div class="col-md-9">
                                -${{ number_format($product->discount_amount, 2) }}
                                <span class="badge bg-danger ms-2">{{ $product->discount_percentage }}% OFF</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Vendor Info --}}
                @if($product->vendor_name || $product->vendor_email || $product->vendor_phone)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Vendor Information</h5>
                    </div>
                    <div class="card-body">
                        @if($product->vendor_name)
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Name:</div>
                            <div class="col-md-9">{{ $product->vendor_name }}</div>
                        </div>
                        @endif
                        @if($product->vendor_email)
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Email:</div>
                            <div class="col-md-9"><a href="mailto:{{ $product->vendor_email }}">{{ $product->vendor_email }}</a></div>
                        </div>
                        @endif
                        @if($product->vendor_phone)
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Phone:</div>
                            <div class="col-md-9">{{ $product->vendor_phone }}</div>
                        </div>
                        @endif
                        @if($product->vendor_address)
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Address:</div>
                            <div class="col-md-9">{{ $product->vendor_address }}</div>
                        </div>
                        @endif
                        @if($product->vendor_details)
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Details:</div>
                            <div class="col-md-9">{{ $product->vendor_details }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Product Images --}}
                @if($product->images->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Gallery Images ({{ $product->images->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($product->images as $image)
                            <div class="col-4 col-md-3">
                                <a href="{{ asset('/' . $image->image) }}" target="_blank">
                                    <img src="{{ asset('/' . $image->image) }}" alt="Product Image" class="img-thumbnail" style="height: 120px; width: 100%; object-fit: cover;">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Timestamps --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Timestamps</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Created:</div>
                            <div class="col-md-9">{{ $product->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Updated:</div>
                            <div class="col-md-9">{{ $product->updated_at->format('M d, Y h:i A') }}</div>
                        </div>
                        @if($product->deleted_at)
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Deleted:</div>
                            <div class="col-md-9 text-danger">{{ $product->deleted_at->format('M d, Y h:i A') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        'use strict';

        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('This will permanently delete this product and all associated images.', {
                title: 'Delete Product?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (!confirmed) return;
                axios.delete(`{{ url('/products') }}/${id}`)
                    .then(res => {
                        Toast.success('Product deleted successfully.');
                        window.location.href = '{{ route('admin.products.index') }}';
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
