@extends('layout.master-layout')
@section('title', 'Business Spotlights')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Business Spotlights</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Business Spotlights</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        {{-- Stats Cards --}}
        <div class="row">
            <x-admin.stats-card icon="ri-file-list-3-line" label="Total Submissions" :count="$stats['total']" color="primary" />
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
                        <h5 class="card-title mb-0 flex-grow-1">All Submissions</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-soft-success btn-sm" id="bulkApproveBtn" disabled>
                                <i class="ri-checkbox-circle-line me-1"></i> Bulk Approve
                            </button>
                            <button type="button" class="btn btn-soft-danger btn-sm" id="bulkRejectBtn" disabled>
                                <i class="ri-close-circle-line me-1"></i> Bulk Reject
                            </button>
                        </div>
                    </div>

                    {{-- Custom Filters --}}
                    <div class="card-body border-bottom pb-3">
                        <div class="row g-3">
                            <div class="col-xl-3 col-md-6">
                                <div class="search-box">
                                    <input type="text" id="dtSearch" class="form-control search"
                                           placeholder="Search business, owner, email...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <select id="filterStatus" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <select id="filterServiceType" class="form-select">
                                    <option value="">All Service Types</option>
                                    <option value="in_person_only">In-Person Only</option>
                                    <option value="online_only">Online Only</option>
                                    <option value="both_in_person_and_online">Both</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <input type="text" id="filterDateRange" class="form-control" placeholder="Date Range">
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
                            <table id="spotlightsTable" class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th style="width:50px;">#</th>
                                        <th>Business</th>
                                        <th>Owner</th>
                                        <th>Location</th>
                                        <th>Service Type</th>
                                        <th class="text-center">Status</th>
                                        <th>Submitted</th>
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
                <h5 class="modal-title">Business Spotlight Details</h5>
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

    let currentSpotlightId = null;
    let selectedIds = [];

    // ── DataTable Initialisation ──────────────────────────────────────────
    const table = $('#spotlightsTable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        order      : [[7, 'desc']],
        lengthMenu : [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom        : "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-end'i>>t<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",

        language: {
            processing  : '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>',
            emptyTable  : '<div class="text-center py-5"><i class="ri-store-2-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No submissions found</p></div>',
            zeroRecords : '<div class="text-center py-5"><i class="ri-search-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No matching records</p></div>',
        },

        ajax: {
            url  : '{{ route('admin.business-spotlights.data') }}',
            type : 'GET',
            data : function (d) {
                d.status       = $('#filterStatus').val();
                d.service_type = $('#filterServiceType').val();
                d.search_term  = $('#dtSearch').val();
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
            { data: 'business',    name: 'business_name', orderable: true,  searchable: true  },
            { data: 'owner',       name: 'owner_founder_name', orderable: true,  searchable: true  },
            { data: 'location',    name: 'city',          orderable: true,  searchable: true  },
            { data: 'service_type', name: 'service_type', orderable: false, searchable: false },
            { data: 'status',      name: 'status',        orderable: true,  searchable: false, className: 'text-center' },
            { data: 'submitted_at', name: 'submitted_at', orderable: true,  searchable: false },
            { data: 'action',      name: 'action',        orderable: false, searchable: false, className: 'text-center' },
        ],

        drawCallback: function () {
            updateBulkButtons();
        },
    });

    // ── Search with debounce ───────────────────────────────────────────
    let searchTimer;
    document.getElementById('dtSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(function () {
            table.draw();
        }, 400);
    });

    // ── Dropdown filters ─────────────────────────────────────────────────
    ['filterStatus', 'filterServiceType'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            table.draw();
        });
    });

    // ── Reset ─────────────────────────────────────────────────────────────
    document.getElementById('resetFilters').addEventListener('click', function () {
        document.getElementById('dtSearch').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterServiceType').value = '';
        table.draw();
    });

    // ── Select All ────────────────────────────────────────────────────────
    document.getElementById('selectAll').addEventListener('change', function () {
        const checked = this.checked;
        document.querySelectorAll('.row-checkbox').forEach(function (cb) {
            cb.checked = checked;
        });
        updateBulkButtons();
    });

    $(document).on('change', '.row-checkbox', function () {
        updateBulkButtons();
    });

    function updateBulkButtons() {
        selectedIds = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(function (cb) {
            selectedIds.push(cb.value);
        });
        const hasSelection = selectedIds.length > 0;
        document.getElementById('bulkApproveBtn').disabled = !hasSelection;
        document.getElementById('bulkRejectBtn').disabled = !hasSelection;
    }

    // ── View Details ──────────────────────────────────────────────────────
    $(document).on('click', '.view-btn', function () {
        currentSpotlightId = $(this).data('id');
        loadSpotlightDetails(currentSpotlightId);
        $('#viewModal').modal('show');
    });

    function loadSpotlightDetails(id) {
        $('#viewModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        
        axios.get('{{ url('business-spotlights') }}/' + id)
            .then(function (res) {
                const d = res.data.data;
                let html = buildDetailsHtml(d);
                $('#viewModalBody').html(html);
                
                // Update modal buttons based on status
                updateModalButtons(d.status);
            })
            .catch(function () {
                $('#viewModalBody').html('<div class="alert alert-danger">Failed to load details.</div>');
            });
    }

    function buildDetailsHtml(d) {
        return `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Business Information</h6>
                    <table class="table table-sm">
                        <tr><th width="40%">Business Name</th><td>${d.business_name || '-'}</td></tr>
                        <tr><th>Owner/Founder</th><td>${d.owner_founder_name || '-'}</td></tr>
                        <tr><th>Category</th><td>${d.business_category || '-'}</td></tr>
                        <tr><th>Year Founded</th><td>${d.year_founded || '-'}</td></tr>
                        <tr><th>Location</th><td>${d.city}, ${d.state}</td></tr>
                        <tr><th>Website</th><td>${d.business_website ? '<a href="'+d.business_website+'" target="_blank">'+d.business_website+'</a>' : '-'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Contact Information</h6>
                    <table class="table table-sm">
                        <tr><th width="40%">Email</th><td>${d.email || '-'}</td></tr>
                        <tr><th>Phone</th><td>${d.phone_number || '-'}</td></tr>
                        <tr><th>Best Time</th><td>${d.best_contact_time || '-'}</td></tr>
                    </table>
                    
                    <h6 class="text-primary mt-3">Social Media</h6>
                    <div class="d-flex flex-wrap gap-2">
                        ${d.social_media.instagram_url ? '<a href="'+d.social_media.instagram_url+'" target="_blank" class="btn btn-sm btn-soft-danger"><i class="ri-instagram-line"></i></a>' : ''}
                        ${d.social_media.facebook_url ? '<a href="'+d.social_media.facebook_url+'" target="_blank" class="btn btn-sm btn-soft-primary"><i class="ri-facebook-fill"></i></a>' : ''}
                        ${d.social_media.tiktok_url ? '<a href="'+d.social_media.tiktok_url+'" target="_blank" class="btn btn-sm btn-soft-dark"><i class="ri-tiktok-fill"></i></a>' : ''}
                        ${d.social_media.youtube_url ? '<a href="'+d.social_media.youtube_url+'" target="_blank" class="btn btn-sm btn-soft-danger"><i class="ri-youtube-fill"></i></a>' : ''}
                        ${d.social_media.linkedin_url ? '<a href="'+d.social_media.linkedin_url+'" target="_blank" class="btn btn-sm btn-soft-info"><i class="ri-linkedin-fill"></i></a>' : ''}
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-12">
                    <h6 class="text-primary">Business Story</h6>
                    <p>${d.business_story || '<em class="text-muted">Not provided</em>'}</p>
                    
                    <h6 class="text-primary">Products/Services</h6>
                    <p>${d.products_services || '<em class="text-muted">Not provided</em>'}</p>
                    
                    <h6 class="text-primary">Challenges Overcome</h6>
                    <p>${d.challenges_overcome || '<em class="text-muted">Not provided</em>'}</p>
                    
                    <h6 class="text-primary">What Makes Business Unique</h6>
                    <p>${d.unique_factor || '<em class="text-muted">Not provided</em>'}</p>
                    
                    <h6 class="text-primary">Target Customer</h6>
                    <p>${d.target_customer || '<em class="text-muted">Not provided</em>'}</p>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Images</h6>
                    <div class="row g-2">
                        ${d.images.portrait_photo ? '<div class="col-6"><img src="'+d.images.portrait_photo+'" class="img-fluid rounded" alt="Portrait"><small class="d-block text-muted">Portrait</small></div>' : ''}
                        ${d.images.storefront_workspace_photo ? '<div class="col-6"><img src="'+d.images.storefront_workspace_photo+'" class="img-fluid rounded" alt="Storefront"><small class="d-block text-muted">Storefront</small></div>' : ''}
                        ${d.images.team_photo ? '<div class="col-6"><img src="'+d.images.team_photo+'" class="img-fluid rounded" alt="Team"><small class="d-block text-muted">Team</small></div>' : ''}
                    </div>
                    ${d.images.product_service_photos.length ? '<div class="mt-2"><small class="text-muted">Product Photos: ' + d.images.product_service_photos.length + '</small></div>' : ''}
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Spotlight Consideration</h6>
                    <p><strong>Why Featured:</strong> ${d.why_featured || '<em class="text-muted">Not provided</em>'}</p>
                    <p><strong>Growth Vision:</strong> ${d.growth_vision || '<em class="text-muted">Not provided</em>'}</p>
                    
                    <h6 class="text-primary mt-3">Permissions</h6>
                    <ul class="list-unstyled">
                        <li><i class="ri-${d.permissions.feature_on_osi ? 'checkbox-circle-fill text-success' : 'close-circle-fill text-danger'}"></i> Feature on OSI</li>
                        <li><i class="ri-${d.permissions.use_submitted_photos ? 'checkbox-circle-fill text-success' : 'close-circle-fill text-danger'}"></i> Use submitted photos</li>
                        <li><i class="ri-${d.permissions.share_business_story ? 'checkbox-circle-fill text-success' : 'close-circle-fill text-danger'}"></i> Share business story</li>
                    </ul>
                    
                    <h6 class="text-primary mt-3">Service Type</h6>
                    <span class="badge bg-info-subtle text-info">${d.service_type_label}</span>
                </div>
            </div>
            
            ${d.reviewer_notes ? `
            <hr>
            <div class="alert alert-warning">
                <h6 class="alert-heading">Reviewer Notes</h6>
                <p class="mb-0">${d.reviewer_notes}</p>
            </div>
            ` : ''}
        `;
    }

    function updateModalButtons(status) {
        const underReviewBtn = document.getElementById('modalUnderReviewBtn');
        const approveBtn = document.getElementById('modalApproveBtn');
        const rejectBtn = document.getElementById('modalRejectBtn');
        
        underReviewBtn.style.display = (status === 'submitted') ? 'inline-block' : 'none';
        approveBtn.style.display = (status !== 'approved' && status !== 'draft') ? 'inline-block' : 'none';
        rejectBtn.style.display = (status !== 'rejected' && status !== 'draft') ? 'inline-block' : 'none';
    }

    // ── Status Actions ────────────────────────────────────────────────────
    document.getElementById('modalUnderReviewBtn').addEventListener('click', function () {
        updateStatus(currentSpotlightId, 'under-review');
    });

    document.getElementById('modalApproveBtn').addEventListener('click', function () {
        updateStatus(currentSpotlightId, 'approve');
    });

    document.getElementById('modalRejectBtn').addEventListener('click', function () {
        $('#viewModal').modal('hide');
        $('#rejectModal').modal('show');
    });

    document.getElementById('confirmRejectBtn').addEventListener('click', function () {
        const reason = document.getElementById('rejectReason').value;
        if (!reason.trim()) {
            Toast.error('Please provide a reason for rejection.');
            return;
        }
        rejectSpotlight(currentSpotlightId, reason);
    });

    function updateStatus(id, action) {

        Alert.confirm('Are sure to ' + action + ' this submission?', {
            title: 'Update Status',
            type: 'warning',
            confirmText: 'Yes, ' + action,
        }).then(function (confirmed) {
            if (!confirmed) return;
            axios.post('{{ url('business-spotlights') }}/' + id + '/' + action)
                .then(function (res) {
                    Toast.success(res.data.message);
                    $('#viewModal').modal('hide');
                    table.draw(false);
                })
                .catch(function (err) {
                    Toast.error(err.response?.data?.message || 'Failed to update status.');
                });
        });
    }

    function rejectSpotlight(id, reason) {
        axios.post('{{ url('business-spotlights') }}/' + id + '/reject', {
            reviewer_notes: reason
        })
            .then(function (res) {
                Toast.success(res.data.message);
                $('#rejectModal').modal('hide');
                document.getElementById('rejectReason').value = '';
                table.draw(false);
            })
            .catch(function (err) {
                Toast.error(err.response?.data?.message || 'Failed to reject submission.');
            });
    }

    // ── Quick Actions ─────────────────────────────────────────────────────
    $(document).on('click', '.approve-btn', function () {
        const id = $(this).data('id');
        updateStatus(id, 'approve');
    });

    $(document).on('click', '.reject-btn', function () {
        currentSpotlightId = $(this).data('id');
        $('#rejectModal').modal('show');
    });

    // ── Bulk Actions ──────────────────────────────────────────────────────
    document.getElementById('bulkApproveBtn').addEventListener('click', function () {
        if (selectedIds.length === 0) return;
        
        Alert.confirm('This will approve ' + selectedIds.length + ' submission(s).', {
            title: 'Bulk Approve?',
            confirmText: 'Yes, approve all',
        }).then(function (confirmed) {
            if (!confirmed) return;
            
            axios.post('{{ route('admin.business-spotlights.bulk-status') }}', {
                ids: selectedIds,
                status: 'approved'
            })
                .then(function (res) {
                    Toast.success(res.data.message);
                    table.draw(false);
                })
                .catch(function (err) {
                    Toast.error(err.response?.data?.message || 'Bulk approve failed.');
                });
        });
    });

    document.getElementById('bulkRejectBtn').addEventListener('click', function () {
        if (selectedIds.length === 0) return;
        $('#rejectModal').modal('show');
        
        // Override confirm button for bulk reject
        document.getElementById('confirmRejectBtn').onclick = function () {
            const reason = document.getElementById('rejectReason').value;
            if (!reason.trim()) {
                Toast.error('Please provide a reason for rejection.');
                return;
            }
            
            axios.post('{{ route('admin.business-spotlights.bulk-status') }}', {
                ids: selectedIds,
                status: 'rejected',
                reviewer_notes: reason
            })
                .then(function (res) {
                    Toast.success(res.data.message);
                    $('#rejectModal').modal('hide');
                    document.getElementById('rejectReason').value = '';
                    table.draw(false);
                })
                .catch(function (err) {
                    Toast.error(err.response?.data?.message || 'Bulk reject failed.');
                });
        };
    });

    // ── Delete ────────────────────────────────────────────────────────────
    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        
        Alert.confirm('This submission will be moved to trash.', {
            title: 'Delete Submission?',
            type: 'danger',
            confirmText: 'Yes, delete',
        }).then(function (confirmed) {
            if (!confirmed) return;
            
            axios.delete('{{ url('business-spotlights') }}/' + id)
                .then(function (res) {
                    Toast.success(res.data.message);
                    table.draw(false);
                })
                .catch(function (err) {
                    Toast.error(err.response?.data?.message || 'Failed to delete.');
                });
        });
    });

})();
</script>
@endpush
