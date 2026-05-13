@extends('layout.master-layout')
@section('title', 'FAQs Management')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">FAQs</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">FAQs</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>Frequently Asked Questions</h5>
                            <a href="{{ route('admin.cms.faq.create') }}" class="btn btn-primary">+ Add FAQ</a>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered align-middle" id="faqsTable" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Question</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {
            const indexRoute = @json(route('admin.cms.faq.index'));
            const destroyRouteTemplate = @json(route('admin.cms.faq.destroy', ['faq' => '__FAQ_ID__']));
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            function endpoint(template, faqId) {
                return template.replace('__FAQ_ID__', String(faqId));
            }

            const table = $('#faqsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: indexRoute,
                    type: 'GET',
                },
                order: [
                    [0, 'desc']
                ],
                columns: [
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'question',
                        name: 'question'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            $(document).on('click', '.js-delete-faq', function() {
                const faqId = Number($(this).data('faq-id'));

                Alert.confirm('Delete this FAQ?', {
                    title: 'Delete FAQ?',
                    type: 'danger',
                    confirmText: 'Yes, delete',
                }).then(function(confirmed) {
                    if (!confirmed) {
                        return;
                    }

                    $.ajax({
                        url: endpoint(destroyRouteTemplate, faqId),
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken,
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).done(function(response) {
                        Toast.success(response?.message || 'FAQ deleted.');
                        table.ajax.reload(null, false);
                    }).fail(function(xhr) {
                        Toast.fromResponse(xhr.responseJSON || {});
                    });
                });
            });
        });
    </script>
@endpush
