@extends('layout.guest-layout')

@section('title', 'Set New Password')

@section('content')
    <div class="auth-page-wrapper pt-5">

        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">

                {{-- Logo --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <a href="/" class="d-inline-block auth-logo">
                                <img src="{{ asset('admin/assets/images/default/logo-light.png') }}"
                                    alt="{{ config('app.name') }}" height="20">
                            </a>
                            <p class="mt-3 fs-15 fw-medium">Admin Panel</p>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4 card-bg-fill">
                            <div class="card-body p-4">

                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Create New Password</h5>
                                    <p class="text-muted">Your new password must be different from your previous one.</p>
                                </div>

                                <div class="p-2">
                                    <form id="resetForm" novalidate>
                                        @csrf

                                        {{-- Password --}}
                                        <div class="mb-3">
                                            <label for="password" class="form-label">
                                                New Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative auth-pass-inputgroup">
                                                <input type="password" class="form-control pe-5 password-input"
                                                    id="password" name="password" placeholder="Enter new password"
                                                    autocomplete="new-password" onpaste="return false">
                                                <button type="button"
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                                                    id="passwordToggle" aria-label="Toggle password">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                            </div>
                                            <div class="text-danger small mt-1" id="error-password"></div>
                                        </div>

                                        {{-- Confirm Password --}}
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">
                                                Confirm Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative auth-pass-inputgroup">
                                                <input type="password" class="form-control pe-5 password-input"
                                                    id="password_confirmation" name="password_confirmation"
                                                    placeholder="Re-enter new password" autocomplete="new-password"
                                                    onpaste="return false">
                                                <button type="button"
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                                                    id="confirmToggle" aria-label="Toggle confirm password">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                            </div>
                                            <div class="text-danger small mt-1" id="error-password_confirmation"></div>
                                        </div>

                                        {{-- Password strength checklist --}}
                                        <div class="p-3 bg-light rounded mb-3" id="password-rules">
                                            <p class="fs-12 fw-semibold mb-2 text-muted text-uppercase"
                                                style="letter-spacing:.5px;">
                                                Password must contain:
                                            </p>
                                            <p class="rule fs-12 mb-1" id="rule-length">
                                                <i class="ri-close-circle-fill me-1"></i> Minimum <b>8 characters</b>
                                            </p>
                                            <p class="rule fs-12 mb-1" id="rule-lower">
                                                <i class="ri-close-circle-fill me-1"></i> At least one <b>lowercase</b>
                                                letter (a-z)
                                            </p>
                                            <p class="rule fs-12 mb-1" id="rule-upper">
                                                <i class="ri-close-circle-fill me-1"></i> At least one <b>uppercase</b>
                                                letter (A-Z)
                                            </p>
                                            <p class="rule fs-12 mb-1" id="rule-number">
                                                <i class="ri-close-circle-fill me-1"></i> At least one <b>number</b> (0-9)
                                            </p>
                                            <p class="rule fs-12 mb-0" id="rule-special">
                                                <i class="ri-close-circle-fill me-1"></i> At least one <b>special
                                                    character</b> (@$!%*?&)
                                            </p>
                                        </div>

                                        {{-- Submit --}}
                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit" id="resetBtn">
                                                <span id="resetBtnText">Reset Password</span>
                                                <span id="resetBtnSpinner" class="d-none">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status"
                                                        aria-hidden="true"></span>
                                                    Resetting…
                                                </span>
                                            </button>
                                        </div>

                                    </form>
                                </div>

                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="mb-0">Wait, I remember my password…
                                <a href="{{ route('show.admin.login') }}"
                                    class="fw-semibold text-primary text-decoration-underline">Login</a>
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        @include('navigations.footer')
    </div>
@endsection

@push('styles')
    <style>
        /* Password rule rows */
        .rule {
            color: #f06548;
            transition: color 0.2s ease;
        }

        .rule.valid {
            color: #0ab39c;
        }

        .rule i {
            font-size: 13px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── Password visibility toggles ───────────────────────────────────────
            function makeToggle(btnId, inputId) {
                document.getElementById(btnId).addEventListener('click', function() {
                    const input = document.getElementById(inputId);
                    const icon = this.querySelector('i');
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    icon.className = isHidden ?
                        'ri-eye-off-fill align-middle' :
                        'ri-eye-fill align-middle';
                });
            }
            makeToggle('passwordToggle', 'password');
            makeToggle('confirmToggle', 'password_confirmation');

            // ── Live password strength checker ────────────────────────────────────
            const rules = [{
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
                rules.forEach(function(rule) {
                    const el = document.getElementById(rule.id);
                    const ok = rule.test(val);
                    el.classList.toggle('valid', ok);
                    el.querySelector('i').className = ok ?
                        'ri-checkbox-circle-fill me-1' :
                        'ri-close-circle-fill me-1';
                });
            });

            // ── Helpers ───────────────────────────────────────────────────────────
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

            function setLoading(state) {
                const btn = document.getElementById('resetBtn');
                const text = document.getElementById('resetBtnText');
                const spinner = document.getElementById('resetBtnSpinner');
                btn.disabled = state;
                text.classList.toggle('d-none', state);
                spinner.classList.toggle('d-none', !state);
            }

            // ── Submit ────────────────────────────────────────────────────────────
            document.getElementById('resetForm').addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const password = document.getElementById('password').value;
                const password_confirmation = document.getElementById('password_confirmation').value;

                // Client-side match check before hitting server
                if (password !== password_confirmation) {
                    showFieldErrors({
                        password_confirmation: ['Passwords do not match.']
                    });
                    return;
                }

                setLoading(true);

                axios.post('{{ route('set.new.password') }}', {
                        password: password,
                        password_confirmation: password_confirmation,
                    })
                    .then(function(res) {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.href = res.data.redirect, 1500);
                    })
                    .catch(function(err) {
                        setLoading(false);
                        const data = err.response?.data;
                        if (data?.errors) {
                            showFieldErrors(data.errors);
                            if (data.message) Toast.error(data.message);
                        } else {
                            Toast.fromResponse(data);
                        }
                    });
            });

        });
    </script>
@endpush
