@extends('layout.master-layout')
@section('title', 'Artist Spotlights')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Artist Spotlights</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Artist Spotlights</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        {{-- Stats Cards --}}
        <div class="row">
            <x-admin.stats-card icon="ri-user-star-line" label="Total Artists" :count="$stats['total']" color="primary" />
            <x-admin.stats-card icon="ri-time-line" label="Pending Review" :count="$stats['pending_review']" color="warning" />
            <x-admin.stats-card icon="ri-checkbox-circle-line" label="Approved" :count="$stats['by_status']['approved']" color="success" />
            <x-admin.stats-card icon="ri-close-circle-line" label="Rejected" :count="$stats['by_status']['rejected']" color="danger" />
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    {{-- Card Header --}}
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Artist Submissions</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-soft-success btn-sm" id="bulkApproveBtn" disabled>
                                <i class="ri-checkbox-circle-line me-1"></i> Bulk Approve
                            </button>
                        </div>
                    </div>

                    {{-- Custom Filters --}}
                    <div class="card-body border-bottom pb-3">
                        <div class="row g-3">
                            <div class="col-xl-4 col-md-6">
                                <div class="search-box">
                                    <input type="text" id="dtSearch" class="form-control search"
                                           placeholder="Search artist, stage name, email...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <select id="filterStatus" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <select id="filterCategory" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach(App\Models\ArtistCategory::all() as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
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
                            <table id="artistsTable" class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th style="width:50px;">#</th>
                                        <th>Artist</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th class="text-center">Status</th>
                                        <th>Submitted At</th>
                                        <th class="text-center" style="width:150px;">Actions</th>
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
                <h5 class="modal-title">Artist Spotlight Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="modalUnderReviewBtn">Mark Under Review</button>
                <button type="button" class="btn btn-success" id="modalApproveBtn">Approve</button>
                <button type="button" class="btn btn-danger" id="modalRejectBtn">Reject</button>
            </div>
        </div>
    </div>
</div>

