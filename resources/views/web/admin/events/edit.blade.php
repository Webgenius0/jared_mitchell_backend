@extends('layout.master-layout')

@section('title', 'Edit Event')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Event: {{ $event->title }}</h4>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Event Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $event->title) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $event->description) }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="venue_name" class="form-label">Venue Name</label>
                                        <input type="text" class="form-control @error('venue_name') is-invalid @enderror" id="venue_name" name="venue_name" value="{{ old('venue_name', $event->venue_name) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $event->address) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $event->city) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $event->state) }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header border-bottom-dashed">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Ticket Tiers <span class="text-danger">*</span></h5>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm" id="add-tier"><i class="ri-add-line align-middle me-1"></i> Add Tier</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="ticket-tiers-container">
                                @forelse($event->ticketTiers as $index => $tier)
                                <div class="tier-item border p-3 rounded mb-3 bg-light">
                                    <input type="hidden" name="ticket_tiers[{{ $index }}][id]" value="{{ $tier->id }}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Tier Name</label>
                                                <input type="text" name="ticket_tiers[{{ $index }}][name]" class="form-control" value="{{ $tier->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Price ($)</label>
                                                <input type="number" step="0.01" name="ticket_tiers[{{ $index }}][price]" class="form-control" value="{{ $tier->price }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="ticket_tiers[{{ $index }}][quantity_available]" class="form-control" value="{{ $tier->quantity_available }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2 mt-4 text-end">
                                            <button type="button" class="btn btn-soft-danger btn-icon waves-effect waves-light remove-tier"><i class="ri-delete-bin-5-line"></i></button>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="tier-item border p-3 rounded mb-3 bg-light">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Tier Name</label>
                                                <input type="text" name="ticket_tiers[0][name]" class="form-control" required placeholder="e.g. Early Bird">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Price ($)</label>
                                                <input type="number" step="0.01" name="ticket_tiers[0][price]" class="form-control" required placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="ticket_tiers[0][quantity_available]" class="form-control" placeholder="Leave blank for unlimited">
                                            </div>
                                        </div>
                                        <div class="col-md-2 mt-4 text-end">
                                            <button type="button" class="btn btn-soft-danger btn-icon waves-effect waves-light remove-tier"><i class="ri-delete-bin-5-line"></i></button>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <label for="cover_image" class="form-label">Cover Image</label>
                                <div class="position-relative d-inline-block">
                                    <div class="position-absolute top-100 start-100 translate-middle">
                                        <label for="cover_image" class="mb-0" data-bs-toggle="tooltip" data-bs-placement="right" title="Select Cover Image">
                                            <div class="avatar-xs">
                                                <div class="avatar-title bg-light border rounded-circle text-muted cursor-pointer">
                                                    <i class="ri-image-fill"></i>
                                                </div>
                                            </div>
                                        </label>
                                        <input class="form-control d-none" id="cover_image" name="cover_image" type="file" accept="image/png, image/gif, image/jpeg">
                                    </div>
                                    <div class="avatar-lg bg-light rounded shadow">
                                        <img src="{{ $event->cover_image_path ? asset('storage/' . $event->cover_image_path) : asset('admin/assets/images/default/no-img.png') }}" id="cover_image_preview" class="avatar-lg rounded object-fit-cover">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="promo_video" class="form-label">Promo Video (Max 20MB)</label>
                                <input type="file" class="form-control @error('promo_video') is-invalid @enderror" id="promo_video" name="promo_video" accept="video/*">
                                @if($event->promo_video_path)
                                    <p class="text-muted small mt-1">Current: <a href="{{ asset('storage/' . $event->promo_video_path) }}" target="_blank">View Video</a></p>
                                @endif
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="starts_at" class="form-label">Starts At <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d H:i')) }}" data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true" placeholder="Select date and time" required>
                            </div>
                            <div class="mb-3">
                                <label for="ends_at" class="form-label">Ends At <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d H:i')) }}" data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true" placeholder="Select date and time" required>
                            </div>
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <input type="text" class="form-control @error('timezone') is-invalid @enderror" id="timezone" name="timezone" value="{{ old('timezone', $event->timezone) }}" placeholder="e.g. EST">
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="event_type" class="form-label">Event Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="event_type" id="event_type" required>
                                    @foreach(['featured', 'workshop', 'art_exhibition', 'pop_up', 'networking', 'other'] as $type)
                                        <option value="{{ $type }}" {{ old('event_type', $event->event_type) == $type ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control" name="status" id="status2" required>
                                    @foreach(['draft', 'published', 'cancelled', 'completed'] as $status)
                                        <option value="{{ $status }}" {{ old('status', $event->status) == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" {{ old('is_featured', $event->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">Mark as Featured</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" id="is_spotlight_eligible" name="is_spotlight_eligible" {{ old('is_spotlight_eligible', $event->is_spotlight_eligible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_spotlight_eligible">Spotlight Eligible</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="hosted_by" class="form-label">Hosted By</label>
                                <input type="text" class="form-control @error('hosted_by') is-invalid @enderror" id="hosted_by" name="hosted_by" value="{{ old('hosted_by', $event->hosted_by) }}" placeholder="e.g. OSI Team">
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100">Update Event</button>
                            <a href="{{ route('admin.events.index') }}" class="btn btn-light w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Display validation errors in toast
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            Toastify({
                text: '{{ $error }}',
                duration: 4000,
                close: true,
                gravity: 'top',
                position: 'right',
                className: 'toastify toast-error',
            }).showToast();
        @endforeach
    @endif

    document.addEventListener('DOMContentLoaded', function() {
        let tierIndex = {{ $event->ticketTiers->count() }};
        const container = document.getElementById('ticket-tiers-container');
        const addButton = document.getElementById('add-tier');

        // Add Tier
        addButton.addEventListener('click', function() {
            const html = `
                <div class="tier-item border p-3 rounded mb-3 bg-light">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Tier Name</label>
                                <input type="text" name="ticket_tiers[${tierIndex}][name]" class="form-control" required placeholder="e.g. Regular">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Price ($)</label>
                                <input type="number" step="0.01" name="ticket_tiers[${tierIndex}][price]" class="form-control" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="ticket_tiers[${tierIndex}][quantity_available]" class="form-control" placeholder="Leave blank for unlimited">
                            </div>
                        </div>
                        <div class="col-md-2 mt-4 text-end">
                            <button type="button" class="btn btn-soft-danger btn-icon waves-effect waves-light remove-tier"><i class="ri-delete-bin-5-line"></i></button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            tierIndex++;
        });

        // Remove Tier
        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-tier')) {
                const item = e.target.closest('.tier-item');
                if (container.children.length > 1) {
                    item.remove();
                } else {
                    alert('At least one ticket tier is required.');
                }
            }
        });

        // Cover Image Preview
        document.getElementById('cover_image').addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('cover_image_preview').src = event.target.result;
            }
            if (e.target.files[0]) {
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });
</script>
@endpush
@endsection
