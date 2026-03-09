@extends('layout.master-layout')
@section('title', 'User Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">User Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        {{-- Stats Cards --}}
        <div class="row">
            <x-admin.stats-card icon="ri-team-line" label="Total Users" :count="$totalUsers" color="primary" />
            <x-admin.stats-card icon="ri-user-follow-line" label="Active Users" :count="$activeUsers" color="success" />
            <x-admin.stats-card icon="ri-user-unfollow-line" label="Inactive Users" :count="$inactiveUsers" color="danger" />
            <x-admin.stats-card icon="ri-shield-user-line" label="Total Roles" :count="$totalRoles" color="warning" />
        </div>

        {{-- Filters & Table --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">All Users</h5>
                        @can('create users')
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="ri-add-line align-bottom me-1"></i> Add New User
                        </a>
                        @endcan
                    </div>

                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm">
                            <div class="row g-3">
                                <div class="col-xl-4 col-md-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control search" name="search"
                                               id="searchInput"
                                               placeholder="Search by name or email..."
                                               value="{{ request('search') }}">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-4">
                                    <select class="form-select" name="role" onchange="document.getElementById('filterForm').submit()">
                                        <option value="">All Roles</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                                {{ ucfirst($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-3 col-md-4">
                                    <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-soft-danger w-100">
                                        <i class="ri-refresh-line me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 46px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th>User</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input user-checkbox" type="checkbox"
                                                           value="{{ $user->id }}"
                                                           {{ $user->hasRole('super-admin') ? 'disabled' : '' }}>
                                                </div>
                                            </th>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <x-admin.avatar :src="$user->profile?->avatar" :name="$user->profile?->name ?? $user->email" size="sm" />
                                                    <div class="ms-3">
                                                        <h5 class="fs-14 mb-1">
                                                            <a href="{{ route('admin.users.show', $user) }}" class="text-body">
                                                                {{ $user->profile?->name ?? 'No name' }}
                                                            </a>
                                                        </h5>
                                                        <p class="text-muted mb-0 fs-12">{{ $user->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($user->roles as $role)
                                                    <x-admin.role-badge :role="$role->name" />
                                                @endforeach
                                            </td>
                                            <td>
                                                <div class="form-check form-switch form-switch-md">
                                                    <input class="form-check-input status-toggle"
                                                           type="checkbox"
                                                           data-user-id="{{ $user->id }}"
                                                           data-url="{{ route('admin.users.toggle-status', $user) }}"
                                                           {{ $user->status === 'active' ? 'checked' : '' }}
                                                           {{ $user->id === auth('admin')->id() ? 'disabled' : '' }}
                                                           {{ $user->hasRole('super-admin') && !auth('admin')->user()->hasRole('super-admin') ? 'disabled' : '' }}>
                                                </div>
                                            </td>
                                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <x-admin.action-buttons
                                                    :showUrl="route('admin.users.show', $user)"
                                                    :editUrl="route('admin.users.edit', $user)"
                                                    :deleteUrl="route('admin.users.destroy', $user)"
                                                    :deletable="!$user->hasRole('super-admin') && $user->id !== auth('admin')->id()"
                                                    :itemName="$user->profile?->name ?? $user->email"
                                                />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="avatar-md mx-auto mb-3">
                                                    <div class="avatar-title bg-light text-primary rounded-circle fs-1">
                                                        <i class="ri-user-line"></i>
                                                    </div>
                                                </div>
                                                <h5 class="mt-2">No Users Found</h5>
                                                <p class="text-muted mb-0">Try adjusting your search or filters.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($users->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<x-admin.confirm-delete-modal />

<script>
(function() {
    'use strict';

    // ── Search Debounce ──
    let searchTimer;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 400);
        });
    }

    // ── Check All ──
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.user-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = checkAll.checked;
            });
        });
    }

    // ── Status Toggle (AJAX) ──
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const url = this.dataset.url;
            const checkbox = this;

            axios.post(url)
                .then(function(res) {
                    Toast.fromResponse(res.data);
                })
                .catch(function(err) {
                    // Revert toggle on error
                    checkbox.checked = !checkbox.checked;
                    const data = err.response?.data;
                    Toast.error(data?.message || 'Failed to change status.');
                });
        });
    });
})();
</script>
@endpush
