@extends('layout.master-layout')
@section('title', 'User Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">User Details</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                            <li class="breadcrumb-item active">{{ $user->profile?->name ?? $user->email }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- User Profile Card --}}
            <div class="col-xxl-3">
                <div class="card">
                    <div class="card-body p-4 text-center">
                        <div class="mx-auto mb-4">
                            @if($user->profile?->avatar)
                                {{-- profiles.avatar already stores the full "storage/..." path --}}
                                <img src="{{ asset($user->profile->avatar) }}" alt="Avatar"
                                     class="rounded-circle avatar-xl img-thumbnail">
                            @else
                                @php
                                    $name = $user->profile?->name ?? $user->email;
                                    $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
                                @endphp
                                <div class="avatar-xl mx-auto">
                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-1">
                                        {{ $initials }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <h5 class="fs-16 mb-1">{{ $user->profile?->name ?? 'No Name' }}</h5>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                        @if($user->phone)
                            <p class="text-muted mb-0 fs-13">{{ $user->phone }}</p>
                        @endif

                        <div class="mt-3">
                            <x-admin.status-badge :status="$user->status" />
                        </div>

                        <div class="mt-3">
                            @foreach($user->roles as $role)
                                <x-admin.role-badge :role="$role->name" />
                            @endforeach
                        </div>

                        <p class="text-muted mt-3 mb-0 fs-12">
                            Member since {{ $user->created_at->format('M d, Y') }}
                        </p>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="card-footer text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-soft-primary">
                                <i class="ri-pencil-line me-1"></i> Edit
                            </a>
                            @if($user->id !== auth('admin')->id() && !$user->hasRole('super-admin'))
                                <button type="button" class="btn btn-sm btn-soft-danger btn-delete"
                                        data-url="{{ route('admin.users.destroy', $user) }}"
                                        data-name="{{ $user->profile?->name ?? $user->email }}">
                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permissions & Details --}}
            <div class="col-xxl-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Permissions</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $grouped = \App\Helpers\PermissionHelper::groupPermissionsByModule($allPermissions);
                        @endphp

                        <div class="row g-3">
                            @foreach($grouped as $module => $perms)
                                <div class="col-md-4 col-sm-6">
                                    <div class="card border shadow-none mb-0">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0 fs-13">{{ $module }}</h6>
                                        </div>
                                        <div class="card-body py-2">
                                            @foreach($perms as $perm)
                                                <div class="d-flex align-items-center mb-1">
                                                    @if(in_array($perm->name, $userPermissions))
                                                        <i class="ri-checkbox-circle-fill text-success me-2 fs-16"></i>
                                                    @else
                                                        <i class="ri-close-circle-fill text-muted me-2 fs-16"></i>
                                                    @endif
                                                    <span class="fs-13">{{ $perm->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(empty($grouped))
                            <div class="text-center py-4">
                                <i class="ri-shield-line fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No permissions available.</p>
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
@endpush
