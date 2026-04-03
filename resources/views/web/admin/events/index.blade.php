@extends('layout.master-layout')

@section('title', 'Events')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Events</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Create Event
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="eventsTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Event Info</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Registrations</th>
                                        <th class="text-center" style="width: 150px;">Action</th>
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

        const table = $('#eventsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.events.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'title_info', name: 'title' },
                { data: 'event_type', name: 'event_type', className: 'text-center' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'date', name: 'starts_at' },
                { data: 'registrations_count', name: 'registrations_count', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[4, 'desc']] // Default order by starts_at
        });

        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            Alert.confirm('This will delete the event and all associated records.', {
                title: 'Delete Event?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (!confirmed) return;
                axios.delete(`{{ url('admin/events') }}/${id}`)
                    .then(res => {
                        Toast.success('Event deleted successfully.');
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
