@extends('layout.master-layout')

@section('title', 'Businesses')
@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Businesses</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Businesses</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-store-2-line fs-24 text-primary"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0">{{ number_format($stats['total']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Active</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-checkbox-circle-line fs-24 text-success"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0 text-success">{{ number_format($stats['active']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Inactive</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-pause-circle-line fs-24 text-secondary"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0 text-secondary">{{ number_format($stats['inactive']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Terminated</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-close-circle-line fs-24 text-danger"></i>
                                </div>
                            </div>
                            <h4 class="mt-3 mb-0 text-danger">{{ number_format($stats['terminated']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">All Businesses</h5>
                        </div>

                        {{-- Filters --}}
                        <div class="card-body border-bottom pb-3">
                            <div class="row g-3">
                                <div class="col-xl-4 col-md-6">
                                    <div class="search-box">
                                        <input type="text" id="dtSearch" class="form-control search"
                                            placeholder="Search business, owner, story, mission...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-4">
                                    <select id="filterStatus" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <button type="button" id="resetFilters" class="btn btn-soft-danger w-100">
                                        <i class="ri-refresh-line me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- DataTable --}}
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="businessesTable" class="table table-bordered table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>Business</th>
                                            <th>Owner</th>
                                            <th>Story</th>
                                            <th>Mission</th>
                                            <th>Website</th>
                                            <th>Revenue Stage</th>
                                            <th>Media</th>
                                            <th class="text-center">Status</th>
                                            <th>Created</th>
                                            <th class="text-center" style="width:130px;">Action</th>
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

    {{-- View Details Modal --}}
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Business Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="modalToggleStatusBtn" style="display:none;">
                        <i class="ri-pause-circle-line me-1"></i> Toggle Status
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- GLightbox for full-size media preview in the details modal --}}
    <link href="{{ asset('admin/assets/libs/glightbox/css/glightbox.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .view-modal-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .view-modal-media-item {
            position: relative;
            display: block;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, .08);
            background: #f5f6f8;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .view-modal-media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, .14);
        }
        .view-modal-media-item img,
        .view-modal-media-item video {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }
        .view-modal-play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .28);
            transition: background .15s ease;
            pointer-events: none;
        }
        .view-modal-play-overlay i {
            font-size: 36px;
            color: rgba(255, 255, 255, .95);
            text-shadow: 0 2px 8px rgba(0, 0, 0, .45);
        }
        .view-modal-media-item:hover .view-modal-play-overlay {
            background: rgba(0, 0, 0, .15);
        }
        .view-modal-media-item .media-caption {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, .72), transparent);
            color: #fff;
            font-size: 11px;
            padding: 20px 8px 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('admin/assets/libs/glightbox/js/glightbox.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        (function () {
            'use strict';

            let currentBusinessId = null;

            // ──────────────────────────────────────────────
            //  HTML HELPERS (used by the details modal)
            // ──────────────────────────────────────────────

            function esc(str) {
                return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function isVideoUrl(url) {
                return /\.(mp4|webm|ogv|mov|m4v)(\?.*)?$/i.test(url || '');
            }

            function isVideoMime(mime) {
                return String(mime || '').toLowerCase().indexOf('video/') === 0;
            }

            function formatBytes(bytes) {
                if (bytes === null || bytes === undefined || isNaN(bytes)) return '';
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            }

            function detailRow(label, value) {
                const v = (value === null || value === undefined || value === '') ? '—' : value;
                return '<tr><th class="text-muted fw-normal" style="width:38%;vertical-align:top;">' + label + '</th><td>' + v + '</td></tr>';
            }

            function sectionTitle(icon, title) {
                return '<div class="d-flex align-items-center gap-2 mb-2">' +
                    '<i class="' + icon + ' text-primary fs-16"></i>' +
                    '<h6 class="mb-0 fw-semibold text-uppercase text-muted" style="font-size:.78rem;letter-spacing:.5px;">' + title + '</h6></div>';
            }

            @if (session('success'))
                Toast.success(@json(session('success')));
            @endif

            @if (session('error'))
                Toast.error(@json(session('error')));
            @endif

            @if (session('warning'))
                Toast.warning(@json(session('warning')));
            @endif

            @if (session('info'))
                Toast.info(@json(session('info')));
            @endif

                // DataTable Initialisation
                const table = $('#businessesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [
                    [0, 'desc']
                ],
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    processing: '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>',
                    emptyTable: '<div class="text-center py-5"><i class="ri-store-2-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No businesses found</p></div>',
                    zeroRecords: '<div class="text-center py-5"><i class="ri-search-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No matching records</p></div>',
                },
                ajax: {
                    url: '{{ route('admin.businesses.index') }}',
                    type: 'GET',
                    data: function (d) {
                        d.status = $('#filterStatus').val();
                        d.search_term = $('#dtSearch').val();
                    },
                },
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'business',
                    name: 'business_name',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'owner',
                    name: 'owner_founder_name',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'story',
                    name: 'story',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'mission',
                    name: 'mission',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'website',
                    name: 'website_social_media',
                    orderable: false,
                    searchable: true,
                    className: 'text-center'
                },
                {
                    data: 'revenue_stage',
                    name: 'revenue_stage',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'media',
                    name: 'photo_video',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                ],
            });

            // Search with debounce
            let searchTimer;
            document.getElementById('dtSearch').addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => table.draw(), 400);
            });

            // Status filter
            document.getElementById('filterStatus').addEventListener('change', function () {
                table.draw();
            });

            // Reset
            document.getElementById('resetFilters').addEventListener('click', function () {
                document.getElementById('dtSearch').value = '';
                document.getElementById('filterStatus').value = '';
                table.draw();
            });

            // View Details
            $(document).on('click', '.view-btn', function () {
                currentBusinessId = $(this).data('id');
                loadBusinessDetails(currentBusinessId);
                $('#viewModal').modal('show');
            });

            // Destroy the lightbox when the modal closes so it does not leak listeners
            $('#viewModal').on('hidden.bs.modal', function () {
                if (window.__businessLightbox) {
                    try { window.__businessLightbox.destroy(); } catch (e) {}
                    window.__businessLightbox = null;
                }
            });

            function loadBusinessDetails(id) {
                $('#viewModalBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

                axios.get('{{ url('businesses') }}/' + id)
                    .then(function (res) {
                        const d = res.data.data;
                        let html = buildDetailsHtml(d);
                        $('#viewModalBody').html(html);

                        // Re-init GLightbox for the freshly injected media links
                        if (window.GLightbox) {
                            try {
                                if (window.__businessLightbox) {
                                    window.__businessLightbox.destroy();
                                }
                            } catch (e) {}
                            window.__businessLightbox = GLightbox({
                                selector: '.view-modal-glightbox',
                                touchNavigation: true,
                                loop: true,
                                closeButton: true,
                            });
                        }

                        // Toggle status button visibility
                        const toggleBtn = document.getElementById('modalToggleStatusBtn');
                        if (d.status === 'terminated') {
                            toggleBtn.style.display = 'none';
                        } else {
                            toggleBtn.style.display = 'inline-block';
                            const isActive = d.status === 'active';
                            toggleBtn.innerHTML = isActive ?
                                '<i class="ri-pause-circle-line me-1"></i> Deactivate' :
                                '<i class="ri-play-circle-line me-1"></i> Activate';
                        }
                    })
                    .catch(function () {
                        $('#viewModalBody').html(
                            '<div class="alert alert-danger">Failed to load business details.</div>');
                    });
            }

            function buildDetailsHtml(d) {
                const statusBadge = {
                    'active': '<span class="badge bg-success-subtle text-success">Active</span>',
                    'inactive': '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>',
                    'terminated': '<span class="badge bg-danger-subtle text-danger">Terminated</span>',
                }[d.status] || '<span class="badge bg-secondary-subtle text-secondary">' + esc(d.status) + '</span>';

                const featuredBadge = d.is_featured
                    ? '<span class="badge bg-info-subtle text-info"><i class="ri-star-fill me-1"></i>Featured</span>'
                    : '';

                const logoHtml = d.business_logo
                    ? '<a href="' + esc(d.business_logo) + '" target="_blank" title="Open logo"><img src="' + esc(d.business_logo) + '" class="rounded border" style="width:72px;height:72px;object-fit:cover;" alt="Logo"></a>'
                    : '<div class="d-flex align-items-center justify-content-center rounded border bg-light" style="width:72px;height:72px;"><i class="ri-store-2-line fs-24 text-muted"></i></div>';

                const statsHtml = [
                    { icon: 'ri-hand-clap-line', label: 'Claps', value: d.total_claps, color: 'primary' },
                    { icon: 'ri-heart-line', label: 'Saves', value: d.total_saves, color: 'success' },
                    { icon: 'ri-share-forward-line', label: 'Shares', value: d.total_shares, color: 'info' },
                    { icon: 'ri-trophy-line', label: 'Points', value: d.total_points, color: 'warning' },
                ].map(function (s) {
                    return '<div class="col-6 col-md-3">' +
                        '<div class="card mb-0 shadow-none border text-center py-2">' +
                        '<div class="card-body py-1">' +
                        '<i class="' + s.icon + ' text-' + s.color + ' fs-18"></i>' +
                        '<h5 class="mb-0 mt-1">' + Number(s.value || 0).toLocaleString() + '</h5>' +
                        '<span class="text-muted small">' + s.label + '</span>' +
                        '</div></div></div>';
                }).join('');

                // Single photo/video field
                let photoVideoHtml = '';
                if (d.photo_video) {
                    photoVideoHtml = '<div class="row mt-3"><div class="col-12">' + sectionTitle('ri-video-line', 'Photo / Video') +
                        (isVideoUrl(d.photo_video)
                            ? '<video src="' + esc(d.photo_video) + '" controls class="w-100 rounded" style="max-height:340px;background:#000;"></video>'
                            : '<a href="' + esc(d.photo_video) + '" class="view-modal-glightbox" title="Photo / Video"><img src="' + esc(d.photo_video) + '" class="img-fluid rounded" style="max-height:340px;width:auto;" alt="Photo / Video"></a>') +
                        '</div></div>';
                }

                // Media gallery — every picture & video
                let galleryHtml = '';
                const media = Array.isArray(d.media) ? d.media : [];
                if (media.length) {
                    galleryHtml = '<div class="row mt-3"><div class="col-12">' + sectionTitle('ri-image-line', 'Media Gallery (' + media.length + ')') +
                        '<div class="view-modal-media-grid">';
                    media.forEach(function (m) {
                        if (!m.url) return;
                        const caption = esc(m.file_name || 'Media #' + m.id);
                        const sizeLabel = formatBytes(m.file_size);
                        const captionLine = sizeLabel ? caption + ' · ' + sizeLabel : caption;
                        if (isVideoMime(m.mime_type) || isVideoUrl(m.url)) {
                            galleryHtml += '<div class="view-modal-media-item" title="' + caption.replace(/"/g, '&quot;') + '">' +
                                '<video src="' + esc(m.url) + '" controls preload="metadata" muted></video>' +
                                '<div class="view-modal-play-overlay"><i class="ri-play-circle-fill"></i></div>' +
                                '<div class="media-caption"><i class="ri-video-line me-1"></i>' + captionLine + '</div></div>';
                        } else {
                            galleryHtml += '<a href="' + esc(m.url) + '" class="view-modal-media-item view-modal-glightbox" title="' + caption.replace(/"/g, '&quot;') + '">' +
                                '<img src="' + esc(m.url) + '" loading="lazy" alt="' + caption + '">' +
                                '<div class="media-caption">' + captionLine + '</div></a>';
                        }
                    });
                    galleryHtml += '</div>' +
                        '<div class="text-muted small mt-2"><i class="ri-zoom-in-line me-1"></i>Click an image to view full size</div>' +
                        '</div></div>';
                }

                // Owner social links
                let socialHtml = '';
                if (d.user_social_links && typeof d.user_social_links === 'object') {
                    const links = Object.keys(d.user_social_links).filter(function (k) { return d.user_social_links[k]; });
                    if (links.length) {
                        socialHtml = '<div class="mt-2">' + links.map(function (k) {
                            return '<a href="' + esc(d.user_social_links[k]) + '" target="_blank" class="badge bg-light text-dark text-decoration-none me-1 mb-1 border"><i class="ri-external-link-line me-1"></i>' + esc(k) + '</a>';
                        }).join('') + '</div>';
                    }
                }

                const ownerAvatarHtml = d.user_avatar
                    ? '<img src="' + esc(d.user_avatar) + '" class="rounded-circle border mt-2" style="width:90px;height:90px;object-fit:cover;" alt="Owner avatar">'
                    : '<div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border mt-2" style="width:90px;height:90px;"><i class="ri-user-line fs-24 text-muted"></i></div>';

                return `
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="flex-shrink-0">${logoHtml}</div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">${esc(d.business_name)} ${featuredBadge}</h5>
                            <p class="text-muted mb-1"><i class="ri-user-line me-1"></i>${esc(d.owner_founder_name)}</p>
                            <div class="d-flex gap-1 flex-wrap">${statusBadge}</div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">${statsHtml}</div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-md-6">
                            ${sectionTitle('ri-information-line', 'Basic Information')}
                            <table class="table table-sm table-borderless mb-0">
                                ${detailRow('Business Name', esc(d.business_name))}
                                ${detailRow('Owner Name', esc(d.owner_name))}
                                ${detailRow('Owner / Founder', esc(d.owner_founder_name))}
                                ${detailRow('Slug', d.slug ? '<code>' + esc(d.slug) + '</code>' : null)}
                                ${detailRow('Category', esc(d.category_name))}
                                ${detailRow('Status', statusBadge)}
                                ${detailRow('Featured', d.is_featured ? '<span class="badge bg-info-subtle text-info"><i class="ri-star-fill me-1"></i>Yes</span>' : 'No')}
                                ${detailRow('Revenue Stage', esc(d.revenue_stage))}
                                ${detailRow('Website / Social Media', d.website_social_media ? '<a href="' + esc(d.website_social_media) + '" target="_blank">' + esc(d.website_social_media) + ' <i class="ri-external-link-line"></i></a>' : null)}
                            </table>
                        </div>
                        <div class="col-md-6">
                            ${sectionTitle('ri-user-line', 'Account')}
                            <table class="table table-sm table-borderless mb-0">
                                ${detailRow('User Name', esc(d.user_name))}
                                ${detailRow('Email', d.user_email && d.user_email !== '—' ? '<a href="mailto:' + esc(d.user_email) + '">' + esc(d.user_email) + '</a>' : null)}
                                ${detailRow('Username', d.user_username ? '<code>' + esc(d.user_username) + '</code>' : null)}
                                ${detailRow('Address', esc(d.user_address))}
                                ${detailRow('Website', d.user_website ? '<a href="' + esc(d.user_website) + '" target="_blank">' + esc(d.user_website) + '</a>' : null)}
                                ${detailRow('Created', esc(d.created_at))}
                                ${detailRow('Updated', esc(d.updated_at))}
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            ${sectionTitle('ri-profile-line', 'Owner Profile')}
                            <table class="table table-sm table-borderless mb-0">
                                ${detailRow('Biography', esc(d.user_biography))}
                            </table>
                            ${socialHtml}
                        </div>
                        <div class="col-md-6 text-center">${ownerAvatarHtml}</div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            ${sectionTitle('ri-file-text-line', 'Story')}
                            <p class="mb-3">${d.story ? esc(d.story) : '—'}</p>

                            ${sectionTitle('ri-flag-line', 'Mission')}
                            <p class="mb-3">${d.mission ? esc(d.mission) : '—'}</p>

                            ${sectionTitle('ri-community-line', 'Community Impact Statement')}
                            <p class="mb-3">${d.community_impact_statement ? esc(d.community_impact_statement) : '—'}</p>

                            ${sectionTitle('ri-trophy-line', 'Why They Deserve To Compete')}
                            <p class="mb-0">${d.why_they_deserve_to_compete ? esc(d.why_they_deserve_to_compete) : '—'}</p>
                        </div>
                    </div>

                    ${photoVideoHtml}
                    ${galleryHtml}
                `;
            }

            // Toggle Status from Modal
            document.getElementById('modalToggleStatusBtn').addEventListener('click', function () {
                if (!currentBusinessId) return;

                const isActive = this.innerHTML.includes('Deactivate');
                const actionLabel = isActive ? 'deactivate' : 'activate';

                Alert.confirm('Are you sure you want to ' + actionLabel + ' this business?', {
                    title: (isActive ? 'Deactivate' : 'Activate') + ' Business?',
                    type: 'warning',
                    confirmText: 'Yes, ' + actionLabel,
                }).then(function (confirmed) {
                    if (!confirmed) return;

                    axios.patch('{{ url('businesses') }}/' + currentBusinessId +
                        '/toggle-status')
                        .then(function (res) {
                            Toast.success(res.data.message);
                            $('#viewModal').modal('hide');
                            table.draw(false);
                        })
                        .catch(function (err) {
                            Toast.error(err.response?.data?.message || 'Failed to toggle status.');
                        });
                });
            });

            // Toggle Status from Table
            $(document).on('click', '.toggle-status-btn', function () {
                const id = $(this).data('id');
                const currentStatus = $(this).data('status');
                const isActive = currentStatus === 'active';
                const actionLabel = isActive ? 'deactivate' : 'activate';

                Alert.confirm('Are you sure you want to ' + actionLabel + ' this business?', {
                    title: (isActive ? 'Deactivate' : 'Activate') + ' Business?',
                    type: 'warning',
                    confirmText: 'Yes, ' + actionLabel,
                }).then(function (confirmed) {
                    if (!confirmed) return;

                    axios.patch('{{ url('businesses') }}/' + id + '/toggle-status')
                        .then(function (res) {
                            Toast.success(res.data.message);
                            table.draw(false);
                        })
                        .catch(function (err) {
                            Toast.error(err.response?.data?.message || 'Failed to toggle status.');
                        });
                });
            });

            // Delete
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');

                Alert.confirm('This business will be permanently deleted and cannot be restored.', {
                    title: 'Delete Business?',
                    type: 'danger',
                    confirmText: 'Yes, delete it',
                }).then(function (confirmed) {
                    if (!confirmed) return;

                    axios.delete('{{ url('businesses') }}/' + id)
                        .then(function (res) {
                            Toast.success(res.data.message);
                            table.draw(false);
                        })
                        .catch(function (err) {
                            Toast.error(err.response?.data?.message ||
                                'Failed to delete business.');
                        });
                });
            });

        })();
    </script>
@endpush