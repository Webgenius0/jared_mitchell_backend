@extends('layout.master-layout')
@section('title', 'Edit User')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Edit User</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <x-admin.tab-nav :tabs="['basic' => 'Basic Info', 'roles' => 'Roles & Permissions', 'avatar' => 'Avatar']" />
                    </div>
                    <div class="card-body">
                        <form id="editUserForm" enctype="multipart/form-data">
                            <div class="tab-content">

                                {{-- Tab 1: Basic Info --}}
                                <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" id="name"
                                                   value="{{ $user->profile?->name }}">
                                            <div class="invalid-feedback" id="error-name"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" id="email"
                                                   value="{{ $user->email }}">
                                            <div class="invalid-feedback" id="error-email"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone" id="phone"
                                                   value="{{ $user->phone }}">
                                            <div class="invalid-feedback" id="error-phone"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status</label>
                                            <select class="form-select form-control" name="status" id="status1">
                                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                                            <div class="position-relative">
                                                <input type="password" class="form-control" name="password" id="password" placeholder="New password">
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-toggle" type="button" data-target="password">
                                                    <i class="ri-eye-off-line align-middle"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="error-password"></div>
                                            <div class="mt-2" id="passwordStrengthBar" style="display:none;">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar" id="strengthBar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <small class="text-muted" id="strengthText"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm New Password</label>
                                            <div class="position-relative">
                                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirm new password">
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-toggle" type="button" data-target="password_confirmation">
                                                    <i class="ri-eye-off-line align-middle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <small class="text-muted">
                                                Last updated: {{ $user->updated_at?->format('M d, Y h:i A') }} |
                                                Created: {{ $user->created_at?->format('M d, Y h:i A') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tab 2: Roles & Permissions --}}
                                <div class="tab-pane fade" id="tab-roles" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Assign Roles</label>
                                            <div class="row g-2">
                                                @foreach($roles as $role)
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input role-checkbox" type="checkbox"
                                                                   name="roles[]" value="{{ $role->name }}"
                                                                   id="role-{{ $role->id }}"
                                                                   {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="role-{{ $role->id }}">
                                                                {{ ucfirst($role->name) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="col-12 mt-4">
                                            <label class="form-label fw-semibold">All Permissions (grouped by module)</label>
                                            @php
                                                $grouped = \App\Helpers\PermissionHelper::groupPermissionsByModule(
                                                    \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->orderBy('name')->get()
                                                );
                                            @endphp
                                            <div class="row g-3">
                                                @foreach($grouped as $module => $perms)
                                                    <div class="col-md-4">
                                                        <div class="card border shadow-none mb-0">
                                                            <div class="card-header bg-light py-2">
                                                                <h6 class="mb-0 fs-13">{{ $module }}</h6>
                                                            </div>
                                                            <div class="card-body py-2">
                                                                @foreach($perms as $perm)
                                                                    <div class="d-flex align-items-center mb-1">
                                                                        @if(in_array($perm->name, $userPermissions))
                                                                            <i class="ri-checkbox-circle-fill text-success me-2"></i>
                                                                        @else
                                                                            <i class="ri-close-circle-fill text-muted me-2"></i>
                                                                        @endif
                                                                        <span class="fs-13">{{ $perm->name }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tab 3: Avatar --}}
                                <div class="tab-pane fade" id="tab-avatar" role="tabpanel">
                                    <div class="row justify-content-center">
                                        <div class="col-md-6 text-center">
                                            <div class="mb-4">
                                                @if($user->profile?->avatar)
                                                    {{-- profiles.avatar already stores the full "storage/..." path --}}
                                                    <img src="{{ asset($user->profile->avatar) }}" alt="Avatar"
                                                         class="rounded-circle avatar-xl" id="avatarPreview">
                                                    <div class="avatar-xl mx-auto mb-3 d-none" id="avatarPreviewContainer">
                                                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-1">
                                                            <i class="ri-user-line"></i>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="avatar-xl mx-auto mb-3" id="avatarPreviewContainer">
                                                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-1">
                                                            {{ strtoupper(mb_substr($user->profile?->name ?? $user->email, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <img src="" alt="Preview" class="rounded-circle avatar-xl d-none" id="avatarPreview">
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <label for="avatar" class="form-label">Upload New Avatar</label>
                                                <input type="file" class="form-control" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp">
                                                <div class="form-text">JPG, PNG, or WebP. Max 2MB.</div>
                                                <div class="invalid-feedback" id="error-avatar"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Form Actions --}}
                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-soft-danger">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="ri-save-line me-1"></i> Update User
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

    // ── Avatar Preview ──
    document.getElementById('avatar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            const preview = document.getElementById('avatarPreview');
            const container = document.getElementById('avatarPreviewContainer');
            preview.src = ev.target.result;
            preview.classList.remove('d-none');
            if (container) container.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    });

    // ── Password Strength ──
    document.getElementById('password').addEventListener('input', function() {
        const bar = document.getElementById('passwordStrengthBar');
        const barEl = document.getElementById('strengthBar');
        const textEl = document.getElementById('strengthText');
        const val = this.value;
        if (!val) { bar.style.display = 'none'; return; }
        bar.style.display = 'block';
        let score = 0;
        if (val.length >= 8) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^a-zA-Z0-9]/.test(val)) score++;
        const levels = [
            { width: '20%', color: 'bg-danger', text: 'Very Weak' },
            { width: '40%', color: 'bg-warning', text: 'Weak' },
            { width: '60%', color: 'bg-info', text: 'Fair' },
            { width: '80%', color: 'bg-primary', text: 'Strong' },
            { width: '100%', color: 'bg-success', text: 'Very Strong' },
        ];
        const level = levels[Math.min(score, 5) - 1] || levels[0];
        barEl.style.width = level.width;
        barEl.className = 'progress-bar ' + level.color;
        textEl.textContent = level.text;
    });

    // ── Password Toggle ──
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');
            if (target.type === 'password') {
                target.type = 'text';
                icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
            } else {
                target.type = 'password';
                icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
            }
        });
    });

    // ── Tab Memory ──
    const TAB_KEY = 'user_edit_tab';
    const savedTab = localStorage.getItem(TAB_KEY);
    if (savedTab) {
        const tabEl = document.querySelector('[data-tab-key="' + savedTab + '"]');
        if (tabEl) new bootstrap.Tab(tabEl).show();
    }
    document.querySelectorAll('[data-tab-key]').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function() {
            localStorage.setItem(TAB_KEY, this.dataset.tabKey);
        });
    });

    // ── Form Submit (PUT via method spoofing with FormData) ──
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        axios.post('{{ route("admin.users.update", $user) }}', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(function(res) {
            Toast.fromResponse(res.data);
            localStorage.removeItem(TAB_KEY);
            if (res.data.redirect) {
                setTimeout(() => window.location.href = res.data.redirect, 800);
            }
        })
        .catch(function(err) {
            const data = err.response?.data;
            if (data?.errors) showErrors(data.errors);
            Toast.error(data?.message || 'Failed to update user.');
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line me-1"></i> Update User';
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
