@extends('layout.master-layout')
@section('title', 'Permission Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Permission Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Permissions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.flash-message />

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">All Permissions</h5>
                        @can('create permissions')
                        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                            <i class="ri-add-line align-bottom me-1"></i> Add Permission
                        </a>
                        @endcan
                    </div>

                    <div class="card-body">
                        @forelse($grouped as $module => $modulePermissions)
                            <div class="card border shadow-none mb-3">
                                <div class="card-header bg-light py-2 d-flex align-items-center justify-content-between"
                                     data-bs-toggle="collapse" data-bs-target="#group-{{ Str::slug($module) }}"
                                     role="button" style="cursor: pointer;">
                                    <h6 class="mb-0">
                                        <i class="ri-arrow-down-s-line me-1"></i>
                                        {{ $module }}
                                        <span class="badge bg-primary-subtle text-primary ms-2">{{ count($modulePermissions) }}</span>
                                    </h6>
                                </div>
                                <div class="collapse show" id="group-{{ Str::slug($module) }}">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0 align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Permission Name</th>
                                                        <th>Assigned Roles</th>
                                                        <th style="width: 100px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($modulePermissions as $permission)
                                                        @php
                                                            $perm = $permissions->firstWhere('name', $permission->name);
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                <x-admin.permission-badge :permission="$permission->name" />
                                                            </td>
                                                            <td>
                                                                @if($perm && $perm->roles->count() > 0)
                                                                    @foreach($perm->roles as $role)
                                                                        <x-admin.role-badge :role="$role->name" />
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted fs-12">Not assigned</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    @can('edit permissions')
                                                                    <a href="{{ route('admin.permissions.edit', $permission) }}"
                                                                       class="btn btn-sm btn-soft-primary" title="Edit">
                                                                        <i class="ri-pencil-line"></i>
                                                                    </a>
                                                                    @endcan
                                                                    @can('delete permissions')
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-soft-danger btn-delete"
                                                                            data-url="{{ route('admin.permissions.destroy', $permission) }}"
                                                                            data-name="{{ $permission->name }}"
                                                                            {{ $perm && $perm->roles->count() > 0 ? 'disabled' : '' }}
                                                                            title="{{ $perm && $perm->roles->count() > 0 ? 'Remove from all roles first' : 'Delete' }}">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                    @endcan
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="avatar-md mx-auto mb-3">
                                    <div class="avatar-title bg-light text-primary rounded-circle fs-1">
                                        <i class="ri-key-line"></i>
                                    </div>
                                </div>
                                <h5 class="mt-2">No Permissions Found</h5>
                                <p class="text-muted mb-0">Create your first permission to get started.</p>
                            </div>
                        @endforelse
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
