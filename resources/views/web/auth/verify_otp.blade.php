@extends('layout.guest-layout')

@section('title', 'Verify OTP')

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
                            <div class="card-body p-3 p-md-4">

                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Verify OTP</h5>
                                    <p class="text-muted">Enter the 6-digit code sent to your email</p>
                                    <lord-icon src="https://cdn.lordicon.com/rhvddzym.json" trigger="loop"
                                        colors="primary:#0ab39c" class="avatar-xl"></lord-icon>
                                </div>

                                {{-- Email hint (from session) --}}
                                <div class="alert border-0 alert-success text-center mb-3 mx-2 py-2" role="alert">
                                    An OTP has been sent to
                                    <strong>{{ session('otp_email') }}</strong>
                                </div>

                                <div class="p-2">
                                    <form id="otpForm" novalidate>
                                        @csrf

                                        {{-- 6 individual OTP boxes --}}
                                        <div class="row g-2 mb-2 justify-content-center">
                                            @for ($i = 0; $i < 6; $i++)
                                                <div class="col-2 px-1">
                                                    <input type="text"
                                                        class="form-control form-control-lg text-center otp-input p-2"
                                                        maxlength="1" inputmode="numeric" autocomplete="one-time-code"
                                                        {{ $i === 0 ? 'autofocus' : '' }}>
                                                </div>
                                            @endfor
                                        </div>

                                        {{-- Field-level error --}}
                                        <div class="text-danger small text-center mb-3" id="error-otp"></div>

                                        {{-- Timer & Resend --}}
                                        <div class="text-center mb-3">
                                            <p class="text-muted mb-1 small">
                                                OTP expires in:
                                                <span class="fw-semibold text-primary" id="timer">10:00</span>
                                            </p>
                                            <p class="text-muted mb-0 small">
                                                Didn't receive code?
                                                <button type="button" id="resendBtn" disabled
                                                    class="btn btn-link p-0 fw-semibold border-0 bg-transparent text-decoration-underline">
                                                    Resend OTP
                                                </button>
                                            </p>
                                        </div>

                                        {{-- Submit --}}
                                        <div class="text-center mt-3">
                                            <button class="btn btn-success w-100 py-2" type="submit" id="verifyBtn">
                                                <span id="verifyBtnText">Verify OTP</span>
                                                <span id="verifyBtnSpinner" class="d-none">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status"
                                                        aria-hidden="true"></span>
                                                    Verifying…
                                                </span>
                                            </button>
                                        </div>

                                        <div class="text-center mt-3">
                                            <a href="{{ route('show.forgot-password') }}" class="text-muted small">
                                                <i class="ri-arrow-left-line align-bottom"></i> Back to Forgot Password
                                            </a>
                                        </div>

                                    </form>
                                </div>

                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <p class="mb-0 small">Remember your password?
                                <a href="{{ route('show.admin.login') }}"
                                    class="fw-semibold text-primary text-decoration-underline">Login here</a>
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
        .otp-input {
            font-size: 1.3rem !important;
            font-weight: 700;
            border: 2px solid #e9e9ef;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            height: 52px !important;
            padding: 0 !important;
            border-radius: 8px;
        }

        .otp-input:focus {
            border-color: #0ab39c !important;
            box-shadow: 0 0 0 0.2rem rgba(10, 179, 156, .2) !important;
        }

        .otp-input.is-invalid {
            border-color: #f06548 !important;
            box-shadow: 0 0 0 0.2rem rgba(240, 101, 72, .2) !important;
        }

        #resendBtn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            color: #878a99 !important;
        }

        #resendBtn:not(:disabled) {
            color: #0ab39c !important;
        }

        #resendBtn:not(:disabled):hover {
            color: #088b7a !important;
        }

        @media (max-width: 576px) {
            .otp-input {
                font-size: 1.1rem !important;
                height: 44px !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const otpInputs = document.querySelectorAll('.otp-input');
            const timerEl = document.getElementById('timer');
            const resendBtn = document.getElementById('resendBtn');
            const errorEl = document.getElementById('error-otp');

            // ── OTP input navigation ──────────────────────────────────────────────
            otpInputs.forEach(function(input, index) {

                // Only digits
                input.addEventListener('keypress', function(e) {
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });

                // Auto-advance on input
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, ''); // strip non-digits (mobile)
                    if (this.value.length === 1 && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                });

                // Backspace: clear + go back
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        otpInputs[index - 1].value = '';
                        otpInputs[index - 1].focus();
                    }
                });

                // Paste: spread across boxes
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0,
                        otpInputs.length);
                    pasted.split('').forEach(function(char, i) {
                        if (otpInputs[i]) otpInputs[i].value = char;
                    });
                    const nextEmpty = pasted.length < otpInputs.length ? pasted.length : otpInputs
                        .length - 1;
                    otpInputs[nextEmpty].focus();
                });
            });

            // ── Timer ─────────────────────────────────────────────────────────────
            let timeLeft = 600; // 10 minutes (matches server-side expiry)
            let timerInterval;

            function formatTime(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                return m + ':' + s;
            }

            function startTimer() {
                clearInterval(timerInterval);
                timeLeft = 600;
                resendBtn.disabled = true;
                timerEl.classList.remove('text-danger');
                timerEl.classList.add('text-primary');

                timerInterval = setInterval(function() {
                    timeLeft--;
                    timerEl.textContent = formatTime(timeLeft);

                    // Turn red in last 60 seconds
                    if (timeLeft <= 60) {
                        timerEl.classList.remove('text-primary');
                        timerEl.classList.add('text-danger');
                    }

                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        timerEl.textContent = '00:00';
                        resendBtn.disabled = false;
                    }
                }, 1000);

                timerEl.textContent = formatTime(timeLeft);
            }

            startTimer();

            // ── Helpers ───────────────────────────────────────────────────────────
            function getOtp() {
                return Array.from(otpInputs).map(el => el.value).join('');
            }

            function clearError() {
                errorEl.textContent = '';
                otpInputs.forEach(el => el.classList.remove('is-invalid'));
            }

            function showError(msg) {
                errorEl.textContent = msg;
                otpInputs.forEach(el => el.classList.add('is-invalid'));
                otpInputs[0].focus();
            }

            function setLoading(state) {
                const btn = document.getElementById('verifyBtn');
                const text = document.getElementById('verifyBtnText');
                const spinner = document.getElementById('verifyBtnSpinner');
                btn.disabled = state;
                text.classList.toggle('d-none', state);
                spinner.classList.toggle('d-none', !state);
            }

            // ── Submit OTP ────────────────────────────────────────────────────────
            document.getElementById('otpForm').addEventListener('submit', function(e) {
                e.preventDefault();
                clearError();

                const otp = getOtp();

                if (otp.length < 6) {
                    showError('Please enter all 6 digits.');
                    return;
                }

                setLoading(true);

                axios.post('{{ route('otp.verification') }}', {
                        otp: otp
                    })
                    .then(function(res) {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.href = res.data.redirect, 1200);
                    })
                    .catch(function(err) {
                        setLoading(false);
                        const data = err.response?.data;
                        if (data?.errors?.otp) {
                            showError(data.errors.otp[0]);
                        } else {
                            Toast.fromResponse(data);
                        }
                    });
            });

            // ── Resend OTP ────────────────────────────────────────────────────────
            resendBtn.addEventListener('click', function() {
                resendBtn.disabled = true;

                // Clear all boxes
                otpInputs.forEach(el => {
                    el.value = '';
                    el.classList.remove('is-invalid');
                });
                errorEl.textContent = '';

                axios.post('{{ route('forgot-password') }}', {
                        email: '{{ session('otp_email') }}',
                    })
                    .then(function(res) {
                        Toast.success(res.data.message || 'A new OTP has been sent to your email.');
                        startTimer();
                    })
                    .catch(function(err) {
                        resendBtn.disabled = false;
                        const data = err.response?.data;
                        Toast.fromResponse(data);
                    });
            });

        });
    </script>
@endpush
