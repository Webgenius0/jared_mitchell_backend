@extends('layout.master-layout')

@section('title', 'Spotlight Weeks')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Spotlight Weeks</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWeekModal">
                            <i class="ri-add-line align-bottom me-1"></i> Create Week
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row">
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-calendar-check-line fs-24 text-primary"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-time-line fs-24 text-warning"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-warning">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Nominating</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-user-search-line fs-24 text-info"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-info">{{ number_format($stats['nominating']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Voting</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-bar-chart-grouped-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-success">{{ number_format($stats['voting']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Completed</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-flag-line fs-24 text-info"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-info">{{ number_format($stats['completed']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl col-md-4 col-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Cancelled</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-close-circle-line fs-24 text-danger"></i>
                            </div>
                        </div>
                        <h4 class="mt-3 mb-0 text-danger">{{ number_format($stats['cancelled']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Spotlight Weeks</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="spotlightWeeksTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Week</th>
                                        <th>Status</th>
                                        <th>Voting Window</th>
                                        <th>Nominees</th>
                                        <th class="text-center" style="width: 140px;">Action</th>
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

{{-- Create Week Modal --}}
<div class="modal fade" id="createWeekModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.spotlight.weeks.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light pb-2">
                    <h5 class="modal-title"><i class="ri-add-circle-line"></i> Create Spotlight Week</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Week # <span class="text-danger">*</span></label>
                            <input type="number" name="week_number" class="form-control" min="1" max="53" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" min="2020" max="2099" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voting Starts At</label>
                            <input type="datetime-local" name="voting_starts_at" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voting Ends At</label>
                            <input type="datetime-local" name="voting_ends_at" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="nominating">Nominating</option>
                                <option value="voting">Voting</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Week</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Week Modal --}}
<div class="modal fade" id="editWeekModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editWeekForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light pb-2">
                    <h5 class="modal-title"><i class="ri-pencil-line"></i> Edit Spotlight Week</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Week # <span class="text-danger">*</span></label>
                            <input type="number" name="week_number" id="edit_week_number" class="form-control" min="1" max="53" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" id="edit_year" class="form-control" min="2020" max="2099" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voting Starts At</label>
                            <input type="datetime-local" name="voting_starts_at" id="edit_voting_starts_at" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voting Ends At</label>
                            <input type="datetime-local" name="voting_ends_at" id="edit_voting_ends_at" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="nominating">Nominating</option>
                                <option value="voting">Voting</option>
                                <option value="voting_closed">Voting Closed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Week</button>
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

        @if(session('warning'))
            Toast.warning(@json(session('warning')));
        @endif

        @if(session('info'))
            Toast.info(@json(session('info')));
        @endif

        const table = $('#spotlightWeeksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.spotlight.weeks.index') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'week_label',  name: 'week_number' },
                { data: 'status_badge', name: 'status', className: 'text-center' },
                { data: 'voting_window', name: 'voting_starts_at' },
                { data: 'nominees_count', name: 'nominees_count', className: 'text-center' },
                { data: 'action',       name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[1, 'desc']]
        });

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Toast.error('{{ $error }}');
            @endforeach
            var createModal = new bootstrap.Modal(document.getElementById('createWeekModal'));
            createModal.show();
        @endif

        @if($editWeekId)
            // Auto-open edit modal on page load (direct API call, no DataTable dependency)
            $(function () {
                $.getJSON('{{ route('admin.spotlight.weeks.edit', $editWeekId) }}', function (res) {
                    if (!res.success) return;
                    var d = res.data;
                    $('#edit_week_number').val(d.week_number);
                    $('#edit_year').val(d.year);
                    $('#edit_voting_starts_at').val(d.voting_starts_at);
                    $('#edit_voting_ends_at').val(d.voting_ends_at);
                    $('#edit_status').val(d.status);
                    $('#editWeekForm').attr('action', '{{ route('admin.spotlight.weeks.update', $editWeekId) }}');
                    new bootstrap.Modal(document.getElementById('editWeekModal')).show();
                });
            });
        @endif

        // ── Edit Week — fetch data & populate modal ──────────────────
        var editUrlTemplate = '{{ route('admin.spotlight.weeks.edit', ':id') }}';
        var updateUrlTemplate = '{{ route('admin.spotlight.weeks.update', ':id') }}';

        $(document).on('click', '.edit-week', function () {
            var id = $(this).data('id');

            $.getJSON(editUrlTemplate.replace(':id', id), function (res) {
                if (!res.success) return;

                var d = res.data;
                $('#edit_week_number').val(d.week_number);
                $('#edit_year').val(d.year);
                $('#edit_voting_starts_at').val(d.voting_starts_at);
                $('#edit_voting_ends_at').val(d.voting_ends_at);
                $('#edit_status').val(d.status);

                $('#editWeekForm').attr('action', updateUrlTemplate.replace(':id', id));

                var modal = new bootstrap.Modal(document.getElementById('editWeekModal'));
                modal.show();
            });
        });

        // ── SweetAlert for DataTable forms with data-confirm (delegated) ──
        document.querySelector('#spotlightWeeksTable').addEventListener('submit', function (e) {
            var form = e.target.closest('form[data-confirm]');
            if (!form) return;

            e.preventDefault();

            var message = form.getAttribute('data-confirm');
            var type = form.getAttribute('data-confirm-type') || 'confirm';

            Alert.confirm(message, {
                type: type,
                confirmText: type === 'danger' ? 'Yes, delete' : type === 'warning' ? 'Yes, proceed' : 'Yes, confirm',
            }).then(function (confirmed) {
                if (confirmed) {
                    form.submit();
                }
            });
        });

    })();
</script>
@endpush
