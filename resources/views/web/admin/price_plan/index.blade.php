@extends('layout.master-layout')
@section('title', 'Price Plans')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Price Plans</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Price Plans</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>Pricing Plans</h5>
                            <a href="{{ route('admin.pricing.create') }}" class="btn btn-primary">+ Add Plan</a>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered align-middle" id="pricingPlansTable" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Plan</th>
                                        <th>Price</th>
                                        <th>Badge</th>
                                        <th>Featured</th>
                                        <th>Groups</th>
                                        <th>Visible</th>
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
            const indexRoute = @json(route('admin.pricing.index'));
            const toggleRouteTemplate = @json(route('admin.pricing.toggle', ['plan' => '__PLAN_ID__']));
            const destroyRouteTemplate = @json(route('admin.pricing.destroy', ['plan' => '__PLAN_ID__']));
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            function endpoint(template, planId) {
                return template.replace('__PLAN_ID__', String(planId));
            }

            const table = $('#pricingPlansTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: indexRoute,
                    type: 'GET',
                },
                order: [
                    [0, 'asc']
                ],
                columns: [{
                        data: 'sort_order',
                        name: 'sort_order'
                    },
                    {
                        data: 'plan_name',
                        name: 'plan_name'
                    },
                    {
                        data: 'price_text',
                        name: 'price',
                        searchable: false
                    },
                    {
                        data: 'badge_text_display',
                        name: 'badge_text',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'featured_display',
                        name: 'is_featured',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'groups_count_display',
                        name: 'feature_groups_count',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'visible_display',
                        name: 'is_visible',
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

            $(document).on('click', '.js-toggle-visibility', function() {
                const planId = Number($(this).data('plan-id'));

                $.ajax({
                    url: endpoint(toggleRouteTemplate, planId),
                    method: 'POST',
                    data: {
                        _method: 'PATCH',
                        _token: csrfToken,
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).done(function(response) {
                    Toast.success(response?.message || 'Visibility updated.');
                    table.ajax.reload(null, false);
                }).fail(function(xhr) {
                    Toast.fromResponse(xhr.responseJSON || {});
                });
            });

            $(document).on('click', '.js-delete-plan', function() {
                const planId = Number($(this).data('plan-id'));

                Alert.confirm('Delete this pricing plan and all its groups/items?', {
                    title: 'Delete plan?',
                    type: 'danger',
                    confirmText: 'Yes, delete',
                }).then(function(confirmed) {
                    if (!confirmed) {
                        return;
                    }

                    $.ajax({
                        url: endpoint(destroyRouteTemplate, planId),
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken,
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).done(function(response) {
                        Toast.success(response?.message || 'Plan deleted.');
                        table.ajax.reload(null, false);
                    }).fail(function(xhr) {
                        Toast.fromResponse(xhr.responseJSON || {});
                    });
                });
            });
        });
    </script>
@endpush

