<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="ri-trophy-line me-1"></i> Rounds Content
        </h5>
    </div>
    <div class="card-body">

        @php
            $roundsRow = $cmsData->get('rounds');
            $roundsData = $roundsRow?->metadata ?? [];
            $block = $roundsData['block'] ?? [];
            $rounds = $roundsData['rounds'] ?? [];
            $bottom = $roundsData['bottom'] ?? [];
        @endphp

        <form id="roundsForm" enctype="multipart/form-data">

            {{-- ══════════════════════════════════════════════════════════
                 PART 1 — SINGLE CONTENT BLOCK (stays OUTSIDE the loop, top)
            ══════════════════════════════════════════════════════════ --}}
            <div class="card border mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="ri-layout-grid-line me-1"></i> Content Block (Single — Title,
                        Subtitle, Description &amp; Image)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Image <span class="text-muted">(preview shown)</span></label>
                            <div class="image-upload-box">
                                <input type="file" name="block_image" class="form-control image-input" accept="image/*">
                                <input type="hidden" name="existing_block_image" value="{{ $block['image'] ?? '' }}">
                                <div class="image-preview mt-2 {{ empty($block['image']) ? 'd-none' : '' }}">
                                    @if(!empty($block['image']))
                                        <img src="{{ asset($block['image']) }}" class="rounded border shadow-sm"
                                            style="height: 90px; width: 90px; object-fit: cover;">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Title</label>
                            <input type="text" name="block_title" class="form-control" value="{{ $block['title'] ?? '' }}"
                                placeholder="Block Title">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="block_subtitle" class="form-control"
                                value="{{ $block['subtitle'] ?? '' }}" placeholder="Block Subtitle">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="block_description" class="form-control" rows="4"
                                placeholder="Block description...">{{ $block['description'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            {{-- ══════════════════════════════════════════════════════════
                 PART 2 — MULTIPLE ROUNDS (repeater, max 5)
            ══════════════════════════════════════════════════════════ --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="ri-trophy-line me-1"></i> Rounds (Max 5)</h5>
                <button type="button" class="btn btn-success btn-sm" id="addRoundBtn">
                    <i class="ri-add-line me-1"></i> Add Round
                </button>
            </div>
            <p class="text-muted small mb-3">
                Each round has its own round text, title, subtitle, icon, goal &amp; requirements.
            </p>

            <div id="roundsContainer">
                @foreach($rounds as $rIndex => $round)
                    <div class="round-item card border shadow-sm mb-4" data-round-index="{{ $rIndex }}">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <span class="badge bg-primary fs-6 round-item-label">Round #{{ $rIndex + 1 }}</span>
                            <button type="button" class="btn btn-danger btn-sm remove-round" title="Remove this round">
                                <i class="ri-delete-bin-5-line me-1"></i> Delete Round
                            </button>
                        </div>
                        <div class="card-body">
                            {{-- Round Header Details --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Round Text <span class="text-muted">(e.g. ROUND 1)</span></label>
                                    <input type="text" name="rounds[{{ $rIndex }}][round_text]" class="form-control"
                                        value="{{ $round['round_text'] ?? '' }}" placeholder="ROUND 1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Round Title <span class="text-muted">(e.g. OPEN NOMINATIONS)</span></label>
                                    <input type="text" name="rounds[{{ $rIndex }}][round_title]" class="form-control"
                                        value="{{ $round['round_title'] ?? '' }}" placeholder="OPEN NOMINATIONS">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Subtitle <span class="text-muted">(e.g. Up to 100 Businesses)</span></label>
                                    <input type="text" name="rounds[{{ $rIndex }}][subtitle]" class="form-control"
                                        value="{{ $round['subtitle'] ?? '' }}" placeholder="Up to 100 Businesses">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Header Icon / Image</label>
                                    <div class="image-upload-box">
                                        <input type="file" name="rounds[{{ $rIndex }}][icon]" class="form-control image-input" accept="image/*">
                                        <input type="hidden" name="rounds[{{ $rIndex }}][existing_icon]" value="{{ $round['icon'] ?? '' }}">
                                        <div class="image-preview mt-2 {{ empty($round['icon']) ? 'd-none' : '' }}">
                                            @if(!empty($round['icon']))
                                                <img src="{{ asset($round['icon']) }}" class="rounded border shadow-sm"
                                                    style="height: 80px; width: 80px; object-fit: cover;">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- Goal & Requirements --}}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Goal Label <span class="text-muted">(e.g. Goal:)</span></label>
                                    <input type="text" name="rounds[{{ $rIndex }}][goal_label]" class="form-control"
                                        value="{{ $round['goal_label'] ?? '' }}" placeholder="Goal:">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Goal Text</label>
                                    <input type="text" name="rounds[{{ $rIndex }}][goal_text]" class="form-control"
                                        value="{{ $round['goal_text'] ?? '' }}" placeholder="Secure a spot in the competition.">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Requirements Label <span class="text-muted">(e.g. Requirements:)</span></label>
                                    <input type="text" name="rounds[{{ $rIndex }}][requirements_label]" class="form-control"
                                        value="{{ $round['requirements_label'] ?? '' }}" placeholder="Requirements:">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Requirements List</h6>
                                        <button type="button" class="btn btn-success btn-sm add-requirement">
                                            <i class="ri-add-line me-1"></i> Add Requirement
                                        </button>
                                    </div>
                                    <div class="requirements-container">
                                        @foreach(($round['requirements'] ?? []) as $reqIndex => $req)
                                            <div class="requirement-item input-group mb-2">
                                                <span class="input-group-text">#{{ $reqIndex + 1 }}</span>
                                                <textarea name="rounds[{{ $rIndex }}][requirements][]" class="form-control" rows="2"
                                                    placeholder="Requirement text...">{{ $req }}</textarea>
                                                <button type="button" class="btn btn-danger remove-requirement">×</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($round['requirements'] ?? []) === 0)
                                        <div class="text-center text-muted border rounded p-3 requirements-empty">
                                            <p class="mb-0">No requirements yet. Click <strong>Add Requirement</strong>.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(count($rounds) === 0)
                <div class="text-center text-muted border rounded p-5" id="roundsEmptyState">
                    <i class="ri-trophy-line fs-1"></i>
                    <p class="mb-0 mt-2">No rounds yet. Click <strong>Add Round</strong> to get started.</p>
                </div>
            @endif

            <hr>

            {{-- ══════════════════════════════════════════════════════════
                 PART 3 — BOTTOM SECTION (single — Title, Subtitle & Description)
            ══════════════════════════════════════════════════════════ --}}
            <div class="card border mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="ri-article-line me-1"></i> Bottom Section (Title, Subtitle &amp;
                        Description)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="bottom_title" class="form-control" value="{{ $bottom['title'] ?? '' }}"
                                placeholder="Bottom Section Title">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="bottom_subtitle" class="form-control"
                                value="{{ $bottom['subtitle'] ?? '' }}" placeholder="Bottom Section Subtitle">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="bottom_description" class="form-control" rows="4"
                                placeholder="Bottom section description...">{{ $bottom['description'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary px-5" id="saveRoundsBtn">Save All Rounds</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            const MAX_ROUNDS = 5;

            // ── Round HTML (template for new rounds) ──
            function roundHtml(roundIdx) {
                return `
                <div class="round-item card border shadow-sm mb-4" data-round-index="${roundIdx}">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <span class="badge bg-primary fs-6 round-item-label">Round #</span>
                        <button type="button" class="btn btn-danger btn-sm remove-round" title="Remove this round">
                            <i class="ri-delete-bin-5-line me-1"></i> Delete Round
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Round Text <span class="text-muted">(e.g. ROUND 1)</span></label>
                                <input type="text" name="rounds[${roundIdx}][round_text]" class="form-control" placeholder="ROUND 1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Round Title <span class="text-muted">(e.g. OPEN NOMINATIONS)</span></label>
                                <input type="text" name="rounds[${roundIdx}][round_title]" class="form-control" placeholder="OPEN NOMINATIONS">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Subtitle <span class="text-muted">(e.g. Up to 100 Businesses)</span></label>
                                <input type="text" name="rounds[${roundIdx}][subtitle]" class="form-control" placeholder="Up to 100 Businesses">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Header Icon / Image</label>
                                <div class="image-upload-box">
                                    <input type="file" name="rounds[${roundIdx}][icon]" class="form-control image-input" accept="image/*">
                                    <input type="hidden" name="rounds[${roundIdx}][existing_icon]" value="">
                                    <div class="image-preview mt-2 d-none">
                                        <img class="rounded border shadow-sm" style="height: 80px; width: 80px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Goal Label <span class="text-muted">(e.g. Goal:)</span></label>
                                <input type="text" name="rounds[${roundIdx}][goal_label]" class="form-control" placeholder="Goal:">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Goal Text</label>
                                <input type="text" name="rounds[${roundIdx}][goal_text]" class="form-control" placeholder="Secure a spot in the competition.">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Requirements Label <span class="text-muted">(e.g. Requirements:)</span></label>
                                <input type="text" name="rounds[${roundIdx}][requirements_label]" class="form-control" placeholder="Requirements:">
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Requirements List</h6>
                                    <button type="button" class="btn btn-success btn-sm add-requirement">
                                        <i class="ri-add-line me-1"></i> Add Requirement
                                    </button>
                                </div>
                                <div class="requirements-container"></div>
                                <div class="text-center text-muted border rounded p-3 requirements-empty">
                                    <p class="mb-0">No requirements yet. Click <strong>Add Requirement</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            // ── Live image preview on file select ──
            $(document).on('change', '.image-input', function () {
                const file = this.files && this.files[0];
                if (!file) return;

                const $box = $(this).closest('.image-upload-box');
                const $preview = $box.find('.image-preview');
                const $img = $preview.find('img');

                const reader = new FileReader();
                reader.onload = function (e) {
                    $img.attr('src', e.target.result);
                    $preview.removeClass('d-none');
                };
                reader.readAsDataURL(file);
            });

            // ── Renumber rounds ──
            function renumberRounds() {
                $('#roundsContainer .round-item').each(function (roundIdx) {
                    const $round = $(this);
                    $round.attr('data-round-index', roundIdx);
                    $round.find('.round-item-label').text('Round #' + (roundIdx + 1));

                    // Renumber round index in all input names for this round
                    $round.find('[name^="rounds["]').each(function () {
                        const $el = $(this);
                        let name = $el.attr('name');
                        name = name.replace(/^rounds\[\d+\]/, `rounds[${roundIdx}]`);
                        $el.attr('name', name);
                    });
                });

                // Enforce round max
                const roundCount = $('#roundsContainer .round-item').length;
                $('#addRoundBtn').prop('disabled', roundCount >= MAX_ROUNDS);
                $('#roundsEmptyState').toggle(roundCount === 0);
            }

            // ── Add Round ──
            $('#addRoundBtn').on('click', function () {
                const count = $('#roundsContainer .round-item').length;
                if (count >= MAX_ROUNDS) {
                    Toast.warning(`You can only add up to ${MAX_ROUNDS} rounds.`);
                    return;
                }
                $('#roundsEmptyState').hide();
                $('#roundsContainer').append(roundHtml(count));
                renumberRounds();
            });

            // ── Remove Round ──
            $(document).on('click', '.remove-round', function () {
                if ($('#roundsContainer .round-item').length <= 1) {
                    Toast.warning('At least one round is required.');
                    return;
                }
                $(this).closest('.round-item').remove();
                renumberRounds();
            });

            // ── Add Requirement ──
            $(document).on('click', '.add-requirement', function () {
                const $round = $(this).closest('.round-item');
                const roundIdx = $round.attr('data-round-index');
                $round.find('.requirements-empty').hide();
                $round.find('.requirements-container').append(`
                    <div class="requirement-item input-group mb-2">
                        <span class="input-group-text">#</span>
                        <textarea name="rounds[${roundIdx}][requirements][]" class="form-control" rows="2" placeholder="Requirement text..."></textarea>
                        <button type="button" class="btn btn-danger remove-requirement">×</button>
                    </div>`);
                renumberRequirements($round);
            });

            // ── Remove Requirement ──
            $(document).on('click', '.remove-requirement', function () {
                const $round = $(this).closest('.round-item');
                if ($round.find('.requirements-container .requirement-item').length <= 1) {
                    Toast.warning('At least one requirement is required.');
                    return;
                }
                $(this).closest('.requirement-item').remove();
                renumberRequirements($round);
            });

            function renumberRequirements($round) {
                $round.find('.requirements-container .requirement-item').each(function (idx) {
                    $(this).find('.input-group-text').text('#' + (idx + 1));
                });
                const count = $round.find('.requirements-container .requirement-item').length;
                $round.find('.requirements-empty').toggle(count === 0);
            }

            // ── Form Submit Handler ──
            $('#roundsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveRoundsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

                axios.post("{{ route('admin.cms.rounds.update') }}", new FormData(this))
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => location.reload(), 1200);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });
        });
    </script>
@endpush
