@extends('layout.master-layout')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Order #{{ $order->order_number }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left Column --}}
            <div class="col-lg-4">
                {{-- Customer Card --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Customer</h5>
                    </div>
                    <div class="card-body">
                        @if($order->user)
                            <div class="d-flex align-items-center mb-3">
                                <img class="rounded-circle me-2" src="{{ $order->user->profile?->avatar_url ?? asset('admin/assets/images/default/user.jpg') }}"
                                    alt="" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <strong>{{ $order->user->profile?->name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $order->user->email }}</small>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">User deleted</p>
                        @endif
                    </div>
                </div>

                {{-- Status Summary Card --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Order Status:</span>
                            <span>{!! \App\Helpers\Helper::statusBadge($order->status) !!}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Payment Status:</span>
                            <span>{!! \App\Helpers\Helper::paymentBadge($order->payment_status) !!}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Payment Method:</span>
                            <span>{{ $order->payment_method ? ucfirst($order->payment_method) : '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Transaction ID:</span>
                            <span class="text-truncate" style="max-width: 150px;">{{ $order->payment_transaction_id ?: '—' }}</span>
                        </div>
                        @if($order->confirmed_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Confirmed:</span>
                            <span>{{ $order->confirmed_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                        @if($order->shipped_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipped:</span>
                            <span>{{ $order->shipped_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                        @if($order->delivered_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivered:</span>
                            <span>{{ $order->delivered_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                        @if($order->cancelled_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Cancelled:</span>
                            <span class="text-danger">{{ $order->cancelled_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                        @if($order->refunded_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Refunded:</span>
                            <span>{{ $order->refunded_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Order Status Actions --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Update Order Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($statuses as $status)
                                @if($status !== $order->status)
                                    <button type="button" class="btn btn-sm btn-outline-{{ \App\Helpers\Helper::statusColor($status) }} update-status-btn"
                                        data-status="{{ $status }}">
                                        {{ ucfirst($status) }}
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-{{ \App\Helpers\Helper::statusColor($status) }}" disabled>
                                        {{ ucfirst($status) }} ✓
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Payment Status Actions --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Update Payment Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($paymentStatuses as $pStatus)
                                    @if($pStatus !== $order->payment_status)
                                        <button type="button" class="btn btn-sm btn-outline-{{ \App\Helpers\Helper::paymentColor($pStatus) }} update-payment-btn"
                                            data-payment_status="{{ $pStatus }}">
                                            {{ str_replace('_', ' ', ucfirst($pStatus)) }}
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-{{ \App\Helpers\Helper::paymentColor($pStatus) }}" disabled>
                                            {{ str_replace('_', ' ', ucfirst($pStatus)) }} ✓
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="transaction-id-group" style="display: none;">
                            <label class="form-label">Transaction ID</label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" id="transactionId" placeholder="Enter transaction ID">
                                <button class="btn btn-sm btn-primary" id="confirmTransactionBtn">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Refund Action --}}
                @if(!in_array($order->status, ['cancelled', 'refunded']))
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-danger">Refund Order</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">This will mark the order as refunded and restore stock.</p>
                        <div class="mb-2">
                            <textarea class="form-control form-control-sm" id="refundNote" rows="2" placeholder="Optional refund note..."></textarea>
                        </div>
                        <button type="button" class="btn btn-danger w-100" id="refundOrderBtn">
                            <i class="ri-refund-line me-1"></i> Process Refund
                        </button>
                    </div>
                </div>
                @endif

                {{-- Notes --}}
                @if($order->notes || $order->admin_notes)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        @if($order->notes)
                        <div class="mb-2">
                            <strong class="text-muted small">Customer Notes:</strong>
                            <p class="mb-0 mt-1">{{ $order->notes }}</p>
                        </div>
                        @endif
                        @if($order->admin_notes)
                        <div>
                            <strong class="text-muted small">Admin Notes:</strong>
                            <p class="mb-0 mt-1">{!! nl2br(e($order->admin_notes)) !!}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column --}}
            <div class="col-lg-8">
                {{-- Order Items --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Items ({{ $order->items->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;"></th>
                                        <th>Product</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->product_thumbnail)
                                                <img src="{{ asset('/' . $item->product_thumbnail) }}" alt=""
                                                    style="width: 48px; height: 48px; object-fit: cover;" class="rounded">
                                            @else
                                                <div class="rounded bg-light d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="ri-image-line text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if($item->product_id)
                                                <br><small class="text-muted">SKU: #{{ $item->product_id }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($item->sale_price)
                                                <span class="text-decoration-line-through text-muted">${{ number_format($item->product_price, 2) }}</span>
                                                <br><span class="text-danger fw-semibold">${{ number_format($item->sale_price, 2) }}</span>
                                            @else
                                                <span class="fw-semibold">${{ number_format($item->product_price, 2) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-semibold">Subtotal:</td>
                                        <td class="text-end">${{ number_format($order->subtotal, 2) }}</td>
                                    </tr>
                                    @if($order->discount > 0)
                                    <tr>
                                        <td colspan="4" class="text-end fw-semibold">Discount:</td>
                                        <td class="text-end text-danger">-${{ number_format($order->discount, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="text-end fw-semibold">Tax:</td>
                                        <td class="text-end">${{ number_format($order->tax, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-semibold">Shipping:</td>
                                        <td class="text-end">${{ number_format($order->shipping_cost, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold fs-5">${{ number_format($order->total, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                @if($order->shippingAddress)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>{{ $order->shippingAddress->name }}</strong></p>
                                <p class="mb-1">{{ $order->shippingAddress->phone ?: '' }}</p>
                                <p class="mb-1">{{ $order->shippingAddress->email ?: '' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">{{ $order->shippingAddress->address_line1 }}</p>
                                @if($order->shippingAddress->address_line2)
                                    <p class="mb-1">{{ $order->shippingAddress->address_line2 }}</p>
                                @endif
                                <p class="mb-1">
                                    {{ $order->shippingAddress->city }},
                                    {{ $order->shippingAddress->state ? $order->shippingAddress->state . ', ' : '' }}
                                    {{ $order->shippingAddress->zip ?: '' }}
                                </p>
                                <p class="mb-0">{{ $order->shippingAddress->country }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Billing Address --}}
                @if($order->billingAddress)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Billing Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>{{ $order->billingAddress->name }}</strong></p>
                                <p class="mb-1">{{ $order->billingAddress->phone ?: '' }}</p>
                                <p class="mb-1">{{ $order->billingAddress->email ?: '' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">{{ $order->billingAddress->address_line1 }}</p>
                                @if($order->billingAddress->address_line2)
                                    <p class="mb-1">{{ $order->billingAddress->address_line2 }}</p>
                                @endif
                                <p class="mb-1">
                                    {{ $order->billingAddress->city }},
                                    {{ $order->billingAddress->state ? $order->billingAddress->state . ', ' : '' }}
                                    {{ $order->billingAddress->zip ?: '' }}
                                </p>
                                <p class="mb-0">{{ $order->billingAddress->country }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Timestamps --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Timestamps</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Created:</div>
                            <div class="col-md-9">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-3 fw-semibold text-muted">Updated:</div>
                            <div class="col-md-9">{{ $order->updated_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden order ID for JS --}}
<input type="hidden" id="orderId" value="{{ $order->id }}">

@push('scripts')
<script>
    (function() {
        'use strict';

        const orderId = document.getElementById('orderId').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Update Order Status
        document.querySelectorAll('.update-status-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const status = this.dataset.status;

                Alert.confirm(`Change order status to "${status}"?`, {
                    title: 'Update Status',
                    type: 'info',
                    confirmText: 'Yes, update'
                }).then(confirmed => {
                    if (!confirmed) return;

                    axios.post(`{{ url('/orders') }}/${orderId}/status`, {
                        status: status
                    })
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Update failed.');
                    });
                });
            });
        });

        // Update Payment Status
        document.querySelectorAll('.update-payment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const paymentStatus = this.dataset.payment_status;
                const transactionGroup = document.querySelector('.transaction-id-group');

                // If marking as paid, show transaction ID input
                if (paymentStatus === 'paid') {
                    transactionGroup.style.display = 'block';
                    document.getElementById('confirmTransactionBtn').onclick = function() {
                        const transactionId = document.getElementById('transactionId').value.trim();
                        updatePaymentStatus(paymentStatus, transactionId);
                    };
                } else {
                    updatePaymentStatus(paymentStatus, null);
                }
            });
        });

        function updatePaymentStatus(paymentStatus, transactionId) {
            const data = { payment_status: paymentStatus };
            if (transactionId) {
                data.transaction_id = transactionId;
            }

            Alert.confirm(`Change payment status to "${paymentStatus.replace(/_/g, ' ')}"?`, {
                title: 'Update Payment',
                type: 'info',
                confirmText: 'Yes, update'
            }).then(confirmed => {
                if (!confirmed) return;

                axios.post(`{{ url('/orders') }}/${orderId}/payment-status`, data)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Update failed.');
                    });
            });
        }

        // Process Refund
        const refundBtn = document.getElementById('refundOrderBtn');
        if (refundBtn) {
            refundBtn.addEventListener('click', function() {
                const note = document.getElementById('refundNote')?.value || '';

                Alert.confirm('This will mark the order as refunded and restore stock. This action cannot be undone.', {
                    title: 'Process Refund?',
                    type: 'danger',
                    confirmText: 'Yes, refund order'
                }).then(confirmed => {
                    if (!confirmed) return;

                    axios.post(`{{ url('/orders') }}/${orderId}/refund`, {
                        note: note
                    })
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Refund failed.');
                    });
                });
            });
        }
    })();
</script>
@endpush
@endsection


