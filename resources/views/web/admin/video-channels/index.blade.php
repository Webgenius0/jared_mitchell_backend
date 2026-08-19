@extends('layout.master-layout')
@section('title', 'Video Channel Management')

@push('styles')
    <style>
        .video-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            transition: all 0.25s ease-in-out;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .video-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .drag-handle {
            cursor: grab;
            user-select: none;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .sortable-ghost {
            opacity: 0.4;
            background: #eef2f7 !important;
            border: 2px dashed #405189 !important;
        }

        .video-preview-wrapper {
            position: relative;
            width: 100%;
            background: #000;
            border-radius: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 16 / 9;
        }

        .video-preview-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .order-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Video Channel Management</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Video Channel</li>
                        </ol>
                    </div>
                </div>
            </div>

            {{-- Main Content Card --}}
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h5 class="card-title mb-1">Manage Category Videos</h5>
                                <p class="text-muted mb-0 fs-13">
                                    Upload multiple videos and drag-and-drop to reorder them within each category. The display order configured here is preserved in API responses.
                                </p>
                            </div>
                            <button type="button" class="btn btn-primary btn-md gap-2 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#uploadVideoModal">
                                <i class="ri-upload-cloud-2-line fs-18"></i> Upload Videos
                            </button>
                        </div>

                        <div class="card-body p-4">
                            {{-- Category Nav Tabs --}}
                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-4" role="tablist">
                                @foreach ($categories as $key => $label)
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-{{ $key }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                            <span class="d-flex align-items-center gap-2">
                                                @if ($key === 'boss_beginning')
                                                    <i class="ri-movie-2-line fs-16"></i>
                                                @elseif($key === 'business_spotlight')
                                                    <i class="ri-briefcase-4-line fs-16"></i>
                                                @elseif($key === 'artist_spotlight')
                                                    <i class="ri-palette-line fs-16"></i>
                                                @else
                                                    <i class="ri-calendar-event-line fs-16"></i>
                                                @endif
                                                <span>{{ $label }}</span>
                                                <span class="badge bg-soft-primary text-primary rounded-pill fs-11 video-count-badge" id="badge-count-{{ $key }}">
                                                    {{ count($videosByCategory[$key] ?? []) }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Tab Contents --}}
                            <div class="tab-content text-muted">
                                @foreach ($categories as $key => $label)
                                    @php
                                        $videos = $videosByCategory[$key] ?? collect();
                                    @endphp
                                    <div class="tab-pane {{ $loop->first ? 'active' : '' }}" id="tab-{{ $key }}" role="tabpanel">
                                        <div class="d-flex align-items-center justify-content-between mb-3 bg-light p-3 rounded">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ri-drag-move-fill text-muted fs-18"></i>
                                                <span class="fw-medium text-dark">Drag and drop video cards to change display order</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openUploadModalFor('{{ $key }}')">
                                                + Add Videos to {{ $label }}
                                            </button>
                                        </div>

                                        {{-- Sortable Grid Container --}}
                                        <div class="row sortable-video-grid g-4" data-category="{{ $key }}" id="video-grid-{{ $key }}">
                                            @forelse ($videos as $video)
                                                <div class="col-xl-3 col-lg-4 col-md-6 video-item-col" data-id="{{ $video->id }}">
                                                    <div class="card video-card h-100 mb-0">
                                                        {{-- Drag Handle --}}
                                                        <div class="drag-handle">
                                                            <span class="badge bg-primary-subtle text-primary order-badge">
                                                                <i class="ri-draggable me-1"></i> Order #<span class="order-number">{{ $loop->iteration }}</span>
                                                            </span>
                                                            <button type="button" class="btn btn-sm btn-soft-danger py-0 px-2 js-delete-video" data-id="{{ $video->id }}" title="Delete Video">
                                                                <i class="ri-delete-bin-line fs-14"></i>
                                                            </button>
                                                        </div>

                                                        {{-- Video Player --}}
                                                        <div class="video-preview-wrapper">
                                                            <video controls preload="metadata">
                                                                <source src="{{ asset($video->video_path) }}" type="video/mp4">
                                                                Your browser does not support HTML5 video.
                                                            </video>
                                                        </div>

                                                        {{-- Card Footer --}}
                                                        <div class="card-body p-2 text-center bg-light-subtle">
                                                            <small class="text-muted text-truncate d-block" title="{{ basename($video->video_path) }}">
                                                                <i class="ri-file-video-line me-1"></i> {{ basename($video->video_path) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12 empty-state-col">
                                                    <div class="text-center py-5 border border-dashed rounded-3">
                                                        <div class="avatar-md mx-auto mb-3">
                                                            <div class="avatar-title bg-primary-subtle text-primary fs-24 rounded-circle">
                                                                <i class="ri-video-add-line"></i>
                                                            </div>
                                                        </div>
                                                        <h5 class="mb-1">No Videos in {{ $label }}</h5>
                                                        <p class="text-muted mb-3">Upload multiple videos to populate this category.</p>
                                                        <button type="button" class="btn btn-sm btn-primary" onclick="openUploadModalFor('{{ $key }}')">
                                                            Upload Videos
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Video Modal --}}
    <div class="modal fade" id="uploadVideoModal" tabindex="-1" aria-labelledby="uploadVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="uploadVideoModalLabel">
                        <i class="ri-video-upload-line me-2 text-primary"></i> Upload Multiple Videos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadVideoForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="modalCategorySelect" class="form-label fw-semibold">Select Category <span class="text-danger">*</span></label>
                            <select name="category" id="modalCategorySelect" class="form-select form-select-lg" required>
                                <option value="">-- Choose Category --</option>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="modalVideoFiles" class="form-label fw-semibold">Upload Video Files <span class="text-danger">*</span></label>
                            <input type="file" name="videos[]" id="modalVideoFiles" class="form-control form-control-lg" multiple accept="video/*" required>
                            <div class="form-text mt-2">
                                <i class="ri-information-line me-1"></i> You can select multiple video files at once. Supported formats: MP4, MOV, AVI, WMV, WEBM, M4V, 3GP. Max size per video: 500MB.
                            </div>
                        </div>

                        {{-- Upload Progress --}}
                        <div class="d-none mt-3" id="uploadProgressWrapper">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fs-13 text-muted" id="uploadStatusText">Uploading videos...</span>
                                <span class="fs-13 fw-semibold text-primary" id="uploadPercentText">0%</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 d-flex align-items-center gap-2" id="submitUploadBtn">
                            <i class="ri-upload-2-line fs-16"></i> Start Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/assets/libs/sortablejs/Sortable.min.js') }}"></script>
    <script>
        $(function() {
            const storeRoute = @json(route('admin.video-channels.store'));
            const reorderRoute = @json(route('admin.video-channels.reorder'));
            const destroyRouteTemplate = @json(route('admin.video-channels.destroy', ['videoChannel' => '__ID__']));
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Function to open upload modal pre-selecting a specific category
            window.openUploadModalFor = function(categoryKey) {
                $('#modalCategorySelect').val(categoryKey);
                $('#uploadVideoModal').modal('show');
            };

            // Initialize SortableJS on each category grid
            $('.sortable-video-grid').each(function() {
                const gridElem = this;
                const categoryKey = $(gridElem).data('category');

                new Sortable(gridElem, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    draggable: '.video-item-col',
                    onEnd: function() {
                        updateGridOrderNumbers(gridElem);
                        saveCategoryOrder(categoryKey, gridElem);
                    }
                });
            });

            // Update badge order numbers locally
            function updateGridOrderNumbers(gridElem) {
                $(gridElem).find('.video-item-col').each(function(index) {
                    $(this).find('.order-number').text(index + 1);
                });
            }

            // AJAX call to save updated reorder sequence
            function saveCategoryOrder(categoryKey, gridElem) {
                const orderIds = [];
                $(gridElem).find('.video-item-col').each(function() {
                    const id = $(this).data('id');
                    if (id) orderIds.push(id);
                });

                if (orderIds.length === 0) return;

                $.ajax({
                    url: reorderRoute,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        category: categoryKey,
                        order_ids: orderIds
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function(res) {
                    Toast.success(res?.message || 'Video order updated.');
                }).fail(function(xhr) {
                    Toast.fromResponse(xhr.responseJSON || {});
                });
            }

            // Handle Multi Video Upload Form Submission
            $('#uploadVideoForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = $('#submitUploadBtn');
                const progressWrapper = $('#uploadProgressWrapper');
                const progressBar = $('#uploadProgressBar');
                const percentText = $('#uploadPercentText');
                const statusText = $('#uploadStatusText');

                submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Uploading...');
                progressWrapper.removeClass('d-none');
                progressBar.css('width', '0%');
                percentText.text('0%');
                statusText.text('Uploading videos, please wait...');

                $.ajax({
                    url: storeRoute,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                progressBar.css('width', percentComplete + '%');
                                percentText.text(percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    }
                }).done(function(res) {
                    Toast.success(res?.message || 'Videos uploaded successfully.');
                    $('#uploadVideoModal').modal('hide');
                    setTimeout(() => window.location.reload(), 800);
                }).fail(function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="ri-upload-2-line fs-16"></i> Start Upload');
                    progressWrapper.addClass('d-none');
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errMsgs = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        Toast.error(errMsgs);
                    } else {
                        Toast.fromResponse(xhr.responseJSON || {});
                    }
                });
            });

            // Handle Video Deletion
            $(document).on('click', '.js-delete-video', function() {
                const videoId = $(this).data('id');
                const cardCol = $(this).closest('.video-item-col');
                const gridElem = cardCol.closest('.sortable-video-grid');
                const categoryKey = gridElem.data('category');

                Alert.confirm('Are you sure you want to delete this video?', {
                    title: 'Delete Video?',
                    type: 'danger',
                    confirmText: 'Yes, delete it',
                }).then(function(confirmed) {
                    if (!confirmed) return;

                    const deleteUrl = destroyRouteTemplate.replace('__ID__', String(videoId));

                    $.ajax({
                        url: deleteUrl,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken,
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).done(function(res) {
                        Toast.success(res?.message || 'Video deleted.');
                        cardCol.fadeOut(300, function() {
                            $(this).remove();
                            updateGridOrderNumbers(gridElem);
                            // Update category badge count
                            const newCount = gridElem.find('.video-item-col').length;
                            $(`#badge-count-${categoryKey}`).text(newCount);
                            if (newCount === 0) {
                                window.location.reload();
                            }
                        });
                    }).fail(function(xhr) {
                        Toast.fromResponse(xhr.responseJSON || {});
                    });
                });
            });

            // Reset modal state on hidden
            $('#uploadVideoModal').on('hidden.bs.modal', function() {
                $('#uploadVideoForm')[0].reset();
                $('#uploadProgressWrapper').addClass('d-none');
                $('#submitUploadBtn').prop('disabled', false).html('<i class="ri-upload-2-line fs-16"></i> Start Upload');
            });
        });
    </script>
@endpush
