@extends('layout.master-layout')

@section('title', 'Registered Events')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Registered Events</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.events.index') }}" class="btn btn-soft-primary">
                            <i class="ri-calendar-event-line align-bottom me-1"></i> All Events
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row mb-3">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-animate border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-file-list-3-line fs-24 text-primary"></i>
                            </div>
                        </div>
                        <h4 class="mt-2 mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-animate border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Confirmed</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h4 class="mt-2 mb-0 text-success">{{ number_format($stats['confirmed']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-animate border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-time-line fs-24 text-warning"></i>
                            </div>
                        </div>
                        <h4 class="mt-2 mb-0 text-warning">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-animate border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Cancelled</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-close-circle-line fs-24 text-danger"></i>
                            </div>
                        </div>
                        <h4 class="mt-2 mb-0 text-danger">{{ number_format($stats['cancelled']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-8 col-12">
                <div class="card card-animate border-0 shadow-sm bg-gradient-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-white text-truncate mb-0 opacity-75">Total Revenue</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-money-dollar-circle-line fs-24 text-white"></i>
                            </div>
                        </div>
                        <h4 class="mt-2 mb-0 text-white">${{ number_format($stats['total_revenue'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0 flex-grow-1">All Registrations</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group">
                                <button type="button" class="btn btn-soft-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-download-2-line me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportCsvBtn">
                                            <i class="ri-file-text-line me-2"></i>Export as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportExcelBtn">
                                            <i class="ri-file-excel-2-line me-2 text-success"></i>Export as Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" id="exportPdfBtn">
                                            <i class="ri-file-pdf-2-line me-2 text-danger"></i>Export as PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="card-body border-bottom pb-2 position-relative">
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label text-muted small mb-1">Search</label>
                                <div class="search-box">
                                    <input type="text" class="form-control" id="searchInput"
                                           placeholder="Search booking ref, name, email, event...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-3">
                                <label class="form-label text-muted small mb-1">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="checked_in">Checked In</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-3">
                                <label class="form-label text-muted small mb-1">Payment</label>
                                <select class="form-select" id="paymentFilter">
                                    <option value="">All Payments</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                    <option value="refunded">Refunded</option>
                                    <option value="unpaid">Unpaid</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Date Range</label>
                                <div class="d-flex gap-1">
                                    <input type="text" class="form-control" id="dateFrom"
                                           data-provider="flatpickr" data-date-format="Y-m-d"
                                           placeholder="From">
                                    <input type="text" class="form-control" id="dateTo"
                                           data-provider="flatpickr" data-date-format="Y-m-d"
                                           placeholder="To">
                                </div>
                            </div>
                            <div class="col-xl-1 col-md-3">
                                <label class="form-label text-muted small mb-1">&nbsp;</label>
                                <button type="button" id="resetFilters" class="btn btn-soft-danger w-100" title="Reset Filters">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div class="spinner-border spinner-border-sm text-primary d-none" id="filterSpinner" role="status" style="position: absolute; top: 10px; right: 10px;"></div>
                    </div>

                    {{-- DataTable --}}
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="registrationsTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Booking / Event</th>
                                        <th>Customer</th>
                                        <th>Tier</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Payment</th>
                                        <th class="text-center">Status</th>
                                        <th>Registered</th>
                                        <th class="text-center" style="width: 80px;">Action</th>
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
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-file-list-3-line fs-18"></i>
                    <h5 class="modal-title mb-0">Registration Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="viewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer border-top flex-wrap d-flex justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-2">
                    <div id="statusActions" class="d-flex gap-2">
                        <button type="button" class="btn btn-success" id="modalConfirmBtn" style="display: none;">
                            <i class="ri-check-double-line me-1"></i> Confirm
                        </button>
                        <button type="button" class="btn btn-info" id="modalCheckinBtn" style="display: none;">
                            <i class="ri-door-open-line me-1"></i> Check In
                        </button>
                        <button type="button" class="btn btn-danger" id="modalCancelBtn" style="display: none;">
                            <i class="ri-close-circle-line me-1"></i> Cancel
                        </button>
                    </div>
                    <div class="vr mx-1" id="paymentDivider" style="display: none;"></div>
                    <div id="paymentActions" class="d-flex gap-2">
                        <button type="button" class="btn btn-success" id="modalPayBtn" style="display: none;">
                            <i class="ri-bank-card-line me-1"></i> Mark Paid
                        </button>
                        <button type="button" class="btn btn-warning" id="modalRefundBtn" style="display: none;">
                            <i class="ri-refund-2-line me-1"></i> Refund
                        </button>
                        <button type="button" class="btn btn-danger" id="modalFailBtn" style="display: none;">
                            <i class="ri-close-circle-line me-1"></i> Mark Failed
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Refund Reason Modal --}}
<div class="modal fade" id="refundReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-refund-2-line fs-18"></i>
                    <h5 class="modal-title mb-0">Refund Payment</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label">Refund Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="refundReasonInput" rows="4" placeholder="Please provide a reason for the refund..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Back</button>
                <button type="button" class="btn btn-warning" id="confirmRefundBtn">
                    <i class="ri-refund-2-line me-1"></i> Confirm Refund
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Reason Modal --}}
<div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-close-circle-line fs-18"></i>
                    <h5 class="modal-title mb-0">Cancel Registration</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="cancelReasonInput" rows="4" placeholder="Please provide a reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Back</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="ri-close-circle-line me-1"></i> Confirm Cancellation
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // Filter helpers
    function getFilterParams() {
        const params = new URLSearchParams();
        const search   = document.getElementById('searchInput').value;
        const status   = document.getElementById('statusFilter').value;
        const payment  = document.getElementById('paymentFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo   = document.getElementById('dateTo').value;
        if (search)   params.set('search_query', search);
        if (status)   params.set('status', status);
        if (payment)  params.set('payment_status', payment);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo)   params.set('date_to', dateTo);
        return params;
    }

    function updateExportLinks() {
        const qs = getFilterParams().toString();
        document.getElementById('exportCsvBtn').href   = '{{ route('admin.events.registrations.export.csv') }}'   + (qs ? '?' + qs : '');
        document.getElementById('exportExcelBtn').href = '{{ route('admin.events.registrations.export.excel') }}' + (qs ? '?' + qs : '');
        document.getElementById('exportPdfBtn').href   = '{{ route('admin.events.registrations.export.pdf') }}'   + (qs ? '?' + qs : '');
    }

    function reloadTable() {
        updateExportLinks();
        table.ajax.reload(null, false);
    }

    // DataTable
    const table = $('#registrationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.events.registrations.data') }}',
            data: function (d) {
                d.search_query = document.getElementById('searchInput').value;
                d.status = document.getElementById('statusFilter').value;
                d.payment_status = document.getElementById('paymentFilter').value;
                d.date_from = document.getElementById('dateFrom').value;
                d.date_to = document.getElementById('dateTo').value;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'booking_info', name: 'booking_reference' },
            { data: 'customer', name: 'first_name' },
            { data: 'tier', name: 'ticket_tier_id', className: 'text-center' },
            { data: 'quantity', name: 'quantity', className: 'text-center' },
            { data: 'total_amount', name: 'total', className: 'text-end' },
            { data: 'payment_status', name: 'payment_status', className: 'text-center' },
            { data: 'registration_status', name: 'status', className: 'text-center' },
            { data: 'registered_date', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            emptyTable: '<div class="text-center py-5"><i class="ri-file-list-3-line fs-1 text-muted"></i><p class="mt-2 mb-0 text-muted">No registrations found</p></div>',
        },
        order: [[8, 'desc']]
    });

    // Filter events
    let searchTimeout = null;
    document.getElementById('searchInput').addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => reloadTable(), 300);
    });

    document.getElementById('statusFilter').addEventListener('change', function () {
        reloadTable();
    });

    document.getElementById('paymentFilter').addEventListener('change', function () {
        reloadTable();
    });

    // Flatpickr
    let fpFrom, fpTo;
    if (typeof flatpickr !== 'undefined') {
        fpFrom = flatpickr("#dateFrom", {
            dateFormat: "Y-m-d",
            onChange: function() { reloadTable(); }
        });
        fpTo = flatpickr("#dateTo", {
            dateFormat: "Y-m-d",
            onChange: function() { reloadTable(); }
        });
    }

    // Init export links
    updateExportLinks();

    // Reset
    document.getElementById('resetFilters').addEventListener('click', function () {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('paymentFilter').value = '';
        if (fpFrom) fpFrom.clear();
        if (fpTo) fpTo.clear();
        reloadTable();
    });

    // View Details
    $(document).on('click', '.view-btn', function () {
        const id = $(this).data('id');
        loadRegistrationDetails(id);
        $('#viewModal').modal('show');
    });

    function loadRegistrationDetails(id) {
        currentRegistrationId = id;

        $('#viewModalBody').html(
            '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Loading details...</p></div>'
        );

        axios.get('{{ route('admin.events.registrations.index') }}/' + id)
            .then(function (res) {
                const d = res.data.data;
                const html = buildDetailsHtml(d);
                $('#viewModalBody').html(html);
                // Update action buttons after DOM is rendered
                updateModalButtons(d.status, d.payment_status);
            })
            .catch(function () {
                $('#viewModalBody').html(
                    '<div class="alert alert-danger m-4">Failed to load registration details.</div>'
                );
            });
    }

    function buildDetailsHtml(d) {
        // Payment timeline HTML
        let timelineHtml = '';
        if (d.timeline && d.timeline.length > 0) {
            d.timeline.forEach(function (item, index) {
                const isLast = index === d.timeline.length - 1;
                timelineHtml += `
                    <div class="d-flex gap-3 ${isLast ? '' : 'mb-3'}">
                        <div class="flex-shrink-0 text-center" style="width: 32px;">
                            <i class="${item.icon} ${item.color} fs-18"></i>
                            ${isLast ? '' : '<div style="width: 2px; height: 28px; background: #e5e7eb; margin: 4px auto 0;"></div>'}
                        </div>
                        <div class="flex-grow-1 pb-2">
                            <strong>${item.event}</strong>
                            <br><small class="text-muted">${item.date}</small>
                        </div>
                    </div>
                `;
            });
        } else {
            timelineHtml = '<p class="text-muted mb-0"><em>No timeline events yet.</em></p>';
        }

        // Build status badge helper
        function statusBadge(status) {
            const map = {
                'confirmed': 'bg-success-subtle text-success',
                'pending': 'bg-warning-subtle text-warning',
                'cancelled': 'bg-danger-subtle text-danger',
                'checked_in': 'bg-info-subtle text-info',
                'failed': 'bg-danger-subtle text-danger',
            };
            const cls = map[status] || 'bg-secondary-subtle text-secondary';
            return `<span class="badge ${cls} fs-12">${status ? status.charAt(0).toUpperCase() + status.slice(1) : 'N/A'}</span>`;
        }

        function paymentBadge(status) {
            const map = {
                'paid': 'bg-success-subtle text-success',
                'pending': 'bg-warning-subtle text-warning',
                'failed': 'bg-danger-subtle text-danger',
                'refunded': 'bg-info-subtle text-info',
                'unpaid': 'bg-secondary-subtle text-secondary',
            };
            const cls = map[status] || 'bg-secondary-subtle text-secondary';
            return `<span class="badge ${cls} fs-12">${status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unpaid'}</span>`;
        }

        return `
            <div class="p-4">
                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div>
                        <h4 class="mb-1 text-primary">${d.booking_reference}</h4>
                        <small class="text-muted">Registered on ${d.registered_at}</small>
                    </div>
                    <div class="d-flex gap-2">
                        ${statusBadge(d.status)}
                        ${paymentBadge(d.payment_status)}
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Customer Info --}}
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="text-primary mb-3"><i class="ri-user-line me-1"></i> Customer Information</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 120px;">Name</td>
                                    <td><strong>${d.customer.first_name} ${d.customer.last_name}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td>${d.customer.email}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone</td>
                                    <td>${d.customer.phone}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Account</td>
                                    <td>${d.customer.user_email}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Event Info --}}
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="text-primary mb-3"><i class="ri-calendar-event-line me-1"></i> Event Information</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 120px;">Event</td>
                                    <td><strong>${d.event.title}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date</td>
                                    <td>${d.event.starts_at}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Venue</td>
                                    <td>${d.event.venue}, ${d.event.city}, ${d.event.state}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>${d.event.status}</td>
                                </tr>
                                ${d.event.id ? `
                                <tr>
                                    <td colspan="2" class="pt-3">
                                        <a href="{{ url('events') }}/${d.event.id}" target="_blank" class="btn btn-sm btn-soft-primary w-100">
                                            <i class="ri-external-link-line me-1"></i> View Event
                                        </a>
                                    </td>
                                </tr>` : ''}
                            </table>
                        </div>
                    </div>

                    {{-- Ticket Details --}}
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="text-primary mb-3"><i class="ri-ticket-line me-1"></i> Ticket Details</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 120px;">Tier</td>
                                    <td><strong>${d.ticket.tier_name}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Quantity</td>
                                    <td>${d.ticket.quantity}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Unit Price</td>
                                    <td>${d.ticket.unit_price}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Service Fee</td>
                                    <td>${d.ticket.service_fee}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Subtotal</td>
                                    <td>${d.ticket.subtotal}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted fw-semibold pt-2">Total</td>
                                    <td class="pt-2"><strong class="fs-15">${d.ticket.total}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Payment History --}}
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="text-primary mb-3"><i class="ri-bank-card-line me-1"></i> Payment History</h6>
                            ${timelineHtml}

                            {{-- Stripe Details --}}
                            ${d.payment.stripe_session_id !== '—' || d.payment.stripe_charge !== '—' ? `
                            <hr class="my-3">
                            <h6 class="text-muted mb-2" style="font-size: 13px;"><i class="ri-bank-card-line me-1"></i> Stripe References</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 12px;">
                                ${d.payment.stripe_session_id !== '—' ? `<tr><td class="text-muted" style="width: 130px;">Session ID</td><td class="text-truncate" style="max-width: 200px;"><code class="small">${d.payment.stripe_session_id}</code></td></tr>` : ''}
                                ${d.payment.stripe_pi_id !== '—' ? `<tr><td class="text-muted">Payment Intent</td><td><code class="small">${d.payment.stripe_pi_id}</code></td></tr>` : ''}
                                ${d.payment.stripe_customer !== '—' ? `<tr><td class="text-muted">Customer ID</td><td><code class="small">${d.payment.stripe_customer}</code></td></tr>` : ''}
                                ${d.payment.stripe_charge !== '—' ? `<tr><td class="text-muted">Charge ID</td><td><code class="small">${d.payment.stripe_charge}</code></td></tr>` : ''}
                                ${d.payment.stripe_refund !== '—' ? `<tr><td class="text-muted">Refund ID</td><td><code class="small">${d.payment.stripe_refund}</code></td></tr>` : ''}
                            </table>
                            ` : ''}

                            {{-- Cancellation Reason --}}
                            ${d.cancellation_reason ? `
                            <hr class="my-3">
                            <div class="alert alert-warning mb-0 py-2 px-3">
                                <h6 class="alert-heading small mb-1"><i class="ri-error-warning-line me-1"></i> Cancellation Reason</h6>
                                <p class="mb-0 small">${d.cancellation_reason}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    // Status Actions
    let currentRegistrationId = null;

    function updateModalButtons(status, paymentStatus) {
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const checkinBtn = document.getElementById('modalCheckinBtn');
        const cancelBtn  = document.getElementById('modalCancelBtn');
        const payBtn     = document.getElementById('modalPayBtn');
        const refundBtn  = document.getElementById('modalRefundBtn');
        const failBtn    = document.getElementById('modalFailBtn');
        const divider    = document.getElementById('paymentDivider');

        // Reset all
        [confirmBtn, checkinBtn, cancelBtn, payBtn, refundBtn, failBtn].forEach(function(btn) {
            btn.style.display = 'none';
        });
        divider.style.display = 'none';

        // Status actions
        if (status === 'pending') {
            confirmBtn.style.display = 'inline-block';
            checkinBtn.style.display = 'inline-block';
            cancelBtn.style.display  = 'inline-block';
        } else if (status === 'confirmed') {
            checkinBtn.style.display = 'inline-block';
            cancelBtn.style.display  = 'inline-block';
        }

        // Payment actions (only for active registrations)
        var hasAnyStatusAction = confirmBtn.style.display !== 'none' || checkinBtn.style.display !== 'none' || cancelBtn.style.display !== 'none';

        if (paymentStatus === 'unpaid' || paymentStatus === 'pending') {
            payBtn.style.display  = 'inline-block';
            failBtn.style.display = 'inline-block';
            if (paymentStatus === 'pending') {
                refundBtn.style.display = 'inline-block';
            }
        } else if (paymentStatus === 'paid') {
            refundBtn.style.display = 'inline-block';
        }

        // Show divider if both sections have visible buttons
        var hasAnyPaymentAction = payBtn.style.display !== 'none' || refundBtn.style.display !== 'none' || failBtn.style.display !== 'none';
        if (hasAnyStatusAction && hasAnyPaymentAction) {
            divider.style.display = 'inline-block';
        }
    }

    document.getElementById('modalConfirmBtn').addEventListener('click', function () {
        updateRegistrationStatus(currentRegistrationId, 'confirmed');
    });

    document.getElementById('modalCheckinBtn').addEventListener('click', function () {
        updateRegistrationStatus(currentRegistrationId, 'checked_in');
    });

    document.getElementById('modalCancelBtn').addEventListener('click', function () {
        $('#viewModal').modal('hide');
        $('#cancelReasonModal').modal('show');
    });

    document.getElementById('confirmCancelBtn').addEventListener('click', function () {
        const reason = document.getElementById('cancelReasonInput').value.trim();
        if (!reason) {
            Toast.error('Please provide a reason for cancellation.');
            return;
        }
        updateRegistrationStatus(currentRegistrationId, 'cancelled', reason);
    });

    // Reset cancel reason when modal opens
    $('#cancelReasonModal').on('show.bs.modal', function () {
        document.getElementById('cancelReasonInput').value = '';
    });

    function updateRegistrationStatus(id, status, reason) {
        const data = { status: status };
        if (reason) {
            data.cancellation_reason = reason;
        }

        axios.post('{{ route('admin.events.registrations.index') }}/' + id + '/status', data)
            .then(function (res) {
                Toast.success(res.data.message);
                $('#cancelReasonModal').modal('hide');
                $('#viewModal').modal('hide');
                reloadTable();
            })
            .catch(function (err) {
                const msg = err.response?.data?.message || 'Failed to update status.';
                Toast.error(msg);
            });
    }

    // Payment Actions
    document.getElementById('modalPayBtn').addEventListener('click', function () {
        updatePaymentStatus(currentRegistrationId, 'paid');
    });

    document.getElementById('modalFailBtn').addEventListener('click', function () {
        updatePaymentStatus(currentRegistrationId, 'failed');
    });

    document.getElementById('modalRefundBtn').addEventListener('click', function () {
        $('#viewModal').modal('hide');
        $('#refundReasonModal').modal('show');
    });

    document.getElementById('confirmRefundBtn').addEventListener('click', function () {
        const reason = document.getElementById('refundReasonInput').value.trim();
        if (!reason) {
            Toast.error('Please provide a reason for the refund.');
            return;
        }
        updatePaymentStatus(currentRegistrationId, 'refunded', reason);
    });

    // Reset refund reason when modal opens
    $('#refundReasonModal').on('show.bs.modal', function () {
        document.getElementById('refundReasonInput').value = '';
    });

    function updatePaymentStatus(id, paymentStatus, reason) {
        const data = { payment_status: paymentStatus };
        if (reason) {
            data.refund_reason = reason;
        }

        axios.post('{{ route('admin.events.registrations.index') }}/' + id + '/payment-status', data)
            .then(function (res) {
                Toast.success(res.data.message);
                $('#refundReasonModal').modal('hide');
                $('#viewModal').modal('hide');
                reloadTable();
            })
            .catch(function (err) {
                const msg = err.response?.data?.message || 'Failed to update payment status.';
                Toast.error(msg);
            });
    }
})();
</script>
@endpush
