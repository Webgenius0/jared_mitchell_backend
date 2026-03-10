@extends('layout.master-layout')
@section('title', 'Create Permission')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create Permission</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <form id="createPermissionForm">
                            <div class="mb-3">
                                <label class="form-label">Permission Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name"
                                       placeholder="e.g., view posts" autocomplete="off">
                                <div class="invalid-feedback" id="error-name"></div>
                                <small class="text-muted">
                                    Use lowercase format: <code>action resource</code> (e.g., <code>view users</code>, <code>edit posts</code>)
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted">Preview</label>
                                <div class="border rounded p-2 bg-light" id="namePreview">
                                    <span class="text-muted">Enter a name above...</span>
                                </div>
                            </div>

                            @if($existingGroups->isNotEmpty())
                                <div class="mb-3">
                                    <label class="form-label text-muted">Existing Groups</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($existingGroups as $group)
                                            <span class="badge bg-light text-body fs-12 group-suggest" style="cursor: pointer;"
                                                  data-group="{{ $group }}">{{ $group }}</span>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Click a group to auto-fill the resource part.</small>
                                </div>
                            @endif

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.permissions.index') }}" class="btn btn-soft-danger">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="ri-save-line me-1"></i> Save Permission
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

    const nameInput = document.getElementById('name');
    const preview = document.getElementById('namePreview');

    // ── Live preview ──
    nameInput.addEventListener('input', function() {
        const val = this.value.toLowerCase().trim();
        if (val) {
            preview.innerHTML = '<span class="badge bg-light text-body fs-12">' + val + '</span>';
        } else {
            preview.innerHTML = '<span class="text-muted">Enter a name above...</span>';
        }
    });

    // ── Group auto-fill ──
    document.querySelectorAll('.group-suggest').forEach(function(badge) {
        badge.addEventListener('click', function() {
            const group = this.dataset.group;
            const current = nameInput.value.trim();
            const parts = current.split(' ');
            if (parts.length > 1) {
                nameInput.value = parts[0] + ' ' + group;
            } else if (current) {
                nameInput.value = current + ' ' + group;
            } else {
                nameInput.value = group;
            }
            nameInput.dispatchEvent(new Event('input'));
            nameInput.focus();
        });
    });

    // ── Form Submit ──
    document.getElementById('createPermissionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        axios.post('{{ route("admin.permissions.store") }}', new FormData(this))
            .then(function(res) {
                Toast.fromResponse(res.data);
                if (res.data.redirect) {
                    setTimeout(() => window.location.href = res.data.redirect, 800);
                }
            })
            .catch(function(err) {
                const data = err.response?.data;
                if (data?.errors) showErrors(data.errors);
                Toast.error(data?.message || 'Failed to create permission.');
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-save-line me-1"></i> Save Permission';
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
