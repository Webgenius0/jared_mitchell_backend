@extends('layout.master-layout')

@section('title', 'Orders')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Orders</h4>
                    <div class="page-title-right">
                        <div class="d-flex gap-2">
                            <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                            <select id="paymentFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="">All Payments</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="refunded">Refunded</option>
                                <option value="partially_refunded">Partially Refunded</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="ordersTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
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

        const table = $('#ordersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.orders.data') }}',
                data: function (d) {
                    d.status = $('#statusFilter').val();
                    d.payment_status = $('#paymentFilter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'order_info', name: 'order_number' },
                { data: 'customer', name: 'customer', searchable: false },
                { data: 'items_count', name: 'items_count', searchable: false, className: 'text-center' },
                { data: 'total_display', name: 'total', className: 'text-center' },
                { data: 'status_badge', name: 'status', className: 'text-center' },
                { data: 'payment_badge', name: 'payment_status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            },
            order: [[1, 'desc']]
        });

        $('#statusFilter, #paymentFilter').on('change', function () {
            table.ajax.reload();
        });

        $('#ordersTable').on('click', '.delete-btn', function () {
            const orderId = $(this).data('id');
            let deleteUrl = '{{ route("admin.orders.destroy", ":id") }}';
            deleteUrl = deleteUrl.replace(':id', orderId);

            Alert.confirm('This will permanently delete the order.', {
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (!confirmed) return;
                
                axios.delete(deleteUrl)
                    .then(res => {
                        Toast.success(res.data.message);
                        table.ajax.reload();
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Failed to delete order.');
                    });
            });
        });
    })();
</script>
@endpush
@endsection
