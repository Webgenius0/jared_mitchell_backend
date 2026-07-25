@extends('layout.master-layout')

@section('title', 'Edit Round Session')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Edit Round Session</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.round-sessions.index') }}">Round Sessions</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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

        <form action="{{ route('admin.round-sessions.update', $season->id) }}" method="POST">
            @csrf
            @method('PUT')

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
                                    value="{{ old('title', $season->title) }}" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Slug</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                    value="{{ old('slug', $season->slug) }}" placeholder="Leave blank to auto-generate">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="4" placeholder="Describe this round session...">{{ old('description', $season->description) }}</textarea>
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
                            @forelse ($season->rounds as $idx => $round)
                                @php $advConfig = is_array($round->advancement_config) ? $round->advancement_config : []; @endphp
                                <div class="round-item border p-3 rounded mb-3 bg-light position-relative">
                                    <div class="position-absolute top-0 end-0 mt-2 me-2 d-flex align-items-center gap-1">
                                        <span class="badge bg-primary round-number-badge">Round {{ $loop->iteration }}</span>
                                        <button type="button" class="btn btn-soft-info btn-icon duplicate-round btn-sm"
                                            title="Duplicate round">
                                            <i class="ri-file-copy-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-danger btn-icon remove-round btn-sm"
                                            title="Remove round" {{ $loop->count <= 1 ? 'style=display:none;' : '' }}>
                                            <i class="ri-delete-bin-5-line"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="rounds[{{ $idx }}][id]" value="{{ $round->id }}">
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Round Number</label>
                                                <input type="number" name="rounds[{{ $idx }}][round_number]"
                                                    class="form-control round-number-input"
                                                    value="{{ old("rounds.{$idx}.round_number", $round->round_number) }}" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="mb-2">
                                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                                <input type="text" name="rounds[{{ $idx }}][title]"
                                                    class="form-control"
                                                    value="{{ old("rounds.{$idx}.title", $round->title) }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Goal</label>
                                                <textarea name="rounds[{{ $idx }}][goal]" class="form-control" rows="2"
                                                    placeholder="What participants need to achieve...">{{ old("rounds.{$idx}.goal", $round->goal) }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Requirements</label>
                                                <textarea name="rounds[{{ $idx }}][requirements]" class="form-control" rows="2"
                                                    placeholder="Detailed requirements...">{{ old("rounds.{$idx}.requirements", $round->requirements) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Advance Limit</label>
                                                <input type="number" name="rounds[{{ $idx }}][advance_limit]"
                                                    class="form-control" min="1" placeholder="e.g. 10"
                                                    value="{{ old("rounds.{$idx}.advance_limit", $round->advance_limit) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Starts At</label>
                                                <input type="text" name="rounds[{{ $idx }}][starts_at]"
                                                    class="form-control flatpickr-input"
                                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                    value="{{ old("rounds.{$idx}.starts_at", $round->starts_at?->format('Y-m-d H:i')) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Ends At</label>
                                                <input type="text" name="rounds[{{ $idx }}][ends_at]"
                                                    class="form-control flatpickr-input"
                                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                    value="{{ old("rounds.{$idx}.ends_at", $round->ends_at?->format('Y-m-d H:i')) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Voting Ends At</label>
                                                <input type="text" name="rounds[{{ $idx }}][voting_ends_at]"
                                                    class="form-control flatpickr-input"
                                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                    value="{{ old("rounds.{$idx}.voting_ends_at", $round->voting_ends_at?->format('Y-m-d H:i')) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <div class="form-check form-switch form-switch-md">
                                                <input class="form-check-input" type="checkbox"
                                                    name="rounds[{{ $idx }}][is_active]" value="1"
                                                    id="round_{{ $idx }}_active"
                                                    {{ old("rounds.{$idx}.is_active", $round->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="round_{{ $idx }}_active">Active</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Advancement Config --}}
                                    <div class="adv-config-section">
                                        <div class="row">
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <label class="form-label fw-semibold mb-2">Advancement Configuration</label>
                                            </div>
                                        </div>

                                        {{-- Tie Breaker (multi-add, max 5) --}}
                                        <div class="row">
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <div class="mb-2">
                                                    <label class="form-label fw-semibold">Tie Breaker</label>
                                                    <div class="tie-breakers-container" data-round-index="{{ $idx }}">
                                                        @php $tieBreakers = $advConfig['categories'] ?? ['']; @endphp
                                                        @forelse ($tieBreakers as $tbIdx => $tbValue)
                                                            <div class="tie-breaker-item mb-2">
                                                                <div class="input-group">
                                                                    <input type="text" name="rounds[{{ $idx }}][adv_config][categories][]"
                                                                        class="form-control" placeholder="Enter tie breaker rule..." maxlength="255"
                                                                        value="{{ old("rounds.{$idx}.adv_config.categories.{$tbIdx}", $tbValue) }}">
                                                                    <button type="button" class="btn btn-soft-danger remove-tie-breaker" title="Remove"
                                                                        {{ count($tieBreakers) <= 1 ? 'style=display:none;' : '' }}>
                                                                        <i class="ri-delete-bin-5-line"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="tie-breaker-item mb-2">
                                                                <div class="input-group">
                                                                    <input type="text" name="rounds[{{ $idx }}][adv_config][categories][]"
                                                                        class="form-control" placeholder="Enter tie breaker rule..." maxlength="255">
                                                                    <button type="button" class="btn btn-soft-danger remove-tie-breaker" title="Remove" style="display:none;">
                                                                        <i class="ri-delete-bin-5-line"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                    <button type="button" class="btn btn-soft-primary btn-sm add-tie-breaker" data-round-index="{{ $idx }}">
                                                        <i class="ri-add-line align-middle me-1"></i> Add Tie Breaker
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @empty
                                <div class="round-item border p-3 rounded mb-3 bg-light position-relative">
                                    <div class="position-absolute top-0 end-0 mt-2 me-2 d-flex align-items-center gap-1">
                                        <span class="badge bg-primary round-number-badge">Round 1</span>
                                        <button type="button" class="btn btn-soft-info btn-icon duplicate-round btn-sm"
                                            title="Duplicate round">
                                            <i class="ri-file-copy-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-danger btn-icon remove-round btn-sm"
                                            title="Remove round" style="display:none;">
                                            <i class="ri-delete-bin-5-line"></i>
                                        </button>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Round Number</label>
                                                <input type="number" name="rounds[0][round_number]"
                                                    class="form-control round-number-input" value="1" min="1" required>
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
                                                <input type="number" name="rounds[0][advance_limit]" class="form-control" min="1" placeholder="e.g. 10">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Starts At</label>
                                                <input type="text" name="rounds[0][starts_at]" class="form-control flatpickr-input"
                                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                    placeholder="Select date & time">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Ends At</label>
                                                <input type="text" name="rounds[0][ends_at]" class="form-control flatpickr-input"
                                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                    placeholder="Select date & time">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Voting Ends At</label>
                                                <input type="text" name="rounds[0][voting_ends_at]" class="form-control flatpickr-input"
                                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                    placeholder="Select date & time">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <div class="form-check form-switch form-switch-md">
                                                <input class="form-check-input" type="checkbox"
                                                    name="rounds[0][is_active]" value="1" id="round_0_active" checked>
                                                <label class="form-check-label fw-semibold" for="round_0_active">Active</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Advancement Config --}}
                                    <div class="adv-config-section">
                                        <div class="row">
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <label class="form-label fw-semibold mb-2">Advancement Configuration</label>
                                            </div>
                                        </div>

                                        {{-- Tie Breaker (multi-add, max 5) --}}
                                        <div class="row">
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <div class="mb-2">
                                                    <label class="form-label fw-semibold">Tie Breaker</label>
                                                    <div class="tie-breakers-container" data-round-index="0">
                                                        <div class="tie-breaker-item mb-2">
                                                            <div class="input-group">
                                                                <input type="text" name="rounds[0][adv_config][categories][]"
                                                                    class="form-control" placeholder="Enter tie breaker rule..." maxlength="255">
                                                                <button type="button" class="btn btn-soft-danger remove-tie-breaker" title="Remove" style="display:none;">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-soft-primary btn-sm add-tie-breaker" data-round-index="0">
                                                        <i class="ri-add-line align-middle me-1"></i> Add Tie Breaker
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endforelse
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
                                <input type="text" name="starts_at"
                                    class="form-control flatpickr-input @error('starts_at') is-invalid @enderror"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    value="{{ old('starts_at', $season->starts_at?->format('Y-m-d H:i')) }}">
                                @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ends At</label>
                                <input type="text" name="ends_at"
                                    class="form-control flatpickr-input @error('ends_at') is-invalid @enderror"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    value="{{ old('ends_at', $season->ends_at?->format('Y-m-d H:i')) }}">
                                @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $season->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Sponsor</label>
                                <select name="sponsor_id" class="form-select @error('sponsor_id') is-invalid @enderror">
                                    <option value="">— No Sponsor —</option>
                                    @foreach ($sponsors as $sponsor)
                                        <option value="{{ $sponsor->id }}" {{ old('sponsor_id', $season->sponsor?->id) == $sponsor->id ? 'selected' : '' }}>
                                            {{ $sponsor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sponsor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ri-save-line align-bottom me-1"></i> Update Round Session
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
        let roundIndex = {{ count($season->rounds) }};

        const container = document.getElementById('rounds-container');
        const addBtn = document.getElementById('add-round');

        function updateBadges() {
            const items = container.querySelectorAll('.round-item');
            items.forEach((item, idx) => {
                const badge = item.querySelector('.round-number-badge');
                if (badge) badge.textContent = 'Round ' + (idx + 1);

                const numInput = item.querySelector('.round-number-input');
                if (numInput) numInput.value = idx + 1;

                const removeBtn = item.querySelector('.remove-round');
                if (removeBtn) {
                    removeBtn.style.display = items.length > 1 ? '' : 'none';
                }

                // Re-index all input/textarea/select names within this round
                const formEls = item.querySelectorAll('input, textarea, select');
                formEls.forEach(function(el) {
                    const name = el.getAttribute('name');
                    if (name && name.startsWith('rounds[')) {
                        el.setAttribute('name', name.replace(/^rounds\[\d+\]/, 'rounds[' + idx + ']'));
                    }
                    const id = el.getAttribute('id');
                    if (id && id.startsWith('round_')) {
                        el.setAttribute('id', id.replace(/round_\d+/, 'round_' + idx));
                    }
                });

                // Update label for attributes
                const labels = item.querySelectorAll('label[for]');
                labels.forEach(function(label) {
                    const forAttr = label.getAttribute('for');
                    if (forAttr && forAttr.startsWith('round_')) {
                        label.setAttribute('for', forAttr.replace(/round_\d+/, 'round_' + idx));
                    }
                });

                // Update tie-breaker container data-round-index
                const tbContainer = item.querySelector('.tie-breakers-container');
                if (tbContainer) {
                    tbContainer.setAttribute('data-round-index', idx.toString());
                }

                // Update tie-breaker add button data-round-index
                const addTbBtn = item.querySelector('.add-tie-breaker');
                if (addTbBtn) {
                    addTbBtn.setAttribute('data-round-index', idx.toString());
                }
            });
        }

        function createRoundHtml(index) {
            return `
                <div class="round-item border p-3 rounded mb-3 bg-light position-relative">
                    <div class="position-absolute top-0 end-0 mt-2 me-2 d-flex align-items-center gap-1">
                        <span class="badge bg-primary round-number-badge">Round ${index + 1}</span>
                        <button type="button" class="btn btn-soft-info btn-icon duplicate-round btn-sm" title="Duplicate round">
                            <i class="ri-file-copy-line"></i>
                        </button>
                        <button type="button" class="btn btn-soft-danger btn-icon remove-round btn-sm" title="Remove round">
                            <i class="ri-delete-bin-5-line"></i>
                        </button>
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
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Starts At</label>
                                <input type="text" name="rounds[${index}][starts_at]" class="form-control flatpickr-input"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    placeholder="Select date & time">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Ends At</label>
                                <input type="text" name="rounds[${index}][ends_at]" class="form-control flatpickr-input"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    placeholder="Select date & time">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Voting Ends At</label>
                                <input type="text" name="rounds[${index}][voting_ends_at]" class="form-control flatpickr-input"
                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                    placeholder="Select date & time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div class="form-check form-switch form-switch-md">
                                <input class="form-check-input" type="checkbox"
                                    name="rounds[${index}][is_active]" value="1" id="round_${index}_active" checked>
                                <label class="form-check-label fw-semibold" for="round_${index}_active">Active</label>
                            </div>
                        </div>
                    </div>

                    {{-- Advancement Config --}}
                    <div class="adv-config-section">
                        <div class="row">
                            <div class="col-12">
                                <hr class="my-2">
                                <label class="form-label fw-semibold mb-2">Advancement Configuration</label>
                            </div>
                        </div>

                        {{-- Tie Breaker (multi-add, max 5) --}}
                        <div class="row">
                            <div class="col-12">
                                <hr class="my-2">
                                <div class="mb-2">
                                    <label class="form-label fw-semibold">Tie Breaker</label>
                                    <div class="tie-breakers-container" data-round-index="${index}">
                                        <div class="tie-breaker-item mb-2">
                                            <div class="input-group">
                                                <input type="text" name="rounds[${index}][adv_config][categories][]"
                                                    class="form-control" placeholder="Enter tie breaker rule..." maxlength="255">
                                                <button type="button" class="btn btn-soft-danger remove-tie-breaker" title="Remove" style="display:none;">
                                                    <i class="ri-delete-bin-5-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-soft-primary btn-sm add-tie-breaker" data-round-index="${index}">
                                        <i class="ri-add-line align-middle me-1"></i> Add Tie Breaker
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            `;
        }

        /**
         * Duplicate a round: clone all its field values and insert after the current round.
         */
        function duplicateRound(item) {
            const clone = item.cloneNode(true);

            // Remove the hidden [id] input if present (existing round in edit mode)
            const idInput = clone.querySelector('input[name$="[id]"]');
            if (idInput) idInput.remove();

            // Strip flatpickr alt inputs (visible date display) and reset original date inputs
            clone.querySelectorAll('input[data-provider="flatpickr"]').forEach(function(el) {
                // Flatpickr creates an alt input sibling. We remove all inputs in the same parent except the original one.
                const parent = el.parentNode;
                parent.querySelectorAll('input').forEach(function(sibling) {
                    if (sibling !== el) {
                        sibling.remove();
                    }
                });

                // Restore original date input state
                el.type = 'text';
                el.value = '';
                el.removeAttribute('value');
                el.style.display = '';
                el.classList.remove('flatpickr-input--initialized');
            });

            item.parentNode.insertBefore(clone, item.nextSibling);
            roundIndex = container.children.length;
            updateBadges();

            // Initialize tie breaker buttons on the cloned round
            const tbContainer = clone.querySelector('.tie-breakers-container');
            if (tbContainer) {
                updateTieBreakerButtons(clone);
            }

            // Initialize flatpickr on new inputs
            if (typeof flatpickr !== 'undefined') {
                clone.querySelectorAll('.flatpickr-input:not(.flatpickr-input--initialized)').forEach(function(el) {
                    el.classList.add('flatpickr-input--initialized');
                    flatpickr(el, {
                        enableTime: true,
                        dateFormat: 'Y-m-d H:i',
                        altInput: true,
                        altFormat: 'Y-m-d H:i',
                    });
                });
            }
        }

        container.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-round');
            if (removeBtn) {
                const item = removeBtn.closest('.round-item');
                if (container.children.length > 1) {
                    item.remove();
                    roundIndex = container.children.length;
                    updateBadges();
                }
                return;
            }

            const dupBtn = e.target.closest('.duplicate-round');
            if (dupBtn) {
                const item = dupBtn.closest('.round-item');
                if (item) {
                    duplicateRound(item);
                }
                return;
            }
        });

        /**
         * Tie Breaker: add/remove multi-entry (max 5)
         */
        function updateTieBreakerButtons(root) {
            const addBtns = root.querySelectorAll('.add-tie-breaker');
            addBtns.forEach(btn => {
                const roundIdx = btn.dataset.roundIndex;
                const tbContainer = root.querySelector('.tie-breakers-container[data-round-index="' + roundIdx + '"]');
                if (tbContainer) {
                    const items = tbContainer.querySelectorAll('.tie-breaker-item');
                    const removeBtns = tbContainer.querySelectorAll('.remove-tie-breaker');
                    removeBtns.forEach(rb => {
                        rb.style.display = items.length > 1 ? '' : 'none';
                    });
                    btn.disabled = items.length >= 5;
                }
            });
        }

        function addTieBreakerItem(roundIndex, root) {
            const tbContainer = root.querySelector('.tie-breakers-container[data-round-index="' + roundIndex + '"]');
            if (!tbContainer) return;
            const items = tbContainer.querySelectorAll('.tie-breaker-item');
            if (items.length >= 5) return;

            const newItem = document.createElement('div');
            newItem.className = 'tie-breaker-item mb-2';
            newItem.innerHTML = `
                        <div class="input-group">
                            <input type="text" name="rounds[${roundIndex}][adv_config][categories][]"
                                class="form-control" placeholder="Enter tie breaker rule..." maxlength="255">
                            <button type="button" class="btn btn-soft-danger remove-tie-breaker" title="Remove">
                                <i class="ri-delete-bin-5-line"></i>
                            </button>
                        </div>
                    `;
            tbContainer.appendChild(newItem);
            updateTieBreakerButtons(root);
        }

        // Delegate tie breaker events on the rounds container
        container.addEventListener('click', function(e) {
            const addBtn = e.target.closest('.add-tie-breaker');
            if (addBtn) {
                const roundIdx = addBtn.dataset.roundIndex;
                const roundItem = addBtn.closest('.round-item');
                if (roundItem) {
                    addTieBreakerItem(roundIdx, roundItem);
                }
                return;
            }

            const removeBtn = e.target.closest('.remove-tie-breaker');
            if (removeBtn) {
                const item = removeBtn.closest('.tie-breaker-item');
                const tbContainer = item.closest('.tie-breakers-container');
                if (tbContainer && tbContainer.querySelectorAll('.tie-breaker-item').length > 1) {
                    item.remove();
                    const roundItem = tbContainer.closest('.round-item');
                    if (roundItem) updateTieBreakerButtons(roundItem);
                }
            }
        });

        // Initialize tie breaker buttons on page load
        document.querySelectorAll('.round-item').forEach(function(item) {
            updateTieBreakerButtons(item);
        });

        // Add button handler
        addBtn.addEventListener('click', function() {
            container.insertAdjacentHTML('beforeend', createRoundHtml(roundIndex));
            roundIndex++;
            updateBadges();

            container.querySelectorAll('.round-item:not([data-listener-attached])').forEach(function(item) {
                item.dataset.listenerAttached = 'true';
                updateTieBreakerButtons(item);
            });

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
