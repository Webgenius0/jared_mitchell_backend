@extends('layout.master-layout')
@section('title', 'Create User')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create User</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                            <li class="breadcrumb-item active">Create</li>
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
                        <form id="createUserForm" enctype="multipart/form-data">
                            <div class="tab-content">

                                {{-- Tab 1: Basic Info --}}
                                <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" id="name" placeholder="Enter full name">
                                            <div class="invalid-feedback" id="error-name"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter email">
                                            <div class="invalid-feedback" id="error-email"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone number">
                                            <div class="invalid-feedback" id="error-phone"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status</label>
                                            <select class="form-select form-control" name="status" id="status1">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <div class="position-relative">
                                                <input type="password" class="form-control" name="password" id="password" placeholder="Enter password">
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-toggle" type="button" data-target="password">
                                                    <i class="ri-eye-off-line align-middle"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="error-password"></div>
                                            {{-- Password strength --}}
                                            <div class="mt-2" id="passwordStrengthBar" style="display:none;">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar" id="strengthBar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <small class="text-muted" id="strengthText"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <div class="position-relative">
                                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirm password">
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-toggle" type="button" data-target="password_confirmation">
                                                    <i class="ri-eye-off-line align-middle"></i>
                                                </button>
                                            </div>
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
                                                                   data-role-id="{{ $role->id }}">
                                                            <label class="form-check-label" for="role-{{ $role->id }}">
                                                                {{ ucfirst($role->name) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 mt-4">
                                            <label class="form-label fw-semibold text-muted">Inherited Permissions (read-only)</label>
                                            <div id="inheritedPermissions" class="d-flex flex-wrap gap-2">
                                                <span class="text-muted fs-12">Select a role to see inherited permissions.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tab 3: Avatar --}}
                                <div class="tab-pane fade" id="tab-avatar" role="tabpanel">
                                    <div class="row justify-content-center">
                                        <div class="col-md-6 text-center">
                                            <div class="mb-4">
                                                <div class="avatar-xl mx-auto mb-3" id="avatarPreviewContainer">
                                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-1">
                                                        <i class="ri-user-line"></i>
                                                    </div>
                                                </div>
                                                <img src="" alt="Preview" class="rounded-circle avatar-xl d-none" id="avatarPreview">
                                            </div>
                                            <div class="mb-3">
                                                <label for="avatar" class="form-label">Upload Avatar</label>
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
                                    <i class="ri-save-line me-1"></i> Save User
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

    // ── Role permissions mapping ──
    const rolePermissions = @json($roles->mapWithKeys(fn($r) => [$r->name => $r->permissions->pluck('name')]));

    // ── Show inherited permissions when role checkboxes change ──
    $(document).on('change', '.role-checkbox', updateInheritedPermissions);

    function updateInheritedPermissions() {
        const $container = $('#inheritedPermissions');
        const checked = $('.role-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (checked.length === 0) {
            $container.html('<span class="text-muted fs-12">Select a role to see inherited permissions.</span>');
            return;
        }

        const perms = new Set();
        checked.forEach(function(role) {
            (rolePermissions[role] || []).forEach(p => perms.add(p));
        });

        $container.html(
            Array.from(perms).sort().map(p =>
                '<span class="badge bg-light text-body fs-12">' + p + '</span>'
            ).join('')
        );
    }

    // ── Avatar Preview ──
    $(document).on('change', '#avatar', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(ev) {
            $('#avatarPreview').attr('src', ev.target.result).removeClass('d-none');
            $('#avatarPreviewContainer').addClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    // ── Password Strength ──
    $(document).on('input', '#password', function() {
        const $bar = $('#passwordStrengthBar');
        const $barEl = $('#strengthBar');
        const $textEl = $('#strengthText');
        const val = $(this).val();

        if (!val) { $bar.hide(); return; }
        $bar.show();

        let score = 0;
        if (val.length >= 8) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[^a-zA-Z0-9]/.test(val)) score++;

        const levels = [
            { width: '20%', color: 'bg-danger',  text: 'Very Weak'  },
            { width: '40%', color: 'bg-warning',  text: 'Weak'       },
            { width: '60%', color: 'bg-info',     text: 'Fair'       },
            { width: '80%', color: 'bg-primary',  text: 'Strong'     },
            { width: '100%',color: 'bg-success',  text: 'Very Strong'},
        ];
        const level = levels[Math.min(score, 5) - 1] || levels[0];

        $barEl.css('width', level.width)
              .attr('class', 'progress-bar ' + level.color);
        $textEl.text(level.text);
    });

    // ── Password Toggle ──
    $(document).on('click', '.password-toggle', function() {
        const $target = $('#' + $(this).data('target'));
        const $icon = $(this).find('i');

        if ($target.attr('type') === 'password') {
            $target.attr('type', 'text');
            $icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
        } else {
            $target.attr('type', 'password');
            $icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
        }
    });

    // ── Tab Memory (localStorage) ──
    const TAB_KEY = 'user_create_tab';
    const savedTab = localStorage.getItem(TAB_KEY);
    if (savedTab) {
        const $tabEl = $('[data-tab-key="' + savedTab + '"]');
        if ($tabEl.length) new bootstrap.Tab($tabEl[0]).show();
    }
    $(document).on('shown.bs.tab', '[data-tab-key]', function() {
        localStorage.setItem(TAB_KEY, $(this).data('tabKey'));
    });

    // ── Form Submit ──
    $(document).on('submit', '#createUserForm', function(e) {
        e.preventDefault();
        clearErrors();

        const $btn = $('#submitBtn');
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        const formData = new FormData(this);

        axios.post('{{ route("admin.users.store") }}', formData, {
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
            Toast.error(data?.message || 'Failed to create user.');
        })
        .finally(function() {
            $btn.prop('disabled', false)
                .html('<i class="ri-save-line me-1"></i> Save User');
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
