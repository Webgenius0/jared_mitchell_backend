@extends('layout.guest-layout')

@section('title', 'Sign In')

@section('content')
    <div class="auth-page-wrapper pt-5">

        {{-- Animated background --}}
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
                                <img src="{{ asset('admin/default/logo.png') }}" alt="{{ config('app.name') }}" height="20">
                            </a>
                            <p class="mt-3 fs-15 fw-medium">Admin Panel</p>
                        </div>
                    </div>
                </div>

                {{-- Card --}}
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
                                            <label for="email" class="form-label">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="email"
                                                class="form-control"
                                                id="email"
                                                name="email"
                                                placeholder="Enter your email"
                                                autocomplete="email"
                                                autofocus
                                            >
                                            <div class="text-danger small mt-1" id="error-email"></div>
                                        </div>

                                        {{-- Password --}}
                                        <div class="mb-3">
                                            <div class="float-end">
                                                <a href="{{ route('show.forgot-password') }}" class="text-muted">
                                                    Forgot password?
                                                </a>
                                            </div>
                                            <label for="password" class="form-label">
                                                Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative auth-pass-inputgroup mb-1">
                                                <input
                                                    type="password"
                                                    class="form-control pe-5 password-input"
                                                    id="password"
                                                    name="password"
                                                    placeholder="Enter password"
                                                    autocomplete="current-password"
                                                >
                                                <button
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                                                    type="button"
                                                    id="passwordToggle"
                                                    aria-label="Toggle password visibility"
                                                >
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                            </div>
                                            <div class="text-danger small mt-1" id="error-password"></div>
                                        </div>

                                        {{-- Remember me --}}
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                            <label class="form-check-label" for="remember">Remember me</label>
                                        </div>

                                        {{-- Submit --}}
                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit" id="loginBtn">
                                                <span id="loginBtnText">Sign In</span>
                                                <span id="loginBtnSpinner" class="d-none">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                    Signing in…
                                                </span>
                                            </button>
                                        </div>

                                    </form>
                                </div>

                            </div>{{-- /.card-body --}}
                        </div>{{-- /.card --}}
                    </div>{{-- /.col --}}
                </div>{{-- /.row --}}

            </div>{{-- /.container --}}
        </div>{{-- /.auth-page-content --}}

        @include('navigations.footer')

    </div>{{-- /.auth-page-wrapper --}}
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Password visibility toggle ──────────────────────────── */
    document.getElementById('passwordToggle').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = this.querySelector('i');
        const isHidden = input.type === 'password';

        input.type      = isHidden ? 'text' : 'password';
        icon.className  = isHidden
            ? 'ri-eye-off-fill align-middle'
            : 'ri-eye-fill align-middle';
    });

    /* ── Helpers ─────────────────────────────────────────────── */
    function clearErrors() {
        document.querySelectorAll('[id^="error-"]').forEach(function (el) {
            el.textContent = '';
        });
        document.querySelectorAll('.form-control').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
    }

    function showFieldErrors(errors) {
        Object.entries(errors).forEach(function ([field, messages]) {
            const errorEl = document.getElementById('error-' + field);
            const inputEl = document.getElementById(field);
            if (errorEl) errorEl.textContent = messages[0];
            if (inputEl) inputEl.classList.add('is-invalid');
        });
    }

    function setLoading(loading) {
        const btn     = document.getElementById('loginBtn');
        const text    = document.getElementById('loginBtnText');
        const spinner = document.getElementById('loginBtnSpinner');

        btn.disabled = loading;
        text.classList.toggle('d-none', loading);
        spinner.classList.toggle('d-none', !loading);
    }

    /* ── Form submission ─────────────────────────────────────── */
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        setLoading(true);

        axios.post('{{ route('admin.login') }}', {
            email   : document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            remember: document.getElementById('remember').checked,
        })
        .then(function (res) {
            Toast.success(res.data.message);
            setTimeout(function () {
                window.location.href = res.data.redirect;
            }, 1200);
        })
        .catch(function (err) {
            setLoading(false);

            const data = err.response?.data;

            if (data?.errors) {
                // Field-level errors
                showFieldErrors(data.errors);
                // Also show the summary message in a toast
                if (data.message) Toast.error(data.message);
            } else {
                // Generic toast for unexpected errors
                Toast.fromResponse(data);
            }
        });
    });

});
</script>
@endpush
