@extends('layout.master-layout')

@section('title', 'Winner Articles')

@push('styles')
<style>
    .media-preview-card {
        transition: all 0.2s ease-in-out;
    }
    .media-preview-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Winner Articles</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Winner Articles</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Action Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card bg-soft-primary border-0">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title text-primary mb-1"><i class="ri-newspaper-line me-1"></i> Winner Articles Management</h5>
                            <p class="card-text text-muted mb-0">Write articles for specific Boss Beginning & Spotlight Winners with multiple image/video attachments.</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#articleModal" id="openCreateModalBtn">
                            <i class="ri-add-line me-1"></i> Add New Article
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom pb-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <label class="form-label text-muted small mb-1">Filter by Type</label>
                                <select class="form-select form-select-sm" id="typeFilter">
                                    <option value="">All Types</option>
                                    <option value="boss_beginning">Boss Beginning Winner</option>
                                    <option value="spotlight">Spotlight Winner</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="winnerArticlesTable" class="table table-bordered align-middle table-nowrap mb-0 w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Type</th>
                                        <th>Target Winner / Info</th>
                                        <th>Title</th>
                                        <th>Media Count</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="text-center" style="width: 180px;">Action</th>
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

<!-- Create / Edit Article Modal -->
<div class="modal fade" id="articleModal" tabindex="-1" aria-labelledby="articleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="articleForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="articleId" name="article_id" value="">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="articleModalLabel">Add Winner Article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Article Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Article Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="articleType" name="type" required>
                                <option value="boss_beginning">Boss Beginning Winner</option>
                                <option value="spotlight">Spotlight Winner</option>
                            </select>
                        </div>
                        
                        <!-- Active Status -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Active Status</label>
                            <div class="form-check form-switch fs-16 mt-2">
                                <input class="form-check-input" type="checkbox" id="articleIsActive" name="is_active" value="1" checked>
                                <label class="form-check-label fs-14" for="articleIsActive">Published / Visible</label>
                            </div>
                        </div>

                        <!-- Boss Beginning Winner Selection -->
                        <div class="col-md-12" id="bossWinnerGroup">
                            <label class="form-label fw-semibold">Select Boss Beginning Winner</label>
                            <select class="form-select" id="contestantId" name="contestant_id">
                                @if(empty($bossWinners) || count($bossWinners) == 0)
                                    <option value="">-- No Winner Found (General Boss Article) --</option>
                                @endif
                                @foreach($bossWinners as $bw)
                                    <option value="{{ $bw['id'] }}" {{ $bw['id'] == $defaultBossWinnerId ? 'selected' : '' }}>
                                        {{ $bw['display_name'] }} {{ $bw['id'] == $defaultBossWinnerId ? '★ (Current Latest Winner)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2"><i class="ri-information-line me-1"></i> Current winner is automatically selected by default.</small>
                        </div>

                        <!-- Spotlight Winner Selection -->
                        <div class="col-md-12 d-none" id="spotlightWinnerGroup">
                            <label class="form-label fw-semibold">Select Spotlight Winner</label>
                            <select class="form-select" id="spotlightNomineeId" name="spotlight_week_nominee_id">
                                @if(empty($spotlightWinners) || count($spotlightWinners) == 0)
                                    <option value="">-- No Winner Found (General Spotlight Article) --</option>
                                @endif
                                @foreach($spotlightWinners as $sw)
                                    <option value="{{ $sw['id'] }}" {{ $sw['id'] == $defaultSpotlightWinnerId ? 'selected' : '' }}>
                                        {{ $sw['display_name'] }} {{ $sw['id'] == $defaultSpotlightWinnerId ? '★ (Current Latest Winner)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2"><i class="ri-information-line me-1"></i> Current winner is automatically selected by default.</small>
                        </div>

                        <!-- Title -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Article Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="articleTitle" name="title" placeholder="Enter article headline or title" required>
                        </div>

                        <!-- Standard Textarea Content -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Article Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="articleContent" name="content" rows="6" placeholder="Write full article content or details..." required></textarea>
                            <div class="d-flex justify-content-end align-items-center mt-1">
                                <small class="text-muted" id="charCount">0 characters</small>
                            </div>
                        </div>

                        <!-- Multiple Media Files Upload Input & Add Button Section -->
                        <div class="col-12 mt-4 pt-3 border-top">
                            <label class="form-label fw-semibold fs-15"><i class="ri-attachment-line me-1 text-primary"></i> Upload Media Files (Images & Videos)</label>
                            
                            <div class="input-group mb-2">
                                <input type="file" class="form-control" id="articleMediaInput" multiple accept="image/*,video/*">
                                <button type="button" class="btn btn-primary px-3" id="addMediaFilesBtn">
                                    <i class="ri-add-line me-1"></i> Add Media
                                </button>
                            </div>

                            <!-- Prominent Notice Alert Box -->
                            <div class="alert alert-soft-info py-2 px-3 mt-2 mb-1 border-0 rounded d-flex align-items-center gap-2">
                                <i class="ri-information-line fs-18 text-info"></i>
                                <span class="fs-13 text-dark fw-medium">Select image or video files and click "+ Add Media". You can add multiple batches before clicking Save.</span>
                            </div>
                        </div>

                        <!-- Live Preview for Staged New Files -->
                        <div class="col-12 mt-3 pt-3 border-top d-none" id="newMediaPreviewContainer">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold text-primary mb-0"><i class="ri-eye-line me-1"></i> Staged Media Files (Ready to Upload)</label>
                                <span class="badge bg-soft-primary text-primary" id="newMediaCountBadge">0 files</span>
                            </div>
                            <div class="row g-2 p-2 bg-light rounded border" id="newMediaPreviewList"></div>
                        </div>

                        <!-- Existing Media Preview container for Editing -->
                        <div class="col-12 mt-4 pt-3 border-top d-none" id="existingMediaContainer">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-semibold text-success mb-0"><i class="ri-folder-upload-line me-1"></i> Uploaded Media Attachments</label>
                                <span class="badge bg-soft-success text-success" id="existingMediaCountBadge">0 files</span>
                            </div>
                            <div class="row g-2 p-2 bg-light rounded border" id="existingMediaList"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveArticleBtn">
                        <i class="ri-save-line me-1"></i> Save Article
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const defaultBossWinnerId = "{{ $defaultBossWinnerId }}";
    const defaultSpotlightWinnerId = "{{ $defaultSpotlightWinnerId }}";

    // Character counter for standard textarea
    $('#articleContent').on('input', function() {
        const len = $(this).val().length;
        $('#charCount').text(len + ' characters');
    });

    // DataTables Initialization
    let table = $('#winnerArticlesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.winner-articles.index') }}",
            data: function(d) {
                d.type = $('#typeFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'type', name: 'type' },
            { data: 'winner_info', name: 'winner_info', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'media_count', name: 'media_count', orderable: false, searchable: false },
            { data: 'is_active', name: 'is_active' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    $('#typeFilter').change(function() {
        table.draw();
    });

    // Toggle Winner Selection Dropdowns based on Article Type
    function toggleWinnerDropdowns(type) {
        if (type === 'spotlight') {
            $('#bossWinnerGroup').addClass('d-none');
            $('#spotlightWinnerGroup').removeClass('d-none');
            if (!$('#articleId').val() && defaultSpotlightWinnerId) {
                $('#spotlightNomineeId').val(defaultSpotlightWinnerId);
            }
        } else {
            $('#bossWinnerGroup').removeClass('d-none');
            $('#spotlightWinnerGroup').addClass('d-none');
            if (!$('#articleId').val() && defaultBossWinnerId) {
                $('#contestantId').val(defaultBossWinnerId);
            }
        }
    }

    $('#articleType').change(function() {
        toggleWinnerDropdowns($(this).val());
    });

    // Media Files Staging Array
    let stagedNewFiles = [];

    $('#addMediaFilesBtn').on('click', function() {
        const input = document.getElementById('articleMediaInput');
        if (!input.files || input.files.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Media Selected',
                text: 'Please select at least one image or video file to add.',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
            return;
        }

        const newFiles = Array.from(input.files);
        stagedNewFiles = stagedNewFiles.concat(newFiles);
        
        // Reset file input for next batch selection
        input.value = '';

        renderStagedMediaPreviews();
    });

    function renderStagedMediaPreviews() {
        const container = $('#newMediaPreviewList');
        container.html('');
        if (stagedNewFiles.length === 0) {
            $('#newMediaPreviewContainer').addClass('d-none');
            return;
        }
        $('#newMediaCountBadge').text(stagedNewFiles.length + ' files');
        $('#newMediaPreviewContainer').removeClass('d-none');

        stagedNewFiles.forEach((file, index) => {
            const isVideo = file.type.startsWith('video/') || /\.(mp4|mov|avi|webm)$/i.test(file.name);
            const objectUrl = URL.createObjectURL(file);
            
            let mediaTag = isVideo 
                ? `<video src="${objectUrl}" style="height:85px; width:120px; object-fit:cover;" class="rounded border" controls></video>`
                : `<img src="${objectUrl}" style="height:85px; width:120px; object-fit:cover;" class="rounded border"/>`;
            
            const cardHtml = `
                <div class="col-auto position-relative text-center p-2 border rounded bg-white media-preview-card" id="staged-media-card-${index}">
                    ${mediaTag}
                    <div class="mt-1 small text-truncate fw-medium" style="max-width:120px;" title="${file.name}">${file.name}</div>
                    <button type="button" class="btn btn-danger btn-sm p-0 px-2 mt-1 remove-staged-file-btn" data-index="${index}">
                        <i class="ri-close-line"></i> Remove
                    </button>
                </div>
            `;
            container.append(cardHtml);
        });
    }

    // Remove single file from stagedNewFiles list
    $(document).on('click', '.remove-staged-file-btn', function() {
        const index = $(this).data('index');
        stagedNewFiles.splice(index, 1);
        renderStagedMediaPreviews();
    });

    // Reset Modal on open
    $('#openCreateModalBtn').click(function() {
        $('#articleForm')[0].reset();
        $('#articleId').val('');
        stagedNewFiles = [];
        $('#articleMediaInput').val('');
        $('#newMediaPreviewContainer').addClass('d-none');
        $('#newMediaPreviewList').html('');
        $('#articleContent').val('');
        $('#charCount').text('0 characters');
        $('#articleModalLabel').text('Add Winner Article');
        $('#existingMediaContainer').addClass('d-none');
        $('#existingMediaList').html('');
        toggleWinnerDropdowns('boss_beginning');
        $('#articleModal').modal('show');
    });

    // Edit Article Click Handler
    $(document).on('click', '.edit-article-btn', function() {
        let id = $(this).data('id');
        stagedNewFiles = [];
        $('#articleMediaInput').val('');
        $('#newMediaPreviewContainer').addClass('d-none');
        $('#newMediaPreviewList').html('');

        $.ajax({
            url: "/winner-articles/" + id,
            type: "GET",
            success: function(res) {
                if (res.success) {
                    let article = res.data;
                    $('#articleId').val(article.id);
                    $('#articleType').val(article.type);
                    toggleWinnerDropdowns(article.type);

                    if (article.type === 'boss_beginning') {
                        $('#contestantId').val(article.contestant_id || defaultBossWinnerId);
                    } else {
                        $('#spotlightNomineeId').val(article.spotlight_week_nominee_id || defaultSpotlightWinnerId);
                    }

                    $('#articleTitle').val(article.title);
                    
                    // Set textarea content for editing
                    const contentText = article.content || '';
                    $('#articleContent').val(contentText);
                    $('#charCount').text(contentText.length + ' characters');

                    $('#articleIsActive').prop('checked', article.is_active);
                    $('#articleModalLabel').text('Edit Winner Article');

                    // Populate existing media
                    let mediaHtml = '';
                    if (article.media && article.media.length > 0) {
                        $('#existingMediaCountBadge').text(article.media.length + ' files');
                        article.media.forEach(function(m) {
                            let preview = m.file_type === 'video' 
                                ? `<video src="${m.url}" style="height:85px; width:120px; object-fit:cover;" class="rounded border" controls></video>`
                                : `<img src="${m.url}" style="height:85px; width:120px; object-fit:cover;" class="rounded border"/>`;
                            
                            mediaHtml += `
                                <div class="col-auto position-relative text-center border p-2 rounded bg-white media-preview-card" id="media-item-${m.id}">
                                    ${preview}
                                    <div class="mt-1 small text-truncate fw-medium" style="max-width:120px;" title="${m.file_name || 'File'}">${m.file_name || 'File'}</div>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-danger btn-sm p-1 px-2 delete-media-btn" data-article-id="${article.id}" data-media-id="${m.id}">
                                            <i class="ri-delete-bin-line"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        $('#existingMediaList').html(mediaHtml);
                        $('#existingMediaContainer').removeClass('d-none');
                    } else {
                        $('#existingMediaContainer').addClass('d-none');
                        $('#existingMediaList').html('');
                    }

                    $('#articleModal').modal('show');
                }
            },
            error: function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not load article data. Please refresh and try again.',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false
                });
            }
        });
    });

    // Delete Media Item with SweetAlert2 Confirmation
    $(document).on('click', '.delete-media-btn', function() {
        let articleId = $(this).data('article-id');
        let mediaId = $(this).data('media-id');

        Swal.fire({
            title: 'Delete Attachment?',
            text: "This media file will be permanently deleted from storage.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/winner-articles/${articleId}/media/${mediaId}`,
                    type: "DELETE",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            $(`#media-item-${mediaId}`).remove();
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: 'Could not delete media file. Please try again.',
                            confirmButtonClass: 'btn btn-primary',
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });

    // Submit Article Form with SweetAlert2 Alerts
    $('#articleForm').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let articleId = $('#articleId').val();
        let url = articleId ? `/winner-articles/${articleId}` : "{{ route('admin.winner-articles.store') }}";

        // Validate content
        if (!$('#articleContent').val().trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Content Required',
                text: 'Please enter article content before saving.',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
            return;
        }

        // Attach all staged files to FormData
        formData.delete('media[]');
        stagedNewFiles.forEach(file => {
            formData.append('media[]', file);
        });

        $('#saveArticleBtn').prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Saving...');

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                $('#saveArticleBtn').prop('disabled', false).html('<i class="ri-save-line me-1"></i> Save Article');
                if (res.success) {
                    $('#articleModal').modal('hide');
                    table.draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(err) {
                $('#saveArticleBtn').prop('disabled', false).html('<i class="ri-save-line me-1"></i> Save Article');
                Swal.fire({
                    icon: 'error',
                    title: 'Save Failed',
                    text: err.responseJSON?.message || 'Error saving article. Please check input fields.',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false
                });
            }
        });
    });

    // Delete Article with SweetAlert2 Confirmation
    $(document).on('click', '.delete-article-btn', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Delete Winner Article?',
            text: "Are you sure you want to delete this article? All media attachments will be permanently removed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete article!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/winner-articles/" + id,
                    type: "DELETE",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            table.draw();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: 'Could not delete article. Please try again.',
                            confirmButtonClass: 'btn btn-primary',
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
