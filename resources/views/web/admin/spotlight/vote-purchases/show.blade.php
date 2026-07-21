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
                                                    'pending'   => 'bg-warning-subtle text-warning',
                                                    'approved'  => 'bg-info-subtle text-info',
                                                    'paid'      => 'bg-success-subtle text-success',
                                                    'refunded'  => 'bg-danger-subtle text-danger',
                                                    'cancelled' => 'bg-secondary-subtle text-secondary',
                                                ];
                                                $class = $statusMap[$purchase->status] ?? 'bg-secondary-subtle text-secondary';
                                            @endphp
                                            <span class="badge {{ $class }} fs-6">{{ ucfirst($purchase->status) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Package</th>
                                        <td>
                                            {{ $purchase->package?->name ?? $purchase->package }}
                                            <br><small class="text-muted">{{ $purchase->package?->description ?? '' }}</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Votes Count</th>
                                        <td><strong>{{ number_format($purchase->votes_count) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Amount</th>
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
                                    @if($purchase->paid_at)
                                    <tr>
                                        <th class="table-light">Paid At</th>
                                        <td>{{ $purchase->paid_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    @endif
                                    @if($purchase->stripe_payment_intent_id)
                                    <tr>
                                        <th class="table-light">Stripe Payment Intent</th>
                                        <td><code>{{ $purchase->stripe_payment_intent_id }}</code></td>
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

                {{-- Stripe Checkout Session Info --}}
                @if($purchase->stripe_checkout_session_id)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment Session</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;" class="table-light">Checkout Session ID</th>
                                        <td><code>{{ $purchase->stripe_checkout_session_id }}</code></td>
                                    </tr>
                                    @if($purchase->stripe_payment_intent_id)
                                    <tr>
                                        <th class="table-light">Payment Intent</th>
                                        <td><code>{{ $purchase->stripe_payment_intent_id }}</code></td>
                                    </tr>
                                    @endif
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
                            $isArtist = $spotlightable && $spotlightable instanceof \App\Models\ArtistSpotlight;
                            $name = $spotlightable
                                ? ($isArtist
                                    ? ($spotlightable->artist_stage_name ?? $spotlightable->full_legal_name)
                                    : ($spotlightable->business_name ?? $spotlightable->owner_founder_name))
                                : '—';
                        @endphp
                        <h6>{{ $name }}</h6>
                        @if($purchase->nominee)
                            <p class="text-muted mb-1">
                                Total Votes: <strong>{{ number_format($purchase->nominee->total_vote_count) }}</strong>
                            </p>
                            <p class="text-muted mb-1">
                                Free Votes: <strong>{{ number_format($purchase->nominee->free_vote_count) }}</strong>
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
                        {{-- Approve (pending → approved) --}}
                        <form action="{{ route('admin.spotlight.vote-purchases.approve', $purchase->id) }}" method="POST" class="mb-3" id="approveForm">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Admin Notes (optional)</label>
                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Approval notes..."></textarea>
                            </div>
                            <button type="button" class="btn btn-info w-100 btn-swal-confirm" data-form="approveForm">
                                <i class="ri-check-double-line me-1"></i> Approve (Allow Payment)
                            </button>
                        </form>

                        {{-- Cancel (pending → cancelled) --}}
                        <form action="{{ route('admin.spotlight.vote-purchases.refund', $purchase->id) }}" method="POST" id="rejectForm">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Reason (optional)</label>
                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Cancellation reason..."></textarea>
                            </div>
                            <button type="button" class="btn btn-danger w-100 btn-swal-confirm" data-form="rejectForm">
                                <i class="ri-close-line me-1"></i> Reject / Cancel
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Paid purchase — show refund option --}}
                @if($purchase->isPaid())
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.spotlight.vote-purchases.refund', $purchase->id) }}" method="POST" id="refundForm">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Refund Reason</label>
                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Refund reason..."></textarea>
                            </div>
                            <button type="button" class="btn btn-danger w-100 btn-swal-confirm" data-form="refundForm">
                                <i class="ri-refund-2-line me-1"></i> Refund Purchase
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Approved By --}}
                @if($purchase->approver)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Approved / Processed By</h5>
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

                {{-- Package Info --}}
                @if($purchase->package instanceof \App\Models\Spotlight\SpotlightVotePackage || is_string($purchase->package))
                @php
                    $isPkgObject = $purchase->package instanceof \App\Models\Spotlight\SpotlightVotePackage;
                    $pkgName = $isPkgObject ? $purchase->package->name : ucfirst($purchase->package);
                    $pkgVotes = $isPkgObject ? $purchase->package->votes_count : $purchase->votes_count;
                    $pkgPrice = $isPkgObject ? $purchase->package->price : $purchase->amount_paid;
                @endphp
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Package Info</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $pkgName }}</strong></p>
                        <p class="mb-0 text-muted">
                            {{ $pkgVotes }} vote(s) — ${{ number_format($pkgPrice, 2) }}
                        </p>
                        @if($isPkgObject && $purchase->package->description)
                            <p class="mb-0 mt-1 text-muted small">{{ $purchase->package->description }}</p>
                        @endif
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

        document.querySelectorAll('.btn-swal-confirm').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                var formId = this.getAttribute('data-form');
                var form = document.getElementById(formId);
                if (!form) return;

                var isApprove = formId === 'approveForm';
                var isRefund  = formId === 'refundForm';

                var title   = isApprove ? 'Approve Purchase?' : (isRefund ? 'Refund Purchase?' : 'Cancel Purchase?');
                var message = isApprove
                    ? 'Approve this purchase? The user will then be able to pay via Stripe to credit <strong>{{ $purchase->votes_count }} votes</strong>.'
                    : (isRefund
                        ? 'Refund this purchase and remove <strong>{{ $purchase->votes_count }} votes</strong> from the nominee? This action cannot be undone.'
                        : 'Reject / cancel this purchase request?');
                var confirmText = isApprove ? 'Yes, approve' : (isRefund ? 'Yes, refund' : 'Yes, reject');
                var type = isApprove ? 'confirm' : 'danger';
                var icon = isApprove ? 'question' : 'warning';

                Alert.confirm(message, {
                    title       : title,
                    confirmText : confirmText,
                    type        : type,
                    icon        : icon,
                }).then(function (confirmed) {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
        });

    })();
</script>
@endpush