{{-- Reject Reason Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectReason" rows="4" placeholder="Please provide a reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    let currentArtistId = null;
    let selectedIds = [];

    // ── DataTable Initialisation ──────────────────────────────────────────
    const table = $('#artistsTable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        order      : [[6, 'desc']],
        lengthMenu : [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom        : "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-end'i>>t<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",

        language: {
            processing  : '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>',
            emptyTable  : '<div class="text-center py-5"><i class="ri-user-star-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No submissions found</p></div>',
            zeroRecords : '<div class="text-center py-5"><i class="ri-search-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No matching records</p></div>',
        },

        ajax: {
            url  : '{{ route('admin.artist-spotlights.data') }}',
            type : 'GET',
            data : function (d) {
                d.status             = $('#filterStatus').val();
                d.artist_category_id = $('#filterCategory').val();
                d.search_term        = $('#dtSearch').val();
            },
        },

        columns: [
            { 
                data: 'id', 
                orderable: false, 
                searchable: false,
                render: function(data) {
                    return '<input type="checkbox" class="form-check-input row-checkbox" value="' + data + '">';
                }
            },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'artist',      name: 'artist_stage_name', orderable: true,  searchable: true  },
            { data: 'category',    name: 'artist_category_id', orderable: true,  searchable: false },
            { data: 'location',    name: 'city',              orderable: true,  searchable: true  },
            { data: 'status',      name: 'status',            orderable: true,  searchable: false, className: 'text-center' },
            { data: 'submitted_at', name: 'submitted_at',     orderable: true,  searchable: false },
            { data: 'action',      name: 'action',            orderable: false, searchable: false, className: 'text-center' },
        ],

        drawCallback: function () {
            updateBulkButtons();
        },
    });

    // ── Search & Filter ──────────────────────────────────────────────────
    let searchTimer;
    document.getElementById('dtSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => table.draw(), 400);
    });

    ['filterStatus', 'filterCategory'].forEach(id => {
        document.getElementById(id).addEventListener('change', () => table.draw());
    });

    document.getElementById('resetFilters').addEventListener('click', () => {
        document.getElementById('dtSearch').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterCategory').value = '';
        table.draw();
    });

    // ── Selection ────────────────────────────────────────────────────────
    document.getElementById('selectAll').addEventListener('change', function () {
        const checked = this.checked;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
        updateBulkButtons();
    });

    $(document).on('change', '.row-checkbox', () => updateBulkButtons());

    function updateBulkButtons() {
        selectedIds = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => selectedIds.push(cb.value));
        document.getElementById('bulkApproveBtn').disabled = selectedIds.length === 0;
    }

    // ── View Details ──────────────────────────────────────────────────────
    $(document).on('click', '.view-btn', function () {
        currentArtistId = $(this).data('id');
        loadArtistDetails(currentArtistId);
        $('#viewModal').modal('show');
    });

    function loadArtistDetails(id) {
        $('#viewModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        axios.get('{{ url('admin/artist-spotlights') }}/' + id)
            .then(res => {
                const d = res.data.data;
                $('#viewModalBody').html(buildDetailsHtml(d));
                updateModalButtons(d.status);
            })
            .catch(() => {
                $('#viewModalBody').html('<div class="alert alert-danger">Failed to load details.</div>');
            });
    }

    function buildDetailsHtml(d) {
        return `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Artist Information</h6>
                    <table class="table table-sm">
                        <tr><th width="40%">Stage Name</th><td>${d.artist_stage_name}</td></tr>
                        <tr><th>Legal Name</th><td>${d.full_legal_name}</td></tr>
                        <tr><th>Email</th><td>${d.email}</td></tr>
                        <tr><th>Phone</th><td>${d.phone_number}</td></tr>
                        <tr><th>DOB</th><td>${d.date_of_birth}</td></tr>
                        <tr><th>Location</th><td>${d.city}, ${d.state}</td></tr>
                        <tr><th>Category</th><td>${d.category ? d.category.name : (d.category_other_description || 'Other')}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary text-end">Social Media</h6>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        ${d.social_media.instagram_handle ? `<a href="https://instagram.com/${d.social_media.instagram_handle}" target="_blank" class="btn btn-sm btn-soft-danger"><i class="ri-instagram-line"></i></a>` : ''}
                        ${d.social_media.tiktok_handle ? `<a href="https://tiktok.com/@${d.social_media.tiktok_handle}" target="_blank" class="btn btn-sm btn-soft-dark"><i class="ri-tiktok-fill"></i></a>` : ''}
                        ${d.social_media.facebook_url ? `<a href="${d.social_media.facebook_url}" target="_blank" class="btn btn-sm btn-soft-primary"><i class="ri-facebook-fill"></i></a>` : ''}
                        ${d.social_media.youtube_url ? `<a href="${d.social_media.youtube_url}" target="_blank" class="btn btn-sm btn-soft-danger"><i class="ri-youtube-fill"></i></a>` : ''}
                        ${d.social_media.website_portfolio_url ? `<a href="${d.social_media.website_portfolio_url}" target="_blank" class="btn btn-sm btn-soft-info"><i class="ri-global-line"></i></a>` : ''}
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6 class="text-primary">Bio & Story</h6>
                    <p><strong>Short Bio:</strong> ${d.short_bio || 'N/A'}</p>
                    <p><strong>Full Story:</strong> ${d.full_artist_story || 'N/A'}</p>
                    <p><strong>Why Spotlighted:</strong> ${d.why_spotlighted || 'N/A'}</p>
                    <p><strong>Community Message:</strong> ${d.community_message || 'N/A'}</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Media</h6>
                    <div class="d-flex flex-wrap gap-2">
                        ${d.media.headshot ? `<div class="text-center"><img src="${d.media.headshot}" class="img-thumbnail" style="height:100px;"><br><small>Headshot</small></div>` : ''}
                        ${d.media.behind_scenes_photo ? `<div class="text-center"><img src="${d.media.behind_scenes_photo}" class="img-thumbnail" style="height:100px;"><br><small>BTS</small></div>` : ''}
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Artwork Gallery: ${d.media.artwork_photos.length} photos</small>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            ${d.media.artwork_photos.map(p => `<img src="${p}" class="img-thumbnail" style="height:60px;">`).join('')}
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                  ${d.media.intro_video ? `<h6 class="text-primary">Intro Video</h6><a href="${d.media.intro_video}" target="_blank" class="btn btn-soft-primary"><i class="ri-video-line me-1"></i> View Video</a>` : ''}
                </div>
            </div>
            ${d.reviewer_notes ? `<hr><div class="alert alert-warning"><strong>Reviewer Note:</strong> ${d.reviewer_notes}</div>` : ''}
        `;
    }

    function updateModalButtons(status) {
        $('#modalUnderReviewBtn').toggle(status === 'submitted');
        $('#modalApproveBtn').toggle(status !== 'approved' && status !== 'draft');
        $('#modalRejectBtn').toggle(status !== 'rejected' && status !== 'draft');
    }

    // ── Actions ──────────────────────────────────────────────────────────
    $('#modalUnderReviewBtn').on('click', () => updateStatus(currentArtistId, 'under-review'));
    $('#modalApproveBtn').on('click', () => updateStatus(currentArtistId, 'approve'));
    $('#modalRejectBtn').on('click', () => { $('#viewModal').modal('hide'); $('#rejectModal').modal('show'); });

    $('#confirmRejectBtn').on('click', () => {
        const reason = $('#rejectReason').val().trim();
        if (!reason) return Toast.error('Reason is required.');
        axios.post(`{{ url('admin/artist-spotlights') }}/${currentArtistId}/reject`, { reviewer_notes: reason })
            .then(res => {
                Toast.success(res.data.message);
                $('#rejectModal').modal('hide');
                table.draw(false);
            })
            .catch(err => Toast.error(err.response?.data?.message || 'Failed to reject.'));
    });

    function updateStatus(id, action) {
        Alert.confirm(`Are you sure to ${action.replace('-', ' ')} this?`).then(confirmed => {
            if (!confirmed) return;
            axios.post(`{{ url('admin/artist-spotlights') }}/${id}/${action}`)
                .then(res => {
                    Toast.success(res.data.message);
                    $('#viewModal').modal('hide');
                    table.draw(false);
                })
                .catch(err => Toast.error(err.response?.data?.message || 'Update failed.'));
        });
    }

    $(document).on('click', '.approve-btn', function () { updateStatus($(this).data('id'), 'approve'); });
    $(document).on('click', '.reject-btn', function () { currentArtistId = $(this).data('id'); $('#rejectModal').modal('show'); });
    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        Alert.confirm('Move to trash?', { type: 'danger' }).then(confirmed => {
            if (!confirmed) return;
            axios.delete(`{{ url('admin/artist-spotlights') }}/${id}`)
                .then(res => { Toast.success(res.data.message); table.draw(false); })
                .catch(err => Toast.error(err.response?.data?.message || 'Delete failed.'));
        });
    });

    $('#bulkApproveBtn').on('click', () => {
        Alert.confirm(`Approve ${selectedIds.length} artist(s)?`).then(confirmed => {
            if (!confirmed) return;
            axios.post('{{ route('admin.artist-spotlights.bulk-status') }}', { ids: selectedIds, status: 'approved' })
                .then(res => { Toast.success(res.data.message); table.draw(false); })
                .catch(() => Toast.error('Bulk approval failed.'));
        });
    });
})();
</script>
@endpush
