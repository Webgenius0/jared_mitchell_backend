@extends('layout.master-layout')

@section('title', 'Winners')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Winners</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Winners</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info banner explaining the flow --}}
        <div class="row">
            <div class="col-12">
                <div class="alert alert-soft-info border-0 d-flex align-items-start gap-2 mb-3">
                    <i class="ri-information-line fs-18 mt-1"></i>
                    <div>
                        <strong>How winners work:</strong> The scheduler finalizes the top 3 businesses of the final
                        round — it does not decide the winner. Confirm (or change) the winner here among the top 3.
                        You can also view all submitted videos & images, hide/include specific media, and upload custom showcase materials.
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Final Round — Top 3 Finalists</h5>
                    </div>

                    {{-- Filters --}}
                    <div class="card-body border-bottom pb-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Season</label>
                                <select class="form-select" id="seasonFilter">
                                    <option value="">All Seasons</option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}">{{ $season->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Round</label>
                                <select class="form-select" id="roundFilter" disabled>
                                    <option value="">Final Rounds</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="button" id="resetFilters" class="btn btn-soft-danger">
                                        <i class="ri-refresh-line me-1"></i> Reset Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="winnersTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Season</th>
                                        <th>Round</th>
                                        <th>Business</th>
                                        <th>Points</th>
                                        <th>Rank</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 250px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Winner Showcase Modal -->
