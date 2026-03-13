@extends('layout.master-layout')
@section('title', 'Edit Role')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit Role: {{ ucfirst($role->name) }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="editRoleForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name"
                                           value="{{ $role->name }}"
                                           {{ $role->name === 'super-admin' ? 'readonly' : '' }}>
                                    <div class="invalid-feedback" id="error-name"></div>
                                    @if($role->name === 'super-admin')
                                        <small class="text-danger">The super-admin role name cannot be changed.</small>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Select a Guard <span class="text-danger">*</span></label>
                                    <select class="form-control" name="guard_name" id="guard_name">
                                        <option value="">Select a Guard</option>
                                        <option value="admin" {{ $role->guard_name === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="api" {{ $role->guard_name === 'api' ? 'selected' : '' }}>Api</option>
                                        <option value="web" {{ $role->guard_name === 'web' ? 'selected' : '' }}>Web</option>
                                    </select>
                                    <div class="invalid-feedback" id="error-guard_name"></div>
                                </div>
                            </div>

                            {{-- Permissions Panel --}}
                            <div class="mt-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <label class="form-label fw-semibold mb-0">Assign Permissions</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                                        <label class="form-check-label fw-medium" for="selectAllPermissions">Select All</label>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    @foreach($groupedPermissions as $module => $permissions)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="card border shadow-none mb-0">
                                                <div class="card-header bg-light py-2 d-flex align-items-center justify-content-between">
                                                    <h6 class="mb-0 fs-13">{{ $module }}</h6>
                                                    <div class="form-check form-check-sm">
                                                        <input class="form-check-input select-group" type="checkbox"
                                                               data-group="{{ $module }}">
                                                    </div>
                                                </div>
                                                <div class="card-body py-2">
                                                    @foreach($permissions as $permission)
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                                   name="permissions[]" value="{{ $permission->name }}"
                                                                   id="perm-{{ $permission->id }}"
                                                                   data-group="{{ $module }}"
                                                                   {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                            <label class="form-check-label fs-13" for="perm-{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-soft-danger">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="ri-save-line me-1"></i> Update Role
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function($) {
    'use strict';

    // ── Initialize group toggles ──
    $('.select-group').each(function() {
        const group = $(this).data('group');
        const all = $('.permission-checkbox[data-group="' + group + '"]');
        $(this).prop('checked', all.length === all.filter(':checked').length);
    });
    updateSelectAll();

    // ── Select All ──
    $(document).on('change', '#selectAllPermissions', function() {
        const isChecked = $(this).is(':checked');
        $('.permission-checkbox, .select-group').prop('checked', isChecked);
    });

    // ── Group Select All ──
    $(document).on('change', '.select-group', function() {
        const group = $(this).data('group');
        $('.permission-checkbox[data-group="' + group + '"]').prop('checked', $(this).is(':checked'));
        updateSelectAll();
    });

    // ── Individual checkbox → update group & master toggles ──
    $(document).on('change', '.permission-checkbox', function() {
        const group = $(this).data('group');
        const allInGroup = $('.permission-checkbox[data-group="' + group + '"]');
        const allChecked = allInGroup.length === allInGroup.filter(':checked').length;
        $('.select-group[data-group="' + group + '"]').prop('checked', allChecked);
        updateSelectAll();
    });

    function updateSelectAll() {
        const all = $('.permission-checkbox');
        $('#selectAllPermissions').prop('checked', all.length === all.filter(':checked').length);
    }

    // ── Form Submit ──
    $(document).on('submit', '#editRoleForm', function(e) {
        e.preventDefault();
        clearErrors();

        const $btn = $('#submitBtn');
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        axios.post('{{ route("admin.roles.update", $role) }}', formData)
            .then(function(res) {
                Toast.fromResponse(res.data);
                if (res.data.redirect) {
                    $('#rolesTable').DataTable().ajax.reload(null, false);
                    // setTimeout(() => window.location.href = res.data.redirect, 800);
                }
            })
            .catch(function(err) {
                const data = err.response?.data;
                if (data?.errors) showErrors(data.errors);
                Toast.error(data?.message || 'Failed to update role.');
            })
            .finally(function() {
                $btn.prop('disabled', false)
                    .html('<i class="ri-save-line me-1"></i> Update Role');
            });
    });

    function clearErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function showErrors(errors) {
        $.each(errors, function(field, messages) {
            $('[name="' + field + '"]').addClass('is-invalid');
            $('#error-' + field).text(messages[0]);
        });
    }

})(jQuery);
</script>
@endpush
