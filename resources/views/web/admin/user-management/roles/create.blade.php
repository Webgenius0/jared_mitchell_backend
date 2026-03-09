@extends('layout.master-layout')
@section('title', 'Create Role')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create Role</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="createRoleForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="Enter role name (e.g., editor)">
                                    <div class="invalid-feedback" id="error-name"></div>
                                    <small class="text-muted">Use lowercase letters and hyphens (e.g., content-manager)</small>
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
                                                                   data-group="{{ $module }}">
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
                                    <i class="ri-save-line me-1"></i> Save Role
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
(function() {
    'use strict';

    // ── Select All ──
    document.getElementById('selectAllPermissions').addEventListener('change', function() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = this.checked);
        document.querySelectorAll('.select-group').forEach(cb => cb.checked = this.checked);
    });

    // ── Group Select All ──
    document.querySelectorAll('.select-group').forEach(function(groupCb) {
        groupCb.addEventListener('change', function() {
            const group = this.dataset.group;
            document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]').forEach(cb => {
                cb.checked = groupCb.checked;
            });
            updateSelectAll();
        });
    });

    // ── Individual checkbox → update group & master toggles ──
    document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupCheckboxes = document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]');
            const groupToggle = document.querySelector('.select-group[data-group="' + group + '"]');
            groupToggle.checked = Array.from(groupCheckboxes).every(c => c.checked);
            updateSelectAll();
        });
    });

    function updateSelectAll() {
        const all = document.querySelectorAll('.permission-checkbox');
        document.getElementById('selectAllPermissions').checked = Array.from(all).every(c => c.checked);
    }

    // ── Form Submit ──
    document.getElementById('createRoleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const formData = new FormData(this);

        axios.post('{{ route("admin.roles.store") }}', formData)
            .then(function(res) {
                Toast.fromResponse(res.data);
                if (res.data.redirect) {
                    setTimeout(() => window.location.href = res.data.redirect, 800);
                }
            })
            .catch(function(err) {
                const data = err.response?.data;
                if (data?.errors) showErrors(data.errors);
                Toast.error(data?.message || 'Failed to create role.');
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-save-line me-1"></i> Save Role';
            });
    });

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }
    function showErrors(errors) {
        Object.keys(errors).forEach(function(field) {
            const input = document.querySelector('[name="' + field + '"]');
            const errorDiv = document.getElementById('error-' + field);
            if (input) input.classList.add('is-invalid');
            if (errorDiv) errorDiv.textContent = errors[field][0];
        });
    }
})();
</script>
@endpush
