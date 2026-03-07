@extends('layout.guest-layout')

@section('title', 'Sign In')

@section('content')
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a href="/" class="d-inline-block auth-logo">
                                    <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="20">
                                </a>
                            </div>
                            <p class="mt-3 fs-15 fw-medium">Admin Panel</p>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4 card-bg-fill">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Welcome Back!</h5>
                                    <p class="text-muted">Sign in to continue to the admin panel.</p>
                                </div>

                                <div class="p-2 mt-4">
                                    <form id="loginForm" novalidate>
                                        @csrf

                                        {{-- Email --}}
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Enter your email" autocomplete="email" autofocus>
                                            <div class="invalid-feedback d-block text-danger small mt-1" id="error-email">
                                            </div>
                                        </div>

                                        {{-- Password --}}
                                        <div class="mb-3">
                                            <div class="float-end">
                                                <a href="{{ route('show.forgot-password') }}" class="text-muted">Forgot
                                                    password?</a>
                                            </div>
                                            <label class="form-label" for="password">Password <span
                                                    class="text-danger">*</span></label>
                                            <div class="position-relative auth-pass-inputgroup mb-1">
                                                <input type="password" class="form-control pe-5 password-input"
                                                    placeholder="Enter password" id="password" name="password"
                                                    autocomplete="current-password">
                                                <button
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                                                    type="button" id="password-toggle">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback d-block text-danger small mt-1"
                                                id="error-password"></div>
                                        </div>

                                        {{-- Remember Me --}}
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="auth-remember-check">
                                            <label class="form-check-label" for="auth-remember-check">Remember me</label>
                                        </div>

                                        {{-- Submit --}}
                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit" id="loginBtn">
                                                <span id="loginBtnText">Sign In</span>
                                                <span id="loginBtnSpinner" class="d-none">
                                                    <span class="spinner-border spinner-border-sm me-1"
                                                        role="status"></span>
                                                    Signing in...
                                                </span>
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('navigations.footer')
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── Password toggle ────────────────────────────────────────────────────
            document.getElementById('password-toggle').addEventListener('click', function() {
                const input = document.getElementById('password');
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'ri-eye-off-fill align-middle';
                } else {
                    input.type = 'password';
                    icon.className = 'ri-eye-fill align-middle';
                }
            });

            // ── Helpers ────────────────────────────────────────────────────────────
            function clearErrors() {
                document.querySelectorAll('[id^="error-"]').forEach(el => el.textContent = '');
                document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
            }

            function showErrors(errors) {
                Object.entries(errors).forEach(([field, messages]) => {
                    const errorEl = document.getElementById('error-' + field);
                    const inputEl = document.getElementById(field);
                    if (errorEl) errorEl.textContent = messages[0];
                    if (inputEl) inputEl.classList.add('is-invalid');
                });
            }

            function setLoading(state) {
                const btn = document.getElementById('loginBtn');
                const text = document.getElementById('loginBtnText');
                const spinner = document.getElementById('loginBtnSpinner');
                btn.disabled = state;
                text.classList.toggle('d-none', state);
                spinner.classList.toggle('d-none', !state);
            }

            // ── Form Submit ────────────────────────────────────────────────────────
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();
                setLoading(true);

                const data = {
                    email: document.getElementById('email').value.trim(),
                    password: document.getElementById('password').value,
                    _token: document.querySelector('meta[name="csrf-token"]').content,
                };

                axios.post('{{ route('admin.login') }}', data)
                    .then(res => {
                        Toastify({
                            text: res.data.message,
                            duration: 2500,
                            gravity: 'top',
                            position: 'right',
                            style: {
                                background: '#0ab39c'
                            },
                        }).showToast();

                        setTimeout(() => window.location.href = res.data.redirect, 1200);
                    })
                    .catch(err => {
                        setLoading(false);
                        const res = err.response;

                        if (res && res.data.errors) {
                            showErrors(res.data.errors);
                        } else {
                            Toastify({
                                text: res?.data?.message ||
                                    'Something went wrong. Please try again.',
                                duration: 3500,
                                gravity: 'top',
                                position: 'right',
                                style: {
                                    background: '#f06548'
                                },
                            }).showToast();
                        }
                    });
            });

        });
    </script>
@endpush
