@extends('layout.guest-layout')

@section('title', 'Forgot Password')

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
                                    <h5 class="text-primary">Forgot Password?</h5>
                                    <p class="text-muted">Enter your admin email — we'll send you a 6-digit OTP.</p>
                                    <lord-icon src="https://cdn.lordicon.com/rhvddzym.json" trigger="loop"
                                        colors="primary:#0ab39c" class="avatar-xl"></lord-icon>
                                </div>

                                <div class="alert border-0 alert-warning text-center mb-2 mx-2" role="alert">
                                    Enter your email and instructions will be sent to you!
                                </div>

                                <div class="p-2">
                                    <form id="forgotForm" novalidate>
                                        @csrf

                                        <div class="mb-4">
                                            <label for="email" class="form-label">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Enter your admin email" autocomplete="email" autofocus>
                                            <div class="text-danger small mt-1" id="error-email"></div>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit" id="submitBtn">
                                                <span id="submitBtnText">Send OTP</span>
                                                <span id="submitBtnSpinner" class="d-none">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status"
                                                        aria-hidden="true"></span>
                                                    Sending…
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
                                    class="fw-semibold text-primary text-decoration-underline">Click here</a>
                            </p>
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
                const btn = document.getElementById('submitBtn');
                const text = document.getElementById('submitBtnText');
                const spinner = document.getElementById('submitBtnSpinner');
                btn.disabled = state;
                text.classList.toggle('d-none', state);
                spinner.classList.toggle('d-none', !state);
            }

            document.getElementById('forgotForm').addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();
                setLoading(true);

                axios.post('{{ route('forgot-password') }}', {
                        email: document.getElementById('email').value.trim(),
                    })
                    .then(function(res) {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.href = res.data.redirect, 1200);
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