<div class="modal fade" id="editShowcaseModal" tabindex="-1" aria-labelledby="editShowcaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editShowcaseModalLabel"><i class="ri-edit-box-line me-1"></i> Edit Winner Showcase Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editShowcaseForm" enctype="multipart/form-data">
                <input type="hidden" id="showcaseContestantId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="showcaseTitle" class="form-label font-semibold">Custom Showcase Title</label>
                            <input type="text" class="form-control" id="showcaseTitle" name="title" placeholder="Enter custom showcase title">
                            <div class="form-text">If left empty, defaults to the business display name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="showcaseMediaFiles" class="form-label font-semibold">Upload New Showcase Media (Images & Videos)</label>
                            <input type="file" class="form-control" id="showcaseMediaFiles" name="media_files[]" multiple accept="image/*,video/*">
                            <div class="form-text">Upload additional photos or videos for this winner's showcase display.</div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="showcaseDescription" class="form-label font-semibold">Custom Showcase Description / Story</label>
                            <textarea class="form-control" id="showcaseDescription" name="description" rows="3" placeholder="Enter custom description or story for the winner..."></textarea>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Original Business & Submissions Media -->
                    <div class="mb-4">
                        <h6 class="font-semibold text-primary mb-2">
                            <i class="ri-movie-line me-1"></i> Winner Submitted Media (Business Profile & Contest Submissions)
                        </h6>
                        <p class="text-muted small mb-2">Toggle "Hide" on any video/photo to exclude it from the winner's public API showcase.</p>
                        <div id="originalMediaList" class="row g-2">
                            <span class="text-muted small">Loading submitted media...</span>
                        </div>
                    </div>

                    <!-- Admin Custom Uploaded Media -->
                    <div class="mb-3">
                        <h6 class="font-semibold text-success mb-2">
                            <i class="ri-upload-cloud-line me-1"></i> Admin Uploaded Custom Media
                        </h6>
                        <div id="customMediaList" class="row g-2">
                            <span class="text-muted small">No custom media uploaded yet.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveShowcaseBtn">
                        <i class="ri-save-line me-1"></i> Save Showcase Details
                    </button>
                </div>
            </form>
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

        const table = $('#winnersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.winners.index') }}',
                data: function (d) {
                    d.season_id = $('#seasonFilter').val();
                    d.round_id = $('#roundFilter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'season', name: 'season' },
                { data: 'round', name: 'round' },
                { data: 'business', name: 'business' },
                { data: 'points', name: 'points', className: 'text-center' },
                { data: 'rank', name: 'rank', className: 'text-center' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
                emptyTable: 'No finalized rounds found. Run the scheduler or select a different filter.',
            }
        });

        // ── Season filter → loads rounds (final rounds only) ──
        $('#seasonFilter').on('change', function () {
            const seasonId = this.value;
            const roundSelect = $('#roundFilter');

            roundSelect.html('<option value="">Final Rounds</option>').prop('disabled', true);

            if (seasonId) {
                axios.get('{{ route('admin.winners.rounds') }}', { params: { season_id: seasonId } })
                    .then(function (res) {
                        const finalRounds = (res.data || []).filter(function (r) { return r.is_final; });
                        finalRounds.forEach(function (round) {
                            const opt = document.createElement('option');
                            opt.value = round.id;
                            opt.textContent = 'Final: Round ' + round.round_number + ' — ' + (round.title || '');
                            roundSelect.append(opt);
                        });
                        roundSelect.prop('disabled', finalRounds.length === 0);
                    })
                    .catch(function () {
                        Toast.error('Failed to load rounds.');
                    });
            }

            table.ajax.reload();
        });

        // ── Round filter ──
        $('#roundFilter').on('change', function () {
            table.ajax.reload();
        });

        // ── Reset filters ──
        $('#resetFilters').on('click', function () {
            $('#seasonFilter').val('');
            $('#roundFilter').html('<option value="">Final Rounds</option>').prop('disabled', true);
            table.ajax.reload();
        });

        // ── Confirm / change winner ──
        $(document).on('click', '.confirm-winner-btn', function () {
            const roundId = $(this).data('round-id');
            const contestantId = $(this).data('contestant-id');
            const business = $(this).data('business');
            const isChange = $(this).text().indexOf('Change') !== -1;

            Alert.confirm(
                `This will make <strong>${business}</strong> the winner of this season. ` +
                (isChange ? 'The previous winner will be demoted to runner-up/finalist.' : 'Only after confirmation will this winner appear in the public API.'),
                {
                    title: isChange ? 'Change Winner?' : 'Confirm Winner?',
                    type: 'success',
                    confirmText: isChange ? 'Yes, change winner' : 'Yes, confirm winner'
                }
            ).then(confirmed => {
                if (!confirmed) return;

                axios.post(`{{ route('admin.winners.confirm-winner', ':round') }}`.replace(':round', roundId), {
                    contestant_id: contestantId
                })
                    .then(res => {
                        Toast.success(res.data.message);
                        table.ajax.reload(null, false);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Failed to confirm winner.');
                    });
            });
        });

        // ── Edit Showcase Modal ──
        $(document).on('click', '.edit-showcase-btn', function () {
            const contestantId = $(this).data('contestant-id');
            const business = $(this).data('business');

            $('#showcaseContestantId').val(contestantId);
            $('#editShowcaseModalLabel').html(`<i class="ri-edit-box-line me-1"></i> Edit Winner Showcase — <strong>${business}</strong>`);
            $('#showcaseTitle').val('');
            $('#showcaseDescription').val('');
            $('#showcaseMediaFiles').val('');
            $('#originalMediaList').html('<div class="col-12"><div class="spinner-border spinner-border-sm text-primary"></div> Loading submitted media...</div>');
            $('#customMediaList').html('<div class="col-12"><div class="spinner-border spinner-border-sm text-primary"></div> Loading custom media...</div>');

            const modal = new bootstrap.Modal(document.getElementById('editShowcaseModal'));
            modal.show();

            fetchShowcaseDetails(contestantId);
        });

        function fetchShowcaseDetails(contestantId) {
            const getUrl = `{{ route('admin.winners.get-showcase', ':id') }}`.replace(':id', contestantId);

            axios.get(getUrl)
                .then(res => {
                    const data = res.data?.data || {};
                    $('#showcaseTitle').val(data.title || '');
                    $('#showcaseDescription').val(data.description || '');

                    renderOriginalMedia(data.original_media || [], contestantId);
                    renderCustomMedia(data.custom_media || [], contestantId);
                })
                .catch(err => {
                    Toast.error('Failed to load showcase details.');
                });
        }

        function renderOriginalMedia(originalMedia, contestantId) {
            const container = $('#originalMediaList');
            container.empty();

            if (!originalMedia || originalMedia.length === 0) {
                container.html('<div class="col-12 text-muted small">No submitted media files found from profile or round submissions.</div>');
                return;
            }

            originalMedia.forEach((media) => {
                const isVideo = media.type === 'video' || (media.mime_type && media.mime_type.includes('video'));
                let mediaPreview = isVideo
                    ? `<video src="${media.file_path}" class="rounded w-100" style="height:100px;object-fit:cover;" controls></video>`
                    : `<img src="${media.file_path}" class="rounded w-100" style="height:100px;object-fit:cover;" alt="">`;

                const isExcluded = media.is_excluded;
                const badgeClass = isExcluded ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success';
                const badgeText = isExcluded ? 'Hidden' : 'Visible';
                const btnClass = isExcluded ? 'btn-soft-success' : 'btn-soft-danger';
                const btnIcon = isExcluded ? 'ri-eye-line' : 'ri-eye-off-line';
                const btnText = isExcluded ? 'Include' : 'Hide';

                const card = `
                    <div class="col-md-3 col-6">
                        <div class="card border mb-0 position-relative p-1 ${isExcluded ? 'opacity-50' : ''}">
                            ${mediaPreview}
                            <div class="mt-1 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-light text-dark small me-1">${media.source}</span>
                                    <span class="badge ${badgeClass} small">${badgeText}</span>
                                </div>
                                <button type="button" class="btn btn-sm ${btnClass} py-0 px-2 toggle-media-btn" data-media-id="${media.id}" data-contestant-id="${contestantId}">
                                    <i class="${btnIcon} me-1"></i>${btnText}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        function renderCustomMedia(customMedia, contestantId) {
            const container = $('#customMediaList');
            container.empty();

            if (!customMedia || customMedia.length === 0) {
                container.html('<div class="col-12 text-muted small">No custom media uploaded yet. Upload below if you want to add extra images/videos.</div>');
                return;
            }

            customMedia.forEach((media) => {
                const isVideo = media.type === 'video' || (media.mime_type && media.mime_type.includes('video'));
                let mediaPreview = isVideo
                    ? `<video src="${media.file_path}" class="rounded w-100" style="height:100px;object-fit:cover;" controls></video>`
                    : `<img src="${media.file_path}" class="rounded w-100" style="height:100px;object-fit:cover;" alt="">`;

                const card = `
                    <div class="col-md-3 col-6">
                        <div class="card border mb-0 position-relative p-1">
                            ${mediaPreview}
                            <div class="mt-1 d-flex align-items-center justify-content-between">
                                <span class="text-truncate small text-muted me-1" style="max-width:100px;" title="${media.file_name}">${media.file_name}</span>
                                <button type="button" class="btn btn-sm btn-soft-danger py-0 px-1 delete-custom-media-btn" data-index="${media.index}" data-contestant-id="${contestantId}">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        // ── Toggle Original Media Hide/Include ──
        $(document).on('click', '.toggle-media-btn', function () {
            const mediaId = $(this).data('media-id');
            const contestantId = $(this).data('contestant-id');

            const postUrl = `{{ route('admin.winners.toggle-exclude-media', ':id') }}`.replace(':id', contestantId);

            axios.post(postUrl, { media_id: mediaId })
                .then(res => {
                    Toast.success(res.data.message);
                    fetchShowcaseDetails(contestantId);
                })
                .catch(err => {
                    Toast.error('Failed to toggle media visibility.');
                });
        });

        // ── Delete Custom Media Item ──
        $(document).on('click', '.delete-custom-media-btn', function () {
            const index = $(this).data('index');
            const contestantId = $(this).data('contestant-id');

            Alert.confirm('Delete this custom showcase media file?', {
                title: 'Delete Media',
                type: 'warning',
                confirmText: 'Yes, delete'
            }).then(confirmed => {
                if (!confirmed) return;

                const delUrl = `{{ route('admin.winners.delete-showcase-media', ['contestant' => ':id', 'mediaIndex' => ':idx']) }}`
                    .replace(':id', contestantId)
                    .replace(':idx', index);

                axios.delete(delUrl)
                    .then(res => {
                        Toast.success(res.data.message);
                        fetchShowcaseDetails(contestantId);
                    })
                    .catch(err => {
                        Toast.error('Failed to delete media.');
                    });
            });
        });

        // ── Submit Edit Showcase Form ──
        $('#editShowcaseForm').on('submit', function (e) {
            e.preventDefault();

            const contestantId = $('#showcaseContestantId').val();
            const postUrl = `{{ route('admin.winners.update-showcase', ':id') }}`.replace(':id', contestantId);

            const formData = new FormData(this);
            const btn = $('#saveShowcaseBtn');

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            axios.post(postUrl, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(res => {
                    Toast.success(res.data.message);
                    bootstrap.Modal.getInstance(document.getElementById('editShowcaseModal')).hide();
                    table.ajax.reload(null, false);
                })
                .catch(err => {
                    Toast.error(err.response?.data?.message || 'Failed to save showcase details.');
                })
                .finally(() => {
                    btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i> Save Showcase Details');
                });
        });
    })();
</script>
@endpush
