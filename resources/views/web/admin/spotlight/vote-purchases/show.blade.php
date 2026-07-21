@extends('layout.master-layout')

@section('title', 'Vote Purchase Details')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Vote Purchase #{{ $purchase->id }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.spotlight.vote-purchases.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Back to Purchases
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Main Details --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Purchase Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;" class="table-light">Purchase ID</th>
                                        <td>#{{ $purchase->id }}</td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Status</th>
                                        <td>
                                            @php
                                                $statusMap = [
                                                    'pending' => 'bg-warning-subtle text-warning',
                                                    'completed' => 'bg-success-subtle text-success',
                                                    'refunded' => 'bg-danger-subtle text-danger',
                                                ];
                                                $class = $statusMap[$purchase->status] ?? 'bg-secondary-subtle text-secondary';
                                            @endphp
                                            <span class="badge {{ $class }} fs-6">{{ ucfirst($purchase->status) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Package</th>
                                        <td>{{ App\Models\Spotlight\SpotlightVotePurchase::packageDetails($purchase->package)['label'] ?? $purchase->package }}</td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Votes Count</th>
                                        <td><strong>{{ number_format($purchase->votes_count) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Amount Paid</th>
                                        <td><strong>${{ number_format($purchase->amount_paid, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Created At</th>
                                        <td>{{ $purchase->created_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    @if($purchase->approved_at)
                                    <tr>
                                        <th class="table-light">Approved At</th>
                                        <td>{{ $purchase->approved_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    @endif
                                    @if($purchase->admin_notes)
                                    <tr>
                                        <th class="table-light">Admin Notes</th>
                                        <td>{{ $purchase->admin_notes }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Order Details (if linked) --}}
                @if($purchase->order)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;" class="table-light">Order ID</th>
                                        <td>#{{ $purchase->order->id }}</td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Order Status</th>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">{{ ucfirst($purchase->order->status) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Total</th>
                                        <td>${{ number_format($purchase->order->total, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar: User & Nominee Info + Actions --}}
            <div class="col-lg-4">
                {{-- User Info --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">User</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $purchase->user?->profile?->avatar_url ?? asset('admin/assets/images/default/user.jpg') }}"
                            class="rounded-circle avatar-lg mb-3 object-fit-cover"
                            alt="User Avatar"
                            style="width: 80px; height: 80px;">
                        <h6>{{ $purchase->user?->profile?->name ?? '—' }}</h6>
                        <p class="text-muted mb-0">{{ $purchase->user?->email ?? '—' }}</p>
                    </div>
                </div>

                {{-- Nominee Info --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Nominee</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $spotlightable = $purchase->nominee?->spotlightable;
                            $name = $spotlightable?->business_name ?? $spotlightable?->brand_name ?? '—';
                        @endphp
                        <h6>{{ $name }}</h6>
                        @if($purchase->nominee)
                            <p class="text-muted mb-1">
                                Total Votes: <strong>{{ number_format($purchase->nominee->total_vote_count) }}</strong>
                            </p>
                            <p class="text-muted mb-0">
                                Paid Votes: <strong>{{ number_format($purchase->nominee->paid_vote_count) }}</strong>
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if($purchase->isPending())
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        {{-- Approve --}}
                        <form action="{{ route('admin.spotlight.vote-purchases.approve', $purchase->id) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Admin Notes (optional)</label>
                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Approval notes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this purchase and credit {{ $purchase->votes_count }} votes to the nominee?')">
                                <i class="ri-check-double-line me-1"></i> Approve & Credit Votes
                            </button>
                        </form>

                        {{-- Refund --}}
                        <form action="{{ route('admin.spotlight.vote-purchases.refund', $purchase->id) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Admin Notes (optional)</label>
                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Refund reason..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Refund this purchase and remove votes? This action can be reversed.')">
                                <i class="ri-refund-2-line me-1"></i> Refund
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Approved By --}}
                @if($purchase->approver)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Approved By</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <img src="{{ $purchase->approver?->profile?->avatar_url ?? asset('admin/assets/images/default/user.jpg') }}"
                                class="rounded-circle avatar-sm me-2 object-fit-cover"
                                alt="" style="width: 40px; height: 40px;">
                            <div>
                                <h6 class="mb-0">{{ $purchase->approver?->profile?->name ?? 'Admin' }}</h6>
                                @if($purchase->approved_at)
                                    <small class="text-muted">{{ $purchase->approved_at->format('M d, Y h:i A') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
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

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Toast.error('{{ $error }}');
            @endforeach
        @endif
    })();
</script>
@endpush
