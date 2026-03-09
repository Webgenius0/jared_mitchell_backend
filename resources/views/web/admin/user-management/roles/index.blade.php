@extends('layout.master-layout')
@section('title', 'Role Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Role Management</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Roles</li>
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
                        <h5 class="card-title mb-0 flex-grow-1">All Roles</h5>
                        @can('create roles')
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                            <i class="ri-add-line align-bottom me-1"></i> Create Role
                        </a>
                        @endcan
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Permissions</th>
                                        <th>Users</th>
                                        <th>Guard</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $role)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($role->name === 'super-admin')
                                                        <i class="ri-lock-line text-danger"></i>
                                                    @endif
                                                    <x-admin.role-badge :role="$role->name" />
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $role->permissions_count }} permissions
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $role->users_count }} users
                                                </span>
                                            </td>
                                            <td><span class="text-muted">{{ $role->guard_name }}</span></td>
                                            <td>
                                                <x-admin.action-buttons
                                                    :editUrl="route('admin.roles.edit', $role)"
                                                    :deleteUrl="route('admin.roles.destroy', $role)"
                                                    :deletable="$role->name !== 'super-admin'"
                                                    :itemName="$role->name"
                                                />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="avatar-md mx-auto mb-3">
                                                    <div class="avatar-title bg-light text-primary rounded-circle fs-1">
                                                        <i class="ri-shield-user-line"></i>
                                                    </div>
                                                </div>
                                                <h5 class="mt-2">No Roles Found</h5>
                                                <p class="text-muted mb-0">Create your first role to get started.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($roles->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $roles->links() }}
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
