@extends('layout.master-layout')

@section('title', 'Round Sessions')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Round Sessions</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.round-sessions.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Create Round Session
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5>All Round Sessions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="roundSessionsTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Rounds</th>
                                        <th>Date Range</th>
                                        <th class="text-center" style="width: 160px;">Action</th>
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

        const table = $('#roundSessionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.round-sessions.index') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title', name: 'title' },
                { data: 'status', name: 'is_active', className: 'text-center' },
                { data: 'rounds_count', name: 'rounds_count', className: 'text-center' },
                { data: 'date_range', name: 'starts_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[4, 'desc']]
        });

        // Toggle active status
        $(document).on('click', '.toggle-active-btn', function() {
            const id = $(this).data('id');
            const isCurrentlyActive = $(this).data('active') === 1;
            const actionLabel = isCurrentlyActive ? 'deactivate' : 'activate';

            Alert.confirm(`This will ${actionLabel} this round session. Only one season can be active at a time.`, {
                title: `${isCurrentlyActive ? 'Deactivate' : 'Activate'} Round Session?`,
                type: 'info',
                confirmText: `Yes, ${actionLabel} it`
            }).then(confirmed => {
                if (!confirmed) return;

                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                axios.patch(`{{ url('/round-sessions') }}/${id}/toggle-active`)
                    .then(res => {
                        Toast.success(res.data.message);
                        table.draw(false);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Failed to toggle status.');
                        btn.prop('disabled', false).html(isCurrentlyActive
                            ? '<i class="ri-pause-circle-line"></i>'
                            : '<i class="ri-play-circle-line"></i>');
                    });
            });
        });

        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('This will delete the round session and all associated rounds.', {
                title: 'Delete Round Session?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (!confirmed) return;
                axios.delete(`{{ url('admin/round-sessions') }}/${id}`)
                    .then(res => {
                        Toast.success('Round session deleted successfully.');
                        table.draw(false);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Delete failed.');
                    });
            });
        });
    })();
</script>
@endpush
@endsection
