@extends('layout.master-layout')

@section('title', 'Create Round Session')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Create Round Session</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.round-sessions.index') }}">Round Sessions</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.round-sessions.store') }}" method="POST">
            @csrf

            <div class="row">
                {{-- Left column: Session details --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Session Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" placeholder="e.g. Spring 2026 Contest" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Slug</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                    value="{{ old('slug') }}" placeholder="Leave blank to auto-generate">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="4" placeholder="Describe this round session...">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Rounds section --}}
                    <div class="card">
                        <div class="card-header border-bottom-dashed">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Rounds</h5>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm" id="add-round">
                                        <i class="ri-add-line align-middle me-1"></i> Add Round
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="rounds-container">
                            <div class="round-item border p-3 rounded mb-3 bg-light position-relative">
                                <div class="position-absolute top-0 end-0 mt-2 me-2">
                                    <span class="badge bg-primary round-number-badge">Round 1</span>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <label class="form-label">Round Number</label>
                                            <input type="number" name="rounds[0][round_number]" class="form-control round-number-input"
                                                value="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mb-2">
                                            <label class="form-label">Title <span class="text-danger">*</span></label>
                                            <input type="text" name="rounds[0][title]" class="form-control"
                                                placeholder="e.g. Quarterfinals" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-label">Goal</label>
                                            <textarea name="rounds[0][goal]" class="form-control" rows="2"
                                                placeholder="What participants need to achieve..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-label">Requirements</label>
                                            <textarea name="rounds[0][requirements]" class="form-control" rows="2"
                                                placeholder="Detailed requirements..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-2">
                                            <label class="form-label">Advance Limit</label>
                                            <input type="number" name="rounds[0][advance_limit]" class="form-control"
                                                min="1" placeholder="e.g. 10">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <label class="form-label">Starts At</label>
                                            <input type="text" name="rounds[0][starts_at]" class="form-control flatpickr-input"
                                                data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                placeholder="Select date & time">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <label class="form-label">Ends At</label>
                                            <input type="text" name="rounds[0][ends_at]" class="form-control flatpickr-input"
                                                data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                placeholder="Select date & time">
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end mb-2">
                                        <button type="button" class="btn btn-soft-danger btn-icon remove-round"
                                            title="Remove round" style="display:none;">
                                            <i class="ri-delete-bin-5-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right column: Settings & actions --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Starts At</label>
                                <input type="text" name="starts_at" class="form-control flatpickr-input @error('starts_at') is-invalid @enderror"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    value="{{ old('starts_at') }}" placeholder="Select date & time">
                                @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ends At</label>
                                <input type="text" name="ends_at" class="form-control flatpickr-input @error('ends_at') is-invalid @enderror"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    value="{{ old('ends_at') }}" placeholder="Select date & time">
                                @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ri-save-line align-bottom me-1"></i> Save Round Session
                            </button>
                            <a href="{{ route('admin.round-sessions.index') }}" class="btn btn-light w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        let roundIndex = 1;

        const container = document.getElementById('rounds-container');
        const addBtn = document.getElementById('add-round');

        function updateBadges() {
            const items = container.querySelectorAll('.round-item');
            items.forEach((item, idx) => {
                const badge = item.querySelector('.round-number-badge');
                if (badge) badge.textContent = 'Round ' + (idx + 1);

                const numInput = item.querySelector('.round-number-input');
                if (numInput) numInput.value = idx + 1;

                // Show remove button only if there's more than 1
                const removeBtn = item.querySelector('.remove-round');
                if (removeBtn) {
                    removeBtn.style.display = items.length > 1 ? '' : 'none';
                }
            });
        }

        function createRoundHtml(index) {
            return `
                <div class="round-item border p-3 rounded mb-3 bg-light position-relative">
                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                        <span class="badge bg-primary round-number-badge">Round ${index + 1}</span>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Round Number</label>
                                <input type="number" name="rounds[${index}][round_number]" class="form-control round-number-input"
                                    value="${index + 1}" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="rounds[${index}][title]" class="form-control" placeholder="e.g. Semifinals" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Goal</label>
                                <textarea name="rounds[${index}][goal]" class="form-control" rows="2" placeholder="What participants need to achieve..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Requirements</label>
                                <textarea name="rounds[${index}][requirements]" class="form-control" rows="2" placeholder="Detailed requirements..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Advance Limit</label>
                                <input type="number" name="rounds[${index}][advance_limit]" class="form-control" min="1" placeholder="e.g. 10">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Starts At</label>
                                <input type="text" name="rounds[${index}][starts_at]" class="form-control flatpickr-input"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    placeholder="Select date & time">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Ends At</label>
                                <input type="text" name="rounds[${index}][ends_at]" class="form-control flatpickr-input"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    placeholder="Select date & time">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end mb-2">
                            <button type="button" class="btn btn-soft-danger btn-icon remove-round" title="Remove round">
                                <i class="ri-delete-bin-5-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        addBtn.addEventListener('click', function() {
            container.insertAdjacentHTML('beforeend', createRoundHtml(roundIndex));
            roundIndex++;
            updateBadges();
            // Initialize flatpickr on new inputs
            if (typeof flatpickr !== 'undefined') {
                container.querySelectorAll('.flatpickr-input:not(.flatpickr-input--initialized)').forEach(el => {
                    el.classList.add('flatpickr-input--initialized');
                    flatpickr(el, {
                        enableTime: true,
                        dateFormat: 'Y-m-d H:i',
                        altInput: true,
                        altFormat: 'Y-m-d H:i',
                    });
                });
            }
        });

        container.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-round');
            if (removeBtn) {
                const item = removeBtn.closest('.round-item');
                if (container.children.length > 1) {
                    item.remove();
                    roundIndex = container.children.length;
                    updateBadges();
                }
            }
        });

        // Initialize flatpickr on page load
        if (typeof flatpickr !== 'undefined') {
            flatpickr("[data-provider='flatpickr']", {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                altInput: true,
                altFormat: 'Y-m-d H:i',
            });
        }
    })();
</script>
@endpush
@endsection
