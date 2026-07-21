@extends('layout.master-layout')

@section('title', 'Dashboard')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="h-100">
                    {{-- Welcome Header --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                <div class="flex-grow-1">
                                    <h4 class="fs-16 mb-1">
                                        Good {{ now()->format('H') < 12 ? 'Morning' : (now()->format('H') < 17 ? 'Afternoon' : 'Evening') }},
                                        {{ $admin?->profile?->name ?? 'Admin' }}!
                                    </h4>
                                    <p class="text-muted mb-0">Here's what's happening on your platform today.</p>
                                </div>
                                <div class="mt-3 mt-lg-0">
                                    <a href="{{ route('admin.profile.index') }}" class="btn btn-soft-success material-shadow-none btn-sm">
                                        <i class="ri-user-settings-line align-middle me-1"></i> My Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 1: Core Business Stats --}}
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Revenue</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                                                +{{ number_format($stats['today_revenue'], 0) }} today
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                $<span class="counter-value" data-target="{{ (int) $stats['total_revenue'] }}">{{ number_format($stats['total_revenue'], 2) }}</span>
                                            </h4>
                                            <a href="{{ route('admin.orders.index') }}" class="text-decoration-underline">View orders</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                                <i class="bx bx-dollar-circle text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Orders</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($stats['pending_orders'] > 0)
                                                <span class="badge bg-warning-subtle text-warning">{{ $stats['pending_orders'] }} pending</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value" data-target="{{ $stats['total_orders'] }}">{{ number_format($stats['total_orders']) }}</span>
                                            </h4>
                                            <a href="{{ route('admin.orders.index') }}" class="text-decoration-underline">View all orders</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                                <i class="bx bx-shopping-bag text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Users</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                                                {{ $stats['active_users'] }} active
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value" data-target="{{ $stats['total_users'] }}">{{ number_format($stats['total_users']) }}</span>
                                            </h4>
                                            <span class="text-muted small">
                                                {{ $stats['artist_users'] }} Artists &middot; {{ $stats['business_users'] }} Businesses
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-user-circle text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Products</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                                                {{ $stats['active_products'] }} active
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                <span class="counter-value" data-target="{{ $stats['total_products'] }}">{{ number_format($stats['total_products']) }}</span>
                                            </h4>
                                            <a href="{{ route('admin.products.index') }}" class="text-decoration-underline">Manage products</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                                <i class="bx bx-package text-warning"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Engagement & Content Stats --}}
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Events</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($stats['upcoming_events'] > 0)
                                                <span class="badge bg-success-subtle text-success">{{ $stats['upcoming_events'] }} upcoming</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ number_format($stats['total_events']) }}</h4>
                                            <a href="{{ route('admin.events.index') }}" class="text-decoration-underline">Manage events</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-danger-subtle rounded fs-3">
                                                <i class="bx bx-calendar-event text-danger"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Event Registrations</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-info fs-14 mb-0">{{ $stats['confirmed_registrations'] }} confirmed</h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ number_format($stats['total_registrations']) }}</h4>
                                            <a href="{{ route('admin.events.registrations.index') }}" class="text-decoration-underline">View registrations</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                                <i class="bx bx-tag text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Businesses</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-success-subtle text-success">{{ $stats['active_businesses'] }} active</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ number_format($stats['total_businesses']) }}</h4>
                                            <a href="{{ route('admin.businesses.index') }}" class="text-decoration-underline">Manage businesses</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-secondary-subtle rounded fs-3">
                                                <i class="bx bx-store-alt text-secondary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Subscribers</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-info-subtle text-info">{{ $stats['unread_contacts'] }} new messages</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ number_format($stats['total_subscribers']) }}</h4>
                                            <a href="{{ route('admin.newsletters.index') }}" class="text-decoration-underline">Manage subscribers</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-pink-subtle rounded fs-3">
                                                <i class="bx bx-mail-send text-pink"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: Spotlight & Contest Stats --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="fs-14 text-muted text-uppercase mb-3">Spotlight &amp; Contest</h5>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate border border-warning border-opacity-25">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Active Seasons</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($stats['active_seasons'] > 0)
                                                <span class="badge bg-success"><i class="ri-check-line"></i> Running</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-3">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($stats['active_seasons']) }}</h4>
                                            <small class="text-muted">{{ $stats['contest_applications'] }} total applications</small>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                                <i class="bx bx-trophy text-warning"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate border border-warning border-opacity-25">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pending Applications</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($stats['pending_applications'] > 0)
                                                <span class="badge bg-warning">{{ $stats['pending_applications'] }} pending</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-3">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($stats['pending_applications']) }}</h4>
                                            <a href="{{ route('admin.contest-applications.index') }}" class="text-decoration-underline small">Review now</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                                <i class="bx bx-file text-warning"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate border border-primary border-opacity-25">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Spotlight Reviews</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if(($stats['pending_artist_spotlights'] + $stats['pending_business_spotlights']) > 0)
                                                <span class="badge bg-primary">{{ $stats['pending_artist_spotlights'] + $stats['pending_business_spotlights'] }} pending</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-3">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-0">
                                                {{ number_format($stats['pending_artist_spotlights'] + $stats['pending_business_spotlights']) }}
                                            </h4>
                                            <small class="text-muted">{{ $stats['pending_artist_spotlights'] }} artists / {{ $stats['pending_business_spotlights'] }} businesses</small>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-star text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate border border-primary border-opacity-25">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Purchases</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-cart text-primary fs-20"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-3">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ number_format($stats['total_spotlight_purchases']) }}</h4>
                                            <a href="{{ route('admin.spotlight.vote-purchases.index') }}" class="text-decoration-underline small">View all</a>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                <i class="bx bx-cart text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ─── Dedicated Vote Purchases Widget ─── --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="fs-14 text-muted text-uppercase mb-3">
                                <i class="ri-shopping-cart-2-line me-1"></i> Vote Purchase Monitor
                            </h5>
                        </div>

                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    {{-- Status counters row --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-xl-3 col-md-6">
                                            <div class="bg-warning-subtle p-3 rounded-3 border border-warning border-opacity-10">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-sm">
                                                            <span class="avatar-title bg-warning rounded-circle fs-5">
                                                                <i class="ri-time-line text-white"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-muted mb-0 small">Pending</p>
                                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['pending_spotlight_purchases']) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-md-6">
                                            <div class="bg-success-subtle p-3 rounded-3 border border-success border-opacity-10">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-sm">
                                                            <span class="avatar-title bg-success rounded-circle fs-5">
                                                                <i class="ri-check-double-line text-white"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-muted mb-0 small">Completed</p>
                                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['completed_spotlight_purchases']) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-md-6">
                                            <div class="bg-danger-subtle p-3 rounded-3 border border-danger border-opacity-10">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-sm">
                                                            <span class="avatar-title bg-danger rounded-circle fs-5">
                                                                <i class="ri-refund-2-line text-white"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-muted mb-0 small">Refunded</p>
                                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['refunded_spotlight_purchases']) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-md-6">
                                            <div class="bg-primary-subtle p-3 rounded-3 border border-primary border-opacity-10">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-sm">
                                                            <span class="avatar-title bg-primary rounded-circle fs-5">
                                                                <i class="ri-money-dollar-circle-line text-white"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="text-muted mb-0 small">Revenue</p>
                                                        <h4 class="mb-0 fw-bold">${{ number_format($stats['spotlight_purchase_revenue'], 2) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        {{-- Package Breakdown --}}
                                        <div class="col-xl-4">
                                            <div class="border rounded-3 p-3 h-100">
                                                <h6 class="fw-semibold mb-3"><i class="ri-bar-chart-grouped-line me-1 text-primary"></i> Package Breakdown</h6>
                                                @php
                                                    $pkgBarColors = ['starter' => 'bg-secondary', 'popular' => 'bg-info', 'boost' => 'bg-primary', 'power' => 'bg-success'];
                                                @endphp
                                                @foreach($votePackages as $key => $pkg)
                                                    @php
                                                        $breakdown = $votePackageBreakdown->get($key);
                                                        $total = $breakdown->total ?? 0;
                                                        $votes = $breakdown->votes ?? 0;
                                                        $revenue = $breakdown->revenue ?? 0;
                                                        $maxTotal = max($votePackageBreakdown->max('total') ?? 1, 1);
                                                        $barWidth = ($total / $maxTotal) * 100;
                                                    @endphp
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="small fw-medium">{{ $pkg['label'] }}</span>
                                                            <span class="small text-muted">{{ $total }} purchases &middot; {{ $votes }} votes</span>
                                                        </div>
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar {{ $pkgBarColors[$key] ?? 'bg-secondary' }}" role="progressbar" style="width: {{ $barWidth }}%" aria-valuenow="{{ $total }}" aria-valuemin="0" aria-valuemax="{{ $maxTotal }}"></div>
                                                        </div>
                                                        <small class="text-muted">${{ number_format($revenue, 2) }} earned</small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Recent Pending Purchases --}}
                                        <div class="col-xl-8">
                                            <div class="border rounded-3 p-3 h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <h6 class="fw-semibold mb-0"><i class="ri-alarm-warning-line me-1 text-warning"></i> Recent Pending Purchases</h6>
                                                    <a href="{{ route('admin.spotlight.vote-purchases.index') }}" class="btn btn-soft-primary btn-sm material-shadow-none">
                                                        <i class="ri-arrow-right-line align-middle"></i> View All
                                                    </a>
                                                </div>

                                                @if($recentPendingPurchases->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-centered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>User</th>
                                                                <th>Nominee</th>
                                                                <th>Package</th>
                                                                <th class="text-center">Votes</th>
                                                                <th class="text-center">Amount</th>
                                                                <th class="text-center">When</th>
                                                                <th class="text-center">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($recentPendingPurchases as $purchase)
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="flex-shrink-0 me-2">
                                                                            <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                                                <i class="ri-user-line text-muted fs-14"></i>
                                                                            </div>
                                                                        </div>
                                                                        <span class="small fw-medium">{{ $purchase['user_name'] }}</span>
                                                                    </div>
                                                                </td>
                                                                <td><span class="small">{{ $purchase['nominee_name'] }}</span></td>
                                                                <td>
                                                                    @php
                                                                        $pkgLabel = $votePackages[$purchase['package']]['label'] ?? $purchase['package'];
                                                                    @endphp
                                                                    <span class="small">{{ $pkgLabel }}</span>
                                                                </td>
                                                                <td class="text-center fw-medium">{{ $purchase['votes'] }}</td>
                                                                <td class="text-center text-success fw-medium">${{ number_format($purchase['amount'], 2) }}</td>
                                                                <td class="text-center text-muted small">{{ $purchase['created_at']->diffForHumans() }}</td>
                                                                <td class="text-center">
                                                                    <a href="{{ route('admin.spotlight.vote-purchases.show', $purchase['id']) }}" class="btn btn-soft-info btn-sm material-shadow-none" title="Review">
                                                                        <i class="ri-eye-line"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                @else
                                                <div class="text-center py-4">
                                                    <i class="ri-inbox-line fs-36 text-muted"></i>
                                                    <p class="text-muted small mt-2 mb-0">No pending purchases. All caught up!</p>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Footer link --}}
                                    <div class="text-end mt-3">
                                        <a href="{{ route('admin.spotlight.vote-purchases.index') }}" class="btn btn-outline-primary btn-sm material-shadow-none">
                                            <i class="ri-shopping-cart-2-line me-1"></i> Manage All Vote Purchases
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Monthly Revenue/Orders Chart --}}
                        <div class="col-xl-7">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex flex-wrap gap-2">
                                    <h4 class="card-title mb-0 flex-grow-1">Monthly Overview</h4>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Chart range">
                                        @foreach($allowedRanges as $range)
                                            <a href="{{ request()->fullUrlWithQuery(['range' => $range]) }}"
                                               class="btn {{ $selectedRange === $range ? 'btn-primary' : 'btn-soft-secondary' }} material-shadow-none">
                                                {{ $range }}M
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-header p-0 border-0 bg-light-subtle">
                                    <div class="row g-0 text-center">
                                        <div class="col-6 col-sm-3">
                                            <div class="p-3 border border-dashed border-start-0">
                                                <h5 class="mb-1"><span class="counter-value" data-target="{{ $stats['total_orders'] }}">{{ number_format($stats['total_orders']) }}</span></h5>
                                                <p class="text-muted mb-0">Total Orders</p>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="p-3 border border-dashed border-start-0">
                                                <h5 class="mb-1">$<span class="counter-value" data-target="{{ (int) $stats['total_revenue'] }}">{{ number_format($stats['total_revenue']) }}</span></h5>
                                                <p class="text-muted mb-0">Total Revenue</p>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="p-3 border border-dashed border-start-0">
                                                <h5 class="mb-1"><span class="counter-value" data-target="{{ $stats['total_products'] }}">{{ number_format($stats['total_products']) }}</span></h5>
                                                <p class="text-muted mb-0">Products</p>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="p-3 border border-dashed border-start-0 border-end-0">
                                                <h5 class="mb-1">{{ $stats['total_registrations'] }}</h5>
                                                <p class="text-muted mb-0">Registrations</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="monthlyChart" class="apex-charts" dir="ltr"
                                        data-months='@json($months->pluck('label'))'
                                        data-orders='@json($months->pluck('orders'))'
                                        data-revenue='@json($months->pluck('revenue'))'>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Inbox / Quick Stats --}}
                        <div class="col-xl-5">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Quick Stats</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-calendar-event-line text-primary me-2"></i> Published Events</td>
                                                    <td class="text-end fw-medium">{{ number_format($stats['published_events']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-store-2-line text-success me-2"></i> Active Businesses</td>
                                                    <td class="text-end fw-medium">{{ number_format($stats['active_businesses']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-mail-open-line text-warning me-2"></i> Unread Messages</td>
                                                    <td class="text-end fw-medium">
                                                        @if($stats['unread_contacts'] > 0)
                                                            <span class="text-warning">{{ number_format($stats['unread_contacts']) }}</span>
                                                        @else
                                                            0
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-user-star-line text-info me-2"></i> Artist Spotlights</td>
                                                    <td class="text-end fw-medium">{{ number_format($stats['total_artist_spotlights']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-trophy-line text-danger me-2"></i> Active Seasons</td>
                                                    <td class="text-end fw-medium">{{ number_format($stats['active_seasons']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-file-list-3-line text-secondary me-2"></i> Contest Applications</td>
                                                    <td class="text-end fw-medium">{{ number_format($stats['contest_applications']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-star-line text-primary me-2"></i> Spotlight Voting Weeks</td>
                                                    <td class="text-end fw-medium">{{ number_format($stats['active_spotlight_weeks']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-0"><i class="ri-user-3-line text-success me-2"></i> Total Users</td>
                                                    <td class="text-end fw-medium">{{ number_format($stats['total_users']) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Pending Reviews Card --}}
                            @if($stats['pending_artist_spotlights'] > 0 || $stats['pending_business_spotlights'] > 0 || $stats['pending_applications'] > 0 || $stats['pending_spotlight_purchases'] > 0)
                            <div class="card border border-warning border-opacity-25">
                                <div class="card-header bg-warning-subtle border-0">
                                    <h5 class="card-title mb-0 text-warning"><i class="ri-alert-line me-1"></i> Needs Your Attention</h5>
                                </div>
                                <div class="card-body py-2">
                                    <div class="list-group list-group-flush">
                                        @if($stats['pending_applications'] > 0)
                                        <a href="{{ route('admin.contest-applications.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 border-bottom-dashed">
                                            <span><i class="ri-file-list-3-line me-2 text-warning"></i> Contest Applications</span>
                                            <span class="badge bg-warning rounded-pill">{{ $stats['pending_applications'] }}</span>
                                        </a>
                                        @endif
                                        @if($stats['pending_artist_spotlights'] > 0)
                                        <a href="{{ route('admin.artist-spotlights.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 border-bottom-dashed">
                                            <span><i class="ri-user-star-line me-2 text-primary"></i> Artist Spotlights</span>
                                            <span class="badge bg-primary rounded-pill">{{ $stats['pending_artist_spotlights'] }}</span>
                                        </a>
                                        @endif
                                        @if($stats['pending_business_spotlights'] > 0)
                                        <a href="{{ route('admin.business-spotlights.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 border-bottom-dashed">
                                            <span><i class="ri-store-2-line me-2 text-success"></i> Business Spotlights</span>
                                            <span class="badge bg-success rounded-pill">{{ $stats['pending_business_spotlights'] }}</span>
                                        </a>
                                        @endif
                                        @if($stats['pending_spotlight_purchases'] > 0)
                                        <a href="{{ route('admin.spotlight.vote-purchases.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                                            <span><i class="ri-shopping-cart-2-line me-2 text-info"></i> Vote Purchases</span>
                                            <span class="badge bg-info rounded-pill">{{ $stats['pending_spotlight_purchases'] }}</span>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Recent Orders Table --}}
                    <div class="row mt-3">
                        <div class="col-xl-7">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Recent Orders</h4>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('admin.orders.index') }}" class="btn btn-soft-info btn-sm material-shadow-none">
                                            <i class="ri-arrow-right-line align-middle"></i> View All
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($recentOrders->count() > 0)
                                    <div class="table-responsive table-card">
                                        <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                            <thead class="text-muted table-light">
                                                <tr>
                                                    <th scope="col">Order #</th>
                                                    <th scope="col">Customer</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Payment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentOrders as $order)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.orders.show', $order['id']) }}" class="fw-medium link-primary">
                                                            #{{ $order['order_number'] }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                                    <i class="ri-user-line text-muted"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">{{ $order['customer_name'] }}</div>
                                                        </div>
                                                    </td>
                                                    <td><span class="text-success">${{ number_format($order['total'], 2) }}</span></td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'pending' => 'bg-warning-subtle text-warning',
                                                                'confirmed' => 'bg-info-subtle text-info',
                                                                'processing' => 'bg-primary-subtle text-primary',
                                                                'shipped' => 'bg-secondary-subtle text-secondary',
                                                                'delivered' => 'bg-success-subtle text-success',
                                                                'cancelled' => 'bg-danger-subtle text-danger',
                                                                'refunded' => 'bg-dark-subtle text-dark',
                                                            ];
                                                            $colorClass = $statusColors[$order['status']] ?? 'bg-secondary-subtle text-secondary';
                                                        @endphp
                                                        <span class="badge {{ $colorClass }}">{{ ucfirst($order['status']) }}</span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $paymentColors = [
                                                                'paid' => 'bg-success-subtle text-success',
                                                                'unpaid' => 'bg-danger-subtle text-danger',
                                                                'refunded' => 'bg-dark-subtle text-dark',
                                                                'partially_refunded' => 'bg-warning-subtle text-warning',
                                                            ];
                                                            $pColor = $paymentColors[$order['payment_status']] ?? 'bg-secondary-subtle text-secondary';
                                                        @endphp
                                                        <span class="badge {{ $pColor }}">{{ ucfirst(str_replace('_', ' ', $order['payment_status'])) }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-4">
                                        <i class="ri-inbox-line fs-48 text-muted"></i>
                                        <p class="mt-2 text-muted">No orders yet.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Recent Contacts --}}
                        <div class="col-xl-5">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Recent Messages</h4>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('admin.contacts.index') }}" class="btn btn-soft-info btn-sm material-shadow-none">
                                            <i class="ri-arrow-right-line align-middle"></i> View All
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($recentContacts->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-centered table-hover align-middle table-nowrap mb-0">
                                            <tbody>
                                                @foreach($recentContacts as $contact)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                                    <i class="ri-user-line text-muted"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="fs-14 my-1 fw-medium">{{ $contact['name'] }}</h6>
                                                                <span class="text-muted small">{{ $contact['email'] }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted small">{{ \Illuminate\Support\Str::limit($contact['subject'], 30) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($contact['status'] === 'pending')
                                                            <span class="badge bg-warning-subtle text-warning">New</span>
                                                        @elseif($contact['status'] === 'read')
                                                            <span class="badge bg-info-subtle text-info">Read</span>
                                                        @else
                                                            <span class="badge bg-success-subtle text-success">Replied</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted small text-end">{{ $contact['created_at']->diffForHumans() }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-4">
                                        <i class="ri-mail-open-line fs-48 text-muted"></i>
                                        <p class="mt-2 text-muted">No messages yet.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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

        @if(session('info'))
            Toast.info(@json(session('info')));
        @endif

        // ── Monthly Overview Chart (ApexCharts) ────────────────
        var chartEl = document.getElementById('monthlyChart');
        if (chartEl && typeof ApexCharts !== 'undefined') {
            var months = JSON.parse(chartEl.dataset.months || '[]');
            var ordersData = JSON.parse(chartEl.dataset.orders || '[]');
            var revenueData = JSON.parse(chartEl.dataset.revenue || '[]');

            var options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                },
                series: [
                    {
                        name: 'Orders',
                        type: 'column',
                        data: ordersData,
                    },
                    {
                        name: 'Revenue ($)',
                        type: 'line',
                        data: revenueData.map(function(v) { return Math.round(v); }),
                    }
                ],
                xaxis: {
                    categories: months,
                    axisBorder: { show: false },
                },
                yaxis: [
                    {
                        title: { text: 'Orders' },
                        min: 0,
                    },
                    {
                        opposite: true,
                        title: { text: 'Revenue ($)' },
                        min: 0,
                    }
                ],
                stroke: {
                    width: [0, 3],
                    curve: 'smooth',
                },
                colors: ['#0ab39c', '#405189'],
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        borderRadius: 4,
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                },
                legend: {
                    position: 'top',
                },
                dataLabels: {
                    enabled: false,
                },
            };

            var chart = new ApexCharts(chartEl, options);
            chart.render();
        }

    })();
</script>
@endpush
