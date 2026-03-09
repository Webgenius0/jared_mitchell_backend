@extends('layout.master-layout')

@section('title', 'Profile Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- ── Cover Photo ───────────────────────────────────────────────────── --}}
            <div class="position-relative mx-n4 mt-n4">
                <div class="profile-wid-bg profile-setting-img">
                    <img id="coverPreview"
                        src="{{ $profile?->cover ? asset('storage/' . $profile?->cover) : asset('admin/assets/images/default/profile-bg.jpg') }}"
                        class="profile-wid-img" alt="Cover Photo">

                    <div class="overlay-content">
                        <div class="text-end p-3">
                            {{-- Cover upload button --}}
                            <label for="coverFileInput" class="btn btn-light btn-sm cursor-pointer mb-0" id="coverLabel"
                                title="Change cover photo">
                                <i class="ri-image-edit-line align-bottom me-1"></i>
                                <span id="coverBtnText">Change Cover</span>
                                <span id="coverBtnSpinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    Uploading…
                                </span>
                            </label>
                            <input type="file" id="coverFileInput" accept="image/jpeg,image/png,image/webp"
                                class="d-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                {{-- ── Left Sidebar ─────────────────────────────────────────────── --}}
                <div class="col-xxl-3">

                    {{-- Avatar card --}}
                    <div class="card mt-n5">
                        <div class="card-body p-4">
                            <div class="text-center">

                                {{-- Avatar with upload + delete --}}
                                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                    <img id="avatarPreview"
                                        src="{{ $profile?->avatar_url ?? asset('admin/default/user.jpg') }}"
                                        class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                        alt="Profile Avatar">

                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                        <label for="avatarFileInput"
                                            class="profile-photo-edit avatar-xs cursor-pointer mb-0" title="Change avatar">
                                            <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                        <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/webp"
                                            class="d-none">
                                    </div>
                                </div>

                                <h5 class="fs-16 mb-1">{{ $profile?->name ?? $user->email }}</h5>
                                <p class="text-muted mb-0">
                                    {{ $user->getRoleNames()->first() ?? 'Administrator' }}
                                </p>

                                {{-- Remove avatar link --}}
                                @if ($profile?->avatar)
                                    <div class="mt-3">
                                        <button type="button" id="removeAvatarBtn" class="btn btn-sm btn-soft-danger">
                                            <i class="ri-delete-bin-line me-1"></i> Remove Avatar
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Account info card --}}
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Account Info</h5>

                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-xs flex-shrink-0 me-3">
                                    <span
                                        class="avatar-title rounded-circle bg-soft-primary text-primary fs-16 material-shadow">
                                        <i class="ri-mail-line"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="mb-0 text-muted small text-truncate">{{ $user->email }}</p>
                                    <p class="mb-0 fs-11 text-muted">Email address</p>
                                </div>
                            </div>

                            @if ($user->phone)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-xs flex-shrink-0 me-3">
                                        <span
                                            class="avatar-title rounded-circle bg-soft-success text-success fs-16 material-shadow">
                                            <i class="ri-phone-line"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 text-muted small">{{ $user->phone }}</p>
                                        <p class="mb-0 fs-11 text-muted">Phone</p>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex align-items-center">
                                <div class="avatar-xs flex-shrink-0 me-3">
                                    <span
                                        class="avatar-title rounded-circle bg-soft-warning text-warning fs-16 material-shadow">
                                        <i class="ri-calendar-line"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-muted small">
                                        {{ $user->created_at->format('d M Y') }}
                                    </p>
                                    <p class="mb-0 fs-11 text-muted">Member since</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- /col --}}

                {{-- ── Right: Tab panels ────────────────────────────────────────── --}}
                <div class="col-xxl-9">
                    <div class="card mt-xxl-n5">
                        <div class="card-header">
                            <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-general" role="tab">
                                        <i class="ri-user-line me-1"></i> Personal Details
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-password" role="tab">
                                        <i class="ri-lock-password-line me-1"></i> Change Password
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-4">
                            <div class="tab-content">

                                {{-- ── Tab 1: Personal Details ──────────────────── --}}
                                <div class="tab-pane active" id="tab-general" role="tabpanel">
                                    <form id="generalForm" novalidate>
                                        @csrf
                                        @method('POST')

                                        <div class="row g-3">

                                            {{-- Name --}}
                                            <div class="col-lg-6">
                                                <label for="name" class="form-label">
                                                    Full Name <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    placeholder="Enter your full name"
                                                    value="{{ old('name', $profile?->name) }}">
                                                <div class="text-danger small mt-1" id="error-name"></div>
                                            </div>

                                            {{-- Email (readonly) --}}
                                            <div class="col-lg-6">
                                                <label class="form-label">Email Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">
                                                        <i class="ri-mail-line text-muted"></i>
                                                    </span>
                                                    <input type="email" class="form-control bg-light"
                                                        value="{{ $user->email }}" readonly>
                                                </div>
                                                <div class="form-text">Email cannot be changed.</div>
                                            </div>

                                            {{-- Phone --}}
                                            <div class="col-lg-6">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="text" class="form-control" id="phone" name="phone"
                                                    placeholder="Enter your phone number"
                                                    value="{{ old('phone', $user->phone) }}">
                                                <div class="text-danger small mt-1" id="error-phone"></div>
                                            </div>

                                            {{-- Address --}}
                                            <div class="col-lg-6">
                                                <label for="address" class="form-label">Address</label>
                                                <input type="text" class="form-control" id="address" name="address"
                                                    placeholder="Enter your address"
                                                    value="{{ old('address', $profile?->address) }}">
                                                <div class="text-danger small mt-1" id="error-address"></div>
                                            </div>

                                            {{-- Biography --}}
                                            <div class="col-12">
                                                <label for="biography" class="form-label">Biography</label>
                                                <textarea class="form-control" id="biography" name="biography" rows="4"
                                                    placeholder="Write a short bio about yourself…" maxlength="500">{{ old('biography', $profile?->biography) }}</textarea>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <div class="text-danger small" id="error-biography"></div>
                                                    <small class="text-muted">
                                                        <span
                                                            id="bioCount">{{ strlen($profile?->biography ?? '') }}</span>/500
                                                    </small>
                                                </div>
                                            </div>

                                            {{-- Actions --}}
                                            <div class="col-12">
                                                <div class="hstack gap-2 justify-content-end">
                                                    <button type="button" class="btn btn-soft-secondary"
                                                        onclick="resetGeneralForm()">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-primary" id="generalSubmitBtn">
                                                        <span id="generalBtnText">
                                                            <i class="ri-save-line me-1"></i> Save Changes
                                                        </span>
                                                        <span id="generalBtnSpinner" class="d-none">
                                                            <span class="spinner-border spinner-border-sm me-1"
                                                                role="status"></span>
                                                            Saving…
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                                {{-- /tab-general --}}

                                {{-- ── Tab 2: Change Password ───────────────────── --}}
                                <div class="tab-pane" id="tab-password" role="tabpanel">
                                    <form id="passwordForm" novalidate>
                                        @csrf
                                        @method('POST')

                                        <div class="row g-3">

                                            {{-- Current password --}}
                                            <div class="col-lg-12">
                                                <label for="current_password" class="form-label">
                                                    Current Password <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <input type="password" class="form-control pe-5"
                                                        id="current_password" name="current_password"
                                                        placeholder="Enter current password">
                                                    <button type="button"
                                                        class="btn btn-link position-absolute end-0 top-0 text-muted text-decoration-none pw-toggle"
                                                        data-target="current_password">
                                                        <i class="ri-eye-fill align-middle"></i>
                                                    </button>
                                                </div>
                                                <div class="text-danger small mt-1" id="error-current_password"></div>
                                            </div>

                                            {{-- New password --}}
                                            <div class="col-lg-6">
                                                <label for="password" class="form-label">
                                                    New Password <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <input type="password" class="form-control pe-5" id="password"
                                                        name="password" placeholder="Enter new password"
                                                        autocomplete="new-password">
                                                    <button type="button"
                                                        class="btn btn-link position-absolute end-0 top-0 text-muted text-decoration-none pw-toggle"
                                                        data-target="password">
                                                        <i class="ri-eye-fill align-middle"></i>
                                                    </button>
                                                </div>
                                                <div class="text-danger small mt-1" id="error-password"></div>
                                            </div>

                                            {{-- Confirm password --}}
                                            <div class="col-lg-6">
                                                <label for="password_confirmation" class="form-label">
                                                    Confirm Password <span class="text-danger">*</span>
                                                </label>
                                                <div class="position-relative">
                                                    <input type="password" class="form-control pe-5"
                                                        id="password_confirmation" name="password_confirmation"
                                                        placeholder="Re-enter new password" autocomplete="new-password">
                                                    <button type="button"
                                                        class="btn btn-link position-absolute end-0 top-0 text-muted text-decoration-none pw-toggle"
                                                        data-target="password_confirmation">
                                                        <i class="ri-eye-fill align-middle"></i>
                                                    </button>
                                                </div>
                                                <div class="text-danger small mt-1" id="error-password_confirmation">
                                                </div>
                                            </div>

                                            {{-- Password strength rules --}}
                                            <div class="col-12">
                                                <div class="p-3 bg-light rounded" id="pw-rules">
                                                    <p class="fs-12 fw-semibold mb-2 text-muted text-uppercase"
                                                        style="letter-spacing:.5px;">
                                                        Password must contain:
                                                    </p>
                                                    <div class="row g-1">
                                                        <div class="col-sm-6">
                                                            <p class="pw-rule fs-12 mb-1" id="rule-length">
                                                                <i class="ri-close-circle-fill me-1"></i> Minimum <b>8
                                                                    characters</b>
                                                            </p>
                                                            <p class="pw-rule fs-12 mb-1" id="rule-lower">
                                                                <i class="ri-close-circle-fill me-1"></i> One
                                                                <b>lowercase</b> letter
                                                            </p>
                                                            <p class="pw-rule fs-12 mb-0" id="rule-upper">
                                                                <i class="ri-close-circle-fill me-1"></i> One
                                                                <b>uppercase</b> letter
                                                            </p>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <p class="pw-rule fs-12 mb-1" id="rule-number">
                                                                <i class="ri-close-circle-fill me-1"></i> One <b>number</b>
                                                            </p>
                                                            <p class="pw-rule fs-12 mb-0" id="rule-special">
                                                                <i class="ri-close-circle-fill me-1"></i> One <b>special
                                                                    character</b> (@$!%*?&)
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Forgot password link --}}
                                            <div class="col-12">
                                                <a href="{{ route('show.forgot-password') }}"
                                                    class="link-primary text-decoration-underline small">
                                                    <i class="ri-question-line me-1"></i> Forgot your current password?
                                                </a>
                                            </div>

                                            {{-- Actions --}}
                                            <div class="col-12">
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success"
                                                        id="passwordSubmitBtn">
                                                        <span id="passwordBtnText">
                                                            <i class="ri-lock-line me-1"></i> Change Password
                                                        </span>
                                                        <span id="passwordBtnSpinner" class="d-none">
                                                            <span class="spinner-border spinner-border-sm me-1"
                                                                role="status"></span>
                                                            Updating…
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                                {{-- /tab-password --}}

                            </div>
                        </div>
                    </div>
                </div>
                {{-- /col --}}

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Password rule indicators */
        .pw-rule {
            color: #f06548;
            transition: color .2s;
        }

        .pw-rule.valid {
            color: #0ab39c;
        }

        .pw-rule i {
            font-size: 13px;
        }

        /* Cover overlay */
        .profile-setting-img {
            position: relative;
        }

        .profile-setting-img .overlay-content {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, .0) 60%, rgba(0, 0, 0, .35) 100%);
        }

        /* Avatar upload hover */
        .profile-user:hover .profile-photo-edit {
            opacity: 1;
        }

        .profile-photo-edit {
            cursor: pointer;
        }

        /* Cover upload label */
        #coverLabel {
            cursor: pointer;
            transition: opacity .2s;
        }

        #coverLabel:hover {
            opacity: .85;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── Shared helpers ────────────────────────────────────────────────────── */

            function clearErrors() {
                document.querySelectorAll('[id^="error-"]').forEach(el => el.textContent = '');
                document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
            }

            function showFieldErrors(errors) {
                Object.entries(errors).forEach(([field, messages]) => {
                    const errorEl = document.getElementById('error-' + field);
                    const inputEl = document.getElementById(field);
                    if (errorEl) errorEl.textContent = messages[0];
                    if (inputEl) inputEl.classList.add('is-invalid');
                });
            }

            function setLoading(btnId, textId, spinnerId, state) {
                document.getElementById(btnId).disabled = state;
                document.getElementById(textId).classList.toggle('d-none', state);
                document.getElementById(spinnerId).classList.toggle('d-none', !state);
            }

            /* ── Password toggles ──────────────────────────────────────────────────── */
            document.querySelectorAll('.pw-toggle').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    const icon = this.querySelector('i');
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    icon.className = isHidden ? 'ri-eye-off-fill align-middle' :
                        'ri-eye-fill align-middle';
                });
            });

            /* ── Biography char counter ────────────────────────────────────────────── */
            document.getElementById('biography').addEventListener('input', function() {
                document.getElementById('bioCount').textContent = this.value.length;
            });

            /* ── Reset general form ────────────────────────────────────────────────── */
            window.resetGeneralForm = function() {
                document.getElementById('generalForm').reset();
                clearErrors();
                document.getElementById('bioCount').textContent =
                    document.getElementById('biography').value.length;
            };

            /* ══════════════════════════════════════════════════════════════════════════
             |  COVER PHOTO
             ══════════════════════════════════════════════════════════════════════════ */
            document.getElementById('coverFileInput').addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                // Instant local preview
                const reader = new FileReader();
                reader.onload = e => document.getElementById('coverPreview').src = e.target.result;
                reader.readAsDataURL(file);

                // Upload
                setLoading('coverLabel', 'coverBtnText', 'coverBtnSpinner', true);

                const fd = new FormData();
                fd.append('cover', file);
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                axios.post('{{ route('admin.profile.cover.update') }}', fd, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                    })
                    .then(function(res) {
                        Toast.success(res.data.message);
                    })
                    .catch(function(err) {
                        const data = err.response?.data;
                        Toast.fromResponse(data);
                        // Revert preview on error
                        document.getElementById('coverPreview').src =
                            '{{ $profile?->cover ? asset('storage/' . $profile?->cover) : asset('admin/assets/images/default/profile-bg.jpg') }}';
                    })
                    .finally(function() {
                        setLoading('coverLabel', 'coverBtnText', 'coverBtnSpinner', false);
                        document.getElementById('coverFileInput').value = '';
                    });
            });

            /* ══════════════════════════════════════════════════════════════════════════
             |  AVATAR PHOTO
             ══════════════════════════════════════════════════════════════════════════ */
            document.getElementById('avatarFileInput').addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                // Instant local preview
                const reader = new FileReader();
                reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
                reader.readAsDataURL(file);

                const fd = new FormData();
                fd.append('avatar', file);
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                axios.post('{{ route('admin.profile.avatar.update') }}', fd, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                    })
                    .then(function(res) {
                        Toast.success(res.data.message);

                        // Update header avatar too (if it exists)
                        const headerAvatar = document.querySelector('.header-profile-user');
                        if (headerAvatar && res.data.data?.avatar_url) {
                            headerAvatar.src = res.data.data.avatar_url;
                        }

                        // Show remove button if hidden
                        const removeBtn = document.getElementById('removeAvatarBtn');
                        if (removeBtn) removeBtn.classList.remove('d-none');
                    })
                    .catch(function(err) {
                        Toast.fromResponse(err.response?.data);
                        document.getElementById('avatarPreview').src = '{{ $profile?->avatar_url }}';
                    })
                    .finally(function() {
                        document.getElementById('avatarFileInput').value = '';
                    });
            });

            /* ── Remove avatar ─────────────────────────────────────────────────────── */
            const removeAvatarBtn = document.getElementById('removeAvatarBtn');
            if (removeAvatarBtn) {
                removeAvatarBtn.addEventListener('click', function() {
                    Alert.confirm('Your profile picture will be removed.', {
                        title: 'Remove Avatar?',
                        type: 'danger',
                        confirmText: 'Yes, remove it',
                    }).then(function(confirmed) {
                        if (!confirmed) return;

                        axios.delete('{{ route('admin.profile.avatar.delete') }}', {
                                data: {
                                    _token: document.querySelector('meta[name="csrf-token"]')
                                        .content
                                },
                            })
                            .then(function(res) {
                                Toast.success(res.data.message);
                                document.getElementById('avatarPreview').src =
                                    '{{ asset('admin/default/user.jpg') }}';
                                removeAvatarBtn.classList.add('d-none');

                                const headerAvatar = document.querySelector(
                                    '.header-profile-user');
                                if (headerAvatar) {
                                    headerAvatar.src =
                                        '{{ asset('admin/default/user.jpg') }}';
                                }
                            })
                            .catch(err => Toast.fromResponse(err.response?.data));
                    });
                });
            }

            /* ══════════════════════════════════════════════════════════════════════════
             |  GENERAL INFO FORM
             ══════════════════════════════════════════════════════════════════════════ */
            document.getElementById('generalForm').addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();
                setLoading('generalSubmitBtn', 'generalBtnText', 'generalBtnSpinner', true);

                axios.post('{{ route('admin.profile.general.update') }}', {
                        name: document.getElementById('name').value.trim(),
                        phone: document.getElementById('phone').value.trim(),
                        address: document.getElementById('address').value.trim(),
                        biography: document.getElementById('biography').value.trim(),
                    })
                    .then(function(res) {
                        Toast.success(res.data.message);

                        // Update sidebar name live
                        const sidebarName = document.querySelector('.user-name-text');
                        if (sidebarName) sidebarName.textContent = document.getElementById('name').value
                            .trim();
                    })
                    .catch(function(err) {
                        const data = err.response?.data;
                        if (data?.errors) {
                            showFieldErrors(data.errors);
                            if (data.message) Toast.error(data.message);
                        } else {
                            Toast.fromResponse(data);
                        }
                    })
                    .finally(() => setLoading('generalSubmitBtn', 'generalBtnText', 'generalBtnSpinner',
                        false));
            });

            /* ══════════════════════════════════════════════════════════════════════════
             |  PASSWORD FORM
             ══════════════════════════════════════════════════════════════════════════ */

            // Live strength checker
            const pwRules = [{
                    id: 'rule-length',
                    test: v => v.length >= 8
                },
                {
                    id: 'rule-lower',
                    test: v => /[a-z]/.test(v)
                },
                {
                    id: 'rule-upper',
                    test: v => /[A-Z]/.test(v)
                },
                {
                    id: 'rule-number',
                    test: v => /\d/.test(v)
                },
                {
                    id: 'rule-special',
                    test: v => /[@$!%*?&]/.test(v)
                },
            ];

            document.getElementById('password').addEventListener('input', function() {
                const val = this.value;
                pwRules.forEach(function(rule) {
                    const el = document.getElementById(rule.id);
                    const ok = rule.test(val);
                    el.classList.toggle('valid', ok);
                    el.querySelector('i').className = ok ?
                        'ri-checkbox-circle-fill me-1' :
                        'ri-close-circle-fill me-1';
                });
            });

            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const password = document.getElementById('password').value;
                const password_confirmation = document.getElementById('password_confirmation').value;

                if (password !== password_confirmation) {
                    showFieldErrors({
                        password_confirmation: ['Passwords do not match.']
                    });
                    return;
                }

                setLoading('passwordSubmitBtn', 'passwordBtnText', 'passwordBtnSpinner', true);

                axios.post('{{ route('admin.profile.password.update') }}', {
                        current_password: document.getElementById('current_password').value,
                        password: password,
                        password_confirmation: password_confirmation,
                    })
                    .then(function(res) {
                        Toast.success(res.data.message);
                        document.getElementById('passwordForm').reset();
                        // Reset strength indicators
                        pwRules.forEach(function(rule) {
                            const el = document.getElementById(rule.id);
                            el.classList.remove('valid');
                            el.querySelector('i').className = 'ri-close-circle-fill me-1';
                        });
                    })
                    .catch(function(err) {
                        const data = err.response?.data;
                        if (data?.errors) {
                            showFieldErrors(data.errors);
                            if (data.message) Toast.error(data.message);
                        } else {
                            Toast.fromResponse(data);
                        }
                    })
                    .finally(() => setLoading('passwordSubmitBtn', 'passwordBtnText', 'passwordBtnSpinner',
                        false));
            });

        });
    </script>
@endpush
