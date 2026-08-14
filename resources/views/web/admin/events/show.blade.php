@extends('layout.master-layout')

@section('title', 'Event Details')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Event Details: {{ $event->title }}</h4>
                </div>
            </div>
        </div>

        
            
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="card mb-4">
                                <div class="card-body">

                                    <div class="mb-3">
                                        <label for="title" class="form-label">
                                            Event Title <span class="text-danger">*</span>
                                        </label>
                                        <input disabled type="text" class="form-control @error('title') is-invalid @enderror"
                                            id="title" name="title" value="{{ old('title', $event->title) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <div class="p-3 border rounded bg-light" style="min-height:100px;">{!! old('description', $event->description) !!}</div>
                                    </div>

                                </div>
                            </div>
                            <div class="card mb-4">

                                <div class="card-body">

                                    <h5 class="mb-4 fw-semibold">Event Location</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="venue_name" class="form-label">Venue Name</label>
                                                <input disabled type="text" class="form-control @error('venue_name') is-invalid @enderror" id="venue_name" name="venue_name" value="{{ old('venue_name', $event->venue_name) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="address" class="form-label">Address</label>
                                                <input disabled type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $event->address) }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                                <input disabled type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $event->city) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                                <input disabled type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $event->state) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1 fw-semibold">Event Media</h5>
                                        <div class="flex-shrink-0">
                                            
                                        </div>
                                    </div>
                                </div>
                        <div class="card-body">
                            <div id="event-media-container">
                                @if($event->media && $event->media->count() > 0)
                                    <div class="row mb-4">
                                        <h6 class="text-muted mb-3">Existing Media</h6>
                                        @foreach($event->media as $mediaItem)
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-2 text-center h-100">
                                                @if($mediaItem->media_type === 'image')
                                                    <img src="{{ asset($mediaItem->file_path) }}" class="img-fluid rounded mb-2" style="max-height: 120px; object-fit: contain;">
                                                @else
                                                    <video src="{{ asset($mediaItem->file_path) }}" class="rounded mb-2" style="max-height: 120px; max-width: 100%;" controls></video>
                                                @endif
                                                <p class="small text-truncate mb-0">{{ $mediaItem->file_name }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <hr>
                                    <h6 class="text-muted mb-3">Add New Media</h6>
                                @endif

                                <div class="media-item border p-3 rounded mb-3 bg-light">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Media Type</label>
                                                <select disabled name="event_media[0][type]" class="form-select media-type-select">
                                                    <option value="image">Image</option>
                                                    <option value="video">Video</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="mb-2">
                                                <label class="form-label">Upload File</label>
                                                <input disabled type="file" name="event_media[0][file]" class="form-control media-file-input" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="media-preview-container border bg-white rounded d-flex align-items-center justify-content-center" style="height: 60px; overflow: hidden;">
                                                <span class="text-muted small">No Preview</span>
                                            </div>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom-dashed">
                    <h5 class="card-title mb-0">Assign Artists</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Select Artist</label>
                                <div class="input-group">
                                    <select disabled id="artist-select" class="form-select">
                                        <option value="">-- Choose an Artist --</option>
                                        @foreach($artists as $artist)
                                            <option value="{{ $artist->id }}" 
                                                data-name="{{ $artist->profile->name ?? $artist->email }}" 
                                                data-image="{{ $artist->profile && $artist->profile->avatar_url ? asset($artist->profile->avatar_url) : asset('assets/images/users/user-dummy-img.jpg') }}">
                                                {{ $artist->profile->name ?? $artist->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                </div>
                            </div>
                            <div id="assigned-artists-container" class="row g-3 mt-2">
                                @foreach($event->artists as $assignedArtist)
                                    <div class="col-sm-6 col-md-4 col-lg-3 artist-card-item">
                                        <div class="card border shadow-none mb-0">
                                            <div class="card-body text-center p-3">

                                                <img src="{{ $assignedArtist->profile && $assignedArtist->profile->avatar_url ? asset($assignedArtist->profile->avatar_url) : asset('assets/images/users/user-dummy-img.jpg') }}" alt="" class="rounded-circle avatar-md mb-2 object-fit-cover" style="width: 64px; height: 64px;">
                                                <h6 class="mb-2 text-truncate" title="{{ $assignedArtist->profile->name ?? $assignedArtist->email }}">{{ $assignedArtist->profile->name ?? $assignedArtist->email }}</h6>
                                                
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header border-bottom-dashed">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Ticket Tiers <span class="text-danger">*</span></h5>
                                <div class="flex-shrink-0">
                                    
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="ticket-tiers-container">
                                @forelse($event->ticketTiers as $index => $tier)
                                <div class="tier-item border p-3 rounded mb-3 bg-light">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Tier Name</label>
                                                <input disabled type="text" name="ticket_tiers[{{ $index }}][name]" class="form-control" value="{{ $tier->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Price ($)</label>
                                                <input disabled type="number" step="0.01" name="ticket_tiers[{{ $index }}][price]" class="form-control" value="{{ $tier->price }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Service Fee ($)</label>
                                                <input disabled type="number" step="0.01" name="ticket_tiers[{{ $index }}][service_fee]" class="form-control" value="{{ $tier->service_fee }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Quantity</label>
                                                <input disabled type="number" name="ticket_tiers[{{ $index }}][quantity_available]" class="form-control" value="{{ $tier->quantity_available }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2 mt-4 text-end">
                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Sale Start Date</label>
                                                <input disabled type="datetime-local" name="ticket_tiers[{{ $index }}][sale_starts_at]" class="form-control" value="{{ $tier->sale_starts_at ? \Carbon\Carbon::parse($tier->sale_starts_at)->format('Y-m-d\TH:i') : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Sale End Date</label>
                                                <input disabled type="datetime-local" name="ticket_tiers[{{ $index }}][sale_ends_at]" class="form-control" value="{{ $tier->sale_ends_at ? \Carbon\Carbon::parse($tier->sale_ends_at)->format('Y-m-d\TH:i') : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label">Description</label>
                                                <textarea disabled name="ticket_tiers[{{ $index }}][description]" class="form-control" rows="2">{{ $tier->description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="tier-item border p-3 rounded mb-3 bg-light">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Tier Name</label>
                                                <input disabled type="text" name="ticket_tiers[0][name]" class="form-control" required placeholder="e.g. Early Bird">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Price ($)</label>
                                                <input disabled type="number" step="0.01" name="ticket_tiers[0][price]" class="form-control" required placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Service Fee ($)</label>
                                                <input disabled type="number" step="0.01" name="ticket_tiers[0][service_fee]" class="form-control" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Quantity</label>
                                                <input disabled type="number" name="ticket_tiers[0][quantity_available]" class="form-control" placeholder="Unlimited">
                                            </div>
                                        </div>
                                        <div class="col-md-2 mt-4 text-end">
                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Sale Start Date</label>
                                                <input disabled type="datetime-local" name="ticket_tiers[0][sale_starts_at]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Sale End Date</label>
                                                <input disabled type="datetime-local" name="ticket_tiers[0][sale_ends_at]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label">Description</label>
                                                <textarea disabled name="ticket_tiers[0][description]" class="form-control" rows="2" placeholder="Brief description of this tier..."></textarea>
                                            </div>
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
                                <label class="form-label fw-semibold">Cover Image</label>
                                <div class="mt-2">
                                    <img src="{{ $event->cover_image_path ? asset($event->cover_image_path) : asset('admin/assets/images/default/no-img.png') }}"
                                        class="img-fluid rounded shadow-sm" style="max-height: 220px; width: 100%; object-fit: cover;">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Promo Video</label>
                                @if($event->promo_video_path)
                                    <video src="{{ asset($event->promo_video_path) }}" class="w-100 rounded mt-2" style="max-height: 220px;" controls></video>
                                @else
                                    <p class="text-muted small mt-1">No promo video uploaded.</p>
                                @endif
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="starts_at" class="form-label">Starts At <span class="text-danger">*</span></label>
                                <input disabled type="text" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d H:i')) }}" data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true" placeholder="Select date and time" required>
                            </div>
                            <div class="mb-3">
                                <label for="ends_at" class="form-label">Ends At <span class="text-danger">*</span></label>
                                <input disabled type="text" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d H:i')) }}" data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true" placeholder="Select date and time" required>
                            </div>
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <input disabled type="text" class="form-control @error('timezone') is-invalid @enderror" id="timezone" name="timezone" value="{{ old('timezone', $event->timezone) }}" placeholder="e.g. EST">
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="event_type" class="form-label">Event Type <span class="text-danger">*</span></label>
                                <select disabled class="form-control" name="event_type" id="event_type" required>
                                    @foreach(['featured', 'workshop', 'art_exhibition', 'pop_up', 'networking', 'other'] as $type)
                                        <option value="{{ $type }}" {{ old('event_type', $event->event_type) == $type ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select disabled class="form-control" name="status" id="status2" required>
                                    @foreach(['draft', 'published', 'cancelled', 'completed'] as $status)
                                        <option value="{{ $status }}" {{ old('status', $event->status) == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch form-switch-md">
                                    <input disabled class="form-check-input" type="checkbox" id="is_featured" name="is_featured" {{ old('is_featured', $event->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">Mark as Featured</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch form-switch-md">
                                    <input disabled class="form-check-input" type="checkbox" id="is_spotlight_eligible" name="is_spotlight_eligible" {{ old('is_spotlight_eligible', $event->is_spotlight_eligible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_spotlight_eligible">Spotlight Eligible</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="hosted_by" class="form-label">Hosted By</label>
                                <input disabled type="text" class="form-control @error('hosted_by') is-invalid @enderror" id="hosted_by" name="hosted_by" value="{{ old('hosted_by', $event->hosted_by) }}" placeholder="e.g. OSI Team">
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            
                            <a href="{{ route('admin.events.index') }}" class="btn btn-light w-100 mt-2">Back to Events</a>
                        </div>
                    </div>
                </div>
            </div>
        
    </div>
</div>


@endsection
