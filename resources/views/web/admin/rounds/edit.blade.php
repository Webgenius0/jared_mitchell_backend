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
                                <div class="round-item border p-3 rounded mb-3 bg-light position-relative">
                                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                                        <span class="badge bg-primary round-number-badge">Round {{ $loop->iteration }}</span>
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
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Voting Ends At</label>
                                                <input type="text" name="rounds[{{ $idx }}][voting_ends_at]"
                                                    class="form-control flatpickr-input"
                                                    data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="true"
                                                    value="{{ old("rounds.{$idx}.voting_ends_at", $round->voting_ends_at?->format('Y-m-d H:i')) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end mb-2">
                                            <button type="button" class="btn btn-soft-danger btn-icon remove-round"
                                                title="Remove round" {{ $loop->count <= 1 ? 'style=display:none;' : '' }}>
                                                <i class="ri-delete-bin-5-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    {{-- Round Mechanics --}}
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Voting Strategy</label>
                                                <select name="rounds[{{ $idx }}][voting_strategy]" class="form-select">
                                                    <option value="popular_vote" {{ old("rounds.{$idx}.voting_strategy", $round->voting_strategy) == 'popular_vote' ? 'selected' : '' }}>Popular Vote</option>
                                                    <option value="judge_scored" {{ old("rounds.{$idx}.voting_strategy", $round->voting_strategy) == 'judge_scored' ? 'selected' : '' }}>Judge Scored</option>
                                                    <option value="weighted" {{ old("rounds.{$idx}.voting_strategy", $round->voting_strategy) == 'weighted' ? 'selected' : '' }}>Weighted</option>
                                                    <option value="admin_pick" {{ old("rounds.{$idx}.voting_strategy", $round->voting_strategy) == 'admin_pick' ? 'selected' : '' }}>Admin Pick</option>
                                                    <option value="single_elimination" {{ old("rounds.{$idx}.voting_strategy", $round->voting_strategy) == 'single_elimination' ? 'selected' : '' }}>Single Elimination</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Submission Type</label>
                                                <select name="rounds[{{ $idx }}][submission_type]" class="form-select">
                                                    <option value="multi" {{ old("rounds.{$idx}.submission_type", $round->submission_type) == 'multi' ? 'selected' : '' }}>Multi</option>
                                                    <option value="file_upload" {{ old("rounds.{$idx}.submission_type", $round->submission_type) == 'file_upload' ? 'selected' : '' }}>File Upload</option>
                                                    <option value="video" {{ old("rounds.{$idx}.submission_type", $round->submission_type) == 'video' ? 'selected' : '' }}>Video</option>
                                                    <option value="link" {{ old("rounds.{$idx}.submission_type", $round->submission_type) == 'link' ? 'selected' : '' }}>Link</option>
                                                    <option value="text" {{ old("rounds.{$idx}.submission_type", $round->submission_type) == 'text' ? 'selected' : '' }}>Text</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Elimination Rule</label>
                                                <select name="rounds[{{ $idx }}][elimination_rule]" class="form-select">
                                                    <option value="advance_limit" {{ old("rounds.{$idx}.elimination_rule", $round->elimination_rule) == 'advance_limit' ? 'selected' : '' }}>Advance Limit</option>
                                                    <option value="bottom_n" {{ old("rounds.{$idx}.elimination_rule", $round->elimination_rule) == 'bottom_n' ? 'selected' : '' }}>Bottom N</option>
                                                    <option value="top_percent" {{ old("rounds.{$idx}.elimination_rule", $round->elimination_rule) == 'top_percent' ? 'selected' : '' }}>Top Percent</option>
                                                    <option value="score_below_threshold" {{ old("rounds.{$idx}.elimination_rule", $round->elimination_rule) == 'score_below_threshold' ? 'selected' : '' }}>Score Below Threshold</option>
                                                    <option value="all_advance" {{ old("rounds.{$idx}.elimination_rule", $round->elimination_rule) == 'all_advance' ? 'selected' : '' }}>All Advance</option>
                                                    <option value="single_elimination" {{ old("rounds.{$idx}.elimination_rule", $round->elimination_rule) == 'single_elimination' ? 'selected' : '' }}>Single Elimination</option>
                                                    <option value="admin_pick" {{ old("rounds.{$idx}.elimination_rule", $round->elimination_rule) == 'admin_pick' ? 'selected' : '' }}>Admin Pick</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Sort Order & Active --}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Sort Order</label>
                                                <input type="number" name="rounds[{{ $idx }}][sort_order]" class="form-control"
                                                    value="{{ old("rounds.{$idx}.sort_order", $round->sort_order ?? 0) }}" min="0" placeholder="0">
                                            </div>
                                        </div>
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
                                    {{-- Submission Requirements (JSON) --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label">
                                                    Submission Requirements (JSON)
                                                    <i class="ri-information-line" data-bs-toggle="tooltip" title='e.g. { "video": { "required": true, "max_duration_sec": 180 } }'></i>
                                                </label>
                                                <textarea name="rounds[{{ $idx }}][submission_requirements]" class="form-control" rows="2"
                                                    placeholder='{"video": {"required": true, "max_duration_sec": 180}}'>{{ old("rounds.{$idx}.submission_requirements", is_array($round->submission_requirements) ? json_encode($round->submission_requirements) : $round->submission_requirements) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Advancement Config --}}
                                    @php $advConfig = is_array($round->advancement_config) ? $round->advancement_config : []; @endphp
                                    <div class="adv-config-section">
                                        <div class="row">
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <label class="form-label fw-semibold mb-2">Advancement Configuration</label>
                                            </div>
                                        </div>
                                        {{-- Elimination-based configs --}}
                                        <div class="row">
                                            <div class="col-md-4 adv-config-advance_limit" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Tie Breaker</label>
                                                    <select name="rounds[{{ $idx }}][adv_config][cutoff_tie_breaker]" class="form-select">
                                                        <option value="all_tied_advance" {{ old("rounds.{$idx}.adv_config.cutoff_tie_breaker", $advConfig['cutoff_tie_breaker'] ?? '') == 'all_tied_advance' ? 'selected' : '' }}>All Tied Advance</option>
                                                        <option value="all_tied_eliminate" {{ old("rounds.{$idx}.adv_config.cutoff_tie_breaker", $advConfig['cutoff_tie_breaker'] ?? '') == 'all_tied_eliminate' ? 'selected' : '' }}>All Tied Eliminated</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-bottom_n" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Eliminate Count</label>
                                                    <input type="number" name="rounds[{{ $idx }}][adv_config][eliminate_count]"
                                                        class="form-control" min="1" placeholder="e.g. 2"
                                                        value="{{ old("rounds.{$idx}.adv_config.eliminate_count", $advConfig['eliminate_count'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-top_percent" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Keep Percent (%)</label>
                                                    <input type="number" name="rounds[{{ $idx }}][adv_config][keep_percent]"
                                                        class="form-control" min="1" max="100" placeholder="e.g. 50"
                                                        value="{{ old("rounds.{$idx}.adv_config.keep_percent", $advConfig['keep_percent'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-top_percent" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Tie Breaker</label>
                                                    <select name="rounds[{{ $idx }}][adv_config][cutoff_tie_breaker]" class="form-select">
                                                        <option value="all_tied_advance" {{ old("rounds.{$idx}.adv_config.cutoff_tie_breaker", $advConfig['cutoff_tie_breaker'] ?? '') == 'all_tied_advance' ? 'selected' : '' }}>All Tied Advance</option>
                                                        <option value="all_tied_eliminate" {{ old("rounds.{$idx}.adv_config.cutoff_tie_breaker", $advConfig['cutoff_tie_breaker'] ?? '') == 'all_tied_eliminate' ? 'selected' : '' }}>All Tied Eliminated</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-score_below_threshold" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Score Threshold</label>
                                                    <input type="number" name="rounds[{{ $idx }}][adv_config][score_threshold]"
                                                        class="form-control" min="0" placeholder="e.g. 50"
                                                        value="{{ old("rounds.{$idx}.adv_config.score_threshold", $advConfig['score_threshold'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Voting-based configs --}}
                                        <div class="row">
                                            <div class="col-12">
                                                <small class="text-muted">Voting Config</small>
                                            </div>
                                            <div class="col-md-4 adv-config-voting-popular_vote" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Max Votes Per User</label>
                                                    <input type="number" name="rounds[{{ $idx }}][adv_config][max_votes_per_user]"
                                                        class="form-control" min="1" placeholder="e.g. 10"
                                                        value="{{ old("rounds.{$idx}.adv_config.max_votes_per_user", $advConfig['max_votes_per_user'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-voting-weighted" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Vote Weight</label>
                                                    <input type="number" name="rounds[{{ $idx }}][adv_config][vote_weight]"
                                                        class="form-control" min="0.1" step="0.1" placeholder="e.g. 1.0"
                                                        value="{{ old("rounds.{$idx}.adv_config.vote_weight", $advConfig['vote_weight'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6 adv-config-voting-categories" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">
                                                        Categories (one per line)
                                                        <i class="ri-information-line" data-bs-toggle="tooltip" title='Enter one category per line, e.g. Innovation, Presentation, Impact'></i>
                                                    </label>
                                                    <textarea name="rounds[{{ $idx }}][adv_config][categories]" class="form-control" rows="2"
                                                        placeholder="Innovation&#10;Presentation&#10;Impact">{{ old("rounds.{$idx}.adv_config.categories", isset($advConfig['categories']) ? (is_array($advConfig['categories']) ? implode("\n", $advConfig['categories']) : $advConfig['categories']) : '') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-voting-categories" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Max Score Per Category</label>
                                                    <input type="number" name="rounds[{{ $idx }}][adv_config][max_score_per_category]"
                                                        class="form-control" min="1" placeholder="e.g. 10"
                                                        value="{{ old("rounds.{$idx}.adv_config.max_score_per_category", $advConfig['max_score_per_category'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="round-item border p-3 rounded mb-3 bg-light position-relative">
                                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                                        <span class="badge bg-primary round-number-badge">Round 1</span>
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
                                        <div class="col-md-2">
                                            <div class="mb-2">
                                                <label class="form-label">Voting Ends At</label>
                                                <input type="text" name="rounds[0][voting_ends_at]" class="form-control flatpickr-input"
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
                                    {{-- Round Mechanics --}}
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Voting Strategy</label>
                                                <select name="rounds[0][voting_strategy]" class="form-select">
                                                    <option value="popular_vote">Popular Vote</option>
                                                    <option value="judge_scored">Judge Scored</option>
                                                    <option value="weighted">Weighted</option>
                                                    <option value="admin_pick">Admin Pick</option>
                                                    <option value="single_elimination">Single Elimination</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Submission Type</label>
                                                <select name="rounds[0][submission_type]" class="form-select">
                                                    <option value="multi">Multi</option>
                                                    <option value="file_upload">File Upload</option>
                                                    <option value="video">Video</option>
                                                    <option value="link">Link</option>
                                                    <option value="text">Text</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Elimination Rule</label>
                                                <select name="rounds[0][elimination_rule]" class="form-select">
                                                    <option value="advance_limit">Advance Limit</option>
                                                    <option value="bottom_n">Bottom N</option>
                                                    <option value="top_percent">Top Percent</option>
                                                    <option value="score_below_threshold">Score Below Threshold</option>
                                                    <option value="all_advance">All Advance</option>
                                                    <option value="single_elimination">Single Elimination</option>
                                                    <option value="admin_pick">Admin Pick</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Sort Order & Active --}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <label class="form-label">Sort Order</label>
                                                <input type="number" name="rounds[0][sort_order]" class="form-control"
                                                    value="0" min="0" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end mb-2">
                                            <div class="form-check form-switch form-switch-md">
                                                <input class="form-check-input" type="checkbox"
                                                    name="rounds[0][is_active]" value="1" id="round_0_active" checked>
                                                <label class="form-check-label fw-semibold" for="round_0_active">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Submission Requirements (JSON) --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-2">
                                                <label class="form-label">
                                                    Submission Requirements (JSON)
                                                    <i class="ri-information-line" data-bs-toggle="tooltip" title='e.g. { "video": { "required": true, "max_duration_sec": 180 } }'></i>
                                                </label>
                                                <textarea name="rounds[0][submission_requirements]" class="form-control" rows="2"
                                                    placeholder='{"video": {"required": true, "max_duration_sec": 180}}'></textarea>
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
                                        {{-- Elimination-based configs --}}
                                        <div class="row">
                                            <div class="col-md-4 adv-config-advance_limit" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Tie Breaker</label>
                                                    <select name="rounds[0][adv_config][cutoff_tie_breaker]" class="form-select">
                                                        <option value="all_tied_advance">All Tied Advance</option>
                                                        <option value="all_tied_eliminate">All Tied Eliminated</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-bottom_n" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Eliminate Count</label>
                                                    <input type="number" name="rounds[0][adv_config][eliminate_count]"
                                                        class="form-control" min="1" placeholder="e.g. 2">
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-top_percent" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Keep Percent (%)</label>
                                                    <input type="number" name="rounds[0][adv_config][keep_percent]"
                                                        class="form-control" min="1" max="100" placeholder="e.g. 50">
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-top_percent" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Tie Breaker</label>
                                                    <select name="rounds[0][adv_config][cutoff_tie_breaker]" class="form-select">
                                                        <option value="all_tied_advance">All Tied Advance</option>
                                                        <option value="all_tied_eliminate">All Tied Eliminated</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-score_below_threshold" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Score Threshold</label>
                                                    <input type="number" name="rounds[0][adv_config][score_threshold]"
                                                        class="form-control" min="0" placeholder="e.g. 50">
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Voting-based configs --}}
                                        <div class="row">
                                            <div class="col-12">
                                                <small class="text-muted">Voting Config</small>
                                            </div>
                                            <div class="col-md-4 adv-config-voting-popular_vote" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Max Votes Per User</label>
                                                    <input type="number" name="rounds[0][adv_config][max_votes_per_user]"
                                                        class="form-control" min="1" placeholder="e.g. 10">
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-voting-weighted" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Vote Weight</label>
                                                    <input type="number" name="rounds[0][adv_config][vote_weight]"
                                                        class="form-control" min="0.1" step="0.1" placeholder="e.g. 1.0">
                                                </div>
                                            </div>
                                            <div class="col-md-6 adv-config-voting-categories" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">
                                                        Categories (one per line)
                                                        <i class="ri-information-line" data-bs-toggle="tooltip" title='Enter one category per line, e.g. Innovation, Presentation, Impact'></i>
                                                    </label>
                                                    <textarea name="rounds[0][adv_config][categories]" class="form-control" rows="2"
                                                        placeholder="Innovation&#10;Presentation&#10;Impact"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4 adv-config-voting-categories" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Max Score Per Category</label>
                                                    <input type="number" name="rounds[0][adv_config][max_score_per_category]"
                                                        class="form-control" min="1" placeholder="e.g. 10">
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
                                <input type="text" name="rounds[${index}][title]" class="form-control" placeholder="e.g. Finals" required>
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
                        <div class="col-md-2">
                            <div class="mb-2">
                                <label class="form-label">Voting Ends At</label>
                                <input type="text" name="rounds[${index}][voting_ends_at]" class="form-control flatpickr-input"
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
                    {{-- Round Mechanics --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Voting Strategy</label>
                                <select name="rounds[${index}][voting_strategy]" class="form-select">
                                    <option value="popular_vote">Popular Vote</option>
                                    <option value="judge_scored">Judge Scored</option>
                                    <option value="weighted">Weighted</option>
                                    <option value="admin_pick">Admin Pick</option>
                                    <option value="single_elimination">Single Elimination</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Submission Type</label>
                                <select name="rounds[${index}][submission_type]" class="form-select">
                                    <option value="multi">Multi</option>
                                    <option value="file_upload">File Upload</option>
                                    <option value="video">Video</option>
                                    <option value="link">Link</option>
                                    <option value="text">Text</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label class="form-label">Elimination Rule</label>
                                <select name="rounds[${index}][elimination_rule]" class="form-select">
                                    <option value="advance_limit">Advance Limit</option>
                                    <option value="bottom_n">Bottom N</option>
                                    <option value="top_percent">Top Percent</option>
                                    <option value="score_below_threshold">Score Below Threshold</option>
                                    <option value="all_advance">All Advance</option>
                                    <option value="single_elimination">Single Elimination</option>
                                    <option value="admin_pick">Admin Pick</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- Sort Order & Active --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-2">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="rounds[${index}][sort_order]" class="form-control"
                                    value="0" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end mb-2">
                            <div class="form-check form-switch form-switch-md">
                                <input class="form-check-input" type="checkbox"
                                    name="rounds[${index}][is_active]" value="1" id="round_${index}_active" checked>
                                <label class="form-check-label fw-semibold" for="round_${index}_active">Active</label>
                            </div>
                        </div>
                    </div>
                    {{-- Submission Requirements (JSON) --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label class="form-label">
                                    Submission Requirements (JSON)
                                    <i class="ri-information-line" data-bs-toggle="tooltip" title='e.g. { "video": { "required": true, "max_duration_sec": 180 } }'></i>
                                </label>
                                <textarea name="rounds[${index}][submission_requirements]" class="form-control" rows="2"
                                    placeholder='{"video": {"required": true, "max_duration_sec": 180}}'></textarea>
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
                        {{-- Elimination-based configs --}}
                        <div class="row">
                            <div class="col-md-4 adv-config-advance_limit" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Tie Breaker</label>
                                    <select name="rounds[${index}][adv_config][cutoff_tie_breaker]" class="form-select">
                                        <option value="all_tied_advance">All Tied Advance</option>
                                        <option value="all_tied_eliminate">All Tied Eliminated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 adv-config-bottom_n" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Eliminate Count</label>
                                    <input type="number" name="rounds[${index}][adv_config][eliminate_count]"
                                        class="form-control" min="1" placeholder="e.g. 2">
                                </div>
                            </div>
                            <div class="col-md-4 adv-config-top_percent" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Keep Percent (%)</label>
                                    <input type="number" name="rounds[${index}][adv_config][keep_percent]"
                                        class="form-control" min="1" max="100" placeholder="e.g. 50">
                                </div>
                            </div>
                            <div class="col-md-4 adv-config-top_percent" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Tie Breaker</label>
                                    <select name="rounds[${index}][adv_config][cutoff_tie_breaker]" class="form-select">
                                        <option value="all_tied_advance">All Tied Advance</option>
                                        <option value="all_tied_eliminate">All Tied Eliminated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 adv-config-score_below_threshold" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Score Threshold</label>
                                    <input type="number" name="rounds[${index}][adv_config][score_threshold]"
                                        class="form-control" min="0" placeholder="e.g. 50">
                                </div>
                            </div>
                        </div>
                        {{-- Voting-based configs --}}
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted">Voting Config</small>
                            </div>
                            <div class="col-md-4 adv-config-voting-popular_vote" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Max Votes Per User</label>
                                    <input type="number" name="rounds[${index}][adv_config][max_votes_per_user]"
                                        class="form-control" min="1" placeholder="e.g. 10">
                                </div>
                            </div>
                            <div class="col-md-4 adv-config-voting-weighted" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Vote Weight</label>
                                    <input type="number" name="rounds[${index}][adv_config][vote_weight]"
                                        class="form-control" min="0.1" step="0.1" placeholder="e.g. 1.0">
                                </div>
                            </div>
                            <div class="col-md-6 adv-config-voting-categories" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">
                                        Categories (one per line)
                                        <i class="ri-information-line" data-bs-toggle="tooltip" title='Enter one category per line, e.g. Innovation, Presentation, Impact'></i>
                                    </label>
                                    <textarea name="rounds[${index}][adv_config][categories]" class="form-control" rows="2"
                                        placeholder="Innovation&#10;Presentation&#10;Impact"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4 adv-config-voting-categories" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">Max Score Per Category</label>
                                    <input type="number" name="rounds[${index}][adv_config][max_score_per_category]"
                                        class="form-control" min="1" placeholder="e.g. 10">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Toggle advancement config fields based on elimination rule and voting strategy.
         */
        function toggleAdvConfig(container) {
            const eliminationRule = container.querySelector('[name$="[elimination_rule]"]');
            const votingStrategy = container.querySelector('[name$="[voting_strategy]"]');

            if (!eliminationRule || !votingStrategy) return;

            // Hide all dynamic config groups within this container (excluding the main section wrapper)
            container.querySelectorAll('[class*="adv-config-"]:not(.adv-config-section)').forEach(el => {
                if (el.closest('.round-item') === container) {
                    el.style.display = 'none';
                }
            });

            // Show the matching elimination config
            const elimValue = eliminationRule.value;
            container.querySelectorAll('.adv-config-' + elimValue).forEach(el => {
                if (el.closest('.round-item') === container) {
                    el.style.display = '';
                }
            });

            // Show matching voting config
            const voteValue = votingStrategy.value;
            container.querySelectorAll('.adv-config-voting-' + voteValue).forEach(el => {
                if (el.closest('.round-item') === container) {
                    el.style.display = '';
                }
            });

            // For judge_scored and weighted, also show categories section
            if (voteValue === 'judge_scored' || voteValue === 'weighted') {
                container.querySelectorAll('.adv-config-voting-categories').forEach(el => {
                    if (el.closest('.round-item') === container) {
                        el.style.display = '';
                    }
                });
            }
        }

        // Attach toggle listeners to a round container
        function attachToggleListeners(container) {
            const elimSelect = container.querySelector('[name$="[elimination_rule]"]');
            const voteSelect = container.querySelector('[name$="[voting_strategy]"]');

            if (elimSelect) {
                elimSelect.addEventListener('change', function() {
                    toggleAdvConfig(this.closest('.round-item'));
                });
            }
            if (voteSelect) {
                voteSelect.addEventListener('change', function() {
                    toggleAdvConfig(this.closest('.round-item'));
                });
            }

            // Initial toggle
            toggleAdvConfig(container);
        }

        // Initialize existing rounds
        document.querySelectorAll('.round-item').forEach(function(item) {
            item.dataset.listenerAttached = 'true';
            attachToggleListeners(item);
        });

        // Override add button to also attach listeners
        addBtn.addEventListener('click', function() {
            container.insertAdjacentHTML('beforeend', createRoundHtml(roundIndex));
            roundIndex++;
            updateBadges();

            // Attach listeners to newly added rounds
            container.querySelectorAll('.round-item:not([data-listener-attached])').forEach(function(item) {
                item.dataset.listenerAttached = 'true';
                attachToggleListeners(item);
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
