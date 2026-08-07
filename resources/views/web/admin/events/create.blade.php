@extends('layout.master-layout')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" />
@endpush

@section('title', 'Create Event')

@section('content')
    @include('components.admin.flash-message')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Create Event</h4>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
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
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                id="title" name="title" value="{{ old('title') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <div id="descriptionEditor" class="snow-editor" style="height:220px;"></div>
                                            <input type="hidden" id="description" name="description"
                                                value="{{ old('description') }}">
                                        </div>

                                    </div>
                                </div>

                                {{-- Event location --}}
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="mb-4 fw-semibold">Event Location</h5>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="venue_name" class="form-label">Venue Name</label>
                                                    <input type="text"
                                                        class="form-control @error('venue_name') is-invalid @enderror"
                                                        id="venue_name" name="venue_name" value="{{ old('venue_name') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="address" class="form-label">Address</label>
                                                    <input type="text"
                                                        class="form-control @error('address') is-invalid @enderror"
                                                        id="address" name="address" value="{{ old('address') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="city" class="form-label">
                                                        City <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('city') is-invalid @enderror"
                                                        id="city" name="city" value="{{ old('city') }}" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="state" class="form-label">
                                                        State <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('state') is-invalid @enderror"
                                                        id="state" name="state" value="{{ old('state') }}" required>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                {{-- Event media --}}
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <h5 class="card-title mb-0 flex-grow-1 fw-semibold">Event Media</h5>
                                            <div class="flex-shrink-0">
                                                <button type="button" class="btn btn-soft-primary btn-sm" id="add-media"><i
                                                        class="ri-add-line align-middle me-1"></i> Add Media</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="event-media-container">
                                            <div class="media-item border p-3 rounded mb-3 bg-light">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3">
                                                        <div class="mb-2">
                                                            <label class="form-label">Media Type <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="event_media[0][type]"
                                                                class="form-select media-type-select" required>
                                                                <option value="image">Image</option>
                                                                <option value="video">Video</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="mb-2">
                                                            <label class="form-label">Upload File <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="file" name="event_media[0][file]"
                                                                class="form-control media-file-input" required
                                                                accept="image/*">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <div class="media-preview-container border bg-white rounded d-flex align-items-center justify-content-center"
                                                            style="height: 60px; overflow: hidden;">
                                                            <span class="text-muted small">No Preview</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 text-end">
                                                        <button type="button"
                                                            class="btn btn-soft-danger btn-icon waves-effect waves-light remove-media"><i
                                                                class="ri-delete-bin-5-line"></i></button>
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
                                        <select id="artist-select" class="form-select">
                                            <option value="">-- Choose an Artist --</option>
                                            @foreach ($artists as $artist)
                                                <option value="{{ $artist->id }}"
                                                    data-name="{{ $artist->profile->name ?? $artist->email }}"
                                                    data-image="{{ $artist->profile && $artist->profile->avatar_url ? asset($artist->profile->avatar_url) : asset('assets/images/users/user-dummy-img.jpg') }}">
                                                    {{ $artist->profile->name ?? $artist->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="add-artist-btn" class="btn btn-primary">Add
                                            Artist</button>
                                    </div>
                                </div>
                                <div id="assigned-artists-container" class="d-flex flex-wrap gap-2 mt-2">
                                    <!-- Artist capsules will be injected here -->
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header border-bottom-dashed">
                                <h5 class="card-title mb-0">Assign Sponsors</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Select Sponsor</label>
                                    <div class="input-group">
                                        <select id="sponsor-select" class="form-select">
                                            <option value="">-- Choose a Sponsor --</option>
                                            @foreach ($sponsors as $sponsor)
                                                <option value="{{ $sponsor->id }}"
                                                    data-name="{{ $sponsor->name }}"
                                                    data-image="{{ $sponsor->logo ? asset($sponsor->logo) : asset('admin/assets/images/default/no-img.png') }}">
                                                    {{ $sponsor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="add-sponsor-btn" class="btn btn-primary">Add
                                            Sponsor</button>
                                    </div>
                                </div>
                                <div id="assigned-sponsors-container" class="d-flex flex-wrap gap-2 mt-2">
                                    <!-- Sponsor capsules will be injected here -->
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header border-bottom-dashed">
                                <div class="d-flex align-items-center">
                                    <h5 class="card-title mb-0 flex-grow-1">Ticket Tiers <span
                                            class="text-danger">*</span>
                                    </h5>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-soft-primary btn-sm" id="add-tier"><i
                                                class="ri-add-line align-middle me-1"></i> Add Tier</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="ticket-tiers-container">
                                    <div class="tier-item border p-3 rounded mb-3 bg-light">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="mb-2">
                                                    <label class="form-label">Tier Name</label>
                                                    <input type="text" name="ticket_tiers[0][name]"
                                                        class="form-control" required placeholder="e.g. Early Bird">
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="mb-2">
                                                    <label class="form-label">Price ($)</label>
                                                    <input type="number" step="0.01" name="ticket_tiers[0][price]"
                                                        class="form-control" required placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-2">
                                                    <label class="form-label">Service Fee ($)</label>
                                                    <input type="number" step="0.01"
                                                        name="ticket_tiers[0][service_fee]" class="form-control"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-2">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" name="ticket_tiers[0][quantity_available]"
                                                        class="form-control" placeholder="Unlimited">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-2">
                                                    <label class="form-label">Sale Start Date</label>
                                                    <input type="datetime-local" name="ticket_tiers[0][sale_starts_at]"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-2">
                                                    <label class="form-label">Sale End Date</label>
                                                    <input type="datetime-local" name="ticket_tiers[0][sale_ends_at]"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <div class="mb-2" style="margin-top: 27px">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button"
                                                        class="btn btn-soft-danger btn-icon waves-effect waves-light remove-tier"><i
                                                            class="ri-delete-bin-5-line"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-2">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="ticket_tiers[0][description]" class="form-control" rows="2"
                                                        placeholder="Brief description of this tier..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Cover Image</label>
                                    <input type="file" class="dropify" id="cover_image" name="cover_image"
                                        data-default-file="{{ asset('admin/default/no-image.png') }}"
                                        data-max-file-size="2M"
                                        data-allowed-file-extensions="png jpg jpeg gif"
                                        data-show-remove="false"
                                        data-height="200"
                                        accept="image/png, image/gif, image/jpeg" />
                                    <p class="text-muted mt-2">Recommended: 1200x600px</p>
                                </div>
                                <div class="mb-3">
                                    <label for="promo_video" class="form-label">Promo Video (Max 20MB)</label>
                                    <input type="file" class="form-control @error('promo_video') is-invalid @enderror"
                                        id="promo_video" name="promo_video" accept="video/*">
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label for="starts_at" class="form-label">Starts At <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('starts_at') is-invalid @enderror"
                                        id="starts_at" name="starts_at" value="{{ old('starts_at') }}"
                                        data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                        placeholder="Select date and time" required>
                                </div>
                                <div class="mb-3">
                                    <label for="ends_at" class="form-label">Ends At <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('ends_at') is-invalid @enderror"
                                        id="ends_at" name="ends_at" value="{{ old('ends_at') }}"
                                        data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                        placeholder="Select date and time" required>
                                </div>
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <input type="text" class="form-control @error('timezone') is-invalid @enderror"
                                        id="timezone" name="timezone" value="{{ old('timezone', 'EST') }}"
                                        placeholder="e.g. EST">
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label for="event_type" class="form-label">Event Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="event_type" id="event_type" required>
                                        <option value="featured" {{ old('event_type') == 'featured' ? 'selected' : '' }}>
                                            Featured</option>
                                        <option value="workshop" {{ old('event_type') == 'workshop' ? 'selected' : '' }}>
                                            Workshop</option>
                                        <option value="art_exhibition"
                                            {{ old('event_type') == 'art_exhibition' ? 'selected' : '' }}>Art Exhibition
                                        </option>
                                        <option value="pop_up" {{ old('event_type') == 'pop_up' ? 'selected' : '' }}>
                                            Pop-Up
                                        </option>
                                        <option value="networking"
                                            {{ old('event_type') == 'networking' ? 'selected' : '' }}>
                                            Networking</option>
                                        <option value="other"
                                            {{ old('event_type') == 'other' || !old('event_type') ? 'selected' : '' }}>
                                            Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="status" id="status1" required>
                                        <option value="draft"
                                            {{ old('status') == 'draft' || !old('status') ? 'selected' : '' }}>Draft
                                        </option>
                                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>
                                            Published</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch form-switch-md">
                                        <input class="form-check-input" type="checkbox" id="is_featured"
                                            name="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">Mark as Featured</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch form-switch-md">
                                        <input class="form-check-input" type="checkbox" id="is_spotlight_eligible"
                                            name="is_spotlight_eligible"
                                            {{ old('is_spotlight_eligible') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_spotlight_eligible">Spotlight
                                            Eligible</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="hosted_by" class="form-label">Hosted By</label>
                                    <input type="text" class="form-control @error('hosted_by') is-invalid @enderror"
                                        id="hosted_by" name="hosted_by" value="{{ old('hosted_by') }}"
                                        placeholder="e.g. OSI Team">
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-success w-100">Save Event</button>
                                <a href="{{ route('admin.events.index') }}" class="btn btn-light w-100 mt-2">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {{-- Dropify --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>

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

                // Quill Description Sync
                // Read directly from .ql-editor DOM element — avoids Quill.find() timing issues
                const descHiddenInput = document.getElementById('description');
                const qlEditorEl = document.querySelector('#descriptionEditor .ql-editor');

                // Restore old() value into Quill on validation fail redirect
                if (qlEditorEl && descHiddenInput && descHiddenInput.value) {
                    qlEditorEl.innerHTML = descHiddenInput.value;
                }

                // On submit, push Quill content into hidden input
                const eventForm = document.querySelector('form[action*="events"]');
                if (eventForm) {
                    eventForm.addEventListener('submit', function() {
                        if (qlEditorEl && descHiddenInput) {
                            const content = qlEditorEl.innerHTML;
                            descHiddenInput.value = (content === '<p><br></p>' || content === '<p></p>') ? '' :
                                content;
                        }
                    });
                }

                let tierIndex = 1;
                const container = document.getElementById('ticket-tiers-container');
                const addButton = document.getElementById('add-tier');

                // Add Tier
                addButton.addEventListener('click', function() {
                    const html = `
                            <div class="tier-item border p-3 rounded mb-3 bg-light">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="mb-2">
                                            <label class="form-label">Tier Name</label>
                                            <input type="text" name="ticket_tiers[${tierIndex}][name]" class="form-control" required placeholder="e.g. Regular">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="mb-2">
                                            <label class="form-label">Price ($)</label>
                                            <input type="number" step="0.01" name="ticket_tiers[${tierIndex}][price]" class="form-control" required placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-2">
                                            <label class="form-label">Service Fee ($)</label>
                                            <input type="number" step="0.01" name="ticket_tiers[${tierIndex}][service_fee]" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" name="ticket_tiers[${tierIndex}][quantity_available]" class="form-control" placeholder="Unlimited">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-2">
                                            <label class="form-label">Sale Start Date</label>
                                            <input type="datetime-local" name="ticket_tiers[${tierIndex}][sale_starts_at]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-2">
                                            <label class="form-label">Sale End Date</label>
                                            <input type="datetime-local" name="ticket_tiers[${tierIndex}][sale_ends_at]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <div class="mb-2" style="margin-top: 27px">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-soft-danger btn-icon waves-effect waves-light remove-tier"><i class="ri-delete-bin-5-line"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-2">
                                            <label class="form-label">Description</label>
                                            <textarea name="ticket_tiers[${tierIndex}][description]" class="form-control" rows="2" placeholder="Brief description of this tier..."></textarea>
                                        </div>
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

                // Dynamic Event Media logic
                let mediaIndex = 1;
                const mediaContainer = document.getElementById('event-media-container');
                const addMediaBtn = document.getElementById('add-media');

                // Add Media
                addMediaBtn.addEventListener('click', function() {
                    const html = `
                        <div class="media-item border p-3 rounded mb-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label class="form-label">Media Type <span class="text-danger">*</span></label>
                                        <select name="event_media[${mediaIndex}][type]" class="form-select media-type-select" required>
                                            <option value="image">Image</option>
                                            <option value="video">Video</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mb-2">
                                        <label class="form-label">Upload File <span class="text-danger">*</span></label>
                                        <input type="file" name="event_media[${mediaIndex}][file]" class="form-control media-file-input" required accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="media-preview-container border bg-white rounded d-flex align-items-center justify-content-center" style="height: 60px; overflow: hidden;">
                                        <span class="text-muted small">No Preview</span>
                                    </div>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-soft-danger btn-icon waves-effect waves-light remove-media"><i class="ri-delete-bin-5-line"></i></button>
                                </div>
                            </div>
                        </div>
                    `;
                    mediaContainer.insertAdjacentHTML('beforeend', html);
                    mediaIndex++;
                });

                // Remove Media
                mediaContainer.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-media')) {
                        const item = e.target.closest('.media-item');
                        item.remove();
                    }
                });

                // Change Media Type & Preview
                mediaContainer.addEventListener('change', function(e) {
                    if (e.target.classList.contains('media-type-select')) {
                        const type = e.target.value;
                        const item = e.target.closest('.media-item');
                        const fileInput = item.querySelector('.media-file-input');
                        const previewContainer = item.querySelector('.media-preview-container');

                        fileInput.value = "";
                        previewContainer.innerHTML = '<span class="text-muted small">No Preview</span>';

                        if (type === 'image') {
                            fileInput.setAttribute('accept', 'image/*');
                        } else {
                            fileInput.setAttribute('accept', 'video/*');
                        }
                    }

                    if (e.target.classList.contains('media-file-input')) {
                        const fileInput = e.target;
                        const item = e.target.closest('.media-item');
                        const typeSelect = item.querySelector('.media-type-select').value;
                        const previewContainer = item.querySelector('.media-preview-container');

                        if (fileInput.files && fileInput.files[0]) {
                            const file = fileInput.files[0];
                            if (typeSelect === 'image') {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    previewContainer.innerHTML =
                                        `<img src="${e.target.result}" style="max-height: 100%; max-width: 100%; object-fit: contain;">`;
                                }
                                reader.readAsDataURL(file);
                            } else if (typeSelect === 'video') {
                                const url = URL.createObjectURL(file);
                                previewContainer.innerHTML =
                                    `<video src="${url}" style="max-height: 100%; max-width: 100%; object-fit: contain;" controls></video>`;
                            }
                        } else {
                            previewContainer.innerHTML = '<span class="text-muted small">No Preview</span>';
                        }
                    }
                });

                // Dropify Initialization
                $('.dropify').dropify({
                    messages: {
                        'default': 'Drag & drop your cover image here or click',
                        'replace': 'Drag & drop or click to replace',
                        'remove': 'Remove',
                        'error': 'Ooops, something wrong happened.'
                    },
                    error: {
                        'fileSize': 'The file size is too big (2M max).',
                        'fileExtension': 'Only png, jpg, jpeg, gif files are allowed.'
                    }
                });

                // Assign Artists Logic
                const artistSelect = document.getElementById('artist-select');
                const addArtistBtn = document.getElementById('add-artist-btn');
                const assignedArtistsContainer = document.getElementById('assigned-artists-container');

                addArtistBtn.addEventListener('click', function() {
                    const selectedOption = artistSelect.options[artistSelect.selectedIndex];
                    if (!selectedOption.value) return;

                    const artistId = selectedOption.value;
                    const artistName = selectedOption.getAttribute('data-name');
                    const artistImage = selectedOption.getAttribute('data-image');

                    if (document.querySelector(`input[name="artists[]"][value="${artistId}"]`)) {
                        alert('Artist already assigned to this event.');
                        return;
                    }

                    const cardHtml = `
                        <div class="artist-card-item">
                            <input type="hidden" name="artists[]" value="${artistId}">
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-light shadow-sm" style="display:inline-flex; width:fit-content;">
                                <img src="${artistImage}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" style="width:36px; height:36px; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.15);">
                                <span class="fw-medium text-truncate" style="max-width:120px; font-size:0.875rem;" title="${artistName}">${artistName}</span>
                                <button type="button" class="remove-artist-btn btn-close btn-close-sm flex-shrink-0" aria-label="Remove" style="font-size:0.65rem;"></button>
                            </div>
                        </div>
                    `;

                    assignedArtistsContainer.insertAdjacentHTML('beforeend', cardHtml);
                    artistSelect.value = '';
                });

                assignedArtistsContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-artist-btn')) {
                        e.target.closest('.artist-card-item').remove();
                    }
                    if (e.target.classList.contains('btn-close')) {
                        e.target.closest('.artist-card-item').remove();
                    }
                });

                // Assign Sponsors Logic
                const sponsorSelect = document.getElementById('sponsor-select');
                const addSponsorBtn = document.getElementById('add-sponsor-btn');
                const assignedSponsorsContainer = document.getElementById('assigned-sponsors-container');

                addSponsorBtn.addEventListener('click', function() {
                    const selectedOption = sponsorSelect.options[sponsorSelect.selectedIndex];
                    if (!selectedOption.value) return;

                    const sponsorId = selectedOption.value;
                    const sponsorName = selectedOption.getAttribute('data-name');
                    const sponsorImage = selectedOption.getAttribute('data-image');

                    if (document.querySelector(`input[name="sponsors[]"][value="${sponsorId}"]`)) {
                        alert('Sponsor already assigned to this event.');
                        return;
                    }

                    const cardHtml = `
                        <div class="sponsor-card-item">
                            <input type="hidden" name="sponsors[]" value="${sponsorId}">
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-light shadow-sm" style="display:inline-flex; width:fit-content;">
                                <img src="${sponsorImage}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" style="width:36px; height:36px; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.15);">
                                <span class="fw-medium text-truncate" style="max-width:120px; font-size:0.875rem;" title="${sponsorName}">${sponsorName}</span>
                                <button type="button" class="remove-sponsor-btn btn-close btn-close-sm flex-shrink-0" aria-label="Remove" style="font-size:0.65rem;"></button>
                            </div>
                        </div>
                    `;

                    assignedSponsorsContainer.insertAdjacentHTML('beforeend', cardHtml);
                    sponsorSelect.value = '';
                });

                assignedSponsorsContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-sponsor-btn')) {
                        e.target.closest('.sponsor-card-item').remove();
                    }
                    if (e.target.classList.contains('btn-close')) {
                        e.target.closest('.sponsor-card-item').remove();
                    }
                });
            });
        </script>
    @endpush
@endsection
